<?php
declare(strict_types=1);
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
    const word_regex = '([a-zA-Z_\x7f-\xff\\\][a-zA-Z0-9_\x7f-\xff\\\]*)';

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
    private function __construct(
        int $comment_flags,
        array $variable_list,
        array $parameter_list,
        array $template_type_list,
        Option $inherited_type,
        UnionType $return_union_type,
        array $suppress_issue_list,
        array $magic_property_list,
        array $magic_method_list,
        Option $closure_scope
    ) {
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
    public static function fromStringInContext(
        string $comment,
        Context $context
    ) : Comment {

        if (!Config::get()->read_type_annotations) {
            return new Comment(
                0, [], [], [], new None, new UnionType(), [], [], [], new None
            );
        }

        $variable_list = [];
        $parameter_list = [];
        $template_type_list = [];
        $inherited_type = new None;
        $return_union_type = new UnionType();
        $suppress_issue_list = [];
        $magic_property_list = [];
        $magic_method_list = [];
        $closure_scope = new None;
        $comment_flags = 0;

        $lines = explode("\n", $comment);

        foreach ($lines as $line) {

            if (strpos($line, '@param') !== false) {
                $parameter_list[] =
                    self::parameterFromCommentLine($context, $line, false);
            } elseif (stripos($line, '@var') !== false) {
                $variable_list[] =
                    self::parameterFromCommentLine($context, $line, true);
            } elseif (stripos($line, '@template') !== false) {

                // Make sure support for generic types is enabled
                if (Config::get()->generic_types_enabled) {
                    if (($template_type =
                        self::templateTypeFromCommentLine($context, $line))
                    ) {
                        $template_type_list[] = $template_type;
                    }
                }
            } elseif (stripos($line, '@inherits') !== false) {
                // Make sure support for generic types is enabled
                if (Config::get()->generic_types_enabled) {
                    $inherited_type =
                        self::inheritsFromCommentLine($context, $line);
                }
            } elseif (stripos($line, '@return') !== false) {
                $return_union_type =
                    self::returnTypeFromCommentLine($context, $line);
            } elseif (stripos($line, '@suppress') !== false) {
                $suppress_issue_list[] =
                    self::suppressIssueFromCommentLine($line);
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
                if (preg_match('/@deprecated\b/', $line, $match)) {
                    $comment_flags |= Flags::IS_DEPRECATED;
                }
            }
        }

        return new Comment(
            $comment_flags,
            $variable_list,
            $parameter_list,
            $template_type_list,
            $inherited_type,
            $return_union_type,
            $suppress_issue_list,
            $magic_property_list,
            $magic_method_list,
            $closure_scope
        );
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
    private static function returnTypeFromCommentLine(
        Context $context,
        string $line
    ) {
        $return_union_type_string = '';

        if (preg_match('/@return\s+(' . UnionType::union_type_regex . '+)/', $line, $match)) {
            $return_union_type_string = $match[1];
        }
        $return_union_type_string = self::rewritePHPDocType($return_union_type_string);

        $return_union_type = UnionType::fromStringInContext(
            $return_union_type_string,
            $context,
            true
        );

        return $return_union_type;
    }

    private static function rewritePHPDocType(
        string $original_type
    ) : string {
        // TODO: Would need to pass in CodeBase to emit an issue:
        $type = Config::get()->experimental_invalid_phpdoc_types[strtolower($original_type)] ?? null;
        if (is_string($type)) {
            return $type;
        }
        return $original_type;
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
    private static function parameterFromCommentLine(
        Context $context,
        string $line,
        bool $is_var
    ) {
        $match = [];
        if (preg_match('/@(param|var)\s+(' . UnionType::union_type_regex . ')(\s+(\.\.\.)?\s*(\\$\S+))?/', $line, $match)) {
            $original_type = $match[2];

            $is_variadic = ($match[29] ?? '') === '...';

            if ($is_var && $is_variadic) {
                $variable_name = '';  // "@var int ...$x" is nonsense and invalid phpdoc.
            } else {
                $variable_name =
                    empty($match[30]) ? '' : trim($match[30], '$');
            }
            // If the parameter has a type which is labelled as a typo (type maps to ''),
            // then treat it the same way as a parameter without a type in the doc comment.
            // Otherwise, continue with the (possibly renamed) type.
            $type = self::rewritePHPDocType($original_type);
            if ($type !== $original_type && $type === '') {
                return new CommentParameter('', new UnionType());
            }

            // If the type looks like a variable name, make it an
            // empty type so that other stuff can match it. We can't
            // just skip it or we'd mess up the parameter order.
            $union_type = null;
            if (0 !== strpos($type, '$')) {
                $union_type =
                    UnionType::fromStringInContext(
                        $type,
                        $context,
                        true
                    );
            } else {
                $union_type = new UnionType();
            }

            return new CommentParameter(
                $variable_name,
                $union_type,
                $is_variadic
            );
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
    private static function templateTypeFromCommentLine(
        Context $context,
        string $line
    ) {
        $match = [];
        if (preg_match('/@template\s+(' . Type::simple_type_regex. ')/', $line, $match)) {
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
    private static function inheritsFromCommentLine(
        Context $context,
        string $line
    ) {
        $match = [];
        if (preg_match('/@inherits\s+(' . Type::type_regex . ')/', $line, $match)) {
            $type_string = $match[1];

            $type = new Some(Type::fromStringInContext(
                $type_string,
                $context,
                true
            ));

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
    private static function suppressIssueFromCommentLine(
        string $line
    ) : string {
        if (preg_match('/@suppress\s+([^\s]+)/', $line, $match)) {
            return $match[1];
        }

        return '';
    }

    /**
     * Parses a magic method based on https://phpdoc.org/docs/latest/references/phpdoc/tags/method.html
     * @return ?CommentParameter - if null, the phpdoc magic method was invalid.
     */
    private static function magicParamFromMagicMethodParamString(
        Context $context,
        string $param_string,
        int $param_index
    ) {
        $param_string = trim($param_string);
        // Don't support trailing commas, or omitted params. Provide at least one of [type] or [parameter]
        if ($param_string === '') {
            return null;
        }
        // Parse an entry for [type] [parameter] - Assume both of those are optional.
        // https://github.com/phpDocumentor/phpDocumentor2/pull/1271/files - phpdoc allows passing an default value.
        // Phan allows `=.*`, to indicate that a parameter is optional
        // TODO: in another PR, check that optional parameters aren't before required parameters.
        if (preg_match('/^(' . UnionType::union_type_regex . ')?\s*((\.\.\.)\s*)?(\$' . self::word_regex . ')?((\s*=.*)?)$/', $param_string, $param_match)) {
            // Note: a magic method parameter can be variadic, but it can't be pass-by-reference? (No support in __call)
            $union_type_string = $param_match[1];
            $union_type = UnionType::fromStringInContext(
                $union_type_string,
                $context,
                true
            );
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
    private static function magicMethodFromCommentLine(
        Context $context,
        string $line
    ) {
        // Note that the type of a property can be left out (@property $myVar) - This is equivalent to @property mixed $myVar
        // TODO: properly handle duplicates...
        // https://phpdoc.org/docs/latest/references/phpdoc/tags/method.html
        // > Going to assume "static" is a magic keyword, based on https://github.com/phpDocumentor/phpDocumentor2/issues/822
        // > TODO: forbid in trait?
        // TODO: finish writing the regex.
        // Syntax:
        //    @method [return type] [name]([[type] [parameter]<, ...>]) [<description>]
        //    Assumes the parameters end at the first ")" after "("
        if (preg_match('/@method(\s+(static))?((\s+(' . UnionType::union_type_regex . '))?)\s+' . self::word_regex . '\s*\(([^()]*)\)\s*(.*)/', $line, $match)) {
            $is_static = $match[2] === 'static';
            $return_union_type_string = $match[4];
            if ($return_union_type_string !== '') {
                $return_union_type =
                    UnionType::fromStringInContext(
                        $return_union_type_string,
                        $context,
                        true
                    );
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
    private static function magicPropertyFromCommentLine(
        Context $context,
        string $line
    ) {
        // Note that the type of a property can be left out (@property $myVar) - This is equivalent to @property mixed $myVar
        // TODO: properly handle duplicates...
        // TODO: support read-only/write-only checks elsewhere in the codebase?
        if (preg_match('/@(property|property-read|property-write)(\s+' . UnionType::union_type_regex . ')?(\s+(\\$\S+))/', $line, $match)) {
            $type = ltrim($match[2] ?? '');

            $property_name =
                empty($match[29]) ? '' : trim($match[29], '$');
            if ($property_name === '') {
                return null;
            }

            // If the type looks like a property name, make it an
            // empty type so that other stuff can match it.
            $union_type = null;
            if (0 !== strpos($type, '$')) {
                $union_type =
                    UnionType::fromStringInContext(
                        $type,
                        $context,
                        true
                    );
            } else {
                $union_type = new UnionType();
            }

            return new CommentParameter(
                $property_name,
                $union_type
            );
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
    private static function getPhanClosureScopeFromCommentLine(
        Context $context,
        string $line
    ) : Option {
        $closure_scope_union_type_string = '';

        // https://secure.php.net/manual/en/closure.bindto.php
        // There wasn't anything in the phpdoc standard to indicate the class to which
        // a Closure would be bound with bind() or bindTo(), so using a custom tag.
        //
        // TODO: Also add a version which forbids using $this in the closure?
        if (preg_match('/@PhanClosureScope\s+(' . UnionType::union_type_regex . '+)/', $line, $match)) {
            $closure_scope_union_type_string = $match[1];
        }

        if ($closure_scope_union_type_string !== '') {
            return new Some(Type::fromStringInContext(
                $closure_scope_union_type_string,
                $context
            ));
        }
        return new None();
    }

    /**
     * @return bool
     * Set to true if the comment contains a 'deprecated'
     * directive.
     */
    public function isDeprecated() : bool
    {
        return ($this->comment_flags & Flags::IS_DEPRECATED) != 0;
    }

    /**
     * @return bool
     * Set to true if the comment contains a 'phan-forbid-undeclared-magic-properties'
     * directive.
     */
    public function getForbidUndeclaredMagicProperties() : bool
    {
        return ($this->comment_flags & Flags::CLASS_FORBID_UNDECLARED_MAGIC_PROPERTIES) != 0;
    }

    /**
     * @return bool
     * Set to true if the comment contains a 'phan-forbid-undeclared-magic-methods'
     * directive.
     */
    public function getForbidUndeclaredMagicMethods() : bool
    {
        return ($this->comment_flags & Flags::CLASS_FORBID_UNDECLARED_MAGIC_METHODS) != 0;
    }

    /**
     * @return UnionType
     * A UnionType defined by a (at)return directive
     */
    public function getReturnType() : UnionType
    {
        return $this->return_union_type;
    }

    /**
     * @return bool
     * True if this doc block contains a (at)return
     * directive specifying a type.
     */
    public function hasReturnUnionType() : bool
    {
        return !$this->return_union_type->isEmpty();
    }

    /**
     * @return Option<Type>
     * An optional Type defined by a (at)PhanClosureScope
     * directive specifying a single type.
     */
    public function getClosureScopeOption() : Option
    {
        return $this->closure_scope;
    }

    /**
     * @return CommentParameter[]
     *
     * @suppress PhanUnreferencedMethod
     */
    public function getParameterList() : array
    {
        return $this->parameter_list;
    }

    /**
     * @return TemplateType[]
     * A list of template types parameterizing a generic class
     */
    public function getTemplateTypeList() : array
    {
        return $this->template_type_list;
    }

    /**
     * @return Option<Type>
     * An optional type declaring what a class extends.
     */
    public function getInheritedTypeOption() : Option
    {
        return $this->inherited_type;
    }

    /**
     * @return string[]
     * A set of issue names like 'PhanUnreferencedMethod' to suppress
     */
    public function getSuppressIssueList() : array
    {
        return $this->suppress_issue_list;
    }

    /**
     * @return bool
     * True if we have a parameter at the given offset
     */
    public function hasParameterWithNameOrOffset(
        string $name,
        int $offset
    ) : bool {
        if (!empty($this->parameter_map[$name])) {
            return true;
        }

        return !empty($this->parameter_list[$offset]);
    }

    /**
     * @return CommentParameter
     * The paramter at the given offset
     */
    public function getParameterWithNameOrOffset(
        string $name,
        int $offset
    ) : CommentParameter {
        if (!empty($this->parameter_map[$name])) {
            return $this->parameter_map[$name];
        }

        return $this->parameter_list[$offset];
    }

    /**
     * @unused
     * @return bool
     * True if we have a magic property with the given name
     */
    public function hasMagicPropertyWithName(
        string $name
    ) : bool {
        return isset($this->magic_property_map[$name]);
    }

    /**
     * @unused
     * @return CommentParameter
     * The magic property with the given name. May or may not have a type.
     */
    public function getMagicPropertyWithName(
        string $name
    ) : CommentParameter {
        return $this->magic_property_map[$name];
    }

    /**
     * @return CommentParameter[] map from parameter name to parameter
     */
    public function getMagicPropertyMap() : array {
        return $this->magic_property_map;
    }

    /**
     * @return CommentMethod[] map from method name to method info
     */
    public function getMagicMethodMap() : array {
        return $this->magic_method_map;
    }

    /**
     * @return CommentParameter[]
     */
    public function getVariableList() : array
    {
        return $this->variable_list;
    }

    public function __toString() : string
    {
        // TODO: add new properties of Comment to this method
        // (magic methods, magic properties, custom @phan directives, etc.))
        $string = "/**\n";

        if (($this->comment_flags & Flags::IS_DEPRECATED) != 0) {
            $string  .= " * @deprecated\n";
        }

        foreach ($this->variable_list as $variable) {
            $string  .= " * @var $variable\n";
        }

        foreach ($this->parameter_list as $parameter) {
            $string  .= " * @param $parameter\n";
        }

        if ($this->return_union_type) {
            $string .= " * @return {$this->return_union_type}\n";
        }

        $string .= " */\n";

        return $string;
    }
}
