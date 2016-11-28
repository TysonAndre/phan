<?php declare(strict_types=1);
namespace Phan\Analysis;

use \ast\Node;
use Phan\AST\Visitor\KindVisitorImplementation;

/**
 * This simplifies a PHP AST into a form which is easier to analyze.
 * Precondition: The original \ast\Node objects are not modified.
 *
 * This caches the status for AST nodes, so references to this object
 * should be removed once the source transformation of a file/function is complete.
 *
 * TODO: Change to AnalysisVisitor if this ever emits issues.
 */
class BlockExitStatusChecker extends KindVisitorImplementation {
    const STATUS_PROCEED  = 1;  // At least one branch continues to completion.
    const STATUS_CONTINUE = 2;  // All branches lead to a continue statement (Or possibly a break, throw, or return)
    const STATUS_BREAK    = 3;  // All branches lead to a break statement (Or possibly a throw or return)
    const STATUS_THROW    = 4;  // All branches lead to a throw statement (Or possibly a return)
    const STATUS_RETURN   = 5;  // All branches lead to a return/exit statement

    /** @var \SplObjectStorage */
    private $_exitStatusCache;
    /** @var string - filename, for debugging*/
    private $_filename;

    public function __construct(string $filename = 'unknown') {
        $this->_exitStatusCache = new \SplObjectStorage();
        $this->_filename = $filename;
    }

    public function check(Node $node = null) : int {
        if (!$node) {
            return self::STATUS_PROCEED;
        }
        $result = $this($node);
        assert(is_int($result), 'Expected int');
        return $result;
    }

    /**
     * @param Node $node - The node to fetch the status of.
     * @param \Closure $cb - Callable accepting a node and returning an exit status.
     * @return int
     */
    private function memoizedStatus(Node $node, \Closure $cb) {
        if (isset($this->_exitStatusCache[$node])) {
            return $this->_exitStatusCache[$node];  // Can't use null coalescing operator for SplObjectStorage due to a php bug. Fixed in 7.0.14
        }
        $status = $cb($node);
        $this->_exitStatusCache->offsetSet($node, $status);  // TODO: Change to regular field assignment after https://github.com/etsy/phan/issues/254 is fixed.
        return $status;
    }

    public function visit(Node $node) {
        return self::STATUS_PROCEED;
    }

    private static function _is_truthy_literal($cond) : bool
    {
        if ($cond instanceof Node) {
            // TODO: Could look up constants and inline expressions, but doing that has low value.
            return false;
        }
        // Cast string, int, etc. literal to a bool
        return (bool)$cond;
    }

    public function visitBreak(Node $node) {
        return self::STATUS_BREAK;
    }

    public function visitContinue(Node $node)
    {
        return self::STATUS_CONTINUE;
    }

    public function visitThrow(Node $node) {
        return self::STATUS_THROW;
    }

    public function visitReturn(Node $node)
    {
        return self::STATUS_RETURN;
    }

    public function visitExit(Node $node)
    {
        return self::STATUS_RETURN;
    }

    public function visitStmtList(Node $node)
    {
        return $this->memoizedStatus($node, function(Node $node) {
            return $this->getStatusOfBlock($node->children ?? []);
        });
    }

    // TODO: Check if for/while/foreach block will execute at least once.
    // (e.g. for ($i = 0; $i < 10; $i++) is guaranteed to work)
    // For now, assume it's possible they may execute 0 times.

    private function visitBranched(Node $node) {
        return $this->memoizedStatus($node, function(Node $node) {
            // A do-while statement and an if branch are executed at least once (or exactly once)
            // TODO: deduplicate
            $stmts = $node->children['stmts'];
            if (is_null($stmts)) {
                return self::STATUS_PROCEED;
            }
            // We can have a single statement in the 'stmts' field when no braces exist?
            if (!($stmts instanceof Node)) {
                return self::STATUS_PROCEED;
            }
            // This may be a statement list (or in rare cases, a statement?)
            $status = $this->check($stmts);
            if ($node->kind === \ast\AST_DO_WHILE) {
                // ignore break/continue within a do{}while ($cond);
                return in_array($status, [self::STATUS_THROW, self::STATUS_RETURN]) ? $status : self::STATUS_PROCEED;
            }
            return $status;
        });
    }

    public function visitDoWhile(Node $node) {
        return $this->visitBranched($node);
    }

    /**
     * @param \ast\Node[] $block
     */
    private function getStatusOfBlock(array $block) : int {
        foreach ($block as $child) {
            if ($child === null) {
                continue;
            }
            // e.g. can be non-Node for statement lists such as `if ($a) { return; }echo "X";2;` (under unknown conditions)
            if (!($child instanceof Node)) {
                continue;
            }
            $status = $this->check($child);
            if ($status !== self::STATUS_PROCEED) {
                // The statement after this one is unreachable, due to unconditional continue/break/throw/return.
                return $status;
            }
        }
        return self::STATUS_PROCEED;
    }
}
