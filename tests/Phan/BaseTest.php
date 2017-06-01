<?php declare(strict_types=1);

namespace Phan\Tests;

/**
 * Any common initialization or configuration should go here
 * (E.g. may want to change https://phpunit.de/manual/current/en/fixtures.html#fixtures.global-state in some classes)
 */
class BaseTest extends \PHPUnit_Framework_TestCase {
    // Needed to prevent phpunit from backing up these private static variables.
    // See https://phpunit.de/manual/current/en/fixtures.html#fixtures.global-state
    protected $backupStaticAttributesBlacklist = [
        'Phan\Language\Type' => [
            'canonical_object_map',
            'internal_fn_cache',
            'singleton_map',
        ],
    ];
}
