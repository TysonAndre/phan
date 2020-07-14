<?php

declare(strict_types=1);

namespace Phan\Tests\Output\Printer;

use Phan\Config;
use Phan\Issue;
use Phan\IssueInstance;
use Phan\Library\FileCache;
use Phan\Output\Colorizing;
use Phan\Output\Printer\VerbosePlainTextPrinter;
use Phan\Tests\BaseTest;
use Symfony\Component\Console\Output\BufferedOutput;

use function sprintf;

/**
 * Unit tests that VerbosePlainTextPrinter converts `IssueInstance`s to the expected 'text' output format.
 * NOTE: Changing the name of this file to something equivalent case-insensitively may cause issues in case-sensitive file systems (e.g. Windows)
 */
final class VerbosePlaintextPrinterTest extends BaseTest
{

    private static function setUpFakeEntry(string $path): void
    {

        $contents = '';
        for ($i = 1; $i <= 10; $i++) {
            $contents .= sprintf("mock line %d of %s\n", $i, $path);
        }
        FileCache::addEntry(Config::projectPath($path), $contents);
    }

    public function setUp(): void
    {
        Config::setValue('color_issue_messages', false);
        Colorizing::resetColorScheme();
        FileCache::clear();
        self::setUpFakeEntry('dim.php');
        self::setUpFakeEntry('test.php');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Config::setValue('color_issue_messages', false);
        // \putenv('PHAN_COLOR_SCHEME=');
        Colorizing::resetColorScheme();
        FileCache::clear();
    }

    /**
     * Sanity check of output without color codes
     */
    public function testPrintUncolorizedOutput(): void
    {
        $actual_output = self::generatePhanOutput(
            new IssueInstance(Issue::fromType(Issue::UndeclaredVariableDim), 'dim.php', 10, ['varName']),
            new IssueInstance(Issue::fromType(Issue::SyntaxError), 'test.php', 1, ['fake error']),
            new IssueInstance(Issue::fromType(Issue::UndeclaredMethod), 'undefinedmethod.php', 1, ['\\Foo::bar'])
        );
        $expected_output = '';
        // phpcs:disable
        $expected_output .= 'dim.php:10 PhanUndeclaredVariableDim Variable $varName was undeclared, but array fields are being added to it.' . \PHP_EOL;
        $expected_output .= '> mock line 10 of dim.php' . \PHP_EOL . \PHP_EOL;
        $expected_output .= 'test.php:1 PhanSyntaxError fake error' . \PHP_EOL;
        $expected_output .= '> mock line 1 of test.php' . \PHP_EOL . \PHP_EOL;
        $expected_output .= 'undefinedmethod.php:1 PhanUndeclaredMethod Call to undeclared method \Foo::bar' . \PHP_EOL;
        // Mock no contents being read
        // phpcs:enable
        $this->assertSame($expected_output, $actual_output);
    }

    /**
     * Sanity check that the expected color codes are emitted.
     */
    public function testPrintColorizedOutput(): void
    {
        Config::setValue('color_issue_messages', true);
        $actual_output = self::generatePhanOutput(
            new IssueInstance(Issue::fromType(Issue::UndeclaredVariableDim), 'dim.php', 10, ['varName']),
            new IssueInstance(Issue::fromType(Issue::SyntaxError), 'test.php', 1, ['fake error']),
            new IssueInstance(Issue::fromType(Issue::UndeclaredMethod), 'undefinedmethod.php', 1, ['\\Foo::bar'])
        );
        $expected_output = '';
        // phpcs:disable
        $expected_output .= "\x1b[96mdim.php\x1b[0m:\x1b[37m10\x1b[0m \x1b[93mPhanUndeclaredVariableDim\x1b[0m Variable \x1b[96m\$varName\x1b[0m was undeclared, but array fields are being added to it." . \PHP_EOL;
        $expected_output .= "\x1b[37m>\x1b[0m mock line 10 of dim.php" . \PHP_EOL . \PHP_EOL;
        $expected_output .= "\x1b[96mtest.php\x1b[0m:\x1b[37m1\x1b[0m \x1b[31mPhanSyntaxError\x1b[0m fake error" . \PHP_EOL;
        $expected_output .= "\x1b[37m>\x1b[0m mock line 1 of test.php" . \PHP_EOL . \PHP_EOL;
        $expected_output .= "\x1b[96mundefinedmethod.php\x1b[0m:\x1b[37m1\x1b[0m \x1b[31mPhanUndeclaredMethod\x1b[0m Call to undeclared method \x1b[93m\\Foo::bar\x1b[0m" . \PHP_EOL;
        // phpcs:enable
        $this->assertSame(\json_encode($expected_output), \json_encode($actual_output));
        $this->assertSame($expected_output, $actual_output);
    }

    private static function generatePhanOutput(IssueInstance ...$instances): string
    {
        $output = new BufferedOutput();

        $printer = new VerbosePlainTextPrinter();
        $printer->configureOutput($output);
        foreach ($instances as $instance) {
            $printer->print($instance);
        }
        return $output->fetch();
    }
}
