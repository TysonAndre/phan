<?php
declare(strict_types=1);
namespace Phan\Language\Element;

use Phan\Config;
use Phan\Language\Context;
use Phan\Language\Element\Comment\Parameter as CommentParameter;
use Phan\Language\Type;
use Phan\Language\Type\TemplateType;
use Phan\Language\UnionType;
use Phan\Library\None;
use Phan\Library\Option;
use Phan\Library\Some;

/**
 */
class Comment
{

    /**
     * @var bool
     * Set to true if the comment contains a 'deprecated'
     * directive.
     */
    private $is_deprecated = false;

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
     * A private constructor meant to ingest a parsed comment
     * docblock.
     *
     * @param bool $is_deprecated
     * Set to true if the comment contains a 'deprecated'
     * directive.
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
     * @param UnionType $return
     *
     * @param string[] $suppress_issue_list
     * A list of tags for error type to be suppressed
     */
    private function __construct(
        bool $is_deprecated,
        array $variable_list,
        array $parameter_list,
        array $template_type_list,
        Option $inherited_type,
        UnionType $return_union_type,
        array $suppress_issue_list,
        array $magic_property_list
    ) {
        $this->is_deprecated = $is_deprecated;
        $this->variable_list = $variable_list;
        $this->parameter_list = $parameter_list;
        $this->template_type_list = $template_type_list;
        $this->inherited_type = $inherited_type;
        $this->return_union_type = $return_union_type;
        $this->suppress_issue_list = $suppress_issue_list;

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
                false, [], [], [], new None, new UnionType(), [], []
            );
        }

        $is_deprecated = false;
        $variable_list = [];
        $parameter_list = [];
        $template_type_list = [];
        $inherited_type = new None;
        $return_union_type = new UnionType();
        $suppress_issue_list = [];
        $magic_property_list = [];

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
            }

            if (($pos=stripos($line, '@deprecated')) !== false) {
                if (preg_match('/@deprecated\b/', $line, $match)) {
                    $is_deprecated = true;
                }
            }
        }

        return new Comment(
            $is_deprecated,
            $variable_list,
            $parameter_list,
            $template_type_list,
            $inherited_type,
            $return_union_type,
            $suppress_issue_list,
            $magic_property_list
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

        $return_union_type = UnionType::fromStringInContext(
            $return_union_type_string,
            $context,
            true
        );

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
    private static function parameterFromCommentLine(
        Context $context,
        string $line,
        bool $is_var
    ) {
        $match = [];
        if (preg_match('/@(param|var)\s+(' . UnionType::union_type_regex . ')(\s+(\.\.\.)?\s*(\\$\S+))?/', $line, $match)) {
            $type = $match[2];

            $is_variadic = ($match[29] ?? '') === '...';

            if ($is_var && $is_variadic) {
                $variable_name = '';  // "@var int ...$x" is nonsense and invalid phpdoc.
            } else {
                $variable_name =
                    empty($match[30]) ? '' : trim($match[30], '$');
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
                empty($match[23]) ? '' : trim($match[23], '$');
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
                        $context
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
     * @return bool
     * Set to true if the comment contains a 'deprecated'
     * directive.
     */
    public function isDeprecated() : bool
    {
        return $this->is_deprecated;
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
        return !empty($this->return_union_type) && !$this->return_union_type->isEmpty();
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
    public function getInheritedTypeOption()
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
     * @return CommentParameter[]
     */
    public function getVariableList() : array
    {
        return $this->variable_list;
    }

    public function __toString() : string
    {
        $string = "/**\n";

        if ($this->is_deprecated) {
            $string  .= " * @deprecated\n";
        }

        foreach ($this->variable_list as $variable) {
            $string  .= " * @var $variable\n";
        }

        foreach ($this->parameter_list as $parameter) {
            $string  .= " * @var $parameter\n";
        }

        if ($this->return_union_type) {
            $string .= " * @return {$this->return_union_type}\n";
        }

        $string .= " */\n";

        return $string;
    }
}
