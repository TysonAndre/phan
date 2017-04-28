<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Language;

/**
 * A Fully-Qualified Name
 */
interface FQSEN
{
    /**
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class::method' or
     * 'Class' or 'Class::method'.
     *
     * @return static
     */
    public static function fromFullyQualifiedString($fully_qualified_string);
    /**
     * @param Context $context
     * The context in which the FQSEN string was found
     *
     * @param $fqsen_string
     * An FQSEN string like '\Namespace\Class::method' or
     * 'Class' or 'Class::method'.
     *
     * @return static
     */
    public static function fromStringInContext($string, Context $context);
    /**
     * @return string
     * The class associated with this FQSEN or
     * null if not defined
     */
    public function getName();
    /**
     * @return string
     * The canonical representation of the name of the object. Functions
     * and Methods, for instance, lowercase their names.
     */
    public static function canonicalName($name);
    /**
     * @return static
     * Get the canonical (non-alternate) FQSEN associated
     * with this FQSEN
     */
    public function getCanonicalFQSEN();
    /**
     * @return string
     * A string representation of this fully-qualified
     * structural element name.
     */
    public function __toString();
}