<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

/**
 * A Fully-Qualified Method Name
 */
class FullyQualifiedMethodName extends FullyQualifiedClassElement implements FullyQualifiedFunctionLikeName
{
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
        $ret5902c6f6adefc = $name;
        if (!is_string($ret5902c6f6adefc)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f6adefc) . " given");
        }
        return $ret5902c6f6adefc;
    }
    /**
     * @return bool
     * True if this FQSEN represents a closure
     */
    public function isClosure()
    {
        $ret5902c6f6ae7f4 = false;
        if (!is_bool($ret5902c6f6ae7f4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f6ae7f4) . " given");
        }
        return $ret5902c6f6ae7f4;
    }
}