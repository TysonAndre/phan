<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Exception\CodeBaseException;
use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedClassElement;
use Phan\Language\FQSEN\FullyQualifiedClassName;
abstract class ClassElement extends AddressableElement
{
    /**
     * @var FullyQualifiedClassElement|null
     * The FQSEN of this element where it is originally
     * defined.
     */
    private $defining_fqsen = null;
    /**
     * @return bool
     * True if this element has a defining FQSEN defined
     */
    public function hasDefiningFQSEN()
    {
        $ret5902c6f4db46d = $this->defining_fqsen != null;
        if (!is_bool($ret5902c6f4db46d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4db46d) . " given");
        }
        return $ret5902c6f4db46d;
    }
    /**
     * @return FullyQualifiedClassElement
     * The FQSEN of this class element from where it was
     * originally defined
     */
    public function getDefiningFQSEN()
    {
        $ret5902c6f4db78d = $this->defining_fqsen;
        if (!$ret5902c6f4db78d instanceof FullyQualifiedClassElement) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassElement, " . (gettype($ret5902c6f4db78d) == "object" ? get_class($ret5902c6f4db78d) : gettype($ret5902c6f4db78d)) . " given");
        }
        return $ret5902c6f4db78d;
    }
    /**
     * @return FullyQualifiedClassName
     * The FQSEN of this class element from where it was
     * originally defined
     */
    public function getDefiningClassFQSEN()
    {
        if (is_null($this->defining_fqsen)) {
            throw new CodeBaseException($this->getFQSEN(), "No defining class for {$this->getFQSEN()}");
        }
        $ret5902c6f4dbba0 = $this->defining_fqsen->getFullyQualifiedClassName();
        if (!$ret5902c6f4dbba0 instanceof FullyQualifiedClassName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6f4dbba0) == "object" ? get_class($ret5902c6f4dbba0) : gettype($ret5902c6f4dbba0)) . " given");
        }
        return $ret5902c6f4dbba0;
    }
    /**
     * @param FullyQualifiedClassElement $defining_fqsen
     * The FQSEN of this class element in the location in which
     * it was originally defined
     */
    public function setDefiningFQSEN(FullyQualifiedClassElement $defining_fqsen)
    {
        $this->defining_fqsen = $defining_fqsen;
    }
    /**
     * @return Clazz
     * The class on which this element was originally defined
     */
    public function getDefiningClass(CodeBase $code_base)
    {
        $class_fqsen = $this->getDefiningClassFQSEN();
        if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
            throw new CodeBaseException($class_fqsen, "Defining class {$class_fqsen} for {$this->getFQSEN()} not found");
        }
        $ret5902c6f4dbfb7 = $code_base->getClassByFQSEN($class_fqsen);
        if (!$ret5902c6f4dbfb7 instanceof Clazz) {
            throw new \InvalidArgumentException("Argument returned must be of the type Clazz, " . (gettype($ret5902c6f4dbfb7) == "object" ? get_class($ret5902c6f4dbfb7) : gettype($ret5902c6f4dbfb7)) . " given");
        }
        return $ret5902c6f4dbfb7;
    }
    /**
     * @return FullyQualifiedClassName
     * The FQSEN of the class on which this element lives
     */
    public function getClassFQSEN()
    {
        $fqsen = $this->getFQSEN();
        if ($fqsen instanceof FullyQualifiedClassElement) {
            $ret5902c6f4dc2d6 = $fqsen->getFullyQualifiedClassName();
            if (!$ret5902c6f4dc2d6 instanceof FullyQualifiedClassName) {
                throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6f4dc2d6) == "object" ? get_class($ret5902c6f4dc2d6) : gettype($ret5902c6f4dc2d6)) . " given");
            }
            return $ret5902c6f4dc2d6;
        }
        throw new \Exception("Cannot get defining class for non-class element {$this}");
    }
    /**
     * @param CodeBase $code_base
     * The code base with which to look for classes
     *
     * @return Clazz
     * The class that defined this element
     *
     * @throws CodeBaseException
     * An exception may be thrown if we can't find the
     * class
     */
    public function getClass(CodeBase $code_base)
    {
        $class_fqsen = $this->getClassFQSEN();
        if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
            throw new CodeBaseException($class_fqsen, "Defining class {$class_fqsen} for {$this->getFQSEN()} not found");
        }
        $ret5902c6f4dc71a = $code_base->getClassByFQSEN($class_fqsen);
        if (!$ret5902c6f4dc71a instanceof Clazz) {
            throw new \InvalidArgumentException("Argument returned must be of the type Clazz, " . (gettype($ret5902c6f4dc71a) == "object" ? get_class($ret5902c6f4dc71a) : gettype($ret5902c6f4dc71a)) . " given");
        }
        return $ret5902c6f4dc71a;
    }
    /**
     * @return bool
     * True if this method overrides another method
     */
    public function getIsOverride()
    {
        $ret5902c6f4dca2c = Flags::bitVectorHasState($this->getPhanFlags(), Flags::IS_OVERRIDE);
        if (!is_bool($ret5902c6f4dca2c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4dca2c) . " given");
        }
        return $ret5902c6f4dca2c;
    }
    /**
     * @param bool $is_override
     * True if this method overrides another method
     *
     * @return void
     */
    public function setIsOverride($is_override)
    {
        if (!is_bool($is_override)) {
            throw new \InvalidArgumentException("Argument \$is_override passed to setIsOverride() must be of the type bool, " . (gettype($is_override) == "object" ? get_class($is_override) : gettype($is_override)) . " given");
        }
        $this->setPhanFlags(Flags::bitVectorWithState($this->getPhanFlags(), Flags::IS_OVERRIDE, $is_override));
    }
    /**
     * @return bool
     * True if this is a static method
     */
    public function isStatic()
    {
        $ret5902c6f4dcfae = Flags::bitVectorHasState($this->getFlags(), \ast\flags\MODIFIER_STATIC);
        if (!is_bool($ret5902c6f4dcfae)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4dcfae) . " given");
        }
        return $ret5902c6f4dcfae;
    }
    /**
     * @param CodeBase $code_base
     * The code base in which this element exists.
     *
     * @return bool
     * True if this is an internal element
     */
    public function isNSInternal(CodeBase $code_base)
    {
        $ret5902c6f4dd279 = parent::isNSInternal($code_base) || $this->getClass($code_base)->isNSInternal($code_base);
        if (!is_bool($ret5902c6f4dd279)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4dd279) . " given");
        }
        return $ret5902c6f4dd279;
    }
    /**
     * @param CodeBase $code_base
     * The code base in which this element exists.
     *
     * @return bool
     * True if this element is intern
     */
    public function isNSInternalAccessFromContext(CodeBase $code_base, Context $context)
    {
        // Get the class that this element is defined on
        $class = $this->getClass($code_base);
        // Get the namespace that the class is within
        $element_namespace = $class->getFQSEN()->getNamespace();
        // Get our current namespace
        $context_namespace = $context->getNamespace();
        $ret5902c6f4dd5e2 = 0 === strcasecmp($context_namespace, $element_namespace);
        if (!is_bool($ret5902c6f4dd5e2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4dd5e2) . " given");
        }
        return $ret5902c6f4dd5e2;
    }
}