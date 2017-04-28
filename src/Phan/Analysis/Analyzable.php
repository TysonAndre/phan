<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\BlockAnalysisVisitor;
use Phan\CodeBase;
use Phan\Config;
use Phan\Language\Context;
use ast\Node;
/**
 * Objects implementing this trait store a handle to
 * the AST node that defines them and allows us to
 * reanalyze them later on
 */
trait Analyzable
{
    /**
     * @var Node
     * The AST Node defining this object. We keep a
     * reference to this so that we can come to it
     * and
     */
    private $node = null;
    /**
     * @var int
     * The depth of recursion on this analyzable
     * object
     */
    private static $recursion_depth = 0;
    /**
     * @param Node $node
     * The AST Node defining this object. We keep a
     * reference to this so that we can come to it
     * and
     */
    public function setNode(Node $node)
    {
        // Don't waste the memory if we're in quick mode
        if (Config::get()->quick_mode) {
            return;
        }
        $this->node = $node;
    }
    /**
     * @return bool
     * True if we have a node defined on this object
     */
    public function hasNode()
    {
        $ret5902c6f180d07 = !empty($this->node);
        if (!is_bool($ret5902c6f180d07)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f180d07) . " given");
        }
        return $ret5902c6f180d07;
    }
    /**
     * @return Node
     * The AST node associated with this object
     */
    public function getNode()
    {
        $ret5902c6f181125 = $this->node;
        if (!$ret5902c6f181125 instanceof Node) {
            throw new \InvalidArgumentException("Argument returned must be of the type Node, " . (gettype($ret5902c6f181125) == "object" ? get_class($ret5902c6f181125) : gettype($ret5902c6f181125)) . " given");
        }
        return $ret5902c6f181125;
    }
    /**
     * @return Context
     * Analyze the node associated with this object
     * in the given context
     */
    public function analyze(Context $context, CodeBase $code_base)
    {
        // Don't do anything if we care about being
        // fast
        if (Config::get()->quick_mode) {
            $ret5902c6f18152b = $context;
            if (!$ret5902c6f18152b instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f18152b) == "object" ? get_class($ret5902c6f18152b) : gettype($ret5902c6f18152b)) . " given");
            }
            return $ret5902c6f18152b;
        }
        if (!$this->hasNode()) {
            $ret5902c6f18184d = $context;
            if (!$ret5902c6f18184d instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f18184d) == "object" ? get_class($ret5902c6f18184d) : gettype($ret5902c6f18184d)) . " given");
            }
            return $ret5902c6f18184d;
        }
        // Closures depend on the context surrounding them such
        // as for getting `use(...)` variables. Since we don't
        // have them, we can't re-analyze them until we change
        // that.
        //
        // TODO: Store the parent context on Analyzable objects
        if ($this->getNode()->kind === \ast\AST_CLOSURE) {
            $ret5902c6f181b5b = $context;
            if (!$ret5902c6f181b5b instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f181b5b) == "object" ? get_class($ret5902c6f181b5b) : gettype($ret5902c6f181b5b)) . " given");
            }
            return $ret5902c6f181b5b;
        }
        // Don't go deeper than one level in
        // TODO: Due to optimizations in checking for duplicate parameter lists, it should now be possible to increase this depth limit.
        if (self::$recursion_depth >= 2) {
            $ret5902c6f181e51 = $context;
            if (!$ret5902c6f181e51 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f181e51) == "object" ? get_class($ret5902c6f181e51) : gettype($ret5902c6f181e51)) . " given");
            }
            return $ret5902c6f181e51;
        }
        self::$recursion_depth++;
        try {
            $ret5902c6f18219c = (new BlockAnalysisVisitor($code_base, clone $context))($this->getNode());
            if (!$ret5902c6f18219c instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f18219c) == "object" ? get_class($ret5902c6f18219c) : gettype($ret5902c6f18219c)) . " given");
            }
            return $ret5902c6f18219c;
        } finally {
            self::$recursion_depth--;
        }
    }
    /**
     * Gets the recursion depth. Starts at 0, increases the deeper the recursion goes
     */
    public function getRecursionDepth()
    {
        $ret5902c6f1824bc = self::$recursion_depth;
        if (!is_int($ret5902c6f1824bc)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f1824bc) . " given");
        }
        return $ret5902c6f1824bc;
    }
}