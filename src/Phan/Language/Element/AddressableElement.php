<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedGlobalStructuralElement;
use Phan\Language\FileRef;
use Phan\Language\UnionType;
use Phan\Model\CalledBy;
abstract class AddressableElement extends TypedElement implements AddressableElementInterface
{
    use \Phan\Memoize;
    /**
     * @var FQSEN
     */
    protected $fqsen;
    /**
     * @var FileRef[]
     * A list of locations in which this typed structural
     * element is referenced from.
     */
    private $reference_list = [];
    /**
     * @param Context $context
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
     *
     * @param FQSEN $fqsen
     * A fully qualified name for the element
     */
    public function __construct(Context $context, $name, UnionType $type, $flags, FQSEN $fqsen)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to __construct() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        parent::__construct($context, $name, $type, $flags);
        $this->setFQSEN($fqsen);
    }
    /**
     * @return FQSEN
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getFQSEN()
    {
        assert(!empty($this->fqsen), "FQSEN must be defined");
        return $this->fqsen;
    }
    /**
     * @param FQSEN $fqsen
     * A fully qualified structural element name to set on
     * this element
     *
     * @return void
     */
    public function setFQSEN(FQSEN $fqsen)
    {
        $this->fqsen = $fqsen;
    }
    /**
     * @return bool
     * True if this is a public property
     */
    public function isPublic()
    {
        $ret5902c6f4c5333 = !($this->isProtected() || $this->isPrivate());
        if (!is_bool($ret5902c6f4c5333)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4c5333) . " given");
        }
        return $ret5902c6f4c5333;
    }
    /**
     * @return bool
     * True if this is a protected element
     */
    public function isProtected()
    {
        $ret5902c6f4c55d0 = Flags::bitVectorHasState($this->getFlags(), \ast\flags\MODIFIER_PROTECTED);
        if (!is_bool($ret5902c6f4c55d0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4c55d0) . " given");
        }
        return $ret5902c6f4c55d0;
    }
    /**
     * @return bool
     * True if this is a private element
     */
    public function isPrivate()
    {
        $ret5902c6f4c585e = Flags::bitVectorHasState($this->getFlags(), \ast\flags\MODIFIER_PRIVATE);
        if (!is_bool($ret5902c6f4c585e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4c585e) . " given");
        }
        return $ret5902c6f4c585e;
    }
    /**
     * @param CodeBase $code_base
     * The code base in which this element exists.
     *
     * @return bool
     * True if this is marked as an `(at)internal` element
     */
    public function isNSInternal(CodeBase $code_base)
    {
        $ret5902c6f4c5b03 = Flags::bitVectorHasState($this->getPhanFlags(), Flags::IS_NS_INTERNAL);
        if (!is_bool($ret5902c6f4c5b03)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4c5b03) . " given");
        }
        return $ret5902c6f4c5b03;
    }
    /**
     * Set this element as being `internal`.
     * @return void
     */
    public function setIsNSInternal($is_internal)
    {
        if (!is_bool($is_internal)) {
            throw new \InvalidArgumentException("Argument \$is_internal passed to setIsNSInternal() must be of the type bool, " . (gettype($is_internal) == "object" ? get_class($is_internal) : gettype($is_internal)) . " given");
        }
        $this->setPhanFlags(Flags::bitVectorWithState($this->getPhanFlags(), Flags::IS_NS_INTERNAL, $is_internal));
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
        $element_fqsen = $this->getFQSEN();
        assert($element_fqsen instanceof FullyQualifiedGlobalStructuralElement);
        // Figure out which namespace this element is within
        $element_namespace = $element_fqsen->getNamespace();
        // Get our current namespace from the context
        $context_namespace = $context->getNamespace();
        $ret5902c6f4c6184 = 0 === strcasecmp($context_namespace, $element_namespace);
        if (!is_bool($ret5902c6f4c6184)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4c6184) . " given");
        }
        return $ret5902c6f4c6184;
    }
    /**
     * @param FileRef $file_ref
     * A reference to a location in which this typed structural
     * element is referenced.
     *
     * @return void
     */
    public function addReference(FileRef $file_ref)
    {
        $this->reference_list[] = $file_ref;
    }
    /**
     * @return FileRef[]
     * A list of references to this typed structural element.
     */
    public function getReferenceList()
    {
        if (!empty($this->reference_list)) {
            $ret5902c6f4c649d = $this->reference_list;
            if (!is_array($ret5902c6f4c649d)) {
                throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f4c649d) . " given");
            }
            return $ret5902c6f4c649d;
        }
        $ret5902c6f4c66e9 = $this->reference_list;
        if (!is_array($ret5902c6f4c66e9)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f4c66e9) . " given");
        }
        return $ret5902c6f4c66e9;
    }
    /**
     * @param CodeBase $code_base
     * Some elements may need access to the code base to
     * figure out their total reference count.
     *
     * @return int
     * The number of references to this typed structural element
     */
    public function getReferenceCount(CodeBase $code_base)
    {
        $ret5902c6f4c6977 = count($this->reference_list);
        if (!is_int($ret5902c6f4c6977)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f4c6977) . " given");
        }
        return $ret5902c6f4c6977;
    }
    /**
     * This method must be called before analysis
     * begins.
     *
     * @return void
     * @override
     */
    public final function hydrate(CodeBase $code_base)
    {
        if (!$this->isFirstExecution(__METHOD__)) {
            return;
        }
        $this->hydrateOnce($code_base);
    }
    protected function hydrateOnce(CodeBase $code_base)
    {
        // Do nothing unless overridden
    }
}