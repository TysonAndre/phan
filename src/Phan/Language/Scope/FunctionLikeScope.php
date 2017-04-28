<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Scope;

use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
// TODO: Wrap this with a ClosureLikeScope
class FunctionLikeScope extends ClosedScope
{
    /**
     * @return bool
     * True if we're in a function scope
     */
    public function isInFunctionLikeScope()
    {
        $ret5902c6fc32495 = true;
        if (!is_bool($ret5902c6fc32495)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc32495) . " given");
        }
        return $ret5902c6fc32495;
    }
    /**
     * @return FullyQualifiedMethodName|FullyQualifiedFunctionName
     * Get the FQSEN for the closure, method or function we're in
     */
    public function getFunctionLikeFQSEN()
    {
        $fqsen = $this->getFQSEN();
        if ($fqsen instanceof FullyQualifiedMethodName) {
            return $fqsen;
        }
        if ($fqsen instanceof FullyQualifiedFunctionName) {
            return $fqsen;
        }
        assert(false, "FQSEN must be a function-like FQSEN");
    }
}