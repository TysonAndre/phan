<?php

class A352 {
    public static function test() {
        B352::bar();
        $b = new B352();
        $b->bar();
        $b->baz();
    }
}
class B352 extends A352 {
    protected function __construct() {
    }

    protected static function bar() {
        echo "Called successfully\n";
    }
    private function baz() {
    }
}
A352::test();
