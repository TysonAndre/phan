<?php

class BaseClass485 {
    private const _BASE_CONST = 3;
    protected const base__const = 2;
    const other_const = 'x';
}

class SubClass485 extends BaseClass485 {
    private const PrivateConstInSameClass = 'x';

    const CaseSensitive = 'y';
    public const CASESENSITIVE = 'z';

    public function example() {
        var_export(self::base_const);  // Should suggest base__const but not _BASE_CONST, because the former is private
        var_export(self::casesensitive);
        var_export(self::_casesensitive);
        var_export(self::PrivateConstInSameClass);
        var_export(self::private_const_in_same_class);
        var_export(self::privateConstInSameClass);
        var_export(self::otherconst);
    }
}
var_export(SubClass485::base_const);  // Should not suggest, all possible suggestions are private/protected
class UnrelatedClass {
    public function foo() {
        var_export(SubClass485::base_const);  // Should not suggest, all possible suggestions are private/protected
        var_export(SubClass485::casesensitive);  // Should not suggest, all possible suggestions are private/protected
    }
}
