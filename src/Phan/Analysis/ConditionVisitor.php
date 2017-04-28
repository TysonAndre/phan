<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\AST\ContextNode;
use Phan\AST\Visitor\KindVisitorImplementation;
use Phan\CodeBase;
use Phan\Langauge\Type;
use Phan\Language\Context;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\NullType;
use Phan\Language\UnionType;
use ast\Node;
class ConditionVisitor extends KindVisitorImplementation
{
    /**
     * @var CodeBase
     */
    private $code_base;
    /**
     * @var Context
     * The context in which the node we're going to be looking
     * at exits.
     */
    private $context;
    /**
     * @param CodeBase $code_base
     * A code base needs to be passed in because we require
     * it to be initialized before any classes or files are
     * loaded.
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     */
    public function __construct(CodeBase $code_base, Context $context)
    {
        $this->code_base = $code_base;
        $this->context = $context;
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
        $ret5902c6f1ef226 = $this->context;
        if (!$ret5902c6f1ef226 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1ef226) == "object" ? get_class($ret5902c6f1ef226) : gettype($ret5902c6f1ef226)) . " given");
        }
        return $ret5902c6f1ef226;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitBinaryOp(Node $node)
    {
        $flags = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0);
        if ($flags === \ast\flags\BINARY_BOOL_AND) {
            $ret5902c6f1ef7ae = $this->visitShortCircuitingAnd($node->children['left'], $node->children['right']);
            if (!$ret5902c6f1ef7ae instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1ef7ae) == "object" ? get_class($ret5902c6f1ef7ae) : gettype($ret5902c6f1ef7ae)) . " given");
            }
            return $ret5902c6f1ef7ae;
        }
        $ret5902c6f1efa88 = $this->context;
        if (!$ret5902c6f1efa88 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1efa88) == "object" ? get_class($ret5902c6f1efa88) : gettype($ret5902c6f1efa88)) . " given");
        }
        return $ret5902c6f1efa88;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitAnd(Node $node)
    {
        $ret5902c6f1efe54 = $this->visitShortCircuitingAnd($node->children['left'], $node->children['right']);
        if (!$ret5902c6f1efe54 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1efe54) == "object" ? get_class($ret5902c6f1efe54) : gettype($ret5902c6f1efe54)) . " given");
        }
        return $ret5902c6f1efe54;
    }
    /**
     * Helper method
     * @param Node|mixed $left
     * a Node or non-node to parse (possibly an AST literal)
     *
     * @param Node|mixed $right
     * a Node or non-node to parse (possibly an AST literal)
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    private function visitShortCircuitingAnd($left, $right)
    {
        // Aside: If left/right is not a node, left/right is a literal such as a number/string, and is either always truthy or always falsey.
        // Inside of this conditional may be dead or redundant code.
        if ($left instanceof Node) {
            $this->context = $this($left);
        }
        if ($right instanceof Node) {
            $ret5902c6f1f01df = $this($right);
            if (!$ret5902c6f1f01df instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f01df) == "object" ? get_class($ret5902c6f1f01df) : gettype($ret5902c6f1f01df)) . " given");
            }
            return $ret5902c6f1f01df;
        }
        $ret5902c6f1f04a6 = $this->context;
        if (!$ret5902c6f1f04a6 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f04a6) == "object" ? get_class($ret5902c6f1f04a6) : gettype($ret5902c6f1f04a6)) . " given");
        }
        return $ret5902c6f1f04a6;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitUnaryOp(Node $node)
    {
        if (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0) !== \ast\flags\UNARY_BOOL_NOT) {
            $ret5902c6f1f07eb = $this->context;
            if (!$ret5902c6f1f07eb instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f07eb) == "object" ? get_class($ret5902c6f1f07eb) : gettype($ret5902c6f1f07eb)) . " given");
            }
            return $ret5902c6f1f07eb;
        }
        $ret5902c6f1f0afb = $this->updateContextWithNegation($node->children['expr'], $this->context);
        if (!$ret5902c6f1f0afb instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f0afb) == "object" ? get_class($ret5902c6f1f0afb) : gettype($ret5902c6f1f0afb)) . " given");
        }
        return $ret5902c6f1f0afb;
    }
    private function updateContextWithNegation(Node $negatedNode, Context $context)
    {
        // Negation
        // TODO: negate instanceof, other checks
        // TODO: negation would also go in the else statement
        if (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$negatedNode->kind, @0) === \ast\AST_CALL) {
            if (self::isCallStringWithSingleVariableArgument($negatedNode)) {
                // TODO: Make this generic to all type assertions? E.g. if (!is_string($x)) removes 'string' from type, makes '?string' (nullable) into 'null'.
                // This may be redundant in some places if AST canonicalization is used, but still useful in some places
                // TODO: Make this generic so that it can be used in the 'else' branches?
                $function_name = $negatedNode->children['expr']->children['name'];
                if (in_array($function_name, ['empty', 'is_null', 'is_scalar'], true)) {
                    $ret5902c6f1f100f = $this->removeNullFromVariable($negatedNode->children['args']->children[0], $context);
                    if (!$ret5902c6f1f100f instanceof Context) {
                        throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f100f) == "object" ? get_class($ret5902c6f1f100f) : gettype($ret5902c6f1f100f)) . " given");
                    }
                    return $ret5902c6f1f100f;
                }
            }
        }
        $ret5902c6f1f12da = $context;
        if (!$ret5902c6f1f12da instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f12da) == "object" ? get_class($ret5902c6f1f12da) : gettype($ret5902c6f1f12da)) . " given");
        }
        return $ret5902c6f1f12da;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitCoalesce(Node $node)
    {
        $ret5902c6f1f15cb = $this->context;
        if (!$ret5902c6f1f15cb instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f15cb) == "object" ? get_class($ret5902c6f1f15cb) : gettype($ret5902c6f1f15cb)) . " given");
        }
        return $ret5902c6f1f15cb;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitIsset(Node $node)
    {
        if ($node->children['var']->kind !== \ast\AST_VAR) {
            $ret5902c6f1f190d = $this->context;
            if (!$ret5902c6f1f190d instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f190d) == "object" ? get_class($ret5902c6f1f190d) : gettype($ret5902c6f1f190d)) . " given");
            }
            return $ret5902c6f1f190d;
        }
        $ret5902c6f1f1c22 = $this->removeNullFromVariable($node->children['var'], $this->context);
        if (!$ret5902c6f1f1c22 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f1c22) == "object" ? get_class($ret5902c6f1f1c22) : gettype($ret5902c6f1f1c22)) . " given");
        }
        return $ret5902c6f1f1c22;
    }
    /**
     * @param Node $node
     * A node to parse, with kind \ast\AST_VAR
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitVar(Node $node)
    {
        $ret5902c6f1f1f7a = $this->removeNullFromVariable($node, $this->context);
        if (!$ret5902c6f1f1f7a instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f1f7a) == "object" ? get_class($ret5902c6f1f1f7a) : gettype($ret5902c6f1f1f7a)) . " given");
        }
        return $ret5902c6f1f1f7a;
    }
    private function removeNullFromVariable(Node $varNode, Context $context)
    {
        try {
            // Get the variable we're operating on
            $variable = (new ContextNode($this->code_base, $context, $varNode))->getVariable();
            if (!$variable->getUnionType()->containsNullable()) {
                $ret5902c6f1f2326 = $context;
                if (!$ret5902c6f1f2326 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f2326) == "object" ? get_class($ret5902c6f1f2326) : gettype($ret5902c6f1f2326)) . " given");
                }
                return $ret5902c6f1f2326;
            }
            // Make a copy of the variable
            $variable = clone $variable;
            $variable->setUnionType($variable->getUnionType()->nonNullableClone());
            // Overwrite the variable with its new type in this
            // scope without overwriting other scopes
            $context = $context->withScopeVariable($variable);
        } catch (\Exception $exception) {
            // Swallow it
        }
        $ret5902c6f1f269a = $context;
        if (!$ret5902c6f1f269a instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f269a) == "object" ? get_class($ret5902c6f1f269a) : gettype($ret5902c6f1f269a)) . " given");
        }
        return $ret5902c6f1f269a;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitInstanceof(Node $node)
    {
        // Only look at things of the form
        // `$variable instanceof ClassName`
        if ($node->children['expr']->kind !== \ast\AST_VAR) {
            $ret5902c6f1f29ec = $this->context;
            if (!$ret5902c6f1f29ec instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f29ec) == "object" ? get_class($ret5902c6f1f29ec) : gettype($ret5902c6f1f29ec)) . " given");
            }
            return $ret5902c6f1f29ec;
        }
        $context = $this->context;
        try {
            // Get the variable we're operating on
            $variable = (new ContextNode($this->code_base, $this->context, $node->children['expr']))->getVariable();
            // Get the type that we're checking it against
            $type = UnionType::fromNode($this->context, $this->code_base, $node->children['class']);
            // Make a copy of the variable
            $variable = clone $variable;
            // Add the type to the variable
            // $variable->getUnionType()->addUnionType($type);
            $variable->setUnionType($type);
            // Overwrite the variable with its new type
            $context = $context->withScopeVariable($variable);
        } catch (\Exception $exception) {
            // Swallow it
        }
        $ret5902c6f1f2ea0 = $context;
        if (!$ret5902c6f1f2ea0 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f2ea0) == "object" ? get_class($ret5902c6f1f2ea0) : gettype($ret5902c6f1f2ea0)) . " given");
        }
        return $ret5902c6f1f2ea0;
    }
    private static function isCallStringWithSingleVariableArgument(Node $node)
    {
        $args = $node->children['args']->children;
        $ret5902c6f1f3367 = count($args) === 1 && $args[0] instanceof Node && $args[0]->kind === \ast\AST_VAR && $node->children['expr'] instanceof Node && !empty(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['expr']->children['name'], @null)) && is_string($node->children['expr']->children['name']);
        if (!is_bool($ret5902c6f1f3367)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f1f3367) . " given");
        }
        return $ret5902c6f1f3367;
    }
    /**
     * Look at elements of the form `is_array($v)` and modify
     * the type of the variable.
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitCall(Node $node)
    {
        // Only look at things of the form
        // `is_string($variable)`
        if (!self::isCallStringWithSingleVariableArgument($node)) {
            $ret5902c6f1f3622 = $this->context;
            if (!$ret5902c6f1f3622 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f3622) == "object" ? get_class($ret5902c6f1f3622) : gettype($ret5902c6f1f3622)) . " given");
            }
            return $ret5902c6f1f3622;
        }
        // Translate the function name into the UnionType it asserts
        $map = array('is_array' => 'array', 'is_bool' => 'bool', 'is_callable' => 'callable', 'is_double' => 'float', 'is_float' => 'float', 'is_int' => 'int', 'is_integer' => 'int', 'is_long' => 'int', 'is_null' => 'null', 'is_numeric' => 'string|int|float', 'is_object' => 'object', 'is_real' => 'float', 'is_resource' => 'resource', 'is_scalar' => 'int|float|bool|string|null', 'is_string' => 'string', 'empty' => 'null');
        $function_name = $node->children['expr']->children['name'];
        if (!isset($map[$function_name])) {
            $ret5902c6f1f3b07 = $this->context;
            if (!$ret5902c6f1f3b07 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f3b07) == "object" ? get_class($ret5902c6f1f3b07) : gettype($ret5902c6f1f3b07)) . " given");
            }
            return $ret5902c6f1f3b07;
        }
        $type = UnionType::fromFullyQualifiedString($map[$function_name]);
        $context = $this->context;
        try {
            // Get the variable we're operating on
            $variable = (new ContextNode($this->code_base, $this->context, $node->children['args']->children[0]))->getVariable();
            if ($variable->getUnionType()->isEmpty()) {
                $variable->getUnionType()->addType(NullType::instance(false));
            }
            // Make a copy of the variable
            $variable = clone $variable;
            $variable->setUnionType(clone $variable->getUnionType());
            // Change the type to match the is_a relationship
            if ($type->isType(ArrayType::instance(false)) && $variable->getUnionType()->hasGenericArray()) {
                // If the variable is already a generic array,
                // note that it can be an arbitrary array without
                // erasing the existing generic type.
                $variable->getUnionType()->addUnionType($type);
            } else {
                // Otherwise, overwrite the type for any simple
                // primitive types.
                $variable->setUnionType($type);
            }
            // Overwrite the variable with its new type in this
            // scope without overwriting other scopes
            $context = $context->withScopeVariable($variable);
        } catch (\Exception $exception) {
            // Swallow it
        }
        $ret5902c6f1f40ae = $context;
        if (!$ret5902c6f1f40ae instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1f40ae) == "object" ? get_class($ret5902c6f1f40ae) : gettype($ret5902c6f1f40ae)) . " given");
        }
        return $ret5902c6f1f40ae;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitEmpty(Node $node)
    {
        $ret5902c6f2001a8 = $this->context;
        if (!$ret5902c6f2001a8 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f2001a8) == "object" ? get_class($ret5902c6f2001a8) : gettype($ret5902c6f2001a8)) . " given");
        }
        return $ret5902c6f2001a8;
    }
}