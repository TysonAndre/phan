<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Filter;

use Phan\IssueInstance;
use Phan\Output\IssueFilterInterface;
final class CategoryIssueFilter implements IssueFilterInterface
{
    /** @var  int */
    private $mask;
    /**
     * CategoryIssueFilter constructor.
     * @param int $mask
     */
    public function __construct($mask = -1)
    {
        $this->mask = $mask;
    }
    /**
     * @param IssueInstance $issue
     * @return bool
     */
    public function supports(IssueInstance $issue)
    {
        return (bool) ($issue->getIssue()->getCategory() & $this->mask);
    }
}