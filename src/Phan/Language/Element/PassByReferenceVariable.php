<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Language\Context;
use Phan\Language\FileRef;
use Phan\Language\UnionType;
/**
 * This class wraps a parameter and a element and proxies
 * calls to the element but keeps the name of the parameter
 * allowing us to pass a element into a method as a
 * pass-by-reference parameter so that its value can be
 * updated when re-analyzing the method.
 */
class PassByReferenceVariable extends Variable
{
    /** @var Variable */
    private $parameter;
    /** @var TypedElement */
    private $element;
    public function __construct(Variable $parameter, TypedElement $element)
    {
        $this->parameter = $parameter;
        $this->element = $element;
    }
    public function getName()
    {
        $ret5902c6f5ed104 = $this->parameter->getName();
        if (!is_string($ret5902c6f5ed104)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f5ed104) . " given");
        }
        return $ret5902c6f5ed104;
    }
    /**
     * Variables can't be variadic. This is the same as getUnionType for
     * variables, but not necessarily for subclasses. Method will return
     * the element type (such as `DateTime`) for variadic parameters.
     */
    public function getNonVariadicUnionType()
    {
        $ret5902c6f5ed692 = $this->element->getNonVariadicUnionType();
        if (!$ret5902c6f5ed692 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f5ed692) == "object" ? get_class($ret5902c6f5ed692) : gettype($ret5902c6f5ed692)) . " given");
        }
        return $ret5902c6f5ed692;
    }
    public function getUnionType()
    {
        $ret5902c6f5eda57 = $this->element->getUnionType();
        if (!$ret5902c6f5eda57 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f5eda57) == "object" ? get_class($ret5902c6f5eda57) : gettype($ret5902c6f5eda57)) . " given");
        }
        return $ret5902c6f5eda57;
    }
    public function setUnionType(UnionType $type)
    {
        $this->element->setUnionType($type);
    }
    public function getFlags()
    {
        $ret5902c6f5edd93 = $this->element->getFlags();
        if (!is_int($ret5902c6f5edd93)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f5edd93) . " given");
        }
        return $ret5902c6f5edd93;
    }
    public function setFlags($flags)
    {
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to setFlags() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        $this->element->setFlags($flags);
    }
    public function getPhanFlags()
    {
        $ret5902c6f5ee2dc = $this->element->getPhanFlags();
        if (!is_int($ret5902c6f5ee2dc)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f5ee2dc) . " given");
        }
        return $ret5902c6f5ee2dc;
    }
    public function setPhanFlags($phan_flags)
    {
        if (!is_int($phan_flags)) {
            throw new \InvalidArgumentException("Argument \$phan_flags passed to setPhanFlags() must be of the type int, " . (gettype($phan_flags) == "object" ? get_class($phan_flags) : gettype($phan_flags)) . " given");
        }
        $this->element->setPhanFlags($phan_flags);
    }
    public function getContext()
    {
        $ret5902c6f5ee7e6 = $this->element->getContext();
        if (!$ret5902c6f5ee7e6 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f5ee7e6) == "object" ? get_class($ret5902c6f5ee7e6) : gettype($ret5902c6f5ee7e6)) . " given");
        }
        return $ret5902c6f5ee7e6;
    }
    public function getFileRef()
    {
        $ret5902c6f5eead2 = $this->element->getFileRef();
        if (!$ret5902c6f5eead2 instanceof FileRef) {
            throw new \InvalidArgumentException("Argument returned must be of the type FileRef, " . (gettype($ret5902c6f5eead2) == "object" ? get_class($ret5902c6f5eead2) : gettype($ret5902c6f5eead2)) . " given");
        }
        return $ret5902c6f5eead2;
    }
    public function isDeprecated()
    {
        $ret5902c6f5eedfc = $this->element->isDeprecated();
        if (!is_bool($ret5902c6f5eedfc)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5eedfc) . " given");
        }
        return $ret5902c6f5eedfc;
    }
    public function setIsDeprecated($is_deprecated)
    {
        if (!is_bool($is_deprecated)) {
            throw new \InvalidArgumentException("Argument \$is_deprecated passed to setIsDeprecated() must be of the type bool, " . (gettype($is_deprecated) == "object" ? get_class($is_deprecated) : gettype($is_deprecated)) . " given");
        }
        $this->element->setIsDeprecated($is_deprecated);
    }
    public function isPHPInternal()
    {
        $ret5902c6f5ef30d = $this->element->isPHPInternal();
        if (!is_bool($ret5902c6f5ef30d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f5ef30d) . " given");
        }
        return $ret5902c6f5ef30d;
    }
}