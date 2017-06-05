<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Tests;

use Phan\CodeBase;
interface CodeBaseAwareTestInterface
{
    /** @param CodeBase $codeBase */
    public function setCodeBase(CodeBase $codeBase = null);
}