<?php declare(strict_types=1);

namespace Phan\LanguageServer;

use Sabre\Event\Loop;
use Throwable;

class Utils {
    public static function crash(Throwable $err) {
        Loop\nextTick(function () use ($err) {
            throw $err;
        });
    }
}
