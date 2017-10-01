<?php
declare(strict_types = 1);

namespace Phan\LanguageServer;

/**
 * Generates unique, incremental IDs for use as request IDs
 *
 * Source: https://github.com/felixfbecker/php-language-server/tree/master/src/IdGenerator.php
 * See ../LICENSE.txt
 */
class IdGenerator
{
    /**
     * @var int
     */
    public $counter = 1;

    /**
     * Returns a unique ID
     *
     * @return int
     */
    public function generate()
    {
        return $this->counter++;
    }
}
