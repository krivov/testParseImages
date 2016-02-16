<?php
/**
 * Created by PhpStorm.
 * User: krivov
 * Date: 16.02.16
 * Time: 20:36
 */

include "src/common.php";

if (isset($argv[1])) {
    $parse = new Parser($argv[1]);

    if ($parse->isLoad()) {

    }
}