<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Filter;

use Phan\IssueInstance;
use Phan\Output\IssueFilterInterface;
final class ChainedIssueFilter implements IssueFilterInterface
{
    /** @var IssueFilterInterface[] */
    private $filters = [];
    /**
     * ChainedIssueFilter constructor.
     *
     * @param IssueFilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }
    /**
     * @param IssueInstance $issue
     * @return bool
     */
    public function supports(IssueInstance $issue)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->supports($issue)) {
                $ret5902c6fe4b143 = false;
                if (!is_bool($ret5902c6fe4b143)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fe4b143) . " given");
                }
                return $ret5902c6fe4b143;
            }
        }
        $ret5902c6fe4b608 = true;
        if (!is_bool($ret5902c6fe4b608)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fe4b608) . " given");
        }
        return $ret5902c6fe4b608;
    }
}