<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * @inherits Option<null>
 */
class None extends Option
{
    /**
     * Get a new instance of nothing
     */
    public function __construct()
    {
    }
    /**
     * @return bool
     */
    public function isDefined()
    {
        $ret5902c6fd8c605 = false;
        if (!is_bool($ret5902c6fd8c605)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd8c605) . " given");
        }
        return $ret5902c6fd8c605;
    }
    /**
     * @param mixed $else
     * @return mixed
     */
    public function getOrElse($else)
    {
        return $else;
    }
    /**
     * @return null
     */
    public function get()
    {
        throw new \Exception("Cannot call get on None");
    }
    /**
     * @return string
     * A string representation of this object
     */
    public function __tostring()
    {
        $ret5902c6fd8ca86 = 'None()';
        if (!is_string($ret5902c6fd8ca86)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd8ca86) . " given");
        }
        return $ret5902c6fd8ca86;
    }
}