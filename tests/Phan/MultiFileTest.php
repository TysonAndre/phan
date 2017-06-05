<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Tests;

class MultiFileTest extends AbstractPhanFileTest
{
    /**
     * @suppress PhanUndeclaredConstant
     * The constant MULTI_FILE_DIR is defined in `phpunit.xml`.
     */
    public function getTestFiles()
    {
        return [[[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '157_a.php', MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '157_b.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '157.php' . AbstractPhanFileTest::EXPECTED_SUFFIX], [[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '245_a.php', MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '245_b.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '245.php' . AbstractPhanFileTest::EXPECTED_SUFFIX], [[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '301_a.php', MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '301_b.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '301.php' . AbstractPhanFileTest::EXPECTED_SUFFIX], [[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '321_a.php', MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '321_b.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '321.php' . AbstractPhanFileTest::EXPECTED_SUFFIX], [[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '551_b.php', MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '551_c.php', MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '551_a.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '551.php' . AbstractPhanFileTest::EXPECTED_SUFFIX], [[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '699.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '699.php' . AbstractPhanFileTest::EXPECTED_SUFFIX, MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '699_config.php'], [[MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '704.php'], MULTI_EXPECTED_DIR . DIRECTORY_SEPARATOR . '704.php' . AbstractPhanFileTest::EXPECTED_SUFFIX, MULTI_FILE_DIR . DIRECTORY_SEPARATOR . '704_config.php']];
    }
}