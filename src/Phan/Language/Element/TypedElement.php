<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\FileRef;
use Phan\Language\UnionType;
/**
 * Any PHP structural element that also has a type and is
 * addressable such as a class, method, closure, property,
 * constant, variable, ...
 */
abstract class TypedElement implements TypedElementInterface
{
    /**
     * @var string
     * The name of the typed structural element
     */
    private $name;
    /**
     * @var UnionType|null
     * A set of types satisfyped by this typed structural
     * element.
     */
    private $type = null;
    /**
     * @var int
     * The flags property contains node specific flags. It is
     * always defined, but for most nodes it is always zero.
     * ast\kind_uses_flags() can be used to determine whether
     * a certain kind has a meaningful flags value.
     */
    private $flags = 0;
    /**
     * @var int
     * The Phan flags property contains node specific flags that
     * are internal to Phan.
     */
    private $phan_flags = 0;
    /**
     * @var Context|null
     * The context in which the structural element lives
     */
    private $context = null;
    /**
     * @var int[]
     * A set of issues types to be suppressed
     */
    private $suppress_issue_list = [];
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
     */
    public function __construct(Context $context, $name, UnionType $type, $flags)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to __construct() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        $this->context = clone $context;
        $this->name = $name;
        $this->type = $type;
        $this->flags = $flags;
        $this->setIsPHPInternal($context->isPHPInternal());
    }
    /**
     * After a clone is called on this object, clone our
     * type and fqsen so that they survive copies intact
     *
     * @return null
     */
    public function __clone()
    {
        $this->context = $this->context ? clone $this->context : $this->context;
        $this->type = $this->type ? clone $this->type : $this->type;
    }
    /**
     * @return string
     * The (not fully-qualified) name of this element.
     */
    public function getName()
    {
        $ret5902c6f61aed9 = $this->name;
        if (!is_string($ret5902c6f61aed9)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f61aed9) . " given");
        }
        return $ret5902c6f61aed9;
    }
    /**
     * @return UnionType
     * Get the type of this structural element
     */
    public function getUnionType()
    {
        $ret5902c6f61b171 = $this->type;
        if (!$ret5902c6f61b171 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f61b171) == "object" ? get_class($ret5902c6f61b171) : gettype($ret5902c6f61b171)) . " given");
        }
        return $ret5902c6f61b171;
    }
    /**
     * @param UnionType $type
     * Set the type of this element
     *
     * @return void
     */
    public function setUnionType(UnionType $type)
    {
        $this->type = clone $type;
    }
    /**
     * @return void
     */
    protected function convertToNonVariadic()
    {
        // Avoid a redundant clone of toGenericArray()
        $this->type = $this->getUnionType();
    }
    /**
     * @return void
     */
    protected function convertToNullable()
    {
        // Avoid a redundant clone of nonNullableClone()
        $type = $this->type;
        if ($type->isEmpty() || $type->containsNullable()) {
            return;
        }
        $this->type = $type->nullableClone();
    }
    /**
     * Variables can't be variadic. This is the same as getUnionType for
     * variables, but not necessarily for subclasses. Method will return
     * the element type (such as `DateTime`) for variadic parameters.
     */
    public function getNonVariadicUnionType()
    {
        $ret5902c6f61b5e3 = $this->getUnionType();
        if (!$ret5902c6f61b5e3 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f61b5e3) == "object" ? get_class($ret5902c6f61b5e3) : gettype($ret5902c6f61b5e3)) . " given");
        }
        return $ret5902c6f61b5e3;
    }
    /**
     * @return int
     */
    public function getFlags()
    {
        $ret5902c6f61b8bd = $this->flags;
        if (!is_int($ret5902c6f61b8bd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f61b8bd) . " given");
        }
        return $ret5902c6f61b8bd;
    }
    /**
     * @param int $flags
     *
     * @return void
     */
    public function setFlags($flags)
    {
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to setFlags() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        $this->flags = $flags;
    }
    /**
     * @return int
     */
    public function getPhanFlags()
    {
        $ret5902c6f61bda2 = $this->phan_flags;
        if (!is_int($ret5902c6f61bda2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f61bda2) . " given");
        }
        return $ret5902c6f61bda2;
    }
    /**
     * @param int $phan_flags
     *
     * @return void
     */
    public function setPhanFlags($phan_flags)
    {
        if (!is_int($phan_flags)) {
            throw new \InvalidArgumentException("Argument \$phan_flags passed to setPhanFlags() must be of the type int, " . (gettype($phan_flags) == "object" ? get_class($phan_flags) : gettype($phan_flags)) . " given");
        }
        $this->phan_flags = $phan_flags;
    }
    /**
     * @return Context
     * The context in which this structural element exists
     */
    public function getContext()
    {
        $ret5902c6f61c2d0 = $this->context;
        if (!$ret5902c6f61c2d0 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f61c2d0) == "object" ? get_class($ret5902c6f61c2d0) : gettype($ret5902c6f61c2d0)) . " given");
        }
        return $ret5902c6f61c2d0;
    }
    /**
     * @return FileRef
     * A reference to where this element was found
     */
    public function getFileRef()
    {
        $ret5902c6f61c5b9 = $this->context;
        if (!$ret5902c6f61c5b9 instanceof FileRef) {
            throw new \InvalidArgumentException("Argument returned must be of the type FileRef, " . (gettype($ret5902c6f61c5b9) == "object" ? get_class($ret5902c6f61c5b9) : gettype($ret5902c6f61c5b9)) . " given");
        }
        return $ret5902c6f61c5b9;
    }
    /**
     * @return bool
     * True if this element is marked as deprecated
     */
    public function isDeprecated()
    {
        $ret5902c6f61c8c8 = Flags::bitVectorHasState($this->phan_flags, Flags::IS_DEPRECATED);
        if (!is_bool($ret5902c6f61c8c8)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f61c8c8) . " given");
        }
        return $ret5902c6f61c8c8;
    }
    /**
     * @param bool $is_deprecated
     * Set this element as deprecated
     *
     * @return void
     */
    public function setIsDeprecated($is_deprecated)
    {
        if (!is_bool($is_deprecated)) {
            throw new \InvalidArgumentException("Argument \$is_deprecated passed to setIsDeprecated() must be of the type bool, " . (gettype($is_deprecated) == "object" ? get_class($is_deprecated) : gettype($is_deprecated)) . " given");
        }
        $this->setPhanFlags(Flags::bitVectorWithState($this->getPhanFlags(), Flags::IS_DEPRECATED, $is_deprecated));
    }
    /**
     * @param string[] $suppress_issue_list
     * Set the set of issue names to suppress
     *
     * @return void
     */
    public function setSuppressIssueList(array $suppress_issue_list)
    {
        $this->suppress_issue_list = [];
        foreach ($suppress_issue_list as $issue_name) {
            $this->suppress_issue_list[$issue_name] = 0;
        }
    }
    /**
     * @return int[]
     */
    public function getSuppressIssueList()
    {
        $ret5902c6f61cf08 = $this->suppress_issue_list ?: [];
        if (!is_array($ret5902c6f61cf08)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f61cf08) . " given");
        }
        return $ret5902c6f61cf08;
    }
    /**
     * return bool
     * True if this element would like to suppress the given
     * issue name
     */
    public function hasSuppressIssue($issue_name)
    {
        if (!is_string($issue_name)) {
            throw new \InvalidArgumentException("Argument \$issue_name passed to hasSuppressIssue() must be of the type string, " . (gettype($issue_name) == "object" ? get_class($issue_name) : gettype($issue_name)) . " given");
        }
        $ret5902c6f61d18e = isset($this->suppress_issue_list[$issue_name]);
        if (!is_bool($ret5902c6f61d18e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f61d18e) . " given");
        }
        return $ret5902c6f61d18e;
    }
    /**
     * @return bool
     * True if this was an internal PHP object
     */
    public function isPHPInternal()
    {
        $ret5902c6f61d674 = Flags::bitVectorHasState($this->getPhanFlags(), Flags::IS_PHP_INTERNAL);
        if (!is_bool($ret5902c6f61d674)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f61d674) . " given");
        }
        return $ret5902c6f61d674;
    }
    /**
     * @return void
     */
    private function setIsPHPInternal($is_internal)
    {
        if (!is_bool($is_internal)) {
            throw new \InvalidArgumentException("Argument \$is_internal passed to setIsPHPInternal() must be of the type bool, " . (gettype($is_internal) == "object" ? get_class($is_internal) : gettype($is_internal)) . " given");
        }
        $this->setPhanFlags(Flags::bitVectorWithState($this->getPhanFlags(), Flags::IS_PHP_INTERNAL, $is_internal));
    }
    /**
     * This method must be called before analysis
     * begins.
     *
     * @return void
     */
    public function hydrate(CodeBase $code_base)
    {
        // Do nothing unless overridden
    }
}