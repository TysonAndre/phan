<?php
// Phan should detect and catch some types of misuse of trait adaptations(`insteadof`/`as`)

trait Trait291 {
    public function baz() { }
    public function xyz() { }
    public function shortform(int $x) { }
}
trait Trait291B {
}

class A291 {
    use Trait291 {
        Trait291::foo as bar;
        Trait291B::xyz insteadof Trait291;
        Trait291C::zz as zzAlias;
        shortform as shortformalias;
        shortform as private shortformaliasPrivate;  // FIXME track visibility of trait method aliases
        shortform2 as shortformAliasOfMissing;
    }
}

function test291() {
    $x = new A291();
    $x->baz();
    $x->bar();
    $x->foo();
    $x->shortform(2);
    $x->shortform([2]);
    $x->shortformalias(2);
    $x->shortformalias([2]);
    $x->shortformaliasPrivate(2);  // FIXME should warn about calling private method
    $x->shortform2();
    $x->shortformAliasOfMissing();
}
