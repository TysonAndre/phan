<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

trait Memoize
{
    use Profile;
    /**
     * @var array
     * A map from key to memoized values
     */
    private $memoized_data = [];
    /**
     * Memoize the result of $fn(), saving the result
     * with key $key.
     *
     * @param string $key
     * The key to use for storing the result of the
     * computation.
     *
     * @param \Closure $fn
     * A function to compute only once for the given
     * $key.
     *
     * @return mixed
     * The result of the given computation is returned
     */
    protected function memoize($key, \Closure $fn)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Argument \$key passed to memoize() must be of the type string, " . (gettype($key) == "object" ? get_class($key) : gettype($key)) . " given");
        }
        if (!array_key_exists($key, $this->memoized_data)) {
            $this->memoized_data[$key] = $fn();
        }
        return $this->memoized_data[$key];
    }
    /**
     * @param string $key
     * A unique key to test to see if its been seen before
     *
     * @return bool
     * True if this is the first time this function has been
     * called on this class with this key.
     */
    protected function isFirstExecution($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Argument \$key passed to isFirstExecution() must be of the type string, " . (gettype($key) == "object" ? get_class($key) : gettype($key)) . " given");
        }
        if (!array_key_exists($key, $this->memoized_data)) {
            $this->memoized_data[$key] = true;
            $ret5902c6fde4753 = true;
            if (!is_bool($ret5902c6fde4753)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fde4753) . " given");
            }
            return $ret5902c6fde4753;
        }
        $ret5902c6fde49af = false;
        if (!is_bool($ret5902c6fde49af)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fde49af) . " given");
        }
        return $ret5902c6fde49af;
    }
    /**
     * Memoize the result of $fn(), saving the result
     * with key $key.
     *
     * @param string $key
     * The key to use for storing the result of the
     * computation.
     *
     * @param Closure $fn
     * A function to compute only once for the given
     * $key.
     *
     * @return mixed
     * The result of the given computation is returned
     */
    protected static function memoizeStatic($key, \Closure $fn)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Argument \$key passed to memoizeStatic() must be of the type string, " . (gettype($key) == "object" ? get_class($key) : gettype($key)) . " given");
        }
        static $memoized_data = [];
        if (!array_key_exists($key, $memoized_data)) {
            $memoized_data[$key] = $fn();
        }
        return $memoized_data[$key];
    }
    /**
     * Delete all memoized data
     *
     * @return void
     */
    protected function memoizeFlushAll()
    {
        $this->memoized_data = [];
    }
}