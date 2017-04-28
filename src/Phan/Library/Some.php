<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Library;

/**
 * @template T
 * The type of the element
 *
 * @inherits Option<T>
 */
class Some extends Option
{
    /** @var T */
    private $_;
    /**
     * @param T $_
     */
    public function __construct($_)
    {
        $this->_ = $_;
    }
    /**
     * @return bool
     */
    public function isDefined()
    {
        $ret5902c6fdab69e = true;
        if (!is_bool($ret5902c6fdab69e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fdab69e) . " given");
        }
        return $ret5902c6fdab69e;
    }
    /**
     * @return T
     */
    public function get()
    {
        return $this->_;
    }
    /**
     * @param T $else
     * @return T
     */
    public function getOrElse($else)
    {
        return $this->get();
    }
    /**
     * @return string
     * A string representation of this object
     */
    public function __tostring()
    {
        $ret5902c6fdabbd4 = 'Some(' . $this->_ . ')';
        if (!is_string($ret5902c6fdabbd4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fdabbd4) . " given");
        }
        return $ret5902c6fdabbd4;
    }
}