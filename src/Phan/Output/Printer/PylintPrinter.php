<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Printer;

use Phan\Issue;
use Phan\IssueInstance;
use Phan\Output\IssuePrinterInterface;
use Symfony\Component\Console\Output\OutputInterface;
final class PylintPrinter implements IssuePrinterInterface
{
    /** @var OutputInterface */
    private $output;
    /** @param IssueInstance $instance */
    public function print_(IssueInstance $instance)
    {
        $message = sprintf("%s: %s", $instance->getIssue()->getType(), $instance->getMessage());
        $line = sprintf("%s:%d: [%s] %s", $instance->getFile(), $instance->getLine(), self::get_severity_code($instance), $message);
        $this->output->writeln($line);
    }
    public static function get_severity_code(IssueInstance $instance)
    {
        $issue = $instance->getIssue();
        $categoryId = $issue->getTypeId();
        switch ($issue->getSeverity()) {
            case Issue::SEVERITY_LOW:
                $ret5902c6fec6c43 = 'C' . $categoryId;
                if (!is_string($ret5902c6fec6c43)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fec6c43) . " given");
                }
                return $ret5902c6fec6c43;
            case Issue::SEVERITY_NORMAL:
                $ret5902c6fec70a9 = 'W' . $categoryId;
                if (!is_string($ret5902c6fec70a9)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fec70a9) . " given");
                }
                return $ret5902c6fec70a9;
            case Issue::SEVERITY_CRITICAL:
                $ret5902c6fec7321 = 'E' . $categoryId;
                if (!is_string($ret5902c6fec7321)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fec7321) . " given");
                }
                return $ret5902c6fec7321;
        }
    }
    /**
     * @param OutputInterface $output
     */
    public function configureOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}