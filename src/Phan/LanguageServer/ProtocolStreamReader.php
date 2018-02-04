<?php
declare(strict_types = 1);

namespace Phan\LanguageServer;

use Phan\LanguageServer\Logger;
use Phan\LanguageServer\Protocol\Message;
use AdvancedJsonRpc\Message as MessageBody;
use Sabre\Event\Loop;
use Sabre\Event\Emitter;
use Exception;

/**
 * Source: https://github.com/felixfbecker/php-language-server/tree/master/src/ProtocolStreamReader.php
 */
class ProtocolStreamReader extends Emitter implements ProtocolReader
{
    const PARSE_HEADERS = 1;
    const PARSE_BODY = 2;

    /** @var resource */
    private $input;
    /** @var int */
    private $parsingMode = self::PARSE_HEADERS;
    /** @var string */
    private $buffer = '';
    /** @var string[] */
    private $headers = [];
    /** @var int */
    private $contentLength;

    /**
     * @param resource $input
     */
    public function __construct($input)
    {
        $this->input = $input;

        $this->on('close', function () {
            Loop\removeReadStream($this->input);
        });

        Loop\addReadStream($this->input, function () {
            if (\feof($this->input)) {
                // If stream_select reported a status change for this stream,
                // but the stream is EOF, it means it was closed.
                $this->emit('close');
                return;
            }
            $c = '';
            $did_read = false;
            // It's possible that the language server is sending multiple change events before we can respond.
            // Do non-blocking reads to read all of the events, and let the implementation discard duplicates.
            // NOTE: In PHP, string concatenation is O(N) - It creates a brand new string instead of modifying the existing string.
            // And reading N bytes would be O(N^2)
            // So instead of appending single bytes, append large chunks.
            while (($c = \fread($this->input, 8000)) !== false && $c !== '') {
                $this->buffer .= $c;
                $did_read = true;
            }
            if (!$did_read) {
                return;
            }
            $msg_instances = [];
            // Process everything we have read (including incomplete input from before
            // When
            $offset = 0;
            $has_more_input = true;
            while ($has_more_input) {
                switch ($this->parsingMode) {
                    // Header line can either be:
                    // - An ascii string with a `key: value` ending in "\r\n"
                    //   One of the headers will be 'Content-Length'
                    // - "\r\n", marking that the body is beginning
                    case self::PARSE_HEADERS:
                        $newline_pos = \strpos($this->buffer, "\r\n", $offset);
                        if ($newline_pos === 0) {
                            $this->parsingMode = self::PARSE_BODY;
                            $this->contentLength = (int)$this->headers['Content-Length'];
                            $offset += 2;
                        } elseif ($newline_pos > 0) {
                            $parts = \explode(':', $this->buffer);
                            $this->headers[$parts[0]] = trim($parts[1]);
                            $offset += ($newline_pos + 2);
                        } else {
                            $has_more_input = false;
                        }
                        break;
                    // Body will consist of $this->contentLength bytes
                    case self::PARSE_BODY:
                        if (\strlen($this->buffer) - $offset >= $this->contentLength) {
                            Logger::logRequest($this->headers, $this->buffer);
                            // MessageBody::parse can throw an Error, maybe log an error?
                            try {
                                $msg = new Message(MessageBody::parse($this->buffer), $this->headers);
                                $msg_instances[] = $msg;
                            } catch (\Exception $e) {
                                $msg = null;
                            }

                            $this->parsingMode = self::PARSE_HEADERS;
                            $this->headers = [];
                            $offset += $this->contentLength;
                            $this->buffer = '';
                        } else {
                            $has_more_input = false;
                        }
                        break;
                }
            }
            // Discard the input that has already been processed.
            if ($offset > 0) {
                $this->buffer = \substr($this->buffer, $offset);
            }
            if (\count($msg_instances) > 0) {
                $this->emit('message', $msg_instances);
            }
        });
    }
}
