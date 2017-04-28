<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language;

use Phan\CodeBase;
use Phan\Config;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\BoolType;
use Phan\Language\Type\CallableType;
use Phan\Language\Type\FloatType;
use Phan\Language\Type\GenericArrayType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\IterableType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\ObjectType;
use Phan\Language\Type\ResourceType;
use Phan\Language\Type\StaticType;
use Phan\Language\Type\StringType;
use Phan\Language\Type\TemplateType;
use Phan\Language\Type\VoidType;
use Phan\Library\Tuple4;
class Type
{
    use \Phan\Memoize;
    /**
     * @var string
     * A legal type identifier (e.g. 'int' or 'DateTime')
     */
    const simple_type_regex = '(\\??)[a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*';
    /**
     * @var string
     * A regex matching template parameter types such
     * as '<int,DateTime|null,string>'
     */
    const template_parameter_type_list_regex = '<' . '(' . '(' . self::simple_type_regex . '(\\[\\])*' . ')' . '(' . '\\s*,\\s*' . '(' . self::simple_type_regex . '(\\[\\])*' . ')' . ')*' . ')' . '>';
    /**
     * @var string
     * A type with an optional template parameter list
     * such as 'Set<Datetime>', 'int' or 'Tuple2<int>'.
     */
    const simple_type_with_template_parameter_list_regex = '(' . self::simple_type_regex . ')' . '(' . self::template_parameter_type_list_regex . ')?';
    /**
     * @var string
     * A legal type identifier matching a type optionally with a []
     * indicating that it's a generic typed array (e.g. 'int[]',
     * 'string' or 'Set<DateTime>')
     * TODO: change the regex so that '@return $this' will work (Currently not parsed, has empty regex)
     */
    const type_regex = self::simple_type_with_template_parameter_list_regex . '(\\[\\])*';
    /**
     * @var bool[] - For checking if a string is an internal type.
     */
    const _internal_type_set = ['array' => true, 'bool' => true, 'callable' => true, 'float' => true, 'int' => true, 'iterable' => true, 'mixed' => true, 'null' => true, 'object' => true, 'resource' => true, 'static' => true, 'string' => true, 'void' => true];
    /**
     * @var string|null
     * The namespace of this type such as '\' or
     * '\Phan\Language'
     */
    protected $namespace = null;
    /**
     * @var string|null
     * The name of this type such as 'int' or 'MyClass'
     */
    protected $name = null;
    /**
     * @var UnionType[]
     * A possibly empty list of concrete types that
     * act as parameters to this type if it is a templated
     * type.
     */
    protected $template_parameter_type_list = [];
    /**
     * @var bool
     * True if this type is nullable, else false
     */
    protected $is_nullable = false;
    /**
     * @param string $name
     * The name of the type such as 'int' or 'MyClass'
     *
     * @param string $namespace
     * The (optional) namespace of the type such as '\'
     * or '\Phan\Language'.
     *
     * @param UnionType[] $template_parameter_type_list
     * A (possibly empty) list of template parameter types
     *
     * @param bool $is_nullable
     * True if this type can be null, false if it cannot
     * be null.
     */
    protected function __construct($namespace, $name, $template_parameter_type_list, $is_nullable)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to __construct() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to __construct() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to __construct() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        $this->namespace = $namespace;
        $this->name = $name;
        $this->template_parameter_type_list = $template_parameter_type_list;
        $this->is_nullable = $is_nullable;
    }
    /**
     * @param string $name
     * The name of the type such as 'int' or 'MyClass'
     *
     * @param string $namespace
     * The (optional) namespace of the type such as '\'
     * or '\Phan\Language'.
     *
     * @param UnionType[] $template_parameter_type_list
     * A (possibly empty) list of template parameter types
     *
     * @param bool $is_nullable
     * True if this type can be null, false if it cannot
     * be null.
     *
     * @param bool $is_phpdoc_type
     * True if $type_name was extracted from a doc comment.
     * (Outside of phpdoc, "integer" would be a class name)
     *
     * @return Type
     * A single canonical instance of the given type.
     */
    protected static function make($namespace, $type_name, $template_parameter_type_list, $is_nullable, $is_phpdoc_type)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to make() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($type_name)) {
            throw new \InvalidArgumentException("Argument \$type_name passed to make() must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
        }
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to make() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if (!is_bool($is_phpdoc_type)) {
            throw new \InvalidArgumentException("Argument \$is_phpdoc_type passed to make() must be of the type bool, " . (gettype($is_phpdoc_type) == "object" ? get_class($is_phpdoc_type) : gettype($is_phpdoc_type)) . " given");
        }
        $namespace = trim($namespace);
        if ('\\' === $namespace && $is_phpdoc_type) {
            $type_name = self::canonicalNameFromName($type_name);
        }
        // If this looks like a generic type string, explicitly
        // make it as such
        if (self::isGenericArrayString($type_name) && ($pos = strrpos($type_name, '[]')) !== false) {
            $ret5902c6fcd4c1f = GenericArrayType::fromElementType(Type::make($namespace, substr($type_name, 0, $pos), $template_parameter_type_list, false, $is_phpdoc_type), $is_nullable);
            if (!$ret5902c6fcd4c1f instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd4c1f) == "object" ? get_class($ret5902c6fcd4c1f) : gettype($ret5902c6fcd4c1f)) . " given");
            }
            return $ret5902c6fcd4c1f;
        }
        assert(!empty($namespace), "Namespace cannot be empty");
        assert('\\' === $namespace[0], "Namespace must be fully qualified");
        assert(!empty($type_name), "Type name cannot be empty");
        assert(false === strpos($type_name, '|'), "Type name may not contain a pipe.");
        // Create a canonical representation of the
        // namespace and name
        $namespace = $namespace ?: '\\';
        if ('\\' === $namespace && $is_phpdoc_type) {
            $type_name = self::canonicalNameFromName($type_name);
        }
        // Make sure we only ever create exactly one
        // object for any unique type
        $key = ($is_nullable ? '?' : '') . $namespace . '\\' . $type_name;
        if ($template_parameter_type_list) {
            $key .= '<' . implode(',', array_map(function (UnionType $union_type) {
                return (string) $union_type;
            }, $template_parameter_type_list)) . '>';
        }
        $key = strtolower($key);
        $ret5902c6fcd52c2 = static::cachedGetInstanceHelper($namespace, $type_name, $template_parameter_type_list, $is_nullable, $key, false);
        if (!$ret5902c6fcd52c2 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd52c2) == "object" ? get_class($ret5902c6fcd52c2) : gettype($ret5902c6fcd52c2)) . " given");
        }
        return $ret5902c6fcd52c2;
    }
    /**
     * @return static
     * @see static::__construct
     */
    protected static final function cachedGetInstanceHelper($namespace, $name, $template_parameter_type_list, $is_nullable, $key, $clear_all_memoize)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to cachedGetInstanceHelper() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to cachedGetInstanceHelper() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to cachedGetInstanceHelper() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Argument \$key passed to cachedGetInstanceHelper() must be of the type string, " . (gettype($key) == "object" ? get_class($key) : gettype($key)) . " given");
        }
        if (!is_bool($clear_all_memoize)) {
            throw new \InvalidArgumentException("Argument \$clear_all_memoize passed to cachedGetInstanceHelper() must be of the type bool, " . (gettype($clear_all_memoize) == "object" ? get_class($clear_all_memoize) : gettype($clear_all_memoize)) . " given");
        }
        // TODO: Figure out why putting this into a static variable results in test failures.
        static $canonical_object_map = [];
        if ($clear_all_memoize) {
            foreach ($canonical_object_map as $type) {
                $type->memoizeFlushAll();
            }
            $ret5902c6fcd5f80 = NullType::instance(false);
            if (!$ret5902c6fcd5f80 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd5f80) == "object" ? get_class($ret5902c6fcd5f80) : gettype($ret5902c6fcd5f80)) . " given");
            }
            return $ret5902c6fcd5f80;
            // dummy
        }
        $value = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$canonical_object_map[$key], @null);
        if (!$value) {
            $value = new static($namespace, $name, $template_parameter_type_list, $is_nullable);
            $canonical_object_map[$key] = $value;
        }
        $ret5902c6fcd6e2c = $value;
        if (!$ret5902c6fcd6e2c instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd6e2c) == "object" ? get_class($ret5902c6fcd6e2c) : gettype($ret5902c6fcd6e2c)) . " given");
        }
        return $ret5902c6fcd6e2c;
    }
    /**
     * Call this before forking and analysis phase, when in daemon mode.
     * This may hurt performance.
     *
     * It's important to clear asExpandedTypes(),
     * as the parent classes may have changed since the last parse attempt.
     *
     * @return void
     */
    public static function clearAllMemoizations()
    {
        // Clear anything that has memoized state
        Type::cachedGetInstanceHelper('', '', [], false, '', true);
        TemplateType::cachedGetInstanceHelper('', '', [], false, '', true);
        GenericArrayType::cachedGetInstanceHelper('', '', [], false, '', true);
    }
    /**
     * @param Type $type
     * The base type of this generic type referencing a
     * generic class
     *
     * @param UnionType[] $template_parameter_type_list
     * A map from a template type identifier to a
     * concrete union type
     */
    public static function fromType(Type $type, $template_parameter_type_list)
    {
        $ret5902c6fcd7f4e = self::make($type->getNamespace(), $type->getName(), $template_parameter_type_list, $type->getIsNullable(), false);
        if (!$ret5902c6fcd7f4e instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd7f4e) == "object" ? get_class($ret5902c6fcd7f4e) : gettype($ret5902c6fcd7f4e)) . " given");
        }
        return $ret5902c6fcd7f4e;
    }
    /**
     * @return Type
     * Get a type for the given object
     */
    public static function fromObject($object)
    {
        $ret5902c6fcd82a1 = Type::fromInternalTypeName(gettype($object), false, true);
        if (!$ret5902c6fcd82a1 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd82a1) == "object" ? get_class($ret5902c6fcd82a1) : gettype($ret5902c6fcd82a1)) . " given");
        }
        return $ret5902c6fcd82a1;
    }
    /**
     * @param string $type_name
     * The name of the internal type such as 'int'
     *
     * @param bool $is_nullable
     * Set to true if the type should be nullable, else pass
     * false
     *
     * @return Type
     * Get a type for the given type name
     */
    public static function fromInternalTypeName($type_name, $is_nullable, $is_phpdoc_type = false)
    {
        if (!is_string($type_name)) {
            throw new \InvalidArgumentException("Argument \$type_name passed to fromInternalTypeName() must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
        }
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to fromInternalTypeName() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if (!is_bool($is_phpdoc_type)) {
            throw new \InvalidArgumentException("Argument \$is_phpdoc_type passed to fromInternalTypeName() must be of the type bool, " . (gettype($is_phpdoc_type) == "object" ? get_class($is_phpdoc_type) : gettype($is_phpdoc_type)) . " given");
        }
        // If this is a generic type (like int[]), return
        // a generic of internal types.
        //
        // When there's a nullability operator such as in
        // `?int[]`, it applies to the array rather than
        // the int
        if (false !== ($pos = strrpos($type_name, '[]'))) {
            $ret5902c6fcd86f8 = GenericArrayType::fromElementType(self::fromInternalTypeName(substr($type_name, 0, $pos), false, $is_phpdoc_type), $is_nullable);
            if (!$ret5902c6fcd86f8 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd86f8) == "object" ? get_class($ret5902c6fcd86f8) : gettype($ret5902c6fcd86f8)) . " given");
            }
            return $ret5902c6fcd86f8;
        }
        $type_name = self::canonicalNameFromName($type_name);
        switch (strtolower($type_name)) {
            case 'array':
                $ret5902c6fcd8a41 = ArrayType::instance($is_nullable);
                if (!$ret5902c6fcd8a41 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd8a41) == "object" ? get_class($ret5902c6fcd8a41) : gettype($ret5902c6fcd8a41)) . " given");
                }
                return $ret5902c6fcd8a41;
            case 'bool':
                $ret5902c6fcd8d1c = BoolType::instance($is_nullable);
                if (!$ret5902c6fcd8d1c instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd8d1c) == "object" ? get_class($ret5902c6fcd8d1c) : gettype($ret5902c6fcd8d1c)) . " given");
                }
                return $ret5902c6fcd8d1c;
            case 'callable':
                $ret5902c6fcd8ff8 = CallableType::instance($is_nullable);
                if (!$ret5902c6fcd8ff8 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd8ff8) == "object" ? get_class($ret5902c6fcd8ff8) : gettype($ret5902c6fcd8ff8)) . " given");
                }
                return $ret5902c6fcd8ff8;
            case 'float':
                $ret5902c6fcd92d1 = FloatType::instance($is_nullable);
                if (!$ret5902c6fcd92d1 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd92d1) == "object" ? get_class($ret5902c6fcd92d1) : gettype($ret5902c6fcd92d1)) . " given");
                }
                return $ret5902c6fcd92d1;
            case 'int':
                $ret5902c6fcd95a7 = IntType::instance($is_nullable);
                if (!$ret5902c6fcd95a7 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd95a7) == "object" ? get_class($ret5902c6fcd95a7) : gettype($ret5902c6fcd95a7)) . " given");
                }
                return $ret5902c6fcd95a7;
            case 'mixed':
                $ret5902c6fcd98b9 = MixedType::instance($is_nullable);
                if (!$ret5902c6fcd98b9 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd98b9) == "object" ? get_class($ret5902c6fcd98b9) : gettype($ret5902c6fcd98b9)) . " given");
                }
                return $ret5902c6fcd98b9;
            case 'null':
                $ret5902c6fcd9b96 = NullType::instance($is_nullable);
                if (!$ret5902c6fcd9b96 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd9b96) == "object" ? get_class($ret5902c6fcd9b96) : gettype($ret5902c6fcd9b96)) . " given");
                }
                return $ret5902c6fcd9b96;
            case 'object':
                $ret5902c6fcd9e6a = ObjectType::instance($is_nullable);
                if (!$ret5902c6fcd9e6a instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcd9e6a) == "object" ? get_class($ret5902c6fcd9e6a) : gettype($ret5902c6fcd9e6a)) . " given");
                }
                return $ret5902c6fcd9e6a;
            case 'resource':
                $ret5902c6fcda14b = ResourceType::instance($is_nullable);
                if (!$ret5902c6fcda14b instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcda14b) == "object" ? get_class($ret5902c6fcda14b) : gettype($ret5902c6fcda14b)) . " given");
                }
                return $ret5902c6fcda14b;
            case 'string':
                $ret5902c6fcda421 = StringType::instance($is_nullable);
                if (!$ret5902c6fcda421 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcda421) == "object" ? get_class($ret5902c6fcda421) : gettype($ret5902c6fcda421)) . " given");
                }
                return $ret5902c6fcda421;
            case 'void':
                $ret5902c6fcda739 = VoidType::instance($is_nullable);
                if (!$ret5902c6fcda739 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcda739) == "object" ? get_class($ret5902c6fcda739) : gettype($ret5902c6fcda739)) . " given");
                }
                return $ret5902c6fcda739;
            case 'iterable':
                $ret5902c6fcdaa59 = IterableType::instance($is_nullable);
                if (!$ret5902c6fcdaa59 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdaa59) == "object" ? get_class($ret5902c6fcdaa59) : gettype($ret5902c6fcdaa59)) . " given");
                }
                return $ret5902c6fcdaa59;
            case 'static':
                $ret5902c6fcdad30 = StaticType::instance($is_nullable);
                if (!$ret5902c6fcdad30 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdad30) == "object" ? get_class($ret5902c6fcdad30) : gettype($ret5902c6fcdad30)) . " given");
                }
                return $ret5902c6fcdad30;
        }
        assert(false, "No internal type with name {$type_name}");
    }
    /**
     * @param string $namespace
     * A fully qualified namespace
     *
     * @param string $type_name
     * The name of the type
     *
     * @return Type
     * A type representing the given namespace and type
     * name.
     *
     * @param bool $is_nullable
     * True if this type can be null, false if it cannot
     * be null.
     */
    public static function fromNamespaceAndName($namespace, $type_name, $is_nullable)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to fromNamespaceAndName() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        if (!is_string($type_name)) {
            throw new \InvalidArgumentException("Argument \$type_name passed to fromNamespaceAndName() must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
        }
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to fromNamespaceAndName() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        $ret5902c6fcdb7e2 = self::make($namespace, $type_name, [], $is_nullable, false);
        if (!$ret5902c6fcdb7e2 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdb7e2) == "object" ? get_class($ret5902c6fcdb7e2) : gettype($ret5902c6fcdb7e2)) . " given");
        }
        return $ret5902c6fcdb7e2;
    }
    public static function fromReflectionType(\ReflectionType $reflection_type)
    {
        $ret5902c6fcdc1fa = self::fromStringInContext((string) $reflection_type, new Context(), false);
        if (!$ret5902c6fcdc1fa instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdc1fa) == "object" ? get_class($ret5902c6fcdc1fa) : gettype($ret5902c6fcdc1fa)) . " given");
        }
        return $ret5902c6fcdc1fa;
    }
    /**
     * @param string $fully_qualified_string
     * A fully qualified type name
     *
     * @param Context $context
     * The context in which the type string was
     * found
     *
     * @return Type
     */
    public static function fromFullyQualifiedString($fully_qualified_string)
    {
        if (!is_string($fully_qualified_string)) {
            throw new \InvalidArgumentException("Argument \$fully_qualified_string passed to fromFullyQualifiedString() must be of the type string, " . (gettype($fully_qualified_string) == "object" ? get_class($fully_qualified_string) : gettype($fully_qualified_string)) . " given");
        }
        assert(!empty($fully_qualified_string), "Type cannot be empty");
        $tuple = self::typeStringComponents($fully_qualified_string);
        $namespace = $tuple->_0;
        $type_name = $tuple->_1;
        $template_parameter_type_name_list = $tuple->_2;
        $is_nullable = $tuple->_3;
        if (empty($namespace)) {
            $ret5902c6fcdc676 = self::fromInternalTypeName($fully_qualified_string, $is_nullable, false);
            if (!$ret5902c6fcdc676 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdc676) == "object" ? get_class($ret5902c6fcdc676) : gettype($ret5902c6fcdc676)) . " given");
            }
            return $ret5902c6fcdc676;
        }
        // Map the names of the types to actual types in the
        // template parameter type list
        $template_parameter_type_list = array_map(function ($type_name) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            return Type::fromFullyQualifiedString($type_name)->asUnionType();
        }, $template_parameter_type_name_list);
        if (0 !== strpos($namespace, '\\')) {
            $namespace = '\\' . $namespace;
        }
        assert(!empty($namespace) && !empty($type_name), "Type was not fully qualified");
        $ret5902c6fcdcd61 = self::make($namespace, $type_name, $template_parameter_type_list, $is_nullable, false);
        if (!$ret5902c6fcdcd61 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdcd61) == "object" ? get_class($ret5902c6fcdcd61) : gettype($ret5902c6fcdcd61)) . " given");
        }
        return $ret5902c6fcdcd61;
    }
    /**
     * @param string $string
     * A string representing a type
     *
     * @param Context $context
     * The context in which the type string was
     * found
     *
     * @param bool $is_phpdoc_type
     * True if $string was extracted from a doc comment.
     *
     * @return Type
     * Parse a type from the given string
     */
    public static function fromStringInContext($string, Context $context, $is_phpdoc_type = false)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException("Argument \$string passed to fromStringInContext() must be of the type string, " . (gettype($string) == "object" ? get_class($string) : gettype($string)) . " given");
        }
        if (!is_bool($is_phpdoc_type)) {
            throw new \InvalidArgumentException("Argument \$is_phpdoc_type passed to fromStringInContext() must be of the type bool, " . (gettype($is_phpdoc_type) == "object" ? get_class($is_phpdoc_type) : gettype($is_phpdoc_type)) . " given");
        }
        assert($string !== '', "Type cannot be empty");
        // Extract the namespace, type and parameter type name list
        $tuple = self::typeStringComponents($string);
        $namespace = $tuple->_0;
        $type_name = $tuple->_1;
        $template_parameter_type_name_list = $tuple->_2;
        $is_nullable = $tuple->_3;
        // Map the names of the types to actual types in the
        // template parameter type list
        $template_parameter_type_list = array_map(function ($type_name) use($context, $is_phpdoc_type) {
            if (!is_string($type_name)) {
                throw new \InvalidArgumentException("Argument \$type_name passed to () must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
            }
            return Type::fromStringInContext($type_name, $context, $is_phpdoc_type)->asUnionType();
        }, $template_parameter_type_name_list);
        // @var bool
        // True if this type name if of the form 'C[]'
        $is_generic_array_type = self::isGenericArrayString($type_name);
        // If this is a generic array type, get the name of
        // the type of each element
        $non_generic_array_type_name = $type_name;
        if ($is_generic_array_type && false !== ($pos = strrpos($type_name, '[]'))) {
            $non_generic_array_type_name = substr($type_name, 0, $pos);
        }
        // Check to see if the type name is mapped via
        // a using clause.
        //
        // Gotta check this before checking for native types
        // because there are monsters out there that will
        // remap the names via things like `use \Foo\String`.
        $non_generic_partially_qualified_array_type_name = $non_generic_array_type_name;
        if ($namespace) {
            $non_generic_partially_qualified_array_type_name = $namespace . '\\' . $non_generic_partially_qualified_array_type_name;
        }
        if ($context->hasNamespaceMapFor(\ast\flags\USE_NORMAL, $non_generic_partially_qualified_array_type_name)) {
            $fqsen = $context->getNamespaceMapFor(\ast\flags\USE_NORMAL, $non_generic_partially_qualified_array_type_name);
            if ($is_generic_array_type) {
                $ret5902c6fcdd998 = GenericArrayType::fromElementType(Type::make($fqsen->getNamespace(), $fqsen->getName(), $template_parameter_type_list, false, $is_phpdoc_type), $is_nullable);
                if (!$ret5902c6fcdd998 instanceof Type) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdd998) == "object" ? get_class($ret5902c6fcdd998) : gettype($ret5902c6fcdd998)) . " given");
                }
                return $ret5902c6fcdd998;
            }
            $ret5902c6fcddccc = Type::make($fqsen->getNamespace(), $fqsen->getName(), $template_parameter_type_list, $is_nullable, $is_phpdoc_type);
            if (!$ret5902c6fcddccc instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcddccc) == "object" ? get_class($ret5902c6fcddccc) : gettype($ret5902c6fcddccc)) . " given");
            }
            return $ret5902c6fcddccc;
        }
        // If this was a fully qualified type, we're all
        // set
        if (!empty($namespace) && 0 === strpos($namespace, '\\')) {
            $ret5902c6fcde04f = self::make($namespace, $type_name, $template_parameter_type_list, $is_nullable, $is_phpdoc_type);
            if (!$ret5902c6fcde04f instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcde04f) == "object" ? get_class($ret5902c6fcde04f) : gettype($ret5902c6fcde04f)) . " given");
            }
            return $ret5902c6fcde04f;
        }
        if (self::isInternalTypeString($type_name, $is_phpdoc_type)) {
            $ret5902c6fcde383 = self::fromInternalTypeName($type_name, $is_nullable, $is_phpdoc_type);
            if (!$ret5902c6fcde383 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcde383) == "object" ? get_class($ret5902c6fcde383) : gettype($ret5902c6fcde383)) . " given");
            }
            return $ret5902c6fcde383;
        }
        if ($is_phpdoc_type && ($namespace ?: '\\') === '\\') {
            $type_name = self::canonicalNameFromName($type_name);
        }
        // Things like `self[]` or `$this[]`
        if ($is_generic_array_type && self::isSelfTypeString($non_generic_array_type_name) && $context->isInClassScope()) {
            // Callers of this method should be checking on their own
            // to see if this type is a reference to 'parent' and
            // dealing with it there. We don't want to have this
            // method be dependent on the code base
            assert('parent' !== $non_generic_array_type_name, __METHOD__ . " does not know how to handle the type name 'parent'");
            $ret5902c6fcde7b9 = GenericArrayType::fromElementType(static::fromFullyQualifiedString((string) $context->getClassFQSEN()), $is_nullable);
            if (!$ret5902c6fcde7b9 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcde7b9) == "object" ? get_class($ret5902c6fcde7b9) : gettype($ret5902c6fcde7b9)) . " given");
            }
            return $ret5902c6fcde7b9;
        }
        // If this is a type referencing the current class
        // in scope such as 'self' or 'static', return that.
        if (self::isSelfTypeString($type_name) && $context->isInClassScope()) {
            // Callers of this method should be checking on their own
            // to see if this type is a reference to 'parent' and
            // dealing with it there. We don't want to have this
            // method be dependent on the code base
            assert('parent' !== $type_name, __METHOD__ . " does not know how to handle the type name 'parent'");
            $ret5902c6fcdeb64 = static::fromFullyQualifiedString((string) $context->getClassFQSEN());
            if (!$ret5902c6fcdeb64 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdeb64) == "object" ? get_class($ret5902c6fcdeb64) : gettype($ret5902c6fcdeb64)) . " given");
            }
            return $ret5902c6fcdeb64;
        }
        // Merge the current namespace with the given relative
        // namespace
        if (!empty($context->getNamespace()) && !empty($namespace)) {
            $namespace = $context->getNamespace() . '\\' . $namespace;
        } else {
            if (!empty($context->getNamespace())) {
                $namespace = $context->getNamespace();
            } else {
                $namespace = '\\' . $namespace;
            }
        }
        $ret5902c6fcdefb6 = self::make($namespace, $type_name, $template_parameter_type_list, $is_nullable, $is_phpdoc_type);
        if (!$ret5902c6fcdefb6 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fcdefb6) == "object" ? get_class($ret5902c6fcdefb6) : gettype($ret5902c6fcdefb6)) . " given");
        }
        return $ret5902c6fcdefb6;
    }
    /**
     * @return UnionType
     * A UnionType representing this and only this type
     */
    public function asUnionType()
    {
        $ret5902c6fcdf8d5 = new UnionType([$this]);
        if (!$ret5902c6fcdf8d5 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fcdf8d5) == "object" ? get_class($ret5902c6fcdf8d5) : gettype($ret5902c6fcdf8d5)) . " given");
        }
        return $ret5902c6fcdf8d5;
    }
    /**
     * @return FQSEN
     * A fully-qualified structural element name derived
     * from this type
     */
    public function asFQSEN()
    {
        $ret5902c6fcdfbef = FullyQualifiedClassName::fromType($this);
        if (!$ret5902c6fcdfbef instanceof FQSEN) {
            throw new \InvalidArgumentException("Argument returned must be of the type FQSEN, " . (gettype($ret5902c6fcdfbef) == "object" ? get_class($ret5902c6fcdfbef) : gettype($ret5902c6fcdfbef)) . " given");
        }
        return $ret5902c6fcdfbef;
    }
    /**
     * @return string
     * The name associated with this type
     */
    public function getName()
    {
        $ret5902c6fcdfedd = $this->name;
        if (!is_string($ret5902c6fcdfedd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fcdfedd) . " given");
        }
        return $ret5902c6fcdfedd;
    }
    /**
     * @return bool
     * True if this namespace is defined
     */
    public function hasNamespace()
    {
        $ret5902c6fce013f = !empty($this->namespace);
        if (!is_bool($ret5902c6fce013f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce013f) . " given");
        }
        return $ret5902c6fce013f;
    }
    /**
     * @return string
     * The namespace associated with this type
     */
    public function getNamespace()
    {
        $ret5902c6fce03be = $this->namespace;
        if (!is_string($ret5902c6fce03be)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fce03be) . " given");
        }
        return $ret5902c6fce03be;
    }
    /**
     *
     */
    public function getIsNullable()
    {
        $ret5902c6fce0614 = $this->is_nullable;
        if (!is_bool($ret5902c6fce0614)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce0614) . " given");
        }
        return $ret5902c6fce0614;
    }
    /**
     * @param bool $is_nullable
     * Set to true if the type should be nullable, else pass
     * false
     *
     * @return Type
     * A new type that is a copy of this type but with the
     * given nullability value.
     */
    public function withIsNullable($is_nullable)
    {
        if (!is_bool($is_nullable)) {
            throw new \InvalidArgumentException("Argument \$is_nullable passed to withIsNullable() must be of the type bool, " . (gettype($is_nullable) == "object" ? get_class($is_nullable) : gettype($is_nullable)) . " given");
        }
        if ($is_nullable === $this->is_nullable) {
            $ret5902c6fce08a0 = $this;
            if (!$ret5902c6fce08a0 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fce08a0) == "object" ? get_class($ret5902c6fce08a0) : gettype($ret5902c6fce08a0)) . " given");
            }
            return $ret5902c6fce08a0;
        }
        $ret5902c6fce0be0 = static::make($this->getNamespace(), $this->getName(), $this->getTemplateParameterTypeList(), $is_nullable, false);
        if (!$ret5902c6fce0be0 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fce0be0) == "object" ? get_class($ret5902c6fce0be0) : gettype($ret5902c6fce0be0)) . " given");
        }
        return $ret5902c6fce0be0;
    }
    /**
     * @return bool
     * True if this is a native type (like int, string, etc.)
     *
     */
    public function isNativeType()
    {
        $ret5902c6fce1106 = false;
        if (!is_bool($ret5902c6fce1106)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce1106) . " given");
        }
        return $ret5902c6fce1106;
    }
    /**
     * @return bool
     * True if this is a native type or an array of native types
     * (like int, string, bool[], etc.),
     *
     * @see \Phan\Deprecated\Util::is_native_type
     * Formerly `function is_native_type`
     */
    private static function isInternalTypeString($type_name, $is_phpdoc_type)
    {
        if (!is_string($type_name)) {
            throw new \InvalidArgumentException("Argument \$type_name passed to isInternalTypeString() must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
        }
        if (!is_bool($is_phpdoc_type)) {
            throw new \InvalidArgumentException("Argument \$is_phpdoc_type passed to isInternalTypeString() must be of the type bool, " . (gettype($is_phpdoc_type) == "object" ? get_class($is_phpdoc_type) : gettype($is_phpdoc_type)) . " given");
        }
        $type_name = str_replace('[]', '', strtolower($type_name));
        if ($is_phpdoc_type) {
            $type_name = self::canonicalNameFromName($type_name);
            // Have to convert boolean[] to bool
        }
        $ret5902c6fce1468 = array_key_exists($type_name, self::_internal_type_set);
        if (!is_bool($ret5902c6fce1468)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce1468) . " given");
        }
        return $ret5902c6fce1468;
    }
    /**
     * @return bool
     * True if this type is a type referencing the
     * class context in which it exists such as 'static'
     * or 'self'.
     */
    public function isSelfType()
    {
        $ret5902c6fce1b7a = self::isSelfTypeString((string) $this);
        if (!is_bool($ret5902c6fce1b7a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce1b7a) . " given");
        }
        return $ret5902c6fce1b7a;
    }
    /**
     * @return bool
     * True if this type is a type referencing the
     * class context 'static'.
     */
    public function isStaticType()
    {
        $ret5902c6fce1e2a = 'static' === strtolower(ltrim((string) $this, '\\'));
        if (!is_bool($ret5902c6fce1e2a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce1e2a) . " given");
        }
        return $ret5902c6fce1e2a;
    }
    /**
     * @param string $type_string
     * A string defining a type such as 'self' or 'int'.
     *
     * @return bool
     * True if the given type references the class context
     * in which it exists such as 'self' or 'parent'
     */
    public static function isSelfTypeString($type_string)
    {
        if (!is_string($type_string)) {
            throw new \InvalidArgumentException("Argument \$type_string passed to isSelfTypeString() must be of the type string, " . (gettype($type_string) == "object" ? get_class($type_string) : gettype($type_string)) . " given");
        }
        $ret5902c6fce20c4 = preg_match('/^\\\\?([sS][eE][lL][fF]|[pP][aA][rR][eE][nN][tT]|\\$this)$/', $type_string) > 0;
        if (!is_bool($ret5902c6fce20c4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce20c4) . " given");
        }
        return $ret5902c6fce20c4;
    }
    /**
     * @param string $type_string
     * A string defining a type such as 'static' or 'int'.
     *
     * @return bool
     * True if the given type references the class context
     * in which it exists is '$this' or 'static'
     */
    public static function isStaticTypeString($type_string)
    {
        if (!is_string($type_string)) {
            throw new \InvalidArgumentException("Argument \$type_string passed to isStaticTypeString() must be of the type string, " . (gettype($type_string) == "object" ? get_class($type_string) : gettype($type_string)) . " given");
        }
        $ret5902c6fce25ab = preg_match('/^\\\\?([sS][tT][aA][tT][iI][cC]|\\$this)$/', $type_string) > 0;
        if (!is_bool($ret5902c6fce25ab)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce25ab) . " given");
        }
        return $ret5902c6fce25ab;
    }
    /**
     * @return bool
     * True if all types in this union are scalars
     *
     * @see \Phan\Deprecated\Util::type_scalar
     * Formerly `function type_scalar`
     */
    public function isScalar()
    {
        $ret5902c6fce2a4c = false;
        if (!is_bool($ret5902c6fce2a4c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce2a4c) . " given");
        }
        return $ret5902c6fce2a4c;
        // Overridden in subclass ScalarType
    }
    /**
     * @return bool
     * True if this type is iterable.
     */
    public function isIterable()
    {
        $ret5902c6fce2cdf = false;
        if (!is_bool($ret5902c6fce2cdf)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce2cdf) . " given");
        }
        return $ret5902c6fce2cdf;
        // Overridden in subclass IterableType (with subclass ArrayType)
    }
    /**
     * @return bool
     * True if this type is array-like (is of type array, is
     * a generic array, or implements ArrayAccess).
     */
    public function isArrayLike()
    {
        $ret5902c6fce2f74 = $this->isIterable() || $this->isGenericArray() || $this->isArrayAccess();
        if (!is_bool($ret5902c6fce2f74)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce2f74) . " given");
        }
        return $ret5902c6fce2f74;
    }
    /**
     * @return bool
     * True if this is a generic type such as 'int[]' or
     * 'string[]'.
     */
    public function isGenericArray()
    {
        $ret5902c6fce31d4 = false;
        if (!is_bool($ret5902c6fce31d4)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce31d4) . " given");
        }
        return $ret5902c6fce31d4;
        // Overridden in GenericArrayType
    }
    /**
     * @return bool - Returns true if this is \ArrayAccess (nullable or not)
     */
    public function isArrayAccess()
    {
        $ret5902c6fce34a2 = strcasecmp($this->getName(), 'ArrayAccess') === 0 && $this->getNamespace() === '\\';
        if (!is_bool($ret5902c6fce34a2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce34a2) . " given");
        }
        return $ret5902c6fce34a2;
    }
    /**
     * @param string $type_name
     * A non-namespaced type name like 'int[]'
     *
     * @return bool
     * True if this is a generic type such as 'int[]' or
     * 'string[]'.
     */
    private static function isGenericArrayString($type_name)
    {
        if (!is_string($type_name)) {
            throw new \InvalidArgumentException("Argument \$type_name passed to isGenericArrayString() must be of the type string, " . (gettype($type_name) == "object" ? get_class($type_name) : gettype($type_name)) . " given");
        }
        if (strrpos($type_name, '[]') !== false) {
            $ret5902c6fce3764 = $type_name !== '[]';
            if (!is_bool($ret5902c6fce3764)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce3764) . " given");
            }
            return $ret5902c6fce3764;
        }
        $ret5902c6fce39eb = false;
        if (!is_bool($ret5902c6fce39eb)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce39eb) . " given");
        }
        return $ret5902c6fce39eb;
    }
    /**
     * @return Type
     * A variation of this type that is not generic.
     * i.e. 'int[]' becomes 'int'.
     */
    public function genericArrayElementType()
    {
        assert($this->isGenericArray(), "Cannot call genericArrayElementType on non-generic array");
        if (($pos = strrpos($this->getName(), '[]')) !== false) {
            assert($this->getName() !== '[]' && $this->getName() !== 'array', "Non-generic type requested to be non-generic");
            $ret5902c6fce4070 = Type::make($this->getNamespace(), substr($this->getName(), 0, $pos), $this->template_parameter_type_list, $this->getIsNullable(), false);
            if (!$ret5902c6fce4070 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fce4070) == "object" ? get_class($ret5902c6fce4070) : gettype($ret5902c6fce4070)) . " given");
            }
            return $ret5902c6fce4070;
        }
        $ret5902c6fce4339 = $this;
        if (!$ret5902c6fce4339 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fce4339) == "object" ? get_class($ret5902c6fce4339) : gettype($ret5902c6fce4339)) . " given");
        }
        return $ret5902c6fce4339;
    }
    /**
     * @return Type
     * Get a new type which is the generic array version of
     * this type. For instance, 'int' will produce 'int[]'.
     */
    public function asGenericArrayType()
    {
        if (!$this instanceof GenericArrayType && ($this->getName() == 'array' || $this->getName() == 'mixed')) {
            $ret5902c6fce46a5 = ArrayType::instance(false);
            if (!$ret5902c6fce46a5 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fce46a5) == "object" ? get_class($ret5902c6fce46a5) : gettype($ret5902c6fce46a5)) . " given");
            }
            return $ret5902c6fce46a5;
        }
        $ret5902c6fce49a0 = GenericArrayType::fromElementType($this, false);
        if (!$ret5902c6fce49a0 instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6fce49a0) == "object" ? get_class($ret5902c6fce49a0) : gettype($ret5902c6fce49a0)) . " given");
        }
        return $ret5902c6fce49a0;
    }
    /**
     * @return bool
     * True if this type has any template parameter types
     */
    public function hasTemplateParameterTypes()
    {
        $ret5902c6fce4c80 = !empty($this->template_parameter_type_list);
        if (!is_bool($ret5902c6fce4c80)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce4c80) . " given");
        }
        return $ret5902c6fce4c80;
    }
    /**
     * @return UnionType[]
     * The set of types filling in template parameter types defined
     * on the class specified by this type.
     */
    public function getTemplateParameterTypeList()
    {
        return $this->template_parameter_type_list;
    }
    /**
     * @param CodeBase $code_base
     * The code base to look up classes against
     *
     * @return UnionType[]
     * A map from template type identifier to a concrete type
     */
    public function getTemplateParameterTypeMap(CodeBase $code_base)
    {
        return $this->memoize(__METHOD__, function () use($code_base) {
            $fqsen = $this->asFQSEN();
            if (!$fqsen instanceof FullyQualifiedClassName) {
                return [];
            }
            assert($fqsen instanceof FullyQualifiedClassName);
            if (!$code_base->hasClassWithFQSEN($fqsen)) {
                return [];
            }
            $class = $code_base->getClassByFQSEN($fqsen);
            $class_template_type_list = $class->getTemplateTypeMap();
            $template_parameter_type_list = $this->getTemplateParameterTypeList();
            $map = [];
            foreach (array_keys($class->getTemplateTypeMap()) as $i => $identifier) {
                if (isset($template_parameter_type_list[$i])) {
                    $map[$identifier] = $template_parameter_type_list[$i];
                }
            }
            return $map;
        });
    }
    /**
     * @param CodeBase
     * The code base to use in order to find super classes, etc.
     *
     * @param $recursion_depth
     * This thing has a tendency to run-away on me. This tracks
     * how bad I messed up by seeing how far the expanded types
     * go
     *
     * @return UnionType
     * Expands class types to all inherited classes returning
     * a superset of this type.
     */
    public function asExpandedTypes(CodeBase $code_base, $recursion_depth = 0)
    {
        if (!is_int($recursion_depth)) {
            throw new \InvalidArgumentException("Argument \$recursion_depth passed to asExpandedTypes() must be of the type int, " . (gettype($recursion_depth) == "object" ? get_class($recursion_depth) : gettype($recursion_depth)) . " given");
        }
        // We're going to assume that if the type hierarchy
        // is taller than some value we probably messed up
        // and should bail out.
        assert($recursion_depth < 20, "Recursion has gotten out of hand");
        if ($this->isNativeType() && !$this->isGenericArray()) {
            $ret5902c6fce52cd = $this->asUnionType();
            if (!$ret5902c6fce52cd instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fce52cd) == "object" ? get_class($ret5902c6fce52cd) : gettype($ret5902c6fce52cd)) . " given");
            }
            return $ret5902c6fce52cd;
        }
        $union_type = $this->asUnionType();
        $class_fqsen = $this->isGenericArray() ? $this->genericArrayElementType()->asFQSEN() : $this->asFQSEN();
        if (!$class_fqsen instanceof FullyQualifiedClassName) {
            $ret5902c6fce562c = $union_type;
            if (!$ret5902c6fce562c instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fce562c) == "object" ? get_class($ret5902c6fce562c) : gettype($ret5902c6fce562c)) . " given");
            }
            return $ret5902c6fce562c;
        }
        assert($class_fqsen instanceof FullyQualifiedClassName);
        if (!$code_base->hasClassWithFQSEN($class_fqsen)) {
            $ret5902c6fce5958 = $union_type;
            if (!$ret5902c6fce5958 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fce5958) == "object" ? get_class($ret5902c6fce5958) : gettype($ret5902c6fce5958)) . " given");
            }
            return $ret5902c6fce5958;
        }
        $clazz = $code_base->getClassByFQSEN($class_fqsen);
        $union_type->addUnionType($this->isGenericArray() ? $clazz->getUnionType()->asGenericArrayTypes() : $clazz->getUnionType());
        // Resurse up the tree to include all types
        $recursive_union_type = new UnionType();
        foreach ($union_type->getTypeSet() as $clazz_type) {
            if ((string) $clazz_type != (string) $this) {
                $recursive_union_type->addUnionType($clazz_type->asExpandedTypes($code_base, $recursion_depth + 1));
            } else {
                $recursive_union_type->addType($clazz_type);
            }
        }
        $ret5902c6fce5da1 = $recursive_union_type;
        if (!$ret5902c6fce5da1 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6fce5da1) == "object" ? get_class($ret5902c6fce5da1) : gettype($ret5902c6fce5da1)) . " given");
        }
        return $ret5902c6fce5da1;
    }
    /**
     * @param CodeBase $code_base
     *
     * @param Type $parent
     *
     * @return bool
     * True if this type represents a class which is a sub-type of
     * the class represented by the passed type.
     */
    public function isSubclassOf(CodeBase $code_base, Type $parent)
    {
        $fqsen = $this->asFQSEN();
        assert($fqsen instanceof FullyQualifiedClassName);
        $this_clazz = $code_base->getClassByFQSEN($fqsen);
        $parent_fqsen = $parent->asFQSEN();
        assert($parent_fqsen instanceof FullyQualifiedClassName);
        $parent_clazz = $code_base->getClassByFQSEN($parent_fqsen);
        $ret5902c6fce6419 = $this_clazz->isSubclassOf($code_base, $parent_clazz);
        if (!is_bool($ret5902c6fce6419)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce6419) . " given");
        }
        return $ret5902c6fce6419;
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    public function canCastToType(Type $type)
    {
        // Check to see if we have an exact object match
        if ($this === $type) {
            $ret5902c6fce66ad = true;
            if (!is_bool($ret5902c6fce66ad)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce66ad) . " given");
            }
            return $ret5902c6fce66ad;
        }
        if ($type instanceof MixedType) {
            $ret5902c6fce691a = true;
            if (!is_bool($ret5902c6fce691a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce691a) . " given");
            }
            return $ret5902c6fce691a;
        }
        // A nullable type cannot cast to a non-nullable type
        if ($this->getIsNullable() && !$type->getIsNullable()) {
            // If this is nullable, but that isn't, and we've
            // configured nulls to cast as anything, ignore
            // the nullable part.
            if (Config::get()->null_casts_as_any_type) {
                $ret5902c6fce6bf4 = $this->withIsNullable(false)->canCastToType($type);
                if (!is_bool($ret5902c6fce6bf4)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce6bf4) . " given");
                }
                return $ret5902c6fce6bf4;
            }
            $ret5902c6fce6e63 = false;
            if (!is_bool($ret5902c6fce6e63)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce6e63) . " given");
            }
            return $ret5902c6fce6e63;
        }
        // Get a non-null version of the type we're comparing
        // against.
        if ($type->getIsNullable()) {
            $type = $type->withIsNullable(false);
            // Check one more time to see if the types are equal
            if ($this === $type) {
                $ret5902c6fce7124 = true;
                if (!is_bool($ret5902c6fce7124)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce7124) . " given");
                }
                return $ret5902c6fce7124;
            }
        }
        $ret5902c6fce738a = $this->canCastToNonNullableType($type);
        if (!is_bool($ret5902c6fce738a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce738a) . " given");
        }
        return $ret5902c6fce738a;
    }
    /**
     * @param Type $type
     * A Type which is not nullable. This constraint is not
     * enforced, so be careful.
     *
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type)
    {
        // can't cast native types (includes iterable or array) to object. ObjectType overrides this function.
        if ($type instanceof ObjectType && !$this->isNativeType()) {
            $ret5902c6fce7639 = true;
            if (!is_bool($ret5902c6fce7639)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce7639) . " given");
            }
            return $ret5902c6fce7639;
        }
        if ($type instanceof MixedType) {
            $ret5902c6fce78ce = true;
            if (!is_bool($ret5902c6fce78ce)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce78ce) . " given");
            }
            return $ret5902c6fce78ce;
        }
        // A matrix of allowable type conversions
        static $matrix = ['\\Traversable' => ['iterable' => true], '\\Closure' => ['callable' => true]];
        $ret5902c6fce7c01 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$matrix[(string) $this][(string) $type], @false);
        if (!is_bool($ret5902c6fce7c01)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fce7c01) . " given");
        }
        return $ret5902c6fce7c01;
    }
    /**
     * @return string
     * A string representation of this type in FQSEN form.
     */
    public function asFQSENString()
    {
        $ret5902c6fce7f64 = $this->memoize(__METHOD__, function () {
            if (!$this->hasNamespace()) {
                return $this->getName();
            }
            if ('\\' === $this->getNamespace()) {
                return '\\' . $this->getName();
            }
            return "{$this->getNamespace()}\\{$this->getName()}";
        });
        if (!is_string($ret5902c6fce7f64)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fce7f64) . " given");
        }
        return $ret5902c6fce7f64;
    }
    /**
     * @return string
     * A human readable representation of this type
     * (This is frequently called, so prefer efficient operations)
     */
    public function __toString()
    {
        $string = $this->asFQSENString();
        if (count($this->template_parameter_type_list) > 0) {
            $string .= $this->templateParameterTypeListAsString();
        }
        if ($this->getIsNullable()) {
            $string = '?' . $string;
        }
        return $string;
    }
    /**
     * Gets the part of the Type string for the template parameters.
     * Precondition: $this->template_parameter_string is not null.
     */
    private function templateParameterTypeListAsString()
    {
        $ret5902c6fce8366 = '<' . implode(',', array_map(function (UnionType $type) {
            return (string) $type;
        }, $this->template_parameter_type_list)) . '>';
        if (!is_string($ret5902c6fce8366)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fce8366) . " given");
        }
        return $ret5902c6fce8366;
    }
    /**
     * @param string $type_name
     * Any type name
     *
     * @return string
     * A canonical name for the given type name
     */
    private static function canonicalNameFromName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to canonicalNameFromName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        static $map = ['boolean' => 'bool', 'callback' => 'callable', 'double' => 'float', 'false' => 'bool', 'true' => 'bool', 'integer' => 'int'];
        $ret5902c6fce86be = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$map[strtolower($name)], @$name);
        if (!is_string($ret5902c6fce86be)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fce86be) . " given");
        }
        return $ret5902c6fce86be;
    }
    /**
     * @param string $type_string
     * Any type string such as 'int' or 'Set<int>'
     *
     * @return Tuple4<string,string,array,bool>
     * A pair with the 0th element being the namespace and the first
     * element being the type name.
     */
    private static function typeStringComponents($type_string)
    {
        if (!is_string($type_string)) {
            throw new \InvalidArgumentException("Argument \$type_string passed to typeStringComponents() must be of the type string, " . (gettype($type_string) == "object" ? get_class($type_string) : gettype($type_string)) . " given");
        }
        // Check to see if we have template parameter types
        $template_parameter_type_name_list = [];
        $match = [];
        $is_nullable = false;
        if (preg_match('/' . self::type_regex . '/', $type_string, $match)) {
            $type_string = $match[1];
            // Rip out the nullability indicator if it
            // exists and note its nullability
            $is_nullable = call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$match[2], @'') == '?';
            if ($is_nullable) {
                $type_string = substr($type_string, 1);
            }
            // If we have a generic array symbol '[]', append that back
            // on to the type string
            if (isset($match[12])) {
                // Figure out the dimensionality of the type array
                $gmatch = [];
                if (preg_match('/\\[[\\]\\[]*\\]/', $match[0], $gmatch)) {
                    $type_string .= $gmatch[0];
                }
            }
            $template_parameter_type_name_list = !empty($match[4]) ? preg_split('/\\s*,\\s*/', $match[4]) : [];
        }
        // Determine if the type name is fully qualified
        // (as specified by a leading backslash).
        $is_fully_qualified = 0 === strpos($type_string, '\\');
        $fq_class_name_elements = array_filter(explode('\\', $type_string));
        $class_name = (string) array_pop($fq_class_name_elements);
        $namespace = ($is_fully_qualified ? '\\' : '') . implode('\\', array_filter($fq_class_name_elements));
        return new Tuple4($namespace, $class_name, $template_parameter_type_name_list, $is_nullable);
    }
}