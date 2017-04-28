<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Filter;

use Phan\IssueInstance;
use Phan\Output\IssueFilterInterface;
final class AnyFilter implements IssueFilterInterface
{
    /**
     * @param IssueInstance $issue
     * @return bool
     */
    public function supports(IssueInstance $issue)
    {
        $ret5902c6fe3864e = true;
        if (!is_bool($ret5902c6fe3864e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fe3864e) . " given");
        }
        return $ret5902c6fe3864e;
    }
}