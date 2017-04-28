<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output\Printer;

use Phan\Issue;
use Phan\IssueInstance;
use Phan\Output\BufferedPrinterInterface;
use Symfony\Component\Console\Output\OutputInterface;
final class CodeClimatePrinter implements BufferedPrinterInterface
{
    const CODECLIMATE_SEVERITY_INFO = 'info';
    const CODECLIMATE_SEVERITY_CRITICAL = 'critical';
    const CODECLIMATE_SEVERITY_NORMAL = 'normal';
    /** @var  OutputInterface */
    private $output;
    /** @var array */
    private $messages = [];
    /** @param IssueInstance $instance */
    public function print_(IssueInstance $instance)
    {
        $this->messages[] = ['type' => 'issue', 'check_name' => $instance->getIssue()->getType(), 'description' => $instance->getMessage(), 'categories' => ['Bug Risk'], 'severity' => self::mapSeverity($instance->getIssue()->getSeverity()), 'location' => ['path' => preg_replace('/^\\/code\\//', '', $instance->getFile()), 'lines' => ['begin' => $instance->getLine(), 'end' => $instance->getLine()]]];
    }
    /**
     * @param int $rawSeverity
     * @return string
     */
    private static function mapSeverity($rawSeverity)
    {
        if (!is_int($rawSeverity)) {
            throw new \InvalidArgumentException("Argument \$rawSeverity passed to mapSeverity() must be of the type int, " . (gettype($rawSeverity) == "object" ? get_class($rawSeverity) : gettype($rawSeverity)) . " given");
        }
        $severity = self::CODECLIMATE_SEVERITY_INFO;
        switch ($rawSeverity) {
            case Issue::SEVERITY_CRITICAL:
                $severity = self::CODECLIMATE_SEVERITY_CRITICAL;
                break;
            case Issue::SEVERITY_NORMAL:
                $severity = self::CODECLIMATE_SEVERITY_NORMAL;
                break;
        }
        $ret5902c6fe95e9d = $severity;
        if (!is_string($ret5902c6fe95e9d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fe95e9d) . " given");
        }
        return $ret5902c6fe95e9d;
    }
    /** flush printer buffer */
    public function flush()
    {
        // See https://github.com/codeclimate/spec/blob/master/SPEC.md#output
        // for details on the CodeClimate output format
        foreach ($this->messages as $message) {
            $this->output->write(json_encode($message, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE) . chr(0));
        }
        $this->messages = [];
    }
    /**
     * @param OutputInterface $output
     */
    public function configureOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}