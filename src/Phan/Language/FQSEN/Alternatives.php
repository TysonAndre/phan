<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

use Phan\Language\FQSEN;
/**
 * This trait allows an FQSEN to have an alternate ID for when
 * there are multiple colliding definitions of the same name.
 * An alternate ID will be appended to a name such as in
 * `\Name\Space\class,1` or `\Name\Space\class::function,1`
 * or when composed as `\Name\Space\class,1::function,1`.
 */
trait Alternatives
{
    /**
     * Implementers must have a getName() method
     */
    public abstract function getName();
    /**
     * Implementers should use the \Phan\Memoize
     * trait
     */
    protected abstract function memoizeFlushAll();
    /**
     * @var int
     * An alternate ID for the elemnet for use when
     * there are multiple definitions of the element
     */
    protected $alternate_id = 0;
    /**
     * @return int
     * An alternate identifier associated with this
     * FQSEN or zero if none if this is not an
     * alternative.
     */
    public function getAlternateId()
    {
        $ret5902c6f64b820 = $this->alternate_id;
        if (!is_int($ret5902c6f64b820)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f64b820) . " given");
        }
        return $ret5902c6f64b820;
    }
    /**
     * @return string
     * Get the name of this element with its alternate id
     * attached
     */
    public function getNameWithAlternateId()
    {
        if ($this->alternate_id) {
            $ret5902c6f64bd20 = "{$this->getName()},{$this->alternate_id}";
            if (!is_string($ret5902c6f64bd20)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f64bd20) . " given");
            }
            return $ret5902c6f64bd20;
        }
        $ret5902c6f64bf9f = $this->getName();
        if (!is_string($ret5902c6f64bf9f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f64bf9f) . " given");
        }
        return $ret5902c6f64bf9f;
    }
    /**
     * @return bool
     * True if this is an alternate
     */
    public function isAlternate()
    {
        $ret5902c6f64c262 = 0 !== $this->alternate_id;
        if (!is_bool($ret5902c6f64c262)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f64c262) . " given");
        }
        return $ret5902c6f64c262;
    }
    /**
     * @return static
     * A FQSEN with the given alternate_id set
     *
     * @suppress PhanTypeMismatchDeclaredReturn
     */
    public abstract function withAlternateId($alternate_id);
    /**
     * @return static
     * Get the canonical (non-alternate) FQSEN associated
     * with this FQSEN
     *
     * @suppress PhanTypeMismatchDeclaredReturn
     */
    public function getCanonicalFQSEN()
    {
        if ($this->alternate_id == 0) {
            $ret5902c6f64c53b = $this;
            if (!$ret5902c6f64c53b instanceof FQSEN) {
                throw new \InvalidArgumentException("Argument returned must be of the type FQSEN, " . (gettype($ret5902c6f64c53b) == "object" ? get_class($ret5902c6f64c53b) : gettype($ret5902c6f64c53b)) . " given");
            }
            return $ret5902c6f64c53b;
        }
        $ret5902c6f64c8bf = $this->withAlternateId(0);
        if (!$ret5902c6f64c8bf instanceof FQSEN) {
            throw new \InvalidArgumentException("Argument returned must be of the type FQSEN, " . (gettype($ret5902c6f64c8bf) == "object" ? get_class($ret5902c6f64c8bf) : gettype($ret5902c6f64c8bf)) . " given");
        }
        return $ret5902c6f64c8bf;
    }
}