<?php

class ASTRewritingCheck {
    /**
     * @param string $a
     */
    public static function bar($a) : int {
        if (!is_int($a)) {
            return strlen($a);
        }
        echo strlen($a);  // Emits issue: this is treated like it is within a block `if (is_int($a))`
        return $a;
    }
}
