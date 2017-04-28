<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedPropertyName;
use Phan\Language\UnionType;
class Property extends ClassElement
{
    use ElementFutureUnionType;
    /**
     * @param \phan\Context $context
     * The context in which the structural element lives
     *
     * @param string $name,
     * The name of the typed structural element
     *
     * @param UnionType $type,
     * A '|' delimited set of types satisfyped by this
     * typed structural element.
     *
     * @param int $flags,
     * The flags property contains node specific flags. It is
     * always defined, but for most nodes it is always zero.
     * ast\kind_uses_flags() can be used to determine whether
     * a certain kind has a meaningful flags value.
     */
    public function __construct(Context $context, $name, UnionType $type, $flags, FullyQualifiedPropertyName $fqsen)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to __construct() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        parent::__construct($context, $name, $type, $flags, $fqsen);
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
        if ($this->isStatic()) {
            $string .= 'static ';
        }
        // Since the UnionType can be a future, and that
        // can throw an exception, we catch it and ignore it
        try {
            $union_type = $this->getUnionType();
        } catch (\Exception $exception) {
            $union_type = new UnionType();
        }
        $string .= "{$union_type} {$this->getName()}";
        $ret5902c6f605e95 = $string;
        if (!is_string($ret5902c6f605e95)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f605e95) . " given");
        }
        return $ret5902c6f605e95;
    }
    /**
     * Override the default getter to fill in a future
     * union type if available.
     */
    public function getUnionType()
    {
        if (null !== ($union_type = $this->getFutureUnionType())) {
            $this->getUnionType()->addUnionType($union_type);
        }
        $ret5902c6f60616e = parent::getUnionType();
        if (!$ret5902c6f60616e instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f60616e) == "object" ? get_class($ret5902c6f60616e) : gettype($ret5902c6f60616e)) . " given");
        }
        return $ret5902c6f60616e;
    }
    /**
     * @return FullyQualifiedPropertyName
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getFQSEN()
    {
        assert(!empty($this->fqsen), "FQSEN must be defined");
        $ret5902c6f6064f3 = $this->fqsen;
        if (!$ret5902c6f6064f3 instanceof FullyQualifiedPropertyName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedPropertyName, " . (gettype($ret5902c6f6064f3) == "object" ? get_class($ret5902c6f6064f3) : gettype($ret5902c6f6064f3)) . " given");
        }
        return $ret5902c6f6064f3;
    }
}