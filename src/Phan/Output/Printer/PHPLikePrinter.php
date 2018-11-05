<?php declare(strict_types = 1);
namespace Phan\Output\Printer;

use Phan\Issue;
use Phan\IssueInstance;
use Phan\Output\IssuePrinterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This prints issues similarly to `php --syntax-check` to the configured OutputInterface.
 * The output is intended for use by other programs (or processes)
 */
final class PHPLikePrinter implements IssuePrinterInterface
{
    /** @var OutputInterface */
    private $output;

    /** @param IssueInstance $instance */
    public function print(IssueInstance $instance)
    {
        // Same format as `php -l`: "Parse error: %s in %s on line %d
        $line = sprintf(
            "Phan error: %s: %s: %s in %s on line %d",
            Issue::getNameForCategory($instance->getIssue()->getCategory()),
            $instance->getIssue()->getType(),
            $instance->getMessage(),
            $instance->getFile(),
            $instance->getLine()
        );

        $this->output->writeln($line);
    }

    /**
     * @param OutputInterface $output
     */
    public function configureOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}
