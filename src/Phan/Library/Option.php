<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * @template T
 * The type of the element
 */
abstract class Option
{
    /**
     * @param T $else
     * @return T
     */
    public abstract function getOrElse($else);
    /**
     * @return bool
     */
    public abstract function isDefined();
    /**
     * @return T
     */
    public abstract function get();
}