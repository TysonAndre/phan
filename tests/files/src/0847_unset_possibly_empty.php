<?php
function test_mixed($result) {
    if (!$result) {
        return;
    }
    unset($result[0]);
    // src/empty.php:7 PhanRedundantCondition Redundant attempt to cast $result of type non-empty-mixed to truthy
    if (!$result) {
        echo "Now it's empty\n";
    } elseif ($result) {
        echo "Redundant\n";
        $result[44] = 55;
        var_export($result);
    }
}

function test_array(array $result) {
    if (!$result) {
        return;
    }
    unset($result[0]);
    if (!$result) {
        echo "Now it's empty\n";
    } elseif ($result) {
        echo "Redundant\n";
        unset($result[1]);
        if ($result) {
            echo "Not redundant\n";
        }
    }

}
