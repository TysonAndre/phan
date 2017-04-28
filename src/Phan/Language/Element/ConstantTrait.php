<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

trait ConstantTrait
{
    use ElementFutureUnionType;
    /**
     * @return string
     * The (not fully-qualified) name of this element.
     */
    public abstract function getName();
    public function __toString()
    {
        $ret5902c6f565c8f = 'const ' . $this->getName();
        if (!is_string($ret5902c6f565c8f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f565c8f) . " given");
        }
        return $ret5902c6f565c8f;
    }
}