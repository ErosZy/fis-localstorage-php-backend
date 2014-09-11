<?php
/**
 * Created by PhpStorm.
 * User: wangcheng
 * Date: 14-9-10
 * Time: 14:49
 */

echo dirname(__FILE__);

$result = array(
    "first" => "time,jfkdl",
    "second" => ""
);

foreach($result as $key=>$value){
    var_dump(strlen($value));
    var_dump(explode(",", $value));
}

$result = null;

var_dump(isset($result));