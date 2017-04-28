<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedGlobalConstantName;
use Phan\Language\UnionType;
class GlobalConstant extends AddressableElement implements ConstantInterface
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
        $ret5902c6f5be1af = parent::getUnionType();
        if (!$ret5902c6f5be1af instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f5be1af) == "object" ? get_class($ret5902c6f5be1af) : gettype($ret5902c6f5be1af)) . " given");
        }
        return $ret5902c6f5be1af;
    }
    /**
     * @return FullyQualifiedGlobalConstantName
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getFQSEN()
    {
        assert(!empty($this->fqsen), "FQSEN must be defined");
        $ret5902c6f5be6cf = $this->fqsen;
        if (!$ret5902c6f5be6cf instanceof FullyQualifiedGlobalConstantName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedGlobalConstantName, " . (gettype($ret5902c6f5be6cf) == "object" ? get_class($ret5902c6f5be6cf) : gettype($ret5902c6f5be6cf)) . " given");
        }
        return $ret5902c6f5be6cf;
    }
}