<?php declare(strict_types=1);
namespace Phan\Language\Element;

/**
 * This contains info for the source method of a trait alias.
 */
class TraitAliasSource
{
    /**
     * @var int line number where this trait method alias was created
     * (in the class using traits).
     */
    private $alias_lineno;

    /**
     * @var string source method name
     */
    private $source_method_name;

    public function __construct(string $source_method_name, int $alias_lineno) {
        $this->source_method_name = $source_method_name;
        $this->alias_lineno = $alias_lineno;
    }

    public function getSourceMethodName() : string {
        return $this->source_method_name;
    }

    public function getAliasLineno() : int {
        return $this->alias_lineno;
    }
}
