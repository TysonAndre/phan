<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Scope;

use Phan\Language\Element\Variable;
use Phan\Language\Scope;
class GlobalScope extends Scope
{
    /**
     * @var Variable[]
     * A map from name to variables for all
     * variables registered under $GLOBALS.
     */
    private static $global_variable_map = [];
    /**
     * @return bool
     * True if we're in a class scope
     */
    public function isInClassScope()
    {
        $ret5902c6fc3be9e = false;
        if (!is_bool($ret5902c6fc3be9e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc3be9e) . " given");
        }
        return $ret5902c6fc3be9e;
    }
    /**
     * @return bool
     * True if we're in a method/function/closure scope
     */
    public function isInFunctionLikeScope()
    {
        $ret5902c6fc3c397 = false;
        if (!is_bool($ret5902c6fc3c397)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc3c397) . " given");
        }
        return $ret5902c6fc3c397;
    }
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
        $ret5902c6fc3c626 = !empty(self::$global_variable_map[$name]);
        if (!is_bool($ret5902c6fc3c626)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc3c626) . " given");
        }
        return $ret5902c6fc3c626;
    }
    /**
     * @return Variable
     */
    public function getVariableByName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getVariableByName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6fc3cb7f = self::$global_variable_map[$name];
        if (!$ret5902c6fc3cb7f instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6fc3cb7f) == "object" ? get_class($ret5902c6fc3cb7f) : gettype($ret5902c6fc3cb7f)) . " given");
        }
        return $ret5902c6fc3cb7f;
    }
    /**
     * @return Variable[]
     * A map from name to Variable in this scope
     */
    public function getVariableMap()
    {
        $ret5902c6fc3d0fd = self::$global_variable_map;
        if (!is_array($ret5902c6fc3d0fd)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fc3d0fd) . " given");
        }
        return $ret5902c6fc3d0fd;
    }
    /**
     * @param Variable $variable
     * A variable to add to the local scope
     *
     * @return Scope;
     */
    public function withVariable(Variable $variable)
    {
        $this->addVariable($variable);
        $ret5902c6fc3d3d0 = $this;
        if (!$ret5902c6fc3d3d0 instanceof Scope) {
            throw new \InvalidArgumentException("Argument returned must be of the type Scope, " . (gettype($ret5902c6fc3d3d0) == "object" ? get_class($ret5902c6fc3d3d0) : gettype($ret5902c6fc3d3d0)) . " given");
        }
        return $ret5902c6fc3d3d0;
    }
    /**
     * @return void
     */
    public function addVariable(Variable $variable)
    {
        $variable_name = $variable->getName();
        if (Variable::isHardcodedGlobalVariableWithName($variable_name)) {
            // Silently ignore globally replacing $_POST, $argv, runkit superglobals, etc.
            // with superglobals.
            // TODO: Add a warning for incompatible assignments in callers.
            return;
        }
        self::$global_variable_map[$variable->getName()] = $variable;
    }
    /**
     * @param Variable $variable
     * A variable to add to the set of global variables
     *
     * @return void
     */
    public function addGlobalVariable(Variable $variable)
    {
        $this->addVariable($variable);
    }
    /**
     * @return bool
     * True if a global variable with the given name exists
     */
    public function hasGlobalVariableWithName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to hasGlobalVariableWithName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6fc3d7ca = $this->hasVariableWithName($name);
        if (!is_bool($ret5902c6fc3d7ca)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc3d7ca) . " given");
        }
        return $ret5902c6fc3d7ca;
    }
    /**
     * @return Variable
     * The global variable with the given name
     */
    public function getGlobalVariableByName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getGlobalVariableByName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6fc3dca0 = $this->getVariableByName($name);
        if (!$ret5902c6fc3dca0 instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6fc3dca0) == "object" ? get_class($ret5902c6fc3dca0) : gettype($ret5902c6fc3dca0)) . " given");
        }
        return $ret5902c6fc3dca0;
    }
}