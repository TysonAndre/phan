<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

/**
 * A Fully-Qualified Method Name
 */
interface FullyQualifiedFunctionLikeName
{
    /**
     * @return bool
     * True if this FQSEN represents a closure
     */
    public function isClosure();
}