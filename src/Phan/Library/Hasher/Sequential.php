<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library\Hasher;

use Phan\Library\Hasher;
/**
 * Hasher implementation mapping keys to sequential groups (first key to 0, second key to 1, looping back to 0)
 * getGroup() is called exactly once on each string to be hashed.
 */
class Sequential implements Hasher
{
    /** @var int */
    protected $_i;
    /** @var int */
    protected $_groupCount;
    public function __construct($groupCount)
    {
        if (!is_int($groupCount)) {
            throw new \InvalidArgumentException("Argument \$groupCount passed to __construct() must be of the type int, " . (gettype($groupCount) == "object" ? get_class($groupCount) : gettype($groupCount)) . " given");
        }
        $this->_i = 1;
        $this->_groupCount = $groupCount;
    }
    /**
     * @return int - an integer between 0 and $this->_groupCount - 1, inclusive
     */
    public function getGroup($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Argument \$key passed to getGroup() must be of the type string, " . (gettype($key) == "object" ? get_class($key) : gettype($key)) . " given");
        }
        $ret5902c6fd79c85 = $this->_i++ % $this->_groupCount;
        if (!is_int($ret5902c6fd79c85)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6fd79c85) . " given");
        }
        return $ret5902c6fd79c85;
    }
    /**
     * Resets counter
     * @return void
     */
    public function reset()
    {
        $this->_i = 1;
    }
}