<?php
declare(strict_types = 1);

namespace Phan\LanguageServer\Server;

use Phan\LanguageServer\{
    CompletionProvider, LanguageClient, PhpDocument, PhpDocumentLoader, DefinitionResolver
};
use Phan\LanguageServer\Index\ReadableIndex;
use Phan\LanguageServer\Logger;
use Phan\LanguageServer\Protocol\{
    FormattingOptions,
    Hover,
    Location,
    MarkedString,
    Position,
    Range,
    ReferenceContext,
    SymbolDescriptor,
    PackageDescriptor,
    SymbolLocationInformation,
    TextDocumentContentChangeEvent,
    TextDocumentIdentifier,
    TextDocumentItem,
    VersionedTextDocumentIdentifier
};
// planning to use TysonAndre/php-parser-to-php-ast for now, no equivalent exists for the error tolerant php parser
// use Microsoft\PhpParser\Node;
use Sabre\Event\Promise;
use Sabre\Uri;
use function Sabre\Event\coroutine;

/**
 * Provides method handlers for all textDocument/* methods
 * Source: https://github.com/felixfbecker/php-language-server/blob/master/src/Server/TextDocument.php
 */
class TextDocument
{
    /**
     * The language client object to call methods on the client
     *
     * @var LanguageClient
     */
    protected $client;

    /**
     * @param LanguageClient $client
     */
    public function __construct(
        LanguageClient $client
    ) {
        $this->client = $client;
    }


    /**
     * The document symbol request is sent from the client to the server to list all symbols found in a given text
     * document.
     * FIXME: reintroduce when support is added back
     *
     * @param TextDocumentIdentifier $textDocument
     * @return Promise <SymbolInformation[]>
     */
    /*
    public function documentSymbol(TextDocumentIdentifier $textDocument): Promise
    {
        return $this->documentLoader->getOrLoad($textDocument->uri)->then(function (PhpDocument $document) {
            $symbols = [];
            foreach ($document->getDefinitions() as $fqn => $definition) {
                $symbols[] = $definition->symbolInformation;
            }
            return $symbols;
        });
    }
     */

    /**
     * The document open notification is sent from the client to the server to signal newly opened text documents. The
     * document's truth is now managed by the client and the server must not try to read the document's truth using the
     * document's uri.
     *
     * @param TextDocumentItem $textDocument The document that was opened.
     * @return void
     */
    public function didOpen(TextDocumentItem $textDocument)
    {
        Logger::logInfo("Called didOpen, uri={$textDocument->uri}");

        //$document = $this->documentLoader->open($textDocument->uri, $textDocument->text);
        // TODO: make this trigger re-analysis
        // TODO: Check based on parse and analyze directories and Phan supported file extensions if this file affects Phan's analysis.
        // TODO:   Add functions to quickly check if a relative/absolute path is within the parse or analysis list of a project
        // TODO:   Maybe allow reloading .phan/config, at least the files and directories to parse/analyze

        // if (!isVendored($document, $this->composerJson)) {
        //     $this->client->textDocument->publishDiagnostics($textDocument->uri, $document->getDiagnostics());
        // }
    }

    /**
     * The document save notification is sent from the client to the server when the document was saved in the client.
     * TODO: Should this use willSave instead
     *
     * @param VersionedTextDocumentIdentifier $textDocument
     * @param string|null $text (NOTE: can't use ?T here)
     * @return void
     */
    public function didSave(TextDocumentIdentifier $textDocument, string $text = null) {
        Logger::logInfo("Called didSave, uri={$textDocument->uri} text=" . json_encode($text, JSON_UNESCAPED_SLASHES));
        LanguageServer::analyzeFile($textDocument, $text);

    }

    /**
     * The document change notification is sent from the client to the server to signal changes to a text document.
     *
     * @param VersionedTextDocumentIdentifier $textDocument
     * @param TextDocumentContentChangeEvent[] $contentChanges
     * @return void
     */
    public function didChange(VersionedTextDocumentIdentifier $textDocument, array $contentChanges)
    {
        Logger::logInfo("Called didChange");
        Logger::logInfo("Called didChange, uri={$textDocument->uri} version={$textDocument->version}");
        // TODO: Check based on parse and analyze directories and Phan supported file extensions if this file affects Phan's analysis.

        // TODO:   Add functions to quickly check if a relative/absolute path is within the parse or analysis list of a project
        // TODO:   Maybe allow reloading .phan/config, at least the files and directories to parse/analyze
        // TODO: make this trigger re-analysis
        //$document = $this->documentLoader->get($textDocument->uri);
        //$document->updateContent($contentChanges[0]->text);

        // XXX hook into getDiagnostics for Phan event publishing
        //$this->client->textDocument->publishDiagnostics($textDocument->uri, $document->getDiagnostics());
    }

    /**
     * The document close notification is sent from the client to the server when the document got closed in the client.
     * The document's truth now exists where the document's uri points to (e.g. if the document's uri is a file uri the
     * truth now exists on disk).
     *
     * @param TextDocumentIdentifier $textDocument The document that was closed
     * @return void
     */
    public function didClose(TextDocumentIdentifier $textDocument)
    {
        Logger::logInfo("Called didClose, uri={$textDocument->uri}");
        // FIXME implement
        // $this->documentLoader->close($textDocument->uri);
    }

    /**
     * The Completion request is sent from the client to the server to compute completion items at a given cursor
     * position. Completion items are presented in the IntelliSense user interface. If computing full completion items
     * is expensive, servers can additionally provide a handler for the completion item resolve request
     * ('completionItem/resolve'). This request is sent when a completion item is selected in the user interface. A
     * typically use case is for example: the 'textDocument/completion' request doesn't fill in the documentation
     * property for returned completion items since it is expensive to compute. When the item is selected in the user
     * interface then a 'completionItem/resolve' request is sent with the selected completion item as a param. The
     * returned completion item should have the documentation property filled in.
     *
     * @param TextDocumentIdentifier The text document
     * @param Position $position The position
     * @return Promise <CompletionItem[]|CompletionList>
     * FIXME: reintroduce this after support gets added to Phan
     */
    /*
        public function completion(TextDocumentIdentifier $textDocument, Position $position): Promise
        {
            return coroutine(function () use ($textDocument, $position) {
                $document = yield $this->documentLoader->getOrLoad($textDocument->uri);
                return $this->completionProvider->provideCompletion($document, $position);
            });
        }
     */

}
