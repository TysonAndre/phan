<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

use ast\Node;
use ast\Node\Decl;
/**
 * Debug utilities
 */
class Debug
{
    /**
     * Print a lil' something to the console to
     * see if a thing is called
     *
     * @suppress PhanUnreferencedMethod
     */
    public static function mark()
    {
        print "mark\n";
    }
    /**
     * @param string|Node|null $node
     * An AST node
     *
     * Print an AST node
     *
     * @return void
     *
     * @suppress PhanUnreferencedMethod
     */
    public static function printNode($node)
    {
        print self::nodeToString($node);
    }
    /**
     * Print the name of a node to the terminal
     *
     * @suppress PhanUnreferencedMethod
     */
    public static function printNodeName($node, $indent = 0)
    {
        print str_repeat("\t", $indent);
        print self::nodeName($node);
        print "\n";
    }
    /**
     * Print a thing with the given indent level
     *
     * @suppress PhanUnreferencedMethod
     */
    public static function print_($message, $indent = 0)
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException("Argument \$message passed to print() must be of the type string, " . (gettype($message) == "object" ? get_class($message) : gettype($message)) . " given");
        }
        if (!is_int($indent)) {
            throw new \InvalidArgumentException("Argument \$indent passed to print() must be of the type int, " . (gettype($indent) == "object" ? get_class($indent) : gettype($indent)) . " given");
        }
        print str_repeat("\t", $indent);
        print $message . "\n";
    }
    /**
     * @return string
     * The name of the node
     */
    public static function nodeName($node)
    {
        if (is_string($node)) {
            $ret5902c6f42711a = 'string';
            if (!is_string($ret5902c6f42711a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f42711a) . " given");
            }
            return $ret5902c6f42711a;
        }
        if (!$node) {
            $ret5902c6f42737a = 'null';
            if (!is_string($ret5902c6f42737a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f42737a) . " given");
            }
            return $ret5902c6f42737a;
        }
        $ret5902c6f4275ea = \ast\get_kind_name($node->kind);
        if (!is_string($ret5902c6f4275ea)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f4275ea) . " given");
        }
        return $ret5902c6f4275ea;
    }
    /**
     * @param string|Node|null $node
     * An AST node
     *
     * @param int $indent
     * The indentation level for the string
     *
     * @return string
     * A string representation of an AST node
     */
    public static function nodeToString($node, $name = null, $indent = 0)
    {
        if (!is_int($indent)) {
            throw new \InvalidArgumentException("Argument \$indent passed to nodeToString() must be of the type int, " . (gettype($indent) == "object" ? get_class($indent) : gettype($indent)) . " given");
        }
        $string = str_repeat("\t", $indent);
        if ($name !== null) {
            $string .= "{$name} => ";
        }
        if (is_string($node)) {
            $ret5902c6f427980 = $string . $node . "\n";
            if (!is_string($ret5902c6f427980)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f427980) . " given");
            }
            return $ret5902c6f427980;
        }
        if (!$node) {
            $ret5902c6f427c3f = $string . 'null' . "\n";
            if (!is_string($ret5902c6f427c3f)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f427c3f) . " given");
            }
            return $ret5902c6f427c3f;
        }
        if (!is_object($node)) {
            $ret5902c6f427edb = $string . $node . "\n";
            if (!is_string($ret5902c6f427edb)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f427edb) . " given");
            }
            return $ret5902c6f427edb;
        }
        $string .= \ast\get_kind_name($node->kind);
        $string .= ' [' . self::astFlagDescription(call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->flags, @0)) . ']';
        if (isset($node->lineno)) {
            $string .= ' #' . $node->lineno;
        }
        if ($node instanceof Decl) {
            if (isset($node->endLineno)) {
                $string .= ':' . $node->endLineno;
            }
        }
        if (isset($node->name)) {
            $string .= ' name:' . $node->name;
        }
        $string .= "\n";
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $name => $child_node) {
            $string .= self::nodeToString($child_node, $name, $indent + 1);
        }
        $ret5902c6f428458 = $string;
        if (!is_string($ret5902c6f428458)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f428458) . " given");
        }
        return $ret5902c6f428458;
    }
    /**
     * @return string
     * Get a string representation of AST node flags such as
     * 'ASSIGN_DIV|TYPE_ARRAY'
     */
    public static function astFlagDescription($flag)
    {
        if (!is_int($flag)) {
            throw new \InvalidArgumentException("Argument \$flag passed to astFlagDescription() must be of the type int, " . (gettype($flag) == "object" ? get_class($flag) : gettype($flag)) . " given");
        }
        $flag_names = [];
        foreach (self::$AST_FLAG_ID_NAME_MAP as $id => $name) {
            if ($flag == $id) {
                $flag_names[] = $name;
            }
        }
        $ret5902c6f4289d4 = implode('|', $flag_names);
        if (!is_string($ret5902c6f4289d4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f4289d4) . " given");
        }
        return $ret5902c6f4289d4;
    }
    /**
     * @return void
     * Pretty-printer for debug_backtrace
     *
     * @suppress PhanUnreferencedMethod
     */
    public static function backtrace($levels = 0)
    {
        if (!is_int($levels)) {
            throw new \InvalidArgumentException("Argument \$levels passed to backtrace() must be of the type int, " . (gettype($levels) == "object" ? get_class($levels) : gettype($levels)) . " given");
        }
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $levels + 1);
        foreach ($bt as $level => $context) {
            if (!$level) {
                continue;
            }
            echo "#" . ($level - 1) . " {$context['file']}:{$context['line']} {$context['class']} ";
            if (!empty($context['type'])) {
                echo $context['class'] . $context['type'];
            }
            echo $context['function'];
            echo "\n";
        }
    }
    /**
     * Note that flag IDs are not unique. You're likely going to get
     * an incorrect name back from this. So sorry.
     *
     * @suppress PhanUnreferencedProperty
     */
    private static $AST_FLAG_ID_NAME_MAP = [\ast\flags\ASSIGN_ADD => 'ASSIGN_ADD', \ast\flags\ASSIGN_BITWISE_AND => 'ASSIGN_BITWISE_AND', \ast\flags\ASSIGN_BITWISE_OR => 'ASSIGN_BITWISE_OR', \ast\flags\ASSIGN_BITWISE_XOR => 'ASSIGN_BITWISE_XOR', \ast\flags\ASSIGN_CONCAT => 'ASSIGN_CONCAT', \ast\flags\ASSIGN_DIV => 'ASSIGN_DIV', \ast\flags\ASSIGN_MOD => 'ASSIGN_MOD', \ast\flags\ASSIGN_MUL => 'ASSIGN_MUL', \ast\flags\ASSIGN_POW => 'ASSIGN_POW', \ast\flags\ASSIGN_SHIFT_LEFT => 'ASSIGN_SHIFT_LEFT', \ast\flags\ASSIGN_SHIFT_RIGHT => 'ASSIGN_SHIFT_RIGHT', \ast\flags\ASSIGN_SUB => 'ASSIGN_SUB', \ast\flags\BINARY_ADD => 'BINARY_ADD', \ast\flags\BINARY_BITWISE_AND => 'BINARY_BITWISE_AND', \ast\flags\BINARY_BITWISE_OR => 'BINARY_BITWISE_OR', \ast\flags\BINARY_BITWISE_XOR => 'BINARY_BITWISE_XOR', \ast\flags\BINARY_BOOL_XOR => 'BINARY_BOOL_XOR', \ast\flags\BINARY_CONCAT => 'BINARY_CONCAT', \ast\flags\BINARY_DIV => 'BINARY_DIV', \ast\flags\BINARY_IS_EQUAL => 'BINARY_IS_EQUAL', \ast\flags\BINARY_IS_IDENTICAL => 'BINARY_IS_IDENTICAL', \ast\flags\BINARY_IS_NOT_EQUAL => 'BINARY_IS_NOT_EQUAL', \ast\flags\BINARY_IS_NOT_IDENTICAL => 'BINARY_IS_NOT_IDENTICAL', \ast\flags\BINARY_IS_SMALLER => 'BINARY_IS_SMALLER', \ast\flags\BINARY_IS_SMALLER_OR_EQUAL => 'BINARY_IS_SMALLER_OR_EQUAL', \ast\flags\BINARY_MOD => 'BINARY_MOD', \ast\flags\BINARY_MUL => 'BINARY_MUL', \ast\flags\BINARY_POW => 'BINARY_POW', \ast\flags\BINARY_SHIFT_LEFT => 'BINARY_SHIFT_LEFT', \ast\flags\BINARY_SHIFT_RIGHT => 'BINARY_SHIFT_RIGHT', \ast\flags\BINARY_SPACESHIP => 'BINARY_SPACESHIP', \ast\flags\BINARY_SUB => 'BINARY_SUB', \ast\flags\CLASS_ABSTRACT => 'CLASS_ABSTRACT', \ast\flags\CLASS_FINAL => 'CLASS_FINAL', \ast\flags\CLASS_INTERFACE => 'CLASS_INTERFACE', \ast\flags\CLASS_TRAIT => 'CLASS_TRAIT', \ast\flags\MODIFIER_ABSTRACT => 'MODIFIER_ABSTRACT', \ast\flags\MODIFIER_FINAL => 'MODIFIER_FINAL', \ast\flags\MODIFIER_PRIVATE => 'MODIFIER_PRIVATE', \ast\flags\MODIFIER_PROTECTED => 'MODIFIER_PROTECTED', \ast\flags\MODIFIER_PUBLIC => 'MODIFIER_PUBLIC', \ast\flags\MODIFIER_STATIC => 'MODIFIER_STATIC', \ast\flags\NAME_FQ => 'NAME_FQ', \ast\flags\NAME_NOT_FQ => 'NAME_NOT_FQ', \ast\flags\NAME_RELATIVE => 'NAME_RELATIVE', \ast\flags\PARAM_REF => 'PARAM_REF', \ast\flags\PARAM_VARIADIC => 'PARAM_VARIADIC', \ast\flags\RETURNS_REF => 'RETURNS_REF', \ast\flags\TYPE_ARRAY => 'TYPE_ARRAY', \ast\flags\TYPE_BOOL => 'TYPE_BOOL', \ast\flags\TYPE_CALLABLE => 'TYPE_CALLABLE', \ast\flags\TYPE_DOUBLE => 'TYPE_DOUBLE', \ast\flags\TYPE_LONG => 'TYPE_LONG', \ast\flags\TYPE_NULL => 'TYPE_NULL', \ast\flags\TYPE_OBJECT => 'TYPE_OBJECT', \ast\flags\TYPE_STRING => 'TYPE_STRING', \ast\flags\UNARY_BITWISE_NOT => 'UNARY_BITWISE_NOT', \ast\flags\UNARY_BOOL_NOT => 'UNARY_BOOL_NOT', \ast\flags\BINARY_BOOL_AND => 'BINARY_BOOL_AND', \ast\flags\BINARY_BOOL_OR => 'BINARY_BOOL_OR', \ast\flags\BINARY_IS_GREATER => 'BINARY_IS_GREATER', \ast\flags\BINARY_IS_GREATER_OR_EQUAL => 'BINARY_IS_GREATER_OR_EQUAL', \ast\flags\CLASS_ANONYMOUS => 'CLASS_ANONYMOUS', \ast\flags\EXEC_EVAL => 'EXEC_EVAL', \ast\flags\EXEC_INCLUDE => 'EXEC_INCLUDE', \ast\flags\EXEC_INCLUDE_ONCE => 'EXEC_INCLUDE_ONCE', \ast\flags\EXEC_REQUIRE => 'EXEC_REQUIRE', \ast\flags\EXEC_REQUIRE_ONCE => 'EXEC_REQUIRE_ONCE', \ast\flags\MAGIC_CLASS => 'MAGIC_CLASS', \ast\flags\MAGIC_DIR => 'MAGIC_DIR', \ast\flags\MAGIC_FILE => 'MAGIC_FILE', \ast\flags\MAGIC_FUNCTION => 'MAGIC_FUNCTION', \ast\flags\MAGIC_LINE => 'MAGIC_LINE', \ast\flags\MAGIC_METHOD => 'MAGIC_METHOD', \ast\flags\MAGIC_NAMESPACE => 'MAGIC_NAMESPACE', \ast\flags\MAGIC_TRAIT => 'MAGIC_TRAIT', \ast\flags\UNARY_MINUS => 'UNARY_MINUS', \ast\flags\UNARY_PLUS => 'UNARY_PLUS', \ast\flags\UNARY_SILENCE => 'UNARY_SILENCE', \ast\flags\USE_CONST => 'USE_CONST', \ast\flags\USE_FUNCTION => 'USE_FUNCTION', \ast\flags\USE_NORMAL => 'USE_NORMAL'];
}