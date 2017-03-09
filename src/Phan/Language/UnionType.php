<?php declare(strict_types=1);
namespace Phan\Language;

use Phan\AST\UnionTypeVisitor;
use Phan\CodeBase;
use Phan\Config;
use Phan\Exception\CodeBaseException;
use Phan\Exception\IssueException;
use Phan\Issue;
use Phan\Language\Element\Clazz;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\FloatType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\TemplateType;
use Phan\Library\ArraySet;
use ast\Node;

class UnionType implements \Serializable
{
    /**
     * @var string
     * A list of one or more types delimited by the '|'
     * character (e.g. 'int|DateTime|string[]')
     */
    const union_type_regex =
        Type::type_regex
        . '(\|' . Type::type_regex . ')*';

    /**
     * @var Type[]
     */
    private $type_set;

    /**
     * @param Type[]|\Iterator|null $type_list
     * @param bool $is_an_array_set - Whether or not this is already a set. Only set to true within UnionSet code.
     *
     * An optional list of types represented by this union
     */
    public function __construct($type_list = null, bool $is_an_array_set = false)
    {
        if ($is_an_array_set) {
            // Disable asserts in production
            /**
            assert(is_array($type_list),
                   'should be an array array');
            assert(ArraySet::is_array_set($type_list),
                   'Should be an array set');
             */
            $this->type_set = $type_list;
            return;
        }
        $this->type_set = ArraySet::from_list($type_list);
    }

    // __clone of $this->type_set would be a no-op due to copy on write semantics.

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
    public static function fromFullyQualifiedString(
        string $fully_qualified_string
    ) : UnionType {
        if ($fully_qualified_string === '') {
            return new UnionType();
        }

        static $memoizeMap = [];
        $types_set = $memoizeMap[$fully_qualified_string] ?? null;

        if (!isset($types_set)) {
            $types_set = ArraySet::from_list(array_map(function (string $type_name) {
                return Type::fromFullyQualifiedString($type_name);
            }, explode('|', $fully_qualified_string)));
            $memoizeMap[$fully_qualified_string] = $types_set;
        }

        return new UnionType($types_set, true);
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
     * @param int $source one of the constants in Type::FROM_*
     *
     * @return UnionType
     */
    public static function fromStringInContext(
        string $type_string,
        Context $context,
        int $source
    ) : UnionType {
        if (empty($type_string)) {
            return new UnionType();
        }

        // If our scope has a generic type identifier defined on it
        // that matches the type string, return that UnionType.
        if ($context->getScope()->hasTemplateType($type_string)) {
            return $context->getScope()->getTemplateType(
                $type_string
            )->asUnionType();
        }

        return new UnionType(
            array_map(function (string $type_name) use ($context, $type_string, $source) {
                assert($type_name !== '', "Type cannot be empty.");
                return Type::fromStringInContext(
                    $type_name,
                    $context,
                    $source
                );
            }, array_filter(array_map(function (string $type_name) {
                return trim($type_name);
            }, explode('|', $type_string)), function(string $type_name) {
                // Exclude empty type names
                // Exclude namespaces without type names (e.g. `\`, `\NS\`)
                return $type_name !== '' && preg_match('@\\\\[\[\]]*$@', $type_name) === 0;
            }))
        );
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
    public static function fromNode(
        Context $context,
        CodeBase $code_base,
        $node,
        bool $should_catch_issue_exception = true
    ) : UnionType {
        return UnionTypeVisitor::unionTypeFromNode(
            $code_base,
            $context,
            $node,
            $should_catch_issue_exception
        );
    }

    /**
     * @param ?\ReflectionType $reflection_type
     *
     * @return UnionType
     * A UnionType with 0 or 1 nullable/non-nullable Types
     */
    public static function fromReflectionType($reflection_type) : UnionType
    {
        if ($reflection_type !== null) {
            return Type::fromReflectionType($reflection_type)->asUnionType();
        }
        return new UnionType();
    }

    /**
     * @return string[]
     * Get a map from property name to its type for the given
     * class name.
     */
    public static function internalPropertyMapForClassName(
        string $class_name
    ) : array {
        $map = self::internalPropertyMap();

        $canonical_class_name = strtolower($class_name);

        return $map[$canonical_class_name] ?? [];
    }

    /**
     * @return array
     * A map from builtin class properties to type information
     *
     * @see \Phan\Language\Internal\PropertyMap
     */
    private static function internalPropertyMap() : array
    {
        static $map = [];

        if (!$map) {
            $map_raw = require(__DIR__.'/Internal/PropertyMap.php');
            foreach ($map_raw as $key => $value) {
                $map[strtolower($key)] = $value;
            }

            // Merge in an empty type for dynamic properties on any
            // classes listed as supporting them.
            foreach (require(__DIR__.'/Internal/DynamicPropertyMap.php') as $class_name) {
                $map[strtolower($class_name)]['*'] = '';
            }
        }

        return $map;
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
    public static function internalFunctionSignatureMapForFQSEN(
        $function_fqsen
    ) : array {
        $map = self::internalFunctionSignatureMap();

        if ($function_fqsen instanceof FullyQualifiedMethodName) {
            $class_fqsen =
                $function_fqsen->getFullyQualifiedClassName();
            $class_name = $class_fqsen->getName();
            $function_name =
                $class_name . '::' . $function_fqsen->getName();
        } else {
            $function_name = $function_fqsen->getName();
        }

        $function_name = strtolower($function_name);

        $function_name_original = $function_name;
        $alternate_id = 0;

        /**
         * @param string|null $type_name
         * @return UnionType|null
         */
        $getForGlobalContext = function($type_name) {
            if (!$type_name) {
                return null;
            }

            static $internal_fn_cache = [];


            $result = $internal_fn_cache[$type_name] ?? null;
            if ($result === null) {
                $context = new Context;
                $result = UnionType::fromStringInContext($type_name, $context, Type::FROM_PHPDOC);
                $internal_fn_cache[$type_name] = $result;
            }
            return clone($result);
        };

        $configurations = [];
        while (isset($map[$function_name])) {


            // Get some static data about the function
            $type_name_struct = $map[$function_name];
            if (empty($type_name_struct)) {
                continue;
            }

            // Figure out the return type
            $return_type_name = array_shift($type_name_struct);
            $return_type = $getForGlobalContext($return_type_name);

            $name_type_name_map = $type_name_struct;
            $parameter_name_type_map = [];

            foreach ($name_type_name_map as $name => $type_name) {
                $parameter_name_type_map[$name] = $getForGlobalContext($type_name) ?? new UnionType();
            }

            $configurations[] = [
                'return_type' => $return_type,
                'parameter_name_type_map' => $parameter_name_type_map,
            ];

            $function_name =
                $function_name_original . '\'' . (++$alternate_id);
        }

        return $configurations;
    }

    /**
     * @return Type[]
     * The set of simple types associated with this
     * union type. The key is based on runkit_object_id()
     */
    public function getTypeSet() : array
    {
        return $this->type_set;
    }

    /**
     * Add a type name to the list of types
     *
     * @return void
     */
    public function addType(Type $type)
    {
        $this->type_set[runkit_object_id($type)] = $type;
    }

    /**
     * Remove a type name to the list of types
     *
     * @return void
     */
    public function removeType(Type $type)
    {
        unset($this->type_set[runkit_object_id($type)]);
    }

    /**
     * @return bool
     * True if this union type contains the given named
     * type.
     */
    public function hasType(Type $type) : bool
    {
        return ArraySet::contains($this->type_set, $type);
    }

    /**
     * Add the given types to this type
     *
     * @return void
     */
    public function addUnionType(UnionType $union_type)
    {
        if (count($this->type_set) === 0) {
            // take advantage of array copy-on-write to save a bit of memory
            $this->type_set = $union_type->type_set;
        } else {
            $this->type_set += $union_type->type_set;
        }
    }

    /**
     * @return bool
     * True if this type has a type referencing the
     * class context in which it exists such as 'self'
     * or '$this'
     */
    public function hasSelfType() : bool
    {
        return ArraySet::exists($this->type_set, function (Type $type) : bool {
            return $type->isSelfType();
        });
    }

    /**
     * @return bool
     * True if this union type has any types that are generic
     * types.
     */
    private function hasGenericType() : bool
    {
        return ArraySet::exists($this->type_set, function (Type $type) : bool {
            return $type->hasTemplateParameterTypes();
        });
    }

    /**
     * @return UnionType[]
     * A map from template type identifiers to the UnionType
     * to replace it with
     */
    public function getTemplateParameterTypeList() : array
    {
        if ($this->isEmpty()) {
            return [];
        }

        return array_reduce($this->type_set,
            function (array $map, Type $type) {
                return array_merge(
                    $type->getTemplateParameterTypeList(),
                    $map
                );
            },
            []
        );
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
    public function getTemplateParameterTypeMap(
        CodeBase $code_base
    ) : array {
        if ($this->isEmpty()) {
            return [];
        }

        return array_reduce($this->type_set,
            function (array $map, Type $type) use ($code_base) {
                return array_merge(
                    $type->getTemplateParameterTypeMap($code_base),
                    $map
                );
            },
            []
        );
    }


    /**
     * @param UnionType[] $template_parameter_type_map
     * A map from template type identifiers to concrete types
     *
     * @return UnionType
     * This UnionType with any template types contained herein
     * mapped to concrete types defined in the given map.
     */
    public function withTemplateParameterTypeMap(
        array $template_parameter_type_map
    ) : UnionType {

        $concrete_type_list = [];
        foreach ($this->type_set as $type) {
            if ($type instanceof TemplateType
                && isset($template_parameter_type_map[$type->getName()])
            ) {
                $union_type =
                    $template_parameter_type_map[$type->getName()];

                foreach ($union_type->type_set as $concrete_type) {
                    $concrete_type_list[] = $concrete_type;
                }
            } else {
                $concrete_type_list[] = $type;
            }
        }

        return new UnionType($concrete_type_list);
    }

    /**
     * @return bool
     * True if this union type has any types that are generic
     * types
     */
    public function hasTemplateType() : bool
    {
        return ArraySet::exists($this->type_set, function (Type $type) : bool {
            return ($type instanceof TemplateType);
        });
    }

    /**
     * @return bool
     * True if this type has a type referencing the
     * class context 'static'.
     */
    public function hasStaticType() : bool
    {
        return ArraySet::exists($this->type_set, function (Type $type) : bool {
            return $type->isStaticType();
        });
    }

    /**
     * @return UnionType
     * A new UnionType with any references to 'static' resolved
     * in the given context.
     */
    public function withStaticResolvedInContext(
        Context $context
    ) : UnionType {

        // If the context isn't in a class scope, there's nothing
        // we can do
        if (!$context->isInClassScope()) {
            return $this;
        }

        // Find the static type on the list
        $static_type = ArraySet::find($this->type_set, function(Type $type) : bool {
            return $type->isStaticType();
        });

        // If we don't actually have a static type, we're all set
        if (!$static_type) {
            return $this;
        }

        // Get a copy of this UnionType to avoid having to know
        // who has copies of it out in the wild and what they're
        // hoping for.
        $union_type = clone($this);

        // Remove the static type
        $union_type->removeType($static_type);

        // Add in the class in scope
        $union_type->addType($context->getClassFQSEN()->asType());

        return $union_type;
    }

    /**
     * @return bool
     * True if and only if this UnionType contains
     * the given type and no others.
     */
    public function isType(Type $type) : bool
    {
        if ($this->typeCount() != 1) {
            return false;
        }

        return ArraySet::contains($this->type_set, $type);
    }

    /**
     * @return bool
     * True if this UnionType is exclusively native
     * types
     */
    public function isNativeType() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return !ArraySet::exists($this->type_set, function (Type $type) : bool {
            return !$type->isNativeType();
        });
    }

    /**
     * @return bool
     * True iff this union contains the exact set of types
     * represented in the given union type.
     */
    public function isEqualTo(UnionType $union_type) : bool
    {
        $type_set = $this->getTypeSet();
        $other_type_set = $union_type->getTypeSet();
        /**
        assert(ArraySet::is_array_set($type_set));
        assert(ArraySet::is_array_set($other_type_set));
         */
        if (count($type_set) !== count($other_type_set)) {
            return false;
        }
        foreach ($type_set as $type_id => $type) {
            if (!isset($other_type_set[$type_id])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool - True if not empty and at least one type is NullType or nullable.
     */
    public function containsNullable() : bool
    {
        foreach ($this->getTypeSet() as $type) {
            if ($type->getIsNullable()) {
                return true;
            }
        }
        return false;
    }

    public function nonNullableClone() : UnionType
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
        return $result;
    }

    public function nullableClone() : UnionType
    {
        $result = new UnionType();
        foreach ($this->getTypeSet() as $type) {
            if ($type->getIsNullable()) {
                $result->addType($type);
                continue;
            }

            $result->addType($type->withIsNullable(true));
        }
        return $result;
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
    public function isExclusivelyNarrowedFormOrEquivalentTo(
        UnionType $union_type,
        Context $context,
        CodeBase $code_base
    ) : bool {

        // Special rule: anything can cast to nothing
        if ($union_type->isEmpty()) {
            return true;
        }

        // Check to see if the types are equivalent
        if ($this->isEqualTo($union_type)) {
            return true;
        }

        // Resolve 'static' for the given context to
        // determine whats actually being referred
        // to in concrete terms.
        $union_type =
            $union_type->withStaticResolvedInContext($context);

        // Convert this type to an array of resolved
        // types.
        $type_set =
            $this->withStaticResolvedInContext($context)
            ->getTypeSet();

        // Test to see if every single type in this union
        // type can cast to the given union type.
        return array_reduce($type_set,
            function (bool $can_cast, Type $type) use($union_type, $code_base) : bool {
                return (
                    $can_cast
                    && $type->asUnionType()->asExpandedTypes($code_base)->canCastToUnionType(
                        $union_type
                    )
                );
            }, true);
    }

    /**
     * @param Type[] $type_list
     * A list of types
     *
     * @return bool
     * True if this union type contains any of the given
     * named types
     */
    public function hasAnyType(array $type_list) : bool
    {
        return ArraySet::containsAny($this->type_set, $type_list);
    }

    /**
     * @return bool
     * True if this type has any subtype of `iterable` type (e.g. Traversable, Array).
     */
    public function hasIterable() : bool
    {
        return ArraySet::exists($this->type_set, function (Type $type) : bool {
            return $type->isIterable();
        });
    }

    /**
     * @return int
     * The number of types in this union type
     */
    public function typeCount() : int
    {
        return count($this->type_set);
    }

    /**
     * @return bool
     * True if this Union has no types
     */
    public function isEmpty() : bool
    {
        return ($this->typeCount() < 1);
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
    public function canCastToExpandedUnionType(
        UnionType $target,
        CodeBase $code_base
    ) : bool {

        $this_expanded =
            $this->asExpandedTypes($code_base);

        $target_expanded =
            $target->asExpandedTypes($code_base);

        return
            $this_expanded->canCastToUnionType(
                $target_expanded
            );
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
    public function canCastToUnionType(
        UnionType $target
    ) : bool {
        // Fast-track most common cases first

        // If either type is unknown, we can't call it
        // a success
        if ($this->isEmpty() || $target->isEmpty()) {
            return true;
        }

        // T === T
        if ($this->isEqualTo($target)) {
            return true;
        }

        if (Config::get()->null_casts_as_any_type) {
            // null <-> null
            if ($this->isType(NullType::instance(false))
                || $target->isType(NullType::instance(false))
            ) {
                return true;
            }
        }

        // mixed <-> mixed
        if ($target->hasType(MixedType::instance(false))
            || $this->hasType(MixedType::instance(false))
        ) {
            return true;
        }

        // int -> float
        if ($this->isType(IntType::instance(false))
            && $target->isType(FloatType::instance(false))
        ) {
            return true;
        }

        // Check conversion on the cross product of all
        // type combinations and see if any can cast to
        // any.
        foreach ($this->type_set as $source_type) {
            // TODO Noop?
            if (empty($source_type)) {
                continue;
            }

            foreach ($target->type_set as $target_type) {
                // TODO noop?
                if (empty($target_type)) {
                    continue;
                }

                if ($source_type->canCastToType($target_type)) {
                    return true;
                }
            }
        }

        // Only if no source types can be cast to any target
        // types do we say that we cannot perform the cast
        return false;
    }

    /**
     * @return bool
     * True if all types in this union are scalars
     *
     * @see \Phan\Deprecated\Util::type_scalar
     * Formerly `function type_scalar`
     */
    public function isScalar() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return !ArraySet::exists($this->type_set, function (Type $type) : bool {
            return !$type->isScalar();
        });
    }

    /**
     * @return bool
     * True if this union has array-like types (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function hasArrayLike() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return !ArraySet::exists($this->type_set, function (Type $type) : bool {
            return !$type->isArrayLike();
        });
    }

    /**
     * @return bool
     * True if this union type represents types that are
     * array-like, and nothing else (e.g. can't be null).
     * If any of the array-like types are nullable, this returns false.
     */
    public function isExclusivelyArrayLike() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return array_reduce($this->getTypeSet(),
            function (bool $is_exclusively_array, Type $type) : bool {
                return (
                    $is_exclusively_array
                    && $type->isArrayLike()
                    && !$type->getIsNullable()
                );
            }, true);
    }

    /**
     * @return bool
     * True if this union type represents types that are arrays
     * or generic arrays, but nothing else.
     */
    public function isExclusivelyArray() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return array_reduce($this->getTypeSet(),
            function (bool $is_exclusively_array, Type $type) : bool {
                return (
                    $is_exclusively_array
                    && (
                        $type === ArrayType::instance(false)
                        || $type->isGenericArray()
                    )
                );
            }, true);
    }

    /**
     * @return UnionType
     * Get the subset of types which are not native
     */
    public function nonNativeTypes() : UnionType
    {
        return $this->makeFromFilter(function (Type $type) {
            return !$type->isNativeType();
        });
    }

    /**
     * A memory efficient way to create a UnionType from a filter operation.
     * If this the filter preserves everything, calls clone() instead.
     */
    public function makeFromFilter(\Closure $cb) : UnionType {
        $newTypeSet = array_filter($this->type_set, $cb);
        if (count($newTypeSet) === count($this->type_set)) {
            return clone($this);
        }
        return new UnionType($newTypeSet, true);
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
    public function asClassList(
        CodeBase $code_base,
        Context $context
    ) {
        // Iterate over each viable class type to see if any
        // have the constant we're looking for
        foreach ($this->nonNativeTypes()->getTypeSet() as $class_type) {

            // Get the class FQSEN
            $class_fqsen = $class_type->asFQSEN();
            assert($class_fqsen instanceof FullyQualifiedClassName);

            if ($class_type->isStaticType()) {
                if (!$context->isInClassScope()) {
                    throw new IssueException(
                        Issue::fromType(Issue::ContextNotObject)(
                            $context->getFile(),
                            $context->getLineNumberStart(),
                            [
                                (string)$class_type
                            ]
                        )
                    );

                }
                yield $context->getClassInScope($code_base);
            } else {
                // See if the class exists
                if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
                    throw new CodeBaseException(
                        $class_fqsen,
                        "Cannot find class $class_fqsen"
                    );
                }

                yield $code_base->getClassByFQSEN($class_fqsen);
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
    public function nonGenericArrayTypes() : UnionType
    {
        return $this->makeFromFilter(function (Type $type) : bool {
            return !$type->isGenericArray();
        });
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
    public function nonArrayTypes() : UnionType
    {

        return new UnionType(
            array_filter($this->type_set,
                function (Type $type) : bool {
                    return !$type->isGenericArray()
                        && $type !== ArrayType::instance(false);
                }
            ),
            true
        );
    }

    /**
     * @return bool
     * True if this is exclusively generic types
     */
    public function isGenericArray() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return !ArraySet::exists($this->type_set, function (Type $type) : bool {
            return !$type->isGenericArray();
        });
    }

    /**
     * @return bool
     * True if this type has any generic types
     */
    public function hasGenericArray() : bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return ArraySet::exists($this->type_set, function (Type $type) : bool {
            return $type->isGenericArray();
        });
    }

    /**
     * Takes "a|b[]|c|d[]|e" and returns "b|d"
     *
     * @return UnionType
     * The subset of types in this
     */
    public function genericArrayElementTypes() : UnionType
    {
        $union_type = new UnionType(
            ArraySet::map(array_filter($this->type_set, function (Type $type) : bool {
                return $type->isGenericArray();
            }), function (Type $type) : Type {
                return $type->genericArrayElementType();
            })
        );

        // If array is in there, then it can be any type
        // Same for mixed
        if ($this->hasType(ArrayType::instance(false))
            || $this->hasType(MixedType::instance(false))
            || (
                Config::get()->null_casts_as_any_type
                && $this->hasType(ArrayType::instance(true))
            )
        ) {
            $union_type->addType(MixedType::instance(false));
        }

        if ($this->hasType(ArrayType::instance(false))) {
            $union_type->addType(NullType::instance(false));
        }

        return $union_type;
    }

    /**
     * @param Closure $closure
     * A closure mapping `Type` to `Type`
     *
     * @return UnionType
     * A new UnionType with each type mapped through the
     * given closure
     */
    public function asMappedUnionType(\Closure $closure) : UnionType
    {
        return new UnionType(ArraySet::map($this->type_set, $closure), true);
    }

    /**
     * @return UnionType
     * Get a new type for each type in this union which is
     * the generic array version of this type. For instance,
     * 'int|float' will produce 'int[]|float[]'.
     */
    public function asGenericArrayTypes() : UnionType
    {
        return $this->asMappedUnionType(
            function (Type $type) : Type {
                return $type->asGenericArrayType();
            }
        );
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
    public function asExpandedTypes(
        CodeBase $code_base,
        int $recursion_depth = 0
    ) : UnionType {
        assert(
            $recursion_depth < 10,
            "Recursion has gotten out of hand"
        );

        $union_type = new UnionType();
        foreach ($this->type_set as $type) {
            $union_type->addUnionType(
                $type->asExpandedTypes(
                    $code_base,
                    $recursion_depth + 1
                )
            );
        }
        return $union_type;
    }

    /**
     * As per the Serializable interface
     *
     * @return string
     * A serialized representation of this type
     *
     * @see \Serializable
     */
    public function serialize() : string
    {
        return (string)$this;
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
        $this->type_set = ArraySet::from_list(
            array_map(function (string $type_name) : Type {
                return Type::fromFullyQualifiedString($type_name);
            }, explode('|', $serialized ?? ''))
        );
    }

    /**
     * @return string
     * A human-readable string representation of this union
     * type
     */
    public function __toString() : string
    {
        // Create a new array containing the string
        // representations of each type
        $types = $this->getTypeSet();
        $type_name_list =
            array_map(function (Type $type) : string {
                return (string)$type;
            }, $types);

        // Sort the types so that we get a stable
        // representation
        asort($type_name_list);

        // Join them with a pipe
        return implode('|', $type_name_list);
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
            $map_raw = require(__DIR__.'/Internal/FunctionSignatureMap.php');
            foreach ($map_raw as $key => $value) {
                $map[strtolower($key)] = $value;
            }
        }

        return $map;
    }
}
