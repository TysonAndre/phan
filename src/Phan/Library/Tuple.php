<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * An abstract tuple.
 */
abstract class Tuple
{
    const ARITY = 0;
    /**
     * @return int
     * The arity of this tuple
     */
    public function arity()
    {
        $ret5902c6fdda3ec = static::ARITY;
        if (!is_int($ret5902c6fdda3ec)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6fdda3ec) . " given");
        }
        return $ret5902c6fdda3ec;
    }
    /**
     * @return array
     * An array of all elements in this tuple.
     */
    public abstract function toArray();
}