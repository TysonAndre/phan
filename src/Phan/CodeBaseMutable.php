<?php declare(strict_types=1);
namespace Phan;

use Phan\CodeBase;
use Phan\CodeBase\ClassMap;
use Phan\Language\Element\ClassConstant;
use Phan\Language\Element\Clazz;
use Phan\Language\Element\Func;
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
use Phan\Library\Map;
use Phan\Library\Set;

/**
 * A code base where files can be parsed and parsed files can be removed.
 * TODO: Change to a better name - CodeBaseUndoable?
 *
 * TODO: Splitting this up wasn't worth the effort and changes to the code base.
 * Add a boolean which indicates whether or not CodeBase is currently "mutable", and depending on that,
 * conditionally start tracking undoable operations.
 * Once the parse phase is over, discard all of the undoable operations.
 */
class CodeBaseMutable implements CodeBaseInterface {
    /**
     * @var CodeBase
     */
    private $inner;

    /**
     * @var string|null absolute path to file
     */
    private $current_parsed_file;

    /**
     * @var \Closure[][] operations to undo for a current path
     */
    private $undoOperationsForPath = [];

    /**
     * @var string[] Maps file paths to the modification dates and file size of the paths. - On ext4, milliseconds are available, but php APIs all return seconds.
     */
    private $fileModificationState = [];

    /**
     * @return string|null - This string should change when the file is modified. Returns null if the file somehow doesn't exist
     */
    public static function getFileState(string $path) {
        clearstatcache(true, $path);  // TODO: does this work properly with symlinks?
        $real = realpath($path);
        if (!$real) {
            return null;
        }
        $stat = @stat($real);
        if (!$stat) {
            return null;  // It was missing or unreadable.
        }
        return sprintf('%d_%d', $stat['mtime'], $stat['size']);
    }

    /**
     * @return string[] - The list of files which are successfully parsed.
     * This changes whenever the file list is reloaded from disk.
     * This also includes files which don't declare classes or functions or globals,
     * because those files use classes/functions/constants.
     *
     * (This is the list prior to any analysis exclusion or whitelisting steps)
     */
    public function getParsedFilePathList() : array {
        return array_keys($this->fileModificationState);
    }

    /**
     * @return string[] - The size of $this->getParsedFilePathList()
     */
    public function getParsedFilePathCount() : int {
        return count($this->fileModificationState);
    }

    /**
     * Initialize a new CodeBaseMutable.
     *
     * This wraps an initialized CodeBase, allowing changes(set, add, etc.) to be undone when files are removed or updated
     */
    public function __construct(CodeBase $inner) {
        $this->inner = $inner;
    }

    public function getInner() : CodeBase{
        return $this->inner;
    }

    /**
     * @return void
     */
    public function setCurrentParsedFile(string $current_parsed_file = null) {
        if (is_string($current_parsed_file)) {
            // printf("Recording file modification state for %s\n", $current_parsed_file);
            $this->fileModificationState[$current_parsed_file] = self::getFileState($current_parsed_file);
        }
        $this->current_parsed_file = $current_parsed_file;
    }

    public function recordUnparseableFile(string $current_parsed_file) {
        unset($this->fileModificationState[$current_parsed_file]);
    }

    /**
     * @param string[] $new_file_list
     * @return string[] - Subset of $new_file_list which changed on disk and has to be parsed again. Automatically unparses the old versions of files which were modified.
     */
    public function updateFileList(array $new_file_list) {
        $new_file_set = [];
        foreach ($new_file_list as $path) {
            $new_file_set[$path] = true;
        }
        $changed_or_added_file_list = [];
        foreach ($new_file_list as $path) {
            if (!isset($this->fileModificationState[$path])) {
                $changed_or_added_file_list[] = $path;
            }
        }
        foreach ($this->fileModificationState as $path => $state) {
            if (!isset($new_file_set[$path])) {
                $this->undoFileChanges($path);
                unset($this->fileModificationState[$path]);
                continue;
            }
            $newState = self::getFileState($path);
            if ($newState !== $state) {
                $this->undoFileChanges($path);
                // TODO: This will call stat() twice as much as necessary for the modified files. Not important.
                unset($this->fileModificationState[$path]);
                if ($newState !== null) {
                    $changed_or_added_file_list[] = $path;
                }
            }
        }
        return $changed_or_added_file_list;
    }

    private function undoFileChanges(string $path) {
        foreach ($this->undoOperationsForPath[$path] ?? [] as $undo_operation) {
            $undo_operation($this->inner);
        }
        unset($this->undoOperationsForPath[$path]);
    }

    /**
     * @param \Closure $undo_operation - a closure expecting 1 param - inner. It undoes a change caused by a parsed file.
     * Ideally, this would extend to all changes.
     *
     * @return void
     */
    private function recordUndo(\Closure $undo_operation) {
        $file = $this->current_parsed_file;
        if (!is_string($file)) {
            throw new \Error("Called recordUndo in CodeBaseMutable, but not parsing a file");
        }
        if (!isset($this->undoOperationsForPath[$file])) {
            $this->undoOperationsForPath[$file] = [];
        }
        $this->undoOperationsForPath[$file][] = $undo_operation;
    }

    // End of CodeBaseMutable-specific methods.

    /**
     * Clone dependent objects when cloning this object.
     */
    public function __clone() {
        throw new \Error("Unsupported __clone on CodeBaseMutable");
    }

    // public function shallowClone() : CodeBaseInterface; // only used in tests

    /**
     * @param Clazz $class
     * A class to add.
     *
     * @return void
     */
    public function addClass(Clazz $class) {
        $this->inner->addClass($class);
        $this->recordUndo(function(CodeBase $inner) use($class) {
            // echo "Calling removeClass " . (string)$class->getFQSEN() . "\n";
            $inner->removeClass($class);
        });
    }

    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasClassWithFQSEN(
        FullyQualifiedClassName $fqsen
    ) : bool {
        return $this->inner->hasClassWithFQSEN($fqsen);
    }

    /**
     * @param FullyQualifiedClassName $fqsen
     * The FQSEN of a class to get
     *
     * @return Clazz
     * A class with the given FQSEN
     */
    public function getClassByFQSEN(
        FullyQualifiedClassName $fqsen
    ) : Clazz {
        return $this->inner->getClassByFQSEN($fqsen);
    }

    /**
     * @return Map
     * A list of all classes
     */
    public function getClassMap() : Map {
        // TODO: investigate uses, make this read-only if possible.
        return $this->inner->getClassMap();
    }

    /**
     * @param Method $method
     * A method to add to the code base
     *
     * @return void
     */
    public function addMethod(Method $method) {
        $this->recordUndo(function(CodeBase $inner) use($method) {
            // echo "Calling removeMethod " . (string)$method->getFQSEN() . "\n";
            $inner->removeMethod($method);
        });
        return $this->inner->addMethod($method);
    }

    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasMethodWithFQSEN(
        FullyQualifiedMethodName $fqsen
    ) : bool {
        return $this->inner->hasMethodWithFQSEN($fqsen);
    }

    /**
     * @param FullyQualifiedMethodName $fqsen
     * The FQSEN of a method to get
     *
     * @return Method
     * A method with the given FQSEN
     */
    public function getMethodByFQSEN(
        FullyQualifiedMethodName $fqsen
    ) : Method {
        return $this->inner->getMethodByFQSEN($fqsen);
    }

    /**
     * @return Method[]
     * The set of methods associated with the given class
     */
    public function getMethodMapByFullyQualifiedClassName(
        FullyQualifiedClassName $fqsen
    ) : array {
        return $this->inner->getMethodMapByFullyQualifiedClassName($fqsen);
    }

    /**
     * @return Set
     * A set of all known methods with the given name
     */
    public function getMethodSetByName(string $name) : Set {
        return $this->inner->getMethodSetByName($name);
    }

    /**
     * @return Set
     * The set of all methods and functions
     */
    public function getFunctionAndMethodSet() : Set {
        return $this->inner->getFunctionAndMethodSet();
    }

    // Should be converted to a CodeBase before this is called.
    // public function exportFunctionAndMethodSet();

    /**
     * @param Func $function
     * A function to add to the code base
     *
     * @return void
     */
    public function addFunction(Func $function)
    {
        // TODO: What about duplicate functions? will they have different FQSENs, and leave gaps when they're removed?
        $this->inner->addFunction($function);
        $this->recordUndo(function(CodeBase $inner) use($function) {
            // echo "Calling removeFunction " . (string)$function->getFQSEN() . "\n";
            $inner->removeFunction($function);
        });
    }

    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasFunctionWithFQSEN(
        FullyQualifiedFunctionName $fqsen
    ) : bool
    {
        return $this->inner->hasFunctionWithFQSEN($fqsen);
    }

    /**
     * @param FullyQualifiedFunctionName $fqsen
     * The FQSEN of a function to get
     *
     * @return Func
     * A function with the given FQSEN
     */
    public function getFunctionByFQSEN(
        FullyQualifiedFunctionName $fqsen
    ) : Func
    {
        return $this->inner->getFunctionByFQSEN($fqsen);
    }

    /**
     * @return Map
     */
    public function getFunctionMap() : Map
    {
        // TODO: Where is this called from?
        return $this->inner->getFunctionMap();
    }

    /**
     * @param ClassConstant $class_constant
     * A class constant to add to the code base
     *
     * @return void
     */
    public function addClassConstant(ClassConstant $class_constant)
    {
        return $this->inner->addClassConstant($class_constant);
    }

    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasClassConstantWithFQSEN(
        FullyQualifiedClassConstantName $fqsen
    ) : bool {
        return $this->inner->hasClassConstantWithFQSEN($fqsen);
    }

    /**
     * @param FullyQualifiedClassConstantName $fqsen
     * The FQSEN of a class constant to get
     *
     * @return ClassConstant
     * A class constant with the given FQSEN
     */
    public function getClassConstantByFQSEN(
        FullyQualifiedClassConstantName $fqsen
    ) : ClassConstant {
        return $this->inner->getClassConstantByFQSEN($fqsen);
    }

    /**
     * @return ClassConstant[]
     * The set of class constants associated with the given class
     */
    public function getClassConstantMapByFullyQualifiedClassName(
        FullyQualifiedClassName $fqsen
    ) : array {
        return $this->inner->getClassConstantMapByFullyQualifiedClassName($fqsen);
    }

    /**
     * @param GlobalConstant $global_constant
     * A global constant to add to the code base
     *
     * @return void
     */
    public function addGlobalConstant(GlobalConstant $global_constant)
    {
        $this->inner->addGlobalConstant($global_constant);
        $this->recordUndo(function(CodeBase $inner) use ($global_constant) {
            $inner->removeGlobalConstant($global_constant);
        });
    }

    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasGlobalConstantWithFQSEN(
        FullyQualifiedGlobalConstantName $fqsen
    ) : bool {
        return $this->inner->hasGlobalConstantWithFQSEN($fqsen);
    }

    /**
     * @param FullyQualifiedGlobalConstantName $fqsen
     * The FQSEN of a global constant to get
     *
     * @return GlobalConstant
     * A global constant with the given FQSEN
     */
    public function getGlobalConstantByFQSEN(
        FullyQualifiedGlobalConstantName $fqsen
    ) : GlobalConstant {
        return $this->inner->getGlobalConstantByFQSEN($fqsen);
    }

    /**
     * @return Map
     */
    public function getGlobalConstantMap() : Map {
        return $this->inner->getGlobalConstantMap();
    }

    /**
     * @param Property $property
     * A property to add to the code base
     *
     * @return void
     */
    public function addProperty(Property $property) {
        // should be undone by addClass's undo
        $this->inner->addProperty($property);
    }

    /**
     * @return bool
     * True if an element with the given FQSEN exists
     */
    public function hasPropertyWithFQSEN(
        FullyQualifiedPropertyName $fqsen
    ) : bool {
        return $this->inner->hasPropertyWithFQSEN($fqsen);
    }

    /**
     * @param FullyQualifiedPropertyName $fqsen
     * The FQSEN of a property to get
     *
     * @return Property
     * A property with the given FQSEN
     */
    public function getPropertyByFQSEN(
        FullyQualifiedPropertyName $fqsen
    ) : Property {
        return $this->inner->getPropertyByFQSEN($fqsen);
    }

    /**
     * @return Property[]
     * The set of properties associated with the given class
     */
    public function getPropertyMapByFullyQualifiedClassName(
        FullyQualifiedClassName $fqsen
    ) : array {
        // TODO: why is this public?
        return $this->inner->getPropertyMapByFullyQualifiedClassName($fqsen);
    }

    /**
     * Will be used in subclass for daemon mode?
     * @return void;
     */
    public function flushDependenciesForFile(string $file_path) {
        // TODO: implement this if implementing dependencyListForFile
        // ...
    }

    /**
     * @return string[]
     * The list of files that depend on the code in the given
     * file path
     * TODO: This is a stub.
     */
    public function dependencyListForFile(string $file_path) : array {
        return [];
    }
}
