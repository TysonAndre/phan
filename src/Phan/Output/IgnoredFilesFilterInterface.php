<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output;

interface IgnoredFilesFilterInterface
{
    /**
     * @param string $filename
     * @return bool True if filename is ignored during analysis
     */
    public function isFilenameIgnored($filename);
}