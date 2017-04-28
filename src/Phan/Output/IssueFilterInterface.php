<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output;

use Phan\IssueInstance;
interface IssueFilterInterface
{
    /**
     * @param IssueInstance $issue
     * @return bool
     */
    public function supports(IssueInstance $issue);
}