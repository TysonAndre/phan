<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Language\Scope\ClosedScope;
/**
 * A trait for closed scope elements (classes, functions, methods,
 * closures).
 */
trait ClosedScopeElement
{
    /**
     * @var ClosedScope
     */
    private $internal_scope;
    /**
     * @return void
     */
    public function setInternalScope(ClosedScope $internal_scope)
    {
        $this->internal_scope = $internal_scope;
    }
    /**
     * @return ClosedScope
     * The internal scope of this closed scope element
     */
    public function getInternalScope()
    {
        $ret5902c6f51fa3f = $this->internal_scope;
        if (!$ret5902c6f51fa3f instanceof ClosedScope) {
            throw new \InvalidArgumentException("Argument returned must be of the type ClosedScope, " . (gettype($ret5902c6f51fa3f) == "object" ? get_class($ret5902c6f51fa3f) : gettype($ret5902c6f51fa3f)) . " given");
        }
        return $ret5902c6f51fa3f;
    }
}