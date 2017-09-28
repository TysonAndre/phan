<?php

namespace Phan\LanguageServer\Protocol;

class TextDocumentIdentifier
{
    /**
     * The text document's URI.
     *
     * @var string|null
     */
    public $uri;

    /**
     * @param string|null $uri The text document's URI.
     */
    public function __construct(string $uri = null)
    {
        $this->uri = $uri;
    }
}
