<?php declare(strict_types = 1);
namespace Phan\Tests\LanguageServer;

use Phan\Tests\BaseTest;
use Phan\LanguageServer\LanguageServer;
use Phan\LanguageServer\Protocol\InitializeResult;
use Phan\LanguageServer\Protocol\ClientCapabilities;
use Phan\LanguageServer\Protocol\ServerCapabilities;
use Phan\LanguageServer\Protocol\TextDocumentSyncKind;

/**
 * Test functionality of the Language Server
 */
class LanguageServerTest extends BaseTest
{
    public function testInitialize() {
        $server = new LanguageServer(new MockProtocolStream, new MockProtocolStream);
        $result = $server->initialize(new ClientCapabilities, __DIR__, getmypid())->wait();

        $serverCapabilities = new ServerCapabilities();
        $serverCapabilities->textDocumentSync = TextDocumentSyncKind::FULL;

        $this->assertEquals(new InitializeResult($serverCapabilities), $result);
    }

    // TODO: Test the ability to create a Request
}
