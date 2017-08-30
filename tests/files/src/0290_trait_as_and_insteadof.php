<?php

// Test that the code is case insensitive
trait ATrait290 {
    public function Foo(int $x) {
    }

    public function baR(float $x) {
    }
}

// Test that the code is case insensitive
trait BTrait290 {
    public function foO(string $x) {
    }

    public function bAr(array $x) {
    }
}

// Test that the code is case insensitive
class ClassUsingTrait290 {
    use ATrait290, BTRAIT290 {
        ATrait290::foo as foo2;
        BTrait290::fOo insteadof aTrait290;
        BTrait290::Bar insteadof ATrait290;
    }
}

function testUsingTrait290() {
    $x = new ClassUsingTrait290();
    $x->foo(42);
    $x->foo2(42);
    $x->foo3(42);
    $x->foo("?");
    $x->foo2("?");
    $x->bar(4.2);
    $x->bar([4.2]);
}
testUsingTrait290();
