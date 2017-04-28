<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\AST\AnalysisVisitor;
use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedGlobalConstantName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use ast\Node;
abstract class ScopeVisitor extends AnalysisVisitor
{
    /**
     * @param CodeBase $code_base
     * The global code base holding all state
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     */
    public function __construct(CodeBase $code_base, Context $context)
    {
        parent::__construct($code_base, $context);
    }
    /**
     * Default visitor for node kinds that do not have
     * an overriding method
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visit(Node $node)
    {
        $ret5902c6f2bdb8b = $this->context;
        if (!$ret5902c6f2bdb8b instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2bdb8b) == "object" ? get_class($ret5902c6f2bdb8b) : gettype($ret5902c6f2bdb8b)) . " given");
        }
        return $ret5902c6f2bdb8b;
    }
    /**
     * Visit a node with kind `\ast\AST_DECLARE`
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitDeclare(Node $node)
    {
        $declares = $node->children['declares'];
        $name = $declares->children[0]->children['name'];
        $value = $declares->children[0]->children['value'];
        if ('strict_types' === $name) {
            $ret5902c6f2be12e = $this->context->withStrictTypes($value);
            if (!$ret5902c6f2be12e instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2be12e) == "object" ? get_class($ret5902c6f2be12e) : gettype($ret5902c6f2be12e)) . " given");
            }
            return $ret5902c6f2be12e;
        }
        $ret5902c6f2be3fa = $this->context;
        if (!$ret5902c6f2be3fa instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2be3fa) == "object" ? get_class($ret5902c6f2be3fa) : gettype($ret5902c6f2be3fa)) . " given");
        }
        return $ret5902c6f2be3fa;
    }
    /**
     * Visit a node with kind `\ast\AST_NAMESPACE`
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitNamespace(Node $node)
    {
        $namespace = '\\' . (string) $node->children['name'];
        $ret5902c6f2be755 = $this->context->withNamespace($namespace);
        if (!$ret5902c6f2be755 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2be755) == "object" ? get_class($ret5902c6f2be755) : gettype($ret5902c6f2be755)) . " given");
        }
        return $ret5902c6f2be755;
    }
    /**
     * Visit a node with kind `\ast\AST_GROUP_USE`
     * such as `use \ast\Node;`.
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitGroupUse(Node $node)
    {
        $children = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]);
        $prefix = array_shift($children);
        $context = $this->context;
        foreach ($this->aliasTargetMapFromUseNode($children['uses'], $prefix, call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0)) as $alias => $map) {
            list($flags, $target) = $map;
            $context = $context->withNamespaceMap($flags, $alias, $target);
        }
        $ret5902c6f2becbb = $context;
        if (!$ret5902c6f2becbb instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2becbb) == "object" ? get_class($ret5902c6f2becbb) : gettype($ret5902c6f2becbb)) . " given");
        }
        return $ret5902c6f2becbb;
    }
    /**
     * Visit a node with kind `\ast\AST_USE`
     * such as `use \ast\Node;`.
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitUse(Node $node)
    {
        $context = $this->context;
        foreach ($this->aliasTargetMapFromUseNode($node) as $alias => $map) {
            list($flags, $target) = $map;
            $context = $context->withNamespaceMap(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->flags, @0), $alias, $target);
        }
        $ret5902c6f2bf179 = $context;
        if (!$ret5902c6f2bf179 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2bf179) == "object" ? get_class($ret5902c6f2bf179) : gettype($ret5902c6f2bf179)) . " given");
        }
        return $ret5902c6f2bf179;
    }
    /**
     * @param Node $node
     * The node with the use statement
     *
     * @param int $flags
     * An optional node flag specifying the type
     * of the use clause.
     *
     * @return array
     * A map from alias to target
     */
    private function aliasTargetMapFromUseNode(Node $node, $prefix = '', $flags = 0)
    {
        if (!is_string($prefix)) {
            throw new \InvalidArgumentException("Argument \$prefix passed to aliasTargetMapFromUseNode() must be of the type string, " . (gettype($prefix) == "object" ? get_class($prefix) : gettype($prefix)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to aliasTargetMapFromUseNode() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        assert($node->kind == \ast\AST_USE, 'Method takes AST_USE nodes');
        $map = [];
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            $target = $child_node->children['name'];
            if (empty($child_node->children['alias'])) {
                if (($pos = strrpos($target, '\\')) !== false) {
                    $alias = substr($target, $pos + 1);
                } else {
                    $alias = $target;
                }
            } else {
                $alias = $child_node->children['alias'];
            }
            // if AST_USE does not have any flags set, then its AST_USE_ELEM
            // children will (this will be for AST_GROUP_USE)
            // The 'use' type can be defined on the `AST_GROUP_USE` node, the
            // `AST_USE_ELEM` or on the child element.
            $use_flag = $flags ?: $node->flags !== 0 ? $node->flags : $child_node->flags;
            if ($use_flag === \ast\flags\USE_FUNCTION) {
                $parts = explode('\\', $target);
                $function_name = array_pop($parts);
                $target = FullyQualifiedFunctionName::make($prefix . '\\' . implode('\\', $parts), $function_name);
            } else {
                if ($use_flag === \ast\flags\USE_CONST) {
                    $parts = explode('\\', $target);
                    $name = array_pop($parts);
                    $target = FullyQualifiedGlobalConstantName::make($prefix . '\\' . implode('\\', $parts), $name);
                } else {
                    if ($use_flag === \ast\flags\USE_NORMAL) {
                        $target = FullyQualifiedClassName::fromFullyQualifiedString($prefix . '\\' . $target);
                    } else {
                        // If we get to this spot and don't know what
                        // kind of a use clause we're dealing with, its
                        // likely that this is a `USE` node which is
                        // a child of a `GROUP_USE` and we already
                        // handled it when analyzing the parent
                        // node.
                        continue;
                    }
                }
            }
            $map[$alias] = [$use_flag, $target];
        }
        $ret5902c6f2bfa57 = $map;
        if (!is_array($ret5902c6f2bfa57)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f2bfa57) . " given");
        }
        return $ret5902c6f2bfa57;
    }
}