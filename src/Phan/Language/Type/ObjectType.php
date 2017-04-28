<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\Type;
class ObjectType extends NativeType
{
    const NAME = 'object';
    protected function canCastToNonNullableType(Type $type)
    {
        // Inverse of check in Type->canCastToNullableType
        if (!$type->isNativeType() && !$type instanceof ArrayType) {
            $ret5902c6fcc15c8 = true;
            if (!is_bool($ret5902c6fcc15c8)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcc15c8) . " given");
            }
            return $ret5902c6fcc15c8;
        }
        $ret5902c6fcc1a36 = parent::canCastToNonNullableType($type);
        if (!is_bool($ret5902c6fcc1a36)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcc1a36) . " given");
        }
        return $ret5902c6fcc1a36;
    }
}