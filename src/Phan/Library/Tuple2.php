<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * A tuple of 2 elements.
 *
 * @template T0
 * The type of element zero
 *
 * @template T1
 * The type of element one
 *
 * @inherits Tuple1<T0>
 */
class Tuple2 extends Tuple1
{
    /** @var int */
    const ARITY = 2;
    /** @var T1 */
    public $_1;
    /**
     * @param T0 $_0
     * The 0th element
     *
     * @param T1 $_1
     * The 1st element
     */
    public function __construct($_0, $_1)
    {
        parent::__construct($_0);
        $this->_1 = $_1;
    }
    /**
     * @return array
     * An array of all elements in this tuple.
     */
    public function toArray()
    {
        $ret5902c6fdbe0c0 = [$this->_0, $this->_1];
        if (!is_array($ret5902c6fdbe0c0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fdbe0c0) . " given");
        }
        return $ret5902c6fdbe0c0;
    }
}