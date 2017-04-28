<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\CodeBase;
use Phan\Config;
use Phan\Language\Type;
abstract class ScalarType extends NativeType
{
    public function isScalar()
    {
        $ret5902c6fd0ba20 = true;
        if (!is_bool($ret5902c6fd0ba20)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0ba20) . " given");
        }
        return $ret5902c6fd0ba20;
    }
    public function isSelfType()
    {
        $ret5902c6fd0bf23 = false;
        if (!is_bool($ret5902c6fd0bf23)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0bf23) . " given");
        }
        return $ret5902c6fd0bf23;
    }
    public function isStaticType()
    {
        $ret5902c6fd0c18d = false;
        if (!is_bool($ret5902c6fd0c18d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0c18d) . " given");
        }
        return $ret5902c6fd0c18d;
    }
    public function isIterable()
    {
        $ret5902c6fd0c3e7 = false;
        if (!is_bool($ret5902c6fd0c3e7)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0c3e7) . " given");
        }
        return $ret5902c6fd0c3e7;
    }
    public function isArrayLike()
    {
        $ret5902c6fd0c68c = false;
        if (!is_bool($ret5902c6fd0c68c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0c68c) . " given");
        }
        return $ret5902c6fd0c68c;
    }
    public function isGenericArray()
    {
        $ret5902c6fd0c8eb = false;
        if (!is_bool($ret5902c6fd0c8eb)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0c8eb) . " given");
        }
        return $ret5902c6fd0c8eb;
    }
    /**
     * @param CodeBase $code_base
     *
     * @param Type $parent
     *
     * @return bool
     * True if this type represents a class which is a sub-type of
     * the class represented by the passed type.
     */
    public function isSubclassOf(CodeBase $code_base, Type $parent)
    {
        $ret5902c6fd0cb6b = false;
        if (!is_bool($ret5902c6fd0cb6b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0cb6b) . " given");
        }
        return $ret5902c6fd0cb6b;
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type)
    {
        // Scalars may be configured to always cast to eachother
        if (Config::get()->scalar_implicit_cast && $type->isScalar()) {
            $ret5902c6fd0ce1b = true;
            if (!is_bool($ret5902c6fd0ce1b)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0ce1b) . " given");
            }
            return $ret5902c6fd0ce1b;
        }
        $ret5902c6fd0d082 = parent::canCastToNonNullableType($type);
        if (!is_bool($ret5902c6fd0d082)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd0d082) . " given");
        }
        return $ret5902c6fd0d082;
    }
}