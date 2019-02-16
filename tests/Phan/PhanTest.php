<?php declare(strict_types=1);

namespace Phan\Tests;

use Phan\Config;
use Phan\Plugin\ConfigPluginSet;

/**
 * The default type of test for Phan
 *
 * Verifies that the analysis of a single file with default settings has the expected output.
 */
class PhanTest extends AbstractPhanFileTest
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        // Read and apply any custom configuration
        // overrides for the tests.
        $test_config_file_name = dirname(__FILE__) . '/../.phan_for_test/config.php';
        foreach (require($test_config_file_name) as $key => $value) {
            Config::setValue($key, $value);
        }
        ConfigPluginSet::reset();  // @phan-suppress-current-line PhanAccessMethodInternal
    }

    /**
     * @suppress PhanUndeclaredConstant
     */
    public function getTestFiles()
    {
        return $this->scanSourceFilesDir(TEST_FILE_DIR, EXPECTED_DIR);
    }
}
