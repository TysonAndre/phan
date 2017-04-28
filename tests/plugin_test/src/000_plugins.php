<?php

function testInstanceofObject($x) {
    var_dump($x instanceof object);
}
testInstanceofObject(new stdClass());


function testDollarDollarPlugin($a, $b = 'a') {
    var_dump($$b);
}
testDollarDollarPlugin(42);


function testDuplicateArrayKeyPlugin() {
    var_dump([
        '0' => 'b',
        0 => 'c',
    ]);
    var_dump([
        'key' => 'b',
        'c',
    ]);
}
testDuplicateArrayKeyPlugin();

function testNoopIsset($Foo) {
    var_dump(isset($foo));
}
testNoopIsset('key');

function testNonBoolBranchPlugin(array $args) {
    if ($args) {
        var_dump($args);
    }
}
testNonBoolBranchPlugin(['value']);

function testNonBoolInLogicalArithVisitor(array $args) {
    if (is_array($args) && $args) {
        var_dump($args);
    }
}
testNonBoolInLogicalArithVisitor(['value']);

function testNumericalEqualityPlugin() {
    var_dump('2e3' == '2000');
    var_dump('2e3' === '2000');  // this is fine
    var_dump(2.0 !== 2);
    var_dump(2.0 != 2);  // this is fine
}
testNumericalEqualityPlugin();

/** @suppress PhanParamTooFew - testing UnusedSuppressionPlugin */
function testUnusedSuppressionPlugin() {
    var_dump(intdiv(84, 2));
}
testUnusedSuppressionPlugin();

class FooTest {
    // Dead code detection should detect this
    public static function unused_static_method() {
    }
}

class FooSubclassTest extends FooTest {
    // Dead code detection should detect this, but shouldn't warn twice about unused_static_method
    public static function unused_static_method_in_subclass() {
        self::used_method(self::class);
    }

    public static function used_method(string $class) {}
}

$c = new FooTest();

// Dead code detection should detect this
function testUnreferencedFunction() {}

/**
 * Test that this doesn't create an uncaught CodeBaseException for dead code detection
 */
function accessUndefinedPropertyTest() {
    $c = new SimpleXMLElement('<widget>a</widget>');
    return $c->widget ?? null;
}

accessUndefinedPropertyTest();

/*
// TODO: fix https://github.com/etsy/phan/issues/715
class SubclassBaseTest {
    public function foo() {}
}
class SubclassSubclassTest extends SubclassBaseTest{
}

function f() {
    $s = new SubclassSubclassTest();
    $s->foo();
}
f();
 */
