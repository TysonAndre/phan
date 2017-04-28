<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\AST\ContextNode;
use Phan\CodeBase;
use Phan\Config;
use Phan\Language\Context;
use Phan\Language\UnionType;
use ast\Node;
class Variable extends TypedElement
{
    /**
     * @access private
     * @var string[] - Maps from a built in superglobal name to a UnionType spec string.
     */
    const _BUILTIN_SUPERGLOBAL_TYPES = ['argv' => 'string[]', 'argc' => 'int', '_GET' => 'string[]|string[][]', '_POST' => 'string[]|string[][]', '_COOKIE' => 'string[]|string[][]', '_REQUEST' => 'string[]|string[][]', '_SERVER' => 'array', '_ENV' => 'string[]', '_FILES' => 'int[][]|string[][]|int[][][]|string[][][]', '_SESSION' => 'array', 'GLOBALS' => 'array', 'http_response_header' => 'string[]|null'];
    /**
     * @param \phan\Context $context
     * The context in which the structural element lives
     *
     * @param string $name,
     * The name of the typed structural element
     *
     * @param UnionType $type,
     * A '|' delimited set of types satisfyped by this
     * typed structural element.
     *
     * @param int $flags,
     * The flags property contains node specific flags. It is
     * always defined, but for most nodes it is always zero.
     * ast\kind_uses_flags() can be used to determine whether
     * a certain kind has a meaningful flags value.
     */
    public function __construct(Context $context, $name, UnionType $type, $flags)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to __construct() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        parent::__construct($context, $name, $type, $flags);
    }
    /**
     * @return bool
     * This will always return false in so far as variables
     * cannot be passed by reference.
     */
    public function isPassByReference()
    {
        return false;
    }
    /**
     * @return bool
     * This will always return false in so far as variables
     * cannot be variadic
     */
    public function isVariadic()
    {
        return false;
    }
    /**
     * Stub for compatibility with Parameter, since we replace the Parameter with a Variable and call setParameterList in PostOrderAnalysisVisitor->visitStaticCall
     * TODO: Should that code create a new Parameter instance instead?
     * @return static
     */
    public function asNonVariadic()
    {
        return $this;
    }
    /**
     * @param Node $node
     * An AST_VAR node
     *
     * @param Context $context
     * The context in which the variable is found
     *
     * @param CodeBase $code_base
     *
     * @return Variable
     * A variable begotten from a node
     */
    public static function fromNodeInContext(Node $node, Context $context, CodeBase $code_base, $should_check_type = true)
    {
        if (!is_bool($should_check_type)) {
            throw new \InvalidArgumentException("Argument \$should_check_type passed to fromNodeInContext() must be of the type bool, " . (gettype($should_check_type) == "object" ? get_class($should_check_type) : gettype($should_check_type)) . " given");
        }
        $variable_name = (new ContextNode($code_base, $context, $node))->getVariableName();
        // Get the type of the assignment
        $union_type = $should_check_type ? UnionType::fromNode($context, $code_base, $node) : new UnionType();
        $variable = new Variable($context->withLineNumberStart(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->lineno, @0)), $variable_name, $union_type, call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0));
        $ret5902c6f6295da = $variable;
        if (!$ret5902c6f6295da instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6f6295da) == "object" ? get_class($ret5902c6f6295da) : gettype($ret5902c6f6295da)) . " given");
        }
        return $ret5902c6f6295da;
    }
    /**
     * @return bool
     * True if the variable with the given name is a
     * superglobal
     * Implies Variable::isHardcodedGlobalVariableWithName($name) is true
     */
    public static function isSuperglobalVariableWithName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to isSuperglobalVariableWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (array_key_exists($name, self::_BUILTIN_SUPERGLOBAL_TYPES)) {
            $ret5902c6f629c01 = true;
            if (!is_bool($ret5902c6f629c01)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f629c01) . " given");
            }
            return $ret5902c6f629c01;
        }
        $ret5902c6f629e86 = in_array($name, Config::get()->runkit_superglobals);
        if (!is_bool($ret5902c6f629e86)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f629e86) . " given");
        }
        return $ret5902c6f629e86;
    }
    /**
     * Returns true for all superglobals and variables in globals_type_map.
     */
    public static function isHardcodedGlobalVariableWithName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to isHardcodedGlobalVariableWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6f62a3d2 = self::isSuperglobalVariableWithName($name) || array_key_exists($name, Config::get()->globals_type_map);
        if (!is_bool($ret5902c6f62a3d2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f62a3d2) . " given");
        }
        return $ret5902c6f62a3d2;
    }
    /**
     * @return UnionType|null
     * Returns UnionType (Possible with empty set) if and only
     * if isHardcodedGlobalVariableWithName is true. Returns null
     * otherwise.
     */
    public static function getUnionTypeOfHardcodedGlobalVariableWithName($name, Context $context)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getUnionTypeOfHardcodedGlobalVariableWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (array_key_exists($name, self::_BUILTIN_SUPERGLOBAL_TYPES)) {
            // More efficient than using context.
            return UnionType::fromFullyQualifiedString(self::_BUILTIN_SUPERGLOBAL_TYPES[$name]);
        }
        if (array_key_exists($name, Config::get()->globals_type_map) || in_array($name, Config::get()->runkit_superglobals)) {
            $type_string = call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @Config::get()->globals_type_map[$name], @'');
            return UnionType::fromStringInContext($type_string, $context, false);
        }
        return null;
    }
    /**
     * Variables can't be variadic. This is the same as
     * getUnionType for variables, but not necessarily
     * for subclasses. Method will return the element
     * type (such as `DateTime`) for variadic parameters.
     */
    public function getNonVariadicUnionType()
    {
        $ret5902c6f62ad65 = parent::getUnionType();
        if (!$ret5902c6f62ad65 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f62ad65) == "object" ? get_class($ret5902c6f62ad65) : gettype($ret5902c6f62ad65)) . " given");
        }
        return $ret5902c6f62ad65;
    }
    /**
     * @return static - A clone of this object, where isVariadic() is false
     * Used for analyzing the context **inside** of this method
     */
    public function cloneAsNonVariadic()
    {
        return clone $this;
    }
    public function __toString()
    {
        $string = '';
        if (!$this->getUnionType()->isEmpty()) {
            $string .= "{$this->getUnionType()} ";
        }
        $ret5902c6f62b0fb = "{$string}\${$this->getName()}";
        if (!is_string($ret5902c6f62b0fb)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f62b0fb) . " given");
        }
        return $ret5902c6f62b0fb;
    }
}