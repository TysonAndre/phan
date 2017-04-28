<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\Type;
/**
 * A Fully-Qualified Global Structural Element
 */
abstract class FullyQualifiedGlobalStructuralElement extends AbstractFQSEN
{
    use \Phan\Language\FQSEN\Alternatives;
    use \Phan\Memoize;
    /**
     * @var string
     * The namespace in this elements scope
     */
    private $namespace = '\\';
    /**
     * @param string $namespace
     * The namespace in this element's scope
     *
     * @param string $name
     * The name of this structural element
     *
     * @param int $alternate_id
     * An alternate ID for the elemnet for use when
     * there are multiple definitions of the element
     */
    protected function __construct($namespace, $name, $alternate_id = 0)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to __construct() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to __construct() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        assert(!empty($name), "The name cannot be empty");
        assert(!empty($namespace), "The namespace cannot be empty");
        assert($namespace[0] === '\\', "The first character of a namespace must be \\");
        parent::__construct($name);
        $this->namespace = $namespace;
        $this->alternate_id = $alternate_id;
    }
    /**
     * @param string $namespace
     * The namespace in this element's scope
     *
     * @param string $name
     * The name of this structural element
     *
     * @param int $alternate_id
     * An alternate ID for the elemnet for use when
     * there are multiple definitions of the element
     *
     * @return static
     */
    public static function make($namespace, $name, $alternate_id = 0)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to make() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to make() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to make() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        // Transfer any relative namespace stuff from the
        // name to the namespace.
        $name_parts = explode('\\', $name);
        $name = array_pop($name_parts);
        $namespace = implode('\\', array_merge([$namespace], $name_parts));
        $namespace = self::cleanNamespace($namespace);
        $key = strtolower(implode('|', [get_called_class(), static::toString($namespace, $name, $alternate_id)]));
        $fqsen = self::memoizeStatic($key, function () use($namespace, $name, $alternate_id) {
            return new static($namespace, $name, $alternate_id);
        });
        return $fqsen;
    }
    /**
     * @param $fully_qualified_string
     * An fully qualified string like '\Namespace\Class'
     *
     * @return static
     */
    public static function fromFullyQualifiedString($fully_qualified_string)
    {
        if (!is_string($fully_qualified_string)) {
            throw new \InvalidArgumentException("Argument \$fully_qualified_string passed to fromFullyQualifiedString() must be of the type string, " . (gettype($fully_qualified_string) == "object" ? get_class($fully_qualified_string) : gettype($fully_qualified_string)) . " given");
        }
        $key = get_called_class() . '|' . $fully_qualified_string;
        return self::memoizeStatic($key, function () use($fully_qualified_string) {
            // Split off the alternate_id
            $parts = explode(',', $fully_qualified_string);
            $fqsen_string = $parts[0];
            $alternate_id = (int) call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$parts[1], @0);
            assert(is_int($alternate_id), "Alternate must be an integer");
            $parts = explode('\\', $fqsen_string);
            $name = array_pop($parts);
            assert(!empty($name), "The name cannot be empty");
            $namespace = '\\' . implode('\\', array_filter($parts));
            assert(!empty($namespace), "The namespace cannot be empty");
            assert($namespace[0] === '\\', "The first character of the namespace must be \\");
            return static::make($namespace, $name, $alternate_id);
        });
    }
    /**
     * @param Context $context
     * The context in which the FQSEN string was found
     *
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class'
     *
     * @return static
     */
    public static function fromStringInContext($fqsen_string, Context $context)
    {
        if (!is_string($fqsen_string)) {
            throw new \InvalidArgumentException("Argument \$fqsen_string passed to fromStringInContext() must be of the type string, " . (gettype($fqsen_string) == "object" ? get_class($fqsen_string) : gettype($fqsen_string)) . " given");
        }
        // Check to see if we're fully qualified
        if (0 === strpos($fqsen_string, '\\')) {
            return static::fromFullyQualifiedString($fqsen_string);
        }
        // Split off the alternate ID
        $parts = explode(',', $fqsen_string);
        $fqsen_string = $parts[0];
        $alternate_id = (int) call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$parts[1], @0);
        assert(is_int($alternate_id), "Alternate must be an integer");
        $parts = explode('\\', $fqsen_string);
        $name = array_pop($parts);
        assert(!empty($name), "The name cannot be empty");
        // Check for a name map
        if ($context->hasNamespaceMapFor(static::getNamespaceMapType(), $name)) {
            return $context->getNamespaceMapFor(static::getNamespaceMapType(), $name);
        }
        $namespace = implode('\\', array_filter($parts));
        // n.b.: Functions must override this method because
        //       they don't prefix the namespace for naked
        //       calls
        if (empty($namespace)) {
            $namespace = $context->getNamespace();
        }
        return static::make($namespace, $name, $alternate_id);
    }
    /**
     * @return int
     * The namespace map type such as \ast\flags\USE_NORMAL or \ast\flags\USE_FUNCTION
     */
    protected static abstract function getNamespaceMapType();
    /**
     * @return string
     * The namespace associated with this FQSEN
     * or null if not defined
     */
    public function getNamespace()
    {
        $ret5902c6f6a2860 = $this->namespace;
        if (!is_string($ret5902c6f6a2860)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6a2860) . " given");
        }
        return $ret5902c6f6a2860;
    }
    /**
     * @return static
     */
    public function withNamespace($namespace)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to withNamespace() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        return static::make(self::cleanNamespace($namespace), $this->getName(), $this->getAlternateId());
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
        if ($this->getAlternateId() === $alternate_id) {
            return $this;
        }
        assert($alternate_id < 1000, "Your alternate IDs have run away");
        return static::make($this->getNamespace(), $this->getName(), $alternate_id);
    }
    /**
     * @param string|null $namespace
     *
     * @return string
     * A cleaned version of the given namespace such that
     * its always prefixed with a '\' and never ends in a
     * '\', and is the string "\" if there is no namespace.
     */
    protected static function cleanNamespace($namespace)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to cleanNamespace() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!$namespace || empty($namespace) || $namespace === '\\') {
            $ret5902c6f6a31ef = '\\';
            if (!is_string($ret5902c6f6a31ef)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6a31ef) . " given");
            }
            return $ret5902c6f6a31ef;
        }
        // Ensure that the first character of the namespace
        // is always a '\'
        if (0 !== strpos($namespace, '\\')) {
            $namespace = '\\' . $namespace;
        }
        // Ensure that we don't have a trailing '\' on the
        // namespace
        if ('\\' === substr($namespace, -1)) {
            $namespace = substr($namespace, 0, -1);
        }
        $ret5902c6f6a3574 = $namespace;
        if (!is_string($ret5902c6f6a3574)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6a3574) . " given");
        }
        return $ret5902c6f6a3574;
    }
    /**
     * @return string
     * A string representation of this fully-qualified
     * structural element name.
     */
    public static function toString($namespace, $name, $alternate_id)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to toString() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to toString() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($alternate_id)) {
            throw new \InvalidArgumentException("Argument \$alternate_id passed to toString() must be of the type int, " . (gettype($alternate_id) == "object" ? get_class($alternate_id) : gettype($alternate_id)) . " given");
        }
        $fqsen_string = $namespace;
        if ($fqsen_string && $fqsen_string !== '\\') {
            $fqsen_string .= '\\';
        }
        $fqsen_string .= static::canonicalName($name);
        // Append an alternate ID if we need to disambiguate
        // multiple definitions
        if ($alternate_id) {
            $fqsen_string .= ',' . $alternate_id;
        }
        $ret5902c6f6a3b5f = $fqsen_string;
        if (!is_string($ret5902c6f6a3b5f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6a3b5f) . " given");
        }
        return $ret5902c6f6a3b5f;
    }
    /**
     * @return string
     * A string representation of this fully-qualified
     * structural element name.
     */
    public function __toString()
    {
        $ret5902c6f6a4572 = $this->memoize(__METHOD__, function () {
            return static::toString($this->getNamespace(), $this->getName(), $this->getAlternateId());
        });
        if (!is_string($ret5902c6f6a4572)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6a4572) . " given");
        }
        return $ret5902c6f6a4572;
    }
}