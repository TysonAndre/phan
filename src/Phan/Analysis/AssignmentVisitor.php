<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Analysis;

use Phan\AST\AnalysisVisitor;
use Phan\AST\ContextNode;
use Phan\CodeBase;
use Phan\Config;
use Phan\Debug;
use Phan\Exception\CodeBaseException;
use Phan\Exception\IssueException;
use Phan\Exception\NodeException;
use Phan\Exception\UnanalyzableException;
use Phan\Issue;
use Phan\Language\Context;
use Phan\Language\Element\Parameter;
use Phan\Language\Element\Variable;
use Phan\Language\FQSEN\FullyQualifiedClassName;
use Phan\Language\UnionType;
use ast\Node;
class AssignmentVisitor extends AnalysisVisitor
{
    /**
     * @var Node
     */
    private $assignment_node;
    /**
     * @var UnionType
     */
    private $right_type;
    /**
     * @var bool
     * True if this assignment is to an array parameter such as
     * in `$foo[3] = 42`. We need to know this in order to decide
     * if we're replacing the union type or if we're adding a
     * type to the union type.
     */
    private $is_dim_assignment = false;
    /**
     * @param CodeBase $code_base
     * The global code base we're operating within
     *
     * @param Context $context
     * The context of the parser at the node for which we'd
     * like to determine a type
     *
     * @param Node $assignment_node
     * The AST node containing the assignment
     *
     * @param UnionType $right_type
     * The type of the element on the right side of the assignment
     *
     * @param bool $is_dim_assignment
     * True if this assignment is to an array parameter such as
     * in `$foo[3] = 42`. We need to know this in order to decide
     * if we're replacing the union type or if we're adding a
     * type to the union type.
     */
    public function __construct(CodeBase $code_base, Context $context, Node $assignment_node, UnionType $right_type, $is_dim_assignment = false)
    {
        if (!is_bool($is_dim_assignment)) {
            throw new \InvalidArgumentException("Argument \$is_dim_assignment passed to __construct() must be of the type bool, " . (gettype($is_dim_assignment) == "object" ? get_class($is_dim_assignment) : gettype($is_dim_assignment)) . " given");
        }
        parent::__construct($code_base, $context);
        $this->assignment_node = $assignment_node;
        $this->right_type = $right_type;
        $this->is_dim_assignment = $is_dim_assignment;
    }
    /**
     * Default visitor for node kinds that do not have
     * an overriding method
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visit(Node $node)
    {
        assert(false, "Unknown left side of assignment in {$this->context} with node type " . Debug::nodeName($node));
        $ret5902c6f1b1500 = $this->visitVar($node);
        if (!$ret5902c6f1b1500 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b1500) == "object" ? get_class($ret5902c6f1b1500) : gettype($ret5902c6f1b1500)) . " given");
        }
        return $ret5902c6f1b1500;
    }
    /**
     * The following is an example of how this'd happen.
     *
     * ```php
     * class C {
     *     function f() {
     *         return [ 24 ];
     *     }
     * }
     * (new C)->f()[1] = 42;
     * ```
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitMethodCall(Node $node)
    {
        $ret5902c6f1b183b = $this->context;
        if (!$ret5902c6f1b183b instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b183b) == "object" ? get_class($ret5902c6f1b183b) : gettype($ret5902c6f1b183b)) . " given");
        }
        return $ret5902c6f1b183b;
    }
    /**
     * The following is an example of how this'd happen.
     *
     * ```php
     * function f() {
     *     return [ 24 ];
     * }
     * f()[1] = 42;
     * ```
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitCall(Node $node)
    {
        $ret5902c6f1b1b27 = $this->context;
        if (!$ret5902c6f1b1b27 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b1b27) == "object" ? get_class($ret5902c6f1b1b27) : gettype($ret5902c6f1b1b27)) . " given");
        }
        return $ret5902c6f1b1b27;
    }
    /**
     * This happens for code like the following
     * ```
     * list($a) = [1, 2, 3];
     * ```
     *
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitArray(Node $node)
    {
        // Figure out the type of elements in the list
        $element_type = $this->right_type->genericArrayElementTypes();
        foreach (call_user_func(function ($v1, $v2) {
            return isset($v1) ? $v1 : $v2;
        }, @$node->children, @[]) as $child_node) {
            // Some times folks like to pass a null to
            // a list to throw the element away. I'm not
            // here to judge.
            if (!$child_node instanceof Node) {
                continue;
            }
            // Get the key and value nodes for each
            // array element we're assigning to
            $key_node = $child_node->children['key'];
            $value_node = $child_node->children['value'];
            if ($value_node->kind == \ast\AST_VAR) {
                $variable = Variable::fromNodeInContext($value_node, $this->context, $this->code_base, false);
                // Set the element type on each element of
                // the list
                $variable->setUnionType($element_type);
                // Note that we're not creating a new scope, just
                // adding variables to the existing scope
                $this->context->addScopeVariable($variable);
            } else {
                if ($value_node->kind == \ast\AST_PROP) {
                    try {
                        $property = (new ContextNode($this->code_base, $this->context, $value_node))->getProperty($value_node->children['prop'], false);
                        // Set the element type on each element of
                        // the list
                        $property->setUnionType($element_type);
                    } catch (UnanalyzableException $exception) {
                        // Ignore it. There's nothing we can do.
                    } catch (NodeException $exception) {
                        // Ignore it. There's nothing we can do.
                    } catch (IssueException $exception) {
                        Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                        $ret5902c6f1b229c = $this->context;
                        if (!$ret5902c6f1b229c instanceof Context) {
                            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b229c) == "object" ? get_class($ret5902c6f1b229c) : gettype($ret5902c6f1b229c)) . " given");
                        }
                        return $ret5902c6f1b229c;
                    }
                }
            }
        }
        $ret5902c6f1b25b3 = $this->context;
        if (!$ret5902c6f1b25b3 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b25b3) == "object" ? get_class($ret5902c6f1b25b3) : gettype($ret5902c6f1b25b3)) . " given");
        }
        return $ret5902c6f1b25b3;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitDim(Node $node)
    {
        // Make the right type a generic (i.e. int -> int[])
        $right_type = $this->right_type->asGenericArrayTypes();
        if ($node->children['expr']->kind == \ast\AST_VAR) {
            $variable_name = (new ContextNode($this->code_base, $this->context, $node))->getVariableName();
            if (Variable::isSuperglobalVariableWithName($variable_name)) {
                $ret5902c6f1b29d2 = $this->analyzeSuperglobalDim($node, $variable_name);
                if (!$ret5902c6f1b29d2 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b29d2) == "object" ? get_class($ret5902c6f1b29d2) : gettype($ret5902c6f1b29d2)) . " given");
                }
                return $ret5902c6f1b29d2;
            }
        }
        // Recurse into whatever we're []'ing
        $context = (new AssignmentVisitor($this->code_base, $this->context, $node, $right_type, true))($node->children['expr']);
        $ret5902c6f1b2d56 = $context;
        if (!$ret5902c6f1b2d56 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b2d56) == "object" ? get_class($ret5902c6f1b2d56) : gettype($ret5902c6f1b2d56)) . " given");
        }
        return $ret5902c6f1b2d56;
    }
    /**
     * Analyze an assignment where $variable_name is a superglobal, and return the new context.
     * May create a new variable in $this->context.
     * TODO: Emit issues if the assignment is incompatible with the pre-existing type?
     */
    private function analyzeSuperglobalDim(Node $node, $variable_name)
    {
        if (!is_string($variable_name)) {
            throw new \InvalidArgumentException("Argument \$variable_name passed to analyzeSuperglobalDim() must be of the type string, " . (gettype($variable_name) == "object" ? get_class($variable_name) : gettype($variable_name)) . " given");
        }
        $dim = $node->children['dim'];
        if ('GLOBALS' === $variable_name) {
            if (!is_string($dim)) {
                $ret5902c6f1b30f2 = $this->context;
                if (!$ret5902c6f1b30f2 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b30f2) == "object" ? get_class($ret5902c6f1b30f2) : gettype($ret5902c6f1b30f2)) . " given");
                }
                return $ret5902c6f1b30f2;
            }
            // assert(is_string($dim), "dim is not a string");
            if (Variable::isSuperglobalVariableWithName($dim)) {
                $ret5902c6f1b33ec = $this->context;
                if (!$ret5902c6f1b33ec instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b33ec) == "object" ? get_class($ret5902c6f1b33ec) : gettype($ret5902c6f1b33ec)) . " given");
                }
                return $ret5902c6f1b33ec;
            }
            $variable = new Variable($this->context, $dim, $this->right_type, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->flags, @0));
            $this->context->addGlobalScopeVariable($variable);
        }
        $ret5902c6f1b37b2 = $this->context;
        if (!$ret5902c6f1b37b2 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b37b2) == "object" ? get_class($ret5902c6f1b37b2) : gettype($ret5902c6f1b37b2)) . " given");
        }
        return $ret5902c6f1b37b2;
    }
    /**
     * @param Node $node
     * A node to parse, for an instance property.
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitProp(Node $node)
    {
        $property_name = $node->children['prop'];
        // Things like $foo->$bar
        if (!is_string($property_name)) {
            $ret5902c6f1b3d5b = $this->context;
            if (!$ret5902c6f1b3d5b instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b3d5b) == "object" ? get_class($ret5902c6f1b3d5b) : gettype($ret5902c6f1b3d5b)) . " given");
            }
            return $ret5902c6f1b3d5b;
        }
        assert(is_string($property_name), "Property must be string");
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, $node->children['expr']))->getClassList();
        } catch (CodeBaseException $exception) {
            $ret5902c6f1b4113 = $this->context;
            if (!$ret5902c6f1b4113 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b4113) == "object" ? get_class($ret5902c6f1b4113) : gettype($ret5902c6f1b4113)) . " given");
            }
            return $ret5902c6f1b4113;
        } catch (\Exception $exception) {
            $ret5902c6f1b43e7 = $this->context;
            if (!$ret5902c6f1b43e7 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b43e7) == "object" ? get_class($ret5902c6f1b43e7) : gettype($ret5902c6f1b43e7)) . " given");
            }
            return $ret5902c6f1b43e7;
        }
        foreach ($class_list as $clazz) {
            // Check to see if this class has the property or
            // a setter
            if (!$clazz->hasPropertyWithName($this->code_base, $property_name)) {
                if (!$clazz->hasMethodWithName($this->code_base, '__set')) {
                    continue;
                }
            }
            try {
                $property = $clazz->getPropertyByNameInContext($this->code_base, $property_name, $this->context, false);
            } catch (IssueException $exception) {
                Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                $ret5902c6f1b4882 = $this->context;
                if (!$ret5902c6f1b4882 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b4882) == "object" ? get_class($ret5902c6f1b4882) : gettype($ret5902c6f1b4882)) . " given");
                }
                return $ret5902c6f1b4882;
            }
            if (!$this->right_type->canCastToExpandedUnionType($property->getUnionType(), $this->code_base) && !$clazz->getHasDynamicProperties($this->code_base)) {
                // TODO: optionally, change the message from "::" to "->"?
                $this->emitIssue(Issue::TypeMismatchProperty, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), (string) $this->right_type, "{$clazz->getFQSEN()}::{$property->getName()}", (string) $property->getUnionType());
                $ret5902c6f1b4cbe = $this->context;
                if (!$ret5902c6f1b4cbe instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b4cbe) == "object" ? get_class($ret5902c6f1b4cbe) : gettype($ret5902c6f1b4cbe)) . " given");
                }
                return $ret5902c6f1b4cbe;
            } else {
                // If we're assigning to an array element then we don't
                // know what the constitutation of the parameter is
                // outside of the scope of this assignment, so we add to
                // its union type rather than replace it.
                if ($this->is_dim_assignment) {
                    $property->getUnionType()->addUnionType($this->right_type);
                }
            }
            // After having checked it, add this type to it
            $property->getUnionType()->addUnionType($this->right_type);
            $ret5902c6f1b500c = $this->context;
            if (!$ret5902c6f1b500c instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b500c) == "object" ? get_class($ret5902c6f1b500c) : gettype($ret5902c6f1b500c)) . " given");
            }
            return $ret5902c6f1b500c;
        }
        $std_class_fqsen = FullyQualifiedClassName::getStdClassFQSEN();
        if (Config::get()->allow_missing_properties || !empty($class_list) && $class_list[0]->getFQSEN() == $std_class_fqsen) {
            try {
                // Create the property
                $property = (new ContextNode($this->code_base, $this->context, $node))->getOrCreateProperty($property_name, false);
                $property->getUnionType()->addUnionType($this->right_type);
            } catch (\Exception $exception) {
                // swallow it
            }
        } elseif (!empty($class_list)) {
            $this->emitIssue(Issue::UndeclaredProperty, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), "{$class_list[0]->getFQSEN()}->{$property_name}");
        } else {
            // If we hit this part, we couldn't figure out
            // the class, so we ignore the issue
        }
        $ret5902c6f1b5571 = $this->context;
        if (!$ret5902c6f1b5571 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b5571) == "object" ? get_class($ret5902c6f1b5571) : gettype($ret5902c6f1b5571)) . " given");
        }
        return $ret5902c6f1b5571;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     *
     * @see $this->visitProp
     */
    public function visitStaticProp(Node $node)
    {
        $property_name = $node->children['prop'];
        // Things like self::${$x}
        if (!is_string($property_name)) {
            $ret5902c6f1b58cd = $this->context;
            if (!$ret5902c6f1b58cd instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b58cd) == "object" ? get_class($ret5902c6f1b58cd) : gettype($ret5902c6f1b58cd)) . " given");
            }
            return $ret5902c6f1b58cd;
        }
        assert(is_string($property_name), "Static property must be string");
        try {
            $class_list = (new ContextNode($this->code_base, $this->context, $node->children['class']))->getClassList();
        } catch (CodeBaseException $exception) {
            $ret5902c6f1b5c8d = $this->context;
            if (!$ret5902c6f1b5c8d instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b5c8d) == "object" ? get_class($ret5902c6f1b5c8d) : gettype($ret5902c6f1b5c8d)) . " given");
            }
            return $ret5902c6f1b5c8d;
        } catch (\Exception $exception) {
            $ret5902c6f1b5f68 = $this->context;
            if (!$ret5902c6f1b5f68 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b5f68) == "object" ? get_class($ret5902c6f1b5f68) : gettype($ret5902c6f1b5f68)) . " given");
            }
            return $ret5902c6f1b5f68;
        }
        foreach ($class_list as $clazz) {
            // Check to see if this class has the property
            if (!$clazz->hasPropertyWithName($this->code_base, $property_name)) {
                continue;
            }
            try {
                // Look for static properties with that $property_name
                $property = $clazz->getPropertyByNameInContext($this->code_base, $property_name, $this->context, true);
            } catch (IssueException $exception) {
                Issue::maybeEmitInstance($this->code_base, $this->context, $exception->getIssueInstance());
                $ret5902c6f1b639d = $this->context;
                if (!$ret5902c6f1b639d instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b639d) == "object" ? get_class($ret5902c6f1b639d) : gettype($ret5902c6f1b639d)) . " given");
                }
                return $ret5902c6f1b639d;
            }
            if (!$this->right_type->canCastToExpandedUnionType($property->getUnionType(), $this->code_base)) {
                // Currently, same warning type for static and non-static property type mismatches.
                $this->emitIssue(Issue::TypeMismatchProperty, call_user_func(function ($v1, $v2) {
                    return isset($v1) ? $v1 : $v2;
                }, @$node->lineno, @0), (string) $this->right_type, "{$clazz->getFQSEN()}::{$property->getName()}", (string) $property->getUnionType());
                $ret5902c6f1b67a3 = $this->context;
                if (!$ret5902c6f1b67a3 instanceof Context) {
                    throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b67a3) == "object" ? get_class($ret5902c6f1b67a3) : gettype($ret5902c6f1b67a3)) . " given");
                }
                return $ret5902c6f1b67a3;
            } else {
                // If we're assigning to an array element then we don't
                // know what the constitutation of the parameter is
                // outside of the scope of this assignment, so we add to
                // its union type rather than replace it.
                if ($this->is_dim_assignment) {
                    $property->getUnionType()->addUnionType($this->right_type);
                }
            }
            // After having checked it, add this type to it
            $property->getUnionType()->addUnionType($this->right_type);
            $ret5902c6f1b6af6 = $this->context;
            if (!$ret5902c6f1b6af6 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b6af6) == "object" ? get_class($ret5902c6f1b6af6) : gettype($ret5902c6f1b6af6)) . " given");
            }
            return $ret5902c6f1b6af6;
        }
        if (!empty($class_list)) {
            $this->emitIssue(Issue::UndeclaredStaticProperty, call_user_func(function ($v1, $v2) {
                return isset($v1) ? $v1 : $v2;
            }, @$node->lineno, @0), $property_name, (string) $class_list[0]->getFQSEN());
        } else {
            // If we hit this part, we couldn't figure out
            // the class, so we ignore the issue
        }
        $ret5902c6f1b6e99 = $this->context;
        if (!$ret5902c6f1b6e99 instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b6e99) == "object" ? get_class($ret5902c6f1b6e99) : gettype($ret5902c6f1b6e99)) . " given");
        }
        return $ret5902c6f1b6e99;
    }
    /**
     * @param Node $node
     * A node to parse
     *
     * @return Context
     * A new or an unchanged context resulting from
     * parsing the node
     */
    public function visitVar(Node $node)
    {
        $variable_name = (new ContextNode($this->code_base, $this->context, $node))->getVariableName();
        // Check to see if the variable already exists
        if ($this->context->getScope()->hasVariableWithName($variable_name)) {
            $variable = $this->context->getScope()->getVariableByName($variable_name);
            // If we're assigning to an array element then we don't
            // know what the constitutation of the parameter is
            // outside of the scope of this assignment, so we add to
            // its union type rather than replace it.
            if ($this->is_dim_assignment) {
                $variable->getUnionType()->addUnionType($this->right_type);
            } else {
                // If the variable isn't a pass-by-reference paramter
                // we clone it so as to not disturb its previous types
                // as we replace it.
                if ($variable instanceof Parameter) {
                    if ($variable->isPassByReference()) {
                    } else {
                        $variable = clone $variable;
                    }
                } else {
                    $variable = clone $variable;
                }
                $variable->setUnionType($this->right_type);
            }
            $this->context->addScopeVariable($variable);
            $ret5902c6f1b73e6 = $this->context;
            if (!$ret5902c6f1b73e6 instanceof Context) {
                throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b73e6) == "object" ? get_class($ret5902c6f1b73e6) : gettype($ret5902c6f1b73e6)) . " given");
            }
            return $ret5902c6f1b73e6;
        }
        $variable = Variable::fromNodeInContext($this->assignment_node, $this->context, $this->code_base, false);
        // Set that type on the variable
        $variable->getUnionType()->addUnionType($this->right_type);
        // Note that we're not creating a new scope, just
        // adding variables to the existing scope
        $this->context->addScopeVariable($variable);
        $ret5902c6f1b779c = $this->context;
        if (!$ret5902c6f1b779c instanceof Context) {
            throw new \InvalidArgumentException("Argument returned must be of the type Context, " . (gettype($ret5902c6f1b779c) == "object" ? get_class($ret5902c6f1b779c) : gettype($ret5902c6f1b779c)) . " given");
        }
        return $ret5902c6f1b779c;
    }
}