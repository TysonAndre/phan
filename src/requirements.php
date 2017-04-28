<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
assert(PHP_VERSION_ID >= 50600, 'Phan with slow php 5.6 backport requires PHP version 5.6 or greater. See https://github.com/etsy/phan#getting-it-running for more details.');
assert(file_exists(__DIR__ . '/../vendor/autoload.php') || file_exists(__DIR__ . '/../../../autoload.php'), 'Autoloader not found. Make sure you run `composer install` before running Phan. See https://github.com/etsy/phan#getting-it-running for more details.');