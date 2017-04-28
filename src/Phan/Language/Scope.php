<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language;

use Phan\Config;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Type\TemplateType;
abstract class Scope
{
    /**
     * @var Scope|null
     */
    private $parent_scope = null;
    /**
     * @var FQSEN|null
     */
    private $fqsen = null;
    /**
     * @var Variable[]
     */
    protected $variable_map = [];
    /**
     * @var TemplateType[]
     * A map from template type identifiers to the
     * TemplateType that parameterizes the generic class
     * in this scope.
     */
    private $template_type_map = [];
    /**
     * @param Scope $parent_scope
     * @param FQSEN $fqsen
     */
    public function __construct(Scope $parent_scope = null, FQSEN $fqsen = null)
    {
        $this->parent_scope = $parent_scope;
        $this->fqsen = $fqsen;
    }
    /**
     * @return bool
     * True if this scope has a parent scope
     */
    public function hasParentScope()
    {
        $ret5902c6fc497fa = !empty($this->parent_scope) && $this->parent_scope !== null;
        if (!is_bool($ret5902c6fc497fa)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc497fa) . " given");
        }
        return $ret5902c6fc497fa;
    }
    /**
     * @return Scope
     * Get the parent scope of this scope
     */
    public function getParentScope()
    {
        $ret5902c6fc49b68 = $this->parent_scope;
        if (!$ret5902c6fc49b68 instanceof Scope) {
            throw new \InvalidArgumentException("Argument returned must be of the type Scope, " . (gettype($ret5902c6fc49b68) == "object" ? get_class($ret5902c6fc49b68) : gettype($ret5902c6fc49b68)) . " given");
        }
        return $ret5902c6fc49b68;
    }
    /**
     * @return bool
     * True if this scope has an FQSEN
     */
    public function hasFQSEN()
    {
        $ret5902c6fc49f10 = !empty($this->fqsen);
        if (!is_bool($ret5902c6fc49f10)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc49f10) . " given");
        }
        return $ret5902c6fc49f10;
    }
    /**
     *
     */
    public function getFQSEN()
    {
        return $this->fqsen;
    }
    /**
     * @return bool
     * True if we're in a class scope
     */
    public function isInClassScope()
    {
        $ret5902c6fc4a263 = $this->hasParentScope() ? $this->getParentScope()->isInClassScope() : false;
        if (!is_bool($ret5902c6fc4a263)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4a263) . " given");
        }
        return $ret5902c6fc4a263;
    }
    /**
     * @return FullyQualifiedClassName
     * Crawl the scope hierarchy to get a class FQSEN.
     */
    public function getClassFQSEN()
    {
        assert($this->hasParentScope(), "Cannot get class FQSEN on scope");
        $ret5902c6fc4a547 = $this->getParentScope()->getClassFQSEN();
        if (!$ret5902c6fc4a547 instanceof FullyQualifiedClassName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6fc4a547) == "object" ? get_class($ret5902c6fc4a547) : gettype($ret5902c6fc4a547)) . " given");
        }
        return $ret5902c6fc4a547;
    }
    /**
     * @return bool
     * True if we're in a method/function/closure scope
     */
    public function isInFunctionLikeScope()
    {
        $ret5902c6fc4a855 = $this->hasParentScope() ? $this->getParentScope()->isInFunctionLikeScope() : false;
        if (!is_bool($ret5902c6fc4a855)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4a855) . " given");
        }
        return $ret5902c6fc4a855;
    }
    /**
     * @return FullyQualifiedMethodName|FullyQualifiedFunctionName
     * Crawl the scope hierarchy to get a method FQSEN.
     */
    public function getFunctionLikeFQSEN()
    {
        assert($this->hasParentScope(), "Cannot get method/function/closure FQSEN on scope");
        return $this->getParentScope()->getFunctionLikeFQSEN();
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
        $ret5902c6fc4ab4e = !empty($this->variable_map[$name]);
        if (!is_bool($ret5902c6fc4ab4e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4ab4e) . " given");
        }
        return $ret5902c6fc4ab4e;
    }
    /**
     * @return Variable
     */
    public function getVariableByName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getVariableByName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $ret5902c6fc4b06b = $this->variable_map[$name];
        if (!$ret5902c6fc4b06b instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6fc4b06b) == "object" ? get_class($ret5902c6fc4b06b) : gettype($ret5902c6fc4b06b)) . " given");
        }
        return $ret5902c6fc4b06b;
    }
    /**
     * @return Variable[]
     * A map from name to Variable in this scope
     */
    public function getVariableMap()
    {
        $ret5902c6fc4b59a = $this->variable_map;
        if (!is_array($ret5902c6fc4b59a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fc4b59a) . " given");
        }
        return $ret5902c6fc4b59a;
    }
    /**
     * @param Variable $variable
     * A variable to add to the local scope
     *
     * @return Scope;
     */
    public function withVariable(Variable $variable)
    {
        $scope = clone $this;
        $scope->addVariable($variable);
        $ret5902c6fc4b853 = $scope;
        if (!$ret5902c6fc4b853 instanceof Scope) {
            throw new \InvalidArgumentException("Argument returned must be of the type Scope, " . (gettype($ret5902c6fc4b853) == "object" ? get_class($ret5902c6fc4b853) : gettype($ret5902c6fc4b853)) . " given");
        }
        return $ret5902c6fc4b853;
    }
    /**
     * @return void
     */
    public function addVariable(Variable $variable)
    {
        $this->variable_map[$variable->getName()] = $variable;
    }
    /**
     * @param Variable $variable
     * A variable to add to the set of global variables
     *
     * @return void
     */
    public function addGlobalVariable(Variable $variable)
    {
        assert($this->hasParentScope(), "No global scope available. This should not happen.");
        $this->getParentScope()->addGlobalVariable($variable);
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
        assert($this->hasParentScope(), "No global scope available. This should not happen.");
        $ret5902c6fc4bc9b = $this->getParentScope()->hasGlobalVariableWithName($name);
        if (!is_bool($ret5902c6fc4bc9b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4bc9b) . " given");
        }
        return $ret5902c6fc4bc9b;
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
        assert($this->hasParentScope(), "No global scope available. This should not happen.");
        $ret5902c6fc4c23e = $this->getParentScope()->getGlobalVariableByName($name);
        if (!$ret5902c6fc4c23e instanceof Variable) {
            throw new \InvalidArgumentException("Argument returned must be of the type Variable, " . (gettype($ret5902c6fc4c23e) == "object" ? get_class($ret5902c6fc4c23e) : gettype($ret5902c6fc4c23e)) . " given");
        }
        return $ret5902c6fc4c23e;
    }
    /**
     * @return bool
     * True if there are any template types parameterizing a
     * generic class in this scope.
     */
    public function hasAnyTemplateType()
    {
        if (!Config::get()->generic_types_enabled) {
            $ret5902c6fc4c7a0 = false;
            if (!is_bool($ret5902c6fc4c7a0)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4c7a0) . " given");
            }
            return $ret5902c6fc4c7a0;
        }
        $ret5902c6fc4ca3e = !empty($this->template_type_map) || $this->hasParentScope() && $this->getParentScope()->hasAnyTemplateType();
        if (!is_bool($ret5902c6fc4ca3e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4ca3e) . " given");
        }
        return $ret5902c6fc4ca3e;
    }
    /**
     * @return TemplateType[]
     * The set of all template types parameterizing this generic
     * class
     */
    public function getTemplateTypeMap()
    {
        $ret5902c6fc4cd4d = array_merge($this->template_type_map, $this->hasParentScope() ? $this->getParentScope()->getTemplateTypeMap() : []);
        if (!is_array($ret5902c6fc4cd4d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6fc4cd4d) . " given");
        }
        return $ret5902c6fc4cd4d;
    }
    /**
     * @return bool
     * True if the given template type identifier is defined within
     * this context
     */
    public function hasTemplateType($template_type_identifier)
    {
        if (!is_string($template_type_identifier)) {
            throw new \InvalidArgumentException("Argument \$template_type_identifier passed to hasTemplateType() must be of the type string, " . (gettype($template_type_identifier) == "object" ? get_class($template_type_identifier) : gettype($template_type_identifier)) . " given");
        }
        echo "Checking template_type_map for $template_type_identifier\n";
        echo "Scope = " . spl_object_hash($this) . "\n";
        var_dump($this->template_type_map);
        $ret5902c6fc4d030 = isset($this->template_type_map[$template_type_identifier]) || ($this->hasParentScope() ? $this->getParentScope()->hasTemplateType($template_type_identifier) : false);
        if ($ret5902c6fc4d030) {
            echo "Returning true\n";
        }
        if (!is_bool($ret5902c6fc4d030)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fc4d030) . " given");
        }
        return $ret5902c6fc4d030;
    }
    /**
     * @param TemplateType $template_type
     * A template type parameterizing the generic class in scope
     *
     * @return void
     */
    public function addTemplateType(TemplateType $template_type)
    {
        $this->template_type_map[$template_type->getName()] = $template_type;
    }
    /**
     * @param string $generic_type_identifier
     * The identifier for a generic type
     *
     * @return TemplateType
     * A TemplateType parameterizing the generic class in scope
     */
    public function getTemplateType($template_type_identifier)
    {
        if (!is_string($template_type_identifier)) {
            throw new \InvalidArgumentException("Argument \$template_type_identifier passed to getTemplateType() must be of the type string, " . (gettype($template_type_identifier) == "object" ? get_class($template_type_identifier) : gettype($template_type_identifier)) . " given");
        }
        // FIXME debugging
        if (!$this->hasTemplateType($template_type_identifier)) {
            var_dump($this->template_type_map);
        }
        assert($this->hasTemplateType($template_type_identifier), "Cannot get template type with identifier {$template_type_identifier}");
        // TODO: This is NOT equivalent, it always computes the right hand side
        $ret5902c6fc4d6e2 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$this->template_type_map[$template_type_identifier], @$this->getParentScope()->getTemplateType($template_type_identifier));
        if (!$ret5902c6fc4d6e2 instanceof TemplateType) {
            throw new \InvalidArgumentException("Argument returned must be of the type TemplateType, " . (gettype($ret5902c6fc4d6e2) == "object" ? get_class($ret5902c6fc4d6e2) : gettype($ret5902c6fc4d6e2)) . " given");
        }
        return $ret5902c6fc4d6e2;
    }
    /**
     * @return string
     * A string representation of this scope
     */
    public function __toString()
    {
        $ret5902c6fc4dd36 = $this->getFQSEN() . "\t" . implode(',', $this->getVariableMap());
        if (!is_string($ret5902c6fc4dd36)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fc4dd36) . " given");
        }
        return $ret5902c6fc4dd36;
    }
}