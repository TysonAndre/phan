<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\Config;
use Phan\Language\Context;
use Phan\Language\Element\Comment\Parameter as CommentParameter;
use Phan\Language\Element\Comment\Method as CommentMethod;
use Phan\Language\Element\Flags;
use Phan\Language\Type;
use Phan\Language\Type\TemplateType;
use Phan\Language\Type\VoidType;
use Phan\Language\UnionType;
use Phan\Library\None;
use Phan\Library\Option;
use Phan\Library\Some;
/**
 * Handles extracting information(param types, return types, magic methods/properties, etc.) from phpdoc comments.
 * Instances of Comment contain the extracted information.
 */
class Comment
{
    const word_regex = '([a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)';
    /**
     * @var int - contains a subset of flags to set on elements
     * Flags::CLASS_FORBID_UNDECLARED_MAGIC_PROPERTIES
     * Flags::CLASS_FORBID_UNDECLARED_MAGIC_METHODS
     * Flags::IS_DEPRECATED
     */
    private $comment_flags = 0;
    /**
     * @var CommentParameter[]
     * A list of CommentParameters from var declarations
     */
    private $variable_list = [];
    /**
     * @var CommentParameter[]
     * A list of CommentParameters from param declarations
     */
    private $parameter_list = [];
    /**
     * @var CommentParameter[]
     * A map from variable name to CommentParameters from
     * param declarations
     */
    private $parameter_map = [];
    /**
     * @var string[]
     * A list of template types parameterizing a generic class
     */
    private $template_type_list = [];
    /**
     * @var Option<Type>|null
     * Classes may specify their inherited type explicitly
     * via `@inherits Type`.
     */
    private $inherited_type = null;
    /**
     * @var UnionType|null
     * A UnionType defined by a @return directive
     */
    private $return_union_type = null;
    /**
     * @var string[]
     * A list of issue types to be suppressed
     */
    private $suppress_issue_list = [];
    /**
     * @var CommentParameter[]
     * A mapping from magic property parameters to types.
     */
    private $magic_property_map = [];
    /**
     * @var CommentMethod[]
     * A mapping from magic methods to parsed parameters, name, and return types.
     */
    private $magic_method_map = [];
    /**
     * @var Option<Type>
     * An optional class name defined by a @PhanClosureScope directive.
     * (overrides the class in which it is analyzed)
     */
    private $closure_scope;
    /**
     * A private constructor meant to ingest a parsed comment
     * docblock.
     *
     * @param int $comment_flags uses the following flags
     * - Flags::IS_DEPRECATED
     *   Set to true if the comment contains a 'deprecated'
     *   directive.
     * - Flags::CLASS_FORBID_UNDECLARED_MAGIC_PROPERTIES
     * - Flags::CLASS_FORBID_UNDECLARED_MAGIC_METHODS
     *
     * @param CommentParameter[] $variable_list
     *
     * @param CommentParameter[] $parameter_list
     *
     * @param string[] $template_type_list
     * A list of template types parameterizing a generic class
     *
     * @param Option<Type> $inherited_type
     * An override on the type of the extended class
     *
     * @param UnionType $return_union_type
     *
     * @param string[] $suppress_issue_list
     * A list of tags for error type to be suppressed
     *
     * @param CommentParameter[] $magic_property_list
     *
     * @param CommentMethod[] $magic_method_list
     *
     * @param Option<Type> $closure_scope
     * For closures: Allows us to document the class of the object
     * to which a closure will be bound.
     */
    private function __construct($comment_flags, array $variable_list, array $parameter_list, array $template_type_list, Option $inherited_type, UnionType $return_union_type, array $suppress_issue_list, array $magic_property_list, array $magic_method_list, Option $closure_scope)
    {
        if (!is_int($comment_flags)) {
            throw new \InvalidArgumentException("Argument \$comment_flags passed to __construct() must be of the type int, " . (gettype($comment_flags) == "object" ? get_class($comment_flags) : gettype($comment_flags)) . " given");
        }
        $this->comment_flags = $comment_flags;
        $this->variable_list = $variable_list;
        $this->parameter_list = $parameter_list;
        $this->template_type_list = $template_type_list;
        $this->inherited_type = $inherited_type;
        $this->return_union_type = $return_union_type;
        $this->suppress_issue_list = $suppress_issue_list;
        $this->closure_scope = $closure_scope;
        foreach ($this->parameter_list as $i => $parameter) {
            $name = $parameter->getName();
            if (!empty($name)) {
                // Add it to the named map
                $this->parameter_map[$name] = $parameter;
                // Remove it from the offset map
                unset($this->parameter_list[$i]);
            }
        }
        foreach ($magic_property_list as $property) {
            $name = $property->getName();
            if (!empty($name)) {
                // Add it to the named map
                // TODO: Detect duplicates, emit warning for duplicates.
                // TODO(optional): Emit Issues when a property with only property-read is written to
                // or vice versa.
                $this->magic_property_map[$name] = $property;
            }
        }
        foreach ($magic_method_list as $method) {
            $name = $method->getName();
            if (!empty($name)) {
                // Add it to the named map
                // TODO: Detect duplicates, emit warning for duplicates.
                $this->magic_method_map[$name] = $method;
            }
        }
    }
    /**
     * @return Comment
     * A comment built by parsing the given doc block
     * string.
     */
    public static function fromStringInContext($comment, Context $context)
    {
        if (!is_string($comment)) {
            throw new \InvalidArgumentException("Argument \$comment passed to fromStringInContext() must be of the type string, " . (gettype($comment) == "object" ? get_class($comment) : gettype($comment)) . " given");
        }
        if (!Config::get()->read_type_annotations) {
            $ret5902c6f548f0a = new Comment(0, [], [], [], new None(), new UnionType(), [], [], [], new None());
            if (!$ret5902c6f548f0a instanceof Comment) {
                throw new \InvalidArgumentException("Argument returned must be of the type Comment, " . (gettype($ret5902c6f548f0a) == "object" ? get_class($ret5902c6f548f0a) : gettype($ret5902c6f548f0a)) . " given");
            }
            return $ret5902c6f548f0a;
        }
        $variable_list = [];
        $parameter_list = [];
        $template_type_list = [];
        $inherited_type = new None();
        $return_union_type = new UnionType();
        $suppress_issue_list = [];
        $magic_property_list = [];
        $magic_method_list = [];
        $closure_scope = new None();
        $comment_flags = 0;
        $lines = explode("\n", $comment);
        foreach ($lines as $line) {
            if (strpos($line, '@param') !== false) {
                $parameter_list[] = self::parameterFromCommentLine($context, $line, false);
            } elseif (stripos($line, '@var') !== false) {
                $variable_list[] = self::parameterFromCommentLine($context, $line, true);
            } elseif (stripos($line, '@template') !== false) {
                // Make sure support for generic types is enabled
                if (Config::get()->generic_types_enabled) {
                    if ($template_type = self::templateTypeFromCommentLine($context, $line)) {
                        $template_type_list[] = $template_type;
                    }
                }
            } elseif (stripos($line, '@inherits') !== false) {
                // Make sure support for generic types is enabled
                if (Config::get()->generic_types_enabled) {
                    $inherited_type = self::inheritsFromCommentLine($context, $line);
                }
            } elseif (stripos($line, '@return') !== false) {
                $return_union_type = self::returnTypeFromCommentLine($context, $line);
            } elseif (stripos($line, '@suppress') !== false) {
                $suppress_issue_list[] = self::suppressIssueFromCommentLine($line);
            } elseif (strpos($line, '@property') !== false) {
                // Make sure support for magic properties is enabled.
                if (Config::get()->read_magic_property_annotations) {
                    $magic_property = self::magicPropertyFromCommentLine($context, $line);
                    if ($magic_property !== null) {
                        $magic_property_list[] = $magic_property;
                    }
                }
            } elseif (strpos($line, '@method') !== false) {
                // Make sure support for magic methods is enabled.
                if (Config::get()->read_magic_method_annotations) {
                    $magic_method = self::magicMethodFromCommentLine($context, $line);
                    if ($magic_method !== null) {
                        $magic_method_list[] = $magic_method;
                    }
                }
            } elseif (stripos($line, '@PhanClosureScope') !== false) {
                $closure_scope = self::getPhanClosureScopeFromCommentLine($context, $line);
            } elseif (stripos($line, '@phan-forbid-undeclared-magic-properties') !== false) {
                $comment_flags |= Flags::CLASS_FORBID_UNDECLARED_MAGIC_PROPERTIES;
            } elseif (stripos($line, '@phan-forbid-undeclared-magic-methods') !== false) {
                $comment_flags |= Flags::CLASS_FORBID_UNDECLARED_MAGIC_METHODS;
            }
            if (stripos($line, '@deprecated') !== false) {
                if (preg_match('/@deprecated\\b/', $line, $match)) {
                    $comment_flags |= Flags::IS_DEPRECATED;
                }
            }
            if (stripos($line, '@internal') !== false) {
                if (preg_match('/@internal\\s/', $line, $match)) {
                    $comment_flags |= Flags::IS_NS_INTERNAL;
                }
            }
        }
        $ret5902c6f549e05 = new Comment($comment_flags, $variable_list, $parameter_list, $template_type_list, $inherited_type, $return_union_type, $suppress_issue_list, $magic_property_list, $magic_method_list, $closure_scope);
        if (!$ret5902c6f549e05 instanceof Comment) {
            throw new \InvalidArgumentException("Argument returned must be of the type Comment, " . (gettype($ret5902c6f549e05) == "object" ? get_class($ret5902c6f549e05) : gettype($ret5902c6f549e05)) . " given");
        }
        return $ret5902c6f549e05;
    }
    /**
     * @param Context $context
     * The context in which the comment line appears
     *
     * @param string $line
     * An individual line of a comment
     *
     * @return UnionType
     * The declared return type
     */
    private static function returnTypeFromCommentLine(Context $context, $line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to returnTypeFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        $return_union_type_string = '';
        if (preg_match('/@return\\s+(' . UnionType::union_type_regex . '+)/', $line, $match)) {
            $return_union_type_string = $match[1];
        }
        $return_union_type = UnionType::fromStringInContext($return_union_type_string, $context, true);
        return $return_union_type;
    }
    /**
     * @param Context $context
     * The context in which the comment line appears
     *
     * @param string $line
     * An individual line of a comment
     *
     * @param bool $is_var
     * True if this is parsing a variable, false if parsing a parameter.
     *
     * @return CommentParameter
     * A CommentParameter associated with a line that has a var
     * or param reference.
     */
    private static function parameterFromCommentLine(Context $context, $line, $is_var)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to parameterFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        if (!is_bool($is_var)) {
            throw new \InvalidArgumentException("Argument \$is_var passed to parameterFromCommentLine() must be of the type bool, " . (gettype($is_var) == "object" ? get_class($is_var) : gettype($is_var)) . " given");
        }
        $match = [];
        if (preg_match('/@(param|var)\\s+(' . UnionType::union_type_regex . ')(\\s+(\\.\\.\\.)?\\s*(\\$\\S+))?/', $line, $match)) {
            $type = $match[2];
            $is_variadic = call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$match[29], @'') === '...';
            if ($is_var && $is_variadic) {
                $variable_name = '';
                // "@var int ...$x" is nonsense and invalid phpdoc.
            } else {
                $variable_name = empty($match[30]) ? '' : trim($match[30], '$');
            }
            // If the type looks like a variable name, make it an
            // empty type so that other stuff can match it. We can't
            // just skip it or we'd mess up the parameter order.
            $union_type = null;
            if (0 !== strpos($type, '$')) {
                $union_type = UnionType::fromStringInContext($type, $context, true);
            } else {
                $union_type = new UnionType();
            }
            return new CommentParameter($variable_name, $union_type, $is_variadic);
        }
        return new CommentParameter('', new UnionType());
    }
    /**
     * @param Context $context
     * The context in which the comment line appears
     *
     * @param string $line
     * An individual line of a comment
     *
     * @return TemplateType|null
     * A generic type identifier or null if a valid type identifier
     * wasn't found.
     */
    private static function templateTypeFromCommentLine(Context $context, $line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to templateTypeFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        $match = [];
        if (preg_match('/@template\\s+(' . Type::simple_type_regex . ')/', $line, $match)) {
            $template_type_identifier = $match[1];
            return new TemplateType($template_type_identifier);
        }
        return null;
    }
    /**
     * @param Context $context
     * The context in which the comment line appears
     *
     * @param string $line
     * An individual line of a comment
     *
     * @return Option<Type>
     * An optional type overriding the extended type of the class
     */
    private static function inheritsFromCommentLine(Context $context, $line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to inheritsFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        $match = [];
        if (preg_match('/@inherits\\s+(' . Type::type_regex . ')/', $line, $match)) {
            $type_string = $match[1];
            $type = new Some(Type::fromStringInContext($type_string, $context, true));
            return $type;
        }
        return new None();
    }
    /**
     * @param string $line
     * An individual line of a comment
     *
     * @return string
     * An issue name to suppress
     */
    private static function suppressIssueFromCommentLine($line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to suppressIssueFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        if (preg_match('/@suppress\\s+([^\\s]+)/', $line, $match)) {
            $ret5902c6f54b7e3 = $match[1];
            if (!is_string($ret5902c6f54b7e3)) {
                throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f54b7e3) . " given");
            }
            return $ret5902c6f54b7e3;
        }
        $ret5902c6f54ba26 = '';
        if (!is_string($ret5902c6f54ba26)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f54ba26) . " given");
        }
        return $ret5902c6f54ba26;
    }
    /**
     * Parses a magic method based on https://phpdoc.org/docs/latest/references/phpdoc/tags/method.html
     * @return ?CommentParameter - if null, the phpdoc magic method was invalid.
     */
    private static function magicParamFromMagicMethodParamString(Context $context, $param_string, $param_index)
    {
        if (!is_string($param_string)) {
            throw new \InvalidArgumentException("Argument \$param_string passed to magicParamFromMagicMethodParamString() must be of the type string, " . (gettype($param_string) == "object" ? get_class($param_string) : gettype($param_string)) . " given");
        }
        if (!is_int($param_index)) {
            throw new \InvalidArgumentException("Argument \$param_index passed to magicParamFromMagicMethodParamString() must be of the type int, " . (gettype($param_index) == "object" ? get_class($param_index) : gettype($param_index)) . " given");
        }
        $param_string = trim($param_string);
        // Don't support trailing commas, or omitted params. Provide at least one of [type] or [parameter]
        if ($param_string === '') {
            return null;
        }
        // Parse an entry for [type] [parameter] - Assume both of those are optional.
        // https://github.com/phpDocumentor/phpDocumentor2/pull/1271/files - phpdoc allows passing an default value.
        // Phan allows `=.*`, to indicate that a parameter is optional
        // TODO: in another PR, check that optional parameters aren't before required parameters.
        if (preg_match('/^(' . UnionType::union_type_regex . ')?\\s*((\\.\\.\\.)\\s*)?(\\$' . self::word_regex . ')?((\\s*=.*)?)$/', $param_string, $param_match)) {
            // Note: a magic method parameter can be variadic, but it can't be pass-by-reference? (No support in __call)
            $union_type_string = $param_match[1];
            $union_type = UnionType::fromStringInContext($union_type_string, $context, true);
            $is_variadic = $param_match[28] === '...';
            $default_str = $param_match[31];
            $has_default_value = $default_str !== '';
            if ($has_default_value) {
                $default_value_repr = trim(explode('=', $default_str, 2)[1]);
                if (strcasecmp($default_value_repr, 'null') === 0) {
                    $union_type = $union_type->nullableClone();
                }
            }
            $var_name = $param_match[30];
            if ($var_name === '') {
                // placeholder names are p1, p2, ...
                $var_name = 'p' . ($param_index + 1);
            }
            return new CommentParameter($var_name, $union_type, $is_variadic, $has_default_value);
        }
        return null;
    }
    /**
     * @param Context $context
     * @param string $line
     * An individual line of a comment
     *
     * @return ?CommentMethod
     * magic method with the parameter types, return types, and name.
     */
    private static function magicMethodFromCommentLine(Context $context, $line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to magicMethodFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        // Note that the type of a property can be left out (@property $myVar) - This is equivalent to @property mixed $myVar
        // TODO: properly handle duplicates...
        // https://phpdoc.org/docs/latest/references/phpdoc/tags/method.html
        // > Going to assume "static" is a magic keyword, based on https://github.com/phpDocumentor/phpDocumentor2/issues/822
        // > TODO: forbid in trait?
        // TODO: finish writing the regex.
        // Syntax:
        //    @method [return type] [name]([[type] [parameter]<, ...>]) [<description>]
        //    Assumes the parameters end at the first ")" after "("
        if (preg_match('/@method(\\s+(static))?((\\s+(' . UnionType::union_type_regex . '))?)\\s+' . self::word_regex . '\\s*\\(([^()]*)\\)\\s*(.*)/', $line, $match)) {
            $is_static = $match[2] === 'static';
            $return_union_type_string = $match[4];
            if ($return_union_type_string !== '') {
                $return_union_type = UnionType::fromStringInContext($return_union_type_string, $context, true);
            } else {
                // From https://phpdoc.org/docs/latest/references/phpdoc/tags/method.html
                // > When the intended method does not have a return value then the return type MAY be omitted; in which case 'void' is implied.
                $return_union_type = VoidType::instance(false)->asUnionType();
            }
            $method_name = $match[31];
            $arg_list = trim($match[32]);
            $comment_params = [];
            // Special check if param list has 0 params.
            if ($arg_list !== '') {
                // TODO: Would need to use a different approach if templates were ever supported
                $params_strings = explode(',', $arg_list);
                foreach ($params_strings as $i => $param_string) {
                    $param = self::magicParamFromMagicMethodParamString($context, $param_string, $i);
                    if ($param === null) {
                        return null;
                    }
                    $comment_params[] = $param;
                }
            }
            return new CommentMethod($method_name, $return_union_type, $comment_params, $is_static);
        }
        return null;
    }
    /**
     * @param Context $context
     * @param string $line
     * An individual line of a comment
     * Currently treats property-read and property-write the same way
     * because of the rewrites required for read-only properties.
     *
     * @return CommentParameter|null
     * magic property with the union type.
     */
    private static function magicPropertyFromCommentLine(Context $context, $line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to magicPropertyFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        // Note that the type of a property can be left out (@property $myVar) - This is equivalent to @property mixed $myVar
        // TODO: properly handle duplicates...
        // TODO: support read-only/write-only checks elsewhere in the codebase?
        if (preg_match('/@(property|property-read|property-write)(\\s+' . UnionType::union_type_regex . ')?(\\s+(\\$\\S+))/', $line, $match)) {
            $type = ltrim(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$match[2], @''));
            $property_name = empty($match[29]) ? '' : trim($match[29], '$');
            if ($property_name === '') {
                return null;
            }
            // If the type looks like a property name, make it an
            // empty type so that other stuff can match it.
            $union_type = null;
            if (0 !== strpos($type, '$')) {
                $union_type = UnionType::fromStringInContext($type, $context, true);
            } else {
                $union_type = new UnionType();
            }
            return new CommentParameter($property_name, $union_type);
        }
        return null;
    }
    /**
     * The context in which the comment line appears
     *
     * @param string $line
     * An individual line of a comment
     *
     * @return Option<Type>
     * A class/interface to use as a context for a closure.
     * (Phan expects a ClassScope to have exactly one type)
     */
    private static function getPhanClosureScopeFromCommentLine(Context $context, $line)
    {
        if (!is_string($line)) {
            throw new \InvalidArgumentException("Argument \$line passed to getPhanClosureScopeFromCommentLine() must be of the type string, " . (gettype($line) == "object" ? get_class($line) : gettype($line)) . " given");
        }
        $closure_scope_union_type_string = '';
        // https://secure.php.net/manual/en/closure.bindto.php
        // There wasn't anything in the phpdoc standard to indicate the class to which
        // a Closure would be bound with bind() or bindTo(), so using a custom tag.
        //
        // TODO: Also add a version which forbids using $this in the closure?
        if (preg_match('/@PhanClosureScope\\s+(' . UnionType::union_type_regex . '+)/', $line, $match)) {
            $closure_scope_union_type_string = $match[1];
        }
        if ($closure_scope_union_type_string !== '') {
            $ret5902c6f54d5dc = new Some(Type::fromStringInContext($closure_scope_union_type_string, $context));
            if (!$ret5902c6f54d5dc instanceof Option) {
                throw new \InvalidArgumentException("Argument returned must be of the type Option, " . (gettype($ret5902c6f54d5dc) == "object" ? get_class($ret5902c6f54d5dc) : gettype($ret5902c6f54d5dc)) . " given");
            }
            return $ret5902c6f54d5dc;
        }
        $ret5902c6f54d8c1 = new None();
        if (!$ret5902c6f54d8c1 instanceof Option) {
            throw new \InvalidArgumentException("Argument returned must be of the type Option, " . (gettype($ret5902c6f54d8c1) == "object" ? get_class($ret5902c6f54d8c1) : gettype($ret5902c6f54d8c1)) . " given");
        }
        return $ret5902c6f54d8c1;
    }
    /**
     * @return bool
     * Set to true if the comment contains a 'deprecated'
     * directive.
     */
    public function isDeprecated()
    {
        $ret5902c6f54de28 = ($this->comment_flags & Flags::IS_DEPRECATED) != 0;
        if (!is_bool($ret5902c6f54de28)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54de28) . " given");
        }
        return $ret5902c6f54de28;
    }
    /**
     * @return bool
     * Set to true if the comment contains an 'internal'
     * directive.
     */
    public function isNSInternal()
    {
        $ret5902c6f54e0a3 = ($this->comment_flags & Flags::IS_NS_INTERNAL) != 0;
        if (!is_bool($ret5902c6f54e0a3)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54e0a3) . " given");
        }
        return $ret5902c6f54e0a3;
    }
    /**
     * @return bool
     * Set to true if the comment contains a 'phan-forbid-undeclared-magic-properties'
     * directive.
     */
    public function getForbidUndeclaredMagicProperties()
    {
        $ret5902c6f54e317 = ($this->comment_flags & Flags::CLASS_FORBID_UNDECLARED_MAGIC_PROPERTIES) != 0;
        if (!is_bool($ret5902c6f54e317)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54e317) . " given");
        }
        return $ret5902c6f54e317;
    }
    /**
     * @return bool
     * Set to true if the comment contains a 'phan-forbid-undeclared-magic-methods'
     * directive.
     */
    public function getForbidUndeclaredMagicMethods()
    {
        $ret5902c6f54e58e = ($this->comment_flags & Flags::CLASS_FORBID_UNDECLARED_MAGIC_METHODS) != 0;
        if (!is_bool($ret5902c6f54e58e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54e58e) . " given");
        }
        return $ret5902c6f54e58e;
    }
    /**
     * @return UnionType
     * A UnionType defined by a (at)return directive
     */
    public function getReturnType()
    {
        $ret5902c6f54e7e6 = $this->return_union_type;
        if (!$ret5902c6f54e7e6 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f54e7e6) == "object" ? get_class($ret5902c6f54e7e6) : gettype($ret5902c6f54e7e6)) . " given");
        }
        return $ret5902c6f54e7e6;
    }
    /**
     * @return bool
     * True if this doc block contains a (at)return
     * directive specifying a type.
     */
    public function hasReturnUnionType()
    {
        $ret5902c6f54eabb = !$this->return_union_type->isEmpty();
        if (!is_bool($ret5902c6f54eabb)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54eabb) . " given");
        }
        return $ret5902c6f54eabb;
    }
    /**
     * @return Option<Type>
     * An optional Type defined by a (at)PhanClosureScope
     * directive specifying a single type.
     */
    public function getClosureScopeOption()
    {
        $ret5902c6f54ed19 = $this->closure_scope;
        if (!$ret5902c6f54ed19 instanceof Option) {
            throw new \InvalidArgumentException("Argument returned must be of the type Option, " . (gettype($ret5902c6f54ed19) == "object" ? get_class($ret5902c6f54ed19) : gettype($ret5902c6f54ed19)) . " given");
        }
        return $ret5902c6f54ed19;
    }
    /**
     * @return CommentParameter[]
     *
     * @suppress PhanUnreferencedMethod
     */
    public function getParameterList()
    {
        $ret5902c6f54efe5 = $this->parameter_list;
        if (!is_array($ret5902c6f54efe5)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f54efe5) . " given");
        }
        return $ret5902c6f54efe5;
    }
    /**
     * @return TemplateType[]
     * A list of template types parameterizing a generic class
     */
    public function getTemplateTypeList()
    {
        $ret5902c6f54f232 = $this->template_type_list;
        if (!is_array($ret5902c6f54f232)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f54f232) . " given");
        }
        return $ret5902c6f54f232;
    }
    /**
     * @return Option<Type>
     * An optional type declaring what a class extends.
     */
    public function getInheritedTypeOption()
    {
        $ret5902c6f54f48c = $this->inherited_type;
        if (!$ret5902c6f54f48c instanceof Option) {
            throw new \InvalidArgumentException("Argument returned must be of the type Option, " . (gettype($ret5902c6f54f48c) == "object" ? get_class($ret5902c6f54f48c) : gettype($ret5902c6f54f48c)) . " given");
        }
        return $ret5902c6f54f48c;
    }
    /**
     * @return string[]
     * A set of issue names like 'PhanUnreferencedMethod' to suppress
     */
    public function getSuppressIssueList()
    {
        $ret5902c6f54f755 = $this->suppress_issue_list;
        if (!is_array($ret5902c6f54f755)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f54f755) . " given");
        }
        return $ret5902c6f54f755;
    }
    /**
     * @return bool
     * True if we have a parameter at the given offset
     */
    public function hasParameterWithNameOrOffset($name, $offset)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to hasParameterWithNameOrOffset() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($offset)) {
            throw new \InvalidArgumentException("Argument \$offset passed to hasParameterWithNameOrOffset() must be of the type int, " . (gettype($offset) == "object" ? get_class($offset) : gettype($offset)) . " given");
        }
        if (!empty($this->parameter_map[$name])) {
            $ret5902c6f54f9ea = true;
            if (!is_bool($ret5902c6f54f9ea)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54f9ea) . " given");
            }
            return $ret5902c6f54f9ea;
        }
        $ret5902c6f54fc4e = !empty($this->parameter_list[$offset]);
        if (!is_bool($ret5902c6f54fc4e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f54fc4e) . " given");
        }
        return $ret5902c6f54fc4e;
    }
    /**
     * @return CommentParameter
     * The paramter at the given offset
     */
    public function getParameterWithNameOrOffset($name, $offset)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getParameterWithNameOrOffset() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_int($offset)) {
            throw new \InvalidArgumentException("Argument \$offset passed to getParameterWithNameOrOffset() must be of the type int, " . (gettype($offset) == "object" ? get_class($offset) : gettype($offset)) . " given");
        }
        if (!empty($this->parameter_map[$name])) {
            $ret5902c6f5503b6 = $this->parameter_map[$name];
            if (!$ret5902c6f5503b6 instanceof CommentParameter) {
                throw new \InvalidArgumentException("Argument returned must be of the type CommentParameter, " . (gettype($ret5902c6f5503b6) == "object" ? get_class($ret5902c6f5503b6) : gettype($ret5902c6f5503b6)) . " given");
            }
            return $ret5902c6f5503b6;
        }
        $ret5902c6f550683 = $this->parameter_list[$offset];
        if (!$ret5902c6f550683 instanceof CommentParameter) {
            throw new \InvalidArgumentException("Argument returned must be of the type CommentParameter, " . (gettype($ret5902c6f550683) == "object" ? get_class($ret5902c6f550683) : gettype($ret5902c6f550683)) . " given");
        }
        return $ret5902c6f550683;
    }
    /**
     * @unused
     * @return bool
     * True if we have a magic property with the given name
     */
    public function hasMagicPropertyWithName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to hasMagicPropertyWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6f550def = isset($this->magic_property_map[$name]);
        if (!is_bool($ret5902c6f550def)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f550def) . " given");
        }
        return $ret5902c6f550def;
    }
    /**
     * @unused
     * @return CommentParameter
     * The magic property with the given name. May or may not have a type.
     */
    public function getMagicPropertyWithName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getMagicPropertyWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6f5512a1 = $this->magic_property_map[$name];
        if (!$ret5902c6f5512a1 instanceof CommentParameter) {
            throw new \InvalidArgumentException("Argument returned must be of the type CommentParameter, " . (gettype($ret5902c6f5512a1) == "object" ? get_class($ret5902c6f5512a1) : gettype($ret5902c6f5512a1)) . " given");
        }
        return $ret5902c6f5512a1;
    }
    /**
     * @return CommentParameter[] map from parameter name to parameter
     */
    public function getMagicPropertyMap()
    {
        $ret5902c6f55179a = $this->magic_property_map;
        if (!is_array($ret5902c6f55179a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f55179a) . " given");
        }
        return $ret5902c6f55179a;
    }
    /**
     * @return CommentMethod[] map from method name to method info
     */
    public function getMagicMethodMap()
    {
        $ret5902c6f5519f0 = $this->magic_method_map;
        if (!is_array($ret5902c6f5519f0)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f5519f0) . " given");
        }
        return $ret5902c6f5519f0;
    }
    /**
     * @return CommentParameter[]
     */
    public function getVariableList()
    {
        $ret5902c6f551c43 = $this->variable_list;
        if (!is_array($ret5902c6f551c43)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f551c43) . " given");
        }
        return $ret5902c6f551c43;
    }
    public function __toString()
    {
        // TODO: add new properties of Comment to this method
        // (magic methods, magic properties, custom @phan directives, etc.))
        $string = "/**\n";
        if (($this->comment_flags & Flags::IS_DEPRECATED) != 0) {
            $string .= " * @deprecated\n";
        }
        foreach ($this->variable_list as $variable) {
            $string .= " * @var {$variable}\n";
        }
        foreach ($this->parameter_list as $parameter) {
            $string .= " * @param {$parameter}\n";
        }
        if ($this->return_union_type) {
            $string .= " * @return {$this->return_union_type}\n";
        }
        $string .= " */\n";
        $ret5902c6f552020 = $string;
        if (!is_string($ret5902c6f552020)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f552020) . " given");
        }
        return $ret5902c6f552020;
    }
}