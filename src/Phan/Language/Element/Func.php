<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Issue;
use Phan\Language\Scope\FunctionLikeScope;
use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\Type;
use Phan\Language\Type\NullType;
use Phan\Language\UnionType;
use Phan\Library\None;
use Phan\Library\Option;
use ast\Node;
use ast\Node\Decl;
class Func extends AddressableElement implements FunctionInterface
{
    use \Phan\Analysis\Analyzable;
    use \Phan\Memoize;
    use FunctionTrait;
    use ClosedScopeElement;
    /**
     * @var Option<Type>|null
     *
     * If this Func was created by analyzing a closure, phpdoc may override the scope to use for analysis.
     */
    protected $closure_scope = null;
    /**
     * @param \phan\Context $context
     * The context in which the structural element lives
     *
     * @param string $name,
     * The name of the typed structural element
     *
     * @param UnionType $type,
     * A '|' delimited set of types satisfyped by this
     * typed structural element.
     *
     * @param int $flags,
     * The flags property contains node specific flags. It is
     * always defined, but for most nodes it is always zero.
     * ast\kind_uses_flags() can be used to determine whether
     * a certain kind has a meaningful flags value.
     */
    public function __construct(Context $context, $name, UnionType $type, $flags, FullyQualifiedFunctionName $fqsen)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to __construct() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        parent::__construct($context, $name, $type, $flags, $fqsen);
        $this->setInternalScope(new FunctionLikeScope($context->getScope(), $fqsen));
    }
    /**
     * If a Closure overrides the scope(class) it will be executed in (via doc comment)
     * then return a context with the new scope instead.
     *
     * @param CodeBase $code_base
     * @param Context $context - The outer context in which the closure was declared.
     *                           Either this (or a new context for the other class) will be returned.
     *
     * Postcondition: if return value !== $context, then $closure_scope is a class which exists in the codebase.
     */
    private static function getClosureOverrideContext(CodeBase $code_base, Context $context, Type $closure_scope, Decl $node)
    {
        if ($node->kind !== \ast\AST_CLOSURE) {
            $ret5902c6f585ee5 = $context;
            if (!$ret5902c6f585ee5 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f585ee5) == "object" ? get_class($ret5902c6f585ee5) : gettype($ret5902c6f585ee5)) . " given");
            }
            return $ret5902c6f585ee5;
        }
        if ($closure_scope->isNativeType()) {
            // TODO: What about 'null' (for code planning to bindTo(null))
            // Emit an error
            Issue::maybeEmit($code_base, $context, Issue::TypeInvalidClosureScope, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $closure_scope);
            $ret5902c6f5862ae = $context;
            if (!$ret5902c6f5862ae instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f5862ae) == "object" ? get_class($ret5902c6f5862ae) : gettype($ret5902c6f5862ae)) . " given");
            }
            return $ret5902c6f5862ae;
        } else {
            // TODO: handle 'parent'?
            // TODO: Check if isInClassScope
            if ($closure_scope->isSelfType() || $closure_scope->isStaticType()) {
                $ret5902c6f5865b4 = $context;
                if (!$ret5902c6f5865b4 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f5865b4) == "object" ? get_class($ret5902c6f5865b4) : gettype($ret5902c6f5865b4)) . " given");
                }
                return $ret5902c6f5865b4;
            }
        }
        $class_fqsen = $closure_scope->asFQSEN();
        if (!$class_fqsen instanceof FullyQualifiedClassName) {
            $ret5902c6f58693c = $context;
            if (!$ret5902c6f58693c instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f58693c) == "object" ? get_class($ret5902c6f58693c) : gettype($ret5902c6f58693c)) . " given");
            }
            return $ret5902c6f58693c;
        }
        assert($class_fqsen instanceof FullyQualifiedClassName);
        if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
            Issue::maybeEmit($code_base, $context, Issue::UndeclaredClosureScope, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $closure_scope);
            $ret5902c6f586d09 = $context;
            if (!$ret5902c6f586d09 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f586d09) == "object" ? get_class($ret5902c6f586d09) : gettype($ret5902c6f586d09)) . " given");
            }
            return $ret5902c6f586d09;
        }
        $clazz = $code_base->getClassByFQSEN($class_fqsen);
        $ret5902c6f587060 = $context->withScope($clazz->getInternalScope());
        if (!$ret5902c6f587060 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f587060) == "object" ? get_class($ret5902c6f587060) : gettype($ret5902c6f587060)) . " given");
        }
        return $ret5902c6f587060;
    }
    /**
     * @param Context $context
     * The context in which the node appears
     *
     * @param CodeBase $code_base
     *
     * @param Node $node
     * An AST node representing a function
     *
     * @param FullyQualifiedFunctionName $fqsen
     * A fully qualified name for the function
     *
     * @return Func
     * A Func representing the AST node in the
     * given context
     */
    public static function fromNode(Context $context, CodeBase $code_base, Decl $node, FullyQualifiedFunctionName $fqsen)
    {
        // Create the skeleton function object from what
        // we know so far
        $func = new Func($context, (string) $node->name, new UnionType(), call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0), $fqsen);
        // Parse the comment above the function to get
        // extra meta information about the function.
        $comment = Comment::fromStringInContext(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->docComment, @''), $context);
        // Redefine the function's internal scope to point to the new class before adding any variables to the scope.
        $closure_scope_option = $comment->getClosureScopeOption();
        $func->closure_scope = $closure_scope_option;
        if ($closure_scope_option->isDefined()) {
            $new_context = self::getClosureOverrideContext($code_base, $context, $closure_scope_option->get(), $node);
            if ($new_context !== $context) {
                $func->setInternalScope(new FunctionLikeScope($new_context->getScope(), $func->getInternalScope()->getFQSEN()));
            } else {
                $func->closure_scope = new None();
            }
        }
        // @var Parameter[]
        // The list of parameters specified on the
        // function
        $parameter_list = Parameter::listFromNode($context, $code_base, $node->children['params']);
        // Add each parameter to the scope of the function
        foreach ($parameter_list as $parameter) {
            $func->getInternalScope()->addVariable($parameter);
        }
        // If the function is Analyzable, set the node so that
        // we can come back to it whenever we like and
        // rescan it
        $func->setNode($node);
        // Set the parameter list on the function
        $func->setParameterList($parameter_list);
        $func->setNumberOfRequiredParameters(array_reduce($parameter_list, function ($carry, Parameter $parameter) {
            if (!is_int($carry)) {
                throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
            }
            $ret5902c6f5877cf = $carry + ($parameter->isRequired() ? 1 : 0);
            if (!is_int($ret5902c6f5877cf)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f5877cf) . " given");
            }
            return $ret5902c6f5877cf;
        }, 0));
        $func->setNumberOfOptionalParameters(array_reduce($parameter_list, function ($carry, Parameter $parameter) {
            if (!is_int($carry)) {
                throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
            }
            $ret5902c6f587d45 = $carry + ($parameter->isOptional() ? 1 : 0);
            if (!is_int($ret5902c6f587d45)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f587d45) . " given");
            }
            return $ret5902c6f587d45;
        }, 0));
        // Check to see if the comment specifies that the
        // function is deprecated
        $func->setIsDeprecated($comment->isDeprecated());
        // Set whether or not the element is internal to
        // the namespace.
        $func->setIsNSInternal($comment->isNSInternal());
        $func->setSuppressIssueList($comment->getSuppressIssueList());
        // Take a look at function return types
        if ($node->children['returnType'] !== null) {
            // Get the type of the parameter
            $union_type = UnionType::fromNode($context, $code_base, $node->children['returnType']);
            $func->getUnionType()->addUnionType($union_type);
        }
        if ($comment->hasReturnUnionType()) {
            // See if we have a return type specified in the comment
            $union_type = $comment->getReturnType();
            assert(!$union_type->hasSelfType(), "Function referencing self in {$context}");
            $func->getUnionType()->addUnionType($union_type);
        }
        // Add params to local scope for user functions
        FunctionTrait::addParamsToScopeOfFunctionOrMethod($context, $code_base, $node, $func, $comment);
        $ret5902c6f5884b5 = $func;
        if (!$ret5902c6f5884b5 instanceof Func) {
            throw new \InvalidArgumentException("Argument returned must be of the type Func, " . (gettype($ret5902c6f5884b5) == "object" ? get_class($ret5902c6f5884b5) : gettype($ret5902c6f5884b5)) . " given");
        }
        return $ret5902c6f5884b5;
    }
    /**
     * @return Option<Type>
     * An optional Type(Class name) defined by a (at)PhanClosureScope
     * directive specifying a single type.
     */
    public function getClosureScopeOption()
    {
        return $this->closure_scope;
    }
    /**
     * @return bool
     * True if this Func's doc block contains a (at)return
     * directive specifying a single type.
     */
    public function hasClosureScope()
    {
        $ret5902c6f58883b = isset($this->closure_scope);
        if (!is_bool($ret5902c6f58883b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f58883b) . " given");
        }
        return $ret5902c6f58883b;
    }
    /**
     * @return FullyQualifiedFunctionName
     */
    public function getFQSEN()
    {
        $ret5902c6f588aa7 = $this->fqsen;
        if (!$ret5902c6f588aa7 instanceof FullyQualifiedFunctionName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedFunctionName, " . (gettype($ret5902c6f588aa7) == "object" ? get_class($ret5902c6f588aa7) : gettype($ret5902c6f588aa7)) . " given");
        }
        return $ret5902c6f588aa7;
    }
    /**
     * @return \Generator
     * The set of all alternates to this function
     */
    public function alternateGenerator(CodeBase $code_base)
    {
        $alternate_id = 0;
        $fqsen = $this->getFQSEN();
        while ($code_base->hasFunctionWithFQSEN($fqsen)) {
            (yield $code_base->getFunctionByFQSEN($fqsen));
            $fqsen = $fqsen->withAlternateId(++$alternate_id);
        }
    }
    /**
     * @return string
     * A string representation of this function signature
     */
    public function __toString()
    {
        $string = '';
        $string .= 'function ' . $this->getName();
        $string .= '(' . implode(', ', $this->getParameterList()) . ')';
        if (!$this->getUnionType()->isEmpty()) {
            $string .= ' : ' . (string) $this->getUnionType();
        }
        $string .= ';';
        $ret5902c6f588fb4 = $string;
        if (!is_string($ret5902c6f588fb4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f588fb4) . " given");
        }
        return $ret5902c6f588fb4;
    }
}