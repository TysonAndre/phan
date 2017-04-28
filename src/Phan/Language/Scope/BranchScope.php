<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Scope;

use Phan\Language\Element\Variable;
use Phan\Language\Scope;
class BranchScope extends Scope
{
    /**
     * @return bool
     * True if a variable with the given name is defined
     * within this scope
     */
    public function hasVariableWithName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to hasVariableWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6fc15bd1 = !empty($this->variable_map[$name]) || $this->getParentScope()->hasVariableWithName($name);
        if (!is_bool($ret5902c6fc15bd1)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc15bd1) . " given");
        }
        return $ret5902c6fc15bd1;
    }
    /**
     * @return Variable
     */
    public function getVariableByName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getVariableByName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6fc164e6 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$this->variable_map[$name], @$this->getParentScope()->getVariableByName($name));
        if (!$ret5902c6fc164e6 instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6fc164e6) == "object" ? get_class($ret5902c6fc164e6) : gettype($ret5902c6fc164e6)) . " given");
        }
        return $ret5902c6fc164e6;
    }
    /**
     * @return Variable[]
     * A map from name to Variable in this scope
     */
    public function getVariableMap()
    {
        $ret5902c6fc16aa2 = array_merge($this->getParentScope()->getVariableMap(), $this->variable_map);
        if (!is_array($ret5902c6fc16aa2)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fc16aa2) . " given");
        }
        return $ret5902c6fc16aa2;
    }
}