<?php declare(strict_types=1);
namespace Phan\Exception;

use ast\Node;
use Exception;

/**
 * Thrown to indicate that the given Node could not be analyzed.
 *
 * Rarely thrown for most valid code.
 * This can be thrown for code Phan doesn't know how to analyze
 * or for Nodes generated by the fallback parser for unparseable code.
 */
class NodeException extends Exception
{

    /**
     * @var Node
     */
    private $node;

    /**
     * @param Node $node
     * The node causing the exception
     *
     * @param string $message
     * The error message
     */
    public function __construct(
        Node $node,
        string $message = ''
    ) {
        parent::__construct($message);
        $this->node = $node;
    }

    /**
     * @return Node
     * The node for which we have an exception
     *
     * @suppress PhanUnreferencedPublicMethod
     */
    public function getNode() : Node
    {
        return $this->node;
    }
}
