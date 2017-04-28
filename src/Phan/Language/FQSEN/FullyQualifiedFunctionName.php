<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

use Phan\Language\Context;
/**
 * A Fully-Qualified Function Name
 */
class FullyQualifiedFunctionName extends FullyQualifiedGlobalStructuralElement implements FullyQualifiedFunctionLikeName
{
    /**
     * @return int
     * The namespace map type such as \ast\flags\USE_NORMAL or \ast\flags\USE_FUNCTION
     */
    protected static function getNamespaceMapType()
    {
        $ret5902c6f689a1f = \ast\flags\USE_FUNCTION;
        if (!is_int($ret5902c6f689a1f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f689a1f) . " given");
        }
        return $ret5902c6f689a1f;
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
        $ret5902c6f689d3e = $name;
        if (!is_string($ret5902c6f689d3e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f689d3e) . " given");
        }
        return $ret5902c6f689d3e;
    }
    /**
     * @param Context $context
     * The context in which the FQSEN string was found
     *
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class'
     */
    public static function fromStringInContext($fqsen_string, Context $context)
    {
        if (!is_string($fqsen_string)) {
            throw new \InvalidArgumentException("Argument \$fqsen_string passed to fromStringInContext() must be of the type string, " . (gettype($fqsen_string) == "object" ? get_class($fqsen_string) : gettype($fqsen_string)) . " given");
        }
        // Check to see if we're fully qualified
        if (0 === strpos($fqsen_string, '\\')) {
            $ret5902c6f68a30b = static::fromFullyQualifiedString($fqsen_string);
            if (!$ret5902c6f68a30b instanceof FullyQualifiedFunctionName) {
                throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedFunctionName, " . (gettype($ret5902c6f68a30b) == "object" ? get_class($ret5902c6f68a30b) : gettype($ret5902c6f68a30b)) . " given");
            }
            return $ret5902c6f68a30b;
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
            $ret5902c6f68a962 = $context->getNamespaceMapFor(static::getNamespaceMapType(), $name);
            if (!$ret5902c6f68a962 instanceof FullyQualifiedFunctionName) {
                throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedFunctionName, " . (gettype($ret5902c6f68a962) == "object" ? get_class($ret5902c6f68a962) : gettype($ret5902c6f68a962)) . " given");
            }
            return $ret5902c6f68a962;
        }
        // For functions we don't use the context's namespace if
        // there is no NS on the call.
        $namespace = implode('\\', array_filter($parts));
        $ret5902c6f68acd2 = static::make($namespace, $name, $alternate_id);
        if (!$ret5902c6f68acd2 instanceof FullyQualifiedFunctionName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedFunctionName, " . (gettype($ret5902c6f68acd2) == "object" ? get_class($ret5902c6f68acd2) : gettype($ret5902c6f68acd2)) . " given");
        }
        return $ret5902c6f68acd2;
    }
    public static function fromClosureInContext(Context $context)
    {
        $name = 'closure_' . substr(md5(implode('|', [$context->getFile(), $context->getLineNumberStart()])), 0, 12);
        $ret5902c6f68b363 = static::fromStringInContext($name, $context);
        if (!$ret5902c6f68b363 instanceof FullyQualifiedFunctionName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedFunctionName, " . (gettype($ret5902c6f68b363) == "object" ? get_class($ret5902c6f68b363) : gettype($ret5902c6f68b363)) . " given");
        }
        return $ret5902c6f68b363;
    }
    /**
     * @return bool
     * True if this FQSEN represents a closure
     */
    public function isClosure()
    {
        $ret5902c6f68b6d1 = preg_match('/^closure_/', $this->getName()) === 1;
        if (!is_bool($ret5902c6f68b6d1)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f68b6d1) . " given");
        }
        return $ret5902c6f68b6d1;
    }
}