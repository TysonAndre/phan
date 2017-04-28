<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Output;

use Phan\Output\Printer\CheckstylePrinter;
use Phan\Output\Printer\CodeClimatePrinter;
use Phan\Output\Printer\CSVPrinter;
use Phan\Output\Printer\JSONPrinter;
use Phan\Output\Printer\PlainTextPrinter;
use Phan\Output\Printer\PylintPrinter;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Class PrinterFactory
 * Subject of future refactoring to be a bit more extensible
 */
class PrinterFactory
{
    /**
     * @return string[]
     */
    public function getTypes()
    {
        $ret5902c6fea944f = ['text', 'json', 'csv', 'codeclimate', 'checkstyle', 'pylint'];
        if (!is_array($ret5902c6fea944f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fea944f) . " given");
        }
        return $ret5902c6fea944f;
    }
    public function getPrinter($type, OutputInterface $output)
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
            case 'text':
            default:
                $printer = new PlainTextPrinter();
                break;
        }
        $printer->configureOutput($output);
        $ret5902c6fea9aa9 = $printer;
        if (!$ret5902c6fea9aa9 instanceof IssuePrinterInterface) {
            throw new \InvalidArgumentException("Argument returned must be of the type IssuePrinterInterface, " . (gettype($ret5902c6fea9aa9) == "object" ? get_class($ret5902c6fea9aa9) : gettype($ret5902c6fea9aa9)) . " given");
        }
        return $ret5902c6fea9aa9;
    }
}