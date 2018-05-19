<?php declare(strict_types = 1);
namespace Phan\Tests\LanguageServer;

use Phan\Tests\BaseTest;

/**
 * Test functionality of the Language Server
 */
class LanguageServerIntegrationTest extends BaseTest
{
    public function testInitialize()
    {
        if (getenv('PHAN_RUN_INTEGRATION_TEST') != '1') {
            $this->markTestSkipped('skipping integration tests - set PHAN_RUN_INTEGRATION_TEST=1 to allow');
        }
        if (!function_exists('pcntl_fork') || !function_exists('proc_open')) {
            $this->markTestSkipped('not supported');
        }
        // TODO: Move this into an OOP abstraction, add time limits, etc.
        $proc = proc_open(
            __DIR__ . '/../../../phan --quick --language-server-on-stdin',
            [['pipe', 'r'], ['pipe', 'w'], STDERR],
            $pipes
        );
        [$proc_in, $proc_out] = $pipes;
        $this->writeMessage($proc_in, 'initialize', ['blah' => 'blah']);
        $response = $this->awaitResponse($proc_out);
    }

    private function awaitResponse($proc_out) {
        // TODO: parse headers and body the same way the language client does
    }

    /**
     * @param resource $proc_in
     * @param string $method
     * @param array|\ArrayObject $params
     */
    private function writeMessage($proc_in, string $method, $params) {
        $body = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
        ];
        $body_raw = json_encode($body, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $raw = sprintf("Content-Length: %d\r\n\r\n%s\r\n", strlen($body_raw), $body_raw);
        fwrite($proc_in, $body_raw);
    }
    // TODO: Test the ability to create a Request
}
