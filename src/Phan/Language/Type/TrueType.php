<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\Language\Type;

// Not sure if it made sense to extend BoolType, so not doing that.
class TrueType extends ScalarType
{
    const NAME = 'true';
}
