<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * A tuple of 3 elements.
 *
 * @template T0
 * The type of element zero
 *
 * @template T1
 * The type of element one
 *
 * @template T2
 * The type of element one
 *
 * @inherits Tuple2<T0, T1>
 */
class Tuple3 extends Tuple2
{
    /** @var int */
    const ARITY = 3;
    /** @var T2 */
    public $_2;
    /**
     * @param T0 $_0
     * The 0th element
     *
     * @param T1 $_1
     * The 1st element
     *
     * @param T2 $_2
     * The 2nd element
     */
    public function __construct($_0, $_1, $_2)
    {
        parent::__construct($_0, $_1);
        $this->_2 = $_2;
    }
    /**
     * @return array
     * An array of all elements in this tuple.
     */
    public function toArray()
    {
        $ret5902c6fdc7967 = [$this->_0, $this->_1, $this->_2];
        if (!is_array($ret5902c6fdc7967)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fdc7967) . " given");
        }
        return $ret5902c6fdc7967;
    }
}