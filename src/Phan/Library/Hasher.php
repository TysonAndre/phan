<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * An interface to map strings to integers representing groups.
 * getGroup() is called exactly once on each string to be hashed.
 */
interface Hasher
{
    /**
     * Returns an integer between 0 and the number of groups - 1
     */
    public function getGroup($key);
    /**
     * @return void
     * If there is any state, clear it.
     */
    public function reset();
}