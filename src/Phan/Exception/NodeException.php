<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Exception;

use ast\Node;
class NodeException extends \Exception
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
    public function __construct(Node $node, $message = '')
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException("Argument \$message passed to __construct() must be of the type string, " . (gettype($message) == "object" ? get_class($message) : gettype($message)) . " given");
        }
        parent::__construct($message);
        $this->node = $node;
    }
    /**
     * @return Node
     * The node for which we have an exception
     *
     * @suppress PhanUnreferencedMethod
     */
    public function getNode()
    {
        $ret5902c6f447b9b = $this->node;
        if (!$ret5902c6f447b9b instanceof Node) {
            throw new \InvalidArgumentException("Argument returned must be of the type Node, " . (gettype($ret5902c6f447b9b) == "object" ? get_class($ret5902c6f447b9b) : gettype($ret5902c6f447b9b)) . " given");
        }
        return $ret5902c6f447b9b;
    }
}