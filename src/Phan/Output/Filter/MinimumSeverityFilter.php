<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Filter;

use Phan\Issue;
use Phan\IssueInstance;
use Phan\Output\IssueFilterInterface;
final class MinimumSeverityFilter implements IssueFilterInterface
{
    /** @var int */
    private $minimumSeverity;
    /**
     * MinimumSeverityFilter constructor.
     * @param $minimumSeverity
     */
    public function __construct($minimumSeverity = Issue::SEVERITY_LOW)
    {
        if (!is_int($minimumSeverity)) {
            throw new \InvalidArgumentException("Argument \$minimumSeverity passed to __construct() must be of the type int, " . (gettype($minimumSeverity) == "object" ? get_class($minimumSeverity) : gettype($minimumSeverity)) . " given");
        }
        $this->minimumSeverity = $minimumSeverity;
    }
    /**
     * @param IssueInstance $issue
     * @return bool
     */
    public function supports(IssueInstance $issue)
    {
        $ret5902c6fe5dfec = $issue->getIssue()->getSeverity() >= $this->minimumSeverity;
        if (!is_bool($ret5902c6fe5dfec)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fe5dfec) . " given");
        }
        return $ret5902c6fe5dfec;
    }
}