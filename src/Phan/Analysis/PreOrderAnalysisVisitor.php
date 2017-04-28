<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\AST\ContextNode;
use Phan\AST\UnionTypeVisitor;
use Phan\CodeBase;
use Phan\Exception\CodeBaseException;
use Phan\Exception\NodeException;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\Element\Clazz;
use Phan\Language\Element\Comment;
use Phan\Language\Element\Func;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Element\Parameter;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\Scope\ClassScope;
use Phan\Language\Type;
use Phan\Language\UnionType;
use ast\Node;
use ast\Node\Decl;
class PreOrderAnalysisVisitor extends ScopeVisitor
{
    /**
     * @param CodeBase $code_base
     * The code base in which we're analyzing code
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     */
    public function __construct(CodeBase $code_base, Context $context)
    {
        parent::__construct($code_base, $context);
    }
    public function visit(Node $node)
    {
        $ret5902c6f2942b5 = $this->context;
        if (!$ret5902c6f2942b5 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2942b5) == "object" ? get_class($ret5902c6f2942b5) : gettype($ret5902c6f2942b5)) . " given");
        }
        return $ret5902c6f2942b5;
    }
    /**
     * Visit a node with kind `\ast\AST_CLASS`
     *
     * @param Decl $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitClass(Decl $node)
    {
        if ($node->flags & \ast\flags\CLASS_ANONYMOUS) {
            $class_name = (new ContextNode($this->code_base, $this->context, $node))->getUnqualifiedNameForAnonymousClass();
        } else {
            $class_name = (string) $node->name;
        }
        assert(!empty($class_name), "Class name cannot be empty");
        $alternate_id = 0;
        // Hunt for the alternate of this class defined
        // in this file
        do {
            $class_fqsen = FullyQualifiedClassName::fromStringInContext($class_name, $this->context)->withAlternateId($alternate_id++);
            if (!$this->code_base->hasClassWithFQSEN($class_fqsen)) {
                throw new CodeBaseException($class_fqsen, "Can't find class {$class_fqsen} - aborting");
            }
            $clazz = $this->code_base->getClassByFQSEN($class_fqsen);
        } while ($this->context->getProjectRelativePath() != $clazz->getFileRef()->getProjectRelativePath() || $this->context->getLineNumberStart() != $clazz->getFileRef()->getLineNumberStart());
        $ret5902c6f294986 = $clazz->getContext()->withScope($clazz->getInternalScope());
        if (!$ret5902c6f294986 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f294986) == "object" ? get_class($ret5902c6f294986) : gettype($ret5902c6f294986)) . " given");
        }
        return $ret5902c6f294986;
    }
    /**
     * Visit a node with kind `\ast\AST_METHOD`
     *
     * @param Decl $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitMethod(Decl $node)
    {
        $method_name = (string) $node->name;
        assert($this->context->isInClassScope(), "Must be in class context to see a method");
        $clazz = $this->getContextClass();
        if (!$clazz->hasMethodWithName($this->code_base, $method_name)) {
            throw new CodeBaseException(null, "Can't find method {$clazz->getFQSEN()}::{$method_name}() - aborting");
        }
        $method = $clazz->getMethodByNameInContext($this->code_base, $method_name, $this->context);
        // Parse the comment above the method to get
        // extra meta information about the method.
        $comment = Comment::fromStringInContext(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->docComment, @''), $this->context);
        $context = $this->context->withScope($method->getInternalScope());
        // For any @var references in the method declaration,
        // add them as variables to the method's scope
        foreach ($comment->getVariableList() as $parameter) {
            $context->addScopeVariable($parameter->asVariable($this->context));
        }
        // Add $this to the scope of non-static methods
        if (!($node->flags & \ast\flags\MODIFIER_STATIC)) {
            assert($clazz->getInternalScope()->hasVariableWithName('this'), "Classes must have a \$this variable.");
            $context->addScopeVariable($clazz->getInternalScope()->getVariableByName('this'));
        }
        // Add each method parameter to the scope. We clone it
        // so that changes to the variable don't alter the
        // parameter definition
        foreach ($method->getParameterList() as $parameter) {
            $context->addScopeVariable($parameter->cloneAsNonVariadic());
        }
        if ($this->analyzeFunctionLikeIsGenerator($node)) {
            $this->setReturnTypeOfGenerator($method, $node);
        }
        $ret5902c6f29519a = $context;
        if (!$ret5902c6f29519a instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f29519a) == "object" ? get_class($ret5902c6f29519a) : gettype($ret5902c6f29519a)) . " given");
        }
        return $ret5902c6f29519a;
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
        $function_name = (string) $node->name;
        try {
            $canonical_function = (new ContextNode($this->code_base, $this->context, $node))->getFunction($function_name, true);
        } catch (CodeBaseException $exception) {
            // This really ought not happen given that
            // we already successfully parsed the code
            // base
            throw $exception;
        }
        // Hunt for the alternate associated with the file we're
        // looking at currently in this context.
        $function = null;
        foreach ($canonical_function->alternateGenerator($this->code_base) as $i => $alternate_function) {
            if ($alternate_function->getFileRef()->getProjectRelativePath() === $this->context->getProjectRelativePath()) {
                $function = $alternate_function;
                break;
            }
        }
        if (empty($function)) {
            // No alternate was found
            throw new CodeBaseException(null, "Can't find function {$function_name} in context {$this->context} - aborting");
        }
        assert($function instanceof Func);
        $context = $this->context->withScope($function->getInternalScope());
        // Parse the comment above the method to get
        // extra meta information about the method.
        $comment = Comment::fromStringInContext(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->docComment, @''), $this->context);
        // For any @var references in the method declaration,
        // add them as variables to the method's scope
        foreach ($comment->getVariableList() as $parameter) {
            $context->addScopeVariable($parameter->asVariable($this->context));
        }
        // Add each method parameter to the scope. We clone it
        // so that changes to the variable don't alter the
        // parameter definition
        foreach ($function->getParameterList() as $parameter) {
            $context->addScopeVariable($parameter->cloneAsNonVariadic());
        }
        if ($this->analyzeFunctionLikeIsGenerator($node)) {
            $this->setReturnTypeOfGenerator($function, $node);
        }
        $ret5902c6f2958f6 = $context;
        if (!$ret5902c6f2958f6 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2958f6) == "object" ? get_class($ret5902c6f2958f6) : gettype($ret5902c6f2958f6)) . " given");
        }
        return $ret5902c6f2958f6;
    }
    /**
     * If a Closure overrides the scope(class) it will be executed in (via doc comment)
     * then return a context with the new scope instead.
     */
    private function withOptionalClosureScope(Context $context, Func $func, Decl $node)
    {
        $closure_scope_option = $func->getClosureScopeOption();
        if (!$closure_scope_option->isDefined()) {
            $ret5902c6f295c48 = $context;
            if (!$ret5902c6f295c48 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f295c48) == "object" ? get_class($ret5902c6f295c48) : gettype($ret5902c6f295c48)) . " given");
            }
            return $ret5902c6f295c48;
        }
        // The Func already checked that the $closure_scope is a class
        // which exists in the codebase
        $closure_scope = $closure_scope_option->get();
        $class_fqsen = $closure_scope->asFQSEN();
        if (!$class_fqsen instanceof FullyQualifiedClassName) {
            $ret5902c6f295f6c = $context;
            if (!$ret5902c6f295f6c instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f295f6c) == "object" ? get_class($ret5902c6f295f6c) : gettype($ret5902c6f295f6c)) . " given");
            }
            return $ret5902c6f295f6c;
        }
        assert($class_fqsen instanceof FullyQualifiedClassName);
        $clazz = $this->code_base->getClassByFQSEN($class_fqsen);
        $ret5902c6f2962ef = $clazz->getContext()->withScope($clazz->getInternalScope());
        if (!$ret5902c6f2962ef instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2962ef) == "object" ? get_class($ret5902c6f2962ef) : gettype($ret5902c6f2962ef)) . " given");
        }
        return $ret5902c6f2962ef;
    }
    /**
     * Visit a node with kind `\ast\AST_CLOSURE`
     *
     * @param Decl $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitClosure(Decl $node)
    {
        $closure_fqsen = FullyQualifiedFunctionName::fromClosureInContext($this->context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0)));
        $func = Func::fromNode($this->context, $this->code_base, $node, $closure_fqsen);
        // usually the same as $this->context, unless this Closure uses @PhanClosureScope
        // Also need to override $this if bindTo is called?
        $context = $this->withOptionalClosureScope($this->context, $func, $node);
        // If we have a 'this' variable in our current scope,
        // pass it down into the closure
        if ($context->getScope()->hasVariableWithName('this')) {
            $thisVarFromScope = $context->getScope()->getVariableByName('this');
            $func->getInternalScope()->addVariable($thisVarFromScope);
        }
        // Make the closure reachable by FQSEN from anywhere
        $this->code_base->addFunction($func);
        if (!empty($node->children['uses']) && $node->children['uses']->kind == \ast\AST_CLOSURE_USES) {
            $uses = $node->children['uses'];
            foreach ($uses->children as $use) {
                if ($use->kind != \ast\AST_CLOSURE_VAR) {
                    $this->emitIssue(Issue::VariableUseClause, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$node->lineno, @0));
                    continue;
                }
                $variable_name = (new ContextNode($this->code_base, $context, $use->children['name']))->getVariableName();
                if (empty($variable_name)) {
                    continue;
                }
                $variable = null;
                // Check to see if the variable exists in this scope
                if (!$context->getScope()->hasVariableWithName($variable_name)) {
                    // If this is not pass-by-reference variable we
                    // have a problem
                    if (!($use->flags & \ast\flags\PARAM_REF)) {
                        $this->emitIssue(Issue::UndeclaredVariable, call_user_func(function ($v1, $v2) {
                            return isset($v1) ? $v1 : $v2;
                        }, @$node->lineno, @0), $variable_name);
                        continue;
                    } else {
                        // If the variable doesn't exist, but its
                        // a pass-by-reference variable, we can
                        // just create it
                        $variable = Variable::fromNodeInContext($use, $context, $this->code_base, false);
                    }
                } else {
                    $variable = $context->getScope()->getVariableByName($variable_name);
                    // If this isn't a pass-by-reference variable, we
                    // clone the variable so state within this scope
                    // doesn't update the outer scope
                    if (!($use->flags & \ast\flags\PARAM_REF)) {
                        $variable = clone $variable;
                    }
                }
                // Pass the variable into a new scope
                $func->getInternalScope()->addVariable($variable);
            }
        }
        // Add all parameters to the scope
        if (!empty($node->children['params']) && $node->children['params']->kind == \ast\AST_PARAM_LIST) {
            $params = $node->children['params'];
            foreach ($params->children as $param) {
                // Read the parameter
                $parameter = Parameter::fromNode($context, $this->code_base, $param);
                // Add it to the scope
                $func->getInternalScope()->addVariable($parameter);
            }
        }
        if ($this->analyzeFunctionLikeIsGenerator($node)) {
            $this->setReturnTypeOfGenerator($func, $node);
        }
        $ret5902c6f296e33 = $context->withScope($func->getInternalScope());
        if (!$ret5902c6f296e33 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f296e33) == "object" ? get_class($ret5902c6f296e33) : gettype($ret5902c6f296e33)) . " given");
        }
        return $ret5902c6f296e33;
    }
    /**
     * The return type of the given FunctionInterface to a Generator.
     * Emit an Issue if the documented return type is incompatible with that.
     * @return void
     */
    private function setReturnTypeOfGenerator(FunctionInterface $func, Node $node)
    {
        // Currently, there is no way to describe the types passed to
        // a Generator in phpdoc.
        // So, nothing bothers recording the types beyond \Generator.
        $func->setHasReturn(true);
        // Returns \Generator, technically
        $func->setHasYield(true);
        if ($func->getUnionType()->isEmpty()) {
            $func->setIsReturnTypeUndefined(true);
            $func->getUnionType()->addUnionType(Type::fromNamespaceAndName('\\', 'Generator', false)->asUnionType());
        }
        if (!$func->isReturnTypeUndefined()) {
            $func_return_type = $func->getUnionType();
            if (!$func_return_type->canCastToExpandedUnionType(Type::fromNamespaceAndName('\\', 'Generator', false)->asUnionType(), $this->code_base)) {
                // At least one of the documented return types must
                // be Generator, Iterable, or Traversable.
                // Check for the issue here instead of in visitReturn/visitYield so that
                // the check is done exactly once.
                $this->emitIssue(Issue::TypeMismatchReturn, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), '\\Generator', $func->getName(), (string) $func_return_type);
            }
        }
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * This must be called before visitReturn is called within a function.
     *
     * @return bool
     * True if the node represents a yield, else false.
     */
    public static function analyzeFunctionLikeIsGenerator(Node $node)
    {
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            if (!$child_node instanceof Node) {
                continue;
            }
            // Technically, `return [yield 2];` is a valid php use of yield. So Analysis::shouldVisit doesn't help much.
            if (self::analyzeNodeHasYield($child_node)) {
                $ret5902c6f2974c2 = true;
                if (!is_bool($ret5902c6f2974c2)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f2974c2) . " given");
                }
                return $ret5902c6f2974c2;
            }
        }
        $ret5902c6f29771b = false;
        if (!is_bool($ret5902c6f29771b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f29771b) . " given");
        }
        return $ret5902c6f29771b;
    }
    public static function analyzeNodeHasYield(Node $node)
    {
        // The ast module doesn't tell us if something has a yield statement.
        // We want to stop if the type of a node is a closure or a anonymous class
        // Get the method/function/closure we're in
        switch ($node->kind) {
            case \ast\AST_YIELD:
            case \ast\AST_YIELD_FROM:
                return true;
            case \ast\AST_METHOD:
            case \ast\AST_FUNC_DECL:
            case \ast\AST_CLOSURE:
            case \ast\AST_CLASS:
                return false;
        }
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            if (!$child_node instanceof Node) {
                continue;
            }
            if (self::analyzeNodeHasYield($child_node)) {
                return true;
            }
        }
        return false;
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
        $expression_union_type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        // Filter out the non-generic types of the
        // expression
        $non_generic_expression_union_type = $expression_union_type->genericArrayElementTypes();
        if ($node->children['value']->kind == \ast\AST_ARRAY) {
            foreach (call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->children['value']->children, @[]) as $child_node) {
                $key_node = call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$child_node->children['key'], @null);
                $value_node = call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$child_node->children['value'], @null);
                // for syntax like: foreach ([] as list(, $a));
                if ($value_node === null) {
                    continue;
                }
                assert($value_node instanceof Node);
                $variable = Variable::fromNodeInContext($value_node, $this->context, $this->code_base, false);
                // If we were able to figure out the type and its
                // a generic type, then set its element types as
                // the type of the variable
                if (!$non_generic_expression_union_type->isEmpty()) {
                    $second_order_non_generic_expression_union_type = $non_generic_expression_union_type->genericArrayElementTypes();
                    if (!$second_order_non_generic_expression_union_type->isEmpty()) {
                        $variable->setUnionType($second_order_non_generic_expression_union_type);
                    }
                }
                $this->context->addScopeVariable($variable);
            }
            // Otherwise, read the value as regular variable and
            // add it to the scope
        } else {
            // Create a variable for the value
            $variable = Variable::fromNodeInContext($node->children['value'], $this->context, $this->code_base, false);
            // If we were able to figure out the type and its
            // a generic type, then set its element types as
            // the type of the variable
            if (!$non_generic_expression_union_type->isEmpty()) {
                $variable->setUnionType($non_generic_expression_union_type);
            }
            // Add the variable to the scope
            $this->context->addScopeVariable($variable);
        }
        // If there's a key, make a variable out of that too
        if (!empty($node->children['key'])) {
            if ($node->children['key'] instanceof \ast\Node && $node->children['key']->kind == \ast\AST_LIST) {
                throw new NodeException($node, "Can't use list() as a key element - aborting");
            }
            $variable = Variable::fromNodeInContext($node->children['key'], $this->context, $this->code_base, false);
            $this->context->addScopeVariable($variable);
        }
        $ret5902c6f298214 = $this->context;
        if (!$ret5902c6f298214 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f298214) == "object" ? get_class($ret5902c6f298214) : gettype($ret5902c6f298214)) . " given");
        }
        return $ret5902c6f298214;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitCatch(Node $node)
    {
        try {
            $union_type = UnionTypeVisitor::unionTypeFromClassNode($this->code_base, $this->context, $node->children['class']);
            $class_list = (new ContextNode($this->code_base, $this->context, $node->children['class']))->getClassList();
            foreach ($class_list as $class) {
                $class->addReference($this->context);
            }
        } catch (CodeBaseException $exception) {
            $this->emitIssue(Issue::UndeclaredClassCatch, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $exception->getFQSEN());
        }
        $variable_name = (new ContextNode($this->code_base, $this->context, $node->children['var']))->getVariableName();
        if (!empty($variable_name)) {
            $variable = Variable::fromNodeInContext($node->children['var'], $this->context, $this->code_base, false);
            if (!$union_type->isEmpty()) {
                $variable->setUnionType($union_type);
            }
            $this->context->addScopeVariable($variable);
        }
        $ret5902c6f29889f = $this->context;
        if (!$ret5902c6f29889f instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f29889f) == "object" ? get_class($ret5902c6f29889f) : gettype($ret5902c6f29889f)) . " given");
        }
        return $ret5902c6f29889f;
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
        if (!isset($node->children['cond']) || !$node->children['cond'] instanceof Node) {
            $ret5902c6f298c07 = $this->context;
            if (!$ret5902c6f298c07 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f298c07) == "object" ? get_class($ret5902c6f298c07) : gettype($ret5902c6f298c07)) . " given");
            }
            return $ret5902c6f298c07;
        }
        // Get the type just to make sure everything
        // is defined.
        $expression_type = UnionType::fromNode($this->context, $this->code_base, $node->children['cond']);
        $ret5902c6f299016 = (new ConditionVisitor($this->code_base, $this->context))($node->children['cond']);
        if (!$ret5902c6f299016 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f299016) == "object" ? get_class($ret5902c6f299016) : gettype($ret5902c6f299016)) . " given");
        }
        return $ret5902c6f299016;
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
        // Look only at nodes of the form `assert(expr, ...)`.
        if (!isset($node->children['expr']) || !isset($node->children['expr']->children['name']) || $node->children['expr']->children['name'] !== 'assert' || !isset($node->children['args']) || !isset($node->children['args']->children[0]) || !$node->children['args']->children[0] instanceof Node) {
            $ret5902c6f2994b0 = $this->context;
            if (!$ret5902c6f2994b0 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2994b0) == "object" ? get_class($ret5902c6f2994b0) : gettype($ret5902c6f2994b0)) . " given");
            }
            return $ret5902c6f2994b0;
        }
        $ret5902c6f299813 = (new ConditionVisitor($this->code_base, $this->context))($node->children['args']->children[0]);
        if (!$ret5902c6f299813 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f299813) == "object" ? get_class($ret5902c6f299813) : gettype($ret5902c6f299813)) . " given");
        }
        return $ret5902c6f299813;
    }
    /**
     * @return Clazz
     * Get the class on this scope or fail real hard
     */
    private function getContextClass()
    {
        $ret5902c6f299b5a = $this->context->getClassInScope($this->code_base);
        if (!$ret5902c6f299b5a instanceof Clazz) {
            throw new \InvalidArgumentException("Argument returned must be of the type Clazz, " . (gettype($ret5902c6f299b5a) == "object" ? get_class($ret5902c6f299b5a) : gettype($ret5902c6f299b5a)) . " given");
        }
        return $ret5902c6f299b5a;
    }
}