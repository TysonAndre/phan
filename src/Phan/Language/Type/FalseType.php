<?php declare(strict_types=1);
namespace Phan\Language\Type;

class FalseType extends ScalarType
{
    const NAME = 'false';

    public function getIsPossiblyFalsey() : bool
    {
        return true;  // it's always falsey, whether or not it's nullable.
    }

    public function getIsAlwaysFalsey() : bool
    {
        return true;  // FalseType is always falsey, whether or not it's nullable.
    }
}
