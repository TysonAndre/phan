<?php declare(strict_types=1);

use ast\Node;
use Microsoft\PhpParser;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phan\AST\TolerantASTConverter\NodeUtils;
use Phan\CodeBase;
use Phan\IssueInstance;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\FQSEN\FullyQualifiedGlobalConstantName;
use Phan\Plugin\Internal\IssueFixingPlugin\FileEdit;
use Phan\Plugin\Internal\IssueFixingPlugin\FileEditSet;
use Phan\Plugin\Internal\IssueFixingPlugin\IssueFixer;
use Phan\PluginV2;
use Phan\PluginV2\PluginAwarePostAnalysisVisitor;
use Phan\PluginV2\PostAnalyzeNodeCapability;

/**
 * This warns if references to global functions or global constants are not fully qualified.
 *
 * This Plugin hooks into one event:
 *
 * - getPostAnalyzeNodeVisitorClassName
 *   This method returns a class that is called on every AST node from every
 *   file being analyzed
 */
class NotFullyQualifiedUsagePlugin extends PluginV2 implements PostAnalyzeNodeCapability
{

    /**
     * @return string - The name of the visitor that will be called (formerly analyzeNode)
     * @override
     */
    public static function getPostAnalyzeNodeVisitorClassName() : string
    {
        return NotFullyQualifiedUsageVisitor::class;
    }
}

/**
 * When __invoke on this class is called with a node, a method
 * will be dispatched based on the `kind` of the given node.
 *
 * Visitors such as this are useful for defining lots of different
 * checks on a node based on its kind.
 */
class NotFullyQualifiedUsageVisitor extends PluginAwarePostAnalysisVisitor
{
    // Subclasses should declare protected $parent_node_list as an instance property if they need to know the list.

    // @var array<int,Node> - Set after the constructor is called if an instance property with this name is declared
    // protected $parent_node_list;

    // A plugin's visitors should NOT implement visit(), unless they need to.

    // phpcs:disable Generic.NamingConventions.UpperCaseConstantName.ClassConstantNotUpperCase
    const NotFullyQualifiedFunctionCall = 'PhanPluginNotFullyQualifiedFunctionCall';
    const NotFullyQualifiedOptimizableFunctionCall = 'PhanPluginNotFullyQualifiedOptimizableFunctionCall';
    const NotFullyQualifiedGlobalConstant = 'PhanPluginNotFullyQualifiedGlobalConstant';
    // phpcs:enable Generic.NamingConventions.UpperCaseConstantName.ClassConstantNotUpperCase

    /**
     * Source of functions: `zend_try_compile_special_func` from https://github.com/php/php-src/blob/master/Zend/zend_compile.c
     */
    const OPTIMIZABLE_FUNCTIONS = [
        'array_key_exists' => true,
        'array_slice' => true,
        'boolval' => true,
        'call_user_func' => true,
        'call_user_func_array' => true,
        'chr' => true,
        'count' => true,
        'defined' => true,
        'doubleval' => true,
        'floatval' => true,
        'func_get_args' => true,
        'func_num_args' => true,
        'get_called_class' => true,
        'get_class' => true,
        'gettype' => true,
        'in_array' => true,
        'intval' => true,
        'is_array' => true,
        'is_bool' => true,
        'is_double' => true,
        'is_float' => true,
        'is_int' => true,
        'is_integer' => true,
        'is_long' => true,
        'is_null' => true,
        'is_object' => true,
        'is_real' => true,
        'is_resource' => true,
        'is_string' => true,
        'ord' => true,
        'strlen' => true,
        'strval' => true,
    ];

    /**
     * @param Node $node
     * A node to analyze of type ast\AST_CALL (call to a global function)
     *
     * @return void
     *
     * @override
     */
    public function visitCall(Node $node)
    {
        $expression = $node->children['expr'];
        if (!($expression instanceof Node) || $expression->kind !== ast\AST_NAME) {
            return;
        }
        if (($expression->flags & ast\flags\NAME_NOT_FQ) !== ast\flags\NAME_NOT_FQ) {
            // This is namespace\foo() or \NS\foo()
            return;
        }
        if ($this->context->getNamespace() === '\\') {
            // This is in the global namespace and is always fully qualified
            return;
        }
        $function_name = $expression->children['name'];
        if (!is_string($function_name)) {
            // Possibly redundant.
            return;
        }
        // TODO: Probably wrong for ast\parse_code - should check namespace map of USE_NORMAL for 'ast' there.
        // Same for ContextNode->getFunction()
        if ($this->context->hasNamespaceMapFor(\ast\flags\USE_FUNCTION, $function_name)) {
            return;
        }
        $this->warnNotFullyQualifiedFunctionCall($function_name, $expression);
    }

    private function warnNotFullyQualifiedFunctionCall(string $function_name, Node $expression)
    {
        if (array_key_exists(strtolower($function_name), self::OPTIMIZABLE_FUNCTIONS)) {
            $issue_type = self::NotFullyQualifiedOptimizableFunctionCall;
            $issue_msg = 'Expected function call to {FUNCTION}() to be fully qualified or have a use statement but none were found in namespace {NAMESPACE}'
               . ' (opcache can optimize fully qualified calls to this function in recent php versions)';
        } else {
            $issue_type = self::NotFullyQualifiedFunctionCall;
            $issue_msg = 'Expected function call to {FUNCTION}() to be fully qualified or have a use statement but none were found in namespace {NAMESPACE}';
        }
        $this->emitPluginIssue(
            $this->code_base,
            clone($this->context)->withLineNumberStart($expression->lineno),
            $issue_type,
            $issue_msg,
            [$function_name, $this->context->getNamespace()]
        );
    }

    /**
     * @param Node $node
     * A node to analyze of type ast\AST_CONST (reference to a constant)
     *
     * @return void
     *
     * @override
     */
    public function visitConst(Node $node)
    {
        $expression = $node->children['name'];
        if (!($expression instanceof Node) || $expression->kind !== ast\AST_NAME) {
            return;
        }
        if (($expression->flags & ast\flags\NAME_NOT_FQ) !== ast\flags\NAME_NOT_FQ) {
            // This is namespace\SOME_CONST or \NS\SOME_CONST
            return;
        }
        if ($this->context->getNamespace() === '\\') {
            // This is in the global namespace and is always fully qualified
            return;
        }
        $constant_name = $expression->children['name'];
        if (!is_string($constant_name)) {
            // Possibly redundant.
            return;
        }
        $constant_name_lower = strtolower($constant_name);
        if ($constant_name_lower === 'true' || $constant_name_lower === 'false' || $constant_name_lower === 'null') {
            // These are keywords and are the same in any namespace
            return;
        }

        // TODO: Probably wrong for ast\AST_NAME - should check namespace map of USE_NORMAL for 'ast' there.
        // Same for ContextNode->getConst()
        if ($this->context->hasNamespaceMapFor(\ast\flags\USE_CONST, $constant_name)) {
            return;
        }
        $this->warnNotFullyQualifiedConstantUsage($constant_name, $expression);
    }

    private function warnNotFullyQualifiedConstantUsage(string $constant_name, Node $expression)
    {
        $this->emitPluginIssue(
            $this->code_base,
            clone($this->context)->withLineNumberStart($expression->lineno),
            self::NotFullyQualifiedGlobalConstant,
            'Expected usage of {CONST} to be fully qualified or have a use statement but none were found in namespace {NAMESPACE}',
            [$constant_name, $this->context->getNamespace()]
        );
    }
}

call_user_func(static function () {
    /**
     * @return ?FileEditSet
     * @suppress PhanAccessMethodInternal
     */
    $fix = static function (CodeBase $code_base, string $file_contents, PhpParser\Node $ast, IssueInstance $instance) {
        $line = $instance->getLine();
        $expected_name = $instance->getTemplateParameters()[0];
        $edits = [];
        foreach (IssueFixer::getNodesAtLine($file_contents, $ast, $line) as $node) {
            if (!$node instanceof QualifiedName) {
                continue;
            }
            if ($node->globalSpecifier || $node->relativeSpecifier) {
                // This is already qualified
                continue;
            }
            $actual_name = (new NodeUtils($file_contents))->phpParserNameToString($node);
            if ($actual_name !== $expected_name) {
                continue;
            }
            $is_actual_call = $node->parent instanceof CallExpression;
            $is_expected_call = $instance->getIssue()->getType() !== NotFullyQualifiedUsageVisitor::NotFullyQualifiedGlobalConstant;
            if ($is_actual_call !== $is_expected_call) {
                fwrite(STDERR, "skip check mismatch actual expected are call vs constants\n");
                // don't warn about constants with the same names as functions or vice-versa
                continue;
            }
            try {
                if ($is_expected_call) {
                    // Don't do this if the global function this refers to doesn't exist.
                    // TODO: Support namespaced functions
                    if (!$code_base->hasFunctionWithFQSEN(FullyQualifiedFunctionName::fromFullyQualifiedString($actual_name))) {
                        fwrite(STDERR, "skip check for $actual_name() has function already\n");
                        return null;
                    }
                } else {
                    // Don't do this if the global function this refers to doesn't exist.
                    // TODO: Support namespaced functions
                    if (!$code_base->hasGlobalConstantWithFQSEN(FullyQualifiedGlobalConstantName::fromFullyQualifiedString($actual_name))) {
                        return null;
                    }
                }
            } catch (Exception $_) {
                continue;
            }
            //fwrite(STDERR, "name is: " . get_class($node->parent) . "\n");

            // They are case-sensitively identical.
            // Generate a fix.
            // @phan-suppress-next-line PhanThrowTypeAbsentForCall
            $start = $node->getStart();
            $edits[] = new FileEdit($start, $start, '\\');
        }
        if ($edits) {
            return new FileEditSet($edits);
        }
        return null;
    };
    IssueFixer::registerFixerClosure(
        NotFullyQualifiedUsageVisitor::NotFullyQualifiedGlobalConstant,
        $fix
    );
    IssueFixer::registerFixerClosure(
        NotFullyQualifiedUsageVisitor::NotFullyQualifiedFunctionCall,
        $fix
    );
    IssueFixer::registerFixerClosure(
        NotFullyQualifiedUsageVisitor::NotFullyQualifiedOptimizableFunctionCall,
        $fix
    );
});

// Every plugin needs to return an instance of itself at the
// end of the file in which it's defined.
return new NotFullyQualifiedUsagePlugin();
