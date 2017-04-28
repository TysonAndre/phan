<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * A tuple of 1 element.
 *
 * @template T0
 * The type of element zero
 */
class Tuple1 extends Tuple
{
    /** @var int */
    const ARITY = 1;
    /** @var T0 */
    public $_0;
    /**
     * @param T0 $_0
     * The 0th element
     */
    public function __construct($_0)
    {
        $this->_0 = $_0;
    }
    /**
     * @return int
     * The arity of this tuple
     */
    public function arity()
    {
        $ret5902c6fdb4b43 = static::ARITY;
        if (!is_int($ret5902c6fdb4b43)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6fdb4b43) . " given");
        }
        return $ret5902c6fdb4b43;
    }
    /**
     * @return array
     * An array of all elements in this tuple.
     */
    public function toArray()
    {
        $ret5902c6fdb50bf = [$this->_0];
        if (!is_array($ret5902c6fdb50bf)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fdb50bf) . " given");
        }
        return $ret5902c6fdb50bf;
    }
}