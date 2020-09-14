<?php

declare(strict_types=1);

namespace Phan\Output;

use Phan\Output\Printer\CheckstylePrinter;
use Phan\Output\Printer\CodeClimatePrinter;
use Phan\Output\Printer\CSVPrinter;
use Phan\Output\Printer\HTMLPrinter;
use Phan\Output\Printer\JSONPrinter;
use Phan\Output\Printer\PHPLikePrinter;
use Phan\Output\Printer\PlainTextPrinter;
use Phan\Output\Printer\PylintPrinter;
use Phan\Output\Printer\VerbosePlainTextPrinter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PrinterFactory
 * Subject of future refactoring to be a bit more extensible
 */
class PrinterFactory
{

    /**
     * @return list<string> the supported types of Printers
     */
    public function getTypes(): array
    {
        return ['text', 'verbose', 'json', 'csv', 'codeclimate', 'checkstyle', 'pylint', 'phplike', 'html'];
    }

    /**
     * Return an IssuePrinterInterface of type $type that outputs issues to $output
     * @param ?string $type the configured type of printer
     */
    public function getPrinter(?string $type, OutputInterface $output): IssuePrinterInterface
    {
        switch ($type) {
            case 'codeclimate':
                $printer = new CodeClimatePrinter();
                break;
            case 'json':
                $printer = new JSONPrinter();
                break;
            case 'checkstyle':
                $printer = new CheckstylePrinter();
                break;
            case 'csv':
                $printer = new CSVPrinter();
                break;
            case 'pylint':
                $printer = new PylintPrinter();
                break;
            case 'phplike':
                $printer = new PHPLikePrinter();
                break;
            case 'html':
                $printer = new HTMLPrinter();
                break;
            case 'verbose':
                $printer = new VerbosePlainTextPrinter();
                break;
            case 'text':
            default:
                $printer = new PlainTextPrinter();
                break;
        }

        $printer->configureOutput($output);

        return $printer;
    }
}
