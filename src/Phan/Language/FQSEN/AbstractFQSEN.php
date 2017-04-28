<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

use Phan\Language\Context;
use Phan\Language\FQSEN;
/**
 * A Fully-Qualified Name
 */
abstract class AbstractFQSEN implements FQSEN
{
    /**
     * @var string
     * The name of this structural element
     */
    private $name = '';
    /**
     * @param string $name
     * The name of this structural element
     */
    protected function __construct($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $this->name = $name;
    }
    /**
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class::method' or
     * 'Class' or 'Class::method'.
     *
     * @return FQSEN
     */
    public static abstract function fromFullyQualifiedString($fully_qualified_string);
    /**
     * @param Context $context
     * The context in which the FQSEN string was found
     *
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class::method' or
     * 'Class' or 'Class::method'.
     *
     * @return FQSEN
     */
    public static abstract function fromStringInContext($string, Context $context);
    /**
     * @return string
     * The class associated with this FQSEN or
     * null if not defined
     */
    public function getName()
    {
        $ret5902c6f6416c4 = $this->name;
        if (!is_string($ret5902c6f6416c4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6416c4) . " given");
        }
        return $ret5902c6f6416c4;
    }
    /**
     * @return string
     * The canonical representation of the name of the object. Functions
     * and Methods, for instance, lowercase their names.
     */
    public static function canonicalName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to canonicalName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6f641955 = $name;
        if (!is_string($ret5902c6f641955)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f641955) . " given");
        }
        return $ret5902c6f641955;
    }
    /**
     * @return string
     * A string representation of this fully-qualified
     * structural element name.
     */
    public abstract function __toString();
}