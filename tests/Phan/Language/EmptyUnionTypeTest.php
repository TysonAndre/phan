<?php

declare(strict_types=1);

namespace Phan\Tests\Language;

use Closure;
use Generator;
use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\Element\Func;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Element\Method;
use Phan\Language\EmptyUnionType;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\FalseType;
use Phan\Language\Type\GenericArrayType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\ObjectType;
use Phan\Language\Type\TemplateType;
use Phan\Language\UnionType;
use Phan\Tests\BaseTest;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use TypeError;

use function count;

/**
 * Checks that EmptyUnionType behaves the same way as an empty UnionType instance
 * @phan-file-suppress PhanThrowTypeAbsentForCall
 */
final class EmptyUnionTypeTest extends BaseTest
{
    private const SKIPPED_METHOD_NAMES = [
        'unserialize',  // throws
        '__construct',
        '__clone',
        // UnionType implementation can't be optimized
        'withIsPossiblyUndefined',
        'isPossiblyUndefined',
        'getIsPossiblyUndefined',  // alias of isPossiblyUndefined
        'isDefinitelyUndefined',
        'withIsDefinitelyUndefined',
    ];

    public function testMethods(): void
    {
        $this->assertTrue(\class_exists(UnionType::class));  // Force the autoloader to load UnionType before attempting to load EmptyUnionType
        $failures = '';
        foreach ((new ReflectionClass(EmptyUnionType::class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }
            $method_name = $method->getName();
            if (\in_array($method_name, self::SKIPPED_METHOD_NAMES, true)) {
                continue;
            }
            $failures .= $this->checkHasSameImplementationForEmpty($method);
            $actual_class = $method->class;
            if (EmptyUnionType::class !== $actual_class) {
                $failures .= "unexpected declaring class $actual_class for $method_name\n";
            }
        }
        $this->assertSame('', \trim($failures));
    }

    /**
     * Returns the test errors to show as a string, or the empty string on success
     */
    public function checkHasSameImplementationForEmpty(ReflectionMethod $method): string
    {
        $method_name = $method->getName();
        if (!\method_exists(UnionType::class, $method_name)) {
            return '';
        }

        $empty_regular = new UnionType([], true, []);
        $empty_union_type = UnionType::empty();

        $candidate_arg_lists = $this->generateArgLists($method);
        if (count($candidate_arg_lists) === 0) {
            throw new RuntimeException("Failed to generate 1 or more candidate arguments lists for $method_name");
        }
        $failures = '';
        foreach ($candidate_arg_lists as $arg_list) {
            $expected_result = $empty_regular->{$method_name}(...$arg_list);
            $actual_result = $empty_union_type->{$method_name}(...$arg_list);
            if ($expected_result instanceof Generator && $actual_result instanceof Generator) {
                $expected_result = \iterator_to_array($expected_result);
                $actual_result = \iterator_to_array($actual_result);
            }
            if (!self::isSameResult($expected_result, $actual_result)) {
                $failures .= "Expected $method_name implementation to be the same for " . \serialize($arg_list) . ": " . \serialize($expected_result) . ' !== ' . \serialize($actual_result) . "\n";
            }
        }
        return $failures;
    }

    /**
     * @param mixed $expected_result
     * @param mixed $actual_result
     */
    private static function isSameResult($expected_result, $actual_result): bool
    {
        if ($expected_result === $actual_result) {
            return true;
        }
        if ($expected_result instanceof UnionType && $actual_result instanceof UnionType) {
            return $expected_result->getDebugRepresentation() === $actual_result->getDebugRepresentation();
        }
        return false;
    }

    /**
     * Generate one or more argument lists to test a method
     * implementation in a subclass of UnionType
     *
     * @return list<list>
     */
    public function generateArgLists(ReflectionMethod $method): array
    {
        $list_of_arg_list = [[]];

        foreach ($method->getParameters() as $param) {
            if ($param->isOptional()) {
                break;
            }
            $possible_new_args = $this->getPossibleArgValues($param);
            if (count($possible_new_args) === 0) {
                throw new RuntimeException("Failed to generate 1 or more candidate arguments for $param");
            }
            $new_list_of_arg_list = [];
            foreach ($possible_new_args as $arg) {
                foreach ($list_of_arg_list as $prev_args) {
                    $new_list_of_arg_list[] = \array_merge($prev_args, [$arg]);
                }
            }
            $list_of_arg_list = $new_list_of_arg_list;
        }
        if (count($list_of_arg_list) === 0) {
            throw new RuntimeException("Failed to generate 1 or more candidate arguments lists for $method");
        }
        return $list_of_arg_list;
    }

    /**
     * Helper method to determine what arguments to use
     * to brute force test this parameter of EmptyUnionType
     * @return list<mixed>
     */
    public function getPossibleArgValues(ReflectionParameter $param): array
    {
        $type = $param->getType();
        $type_name = Type::stringFromReflectionType($type);
        // @phan-suppress-next-line PhanSuspiciousTruthyString emitted because of comparison to ''
        switch ($type_name) {
            case 'bool':
                return [false, true];
            case '?array':
                return [[], null];
            case 'array':
                return [[]];
            case 'int':
                if ($param->getName() === 'key_type') {
                    return [GenericArrayType::KEY_INT, GenericArrayType::KEY_STRING, GenericArrayType::KEY_MIXED];
                }
                break;
            case CodeBase::class:
                return [new CodeBase([], [], [], [], [])];
            case Context::class:
                return [new Context()];
            case Type::class:
                return [
                    IntType::instance(false),
                    ArrayType::instance(false),
                    FalseType::instance(true),
                    ObjectType::instance(false),
                    MixedType::instance(false),
                    Type::fromFullyQualifiedString('\stdClass'),
                ];
            case UnionType::class:
                // TODO: Add tests of real union types
                return [
                    IntType::instance(false)->asPHPDocUnionType(),
                    IntType::instance(false)->asRealUnionType(),
                    UnionType::empty(),
                    new UnionType([FalseType::instance(false), ArrayType::instance(false)], true),
                    new UnionType([FalseType::instance(false), ArrayType::instance(false)], true, [FalseType::instance(false), ArrayType::instance(false)]),
                    ArrayType::instance(false)->asPHPDocUnionType(),
                    FalseType::instance(true)->asPHPDocUnionType(),
                    ObjectType::instance(false)->asPHPDocUnionType(),
                    ObjectType::instance(false)->asRealUnionType(),
                    MixedType::instance(false)->asPHPDocUnionType(),
                    Type::fromFullyQualifiedString('\stdClass')->asPHPDocUnionType(),
                ];
            case Closure::class:
                return [
                    /** @param mixed ...$unused_args */
                    static function (...$unused_args): bool {
                        return false;
                    },
                    /** @param mixed ...$unused_args */
                    static function (...$unused_args): bool {
                        return true;
                    },
                ];
            case FunctionInterface::class:
                return [
                    new Func(
                        new Context(),
                        'placeholder1',
                        UnionType::empty(),
                        0,
                        FullyQualifiedFunctionName::fromFullyQualifiedString('placeholder1'),
                        []
                    ),
                    new Method(
                        new Context(),
                        'placeholder2',
                        UnionType::empty(),
                        0,
                        FullyQualifiedMethodName::fromFullyQualifiedString('PlaceholderClass::placeholder2'),
                        []
                    ),
                ];
            case TemplateType::class:
                return [
                    TemplateType::instanceForId('T', false),
                    TemplateType::instanceForId('TKey', true),
                ];
            case '':
                if ($param->getName() === 'field_key') {
                    return ['', 'key', 0, 2, false, 2.5];
                }
                break;
        }
        throw new TypeError("Unable to handle param {$type_name} \${$param->getName()}");
    }
}
