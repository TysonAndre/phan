<?php declare(strict_types=1);
namespace Phan\AST;

use \ast\Node;

/**
 * This simplifies a PHP AST into a form which is easier to analyze.
 * Precondition: The original \ast\Node objects are not modified.
 *
 * This caches the status for AST nodes, so references to this object
 * should be removed once the source transformation of a file/function is complete.
 */
class BlockExitStatusChecker {
    const STATUS_PROCEED  = 1;  // At least one branch continues to completion.
    const STATUS_CONTINUE = 2;  // All branches lead to a continue statement (Or possibly a break, throw, or return)
    const STATUS_BREAK    = 3;  // All branches lead to a break statement (Or possibly a throw or return)
    const STATUS_THROW    = 4;  // All branches lead to a throw statement (Or possibly a return)
    const STATUS_RETURN   = 5;  // All branches lead to a return/exit statement

    /** @var \SplObjectStorage */
    private $_exitStatusCache;
    /** @var string */
    private $_filename;

    public function __construct(string $filename = 'unknown') {
        $this->_exitStatusCache = new \SplObjectStorage();
        $this->_filename = $filename;
    }

    public function check(Node $node = null) : int {
        if (!$node) {
            return self::STATUS_PROCEED;
        }
        if (isset($this->_exitStatusCache[$node])) {
            return $this->_exitStatusCache[$node];  // Can't use null coalescing operator for SplObjectStorage due to a php bug.
        }
        $status = $this->_checkInner($node);
        /*
        } catch(\Exception $e) {
            // FIXME: Emit issue or log?
            printf("Caught exception processing node of type %d: %s\n%s", $node->kind, $e->getMessage(), $e->getTraceAsString());
            var_export($node);
            $status = self::STATUS_PROCEED;
        }
         */
        $this->_exitStatusCache[$node] = $status;
        return $status;
    }

    private static function _is_truthy_literal($cond) : bool {
        if ($cond instanceof Node) {
            return false;
        }
        // Cast string, int, etc. literal to a bool
        return (bool)$cond;
    }
    private function _checkInner(Node $node) : int {
        switch ($node->kind) {
        case \ast\AST_FUNC_DECL:
        case \ast\AST_METHOD:
        case \ast\AST_CLOSURE:
            return self::STATUS_PROCEED; // Ignore these
        case \ast\AST_CONTINUE:
            return self::STATUS_CONTINUE;
        case \ast\AST_BREAK:
            return self::STATUS_BREAK;
        case \ast\AST_THROW:
            return self::STATUS_THROW;
        case \ast\AST_RETURN:
        case \ast\AST_EXIT:
            return self::STATUS_RETURN;
        case \ast\AST_STMT_LIST:
            return $this->_getStatusOfBlock($node->children);
        // Conditional blocks:
        case \ast\AST_FOR:
        case \ast\AST_FOREACH:
        case \ast\AST_WHILE:
            // TODO: Check if this will be executed at least once.
            return self::STATUS_PROCEED;
        case \ast\AST_DO_WHILE:
        case \ast\AST_IF_ELEM:
            $stmts = $node->children['stmts'];
            if (is_null($stmts) || !($stmts instanceof Node)) {  // TODO: Why does non-Node happen sometimes?
                return self::STATUS_PROCEED;
            }
            if ($stmts->kind !== \ast\AST_STMT_LIST) {
                // Some of the time, it's a single statement instead of a statement list.
                return self::STATUS_PROCEED;  // TODO: Handle it as an individual statement?
            }
            $status = $this->_getStatusOfBlock($stmts->children, $node);
            if ($node->kind === \ast\AST_DO_WHILE) {
                // ignore break/continue within a do{}while ($cond);
                return in_array($status, [self::STATUS_THROW, self::STATUS_RETURN]) ? $status : self::STATUS_PROCEED;
            }
            return $status;
        case \ast\AST_IF:
            $stmts = $node->children;
            if (count($node->children) === 1 && !self::_is_truthy_literal($stmts[0]->children['cond'])) {
                return self::STATUS_PROCEED;
            }
            if (count($node->children) >= 2) {
                // Can have more than 2 if `elseif` is used.
                // TODO: Check for unconditional statements before the last one?
                $lastcond = $stmts[count($stmts) - 1];
                if ($lastCond !== null && !self::_is_truthy_literal($lastCond)) {
                    // It's possible none of the branches will match.
                    return self::STATUS_PROCEED;
                }
            }
            // if-else statements, or unconditionals such as if (true)

            // TODO: Check for literal false values
            return min(array_map(function($childNode) {
                if ($childNode instanceof Node) {
                    return $this->check($childNode);
                }
                return self::STATUS_PROCEED;  // No-op node with just a literal?
            }, $stmts));
            // TODO: handle case \ast\AST_TRY
            // (E.g. by checking for everything except throw statements in the `try` block,
            // and by making the optional `finally` take precedence,
            // and checking if try and catches all have the same status (E.g. all have a `continue` statement))
        }

        return self::STATUS_PROCEED;
    }

    /**
     * @param \ast\Node[] $block
     */
    private function _getStatusOfBlock(array $block) : int {
        foreach ($block as $child) {
            // e.g. can be non-string for statement lists such as `if ($a) { return; }echo "X";2;` (under unknown conditions)
            if (!($child instanceof Node) && $child !== null) {
                continue;
                /*
                global $currentAnalyzeFile;
                printf("In %s\n", $this->_filename);
                var_export($block);
                var_export($debugVal);
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                debug_print_backtrace();
                exit(1);
                 */
            }
            $status = $this->check($child);
            if ($status !== self::STATUS_PROCEED) {
                return $status;
            }
        }
        return self::STATUS_PROCEED;
    }

}
