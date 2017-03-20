<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\Language\Type;

class IterableType extends NativeType
{
    const NAME = 'iterable';

    public function isIterable() : bool
    {
        return true;
    }

    /**
     * @return bool
     * True if this type is array-like (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function isArrayLike() : bool
    {
        // TODO: the base `iterable` isn't always array-like (no array access on Traversable)
        return true;
    }

    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type) : bool
    {
        // TODO: Really?
        if ($type instanceof GenericArrayType) {
            return true;
        }

        return parent::canCastToNonNullableType($type);
    }
}
