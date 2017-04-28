<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

use Phan\Language\Context;
use Phan\Language\FQSEN;
/**
 * A Fully-Qualified Class Name
 */
abstract class FullyQualifiedClassElement extends AbstractFQSEN
{
    use \Phan\Language\FQSEN\Alternatives;
    use \Phan\Memoize;
    /**
     * @var FullyQualifiedClassName
     * A fully qualified class name for the class in
     * which this element exists
     */
    private $fully_qualified_class_name;
    /**
     * @param FullyQualifiedClassName $fully_qualified_class_name
     * The fully qualified class name of the class in whic
     * this element exists
     *
     * @param string $name
     * A name if one is in scope or the empty string otherwise.
     *
     * @param int $alternate_id
     * An alternate ID for the element for use when
     * there are multiple definitions of the element
     */
    protected function __construct(FullyQualifiedClassName $fully_qualified_class_name, $name, $alternate_id = 0)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to __construct() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        parent::__construct($name);
        $this->fully_qualified_class_name = $fully_qualified_class_name;
        $this->alternate_id = $alternate_id;
    }
    /**
     * @param FullyQualifiedClassName $fully_qualified_class_name
     * The fully qualified class name of the class in whic
     * this element exists
     *
     * @param string $name
     * A name if one is in scope or the empty string otherwise.
     *
     * @param int $alternate_id
     * An alternate ID for the element for use when
     * there are multiple definitions of the element
     */
    public static function make(FullyQualifiedClassName $fully_qualified_class_name, $name, $alternate_id = 0)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to make() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to make() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        $name = static::canonicalName($name);
        $key = self::toString($fully_qualified_class_name, $name, $alternate_id) . '|' . get_called_class();
        return self::memoizeStatic($key, function () use($fully_qualified_class_name, $name, $alternate_id) {
            return new static($fully_qualified_class_name, $name, $alternate_id);
        });
    }
    /**
     * @param $fully_qualified_string
     * An FQSEN string like '\Namespace\Class::methodName'
     */
    public static function fromFullyQualifiedString($fully_qualified_string)
    {
        if (!is_string($fully_qualified_string)) {
            throw new \InvalidArgumentException("Argument \$fully_qualified_string passed to fromFullyQualifiedString() must be of the type string, " . (gettype($fully_qualified_string) == "object" ? get_class($fully_qualified_string) : gettype($fully_qualified_string)) . " given");
        }
        assert(false !== strpos($fully_qualified_string, '::'), "Fully qualified class element lacks '::' delimiter");
        list($fully_qualified_class_name_string, $name_string) = explode('::', $fully_qualified_string);
        $fully_qualified_class_name = FullyQualifiedClassName::fromFullyQualifiedString($fully_qualified_class_name_string);
        // Make sure that we're actually getting a class
        // name reference back
        assert($fully_qualified_class_name instanceof FullyQualifiedClassName, "FQSEN must be an instanceof FullyQualifiedClassName");
        // Split off the alternate ID
        $parts = explode(',', $name_string);
        $name = $parts[0];
        $alternate_id = (int) call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$parts[1], @0);
        assert(is_int($alternate_id), "Alternate must be an integer");
        return static::make($fully_qualified_class_name, $name, $alternate_id);
    }
    /**
     * @param Context $context
     * The context in which the FQSEN string was found
     *
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class::methodName'
     *
     * @return static
     */
    public static function fromStringInContext($fqsen_string, Context $context)
    {
        if (!is_string($fqsen_string)) {
            throw new \InvalidArgumentException("Argument \$fqsen_string passed to fromStringInContext() must be of the type string, " . (gettype($fqsen_string) == "object" ? get_class($fqsen_string) : gettype($fqsen_string)) . " given");
        }
        // Test to see if we have a class defined
        if (false === strpos($fqsen_string, '::')) {
            assert($context->isInClassScope(), "Cannot reference class element without class name when not in class scope.");
            $fully_qualified_class_name = $context->getClassFQSEN();
        } else {
            assert(false !== strpos($fqsen_string, '::'), "Fully qualified class element lacks '::' delimiter");
            list($class_name_string, $fqsen_string) = explode('::', $fqsen_string);
            $fully_qualified_class_name = FullyQualifiedClassName::fromStringInContext($class_name_string, $context);
        }
        // Split off the alternate ID
        $parts = explode(',', $fqsen_string);
        $name = $parts[0];
        $alternate_id = (int) call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$parts[1], @0);
        assert(is_int($alternate_id), "Alternate must be an integer");
        return static::make($fully_qualified_class_name, $name, $alternate_id);
    }
    /**
     * @return FullyQualifiedClassName
     * The fully qualified class name associated with this
     * class element.
     */
    public function getFullyQualifiedClassName()
    {
        $ret5902c6f66265d = $this->fully_qualified_class_name;
        if (!$ret5902c6f66265d instanceof FullyQualifiedClassName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6f66265d) == "object" ? get_class($ret5902c6f66265d) : gettype($ret5902c6f66265d)) . " given");
        }
        return $ret5902c6f66265d;
    }
    /**
     * @return static
     * A new object with the given fully qualified
     * class name
     */
    public function withFullyQualifiedClassName(FullyQualifiedClassName $fully_qualified_class_name)
    {
        return static::make($fully_qualified_class_name, $this->getName(), $this->getAlternateId());
    }
    /**
     * @return static
     * A FQSEN with the given alternate_id set
     */
    public function withAlternateId($alternate_id)
    {
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to withAlternateId() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        assert($alternate_id < 1000, "Your alternate IDs have run away");
        return static::make($this->getFullyQualifiedClassName(), $this->getName(), $alternate_id);
    }
    /**
     * @return string
     * A string representation of the given values
     */
    public static function toString(FullyQualifiedClassName $fqsen, $name, $alternate_id)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to toString() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to toString() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        $fqsen_string = (string) $fqsen;
        $fqsen_string .= '::' . $name;
        if ($alternate_id) {
            $fqsen_string .= ",{$alternate_id}";
        }
        $ret5902c6f662d91 = $fqsen_string;
        if (!is_string($ret5902c6f662d91)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f662d91) . " given");
        }
        return $ret5902c6f662d91;
    }
    /**
     * @return string
     * A string representation of this fully-qualified
     * structural element name.
     */
    public function __toString()
    {
        $fqsen_string = $this->memoize(__METHOD__, function () {
            return self::toString($this->getFullyQualifiedClassName(), $this->getName(), $this->alternate_id);
        });
        $ret5902c6f66357a = $fqsen_string;
        if (!is_string($ret5902c6f66357a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f66357a) . " given");
        }
        return $ret5902c6f66357a;
    }
}