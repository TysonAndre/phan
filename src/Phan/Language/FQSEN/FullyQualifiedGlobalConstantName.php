<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\FQSEN;

/**
 * A Fully-Qualified Constant Name
 */
class FullyQualifiedGlobalConstantName extends FullyQualifiedGlobalStructuralElement implements FullyQualifiedConstantName
{
    /**
     * @return int
     * The namespace map type such as \ast\flags\USE_NORMAL or \ast\flags\USE_FUNCTION
     */
    protected static function getNamespaceMapType()
    {
        $ret5902c6f694e28 = \ast\flags\USE_CONST;
        if (!is_int($ret5902c6f694e28)) {
            throw new \InvalidArgumentException("Argument returned must be of the type int, " . gettype($ret5902c6f694e28) . " given");
        }
        return $ret5902c6f694e28;
    }
}