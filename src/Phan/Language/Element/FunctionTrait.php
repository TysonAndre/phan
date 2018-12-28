<?php declare(strict_types=1);

namespace Phan\Language\Element;

use AssertionError;
use ast\Node;
use Closure;
use Phan\Analysis\ParameterTypesAnalyzer;
use Phan\AST\UnionTypeVisitor;
use Phan\CodeBase;
use Phan\Config;
use Phan\Issue;
use Phan\IssueFixSuggester;
use Phan\Language\Context;
use Phan\Language\FileRef;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\BoolType;
use Phan\Language\Type\ClosureDeclarationParameter;
use Phan\Language\Type\ClosureDeclarationType;
use Phan\Language\Type\FalseType;
use Phan\Language\Type\FunctionLikeDeclarationType;
use Phan\Language\Type\GenericArrayType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\StaticOrSelfType;
use Phan\Language\Type\TemplateType;
use Phan\Language\Type\TrueType;
use Phan\Language\Type\VoidType;
use Phan\Language\UnionType;

/**
 * This contains functionality common to global functions, closures, and methods
 * @see FunctionInterface - Classes using this trait use that interface
 * @phan-file-suppress PhanPluginDescriptionlessCommentOnPublicMethod
 */
trait FunctionTrait
{
    /**
     * @var Comment|null This is reused when quick mode is off.
     */
    protected $comment;

    /**
     * Did we initialize the inner scope of this method?
     * Deferred because hydrating parameter defaults requires having all class constants be known
     * @var bool This is set to null immediately after scope initialization is finished.
     */
    protected $is_inner_scope_initialized  = false;

    /** @return int flags from \Phan\Language\Element\Flags */
    abstract public function getPhanFlags() : int;

    /** @return bool true if all of the bits in $bits is true in $this->getPhanFlags() */
    abstract public function getPhanFlagsHasState(int $bits) : bool;

    /**
     * @param int $phan_flags
     *
     * @return void
     */
    abstract public function setPhanFlags(int $phan_flags);

    /**
     * @return string
     * The (not fully-qualified) name of this element.
     */
    abstract public function getName() : string;

    /**
     * @return FQSEN
     * The fully-qualified structural element name of this
     * structural element
     */
    abstract public function getFQSEN();

    /**
     * @return string
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getRepresentationForIssue() : string
    {
        return $this->getFQSEN()->__toString() . '()';
    }

    /**
     * @return string
     * The name of this structural element (without namespace/class),
     * or a string for FunctionLikeDeclarationType which lacks a real FQSEN
     * @suppress PhanUnreferencedPublicMethod bad inference?
     */
    public function getNameForIssue() : string
    {
        return $this->getName() . '()';
    }

    /**
     * @var int
     * The number of required parameters for the method
     */
    private $number_of_required_parameters = 0;

    /**
     * @var int
     * The number of optional parameters for the method.
     * Note that this is set to a large number in methods using varargs or func_get_arg*()
     */
    private $number_of_optional_parameters = 0;

    /**
     * @var int
     * The number of required (real) parameters for the method declaration.
     * For internal methods, ignores Phan's annotations.
     */
    private $number_of_required_real_parameters = 0;

    /**
     * @var int
     * The number of optional (real) parameters for the method declaration.
     * For internal methods, ignores Phan's annotations.
     * For user-defined methods, ignores presence of func_get_arg*()
     */
    private $number_of_optional_real_parameters = 0;

    /**
     * @var bool|null
     * Does any parameter type possibly require recursive analysis if more specific types are provided?
     * Caches the return value for $this->needsRecursiveAnalysis()
     */
    private $needs_recursive_analysis = null;

    /**
     * @var array<int,Parameter>
     * The list of parameters for this method
     * This will change while the method is being analyzed when the config quick_mode is false.
     */
    private $parameter_list = [];

    /**
     * @var ?int
     * The hash of the types for the list of parameters for this function/method.
     */
    private $parameter_list_hash = null;

    /**
     * @var ?bool
     * Whether or not this function/method has any pass by reference parameters.
     */
    private $has_pass_by_reference_parameters = null;

    /**
     * @var array<int,int>
     * If the types for a parameter list were checked,
     * this contains the recursion depth for a given integer hash (smaller is earlier in recursion)
     */
    private $checked_parameter_list_hashes = [];

    /**
     * @var array<int,Parameter>
     * The list of *real* (not from phpdoc) parameters for this method.
     * This does not change after initialization.
     */
    private $real_parameter_list = [];

    /**
     * @var array<string,UnionType>
     * The list of unmodified *phpdoc* parameter types for this method.
     * This does not change after initialization.
     */
    private $phpdoc_parameter_type_map = [];

    /**
     * @var array<int,string>
     * A list of parameter names that are output-only references
     */
    private $phpdoc_output_references = [];

    /**
     * @var ?UnionType
     * The unmodified *phpdoc* union type for this method.
     * Will be null without any (at)return statements.
     */
    private $phpdoc_return_type;

    /**
     * @var UnionType
     * The *real* (not from phpdoc) return type from this method.
     * This does not change after initialization.
     */
    private $real_return_type;

    /**
     * @var Closure|null (CodeBase, Context, Func|Method $func, Node[]|string[]|int[] $arg_list) => UnionType
     */
    private $return_type_callback = null;

    /**
     * @var Closure|null (CodeBase, Context, Func|Method $func, Node[]|string[]|int[] $arg_list) => void
     */
    private $function_call_analyzer_callback = null;

    /**
     * @var FunctionLikeDeclarationType|null (Lazily generated)
     */
    private $as_closure_declaration_type;

    /**
     * @var Type|null (Lazily generated)
     */
    private $as_generator_template_type;

    /**
     * @return int
     * The number of optional real parameters on this function/method.
     * This may differ from getNumberOfOptionalParameters()
     * for internal modules lacking proper reflection info,
     * or if the installed module version's API changed from what Phan's stubs used,
     * or if a function/method uses variadics/func_get_arg*()
     */
    public function getNumberOfOptionalRealParameters() : int
    {
        return $this->number_of_optional_real_parameters;
    }

    /**
     * @return int
     * The number of optional parameters on this method
     */
    public function getNumberOfOptionalParameters() : int
    {
        return $this->number_of_optional_parameters;
    }

    /**
     * The number of optional parameters
     *
     * @return void
     */
    public function setNumberOfOptionalParameters(int $number)
    {
        $this->number_of_optional_parameters = $number;
    }

    /**
     * @return int
     * The number of parameters in this function/method declaration.
     * Variadic parameters are counted only once.
     * TODO: Specially handle variadic parameters, either here or in ParameterTypesAnalyzer::analyzeOverrideRealSignature
     */
    public function getNumberOfRealParameters() : int
    {
        return (
            $this->getNumberOfRequiredRealParameters()
            + $this->getNumberOfOptionalRealParameters()
        );
    }

    /**
     * @return int
     * The maximum number of parameters to this function/method
     */
    public function getNumberOfParameters() : int
    {
        return (
            $this->getNumberOfRequiredParameters()
            + $this->getNumberOfOptionalParameters()
        );
    }

    /**
     * @return int
     * The number of required real parameters on this function/method.
     * This may differ for internal modules lacking proper reflection info,
     * or if the installed module version's API changed from what Phan's stubs used.
     */
    public function getNumberOfRequiredRealParameters() : int
    {
        return $this->number_of_required_real_parameters;
    }

    /**
     * @return int
     * The number of required parameters on this function/method
     */
    public function getNumberOfRequiredParameters() : int
    {
        return $this->number_of_required_parameters;
    }

    /**
     *
     * The number of required parameters
     *
     * @return void
     */
    public function setNumberOfRequiredParameters(int $number)
    {
        $this->number_of_required_parameters = $number;
    }

    /**
     * @return bool
     * True if this method had no return type defined when it
     * was defined (either in the signature itself or in the
     * docblock).
     */
    public function isReturnTypeUndefined() : bool
    {
        return $this->getPhanFlagsHasState(Flags::IS_RETURN_TYPE_UNDEFINED);
    }

    /**
     * @param bool $is_return_type_undefined
     * True if this method had no return type defined when it
     * was defined (either in the signature itself or in the
     * docblock).
     *
     * @return void
     */
    public function setIsReturnTypeUndefined(
        bool $is_return_type_undefined
    ) {
        $this->setPhanFlags(Flags::bitVectorWithState(
            $this->getPhanFlags(),
            Flags::IS_RETURN_TYPE_UNDEFINED,
            $is_return_type_undefined
        ));
    }

    /**
     * @return bool
     * True if this method returns a value
     * (i.e. it has a return with an expression)
     */
    public function getHasReturn() : bool
    {
        return $this->getPhanFlagsHasState(Flags::HAS_RETURN);
    }

    /**
     * @return bool
     * True if this method yields any value(i.e. it is a \Generator)
     */
    public function getHasYield() : bool
    {
        return $this->getPhanFlagsHasState(Flags::HAS_YIELD);
    }

    /**
     * @param bool $has_return
     * Set to true to mark this method as having a
     * return value
     *
     * @return void
     */
    public function setHasReturn(bool $has_return)
    {
        $this->setPhanFlags(Flags::bitVectorWithState(
            $this->getPhanFlags(),
            Flags::HAS_RETURN,
            $has_return
        ));
    }

    /**
     * @param bool $has_yield
     * Set to true to mark this method as having a
     * yield value
     *
     * @return void
     */
    public function setHasYield(bool $has_yield)
    {
        // TODO: In a future release of php-ast, this information will be part of the function node's flags.
        // (PHP 7.1+ only, not supported in PHP 7.0)
        $this->setPhanFlags(Flags::bitVectorWithState(
            $this->getPhanFlags(),
            Flags::HAS_YIELD,
            $has_yield
        ));
    }

    /**
     * @return array<int,Parameter>
     * A list of parameters on the method
     */
    public function getParameterList()
    {
        return $this->parameter_list;
    }

    /**
     * @return bool - Does any parameter type possibly require recursive analysis if more specific types are provided?
     *
     * If this returns true, there is at least one parameter and at least one of those can be overridden with a more specific type.
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function needsRecursiveAnalysis() : bool
    {
        return $this->needs_recursive_analysis ?? ($this->needs_recursive_analysis = $this->computeNeedsRecursiveAnalysis());
    }

    private function computeNeedsRecursiveAnalysis() : bool
    {
        if (!$this->getNode()) {
            // E.g. this can be the case for magic methods, internal methods, stubs, etc.
            return false;
        }

        foreach ($this->parameter_list as $parameter) {
            if ($parameter->getNonVariadicUnionType()->shouldBeReplacedBySpecificTypes()) {
                return true;
            }
            if ($parameter->isPassByReference() && $parameter->getReferenceType() !== Flags::IS_WRITE_REFERENCE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the $ith parameter for the **caller**.
     * In the case of variadic arguments, an infinite number of parameters exist.
     * (The callee would see variadic arguments(T ...$args) as a single variable of type T[],
     * while the caller sees a place expecting an expression of type T.
     *
     * @param int $i - offset of the parameter.
     * @return Parameter|null The parameter type that the **caller** observes.
     */
    public function getParameterForCaller(int $i)
    {
        $list = $this->parameter_list;
        if (count($list) === 0) {
            return null;
        }
        $parameter = $list[$i] ?? null;
        if ($parameter) {
            return $parameter->asNonVariadic();
        }
        $last_parameter = $list[count($list) - 1];
        if ($last_parameter->isVariadic()) {
            return $last_parameter->asNonVariadic();
        }
        return null;
    }

    /**
     * @param array<int,Parameter> $parameter_list
     * A list of parameters to set on this method
     * (When quick_mode is false, this is also called to temporarily
     * override parameter types, etc.)
     *
     * @return void
     * @internal
     */
    public function setParameterList(array $parameter_list)
    {
        $this->parameter_list = $parameter_list;
        if ($this->parameter_list_hash === null) {
            $this->initParameterListInfo();
        }
    }

    /**
     * Called to lazily initialize properties of $this derived from $this->parameter_list
     */
    private function initParameterListInfo()
    {
        $parameter_list = $this->parameter_list;
        $this->parameter_list_hash = self::computeParameterListHash($parameter_list);
        $has_pass_by_reference_parameters = false;
        foreach ($parameter_list as $param) {
            if ($param->isPassByReference()) {
                $has_pass_by_reference_parameters = true;
                break;
            }
        }
        $this->has_pass_by_reference_parameters = $has_pass_by_reference_parameters;
    }

    /**
     * Called to generate a hash of a given parameter list, to avoid calling this on the same parameter list twice.
     *
     * @param array<int,Parameter> $parameter_list
     *
     * @return int 32-bit or 64-bit hash. Not likely to collide unless there are around 2^16 possible union types on 32-bit, or around 2^32 on 64-bit.
     *    (Collisions aren't a concern; The memory/runtime would probably be a bigger issue than collisions in non-quick mode.)
     */
    private static function computeParameterListHash(array $parameter_list) : int
    {
        // Choosing a small value to fit inside of a packed array.
        if (\count($parameter_list) === 0) {
            return 0;
        }
        if (Config::get_quick_mode()) {
            return 0;
        }
        $param_repr = '';
        foreach ($parameter_list as $param) {
            $param_repr .= $param->getUnionType()->__toString() . ',';
        }
        $raw_bytes = \md5($param_repr, true);
        // @phan-suppress-next-line PhanPossiblyNullTypeReturn we checked
        return unpack(PHP_INT_SIZE === 8 ? 'q' : 'l', $raw_bytes)[1];
    }

    /**
     * @return array<int,Parameter> $parameter_list
     * A list of parameters (not from phpdoc) that were set on this method. The parameters will be cloned.
     */
    public function getRealParameterList()
    {
        // Excessive cloning, to ensure that this stays immutable.
        return array_map(/** @return Parameter */ function (Parameter $param) {
            return clone($param);
        }, $this->real_parameter_list);
    }

    /**
     * @param array<int,Parameter> $parameter_list
     * A list of parameters (not from phpdoc) to set on this method. The parameters will be cloned.
     *
     * @return void
     */
    public function setRealParameterList(array $parameter_list)
    {
        $this->real_parameter_list = array_map(/** @return Parameter */ function (Parameter $param) {
            return clone($param);
        }, $parameter_list);

        $required_count = 0;
        $optional_count = 0;
        foreach ($parameter_list as $parameter) {
            if ($parameter->isOptional()) {
                $optional_count++;
            } else {
                $required_count++;
            }
        }
        $this->number_of_required_real_parameters = $required_count;
        $this->number_of_optional_real_parameters = $optional_count;
    }

    /**
     * @param UnionType $union_type
     * The real (non-phpdoc) return type of this method in its given context.
     *
     * @return void
     */
    public function setRealReturnType(UnionType $union_type)
    {
        // TODO: was `self` properly resolved already? What about in subclasses?
        $this->real_return_type = $union_type;
    }

    /**
     * @return UnionType
     * The type of this method in its given context.
     */
    public function getRealReturnType() : UnionType
    {
        if (!$this->real_return_type) {
            // Incomplete patch for https://github.com/phan/phan/issues/670
            return UnionType::empty();
            // throw new \Error(sprintf("Failed to get real return type in %s method %s", (string)$this->getClassFQSEN(), (string)$this));
        }
        // Clone the union type, to be certain it will remain immutable.
        return $this->real_return_type;
    }

    /**
     * @param Parameter $parameter
     * A parameter to append to the parameter list
     *
     * @return void
     * @internal
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function appendParameter(Parameter $parameter)
    {
        $this->parameter_list[] = $parameter;
    }

    /**
     * @return void
     *
     * Call this before calling appendParameter, if parameters were already added.
     * @internal
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function clearParameterList()
    {
        $this->parameter_list = [];
        $this->parameter_list_hash = null;
    }

    /**
     * Adds types from comments to the params of a user-defined function or method.
     * Also adds the types from defaults, and emits warnings for certain violations.
     *
     * Conceptually, Func and Method should have defaults/comments analyzed in the same way.
     *
     * This does nothing if $function is for an internal method.
     *
     * @param Context $context
     * The context in which the node appears
     *
     * @param CodeBase $code_base
     *
     * @param FunctionInterface $function - A Func or Method to add params to the local scope of.
     *
     * @param Comment $comment - processed doc comment of $node, with params
     *
     * @return void
     */
    public static function addParamsToScopeOfFunctionOrMethod(
        Context $context,
        CodeBase $code_base,
        FunctionInterface $function,
        Comment $comment
    ) {
        if ($function->isPHPInternal()) {
            return;
        }
        $parameter_offset = 0;
        $function_parameter_list = $function->getParameterList();
        $real_parameter_name_map = [];
        foreach ($function_parameter_list as $parameter) {
            $real_parameter_name_map[$parameter->getName()] = $parameter;
            self::addParamToScopeOfFunctionOrMethod(
                $context,
                $code_base,
                $function,
                $comment,
                $parameter_offset,
                $parameter
            );
            ++$parameter_offset;
        }

        $valid_comment_parameter_type_map = [];
        foreach ($comment->getParameterMap() as $comment_parameter_name => $comment_parameter) {
            if (!\array_key_exists($comment_parameter_name, $real_parameter_name_map)) {
                Issue::maybeEmit(
                    $code_base,
                    $context,
                    count($real_parameter_name_map) > 0 ? Issue::CommentParamWithoutRealParam : Issue::CommentParamOnEmptyParamList,
                    $comment_parameter->getLineno(),
                    $comment_parameter_name,
                    (string)$function
                );
                continue;
            }
            // Record phpdoc types to check if they are narrower than real types, later.
            // Only keep non-empty types.
            $comment_parameter_type = $comment_parameter->getUnionType();
            if (!$comment_parameter_type->isEmpty()) {
                $valid_comment_parameter_type_map[$comment_parameter_name] = $comment_parameter_type;
            }
            if ($comment_parameter->isOutputReference()) {
                $real_parameter_name_map[$comment_parameter_name]->setIsOutputReference();
            }
        }
        $function->setPHPDocParameterTypeMap($valid_comment_parameter_type_map);
        if ($function instanceof Method) {
            $function->checkForTemplateTypes();
        }
        // Special, for libraries which use this for to document variadic param lists.
    }

    /**
     * Internally used.
     * @return void
     */
    public static function addParamToScopeOfFunctionOrMethod(
        Context $context,
        CodeBase $code_base,
        FunctionInterface $function,
        Comment $comment,
        int $parameter_offset,
        Parameter $parameter
    ) {
        if ($function->isPHPInternal()) {
            return;
        }
        $parameter_name = $parameter->getName();
        if ($parameter->getUnionType()->isEmpty()) {
            // If there is no type specified in PHP, check
            // for a docComment with @param declarations. We
            // assume order in the docComment matches the
            // parameter order in the code
            if ($comment->hasParameterWithNameOrOffset(
                $parameter_name,
                $parameter_offset
            )) {
                $comment_param = $comment->getParameterWithNameOrOffset(
                    $parameter_name,
                    $parameter_offset
                );
                $comment_param_type = $comment_param->getUnionType();
                if ($parameter->isVariadic() !== $comment_param->isVariadic()) {
                    Issue::maybeEmit(
                        $code_base,
                        $context,
                        $parameter->isVariadic() ? Issue::TypeMismatchVariadicParam : Issue::TypeMismatchVariadicComment,
                        $comment_param->getLineno(),
                        $comment_param->__toString(),
                        $parameter->__toString()
                    );
                }

                // if ($parameter->isCloneOfVariadic()) { throw new \Error("Impossible\n"); }
                $parameter->addUnionType($comment_param_type);
            }
        }

        // If there's a default value on the parameter, check to
        // see if the type of the default is cool with the
        // specified type.
        if ($parameter->hasDefaultValue()) {
            $default_type = $parameter->getDefaultValueType();
            $default_is_null = $default_type->isType(NullType::instance(false));
            // If the default type isn't null and can't cast
            // to the parameter's declared type, emit an
            // issue.
            if (!$default_is_null) {
                if (!$default_type->canCastToUnionType(
                    $parameter->getUnionType()
                )) {
                    Issue::maybeEmit(
                        $code_base,
                        $context,
                        Issue::TypeMismatchDefault,
                        $function->getFileRef()->getLineNumberStart(),
                        (string)$parameter->getUnionType(),
                        $parameter_name,
                        (string)$default_type
                    );
                }
            }

            // If there are no types on the parameter, the
            // default shouldn't be treated as the one
            // and only allowable type.
            $was_empty = $parameter->getUnionType()->isEmpty();

            // If we have no other type info about a parameter,
            // just because it has a default value of null
            // doesn't mean that is its type. Any type can default
            // to null
            if ($default_is_null) {
                if ($was_empty) {
                    $parameter->addUnionType(MixedType::instance(false)->asUnionType());
                }
                // The parameter constructor or above check for wasEmpty already took care of null default case
            } else {
                $default_type = $default_type->withFlattenedArrayShapeOrLiteralTypeInstances();
                if ($was_empty) {
                    $parameter->addUnionType(self::inferNormalizedTypesOfDefault($default_type));
                    if (!Config::getValue('guess_unknown_parameter_type_using_default')) {
                        $parameter->addUnionType(MixedType::instance(false)->asUnionType());
                    }
                } else {
                    // Don't add both `int` and `?int` to the same set.
                    foreach ($default_type->getTypeSet() as $default_type_part) {
                        if (!$parameter->getNonvariadicUnionType()->hasType($default_type_part->withIsNullable(true))) {
                            // if ($parameter->isCloneOfVariadic()) { throw new \Error("Impossible\n"); }
                            $parameter->addType($default_type_part);
                        }
                    }
                }
            }
        }
    }

    private static function inferNormalizedTypesOfDefault(UnionType $default_type) : UnionType
    {
        $type_set = $default_type->getTypeSet();
        if (\count($type_set) === 0) {
            return $default_type;
        }
        $normalized_default_type = new UnionType();
        foreach ($type_set as $type) {
            if ($type instanceof FalseType || $type instanceof NullType) {
                return MixedType::instance(false)->asUnionType();
            } elseif ($type instanceof GenericArrayType) {
                // Ideally should be the **only** type.
                $normalized_default_type = $normalized_default_type->withType(ArrayType::instance(false));
            } elseif ($type instanceof TrueType) {
                // e.g. for `function myFn($x = true) { }, $x is probably of type bool, but we're less sure about the type of $x from `$x = false`
                $normalized_default_type = $normalized_default_type->withType(BoolType::instance(false));
            } else {
                $normalized_default_type = $normalized_default_type->withType($type);
            }
        }
        return $normalized_default_type;
    }

    /**
     * @param array<string,UnionType> $parameter_map maps a subset of param names to the unmodified phpdoc parameter types. This may differ from real parameter types.
     * @return void
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function setPHPDocParameterTypeMap(array $parameter_map)
    {
        $this->phpdoc_parameter_type_map = $parameter_map;
    }

    /**
     * Records the fact that $parameter_name is an output-only reference.
     * @param string $parameter_name
     * @return void
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function recordOutputReferenceParamName(string $parameter_name)
    {
        $this->phpdoc_output_references[] = $parameter_name;
    }

    /**
     * @return array<int,string> list of output references. Usually empty.
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function getOutputReferenceParamNames() : array
    {
        return $this->phpdoc_output_references;
    }

    /**
     * @return array<string,UnionType> maps a subset of param names to the unmodified phpdoc parameter types.
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function getPHPDocParameterTypeMap()
    {
        return $this->phpdoc_parameter_type_map;
    }

    /**
     * @param ?UnionType $type the raw phpdoc union type
     * @return void
     */
    public function setPHPDocReturnType($type)
    {
        $this->phpdoc_return_type = $type;
    }

    /**
     * @return ?UnionType the raw phpdoc union type
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function getPHPDocReturnType()
    {
        return $this->phpdoc_return_type;
    }

    /**
     * Returns true if the param list has an instance of PassByReferenceVariable
     * If it does, the method has to be analyzed even if the same parameter types were analyzed already
     */
    private function hasPassByReferenceVariable(array $parameter_list) : bool
    {
        // Common case: function doesn't have any references in parameter list
        if ($this->has_pass_by_reference_parameters === false) {
            return false;
        }
        foreach ($parameter_list as $param) {
            if ($param instanceof PassByReferenceVariable) {
                return true;
            }
        }
        return false;
    }

    /**
     * analyzeWithNewParams is called only when the quick_mode config is false.
     * The new types are inferred based on the caller's types.
     * As an optimization, this refrains from re-analyzing the method/function it has already been analyzed for those param types
     * (With an equal or larger remaining recursion depth)
     *
     * @param array<int,Parameter> $parameter_list
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function analyzeWithNewParams(Context $context, CodeBase $code_base, array $parameter_list) : Context
    {
        $hash = $this->computeParameterListHash($parameter_list);
        $has_pass_by_reference_variable = null;
        // Nothing to do, except if PassByReferenceVariable was used
        if ($hash === $this->parameter_list_hash) {
            if (!$this->hasPassByReferenceVariable($parameter_list)) {
                // Have to analyze pass by reference variables anyway
                return $context;
            }
            $has_pass_by_reference_variable = true;
        }
        // Check if we've already analyzed this method with those given types,
        // with as much or even more depth left in the recursion.
        // (getRecursionDepth() increases as the program recurses downward)
        $old_recursion_depth_for_hash = $this->checked_parameter_list_hashes[$hash] ?? null;
        $new_recursion_depth_for_hash = $this->getRecursionDepth();
        if ($old_recursion_depth_for_hash !== null) {
            if ($new_recursion_depth_for_hash >= $old_recursion_depth_for_hash) {
                if (!($has_pass_by_reference_variable ?? $this->hasPassByReferenceVariable($parameter_list))) {
                    return $context;
                }
                // Have to analyze pass by reference variables anyway
                $new_recursion_depth_for_hash = $old_recursion_depth_for_hash;
            }
        }
        // Record the fact that it has already been analyzed,
        // along with the depth of recursion so far.
        $this->checked_parameter_list_hashes[$hash] = $new_recursion_depth_for_hash;
        return $this->analyze($context, $code_base);
    }

    /**
     * Analyze this with original parameter types or types from arguments.
     */
    abstract public function analyze(Context $context, CodeBase $code_base) : Context;

    /** @return int the current depth of recursive non-quick analysis. */
    abstract public function getRecursionDepth() : int;

    /** @return Node|null the node of this function-like's declaration, if any exist and were kept for recursive non-quick analysis. */
    abstract public function getNode();

    /** @return Context location and scope where this was declared. */
    abstract public function getContext() : Context;

    /** @return FileRef location where this was declared. */
    abstract public function getFileRef() : FileRef;

    /**
     * Returns true if the return type depends on the argument, and a plugin makes Phan aware of that.
     */
    public function hasDependentReturnType() : bool
    {
        return $this->return_type_callback !== null;
    }

    /**
     * Returns a union type based on $args_node and $context
     *
     * @param CodeBase $code_base
     * @param Context $context
     * @param array<int,Node|int|string|float> $args
     */
    public function getDependentReturnType(CodeBase $code_base, Context $context, array $args) : UnionType
    {
        // @phan-suppress-next-line PhanTypePossiblyInvalidCallable - Callers should check hasDependentReturnType
        return ($this->return_type_callback)($code_base, $context, $this, $args);
    }

    /**
     * @return void
     */
    public function setDependentReturnTypeClosure(Closure $closure)
    {
        $this->return_type_callback = $closure;
    }

    /**
     * Returns true if this function or method has additional analysis logic for invocations (From internal and user defined plugins)
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function hasFunctionCallAnalyzer() : bool
    {
        return $this->function_call_analyzer_callback !== null;
    }

    /**
     * Perform additional analysis logic for invocations (From internal and user defined plugins)
     *
     * @param CodeBase $code_base
     * @param Context $context
     * @param array<int,Node|int|string|float> $args
     * @return void
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function analyzeFunctionCall(CodeBase $code_base, Context $context, array $args)
    {
        // @phan-suppress-next-line PhanTypePossiblyInvalidCallable - Callers should check hasFunctionCallAnalyzer
        ($this->function_call_analyzer_callback)($code_base, $context, $this, $args);
    }

    /**
     * Make additional analysis logic of this function/method use $closure
     * If callers need to invoke multiple closures, they should pass in a closure to invoke multiple closures.
     * @return void
     */
    public function setFunctionCallAnalyzer(Closure $closure)
    {
        $this->function_call_analyzer_callback = $closure;
    }

    /**
     * @return ?Comment - Not set for internal functions/methods
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     * @return void
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function getThrowsUnionType() : UnionType
    {
        $comment = $this->comment;
        return $comment ? $comment->getThrowsUnionType() : UnionType::empty();
    }

    /**
     * Initialize the inner scope of this method with variables created from the parameters.
     *
     * Deferred until the parse phase because getting the UnionType of parameter defaults requires having all class constants be known.
     *
     * @return void
     */
    public function ensureScopeInitialized(CodeBase $code_base)
    {
        if ($this->is_inner_scope_initialized) {
            return;
        }
        $this->is_inner_scope_initialized = true;
        $comment = $this->comment;
        // $comment can be null for magic methods from `@method`
        if ($comment !== null) {
            if (!($this instanceof FunctionInterface)) {
                throw new AssertionError('Expected any class using FunctionTrait to implement FunctionInterface');
            }
            FunctionTrait::addParamsToScopeOfFunctionOrMethod($this->getContext(), $code_base, $this, $comment);
        }
    }

    /** @return void */
    abstract public function memoizeFlushAll();

    /** @return UnionType union type this function-like's declared return type (from PHPDoc, signatures, etc.)  */
    abstract public function getUnionType() : UnionType;

    /** @return void */
    abstract public function setUnionType(UnionType $type);

    /**
     * Creates a callback that can restore this element to the state it had before parsing.
     * @internal - Used by daemon mode
     * @return Closure
     */
    public function createRestoreCallback()
    {
        $clone_this = clone($this);
        foreach ($clone_this->parameter_list as $i => $parameter) {
            $clone_this->parameter_list[$i] = clone($parameter);
        }
        foreach ($clone_this->real_parameter_list as $i => $parameter) {
            $clone_this->real_parameter_list[$i] = clone($parameter);
        }
        $union_type = $this->getUnionType();

        return function () use ($clone_this, $union_type) {
            $this->memoizeFlushAll();
            // @phan-suppress-next-line PhanTypeSuspiciousNonTraversableForeach this is intentionally iterating over private properties of the clone.
            foreach ($clone_this as $key => $value) {
                $this->{$key} = $value;
            }
            $this->setUnionType($union_type);
        };
    }

    /**
     * Clone the parameter list, so that modifying the parameters on the first call won't modify the others.
     * TODO: If they're immutable, they can be shared without cloning with less worry.
     * @internal
     * @return void
     */
    public function cloneParameterList()
    {
        $this->setParameterList(
            \array_map(
                function (Parameter $parameter) : Parameter {
                    return clone($parameter);
                },
                $this->getParameterList()
            )
        );
    }

    /**
     * Returns a FunctionLikeDeclarationType based on phpdoc+real types.
     * The return value is used for type casting rule checking.
     * @suppress PhanUnreferencedPublicMethod Phan knows FunctionInterface's method is referenced, but can't associate that yet.
     */
    public function asFunctionLikeDeclarationType() : FunctionLikeDeclarationType
    {
        return $this->as_closure_declaration_type ?? ($this->as_closure_declaration_type = $this->createFunctionLikeDeclarationType());
    }

    /**
     * Does this function-like return a reference?
     */
    abstract public function returnsRef() : bool;

    private function createFunctionLikeDeclarationType() : FunctionLikeDeclarationType
    {
        $params = array_map(function (Parameter $parameter) : ClosureDeclarationParameter {
            return $parameter->asClosureDeclarationParameter();
        }, $this->getParameterList());

        $return_type = $this->getUnionType();
        if ($return_type->isEmpty()) {
            $return_type = MixedType::instance(false)->asUnionType();
        }
        return new ClosureDeclarationType(
            $this->getFileRef(),
            $params,
            $return_type,
            $this->returnsRef(),
            false
        );
    }

    /**
     * @return array<mixed,string> in the same format as FunctionSignatureMap.php
     * @throws \InvalidArgumentException if this function has invalid parameters for generating a stub (e.g. param names, types, etc.)
     */
    public function toFunctionSignatureArray() : array
    {
        $return_type = $this->getUnionType();
        $stub = [$return_type->__toString()];
        '@phan-var array<mixed,string> $stub';  // TODO: Should not warn about PhanTypeMismatchDimFetch in isset below
        foreach ($this->getParameterList() as $parameter) {
            $name = $parameter->getName();
            if (!$name || isset($stub[$name])) {
                throw new \InvalidArgumentException("Invalid name '$name' for {$this->getFQSEN()}");
            }
            if ($parameter->isOptional()) {
                $name .= '=';
            }
            $type_string = $parameter->getUnionType()->__toString();
            if ($parameter->isVariadic()) {
                $name = '...' . $name;
            }
            if ($parameter->isPassByReference()) {
                $name = '&' . $name;
            }
            $stub[$name] = $type_string;
        }
        return $stub;
    }

    /**
     * Precondition: This function is a generator type
     * Converts Generator|T[] to Generator<T>
     * Converts Generator|array<int,stdClass> to Generator<int,stdClass>, etc.
     */
    public function getReturnTypeAsGeneratorTemplateType() : Type
    {
        return $this->as_generator_template_type ?? ($this->as_generator_template_type = $this->getUnionType()->asGeneratorTemplateType());
    }

    /**
     * @var bool have the return types (both real and PHPDoc) of this method been analyzed and combined yet?
     */
    protected $did_analyze_return_types = false;

    /**
     * Check this method's return types (phpdoc and real) to make sure they're valid,
     * and infer a return type from the combination of the signature and phpdoc return types.
     *
     * @return void
     */
    public function analyzeReturnTypes(CodeBase $code_base)
    {
        if ($this->did_analyze_return_types) {
            return;
        }
        $this->did_analyze_return_types = true;
        $this->analyzeReturnTypesInner($code_base);
    }

    /**
     * Is this internal?
     */
    abstract public function isPHPInternal() : bool;

    /**
     * Returns this function's union type without resolving `static` in the function declaration's context.
     */
    abstract public function getUnionTypeWithUnmodifiedStatic() : UnionType;

    private function analyzeReturnTypesInner(CodeBase $code_base)
    {
        if ($this->isPHPInternal()) {
            // nothing to do, no known Node
            return;
        }
        $return_type = $this->getUnionTypeWithUnmodifiedStatic();
        $real_return_type = $this->getRealReturnType();
        $phpdoc_return_type = $this->getPHPDocReturnType();
        $context = $this->getContext();
        // TODO: use method->getPHPDocUnionType() to check compatibility, like analyzeParameterTypesDocblockSignaturesMatch

        // Look at each parameter to make sure their types
        // are valid

        // Look at each type in the function's return union type
        foreach ($return_type->withFlattenedArrayShapeOrLiteralTypeInstances()->getTypeSet() as $outer_type) {
            $type = $outer_type;
            // TODO: Expand this to ArrayShapeType, add unit test of `@return array{key:MissingClazz}`
            while ($type instanceof GenericArrayType) {
                $type = $type->genericArrayElementType();
            }

            // If its a native type or a reference to
            // self, its OK
            if ($type->isNativeType() || ($this instanceof Method && $type instanceof StaticOrSelfType)) {
                continue;
            }

            if ($type instanceof TemplateType) {
                if ($this instanceof Method) {
                    if ($this->isStatic() && !$this->declaresTemplateTypeInComment($type)) {
                        Issue::maybeEmit(
                            $code_base,
                            $context,
                            Issue::TemplateTypeStaticMethod,
                            $this->getFileRef()->getLineNumberStart(),
                            (string)$this->getFQSEN()
                        );
                    }
                }
                continue;
            }
            // Make sure the class exists
            $type_fqsen = FullyQualifiedClassName::fromType($type);
            if (!$code_base->hasClassWithFQSEN($type_fqsen)) {
                Issue::maybeEmitWithParameters(
                    $code_base,
                    $this->getContext(),
                    Issue::UndeclaredTypeReturnType,
                    $this->getFileRef()->getLineNumberStart(),
                    [$this->getNameForIssue(), (string)$outer_type],
                    IssueFixSuggester::suggestSimilarClass($code_base, $this->getContext(), $type_fqsen, null, 'Did you mean', IssueFixSuggester::CLASS_SUGGEST_CLASSES_AND_TYPES_AND_VOID)
                );
            }
        }
        if (Config::getValue('check_docblock_signature_return_type_match') && !$real_return_type->isEmpty() && ($phpdoc_return_type instanceof UnionType) && !$phpdoc_return_type->isEmpty()) {
            $resolved_real_return_type = $real_return_type->withStaticResolvedInContext($context);
            foreach ($phpdoc_return_type->getTypeSet() as $phpdoc_type) {
                $is_exclusively_narrowed = $phpdoc_type->isExclusivelyNarrowedFormOrEquivalentTo(
                    $resolved_real_return_type,
                    $context,
                    $code_base
                );
                // Make sure that the commented type is a narrowed
                // or equivalent form of the syntax-level declared
                // return type.
                if (!$is_exclusively_narrowed) {
                    Issue::maybeEmit(
                        $code_base,
                        $context,
                        Issue::TypeMismatchDeclaredReturn,
                        // @phan-suppress-next-line PhanAccessMethodInternal, PhanPartialTypeMismatchArgument TODO: Support inferring this is FunctionInterface
                        ParameterTypesAnalyzer::guessCommentReturnLineNumber($this) ?? $context->getLineNumberStart(),
                        $this->getName(),
                        $phpdoc_type->__toString(),
                        $real_return_type->__toString()
                    );
                }
                if ($is_exclusively_narrowed && Config::getValue('prefer_narrowed_phpdoc_return_type')) {
                    $normalized_phpdoc_return_type = ParameterTypesAnalyzer::normalizeNarrowedParamType($phpdoc_return_type, $real_return_type);
                    if ($normalized_phpdoc_return_type) {
                        // TODO: How does this currently work when there are multiple types in the union type that are compatible?
                        $this->setUnionType($normalized_phpdoc_return_type);
                    } else {
                        // This check isn't urgent to fix, and is specific to nullable casting rules,
                        // so use a different issue type.
                        Issue::maybeEmit(
                            $code_base,
                            $context,
                            Issue::TypeMismatchDeclaredReturnNullable,
                            // @phan-suppress-next-line PhanAccessMethodInternal, PhanPartialTypeMismatchArgument TODO: Support inferring this is FunctionInterface
                            ParameterTypesAnalyzer::guessCommentReturnLineNumber($this) ?? $context->getLineNumberStart(),
                            $this->getName(),
                            $phpdoc_type->__toString(),
                            $real_return_type->__toString()
                        );
                    }
                }
            }
        }
        if ($return_type->isEmpty() && !$this->getHasReturn()) {
            if ($this instanceof Func || ($this instanceof Method && ($this->isPrivate() || $this->isFinal() || $this->getIsMagicAndVoid() || $this->getClass($code_base)->isFinal()))) {
                $this->setUnionType(VoidType::instance(false)->asUnionType());
            }
        }
        foreach ($real_return_type->getTypeSet() as $type) {
            if (!$type->isObjectWithKnownFQSEN()) {
                continue;
            }
            $type_fqsen = FullyQualifiedClassName::fromType($type);
            if (!$code_base->hasClassWithFQSEN($type_fqsen)) {
                // We should have already warned
                continue;
            }
            $class = $code_base->getClassByFQSEN($type_fqsen);
            if ($class->isTrait()) {
                Issue::maybeEmit(
                    $code_base,
                    $context,
                    Issue::TypeInvalidTraitReturn,
                    $this->getFileRef()->getLineNumberStart(),
                    $this->getNameForIssue(),
                    $type_fqsen->__toString()
                );
            }
        }
        $comment = $this->comment;
        if ($comment) {
            $template_type_list = $comment->getTemplateTypeList();
            if ($template_type_list) {
                $this->addClosureForDependentTemplateType($code_base, $context, $template_type_list);
            }
        }
    }

    /**
     * Does this function/method declare an (at)template type for this type?
     */
    public function declaresTemplateTypeInComment(TemplateType $template_type) : bool
    {
        $comment = $this->comment;
        if ($comment) {
            // Template types are identical if they have the same name. See TemplateType::instanceForId.
            return \in_array($template_type, $comment->getTemplateTypeList(), true);
        }
        return false;
    }

    /**
     * @param TemplateType[] $template_type_list
     */
    private function addClosureForDependentTemplateType(CodeBase $code_base, Context $context, array $template_type_list)
    {
        if ($this->hasDependentReturnType()) {
            // We already added this or this conflicts with a plugin.
            return;
        }
        if (!$template_type_list) {
            // Shouldn't happen
            return;
        }
        $return_type = $this->getUnionType();
        $parameter_extracter_map = [];
        foreach ($template_type_list as $template_type) {
            $template_type_map = [$template_type->getName() => MixedType::instance(false)->asUnionType()];
            $return_type_with_template = $return_type->withTemplateParameterTypeMap($template_type_map);
            if ($return_type_with_template->isEqualTo($return_type)) {
                Issue::maybeEmit(
                    $code_base,
                    $context,
                    Issue::TemplateTypeNotUsedInFunctionReturn,
                    $context->getLineNumberStart(),
                    $template_type,
                    $this->getNameForIssue()
                );
                return;
            }
            $parameter_extracter = $this->getTemplateTypeExtractorClosure($code_base, $template_type);
            if (!$parameter_extracter) {
                Issue::maybeEmit(
                    $code_base,
                    $context,
                    Issue::TemplateTypeNotDeclaredInFunctionParams,
                    $context->getLineNumberStart(),
                    $template_type,
                    $this->getNameForIssue()
                );
                return;
            }
            $parameter_extracter_map[$template_type->getName()] = $parameter_extracter;
        }
        // Resolve the template types based on the parameters passed to the function
        $analyzer = function (CodeBase $code_base, Context $context, FunctionInterface $function, array $args) use ($parameter_extracter_map) : UnionType {
            $args_types = array_map(
                /**
                 * @param mixed $node
                 */
                function ($node) use ($code_base, $context) : UnionType {
                    return UnionTypeVisitor::unionTypeFromNode($code_base, $context, $node);
                },
                $args
            );
            $template_type_map = [];
            foreach ($parameter_extracter_map as $name => $closure) {
                // TODO: Do a better job of
                $template_type_map[$name] = $closure ? $closure($args_types) : UnionType::empty();
            }
            return $function->getUnionType()->withTemplateParameterTypeMap($template_type_map);
        };
        $this->setDependentReturnTypeClosure($analyzer);
    }

    /**
     * @param TemplateType $template_type the template type that this function is looking for references to in parameters
     *
     * @return ?Closure(array<int,UnionType>):UnionType
     */
    public function getTemplateTypeExtractorClosure(CodeBase $code_base, TemplateType $template_type)
    {
        $closure = null;
        foreach ($this->parameter_list as $i => $parameter) {
            $closure_for_type = $parameter->getUnionType()->getTemplateTypeExtractorClosure($code_base, $template_type);
            if (!$closure_for_type) {
                continue;
            }
            $closure = TemplateType::combineParameterClosures(
                $closure,
                function (array $parameters) use ($i, $closure_for_type) : UnionType {
                    $param_type = $parameters[$i] ?? null;
                    if ($param_type) {
                        return $closure_for_type($param_type);
                    }
                    return UnionType::empty();
                }
            );
        }
        return $closure;
    }
}
