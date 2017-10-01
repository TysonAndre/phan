<?php
declare(strict_types = 1);

namespace Phan\LanguageServer;

use JsonMapper;

/**
 * Source: https://github.com/felixfbecker/php-language-server/tree/master/src/LanguageClient.php
 * See ./LICENSE.txt
 */
class LanguageClient
{
    /**
     * Handles textDocument/* methods
     *
     * @var Client\TextDocument
     */
    public $textDocument;

    /**
     * Handles window/* methods
     *
     * @var Client\Window
     */
    //public $window;

    /**
     * Handles workspace/* methods
     *
     * @var Client\Workspace
     */
    //public $workspace;

    /**
     * Handles xcache/* methods
     *
     * @var Client\XCache
     */
    //public $xcache;

    /**
     *
     */
    public function __construct(ProtocolReader $reader, ProtocolWriter $writer)
    {
        $handler = new ClientHandler($reader, $writer);
        $mapper = new JsonMapper;

        $this->textDocument = new Client\TextDocument($handler, $mapper);
        //$this->window = new Client\Window($handler);
        //$this->workspace = new Client\Workspace($handler, $mapper);
        //$this->xcache = new Client\XCache($handler);
    }
}
