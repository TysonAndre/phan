<?php declare(strict_types=1);
namespace Phan\AST;

use Phan\Analysis\BlockExitStatusChecker;
use ast\Node;

/**
 * This simplifies a PHP AST into a form which is easier to analyze.
 * The original \ast\Node objects are not modified.
 */
class ASTSimplifier {
    /** @var BlockExitStatusChecker */
    private $_blockChecker;
    /** @var string - for debugging purposes */
    private $_filename;

    public function __construct(string $filename = 'unknown') {
        $this->_blockChecker = new BlockExitStatusChecker($filename);
        $this->_filename = $filename;
    }

    private function _apply(Node $node) : array {
        switch ($node->kind) {
        case \ast\AST_FUNC_DECL:
        case \ast\AST_METHOD:
        case \ast\AST_CLOSURE:
        case \ast\AST_CLASS:
            return [$this->applyToStmts($node)];
        case \ast\AST_BREAK:
        case \ast\AST_CONTINUE:
        case \ast\AST_RETURN:
        case \ast\AST_THROW:
        case \ast\AST_EXIT:
            return [$node];
        case \ast\AST_STMT_LIST:
            return [$this->applyToStatementList($node)];
        // Conditional blocks:
        case \ast\AST_DO_WHILE:
        case \ast\AST_FOR:
        case \ast\AST_FOREACH:
        case \ast\AST_WHILE:
            return [$this->applyToStmts($node)];
        case \ast\AST_IF:
            return $this->normalizeIfStatement($node);
        case \ast\AST_TRY:
            return [$this->normalizeTryStatement($node)];
        }

        // TODO
        return [$node];
    }

    private function applyToStmts(Node $node) : Node {
        $stmts = $node->children['stmts'];
        // Can be null, a single statement, or (possibly) a scalar instead of a node?
        if (!($stmts instanceof Node)) {
            return $node;
        }
        $newStmts = $this->applyToStatementList($stmts);
        if ($newStmts === $stmts) {
            return $node;
        }
        $newNode = clone($node);
        $newNode->children['stmts'] = $newStmts;
        return $newNode;
    }

    private function applyToStatementList(Node $statementList) : Node {
        if ($statementList->kind !== \ast\AST_STMT_LIST) {
            $statementList = self::buildStatementList($statementList->lineno ?? 0, $statementList);
        }
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
        $newChildren = $this->normalizeStatementList($newChildren);
        if ($newChildren === $statementList->children) {
            return $statementList;
        }
        $cloneNode = clone($statementList);
        $cloneNode->children = $newChildren;
        return $cloneNode;
    }

    private static function buildStatementList(int $lineno, Node ...$child_nodes) : Node {
        $stmt_list = new Node();
        $stmt_list->lineno = $lineno;
        $stmt_list->kind = \ast\AST_STMT_LIST;
        $stmt_list->flags = 0;
        $stmt_list->children = $child_nodes;
        return $stmt_list;
    }

    /**
     * Get a modifiable Node that is a clone of the statement or statement list.
     * The resulting Node has kind AST_STMT_LIST
     */
    private static function cloneStatementList(Node $stmtList = null) : Node {
        if (is_null($stmtList)) {
            return self::buildStatementList(0);
        }
        if ($stmtList->kind === \ast\AST_STMT_LIST) {
            return clone($stmtList);
        }
        // $parent->children['stmts'] is a statement, not a statement list.
        return self::buildStatementList($stmtList->lineno ?? 0, $stmtList);
    }

    /**
     * @param \ast\Node[] $statements
     */
    private function normalizeStatementList(array $statements) : array {
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
                if ($N > 2) {
                    continue;  // early exit, no simplification rules apply
                }
                // if (A) {X} else {Y_NOEXIT} Z -> if (A) {X; Z} else {Y_NOEXIT}
                // (Note that the above rule does not apply to elseifs)
                if ($N === 2 &&
                        ($stmt->children[1]->children['stmts'] instanceof Node) &&
                        $stmt->children[1]->children['cond'] === null &&  // cannot be elseif
                        $this->_blockChecker->check($stmt->children[1]->children['stmts']) !== BlockExitStatusChecker::STATUS_PROCEED) {
                    // If the else statement is guaranteed to break/continue/return/throw,
                    // then merge the remaining statements following that into the `if` block.
                    $newIfElem = clone($stmt->children[0]);
                    $newStmts = self::cloneStatementList($newIfElem->children['stmts']);
                    $newStmts->children = array_merge($newStmts->children, array_slice($statements, $i + 1));
                    $newIfElem->children['stmts'] = $newStmts;
                    $newIf = clone($stmt);
                    $newIf->children[0] = $newIfElem;
                    // Replace the old `if` node (followed by statements) with the new `if` node
                    while (count($statements) > $i) {
                        array_pop($statements);
                    }
                    $statements[$i] = $newIf;
                    continue;
                }
                if (($N == 1 || ($N == 2 && $stmt->children[1]->children['cond'] === null)) &&
                        $stmt->children[0]->children['stmts'] instanceof Node &&  // Why does php-ast sometime return string.
                        $this->_blockChecker->check($stmt->children[0]->children['stmts']) !== BlockExitStatusChecker::STATUS_PROCEED) {
                    // If the if statement is guaranteed to break/continue/return/throw,
                    // then merge the remaining statements following that into the `else` block (not `elseif`)
                    // Create an `else` block if necessary.
                    // This prevents inferences(e.g. in Phan) from the `if` block from leaking out into the remaining statemtns.
                    if ($N == 1) {
                        $newElseElem = clone($stmt->children[0]);
                        $newElseElem->children['cond'] = null;
                        // Don't clone the original if statement - It might not be a statement list.
                        $newElseElem->children['stmts'] = self::buildStatementList($stmt->children[0]->lineno ?? 0);
                    } else {
                        assert($N === 2);
                        $newElseElem = clone($stmt->children[1]);
                        // Convert a singular statement (or null) into a statement list, if necessary.
                        $newElseElem->children['stmts'] = self::cloneStatementList($newElseElem->children['stmts']);
                    }
                    $newElseElem->children['stmts']->children = array_merge($newElseElem->children['stmts']->children, array_slice($statements, $i + 1));
                    $newIfElse = clone($stmt);
                    $newIfElse->children[1] = $newElseElem;
                    // We might end up undoing a negation as well, now that there is an else branch.
                    // Run normalizeIfStatement again.
                    while (count($statements) > $i) {
                        array_pop($statements);
                    }
                    array_push($statements, ...$this->normalizeIfStatement($newIfElse));
                    continue;
                }
            }
        }
        return $statements;
    }

    /**
     * @param \ast\Node[] $nodes
     * @param \ast\Node[] $newStatements
     * @return void
     */
    private static function replaceLastNodeWithNodeList(array &$nodes, Node... $newStatements) {
        assert(count($nodes) > 0);
        array_pop($nodes);
        foreach ($newStatements as $stmt) {
            $nodes[] = $stmt;
        }
    }

    private function normalizeIfStatement(Node $originalNode) : array {
        $oldNodes = [];
        $nodes = [$originalNode];
        // Repeatedly apply these rules
        while ($oldNodes !== $nodes) {
            $oldNodes = $nodes;
            $node = $nodes[count($nodes) - 1];
            $ifCond = $node->children[0]->children['cond'];
            if (!($ifCond instanceof Node)) {
                break;  // No transformation rules apply here.
            }

            if ($ifCond->kind === \ast\AST_UNARY_OP &&
                    $ifCond->flags === \ast\flags\UNARY_BOOL_NOT &&
                    $ifCond->children['expr']->kind === \ast\AST_UNARY_OP &&
                    $ifCond->children['expr']->flags === \ast\flags\UNARY_BOOL_NOT) {
                self::replaceLastNodeWithNodeList($nodes, $this->_applyIfDoubleNegateReduction($node));
                continue;
            }
            if (count($node->children) === 1) {
                if ($ifCond->kind === \ast\AST_BINARY_OP &&
                        $ifCond->flags === \ast\flags\BINARY_BOOL_AND) {
                    self::replaceLastNodeWithNodeList($nodes, $this->_applyIfAndReduction($node));
                    // if (A && B) {X} -> if (A) { if (B) {X}}
                    // Do this, unless there is an else statement that can be executed.
                    continue;
                }
            } else if (count($node->children) === 2) {
                if ($ifCond->kind === \ast\AST_UNARY_OP &&
                        $ifCond->flags === \ast\flags\UNARY_BOOL_NOT &&
                        $node->children[1]->children['cond'] === null) {
                    self::replaceLastNodeWithNodeList($nodes, $this->_applyIfNegateReduction($node));
                    continue;
                }
            } else if (count($node->children) >= 3) {
                self::replaceLastNodeWithNodeList($nodes, $this->_applyIfChainReduction($node));
                continue;
            }
            if ($ifCond->kind === \ast\AST_ASSIGN &&
                    $ifCond->children['var']->kind === \ast\AST_VAR) {
                // if ($var = A) {X} -> $var = A; if ($var) {X}
                // do this whether or not there is an else.
                // TODO: Could also reduce `if (($var = A) && B) {X} else if (C) {Y} -> $var = A; ....
                self::replaceLastNodeWithNodeList($nodes, ...$this->_applyIfAssignReduction($node));
                continue;
            }
        }
        return $nodes;
    }

    private function buildIfNode(Node $l, Node $r) : Node {
        assert($l->kind === \ast\AST_IF_ELEM);
        assert($r->kind === \ast\AST_IF_ELEM);
        $ifNode = new Node();
        $ifNode->kind = \ast\AST_IF;
        $ifNode->lineno = $l->lineno ?? 0;
        $ifNode->flags = 0;
        $ifNode->children = [$l, $r];
        return $ifNode;
    }

    /**
     * maps if (A) {X} elseif (B) {Y} else {Z} -> if (A) {Y} else { if (B) {Y} else {Z}}
     */
    private function _applyIfChainReduction(Node $node) : Node {
        $children = $node->children;  // Copy of array of Nodes of type IF_ELEM
        if (count($children) <= 2) {
            return $node;
        }
        assert(is_array($children));
        while (count($children) > 2) {
            $r = array_pop($children);
            $l = array_pop($children);
            $innerIfNode = self::buildIfNode($l, $r);
            $newR = new Node();
            $newR->kind = \ast\AST_IF_ELEM;
            $newR->lineno = $l->lineno ?? 0;
            $newR->flags = 0;
            $newR->children = [
                'cond' => null,
                'stmts' => self::buildStatementList($innerIfNode->lineno, ...($this->normalizeIfStatement($innerIfNode))),
            ];

            $children[] = $newR;
        }
        // $children is an array of 2 nodes of type IF_ELEM
        $newNode = clone($node);
        $newNode->children = $children;
        return $newNode;
    }

    private function _applyIfAndReduction(Node $node) : Node {
        assert(count($node->children) == 1);
        $innerNodeElem = clone($node->children[0]);  // AST_IF_ELEM
        $innerNodeElem->children['cond'] = $innerNodeElem->children['cond']->children['right'];
        $innerNode = clone($node);  // AST_IF
        $innerNode->children[0] = $innerNodeElem;
        $innerNode->lineno = $innerNodeElem->lineno ?? 0;
        $innerNodeStmtList = self::buildStatementList($innerNode->lineno, $innerNode);  // AST_STMT_LIST
        $outerNodeElem = clone($node->children[0]);  // AST_IF_ELEM
        $outerNodeElem->children['cond'] = $node->children[0]->children['cond']->children['left'];
        $outerNodeElem->children['stmts'] = $innerNodeStmtList;
        $outerNode = clone($node);  // AST_IF
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
        $newNode->lineno = $newNodeElem->lineno ?? 0;
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

    private function normalizeCatchesList(Node $catches) : Node {
        $list = $catches->children;
        $newList = array_map(function(Node $node) {
            return $this->applyToStmts($node);
        }, $list);
        if ($newList === $list) {
            return $catches;
        }
        $newCatches = clone($catches);
        $newCatches->children = $newList;
        return $newCatches;
    }

    private function normalizeTryStatement(Node $node) : Node {
        $try = $node->children['try'];
        $catches = $node->children['catches'];
        $finally = $node->children['finally'] ?? null;
        $newTry = $this->applyToStatementList($try);
        $newCatches = $catches ? $this->normalizeCatchesList($catches) : $catches;
        $newFinally = $finally ? $this->applyToStatementList($finally) : $finally;
        if ($newTry === $try && $newCatches === $catches && $newFinally === $finally) {
            return $node;
        }
        $newNode = clone($node);
        $newNode->children['try'] = $newTry;
        $newNode->children['catches'] = $newCatches;
        $newNode->children['finally'] = $newFinally;
        return $newNode;
    }

    public static function apply_static(Node $node, string $filename = 'unknown') : Node {
        $rewriter = new self($filename);
        $nodes = $rewriter->_apply($node);
        assert(count($nodes) === 1);
        return $nodes[0];
    }
}
