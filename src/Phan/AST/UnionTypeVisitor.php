<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\AST;

use Phan\Analysis\BinaryOperatorFlagVisitor;
use Phan\Analysis\ConditionVisitor;
use Phan\CodeBase;
use Phan\Config;
use Phan\Debug;
use Phan\Exception\CodeBaseException;
use Phan\Exception\IssueException;
use Phan\Exception\NodeException;
use Phan\Exception\TypeException;
use Phan\Exception\UnanalyzableException;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\Element\Clazz;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\FQSEN\FullyQualifiedFunctionName;
use Phan\Language\Type;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\BoolType;
use Phan\Language\Type\CallableType;
use Phan\Language\Type\FloatType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\MixedType;
use Phan\Language\Type\NullType;
use Phan\Language\Type\ObjectType;
use Phan\Language\Type\StringType;
use Phan\Language\Type\StaticType;
use Phan\Language\UnionType;
use ast\Node;
use ast\Node\Decl;
/**
 * Determine the UnionType associated with a
 * given node
 */
class UnionTypeVisitor extends AnalysisVisitor
{
    /**
     * @var bool
     * Set to true to cause loggable issues to be thrown
     * instead of emitted as issues to the log.
     */
    private $should_catch_issue_exception = false;
    /**
     * @param CodeBase $code_base
     * The code base within which we're operating
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param bool $should_catch_issue_exception
     * Set to true to cause loggable issues to be thrown
     * instead of emitted as issues to the log.
     */
    public function __construct(CodeBase $code_base, Context $context, $should_catch_issue_exception = true)
    {
        if (!is_bool($should_catch_issue_exception)) {
            throw new \InvalidArgumentException("Argument \$should_catch_issue_exception passed to __construct() must be of the type bool, " . (gettype($should_catch_issue_exception) == "object" ? get_class($should_catch_issue_exception) : gettype($should_catch_issue_exception)) . " given");
        }
        parent::__construct($code_base, $context);
        $this->should_catch_issue_exception = $should_catch_issue_exception;
    }
    /**
     * @param CodeBase $code_base
     * The code base within which we're operating
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param Node|string|bool|int|float|null $node
     * The node for which we'd like to determine its type
     *
     * @param bool $should_catch_issue_exception
     * Set to true to cause loggable issues to be thrown
     * instead
     *
     * @return UnionType
     * The UnionType associated with the given node
     * in the given Context within the given CodeBase
     *
     * @throws IssueException
     * If $should_catch_issue_exception is false an IssueException may
     * be thrown for optional issues.
     */
    public static function unionTypeFromNode(CodeBase $code_base, Context $context, $node, $should_catch_issue_exception = true)
    {
        if (!is_bool($should_catch_issue_exception)) {
            throw new \InvalidArgumentException("Argument \$should_catch_issue_exception passed to unionTypeFromNode() must be of the type bool, " . (gettype($should_catch_issue_exception) == "object" ? get_class($should_catch_issue_exception) : gettype($should_catch_issue_exception)) . " given");
        }
        if (!$node instanceof Node) {
            if ($node === null || $node === 'null') {
                $ret5902c6f30d3a0 = new UnionType();
                if (!$ret5902c6f30d3a0 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30d3a0) == "object" ? get_class($ret5902c6f30d3a0) : gettype($ret5902c6f30d3a0)) . " given");
                }
                return $ret5902c6f30d3a0;
            }
            $ret5902c6f30d6a7 = Type::fromObject($node)->asUnionType();
            if (!$ret5902c6f30d6a7 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30d6a7) == "object" ? get_class($ret5902c6f30d6a7) : gettype($ret5902c6f30d6a7)) . " given");
            }
            return $ret5902c6f30d6a7;
        }
        if ($should_catch_issue_exception) {
            try {
                $visitor = new self($code_base, $context, $should_catch_issue_exception);
                $ret5902c6f30d9c0 = $visitor($node);
                if (!$ret5902c6f30d9c0 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30d9c0) == "object" ? get_class($ret5902c6f30d9c0) : gettype($ret5902c6f30d9c0)) . " given");
                }
                return $ret5902c6f30d9c0;
            } catch (IssueException $exception) {
                Issue::maybeEmitInstance($code_base, $context, $exception->getIssueInstance());
                $ret5902c6f30dd06 = new UnionType();
                if (!$ret5902c6f30dd06 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30dd06) == "object" ? get_class($ret5902c6f30dd06) : gettype($ret5902c6f30dd06)) . " given");
                }
                return $ret5902c6f30dd06;
            }
        }
        $visitor = new self($code_base, $context, $should_catch_issue_exception);
        $ret5902c6f30e022 = $visitor($node);
        if (!$ret5902c6f30e022 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30e022) == "object" ? get_class($ret5902c6f30e022) : gettype($ret5902c6f30e022)) . " given");
        }
        return $ret5902c6f30e022;
    }
    /**
     * Default visitor for node kinds that do not have
     * an overriding method
     *
     * @param Node $node
     * An AST node we'd like to determine the UnionType
     * for
     *
     * @return UnionType
     * The set of types associated with the given node
     */
    public function visit(Node $node)
    {
        $ret5902c6f30e59a = new UnionType();
        if (!$ret5902c6f30e59a instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30e59a) == "object" ? get_class($ret5902c6f30e59a) : gettype($ret5902c6f30e59a)) . " given");
        }
        return $ret5902c6f30e59a;
    }
    /**
     * Visit a node with kind `\ast\AST_POST_INC`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitPostInc(Node $node)
    {
        $ret5902c6f30e8e5 = self::unionTypeFromNode($this->code_base, $this->context, $node->children['var']);
        if (!$ret5902c6f30e8e5 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30e8e5) == "object" ? get_class($ret5902c6f30e8e5) : gettype($ret5902c6f30e8e5)) . " given");
        }
        return $ret5902c6f30e8e5;
    }
    /**
     * Visit a node with kind `\ast\AST_POST_DEC`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitPostDec(Node $node)
    {
        $ret5902c6f30ec2b = self::unionTypeFromNode($this->code_base, $this->context, $node->children['var']);
        if (!$ret5902c6f30ec2b instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30ec2b) == "object" ? get_class($ret5902c6f30ec2b) : gettype($ret5902c6f30ec2b)) . " given");
        }
        return $ret5902c6f30ec2b;
    }
    /**
     * Visit a node with kind `\ast\AST_PRE_DEC`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitPreDec(Node $node)
    {
        $ret5902c6f30ef72 = self::unionTypeFromNode($this->code_base, $this->context, $node->children['var']);
        if (!$ret5902c6f30ef72 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30ef72) == "object" ? get_class($ret5902c6f30ef72) : gettype($ret5902c6f30ef72)) . " given");
        }
        return $ret5902c6f30ef72;
    }
    /**
     * Visit a node with kind `\ast\AST_PRE_INC`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitPreInc(Node $node)
    {
        $ret5902c6f30f2be = self::unionTypeFromNode($this->code_base, $this->context, $node->children['var']);
        if (!$ret5902c6f30f2be instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30f2be) == "object" ? get_class($ret5902c6f30f2be) : gettype($ret5902c6f30f2be)) . " given");
        }
        return $ret5902c6f30f2be;
    }
    /**
     * Visit a node with kind `\ast\AST_CLONE`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitClone(Node $node)
    {
        $ret5902c6f30f610 = self::unionTypeFromNode($this->code_base, $this->context, $node->children['expr']);
        if (!$ret5902c6f30f610 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30f610) == "object" ? get_class($ret5902c6f30f610) : gettype($ret5902c6f30f610)) . " given");
        }
        return $ret5902c6f30f610;
    }
    /**
     * Visit a node with kind `\ast\AST_COALESCE`
     * (Null coalescing operator)
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitCoalesce(Node $node)
    {
        $union_type = new UnionType();
        $left_type = self::unionTypeFromNode($this->code_base, $this->context, $node->children['left']);
        $right_type = self::unionTypeFromNode($this->code_base, $this->context, $node->children['right']);
        // On the left side, remove null and replace '?T' with 'T'
        // Don't bother if the right side contains null.
        if (!$right_type->isEmpty() && $left_type->containsNullable() && !$right_type->containsNullable()) {
            $left_type = $left_type->nonNullableClone();
        }
        $union_type->addUnionType($left_type);
        $union_type->addUnionType($right_type);
        $ret5902c6f30facd = $union_type;
        if (!$ret5902c6f30facd instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30facd) == "object" ? get_class($ret5902c6f30facd) : gettype($ret5902c6f30facd)) . " given");
        }
        return $ret5902c6f30facd;
    }
    /**
     * Visit a node with kind `\ast\AST_EMPTY`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitEmpty(Node $node)
    {
        $ret5902c6f30fddd = BoolType::instance(false)->asUnionType();
        if (!$ret5902c6f30fddd instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f30fddd) == "object" ? get_class($ret5902c6f30fddd) : gettype($ret5902c6f30fddd)) . " given");
        }
        return $ret5902c6f30fddd;
    }
    /**
     * Visit a node with kind `\ast\AST_ISSET`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitIsset(Node $node)
    {
        $ret5902c6f3100ed = BoolType::instance(false)->asUnionType();
        if (!$ret5902c6f3100ed instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3100ed) == "object" ? get_class($ret5902c6f3100ed) : gettype($ret5902c6f3100ed)) . " given");
        }
        return $ret5902c6f3100ed;
    }
    /**
     * Visit a node with kind `\ast\AST_INCLUDE_OR_EVAL`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitIncludeOrEval(Node $node)
    {
        $ret5902c6f3103de = new UnionType();
        if (!$ret5902c6f3103de instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3103de) == "object" ? get_class($ret5902c6f3103de) : gettype($ret5902c6f3103de)) . " given");
        }
        return $ret5902c6f3103de;
    }
    /**
     * Visit a node with kind `\ast\AST_MAGIC_CONST`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitMagicConst(Node $node)
    {
        $ret5902c6f3106eb = StringType::instance(false)->asUnionType();
        if (!$ret5902c6f3106eb instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3106eb) == "object" ? get_class($ret5902c6f3106eb) : gettype($ret5902c6f3106eb)) . " given");
        }
        return $ret5902c6f3106eb;
    }
    /**
     * Visit a node with kind `\ast\AST_ASSIGN_REF`
     * @see $this->visitAssign
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitAssignRef(Node $node)
    {
        $ret5902c6f310a09 = $this->visitAssign($node);
        if (!$ret5902c6f310a09 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f310a09) == "object" ? get_class($ret5902c6f310a09) : gettype($ret5902c6f310a09)) . " given");
        }
        return $ret5902c6f310a09;
    }
    /**
     * Visit a node with kind `\ast\AST_SHELL_EXEC`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitShellExec(Node $node)
    {
        $ret5902c6f310d14 = StringType::instance(false)->asUnionType();
        if (!$ret5902c6f310d14 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f310d14) == "object" ? get_class($ret5902c6f310d14) : gettype($ret5902c6f310d14)) . " given");
        }
        return $ret5902c6f310d14;
    }
    /**
     * Visit a node with kind `\ast\AST_NAME`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitName(Node $node)
    {
        if ($node->flags & \ast\flags\NAME_NOT_FQ) {
            if ('parent' === $node->children['name']) {
                $class = $this->context->getClassInScope($this->code_base);
                if ($class->hasParentType()) {
                    $ret5902c6f3110e9 = Type::fromFullyQualifiedString((string) $class->getParentClassFQSEN())->asUnionType();
                    if (!$ret5902c6f3110e9 instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3110e9) == "object" ? get_class($ret5902c6f3110e9) : gettype($ret5902c6f3110e9)) . " given");
                    }
                    return $ret5902c6f3110e9;
                } else {
                    if (!$class->isTrait()) {
                        $this->emitIssue(Issue::ParentlessClass, call_user_func(function ($v1, $v2) {
                            return isset($v1) ? $v1 : $v2;
                        }, @$node->lineno, @0), (string) $class->getFQSEN());
                    }
                    $ret5902c6f3114e7 = new UnionType();
                    if (!$ret5902c6f3114e7 instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3114e7) == "object" ? get_class($ret5902c6f3114e7) : gettype($ret5902c6f3114e7)) . " given");
                    }
                    return $ret5902c6f3114e7;
                }
            }
            if ('self' === $node->children['name'] && $this->context->getClassInScope($this->code_base)->isTrait()) {
                $ret5902c6f31183a = new UnionType();
                if (!$ret5902c6f31183a instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31183a) == "object" ? get_class($ret5902c6f31183a) : gettype($ret5902c6f31183a)) . " given");
                }
                return $ret5902c6f31183a;
            }
            $ret5902c6f311ba0 = Type::fromStringInContext($node->children['name'], $this->context, false)->asUnionType();
            if (!$ret5902c6f311ba0 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f311ba0) == "object" ? get_class($ret5902c6f311ba0) : gettype($ret5902c6f311ba0)) . " given");
            }
            return $ret5902c6f311ba0;
        }
        $ret5902c6f311ea6 = Type::fromFullyQualifiedString('\\' . $node->children['name'])->asUnionType();
        if (!$ret5902c6f311ea6 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f311ea6) == "object" ? get_class($ret5902c6f311ea6) : gettype($ret5902c6f311ea6)) . " given");
        }
        return $ret5902c6f311ea6;
    }
    /**
     * Visit a node with kind `\ast\AST_TYPE`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitType(Node $node)
    {
        switch ($node->flags) {
            case \ast\flags\TYPE_ARRAY:
                $ret5902c6f3121e4 = ArrayType::instance(false)->asUnionType();
                if (!$ret5902c6f3121e4 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3121e4) == "object" ? get_class($ret5902c6f3121e4) : gettype($ret5902c6f3121e4)) . " given");
                }
                return $ret5902c6f3121e4;
            case \ast\flags\TYPE_BOOL:
                $ret5902c6f312524 = BoolType::instance(false)->asUnionType();
                if (!$ret5902c6f312524 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f312524) == "object" ? get_class($ret5902c6f312524) : gettype($ret5902c6f312524)) . " given");
                }
                return $ret5902c6f312524;
            case \ast\flags\TYPE_CALLABLE:
                $ret5902c6f31281a = CallableType::instance(false)->asUnionType();
                if (!$ret5902c6f31281a instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31281a) == "object" ? get_class($ret5902c6f31281a) : gettype($ret5902c6f31281a)) . " given");
                }
                return $ret5902c6f31281a;
            case \ast\flags\TYPE_DOUBLE:
                $ret5902c6f312b11 = FloatType::instance(false)->asUnionType();
                if (!$ret5902c6f312b11 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f312b11) == "object" ? get_class($ret5902c6f312b11) : gettype($ret5902c6f312b11)) . " given");
                }
                return $ret5902c6f312b11;
            case \ast\flags\TYPE_LONG:
                $ret5902c6f312e09 = IntType::instance(false)->asUnionType();
                if (!$ret5902c6f312e09 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f312e09) == "object" ? get_class($ret5902c6f312e09) : gettype($ret5902c6f312e09)) . " given");
                }
                return $ret5902c6f312e09;
            case \ast\flags\TYPE_NULL:
                $ret5902c6f313108 = NullType::instance(false)->asUnionType();
                if (!$ret5902c6f313108 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f313108) == "object" ? get_class($ret5902c6f313108) : gettype($ret5902c6f313108)) . " given");
                }
                return $ret5902c6f313108;
            case \ast\flags\TYPE_OBJECT:
                $ret5902c6f31342e = ObjectType::instance(false)->asUnionType();
                if (!$ret5902c6f31342e instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31342e) == "object" ? get_class($ret5902c6f31342e) : gettype($ret5902c6f31342e)) . " given");
                }
                return $ret5902c6f31342e;
            case \ast\flags\TYPE_STRING:
                $ret5902c6f313724 = StringType::instance(false)->asUnionType();
                if (!$ret5902c6f313724 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f313724) == "object" ? get_class($ret5902c6f313724) : gettype($ret5902c6f313724)) . " given");
                }
                return $ret5902c6f313724;
            default:
                assert(false, "All flags must match. Found " . Debug::astFlagDescription(call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->flags, @0)));
                break;
        }
    }
    /**
     * Visit a node with kind `\ast\AST_TYPE` representing
     * a nullable type such as `?string`.
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitNullableType(Node $node)
    {
        // Get the type
        $union_type = UnionType::fromNode($this->context, $this->code_base, $node->children['type'], $this->should_catch_issue_exception);
        $ret5902c6f313e69 = $union_type->asMappedUnionType(function (Type $type) {
            $ret5902c6f313ba8 = $type->withIsNullable(true);
            if (!$ret5902c6f313ba8 instanceof Type) {
                throw new \InvalidArgumentException("Argument returned must be of the type Type, " . (gettype($ret5902c6f313ba8) == "object" ? get_class($ret5902c6f313ba8) : gettype($ret5902c6f313ba8)) . " given");
            }
            return $ret5902c6f313ba8;
        });
        if (!$ret5902c6f313e69 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f313e69) == "object" ? get_class($ret5902c6f313e69) : gettype($ret5902c6f313e69)) . " given");
        }
        return $ret5902c6f313e69;
    }
    /**
     * @param int|float|string|Node $cond
     * @return ?bool
     */
    private function checkCondUnconditionalTruthiness($cond)
    {
        if ($cond instanceof Node) {
            if ($cond->kind === \ast\AST_CONST) {
                $name = $cond->children['name'];
                if ($name->kind === \ast\AST_NAME) {
                    switch (strtolower($name->children['name'])) {
                        case 'true':
                            return true;
                        case 'false':
                            return false;
                        case 'null':
                            return false;
                        default:
                            // Could add heuristics based on internal/user-defined constant values, but that is unreliable.
                            // (E.g. feature flags for an extension may be true or false, depending on the environment)
                            // (and Phan doesn't store constant values for user-defined constants, only the types)
                            return null;
                    }
                }
            }
            return null;
        }
        // Otherwise, this is an int/float/string.
        // Use the exact same truthiness rules as PHP to check if the conditional is truthy.
        // (e.g. "0" and 0.0 and '' are false)
        assert(is_scalar($cond), 'cond must be Node or scalar');
        return (bool) $cond;
    }
    /**
     * Visit a node with kind `\ast\AST_CONDITIONAL`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitConditional(Node $node)
    {
        $cond_node = $node->children['cond'];
        $cond_truthiness = $this->checkCondUnconditionalTruthiness($cond_node);
        // For the shorthand $a ?: $b, the cond node will be the truthy value.
        // Note: an ast node will never be null(can be unset), it will be a const AST node with the name null.
        $true_node = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['trueExpr'], @call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['true'], @$cond_node));
        // Rarely, an
        if ($cond_truthiness !== null) {
            // TODO: Add no-op checks in another PR, if they don't already exist for conditional.
            if ($cond_truthiness === true) {
                $ret5902c6f3145af = UnionType::fromNode($this->context, $this->code_base, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->children['trueExpr'], @call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->children['true'], @$cond_node)));
                if (!$ret5902c6f3145af instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3145af) == "object" ? get_class($ret5902c6f3145af) : gettype($ret5902c6f3145af)) . " given");
                }
                return $ret5902c6f3145af;
            } else {
                $ret5902c6f31493e = UnionType::fromNode($this->context, $this->code_base, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->children['falseExpr'], @call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->children['false'], @'')));
                if (!$ret5902c6f31493e instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31493e) == "object" ? get_class($ret5902c6f31493e) : gettype($ret5902c6f31493e)) . " given");
                }
                return $ret5902c6f31493e;
            }
        }
        // TODO: false_context once there is a NegatedConditionVisitor
        // TODO: emit no-op if $cond_node is a literal, such as `if (2)`
        // - Also note that some things such as `true` and `false` are \ast\AST_NAME nodes.
        if ($cond_node instanceof Node) {
            $true_visitor = new ConditionVisitor($this->code_base, $this->context);
            $true_context = $true_visitor($cond_node);
        } else {
            $true_context = $this->context;
        }
        $true_type = UnionType::fromNode($true_context, $this->code_base, $true_node);
        $false_type = UnionType::fromNode($this->context, $this->code_base, call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['falseExpr'], @call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['false'], @'')));
        $union_type = new UnionType();
        // Add the type for the 'true' side
        $union_type->addUnionType($true_type);
        // Add the type for the 'false' side
        $union_type->addUnionType($false_type);
        // If one side has an unknown type but the other doesn't
        // we can't let the unseen type get erased. Unfortunately,
        // we need to add 'mixed' in so that we know it could be
        // anything at all.
        //
        // See Issue #104
        if ($true_type->isEmpty() xor $false_type->isEmpty()) {
            $union_type->addUnionType(MixedType::instance(false)->asUnionType());
        }
        $ret5902c6f314ecb = $union_type;
        if (!$ret5902c6f314ecb instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f314ecb) == "object" ? get_class($ret5902c6f314ecb) : gettype($ret5902c6f314ecb)) . " given");
        }
        return $ret5902c6f314ecb;
    }
    /**
     * Visit a node with kind `\ast\AST_ARRAY`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitArray(Node $node)
    {
        if (!empty($node->children) && $node->children[0] instanceof Node && $node->children[0]->kind == \ast\AST_ARRAY_ELEM) {
            $element_types = [];
            // Check the first 5 (completely arbitrary) elements
            // and assume the rest are the same type
            for ($i = 0; $i < 5; $i++) {
                // Check to see if we're out of elements
                if (empty($node->children[$i])) {
                    break;
                }
                if ($node->children[$i]->children['value'] instanceof Node) {
                    $element_types[] = UnionType::fromNode($this->context, $this->code_base, $node->children[$i]->children['value'], $this->should_catch_issue_exception);
                } else {
                    $element_types[] = Type::fromObject($node->children[$i]->children['value'])->asUnionType();
                }
            }
            $element_types = array_values(array_unique($element_types));
            if (count($element_types) == 1) {
                $ret5902c6f31551e = $element_types[0]->asGenericArrayTypes();
                if (!$ret5902c6f31551e instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31551e) == "object" ? get_class($ret5902c6f31551e) : gettype($ret5902c6f31551e)) . " given");
                }
                return $ret5902c6f31551e;
            }
        }
        $ret5902c6f315810 = ArrayType::instance(false)->asUnionType();
        if (!$ret5902c6f315810 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f315810) == "object" ? get_class($ret5902c6f315810) : gettype($ret5902c6f315810)) . " given");
        }
        return $ret5902c6f315810;
    }
    /**
     * Visit a node with kind `\ast\AST_BINARY_OP`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitBinaryOp(Node $node)
    {
        $visitor = new BinaryOperatorFlagVisitor($this->code_base, $this->context);
        $ret5902c6f315b7d = $visitor($node);
        if (!$ret5902c6f315b7d instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f315b7d) == "object" ? get_class($ret5902c6f315b7d) : gettype($ret5902c6f315b7d)) . " given");
        }
        return $ret5902c6f315b7d;
    }
    /**
     * Visit a node with kind `\ast\AST_GREATER`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitGreater(Node $node)
    {
        $ret5902c6f315e7c = $this->visitBinaryOp($node);
        if (!$ret5902c6f315e7c instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f315e7c) == "object" ? get_class($ret5902c6f315e7c) : gettype($ret5902c6f315e7c)) . " given");
        }
        return $ret5902c6f315e7c;
    }
    /**
     * Visit a node with kind `\ast\AST_GREATER_EQUAL`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitGreaterEqual(Node $node)
    {
        $ret5902c6f31617e = $this->visitBinaryOp($node);
        if (!$ret5902c6f31617e instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31617e) == "object" ? get_class($ret5902c6f31617e) : gettype($ret5902c6f31617e)) . " given");
        }
        return $ret5902c6f31617e;
    }
    /**
     * Visit a node with kind `\ast\AST_CAST`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitCast(Node $node)
    {
        // TODO: Check if the cast is allowed based on the right side type
        UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        switch ($node->flags) {
            case \ast\flags\TYPE_NULL:
                $ret5902c6f316526 = NullType::instance(false)->asUnionType();
                if (!$ret5902c6f316526 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f316526) == "object" ? get_class($ret5902c6f316526) : gettype($ret5902c6f316526)) . " given");
                }
                return $ret5902c6f316526;
            case \ast\flags\TYPE_BOOL:
                $ret5902c6f316822 = BoolType::instance(false)->asUnionType();
                if (!$ret5902c6f316822 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f316822) == "object" ? get_class($ret5902c6f316822) : gettype($ret5902c6f316822)) . " given");
                }
                return $ret5902c6f316822;
            case \ast\flags\TYPE_LONG:
                $ret5902c6f316b1d = IntType::instance(false)->asUnionType();
                if (!$ret5902c6f316b1d instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f316b1d) == "object" ? get_class($ret5902c6f316b1d) : gettype($ret5902c6f316b1d)) . " given");
                }
                return $ret5902c6f316b1d;
            case \ast\flags\TYPE_DOUBLE:
                $ret5902c6f316e16 = FloatType::instance(false)->asUnionType();
                if (!$ret5902c6f316e16 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f316e16) == "object" ? get_class($ret5902c6f316e16) : gettype($ret5902c6f316e16)) . " given");
                }
                return $ret5902c6f316e16;
            case \ast\flags\TYPE_STRING:
                $ret5902c6f317114 = StringType::instance(false)->asUnionType();
                if (!$ret5902c6f317114 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f317114) == "object" ? get_class($ret5902c6f317114) : gettype($ret5902c6f317114)) . " given");
                }
                return $ret5902c6f317114;
            case \ast\flags\TYPE_ARRAY:
                $ret5902c6f317414 = ArrayType::instance(false)->asUnionType();
                if (!$ret5902c6f317414 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f317414) == "object" ? get_class($ret5902c6f317414) : gettype($ret5902c6f317414)) . " given");
                }
                return $ret5902c6f317414;
            case \ast\flags\TYPE_OBJECT:
                $ret5902c6f31770e = ObjectType::instance(false)->asUnionType();
                if (!$ret5902c6f31770e instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31770e) == "object" ? get_class($ret5902c6f31770e) : gettype($ret5902c6f31770e)) . " given");
                }
                return $ret5902c6f31770e;
            default:
                throw new NodeException($node, 'Unknown type (' . $node->flags . ') in cast');
        }
    }
    /**
     * Visit a node with kind `\ast\AST_NEW`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitNew(Node $node)
    {
        $union_type = $this->visitClassNode($node->children['class']);
        $ret5902c6f317eb5 = new UnionType(array_map(function (Type $type) use($node) {
            // Get a fully qualified name for the type
            $fqsen = $type->asFQSEN();
            // If this isn't a class, its fine as is
            if (!$fqsen instanceof FullyQualifiedClassName) {
                return $type;
            }
            assert($fqsen instanceof FullyQualifiedClassName);
            // If we don't have the class, we'll catch that problem
            // elsewhere
            if (!$this->code_base->hasClassWithFQSEN($fqsen)) {
                return $type;
            }
            $class = $this->code_base->getClassByFQSEN($fqsen);
            // If this class doesn't have any generics on it, we're
            // fine as we are with this Type
            if (!$class->isGeneric()) {
                return $type;
            }
            // Now things are interesting. We need to map the
            // arguments to the generic types and return a special
            // kind of type.
            // Get the constructor so that we can figure out what
            // template types we're going to be mapping
            $constructor_method = $class->getMethodByName($this->code_base, '__construct');
            // Map each argument to its type
            $arg_type_list = array_map(function ($arg_node) {
                return UnionType::fromNode($this->context, $this->code_base, $arg_node);
            }, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->children['args']->children, @[]));
            // Map each template type o the argument's concrete type
            $template_type_list = [];
            foreach ($constructor_method->getParameterList() as $i => $parameter) {
                if (isset($arg_type_list[$i])) {
                    $template_type_list[] = $arg_type_list[$i];
                }
            }
            // Create a new type that assigns concrete
            // types to template type identifiers.
            return Type::fromType($type, $template_type_list);
        }, $union_type->getTypeSet()->toArray()));
        if (!$ret5902c6f317eb5 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f317eb5) == "object" ? get_class($ret5902c6f317eb5) : gettype($ret5902c6f317eb5)) . " given");
        }
        return $ret5902c6f317eb5;
    }
    /**
     * Visit a node with kind `\ast\AST_INSTANCEOF`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitInstanceOf(Node $node)
    {
        // Check to make sure the left side is valid
        UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
        try {
            // Confirm that the right-side exists
            $union_type = $this->visitClassNode($node->children['class']);
        } catch (TypeException $exception) {
            // TODO: log it?
        }
        $ret5902c6f318e37 = BoolType::instance(false)->asUnionType();
        if (!$ret5902c6f318e37 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f318e37) == "object" ? get_class($ret5902c6f318e37) : gettype($ret5902c6f318e37)) . " given");
        }
        return $ret5902c6f318e37;
    }
    /**
     * Visit a node with kind `\ast\AST_DIM`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitDim(Node $node)
    {
        $union_type = self::unionTypeFromNode($this->code_base, $this->context, $node->children['expr'], $this->should_catch_issue_exception);
        if ($union_type->isEmpty()) {
            $ret5902c6f319227 = $union_type;
            if (!$ret5902c6f319227 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f319227) == "object" ? get_class($ret5902c6f319227) : gettype($ret5902c6f319227)) . " given");
            }
            return $ret5902c6f319227;
        }
        // Figure out what the types of accessed array
        // elements would be
        $generic_types = $union_type->genericArrayElementTypes();
        // If we have generics, we're all set
        if (!$generic_types->isEmpty()) {
            $ret5902c6f31953b = $generic_types;
            if (!$ret5902c6f31953b instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31953b) == "object" ? get_class($ret5902c6f31953b) : gettype($ret5902c6f31953b)) . " given");
            }
            return $ret5902c6f31953b;
        }
        // If the only type is null, we don't know what
        // accessed items will be
        if ($union_type->isType(NullType::instance(false))) {
            $ret5902c6f319893 = new UnionType();
            if (!$ret5902c6f319893 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f319893) == "object" ? get_class($ret5902c6f319893) : gettype($ret5902c6f319893)) . " given");
            }
            return $ret5902c6f319893;
        }
        $element_types = new UnionType();
        // You can access string characters via array index,
        // so we'll add the string type to the result if we're
        // indexing something that could be a string
        if ($union_type->isType(StringType::instance(false)) || $union_type->canCastToUnionType(StringType::instance(false)->asUnionType())) {
            $element_types->addType(StringType::instance(false));
        }
        // array offsets work on strings, unfortunately
        // Double check that any classes in the type don't
        // have ArrayAccess
        $array_access_type = Type::fromNamespaceAndName('\\', 'ArrayAccess', false);
        // Hunt for any types that are viable class names and
        // see if they inherit from ArrayAccess
        try {
            foreach ($union_type->asClassList($this->code_base, $this->context) as $class) {
                if ($class->getUnionType()->asExpandedTypes($this->code_base)->hasType($array_access_type)) {
                    $ret5902c6f319dbd = $element_types;
                    if (!$ret5902c6f319dbd instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f319dbd) == "object" ? get_class($ret5902c6f319dbd) : gettype($ret5902c6f319dbd)) . " given");
                    }
                    return $ret5902c6f319dbd;
                }
            }
        } catch (CodeBaseException $exception) {
        }
        if ($element_types->isEmpty()) {
            $this->emitIssue(Issue::TypeArraySuspicious, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $union_type);
        }
        $ret5902c6f31a152 = $element_types;
        if (!$ret5902c6f31a152 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31a152) == "object" ? get_class($ret5902c6f31a152) : gettype($ret5902c6f31a152)) . " given");
        }
        return $ret5902c6f31a152;
    }
    /**
     * Visit a node with kind `\ast\AST_CLOSURE`
     *
     * @param Decl $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitClosure(Decl $node)
    {
        // The type of a closure is the fqsen pointing
        // at its definition
        $closure_fqsen = FullyQualifiedFunctionName::fromClosureInContext($this->context);
        $type = CallableType::instanceWithClosureFQSEN($closure_fqsen)->asUnionType();
        $ret5902c6f31a4b2 = $type;
        if (!$ret5902c6f31a4b2 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31a4b2) == "object" ? get_class($ret5902c6f31a4b2) : gettype($ret5902c6f31a4b2)) . " given");
        }
        return $ret5902c6f31a4b2;
    }
    /**
     * Visit a node with kind `\ast\AST_VAR`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitVar(Node $node)
    {
        // $$var or ${...} (whose idea was that anyway?)
        if ($node->children['name'] instanceof Node && ($node->children['name']->kind == \ast\AST_VAR || $node->children['name']->kind == \ast\AST_BINARY_OP)) {
            $ret5902c6f31a883 = MixedType::instance(false)->asUnionType();
            if (!$ret5902c6f31a883 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31a883) == "object" ? get_class($ret5902c6f31a883) : gettype($ret5902c6f31a883)) . " given");
            }
            return $ret5902c6f31a883;
        }
        // This is nonsense. Give up.
        if ($node->children['name'] instanceof Node) {
            $ret5902c6f31ab80 = new UnionType();
            if (!$ret5902c6f31ab80 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31ab80) == "object" ? get_class($ret5902c6f31ab80) : gettype($ret5902c6f31ab80)) . " given");
            }
            return $ret5902c6f31ab80;
        }
        $variable_name = $node->children['name'];
        if (!$this->context->getScope()->hasVariableWithName($variable_name)) {
            if (Variable::isSuperglobalVariableWithName($variable_name)) {
                $ret5902c6f31af05 = Variable::getUnionTypeOfHardcodedGlobalVariableWithName($variable_name, $this->context);
                if (!$ret5902c6f31af05 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31af05) == "object" ? get_class($ret5902c6f31af05) : gettype($ret5902c6f31af05)) . " given");
                }
                return $ret5902c6f31af05;
            }
            if (!Config::get()->ignore_undeclared_variables_in_global_scope || !$this->context->isInGlobalScope()) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredVariable, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), [$variable_name]));
            }
        } else {
            $variable = $this->context->getScope()->getVariableByName($variable_name);
            $ret5902c6f31b33f = $variable->getUnionType();
            if (!$ret5902c6f31b33f instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31b33f) == "object" ? get_class($ret5902c6f31b33f) : gettype($ret5902c6f31b33f)) . " given");
            }
            return $ret5902c6f31b33f;
        }
        $ret5902c6f31b608 = new UnionType();
        if (!$ret5902c6f31b608 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31b608) == "object" ? get_class($ret5902c6f31b608) : gettype($ret5902c6f31b608)) . " given");
        }
        return $ret5902c6f31b608;
    }
    /**
     * Visit a node with kind `\ast\AST_ENCAPS_LIST`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitEncapsList(Node $node)
    {
        $ret5902c6f31b917 = StringType::instance(false)->asUnionType();
        if (!$ret5902c6f31b917 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31b917) == "object" ? get_class($ret5902c6f31b917) : gettype($ret5902c6f31b917)) . " given");
        }
        return $ret5902c6f31b917;
    }
    /**
     * Visit a node with kind `\ast\AST_CONST`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitConst(Node $node)
    {
        if ($node->children['name']->kind == \ast\AST_NAME) {
            if (defined($node->children['name']->children['name'])) {
                $ret5902c6f31bd03 = Type::fromObject(constant($node->children['name']->children['name']))->asUnionType();
                if (!$ret5902c6f31bd03 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31bd03) == "object" ? get_class($ret5902c6f31bd03) : gettype($ret5902c6f31bd03)) . " given");
                }
                return $ret5902c6f31bd03;
            } else {
                // Figure out the name of the constant if it's
                // a string.
                // NOTE: It seems like this will always be '' because defined() would catch everything except absence?
                $constant_name = call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->children['name']->children['name'], @'');
                // If the constant is referring to the current
                // class, return that as a type
                if (Type::isSelfTypeString($constant_name) || Type::isStaticTypeString($constant_name)) {
                    $ret5902c6f31c0a0 = $this->visitClassNode($node);
                    if (!$ret5902c6f31c0a0 instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31c0a0) == "object" ? get_class($ret5902c6f31c0a0) : gettype($ret5902c6f31c0a0)) . " given");
                    }
                    return $ret5902c6f31c0a0;
                }
                try {
                    $constant = (new ContextNode($this->code_base, $this->context, $node))->getConst();
                } catch (IssueException $exception) {
                    Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                    $ret5902c6f31c445 = new UnionType();
                    if (!$ret5902c6f31c445 instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31c445) == "object" ? get_class($ret5902c6f31c445) : gettype($ret5902c6f31c445)) . " given");
                    }
                    return $ret5902c6f31c445;
                }
                $ret5902c6f31c70a = $constant->getUnionType();
                if (!$ret5902c6f31c70a instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31c70a) == "object" ? get_class($ret5902c6f31c70a) : gettype($ret5902c6f31c70a)) . " given");
                }
                return $ret5902c6f31c70a;
            }
        }
        $ret5902c6f31c9d3 = new UnionType();
        if (!$ret5902c6f31c9d3 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31c9d3) == "object" ? get_class($ret5902c6f31c9d3) : gettype($ret5902c6f31c9d3)) . " given");
        }
        return $ret5902c6f31c9d3;
    }
    /**
     * Visit a node with kind `\ast\AST_CLASS_CONST`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     *
     * @throws IssueException
     * An exception is thrown if we can't find the constant
     */
    public function visitClassConst(Node $node)
    {
        $constant_name = $node->children['const'];
        try {
            $constant = (new ContextNode($this->code_base, $this->context, $node))->getClassConst();
            $ret5902c6f31cd60 = $constant->getUnionType();
            if (!$ret5902c6f31cd60 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31cd60) == "object" ? get_class($ret5902c6f31cd60) : gettype($ret5902c6f31cd60)) . " given");
            }
            return $ret5902c6f31cd60;
        } catch (NodeException $exception) {
            $this->emitIssue(Issue::Unanalyzable, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0));
        }
        $ret5902c6f31d0a2 = new UnionType();
        if (!$ret5902c6f31d0a2 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31d0a2) == "object" ? get_class($ret5902c6f31d0a2) : gettype($ret5902c6f31d0a2)) . " given");
        }
        return $ret5902c6f31d0a2;
    }
    /**
     * Visit a node with kind `\ast\AST_PROP`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitProp(Node $node)
    {
        $ret5902c6f31d3be = $this->analyzeProp($node, false);
        if (!$ret5902c6f31d3be instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31d3be) == "object" ? get_class($ret5902c6f31d3be) : gettype($ret5902c6f31d3be)) . " given");
        }
        return $ret5902c6f31d3be;
    }
    /**
     * Analyzes a node with kind `\ast\AST_PROP` or `\ast\AST_STATIC_PROP`
     *
     * @param Node $node
     * The instance/static property access node.
     *
     * @param bool $is_static
     * True if this is a static property fetch,
     * false if this is an instance property fetch.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    private function analyzeProp(Node $node, $is_static)
    {
        if (!is_bool($is_static)) {
            throw new \InvalidArgumentException("Argument \$is_static passed to analyzeProp() must be of the type bool, " . (gettype($is_static) == "object" ? get_class($is_static) : gettype($is_static)) . " given");
        }
        try {
            $property = (new ContextNode($this->code_base, $this->context, $node))->getProperty($node->children['prop'], $is_static);
            // Map template types to concrete types
            if ($property->getUnionType()->hasTemplateType()) {
                // Get the type of the object calling the property
                $expression_type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
                $union_type = $property->getUnionType()->withTemplateParameterTypeMap($expression_type->getTemplateParameterTypeMap($this->code_base));
                $ret5902c6f31d882 = $union_type;
                if (!$ret5902c6f31d882 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31d882) == "object" ? get_class($ret5902c6f31d882) : gettype($ret5902c6f31d882)) . " given");
                }
                return $ret5902c6f31d882;
            }
            $ret5902c6f31db46 = $property->getUnionType();
            if (!$ret5902c6f31db46 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31db46) == "object" ? get_class($ret5902c6f31db46) : gettype($ret5902c6f31db46)) . " given");
            }
            return $ret5902c6f31db46;
        } catch (IssueException $exception) {
            Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
        } catch (CodeBaseException $exception) {
            $property_name = $node->children['prop'];
            $this->emitIssue(Issue::UndeclaredProperty, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), "{$exception->getFQSEN()}->{$property_name}");
        } catch (UnanalyzableException $exception) {
            // Swallow it. There are some constructs that we
            // just can't figure out.
        } catch (NodeException $exception) {
            // Swallow it. There are some constructs that we
            // just can't figure out.
        }
        $ret5902c6f31df9c = new UnionType();
        if (!$ret5902c6f31df9c instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31df9c) == "object" ? get_class($ret5902c6f31df9c) : gettype($ret5902c6f31df9c)) . " given");
        }
        return $ret5902c6f31df9c;
    }
    /**
     * Visit a node with kind `\ast\AST_STATIC_PROP`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitStaticProp(Node $node)
    {
        $ret5902c6f31e50a = $this->analyzeProp($node, true);
        if (!$ret5902c6f31e50a instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31e50a) == "object" ? get_class($ret5902c6f31e50a) : gettype($ret5902c6f31e50a)) . " given");
        }
        return $ret5902c6f31e50a;
    }
    /**
     * Visit a node with kind `\ast\AST_CALL`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitCall(Node $node)
    {
        // Things like `$func()`. We don't understand these.
        if ($node->children['expr']->kind !== \ast\AST_NAME) {
            $ret5902c6f31e843 = new UnionType();
            if (!$ret5902c6f31e843 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31e843) == "object" ? get_class($ret5902c6f31e843) : gettype($ret5902c6f31e843)) . " given");
            }
            return $ret5902c6f31e843;
        }
        $function_name = $node->children['expr']->children['name'];
        try {
            $function = (new ContextNode($this->code_base, $this->context, $node->children['expr']))->getFunction($function_name);
        } catch (CodeBaseException $exception) {
            $ret5902c6f31ebf9 = new UnionType();
            if (!$ret5902c6f31ebf9 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31ebf9) == "object" ? get_class($ret5902c6f31ebf9) : gettype($ret5902c6f31ebf9)) . " given");
            }
            return $ret5902c6f31ebf9;
        }
        $function_fqsen = $function->getFQSEN();
        // TODO: I don't believe we need this any more
        // If this is an internal function, see if we can get
        // its types from the static dataset.
        if ($function->isPHPInternal() && $function->getUnionType()->isEmpty()) {
            $map = UnionType::internalFunctionSignatureMapForFQSEN($function->getFQSEN());
            $ret5902c6f31ef81 = call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$map[$function_name], @new UnionType());
            if (!$ret5902c6f31ef81 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31ef81) == "object" ? get_class($ret5902c6f31ef81) : gettype($ret5902c6f31ef81)) . " given");
            }
            return $ret5902c6f31ef81;
        }
        $ret5902c6f31f242 = $function->getUnionType();
        if (!$ret5902c6f31f242 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31f242) == "object" ? get_class($ret5902c6f31f242) : gettype($ret5902c6f31f242)) . " given");
        }
        return $ret5902c6f31f242;
    }
    /**
     * Visit a node with kind `\ast\AST_STATIC_CALL`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitStaticCall(Node $node)
    {
        $ret5902c6f31f546 = $this->visitMethodCall($node);
        if (!$ret5902c6f31f546 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31f546) == "object" ? get_class($ret5902c6f31f546) : gettype($ret5902c6f31f546)) . " given");
        }
        return $ret5902c6f31f546;
    }
    /**
     * Visit a node with kind `\ast\AST_METHOD_CALL`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitMethodCall(Node $node)
    {
        $method_name = call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children['method'], @'');
        // Give up on any complicated nonsense where the
        // method name is a variable such as in
        // `$variable->$function_name()`.
        if ($method_name instanceof Node) {
            $ret5902c6f31f89b = new UnionType();
            if (!$ret5902c6f31f89b instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f31f89b) == "object" ? get_class($ret5902c6f31f89b) : gettype($ret5902c6f31f89b)) . " given");
            }
            return $ret5902c6f31f89b;
        }
        // Method names can some times turn up being
        // other method calls.
        assert(is_string($method_name), "Method name must be a string. Something else given.");
        try {
            $class_fqsen = null;
            foreach ($this->classListFromNode(call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->children['class'], @$node->children['expr'])) as $i => $class) {
                $class_fqsen = $class->getFQSEN();
                if (!$class->hasMethodWithName($this->code_base, $method_name)) {
                    continue;
                }
                try {
                    $method = $class->getMethodByNameInContext($this->code_base, $method_name, $this->context);
                    $union_type = $method->getUnionType();
                    // Map template types to concrete types
                    if ($union_type->hasTemplateType()) {
                        // Get the type of the object calling the property
                        $expression_type = UnionType::fromNode($this->context, $this->code_base, $node->children['expr']);
                        // Map template types to concrete types
                        $union_type = $union_type->withTemplateParameterTypeMap($expression_type->getTemplateParameterTypeMap($this->code_base));
                    }
                    // Remove any references to \static or \static[]
                    // once we're talking about the method's return
                    // type outside of its class
                    if ($union_type->hasStaticType()) {
                        $union_type = clone $union_type;
                        $union_type->removeType(\Phan\Language\Type\StaticType::instance(false));
                    }
                    if ($union_type->genericArrayElementTypes()->hasStaticType()) {
                        $union_type = clone $union_type;
                        // Find the static type on the list
                        $static_type = $union_type->getTypeSet()->find(function (Type $type) {
                            $ret5902c6f31ff64 = $type->isGenericArray() && $type->genericArrayElementType()->isStaticType();
                            if (!is_bool($ret5902c6f31ff64)) {
                                throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f31ff64) . " given");
                            }
                            return $ret5902c6f31ff64;
                        });
                        // Remove it from the list
                        $union_type->removeType($static_type);
                    }
                    $ret5902c6f3201f1 = $union_type;
                    if (!$ret5902c6f3201f1 instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3201f1) == "object" ? get_class($ret5902c6f3201f1) : gettype($ret5902c6f3201f1)) . " given");
                    }
                    return $ret5902c6f3201f1;
                } catch (IssueException $exception) {
                    $ret5902c6f3204c1 = new UnionType();
                    if (!$ret5902c6f3204c1 instanceof UnionType) {
                        throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3204c1) == "object" ? get_class($ret5902c6f3204c1) : gettype($ret5902c6f3204c1)) . " given");
                    }
                    return $ret5902c6f3204c1;
                }
            }
        } catch (IssueException $exception) {
            // Swallow it
        } catch (CodeBaseException $exception) {
            $this->emitIssue(Issue::UndeclaredClassMethod, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), $method_name, (string) $exception->getFQSEN());
        }
        $ret5902c6f320855 = new UnionType();
        if (!$ret5902c6f320855 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f320855) == "object" ? get_class($ret5902c6f320855) : gettype($ret5902c6f320855)) . " given");
        }
        return $ret5902c6f320855;
    }
    /**
     * Visit a node with kind `\ast\AST_ASSIGN`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitAssign(Node $node)
    {
        $ret5902c6f320b9d = self::unionTypeFromNode($this->code_base, $this->context, $node->children['expr']);
        if (!$ret5902c6f320b9d instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f320b9d) == "object" ? get_class($ret5902c6f320b9d) : gettype($ret5902c6f320b9d)) . " given");
        }
        return $ret5902c6f320b9d;
    }
    /**
     * Visit a node with kind `\ast\AST_UNARY_OP`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitUnaryOp(Node $node)
    {
        // Shortcut some easy operators
        switch ($node->flags) {
            case \ast\flags\UNARY_BOOL_NOT:
                $ret5902c6f320ee3 = BoolType::instance(false)->asUnionType();
                if (!$ret5902c6f320ee3 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f320ee3) == "object" ? get_class($ret5902c6f320ee3) : gettype($ret5902c6f320ee3)) . " given");
                }
                return $ret5902c6f320ee3;
        }
        $ret5902c6f321209 = self::unionTypeFromNode($this->code_base, $this->context, $node->children['expr']);
        if (!$ret5902c6f321209 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f321209) == "object" ? get_class($ret5902c6f321209) : gettype($ret5902c6f321209)) . " given");
        }
        return $ret5902c6f321209;
    }
    /**
     * Visit a node with kind `\ast\AST_UNARY_MINUS`
     *
     * @param Node $node
     * A node of the type indicated by the method name that we'd
     * like to figure out the type that it produces.
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     */
    public function visitUnaryMinus(Node $node)
    {
        $ret5902c6f321530 = Type::fromObject($node->children['expr'])->asUnionType();
        if (!$ret5902c6f321530 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f321530) == "object" ? get_class($ret5902c6f321530) : gettype($ret5902c6f321530)) . " given");
        }
        return $ret5902c6f321530;
    }
    /*
     * @param Node $node
     * A node holding a class
     *
     * @return UnionType
     * The set of types that are possibly produced by the
     * given node
     *
     * @throws IssueException
     * An exception is thrown if we can't find a class for
     * the given type
     */
    private function visitClassNode(Node $node)
    {
        // Things of the form `new $class_name();`
        if ($node->kind == \ast\AST_VAR) {
            $ret5902c6f321860 = new UnionType();
            if (!$ret5902c6f321860 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f321860) == "object" ? get_class($ret5902c6f321860) : gettype($ret5902c6f321860)) . " given");
            }
            return $ret5902c6f321860;
        }
        // Anonymous class of form `new class { ... }`
        if ($node->kind == \ast\AST_CLASS && $node->flags & \ast\flags\CLASS_ANONYMOUS) {
            // Generate a stable name for the anonymous class
            $anonymous_class_name = (new ContextNode($this->code_base, $this->context, $node))->getUnqualifiedNameForAnonymousClass();
            // Turn that into a fully qualified name
            $fqsen = FullyQualifiedClassName::fromStringInContext($anonymous_class_name, $this->context);
            $ret5902c6f321c5e = Type::fromFullyQualifiedString((string) $fqsen)->asUnionType();
            if (!$ret5902c6f321c5e instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f321c5e) == "object" ? get_class($ret5902c6f321c5e) : gettype($ret5902c6f321c5e)) . " given");
            }
            return $ret5902c6f321c5e;
        }
        // Things of the form `new $method->name()`
        if ($node->kind !== \ast\AST_NAME) {
            $ret5902c6f321f53 = new UnionType();
            if (!$ret5902c6f321f53 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f321f53) == "object" ? get_class($ret5902c6f321f53) : gettype($ret5902c6f321f53)) . " given");
            }
            return $ret5902c6f321f53;
        }
        // Get the name of the class
        $class_name = $node->children['name'];
        // If this is a straight-forward class name, recurse into the
        // class node and get its type
        $is_static_type_string = Type::isStaticTypeString($class_name);
        if (!($is_static_type_string || Type::isSelfTypeString($class_name))) {
            $ret5902c6f3222fc = self::unionTypeFromClassNode($this->code_base, $this->context, $node);
            if (!$ret5902c6f3222fc instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3222fc) == "object" ? get_class($ret5902c6f3222fc) : gettype($ret5902c6f3222fc)) . " given");
            }
            return $ret5902c6f3222fc;
        }
        // This is a self-referential node
        if (!$this->context->isInClassScope()) {
            $this->emitIssue(Issue::ContextNotObject, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), $class_name);
            $ret5902c6f322659 = new UnionType();
            if (!$ret5902c6f322659 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f322659) == "object" ? get_class($ret5902c6f322659) : gettype($ret5902c6f322659)) . " given");
            }
            return $ret5902c6f322659;
        }
        // Reference to a parent class
        if ($class_name === 'parent') {
            $class = $this->context->getClassInScope($this->code_base);
            if (!$class->hasParentType()) {
                $this->emitIssue(Issue::ParentlessClass, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), (string) $class->getFQSEN());
                $ret5902c6f322a19 = new UnionType();
                if (!$ret5902c6f322a19 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f322a19) == "object" ? get_class($ret5902c6f322a19) : gettype($ret5902c6f322a19)) . " given");
                }
                return $ret5902c6f322a19;
            }
            $ret5902c6f322d0b = Type::fromFullyQualifiedString((string) $class->getParentClassFQSEN())->asUnionType();
            if (!$ret5902c6f322d0b instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f322d0b) == "object" ? get_class($ret5902c6f322d0b) : gettype($ret5902c6f322d0b)) . " given");
            }
            return $ret5902c6f322d0b;
        }
        $result = Type::fromFullyQualifiedString((string) $this->context->getClassFQSEN())->asUnionType();
        if ($is_static_type_string) {
            $result->addType(StaticType::instance(false));
        }
        $ret5902c6f323073 = $result;
        if (!$ret5902c6f323073 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f323073) == "object" ? get_class($ret5902c6f323073) : gettype($ret5902c6f323073)) . " given");
        }
        return $ret5902c6f323073;
    }
    /**
     * @param CodeBase $code_base
     * The code base within which we're operating
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param Node|mixed $node
     * The node for which we'd like to determine its type
     *
     * @return UnionType
     * The UnionType associated with the given node
     * in the given Context within the given CodeBase
     *
     * @throws IssueException
     * An exception is thrown if we can't find a class for
     * the given type
     */
    public static function unionTypeFromClassNode(CodeBase $code_base, Context $context, $node)
    {
        // If this is a list, build a union type by
        // recursively visiting the child nodes
        if ($node instanceof Node && $node->kind == \ast\AST_NAME_LIST) {
            $union_type = new UnionType();
            foreach (call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->children, @[]) as $child_node) {
                $union_type->addUnionType(self::unionTypeFromClassNode($code_base, $context, $child_node));
            }
            $ret5902c6f323480 = $union_type;
            if (!$ret5902c6f323480 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f323480) == "object" ? get_class($ret5902c6f323480) : gettype($ret5902c6f323480)) . " given");
            }
            return $ret5902c6f323480;
        }
        // For simple nodes or very complicated nodes,
        // recurse
        if (!$node instanceof \ast\Node || $node->kind != \ast\AST_NAME) {
            $ret5902c6f3237d4 = self::unionTypeFromNode($code_base, $context, $node);
            if (!$ret5902c6f3237d4 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f3237d4) == "object" ? get_class($ret5902c6f3237d4) : gettype($ret5902c6f3237d4)) . " given");
            }
            return $ret5902c6f3237d4;
        }
        $class_name = $node->children['name'];
        if ('parent' === $class_name) {
            if (!$context->isInClassScope()) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::ContextNotObject, $context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), [$class_name]));
            }
            $class = $context->getClassInScope($code_base);
            if ($class->isTrait()) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::TraitParentReference, $context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), [(string) $context->getClassFQSEN()]));
            }
            if (!$class->hasParentType()) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::ParentlessClass, $context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), [(string) $context->getClassFQSEN()]));
            }
            $parent_class_fqsen = $class->getParentClassFQSEN();
            if (!$code_base->hasClassWithFQSEN($parent_class_fqsen)) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredClass, $context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), [(string) $parent_class_fqsen]));
            } else {
                $parent_class = $code_base->getClassByFQSEN($parent_class_fqsen);
                $ret5902c6f323fe6 = $parent_class->getUnionType();
                if (!$ret5902c6f323fe6 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f323fe6) == "object" ? get_class($ret5902c6f323fe6) : gettype($ret5902c6f323fe6)) . " given");
                }
                return $ret5902c6f323fe6;
            }
        }
        // We're going to convert the class reference to a type
        $type = null;
        // Check to see if the name is fully qualified
        if (!($node->flags & \ast\flags\NAME_NOT_FQ)) {
            if (0 !== strpos($class_name, '\\')) {
                $class_name = '\\' . $class_name;
            }
            $type = Type::fromFullyQualifiedString($class_name);
        } else {
            $type = Type::fromStringInContext($class_name, $context, false);
        }
        $ret5902c6f324532 = $type->asUnionType();
        if (!$ret5902c6f324532 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f324532) == "object" ? get_class($ret5902c6f324532) : gettype($ret5902c6f324532)) . " given");
        }
        return $ret5902c6f324532;
    }
    /**
     * @return \Generator|Clazz[]
     * A list of classes associated with the given node
     *
     * @throws IssueException
     * An exception is thrown if we can't find a class for
     * the given type
     */
    private function classListFromNode(Node $node)
    {
        // Get the types associated with the node
        $union_type = self::unionTypeFromNode($this->code_base, $this->context, $node);
        // Iterate over each viable class type to see if any
        // have the constant we're looking for
        foreach ($union_type->nonNativeTypes()->getTypeSet() as $class_type) {
            // Get the class FQSEN
            $class_fqsen = $class_type->asFQSEN();
            // See if the class exists
            if (!$this->code_base->hasClassWithFQSEN($class_fqsen)) {
                throw new IssueException(Issue::fromTypeAndInvoke(Issue::UndeclaredClassReference, $this->context->getFile(), call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), [(string) $class_fqsen]));
            }
            (yield $this->code_base->getClassByFQSEN($class_fqsen));
        }
    }
}