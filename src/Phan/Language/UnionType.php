<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language;

use Phan\AST\UnionTypeVisitor;
use Phan\CodeBase;
use Phan\Config;
use Phan\Exception\CodeBaseException;
use Phan\Exception\IssueException;
use Phan\Issue;
use Phan\Language\Element\Clazz;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\FloatType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\TemplateType;
use Phan\Library\Set;
use ast\Node;
class UnionType implements \Serializable
{
    use \Phan\Memoize;
    /**
     * @var string
     * A list of one or more types delimited by the '|'
     * character (e.g. 'int|DateTime|string[]')
     */
    const union_type_regex = Type::type_regex . '(\\|' . Type::type_regex . ')*';
    /**
     * @var Set
     */
    private $type_set;
    /**
     * @param Type[]|\Iterator|null $type_list
     * An optional list of types represented by this union
     */
    public function __construct($type_list = null)
    {
        $this->type_set = new Set($type_list);
    }
    /**
     * After a clone is called on this object, clone our
     * deep objects.
     *
     * @return null
     */
    public function __clone()
    {
        $set = new Set();
        $set->addAll($this->type_set);
        $this->type_set = $set;
    }
    /**
     * @param string $fully_qualified_string
     * A '|' delimited string representing a type in the form
     * 'int|string|null|ClassName'.
     *
     * @param Context $context
     * The context in which the type string was
     * found
     *
     * @return UnionType
     */
    public static function fromFullyQualifiedString($fully_qualified_string)
    {
        if (!is_string($fully_qualified_string)) {
            throw new \InvalidArgumentException("Argument \$fully_qualified_string passed to fromFullyQualifiedString() must be of the type string, " . (gettype($fully_qualified_string) == "object" ? get_class($fully_qualified_string) : gettype($fully_qualified_string)) . " given");
        }
        if ($fully_qualified_string === '') {
            $ret5902c6fd44d79 = new UnionType();
            if (!$ret5902c6fd44d79 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd44d79) == "object" ? get_class($ret5902c6fd44d79) : gettype($ret5902c6fd44d79)) . " given");
            }
            return $ret5902c6fd44d79;
        }
        $ret5902c6fd453fc = new UnionType(array_map(function ($type_name) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            return Type::fromFullyQualifiedString($type_name);
        }, explode('|', $fully_qualified_string)));
        if (!$ret5902c6fd453fc instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd453fc) == "object" ? get_class($ret5902c6fd453fc) : gettype($ret5902c6fd453fc)) . " given");
        }
        return $ret5902c6fd453fc;
    }
    /**
     * @param string $type_string
     * A '|' delimited string representing a type in the form
     * 'int|string|null|ClassName'.
     *
     * @param Context $context
     * The context in which the type string was
     * found
     *
     * @param bool $is_phpdoc_type
     * True if $type_string was extracted from a doc comment.
     *
     * @return UnionType
     */
    public static function fromStringInContext($type_string, Context $context, $is_phpdoc_type)
    {
        if (!is_string($type_string)) {
            throw new \InvalidArgumentException("Argument \$type_string passed to fromStringInContext() must be of the type string, " . (gettype($type_string) == "object" ? get_class($type_string) : gettype($type_string)) . " given");
        }
        if (!is_bool($is_phpdoc_type)) {
            throw new \InvalidArgumentException("Argument \$is_phpdoc_type passed to fromStringInContext() must be of the type bool, " . (gettype($is_phpdoc_type) == "object" ? get_class($is_phpdoc_type) : gettype($is_phpdoc_type)) . " given");
        }
        if (empty($type_string)) {
            $ret5902c6fd45968 = new UnionType();
            if (!$ret5902c6fd45968 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd45968) == "object" ? get_class($ret5902c6fd45968) : gettype($ret5902c6fd45968)) . " given");
            }
            return $ret5902c6fd45968;
        }
        // If our scope has a generic type identifier defined on it
        // that matches the type string, return that UnionType.
        if ($context->getScope()->hasTemplateType($type_string)) {
            $ret5902c6fd45c7e = $context->getScope()->getTemplateType($type_string)->asUnionType();
            if (!$ret5902c6fd45c7e instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd45c7e) == "object" ? get_class($ret5902c6fd45c7e) : gettype($ret5902c6fd45c7e)) . " given");
            }
            return $ret5902c6fd45c7e;
        }
        $ret5902c6fd468ef = new UnionType(array_map(function ($type_name) use($context, $type_string, $is_phpdoc_type) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            assert($type_name !== '', "Type cannot be empty.");
            return Type::fromStringInContext($type_name, $context, $is_phpdoc_type);
        }, array_filter(array_map(function ($type_name) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            return trim($type_name);
        }, explode('|', $type_string)), function ($type_name) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            // Exclude empty type names
            // Exclude namespaces without type names (e.g. `\`, `\NS\`)
            return $type_name !== '' && preg_match('@\\\\[\\[\\]]*$@', $type_name) === 0;
        })));
        if (!$ret5902c6fd468ef instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd468ef) == "object" ? get_class($ret5902c6fd468ef) : gettype($ret5902c6fd468ef)) . " given");
        }
        return $ret5902c6fd468ef;
    }
    /**
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param CodeBase $code_base
     * The code base within which we're operating
     *
     * @param Node|string|bool|int|float|null $node
     * The node for which we'd like to determine its type
     *
     * @param bool $should_catch_issue_exception
     * Set to true to cause loggable issues to be thrown
     * instead of emitted as issues to the log.
     *
     * @return UnionType
     *
     * @throws IssueException
     * If $should_catch_issue_exception is false an IssueException may
     * be thrown for optional issues.
     */
    public static function fromNode(Context $context, CodeBase $code_base, $node, $should_catch_issue_exception = true)
    {
        if (!is_bool($should_catch_issue_exception)) {
            throw new \InvalidArgumentException("Argument \$should_catch_issue_exception passed to fromNode() must be of the type bool, " . (gettype($should_catch_issue_exception) == "object" ? get_class($should_catch_issue_exception) : gettype($should_catch_issue_exception)) . " given");
        }
        $ret5902c6fd4713b = UnionTypeVisitor::unionTypeFromNode($code_base, $context, $node, $should_catch_issue_exception);
        if (!$ret5902c6fd4713b instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4713b) == "object" ? get_class($ret5902c6fd4713b) : gettype($ret5902c6fd4713b)) . " given");
        }
        return $ret5902c6fd4713b;
    }
    /**
     * @param ?\ReflectionType $reflection_type
     *
     * @return UnionType
     * A UnionType with 0 or 1 nullable/non-nullable Types
     */
    public static function fromReflectionType($reflection_type)
    {
        if ($reflection_type !== null) {
            $ret5902c6fd476a2 = Type::fromReflectionType($reflection_type)->asUnionType();
            if (!$ret5902c6fd476a2 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd476a2) == "object" ? get_class($ret5902c6fd476a2) : gettype($ret5902c6fd476a2)) . " given");
            }
            return $ret5902c6fd476a2;
        }
        $ret5902c6fd4795e = new UnionType();
        if (!$ret5902c6fd4795e instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4795e) == "object" ? get_class($ret5902c6fd4795e) : gettype($ret5902c6fd4795e)) . " given");
        }
        return $ret5902c6fd4795e;
    }
    /**
     * @return string[]
     * Get a map from property name to its type for the given
     * class name.
     */
    public static function internalPropertyMapForClassName($class_name)
    {
        if (!is_string($class_name)) {
            throw new \InvalidArgumentException("Argument \$class_name passed to internalPropertyMapForClassName() must be of the type string, " . (gettype($class_name) == "object" ? get_class($class_name) : gettype($class_name)) . " given");
        }
        $map = self::internalPropertyMap();
        $canonical_class_name = strtolower($class_name);
        $ret5902c6fd47d03 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$map[$canonical_class_name], @[]);
        if (!is_array($ret5902c6fd47d03)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd47d03) . " given");
        }
        return $ret5902c6fd47d03;
    }
    /**
     * @return array
     * A map from builtin class properties to type information
     *
     * @see \Phan\Language\Internal\PropertyMap
     */
    private static function internalPropertyMap()
    {
        static $map = [];
        if (!$map) {
            $map_raw = (require __DIR__ . '/Internal/PropertyMap.php');
            foreach ($map_raw as $key => $value) {
                $map[strtolower($key)] = $value;
            }
            // Merge in an empty type for dynamic properties on any
            // classes listed as supporting them.
            foreach (require __DIR__ . '/Internal/DynamicPropertyMap.php' as $class_name) {
                $map[strtolower($class_name)]['*'] = '';
            }
        }
        $ret5902c6fd48358 = $map;
        if (!is_array($ret5902c6fd48358)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd48358) . " given");
        }
        return $ret5902c6fd48358;
    }
    /**
     * A list of types for parameters associated with the
     * given builtin function with the given name
     *
     * @param FullyQualifiedMethodName|FullyQualifiedFunctionName $function_fqsen
     *
     * @see internal_varargs_check
     * Formerly `function internal_varargs_check`
     */
    public static function internalFunctionSignatureMapForFQSEN($function_fqsen)
    {
        $context = new Context();
        $map = self::internalFunctionSignatureMap();
        if ($function_fqsen instanceof FullyQualifiedMethodName) {
            $class_fqsen = $function_fqsen->getFullyQualifiedClassName();
            $class_name = $class_fqsen->getName();
            $function_name = $class_name . '::' . $function_fqsen->getName();
        } else {
            $function_name = $function_fqsen->getName();
        }
        $function_name = strtolower($function_name);
        $function_name_original = $function_name;
        $alternate_id = 0;
        $configurations = [];
        while (isset($map[$function_name])) {
            // Get some static data about the function
            $type_name_struct = $map[$function_name];
            if (empty($type_name_struct)) {
                continue;
            }
            // Figure out the return type
            $return_type_name = array_shift($type_name_struct);
            $return_type = $return_type_name ? UnionType::fromStringInContext($return_type_name, $context, false) : null;
            $name_type_name_map = $type_name_struct;
            $parameter_name_type_map = [];
            foreach ($name_type_name_map as $name => $type_name) {
                $parameter_name_type_map[$name] = empty($type_name) ? new UnionType() : UnionType::fromStringInContext($type_name, $context, false);
            }
            $configurations[] = ['return_type' => $return_type, 'parameter_name_type_map' => $parameter_name_type_map];
            $function_name = $function_name_original . '\'' . ++$alternate_id;
        }
        $ret5902c6fd489e4 = $configurations;
        if (!is_array($ret5902c6fd489e4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd489e4) . " given");
        }
        return $ret5902c6fd489e4;
    }
    /**
     * @return Set
     * The set of simple types associated with this
     * union type.
     */
    public function getTypeSet()
    {
        $ret5902c6fd48c48 = $this->type_set;
        if (!$ret5902c6fd48c48 instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fd48c48) == "object" ? get_class($ret5902c6fd48c48) : gettype($ret5902c6fd48c48)) . " given");
        }
        return $ret5902c6fd48c48;
    }
    /**
     * Add a type name to the list of types
     *
     * @return void
     */
    public function addType(Type $type)
    {
        $this->type_set->attach($type);
    }
    /**
     * Remove a type name to the list of types
     *
     * @return void
     */
    public function removeType(Type $type)
    {
        $this->type_set->detach($type);
    }
    /**
     * @return bool
     * True if this union type contains the given named
     * type.
     */
    public function hasType(Type $type)
    {
        $ret5902c6fd4902c = $this->type_set->contains($type);
        if (!is_bool($ret5902c6fd4902c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4902c) . " given");
        }
        return $ret5902c6fd4902c;
    }
    /**
     * Add the given types to this type
     *
     * @return void
     */
    public function addUnionType(UnionType $union_type)
    {
        $this->type_set->addAll($union_type->getTypeSet());
    }
    /**
     * @return bool
     * True if this type has a type referencing the
     * class context in which it exists such as 'self'
     * or '$this'
     */
    public function hasSelfType()
    {
        $ret5902c6fd49580 = false !== $this->type_set->find(function (Type $type) {
            $ret5902c6fd4932f = $type->isSelfType();
            if (!is_bool($ret5902c6fd4932f)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4932f) . " given");
            }
            return $ret5902c6fd4932f;
        });
        if (!is_bool($ret5902c6fd49580)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd49580) . " given");
        }
        return $ret5902c6fd49580;
    }
    /**
     * @return UnionType[]
     * A map from template type identifiers to the UnionType
     * to replace it with
     */
    public function getTemplateParameterTypeList()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd497ee = [];
            if (!is_array($ret5902c6fd497ee)) {
                throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd497ee) . " given");
            }
            return $ret5902c6fd497ee;
        }
        $ret5902c6fd49af4 = array_reduce($this->getTypeSet()->toArray(), function (array $map, Type $type) {
            return array_merge($type->getTemplateParameterTypeList(), $map);
        }, []);
        if (!is_array($ret5902c6fd49af4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd49af4) . " given");
        }
        return $ret5902c6fd49af4;
    }
    /**
     * @param CodeBase $code_base
     * The code base to look up classes against
     *
     * TODO: Defer resolving the template parameters until parse ends. Low priority.
     *
     * @return UnionType[]
     * A map from template type identifiers to the UnionType
     * to replace it with
     */
    public function getTemplateParameterTypeMap(CodeBase $code_base)
    {
        if ($this->isEmpty()) {
            $ret5902c6fd49d98 = [];
            if (!is_array($ret5902c6fd49d98)) {
                throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd49d98) . " given");
            }
            return $ret5902c6fd49d98;
        }
        $ret5902c6fd4a0d6 = array_reduce($this->getTypeSet()->toArray(), function (array $map, Type $type) use($code_base) {
            return array_merge($type->getTemplateParameterTypeMap($code_base), $map);
        }, []);
        if (!is_array($ret5902c6fd4a0d6)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd4a0d6) . " given");
        }
        return $ret5902c6fd4a0d6;
    }
    /**
     * @param UnionType[] $template_parameter_type_map
     * A map from template type identifiers to concrete types
     *
     * @return UnionType
     * This UnionType with any template types contained herein
     * mapped to concrete types defined in the given map.
     */
    public function withTemplateParameterTypeMap(array $template_parameter_type_map)
    {
        $concrete_type_list = [];
        foreach ($this->getTypeSet() as $i => $type) {
            if ($type instanceof TemplateType && isset($template_parameter_type_map[$type->getName()])) {
                $union_type = $template_parameter_type_map[$type->getName()];
                foreach ($union_type->getTypeSet() as $concrete_type) {
                    $concrete_type_list[] = $concrete_type;
                }
            } else {
                $concrete_type_list[] = $type;
            }
        }
        $ret5902c6fd4a48b = new UnionType($concrete_type_list);
        if (!$ret5902c6fd4a48b instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4a48b) == "object" ? get_class($ret5902c6fd4a48b) : gettype($ret5902c6fd4a48b)) . " given");
        }
        return $ret5902c6fd4a48b;
    }
    /**
     * @return bool
     * True if this union type has any types that are generic
     * types
     */
    public function hasTemplateType()
    {
        $ret5902c6fd4aa69 = false !== $this->type_set->find(function (Type $type) {
            $ret5902c6fd4a812 = $type instanceof TemplateType;
            if (!is_bool($ret5902c6fd4a812)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4a812) . " given");
            }
            return $ret5902c6fd4a812;
        });
        if (!is_bool($ret5902c6fd4aa69)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4aa69) . " given");
        }
        return $ret5902c6fd4aa69;
    }
    /**
     * @return bool
     * True if this type has a type referencing the
     * class context 'static'.
     */
    public function hasStaticType()
    {
        $ret5902c6fd4af95 = false !== $this->type_set->find(function (Type $type) {
            $ret5902c6fd4ad43 = $type->isStaticType();
            if (!is_bool($ret5902c6fd4ad43)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4ad43) . " given");
            }
            return $ret5902c6fd4ad43;
        });
        if (!is_bool($ret5902c6fd4af95)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4af95) . " given");
        }
        return $ret5902c6fd4af95;
    }
    /**
     * @return UnionType
     * A new UnionType with any references to 'static' resolved
     * in the given context.
     */
    public function withStaticResolvedInContext(Context $context)
    {
        // If the context isn't in a class scope, there's nothing
        // we can do
        if (!$context->isInClassScope()) {
            $ret5902c6fd4b220 = $this;
            if (!$ret5902c6fd4b220 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4b220) == "object" ? get_class($ret5902c6fd4b220) : gettype($ret5902c6fd4b220)) . " given");
            }
            return $ret5902c6fd4b220;
        }
        // Find the static type on the list
        $static_type = $this->getTypeSet()->find(function (Type $type) {
            $ret5902c6fd4bf2f = $type->isStaticType();
            if (!is_bool($ret5902c6fd4bf2f)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4bf2f) . " given");
            }
            return $ret5902c6fd4bf2f;
        });
        // If we don't actually have a static type, we're all set
        if (!$static_type) {
            $ret5902c6fd4c1dd = $this;
            if (!$ret5902c6fd4c1dd instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4c1dd) == "object" ? get_class($ret5902c6fd4c1dd) : gettype($ret5902c6fd4c1dd)) . " given");
            }
            return $ret5902c6fd4c1dd;
        }
        // Get a copy of this UnionType to avoid having to know
        // who has copies of it out in the wild and what they're
        // hoping for.
        $union_type = clone $this;
        // Remove the static type
        $union_type->removeType($static_type);
        // Add in the class in scope
        $union_type->addType($context->getClassFQSEN()->asType());
        $ret5902c6fd4c53f = $union_type;
        if (!$ret5902c6fd4c53f instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4c53f) == "object" ? get_class($ret5902c6fd4c53f) : gettype($ret5902c6fd4c53f)) . " given");
        }
        return $ret5902c6fd4c53f;
    }
    /**
     * @return bool
     * True if and only if this UnionType contains
     * the given type and no others.
     */
    public function isType(Type $type)
    {
        if ($this->typeCount() != 1) {
            $ret5902c6fd4c85a = false;
            if (!is_bool($ret5902c6fd4c85a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4c85a) . " given");
            }
            return $ret5902c6fd4c85a;
        }
        $ret5902c6fd4cac7 = $this->type_set->contains($type);
        if (!is_bool($ret5902c6fd4cac7)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4cac7) . " given");
        }
        return $ret5902c6fd4cac7;
    }
    /**
     * @return bool
     * True if this UnionType is exclusively native
     * types
     */
    public function isNativeType()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd4cd79 = false;
            if (!is_bool($ret5902c6fd4cd79)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4cd79) . " given");
            }
            return $ret5902c6fd4cd79;
        }
        $ret5902c6fd4d26f = false === $this->type_set->find(function (Type $type) {
            $ret5902c6fd4d01f = !$type->isNativeType();
            if (!is_bool($ret5902c6fd4d01f)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4d01f) . " given");
            }
            return $ret5902c6fd4d01f;
        });
        if (!is_bool($ret5902c6fd4d26f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4d26f) . " given");
        }
        return $ret5902c6fd4d26f;
    }
    /**
     * @return bool
     * True iff this union contains the exact set of types
     * represented in the given union type.
     */
    public function isEqualTo(UnionType $union_type)
    {
        $type_set = $this->getTypeSet();
        $other_type_set = $union_type->getTypeSet();
        if (count($type_set) !== count($other_type_set)) {
            $ret5902c6fd4d57a = false;
            if (!is_bool($ret5902c6fd4d57a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4d57a) . " given");
            }
            return $ret5902c6fd4d57a;
        }
        foreach ($type_set as $type) {
            if (!$other_type_set->contains($type)) {
                $ret5902c6fd4d813 = false;
                if (!is_bool($ret5902c6fd4d813)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4d813) . " given");
                }
                return $ret5902c6fd4d813;
            }
        }
        $ret5902c6fd4da8e = true;
        if (!is_bool($ret5902c6fd4da8e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4da8e) . " given");
        }
        return $ret5902c6fd4da8e;
    }
    /**
     * @return bool - True if not empty and at least one type is NullType or nullable.
     */
    public function containsNullable()
    {
        foreach ($this->getTypeSet() as $type) {
            if ($type->getIsNullable()) {
                $ret5902c6fd4dda1 = true;
                if (!is_bool($ret5902c6fd4dda1)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4dda1) . " given");
                }
                return $ret5902c6fd4dda1;
            }
        }
        $ret5902c6fd4e010 = false;
        if (!is_bool($ret5902c6fd4e010)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4e010) . " given");
        }
        return $ret5902c6fd4e010;
    }
    public function nonNullableClone()
    {
        $result = new UnionType();
        foreach ($this->getTypeSet() as $type) {
            if (!$type->getIsNullable()) {
                $result->addType($type);
                continue;
            }
            if ($type === NullType::instance(false)) {
                continue;
            }
            $result->addType($type->withIsNullable(false));
        }
        $ret5902c6fd4e397 = $result;
        if (!$ret5902c6fd4e397 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4e397) == "object" ? get_class($ret5902c6fd4e397) : gettype($ret5902c6fd4e397)) . " given");
        }
        return $ret5902c6fd4e397;
    }
    public function nullableClone()
    {
        $result = new UnionType();
        foreach ($this->getTypeSet() as $type) {
            if ($type->getIsNullable()) {
                $result->addType($type);
                continue;
            }
            $result->addType($type->withIsNullable(true));
        }
        $ret5902c6fd4e745 = $result;
        if (!$ret5902c6fd4e745 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd4e745) == "object" ? get_class($ret5902c6fd4e745) : gettype($ret5902c6fd4e745)) . " given");
        }
        return $ret5902c6fd4e745;
    }
    /**
     * @param UnionType $union_type
     * A union type to compare against
     *
     * @param Context $context
     * The context in which this type exists.
     *
     * @param CodeBase $code_base
     * The code base in which both this and the given union
     * types exist.
     *
     * @return bool
     * True if each type within this union type can cast
     * to the given union type.
     */
    public function isExclusivelyNarrowedFormOrEquivalentTo(UnionType $union_type, Context $context, CodeBase $code_base)
    {
        // Special rule: anything can cast to nothing
        if ($union_type->isEmpty()) {
            $ret5902c6fd4ea70 = true;
            if (!is_bool($ret5902c6fd4ea70)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4ea70) . " given");
            }
            return $ret5902c6fd4ea70;
        }
        // Check to see if the types are equivalent
        if ($this->isEqualTo($union_type)) {
            $ret5902c6fd4ed14 = true;
            if (!is_bool($ret5902c6fd4ed14)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4ed14) . " given");
            }
            return $ret5902c6fd4ed14;
        }
        // Resolve 'static' for the given context to
        // determine whats actually being referred
        // to in concrete terms.
        $union_type = $union_type->withStaticResolvedInContext($context);
        // Convert this type to an array of resolved
        // types.
        $type_set = $this->withStaticResolvedInContext($context)->getTypeSet()->toArray();
        $ret5902c6fd4f54e = array_reduce($type_set, function ($can_cast, Type $type) use($union_type, $code_base) {
            if (!is_bool($can_cast)) {
                throw new \InvalidArgumentException("Argument \$can_cast passed to () must be of the type bool, " . (gettype($can_cast) == "object" ? get_class($can_cast) : gettype($can_cast)) . " given");
            }
            $ret5902c6fd4f07f = $can_cast && $type->asUnionType()->asExpandedTypes($code_base)->canCastToUnionType($union_type);
            if (!is_bool($ret5902c6fd4f07f)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4f07f) . " given");
            }
            return $ret5902c6fd4f07f;
        }, true);
        if (!is_bool($ret5902c6fd4f54e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4f54e) . " given");
        }
        return $ret5902c6fd4f54e;
    }
    /**
     * @param Type[] $type_list
     * A list of types
     *
     * @return bool
     * True if this union type contains any of the given
     * named types
     */
    public function hasAnyType(array $type_list)
    {
        $ret5902c6fd4f7dd = $this->type_set->containsAny($type_list);
        if (!is_bool($ret5902c6fd4f7dd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4f7dd) . " given");
        }
        return $ret5902c6fd4f7dd;
    }
    /**
     * @return bool
     * True if this type has any subtype of `iterable` type (e.g. Traversable, Array).
     */
    public function hasIterable()
    {
        $ret5902c6fd4fd15 = false !== $this->type_set->find(function (Type $type) {
            $ret5902c6fd4fa91 = $type->isIterable();
            if (!is_bool($ret5902c6fd4fa91)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4fa91) . " given");
            }
            return $ret5902c6fd4fa91;
        });
        if (!is_bool($ret5902c6fd4fd15)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd4fd15) . " given");
        }
        return $ret5902c6fd4fd15;
    }
    /**
     * @return int
     * The number of types in this union type
     */
    public function typeCount()
    {
        $ret5902c6fd4ff7e = $this->type_set->count();
        if (!is_int($ret5902c6fd4ff7e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6fd4ff7e) . " given");
        }
        return $ret5902c6fd4ff7e;
    }
    /**
     * @return bool
     * True if this Union has no types
     */
    public function isEmpty()
    {
        $ret5902c6fd501f2 = $this->typeCount() < 1;
        if (!is_bool($ret5902c6fd501f2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd501f2) . " given");
        }
        return $ret5902c6fd501f2;
    }
    /**
     * @param UnionType $target
     * The type we'd like to see if this type can cast
     * to
     *
     * @param CodeBase $code_base
     * The code base used to expand types
     *
     * @return bool
     * Test to see if this type can be cast to the
     * given type after expanding both union types
     * to include all ancestor types
     *
     * TODO: ensure that this is only called after the parse phase is over.
     */
    public function canCastToExpandedUnionType(UnionType $target, CodeBase $code_base)
    {
        $this_expanded = $this->asExpandedTypes($code_base);
        $target_expanded = $target->asExpandedTypes($code_base);
        $ret5902c6fd50517 = $this_expanded->canCastToUnionType($target_expanded);
        if (!is_bool($ret5902c6fd50517)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd50517) . " given");
        }
        return $ret5902c6fd50517;
    }
    /**
     * @param UnionType $target
     * A type to check to see if this can cast to it
     *
     * @return bool
     * True if this type is allowed to cast to the given type
     * i.e. int->float is allowed  while float->int is not.
     *
     * @see \Phan\Deprecated\Pass2::type_check
     * Formerly 'function type_check'
     */
    public function canCastToUnionType(UnionType $target)
    {
        // Fast-track most common cases first
        // If either type is unknown, we can't call it
        // a success
        if ($this->isEmpty() || $target->isEmpty()) {
            $ret5902c6fd507c5 = true;
            if (!is_bool($ret5902c6fd507c5)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd507c5) . " given");
            }
            return $ret5902c6fd507c5;
        }
        // T === T
        if ($this->isEqualTo($target)) {
            $ret5902c6fd50a44 = true;
            if (!is_bool($ret5902c6fd50a44)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd50a44) . " given");
            }
            return $ret5902c6fd50a44;
        }
        if (Config::get()->null_casts_as_any_type) {
            // null <-> null
            if ($this->isType(NullType::instance(false)) || $target->isType(NullType::instance(false))) {
                $ret5902c6fd50d55 = true;
                if (!is_bool($ret5902c6fd50d55)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd50d55) . " given");
                }
                return $ret5902c6fd50d55;
            }
        }
        // mixed <-> mixed
        if ($target->hasType(MixedType::instance(false)) || $this->hasType(MixedType::instance(false))) {
            $ret5902c6fd5104a = true;
            if (!is_bool($ret5902c6fd5104a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd5104a) . " given");
            }
            return $ret5902c6fd5104a;
        }
        // int -> float
        if ($this->isType(IntType::instance(false)) && $target->isType(FloatType::instance(false))) {
            $ret5902c6fd5133a = true;
            if (!is_bool($ret5902c6fd5133a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd5133a) . " given");
            }
            return $ret5902c6fd5133a;
        }
        // Check conversion on the cross product of all
        // type combinations and see if any can cast to
        // any.
        foreach ($this->getTypeSet() as $source_type) {
            if (empty($source_type)) {
                continue;
            }
            foreach ($target->getTypeSet() as $target_type) {
                if (empty($target_type)) {
                    continue;
                }
                if ($source_type->canCastToType($target_type)) {
                    $ret5902c6fd51637 = true;
                    if (!is_bool($ret5902c6fd51637)) {
                        throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd51637) . " given");
                    }
                    return $ret5902c6fd51637;
                }
            }
        }
        $ret5902c6fd5189d = false;
        if (!is_bool($ret5902c6fd5189d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd5189d) . " given");
        }
        return $ret5902c6fd5189d;
    }
    /**
     * @return bool
     * True if all types in this union are scalars
     *
     * @see \Phan\Deprecated\Util::type_scalar
     * Formerly `function type_scalar`
     */
    public function isScalar()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd51b25 = false;
            if (!is_bool($ret5902c6fd51b25)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd51b25) . " given");
            }
            return $ret5902c6fd51b25;
        }
        $ret5902c6fd52040 = false === $this->type_set->find(function (Type $type) {
            $ret5902c6fd51de4 = !$type->isScalar();
            if (!is_bool($ret5902c6fd51de4)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd51de4) . " given");
            }
            return $ret5902c6fd51de4;
        });
        if (!is_bool($ret5902c6fd52040)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd52040) . " given");
        }
        return $ret5902c6fd52040;
    }
    /**
     * @return bool
     * True if this union has array-like types (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function hasArrayLike()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd522c1 = false;
            if (!is_bool($ret5902c6fd522c1)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd522c1) . " given");
            }
            return $ret5902c6fd522c1;
        }
        $ret5902c6fd527e0 = false === $this->type_set->find(function (Type $type) {
            $ret5902c6fd52580 = !$type->isArrayLike();
            if (!is_bool($ret5902c6fd52580)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd52580) . " given");
            }
            return $ret5902c6fd52580;
        });
        if (!is_bool($ret5902c6fd527e0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd527e0) . " given");
        }
        return $ret5902c6fd527e0;
    }
    /**
     * @return bool
     * True if this union type represents types that are
     * array-like, and nothing else (e.g. can't be null).
     * If any of the array-like types are nullable, this returns false.
     */
    public function isExclusivelyArrayLike()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd52a6a = false;
            if (!is_bool($ret5902c6fd52a6a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd52a6a) . " given");
            }
            return $ret5902c6fd52a6a;
        }
        $ret5902c6fd53224 = array_reduce($this->getTypeSet()->toArray(), function ($is_exclusively_array, Type $type) {
            if (!is_bool($is_exclusively_array)) {
                throw new \InvalidArgumentException("Argument \$is_exclusively_array passed to () must be of the type bool, " . (gettype($is_exclusively_array) == "object" ? get_class($is_exclusively_array) : gettype($is_exclusively_array)) . " given");
            }
            $ret5902c6fd52d53 = $is_exclusively_array && $type->isArrayLike() && !$type->getIsNullable();
            if (!is_bool($ret5902c6fd52d53)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd52d53) . " given");
            }
            return $ret5902c6fd52d53;
        }, true);
        if (!is_bool($ret5902c6fd53224)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd53224) . " given");
        }
        return $ret5902c6fd53224;
    }
    /**
     * @return bool
     * True if this union type represents types that are arrays
     * or generic arrays, but nothing else.
     */
    public function isExclusivelyArray()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd534a2 = false;
            if (!is_bool($ret5902c6fd534a2)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd534a2) . " given");
            }
            return $ret5902c6fd534a2;
        }
        $ret5902c6fd53c92 = array_reduce($this->getTypeSet()->toArray(), function ($is_exclusively_array, Type $type) {
            if (!is_bool($is_exclusively_array)) {
                throw new \InvalidArgumentException("Argument \$is_exclusively_array passed to () must be of the type bool, " . (gettype($is_exclusively_array) == "object" ? get_class($is_exclusively_array) : gettype($is_exclusively_array)) . " given");
            }
            $ret5902c6fd537b6 = $is_exclusively_array && ($type === ArrayType::instance(false) || $type->isGenericArray());
            if (!is_bool($ret5902c6fd537b6)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd537b6) . " given");
            }
            return $ret5902c6fd537b6;
        }, true);
        if (!is_bool($ret5902c6fd53c92)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd53c92) . " given");
        }
        return $ret5902c6fd53c92;
    }
    /**
     * @return UnionType
     * Get the subset of types which are not native
     */
    public function nonNativeTypes()
    {
        $ret5902c6fd53f89 = new UnionType($this->type_set->filter(function (Type $type) {
            return !$type->isNativeType();
        }));
        if (!$ret5902c6fd53f89 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd53f89) == "object" ? get_class($ret5902c6fd53f89) : gettype($ret5902c6fd53f89)) . " given");
        }
        return $ret5902c6fd53f89;
    }
    /**
     * @param CodeBase $code_base
     * The code base in which to find classes
     *
     * @param Context $context
     * The context in which we're resolving this union
     * type.
     *
     * @return \Generator
     *
     * A list of classes representing the non-native types
     * associated with this UnionType
     *
     * @throws CodeBaseException
     * An exception is thrown if a non-native type does not have
     * an associated class
     *
     * @throws IssueException
     * An exception is thrown if static is used as a type outside of an object
     * context
     */
    public function asClassList(CodeBase $code_base, Context $context)
    {
        // Iterate over each viable class type to see if any
        // have the constant we're looking for
        foreach ($this->nonNativeTypes()->getTypeSet() as $class_type) {
            // Get the class FQSEN
            $class_fqsen = $class_type->asFQSEN();
            if ($class_type->isStaticType()) {
                if (!$context->isInClassScope()) {
                    throw new IssueException(Issue::fromType(Issue::ContextNotObject)($context->getFile(), $context->getLineNumberStart(), [(string) $class_type]));
                }
                (yield $context->getClassInScope($code_base));
            } else {
                // See if the class exists
                if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
                    throw new CodeBaseException($class_fqsen, "Cannot find class {$class_fqsen}");
                }
                (yield $code_base->getClassByFQSEN($class_fqsen));
            }
        }
    }
    /**
     * Takes "a|b[]|c|d[]|e" and returns "a|c|e"
     *
     * @return UnionType
     * A UnionType with generic types filtered out
     *
     * @see \Phan\Deprecated\Pass2::nongenerics
     * Formerly `function nongenerics`
     */
    public function nonGenericArrayTypes()
    {
        $ret5902c6fd547b7 = new UnionType($this->type_set->filter(function (Type $type) {
            $ret5902c6fd54555 = !$type->isGenericArray();
            if (!is_bool($ret5902c6fd54555)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd54555) . " given");
            }
            return $ret5902c6fd54555;
        }));
        if (!$ret5902c6fd547b7 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd547b7) == "object" ? get_class($ret5902c6fd547b7) : gettype($ret5902c6fd547b7)) . " given");
        }
        return $ret5902c6fd547b7;
    }
    /**
     * Takes "a|b[]|c|d[]|e|array|ArrayAccess" and returns "a|c|e|ArrayAccess"
     *
     * @return UnionType
     * A UnionType with generic types(as well as the non-generic type "array")
     * filtered out.
     *
     * @see nonGenericArrayTypes
     */
    public function nonArrayTypes()
    {
        $ret5902c6fd54dad = new UnionType($this->type_set->filter(function (Type $type) {
            $ret5902c6fd54b48 = !$type->isGenericArray() && $type !== ArrayType::instance(false);
            if (!is_bool($ret5902c6fd54b48)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd54b48) . " given");
            }
            return $ret5902c6fd54b48;
        }));
        if (!$ret5902c6fd54dad instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd54dad) == "object" ? get_class($ret5902c6fd54dad) : gettype($ret5902c6fd54dad)) . " given");
        }
        return $ret5902c6fd54dad;
    }
    /**
     * @return bool
     * True if this is exclusively generic types
     */
    public function isGenericArray()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd550a8 = false;
            if (!is_bool($ret5902c6fd550a8)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd550a8) . " given");
            }
            return $ret5902c6fd550a8;
        }
        $ret5902c6fd555b5 = false === $this->type_set->find(function (Type $type) {
            $ret5902c6fd5535a = !$type->isGenericArray();
            if (!is_bool($ret5902c6fd5535a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd5535a) . " given");
            }
            return $ret5902c6fd5535a;
        });
        if (!is_bool($ret5902c6fd555b5)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd555b5) . " given");
        }
        return $ret5902c6fd555b5;
    }
    /**
     * @return bool
     * True if this type has any generic types
     */
    public function hasGenericArray()
    {
        if ($this->isEmpty()) {
            $ret5902c6fd55837 = false;
            if (!is_bool($ret5902c6fd55837)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd55837) . " given");
            }
            return $ret5902c6fd55837;
        }
        $ret5902c6fd55d5c = false !== $this->type_set->find(function (Type $type) {
            $ret5902c6fd55aec = $type->isGenericArray();
            if (!is_bool($ret5902c6fd55aec)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd55aec) . " given");
            }
            return $ret5902c6fd55aec;
        });
        if (!is_bool($ret5902c6fd55d5c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd55d5c) . " given");
        }
        return $ret5902c6fd55d5c;
    }
    /**
     * Takes "a|b[]|c|d[]|e" and returns "b|d"
     *
     * @return UnionType
     * The subset of types in this
     */
    public function genericArrayElementTypes()
    {
        $union_type = new UnionType($this->type_set->filter(function (Type $type) {
            $ret5902c6fd56031 = $type->isGenericArray();
            if (!is_bool($ret5902c6fd56031)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd56031) . " given");
            }
            return $ret5902c6fd56031;
        })->map(function (Type $type) {
            $ret5902c6fd562d1 = $type->genericArrayElementType();
            if (!$ret5902c6fd562d1 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fd562d1) == "object" ? get_class($ret5902c6fd562d1) : gettype($ret5902c6fd562d1)) . " given");
            }
            return $ret5902c6fd562d1;
        }));
        // If array is in there, then it can be any type
        // Same for mixed
        if ($this->hasType(ArrayType::instance(false)) || $this->hasType(MixedType::instance(false)) || Config::get()->null_casts_as_any_type && $this->hasType(ArrayType::instance(true))) {
            $union_type->addType(MixedType::instance(false));
        }
        if ($this->hasType(ArrayType::instance(false))) {
            $union_type->addType(NullType::instance(false));
        }
        $ret5902c6fd567db = $union_type;
        if (!$ret5902c6fd567db instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd567db) == "object" ? get_class($ret5902c6fd567db) : gettype($ret5902c6fd567db)) . " given");
        }
        return $ret5902c6fd567db;
    }
    /**
     * @param Closure $closure
     * A closure mapping `Type` to `Type`
     *
     * @return UnionType
     * A new UnionType with each type mapped through the
     * given closure
     */
    public function asMappedUnionType(\Closure $closure)
    {
        $ret5902c6fd56b3f = new UnionType($this->type_set->map($closure));
        if (!$ret5902c6fd56b3f instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd56b3f) == "object" ? get_class($ret5902c6fd56b3f) : gettype($ret5902c6fd56b3f)) . " given");
        }
        return $ret5902c6fd56b3f;
    }
    /**
     * @return UnionType
     * Get a new type for each type in this union which is
     * the generic array version of this type. For instance,
     * 'int|float' will produce 'int[]|float[]'.
     */
    public function asGenericArrayTypes()
    {
        $ret5902c6fd5719c = $this->asMappedUnionType(function (Type $type) {
            $ret5902c6fd56e90 = $type->asGenericArrayType();
            if (!$ret5902c6fd56e90 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fd56e90) == "object" ? get_class($ret5902c6fd56e90) : gettype($ret5902c6fd56e90)) . " given");
            }
            return $ret5902c6fd56e90;
        });
        if (!$ret5902c6fd5719c instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd5719c) == "object" ? get_class($ret5902c6fd5719c) : gettype($ret5902c6fd5719c)) . " given");
        }
        return $ret5902c6fd5719c;
    }
    /**
     * @param CodeBase
     * The code base to use in order to find super classes, etc.
     *
     * @param $recursion_depth
     * This thing has a tendency to run-away on me. This tracks
     * how bad I messed up by seeing how far the expanded types
     * go
     *
     * @return UnionType
     * Expands all class types to all inherited classes returning
     * a superset of this type.
     */
    public function asExpandedTypes(CodeBase $code_base, $recursion_depth = 0)
    {
        if (!is_int($recursion_depth)) {
            throw new \InvalidArgumentException("Argument \$recursion_depth passed to asExpandedTypes() must be of the type int, " . (gettype($recursion_depth) == "object" ? get_class($recursion_depth) : gettype($recursion_depth)) . " given");
        }
        assert($recursion_depth < 10, "Recursion has gotten out of hand");
        $union_type = new UnionType();
        foreach ($this->type_set as $type) {
            $union_type->addUnionType($type->asExpandedTypes($code_base, $recursion_depth + 1));
        }
        $ret5902c6fd575e4 = $union_type;
        if (!$ret5902c6fd575e4 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fd575e4) == "object" ? get_class($ret5902c6fd575e4) : gettype($ret5902c6fd575e4)) . " given");
        }
        return $ret5902c6fd575e4;
    }
    /**
     * As per the Serializable interface
     *
     * @return string
     * A serialized representation of this type
     *
     * @see \Serializable
     */
    public function serialize()
    {
        $ret5902c6fd57b8b = (string) $this;
        if (!is_string($ret5902c6fd57b8b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd57b8b) . " given");
        }
        return $ret5902c6fd57b8b;
    }
    /**
     * As per the Serializable interface
     *
     * @param string $serialized
     * A serialized UnionType
     *
     * @return void
     *
     * @see \Serializable
     */
    public function unserialize($serialized)
    {
        $this->type_set = new Set(array_map(function ($type_name) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            return Type::fromFullyQualifiedString($type_name);
        }, explode('|', call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$serialized, @''))));
    }
    /**
     * @return string
     * A human-readable string representation of this union
     * type
     */
    public function __toString()
    {
        // Create a new array containing the string
        // representations of each type
        $type_name_list = array_map(function (Type $type) {
            $ret5902c6fd58204 = (string) $type;
            if (!is_string($ret5902c6fd58204)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd58204) . " given");
            }
            return $ret5902c6fd58204;
        }, $this->getTypeSet()->toArray());
        // Sort the types so that we get a stable
        // representation
        asort($type_name_list);
        $ret5902c6fd584fe = implode('|', $type_name_list);
        if (!is_string($ret5902c6fd584fe)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd584fe) . " given");
        }
        return $ret5902c6fd584fe;
    }
    /**
     * @return array
     * A map from builtin function name to type information
     *
     * @see \Phan\Language\Internal\FunctionSignatureMap
     */
    public static function internalFunctionSignatureMap()
    {
        static $map = [];
        if (!$map) {
            $map_raw = (require __DIR__ . '/Internal/FunctionSignatureMap.php');
            foreach ($map_raw as $key => $value) {
                $map[strtolower($key)] = $value;
            }
        }
        return $map;
    }
}