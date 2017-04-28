<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedClassConstantName;
use Phan\Language\UnionType;
class ClassConstant extends ClassElement implements ConstantInterface
{
    use ConstantTrait;
    /**
     * Override the default getter to fill in a future
     * union type if available.
     *
     * @return UnionType
     */
    public function getUnionType()
    {
        if (null !== ($union_type = $this->getFutureUnionType())) {
            $this->getUnionType()->addUnionType($union_type);
        }
        $ret5902c6f4d053d = parent::getUnionType();
        if (!$ret5902c6f4d053d instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f4d053d) == "object" ? get_class($ret5902c6f4d053d) : gettype($ret5902c6f4d053d)) . " given");
        }
        return $ret5902c6f4d053d;
    }
    /**
     * @return FullyQualifiedClassConstantName
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getFQSEN()
    {
        assert(!empty($this->fqsen), "FQSEN must be defined");
        $ret5902c6f4d0a56 = $this->fqsen;
        if (!$ret5902c6f4d0a56 instanceof FullyQualifiedClassConstantName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassConstantName, " . (gettype($ret5902c6f4d0a56) == "object" ? get_class($ret5902c6f4d0a56) : gettype($ret5902c6f4d0a56)) . " given");
        }
        return $ret5902c6f4d0a56;
    }
    public function __toString()
    {
        $string = '';
        if ($this->isPublic()) {
            $string .= 'public ';
        } elseif ($this->isProtected()) {
            $string .= 'protected ';
        } elseif ($this->isPrivate()) {
            $string .= 'private ';
        }
        $ret5902c6f4d0e0d = $string . 'const ' . $this->getName();
        if (!is_string($ret5902c6f4d0e0d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f4d0e0d) . " given");
        }
        return $ret5902c6f4d0e0d;
    }
}