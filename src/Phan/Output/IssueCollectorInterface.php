<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output;

use Phan\IssueInstance;
interface IssueCollectorInterface
{
    /**
     * Collect issue
     * @param IssueInstance $issue
     */
    public function collectIssue(IssueInstance $issue);
    /**
     * @return IssueInstance[]
     */
    public function getCollectedIssues();
    /**
     * Remove all collected issues.
     */
    public function reset();
}