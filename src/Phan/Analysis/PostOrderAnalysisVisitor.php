<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\AST\AnalysisVisitor;
use Phan\AST\ContextNode;
use Phan\CodeBase;
use Phan\Config;
use Phan\Exception\CodeBaseException;
use Phan\Exception\IssueException;
use Phan\Exception\NodeException;
use Phan\Exception\UnanalyzableException;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\Element\Func;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Element\Method;
use Phan\Language\Element\Parameter;
use Phan\Language\Element\PassByReferenceVariable;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\CallableType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\VoidType;
use Phan\Language\UnionType;
use ast\Node;
use ast\Node\Decl;
class PostOrderAnalysisVisitor extends AnalysisVisitor
{
    /**
     * @var Node|null
     */
    private $parent_node;
    /**
     * @param CodeBase $code_base
     * A code base needs to be passed in because we require
     * it to be initialized before any classes or files are
     * loaded.
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param Node|null $parent_node
     * The parent node of the node being analyzed
     */
    public function __construct(CodeBase $code_base, Context $context, Node $parent_node = null)
    {
        parent::__construct($code_base, $context);
        $this->parent_node = $parent_node;
    }
    /**
     * Default visitor for node kinds that do not have
     * an overriding method
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visit(Node $node)
    {
        $ret5902c6f26d597 = $this->context;
        if (!$ret5902c6f26d597 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26d597) == "object" ? get_class($ret5902c6f26d597) : gettype($ret5902c6f26d597)) . " given");
        }
        return $ret5902c6f26d597;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitAssign(Node $node)
    {
        // Get the type of the right side of the
        // assignment
        $right_type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        assert($node->children['var'] instanceof Node, "Expected left side of assignment to be a var");
        if ($right_type->isType(VoidType::instance(false))) {
            $this->emitIssue(Issue::TypeVoidAssignment, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0));
        }
        // Handle the assignment based on the type of the
        // right side of the equation and the kind of item
        // on the left
        $visitor = new AssignmentVisitor($this->code_base, $this->context, $node, $right_type);
        $context = $visitor($node->children['var']);
        // Analyze the assignment for compatibility with some
        // breaking changes betweeen PHP5 and PHP7.
        (new ContextNode($this->code_base, $this->context, $node->children['var']))->analyzeBackwardCompatibility();
        (new ContextNode($this->code_base, $this->context, $node->children['expr']))->analyzeBackwardCompatibility();
        if ($node->children['expr'] instanceof Node && $node->children['expr']->kind == \ast\AST_CLOSURE) {
            $closure_node = $node->children['expr'];
            $method = (new ContextNode($this->code_base, $this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$closure_node->lineno, @0)), $closure_node))->getClosure();
            $method->addReference($this->context);
        }
        $ret5902c6f26dfc6 = $context;
        if (!$ret5902c6f26dfc6 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26dfc6) == "object" ? get_class($ret5902c6f26dfc6) : gettype($ret5902c6f26dfc6)) . " given");
        }
        return $ret5902c6f26dfc6;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitAssignRef(Node $node)
    {
        $ret5902c6f26e348 = $this->visitAssign($node);
        if (!$ret5902c6f26e348 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26e348) == "object" ? get_class($ret5902c6f26e348) : gettype($ret5902c6f26e348)) . " given");
        }
        return $ret5902c6f26e348;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitIfElem(Node $node)
    {
        $ret5902c6f26e69b = $this->context;
        if (!$ret5902c6f26e69b instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26e69b) == "object" ? get_class($ret5902c6f26e69b) : gettype($ret5902c6f26e69b)) . " given");
        }
        return $ret5902c6f26e69b;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitWhile(Node $node)
    {
        $ret5902c6f26e9e9 = $this->visitIfElem($node);
        if (!$ret5902c6f26e9e9 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26e9e9) == "object" ? get_class($ret5902c6f26e9e9) : gettype($ret5902c6f26e9e9)) . " given");
        }
        return $ret5902c6f26e9e9;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitSwitch(Node $node)
    {
        $ret5902c6f26ed39 = $this->visitIfElem($node);
        if (!$ret5902c6f26ed39 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26ed39) == "object" ? get_class($ret5902c6f26ed39) : gettype($ret5902c6f26ed39)) . " given");
        }
        return $ret5902c6f26ed39;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitSwitchCase(Node $node)
    {
        $ret5902c6f26f092 = $this->visitIfElem($node);
        if (!$ret5902c6f26f092 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26f092) == "object" ? get_class($ret5902c6f26f092) : gettype($ret5902c6f26f092)) . " given");
        }
        return $ret5902c6f26f092;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitExprList(Node $node)
    {
        $ret5902c6f26f3de = $this->visitIfElem($node);
        if (!$ret5902c6f26f3de instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26f3de) == "object" ? get_class($ret5902c6f26f3de) : gettype($ret5902c6f26f3de)) . " given");
        }
        return $ret5902c6f26f3de;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitEncapsList(Node $node)
    {
        foreach ((array) $node->children as $child_node) {
            // Confirm that variables exists
            if ($child_node instanceof Node && $child_node->kind == \ast\AST_VAR) {
                $variable_name = $child_node->children['name'];
                // Ignore $$var type things
                if (!is_string($variable_name)) {
                    continue;
                }
                // Don't worry about non-existent undeclared variables
                // in the global scope if configured to do so
                if (Config::get()->ignore_undeclared_variables_in_global_scope && $this->context->isInGlobalScope()) {
                    continue;
                }
                if (!$this->context->getScope()->hasVariableWithName($variable_name) && !Variable::isSuperglobalVariableWithName($variable_name)) {
                    $this->emitIssue(Issue::UndeclaredVariable, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$child_node->lineno, @0), $variable_name);
                }
            }
        }
        $ret5902c6f26f95b = $this->context;
        if (!$ret5902c6f26f95b instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26f95b) == "object" ? get_class($ret5902c6f26f95b) : gettype($ret5902c6f26f95b)) . " given");
        }
        return $ret5902c6f26f95b;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitDoWhile(Node $node)
    {
        $ret5902c6f26fc91 = $this->context;
        if (!$ret5902c6f26fc91 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f26fc91) == "object" ? get_class($ret5902c6f26fc91) : gettype($ret5902c6f26fc91)) . " given");
        }
        return $ret5902c6f26fc91;
    }
    /**
     * Visit a node with kind `\ast\AST_GLOBAL`
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitGlobal(Node $node)
    {
        $variable = Variable::fromNodeInContext($node->children['var'], $this->context, $this->code_base, false);
        // Note that we're not creating a new scope, just
        // adding variables to the existing scope
        $this->context->addScopeVariable($variable);
        $ret5902c6f270092 = $this->context;
        if (!$ret5902c6f270092 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f270092) == "object" ? get_class($ret5902c6f270092) : gettype($ret5902c6f270092)) . " given");
        }
        return $ret5902c6f270092;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitForeach(Node $node)
    {
        $expression_type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        // Check the expression type to make sure its
        // something we can iterate over
        if ($expression_type->isScalar()) {
            $this->emitIssue(Issue::TypeMismatchForeach, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $expression_type);
        }
        $ret5902c6f2704ee = $this->context;
        if (!$ret5902c6f2704ee instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2704ee) == "object" ? get_class($ret5902c6f2704ee) : gettype($ret5902c6f2704ee)) . " given");
        }
        return $ret5902c6f2704ee;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitStatic(Node $node)
    {
        $variable = Variable::fromNodeInContext($node->children['var'], $this->context, $this->code_base, false);
        // If the element has a default, set its type
        // on the variable
        if (isset($node->children['default'])) {
            $default_type = UnionType::fromNode($this->context, $this->code_base, $node->children['default']);
            $variable->setUnionType($default_type);
        }
        // Note that we're not creating a new scope, just
        // adding variables to the existing scope
        $this->context->addScopeVariable($variable);
        $ret5902c6f2709a9 = $this->context;
        if (!$ret5902c6f2709a9 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2709a9) == "object" ? get_class($ret5902c6f2709a9) : gettype($ret5902c6f2709a9)) . " given");
        }
        return $ret5902c6f2709a9;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitEcho(Node $node)
    {
        $ret5902c6f270ccc = $this->visitPrint($node);
        if (!$ret5902c6f270ccc instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f270ccc) == "object" ? get_class($ret5902c6f270ccc) : gettype($ret5902c6f270ccc)) . " given");
        }
        return $ret5902c6f270ccc;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitPrint(Node $node)
    {
        $type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        if ($type->isType(ArrayType::instance(false)) || $type->isType(ArrayType::instance(true)) || $type->isGenericArray()) {
            $this->emitIssue(Issue::TypeConversionFromArray, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), 'string');
        }
        $ret5902c6f27119e = $this->context;
        if (!$ret5902c6f27119e instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27119e) == "object" ? get_class($ret5902c6f27119e) : gettype($ret5902c6f27119e)) . " given");
        }
        return $ret5902c6f27119e;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitVar(Node $node)
    {
        $this->analyzeNoOp($node, Issue::NoopVariable);
        $ret5902c6f2714eb = $this->context;
        if (!$ret5902c6f2714eb instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2714eb) == "object" ? get_class($ret5902c6f2714eb) : gettype($ret5902c6f2714eb)) . " given");
        }
        return $ret5902c6f2714eb;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitArray(Node $node)
    {
        $this->analyzeNoOp($node, Issue::NoopArray);
        $ret5902c6f271846 = $this->context;
        if (!$ret5902c6f271846 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f271846) == "object" ? get_class($ret5902c6f271846) : gettype($ret5902c6f271846)) . " given");
        }
        return $ret5902c6f271846;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitConst(Node $node)
    {
        try {
            $nameNode = $node->children['name'];
            // Based on UnionTypeVisitor::visitConst
            if ($nameNode->kind == \ast\AST_NAME) {
                if (defined($nameNode->children['name'])) {
                    // Do nothing, this is an internal type such as `true` or `\ast\AST_NAME`
                } else {
                    $constant = (new ContextNode($this->code_base, $this->context, $node))->getConst();
                    // Mark that this constant has been referenced from
                    // this context
                    $constant->addReference($this->context);
                }
            }
        } catch (IssueException $exception) {
            // We need to do this in order to check keys and (after the first 5) values in AST arrays.
            // Other parts of the AST may also not be covered.
            // (This issue may be a duplicate)
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
        } catch (\Exception $exception) {
            // Swallow any other types of exceptions. We'll log the errors
            // elsewhere.
        }
        // Check to make sure we're doing something with the
        // constant
        $this->analyzeNoOp($node, Issue::NoopConstant);
        $ret5902c6f271d74 = $this->context;
        if (!$ret5902c6f271d74 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f271d74) == "object" ? get_class($ret5902c6f271d74) : gettype($ret5902c6f271d74)) . " given");
        }
        return $ret5902c6f271d74;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitClassConst(Node $node)
    {
        try {
            $constant = (new ContextNode($this->code_base, $this->context, $node))->getClassConst();
            // Mark that this class constant has been referenced
            // from this context
            $constant->addReference($this->context);
        } catch (IssueException $exception) {
            // We need to do this in order to check keys and (after the first 5) values in AST arrays, possibly other types.
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
        } catch (\Exception $exception) {
            // Swallow any other types of exceptions. We'll log the errors
            // elsewhere.
        }
        // Check to make sure we're doing something with the
        // class constant
        $this->analyzeNoOp($node, Issue::NoopConstant);
        $ret5902c6f27223f = $this->context;
        if (!$ret5902c6f27223f instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27223f) == "object" ? get_class($ret5902c6f27223f) : gettype($ret5902c6f27223f)) . " given");
        }
        return $ret5902c6f27223f;
    }
    /**
     * @param Decl $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitClosure(Decl $node)
    {
        $this->analyzeNoOp($node, Issue::NoopClosure);
        $ret5902c6f272578 = $this->context;
        if (!$ret5902c6f272578 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f272578) == "object" ? get_class($ret5902c6f272578) : gettype($ret5902c6f272578)) . " given");
        }
        return $ret5902c6f272578;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitReturn(Node $node)
    {
        // Don't check return types in traits
        if ($this->context->isInClassScope()) {
            $clazz = $this->context->getClassInScope($this->code_base);
            if ($clazz->isTrait()) {
                $ret5902c6f2728e5 = $this->context;
                if (!$ret5902c6f2728e5 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2728e5) == "object" ? get_class($ret5902c6f2728e5) : gettype($ret5902c6f2728e5)) . " given");
                }
                return $ret5902c6f2728e5;
            }
        }
        // Make sure we're actually returning from a method.
        if (!$this->context->isInFunctionLikeScope()) {
            $ret5902c6f272bda = $this->context;
            if (!$ret5902c6f272bda instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f272bda) == "object" ? get_class($ret5902c6f272bda) : gettype($ret5902c6f272bda)) . " given");
            }
            return $ret5902c6f272bda;
        }
        // Get the method/function/closure we're in
        $method = $this->context->getFunctionLikeInScope($this->code_base);
        assert(!empty($method), "We're supposed to be in either method or closure scope.");
        // Figure out what we intend to return
        $method_return_type = $method->getUnionType();
        // Figure out what is actually being returned
        $expression_type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        if (null === $node->children['expr']) {
            $expression_type = VoidType::instance(false)->asUnionType();
        }
        if ($expression_type->hasStaticType()) {
            $expression_type = $expression_type->withStaticResolvedInContext($this->context);
        }
        if ($method->getHasYield()) {
            $ret5902c6f2730ff = $this->context;
            if (!$ret5902c6f2730ff instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2730ff) == "object" ? get_class($ret5902c6f2730ff) : gettype($ret5902c6f2730ff)) . " given");
            }
            return $ret5902c6f2730ff;
            // Analysis was completed in PreOrderAnalysisVisitor
        }
        // This leaves functions which aren't syntactically generators.
        // If there is no declared type, see if we can deduce
        // what it should be based on the return type
        if ($method_return_type->isEmpty() || $method->isReturnTypeUndefined()) {
            $method->setIsReturnTypeUndefined(true);
            // Set the inferred type of the method based
            // on what we're returning
            $method->getUnionType()->addUnionType($expression_type);
            $ret5902c6f273462 = $this->context;
            if (!$ret5902c6f273462 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f273462) == "object" ? get_class($ret5902c6f273462) : gettype($ret5902c6f273462)) . " given");
            }
            return $ret5902c6f273462;
        }
        // C
        if (!$method->isReturnTypeUndefined() && !$expression_type->canCastToExpandedUnionType($method_return_type, $this->code_base)) {
            $this->emitIssue(Issue::TypeMismatchReturn, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $expression_type, $method->getName(), (string) $method_return_type);
        }
        // For functions that aren't syntactically Generators,
        // update the set/existence of return values.
        if ($method->isReturnTypeUndefined()) {
            // Add the new type to the set of values returned by the
            // method
            $method->getUnionType()->addUnionType($expression_type);
        }
        // Mark the method as returning something (even if void)
        $method->setHasReturn(true);
        $ret5902c6f2738a5 = $this->context;
        if (!$ret5902c6f2738a5 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2738a5) == "object" ? get_class($ret5902c6f2738a5) : gettype($ret5902c6f2738a5)) . " given");
        }
        return $ret5902c6f2738a5;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitPropDecl(Node $node)
    {
        $ret5902c6f273b8e = $this->context;
        if (!$ret5902c6f273b8e instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f273b8e) == "object" ? get_class($ret5902c6f273b8e) : gettype($ret5902c6f273b8e)) . " given");
        }
        return $ret5902c6f273b8e;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitCall(Node $node)
    {
        $expression = $node->children['expr'];
        (new ContextNode($this->code_base, $this->context, $node))->analyzeBackwardCompatibility();
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['args']->children, @[]) as $arg_node) {
            if ($arg_node instanceof Node) {
                (new ContextNode($this->code_base, $this->context, $arg_node))->analyzeBackwardCompatibility();
            }
        }
        if ($expression->kind == \ast\AST_VAR) {
            $variable_name = (new ContextNode($this->code_base, $this->context, $expression))->getVariableName();
            if (empty($variable_name)) {
                $ret5902c6f2740b6 = $this->context;
                if (!$ret5902c6f2740b6 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2740b6) == "object" ? get_class($ret5902c6f2740b6) : gettype($ret5902c6f2740b6)) . " given");
                }
                return $ret5902c6f2740b6;
            }
            // $var() - hopefully a closure, otherwise we don't know
            if ($this->context->getScope()->hasVariableWithName($variable_name)) {
                $variable = $this->context->getScope()->getVariableByName($variable_name);
                $union_type = $variable->getUnionType();
                if ($union_type->isEmpty()) {
                    $ret5902c6f274445 = $this->context;
                    if (!$ret5902c6f274445 instanceof Context) {
                        throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f274445) == "object" ? get_class($ret5902c6f274445) : gettype($ret5902c6f274445)) . " given");
                    }
                    return $ret5902c6f274445;
                }
                foreach ($union_type->getTypeSet() as $type) {
                    if (!$type instanceof CallableType) {
                        continue;
                    }
                    $closure_fqsen = FullyQualifiedFunctionName::fromFullyQualifiedString((string) $type->asFQSEN());
                    if ($this->code_base->hasFunctionWithFQSEN($closure_fqsen)) {
                        // Get the closure
                        $function = $this->code_base->getFunctionByFQSEN($closure_fqsen);
                        // Check the call for paraemter and argument types
                        $this->analyzeCallToMethod($this->code_base, $function, $node);
                    }
                }
            }
        } elseif ($expression->kind == \ast\AST_NAME) {
            try {
                $method = (new ContextNode($this->code_base, $this->context, $expression))->getFunction(call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$expression->children['name'], @$expression->children['method']));
            } catch (IssueException $exception) {
                Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                $ret5902c6f2749c5 = $this->context;
                if (!$ret5902c6f2749c5 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2749c5) == "object" ? get_class($ret5902c6f2749c5) : gettype($ret5902c6f2749c5)) . " given");
                }
                return $ret5902c6f2749c5;
            }
            // Check the call for paraemter and argument types
            $this->analyzeCallToMethod($this->code_base, $method, $node);
        } elseif ($expression->kind == \ast\AST_CALL || $expression->kind == \ast\AST_STATIC_CALL || $expression->kind == \ast\AST_NEW || $expression->kind == \ast\AST_METHOD_CALL) {
            $class_list = (new ContextNode($this->code_base, $this->context, $expression))->getClassList();
            foreach ($class_list as $class) {
                if (!$class->hasMethodWithName($this->code_base, '__invoke')) {
                    continue;
                }
                $method = $class->getMethodByNameInContext($this->code_base, '__invoke', $this->context);
                // Check the call for paraemter and argument types
                $this->analyzeCallToMethod($this->code_base, $method, $node);
            }
        }
        $ret5902c6f274f7b = $this->context;
        if (!$ret5902c6f274f7b instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f274f7b) == "object" ? get_class($ret5902c6f274f7b) : gettype($ret5902c6f274f7b)) . " given");
        }
        return $ret5902c6f274f7b;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitNew(Node $node)
    {
        try {
            $context_node = new ContextNode($this->code_base, $this->context, $node);
            $method = $context_node->getMethod('__construct', false);
            // Add a reference to each class this method
            // could be called on
            foreach ($context_node->getClassList() as $class) {
                $class->addReference($this->context);
                if ($class->isDeprecated()) {
                    $this->emitIssue(Issue::DeprecatedClass, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$node->lineno, @0), (string) $class->getFQSEN(), $class->getContext()->getFile(), (string) $class->getContext()->getLineNumberStart());
                }
                foreach ($class->getInterfaceFQSENList() as $interface) {
                    $clazz = $this->code_base->getClassByFQSEN($interface);
                    if ($clazz->isDeprecated()) {
                        $this->emitIssue(Issue::DeprecatedInterface, call_user_func(function ($v1, $v2) {
                            return isset($v1) ? $v1 : $v2;
                        }, @$node->lineno, @0), (string) $clazz->getFQSEN(), $clazz->getContext()->getFile(), (string) $clazz->getContext()->getLineNumberStart());
                    }
                }
                foreach ($class->getTraitFQSENList() as $trait) {
                    $clazz = $this->code_base->getClassByFQSEN($trait);
                    if ($clazz->isDeprecated()) {
                        $this->emitIssue(Issue::DeprecatedTrait, call_user_func(function ($v1, $v2) {
                            return isset($v1) ? $v1 : $v2;
                        }, @$node->lineno, @0), (string) $clazz->getFQSEN(), $clazz->getContext()->getFile(), (string) $clazz->getContext()->getLineNumberStart());
                    }
                }
            }
            $this->analyzeCallToMethod($this->code_base, $method, $node);
            $class_list = $context_node->getClassList();
            foreach ($class_list as $class) {
                // Make sure we're not instantiating an abstract
                // class
                if ($class->isAbstract() && (!$this->context->isInClassScope() || $class->getFQSEN() != $this->context->getClassFQSEN())) {
                    $this->emitIssue(Issue::TypeInstantiateAbstract, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$node->lineno, @0), (string) $class->getFQSEN());
                }
                // Make sure we're not instantiating an interface
                if ($class->isInterface()) {
                    $this->emitIssue(Issue::TypeInstantiateInterface, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$node->lineno, @0), (string) $class->getFQSEN());
                }
            }
        } catch (IssueException $exception) {
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
        } catch (\Exception $exception) {
            $ret5902c6f27595d = $this->context;
            if (!$ret5902c6f27595d instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27595d) == "object" ? get_class($ret5902c6f27595d) : gettype($ret5902c6f27595d)) . " given");
            }
            return $ret5902c6f27595d;
        }
        $ret5902c6f275c35 = $this->context;
        if (!$ret5902c6f275c35 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f275c35) == "object" ? get_class($ret5902c6f275c35) : gettype($ret5902c6f275c35)) . " given");
        }
        return $ret5902c6f275c35;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitInstanceof(Node $node)
    {
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, $node->children['class']))->getClassList();
        } catch (CodeBaseException $exception) {
            $this->emitIssue(Issue::UndeclaredClassInstanceof, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $exception->getFQSEN());
        }
        $ret5902c6f276050 = $this->context;
        if (!$ret5902c6f276050 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f276050) == "object" ? get_class($ret5902c6f276050) : gettype($ret5902c6f276050)) . " given");
        }
        return $ret5902c6f276050;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitStaticCall(Node $node)
    {
        // Get the name of the method being called
        $method_name = $node->children['method'];
        // Give up on things like Class::$var
        if (!is_string($method_name)) {
            $ret5902c6f276399 = $this->context;
            if (!$ret5902c6f276399 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f276399) == "object" ? get_class($ret5902c6f276399) : gettype($ret5902c6f276399)) . " given");
            }
            return $ret5902c6f276399;
        }
        // Get the name of the static class being referenced
        $static_class = '';
        if ($node->children['class']->kind == \ast\AST_NAME) {
            $static_class = $node->children['class']->children['name'];
        }
        $method = $this->getStaticMethodOrEmitIssue($node);
        if ($method === null) {
            // Short circuit on a constructor being called statically
            // on something other than 'parent'
            if ($method_name === '__construct' && $static_class !== 'parent') {
                $this->emitConstructorWarning($node, $static_class, $method_name);
            }
            $ret5902c6f2767dd = $this->context;
            if (!$ret5902c6f2767dd instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2767dd) == "object" ? get_class($ret5902c6f2767dd) : gettype($ret5902c6f2767dd)) . " given");
            }
            return $ret5902c6f2767dd;
        }
        try {
            if ($method_name === '__construct') {
                $this->checkNonAncestorConstructCall($node, $static_class, $method_name);
                // Even if it exists, continue on and type check the arguments passed.
            }
            // Get the method thats calling the static method
            $calling_method = null;
            if ($this->context->isInMethodScope()) {
                $calling_function_like = $this->context->getFunctionLikeInScope($this->code_base);
                if ($calling_function_like instanceof Method) {
                    $calling_method = $calling_function_like;
                }
            }
            // If the method being called isn't actually static and it's
            // not a call to parent::f from f, we may be in trouble.
            if (!$method->isStatic() && !(('parent' === $static_class || 'self' === $static_class || 'static' === $static_class) && $this->context->isInMethodScope() && ($this->context->getFunctionLikeFQSEN()->getName() == $method->getFQSEN()->getName() || $calling_method && !$calling_method->isStatic())) && !($this->context->isInClassScope() && $this->context->isInFunctionLikeScope() && ($calling_method && !$calling_method->isStatic())) && !($this->context->isInClassScope() && $this->context->isInFunctionLikeScope() && $this->context->getFunctionLikeFQSEN()->isClosure())) {
                $class_list = (new ContextNode($this->code_base, $this->context, $node->children['class']))->getClassList();
                if (!empty($class_list)) {
                    $class = array_values($class_list)[0];
                    $this->emitIssue(Issue::StaticCallToNonStatic, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$node->lineno, @0), "{$class->getFQSEN()}::{$method_name}()", $method->getFileRef()->getFile(), (string) $method->getFileRef()->getLineNumberStart());
                }
            }
            // Make sure the parameters look good
            $this->analyzeCallToMethod($this->code_base, $method, $node);
        } catch (IssueException $exception) {
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
        } catch (\Exception $exception) {
            // If we can't figure out the class for this method
            // call, cry YOLO and mark every method with that
            // name with a reference.
            if (Config::get()->dead_code_detection && Config::get()->dead_code_detection_prefer_false_negative) {
                foreach ($this->code_base->getMethodSetByName($method_name) as $method) {
                    $method->addReference($this->context);
                }
            }
            $ret5902c6f277134 = $this->context;
            if (!$ret5902c6f277134 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f277134) == "object" ? get_class($ret5902c6f277134) : gettype($ret5902c6f277134)) . " given");
            }
            return $ret5902c6f277134;
        }
        $ret5902c6f2773fc = $this->context;
        if (!$ret5902c6f2773fc instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2773fc) == "object" ? get_class($ret5902c6f2773fc) : gettype($ret5902c6f2773fc)) . " given");
        }
        return $ret5902c6f2773fc;
    }
    /**
     * Check calling A::__construct (where A is not parent)
     * @return void
     */
    private function checkNonAncestorConstructCall(Node $node, $static_class, $method_name)
    {
        if (!is_string($static_class)) {
            throw new \InvalidArgumentException("Argument \$static_class passed to checkNonAncestorConstructCall() must be of the type string, " . (gettype($static_class) == "object" ? get_class($static_class) : gettype($static_class)) . " given");
        }
        if (!is_string($method_name)) {
            throw new \InvalidArgumentException("Argument \$method_name passed to checkNonAncestorConstructCall() must be of the type string, " . (gettype($method_name) == "object" ? get_class($method_name) : gettype($method_name)) . " given");
        }
        if ($static_class === 'parent') {
            return;
        }
        // TODO: what about unanalyzable?
        if ($node->children['class']->kind != \ast\AST_NAME) {
            return;
        }
        $class_context_node = new ContextNode($this->code_base, $this->context, $node->children['class']);
        // TODO: check for self/static/<class name of self> and warn about recursion?
        // TODO: Only allow calls to __construct from other constructors?
        $found_ancestor_constructor = false;
        if ($this->context->isInMethodScope()) {
            $possible_ancestor_type = $class_context_node->getClassUnionType();
            // If we can determine the ancestor type, and it's an parent/ancestor class, allow the call without warning.
            // (other code should check visibility and existence and args of __construct)
            if (!$possible_ancestor_type->isEmpty()) {
                // but forbid 'self::__construct', 'static::__construct'
                $type = $this->context->getClassFQSEN()->asUnionType();
                if ($type->asExpandedTypes($this->code_base)->canCastToUnionType($possible_ancestor_type) && !$type->canCastToUnionType($possible_ancestor_type)) {
                    $found_ancestor_constructor = true;
                }
            }
        }
        if (!$found_ancestor_constructor) {
            // TODO: new issue type?
            $this->emitConstructorWarning($node, $static_class, $method_name);
        }
    }
    /**
     * TODO: change to a different issue type in a future phan release?
     * @return void
     */
    private function emitConstructorWarning(Node $node, $static_class, $method_name)
    {
        if (!is_string($static_class)) {
            throw new \InvalidArgumentException("Argument \$static_class passed to emitConstructorWarning() must be of the type string, " . (gettype($static_class) == "object" ? get_class($static_class) : gettype($static_class)) . " given");
        }
        if (!is_string($method_name)) {
            throw new \InvalidArgumentException("Argument \$method_name passed to emitConstructorWarning() must be of the type string, " . (gettype($method_name) == "object" ? get_class($method_name) : gettype($method_name)) . " given");
        }
        $this->emitIssue(Issue::UndeclaredStaticMethod, call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0), "{$static_class}::{$method_name}()");
    }
    /**
     * gets the static method, or emits an issue.
     * @return Method|null
     */
    private function getStaticMethodOrEmitIssue(Node $node)
    {
        $method_name = $node->children['method'];
        try {
            // Get a reference to the method being called
            return (new ContextNode($this->code_base, $this->context, $node))->getMethod($method_name, true);
        } catch (IssueException $exception) {
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
        } catch (\Exception $exception) {
            // If we can't figure out the class for this method
            // call, cry YOLO and mark every method with that
            // name with a reference.
            if (Config::get()->dead_code_detection && Config::get()->dead_code_detection_prefer_false_negative) {
                foreach ($this->code_base->getMethodSetByName($method_name) as $method) {
                    $method->addReference($this->context);
                }
            }
            // If we can't figure out what kind of a call
            // this is, don't worry about it
        }
    }
    /**
     * @param Decl $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitMethod(Decl $node)
    {
        assert($this->context->isInFunctionLikeScope(), "Must be in function-like scope to get method");
        $method = $this->context->getFunctionLikeInScope($this->code_base);
        $return_type = $method->getUnionType();
        assert($method instanceof Method, "Function found where method expected");
        $has_interface_class = false;
        if ($method instanceof Method) {
            try {
                $class = $method->getClass($this->code_base);
                $has_interface_class = $class->isInterface();
            } catch (\Exception $exception) {
            }
            if (!$method->isAbstract() && !$has_interface_class && !$return_type->isEmpty() && !$method->getHasReturn() && !$this->declOnlyThrows($node) && !$return_type->hasType(VoidType::instance(false)) && !$return_type->hasType(NullType::instance(false))) {
                $this->emitIssue(Issue::TypeMissingReturn, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), (string) $method->getFQSEN(), (string) $return_type);
            }
            if ($method->isStatic() && $method->getUnionType()->hasTemplateType()) {
                $this->emitIssue(Issue::TemplateTypeStaticMethod, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), (string) $method->getFQSEN());
            }
        }
        $parameters_seen = [];
        foreach ($method->getParameterList() as $i => $parameter) {
            if (isset($parameters_seen[$parameter->getName()])) {
                $this->emitIssue(Issue::ParamRedefined, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), '$' . $parameter->getName());
            } else {
                $parameters_seen[$parameter->getName()] = $i;
            }
        }
        $ret5902c6f278b8c = $this->context;
        if (!$ret5902c6f278b8c instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f278b8c) == "object" ? get_class($ret5902c6f278b8c) : gettype($ret5902c6f278b8c)) . " given");
        }
        return $ret5902c6f278b8c;
    }
    /**
     * Visit a node with kind `\ast\AST_FUNC_DECL`
     *
     * @param Decl $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitFuncDecl(Decl $node)
    {
        $method = $this->context->getFunctionLikeInScope($this->code_base);
        $return_type = $method->getUnionType();
        if (!$return_type->isEmpty() && !$method->getHasReturn() && !$this->declOnlyThrows($node) && !$return_type->hasType(VoidType::instance(false)) && !$return_type->hasType(NullType::instance(false))) {
            $this->emitIssue(Issue::TypeMissingReturn, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $method->getFQSEN(), (string) $return_type);
        }
        $parameters_seen = [];
        foreach ($method->getParameterList() as $i => $parameter) {
            if (isset($parameters_seen[$parameter->getName()])) {
                $this->emitIssue(Issue::ParamRedefined, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), '$' . $parameter->getName());
            } else {
                $parameters_seen[$parameter->getName()] = $i;
            }
        }
        $ret5902c6f27920d = $this->context;
        if (!$ret5902c6f27920d instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27920d) == "object" ? get_class($ret5902c6f27920d) : gettype($ret5902c6f27920d)) . " given");
        }
        return $ret5902c6f27920d;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitMethodCall(Node $node)
    {
        $method_name = $node->children['method'];
        if (!is_string($method_name)) {
            $ret5902c6f27955d = $this->context;
            if (!$ret5902c6f27955d instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27955d) == "object" ? get_class($ret5902c6f27955d) : gettype($ret5902c6f27955d)) . " given");
            }
            return $ret5902c6f27955d;
        }
        try {
            $method = (new ContextNode($this->code_base, $this->context, $node))->getMethod($method_name, false);
        } catch (IssueException $exception) {
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
            $ret5902c6f27992d = $this->context;
            if (!$ret5902c6f27992d instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27992d) == "object" ? get_class($ret5902c6f27992d) : gettype($ret5902c6f27992d)) . " given");
            }
            return $ret5902c6f27992d;
        } catch (NodeException $exception) {
            // If we can't figure out the class for this method
            // call, cry YOLO and mark every method with that
            // name with a reference.
            if (Config::get()->dead_code_detection && Config::get()->dead_code_detection_prefer_false_negative) {
                foreach ($this->code_base->getMethodSetByName($method_name) as $method) {
                    $method->addReference($this->context);
                }
            }
            $ret5902c6f279ca2 = $this->context;
            if (!$ret5902c6f279ca2 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f279ca2) == "object" ? get_class($ret5902c6f279ca2) : gettype($ret5902c6f279ca2)) . " given");
            }
            return $ret5902c6f279ca2;
        }
        // Make sure the magic method is accessible
        if ($method->isPrivate() && !$method->getDefiningClass($this->code_base)->isTrait() && (!$this->context->isInClassScope() || $this->context->getClassFQSEN() != $method->getDefiningClassFQSEN())) {
            $this->emitIssue(Issue::AccessMethodPrivate, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $method->getFQSEN(), $method->getFileRef()->getFile(), (string) $method->getFileRef()->getLineNumberStart());
        } else {
            if ($method->isProtected() && !$method->getDefiningClass($this->code_base)->isTrait() && (!$this->context->isInClassScope() || !$this->context->getClassFQSEN()->asType()->canCastToType($method->getClassFQSEN()->asType()) && !$this->context->getClassFQSEN()->asType()->isSubclassOf($this->code_base, $method->getDefiningClassFQSEN()->asType()) && $this->context->getClassFQSEN() != $method->getDefiningClassFQSEN())) {
                $this->emitIssue(Issue::AccessMethodProtected, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), (string) $method->getFQSEN(), $method->getFileRef()->getFile(), (string) $method->getFileRef()->getLineNumberStart());
            }
        }
        // Check the call for paraemter and argument types
        $this->analyzeCallToMethod($this->code_base, $method, $node);
        $ret5902c6f27a386 = $this->context;
        if (!$ret5902c6f27a386 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27a386) == "object" ? get_class($ret5902c6f27a386) : gettype($ret5902c6f27a386)) . " given");
        }
        return $ret5902c6f27a386;
    }
    /**
     * Visit a node with kind `\ast\AST_DIM`
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitDim(Node $node)
    {
        // Check the array type to trigger
        // TypeArraySuspicious
        try {
            $array_type = UnionType::fromNode($this->context, $this->code_base, $node, false);
        } catch (IssueException $exception) {
            // Swallow it. We'll deal with issues elsewhere
        }
        if (!Config::get()->backward_compatibility_checks) {
            $ret5902c6f27a738 = $this->context;
            if (!$ret5902c6f27a738 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27a738) == "object" ? get_class($ret5902c6f27a738) : gettype($ret5902c6f27a738)) . " given");
            }
            return $ret5902c6f27a738;
        }
        if (!($node->children['expr'] instanceof Node && call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['expr']->children['name'], @null) instanceof Node)) {
            $ret5902c6f27aaa1 = $this->context;
            if (!$ret5902c6f27aaa1 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27aaa1) == "object" ? get_class($ret5902c6f27aaa1) : gettype($ret5902c6f27aaa1)) . " given");
            }
            return $ret5902c6f27aaa1;
        }
        // check for $$var[]
        if ($node->children['expr']->kind == \ast\AST_VAR && $node->children['expr']->children['name']->kind == \ast\AST_VAR) {
            $temp = $node->children['expr']->children['name'];
            $depth = 1;
            while ($temp instanceof Node) {
                assert(isset($temp->children['name']), "Expected to find a name in context, something else found.");
                $temp = $temp->children['name'];
                $depth++;
            }
            $dollars = str_repeat('$', $depth);
            $ftemp = new \SplFileObject($this->context->getFile());
            $ftemp->seek($node->lineno - 1);
            $line = $ftemp->current();
            assert(is_string($line));
            unset($ftemp);
            if (strpos($line, '{') === false || strpos($line, '}') === false) {
                $this->emitIssue(Issue::CompatibleExpressionPHP7, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), "{$dollars}{$temp}[]");
            }
            // $foo->$bar['baz'];
        } elseif (!empty($node->children['expr']->children[1]) && $node->children['expr']->children[1] instanceof Node && $node->children['expr']->kind == \ast\AST_PROP && $node->children['expr']->children[0]->kind == \ast\AST_VAR && $node->children['expr']->children[1]->kind == \ast\AST_VAR) {
            $ftemp = new \SplFileObject($this->context->getFile());
            $ftemp->seek($node->lineno - 1);
            $line = $ftemp->current();
            assert(is_string($line));
            unset($ftemp);
            if (strpos($line, '{') === false || strpos($line, '}') === false) {
                $this->emitIssue(Issue::CompatiblePHP7, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0));
            }
        }
        $ret5902c6f27b56a = $this->context;
        if (!$ret5902c6f27b56a instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27b56a) == "object" ? get_class($ret5902c6f27b56a) : gettype($ret5902c6f27b56a)) . " given");
        }
        return $ret5902c6f27b56a;
    }
    public function visitStaticProp(Node $node)
    {
        $ret5902c6f27b889 = $this->analyzeProp($node, true);
        if (!$ret5902c6f27b889 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27b889) == "object" ? get_class($ret5902c6f27b889) : gettype($ret5902c6f27b889)) . " given");
        }
        return $ret5902c6f27b889;
    }
    public function visitProp(Node $node)
    {
        $ret5902c6f27bba7 = $this->analyzeProp($node, false);
        if (!$ret5902c6f27bba7 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27bba7) == "object" ? get_class($ret5902c6f27bba7) : gettype($ret5902c6f27bba7)) . " given");
        }
        return $ret5902c6f27bba7;
    }
    /**
     * Analyze a node with kind `\ast\AST_PROP` or `\ast\AST_STATIC_PROP`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @param bool $is_static
     * True if fetching a static property.
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function analyzeProp(Node $node, $is_static)
    {
        if (!is_bool($is_static)) {
            throw new \InvalidArgumentException("Argument \$is_static passed to analyzeProp() must be of the type bool, " . (gettype($is_static) == "object" ? get_class($is_static) : gettype($is_static)) . " given");
        }
        $exception_or_null = null;
        try {
            $property = (new ContextNode($this->code_base, $this->context, $node))->getProperty($node->children['prop'], $is_static);
            // Mark that this property has been referenced from
            // this context
            $property->addReference($this->context);
        } catch (IssueException $exception) {
            // We'll check out some reasons it might not exist
            // before logging the issue
            $exception_or_null = $exception;
        } catch (\Exception $exception) {
            // Swallow any exceptions. We'll catch it later.
        }
        if (isset($property)) {
            $this->analyzeNoOp($node, Issue::NoopProperty);
        } else {
            assert(isset($node->children['expr']) || isset($node->children['class']), "Property nodes must either have an expression or class");
            $class_list = [];
            try {
                // Get the set of classes that are being referenced
                $class_list = (new ContextNode($this->code_base, $this->context, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->children['expr'], @$node->children['class'])))->getClassList(true);
            } catch (IssueException $exception) {
                Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
            }
            if (!$is_static) {
                // Find out of any of them have a __get magic method
                // (Only check if looking for instance properties)
                $has_getter = array_reduce($class_list, function ($carry, $class) {
                    return $carry || $class->hasGetMethod($this->code_base);
                }, false);
                // If they don't, then analyze for Noops.
                if (!$has_getter) {
                    $this->analyzeNoOp($node, Issue::NoopProperty);
                    if ($exception_or_null instanceof IssueException) {
                        Issue::maybeEmitInstance($this->code_base, $this->context, $exception_or_null->getIssueInstance());
                    }
                }
            }
        }
        $ret5902c6f27c407 = $this->context;
        if (!$ret5902c6f27c407 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f27c407) == "object" ? get_class($ret5902c6f27c407) : gettype($ret5902c6f27c407)) . " given");
        }
        return $ret5902c6f27c407;
    }
    /**
     * Analyze the parameters and arguments for a call
     * to the given method or function
     *
     * @param CodeBase $code_base
     * @param FunctionInterface $method
     * @param Node $node
     *
     * @return void
     */
    private function analyzeCallToMethod(CodeBase $code_base, FunctionInterface $method, Node $node)
    {
        $method->addReference($this->context);
        // Create variables for any pass-by-reference
        // parameters
        $argument_list = $node->children['args'];
        foreach ($argument_list->children as $i => $argument) {
            if (!$argument instanceof \ast\Node) {
                continue;
            }
            $parameter = $method->getParameterForCaller($i);
            if (!$parameter) {
                continue;
            }
            // If pass-by-reference, make sure the variable exists
            // or create it if it doesn't.
            if ($parameter->isPassByReference()) {
                if ($argument->kind == \ast\AST_VAR) {
                    // We don't do anything with it; just create it
                    // if it doesn't exist
                    $variable = (new ContextNode($this->code_base, $this->context, $argument))->getOrCreateVariable();
                } elseif ($argument->kind == \ast\AST_STATIC_PROP || $argument->kind == \ast\AST_PROP) {
                    $property_name = $argument->children['prop'];
                    if (is_string($property_name)) {
                        // We don't do anything with it; just create it
                        // if it doesn't exist
                        try {
                            $property = (new ContextNode($this->code_base, $this->context, $argument))->getOrCreateProperty($argument->children['prop'], $argument->kind == \ast\AST_STATIC_PROP);
                        } catch (IssueException $exception) {
                            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                        } catch (\Exception $exception) {
                            // If we can't figure out what kind of a call
                            // this is, don't worry about it
                        }
                    } else {
                        // This is stuff like `Class->$foo`. I'm ignoring
                        // it.
                    }
                }
            }
        }
        // Confirm the argument types are clean
        ArgumentType::analyze($method, $node, $this->context, $this->code_base);
        // Take another pass over pass-by-reference parameters
        // and assign types to passed in variables
        foreach ($argument_list->children as $i => $argument) {
            if (!$argument instanceof \ast\Node) {
                continue;
            }
            $parameter = $method->getParameterForCaller($i);
            if (!$parameter) {
                continue;
            }
            if (Config::get()->dead_code_detection) {
                $visitor = new ArgumentVisitor($this->code_base, $this->context);
                $visitor($argument);
            }
            // If the parameter is pass-by-reference and we're
            // passing a variable in, see if we should pass
            // the parameter and variable types to eachother
            $variable = null;
            if ($parameter->isPassByReference()) {
                if ($argument->kind == \ast\AST_VAR) {
                    $variable = (new ContextNode($this->code_base, $this->context, $argument))->getOrCreateVariable();
                } elseif ($argument->kind == \ast\AST_STATIC_PROP || $argument->kind == \ast\AST_PROP) {
                    $property_name = $argument->children['prop'];
                    if (is_string($property_name)) {
                        // We don't do anything with it; just create it
                        // if it doesn't exist
                        try {
                            $variable = (new ContextNode($this->code_base, $this->context, $argument))->getOrCreateProperty($argument->children['prop'], $argument->kind == \ast\AST_STATIC_PROP);
                        } catch (IssueException $exception) {
                            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                        } catch (\Exception $exception) {
                            // If we can't figure out what kind of a call
                            // this is, don't worry about it
                        }
                    } else {
                        // This is stuff like `Class->$foo`. I'm ignoring
                        // it.
                    }
                }
                if ($variable) {
                    $variable->getUnionType()->addUnionType($parameter->getNonVariadicUnionType());
                }
            }
        }
        // If we're in quick mode, don't retest methods based on
        // parameter types passed in
        if (Config::get()->quick_mode) {
            return;
        }
        // Re-analyze the method with the types of the arguments
        // being passed in.
        $this->analyzeMethodWithArgumentTypes($code_base, $node->children['args'], $method);
    }
    /**
     * Replace the method's parameter types with the argument
     * types and re-analyze the method.
     *
     * @param CodeBase $code_base
     * The code base in which the method call was found
     *
     * @param Node $argument_list_node
     * An AST node listing the arguments
     *
     * @param FunctionInterface $method
     * The method or function being called
     *
     * @return void
     */
    private function analyzeMethodWithArgumentTypes(CodeBase $code_base, Node $argument_list_node, FunctionInterface $method)
    {
        // Don't re-analyze recursive methods. That doesn't go
        // well.
        if ($this->context->isInFunctionLikeScope() && $method->getFQSEN() === $this->context->getFunctionLikeFQSEN()) {
            return;
        }
        // Create a copy of the method's original parameter list
        // and scope so that we can reset it after re-analyzing
        // it.
        $original_method_scope = clone $method->getInternalScope();
        $original_parameter_list = array_map(function (Variable $parameter) {
            $ret5902c6f27d41c = clone $parameter;
            if (!$ret5902c6f27d41c instanceof Variable) {
                throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6f27d41c) == "object" ? get_class($ret5902c6f27d41c) : gettype($ret5902c6f27d41c)) . " given");
            }
            return $ret5902c6f27d41c;
        }, $method->getParameterList());
        if (count($original_parameter_list) === 0) {
            return;
            // No point in recursing if there's no changed parameters.
        }
        // always resolve all arguments outside of quick mode to detect undefined variables, other problems in call arguments.
        // Fixes https://github.com/etsy/phan/issues/583
        $argument_types = [];
        foreach ($argument_list_node->children as $i => $argument) {
            if (!$argument) {
                continue;
            }
            // Determine the type of the argument at position $i
            $argument_types[$i] = UnionType::fromNode($this->context, $this->code_base, $argument);
        }
        // Get the list of parameters on the method
        $parameter_list = $method->getParameterList();
        foreach ($parameter_list as $i => $parameter) {
            $argument = call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$argument_list_node->children[$i], @null);
            if (!$argument && $parameter->hasDefaultValue()) {
                $parameter_list = $method->getParameterList();
                $parameter_list[$i] = clone $parameter;
                $parameter_type = $parameter->getDefaultValueType();
                if ($parameter_type->isType(NullType::instance(false))) {
                    // Treat a parameter default of null the same way as passing null to that parameter
                    // (Add null to the list of possibilities)
                    $parameter_list[$i]->addUnionType($parameter_type);
                } else {
                    // For other types (E.g. string), just replace the union type.
                    $parameter_list[$i]->setUnionType($parameter_type);
                }
                $method->setParameterList($parameter_list);
            }
            // If there's no parameter at that offset, we may be in
            // a ParamTooMany situation. That is caught elsewhere.
            if (!$argument || !$parameter->getNonVariadicUnionType()->isEmpty()) {
                continue;
            }
            $this->updateParameterTypeByArgument($method, $parameter, $argument, $argument_types[$i], $i);
        }
        // Now that we know something about the parameters used
        // to call the method, we can reanalyze the method with
        // the types of the parameter
        $method->analyzeWithNewParams($method->getContext(), $code_base);
        // Reset to the original parameter list and scope after
        // having tested the parameters with the types passed in
        $method->setParameterList($original_parameter_list);
        $method->setInternalScope($original_method_scope);
    }
    /**
     * @param FunctionInterface $method
     * The method that we're updating parameter types for
     *
     * @param Parameter $parameter
     * The parameter that we're updating
     *
     * @param Node|mixed $argument
     * The argument whose type we'd like to replace the
     * parameter type with.
     *
     * @param Node|mixed $argument_type
     * The type of $argument
     *
     * @param int $parameter_offset
     * The offset of the parameter on the method's
     * signature.
     *
     * @return void
     */
    private function updateParameterTypeByArgument(FunctionInterface $method, Variable $parameter, $argument, UnionType $argument_type, $parameter_offset)
    {
        if (!is_int($parameter_offset)) {
            throw new \InvalidArgumentException("Argument \$parameter_offset passed to updateParameterTypeByArgument() must be of the type int, " . (gettype($parameter_offset) == "object" ? get_class($parameter_offset) : gettype($parameter_offset)) . " given");
        }
        // Then set the new type on that parameter based
        // on the argument's type. We'll use this to
        // retest the method with the passed in types
        // TODO: if $argument_type is non-empty and !isType(NullType), instead use setUnionType?
        $parameter->getNonVariadicUnionType()->addUnionType($argument_type);
        // If we're passing by reference, get the variable
        // we're dealing with wrapped up and shoved into
        // the scope of the method
        if (!$parameter->isPassByReference()) {
            // Overwrite the method's variable representation
            // of the parameter with the parameter with the
            // new type
            $method->getInternalScope()->addVariable($parameter);
            return;
        }
        // At this point we're dealing with a pass-by-reference
        // parameter.
        // For now, give up and work on it later.
        //
        // TODO (Issue #376): It's possible to have a
        // parameter `&...$args`. Analysing that is going to
        // be a problem. Is it possible to create
        // `PassByReferenceVariableCollection extends Variable`
        // or something similar?
        if ($parameter->isVariadic()) {
            return;
        }
        if (!$argument instanceof \ast\Node) {
            return;
        }
        $variable = null;
        if ($argument->kind == \ast\AST_VAR) {
            $variable = (new ContextNode($this->code_base, $this->context, $argument))->getOrCreateVariable();
        } else {
            if ($argument->kind == \ast\AST_STATIC_PROP) {
                try {
                    // TODO: shouldn't call getOrCreateProperty for a static property. You can't create a static property.
                    $variable = (new ContextNode($this->code_base, $this->context, $argument))->getOrCreateProperty(call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$argument->children['prop'], @''), true);
                } catch (UnanalyzableException $exception) {
                    // Ignore it. There's nothing we can do. (E.g. the class name for the static property fetch couldn't be determined.
                }
            }
        }
        // If we couldn't find a variable, give up
        if (!$variable) {
            return;
        }
        $pass_by_reference_variable = new PassByReferenceVariable($parameter, $variable);
        // Substitute the new type in for the parameter's type
        $parameter_list = $method->getParameterList();
        $parameter_list[$parameter_offset] = $pass_by_reference_variable;
        $method->setParameterList($parameter_list);
        // Add it to the scope of the function wrapped
        // in a way that makes it addressable as the
        // parameter its mimicking
        $method->getInternalScope()->addVariable($pass_by_reference_variable);
    }
    /**
     * @param Node $node
     * A node to check to see if it's a no-op
     *
     * @param string $issue_type
     * A message to emit if it's a no-op
     *
     * @return void
     */
    private function analyzeNoOp(Node $node, $issue_type)
    {
        if (!is_string($issue_type)) {
            throw new \InvalidArgumentException("Argument \$issue_type passed to analyzeNoOp() must be of the type string, " . (gettype($issue_type) == "object" ? get_class($issue_type) : gettype($issue_type)) . " given");
        }
        if ($this->parent_node instanceof Node && $this->parent_node->kind == \ast\AST_STMT_LIST) {
            $this->emitIssue($issue_type, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0));
        }
    }
    /**
     * @param Decl $node
     * A decl to check to see if it's only effect
     * is the throw an exception
     *
     * @return bool
     * True when the decl can only throw an exception
     */
    private function declOnlyThrows(Decl $node)
    {
        return isset($node->children['stmts']) && $node->children['stmts']->kind === \ast\AST_STMT_LIST && count($node->children['stmts']->children) === 1 && $node->children['stmts']->children[0]->kind === \ast\AST_THROW;
    }
}