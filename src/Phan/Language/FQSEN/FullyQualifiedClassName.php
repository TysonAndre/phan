<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

use Phan\Language\Type;
use Phan\Language\UnionType;
use Phan\Memoize;
/**
 * A Fully-Qualified Class Name
 */
class FullyQualifiedClassName extends FullyQualifiedGlobalStructuralElement
{
    use Memoize;
    /**
     * @return int
     * The namespace map type such as \ast\flags\USE_NORMAL or \ast\flags\USE_FUNCTION
     */
    protected static function getNamespaceMapType()
    {
        $ret5902c6f66d5dd = \ast\flags\USE_NORMAL;
        if (!is_int($ret5902c6f66d5dd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f66d5dd) . " given");
        }
        return $ret5902c6f66d5dd;
    }
    /**
     * @return string
     * The canonical representation of the name of the object. Functions
     * and Methods, for instance, lowercase their names.
     */
    public static function canonicalName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to canonicalName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6f66da61 = $name;
        if (!is_string($ret5902c6f66da61)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f66da61) . " given");
        }
        return $ret5902c6f66da61;
    }
    /**
     * @return FullyQualifiedClassName
     * A fully qualified class name from the given type
     */
    public static function fromType(Type $type)
    {
        $ret5902c6f66e01a = self::fromFullyQualifiedString($type->asFQSENString());
        if (!$ret5902c6f66e01a instanceof FullyQualifiedClassName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6f66e01a) == "object" ? get_class($ret5902c6f66e01a) : gettype($ret5902c6f66e01a)) . " given");
        }
        return $ret5902c6f66e01a;
    }
    /**
     * @return Type
     * The type of this class
     */
    public function asType()
    {
        $ret5902c6f66e36d = Type::fromFullyQualifiedString((string) $this);
        if (!$ret5902c6f66e36d instanceof Type) {
            throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6f66e36d) == "object" ? get_class($ret5902c6f66e36d) : gettype($ret5902c6f66e36d)) . " given");
        }
        return $ret5902c6f66e36d;
    }
    /**
     * @return UnionType
     * The union type of just this class type
     */
    public function asUnionType()
    {
        $ret5902c6f66e67b = $this->asType()->asUnionType();
        if (!$ret5902c6f66e67b instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f66e67b) == "object" ? get_class($ret5902c6f66e67b) : gettype($ret5902c6f66e67b)) . " given");
        }
        return $ret5902c6f66e67b;
    }
    /**
     * @return FullyQualifiedClassName
     * The FQSEN for \stdClass.
     */
    public static function getStdClassFQSEN()
    {
        $ret5902c6f66e9b8 = self::memoizeStatic(__METHOD__, function () {
            return self::fromFullyQualifiedString("\\stdClass");
        });
        if (!$ret5902c6f66e9b8 instanceof FullyQualifiedClassName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6f66e9b8) == "object" ? get_class($ret5902c6f66e9b8) : gettype($ret5902c6f66e9b8)) . " given");
        }
        return $ret5902c6f66e9b8;
    }
}