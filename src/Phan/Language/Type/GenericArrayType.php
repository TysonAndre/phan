<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\Type;
class GenericArrayType extends ArrayType
{
    const NAME = 'array';
    /**
     * @var Type|null
     * The type of every element in this array
     */
    private $element_type = null;
    /**
     * @param Type $type
     * The type of every element in this array
     *
     * @param bool $is_nullable
     * Set to true if the type should be nullable, else pass
     * false
     */
    protected function __construct(Type $type, $is_nullable)
    {
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to __construct() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        parent::__construct('\\', self::NAME, [], false);
        $this->element_type = $type;
        $this->is_nullable = $is_nullable;
    }
    /**
     * @param bool $is_nullable
     * Set to true if the type should be nullable, else pass
     * false
     *
     * @return Type
     * A new type that is a copy of this type but with the
     * given nullability value.
     */
    public function withIsNullable($is_nullable)
    {
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to withIsNullable() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if ($is_nullable === $this->is_nullable) {
            $ret5902c6fc7e01a = $this;
            if (!$ret5902c6fc7e01a instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fc7e01a) == "object" ? get_class($ret5902c6fc7e01a) : gettype($ret5902c6fc7e01a)) . " given");
            }
            return $ret5902c6fc7e01a;
        }
        $ret5902c6fc7e33b = GenericArrayType::fromElementType($this->element_type, $is_nullable);
        if (!$ret5902c6fc7e33b instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fc7e33b) == "object" ? get_class($ret5902c6fc7e33b) : gettype($ret5902c6fc7e33b)) . " given");
        }
        return $ret5902c6fc7e33b;
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type)
    {
        if ($type instanceof GenericArrayType) {
            $ret5902c6fc7e8b9 = $this->genericArrayElementType()->canCastToType($type->genericArrayElementType());
            if (!is_bool($ret5902c6fc7e8b9)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc7e8b9) . " given");
            }
            return $ret5902c6fc7e8b9;
        }
        if ($type->isArrayLike()) {
            $ret5902c6fc7eb4d = true;
            if (!is_bool($ret5902c6fc7eb4d)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc7eb4d) . " given");
            }
            return $ret5902c6fc7eb4d;
        }
        $d = strtolower((string) $type);
        if ($d[0] == '\\') {
            $d = substr($d, 1);
        }
        if ($d === 'callable') {
            $ret5902c6fc7ee71 = true;
            if (!is_bool($ret5902c6fc7ee71)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc7ee71) . " given");
            }
            return $ret5902c6fc7ee71;
        }
        $ret5902c6fc7f0d0 = parent::canCastToNonNullableType($type);
        if (!is_bool($ret5902c6fc7f0d0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc7f0d0) . " given");
        }
        return $ret5902c6fc7f0d0;
    }
    /**
     * @param Type $type
     * The element type for an array.
     *
     * @param bool $is_nullable
     * Set to true if the type should be nullable, else pass
     * false
     *
     * @return GenericArrayType
     * Get a type representing an array of the given type
     */
    public static function fromElementType(Type $type, $is_nullable)
    {
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to fromElementType() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        // Make sure we only ever create exactly one
        // object for any unique type
        static $canonical_object_map_non_nullable = null;
        static $canonical_object_map_nullable = null;
        if (!$canonical_object_map_non_nullable) {
            $canonical_object_map_non_nullable = new \SplObjectStorage();
        }
        if (!$canonical_object_map_nullable) {
            $canonical_object_map_nullable = new \SplObjectStorage();
        }
        $map = $is_nullable ? $canonical_object_map_nullable : $canonical_object_map_non_nullable;
        if (!$map->contains($type)) {
            $map->attach($type, new GenericArrayType($type, $is_nullable));
        }
        $ret5902c6fc7f4d8 = $map->offsetGet($type);
        if (!$ret5902c6fc7f4d8 instanceof GenericArrayType) {
            throw new \InvalidArgumentException("Argument returned must be of the type GenericArrayType, " . (gettype($ret5902c6fc7f4d8) == "object" ? get_class($ret5902c6fc7f4d8) : gettype($ret5902c6fc7f4d8)) . " given");
        }
        return $ret5902c6fc7f4d8;
    }
    public function isGenericArray()
    {
        $ret5902c6fc7fa77 = true;
        if (!is_bool($ret5902c6fc7fa77)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc7fa77) . " given");
        }
        return $ret5902c6fc7fa77;
    }
    /**
     * @return Type
     * A variation of this type that is not generic.
     * i.e. 'int[]' becomes 'int'.
     */
    public function genericArrayElementType()
    {
        $ret5902c6fc7fce0 = $this->element_type;
        if (!$ret5902c6fc7fce0 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fc7fce0) == "object" ? get_class($ret5902c6fc7fce0) : gettype($ret5902c6fc7fce0)) . " given");
        }
        return $ret5902c6fc7fce0;
    }
    public function __toString()
    {
        $string = "{$this->element_type}[]";
        if ($this->getIsNullable()) {
            $string = '?' . $string;
        }
        $ret5902c6fc80025 = $string;
        if (!is_string($ret5902c6fc80025)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fc80025) . " given");
        }
        return $ret5902c6fc80025;
    }
}