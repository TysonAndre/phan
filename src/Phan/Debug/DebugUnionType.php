<?php declare(strict_types=1);

namespace Phan\Debug;

use Phan\Language\Type;
use Phan\Language\UnionType;

/**
 * Utility for debugging assignments to a given union type
 */
class DebugUnionType extends UnionType
{

    /**
     * Add a type name to the list of types
     *
     * @return void
     * @override
     */
    public function addType(Type $type)
    {
        printf("%x: Adding type %s to %s", \runkit_object_id($this), (string)$type, (string)$this);
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        parent::addType($type);
    }

    /**
     * Add the given types to this type
     *
     * @return void
     */
    public function addUnionType(UnionType $union_type)
    {
        printf("%x: Adding union type %s to %s", \runkit_object_id($this), (string)$union_type, (string)$this);
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        parent::addUnionType($union_type);
    }
}
