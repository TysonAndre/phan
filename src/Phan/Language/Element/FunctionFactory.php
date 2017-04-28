<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Type\NullType;
use Phan\Language\UnionType;
class FunctionFactory
{
    /**
     * @return Func[]
     * One or more (alternate) methods begotten from
     * reflection info and internal method data
     */
    public static function functionListFromName(CodeBase $code_base, $function_name)
    {
        if (!is_string($function_name)) {
            throw new \InvalidArgumentException("Argument \$function_name passed to functionListFromName() must be of the type string, " . (gettype($function_name) == "object" ? get_class($function_name) : gettype($function_name)) . " given");
        }
        $ret5902c6f59576d = self::functionListFromReflectionFunction($code_base, new \ReflectionFunction($function_name));
        if (!is_array($ret5902c6f59576d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f59576d) . " given");
        }
        return $ret5902c6f59576d;
    }
    /**
     * @return Func[]
     * One or more (alternate) methods begotten from
     * reflection info and internal method data
     */
    public static function functionListFromReflectionFunction(CodeBase $code_base, \ReflectionFunction $reflection_function)
    {
        $context = new Context();
        $parts = explode('\\', $reflection_function->getName());
        $method_name = array_pop($parts);
        $namespace = '\\' . implode('\\', $parts);
        $fqsen = FullyQualifiedFunctionName::make($namespace, $method_name);
        $function = new Func($context, $fqsen->getName(), new UnionType(), 0, $fqsen);
        $function->setNumberOfRequiredParameters($reflection_function->getNumberOfRequiredParameters());
        $function->setNumberOfOptionalParameters($reflection_function->getNumberOfParameters() - $reflection_function->getNumberOfRequiredParameters());
        $ret5902c6f595f6d = self::functionListFromFunction($function, $code_base);
        if (!is_array($ret5902c6f595f6d)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f595f6d) . " given");
        }
        return $ret5902c6f595f6d;
    }
    /**
     * @return Func[]
     * One or more (alternate) methods begotten from
     * reflection info and internal method data
     */
    public static function functionListFromSignature(CodeBase $code_base, FullyQualifiedFunctionName $fqsen, array $signature)
    {
        $context = new Context();
        $return_type = UnionType::fromStringInContext(array_shift($signature), $context, false);
        $func = new Func($context, $fqsen->getName(), $return_type, 0, $fqsen);
        $ret5902c6f59637b = self::functionListFromFunction($func, $code_base);
        if (!is_array($ret5902c6f59637b)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f59637b) . " given");
        }
        return $ret5902c6f59637b;
    }
    /**
     * @return Method[]
     */
    public static function methodListFromReflectionClassAndMethod(Context $context, CodeBase $code_base, \ReflectionClass $class, \ReflectionMethod $reflection_method)
    {
        $method_fqsen = FullyQualifiedMethodName::fromStringInContext($reflection_method->getName(), $context);
        $reflection_method = new \ReflectionMethod($class->getName(), $reflection_method->name);
        $method = new Method($context, $reflection_method->name, new UnionType(), $reflection_method->getModifiers(), $method_fqsen);
        $method->setNumberOfRequiredParameters($reflection_method->getNumberOfRequiredParameters());
        $method->setNumberOfOptionalParameters($reflection_method->getNumberOfParameters() - $reflection_method->getNumberOfRequiredParameters());
        if ($method->getIsMagicCall() || $method->getIsMagicCallStatic()) {
            $method->setNumberOfOptionalParameters(999);
            $method->setNumberOfRequiredParameters(0);
        }
        // FIXME: make this from ReflectionMethod->getReturnType
        $method->setRealReturnType(UnionType::fromReflectionType($reflection_method->getReturnType()));
        $method->setRealParameterList(Parameter::listFromReflectionParameterList($reflection_method->getParameters()));
        $ret5902c6f5968db = self::functionListFromFunction($method, $code_base);
        if (!is_array($ret5902c6f5968db)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f5968db) . " given");
        }
        return $ret5902c6f5968db;
    }
    /**
     * @param FunctionInterface $function
     * Get a list of methods hydrated with type information
     * for the given partial method
     *
     * @param CodeBase $code_base
     * The global code base holding all state
     *
     * @return FunctionInterface[]
     * A list of typed functions/methods based on the given method
     */
    private static function functionListFromFunction(FunctionInterface $function, CodeBase $code_base)
    {
        // See if we have any type information for this
        // internal function
        $map_list = UnionType::internalFunctionSignatureMapForFQSEN($function->getFQSEN());
        if (!$map_list) {
            $ret5902c6f596bf2 = [$function];
            if (!is_array($ret5902c6f596bf2)) {
                throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f596bf2) . " given");
            }
            return $ret5902c6f596bf2;
        }
        $alternate_id = 0;
        $ret5902c6f597d32 = array_map(function ($map) use($function, &$alternate_id) {
            $alternate_function = clone $function;
            $alternate_function->setFQSEN($alternate_function->getFQSEN()->withAlternateId($alternate_id++));
            // Set the return type if one is defined
            if (!empty($map['return_type'])) {
                $alternate_function->setUnionType($map['return_type']);
            }
            // Load parameter types if defined
            foreach (call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$map['parameter_name_type_map'], @[]) as $parameter_name => $parameter_type) {
                $flags = 0;
                $is_optional = false;
                // Check to see if its a pass-by-reference parameter
                if (strpos($parameter_name, '&') === 0) {
                    $flags |= \ast\flags\PARAM_REF;
                    $parameter_name = substr($parameter_name, 1);
                }
                // Check to see if its variadic
                if (strpos($parameter_name, '...') !== false) {
                    $flags |= \ast\flags\PARAM_VARIADIC;
                    $parameter_name = str_replace('...', '', $parameter_name);
                }
                // Check to see if its an optional parameter
                if (strpos($parameter_name, '=') !== false) {
                    $is_optional = true;
                    $parameter_name = str_replace('=', '', $parameter_name);
                }
                $parameter = new Parameter($function->getContext(), $parameter_name, $parameter_type, $flags);
                if ($is_optional) {
                    // TODO: could check isDefaultValueAvailable and getDefaultValue, for a better idea.
                    // I don't see any cases where this will be used for internal types, though.
                    $parameter->setDefaultValueType(NullType::instance(false)->asUnionType());
                }
                // Add the parameter
                $alternate_function->appendParameter($parameter);
            }
            // TODO: Store the "real" number of required parameters,
            // if this is out of sync with the extension's ReflectionMethod->getParameterList()?
            // (e.g. third party extensions may add more required parameters?)
            $alternate_function->setNumberOfRequiredParameters(array_reduce($alternate_function->getParameterList(), function ($carry, Parameter $parameter) {
                if (!is_int($carry)) {
                    throw new \InvalidArgumentException("Argument \$carry passed to () must be of the type int, " . (gettype($carry) == "object" ? get_class($carry) : gettype($carry)) . " given");
                }
                $ret5902c6f59747a = $carry + ($parameter->isOptional() ? 0 : 1);
                if (!is_int($ret5902c6f59747a)) {
                    throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f59747a) . " given");
                }
                return $ret5902c6f59747a;
            }, 0));
            $alternate_function->setNumberOfOptionalParameters(count($alternate_function->getParameterList()) - $alternate_function->getNumberOfRequiredParameters());
            if ($alternate_function instanceof Method) {
                if ($alternate_function->getIsMagicCall() || $alternate_function->getIsMagicCallStatic()) {
                    $alternate_function->setNumberOfOptionalParameters(999);
                    $alternate_function->setNumberOfRequiredParameters(0);
                }
            }
            $ret5902c6f597a54 = $alternate_function;
            if (!$ret5902c6f597a54 instanceof FunctionInterface) {
                throw new \InvalidArgumentException("Argument returned must be of the type FunctionInterface, " . (gettype($ret5902c6f597a54) == "object" ? get_class($ret5902c6f597a54) : gettype($ret5902c6f597a54)) . " given");
            }
            return $ret5902c6f597a54;
        }, $map_list);
        if (!is_array($ret5902c6f597d32)) {
            throw new \InvalidArgumentException("Argument returned must be of the type array, " . gettype($ret5902c6f597d32) . " given");
        }
        return $ret5902c6f597d32;
    }
}