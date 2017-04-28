<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\AST\Visitor\Element;
use Phan\AST\Visitor\FlagVisitorImplementation;
use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\UnionType;
use Phan\Language\Type\ArrayType;
use Phan\Language\Type\BoolType;
use Phan\Language\Type\FloatType;
use Phan\Language\Type\IntType;
use Phan\Language\Type\StringType;
use Phan\Issue;
use ast\Node;
class BinaryOperatorFlagVisitor extends FlagVisitorImplementation
{
    /**
     * @var CodeBase
     */
    private $code_base;
    /**
     * @var Context
     */
    private $context;
    /**
     * Create a new BinaryOperatorFlagVisitor
     */
    public function __construct(CodeBase $code_base, Context $context)
    {
        $this->code_base = $code_base;
        $this->context = $context;
    }
    /**
     * @param Node $node
     * A node to visit
     */
    public function __invoke(Node $node)
    {
        return (new Element($node))->acceptBinaryFlagVisitor($this);
    }
    /**
     * Default visitor for node kinds that do not have
     * an overriding method
     *
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visit(Node $node)
    {
        $left = UnionType::fromNode($this->context, $this->code_base, $node->children['left']);
        $right = UnionType::fromNode($this->context, $this->code_base, $node->children['right']);
        if ($left->isType(ArrayType::instance(false)) || $right->isType(ArrayType::instance(false))) {
            Issue::maybeEmit($this->code_base, $this->context, Issue::TypeArrayOperator, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), $left, $right);
            $ret5902c6f1c5adc = new UnionType();
            if (!$ret5902c6f1c5adc instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c5adc) == "object" ? get_class($ret5902c6f1c5adc) : gettype($ret5902c6f1c5adc)) . " given");
            }
            return $ret5902c6f1c5adc;
        } elseif ($left->hasType(IntType::instance(false)) && $right->hasType(IntType::instance(false))) {
            $ret5902c6f1c6020 = IntType::instance(false)->asUnionType();
            if (!$ret5902c6f1c6020 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c6020) == "object" ? get_class($ret5902c6f1c6020) : gettype($ret5902c6f1c6020)) . " given");
            }
            return $ret5902c6f1c6020;
        } elseif ($left->hasType(FloatType::instance(false)) && $right->hasType(FloatType::instance(false))) {
            $ret5902c6f1c63c7 = FloatType::instance(false)->asUnionType();
            if (!$ret5902c6f1c63c7 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c63c7) == "object" ? get_class($ret5902c6f1c63c7) : gettype($ret5902c6f1c63c7)) . " given");
            }
            return $ret5902c6f1c63c7;
        }
        $ret5902c6f1c670b = new UnionType([IntType::instance(false), FloatType::instance(false)]);
        if (!$ret5902c6f1c670b instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c670b) == "object" ? get_class($ret5902c6f1c670b) : gettype($ret5902c6f1c670b)) . " given");
        }
        return $ret5902c6f1c670b;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryBoolAnd(Node $node)
    {
        $ret5902c6f1c6a09 = $this->visitBinaryBool($node);
        if (!$ret5902c6f1c6a09 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c6a09) == "object" ? get_class($ret5902c6f1c6a09) : gettype($ret5902c6f1c6a09)) . " given");
        }
        return $ret5902c6f1c6a09;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryBoolXor(Node $node)
    {
        $ret5902c6f1c6d7e = $this->visitBinaryBool($node);
        if (!$ret5902c6f1c6d7e instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c6d7e) == "object" ? get_class($ret5902c6f1c6d7e) : gettype($ret5902c6f1c6d7e)) . " given");
        }
        return $ret5902c6f1c6d7e;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryBoolOr(Node $node)
    {
        $ret5902c6f1c7082 = $this->visitBinaryBool($node);
        if (!$ret5902c6f1c7082 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c7082) == "object" ? get_class($ret5902c6f1c7082) : gettype($ret5902c6f1c7082)) . " given");
        }
        return $ret5902c6f1c7082;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryConcat(Node $node)
    {
        $ret5902c6f1c7396 = StringType::instance(false)->asUnionType();
        if (!$ret5902c6f1c7396 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c7396) == "object" ? get_class($ret5902c6f1c7396) : gettype($ret5902c6f1c7396)) . " given");
        }
        return $ret5902c6f1c7396;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    private function visitBinaryOpCommon(Node $node)
    {
        $left = UnionType::fromNode($this->context, $this->code_base, $node->children['left']);
        $right = UnionType::fromNode($this->context, $this->code_base, $node->children['right']);
        $left_is_array_like = $left->isExclusivelyArrayLike();
        $right_is_array_like = $right->isExclusivelyArrayLike();
        $left_can_cast_to_array = $left->canCastToUnionType(ArrayType::instance(false)->asUnionType());
        $right_can_cast_to_array = $right->canCastToUnionType(ArrayType::instance(false)->asUnionType());
        if ($left_is_array_like && !$right->hasArrayLike() && !$right_can_cast_to_array && !$right->isEmpty()) {
            Issue::maybeEmit($this->code_base, $this->context, Issue::TypeComparisonFromArray, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $right);
        } elseif ($right_is_array_like && !$left->hasArrayLike() && !$left_can_cast_to_array && !$left->isEmpty()) {
            Issue::maybeEmit($this->code_base, $this->context, Issue::TypeComparisonToArray, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), (string) $left);
        }
        return BoolType::instance(false)->asUnionType();
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsIdentical(Node $node)
    {
        $ret5902c6f1c7b04 = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c7b04 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c7b04) == "object" ? get_class($ret5902c6f1c7b04) : gettype($ret5902c6f1c7b04)) . " given");
        }
        return $ret5902c6f1c7b04;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsNotIdentical(Node $node)
    {
        $ret5902c6f1c7e3b = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c7e3b instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c7e3b) == "object" ? get_class($ret5902c6f1c7e3b) : gettype($ret5902c6f1c7e3b)) . " given");
        }
        return $ret5902c6f1c7e3b;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsEqual(Node $node)
    {
        $ret5902c6f1c8146 = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c8146 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c8146) == "object" ? get_class($ret5902c6f1c8146) : gettype($ret5902c6f1c8146)) . " given");
        }
        return $ret5902c6f1c8146;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsNotEqual(Node $node)
    {
        $ret5902c6f1c844e = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c844e instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c844e) == "object" ? get_class($ret5902c6f1c844e) : gettype($ret5902c6f1c844e)) . " given");
        }
        return $ret5902c6f1c844e;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsSmaller(Node $node)
    {
        $ret5902c6f1c8757 = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c8757 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c8757) == "object" ? get_class($ret5902c6f1c8757) : gettype($ret5902c6f1c8757)) . " given");
        }
        return $ret5902c6f1c8757;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsSmallerOrEqual(Node $node)
    {
        $ret5902c6f1c8a68 = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c8a68 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c8a68) == "object" ? get_class($ret5902c6f1c8a68) : gettype($ret5902c6f1c8a68)) . " given");
        }
        return $ret5902c6f1c8a68;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsGreater(Node $node)
    {
        $ret5902c6f1c8da8 = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c8da8 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c8da8) == "object" ? get_class($ret5902c6f1c8da8) : gettype($ret5902c6f1c8da8)) . " given");
        }
        return $ret5902c6f1c8da8;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryIsGreaterOrEqual(Node $node)
    {
        $ret5902c6f1c90ae = $this->visitBinaryOpCommon($node);
        if (!$ret5902c6f1c90ae instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c90ae) == "object" ? get_class($ret5902c6f1c90ae) : gettype($ret5902c6f1c90ae)) . " given");
        }
        return $ret5902c6f1c90ae;
    }
    /**
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    public function visitBinaryAdd(Node $node)
    {
        $left = UnionType::fromNode($this->context, $this->code_base, $node->children['left']);
        $right = UnionType::fromNode($this->context, $this->code_base, $node->children['right']);
        // fast-track common cases
        if ($left->isType(IntType::instance(false)) && $right->isType(IntType::instance(false))) {
            $ret5902c6f1c954d = IntType::instance(false)->asUnionType();
            if (!$ret5902c6f1c954d instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c954d) == "object" ? get_class($ret5902c6f1c954d) : gettype($ret5902c6f1c954d)) . " given");
            }
            return $ret5902c6f1c954d;
        }
        // If both left and right are arrays, then this is array
        // concatenation.
        if ($left->isGenericArray() && $right->isGenericArray()) {
            if ($left->isEqualTo($right)) {
                $ret5902c6f1c9864 = $left;
                if (!$ret5902c6f1c9864 instanceof UnionType) {
                    throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c9864) == "object" ? get_class($ret5902c6f1c9864) : gettype($ret5902c6f1c9864)) . " given");
                }
                return $ret5902c6f1c9864;
            }
            $ret5902c6f1c9b56 = ArrayType::instance(false)->asUnionType();
            if (!$ret5902c6f1c9b56 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c9b56) == "object" ? get_class($ret5902c6f1c9b56) : gettype($ret5902c6f1c9b56)) . " given");
            }
            return $ret5902c6f1c9b56;
        }
        if (($left->isType(IntType::instance(false)) || $left->isType(FloatType::instance(false))) && ($right->isType(IntType::instance(false)) || $right->isType(FloatType::instance(false)))) {
            $ret5902c6f1c9f9b = FloatType::instance(false)->asUnionType();
            if (!$ret5902c6f1c9f9b instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1c9f9b) == "object" ? get_class($ret5902c6f1c9f9b) : gettype($ret5902c6f1c9f9b)) . " given");
            }
            return $ret5902c6f1c9f9b;
        }
        $left_is_array = !$left->genericArrayElementTypes()->isEmpty() && $left->nonArrayTypes()->isEmpty() || $left->isType(ArrayType::instance(false));
        $right_is_array = !$right->genericArrayElementTypes()->isEmpty() && $right->nonArrayTypes()->isEmpty() || $right->isType(ArrayType::instance(false));
        if ($left_is_array && !$right->canCastToUnionType(ArrayType::instance(false)->asUnionType())) {
            Issue::maybeEmit($this->code_base, $this->context, Issue::TypeInvalidRightOperand, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0));
            $ret5902c6f1ca4cb = new UnionType();
            if (!$ret5902c6f1ca4cb instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1ca4cb) == "object" ? get_class($ret5902c6f1ca4cb) : gettype($ret5902c6f1ca4cb)) . " given");
            }
            return $ret5902c6f1ca4cb;
        } elseif ($right_is_array && !$left->canCastToUnionType(ArrayType::instance(false)->asUnionType())) {
            Issue::maybeEmit($this->code_base, $this->context, Issue::TypeInvalidLeftOperand, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0));
            $ret5902c6f1ca891 = new UnionType();
            if (!$ret5902c6f1ca891 instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1ca891) == "object" ? get_class($ret5902c6f1ca891) : gettype($ret5902c6f1ca891)) . " given");
            }
            return $ret5902c6f1ca891;
        } elseif ($left_is_array || $right_is_array) {
            $ret5902c6f1cabee = ArrayType::instance(false)->asUnionType();
            if (!$ret5902c6f1cabee instanceof UnionType) {
                throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1cabee) == "object" ? get_class($ret5902c6f1cabee) : gettype($ret5902c6f1cabee)) . " given");
            }
            return $ret5902c6f1cabee;
        }
        $ret5902c6f1caf47 = new UnionType([IntType::instance(false), FloatType::instance(false)]);
        if (!$ret5902c6f1caf47 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1caf47) == "object" ? get_class($ret5902c6f1caf47) : gettype($ret5902c6f1caf47)) . " given");
        }
        return $ret5902c6f1caf47;
    }
    /**
     * Common visitor for binary boolean operations
     *
     * @param Node $node
     * A node to check types on
     *
     * @return UnionType
     * The resulting type(s) of the binary operation
     */
    private function visitBinaryBool(Node $node)
    {
        $left = UnionType::fromNode($this->context, $this->code_base, $node->children['left']);
        $right = UnionType::fromNode($this->context, $this->code_base, $node->children['right']);
        $ret5902c6f1cb356 = BoolType::instance(false)->asUnionType();
        if (!$ret5902c6f1cb356 instanceof UnionType) {
            throw new \InvalidArgumentException("Argument returned must be of the type UnionType, " . (gettype($ret5902c6f1cb356) == "object" ? get_class($ret5902c6f1cb356) : gettype($ret5902c6f1cb356)) . " given");
        }
        return $ret5902c6f1cb356;
    }
}