<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output;

use Phan\IssueInstance;
use Symfony\Component\Console\Output\OutputInterface;
interface IssuePrinterInterface
{
    /** @param IssueInstance $instance */
    public function print_(IssueInstance $instance);
    /**
     * @param OutputInterface $output
     */
    public function configureOutput(OutputInterface $output);
}