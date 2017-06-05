<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Tests\Language;

use Phan\CodeBase;
use Phan\Config;
use Phan\Language\Context;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedMethodName;
use Phan\Language\Scope\ClassScope;
use Phan\Language\Scope\FunctionLikeScope;
use Phan\Tests\BaseTest;
use Phan\Parse\ParseVisitor;
class ContextTest extends BaseTest
{
    /** @var CodeBase|null */
    protected $code_base = null;
    protected function setUp()
    {
        $this->code_base = new CodeBase([], [], [], [], []);
    }
    protected function tearDown()
    {
        $this->code_base = null;
    }
    public function testSimple()
    {
        $context = new Context();
        $context_namespace = $context->withNamespace('\\A');
        $context_class = $context_namespace->withScope(new ClassScope($context_namespace->getScope(), FullyQualifiedClassName::fromFullyQualifiedString('\\A\\B')));
        $context_method = $context_namespace->withScope(new FunctionLikeScope($context_namespace->getScope(), FullyQualifiedMethodName::fromFullyQualifiedString('\\A\\b::c')));
        $this->assertTrue(!empty($context));
        $this->assertTrue(!empty($context_namespace));
        $this->assertTrue(!empty($context_class));
        $this->assertTrue(!empty($context_method));
    }
    public function testClassContext()
    {
        $code = "<?php\n            class C {\n                private function f() {\n                    return 42;\n                }\n            }";
        $stmt_list_node = \ast\parse_code($code, Config::get()->ast_version);
        $class_node = $stmt_list_node->children[0];
        $context = new Context();
        $context_visitor = new ParseVisitor($this->code_base, $context);
        $context = $context_visitor($class_node);
        $stmt_list_node = $class_node->children['stmts'];
        $method_node = $stmt_list_node->children[0];
        $context_visitor = new ParseVisitor($this->code_base, $context);
        $context = $context_visitor($method_node);

        $this->assertSame('\C::f', (string)$context->getScope()->getFQSEN());
    }

    public function disabled_testNamespaceMap()
    {
        // ...
    }
}