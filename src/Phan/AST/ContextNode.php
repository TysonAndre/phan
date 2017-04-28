<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\AST;

use Phan\CodeBase;
use Phan\Config;
use Phan\Exception\CodeBaseException;
use Phan\Exception\IssueException;
use Phan\Exception\NodeException;
use Phan\Exception\TypeException;
use Phan\Exception\UnanalyzableException;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\Element\ClassConstant;
use Phan\Language\Element\Clazz;
use Phan\Language\Element\Func;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Element\GlobalConstant;
use Phan\Language\Element\Method;
use Phan\Language\Element\Property;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedGlobalConstantName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\FQSEN\FullyQualifiedPropertyName;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\ObjectType;
use Phan\Language\Type\StringType;
use Phan\Language\UnionType;
use Phan\Library\None;
use Phan\Library\Some;
use ast\Node;
/**
 * Methods for an AST node in context
 */
class ContextNode
{
    /** @var CodeBase */
    private $code_base;
    /** @var Context */
    private $context;
    /** @var Node|string|null */
    private $node;
    /**
     * @param CodeBase $code_base
     * @param Context $context
     * @param Node|string|null $node
     */
    public function __construct(CodeBase $code_base, Context $context, $node)
    {
        $this->code_base = $code_base;
        $this->context = $context;
        $this->node = $node;
    }
    /**
     * Get a list of fully qualified names from a node
     *
     * @return string[]
     */
    public function getQualifiedNameList()
    {
        if (!$this->node instanceof Node) {
            $ret5902c6f2ddd7d = [];
            if (!is_array($ret5902c6f2ddd7d)) {
                throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f2ddd7d) . " given");
            }
            return $ret5902c6f2ddd7d;
        }
        $ret5902c6f2de189 = array_map(function ($name_node) {
            return (new ContextNode($this->code_base, $this->context, $name_node))->getQualifiedName();
        }, call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$this->node->children, @[]));
        if (!is_array($ret5902c6f2de189)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f2de189) . " given");
        }
        return $ret5902c6f2de189;
    }
    /**
     * Get a fully qualified name form a node
     *
     * @return string
     */
    public function getQualifiedName()
    {
        $ret5902c6f2de3f8 = $this->getClassUnionType()->__toString();
        if (!is_string($ret5902c6f2de3f8)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2de3f8) . " given");
        }
        return $ret5902c6f2de3f8;
    }
    /**
     * @return string
     * A variable name associated with the given node
     */
    public function getVariableName()
    {
        if (!$this->node instanceof \ast\Node) {
            $ret5902c6f2de6de = (string) $this->node;
            if (!is_string($ret5902c6f2de6de)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2de6de) . " given");
            }
            return $ret5902c6f2de6de;
        }
        $node = $this->node;
        $parent = $node;
        while ($node instanceof \ast\Node && $node->kind != \ast\AST_VAR && $node->kind != \ast\AST_STATIC && $node->kind != \ast\AST_MAGIC_CONST) {
            $parent = $node;
            $node = array_values(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->children, @[]))[0];
        }
        if (!$node instanceof \ast\Node) {
            $ret5902c6f2deab7 = (string) $node;
            if (!is_string($ret5902c6f2deab7)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2deab7) . " given");
            }
            return $ret5902c6f2deab7;
        }
        if (empty($node->children['name'])) {
            $ret5902c6f2ded2b = '';
            if (!is_string($ret5902c6f2ded2b)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2ded2b) . " given");
            }
            return $ret5902c6f2ded2b;
        }
        if ($node->children['name'] instanceof \ast\Node) {
            $ret5902c6f2def9c = '';
            if (!is_string($ret5902c6f2def9c)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2def9c) . " given");
            }
            return $ret5902c6f2def9c;
        }
        $ret5902c6f2df1f1 = (string) $node->children['name'];
        if (!is_string($ret5902c6f2df1f1)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2df1f1) . " given");
        }
        return $ret5902c6f2df1f1;
    }
    /**
     * @return UnionType the union type of the class for this class node. (Should have just one Type)
     */
    public function getClassUnionType()
    {
        $ret5902c6f2df655 = UnionTypeVisitor::unionTypeFromClassNode($this->code_base, $this->context, $this->node);
        if (!$ret5902c6f2df655 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f2df655) == "object" ? get_class($ret5902c6f2df655) : gettype($ret5902c6f2df655)) . " given");
        }
        return $ret5902c6f2df655;
    }
    /**
     * @param bool $ignore_missing_classes
     * If set to true, missing classes will be ignored and
     * exceptions will be inhibited
     *
     * @return Clazz[]
     * A list of classes representing the non-native types
     * associated with the given node
     *
     * @throws CodeBaseException
     * An exception is thrown if a non-native type does not have
     * an associated class
     */
    public function getClassList($ignore_missing_classes = false)
    {
        $union_type = $this->getClassUnionType();
        $class_list = [];
        if ($ignore_missing_classes) {
            try {
                foreach ($union_type->asClassList($this->code_base, $this->context) as $i => $clazz) {
                    $class_list[] = $clazz;
                }
            } catch (CodeBaseException $exception) {
                // swallow it
            }
        } else {
            foreach ($union_type->asClassList($this->code_base, $this->context) as $i => $clazz) {
                $class_list[] = $clazz;
            }
        }
        return $class_list;
    }
    /**
     * @param Node|string $method_name
     * Either then name of the method or a node that
     * produces the name of the method.
     *
     * @param bool $is_static
     * Set to true if this is a static method call
     *
     * @return Method
     * A method with the given name on the class referenced
     * from the given node
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     *
     * @throws CodeBaseExtension
     * An exception is thrown if we can't find the given
     * method
     *
     * @throws TypeException
     * An exception may be thrown if the only viable candidate
     * is a non-class type.
     *
     * @throws IssueException
     */
    public function getMethod($method_name, $is_static)
    {
        if (!is_bool($is_static)) {
            throw new \InvalidArgumentException("Argument \$is_static passed to getMethod() must be of the type bool, " . (gettype($is_static) == "object" ? get_class($is_static) : gettype($is_static)) . " given");
        }
        if ($method_name instanceof Node) {
            // The method_name turned out to be a variable.
            // There isn't much we can do to figure out what
            // it's referring to.
            throw new NodeException($method_name, "Unexpected method node");
        }
        assert(is_string($method_name), "Method name must be a string. Found non-string in context.");
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->children['expr'], @$this->node->children['class'])))->getClassList();
        } catch (CodeBaseException $exception) {
            $issue_creator = Issue::fromType(Issue::UndeclaredClassMethod);
            throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), [$method_name, (string) $exception->getFQSEN()]));
        }
        // If there were no classes on the left-type, figure
        // out what we were trying to call the method on
        // and send out an error.
        if (empty($class_list)) {
            $union_type = UnionTypeVisitor::unionTypeFromClassNode($this->code_base, $this->context, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->children['expr'], @$this->node->children['class']));
            if (!$union_type->isEmpty() && $union_type->isNativeType() && !$union_type->hasAnyType([MixedType::instance(false), ObjectType::instance(false), StringType::instance(false)]) && !(Config::get()->null_casts_as_any_type && $union_type->hasType(NullType::instance(false)))) {
                $issue_creator = Issue::fromType(Issue::NonClassMethodCall);
                throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [$method_name, (string) $union_type]));
            }
            throw new NodeException($this->node, "Can't figure out method call for {$method_name}");
        }
        // Hunt to see if any of them have the method we're
        // looking for
        foreach ($class_list as $i => $class) {
            if ($class->hasMethodWithName($this->code_base, $method_name)) {
                $ret5902c6f2e0d19 = $class->getMethodByNameInContext($this->code_base, $method_name, $this->context);
                if (!$ret5902c6f2e0d19 instanceof Method) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f2e0d19) == "object" ? get_class($ret5902c6f2e0d19) : gettype($ret5902c6f2e0d19)) . " given");
                }
                return $ret5902c6f2e0d19;
            } else {
                if (!$is_static && $class->allowsCallingUndeclaredInstanceMethod($this->code_base)) {
                    $ret5902c6f2e109b = $class->getCallMethod($this->code_base);
                    if (!$ret5902c6f2e109b instanceof Method) {
                        throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f2e109b) == "object" ? get_class($ret5902c6f2e109b) : gettype($ret5902c6f2e109b)) . " given");
                    }
                    return $ret5902c6f2e109b;
                } else {
                    if ($is_static && $class->allowsCallingUndeclaredStaticMethod($this->code_base)) {
                        $ret5902c6f2e13e5 = $class->getCallStaticMethod($this->code_base);
                        if (!$ret5902c6f2e13e5 instanceof Method) {
                            throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f2e13e5) == "object" ? get_class($ret5902c6f2e13e5) : gettype($ret5902c6f2e13e5)) . " given");
                        }
                        return $ret5902c6f2e13e5;
                    }
                }
            }
        }
        // Figure out an FQSEN for the method we couldn't find
        $method_fqsen = FullyQualifiedMethodName::make($class_list[0]->getFQSEN(), $method_name);
        if ($is_static) {
            $issue_creator = Issue::fromType(Issue::UndeclaredStaticMethod);
            throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), [(string) $method_fqsen]));
        }
        $issue_creator = Issue::fromType(Issue::UndeclaredMethod);
        throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$this->node->lineno, @0), [(string) $method_fqsen]));
    }
    /**
     * @param string $function_name
     * The name of the function we'd like to look up
     *
     * @param bool $is_function_declaration
     * This must be set to true if we're getting a function
     * that is being declared and false if we're getting a
     * function being called.
     *
     * @return FunctionInterface
     * A method with the given name in the given context
     *
     * @throws IssueException
     * An exception is thrown if we can't find the given
     * function
     */
    public function getFunction($function_name, $is_function_declaration = false)
    {
        if (!is_string($function_name)) {
            throw new \InvalidArgumentException("Argument \$function_name passed to getFunction() must be of the type string, " . (gettype($function_name) == "object" ? get_class($function_name) : gettype($function_name)) . " given");
        }
        if (!is_bool($is_function_declaration)) {
            throw new \InvalidArgumentException("Argument \$is_function_declaration passed to getFunction() must be of the type bool, " . (gettype($is_function_declaration) == "object" ? get_class($is_function_declaration) : gettype($is_function_declaration)) . " given");
        }
        if ($is_function_declaration) {
            $function_fqsen = FullyQualifiedFunctionName::make($this->context->getNamespace(), $function_name);
        } else {
            $function_fqsen = FullyQualifiedFunctionName::make($this->context->getNamespace(), $function_name);
            // If it doesn't exist in the local namespace, try it
            // in the global namespace
            if (!$this->code_base->hasFunctionWithFQSEN($function_fqsen)) {
                $function_fqsen = FullyQualifiedFunctionName::fromStringInContext($function_name, $this->context);
            }
        }
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        // Make sure the method we're calling actually exists
        if (!$this->code_base->hasFunctionWithFQSEN($function_fqsen)) {
            $issue_creator = Issue::fromType(Issue::UndeclaredFunction);
            throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), ["{$function_fqsen}()"]));
        }
        $ret5902c6f2e1ea3 = $this->code_base->getFunctionByFQSEN($function_fqsen);
        if (!$ret5902c6f2e1ea3 instanceof FunctionInterface) {
            throw new \InvalidArgumentException("Argument returned must be of the type FunctionInterface, " . (gettype($ret5902c6f2e1ea3) == "object" ? get_class($ret5902c6f2e1ea3) : gettype($ret5902c6f2e1ea3)) . " given");
        }
        return $ret5902c6f2e1ea3;
    }
    /**
     * @return Variable
     * A variable in scope or a new variable
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     *
     * @throws IssueException
     * A IssueException is thrown if the variable doesn't
     * exist
     */
    public function getVariable()
    {
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        // Get the name of the variable
        $variable_name = $this->getVariableName();
        if (empty($variable_name)) {
            throw new NodeException($this->node, "Variable name not found");
        }
        // Check to see if the variable exists in this scope
        if (!$this->context->getScope()->hasVariableWithName($variable_name)) {
            $issue_creator = Issue::fromType(Issue::UndeclaredVariable);
            throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), [$variable_name]));
        }
        $ret5902c6f2e28b6 = $this->context->getScope()->getVariableByName($variable_name);
        if (!$ret5902c6f2e28b6 instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6f2e28b6) == "object" ? get_class($ret5902c6f2e28b6) : gettype($ret5902c6f2e28b6)) . " given");
        }
        return $ret5902c6f2e28b6;
    }
    /**
     * @return Variable
     * A variable in scope or a new variable
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     */
    public function getOrCreateVariable()
    {
        try {
            $ret5902c6f2e2bff = $this->getVariable();
            if (!$ret5902c6f2e2bff instanceof Variable) {
                throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6f2e2bff) == "object" ? get_class($ret5902c6f2e2bff) : gettype($ret5902c6f2e2bff)) . " given");
            }
            return $ret5902c6f2e2bff;
        } catch (IssueException $exception) {
            // Swallow it
        }
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        // Create a new variable
        $variable = Variable::fromNodeInContext($this->node, $this->context, $this->code_base, false);
        $this->context->addScopeVariable($variable);
        $ret5902c6f2e2fcf = $variable;
        if (!$ret5902c6f2e2fcf instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6f2e2fcf) == "object" ? get_class($ret5902c6f2e2fcf) : gettype($ret5902c6f2e2fcf)) . " given");
        }
        return $ret5902c6f2e2fcf;
    }
    /**
     * @param string|Node $property_name
     * The name of the property we're looking up
     *
     * @param bool $is_static
     * True if we're looking for a static property,
     * false if we're looking for an instance property.
     *
     * @return Property
     * A variable in scope or a new variable
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     *
     * @throws IssueException
     * An exception is thrown if we can't find the given
     * class or if we don't have access to the property (its
     * private or protected)
     * or if the property is static and missing.
     *
     * @throws TypeException
     * An exception may be thrown if the only viable candidate
     * is a non-class type.
     *
     * @throws UnanalyzableException
     * An exception is thrown if we hit a construct in which
     * we can't determine if the property exists or not
     */
    public function getProperty($property_name, $is_static)
    {
        if (!is_bool($is_static)) {
            throw new \InvalidArgumentException("Argument \$is_static passed to getProperty() must be of the type bool, " . (gettype($is_static) == "object" ? get_class($is_static) : gettype($is_static)) . " given");
        }
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        $property_name = $this->node->children['prop'];
        // Give up for things like C::$prop_name
        if (!is_string($property_name)) {
            throw new NodeException($this->node, "Cannot figure out non-string property name");
        }
        $class_fqsen = null;
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->children['expr'], @$this->node->children['class'])))->getClassList(true);
        } catch (CodeBaseException $exception) {
            if ($is_static) {
                $issue_creator = Issue::fromType(Issue::UndeclaredStaticProperty);
                throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [$property_name, (string) $exception->getFQSEN()]));
            } else {
                $issue_creator = Issue::fromType(Issue::UndeclaredProperty);
                throw new IssueException($issue_creator($this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), ["{$exception->getFQSEN()}->{$property_name}"]));
            }
        }
        foreach ($class_list as $i => $class) {
            $class_fqsen = $class->getFQSEN();
            // Keep hunting if this class doesn't have the given
            // property
            if (!$class->hasPropertyWithName($this->code_base, $property_name)) {
                // (if fetching an instance property)
                // If there's a getter on properties then all
                // bets are off. However, @phan-forbid-undeclared-magic-properties
                // will make this method analyze the code as if all properties were declared or had @property annotations.
                if (!$is_static && $class->hasGetMethod($this->code_base) && !$class->getForbidUndeclaredMagicProperties($this->code_base)) {
                    throw new UnanalyzableException($this->node, "Can't determine if property {$property_name} exists in class {$class->getFQSEN()} with __get defined");
                }
                continue;
            }
            $property = $class->getPropertyByNameInContext($this->code_base, $property_name, $this->context, $is_static);
            if ($property->isDeprecated()) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::DeprecatedProperty, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [(string) $property->getFQSEN(), $property->getFileRef()->getFile(), $property->getFileRef()->getLineNumberStart()]));
            }
            if ($property->isNSInternal($this->code_base) && !$property->isNSInternalAccessFromContext($this->code_base, $this->context)) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::AccessPropertyInternal, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [(string) $property->getFQSEN(), $property->getFileRef()->getFile(), $property->getFileRef()->getLineNumberStart()]));
            }
            $ret5902c6f2e3bd0 = $property;
            if (!$ret5902c6f2e3bd0 instanceof Property) {
                throw new \InvalidArgumentException("Argument returned must be of the type Property, " . (gettype($ret5902c6f2e3bd0) == "object" ? get_class($ret5902c6f2e3bd0) : gettype($ret5902c6f2e3bd0)) . " given");
            }
            return $ret5902c6f2e3bd0;
        }
        // Since we didn't find the property on any of the
        // possible classes, check for classes with dynamic
        // properties
        if (!$is_static) {
            foreach ($class_list as $i => $class) {
                if (Config::get()->allow_missing_properties || $class->getHasDynamicProperties($this->code_base)) {
                    $ret5902c6f2e3f6e = $class->getPropertyByNameInContext($this->code_base, $property_name, $this->context, $is_static);
                    if (!$ret5902c6f2e3f6e instanceof Property) {
                        throw new \InvalidArgumentException("Argument returned must be of the type Property, " . (gettype($ret5902c6f2e3f6e) == "object" ? get_class($ret5902c6f2e3f6e) : gettype($ret5902c6f2e3f6e)) . " given");
                    }
                    return $ret5902c6f2e3f6e;
                }
            }
        }
        /*
        $std_class_fqsen =
            FullyQualifiedClassName::getStdClassFQSEN();

        // If missing properties are cool, create it on
        // the first class we found
        if (!$is_static && ($class_fqsen && ($class_fqsen === $std_class_fqsen))
            || Config::get()->allow_missing_properties
        ) {
            if (count($class_list) > 0) {
                $class = $class_list[0];
                return $class->getPropertyByNameInContext(
                    $this->code_base,
                    $property_name,
                    $this->context,
                    $is_static
                );
            }
        }
        */
        // If the class isn't found, we'll get the message elsewhere
        if ($class_fqsen) {
            if ($is_static) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredStaticProperty, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [$property_name, (string) $class_fqsen]));
            } else {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredProperty, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), ["{$class_fqsen}->{$property_name}"]));
            }
        }
        throw new NodeException($this->node, "Cannot figure out property from {$this->context}");
    }
    /**
     * @return Property
     * A variable in scope or a new variable
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     *
     * @throws UnanalyzableException
     * An exception is thrown if we can't find the given
     * class
     *
     * @throws CodeBaseExtension
     * An exception is thrown if we can't find the given
     * class
     *
     * @throws TypeException
     * An exception may be thrown if the only viable candidate
     * is a non-class type.
     *
     * @throws IssueException
     * An exception is thrown if $is_static, but the property doesn't exist.
     */
    public function getOrCreateProperty($property_name, $is_static)
    {
        if (!is_string($property_name)) {
            throw new \InvalidArgumentException("Argument \$property_name passed to getOrCreateProperty() must be of the type string, " . (gettype($property_name) == "object" ? get_class($property_name) : gettype($property_name)) . " given");
        }
        if (!is_bool($is_static)) {
            throw new \InvalidArgumentException("Argument \$is_static passed to getOrCreateProperty() must be of the type bool, " . (gettype($is_static) == "object" ? get_class($is_static) : gettype($is_static)) . " given");
        }
        try {
            $ret5902c6f2e4749 = $this->getProperty($property_name, $is_static);
            if (!$ret5902c6f2e4749 instanceof Property) {
                throw new \InvalidArgumentException("Argument returned must be of the type Property, " . (gettype($ret5902c6f2e4749) == "object" ? get_class($ret5902c6f2e4749) : gettype($ret5902c6f2e4749)) . " given");
            }
            return $ret5902c6f2e4749;
        } catch (IssueException $exception) {
            if ($is_static) {
                throw $exception;
            }
            // TODO: log types of IssueException that aren't for undeclared properties?
            // (in another PR)
            // For instance properties, ignore it,
            // because we'll create our own property
        } catch (UnanalyzableException $exception) {
            if ($is_static) {
                throw $exception;
            }
            // For instance properties, ignore it,
            // because we'll create our own property
        }
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->children['expr'], @null)))->getClassList();
        } catch (CodeBaseException $exception) {
            throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredClassReference, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), [$exception->getFQSEN()]));
        }
        if (empty($class_list)) {
            throw new UnanalyzableException($this->node, "Could not get class name from node");
        }
        $class = array_values($class_list)[0];
        $flags = 0;
        if ($this->node->kind == \ast\AST_STATIC_PROP) {
            $flags |= \ast\flags\MODIFIER_STATIC;
        }
        $property_fqsen = FullyQualifiedPropertyName::make($class->getFQSEN(), $property_name);
        // Otherwise, we'll create it
        $property = new Property($this->context, $property_name, new UnionType(), $flags, $property_fqsen);
        $class->addProperty($this->code_base, $property, new None());
        $ret5902c6f2e4f33 = $property;
        if (!$ret5902c6f2e4f33 instanceof Property) {
            throw new \InvalidArgumentException("Argument returned must be of the type Property, " . (gettype($ret5902c6f2e4f33) == "object" ? get_class($ret5902c6f2e4f33) : gettype($ret5902c6f2e4f33)) . " given");
        }
        return $ret5902c6f2e4f33;
    }
    /**
     * @return GlobalConstant
     * Get the (non-class) constant associated with this node
     * in this context
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     *
     * @throws CodeBaseExtension
     * An exception is thrown if we can't find the given
     * class
     */
    public function getConst()
    {
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        assert($this->node->kind === \ast\AST_CONST, "Node must be of type \\ast\\AST_CONST");
        if ($this->node->children['name']->kind !== \ast\AST_NAME) {
            throw new NodeException($this->node, "Can't determine constant name");
        }
        $constant_name = $this->node->children['name']->children['name'];
        $fqsen = FullyQualifiedGlobalConstantName::fromStringInContext($constant_name, $this->context);
        if (!$this->code_base->hasGlobalConstantWithFQSEN($fqsen)) {
            $fqsen = FullyQualifiedGlobalConstantName::fromFullyQualifiedString($constant_name);
            if (!$this->code_base->hasGlobalConstantWithFQSEN($fqsen)) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredConstant, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [$fqsen]));
            }
        }
        $constant = $this->code_base->getGlobalConstantByFQSEN($fqsen);
        if ($constant->isNSInternal($this->code_base) && !$constant->isNSInternalAccessFromContext($this->code_base, $this->context)) {
            throw new IssueException(Issue::fromTypeAndInvoke(Issue::AccessConstantInternal, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), [(string) $constant->getFQSEN(), $constant->getFileRef()->getFile(), $constant->getFileRef()->getLineNumberStart()]));
        }
        $ret5902c6f2e5bc7 = $constant;
        if (!$ret5902c6f2e5bc7 instanceof GlobalConstant) {
            throw new \InvalidArgumentException("Argument returned must be of the type GlobalConstant, " . (gettype($ret5902c6f2e5bc7) == "object" ? get_class($ret5902c6f2e5bc7) : gettype($ret5902c6f2e5bc7)) . " given");
        }
        return $ret5902c6f2e5bc7;
    }
    /**
     * @return ClassConstant
     * Get the (non-class) constant associated with this node
     * in this context
     *
     * @throws NodeException
     * An exception is thrown if we can't understand the node
     *
     * @throws CodeBaseExtension
     * An exception is thrown if we can't find the given
     * class
     *
     * @throws UnanalyzableException
     * An exception is thrown if we hit a construct in which
     * we can't determine if the property exists or not
     *
     * @throws IssueException
     * An exception is thrown if an issue is found while getting
     * the list of possible classes.
     */
    public function getClassConst()
    {
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        assert($this->node->kind === \ast\AST_CLASS_CONST, "Node must be of type \\ast\\AST_CLASS_CONST");
        $constant_name = $this->node->children['const'];
        $class_fqsen = null;
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, $this->node->children['class']))->getClassList();
        } catch (CodeBaseException $exception) {
            throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredClassConstant, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), [$constant_name, $exception->getFQSEN()]));
        }
        foreach ($class_list as $i => $class) {
            $class_fqsen = $class->getFQSEN();
            // Check to see if the class has the constant
            if (!$class->hasConstantWithName($this->code_base, $constant_name)) {
                continue;
            }
            $constant = $class->getConstantByNameInContext($this->code_base, $constant_name, $this->context);
            if ($constant->isNSInternal($this->code_base) && !$constant->isNSInternalAccessFromContext($this->code_base, $this->context)) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::AccessClassConstantInternal, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0), [(string) $constant->getFQSEN(), $constant->getFileRef()->getFile(), $constant->getFileRef()->getLineNumberStart()]));
            }
            $ret5902c6f2e63e5 = $constant;
            if (!$ret5902c6f2e63e5 instanceof ClassConstant) {
                throw new \InvalidArgumentException("Argument returned must be of the type ClassConstant, " . (gettype($ret5902c6f2e63e5) == "object" ? get_class($ret5902c6f2e63e5) : gettype($ret5902c6f2e63e5)) . " given");
            }
            return $ret5902c6f2e63e5;
        }
        // If no class is found, we'll emit the error elsewhere
        if ($class_fqsen) {
            throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredConstant, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$this->node->lineno, @0), ["{$class_fqsen}::{$constant_name}"]));
        }
        throw new NodeException($this->node, "Can't figure out constant {$constant_name} in node");
    }
    /**
     * @return string
     * A unique and stable name for an anonymous class
     */
    public function getUnqualifiedNameForAnonymousClass()
    {
        assert($this->node instanceof \ast\Node, '$this->node must be a node');
        assert((bool) ($this->node->flags & \ast\flags\CLASS_ANONYMOUS), "Node must be an anonymous class node");
        $class_name = 'anonymous_class_' . substr(md5(implode('|', [$this->context->getFile(), $this->context->getLineNumberStart()])), 0, 8);
        $ret5902c6f2e69e3 = $class_name;
        if (!is_string($ret5902c6f2e69e3)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f2e69e3) . " given");
        }
        return $ret5902c6f2e69e3;
    }
    /**
     * @return Func
     */
    public function getClosure()
    {
        $closure_fqsen = FullyQualifiedFunctionName::fromClosureInContext($this->context);
        if (!$this->code_base->hasFunctionWithFQSEN($closure_fqsen)) {
            throw new CodeBaseException($closure_fqsen, "Could not find closure {$closure_fqsen}");
        }
        $ret5902c6f2e6d2a = $this->code_base->getFunctionByFQSEN($closure_fqsen);
        if (!$ret5902c6f2e6d2a instanceof Func) {
            throw new \InvalidArgumentException("Argument returned must be of the type Func, " . (gettype($ret5902c6f2e6d2a) == "object" ? get_class($ret5902c6f2e6d2a) : gettype($ret5902c6f2e6d2a)) . " given");
        }
        return $ret5902c6f2e6d2a;
    }
    /**
     * Perform some backwards compatibility checks on a node
     *
     * @return void
     */
    public function analyzeBackwardCompatibility()
    {
        if (!Config::get()->backward_compatibility_checks) {
            return;
        }
        if (!$this->node instanceof \ast\Node || empty($this->node->children['expr'])) {
            return;
        }
        if ($this->node->kind === \ast\AST_STATIC_CALL || $this->node->kind === \ast\AST_METHOD_CALL) {
            return;
        }
        $llnode = $this->node;
        if ($this->node->kind !== \ast\AST_DIM) {
            if (!$this->node->children['expr'] instanceof Node) {
                return;
            }
            if ($this->node->children['expr']->kind !== \ast\AST_DIM) {
                (new ContextNode($this->code_base, $this->context, $this->node->children['expr']))->analyzeBackwardCompatibility();
                return;
            }
            $temp = $this->node->children['expr']->children['expr'];
            $llnode = $this->node->children['expr'];
            $lnode = $temp;
        } else {
            $temp = $this->node->children['expr'];
            $lnode = $temp;
        }
        // Strings can have DIMs, it turns out.
        if (!$temp instanceof Node) {
            return;
        }
        if (!($temp->kind == \ast\AST_PROP || $temp->kind == \ast\AST_STATIC_PROP)) {
            return;
        }
        while ($temp instanceof Node && ($temp->kind == \ast\AST_PROP || $temp->kind == \ast\AST_STATIC_PROP)) {
            $llnode = $lnode;
            $lnode = $temp;
            // Lets just hope the 0th is the expression
            // we want
            $temp = array_values($temp->children)[0];
        }
        if (!$temp instanceof Node) {
            return;
        }
        // Foo::$bar['baz'](); is a problem
        // Foo::$bar['baz'] is not
        if ($lnode->kind === \ast\AST_STATIC_PROP && $this->node->kind !== \ast\AST_CALL) {
            return;
        }
        // $this->$bar['baz']; is a problem
        // $this->bar['baz'] is not
        if ($lnode->kind === \ast\AST_PROP && !$lnode->children['prop'] instanceof Node && !$llnode->children['prop'] instanceof Node) {
            return;
        }
        if (($lnode->children['prop'] instanceof Node && $lnode->children['prop']->kind == \ast\AST_VAR || !empty($lnode->children['class']) && $lnode->children['class'] instanceof Node && ($lnode->children['class']->kind == \ast\AST_VAR || $lnode->children['class']->kind == \ast\AST_NAME) || !empty($lnode->children['expr']) && $lnode->children['expr'] instanceof Node && ($lnode->children['expr']->kind == \ast\AST_VAR || $lnode->children['expr']->kind == \ast\AST_NAME)) && ($temp->kind == \ast\AST_VAR || $temp->kind == \ast\AST_NAME)) {
            $ftemp = new \SplFileObject($this->context->getFile());
            $ftemp->seek($this->node->lineno - 1);
            $line = $ftemp->current();
            assert(is_string($line));
            unset($ftemp);
            if (strpos($line, '}[') === false || strpos($line, ']}') === false || strpos($line, '>{') === false) {
                Issue::maybeEmit($this->code_base, $this->context, Issue::CompatiblePHP7, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$this->node->lineno, @0));
            }
        }
    }
}