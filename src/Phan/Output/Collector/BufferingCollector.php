<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Collector;

use Phan\IssueInstance;
use Phan\Output\Filter\AnyFilter;
use Phan\Output\IssueCollectorInterface;
use Phan\Output\IssueFilterInterface;
final class BufferingCollector implements IssueCollectorInterface
{
    /** @var  IssueInstance[] */
    private $issues = [];
    /** @var IssueFilterInterface|null */
    private $filter;
    /**
     * BufferingCollector constructor.
     * @param IssueFilterInterface $filter
     */
    public function __construct(IssueFilterInterface $filter = null)
    {
        $this->filter = $filter;
        if (null === $this->filter) {
            $this->filter = new AnyFilter();
        }
    }
    /**
     * Collect issue
     * @param IssueInstance $issue
     */
    public function collectIssue(IssueInstance $issue)
    {
        if (!$this->filter->supports($issue)) {
            return;
        }
        $this->issues[$this->formatSortableKey($issue)] = $issue;
    }
    /**
     * @param IssueInstance $issue
     * @return string
     */
    private function formatSortableKey(IssueInstance $issue)
    {
        // This needs to be a sortable key so that output
        // is in the expected order
        return implode('|', [$issue->getFile(), str_pad((string) $issue->getLine(), 5, '0', STR_PAD_LEFT), $issue->getIssue()->getType(), $issue->getMessage()]);
    }
    /**
     * @return IssueInstance[]
     */
    public function getCollectedIssues()
    {
        ksort($this->issues);
        $ret5902c6fe0f2c0 = array_values($this->issues);
        if (!is_array($ret5902c6fe0f2c0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fe0f2c0) . " given");
        }
        return $ret5902c6fe0f2c0;
    }
    /**
     * @return void
     */
    public function flush()
    {
        $this->issues = [];
    }
    /**
     * Removes all collected issues.
     */
    public function reset()
    {
        $this->issues = [];
    }
}