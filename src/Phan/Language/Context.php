<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language;

use Phan\CodeBase;
use Phan\Exception\CodeBaseException;
use Phan\Language\Element\Clazz;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Element\TypedElement;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionLikeName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedGlobalStructuralElement;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Scope\GlobalScope;
/**
 * An object representing the context in which any
 * structural element (such as a class or method) lives.
 */
class Context extends FileRef
{
    /**
     * @var string
     * The namespace of the file
     */
    private $namespace = '';
    /**
     * @var array
     */
    private $namespace_map = [];
    /**
     * @var int
     * strict_types setting for the file
     */
    protected $strict_types = 0;
    /**
     * @var Scope
     * The current scope in this context
     */
    private $scope;
    /**
     * Create a new context
     */
    public function __construct()
    {
        $this->namespace = '';
        $this->namespace_map = [];
        $this->scope = new GlobalScope();
    }
    /*
     * @param string $namespace
     * The namespace of the file
     *
     * @return Context
     * A clone of this context with the given value is returned
     */
    public function withNamespace($namespace)
    {
        if (!is_string($namespace)) {
            throw new \InvalidArgumentException("Argument \$namespace passed to withNamespace() must be of the type string, " . (gettype($namespace) == "object" ? get_class($namespace) : gettype($namespace)) . " given");
        }
        $context = clone $this;
        $context->namespace = $namespace;
        $ret5902c6f4a9e38 = $context;
        if (!$ret5902c6f4a9e38 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f4a9e38) == "object" ? get_class($ret5902c6f4a9e38) : gettype($ret5902c6f4a9e38)) . " given");
        }
        return $ret5902c6f4a9e38;
    }
    /**
     * @return string
     * The namespace of the file
     */
    public function getNamespace()
    {
        $ret5902c6f4aa422 = $this->namespace;
        if (!is_string($ret5902c6f4aa422)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6f4aa422) . " given");
        }
        return $ret5902c6f4aa422;
    }
    /**
     * @return bool
     * True if we have a mapped NS for the given named element
     */
    public function hasNamespaceMapFor($flags, $name)
    {
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to hasNamespaceMapFor() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to hasNamespaceMapFor() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        // Look for the mapping on the part before a
        // slash
        $name_parts = explode('\\', $name, 2);
        if (count($name_parts) > 1) {
            $name = $name_parts[0];
        }
        $ret5902c6f4aa796 = !empty($this->namespace_map[$flags][strtolower($name)]);
        if (!is_bool($ret5902c6f4aa796)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4aa796) . " given");
        }
        return $ret5902c6f4aa796;
    }
    /**
     * @return FullyQualifiedGlobalStructuralElement
     * The namespace mapped name for the given flags and name
     */
    public function getNamespaceMapFor($flags, $name)
    {
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to getNamespaceMapFor() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getNamespaceMapFor() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        $name = strtolower($name);
        // Look for the mapping on the part before a
        // slash
        $name_parts = explode('\\', $name, 2);
        $suffix = '';
        if (count($name_parts) > 1) {
            $name = $name_parts[0];
            $suffix = $name_parts[1];
        }
        assert(!empty($this->namespace_map[$flags][$name]), "No namespace defined for name");
        assert($this->namespace_map[$flags][$name] instanceof FQSEN, "Namespace map was not an FQSEN");
        $fqsen = $this->namespace_map[$flags][$name];
        if (!$suffix) {
            $ret5902c6f4ab155 = $fqsen;
            if (!$ret5902c6f4ab155 instanceof FullyQualifiedGlobalStructuralElement) {
                throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedGlobalStructuralElement, " . (gettype($ret5902c6f4ab155) == "object" ? get_class($ret5902c6f4ab155) : gettype($ret5902c6f4ab155)) . " given");
            }
            return $ret5902c6f4ab155;
        }
        switch ($flags) {
            case \ast\flags\USE_NORMAL:
                $ret5902c6f4ab472 = FullyQualifiedClassName::fromFullyQualifiedString((string) $fqsen . '\\' . $suffix);
                if (!$ret5902c6f4ab472 instanceof FullyQualifiedGlobalStructuralElement) {
                    throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedGlobalStructuralElement, " . (gettype($ret5902c6f4ab472) == "object" ? get_class($ret5902c6f4ab472) : gettype($ret5902c6f4ab472)) . " given");
                }
                return $ret5902c6f4ab472;
            case \ast\flags\USE_FUNCTION:
                $ret5902c6f4ab797 = FullyQualifiedFunctionName::fromFullyQualifiedString((string) $fqsen . '\\' . $suffix);
                if (!$ret5902c6f4ab797 instanceof FullyQualifiedGlobalStructuralElement) {
                    throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedGlobalStructuralElement, " . (gettype($ret5902c6f4ab797) == "object" ? get_class($ret5902c6f4ab797) : gettype($ret5902c6f4ab797)) . " given");
                }
                return $ret5902c6f4ab797;
        }
        assert(false, "Unknown flag {$flags}");
        $ret5902c6f4abac4 = $fqsen;
        if (!$ret5902c6f4abac4 instanceof FullyQualifiedGlobalStructuralElement) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedGlobalStructuralElement, " . (gettype($ret5902c6f4abac4) == "object" ? get_class($ret5902c6f4abac4) : gettype($ret5902c6f4abac4)) . " given");
        }
        return $ret5902c6f4abac4;
    }
    /**
     * @return Context
     * This context with the given value is returned
     */
    public function withNamespaceMap($flags, $alias, FullyQualifiedGlobalStructuralElement $target)
    {
        if (!is_int($flags)) {
            throw new \InvalidArgumentException("Argument \$flags passed to withNamespaceMap() must be of the type int, " . (gettype($flags) == "object" ? get_class($flags) : gettype($flags)) . " given");
        }
        if (!is_string($alias)) {
            throw new \InvalidArgumentException("Argument \$alias passed to withNamespaceMap() must be of the type string, " . (gettype($alias) == "object" ? get_class($alias) : gettype($alias)) . " given");
        }
        $this->namespace_map[$flags][strtolower($alias)] = $target;
        $ret5902c6f4ac2b4 = $this;
        if (!$ret5902c6f4ac2b4 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f4ac2b4) == "object" ? get_class($ret5902c6f4ac2b4) : gettype($ret5902c6f4ac2b4)) . " given");
        }
        return $ret5902c6f4ac2b4;
    }
    /**
     * @param int $strict_types
     * The strict_type setting for the file
     *
     * @return Context
     * This context with the given value is returned
     */
    public function withStrictTypes($strict_types)
    {
        if (!is_int($strict_types)) {
            throw new \InvalidArgumentException("Argument \$strict_types passed to withStrictTypes() must be of the type int, " . (gettype($strict_types) == "object" ? get_class($strict_types) : gettype($strict_types)) . " given");
        }
        $this->strict_types = $strict_types;
        $ret5902c6f4acade = $this;
        if (!$ret5902c6f4acade instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f4acade) == "object" ? get_class($ret5902c6f4acade) : gettype($ret5902c6f4acade)) . " given");
        }
        return $ret5902c6f4acade;
    }
    /**
     * @return bool
     * True if strict_types is set to 1 in this
     * context.
     */
    public function getIsStrictTypes()
    {
        $ret5902c6f4ad00c = 1 === $this->strict_types;
        if (!is_bool($ret5902c6f4ad00c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4ad00c) . " given");
        }
        return $ret5902c6f4ad00c;
    }
    /**
     * @return Scope
     * An object describing the contents of the current
     * scope.
     */
    public function getScope()
    {
        $ret5902c6f4ad27d = $this->scope;
        if (!$ret5902c6f4ad27d instanceof Scope) {
            throw new \InvalidArgumentException("Argument returned must be of the type Scope, " . (gettype($ret5902c6f4ad27d) == "object" ? get_class($ret5902c6f4ad27d) : gettype($ret5902c6f4ad27d)) . " given");
        }
        return $ret5902c6f4ad27d;
    }
    /**
     * Set the scope on the context
     *
     * @return void
     */
    public function setScope(Scope $scope)
    {
        $this->scope = $scope;
    }
    /**
     * @return Context
     * A new context with the given scope
     */
    public function withScope(Scope $scope)
    {
        $context = clone $this;
        $context->setScope($scope);
        $ret5902c6f4ad5fe = $context;
        if (!$ret5902c6f4ad5fe instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f4ad5fe) == "object" ? get_class($ret5902c6f4ad5fe) : gettype($ret5902c6f4ad5fe)) . " given");
        }
        return $ret5902c6f4ad5fe;
    }
    /**
     * @param Variable $variable
     * A variable to add to the scope for the new
     * context
     *
     * @return Context
     * A new context based on this with a variable
     * as defined by the parameters in scope
     */
    public function withScopeVariable(Variable $variable)
    {
        $ret5902c6f4ad95a = $this->withScope($this->getScope()->withVariable($variable));
        if (!$ret5902c6f4ad95a instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f4ad95a) == "object" ? get_class($ret5902c6f4ad95a) : gettype($ret5902c6f4ad95a)) . " given");
        }
        return $ret5902c6f4ad95a;
    }
    /**
     * @param Variable $variable
     * A variable to add to the scope for the new
     * context
     *
     * @return void
     */
    public function addGlobalScopeVariable(Variable $variable)
    {
        $this->getScope()->addGlobalVariable($variable);
    }
    /**
     * Add a variable to this context's scope. Note that
     * this does not create a new context. You're actually
     * injecting the variable into the context. Use with
     * caution.
     *
     * @param Variable $variable
     * A variable to inject into this context
     *
     * @return void
     */
    public function addScopeVariable(Variable $variable)
    {
        $this->getScope()->addVariable($variable);
    }
    /**
     * @return bool
     * True if this context is currently within a class
     * scope, else false.
     */
    public function isInClassScope()
    {
        $ret5902c6f4adce6 = $this->getScope()->isInClassScope();
        if (!is_bool($ret5902c6f4adce6)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4adce6) . " given");
        }
        return $ret5902c6f4adce6;
    }
    /**
     * @return FullyQualifiedClassName
     * A fully-qualified structural element name describing
     * the current class in scope.
     */
    public function getClassFQSEN()
    {
        $ret5902c6f4adf61 = $this->getScope()->getClassFQSEN();
        if (!$ret5902c6f4adf61 instanceof FullyQualifiedClassName) {
            throw new \InvalidArgumentException("Argument returned must be of the type FullyQualifiedClassName, " . (gettype($ret5902c6f4adf61) == "object" ? get_class($ret5902c6f4adf61) : gettype($ret5902c6f4adf61)) . " given");
        }
        return $ret5902c6f4adf61;
    }
    /**
     * @param CodeBase $code_base
     * The global code base holding all state
     *
     * @return Clazz
     * Get the class in this scope, or fail real hard
     *
     * @throws CodeBaseException
     * Thrown if we can't find the class in scope within the
     * given codebase.
     */
    public function getClassInScope(CodeBase $code_base)
    {
        assert($this->isInClassScope(), "Must be in class scope to get class");
        if (!$code_base->hasClassWithFQSEN($this->getClassFQSEN())) {
            throw new CodeBaseException($this->getClassFQSEN(), "Cannot find class with FQSEN {$this->getClassFQSEN()} in context {$this}");
        }
        $ret5902c6f4ae362 = $code_base->getClassByFQSEN($this->getClassFQSEN());
        if (!$ret5902c6f4ae362 instanceof Clazz) {
            throw new \InvalidArgumentException("Argument returned must be of the type Clazz, " . (gettype($ret5902c6f4ae362) == "object" ? get_class($ret5902c6f4ae362) : gettype($ret5902c6f4ae362)) . " given");
        }
        return $ret5902c6f4ae362;
    }
    /**
     * @return bool
     * True if this context is currently within a method,
     * function or closure scope.
     */
    public function isInFunctionLikeScope()
    {
        $ret5902c6f4ae64e = $this->getScope()->isInFunctionLikeScope();
        if (!is_bool($ret5902c6f4ae64e)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4ae64e) . " given");
        }
        return $ret5902c6f4ae64e;
    }
    /**
     * @return bool
     * True if this context is currently within a method.
     */
    public function isInMethodScope()
    {
        $ret5902c6f4ae903 = $this->isInClassScope() && $this->isInFunctionLikeScope();
        if (!is_bool($ret5902c6f4ae903)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4ae903) . " given");
        }
        return $ret5902c6f4ae903;
    }
    /*
     * @return FullyQualifiedFunctionLikeName|FullyQualifiedMethodName|FullyQualifiedFunctionName
     * A fully-qualified structural element name describing
     * the current function or method in scope.
     */
    public function getFunctionLikeFQSEN()
    {
        assert($this->getScope()->isInFunctionLikeScope());
        return $this->getScope()->getFunctionLikeFQSEN();
    }
    /**
     * @param CodeBase $code_base
     * The global code base holding all state
     *
     * @return Element\Func|Element\Method
     * Get the method in this scope or fail real hard
     */
    public function getFunctionLikeInScope(CodeBase $code_base)
    {
        assert($this->isInFunctionLikeScope(), "Must be in method scope to get method.");
        $fqsen = $this->getFunctionLikeFQSEN();
        if ($fqsen instanceof FullyQualifiedFunctionName) {
            assert($code_base->hasFunctionWithFQSEN($fqsen), "The function does not exist");
            $ret5902c6f4aeceb = $code_base->getFunctionByFQSEN($fqsen);
            if (!$ret5902c6f4aeceb instanceof FunctionInterface) {
                throw new \InvalidArgumentException("Argument returned must be of the type FunctionInterface, " . (gettype($ret5902c6f4aeceb) == "object" ? get_class($ret5902c6f4aeceb) : gettype($ret5902c6f4aeceb)) . " given");
            }
            return $ret5902c6f4aeceb;
        }
        if ($fqsen instanceof FullyQualifiedMethodName) {
            assert($code_base->hasMethodWithFQSEN($fqsen), "Method does not exist");
            $ret5902c6f4af037 = $code_base->getMethodByFQSEN($fqsen);
            if (!$ret5902c6f4af037 instanceof FunctionInterface) {
                throw new \InvalidArgumentException("Argument returned must be of the type FunctionInterface, " . (gettype($ret5902c6f4af037) == "object" ? get_class($ret5902c6f4af037) : gettype($ret5902c6f4af037)) . " given");
            }
            return $ret5902c6f4af037;
        }
        assert(false, "FQSEN must be for a function or method");
    }
    /**
     * @return bool
     * True if we're within the scope of a class, method,
     * function or closure. False if we're in the global
     * scope
     */
    public function isInElementScope()
    {
        $ret5902c6f4af37b = $this->isInFunctionLikeScope() || $this->isInClassScope();
        if (!is_bool($ret5902c6f4af37b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4af37b) . " given");
        }
        return $ret5902c6f4af37b;
    }
    /**
     * @return bool
     * True if we're in the global scope (not in a class,
     * method, function, closure).
     */
    public function isInGlobalScope()
    {
        $ret5902c6f4af5f5 = !$this->isInElementScope();
        if (!is_bool($ret5902c6f4af5f5)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4af5f5) . " given");
        }
        return $ret5902c6f4af5f5;
    }
    /**
     * @param CodeBase $code_base
     * The code base from which to retrieve the TypedElement
     *
     * @return TypedElement
     * The element who's scope we're in. If we're in the global
     * scope this method will go down in flames and take your
     * process with it.
     */
    public function getElementInScope(CodeBase $code_base)
    {
        assert($this->isInElementScope(), "Cannot get element in scope if we're in the global scope");
        if ($this->isInFunctionLikeScope()) {
            $ret5902c6f4af969 = $this->getFunctionLikeInScope($code_base);
            if (!$ret5902c6f4af969 instanceof TypedElement) {
                throw new \InvalidArgumentException("Argument returned must be of the type TypedElement, " . (gettype($ret5902c6f4af969) == "object" ? get_class($ret5902c6f4af969) : gettype($ret5902c6f4af969)) . " given");
            }
            return $ret5902c6f4af969;
        } else {
            if ($this->isInClassScope()) {
                $ret5902c6f4afc60 = $this->getClassInScope($code_base);
                if (!$ret5902c6f4afc60 instanceof TypedElement) {
                    throw new \InvalidArgumentException("Argument returned must be of the type TypedElement, " . (gettype($ret5902c6f4afc60) == "object" ? get_class($ret5902c6f4afc60) : gettype($ret5902c6f4afc60)) . " given");
                }
                return $ret5902c6f4afc60;
            }
        }
        throw new CodeBaseException(null, "Cannot get element in scope if we're in the global scope");
    }
    /**
     * @param CodeBase $code_base
     * The code base from which to retrieve a possible TypedElement
     * that contains an issue suppression list
     *
     * @return bool
     * True if issues with the given name are suppressed within
     * this context.
     */
    public function hasSuppressIssue(CodeBase $code_base, $issue_name)
    {
        if (!is_string($issue_name)) {
            throw new \InvalidArgumentException("Argument \$issue_name passed to hasSuppressIssue() must be of the type string, " . (gettype($issue_name) == "object" ? get_class($issue_name) : gettype($issue_name)) . " given");
        }
        if (!$this->isInElementScope()) {
            $ret5902c6f4affd9 = false;
            if (!is_bool($ret5902c6f4affd9)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4affd9) . " given");
            }
            return $ret5902c6f4affd9;
        }
        $has_suppress_issue = $this->getElementInScope($code_base)->hasSuppressIssue($issue_name);
        // Increment the suppression use count
        // FIXME: Does this work properly in php7? That wasn't a reference, it was a value
        /**
        if ($has_suppress_issue) {
            ++$this->getElementInScope($code_base)->getSuppressIssueList()[$issue_name];
        }
         */
        $ret5902c6f4b02cb = $has_suppress_issue;
        if (!is_bool($ret5902c6f4b02cb)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f4b02cb) . " given");
        }
        return $ret5902c6f4b02cb;
    }
}