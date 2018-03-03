<?php declare(strict_types=1);
namespace Phan\Language;

use Phan\CodeBase;
use Phan\Config;
use Phan\Exception\CodeBaseException;
use Phan\Issue;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\ArrayShapeType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\FloatType;
use Phan\Language\Type\GenericArrayType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\StaticType;
use Phan\Language\Type\TemplateType;
use Phan\Exception\IssueException;

/**
 * NOTE: there may also be instances of UnionType that are singletons, due to the constructor being public
 */
final class SingletonUnionType extends UnionType
{
    /** @var Type the only type in this singleton*/
    private $type;

    /**
     * An optional list of types represented by this union
     * @internal
     */
    public function __construct(Type $type)
    {
        parent::__construct([], true);
        $this->type = $type;
    }

    // no need to override getTypeSet()

    /**
     * Add a type name to the list of types
     *
     * @return UnionType
     * @override
     */
    public function withType(Type $type)
    {
        $other_type = $this->type;
        if ($type === $other_type) {
            return $this;
        }
        return new UnionType([$other_type, $type], true);
    }

    /**
     * Returns a new union type
     * which removes this type from the list of types,
     * keeping the keys in a consecutive order.
     *
     * Each type in $this->type_set occurs exactly once.
     *
     * @return UnionType
     * @override
     */
    public function withoutType(Type $type)
    {
        if ($type === $this->type) {
            return self::$empty_instance;
        }
        return $this;
    }

    /**
     * @return bool
     * True if this union type contains the given named
     * type.
     * @override
     */
    public function hasType(Type $type) : bool
    {
        return $this->type === $type;
    }

    /**
     * Returns a union type which add the given types to this type
     *
     * @return UnionType
     * @override
     */
    public function withUnionType(UnionType $union_type)
    {
        $type_set = $union_type->type_set;
        $type = $this->type;
        if (\in_array($type, $type_set, true)) {
            return $union_type;
        }
        if (\count($type_set) === 0) {
            return $this;
        }
        $type_set[] = $type;
        return new UnionType($type_set, true);
    }

    /**
     * @return bool
     * True if this type has a type referencing the
     * class context in which it exists such as 'self'
     * or '$this'
     * @override
     */
    public function hasSelfType() : bool
    {
        return $this->type->isSelfType();
    }

    /**
     * @return bool
     * True if this union type has any types that are bool/false/true types
     * @override
     */
    public function hasTypeInBoolFamily() : bool
    {
        return $this->type->getIsInBoolFamily();
    }

    /**
     * @return UnionType[]
     * A map from template type identifiers to the UnionType
     * to replace it with
     * @override
     */
    public function getTemplateParameterTypeList() : array
    {
        return $this->type->getTemplateParameterTypeList();
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
        return $this->type->getTemplateParameterTypeMap($code_base);
    }


    // not overriding withTemplateParameterTypeMap

    /**
     * @return bool
     * True if this union type has any types that are generic
     * types
     * @override
     */
    public function hasTemplateType() : bool
    {
        return $this->type instanceof TemplateType;
    }

    /**
     * @return bool
     * True if this type has a type referencing the
     * class context 'static'.
     * @override
     */
    public function hasStaticType() : bool
    {
        return $this->type instanceof StaticType;
    }

    /**
     * @return bool
     * True if and only if this UnionType contains
     * the given type and no others.
     * @override
     */
    public function isType(Type $type) : bool
    {
        return $this->type === $type;
    }

    /**
     * @return bool
     * True if this UnionType is exclusively native
     * types
     * @override
     */
    public function isNativeType() : bool
    {
        return $this->type->isNativeType();
    }

    /**
     * @return bool
     * True iff this union contains the exact set of types
     * represented in the given union type.
     * @override
     */
    public function isEqualTo(UnionType $union_type) : bool
    {
        $type_set = $union_type->type_set;
        return \count($type_set) === 1 && \reset($type_set) === $this->type;
    }

    /**
     * @return bool
     * True iff this union contains a type that's also in
     * the other union type.
     */
    public function hasCommonType(UnionType $union_type) : bool
    {
        return \in_array($this->type, $union_type->type_set, true);
    }

    /**
     * @return bool - True if not empty and at least one type is NullType or nullable.
     */
    public function containsNullable() : bool
    {
        return $this->type->getIsNullable();
    }

    /** @override */
    public function nonNullableClone() : UnionType
    {
        $type = $this->type;
        if (!$type->getIsNullable()) {
            return $this;
        }
        if ($type instanceof NullType) {
            return self::$empty_instance;
        }
        return $type->withIsNullable(false)->asUnionType();
    }

    /** @override */
    public function nullableClone() : UnionType
    {
        $type = $this->type;
        if ($type->getIsNullable()) {
            return $this;
        }
        return $type->withIsNullable(true)->asUnionType();
    }

    /**
     * @return bool - True if type set is not empty and at least one type is NullType or nullable or FalseType or BoolType.
     * (I.e. the type is always falsey, or both sometimes falsey with a non-falsey type it can be narrowed down to)
     * This does not include values such as `IntType`, since there is currently no `NonZeroIntType`.
     * @override
     */
    public function containsFalsey() : bool
    {
        return $this->type->getIsPossiblyFalsey();
    }

    /** @override */
    public function nonFalseyClone() : UnionType
    {
        $type = $this->type;
        if (!$type->getIsPossiblyFalsey()) {
            return $this;
        }
        if ($type->getIsAlwaysFalsey()) {
            return self::$empty_instance;
        }

        // add non-nullable equivalents, and replace BoolType with non-nullable TrueType
        return $type->asNonFalseyType()->asUnionType();
    }

    /**
     * @return bool - True if type set is not empty and at least one type is NullType or nullable or FalseType or BoolType.
     * (I.e. the type is always falsey, or both sometimes falsey with a non-falsey type it can be narrowed down to)
     * This does not include values such as `IntType`, since there is currently no `NonZeroIntType`.
     * @override
     */
    public function containsTruthy() : bool
    {
        return $this->type->getIsPossiblyTruthy();
    }

    /** @override */
    public function nonTruthyClone() : UnionType
    {
        $type = $this->type;
        if (!$type->getIsPossiblyTruthy()) {
            return $this;
        }
        $did_change = true;
        if ($type->getIsAlwaysTruthy()) {
            return self::$empty_instance;
        }

        // add non-nullable equivalents, and replace BoolType with non-nullable TrueType
        return $type->asNonTruthyType()->asUnionType();
    }

    /**
     * @return bool - True if type set is not empty and at least one type is BoolType or FalseType
     * @override
     */
    public function containsFalse() : bool
    {
        return $this->type->getIsPossiblyFalse();
    }

    /**
     * @return bool - True if type set is not empty and at least one type is BoolType or TrueType
     * @override
     */
    public function containsTrue() : bool
    {
        return $this->type->getIsPossiblyTrue();
    }

    public function nonFalseClone() : UnionType
    {
        $type = $this->type;
        if (!$type->getIsPossiblyFalse()) {
            return $this;
        }
        $did_change = true;
        if ($type->getIsAlwaysFalse()) {
            return self::$empty_instance;
        }

        // add non-nullable equivalents, and replace BoolType with non-nullable TrueType
        return $type->asNonFalseType()->asUnionType();
    }

    public function nonTrueClone() : UnionType
    {
        return $this;
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
        return \in_array($this->type, $type_list, true);
    }

    /**
     * @return bool
     * True if this type has any subtype of `iterable` type (e.g. Traversable, Array).
     */
    public function hasIterable() : bool
    {
        return $this->type->isIterable();
    }

    /**
     * @return int
     * The number of types in this union type
     */
    public function typeCount() : int
    {
        return 1;
    }

    /**
     * @return bool
     * True if this Union has no types
     */
    public function isEmpty() : bool
    {
        return false;
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
        $this_expanded = $this->type->asExpandedTypes($code_base);
        $target_expanded = $target->asExpandedTypes($code_base);
        return $this_expanded->canCastToUnionType($target_expanded);
    }

    /**
     * @param UnionType $target
     * A type to check to see if this can cast to it
     *
     * @return bool
     * True if this type is allowed to cast to the given type
     * i.e. int->float is allowed  while float->int is not.
     *
     * @see UnionType->canCastToUnionType
     *
     * TODO: Move to class Type
     */
    public function canCastToUnionType(
        UnionType $target
    ) : bool {
        // T overlaps with T, a future call to Type->canCastToType will pass.
        $target_type_set = $target->type_set;
        if (\count($target_type_set) === 0) {
            return true;
        }
        $source_type = $this->type;
        if (\in_array($source_type, $target_type_set, true)) {
            return true;
        }
        static $float_type;
        static $int_type;
        static $mixed_type;
        static $null_type;
        if ($null_type === null) {
            $int_type   = IntType::instance(false);
            $float_type = FloatType::instance(false);
            $mixed_type = MixedType::instance(false);
            $null_type  = NullType::instance(false);
        }

        if (Config::get_null_casts_as_any_type()) {
            // null <-> null
            if ($source_type === $null_type
                || $target->isType($null_type)
            ) {
                return true;
            }
        } else {
            // If null_casts_as_any_type isn't set, then try the other two fallbacks.
            if (Config::get_null_casts_as_array() && $source_type === $null_type && $target->hasArrayLike()) {
                return true;
            } elseif (Config::get_array_casts_as_null() && $target->isType($null_type) && $this->hasArrayLike()) {
                return true;
            }
        }

        // mixed <-> mixed
        if ($this === $mixed_type
            || \in_array($mixed_type, $target_type_set, true)
        ) {
            return true;
        }

        // int -> float
        if ($this === $int_type
            && \in_array($float_type, $target_type_set, true)
        ) {
            return true;
        }

        // Check conversion on the cross product of all
        // type combinations and see if any can cast to
        // any.
        foreach ($target_type_set as $target_type) {
            if ($source_type->canCastToType($target_type)) {
                return true;
            }
        }

        // Allow casting ?T to T|null for any type T. Check if null is part of this type first.
        if (\in_array($null_type, $target_type_set, true)) {
            // Only redo this check for the nullable types, we already failed the checks for non-nullable types.
            if ($source_type->getIsNullable()) {
                $non_null_source_type = $source_type->withIsNullable(false);
                foreach ($target_type_set as $target_type) {
                    if ($non_null_source_type->canCastToType($target_type)) {
                        return true;
                    }
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
     */
    public function isScalar() : bool
    {
        return $this->type->isScalar();
    }

    /**
     * @return bool
     * True if this union has array-like types (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function hasArrayLike() : bool
    {
        return $this->type->isArrayLike();
    }

    /**
     * @return bool
     * True if this union has array-like types (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function hasGenericArray() : bool
    {
        return $this->type->isGenericArray();
    }

    /**
     * @return bool
     * True if this union contains the ArrayAccess type.
     * (Call asExpandedTypes() first to check for subclasses of ArrayAccess)
     */
    public function hasArrayAccess() : bool
    {
        return $this->type->isArrayAccess();
    }

    /**
     * @return bool
     * True if this union contains the Traversable type.
     * (Call asExpandedTypes() first to check for subclasses of Traversable)
     */
    public function hasTraversable() : bool
    {
        return $this->type->isTraversable();
    }

    /**
     * @return bool
     * True if this union type represents types that are
     * array-like, and nothing else (e.g. can't be null).
     * If any of the array-like types are nullable, this returns false.
     */
    public function isExclusivelyArrayLike() : bool
    {
        $type = $this->type;
        return $type->isArrayLike() && !$type->getIsNullable();
    }

    /**
     * @return bool
     * True if this union type represents types that are arrays
     * or generic arrays, but nothing else.
     * @suppress PhanUnreferencedPublicMethod
     */
    public function isExclusivelyArray() : bool
    {
        $type = $this->type;
        return $type === ArrayType::instance(false) || !$type->isGenericArray();
    }

    /**
     * @return UnionType
     * Get the subset of types which are not native
     */
    public function nonNativeTypes() : UnionType
    {
        return $this->type->isNativeType() ? self::$empty_instance : $this;
    }

    /**
     * A memory efficient way to create a UnionType from a filter operation.
     * If this the filter preserves everything, returns $this instead
     */
    public function makeFromFilter(\Closure $cb) : UnionType
    {
        return $cb($this->type) ? $this : self::$empty_instance;
    }

    /**
     * @param Context $context
     * The context in which we're resolving this union
     * type.
     *
     * @return iterable
     *
     * A list of class FQSENs representing the non-native types
     * associated with this UnionType
     *
     * @throws CodeBaseException
     * An exception is thrown if a non-native type does not have
     * an associated class
     *
     * @throws IssueException
     * An exception is thrown if static is used as a type outside of an object
     * context
     *
     * TODO: Add a method to ContextNode to directly get FQSEN instead?
     */
    public function asClassFQSENList(
        Context $context
    ) {
        $class_type = $this->type;
        if ($class_type->isNativeType()) {
            return [];
        }
        // Get the class FQSEN
        $class_fqsen = $class_type->asFQSEN();

        if ($class_type->isStaticType()) {
            if (!$context->isInClassScope()) {
                throw new IssueException(
                    Issue::fromType(Issue::ContextNotObject)(
                        $context->getFile(),
                        $context->getLineNumberStart(),
                        [
                            $class_type->getName()
                        ]
                    )
                );
            }
        }
        return [$class_fqsen];
    }

    /**
     * @param CodeBase $code_base
     * The code base in which to find classes
     *
     * @param Context $context
     * The context in which we're resolving this union
     * type.
     *
     * @return iterable
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
        $class_type = $this->type;
        if ($class_type->isNativeType()) {
            return [];
        }
        // Get the class FQSEN
        $class_fqsen = FullyQualifiedClassName::fromType($class_type);

        if ($class_type->isStaticType()) {
            if (!$context->isInClassScope()) {
                throw new IssueException(
                    Issue::fromType(Issue::ContextNotObject)(
                        $context->getFile(),
                        $context->getLineNumberStart(),
                        [
                            $class_type->getName()
                        ]
                    )
                );
            }
            return [$context->getClassInScope($code_base)];
        }
        if ($class_type->isSelfType()) {
            if (!$context->isInClassScope()) {
                throw new IssueException(
                    Issue::fromType(Issue::ContextNotObject)(
                        $context->getFile(),
                        $context->getLineNumberStart(),
                        [
                            $class_type->getName()
                        ]
                    )
                );
            }
            if (strcasecmp($class_type->getName(), 'self') === 0) {
                return [$context->getClassInScope($code_base)];
            } else {
                return [$class_type];
            }
        }
        // See if the class exists
        if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
            throw new CodeBaseException(
                $class_fqsen,
                "Cannot find class $class_fqsen"
            );
        }

        return [$code_base->getClassByFQSEN($class_fqsen)];
    }

    /**
     * Takes "a|b[]|c|d[]|e" and returns "a|c|e"
     *
     * @return UnionType
     * A UnionType with generic array types filtered out
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function nonGenericArrayTypes() : UnionType
    {
        return $this->type->isGenericArray() ? self::$empty_instance : $this;
    }

    /**
     * Takes "a|b[]|c|d[]|e" and returns "b[]|d[]"
     *
     * @return UnionType
     * A UnionType with generic array types kept, other types filtered out.
     *
     * @see nonGenericArrayTypes
     */
    public function genericArrayTypes() : UnionType
    {
        return $this->type->isGenericArray() ? $this : self::$empty_instance;
    }

    /**
     * Takes "MyClass|int|array|?object" and returns "MyClass|?object"
     *
     * @return UnionType
     * A UnionType with known object types kept, other types filtered out.
     *
     * @see nonGenericArrayTypes
     */
    public function objectTypes() : UnionType
    {
        return $this->type->isObject() ? $this : self::$empty_instance;
    }

    /**
     * Returns true if objectTypes would be non-empty.
     *
     * @return bool
     */
    public function hasObjectTypes() : bool
    {
        return $this->type->isObject();
    }

    public function hasPossiblyObjectTypes() : bool
    {
        return $this->type->isObject();
    }

    /**
     * Returns the types for which is_scalar($x) would be true.
     * This means null/nullable is removed.
     * Takes "MyClass|int|?bool|array|?object" and returns "int|bool"
     * Takes "?MyClass" and returns an empty union type.
     *
     * @return UnionType
     * A UnionType with known scalar types kept, other types filtered out.
     *
     * @see nonGenericArrayTypes
     */
    public function scalarTypes() : UnionType
    {
        $type = $this->type;
        return $type->isScalar() && !($type instanceof NullType) ? $this : self::$empty_instance;
    }

    /**
     * Returns the types for which is_callable($x) would be true.
     * TODO: Check for __invoke()?
     * Takes "Closure|false" and returns "Closure"
     * Takes "?MyClass" and returns an empty union type.
     *
     * @return UnionType
     * A UnionType with known callable types kept, other types filtered out.
     *
     * @see nonGenericArrayTypes
     */
    public function callableTypes() : UnionType
    {
        return $this->type->isCallable() ? $this : self::$empty_instance;
    }

    /**
     * Returns true if this has one or more callable types
     * TODO: Check for __invoke()?
     * Takes "Closure|false" and returns true
     * Takes "?MyClass" and returns false
     *
     * @return bool
     * A UnionType with known callable types kept, other types filtered out.
     *
     * @see $this->callableTypes()
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function hasCallableType() : bool
    {
        return $this->type->isCallable();
    }

    /**
     * Returns true if every type in this type is callable.
     * TODO: Check for __invoke()?
     * Takes "callable" and returns true
     * Takes "callable|false" and returns false
     *
     * @return bool
     * A UnionType with known callable types kept, other types filtered out.
     *
     * @see nonGenericArrayTypes
     */
    public function isExclusivelyCallable() : bool
    {
        return $this->type->isCallable();
    }

    public function isExclusivelyBoolTypes() : bool
    {
        $type = $this->type;
        return $type->getIsInBoolFamily() && $type->getIsNullable();
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
        $type = $this->type;
        if (!$type->isGenericArray() && $type !== ArrayType::instance(false)) {
            return self::$empty_instance;
        }
        return $this;
    }

    /**
     * @return bool
     * True if this is exclusively generic types
     */
    public function isGenericArray() : bool
    {
        return $this->type->isGenericArray();
    }

    /**
     * @return bool
     * True if any of the types in this UnionType made $matcher_callback return true
     */
    public function hasTypeMatchingCallback(\Closure $matcher_callback) : bool
    {
        return $matcher_callback($this->type);
    }

    /**
     * @return Type|false
     * Returns the first type in this UnionType made $matcher_callback return true
     */
    public function findTypeMatchingCallback(\Closure $matcher_callback)
    {
        $type = $this->type;
        return $matcher_callback($type) ? $type : false;
    }

    /**
     * Takes "a|b[]|c|d[]|e" and returns "b|d"
     *
     * @return UnionType
     * The subset of types in this
     */
    public function genericArrayElementTypes() : UnionType
    {
        // This is frequently called, and has been optimized
        $type = $this->type;
        if ($type->isGenericArray()) {
            if ($type instanceof ArrayShapeType) {
                return $type->genericArrayElementUnionType();
            } else {
                return $type->genericArrayElementType()->asUnionType();
            }
        }

        static $array_type_nonnull = null;
        static $array_type_nullable = null;
        static $mixed_type = null;
        static $null_type = null;
        if ($array_type_nonnull === null) {
            $array_type_nonnull = ArrayType::instance(false);
            $array_type_nullable = ArrayType::instance(true);
            $mixed_type = MixedType::instance(false);
            $null_type = NullType::instance(false);
        }

        // If array is in there, then it can be any type
        if ($array_type_nonnull === $type) {
            return new UnionType([$mixed_type, $null_type], true);
        } elseif ($mixed_type === $type || $array_type_nullable === $type) {
            return $mixed_type->asUnionType();
        }

        return self::$empty_instance;
    }

    /**
     * Takes "b|d[]" and returns "b[]|d[][]"
     *
     * @param int $key_type
     * Corresponds to the type of the array keys. Set this to a GenericArrayType::KEY_* constant.
     *
     * @return UnionType
     * The subset of types in this
     */
    public function elementTypesToGenericArray(int $key_type) : UnionType
    {
        $type = $this->type;
        if ($type instanceof MixedType) {
            return ArrayType::instance(false)->asUnionType();
        }
        return GenericArrayType::fromElementType($type, false, $key_type)->asUnionType();
    }

    /**
     * @param \Closure $closure
     * A closure mapping `Type` to `Type`
     *
     * @return UnionType
     * A new UnionType with each type mapped through the
     * given closure
     */
    public function asMappedUnionType(\Closure $closure) : UnionType
    {
        return $closure($this)->asUnionType();
    }

    /**
     * @param int $key_type
     * Corresponds to the type of the array keys. Set this to a GenericArrayType::KEY_* constant.
     *
     * @return UnionType
     * Get a new type for each type in this union which is
     * the generic array version of this type. For instance,
     * 'int|float' will produce 'int[]|float[]'.
     *
     * If $this is an empty UnionType, this method will produce an empty UnionType
     */
    public function asGenericArrayTypes(int $key_type) : UnionType
    {
        return $this->type->asGenericArrayType($key_type)->asUnionType();
    }

    /**
     * @return UnionType
     * Get a new type for each type in this union which is
     * the generic array version of this type. For instance,
     * 'int|float' will produce 'int[]|float[]'.
     *
     * If $this is an empty UnionType, this method will produce 'array'
     */
    public function asNonEmptyGenericArrayTypes(int $key_type) : UnionType
    {
        // TODO: Be more precise for ArrayShapeType
        return $this->type->asGenericArrayType($key_type)->asUnionType();
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
        return $this->type->asExpandedTypes($code_base, $recursion_depth + 1);
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
        return $this->type->__toString();
    }

    /**
     * @return string
     * A human-readable string representation of this union
     * type
     */
    public function __toString() : string
    {
        return $this->type->__toString();
    }

    /**
     * @return UnionType - A normalized version of this union type (May or may not be the same object, if no modifications were made)
     *
     * The following normalization rules apply
     *
     * 1. If one of the types is null or nullable, convert all types to nullable and remove "null" from the union type
     * 2. If both "true" and "false" (possibly nullable) coexist, or either coexists with "bool" (possibly nullable),
     *    then remove "true" and "false"
     */
    public function asNormalizedTypes() : UnionType
    {
        return $this;
    }

    public function generateUniqueId() : string {
        return (string)\spl_object_id($this->type);
    }

    public function hasTopLevelArrayShapeTypeInstances() : bool
    {
        return $this->type instanceof ArrayShapeType;
    }

    /** @override */
    public function hasTopLevelNonArrayShapeTypeInstances() : bool
    {
        return !($this->type instanceof ArrayShapeType);
    }

    /** @override */
    public function withFlattenedArrayShapeTypeInstances() : UnionType
    {
        $type = $this->type;
        if (!($type instanceof ArrayShapeType)) {
            return $this;
        }
        return UnionType::of($type->withFlattenedArrayShapeTypeInstances());
    }
}
