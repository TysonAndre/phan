<?php
declare(strict_types = 1);

namespace Phan\LanguageServer;

use Phan\LanguageServer\Protocol\Message;
use AdvancedJsonRpc\Message as MessageBody;
use Sabre\Event\Emitter;
use Sabre\Event\Loop;

// FIXME disable logging by default
class Logger {
    /** @var resource|false */
    public static $file;

    /** @return void */
    public static function logRequest(array $headers, string $buffer) {
        self::logInfo(sprintf("Request:\n%s\nData:\n%s\n\n", json_encode($headers), $buffer));
    }

    /** @return void */
    public static function logResponse(array $headers, string $buffer) {
        self::logInfo(sprintf("Response:\n%s\nData:\n%s\n\n", json_encode($headers), $buffer));
    }

    /** @return void */
    public static function logInfo(string $msg) {
        $file = self::getLogFile();
        fwrite($file, $msg . "\n");
    }

    /**
     * @return resource
     */
    private static function getLogFile() {
        if (self::$file === null) {
            self::$file = fopen('/tmp/phan-language-server-logs', 'wa');
        }
        return self::$file;
    }

    /**
     * @param resource $newFile
     * @return void
     */
    public static function setLogFile($newFile) {
        assert(is_resource($newFile));
        if (is_resource(self::$file)) {
            if (self::$file === $newFile) {
                return;
            }
            fclose(self::$file);
            self::$file = false;
        }
        self::$file = $newFile;
    }
}
