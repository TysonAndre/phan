<?php declare(strict_types=1);

namespace Phan\Tests\Language;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\EmptyUnionType;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\BoolType;
use Phan\Language\Type\FalseType;
use Phan\Language\Type\ObjectType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\IterableType;
use Phan\Language\Type\StaticType;
use Phan\Language\Type\TrueType;
use Phan\Language\Type\GenericArrayType;
use Phan\Language\UnionType;
use Phan\Tests\BaseTest;

use Closure;
use Generator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use TypeError;

class EmptyUnionTypeTest extends BaseTest
{

    public function testMethods() {
        $this->assertTrue(class_exists(UnionType::class));  // Force the autoloader to load UnionType before attempting to load EmptyUnionType
        $failures = '';
        foreach ((new ReflectionClass(EmptyUnionType::class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }
            $method_name = $method->getName();
            if (\in_array($method_name, ['unserialize', '__construct'], true)) {
                continue;
            }
            $failures .= $this->checkHasSameImplementationForEmpty($method);
            $actual_class = $method->getDeclaringClass()->getName();
            if (EmptyUnionType::class !== $actual_class) {
                $failures .= "unexpected declaring class $actual_class for $method_name\n";
            }
        }
        $this->assertSame('', $failures);
    }

    public function checkHasSameImplementationForEmpty(ReflectionMethod $method) {
        $method_name = $method->getName();
        if (!method_exists(UnionType::class, $method_name)) {
            return '';
        }

        $empty_regular = new UnionType([]);
        $empty_subclass = UnionType::empty();

        $candidate_arg_lists = $this->generateArgLists($method);
        if (count($candidate_arg_lists) === 0) {
            throw new RuntimeException("Failed to generate 1 or more candidate arguments lists for $method_name");
        }
        $failures = '';
        foreach ($candidate_arg_lists as $arg_list) {
            $expected_result = $empty_regular->{$method_name}(...$arg_list);
            $actual_result = $empty_regular->{$method_name}(...$arg_list);
            if ($expected_result instanceof Generator && $actual_result instanceof Generator) {
                $expected_result = iterator_to_array($expected_result);
                $actual_result = iterator_to_array($actual_result);
            }
            if (!$this->isSameResultOfUnionType($expected_result, $actual_result)) {
                $failures .= sprintf(
                    "Expected %s implementation to be the same for args %s\nWant: %s\nGot:  %s\n",
                    $method_name,
                    serialize($arg_list),
                    serialize($expected_result),
                    serialize($actual_result)
                );
            }
        }
        return $failures;
    }

    private function isSameResultOfUnionType($expected_result, $actual_result) : bool {
        if ($expected_result instanceof UnionType && $actual_result instanceof UnionType) {
            return $actual_result->isEqualTo($expected_result);
        }
        return $expected_result === $actual_result;
    }

    /**
     * Generate one or more argument lists to test a method
     * implementation in a subclass of UnionType
     *
     * @return array<int,array>
     */
    public function generateArgLists(ReflectionMethod $method) : array {
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
                    $new_list_of_arg_list[] = array_merge($prev_args, [$arg]);
                }
            }
            $list_of_arg_list = $new_list_of_arg_list;
        }
        if (count($list_of_arg_list) === 0) {
            throw new RuntimeException("Failed to generate 1 or more candidate arguments lists for $param");
        }
        return $list_of_arg_list;
    }

    public function getPossibleArgValues(ReflectionParameter $param) : array {
        $type = $param->getType();
        $type_name = (string)$type;
        switch ($type_name) {
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
                    IntType::instance(false),
                    ArrayType::instance(false),
                    ArrayType::instance(true),
                    StaticType::instance(false),
                    FalseType::instance(true),
                    FalseType::instance(false),
                    TrueType::instance(true),
                    TrueType::instance(false),
                    BoolType::instance(false),
                    BoolType::instance(true),
                    IterableType::instance(true),
                    IterableType::instance(false),
                    ObjectType::instance(false),
                    MixedType::instance(false),
                    Type::fromFullyQualifiedString('\stdClass'),
                    Type::fromFullyQualifiedString('\ArrayObject'),
                    Type::fromFullyQualifiedString('\Traversable'),
                ];
            case UnionType::class:
                return $this->getCandidateUnionTypes();
            case Closure::class:
                return [
                    function(...$args) { return false; },
                    function(...$args) { return true; },
                ];
        }
        throw new TypeError("Unable to handle param {$type_name} \${$param->getName()}");
    }

    private function getCandidateUnionTypes() {
        static $types = null;
        if (!is_array($types)) {
            $types = [
                IntType::instance(false)->asUnionType(),
                new UnionType([IntType::instance(false)]),
                UnionType::empty(),
                new UnionType([FalseType::instance(false), ArrayType::instance(false)]),
                ArrayType::instance(false)->asUnionType(),
                ArrayType::instance(true)->asUnionType(),
                StaticType::instance(false)->asUnionType(),
                FalseType::instance(true)->asUnionType(),
                FalseType::instance(false)->asUnionType(),
                TrueType::instance(true)->asUnionType(),
                TrueType::instance(false)->asUnionType(),
                BoolType::instance(false)->asUnionType(),
                BoolType::instance(true)->asUnionType(),
                IterableType::instance(true)->asUnionType(),
                IterableType::instance(false)->asUnionType(),
                ObjectType::instance(false)->asUnionType(),
                MixedType::instance(false)->asUnionType(),
                Type::fromFullyQualifiedString('\stdClass')->asUnionType(),
                Type::fromFullyQualifiedString('\ArrayObject')->asUnionType(),
            ];
        }
        return $types;
    }
}
