<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element\Comment;

use Phan\Language\Context;
use Phan\Language\Element\Variable;
use Phan\Language\UnionType;
class Method
{
    /**
     * @var string
     * The name of the method
     */
    private $name;
    /**
     * @var UnionType
     * The return type of the magic method
     */
    private $type;
    /**
     * @var Parameter[]
     * A list of phpdoc parameters
     */
    private $parameters;
    /**
     * @var bool
     * Whether or not this is a static magic method
     */
    private $is_static;
    /**
     * @param string $name
     * The name of the method
     *
     * @param UnionType $type
     * The return type of the method
     *
     * @param Parameter[] $parameters
     * 0 or more comment parameters for this magic method
     *
     * @param bool $is_static
     * Whether this method is static
     */
    public function __construct($name, UnionType $type, array $parameters, $is_static)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_bool($is_static)) {
            throw new \InvalidArgumentException("Argument \$is_static passed to __construct() must be of the type bool, " . (gettype($is_static) == "object" ? get_class($is_static) : gettype($is_static)) . " given");
        }
        $this->name = $name;
        $this->type = $type;
        $this->parameters = $parameters;
        $this->is_static = $is_static;
    }
    /**
     * @return string
     * The name of the magic method
     */
    public function getName()
    {
        $ret5902c6f52a4b2 = $this->name;
        if (!is_string($ret5902c6f52a4b2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f52a4b2) . " given");
        }
        return $ret5902c6f52a4b2;
    }
    /**
     * @return UnionType
     * The return type of the magic method
     */
    public function getUnionType()
    {
        $ret5902c6f52a722 = $this->type;
        if (!$ret5902c6f52a722 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f52a722) == "object" ? get_class($ret5902c6f52a722) : gettype($ret5902c6f52a722)) . " given");
        }
        return $ret5902c6f52a722;
    }
    /**
     * @return Parameter[] - comment parameters of magic method, from phpdoc.
     */
    public function getParameterList()
    {
        $ret5902c6f52aa9f = $this->parameters;
        if (!is_array($ret5902c6f52aa9f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f52aa9f) . " given");
        }
        return $ret5902c6f52aa9f;
    }
    /**
     * @return bool
     * Whether or not the magic method is static
     */
    public function isStatic()
    {
        $ret5902c6f52ad02 = $this->is_static;
        if (!is_bool($ret5902c6f52ad02)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f52ad02) . " given");
        }
        return $ret5902c6f52ad02;
    }
    /**
     * @return int
     * Number of required parameters of this method
     */
    public function getNumberOfRequiredParameters()
    {
        $ret5902c6f52b4bd = array_reduce($this->parameters, function ($carry, Parameter $parameter) {
            if (!is_int($carry)) {
                throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
            }
            $ret5902c6f52afe6 = $carry + ($parameter->isRequired() ? 1 : 0);
            if (!is_int($ret5902c6f52afe6)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f52afe6) . " given");
            }
            return $ret5902c6f52afe6;
        }, 0);
        if (!is_int($ret5902c6f52b4bd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f52b4bd) . " given");
        }
        return $ret5902c6f52b4bd;
    }
    /**
     * @return int
     * Number of optional parameters of this method
     */
    public function getNumberOfOptionalParameters()
    {
        $ret5902c6f52bcc3 = array_reduce($this->parameters, function ($carry, Parameter $parameter) {
            if (!is_int($carry)) {
                throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
            }
            $ret5902c6f52b7a1 = $carry + ($parameter->isOptional() ? 1 : 0);
            if (!is_int($ret5902c6f52b7a1)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f52b7a1) . " given");
            }
            return $ret5902c6f52b7a1;
        }, 0);
        if (!is_int($ret5902c6f52bcc3)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f52bcc3) . " given");
        }
        return $ret5902c6f52bcc3;
    }
    public function __toString()
    {
        $string = 'function ';
        // Magic methods can't be by ref?
        $string .= $this->getName();
        $string .= '(' . implode(', ', $this->getParameterList()) . ')';
        if (!$this->getUnionType()->isEmpty()) {
            $string .= ' : ' . (string) $this->getUnionType();
        }
        $ret5902c6f52c059 = $string;
        if (!is_string($ret5902c6f52c059)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f52c059) . " given");
        }
        return $ret5902c6f52c059;
    }
}