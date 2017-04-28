<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library\Hasher;

use Phan\Library\Hasher;
/**
 * Hasher implementation mapping keys to sequential groups (first key to 0, second key to 1, looping back to 0)
 * getGroup() is called exactly once on each string to be hashed.
 * See https://en.wikipedia.org/wiki/Consistent_hashing
 */
class Consistent implements Hasher
{
    const VIRTUAL_COPY_COUNT = 16;
    // Larger number means a more balanced distribution.
    const MAX = 0x40000000;
    // i.e. (1 << 30)
    /** @var int */
    protected $_groupCount;
    /** @var int[] - Sorted list of hash values, for binary search. */
    protected $_hashRingIds;
    /** @var int[] - Groups corresponding to hash values in _hashRingIds */
    protected $_hashRingGroups;
    public function __construct($groupCount)
    {
        if (!is_int($groupCount)) {
            throw new \InvalidArgumentException("Argument \$groupCount passed to __construct() must be of the type int, " . (gettype($groupCount) == "object" ? get_class($groupCount) : gettype($groupCount)) . " given");
        }
        $this->_groupCount = $groupCount;
        $map = [];
        for ($group = 0; $group < $groupCount; $group++) {
            foreach (self::get_hashes_for_group($group) as $hash) {
                $map[$hash] = $group;
            }
        }
        $hashRingIds = [];
        ksort($map);
        foreach ($map as $key => $group) {
            $hashRingIds[] = $key;
            $hashRingGroups[] = $group;
        }
        // ... and make the map wrap around.
        $hashRingIds[] = self::MAX - 1;
        $hashRingGroups[] = reset($map);
        $this->_hashRingIds = $hashRingIds;
        $this->_hashRingGroups = $hashRingGroups;
    }
    /**
     * Do a binary search in the consistent hashing ring to find the group.
     * @return int - an integer between 0 and $this->_groupCount - 1, inclusive
     */
    public function getGroup($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Argument \$key passed to getGroup() must be of the type string, " . (gettype($key) == "object" ? get_class($key) : gettype($key)) . " given");
        }
        $searchHash = self::generate_key_hash($key);
        $begin = 0;
        $end = count($this->_hashRingIds) - 1;
        while ($begin <= $end) {
            $pos = $begin + ($end - $begin >> 1);
            $curVal = $this->_hashRingIds[$pos];
            if ($searchHash > $curVal) {
                $begin = $pos + 1;
            } else {
                $end = $pos - 1;
            }
        }
        $ret5902c6fd670a4 = $this->_hashRingGroups[$begin];
        if (!is_int($ret5902c6fd670a4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6fd670a4) . " given");
        }
        return $ret5902c6fd670a4;
    }
    /**
     * No-op reset
     * @return void
     */
    public function reset()
    {
    }
    /**
     * @return int[]
     */
    public static function get_hashes_for_group($group)
    {
        if (!is_int($group)) {
            throw new \InvalidArgumentException("Argument \$group passed to get_hashes_for_group() must be of the type int, " . (gettype($group) == "object" ? get_class($group) : gettype($group)) . " given");
        }
        $hashes = [];
        for ($i = 0; $i < self::VIRTUAL_COPY_COUNT; $i++) {
            $hashes[$i] = self::generate_key_hash("{$i}@{$group}");
        }
        $ret5902c6fd67658 = $hashes;
        if (!is_array($ret5902c6fd67658)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fd67658) . " given");
        }
        return $ret5902c6fd67658;
    }
    /**
     * Returns a 30-bit signed integer (i.e. in the range [0, self::MAX-1])
     * Designed to work on 32-bit php installations as well.
     */
    public static function generate_key_hash($material)
    {
        if (!is_string($material)) {
            throw new \InvalidArgumentException("Argument \$material passed to generate_key_hash() must be of the type string, " . (gettype($material) == "object" ? get_class($material) : gettype($material)) . " given");
        }
        $bits = md5($material);
        $result = (intval($bits[0], 16) & 3) << 28 ^ intval(substr($bits, 1, 7), 16);
        $ret5902c6fd67c2d = $result;
        if (!is_int($ret5902c6fd67c2d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6fd67c2d) . " given");
        }
        return $ret5902c6fd67c2d;
    }
}