<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Exception;

use Exception;
use Phan\IssueInstance;
/**
 * # Example Usage
 * ```
 * throw new IssueException(
 *     Issue::fromType(
 *         Issue::UndeclaredClassReference
 *     )(
 *         $context->getFile(),
 *         $node->getLine() ?? 0
 *     )
 * );
 * ```
 */
class IssueException extends Exception
{
    /** @var IssueInstance */
    private $issue_instance;
    /**
     * @param IssueInstance $issue_instance
     * An instance of an issue that was found but can't be
     * reported on immediately.
     */
    public function __construct(IssueInstance $issue_instance)
    {
        parent::__construct();
        $this->issue_instance = $issue_instance;
    }
    /**
     * @return IssueInstance
     * The issue that was found
     */
    public function getIssueInstance()
    {
        $ret5902c6f43debb = $this->issue_instance;
        if (!$ret5902c6f43debb instanceof IssueInstance) {
            throw new \InvalidArgumentException("Argument returned must be of the type IssueInstance, " . (gettype($ret5902c6f43debb) == "object" ? get_class($ret5902c6f43debb) : gettype($ret5902c6f43debb)) . " given");
        }
        return $ret5902c6f43debb;
    }
}