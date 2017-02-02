<?php

/**
 * @property string $stringVar
 * @property DateTime $dateVar
 * @property mixed $mixedVar a magic property that explicitly has a mixed type
 * @property $undeclaredTypeVar a magic property that has a property annotation without a type (automatically inferred)
 */
class MagicPropertiesClass {
    /** @var string */
    private $_stringVar;
    /** @var DateTime */
    private $_dateVar;
    /** @var mixed */
    private $_mixedVar;
    private $_undeclaredTypeVar;

    public function __construct() {
        $this->_stringVar = 'value';
        $this->_dateVar = new DateTime();
        $this->_mixedVar = null;
        $this->_undeclaredTypeVar = null;
    }

    // A boilerplate getter that implements the declared property annotation.
    // Note: The getters aren't analyzed by phan to check if they match.
    public function __get($name) {
        switch($name) {
        case 'stringVar':
            return $this->_stringVar;
        case 'dateVar':
            return $this->_dateVar;
        case 'mixedVar':
            return $this->_mixedVar;
        case 'undeclaredTypeVar':
            return $this->_undeclaredTypeVar;
        default:
            return null;
        }
    }

    // A boilerplate setter that implements the declared property annotation.
    // The setters aren't yet analyzed by phan to check if they match.
    public function __set($name, $value) {
        switch($name) {
        case 'stringVar':
            $this->_stringVar = $value;
            return;
        case 'dateVar':
            $this->_dateVar = $value;
            return;
        case 'mixedVar':
            $this->_mixedVar = $value;
            return;
        case 'undeclaredTypeVar':
            $this->_undeclaredTypeVar = $value;
        default:
            return;
        }
    }
}

function testMagic() {
    $magic = new MagicPropertiesClass();
    $magic->stringVar = 'a';
    echo strlen($magic->stringVar) . "\n";
    $magic->stringVar = new stdClass();  // incorrect type
    $magic->dateVar = 42;  // Bug
    $magic->dateVar = new DateTime();
    echo intdiv($magic->stringVar, 4) . "\n";  // incorrect type for intdiv
    $magic->mixedVar = 'a';
    $magic->mixedVar = new stdClass();
    $magic->undeclaredTypeVar = 'a';
    $magic->undeclaredTypeVar = new stdClass();
}

function testMagic2() {
    // Should be able to infer types without assignments
    $magic = new MagicPropertiesClass();
    echo $magic->dateVar->getTimestamp() . "\n";
}

testMagic();
testMagic2();
