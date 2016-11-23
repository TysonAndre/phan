<?php

class foo {

    /**
     * @param string $a
     */
    public static function bar($a) : int {
        if (!is_int($a)) {
            return strlen($a);
        }
        echo $a;
        return $a;
    }
}
