<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Config;
use Phan\Language\Type;
use Phan\Language\UnionType;
class NullType extends ScalarType
{
    const NAME = 'null';
    /**
     * @param string $name
     * The name of the type such as 'int' or 'MyClass'
     *
     * @param string $namespace
     * The (optional) namespace of the type such as '\'
     * or '\Phan\Language'.
     *
     * @param UnionType[] $template_parameter_type_list
     * A (possibly empty) list of template parameter types
     *
     * @param bool $is_nullable
     * True if this type can be null, false if it cannot
     * be null.
     */
    protected function __construct($namespace, $name, $template_parameter_type_list, $is_nullable)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to __construct() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to __construct() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        parent::__construct($namespace, $name, $template_parameter_type_list, true);
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    public function canCastToType(Type $type)
    {
        // Check to see if we have an exact object match
        if ($this === $type) {
            $ret5902c6fcb6a23 = true;
            if (!is_bool($ret5902c6fcb6a23)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcb6a23) . " given");
            }
            return $ret5902c6fcb6a23;
        }
        // Null can cast to a nullable type.
        if ($type->getIsNullable()) {
            $ret5902c6fcb6cd7 = true;
            if (!is_bool($ret5902c6fcb6cd7)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcb6cd7) . " given");
            }
            return $ret5902c6fcb6cd7;
        }
        if (Config::get()->null_casts_as_any_type) {
            $ret5902c6fcb6f44 = true;
            if (!is_bool($ret5902c6fcb6f44)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcb6f44) . " given");
            }
            return $ret5902c6fcb6f44;
        }
        // NullType is a sub-type of ScalarType. So it's affected by scalar_implicit_cast.
        if (Config::get()->scalar_implicit_cast && $type->isScalar()) {
            $ret5902c6fcb71c9 = true;
            if (!is_bool($ret5902c6fcb71c9)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcb71c9) . " given");
            }
            return $ret5902c6fcb71c9;
        }
        $ret5902c6fcb745b = false;
        if (!is_bool($ret5902c6fcb745b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fcb745b) . " given");
        }
        return $ret5902c6fcb745b;
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
        $ret5902c6fcb76c7 = $this;
        if (!$ret5902c6fcb76c7 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcb76c7) == "object" ? get_class($ret5902c6fcb76c7) : gettype($ret5902c6fcb76c7)) . " given");
        }
        return $ret5902c6fcb76c7;
    }
    public function __toString()
    {
        $ret5902c6fcb7c4b = $this->name;
        if (!is_string($ret5902c6fcb7c4b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fcb7c4b) . " given");
        }
        return $ret5902c6fcb7c4b;
    }
}