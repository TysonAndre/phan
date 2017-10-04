<?php declare(strict_types=1);
namespace Phan\Language\Element;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\FQSEN;
use Phan\Language\FQSEN\FullyQualifiedGlobalConstantName;
use Phan\Language\Type;
use Phan\Language\UnionType;

class GlobalConstant extends AddressableElement implements ConstantInterface
{
    use ConstantTrait;

    /**
     * Override the default getter to fill in a future
     * union type if available.
     *
     * @return UnionType
     */
    public function getUnionType() : UnionType
    {
        if (null !== ($union_type = $this->getFutureUnionType())) {
            $this->getUnionType()->addUnionType($union_type);
        }

        return parent::getUnionType();
    }

    /**
     * @return FullyQualifiedGlobalConstantName
     * The fully-qualified structural element name of this
     * structural element
     */
    public function getFQSEN() : FullyQualifiedGlobalConstantName
    {
        \assert(!empty($this->fqsen), "FQSEN must be defined");
        return $this->fqsen;
    }

    /**
     * @param CodeBase $code_base
     * @param string $name
     * The name of a builtin constant to build a new GlobalConstant structural
     * element from.
     *
     * @return GlobalConstant
     * A GlobalConstant structural element representing the given named
     * builtin constant.
     */
    public static function fromGlobalConstantName(
        CodeBase $code_base,
        string $name
    ) : GlobalConstant {
        if (!defined($name)) {
            throw new \InvalidArgumentException(sprintf("This should not happen, defined(%s) is false, but the constant was returned by get_defined_constants()", var_export($name, true)));
        }
        $value = constant($name);
        $constant_fqsen = FullyQualifiedGlobalConstantName::fromFullyQualifiedString(
            '\\' . $name
        );
        return new self(
            new Context(),
            $name,
            Type::fromObject($value)->asUnionType(),
            0,
            $constant_fqsen
        );
    }

    public function toStub() : string
    {
        [$namespace, $string] = $this->toStubInfo();
        $namespace_text = $namespace === '' ? '' : "$namespace ";
        $string = sprintf("namespace %s{\n%s}\n", $namespace_text, $string);
        return $string;
    }

    /** @return string[] [string $namespace, string $text] */
    public function toStubInfo() : array
    {
        $fqsen = (string)$this->getFQSEN();
        $pos = \strrpos($fqsen, '\\');
        if ($pos !== false) {
            $name = \substr($fqsen, $pos + 1);
            $namespace = \substr($fqsen, 0, $pos);
        } else {
            $name = $fqsen;
            $namespace = '';
        }
        $string = 'const ' . $name . ' = ';
        $namespace = ltrim($this->getFQSEN()->getNamespace(), '\\');
        if (\defined($fqsen)) {
            $string .= var_export(constant($fqsen), true) . ";\n";
        } else {
            $string .= "null;  // could not find\n";
        }
        return [$namespace, $string];
    }
}
