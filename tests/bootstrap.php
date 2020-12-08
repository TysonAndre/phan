<?php
declare(strict_types=1);

use Phan\AST\TolerantASTConverter\Shim;

// Test output of failures is shorter with relative paths than absolute paths
const RASMUS_TEST_FILE_DIR = './tests/rasmus_files/src';
const RASMUS_EXPECTED_DIR = './tests/rasmus_files/expected';
const AST_TEST_FILE_DIR = './tests/misc/ast/src';
const AST_EXPECTED_DIR = './tests/misc/ast/expected';
const TEST_FILE_DIR = './tests/files/src';
const EXPECTED_DIR = './tests/files/expected';
const MULTI_FILE_DIR = './tests/multi_files/src';
const MULTI_EXPECTED_DIR = './tests/multi_files/expected';
const SOAP_TEST_FILE_DIR = './tests/misc/soap_files/src';
const SOAP_EXPECTED_DIR = './tests/misc/soap_files/expected';
const INTL_TEST_FILE_DIR = './tests/misc/intl_files/src';
const INTL_EXPECTED_DIR = './tests/misc/intl_files/expected';
const PHP70_TEST_FILE_DIR = './tests/php70_files/src';
const PHP70_EXPECTED_DIR = './tests/php70_files/expected';
const PHP72_TEST_FILE_DIR = './tests/php72_files/src';
const PHP72_EXPECTED_DIR = './tests/php72_files/expected';
const PHP73_TEST_FILE_DIR = './tests/php73_files/src';
const PHP73_EXPECTED_DIR = './tests/php73_files/expected';
const PHP74_TEST_FILE_DIR = './tests/php74_files/src';
const PHP74_EXPECTED_DIR = './tests/php74_files/expected';
const PHP80_TEST_FILE_DIR = './tests/php80_files/src';
const PHP80_EXPECTED_DIR = './tests/php80_files/expected';

require_once dirname(__DIR__) . '/src/Phan/Bootstrap.php';

// Need to declare newer constants such as PARAM_MODIFIER_PUBLIC when running some tests
Shim::load();
