<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output;

interface BufferedPrinterInterface extends IssuePrinterInterface
{
    /** flush printer buffer */
    public function flush();
}