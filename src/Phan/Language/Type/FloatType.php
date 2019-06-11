<?php declare(strict_types=1);

namespace Phan\Language\Type;

use Phan\Config;

/**
 * Phan's representation of the type for `float`
 */
final class FloatType extends ScalarType
{
    /** @phan-override */
    const NAME = 'float';

    /** @override */
    public function isPossiblyNumeric() : bool
    {
        return true;
    }

    public function isValidBitwiseOperand() : bool
    {
        return Config::getValue('scalar_implicit_cast');
    }

    public function isPossiblyTruthy() : bool
    {
        return true;
    }

    public function isPossiblyFalsey() : bool
    {
        return true;
    }

    public function isAlwaysTruthy() : bool
    {
        return false;
    }

    public function isAlwaysFalsey() : bool
    {
        return false;
    }
}
