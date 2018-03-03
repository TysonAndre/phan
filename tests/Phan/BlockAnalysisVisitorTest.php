<?php declare(strict_types=1);

namespace Phan\Tests;

use Phan\BlockAnalysisVisitor;

class BlockAnalysisVisitorTest extends BaseTest
{
    public function testPreOrderMethods()
    {
        $reflection_methods = (new ReflectionClass(BlockAnalysisVisitor::class))->getMethods();
        $expected_method_names = [];
        $actual_method_names = \array_values(BlockAnalysisVisitor::PRE_VISIT_LOOKUP_TABLE);
        sort($actual_method_names);

        foreach ($reflection_methods as $method) {
            if (stripos($method->getName(), 'preVisit') === 0) {
                $expected_method_names[] = $method->getName();
            }
        }
        sort($expected_method_names);
        $this->assertEquals($expected_method_names, $actual_method_names);
    }

    public function testPosstOrderMethods()
    {
        $reflection_methods = (new ReflectionClass(BlockAnalysisVisitor::class))->getMethods();
        $expected_method_names = [];
        $actual_method_names = \array_values(BlockAnalysisVisitor::POST_VISIT_LOOKUP_TABLE);
        sort($actual_method_names);

        foreach ($reflection_methods as $method) {
            if (stripos($method->getName(), 'postVisit') === 0) {
                $expected_method_names[] = $method->getName();
            }
        }
        sort($expected_method_names);
        $this->assertEquals($expected_method_names, $actual_method_names);
    }
}
