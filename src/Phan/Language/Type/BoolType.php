<?php declare(strict_types=1);
namespace Phan\Language\Type;

use Phan\Language\UnionType;
// Temporary hack to load FalseType and TrueType before BoolType can be loaded
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
}
