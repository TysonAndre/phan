<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Config;
use Phan\Exception\CodeBaseException;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Scope\FunctionLikeScope;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\UnionType;
use ast\Node;
use ast\Node\Decl;
class Method extends ClassElement implements FunctionInterface
{
    use \Phan\Analysis\Analyzable;
    use \Phan\Memoize;
    use FunctionTrait;
    use ClosedScopeElement;
    /**
     * @param Context $context
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
     *
     * @param FullyQualifiedMethodName $fqsen
     * A fully qualified name for the element
     */
    public function __construct(Context $context, $name, UnionType $type, $flags, FullyQualifiedMethodName $fqsen)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to __construct() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        parent::__construct($context, $name, $type, $flags, $fqsen);
        // Presume that this is the original definition
        // of this method, and let it be overwritten
        // if it isn't.
        $this->setDefiningFQSEN($fqsen);
        $this->setInternalScope(new FunctionLikeScope($context->getScope(), $fqsen));
    }
    /**
     * @return bool
     * True if this is an abstract method
     */
    public function isAbstract()
    {
        $ret5902c6f5cbede = Flags::bitVectorHasState($this->getFlags(), \ast\flags\MODIFIER_ABSTRACT);
        if (!is_bool($ret5902c6f5cbede)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5cbede) . " given");
        }
        return $ret5902c6f5cbede;
    }
    /**
     * @return bool
     * True if this method returns reference
     */
    public function returnsRef()
    {
        $ret5902c6f5cc16f = Flags::bitVectorHasState($this->getFlags(), \ast\flags\RETURNS_REF);
        if (!is_bool($ret5902c6f5cc16f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5cc16f) . " given");
        }
        return $ret5902c6f5cc16f;
    }
    /**
     * @return bool
     * True if this is a magic method
     */
    public function getIsMagic()
    {
        $ret5902c6f5cc4fc = in_array($this->getName(), ['__call', '__callStatic', '__clone', '__construct', '__debugInfo', '__destruct', '__get', '__invoke', '__isset', '__set', '__set_state', '__sleep', '__toString', '__unset', '__wakeup']);
        if (!is_bool($ret5902c6f5cc4fc)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5cc4fc) . " given");
        }
        return $ret5902c6f5cc4fc;
    }
    /**
     * @return bool
     * True if this is the magic `__call` method
     */
    public function getIsMagicCall()
    {
        $ret5902c6f5cc764 = $this->getName() === '__call';
        if (!is_bool($ret5902c6f5cc764)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5cc764) . " given");
        }
        return $ret5902c6f5cc764;
    }
    /**
     * @return bool
     * True if this is the magic `__callStatic` method
     */
    public function getIsMagicCallStatic()
    {
        $ret5902c6f5cc9d0 = $this->getName() === '__callStatic';
        if (!is_bool($ret5902c6f5cc9d0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5cc9d0) . " given");
        }
        return $ret5902c6f5cc9d0;
    }
    /**
     * @return bool
     * True if this is the magic `__get` method
     */
    public function getIsMagicGet()
    {
        $ret5902c6f5ccc82 = $this->getName() === '__get';
        if (!is_bool($ret5902c6f5ccc82)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5ccc82) . " given");
        }
        return $ret5902c6f5ccc82;
    }
    /**
     * @return bool
     * True if this is the magic `__set` method
     */
    public function getIsMagicSet()
    {
        $ret5902c6f5ccee7 = $this->getName() === '__set';
        if (!is_bool($ret5902c6f5ccee7)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5ccee7) . " given");
        }
        return $ret5902c6f5ccee7;
    }
    /**
     * @return Method
     * A default constructor for the given class
     */
    public static function defaultConstructorForClassInContext(Clazz $clazz, Context $context, CodeBase $code_base)
    {
        $method_fqsen = FullyQualifiedMethodName::make($clazz->getFQSEN(), '__construct');
        $method = new Method($context, '__construct', $clazz->getUnionType(), 0, $method_fqsen);
        if ($clazz->hasMethodWithName($code_base, $clazz->getName())) {
            $old_style_constructor = $clazz->getMethodByName($code_base, $clazz->getName());
            $parameter_list = $old_style_constructor->getParameterList();
            $method->setParameterList($parameter_list);
            $method->setRealParameterList($parameter_list);
            $method->setNumberOfRequiredParameters($old_style_constructor->getNumberOfRequiredParameters());
            $method->setNumberOfOptionalParameters($old_style_constructor->getNumberOfOptionalParameters());
        }
        $ret5902c6f5cd37e = $method;
        if (!$ret5902c6f5cd37e instanceof Method) {
            throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f5cd37e) == "object" ? get_class($ret5902c6f5cd37e) : gettype($ret5902c6f5cd37e)) . " given");
        }
        return $ret5902c6f5cd37e;
    }
    /**
     * @param Context $context
     * The context in which the node appears
     *
     * @param CodeBase $code_base
     *
     * @param Decl $node
     * An AST node representing a method
     *
     * @return Method
     * A Method representing the AST node in the
     * given context
     */
    public static function fromNode(Context $context, CodeBase $code_base, Decl $node, FullyQualifiedMethodName $fqsen)
    {
        // Create the skeleton method object from what
        // we know so far
        $method = new Method($context, (string) $node->name, new UnionType(), call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0), $fqsen);
        // Parse the comment above the method to get
        // extra meta information about the method.
        $comment = Comment::fromStringInContext(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->docComment, @''), $context);
        // @var Parameter[]
        // The list of parameters specified on the
        // method
        $parameter_list = Parameter::listFromNode($context, $code_base, $node->children['params']);
        // Add each parameter to the scope of the function
        foreach ($parameter_list as $parameter) {
            $method->getInternalScope()->addVariable($parameter);
        }
        // If the method is Analyzable, set the node so that
        // we can come back to it whenever we like and
        // rescan it
        $method->setNode($node);
        // Set the parameter list on the method
        $method->setParameterList($parameter_list);
        // Keep an copy of the original parameter list, to check for fatal errors later on.
        $method->setRealParameterList($parameter_list);
        $method->setNumberOfRequiredParameters(array_reduce($parameter_list, function ($carry, Parameter $parameter) {
            if (!is_int($carry)) {
                throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
            }
            $ret5902c6f5cda27 = $carry + ($parameter->isRequired() ? 1 : 0);
            if (!is_int($ret5902c6f5cda27)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f5cda27) . " given");
            }
            return $ret5902c6f5cda27;
        }, 0));
        $method->setNumberOfOptionalParameters(array_reduce($parameter_list, function ($carry, Parameter $parameter) {
            if (!is_int($carry)) {
                throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
            }
            $ret5902c6f5cdfea = $carry + ($parameter->isOptional() ? 1 : 0);
            if (!is_int($ret5902c6f5cdfea)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f5cdfea) . " given");
            }
            return $ret5902c6f5cdfea;
        }, 0));
        // Check to see if the comment specifies that the
        // method is deprecated
        $method->setIsDeprecated($comment->isDeprecated());
        // Set whether or not the element is internal to
        // the namespace.
        $method->setIsNSInternal($comment->isNSInternal());
        $method->setSuppressIssueList($comment->getSuppressIssueList());
        if ($method->getIsMagicCall() || $method->getIsMagicCallStatic()) {
            $method->setNumberOfOptionalParameters(999);
            $method->setNumberOfRequiredParameters(0);
        }
        // Add the syntax-level return type to the method's union type
        // if it exists
        $return_union_type = new UnionType();
        if ($node->children['returnType'] !== null) {
            $return_union_type = UnionType::fromNode($context, $code_base, $node->children['returnType']);
            $method->getUnionType()->addUnionType($return_union_type);
        }
        $method->setRealReturnType($return_union_type);
        // If available, add in the doc-block annotated return type
        // for the method.
        if ($comment->hasReturnUnionType()) {
            $comment_return_union_type = $comment->getReturnType();
            if ($comment_return_union_type->hasSelfType()) {
                // We can't actually figure out 'static' at this
                // point, but fill it in regardless. It will be partially
                // correct
                if ($context->isInClassScope()) {
                    // n.b.: We're leaving the reference to self, static
                    //       or $this in the type because I'm guessing
                    //       it doesn't really matter. Apologies if it
                    //       ends up being an issue.
                    $comment_return_union_type->addUnionType($context->getClassFQSEN()->asUnionType());
                }
            }
            if (Config::get()->check_docblock_signature_return_type_match) {
                // Make sure that the commented type is a narrowed
                // or equivalent form of the syntax-level declared
                // return type.
                if (!$comment_return_union_type->isExclusivelyNarrowedFormOrEquivalentTo($return_union_type, $context, $code_base)) {
                    Issue::maybeEmit($code_base, $context, Issue::TypeMismatchDeclaredReturn, call_user_func(function ($v1, $v2) {
                        return isset($v1) ? $v1 : $v2;
                    }, @$node->lineno, @0), $comment_return_union_type->__toString(), $return_union_type->__toString());
                }
            }
            $method->getUnionType()->addUnionType($comment_return_union_type);
        }
        // Add params to local scope for user functions
        FunctionTrait::addParamsToScopeOfFunctionOrMethod($context, $code_base, $node, $method, $comment);
        $ret5902c6f5ce949 = $method;
        if (!$ret5902c6f5ce949 instanceof Method) {
            throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f5ce949) == "object" ? get_class($ret5902c6f5ce949) : gettype($ret5902c6f5ce949)) . " given");
        }
        return $ret5902c6f5ce949;
    }
    /**
     * @param Context $context
     *
     * @return UnionType
     * The type of this method in its given context.
     */
    public function getUnionType()
    {
        $union_type = parent::getUnionType();
        // If the type is 'static', add this context's class
        // to the return type
        if ($union_type->hasStaticType()) {
            $union_type = clone $union_type;
            $union_type->addType($this->getFQSEN()->getFullyQualifiedClassName()->asType());
        }
        // If the type is a generic array of 'static', add
        // a generic array of this context's class to the return type
        if ($union_type->genericArrayElementTypes()->hasStaticType()) {
            $union_type = clone $union_type;
            $union_type->addType($this->getFQSEN()->getFullyQualifiedClassName()->asType()->asGenericArrayType());
        }
        $ret5902c6f5ced7d = $union_type;
        if (!$ret5902c6f5ced7d instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f5ced7d) == "object" ? get_class($ret5902c6f5ced7d) : gettype($ret5902c6f5ced7d)) . " given");
        }
        return $ret5902c6f5ced7d;
    }
    /**
     * @return FullyQualifiedMethodName
     */
    public function getFQSEN()
    {
        $ret5902c6f5cf065 = $this->fqsen;
        if (!$ret5902c6f5cf065 instanceof FullyQualifiedMethodName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedMethodName, " . (gettype($ret5902c6f5cf065) == "object" ? get_class($ret5902c6f5cf065) : gettype($ret5902c6f5cf065)) . " given");
        }
        return $ret5902c6f5cf065;
    }
    /**
     * @return \Generator
     * The set of all alternates to this method
     */
    public function alternateGenerator(CodeBase $code_base)
    {
        $alternate_id = 0;
        $fqsen = $this->getFQSEN();
        while ($code_base->hasMethodWithFQSEN($fqsen)) {
            (yield $code_base->getMethodByFQSEN($fqsen));
            $fqsen = $fqsen->withAlternateId(++$alternate_id);
        }
    }
    /**
     * @param CodeBase $code_base
     * The code base with which to look for classes
     *
     * @return Method
     * The Method that this Method is overriding
     */
    public function getOverriddenMethod(CodeBase $code_base)
    {
        // Get the class that defines this method
        $class = $this->getClass($code_base);
        // Get the list of ancestors of that class
        $ancestor_class_list = $class->getAncestorClassList($code_base);
        // Hunt for any ancestor class that defines a method with
        // the same name as this one
        foreach ($ancestor_class_list as $ancestor_class) {
            if ($ancestor_class->hasMethodWithName($code_base, $this->getName())) {
                $ret5902c6f5cf538 = $ancestor_class->getMethodByName($code_base, $this->getName());
                if (!$ret5902c6f5cf538 instanceof Method) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f5cf538) == "object" ? get_class($ret5902c6f5cf538) : gettype($ret5902c6f5cf538)) . " given");
                }
                return $ret5902c6f5cf538;
            }
        }
        // Throw an exception if this method doesn't override
        // anything
        throw new CodeBaseException($this->getFQSEN(), "Method {$this} with FQSEN {$this->getFQSEN()} does not override another method");
    }
    /**
     * @return string
     * A string representation of this method signature
     */
    public function __toString()
    {
        $string = '';
        $string .= 'function ';
        if ($this->returnsRef()) {
            $string .= '&';
        }
        $string .= $this->getName();
        $string .= '(' . implode(', ', $this->getParameterList()) . ')';
        if (!$this->getUnionType()->isEmpty()) {
            $string .= ' : ' . (string) $this->getUnionType();
        }
        $ret5902c6f5cfa16 = $string;
        if (!is_string($ret5902c6f5cfa16)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f5cfa16) . " given");
        }
        return $ret5902c6f5cfa16;
    }
    /**
     * @return string
     * A string representation of this method signature
     * (Based on real types only, instead of phpdoc+real types)
     */
    public function toRealSignatureString()
    {
        $string = '';
        $string .= 'function ';
        if ($this->returnsRef()) {
            $string .= '&';
        }
        $string .= $this->getName();
        $string .= '(' . implode(', ', $this->getRealParameterList()) . ')';
        if (!$this->getRealReturnType()->isEmpty()) {
            $string .= ' : ' . (string) $this->getRealReturnType();
        }
        $ret5902c6f5cfdc8 = $string;
        if (!is_string($ret5902c6f5cfdc8)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f5cfdc8) . " given");
        }
        return $ret5902c6f5cfdc8;
    }
}