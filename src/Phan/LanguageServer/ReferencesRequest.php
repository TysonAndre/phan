<?php declare(strict_types=1);

namespace Phan\LanguageServer;

use Exception;
use Phan\Language\FileRef;
use Phan\LanguageServer\Protocol\Location;
use Phan\LanguageServer\Protocol\Position;

/**
 * Represents the Language Server Protocol's "Find References" request for a declaration of an Element
 * (class, property, function-like, constant, etc.)
 *
 * @see https://microsoft.github.io/language-server-protocol/specification#textDocument_references
 * @phan-file-suppress PhanPluginDescriptionlessCommentOnPublicMethod
 */
final class ReferencesRequest extends NodeInfoRequest
{
    /**
     * @var array<string,Location> the list of references for a "Find References" request
     */
    private $references = [];

    public function __construct(
        string $uri,
        Position $position
    ) {
        parent::__construct($uri, $position);
    }

    /**
     * @return void
     */
    public function recordUsage(FileRef $context)
    {
        $this->references[$context->__toString()] = Location::fromContext($context);
    }

    public function finalize()
    {
        $promise = $this->promise;
        if ($promise) {
            $promise->fulfill(array_values($this->references) ?: null);
        }
    }

    public function __destruct()
    {
        $promise = $this->promise;
        if ($promise) {
            $promise->reject(new Exception('Failed to send a valid textDocument/references result'));
            $this->promise = null;
        }
    }
}
