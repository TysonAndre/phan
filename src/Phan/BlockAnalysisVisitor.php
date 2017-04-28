<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

use Phan\AST\AnalysisVisitor;
use Phan\Analysis\ConditionVisitor;
use Phan\Analysis\ContextMergeVisitor;
use Phan\Analysis\PostOrderAnalysisVisitor;
use Phan\Analysis\PreOrderAnalysisVisitor;
use Phan\Language\Context;
use Phan\Language\Scope\BranchScope;
use Phan\Plugin\ConfigPluginSet;
use ast\Node;
use ast\Node\Decl;
/**
 * Analyze blocks of code
 */
class BlockAnalysisVisitor extends AnalysisVisitor
{
    /**
     * @var ?Node
     * The parent of the current node
     */
    private $parent_node;
    /**
     * @var int
     * The depth of the node being analyzed in the
     * AST
     */
    private $depth;
    /**
     * @var bool
     * Whether or not this visitor will visit all nodes
     */
    private $should_visit_everything;
    /**
     * @param CodeBase $code_base
     * The code base within which we're operating
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param Node $parent_node
     * The parent of the node being analyzed
     *
     * @param int $depth
     * The depth of the node being analyzed in the AST
     *
     * @param bool|null $should_visit_everything
     * Determined from the Config instance. Cached to avoid overhead of function calls.
     */
    public function __construct(CodeBase $code_base, Context $context, Node $parent_node = null, $depth = 0, $should_visit_everything = null)
    {
        if (!is_int($depth)) {
            throw new \InvalidArgumentException("Argument \$depth passed to __construct() must be of the type int, " . (gettype($depth) == "object" ? get_class($depth) : gettype($depth)) . " given");
        }
        if (!is_bool($should_visit_everything) and !is_null($should_visit_everything)) {
            throw new \InvalidArgumentException("Argument \$should_visit_everything passed to __construct() must be of the type bool, " . (gettype($should_visit_everything) == "object" ? get_class($should_visit_everything) : gettype($should_visit_everything)) . " given");
        }
        $should_visit_everything = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$should_visit_everything, @Analysis::shouldVisitEverything());
        parent::__construct($code_base, $context);
        $this->parent_node = $parent_node;
        $this->depth = $depth;
        $this->should_visit_everything = $should_visit_everything;
    }
    /**
     * For non-special nodes, we propagate the context and scope
     * from the parent, through the children and return the
     * modified scope
     *
     *          │
     *          ▼
     *       ┌──●
     *       │
     *       ●──●──●
     *             │
     *          ●──┘
     *          │
     *          ▼
     *
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visit(Node $node)
    {
        $context = $this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0));
        // Visit the given node populating the code base
        // with anything we learn and get a new context
        // indicating the state of the world within the
        // given node
        $visitor = new PreOrderAnalysisVisitor($this->code_base, $context);
        $context = $visitor($node);
        // Let any configured plugins do a pre-order
        // analysis of the node.
        ConfigPluginSet::instance()->preAnalyzeNode($this->code_base, $context, $node);
        assert(!empty($context), 'Context cannot be null');
        // With a context that is inside of the node passed
        // to this method, we analyze all children of the
        // node.
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            // Skip any non Node children or boring nodes
            // that are too deep.
            if (!$child_node instanceof Node || !($this->should_visit_everything || Analysis::shouldVisitNode($child_node))) {
                $context->withLineNumberStart(call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$child_node->lineno, @0));
                continue;
            }
            // Step into each child node and get an
            // updated context for the node
            $child_node_visitor = new BlockAnalysisVisitor($this->code_base, $context, $node, $this->depth + 1);
            $context = $child_node_visitor($child_node);
        }
        $context = $this->postOrderAnalyze($context, $node);
        $ret5902c6f388668 = $context;
        if (!$ret5902c6f388668 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f388668) == "object" ? get_class($ret5902c6f388668) : gettype($ret5902c6f388668)) . " given");
        }
        return $ret5902c6f388668;
    }
    /**
     * For nodes that are the root of mutually exclusive child
     * nodes (if, try), we analyze each child in the parent context
     * and then merge them together to try to guess what happens
     * after the branching finishes.
     *
     *           │
     *           ▼
     *        ┌──●──┐
     *        │  │  │
     *        ●  ●  ●
     *        │  │  │
     *        └──●──┘
     *           │
     *           ▼
     *
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitBranchedContext(Node $node)
    {
        $context = $this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0));
        $context = $this->preOrderAnalyze($context, $node);
        assert(!empty($context), 'Context cannot be null');
        // We collect all child context so that the
        // PostOrderAnalysisVisitor can optionally operate on
        // them
        $child_context_list = [];
        // With a context that is inside of the node passed
        // to this method, we analyze all children of the
        // node.
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $node_key => $child_node) {
            // Skip any non Node children.
            if (!$child_node instanceof Node) {
                continue;
            }
            if (!($this->should_visit_everything || Analysis::shouldVisitNode($child_node))) {
                continue;
            }
            // The conditions need to communicate to the outter
            // scope for things like assigning veriables.
            if ($child_node->kind != \ast\AST_IF_ELEM) {
                $child_context = $context->withScope(new BranchScope($context->getScope()));
            } else {
                $child_context = $context;
            }
            $child_context->withLineNumberStart(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$child_node->lineno, @0));
            // Step into each child node and get an
            // updated context for the node
            $child_node_visitor = new BlockAnalysisVisitor($this->code_base, $child_context, $node, $this->depth + 1);
            $child_context = $child_node_visitor($child_node);
            $child_context_list[] = $child_context;
        }
        // For if statements, we need to merge the contexts
        // of all child context into a single scope based
        // on any possible branching structure
        $context_visitor = new ContextMergeVisitor($this->code_base, $context, $child_context_list);
        $context = $context_visitor($node);
        $context = $this->postOrderAnalyze($context, $node);
        $ret5902c6f388d85 = $context;
        if (!$ret5902c6f388d85 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f388d85) == "object" ? get_class($ret5902c6f388d85) : gettype($ret5902c6f388d85)) . " given");
        }
        return $ret5902c6f388d85;
    }
    /**
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitIfElem(Node $node)
    {
        $context = $this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0));
        $context = $this->preOrderAnalyze($context, $node);
        assert(!empty($context), 'Context cannot be null');
        $condition_node = $node->children['cond'];
        if ($condition_node && $condition_node instanceof Node) {
            $context_visitor = new BlockAnalysisVisitor($this->code_base, $context->withLineNumberStart(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$condition_node->lineno, @0)), $node, $this->depth + 1);
            $context = $context_visitor($condition_node);
        }
        if ($stmts_node = $node->children['stmts']) {
            if ($stmts_node instanceof Node) {
                $context_visitor = new BlockAnalysisVisitor($this->code_base, $context->withScope(new BranchScope($context->getScope()))->withLineNumberStart(call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$stmts_node->lineno, @0)), $node, $this->depth + 1);
                $context = $context_visitor($stmts_node);
            }
        }
        // Now that we know all about our context (like what
        // 'self' means), we can analyze statements like
        // assignments and method calls.
        $context = $this->postOrderAnalyze($context, $node);
        $ret5902c6f38946f = $context;
        if (!$ret5902c6f38946f instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38946f) == "object" ? get_class($ret5902c6f38946f) : gettype($ret5902c6f38946f)) . " given");
        }
        return $ret5902c6f38946f;
    }
    /**
     * For 'closed context' items (classes, methods, functions,
     * closures), we analyze children in the parent context, but
     * then return the parent context itself unmodified by the
     * children.
     *
     *           │
     *           ▼
     *        ┌──●────┐
     *        │       │
     *        ●──●──● │
     *           ┌────┘
     *           ●
     *           │
     *           ▼
     *
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitClosedContext(Node $node)
    {
        // Make a copy of the internal context so that we don't
        // leak any changes within the closed context to the
        // outer scope
        $context = clone $this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0));
        $context = $this->preOrderAnalyze($context, $node);
        assert(!empty($context), 'Context cannot be null');
        // We collect all child context so that the
        // PostOrderAnalysisVisitor can optionally operate on
        // them
        $child_context_list = [];
        $child_context = $context;
        // With a context that is inside of the node passed
        // to this method, we analyze all children of the
        // node.
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            // Skip any non Node children.
            if (!$child_node instanceof Node) {
                continue;
            }
            if (!($this->should_visit_everything || Analysis::shouldVisit($child_node))) {
                $child_context->withLineNumberStart(call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$child_node->lineno, @0));
                continue;
            }
            // Step into each child node and get an
            // updated context for the node
            $child_context_visitor = new BlockAnalysisVisitor($this->code_base, $child_context, $node, $this->depth + 1);
            $child_context = $child_context_visitor($child_node);
            $child_context_list[] = $child_context;
        }
        // For if statements, we need to merge the contexts
        // of all child context into a single scope based
        // on any possible branching structure
        $context_visitor = new ContextMergeVisitor($this->code_base, $context, $child_context_list);
        $context = $context_visitor($node);
        $context = $this->postOrderAnalyze($context, $node);
        $ret5902c6f389b9e = $this->context;
        if (!$ret5902c6f389b9e instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f389b9e) == "object" ? get_class($ret5902c6f389b9e) : gettype($ret5902c6f389b9e)) . " given");
        }
        return $ret5902c6f389b9e;
    }
    /**
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitIf(Node $node)
    {
        $ret5902c6f389ead = $this->visitBranchedContext($node);
        if (!$ret5902c6f389ead instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f389ead) == "object" ? get_class($ret5902c6f389ead) : gettype($ret5902c6f389ead)) . " given");
        }
        return $ret5902c6f389ead;
    }
    /**
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitCatchList(Node $node)
    {
        $ret5902c6f38a1b5 = $this->visitBranchedContext($node);
        if (!$ret5902c6f38a1b5 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38a1b5) == "object" ? get_class($ret5902c6f38a1b5) : gettype($ret5902c6f38a1b5)) . " given");
        }
        return $ret5902c6f38a1b5;
    }
    /**
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitTry(Node $node)
    {
        $ret5902c6f38a4f5 = $this->visitBranchedContext($node);
        if (!$ret5902c6f38a4f5 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38a4f5) == "object" ? get_class($ret5902c6f38a4f5) : gettype($ret5902c6f38a4f5)) . " given");
        }
        return $ret5902c6f38a4f5;
    }
    public function visitConditional(Node $node)
    {
        $context = $this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0));
        // Visit the given node populating the code base
        // with anything we learn and get a new context
        // indicating the state of the world within the
        // given node
        // NOTE: unused for AST_CONDITIONAL
        // $context = (new PreOrderAnalysisVisitor(
        //     $this->code_base, $context
        // ))($node);
        // Let any configured plugins do a pre-order
        // analysis of the node.
        ConfigPluginSet::instance()->preAnalyzeNode($this->code_base, $context, $node);
        assert(!empty($context), 'Context cannot be null');
        $true_node = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['trueExpr'], @call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['true'], @null));
        $false_node = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['falseExpr'], @call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['false'], @null));
        $cond_node = $node->children['cond'];
        if ($cond_node instanceof Node && ($this->should_visit_everything || Analysis::shouldVisitNode($cond_node))) {
            // Step into each child node and get an
            // updated context for the node
            // (e.g. there may be assignments such as '($x = foo()) ? $a : $b)
            $context_visitor = new BlockAnalysisVisitor($this->code_base, $context, $node, $this->depth + 1);
            $context = $context_visitor($cond_node);
            // TODO: false_context once there is a NegatedConditionVisitor
            $context_visitor = new ConditionVisitor($this->code_base, $this->context);
            $true_context = $context_visitor($cond_node);
        } else {
            $true_context = $context;
        }
        $child_context_list = [];
        // In the long form, there's a $true_node, but in the short form (?:),
        // $cond_node is the (already processed) value for truthy.
        if ($true_node instanceof Node) {
            if ($this->should_visit_everything || Analysis::shouldVisit($true_node)) {
                $child_context_visitor = new BlockAnalysisVisitor($this->code_base, $true_context, $node, $this->depth + 1);
                $child_context = $child_context_visitor($true_node);
                $child_context_list[] = $child_context;
            }
        }
        if ($false_node instanceof Node) {
            if ($this->should_visit_everything || Analysis::shouldVisit($false_node)) {
                $child_context_visitor = new BlockAnalysisVisitor($this->code_base, $context, $node, $this->depth + 1);
                $child_context = $child_context_visitor($false_node);
                $child_context_list[] = $child_context;
            }
        }
        if (count($child_context_list) >= 1) {
            $child_context_visitor = new ContextMergeVisitor($this->code_base, $context, $child_context_list);
            $context = $child_context_visitor($node);
        }
        $context = $this->postOrderAnalyze($context, $node);
        $ret5902c6f38af1d = $context;
        if (!$ret5902c6f38af1d instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38af1d) == "object" ? get_class($ret5902c6f38af1d) : gettype($ret5902c6f38af1d)) . " given");
        }
        return $ret5902c6f38af1d;
    }
    /**
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitClass(Decl $node)
    {
        $ret5902c6f38b22e = $this->visitClosedContext($node);
        if (!$ret5902c6f38b22e instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38b22e) == "object" ? get_class($ret5902c6f38b22e) : gettype($ret5902c6f38b22e)) . " given");
        }
        return $ret5902c6f38b22e;
    }
    /**
     * @param Decl $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitMethod(Decl $node)
    {
        $ret5902c6f38b596 = $this->visitClosedContext($node);
        if (!$ret5902c6f38b596 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38b596) == "object" ? get_class($ret5902c6f38b596) : gettype($ret5902c6f38b596)) . " given");
        }
        return $ret5902c6f38b596;
    }
    /**
     * @param Decl $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitFuncDecl(Decl $node)
    {
        $ret5902c6f38b8bb = $this->visitClosedContext($node);
        if (!$ret5902c6f38b8bb instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38b8bb) == "object" ? get_class($ret5902c6f38b8bb) : gettype($ret5902c6f38b8bb)) . " given");
        }
        return $ret5902c6f38b8bb;
    }
    /**
     * @param Decl $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after visiting the node
     */
    public function visitClosure(Decl $node)
    {
        $ret5902c6f38bbc1 = $this->visitClosedContext($node);
        if (!$ret5902c6f38bbc1 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38bbc1) == "object" ? get_class($ret5902c6f38bbc1) : gettype($ret5902c6f38bbc1)) . " given");
        }
        return $ret5902c6f38bbc1;
    }
    /**
     * Common options for pre-order analysis phase of a Node.
     * Run pre-order analysis steps, then run plugins.
     *
     * @param Context $context - The context before pre-order analysis
     *
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after pre-order analysis of the node
     */
    private function preOrderAnalyze(Context $context, Node $node)
    {
        // Visit the given node populating the code base
        // with anything we learn and get a new context
        // indicating the state of the world within the
        // given node
        $context_visitor = new PreOrderAnalysisVisitor($this->code_base, $context);
        $context = $context_visitor($node);
        // Let any configured plugins do a pre-order
        // analysis of the node.
        ConfigPluginSet::instance()->preAnalyzeNode($this->code_base, $context, $node);
        $ret5902c6f38bf82 = $context;
        if (!$ret5902c6f38bf82 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38bf82) == "object" ? get_class($ret5902c6f38bf82) : gettype($ret5902c6f38bf82)) . " given");
        }
        return $ret5902c6f38bf82;
    }
    /**
     * Common options for post-order analysis phase of a Node.
     * Run analysis steps and run plugins.
     *
     * @param Context $context - The context before post-order analysis
     *
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return Context
     * The updated context after post-order analysis of the node
     */
    private function postOrderAnalyze(Context $context, Node $node)
    {
        // Now that we know all about our context (like what
        // 'self' means), we can analyze statements like
        // assignments and method calls.
        $context_visitor = new PostOrderAnalysisVisitor($this->code_base, $context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0)), $this->parent_node);
        $context = $context_visitor($node);
        // let any configured plugins analyze the node
        ConfigPluginSet::instance()->analyzeNode($this->code_base, $context, $node, $this->parent_node);
        $ret5902c6f38c3c9 = $context;
        if (!$ret5902c6f38c3c9 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f38c3c9) == "object" ? get_class($ret5902c6f38c3c9) : gettype($ret5902c6f38c3c9)) . " given");
        }
        return $ret5902c6f38c3c9;
    }
}