<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
// Listen for all errors
error_reporting(E_ALL);
// Take as much memory as we need
ini_set("memory_limit", '-1');
// Add the root to the include path
define('CLASS_DIR', __DIR__ . '/../');
set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_DIR);
// Use the composer autoloader
foreach ([__DIR__ . '/../../vendor/autoload.php', __DIR__ . '/../../../../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}
define('EXIT_SUCCESS', 0);
define('EXIT_FAILURE', 1);
define('EXIT_ISSUES_FOUND', EXIT_FAILURE);
// Customize assertions
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_BAIL, true);
assert_options(ASSERT_WARNING, false);
assert_options(ASSERT_CALLBACK, function ($script, $line, $expression, $message) {
    if (!is_string($script)) {
        throw new \InvalidArgumentException("Argument \$script passed to () must be of the type string, " . (gettype($script) == "object" ? get_class($script) : gettype($script)) . " given");
    }
    if (!is_int($line)) {
        throw new \InvalidArgumentException("Argument \$line passed to () must be of the type int, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
    }
    print "{$script}:{$line} ({$expression}) {$message}\n";
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    exit(EXIT_FAILURE);
});
// Print more of the backtrace than is done by default
set_exception_handler(function (Throwable $throwable) {
    print "{$throwable}\n";
    exit(EXIT_FAILURE);
});
/**
 * @suppress PhanUnreferencedMethod
 */
function phan_error_handler($errno, $errstr, $errfile, $errline)
{
    // The transphpiler is suppressing notices, e.g. with @$array['key']
    if (error_reporting() == 0) {
        return;
    }
    print "{$errfile}:{$errline} [{$errno}] {$errstr}\n";
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    exit(EXIT_FAILURE);
}
set_error_handler('phan_error_handler');