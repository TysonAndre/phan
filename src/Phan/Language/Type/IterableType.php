<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\Type;
class IterableType extends NativeType
{
    const NAME = 'iterable';
    public function isIterable()
    {
        $ret5902c6fc91faf = true;
        if (!is_bool($ret5902c6fc91faf)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc91faf) . " given");
        }
        return $ret5902c6fc91faf;
    }
    /**
     * @return bool
     * True if this type is array-like (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function isArrayLike()
    {
        $ret5902c6fc9248b = true;
        if (!is_bool($ret5902c6fc9248b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc9248b) . " given");
        }
        return $ret5902c6fc9248b;
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type)
    {
        // TODO: Really?
        if ($type instanceof GenericArrayType) {
            $ret5902c6fc92721 = true;
            if (!is_bool($ret5902c6fc92721)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc92721) . " given");
            }
            return $ret5902c6fc92721;
        }
        $ret5902c6fc92988 = parent::canCastToNonNullableType($type);
        if (!is_bool($ret5902c6fc92988)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc92988) . " given");
        }
        return $ret5902c6fc92988;
    }
}