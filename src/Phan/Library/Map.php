<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * A map from object to object with key comparisons
 * based on spl_object_hash, which I believe its the zval's
 * memory address.
 */
class Map extends \SplObjectStorage
{
    /**
     * We redefine the key to be the actual key rather than
     * the index of the key
     */
    public function key()
    {
        return parent::current();
    }
    /**
     * We redefine the current value to the current value rather
     * than the current key
     */
    public function current()
    {
        return $this->offsetGet(parent::current());
    }
    /**
     * @param \Closure $key_closure
     * A closure that maps each key of this map
     * to a new key
     *
     * @param \Closure $value_closure
     * A closure that maps each value of this map
     * to a new value.
     *
     * @return Map
     * A new map containing the mapped keys and
     * values
     */
    public function keyValueMap(\Closure $key_closure, \Closure $value_closure)
    {
        $map = new Map();
        foreach ($this as $key => $value) {
            $map[$key_closure($key)] = $value_closure($value);
        }
        return $map;
    }
    /**
     * @return Map
     * A new map with each key and value cloned
     */
    public function deepCopy()
    {
        $clone = function ($element) {
            return clone $element;
        };
        $ret5902c6fd832f3 = $this->keyValueMap($clone, $clone);
        if (!$ret5902c6fd832f3 instanceof Map) {
            throw new \InvalidArgumentException("Argument returned must be of the type Map, " . (gettype($ret5902c6fd832f3) == "object" ? get_class($ret5902c6fd832f3) : gettype($ret5902c6fd832f3)) . " given");
        }
        return $ret5902c6fd832f3;
    }
}