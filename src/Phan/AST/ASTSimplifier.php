<?php declare(strict_types=1);
namespace Phan\AST;

use Phan\AST\BlockExitStatusChecker;
use ast\Node;

require_once __DIR__ . '/BlockExitStatusChecker.php';

/**
 * This simplifies a PHP AST into a form which is easier to analyze.
 * The original \ast\Node objects are not modified.
 */
class ASTSimplifier {
    /** @var Node */
    private $_originalAst;
    /** @var BlockExitStatusChecker */
    private $_blockChecker;

    public function __construct() {
        $this->_blockChecker = new BlockExitStatusChecker();
    }

    private function _apply(Node $node) : array {
        switch ($node->kind) {
        case \ast\AST_FUNC_DECL:
        case \ast\AST_METHOD:
        case \ast\AST_CLOSURE:
        case \ast\AST_CLASS:
            return [$this->_applyToStmts($node)];
        case \ast\AST_BREAK:
        case \ast\AST_CONTINUE:
        case \ast\AST_RETURN:
        case \ast\AST_THROW:
        case \ast\AST_EXIT:
            return [$node];
        case \ast\AST_STMT_LIST:
            return [$this->_applyToStatementList($node)];
        // Conditional blocks:
        case \ast\AST_DO_WHILE:
        case \ast\AST_FOR:
        case \ast\AST_FOREACH:
        case \ast\AST_WHILE:
            return [$this->_applyToStmts($node)];
        case \ast\AST_IF:
            return $this->_normalizeIfStatment($node);
        case \ast\AST_TRY:
            return [$this->_normalizeTryStatement($node)];
        }

        // TODO
        return [$node];
    }

    private function _applyToStmts(Node $node) : Node {
        $stmts = $node->children['stmts'];
        if ($stmts === null) {
            return $node;
        }
        $newStmts = $this->_applyToStatementList($stmts);
        if ($newStmts === $stmts) {
            return $node;
        }
        $newNode = clone($node);
        $newNode->children['stmts'] = $newStmts;
        return $newNode;
    }

    private function _applyToStatementList(Node $statementList) : Node {
        //assert($statementList->kind === \ast\AST_STMT_LIST);
        $newChildren = [];
        foreach ($statementList->children as $childNode) {
            if ($childNode instanceof Node) {
                foreach ($this->_apply($childNode) as $newChildNode) {
                    $newChildren[] = $newChildNode;
                }
            } else {
                $newChildren[] = $childNode;
            }
        }
        $newChildren = $this->_normalizeStatementList($newChildren);
        if ($newChildren === $statementList->children) {
            return $statementList;
        }
        $cloneNode = clone($statementList);
        $cloneNode->children = $newChildren;
        return $cloneNode;
    }

    /**
     * @param \ast\Node[] $statements
     */
    private function _normalizeStatementList(array $statements) : array {
        for ($i = count($statements) - 1; $i >= 0; $i--) {
            $stmt = $statements[$i];
            if (!($stmt instanceof Node)) {
                continue;
            }
            if ($stmt->kind !== \ast\AST_IF) {
                continue;
            }
            if (count($statements) > $i + 1) {
                $N = count($stmt->children);
                if ($N === 2 && $this->_blockChecker->check($stmt->children[1]->children['stmts']) !== BlockExitStatusChecker::STATUS_PROCEED) {
                    // If the else statement is guaranteed to break/continue/return/throw,
                    // then merge the remaining statements following that into the `if` block.
                    $newIfElem = clone($stmt->children[0]);
                    $newIfElem->children['stmts']->children = array_merge($newIfElem->children['stmts']->children, array_slice($statements, $i + 1));
                    $newIf = clone($stmt);
                    $newIf->children[0] = $newIfElem;
                    $statements[$i] = $newIf;
                    $statements = array_slice($statements, 0, $i + 1);
                    continue;
                }
                if (($N == 1 || $stmt->children[1]->children['cond'] === null) && $this->_blockChecker->check($stmt->children[0]->children['stmts']) !== BlockExitStatusChecker::STATUS_PROCEED) {
                    // If the if statement is guaranteed to break/continue/return/throw,
                    // then merge the remaining statements following that into the `else` block.
                    // Create an `else` block if necessary.
                    // This prevents inferences(e.g. in Phan) from the `if` block from leaking out into the remaining statemtns.
                    if ($N == 1) {
                        $newElseElem = clone($stmt->children[0]);
                        $newElseElem->children['cond'] = null;
                        $elseStmtList = clone($newElseElem->children['stmts']);
                        $elseStmtList->children = [];
                        $newElseElem->children['stmts'] = $elseStmtList;
                    } else {
                        $newElseElem = clone($stmt->children[1]);
                        $newElseElem->children['stmts'] = clone($newElseElem->children['stmts']);
                    }
                    $newElseElem->children['stmts']->children = array_merge($newElseElem->children['stmts']->children, array_slice($statements, $i + 1));
                    $newIfElse = clone($stmt);
                    $newIfElse->children[1] = $newElseElem;
                    // We might end up undoing a negation as well, now that there is an else branch.
                    // Run _normalizeIfStatment again.
                    $statements = array_merge(array_slice($statements, 0, $i), $this->_normalizeIfStatment($newIfElse));
                    continue;
                }
            }
        }
        return $statements;
    }

    /** @return \ast\Node[] */
    private static function _replace_nodes(array $nodes, int $pos, Node... $newStatements) : array {
        return array_merge(array_slice($nodes, 0, $pos), $newStatements, array_slice($nodes, $pos + 1));
    }

    // TODO: Need to map a Node to a **LIST** of nodes (to be part of a AST_STMT_LIST).
    private function _normalizeIfStatment(Node $originalNode) : array {
        $oldNodes = [];
        $nodes = [$originalNode];
        // Repeatedly apply these rules
        while ($oldNodes !== $nodes) {
            $pos = count($nodes) - 1;
            $oldNodes = $nodes;
            $node = $nodes[$pos];
            $ifCond = $node->children[0]->children['cond'];
            if (!($ifCond instanceof Node)) {
                break;  // No transformation rules apply here.
            }

            if ($ifCond->kind === \ast\AST_UNARY_OP &&
                    $ifCond->flags === \ast\flags\UNARY_BOOL_NOT &&
                    $ifCond->children['expr']->kind === \ast\AST_UNARY_OP &&
                    $ifCond->children['expr']->flags === \ast\flags\UNARY_BOOL_NOT) {
                $nodes = self::_replace_nodes($nodes, $pos, $this->_applyIfDoubleNegateReduction($node));
                continue;
            }
            if (count($node->children) === 1) {
                if ($ifCond->kind === \ast\AST_BINARY_OP &&
                        $ifCond->flags === \ast\flags\BINARY_BOOL_AND) {
                    $nodes = self::_replace_nodes($nodes, $pos, $this->_applyIfAndReduction($node));
                    // if (A && B) {X} -> if (A) { if (B) {X}}
                    // Do this, unless there is an else statement that can be executed.
                    continue;
                }
            } else if (count($node->children) === 2) {
                if ($ifCond->kind === \ast\AST_UNARY_OP &&
                        $ifCond->flags === \ast\flags\UNARY_BOOL_NOT &&
                        $node->children[1]->children['cond'] === null) {
                    $nodes = self::_replace_nodes($nodes, $pos, $this->_applyIfNegateReduction($node));
                    continue;
                }
            }
            if ($ifCond->kind === \ast\AST_ASSIGN &&
                    $ifCond->children['var']->kind === \ast\AST_VAR) {
                // if ($var = A) {X} -> $var = A; if ($var) {X}
                // do this whether or not there is an else.
                $nodes = self::_replace_nodes($nodes, $pos, ...$this->_applyIfAssignReduction($node));
                continue;
            }
        }
        return $nodes;
    }

    private function _applyIfAndReduction(Node $node) : Node {
        assert(count($node->children) == 1);
        $innerNodeElem = clone($node->children[0]);
        $innerNodeElem->children['cond'] = $innerNodeElem->children['cond']->children['right'];
        $innerNode = clone($node);
        $innerNode->children[0] = $innerNodeElem;
        $innerNode->lineno = $innerNodeElem->lineno;
        $innerNodeStmtList = new Node();
        $innerNodeStmtList->kind = \ast\AST_STMT_LIST;
        $innerNodeStmtList->flags = 0;
        $innerNodeStmtList->lineno = $innerNode->lineno;
        $innerNodeStmtList->children = [$innerNode];
        $outerNodeElem = clone($node->children[0]);
        $outerNodeElem->children['cond'] = $node->children[0]->children['cond']->children['left'];
        $outerNodeElem->children['stmts'] = $innerNodeStmtList;
        $outerNode = clone($node);
        $outerNode->children[0] = $outerNodeElem;
        return $outerNode;
    }

    /** @return \ast\Node[] */
    private function _applyIfAssignReduction(Node $node) : array {
        $outerAssignStatement = $node->children[0]->children['cond'];
        $newNodeElem = clone($node->children[0]);
        $newNodeElem->children['cond'] = $newNodeElem->children['cond']->children['var'];
        $newNode = clone($node);
        $newNode->children[0] = $newNodeElem;
        $newNode->lineno = $newNodeElem->lineno;
        return [$outerAssignStatement, $newNode];
    }

    private function _applyIfNegateReduction(Node $node) : Node {
        assert(count($node->children) === 2);
        assert($node->children[0]->children['cond']->flags === \ast\flags\UNARY_BOOL_NOT);
        assert($node->children[1]->children['cond'] === null);
        $newNode = clone($node);
        $ifElem = $newNode->children[0];
        $newNode->children = [clone($newNode->children[1]), clone($newNode->children[0])];
        $newNode->children[0]->children['cond'] = $node->children[0]->children['cond']->children['expr'];
        $newNode->children[1]->children['cond'] = null;
        return $newNode;
    }

    private function _applyIfDoubleNegateReduction(Node $node) : Node {
        assert($node->children[0]->children['cond']->flags === \ast\flags\UNARY_BOOL_NOT);
        assert($node->children[0]->children['cond']->children['expr']->flags === \ast\flags\UNARY_BOOL_NOT);

        $newCond = $node->children[0]->children['cond']->children['expr']->children['expr'];
        $newNode = clone($node);
        $newNode->children[0] = clone($node->children[0]);
        $newNode->children[0]->children['cond'] = $newCond;

        return $newNode;
    }

    private function _normalizeCatchesList(Node $catches) : Node {
        $list = $catches->children;
        $newList = array_map(function(Node $node) {
            return $this->_applyToStmts($node);
        }, $list);
        if ($newList === $list) {
            return $catches;
        }
        $newCatches = clone($catches);
        $newCatches->children = $newList;
        return $newCatches;
    }

    private function _normalizeTryStatement(Node $node) : Node {
        $try = $node->children['try'];
        $catches = $node->children['catches'];
        $finally = $node->children['finally'] ?? null;
        $newTry = $this->_applyToStatementList($try);
        $newCatches = $catches ? $this->_normalizeCatchesList($catches) : $catches;
        $newFinally = $finally ? $this->_applyToStatementList($finally) : $finally;
        if ($newTry === $try && $newCatches === $catches && $newFinally === $finally) {
            return $node;
        }
        $newNode = clone($node);
        $newNode->children['try'] = $newTry;
        $newNode->children['catches'] = $newCatches;
        $newNode->children['finally'] = $newFinally;
        return $newNode;
    }

    public static function apply_static(Node $node) : Node {
        $rewriter = new self();
        $nodes = $rewriter->_apply($node);
        assert(count($nodes) === 1);
        return $nodes[0];
    }
}
