<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language\Type;

use Phan\Language\Type;
class TemplateType extends Type
{
    /** @var string */
    private $template_type_identifier;
    /**
     * @param string $template_type_identifier
     * An identifier for the template type
     */
    public function __construct($template_type_identifier)
    {
        if (!is_string($template_type_identifier)) {
            throw new \InvalidArgumentException("Argument \$template_type_identifier passed to __construct() must be of the type string, " . (gettype($template_type_identifier) == "object" ? get_class($template_type_identifier) : gettype($template_type_identifier)) . " given");
        }
        $this->template_type_identifier = $template_type_identifier;
    }
    /**
     * @return string
     * The name associated with this type
     */
    public function getName()
    {
        $ret5902c6fd2af94 = $this->template_type_identifier;
        if (!is_string($ret5902c6fd2af94)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd2af94) . " given");
        }
        return $ret5902c6fd2af94;
    }
    /**
     * @return bool
     * True if this namespace is defined
     */
    public function hasNamespace()
    {
        $ret5902c6fd2b205 = false;
        if (!is_bool($ret5902c6fd2b205)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6fd2b205) . " given");
        }
        return $ret5902c6fd2b205;
    }
    /**
     * @return string
     * The namespace associated with this type
     */
    public function getNamespace()
    {
        $ret5902c6fd2b45f = '';
        if (!is_string($ret5902c6fd2b45f)) {
            throw new \InvalidArgumentException("Argument returned must be of the type string, " . gettype($ret5902c6fd2b45f) . " given");
        }
        return $ret5902c6fd2b45f;
    }
}