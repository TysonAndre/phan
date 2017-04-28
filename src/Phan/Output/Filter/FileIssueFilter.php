<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Filter;

use Phan\IssueInstance;
use Phan\Output\IgnoredFilesFilterInterface;
use Phan\Output\IssueFilterInterface;
final class FileIssueFilter implements IssueFilterInterface
{
    /** @var IgnoredFilesFilterInterface */
    private $ignoredFilesFilter;
    /**
     * FileIssueFilter constructor.
     *
     * @param IgnoredFilesFilterInterface $ignoredFilesFilter
     */
    public function __construct(IgnoredFilesFilterInterface $ignoredFilesFilter)
    {
        $this->ignoredFilesFilter = $ignoredFilesFilter;
    }
    /**
     * @param IssueInstance $issue
     * @return bool
     */
    public function supports(IssueInstance $issue)
    {
        $ret5902c6fe54785 = !$this->ignoredFilesFilter->isFilenameIgnored($issue->getFile());
        if (!is_bool($ret5902c6fe54785)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fe54785) . " given");
        }
        return $ret5902c6fe54785;
    }
}