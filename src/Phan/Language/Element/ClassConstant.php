<?php declare(strict_types=1);
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
    public function getUnionType() : UnionType
    {
        if (null !== ($union_type = $this->getFutureUnionType())) {
            $this->getUnionType()->addUnionType($union_type);
        }

        return parent::getUnionType();
    }

    /**
     * @return FullyQualifiedClassConstantName
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getFQSEN() : FullyQualifiedClassConstantName
    {
        assert(!empty($this->fqsen), "FQSEN must be defined");
        return $this->fqsen;
    }

    public function __toString() : string
    {
        $string = '';

        if ($this->isPublic()) {
            $string .= 'public ';
        } elseif ($this->isProtected()) {
            $string .= 'protected ';
        } elseif ($this->isPrivate()) {
            $string .= 'private ';
        }

        return $string . 'const ' . $this->getName();
    }

    public function toStub(\Phan\CodeBase $code_base) : string {
        $string = '    ';

        if ($this->isPublic()) {
            $string .= 'public ';
        } elseif ($this->isProtected()) {
            $string .= 'protected ';
        } elseif ($this->isPrivate()) {
            $string .= 'private ';
        }

        $string .= 'const ' . $this->getName() . ' = ';
        $fqsen = (string)$this->getFQSEN();
        if (defined($fqsen)) {
            $string .= var_export(constant($fqsen), true) . ';';
        } else {
            $string .= "null;  // could not find";
        }
        return $string;
    }
}
