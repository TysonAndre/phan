<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element\Comment;

use Phan\Language\Context;
use Phan\Language\Element\Variable;
use Phan\Language\Type\NullType;
use Phan\Language\UnionType;
class Parameter
{
    /**
     * @var string
     * The name of the parameter
     */
    private $name;
    /**
     * @var UnionType
     * The type of the parameter
     */
    private $type;
    /**
     * @var bool
     * Whether or not the parameter is variadic (in the comment)
     */
    private $is_variadic;
    /**
     * @var bool
     * Whether or not the parameter is optional (Note: only applies to the comment for (at)method.
     */
    private $has_default_value;
    /**
     * @param string $name
     * The name of the parameter
     *
     * @param UnionType $type
     * The type of the parameter
     */
    public function __construct($name, UnionType $type, $is_variadic = false, $has_default_value = false)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_bool($is_variadic)) {
            throw new \InvalidArgumentException("Argument \$is_variadic passed to __construct() must be of the type bool, " . (gettype($is_variadic) == "object" ? get_class($is_variadic) : gettype($is_variadic)) . " given");
        }
        if (!is_bool($has_default_value)) {
            throw new \InvalidArgumentException("Argument \$has_default_value passed to __construct() must be of the type bool, " . (gettype($has_default_value) == "object" ? get_class($has_default_value) : gettype($has_default_value)) . " given");
        }
        $this->name = $name;
        $this->type = $type;
        $this->is_variadic = $is_variadic;
        $this->has_default_value = $has_default_value;
    }
    /**
     *
     */
    public function asVariable(Context $context, $flags = 0)
    {
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to asVariable() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        $ret5902c6f5373b0 = new Variable($context, $this->getName(), $this->getUnionType(), $flags);
        if (!$ret5902c6f5373b0 instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6f5373b0) == "object" ? get_class($ret5902c6f5373b0) : gettype($ret5902c6f5373b0)) . " given");
        }
        return $ret5902c6f5373b0;
    }
    /**
     *
     */
    public function asRealParameter(Context $context)
    {
        $flags = 0;
        if ($this->isVariadic()) {
            $flags |= \ast\flags\PARAM_VARIADIC;
        }
        $union_type = $this->getUnionType();
        $param = new \Phan\Language\Element\Parameter($context, $this->getName(), $union_type, $flags);
        if ($this->has_default_value) {
            $param->setDefaultValueType(clone $union_type);
            // TODO: could setDefaultValue in a future PR. Would have to run \ast\parse_code on the default value, catch ParseError if necessary.
            // If given '= "Default"', then extract the default from '<?php ("Default");'
            // Then get the type from UnionTypeVisitor, for defaults such as SomeClass::CONST.
        }
        $ret5902c6f537add = $param;
        if (!$ret5902c6f537add instanceof \Phan\Language\Element\Parameter) {
            throw new \InvalidArgumentException("Argument returned must be of the type \\Phan\\Language\\Element\\Parameter, " . (gettype($ret5902c6f537add) == "object" ? get_class($ret5902c6f537add) : gettype($ret5902c6f537add)) . " given");
        }
        return $ret5902c6f537add;
    }
    /**
     * @return string
     * The name of the parameter
     */
    public function getName()
    {
        $ret5902c6f537e10 = $this->name;
        if (!is_string($ret5902c6f537e10)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f537e10) . " given");
        }
        return $ret5902c6f537e10;
    }
    /**
     * @return UnionType
     * The type of the parameter
     */
    public function getUnionType()
    {
        $ret5902c6f538076 = $this->type;
        if (!$ret5902c6f538076 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f538076) == "object" ? get_class($ret5902c6f538076) : gettype($ret5902c6f538076)) . " given");
        }
        return $ret5902c6f538076;
    }
    /**
     * @return bool
     * Whether or not the parameter is variadic
     */
    public function isVariadic()
    {
        $ret5902c6f53834c = $this->is_variadic;
        if (!is_bool($ret5902c6f53834c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f53834c) . " given");
        }
        return $ret5902c6f53834c;
    }
    /**
     * @return bool
     * Whether or not the parameter is required
     */
    public function isRequired()
    {
        $ret5902c6f538602 = !$this->isOptional();
        if (!is_bool($ret5902c6f538602)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f538602) . " given");
        }
        return $ret5902c6f538602;
    }
    /**
     * @return bool
     * Whether or not the parameter is optional
     */
    public function isOptional()
    {
        $ret5902c6f538893 = $this->has_default_value || $this->is_variadic;
        if (!is_bool($ret5902c6f538893)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f538893) . " given");
        }
        return $ret5902c6f538893;
    }
    public function __toString()
    {
        $string = '';
        if (!$this->type->isEmpty()) {
            $string .= "{$this->type} ";
        }
        if ($this->is_variadic) {
            $string .= '...';
        }
        $string .= $this->name;
        if ($this->has_default_value) {
            $string .= ' = default';
        }
        $ret5902c6f538be4 = $string;
        if (!is_string($ret5902c6f538be4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f538be4) . " given");
        }
        return $ret5902c6f538be4;
    }
}