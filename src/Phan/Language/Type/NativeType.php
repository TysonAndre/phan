<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Config;
use Phan\Language\Type;
abstract class NativeType extends Type
{
    const NAME = '';
    /**
     * @param bool $is_nullable
     * If true, returns a nullable instance of this native type
     *
     * @return static
     */
    public static function instance($is_nullable)
    {
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to instance() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if ($is_nullable) {
            static $nullable_instance = null;
            if (empty($nullable_instance)) {
                $nullable_instance = static::make('\\', static::NAME, [], true, false);
            }
            assert($nullable_instance instanceof static);
            return $nullable_instance;
        }
        static $instance = null;
        if (empty($instance)) {
            $instance = static::make('\\', static::NAME, [], false, false);
        }
        assert($instance instanceof static);
        return $instance;
    }
    public function isNativeType()
    {
        $ret5902c6fca8b12 = true;
        if (!is_bool($ret5902c6fca8b12)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fca8b12) . " given");
        }
        return $ret5902c6fca8b12;
    }
    public function isSelfType()
    {
        $ret5902c6fca8d7a = false;
        if (!is_bool($ret5902c6fca8d7a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fca8d7a) . " given");
        }
        return $ret5902c6fca8d7a;
    }
    public function isArrayAccess()
    {
        $ret5902c6fca8fdd = false;
        if (!is_bool($ret5902c6fca8fdd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fca8fdd) . " given");
        }
        return $ret5902c6fca8fdd;
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type)
    {
        // Anything can cast to mixed or ?mixed
        // Not much of a distinction in nullable mixed, except to emphasize in comments that it definitely can be null.
        // MixedType overrides the canCastTo*Type methods to always return true.
        if ($type instanceof MixedType) {
            $ret5902c6fca9263 = true;
            if (!is_bool($ret5902c6fca9263)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fca9263) . " given");
            }
            return $ret5902c6fca9263;
        }
        if (!$type instanceof NativeType || $this instanceof GenericArrayType || $type instanceof GenericArrayType) {
            $ret5902c6fca9528 = parent::canCastToNonNullableType($type);
            if (!is_bool($ret5902c6fca9528)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fca9528) . " given");
            }
            return $ret5902c6fca9528;
        }
        // Cast this to a native type
        assert($type instanceof NativeType);
        // A nullable type cannot cast to a non-nullable type
        if ($this->getIsNullable() && !$type->getIsNullable()) {
            $ret5902c6fca97db = false;
            if (!is_bool($ret5902c6fca97db)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fca97db) . " given");
            }
            return $ret5902c6fca97db;
        }
        // A matrix of allowable type conversions between
        // the various native types.
        static $matrix = [ArrayType::NAME => [ArrayType::NAME => true, IterableType::NAME => true, BoolType::NAME => false, CallableType::NAME => true, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], IterableType::NAME => [ArrayType::NAME => false, IterableType::NAME => true, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], BoolType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => true, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], CallableType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => true, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], FloatType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => true, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], IntType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => true, IntType::NAME => true, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], MixedType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], NullType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => true, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], ObjectType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => true, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => false], ResourceType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => true, StringType::NAME => false, VoidType::NAME => false], StringType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => true, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => true, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => true, VoidType::NAME => false], VoidType::NAME => [ArrayType::NAME => false, IterableType::NAME => false, BoolType::NAME => false, CallableType::NAME => false, FloatType::NAME => false, IntType::NAME => false, MixedType::NAME => false, NullType::NAME => false, ObjectType::NAME => false, ResourceType::NAME => false, StringType::NAME => false, VoidType::NAME => true]];
        $ret5902c6fcab4b2 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$matrix[$this->getName()][$type->getName()], @parent::canCastToNonNullableType($type));
        if (!is_bool($ret5902c6fcab4b2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcab4b2) . " given");
        }
        return $ret5902c6fcab4b2;
    }
    public function __toString()
    {
        // Native types can just use their
        // non-fully-qualified names
        $string = $this->name;
        if ($this->getIsNullable()) {
            $string = '?' . $string;
        }
        $ret5902c6fcab78b = $string;
        if (!is_string($ret5902c6fcab78b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fcab78b) . " given");
        }
        return $ret5902c6fcab78b;
    }
    public function asFQSENString()
    {
        $ret5902c6fcaba22 = $this->name;
        if (!is_string($ret5902c6fcaba22)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fcaba22) . " given");
        }
        return $ret5902c6fcaba22;
    }
}