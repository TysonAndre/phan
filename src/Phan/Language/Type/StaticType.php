<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\Type;
final class StaticType extends Type
{
    const NAME = 'static';
    /**
     * @param bool $is_nullable
     * An optional parameter, which if true returns a
     * nullable instance of this native type
     *
     * @return static
     */
    public static function instance($is_nullable)
    {
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to instance() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if ($is_nullable) {
            static $nullable_instance = null;
            if (empty($nullable_instance)) {
                $nullable_instance = static::make('\\', static::NAME, [], true, false);
            }
            assert($nullable_instance instanceof static);
            $ret5902c6fd16e0a = $nullable_instance;
            if (!$ret5902c6fd16e0a instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fd16e0a) == "object" ? get_class($ret5902c6fd16e0a) : gettype($ret5902c6fd16e0a)) . " given");
            }
            return $ret5902c6fd16e0a;
        }
        static $instance;
        if (empty($instance)) {
            $instance = static::make('\\', static::NAME, [], false, false);
            assert($instance instanceof static);
        }
        assert($instance instanceof static);
        $ret5902c6fd17444 = $instance;
        if (!$ret5902c6fd17444 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fd17444) == "object" ? get_class($ret5902c6fd17444) : gettype($ret5902c6fd17444)) . " given");
        }
        return $ret5902c6fd17444;
    }
    public function isNativeType()
    {
        $ret5902c6fd17991 = false;
        if (!is_bool($ret5902c6fd17991)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd17991) . " given");
        }
        return $ret5902c6fd17991;
    }
    public function isSelfType()
    {
        $ret5902c6fd17bf6 = false;
        if (!is_bool($ret5902c6fd17bf6)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd17bf6) . " given");
        }
        return $ret5902c6fd17bf6;
    }
    public function isStaticType()
    {
        $ret5902c6fd17e59 = true;
        if (!is_bool($ret5902c6fd17e59)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd17e59) . " given");
        }
        return $ret5902c6fd17e59;
    }
    public function __toString()
    {
        $string = $this->name;
        if ($this->getIsNullable()) {
            $string = '?' . $string;
        }
        $ret5902c6fd18154 = $string;
        if (!is_string($ret5902c6fd18154)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd18154) . " given");
        }
        return $ret5902c6fd18154;
    }
}