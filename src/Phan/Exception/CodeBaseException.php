<?php

/*
 * This code has been transpiled via TransPHPile. For more information, visit https://github.com/jaytaph/transphpile
 */
namespace Phan\Exception;

use Phan\Language\FQSEN;
class CodeBaseException extends \Exception
{
    /** @var FQSEN|null */
    private $missing_fqsen;
    /**
     * @param FQSEN|null $missing_fqsen
     * The FQSEN that cannot be found in the code base
     *
     * @param string $message
     * The error message
     */
    public function __construct(FQSEN $missing_fqsen = null, $message = "")
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException("Argument \$message passed to __construct() must be of the type string, " . (gettype($message) == "object" ? get_class($message) : gettype($message)) . " given");
        }
        parent::__construct($message);
        $this->missing_fqsen = $missing_fqsen;
    }
    /**
     * @return bool
     * True if we have an FQSEN defined
     *
     * @suppress PhanUnreferencedMethod
     */
    public function hasFQSEN()
    {
        $ret5902c6f434536 = !empty($this->missing_fqsen);
        if (!is_bool($ret5902c6f434536)) {
            throw new \InvalidArgumentException("Argument returned must be of the type bool, " . gettype($ret5902c6f434536) . " given");
        }
        return $ret5902c6f434536;
    }
    /**
     * @return FQSEN
     * The missing FQSEN
     */
    public function getFQSEN()
    {
        $ret5902c6f4347f9 = $this->missing_fqsen;
        if (!$ret5902c6f4347f9 instanceof FQSEN) {
            throw new \InvalidArgumentException("Argument returned must be of the type FQSEN, " . (gettype($ret5902c6f4347f9) == "object" ? get_class($ret5902c6f4347f9) : gettype($ret5902c6f4347f9)) . " given");
        }
        return $ret5902c6f4347f9;
    }
}