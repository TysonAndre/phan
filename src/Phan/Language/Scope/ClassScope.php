<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Scope;

use Phan\Language\FQSEN\FullyQualifiedClassName;
class ClassScope extends ClosedScope
{
    /**
     * @return bool
     * True if we're in a function scope
     */
    public function isInClassScope()
    {
        $ret5902c6fc1fbd1 = true;
        if (!is_bool($ret5902c6fc1fbd1)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc1fbd1) . " given");
        }
        return $ret5902c6fc1fbd1;
    }
    /**
     * @return FullyQualifiedClassName
     * Get the FullyQualifiedClassName of the class who's scope
     * we're in
     */
    public function getClassFQSEN()
    {
        $fqsen = $this->getFQSEN();
        if ($fqsen instanceof FullyQualifiedClassName) {
            $ret5902c6fc20066 = $fqsen;
            if (!$ret5902c6fc20066 instanceof FullyQualifiedClassName) {
                throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6fc20066) == "object" ? get_class($ret5902c6fc20066) : gettype($ret5902c6fc20066)) . " given");
            }
            return $ret5902c6fc20066;
        }
        assert($fqsen instanceof FullyQualifiedClassName, "FQSEN must be a FullyQualifiedClassName");
    }
}