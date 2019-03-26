#!/usr/bin/env php
<?php declare(strict_types=1);

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Tyson Andre
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Based on https://github.com/phan/phan/blob/master/internal/dump_fallback_ast.php
 */

// @phan-file-suppress PhanNativePHPSyntaxCheckPlugin this does not expect "env php" header when the script is provided on stdin
// @phan-file-suppress PhanMissingRequireFile this depends on where Phan is installed
if (file_exists(__DIR__ . "/../../../../vendor/autoload.php")) {
    require __DIR__ . "/../../../../vendor/autoload.php";
} else {
    require __DIR__ . "/../vendor/autoload.php";
}

dump_main();

/**
 * Dumps a snippet provided as a command line argument
 * @return void
 * @throws Exception if it can't render the AST
 */
function dump_main()
{
    $print_help = static function (int $exit_code) {
        global $argv;
        $help = <<<"EOB"
Usage: php [--help|-h|help] {$argv[0]} 'snippet'
E.g.
  {$argv[0]} '2+2;'
  {$argv[0]} '<?php function test() {}'
  {$argv[0]} "$(cat 'path/to/file.php')"

Dumps the token_get_all() output for a given snippet.
EOB;
        echo $help;
        exit($exit_code);
    };
    error_reporting(E_ALL);
    global $argv;

    foreach ($argv as $arg) {
        if (in_array($arg, ['help', '-h', '--help'])) {
            $print_help(0);
        }
    }
    $argv = array_values($argv);

    if (count($argv) !== 2) {
        $print_help(1);
    }
    $expr = $argv[1];
    if (!is_string($expr)) {
        throw new AssertionError("missing 2nd argument");
    }

    // Guess if this is a snippet or file contents
    if (($expr[0] ?? '') !== '<') {
        $expr = '<' . '?php ' . $expr;
    }

    dump_tokens($expr);
}

function dump_tokens(string $expr) {
    foreach (token_get_all($expr) as $token) {
        if (is_string($token)) {
            echo $token . "\n";
            continue;
        }
        list($type, $str, $unused_line) = $token;
        echo token_name($type) . ': ' . $str . "\n";  // . " #$line\n";
    }
}
