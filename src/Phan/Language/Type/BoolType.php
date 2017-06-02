<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\Language\UnionType;
use Phan\Language\Type;

// Temporary hack to load FalseType and TrueType before BoolType::instance() is called
// (Due to bugs in php static variables)
assert(class_exists(FalseType::class));
assert(class_exists(TrueType::class));

class BoolType extends ScalarType
{
    const NAME = 'bool';
    public static function unionTypeInstance(bool $is_nullable) : UnionType
    {
        // Optimized equivalent of `return new UnionType([FalseType::instance($is_nullable), TrueType::instance($is_nullable)]);`
        if ($is_nullable) {
            static $nullable_instance = null;
            if ($nullable_instance === null) {
                $nullable_instance = new UnionType([FalseType::instance(true), TrueType::instance(true)]);
            }
            return clone($nullable_instance);
        }
        static $instance = null;
        if ($instance === null) {
            $instance = new UnionType([FalseType::instance(false), TrueType::instance(false)]);
        }
        return clone($instance);
    }

    public function getIsPossiblyFalsey() : bool
    {
        return true;  // it's always falsey, since this is conceptually a collection of FalseType and TrueType
    }

    public function asNonFalseyType() : Type
    {
        return TrueType::instance(false);
    }
}
