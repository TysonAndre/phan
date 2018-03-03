<?php declare(strict_types=1);

namespace Phan\Tests;

use Phan\BlockAnalysisVisitor;
use ReflectionClass;
use ReflectionParameter;

class BlockAnalysisVisitorTest extends BaseTest
{
    public function testPreOrderMethods()
    {
        $reflection_methods = (new ReflectionClass(BlockAnalysisVisitor::class))->getMethods();
        $expected_method_names = [];
        $actual_method_name_set = [];
        foreach (BlockAnalysisVisitor::PRE_VISIT_LOOKUP_TABLE as $name) {
            $actual_method_name_set[$name] = true;
        }
        ksort($actual_method_name_set);

        foreach ($reflection_methods as $method) {
            if (stripos($method->getName(), 'preVisit') === 0) {
                $expected_method_name_set[$method->getName()] = true;
                $this->assertSame(['ast\Node $node', 'Phan\Language\Context $context'], array_map(function(ReflectionParameter $parameter) {
                    return (string)$parameter->getType() . ' $' . $parameter->getName();
                }, $method->getParameters()));
            }
        }
        ksort($expected_method_name_set);
        $this->assertEquals($expected_method_name_set, $actual_method_name_set);
    }

    public function testPostOrderMethods()
    {
        $reflection_methods = (new ReflectionClass(BlockAnalysisVisitor::class))->getMethods();
        $actual_method_name_set = [];
        foreach (BlockAnalysisVisitor::POST_VISIT_LOOKUP_TABLE as $name) {
            $actual_method_name_set[$name] = true;
        }
        ksort($actual_method_name_set);

        foreach ($reflection_methods as $method) {
            if (stripos($method->getName(), 'postVisit') === 0) {
                $expected_method_name_set[$method->getName()] = true;
            }
        }
        ksort($expected_method_name_set);
        $this->assertEquals($expected_method_name_set, $actual_method_name_set);
    }
}
