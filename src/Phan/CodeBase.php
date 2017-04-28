<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan;

use Phan\CodeBase\ClassMap;
use Phan\CodeBase\UndoTracker;
use Phan\Language\Element\ClassConstant;
use Phan\Language\Element\Clazz;
use Phan\Language\Element\Func;
use Phan\Language\Element\FunctionFactory;
use Phan\Language\Element\GlobalConstant;
use Phan\Language\Element\Method;
use Phan\Language\Element\Property;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedClassConstantName;
use Phan\Language\FQSEN\FullyQualifiedClassElement;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedGlobalConstantName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\FQSEN\FullyQualifiedPropertyName;
use Phan\Language\UnionType;
use Phan\Library\Map;
use Phan\Library\Set;
/**
 * A CodeBase represents the known state of a code base
 * we're analyzing.
 *
 * In order to understand internal classes, interfaces,
 * traits and functions, a CodeBase needs to be
 * initialized with the list of those elements begotten
 * before any classes are loaded.
 *
 * # Example
 * ```
 * // Grab these before we define our own classes
 * $internal_class_name_list = get_declared_classes();
 * $internal_interface_name_list = get_declared_interfaces();
 * $internal_trait_name_list = get_declared_traits();
 * $internal_function_name_list = get_defined_functions()['internal'];
 *
 * // Load any required code ...
 *
 * $code_base = new CodeBase(
 *     $internal_class_name_list,
 *     $internal_interface_name_list,
 *     $internal_trait_name_list,
 *     $internal_function_name_list
 *  );
 *
 *  // Do stuff ...
 * ```
 *
 * This supports undoing some operations in the parse phase,
 * for a background daemon analyzing single files. (Phan\CodeBase\UndoTracker)
 */
class CodeBase
{
    // Hack to allow running the phan 0.8 backport on php 7.1.
    // These are only checked for internal functions - Users can add their own stubs.
    const unsupported_php71_and_newer_functions = ['curl_multi_errno' => true, 'curl_share_errno' => true, 'curl_share_strerror' => true, 'is_iterable' => true, 'pcntl_async_signals' => true, 'pcntl_signal_get_handler' => true, 'sapi_windows_cp_conv' => true, 'sapi_windows_cp_get' => true, 'sapi_windows_cp_is_utf8' => true, 'sapi_windows_cp_set' => true, 'session_create_id' => true, 'session_gc' => true];
    /**
     * @var Map
     * A map from FQSEN to a class
     */
    private $fqsen_class_map;
    /**
     * @var Map
     * A map from FQSEN to a global constant
     */
    private $fqsen_global_constant_map;
    /**
     * @var Map
     * A map from FQSEN to function
     */
    private $fqsen_func_map;
    /**
     * @var Set
     * The set of all functions and methods
     */
    private $func_and_method_set;
    /**
     * @var Map
     * A map from FullyQualifiedClassName to a ClassMap,
     * an object that holds properties, methods and class
     * constants.
     */
    private $class_fqsen_class_map_map;
    /**
     * @var Set[]
     * A map from a string method name to a Set of
     * Methods
     */
    private $name_method_map = [];
    /**
     * @var bool
     * If true, elements will be ensured to be hydrated
     * on demand as they are requested.
     */
    private $should_hydrate_requested_elements = false;
    /**
     * @var UndoTracker|null - undoes the addition of global constants, classes, functions, and methods.
     */
    private $undo_tracker;
    /**
     * @var bool
     */
    private $has_enabled_undo_tracker = false;
    /**
     * Initialize a new CodeBase
     */
    public function __construct(array $internal_class_name_list, array $internal_interface_name_list, array $internal_trait_name_list, array $internal_function_name_list)
    {
        $this->fqsen_class_map = new Map();
        $this->fqsen_global_constant_map = new Map();
        $this->fqsen_func_map = new Map();
        $this->class_fqsen_class_map_map = new Map();
        $this->func_and_method_set = new Set();
        // Add any pre-defined internal classes, interfaces,
        // traits and functions
        $this->addClassesByNames($internal_class_name_list);
        $this->addClassesByNames($internal_interface_name_list);
        $this->addClassesByNames($internal_trait_name_list);
        $this->addFunctionsByNames($internal_function_name_list);
    }
    /**
     * @return void
     */
    public function enableUndoTracking()
    {
        if ($this->has_enabled_undo_tracker) {
            throw new \RuntimeException("Undo tracking already enabled");
        }
        $this->has_enabled_undo_tracker = true;
        $this->undo_tracker = new UndoTracker();
    }
    /**
     * @return void
     */
    public function disableUndoTracking()
    {
        if (!$this->has_enabled_undo_tracker) {
            throw new \RuntimeException("Undo tracking was never enabled");
        }
        $this->undo_tracker = null;
    }
    public function isUndoTrackingEnabled()
    {
        $ret5902c6f3c6ca5 = $this->undo_tracker !== null;
        if (!is_bool($ret5902c6f3c6ca5)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3c6ca5) . " given");
        }
        return $ret5902c6f3c6ca5;
    }
    /**
     * @return void
     */
    public function setShouldHydrateRequestedElements($should_hydrate_requested_elements)
    {
        if (!is_bool($should_hydrate_requested_elements)) {
            throw new \InvalidArgumentException("Argument \$should_hydrate_requested_elements passed to setShouldHydrateRequestedElements() must be of the type bool, " . (gettype($should_hydrate_requested_elements) == "object" ? get_class($should_hydrate_requested_elements) : gettype($should_hydrate_requested_elements)) . " given");
        }
        $this->should_hydrate_requested_elements = $should_hydrate_requested_elements;
    }
    /**
     * @return string[] - The list of files which are successfully parsed.
     * This changes whenever the file list is reloaded from disk.
     * This also includes files which don't declare classes or functions or globals,
     * because those files use classes/functions/constants.
     *
     * (This is the list prior to any analysis exclusion or whitelisting steps)
     */
    public function getParsedFilePathList()
    {
        if ($this->undo_tracker) {
            $ret5902c6f3c72ed = $this->undo_tracker->getParsedFilePathList();
            if (!is_array($ret5902c6f3c72ed)) {
                throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3c72ed) . " given");
            }
            return $ret5902c6f3c72ed;
        }
        throw new \RuntimeException("Calling getParsedFilePathList without an undo tracker");
    }
    /**
     * @return string[] - The size of $this->getParsedFilePathList()
     */
    public function getParsedFilePathCount()
    {
        if ($this->undo_tracker) {
            $ret5902c6f3c7594 = $this->undo_tracker->getParsedFilePathCount();
            if (!is_int($ret5902c6f3c7594)) {
                throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f3c7594) . " given");
            }
            return $ret5902c6f3c7594;
        }
        throw new \RuntimeException("Calling getParsedFilePathCount without an undo tracker");
    }
    /**
     * @param string|null $current_parsed_file
     * @return void
     */
    public function setCurrentParsedFile($current_parsed_file)
    {
        if ($this->undo_tracker) {
            $this->undo_tracker->setCurrentParsedFile($current_parsed_file);
        }
    }
    /**
     * Called when a file is unparseable.
     * Removes the classes and functions, etc. from an older version of the file, if one exists.
     * @return void
     */
    public function recordUnparseableFile($current_parsed_file)
    {
        if (!is_string($current_parsed_file)) {
            throw new \InvalidArgumentException("Argument \$current_parsed_file passed to recordUnparseableFile() must be of the type string, " . (gettype($current_parsed_file) == "object" ? get_class($current_parsed_file) : gettype($current_parsed_file)) . " given");
        }
        if ($this->undo_tracker) {
            $this->undo_tracker->recordUnparseableFile($this, $current_parsed_file);
        }
    }
    /**
     * @param string[] $class_name_list
     * A list of class names to load type information for
     *
     * @return void
     */
    private function addClassesByNames(array $class_name_list)
    {
        foreach ($class_name_list as $i => $class_name) {
            $this->addClass(Clazz::fromClassName($this, $class_name));
        }
    }
    /**
     * @param string[] $new_file_list
     * @return string[] - Subset of $new_file_list which changed on disk and has to be parsed again. Automatically unparses the old versions of files which were modified.
     */
    public function updateFileList(array $new_file_list)
    {
        if ($this->undo_tracker) {
            return $this->undo_tracker->updateFileList($this, $new_file_list);
        }
        throw new \RuntimeException("Calling updateFileList without undo tracker");
    }
    /**
     * @param string[] $function_name_list
     * A list of function names to load type information for
     */
    private function addFunctionsByNames(array $function_name_list)
    {
        foreach ($function_name_list as $i => $function_name) {
            if (array_key_exists($function_name, self::unsupported_php71_and_newer_functions)) {
                continue;
            }
            foreach (FunctionFactory::functionListFromName($this, $function_name) as $function_or_method) {
                $this->addFunction($function_or_method);
            }
        }
    }
    /**
     * Clone dependent objects when cloning this object.
     */
    public function __clone()
    {
        $this->fqsen_class_map = $this->fqsen_class_map->deepCopy();
        $this->fqsen_global_constant_map = $this->fqsen_global_constant_map->deepCopy();
        $this->fqsen_func_map = $this->fqsen_func_map->deepCopy();
        $this->func_and_method_set = $this->func_and_method_set->deepCopy();
        $this->class_fqsen_class_map_map = $this->class_fqsen_class_map_map->deepCopy();
        $name_method_map = $this->name_method_map;
        $this->name_method_map = [];
        foreach ($name_method_map as $name => $method_map) {
            $this->name_method_map[$name] = $method_map->deepCopy();
        }
    }
    /**
     * @return CodeBase
     * A new code base is returned which is a shallow clone
     * of this one, which is to say that the sets and maps
     * of elements themselves are cloned, but the keys and
     * values within those sets and maps are not cloned.
     *
     * Updates to elements will bleed through code bases
     * with only shallow clones. See
     * https://github.com/etsy/phan/issues/257
     */
    public function shallowClone()
    {
        $code_base = new CodeBase([], [], [], []);
        $code_base->fqsen_class_map = clone $this->fqsen_class_map;
        $code_base->fqsen_global_constant_map = clone $this->fqsen_global_constant_map;
        $code_base->fqsen_func_map = clone $this->fqsen_func_map;
        $code_base->class_fqsen_class_map_map = clone $this->class_fqsen_class_map_map;
        $code_base->func_and_method_set = clone $this->func_and_method_set;
        $ret5902c6f3c8074 = $code_base;
        if (!$ret5902c6f3c8074 instanceof CodeBase) {
            throw new \InvalidArgumentException("Argument returned must be of the type CodeBase, " . (gettype($ret5902c6f3c8074) == "object" ? get_class($ret5902c6f3c8074) : gettype($ret5902c6f3c8074)) . " given");
        }
        return $ret5902c6f3c8074;
    }
    /**
     * @param Clazz $class
     * A class to add.
     *
     * @return void
     */
    public function addClass(Clazz $class)
    {
        // Map the FQSEN to the class
        $this->fqsen_class_map[$class->getFQSEN()] = $class;
        if ($this->undo_tracker) {
            $this->undo_tracker->recordUndo(function (CodeBase $inner) use($class) {
                $fqsen = $class->getFQSEN();
                Daemon::debugf("Undoing addClass %s\n", $fqsen);
                unset($inner->fqsen_class_map[$fqsen]);
                unset($inner->class_fqsen_class_map_map[$fqsen]);
            });
        }
    }
    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasClassWithFQSEN(FullyQualifiedClassName $fqsen)
    {
        $ret5902c6f3c8549 = !empty($this->fqsen_class_map[$fqsen]);
        if (!is_bool($ret5902c6f3c8549)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3c8549) . " given");
        }
        return $ret5902c6f3c8549;
    }
    /**
     * @param FullyQualifiedClassName $fqsen
     * The FQSEN of a class to get
     *
     * @return Clazz
     * A class with the given FQSEN
     */
    public function getClassByFQSEN(FullyQualifiedClassName $fqsen)
    {
        $clazz = $this->fqsen_class_map[$fqsen];
        // This is an optimization that saves us a few minutes
        // on very large code bases.
        //
        // Instead of 'hydrating' all classes (expanding their
        // types and importing parent methods, properties, etc.)
        // all in one go, we just do it on the fly as they're
        // requested. When running as multiple processes this
        // lets us avoid a significant amount of hydration per
        // process.
        if ($this->should_hydrate_requested_elements) {
            $clazz->hydrate($this);
        }
        $ret5902c6f3c881c = $clazz;
        if (!$ret5902c6f3c881c instanceof Clazz) {
            throw new \InvalidArgumentException("Argument returned must be of the type Clazz, " . (gettype($ret5902c6f3c881c) == "object" ? get_class($ret5902c6f3c881c) : gettype($ret5902c6f3c881c)) . " given");
        }
        return $ret5902c6f3c881c;
    }
    /**
     * @return Map
     * A list of all classes
     */
    public function getClassMap()
    {
        $ret5902c6f3c8af8 = $this->fqsen_class_map;
        if (!$ret5902c6f3c8af8 instanceof Map) {
            throw new \InvalidArgumentException("Argument returned must be of the type Map, " . (gettype($ret5902c6f3c8af8) == "object" ? get_class($ret5902c6f3c8af8) : gettype($ret5902c6f3c8af8)) . " given");
        }
        return $ret5902c6f3c8af8;
    }
    /**
     * @param Method $method
     * A method to add to the code base
     *
     * @return void
     */
    public function addMethod(Method $method)
    {
        // Add the method to the map
        $this->getClassMapByFQSEN($method->getFQSEN())->addMethod($method);
        $this->func_and_method_set->attach($method);
        // If we're doing dead code detection and this is a
        // method, map the name to the FQSEN so we can do hail-
        // mary references.
        if (Config::get()->dead_code_detection) {
            if (empty($this->name_method_map[$method->getFQSEN()->getNameWithAlternateId()])) {
                $this->name_method_map[$method->getFQSEN()->getNameWithAlternateId()] = new Set();
            }
            $this->name_method_map[$method->getFQSEN()->getNameWithAlternateId()]->attach($method);
        }
        if ($this->undo_tracker) {
            // The addClass's recordUndo should remove the class map. Only need to remove it from func_and_method_set
            $this->undo_tracker->recordUndo(function (CodeBase $inner) use($method) {
                $inner->func_and_method_set->detach($method);
            });
        }
    }
    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasMethodWithFQSEN(FullyQualifiedMethodName $fqsen)
    {
        $ret5902c6f3c905a = $this->getClassMapByFQSEN($fqsen)->hasMethodWithName($fqsen->getNameWithAlternateId());
        if (!is_bool($ret5902c6f3c905a)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3c905a) . " given");
        }
        return $ret5902c6f3c905a;
    }
    /**
     * @param FullyQualifiedMethodName $fqsen
     * The FQSEN of a method to get
     *
     * @return Method
     * A method with the given FQSEN
     */
    public function getMethodByFQSEN(FullyQualifiedMethodName $fqsen)
    {
        $ret5902c6f3c92ff = $this->getClassMapByFQSEN($fqsen)->getMethodByName($fqsen->getNameWithAlternateId());
        if (!$ret5902c6f3c92ff instanceof Method) {
            throw new \InvalidArgumentException("Argument returned must be of the type Method, " . (gettype($ret5902c6f3c92ff) == "object" ? get_class($ret5902c6f3c92ff) : gettype($ret5902c6f3c92ff)) . " given");
        }
        return $ret5902c6f3c92ff;
    }
    /**
     * @return Method[]
     * The set of methods associated with the given class
     */
    public function getMethodMapByFullyQualifiedClassName(FullyQualifiedClassName $fqsen)
    {
        $ret5902c6f3c95fe = $this->getClassMapByFullyQualifiedClassName($fqsen)->getMethodMap();
        if (!is_array($ret5902c6f3c95fe)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3c95fe) . " given");
        }
        return $ret5902c6f3c95fe;
    }
    /**
     * @return Set
     * A set of all known methods with the given name
     */
    public function getMethodSetByName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Argument \$name passed to getMethodSetByName() must be of the type string, " . (gettype($name) == "object" ? get_class($name) : gettype($name)) . " given");
        }
        assert(Config::get()->dead_code_detection, __METHOD__ . ' can only be called when dead code ' . ' detection is enabled.');
        $ret5902c6f3c9977 = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$this->name_method_map[$name], @new Set());
        if (!$ret5902c6f3c9977 instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6f3c9977) == "object" ? get_class($ret5902c6f3c9977) : gettype($ret5902c6f3c9977)) . " given");
        }
        return $ret5902c6f3c9977;
    }
    /**
     * @return Set
     * The set of all methods and functions
     */
    public function getFunctionAndMethodSet()
    {
        $ret5902c6f3c9ecf = $this->func_and_method_set;
        if (!$ret5902c6f3c9ecf instanceof Set) {
            throw new \InvalidArgumentException("Argument returned must be of the type Set, " . (gettype($ret5902c6f3c9ecf) == "object" ? get_class($ret5902c6f3c9ecf) : gettype($ret5902c6f3c9ecf)) . " given");
        }
        return $ret5902c6f3c9ecf;
    }
    /**
     * @return string[][] -
     * A human readable encoding of $this->func_and_method_set [string $filename => [int|string $pos => string $spec]]
     * Excludes internal functions and methods.
     */
    public function exportFunctionAndMethodSet()
    {
        $result = [];
        foreach ($this->func_and_method_set as $function_or_method) {
            if ($function_or_method->isPHPInternal()) {
                continue;
            }
            $fqsen = $function_or_method->getFQSEN();
            $function_or_method_name = (string) $fqsen;
            $signature = [(string) $function_or_method->getUnionType()];
            foreach ($function_or_method->getParameterList() as $param) {
                $name = $param->getName();
                $paramType = (string) $param->getUnionType();
                if ($param->isVariadic()) {
                    $name = '...' . $name;
                }
                if ($param->isPassByReference()) {
                    $name = '&' . $name;
                }
                if ($param->isOptional()) {
                    $name = $name . '=';
                }
                $signature[$name] = $paramType;
            }
            $result[$function_or_method_name] = $signature;
        }
        ksort($result);
        $ret5902c6f3ca41c = $result;
        if (!is_array($ret5902c6f3ca41c)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3ca41c) . " given");
        }
        return $ret5902c6f3ca41c;
    }
    /**
     * @param Func $function
     * A function to add to the code base
     *
     * @return void
     */
    public function addFunction(Func $function)
    {
        // Add it to the map of functions
        $this->fqsen_func_map[$function->getFQSEN()] = $function;
        // Add it to the set of functions and methods
        $this->func_and_method_set->attach($function);
        if ($this->undo_tracker) {
            $this->undo_tracker->recordUndo(function (CodeBase $inner) use($function) {
                Daemon::debugf("Undoing addFunction on %s\n", $function->getFQSEN());
                unset($inner->fqsen_func_map[$function->getFQSEN()]);
                $inner->func_and_method_set->detach($function);
            });
        }
    }
    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasFunctionWithFQSEN(FullyQualifiedFunctionName $fqsen)
    {
        $has_function = $this->fqsen_func_map->contains($fqsen);
        if ($has_function) {
            $ret5902c6f3ca884 = $has_function;
            if (!is_bool($ret5902c6f3ca884)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3ca884) . " given");
            }
            return $ret5902c6f3ca884;
        }
        $ret5902c6f3caae1 = $this->hasInternalFunctionWithFQSEN($fqsen);
        if (!is_bool($ret5902c6f3caae1)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3caae1) . " given");
        }
        return $ret5902c6f3caae1;
    }
    /**
     * @param FullyQualifiedFunctionName $fqsen
     * The FQSEN of a function to get
     *
     * @return Func
     * A function with the given FQSEN
     */
    public function getFunctionByFQSEN(FullyQualifiedFunctionName $fqsen)
    {
        if (empty($this->fqsen_func_map[$fqsen])) {
            print "Not found {$fqsen}\n";
        }
        $ret5902c6f3cadfa = $this->fqsen_func_map[$fqsen];
        if (!$ret5902c6f3cadfa instanceof Func) {
            throw new \InvalidArgumentException("Argument returned must be of the type Func, " . (gettype($ret5902c6f3cadfa) == "object" ? get_class($ret5902c6f3cadfa) : gettype($ret5902c6f3cadfa)) . " given");
        }
        return $ret5902c6f3cadfa;
    }
    /**
     * @return Map
     */
    public function getFunctionMap()
    {
        $ret5902c6f3cb0d7 = $this->fqsen_func_map;
        if (!$ret5902c6f3cb0d7 instanceof Map) {
            throw new \InvalidArgumentException("Argument returned must be of the type Map, " . (gettype($ret5902c6f3cb0d7) == "object" ? get_class($ret5902c6f3cb0d7) : gettype($ret5902c6f3cb0d7)) . " given");
        }
        return $ret5902c6f3cb0d7;
    }
    /**
     * @param ClassConstant $class_constant
     * A class constant to add to the code base
     *
     * @return void
     */
    public function addClassConstant(ClassConstant $class_constant)
    {
        return $this->getClassMapByFQSEN($class_constant->getFQSEN())->addClassConstant($class_constant);
    }
    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasClassConstantWithFQSEN(FullyQualifiedClassConstantName $fqsen)
    {
        $ret5902c6f3cb460 = $this->getClassMapByFQSEN($fqsen)->hasClassConstantWithName($fqsen->getNameWithAlternateId());
        if (!is_bool($ret5902c6f3cb460)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3cb460) . " given");
        }
        return $ret5902c6f3cb460;
    }
    /**
     * @param FullyQualifiedClassConstantName $fqsen
     * The FQSEN of a class constant to get
     *
     * @return ClassConstant
     * A class constant with the given FQSEN
     */
    public function getClassConstantByFQSEN(FullyQualifiedClassConstantName $fqsen)
    {
        $ret5902c6f3cb702 = $this->getClassMapByFQSEN($fqsen)->getClassConstantByName($fqsen->getNameWithAlternateId());
        if (!$ret5902c6f3cb702 instanceof ClassConstant) {
            throw new \InvalidArgumentException("Argument returned must be of the type ClassConstant, " . (gettype($ret5902c6f3cb702) == "object" ? get_class($ret5902c6f3cb702) : gettype($ret5902c6f3cb702)) . " given");
        }
        return $ret5902c6f3cb702;
    }
    /**
     * @return ClassConstant[]
     * The set of class constants associated with the given class
     */
    public function getClassConstantMapByFullyQualifiedClassName(FullyQualifiedClassName $fqsen)
    {
        $ret5902c6f3cba04 = $this->getClassMapByFullyQualifiedClassName($fqsen)->getClassConstantMap();
        if (!is_array($ret5902c6f3cba04)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3cba04) . " given");
        }
        return $ret5902c6f3cba04;
    }
    /**
     * @param GlobalConstant $global_constant
     * A global constant to add to the code base
     *
     * @return void
     */
    public function addGlobalConstant(GlobalConstant $global_constant)
    {
        $this->fqsen_global_constant_map[$global_constant->getFQSEN()] = $global_constant;
        if ($this->undo_tracker) {
            $this->undo_tracker->recordUndo(function (CodeBase $inner) use($global_constant) {
                Daemon::debugf("Undoing addGlobalConstant on %s\n", $global_constant->getFQSEN());
                unset($inner->fqsen_global_constant_map[$global_constant->getFQSEN()]);
            });
        }
    }
    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasGlobalConstantWithFQSEN(FullyQualifiedGlobalConstantName $fqsen)
    {
        $ret5902c6f3cbdec = !empty($this->fqsen_global_constant_map[$fqsen]);
        if (!is_bool($ret5902c6f3cbdec)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3cbdec) . " given");
        }
        return $ret5902c6f3cbdec;
    }
    /**
     * @param FullyQualifiedGlobalConstantName $fqsen
     * The FQSEN of a global constant to get
     *
     * @return GlobalConstant
     * A global constant with the given FQSEN
     */
    public function getGlobalConstantByFQSEN(FullyQualifiedGlobalConstantName $fqsen)
    {
        $ret5902c6f3cc075 = $this->fqsen_global_constant_map[$fqsen];
        if (!$ret5902c6f3cc075 instanceof GlobalConstant) {
            throw new \InvalidArgumentException("Argument returned must be of the type GlobalConstant, " . (gettype($ret5902c6f3cc075) == "object" ? get_class($ret5902c6f3cc075) : gettype($ret5902c6f3cc075)) . " given");
        }
        return $ret5902c6f3cc075;
    }
    /**
     * @return Map
     */
    public function getGlobalConstantMap()
    {
        $ret5902c6f3cc34a = $this->fqsen_global_constant_map;
        if (!$ret5902c6f3cc34a instanceof Map) {
            throw new \InvalidArgumentException("Argument returned must be of the type Map, " . (gettype($ret5902c6f3cc34a) == "object" ? get_class($ret5902c6f3cc34a) : gettype($ret5902c6f3cc34a)) . " given");
        }
        return $ret5902c6f3cc34a;
    }
    /**
     * @param Property $property
     * A property to add to the code base
     *
     * @return void
     */
    public function addProperty(Property $property)
    {
        return $this->getClassMapByFQSEN($property->getFQSEN())->addProperty($property);
    }
    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasPropertyWithFQSEN(FullyQualifiedPropertyName $fqsen)
    {
        $ret5902c6f3cc6ce = $this->getClassMapByFQSEN($fqsen)->hasPropertyWithName($fqsen->getNameWithAlternateId());
        if (!is_bool($ret5902c6f3cc6ce)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3cc6ce) . " given");
        }
        return $ret5902c6f3cc6ce;
    }
    /**
     * @param FullyQualifiedPropertyName $fqsen
     * The FQSEN of a property to get
     *
     * @return Property
     * A property with the given FQSEN
     */
    public function getPropertyByFQSEN(FullyQualifiedPropertyName $fqsen)
    {
        $ret5902c6f3cc972 = $this->getClassMapByFQSEN($fqsen)->getPropertyByName($fqsen->getNameWithAlternateId());
        if (!$ret5902c6f3cc972 instanceof Property) {
            throw new \InvalidArgumentException("Argument returned must be of the type Property, " . (gettype($ret5902c6f3cc972) == "object" ? get_class($ret5902c6f3cc972) : gettype($ret5902c6f3cc972)) . " given");
        }
        return $ret5902c6f3cc972;
    }
    /**
     * @return Property[]
     * The set of properties associated with the given class
     */
    public function getPropertyMapByFullyQualifiedClassName(FullyQualifiedClassName $fqsen)
    {
        $ret5902c6f3ccc99 = $this->getClassMapByFullyQualifiedClassName($fqsen)->getPropertyMap();
        if (!is_array($ret5902c6f3ccc99)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3ccc99) . " given");
        }
        return $ret5902c6f3ccc99;
    }
    /**
     * @param FullyQualifiedClassElement $fqsen
     * The FQSEN of a class element
     *
     * @return ClassMap
     * Get the class map for an FQSEN representing
     * a class element
     */
    private function getClassMapByFQSEN(FullyQualifiedClassElement $fqsen)
    {
        $ret5902c6f3ccf25 = $this->getClassMapByFullyQualifiedClassName($fqsen->getFullyQualifiedClassName());
        if (!$ret5902c6f3ccf25 instanceof ClassMap) {
            throw new \InvalidArgumentException("Argument returned must be of the type ClassMap, " . (gettype($ret5902c6f3ccf25) == "object" ? get_class($ret5902c6f3ccf25) : gettype($ret5902c6f3ccf25)) . " given");
        }
        return $ret5902c6f3ccf25;
    }
    /**
     * @param FullyQualifiedClassName $fqsen
     * The FQSEN of a class
     *
     * @return ClassMap
     * Get the class map for an FQSEN representing
     * a class element
     */
    private function getClassMapByFullyQualifiedClassName(FullyQualifiedClassName $fqsen)
    {
        if (empty($this->class_fqsen_class_map_map[$fqsen])) {
            $this->class_fqsen_class_map_map[$fqsen] = new ClassMap();
        }
        $ret5902c6f3cd2b8 = $this->class_fqsen_class_map_map[$fqsen];
        if (!$ret5902c6f3cd2b8 instanceof ClassMap) {
            throw new \InvalidArgumentException("Argument returned must be of the type ClassMap, " . (gettype($ret5902c6f3cd2b8) == "object" ? get_class($ret5902c6f3cd2b8) : gettype($ret5902c6f3cd2b8)) . " given");
        }
        return $ret5902c6f3cd2b8;
    }
    /**
     * @return Map
     */
    public function getClassMapMap()
    {
        $ret5902c6f3cd595 = $this->class_fqsen_class_map_map;
        if (!$ret5902c6f3cd595 instanceof Map) {
            throw new \InvalidArgumentException("Argument returned must be of the type Map, " . (gettype($ret5902c6f3cd595) == "object" ? get_class($ret5902c6f3cd595) : gettype($ret5902c6f3cd595)) . " given");
        }
        return $ret5902c6f3cd595;
    }
    /**
     * @param FullyQualifiedFunctionName
     * The FQSEN of a function we'd like to look up
     *
     * @return bool
     * If the FQSEN represents an internal function that
     * hasn't been loaded yet, true is returned.
     */
    private function hasInternalFunctionWithFQSEN(FullyQualifiedFunctionName $fqsen)
    {
        // Only root namespaced functions will be found in
        // the internal function map.
        if ($fqsen->getNamespace() != '\\') {
            $ret5902c6f3cd8a2 = false;
            if (!is_bool($ret5902c6f3cd8a2)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3cd8a2) . " given");
            }
            return $ret5902c6f3cd8a2;
        }
        // For elements in the root namespace, check to see if
        // there's a static method signature for something that
        // hasn't been loaded into memory yet and create a
        // method out of it as its requested
        $function_signature_map = UnionType::internalFunctionSignatureMap();
        if (!empty($function_signature_map[$fqsen->getNameWithAlternateId()])) {
            $signature = $function_signature_map[$fqsen->getNameWithAlternateId()];
            // Add each method returned for the signature
            foreach (FunctionFactory::functionListFromSignature($this, $fqsen, $signature) as $i => $function) {
                $this->addFunction($function);
            }
            $ret5902c6f3cdc1a = true;
            if (!is_bool($ret5902c6f3cdc1a)) {
                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3cdc1a) . " given");
            }
            return $ret5902c6f3cdc1a;
        }
        $ret5902c6f3cde5f = false;
        if (!is_bool($ret5902c6f3cde5f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f3cde5f) . " given");
        }
        return $ret5902c6f3cde5f;
    }
    /**
     * @return int
     * The total number of elements of all types in the
     * code base.
     */
    public function totalElementCount()
    {
        $sum = count($this->getFunctionMap()) + count($this->getGlobalConstantMap()) + count($this->getClassMap());
        foreach ($this->getClassMapMap() as $class_map) {
            $sum += count($class_map->getClassConstantMap()) + count($class_map->getPropertyMap()) + count($class_map->getMethodMap());
        }
        $ret5902c6f3ce2e8 = $sum;
        if (!is_int($ret5902c6f3ce2e8)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f3ce2e8) . " given");
        }
        return $ret5902c6f3ce2e8;
    }
    /**
     * @return void;
     */
    public function flushDependenciesForFile($file_path)
    {
        if (!is_string($file_path)) {
            throw new \InvalidArgumentException("Argument \$file_path passed to flushDependenciesForFile() must be of the type string, " . (gettype($file_path) == "object" ? get_class($file_path) : gettype($file_path)) . " given");
        }
        // TODO: ...
    }
    /**
     * @return void
     */
    public function store()
    {
        // TODO: ...
    }
    /**
     * @return string[]
     * The list of files that depend on the code in the given
     * file path
     */
    public function dependencyListForFile($file_path)
    {
        if (!is_string($file_path)) {
            throw new \InvalidArgumentException("Argument \$file_path passed to dependencyListForFile() must be of the type string, " . (gettype($file_path) == "object" ? get_class($file_path) : gettype($file_path)) . " given");
        }
        $ret5902c6f3ce921 = [];
        if (!is_array($ret5902c6f3ce921)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f3ce921) . " given");
        }
        return $ret5902c6f3ce921;
    }
}