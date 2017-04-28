<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\AST;

use Phan\AST\Visitor\KindVisitorImplementation;
use Phan\CodeBase;
use Phan\Issue;
use Phan\Language\Context;
abstract class AnalysisVisitor extends KindVisitorImplementation
{
    /**
     * @var CodeBase
     * The code base within which we're operating
     */
    protected $code_base;
    /**
     * @var Context
     * The context in which the node we're going to be looking
     * at exits.
     */
    protected $context;
    /**
     * @param CodeBase $code_base
     * The code base within which we're operating
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     */
    public function __construct(CodeBase $code_base, Context $context)
    {
        $this->context = $context;
        $this->code_base = $code_base;
    }
    /**
     * @param string $issue_type
     * The type of issue to emit such as Issue::ParentlessClass
     *
     * @param int $lineno
     * The line number where the issue was found
     *
     * @param mixed ...$parameters
     * Template parameters for the issue's error message
     *
     * @return void
     */
    protected function emitIssue($issue_type, $lineno, ...$parameters)
    {
        if (!is_string($issue_type)) {
            throw new \InvalidArgumentException("Argument \$issue_type passed to emitIssue() must be of the type string, " . (gettype($issue_type) == "object" ? get_class($issue_type) : gettype($issue_type)) . " given");
        }
        if (!is_int($lineno)) {
            throw new \InvalidArgumentException("Argument \$lineno passed to emitIssue() must be of the type int, " . (gettype($lineno) == "object" ? get_class($lineno) : gettype($lineno)) . " given");
        }
        Issue::maybeEmitWithParameters($this->code_base, $this->context, $issue_type, $lineno, $parameters);
    }
}