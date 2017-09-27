<?php
declare(strict_types = 1);

namespace Phan\LanguageServer\Server;

use Phan\LanguageServer\LanguageClient;
use Phan\LanguageServer\LanguageServer;
use Phan\LanguageServer\Protocol\FileChangeType;
use Phan\LanguageServer\Protocol\FileEvent;

/**
 * Provides method handlers for all workspace/* methods
 */
class Workspace
{
    /**
     * @var LanguageClient
     */
    public $client;

    /**
     * @var LanguageServer
     */
    public $server;

    /**
     * @param LanguageClient    $client            LanguageClient instance used to signal updated results
     * FIXME: Rewrite to avoid static methods?
     */
    public function __construct(LanguageClient $client, LanguageServer $server)
    {
        $this->client = $client;
        $this->server = $server;
    }

    /**
     * The watched files notification is sent from the client to the server when the client detects changes to files watched by the language client.
     *
     * @param FileEvent[] $changes
     * @return void
     */
    public function didChangeWatchedFiles(array $changes)
    {
        // TODO invalidate Phan's cache for these files
        // TODO: convert file:///path/to/file to /path/to/file
        foreach ($changes as $change) {
            if ($change->type === FileChangeType::DELETED) {
                $this->client->textDocument->publishDiagnostics($change->uri, []);
            }
        }
        // TODO: more than one file
        foreach ($changes as $change) {
            if ($change->type === FileChangeType::CHANGED) {
                $this->server->analyzeFile($change->uri);
            }
        }
    }
}
