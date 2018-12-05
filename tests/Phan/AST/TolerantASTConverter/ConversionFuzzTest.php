<?php declare(strict_types = 1);

namespace Phan\Tests\AST\TolerantASTConverter;

use ast;
use ast\Node;
use Phan\AST\TolerantASTConverter\TolerantASTConverter;
use Phan\Config;
use Phan\Debug;
use Phan\Tests\BaseTest;

/**
 * Tests that the polyfill works with valid ASTs
 *
 * @phan-file-suppress PhanThrowTypeAbsent it's a test
 */
final class ConversionFuzzTest extends BaseTest
{
    const BINARY_OPERATORS = [
        '**',
        'instanceof', // FIXME Determine precedence
        '*',
        '/',
        '%',
        '+',
        '-',
        '.',
        '<<',
        '>>',
        '<',
        '<=',
        '>',
        '>=',
        '==',
        '!=',
        '===',
        '!==',
        '<>',
        '<=>',
        '&',
        '^',
        '|',
        '||',
        '??',
        '?:',
        '=',
        '+=',
        '-=',
        '*=',
        '**=',
        '/=',
        '.=',
        '%=',
        '&=',
        '|=',
        '^=',
        '<<=',
        '>>=',
        'and',
        'xor',
        'or',
    ];

    /** @var string[] list of parsed errors */
    private $errors = [];

    public function testWorksWithTrigrams() {
        foreach (self::BINARY_OPERATORS as $op1) {
            foreach (self::BINARY_OPERATORS as $op2) {
                $code = \sprintf('<'.'?php $x %s $y %s $z;', $op1, $op2);
                $this->checkCode($code);
            }
        }
        $this->assertSame(0, count($this->errors), implode("\n\n", $this->errors));
    }

    const FAKE_FILE_PATH = 'fakepath.php';

    private function checkCode(string $code)
    {
        try {
            $expected_ast = ast\parse_code($code, 50);
        } catch (\ParseError $_) {
            return;
        }
        $converter = new TolerantASTConverter();
        $converter->setPHPVersionId(PHP_VERSION_ID);
        $errors = [];
        $actual_ast = $converter->parseCodeAsPHPAST($code, Config::AST_VERSION, $errors);
        $expected_ast_repr = \var_export($expected_ast, true);
        $ast_repr = \var_export($actual_ast, true);
        if ($expected_ast_repr === $ast_repr) {
            return;
        }
        $this->recordFailure($code, $actual_ast, $expected_ast);
    }

    private function recordFailure(string $code, Node $expected_ast, Node $actual_ast)
    {
        $expected_dump = Debug::nodeToString($expected_ast);
        $actual_dump = Debug::nodeToString($actual_ast);
        $error = <<<EOT
Failed to parse code:
$code
expected:
$expected_dump
actual:
$actual_dump
EOT;
        $this->errors[] = $error;
    }
}
