<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\FQSEN;
use Phan\Language\Type;
class CallableType extends NativeType
{
    const NAME = 'callable';
    /**
     * @var FQSEN|null
     */
    private $fqsen;
    // Same as instance(), but guaranteed not to have memoized state.
    private static function callableInstance()
    {
        static $instance = null;
        if (empty($instance)) {
            $instance = self::make('\\', static::NAME, [], false, false);
        }
        $ret5902c6fc69989 = $instance;
        if (!$ret5902c6fc69989 instanceof CallableType) {
            throw new \InvalidArgumentException("Argument returned must be of the type CallableType, " . (gettype($ret5902c6fc69989) == "object" ? get_class($ret5902c6fc69989) : gettype($ret5902c6fc69989)) . " given");
        }
        return $ret5902c6fc69989;
    }
    public static function instanceWithClosureFQSEN(FQSEN $fqsen)
    {
        // Use an instance with no memoized or lazily initialized results.
        // Avoids picking up changes to CallableType::instance(false) in the case that a result depends on asFQSEN()
        $instance = clone self::callableInstance();
        $instance->fqsen = $fqsen;
        return $instance;
    }
    /**
     * Override asFQSEN to return the closure's FQSEN
     */
    public function asFQSEN()
    {
        if (!empty($this->fqsen)) {
            $ret5902c6fc69f57 = $this->fqsen;
            if (!$ret5902c6fc69f57 instanceof FQSEN) {
                throw new \InvalidArgumentException("Argument returned must be of the type FQSEN, " . (gettype($ret5902c6fc69f57) == "object" ? get_class($ret5902c6fc69f57) : gettype($ret5902c6fc69f57)) . " given");
            }
            return $ret5902c6fc69f57;
        }
        $ret5902c6fc6a292 = parent::asFQSEN();
        if (!$ret5902c6fc6a292 instanceof FQSEN) {
            throw new \InvalidArgumentException("Argument returned must be of the type FQSEN, " . (gettype($ret5902c6fc6a292) == "object" ? get_class($ret5902c6fc6a292) : gettype($ret5902c6fc6a292)) . " given");
        }
        return $ret5902c6fc6a292;
    }
    /**
     * @return bool
     * True if this Type can be cast to the given Type
     * cleanly
     */
    protected function canCastToNonNullableType(Type $type)
    {
        $d = strtolower((string) $type);
        if ($d[0] == '\\') {
            $d = substr($d, 1);
        }
        // TODO: you can have a callable that isn't a closure
        //       This is wrong
        if ($d === 'closure') {
            $ret5902c6fc6a67b = true;
            if (!is_bool($ret5902c6fc6a67b)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc6a67b) . " given");
            }
            return $ret5902c6fc6a67b;
        }
        $ret5902c6fc6a904 = parent::canCastToNonNullableType($type);
        if (!is_bool($ret5902c6fc6a904)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc6a904) . " given");
        }
        return $ret5902c6fc6a904;
    }
}