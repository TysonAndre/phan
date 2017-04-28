<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Language\FutureUnionType;
interface ConstantInterface
{
    /**
     * @return void
     */
    public function setFutureUnionType(FutureUnionType $future_union_type);
}