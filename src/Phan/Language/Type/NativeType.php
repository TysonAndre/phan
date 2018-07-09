<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\CodeBase;
use Phan\Language\UnionType;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;

abstract class NativeType extends Type
{
    const NAME = '';

    /** @phan-override */
    const KEY_PREFIX = '!';

    /**
     * @param bool $is_nullable
     * If true, returns a nullable instance of this native type
     *
     * @return static
     */
    public static function instance(bool $is_nullable)
    {
        if ($is_nullable) {
            static $nullable_instance = null;

            if ($nullable_instance === null) {
                $nullable_instance = static::make('\\', static::NAME, [], true, Type::FROM_NODE);
            }

            return $nullable_instance;
        }

        static $instance = null;

        if ($instance === null) {
            $instance = static::make('\\', static::NAME, [], false, Type::FROM_NODE);
        }

        return $instance;
    }

    public function isNativeType() : bool
    {
        return true;
    }

    public function isSelfType() : bool
    {
        return false;
    }

    public function isArrayAccess() : bool
    {
        return false;
    }

    public function isTraversable() : bool
    {
        return false;
    }

    public function isGenerator() : bool
    {
        return false;
    }

    public function isObject() : bool
    {
        return false;
    }

    public function isObjectWithKnownFQSEN() : bool
    {
        return false;
    }

    public function isPossiblyObject() : bool
    {
        return false;
    }

    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type) : bool
    {
        // Anything can cast to mixed or ?mixed
        // Not much of a distinction in nullable mixed, except to emphasize in comments that it definitely can be null.
        // MixedType overrides the canCastTo*Type methods to always return true.
        if ($type instanceof MixedType) {
            return true;
        }

        if (!($type instanceof NativeType)
            || $this instanceof GenericArrayType
            || $type instanceof GenericArrayType
        ) {
            return parent::canCastToNonNullableType($type);
        }

        // Cast this to a native type
        \assert($type instanceof NativeType);

        // A nullable type cannot cast to a non-nullable type
        if ($this->getIsNullable() && !$type->getIsNullable()) {
            return false;
        }
        static $matrix;
        if ($matrix === null) {
            $matrix = self::initializeTypeCastingMatrix();
        }

        return $matrix[$this->getName()][$type->getName()]
            ?? parent::canCastToNonNullableType($type);
    }

    /**
     * @return array<string,array<string,bool>>
     */
    private static function initializeTypeCastingMatrix() : array
    {
        $generate_row = function (string ...$permitted_cast_type_names) {
            return [
                ArrayType::NAME    => in_array(ArrayType::NAME, $permitted_cast_type_names, true),
                IterableType::NAME => in_array(IterableType::NAME, $permitted_cast_type_names, true),
                BoolType::NAME     => in_array(BoolType::NAME, $permitted_cast_type_names, true),
                CallableType::NAME => in_array(CallableType::NAME, $permitted_cast_type_names, true),
                FalseType::NAME    => in_array(FalseType::NAME, $permitted_cast_type_names, true),
                FloatType::NAME    => in_array(FloatType::NAME, $permitted_cast_type_names, true),
                IntType::NAME      => in_array(IntType::NAME, $permitted_cast_type_names, true),
                MixedType::NAME    => true,
                NullType::NAME     => in_array(NullType::NAME, $permitted_cast_type_names, true),
                ObjectType::NAME   => in_array(ObjectType::NAME, $permitted_cast_type_names, true),
                ResourceType::NAME => in_array(ResourceType::NAME, $permitted_cast_type_names, true),
                ScalarRawType::NAME => in_array(ScalarRawType::NAME, $permitted_cast_type_names, true),
                StringType::NAME   => in_array(StringType::NAME, $permitted_cast_type_names, true),
                TrueType::NAME     => in_array(TrueType::NAME, $permitted_cast_type_names, true),
                VoidType::NAME     => in_array(VoidType::NAME, $permitted_cast_type_names, true),
            ];
        };

        // A matrix of allowable type conversions between
        // the various native types.
        // (Represented in a readable format, with only the true entries (omitting Mixed, which is always true))

        return [
            ArrayType::NAME    => $generate_row(ArrayType::NAME, IterableType::NAME, CallableType::NAME),
            BoolType::NAME     => $generate_row(BoolType::NAME, FalseType::NAME, TrueType::NAME, ScalarRawType::NAME),
            CallableType::NAME => $generate_row(CallableType::NAME),
            FalseType::NAME    => $generate_row(FalseType::NAME, BoolType::NAME, ScalarRawType::NAME),
            FloatType::NAME    => $generate_row(FloatType::NAME, ScalarRawType::NAME),
            IntType::NAME      => $generate_row(IntType::NAME, FloatType::NAME, ScalarRawType::NAME),
            IterableType::NAME => $generate_row(IterableType::NAME),
            MixedType::NAME    => $generate_row(MixedType::NAME),  // MixedType overrides the methods which would use this
            NullType::NAME     => $generate_row(NullType::NAME),
            ObjectType::NAME   => $generate_row(ObjectType::NAME),
            ResourceType::NAME => $generate_row(ResourceType::NAME),
            StringType::NAME   => $generate_row(StringType::NAME, CallableType::NAME, ScalarRawType::NAME),
            TrueType::NAME     => $generate_row(TrueType::NAME, BoolType::NAME, ScalarRawType::NAME),
            VoidType::NAME     => $generate_row(VoidType::NAME),
        ];
    }

    public function __toString() : string
    {
        // Native types can just use their
        // non-fully-qualified names
        $string = $this->name;

        if ($this->getIsNullable()) {
            $string = '?' . $string;
        }

        return $string;
    }

    public function asFQSENString() : string
    {
        return $this->name;
    }

    /**
     * @param CodeBase $code_base @phan-unused-param
     * The code base to use in order to find super classes, etc.
     *
     * @param int $recursion_depth @phan-unused-param
     * This thing has a tendency to run-away on me. This tracks
     * how bad I messed up by seeing how far the expanded types
     * go
     *
     * @return UnionType
     * Does nothing for Native Types, but GenericArrayType is an exception to that.
     * @override
     */
    public function asExpandedTypes(
        CodeBase $code_base,
        int $recursion_depth = 0
    ) : UnionType {
        return $this->asUnionType();
    }

    public function hasTemplateParameterTypes() : bool
    {
        return false;
    }

    /**
     * @return ?UnionType returns the iterable value's union type if this is a subtype of iterable, null otherwise.
     */
    public function iterableKeyUnionType(CodeBase $unused_code_base)
    {
        return null;
    }

    /**
     * @return ?UnionType returns the iterable value's union type if this is a subtype of iterable, null otherwise.
     */
    public function iterableValueUnionType(CodeBase $unused_code_base)
    {
        return null;
    }
}
\class_exists(ArrayType::class);
\class_exists(ScalarType::class);
