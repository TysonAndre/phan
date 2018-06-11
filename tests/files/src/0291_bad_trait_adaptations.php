<?php
// Phan should detect and catch some types of misuse of trait adaptations(`insteadof`/`as`)

trait Trait291 {
    public function baz() { }
    public function xyz() { }
}
trait Trait291B {
}

class A291 {
    use Trait291 {
        Trait291::foo as bar;
        Trait291B::xyz insteadof Trait291;
        Trait291C::zz as zzAlias;
    }
}

function test291() {
    $x = new A291();
    $x->baz();
    $x->bar();
    $x->foo();
}
