<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\Type;
class MixedType extends NativeType
{
    const NAME = 'mixed';
    // mixed or ?mixed can cast to/from anything.
    // For purposes of analysis, there's no difference between mixed and nullable mixed.
    public function canCastToType(Type $type)
    {
        $ret5902c6fc9b8b7 = true;
        if (!is_bool($ret5902c6fc9b8b7)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc9b8b7) . " given");
        }
        return $ret5902c6fc9b8b7;
    }
    // mixed or ?mixed can cast to/from anything.
    // For purposes of analysis, there's no difference between mixed and nullable mixed.
    protected function canCastToNonNullableType(Type $type)
    {
        $ret5902c6fc9be54 = true;
        if (!is_bool($ret5902c6fc9be54)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc9be54) . " given");
        }
        return $ret5902c6fc9be54;
    }
}