<?php declare(strict_types=1);

namespace Phan\Plugin\Internal;

use ast;
use ast\Node;
use Closure;
use InvalidArgumentException;
use Phan\AST\ContextNode;
use Phan\AST\Parser;
use Phan\AST\UnionTypeVisitor;
use Phan\Exception\IssueException;
use Phan\CodeBase;
use Phan\Config;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Type;
use Phan\Language\Type\LiteralIntType;
use Phan\Language\UnionType;
use Phan\PluginV2;
use Phan\PluginV2\AnalyzeFunctionCallCapability;
use RuntimeException;
use Throwable;

/**
 * This plugin checks (at)phan-having(cond) annotations on function-likes.
 *
 * Examples of what is planned:
 *
 * (at)phan-having ($x > 0)
 * (at)phan-having ($x % 2 === 0)
 * (at)phan-having (preg_match(self::SOME_REGEX, $arg))
 *
 * @phan-file-suppress PhanPluginUnknownArrayClosureParamType
 */
class HavingPlugin extends PluginV2 implements AnalyzeFunctionCallCapability
{
    /**
     * @param array<int,int> $callable_params
     * @param array<int,int> $class_params
     * @phan-return Closure(CodeBase,Context,FunctionInterface,array):void
     */
    private static function generateClosure(array $callable_params, array $class_params) : Closure
    {
        $key = \json_encode([$callable_params, $class_params]);
        static $cache = [];
        $closure = $cache[$key] ?? null;
        if ($closure !== null) {
            return $closure;
        }
        /**
         * @param array<int,Node|string|int|float> $args
         */
        $closure = function (CodeBase $code_base, Context $context, FunctionInterface $function, array $args) use ($callable_params, $class_params) {
            // TODO: Implement support for variadic callable arguments.
            foreach ($callable_params as $i) {
                $arg = $args[$i] ?? null;
                if ($arg === null) {
                    continue;
                }

                // Fetch possible functions. As an intentional side effect, this warns about invalid callables.
                // TODO: Check if the signature allows non-array callables? Not sure of desired semantics.
                $function_like_list = UnionTypeVisitor::functionLikeListFromNodeAndContext($code_base, $context, $arg, true);
                if (\count($function_like_list) === 0) {
                    // Nothing to do
                    continue;
                }

                if (Config::get_track_references()) {
                    foreach ($function_like_list as $function) {
                        $function->addReference($context);
                    }
                }
                // self::analyzeFunctionAndNormalArgumentList($code_base, $context, $function_like_list, $arguments);
            }
            foreach ($class_params as $i) {
                $arg = $args[$i] ?? null;
                if ($arg === null) {
                    continue;
                }

                // Fetch possible classes. As an intentional side effect, this warns about invalid/undefined class names.
                $class_list = UnionTypeVisitor::classListFromClassNameNode($code_base, $context, $arg);
                if (\count($class_list) === 0) {
                    // Nothing to do
                    continue;
                }

                if (Config::get_track_references()) {
                    foreach ($class_list as $class) {
                        $class->addReference($context);
                    }
                }
            }
        };

        $cache[$key] = $closure;
        return $closure;
    }

    /**
     * @param UnionType $value
     * @return Closure(array<int,UnionType>):UnionType
     */
    private static function makeClosureReturningConstant(UnionType $value) : Closure
    {
        /** @return UnionType */
        return function (array $_) use ($value) {
            return $value;
        };
    }

    /**
     * @param Node|mixed $node
     * @return Closure(array<int,UnionType>):UnionType
     * @throws InvalidArgumentException if invalid or not implemented
     */
    private function createPhanHavingClosure(CodeBase $code_base, FunctionInterface $function, $node) : Closure
    {
        if (!($node instanceof Node)) {
            return self::makeClosureReturningConstant(Type::fromObject($node)->asUnionType());
        }

        switch ($node->kind) {
            case ast\AST_BINARY_OP:
                // TODO: Reuse implementation from DuplicateExpressionPlugin
                break;
            case ast\AST_CONST:
                try {
                    $const = (new ContextNode($code_base, $function->getContext(), $node))->getConst();
                } catch (IssueException $e) {
                    throw new InvalidArgumentException($e->getMessage());
                }
                $const_type = $const->getUnionType();
                $result = $const_type->asValueOrNullOrSelf();
                if (is_object($result)) {
                    throw new InvalidArgumentException("Could not find value of constant $const");
                }
                /** @return UnionType */
                return function (array $_) use ($const_type) {
                    return $const_type;
                };
            case ast\AST_CALL:
                // strlen, count, etc.
                return $this->createPhanHavingClosureForCall($code_base, $function, $node);
            case ast\AST_VAR:
                return $this->createPhanHavingClosureForVar($function, $node->children['name']);
        }
        throw new InvalidArgumentException("Unsupported node kind " . ast\get_kind_name($node->kind));
    }

    /**
     * @return Closure(array<int,UnionType>):UnionType
     * @suppress PhanPartialTypeMismatchArgument
     */
    private function createPhanHavingClosureForCall(CodeBase $code_base, FunctionInterface $function, Node $node)
    {
        $name = $node->children['expr'];
        if (($name->kind ?? null) !== ast\AST_NAME) {
            throw new InvalidArgumentException("Unsupported function kind");
        }
        $name = $name->children['name'];
        if (!is_string($name)) {
            throw new InvalidArgumentException("Unsupported function name kind");
        }
        $args = $node->children['args']->children;
        switch (strtolower($name)) {
            case 'strlen':
                return $this->createPhanHavingClosureForStrlen($code_base, $function, $args);
            case 'preg_match':
                return $this->createPhanHavingClosureForPregMatch($code_base, $function, $args);
        }
        throw new InvalidArgumentException("Unsupported function '$name'");
    }

    /**
     * @param array<int,mixed> $arg_nodes
     * @return Closure(array<int,UnionType>):UnionType
     */
    private function createPhanHavingClosureForStrlen(CodeBase $code_base, FunctionInterface $function, array $arg_nodes) : Closure
    {
        if (count($arg_nodes) !== 1) {
            throw new InvalidArgumentException("expected 1 arg");
        }
        $arg_closure = $this->createPhanHavingClosure($code_base, $function, $arg_nodes[0]);
        /**
         * @param array<int,UnionType> $args
         */
        return function (array $args) use ($arg_closure) : UnionType {
            $str = $arg_closure($args)->asSingleScalarValueOrNull();
            if (!is_string($str)) {
                throw new InvalidArgumentException("expected a string for strlen");
            }
            return LiteralIntType::instanceForValue(strlen($str), false)->asUnionType();
        };
    }

    /**
     * @param array<int,mixed> $arg_nodes
     * @return Closure(array<int,UnionType>):UnionType
     */
    private function createPhanHavingClosureForPregMatch(CodeBase $code_base, FunctionInterface $function, array $arg_nodes) : Closure
    {
        if (count($arg_nodes) !== 2) {
            throw new InvalidArgumentException("expected 2 arg_nodes to preg_match");
        }
        $regex_closure = $this->createPhanHavingClosure($code_base, $function, $arg_nodes[0]);
        $subject_closure = $this->createPhanHavingClosure($code_base, $function, $arg_nodes[1]);

        /**
         * @param array<int,UnionType> $args
         */
        return function (array $args) use ($regex_closure, $subject_closure) : UnionType {
            $regex = $regex_closure($args)->asSingleScalarValueOrNull();
            if (!is_string($regex)) {
                throw new InvalidArgumentException("expected a string for a regex");
            }
            $arg = $subject_closure($args)->asSingleScalarValueOrNull();
            if (!is_string($arg)) {
                throw new InvalidArgumentException("expected a string for a subject");
            }

            $result = with_disabled_phan_error_handler(/** @return int|false */ static function () use ($regex, $arg) {
                $old_error_reporting = error_reporting();
                \error_reporting(0);
                \ob_start();
                try {
                    return @\preg_match($regex, $arg);
                } finally {
                    \ob_end_clean();
                    \error_reporting($old_error_reporting);
                }
            });
            return Type::fromObject($result)->asUnionType();
        };
    }
    /**
     * @param string|int|float|null|Node $name
     * @return Closure(array<int,UnionType>):UnionType
     */
    private function createPhanHavingClosureForVar(FunctionInterface $function, $name) : Closure
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Expected regular variable');
        }
        foreach ($function->getParameterList() as $i => $param) {
            if ($param->getName() === $name) {
                /**
                 * @return Node|mixed the resolved value of the argument (from union type of the argument)
                 */
                return function (array $args) use ($i) {
                    $x = $args[$i] ?? null;
                    if ($x === null) {
                        throw new RuntimeException("skip not provided");
                    }
                    $result = $x->asValueOrNullOrSelf();
                    if (is_object($result)) {
                        throw new RuntimeException("skip not a literal value");
                    }

                    return $x;
                };
            }
        }
        throw new InvalidArgumentException("Could not locate '$name'");
    }

    /**
     * @return array<string,\Closure>
     * @phan-return array<string,Closure(CodeBase,Context,FunctionInterface,array):void>
     */
    private function getAnalyzeFunctionCallClosuresStatic(CodeBase $code_base) : array
    {
        $result = [];
        $add_phan_having_closure = function (FunctionInterface $function, string $comment) use (&$result, $code_base) {
            // if (!preg_match('/@phan-having\s*\((([^()]+|\((?-2\)))*)\)/', $comment, $match)) {
            if (!preg_match('/@phan-having\s*\((([^()]+|\((?-2)*\))+)\)/', $comment, $match)) {
                echo "Failed regex match $comment\n";
                return;
            }
            $expression = $match[1];
            $code = '<'.'?php '. $expression . ';';
            try {
                echo "Parsing $code\n";
                $node = Parser::parseCode($code_base, new Context(), null, 'internal', $code, true);
            } catch (Throwable $e) {
                Issue::maybeEmit(
                    $code_base,
                    $function->getContext(),
                    Issue::CommentInvalidHavingExpression,
                    $function->getContext()->getLineNumberStart(),
                    'Unparseable: ' . $e->getMessage()
                );
                return;
            }
            try {
                $closure = $this->createPhanHavingClosure($code_base, $function, $node);
            } catch (Throwable $e) {
                Issue::maybeEmit(
                    $code_base,
                    $function->getContext(),
                    Issue::CommentInvalidHavingExpression,
                    $function->getContext()->getLineNumberStart(),
                    'Could not compile: ' . $e->getMessage()
                );
                return;
            }
            $result[(string)$function->getFQSEN()] = function (CodeBase $code_base, Context $context, FunctionInterface $unused_function, array $args) use ($closure) {
                // XXX need to compute arg types
                $closure($args);
            }
        };

        $add_misc_closures = function (FunctionInterface $function) use ($add_phan_having_closure) {
            $comment = $function->getDocComment();
            if (!$comment || strpos($comment, '@phan-having') === false) {
                return;
            }
            echo "Looking at $comment\n";
            $add_phan_having_closure($function, $comment);
        };

        foreach ($code_base->getFunctionMap() as $function) {
            $add_misc_closures($function);
        }
        foreach ($code_base->getMethodSet() as $function) {
            $add_misc_closures($function);
        }
        return $result;
    }

    /**
     * @phan-return array<string,Closure(CodeBase,Context,FunctionInterface,array):void>
     */
    public function getAnalyzeFunctionCallClosures(CodeBase $code_base) : array
    {
        // Unit tests invoke this repeatedly. Cache it.
        static $analyzers = null;
        if ($analyzers === null) {
            $analyzers = self::getAnalyzeFunctionCallClosuresStatic($code_base);
        }
        return $analyzers;
    }
}
