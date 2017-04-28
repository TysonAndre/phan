<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * A set of objects supporting union and
 * intersection
 */
class Set extends \SplObjectStorage
{
    /**
     * @param \Iterator|array $elements
     * An optional set of items to add to the set
     */
    public function __construct($element_iterator = null)
    {
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$element_iterator, @[]) as $element) {
            $this->attach($element);
        }
    }
    /**
     * @return array
     * An array of all elements in the set is returned
     */
    public function toArray()
    {
        $ret5902c6fd9f9ab = iterator_to_array($this);
        if (!is_array($ret5902c6fd9f9ab)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd9f9ab) . " given");
        }
        return $ret5902c6fd9f9ab;
    }
    /**
     * @param Set $other
     * A set of items to intersect with this set
     *
     * @return Set
     * A new set which contains only items in this
     * Set and the given Set
     */
    public function intersect(Set $other)
    {
        $set = new Set();
        foreach ($this as $element) {
            if ($other->contains($element)) {
                $set->attach($element);
            }
        }
        $ret5902c6fd9fdb3 = $set;
        if (!$ret5902c6fd9fdb3 instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fd9fdb3) == "object" ? get_class($ret5902c6fd9fdb3) : gettype($ret5902c6fd9fdb3)) . " given");
        }
        return $ret5902c6fd9fdb3;
    }
    /**
     * @param Set[] $set_list
     * A list of sets to intersect
     *
     * @return Set
     * A new Set containing only the elements that appear in
     * all parameters
     */
    public static function intersectAll(array $set_list)
    {
        if (empty($set_list)) {
            $ret5902c6fda014d = new Set();
            if (!$ret5902c6fda014d instanceof Set) {
                throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda014d) == "object" ? get_class($ret5902c6fda014d) : gettype($ret5902c6fda014d)) . " given");
            }
            return $ret5902c6fda014d;
        }
        $intersected_set = array_shift($set_list);
        foreach ($set_list as $set) {
            $intersected_set = $intersected_set->intersect($set);
        }
        $ret5902c6fda0496 = $intersected_set;
        if (!$ret5902c6fda0496 instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda0496) == "object" ? get_class($ret5902c6fda0496) : gettype($ret5902c6fda0496)) . " given");
        }
        return $ret5902c6fda0496;
    }
    /**
     * @param Set $other
     * A set of items to union with this set
     *
     * @return Set
     * A new set which contains only items in this
     * Set and the given Set.
     *
     * @suppress PhanUnreferencedMethod
     */
    public function union(Set $other)
    {
        $set = new Set();
        $set->addAll($this);
        $set->addAll($other);
        $ret5902c6fda086c = $set;
        if (!$ret5902c6fda086c instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda086c) == "object" ? get_class($ret5902c6fda086c) : gettype($ret5902c6fda086c)) . " given");
        }
        return $ret5902c6fda086c;
    }
    /**
     * @param Set[] $set_list
     * A list of sets to intersect
     *
     * @return Set
     * A new Set containing any element that appear in
     * any parameters
     */
    public static function unionAll(array $set_list)
    {
        if (empty($set_list)) {
            $ret5902c6fda0b6c = new Set();
            if (!$ret5902c6fda0b6c instanceof Set) {
                throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda0b6c) == "object" ? get_class($ret5902c6fda0b6c) : gettype($ret5902c6fda0b6c)) . " given");
            }
            return $ret5902c6fda0b6c;
        }
        $union_set = array_shift($set_list);
        foreach ($set_list as $set) {
            $union_set = $union_set->union($set);
        }
        $ret5902c6fda0ef9 = $union_set;
        if (!$ret5902c6fda0ef9 instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda0ef9) == "object" ? get_class($ret5902c6fda0ef9) : gettype($ret5902c6fda0ef9)) . " given");
        }
        return $ret5902c6fda0ef9;
    }
    /**
     * @return bool
     * True if this set contains any elements in the given list
     */
    public function containsAny(array $element_list)
    {
        foreach ($element_list as $element) {
            if ($this->contains($element)) {
                $ret5902c6fda121d = true;
                if (!is_bool($ret5902c6fda121d)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fda121d) . " given");
                }
                return $ret5902c6fda121d;
            }
        }
        $ret5902c6fda1478 = false;
        if (!is_bool($ret5902c6fda1478)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fda1478) . " given");
        }
        return $ret5902c6fda1478;
    }
    /**
     * @param \Closure $closure
     * A closure taking a set element that returns a boolean
     * for which true will cause the element to be retained
     * and false will cause the element to be removed
     *
     * @return Set
     * A new set for which all elements when passed to the given
     * closure return true
     */
    public function filter(\Closure $closure)
    {
        $set = new Set();
        foreach ($this as $element) {
            if ($closure($element)) {
                $set->attach($element);
            }
        }
        return $set;
    }
    /**
     * @param \Closure $closure
     * A closure that maps each element of this set
     * to a new element
     *
     * @return Set
     * A new set containing the mapped values
     */
    public function map(\Closure $closure)
    {
        $set = new Set();
        foreach ($this as $element) {
            $set->attach($closure($element));
        }
        $ret5902c6fda1828 = $set;
        if (!$ret5902c6fda1828 instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda1828) == "object" ? get_class($ret5902c6fda1828) : gettype($ret5902c6fda1828)) . " given");
        }
        return $ret5902c6fda1828;
    }
    /**
     * @return Set
     * A new set with each element cloned
     */
    public function deepCopy()
    {
        $ret5902c6fda1b9e = $this->map(function ($element) {
            return clone $element;
        });
        if (!$ret5902c6fda1b9e instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6fda1b9e) == "object" ? get_class($ret5902c6fda1b9e) : gettype($ret5902c6fda1b9e)) . " given");
        }
        return $ret5902c6fda1b9e;
    }
    /**
     * @param \Closure $closure
     * A closure that takes an element and returns a boolean
     *
     * @return mixed|bool
     * The first element for which the given closure returns
     * true is returned or false if no elements pass the
     * given closure
     */
    public function find(\Closure $closure)
    {
        foreach ($this as $element) {
            if ($closure($element)) {
                return $element;
            }
        }
        return false;
    }
    /**
     * @return string
     * A string representation of this set for use in
     * debugging
     */
    public function __toString()
    {
        $string = '[' . implode(',', array_map(function ($element) {
            return (string) $element;
        }, iterator_to_array($this))) . ']';
        $ret5902c6fda1ff7 = $string;
        if (!is_string($ret5902c6fda1ff7)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fda1ff7) . " given");
        }
        return $ret5902c6fda1ff7;
    }
}