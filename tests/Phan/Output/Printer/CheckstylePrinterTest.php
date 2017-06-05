<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Tests\Output\Printer;

use Phan\Issue;
use Phan\IssueInstance;
use Phan\Output\Printer\CheckstylePrinter;
use Phan\Tests\BaseTest;
use Symfony\Component\Console\Output\BufferedOutput;
class CheckstylePrinterTest extends BaseTest
{
    /**
     * @param string $string String to check against
     *
     * @dataProvider invalidUTF8StringsProvider
     */
    public function testUTF8CharactersDoNotCauseDOMAttrToFail($string)
    {
        $output = new BufferedOutput();
        $printer = new CheckstylePrinter();
        $printer->configureOutput($output);
        $printer->print_(new IssueInstance(Issue::fromType(Issue::SyntaxError), 'test.php', 0, [$string]));
        $printer->flush();
        $this->assertContains('PhanSyntaxError', $output->fetch());
    }
    public function invalidUTF8StringsProvider()
    {
        return [["a"], ["Ã±"], ["Ã("], [" ¡"], ["â‚¡"], ["â(¡"], ["â‚("], ["ğŒ¼"], ["ğ(Œ¼"], ["ğ(¼"], ["ğ(Œ("], ["ø¡¡¡¡"], ["ü¡¡¡¡¡"]];
    }
}