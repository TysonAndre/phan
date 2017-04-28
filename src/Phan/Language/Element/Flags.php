<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

class Flags
{
    const IS_DEPRECATED = 1 << 1;
    const IS_PHP_INTERNAL = 1 << 2;
    const IS_PARENT_CONSTRUCTOR_CALLED = 1 << 3;
    const IS_RETURN_TYPE_UNDEFINED = 1 << 4;
    const HAS_RETURN = 1 << 5;
    const IS_OVERRIDE = 1 << 6;
    const HAS_YIELD = 1 << 7;
    const CLASS_HAS_DYNAMIC_PROPERTIES = 1 << 8;
    const IS_CLONE_OF_VARIADIC = 1 << 9;
    const CLASS_FORBID_UNDECLARED_MAGIC_PROPERTIES = 1 << 10;
    const CLASS_FORBID_UNDECLARED_MAGIC_METHODS = 1 << 11;
    const IS_NS_INTERNAL = 1 << 12;
    /**
     * Either enable or disable the given flag on
     * the given bit vector.
     *
     * @param int $bit_vector
     * The bit vector we're operating on
     *
     * @param int $flag
     * The flag we're setting on the bit vector such
     * as Flags::IS_DEPRECATED.
     *
     * @param bool $value
     * True to or the flag in, false to & the bit vector
     * with the flags negation
     *
     * @return int
     * A new bit vector with the given flag set or unset
     */
    public static function bitVectorWithState($bit_vector, $flag, $value)
    {
        if (!is_int($bit_vector)) {
            throw new \InvalidArgumentException("Argument \$bit_vector passed to bitVectorWithState() must be of the type int, " . (gettype($bit_vector) == "object" ? get_class($bit_vector) : gettype($bit_vector)) . " given");
        }
        if (!is_int($flag)) {
            throw new \InvalidArgumentException("Argument \$flag passed to bitVectorWithState() must be of the type int, " . (gettype($flag) == "object" ? get_class($flag) : gettype($flag)) . " given");
        }
        if (!is_bool($value)) {
            throw new \InvalidArgumentException("Argument \$value passed to bitVectorWithState() must be of the type bool, " . (gettype($value) == "object" ? get_class($value) : gettype($value)) . " given");
        }
        $bit_vector = $value ? $bit_vector | $flag : $bit_vector & ~$flag;
        $ret5902c6f578869 = $bit_vector;
        if (!is_int($ret5902c6f578869)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f578869) . " given");
        }
        return $ret5902c6f578869;
    }
    /**
     * @param int $bit_vector
     * The bit vector we'd like to get the state for
     *
     * @param int $flag
     * The flag we'd like to get the state for
     *
     * @return bool
     * True if all bits in the flag are eanbled in the bit
     * vector, else false.
     */
    public static function bitVectorHasState($bit_vector, $flag)
    {
        if (!is_int($bit_vector)) {
            throw new \InvalidArgumentException("Argument \$bit_vector passed to bitVectorHasState() must be of the type int, " . (gettype($bit_vector) == "object" ? get_class($bit_vector) : gettype($bit_vector)) . " given");
        }
        if (!is_int($flag)) {
            throw new \InvalidArgumentException("Argument \$flag passed to bitVectorHasState() must be of the type int, " . (gettype($flag) == "object" ? get_class($flag) : gettype($flag)) . " given");
        }
        $ret5902c6f5794fe = ($bit_vector & $flag) == $flag;
        if (!is_bool($ret5902c6f5794fe)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5794fe) . " given");
        }
        return $ret5902c6f5794fe;
    }
}