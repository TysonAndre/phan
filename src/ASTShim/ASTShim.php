<?php
/**
 * Based on PHPDoc stub file for ast extension
 * @author Bill Schaller <bill@zeroedin.com>
 * @author Nikita Popov <nikic@php.net>
 *
 * With modifications to be a functional replacement for ext-ast
 * @author Tyson Andre
 */

// AST KIND CONSTANTS
namespace ast;
require_once __DIR__ . '/../ASTConverter/ASTConverter.php';
use ASTConverter\ASTConverter;

const AST_ARG_LIST = 128;
const AST_LIST = 255;
const AST_ARRAY = 129;
const AST_ENCAPS_LIST = 130;
const AST_EXPR_LIST = 131;
const AST_STMT_LIST = 132;
const AST_IF = 133;
const AST_SWITCH_LIST = 134;
const AST_CATCH_LIST = 135;
const AST_PARAM_LIST = 136;
const AST_CLOSURE_USES = 137;
const AST_PROP_DECL = 138;
const AST_CONST_DECL = 139;
const AST_CLASS_CONST_DECL = 140;
const AST_NAME_LIST = 141;
const AST_TRAIT_ADAPTATIONS = 142;
const AST_USE = 143;
const AST_NAME = 2048;
const AST_CLOSURE_VAR = 2049;
const AST_NULLABLE_TYPE = 2050;
const AST_FUNC_DECL = 66;
const AST_CLOSURE = 67;
const AST_METHOD = 68;
const AST_CLASS = 69;
const AST_MAGIC_CONST = 0;
const AST_TYPE = 1;
const AST_VAR = 256;
const AST_CONST = 257;
const AST_UNPACK = 258;
const AST_UNARY_PLUS = 259;
const AST_UNARY_MINUS = 260;
const AST_CAST = 261;
const AST_EMPTY = 262;
const AST_ISSET = 263;
const AST_SILENCE = 264;
const AST_SHELL_EXEC = 265;
const AST_CLONE = 266;
const AST_EXIT = 267;
const AST_PRINT = 268;
const AST_INCLUDE_OR_EVAL = 269;
const AST_UNARY_OP = 270;
const AST_PRE_INC = 271;
const AST_PRE_DEC = 272;
const AST_POST_INC = 273;
const AST_POST_DEC = 274;
const AST_YIELD_FROM = 275;
const AST_GLOBAL = 276;
const AST_UNSET = 277;
const AST_RETURN = 278;
const AST_LABEL = 279;
const AST_REF = 280;
const AST_HALT_COMPILER = 281;
const AST_ECHO = 282;
const AST_THROW = 283;
const AST_GOTO = 284;
const AST_BREAK = 285;
const AST_CONTINUE = 286;
const AST_DIM = 512;
const AST_PROP = 513;
const AST_STATIC_PROP = 514;
const AST_CALL = 515;
const AST_CLASS_CONST = 516;
const AST_ASSIGN = 517;
const AST_ASSIGN_REF = 518;
const AST_ASSIGN_OP = 519;
const AST_BINARY_OP = 520;
const AST_GREATER = 521;
const AST_GREATER_EQUAL = 522;
const AST_AND = 523;
const AST_OR = 524;
const AST_ARRAY_ELEM = 525;
const AST_NEW = 526;
const AST_INSTANCEOF = 527;
const AST_YIELD = 528;
const AST_COALESCE = 529;
const AST_STATIC = 530;
const AST_WHILE = 531;
const AST_DO_WHILE = 532;
const AST_IF_ELEM = 533;
const AST_SWITCH = 534;
const AST_SWITCH_CASE = 535;
const AST_DECLARE = 536;
const AST_PROP_ELEM = 774;
const AST_CONST_ELEM = 775;
const AST_USE_TRAIT = 537;
const AST_TRAIT_PRECEDENCE = 538;
const AST_METHOD_REFERENCE = 539;
const AST_NAMESPACE = 540;
const AST_USE_ELEM = 541;
const AST_TRAIT_ALIAS = 542;
const AST_GROUP_USE = 543;
const AST_METHOD_CALL = 768;
const AST_STATIC_CALL = 769;
const AST_CONDITIONAL = 770;
const AST_TRY = 771;
const AST_CATCH = 772;
const AST_PARAM = 773;
const AST_FOR = 1024;
const AST_FOREACH = 1025;
// END AST KIND CONSTANTS

// AST FLAG CONSTANTS
namespace ast\flags;
const NAME_FQ = 0;
const NAME_NOT_FQ = 1;
const NAME_RELATIVE = 2;
const MODIFIER_PUBLIC = 256;
const MODIFIER_PROTECTED = 512;
const MODIFIER_PRIVATE = 1024;
const MODIFIER_STATIC = 1;
const MODIFIER_ABSTRACT = 2;
const MODIFIER_FINAL = 4;
const RETURNS_REF = 67108864;
const CLASS_ABSTRACT = 32;
const CLASS_FINAL = 4;
const CLASS_TRAIT = 128;
const CLASS_INTERFACE = 64;
const CLASS_ANONYMOUS = 256;
const PARAM_REF = 1;
const PARAM_VARIADIC = 2;
const TYPE_NULL = 1;
const TYPE_BOOL = 13;
const TYPE_LONG = 4;
const TYPE_DOUBLE = 5;
const TYPE_STRING = 6;
const TYPE_ARRAY = 7;
const TYPE_OBJECT = 8;
const TYPE_CALLABLE = 14;
const TYPE_VOID = 18;
const TYPE_ITERABLE = 19;
const UNARY_BOOL_NOT = 13;
const UNARY_BITWISE_NOT = 12;
const UNARY_SILENCE = 260;
const UNARY_PLUS = 261;
const UNARY_MINUS = 262;
const BINARY_BOOL_AND = 259;
const BINARY_BOOL_OR = 258;
const BINARY_BOOL_XOR = 14;
const BINARY_BITWISE_OR = 9;
const BINARY_BITWISE_AND = 10;
const BINARY_BITWISE_XOR = 11;
const BINARY_CONCAT = 8;
const BINARY_ADD = 1;
const BINARY_SUB = 2;
const BINARY_MUL = 3;
const BINARY_DIV = 4;
const BINARY_MOD = 5;
const BINARY_POW = 166;
const BINARY_SHIFT_LEFT = 6;
const BINARY_SHIFT_RIGHT = 7;
const BINARY_IS_IDENTICAL = 15;
const BINARY_IS_NOT_IDENTICAL = 16;
const BINARY_IS_EQUAL = 17;
const BINARY_IS_NOT_EQUAL = 18;
const BINARY_IS_SMALLER = 19;
const BINARY_IS_SMALLER_OR_EQUAL = 20;
const BINARY_IS_GREATER = 256;
const BINARY_IS_GREATER_OR_EQUAL = 257;
const BINARY_SPACESHIP = 170;
const BINARY_COALESCE = 260;
const ASSIGN_BITWISE_OR = 31;
const ASSIGN_BITWISE_AND = 32;
const ASSIGN_BITWISE_XOR = 33;
const ASSIGN_CONCAT = 30;
const ASSIGN_ADD = 23;
const ASSIGN_SUB = 24;
const ASSIGN_MUL = 25;
const ASSIGN_DIV = 26;
const ASSIGN_MOD = 27;
const ASSIGN_POW = 167;
const ASSIGN_SHIFT_LEFT = 28;
const ASSIGN_SHIFT_RIGHT = 29;
const EXEC_EVAL = 1;
const EXEC_INCLUDE = 2;
const EXEC_INCLUDE_ONCE = 4;
const EXEC_REQUIRE = 8;
const EXEC_REQUIRE_ONCE = 16;
const USE_NORMAL = 361;
const USE_FUNCTION = 346;
const USE_CONST = 347;
const MAGIC_LINE = 370;
const MAGIC_FILE = 371;
const MAGIC_DIR = 372;
const MAGIC_NAMESPACE = 389;
const MAGIC_FUNCTION = 376;
const MAGIC_METHOD = 375;
const MAGIC_CLASS = 373;
const MAGIC_TRAIT = 374;
const ARRAY_SYNTAX_LIST = 1;
const ARRAY_SYNTAX_LONG = 2;
const ARRAY_SYNTAX_SHORT = 3;
// END AST FLAG CONSTANTS

namespace ast;
use ASTConverter\ASTConverter;

if (!function_exists('\ast\parse_file')) {
/**
 * Parses code file and returns AST root node.
 *
 * @param string $filename Code file to parse
 * @param int    $version  AST version
 * @return Node Root node of AST
 *
 * @see https://github.com/nikic/php-ast for version information
 */
function parse_file(string $filename, int $version)
{
    return parse_code(file_get_contents($filename), $version);
}
}

if (!function_exists('\ast\parse_code')) {
/**
 * Parses code string and returns AST root node.
 *
 * @param string $code     Code string to parse
 * @param int    $version  AST version
 * @param string $filename Optional filename for use in parse errors
 * @return Node Root node of AST
 *
 * @see https://github.com/nikic/php-ast for version information
 */
function parse_code(string $code, int $version, string $filename = "string code")
{
    // TODO: Handle errors?
    return ASTConverter::ast_parse_code_fallback($code, $version);
}
}

if (!function_exists('\ast\get_kind_name')) {
/**
 * @param int $kind AST_* constant value defining the kind of an AST node
 * @return string|null String representation of AST kind value
 * Source: php-ast/ast_data.c, ast_kind_to_name
 */
function get_kind_name($kind)
{
    // TODO: An array lookup would be faster than a giant switch.
	switch (kind) {
		case AST_ARG_LIST: return "AST_ARG_LIST";
		case AST_LIST: return "AST_LIST";
		case AST_ARRAY: return "AST_ARRAY";
		case AST_ENCAPS_LIST: return "AST_ENCAPS_LIST";
		case AST_EXPR_LIST: return "AST_EXPR_LIST";
		case AST_STMT_LIST: return "AST_STMT_LIST";
		case AST_IF: return "AST_IF";
		case AST_SWITCH_LIST: return "AST_SWITCH_LIST";
		case AST_CATCH_LIST: return "AST_CATCH_LIST";
		case AST_PARAM_LIST: return "AST_PARAM_LIST";
		case AST_CLOSURE_USES: return "AST_CLOSURE_USES";
		case AST_PROP_DECL: return "AST_PROP_DECL";
		case AST_CONST_DECL: return "AST_CONST_DECL";
		case AST_CLASS_CONST_DECL: return "AST_CLASS_CONST_DECL";
		case AST_NAME_LIST: return "AST_NAME_LIST";
		case AST_TRAIT_ADAPTATIONS: return "AST_TRAIT_ADAPTATIONS";
		case AST_USE: return "AST_USE";
		case AST_NAME: return "AST_NAME";
		case AST_CLOSURE_VAR: return "AST_CLOSURE_VAR";
		case AST_NULLABLE_TYPE: return "AST_NULLABLE_TYPE";
		case AST_FUNC_DECL: return "AST_FUNC_DECL";
		case AST_CLOSURE: return "AST_CLOSURE";
		case AST_METHOD: return "AST_METHOD";
		case AST_CLASS: return "AST_CLASS";
		case AST_MAGIC_CONST: return "AST_MAGIC_CONST";
		case AST_TYPE: return "AST_TYPE";
		case AST_VAR: return "AST_VAR";
		case AST_CONST: return "AST_CONST";
		case AST_UNPACK: return "AST_UNPACK";
		case AST_UNARY_PLUS: return "AST_UNARY_PLUS";
		case AST_UNARY_MINUS: return "AST_UNARY_MINUS";
		case AST_CAST: return "AST_CAST";
		case AST_EMPTY: return "AST_EMPTY";
		case AST_ISSET: return "AST_ISSET";
		case AST_SILENCE: return "AST_SILENCE";
		case AST_SHELL_EXEC: return "AST_SHELL_EXEC";
		case AST_CLONE: return "AST_CLONE";
		case AST_EXIT: return "AST_EXIT";
		case AST_PRINT: return "AST_PRINT";
		case AST_INCLUDE_OR_EVAL: return "AST_INCLUDE_OR_EVAL";
		case AST_UNARY_OP: return "AST_UNARY_OP";
		case AST_PRE_INC: return "AST_PRE_INC";
		case AST_PRE_DEC: return "AST_PRE_DEC";
		case AST_POST_INC: return "AST_POST_INC";
		case AST_POST_DEC: return "AST_POST_DEC";
		case AST_YIELD_FROM: return "AST_YIELD_FROM";
		case AST_GLOBAL: return "AST_GLOBAL";
		case AST_UNSET: return "AST_UNSET";
		case AST_RETURN: return "AST_RETURN";
		case AST_LABEL: return "AST_LABEL";
		case AST_REF: return "AST_REF";
		case AST_HALT_COMPILER: return "AST_HALT_COMPILER";
		case AST_ECHO: return "AST_ECHO";
		case AST_THROW: return "AST_THROW";
		case AST_GOTO: return "AST_GOTO";
		case AST_BREAK: return "AST_BREAK";
		case AST_CONTINUE: return "AST_CONTINUE";
		case AST_DIM: return "AST_DIM";
		case AST_PROP: return "AST_PROP";
		case AST_STATIC_PROP: return "AST_STATIC_PROP";
		case AST_CALL: return "AST_CALL";
		case AST_CLASS_CONST: return "AST_CLASS_CONST";
		case AST_ASSIGN: return "AST_ASSIGN";
		case AST_ASSIGN_REF: return "AST_ASSIGN_REF";
		case AST_ASSIGN_OP: return "AST_ASSIGN_OP";
		case AST_BINARY_OP: return "AST_BINARY_OP";
		case AST_GREATER: return "AST_GREATER";
		case AST_GREATER_EQUAL: return "AST_GREATER_EQUAL";
		case AST_AND: return "AST_AND";
		case AST_OR: return "AST_OR";
		case AST_ARRAY_ELEM: return "AST_ARRAY_ELEM";
		case AST_NEW: return "AST_NEW";
		case AST_INSTANCEOF: return "AST_INSTANCEOF";
		case AST_YIELD: return "AST_YIELD";
		case AST_COALESCE: return "AST_COALESCE";
		case AST_STATIC: return "AST_STATIC";
		case AST_WHILE: return "AST_WHILE";
		case AST_DO_WHILE: return "AST_DO_WHILE";
		case AST_IF_ELEM: return "AST_IF_ELEM";
		case AST_SWITCH: return "AST_SWITCH";
		case AST_SWITCH_CASE: return "AST_SWITCH_CASE";
		case AST_DECLARE: return "AST_DECLARE";
		case AST_PROP_ELEM: return "AST_PROP_ELEM";
		case AST_CONST_ELEM: return "AST_CONST_ELEM";
		case AST_USE_TRAIT: return "AST_USE_TRAIT";
		case AST_TRAIT_PRECEDENCE: return "AST_TRAIT_PRECEDENCE";
		case AST_METHOD_REFERENCE: return "AST_METHOD_REFERENCE";
		case AST_NAMESPACE: return "AST_NAMESPACE";
		case AST_USE_ELEM: return "AST_USE_ELEM";
		case AST_TRAIT_ALIAS: return "AST_TRAIT_ALIAS";
		case AST_GROUP_USE: return "AST_GROUP_USE";
		case AST_METHOD_CALL: return "AST_METHOD_CALL";
		case AST_STATIC_CALL: return "AST_STATIC_CALL";
		case AST_CONDITIONAL: return "AST_CONDITIONAL";
		case AST_TRY: return "AST_TRY";
		case AST_CATCH: return "AST_CATCH";
		case AST_PARAM: return "AST_PARAM";
		case AST_FOR: return "AST_FOR";
		case AST_FOREACH: return "AST_FOREACH";
	}
}
}

if (!function_exists('\ast\kind_uses_flags')) {
/**
 * @param int $kind AST_* constant value defining the kind of an AST node
 * @return bool Returns true if AST kind uses flags
 */
function kind_uses_flags($kind)
{
    // TODO: lookup table
    // source: php-ast/ast.c, ast_kind_uses_attr(kind)
    if ($kind === AST_PARAM || $kind === AST_TYPE || $kind === AST_TRAIT_ALIAS
		|| $kind === AST_UNARY_OP || $kind === AST_BINARY_OP || $kind === AST_ASSIGN_OP
		|| $kind === AST_CAST || $kind === AST_MAGIC_CONST || $kind === AST_ARRAY_ELEM
		|| $kind === AST_INCLUDE_OR_EVAL || $kind === AST_USE || $kind === AST_PROP_DECL
		|| $kind === AST_GROUP_USE || $kind === AST_USE_ELEM
		|| $kind === AST_NAME || $kind === AST_CLOSURE_VAR || $kind === AST_CLASS_CONST_DECL
        || $kind === AST_ARRAY) {
        return true;
    }
    // source: php-ast/ast.c, ast_kind_is_decl(kind)
	return $kind === AST_FUNC_DECL || $kind === AST_CLOSURE
		|| $kind === AST_METHOD || $kind === AST_CLASS;
}
}

if (!class_exists('\ast\Node')) {
/**
 * This class describes a single node in a PHP AST.
 */
class Node
{
    /** @var int AST Node Kind. Values are one of ast\AST_* constants. */
    public $kind;

    /**
     * @var int AST Flags.
     * Certain node kinds have flags that can be set.
     * These will be a bitfield of ast\flags\* constants.
     */
    public $flags;

    /** @var int Line the node starts in */
    public $lineno;

    /** @var array Child nodes (may be empty) */
    public $children;
}
}

namespace ast\Node;

if (!class_exists('\ast\Node\Decl')) {
/**
 * AST Node type for function and class declarations.
 */
class Decl extends \ast\Node
{
    /** @var int End line number of the declaration */
    public $endLineno;

    /** @var string Name of the function or class (not including the namespace prefix) */
    public $name;

    /** @var string|null Doc comment preceeding the declaration. null if no doc comment was used. */
    public $docComment;
}
}
