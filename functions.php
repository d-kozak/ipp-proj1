<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 11:45
 */

function print_info_line($msg){
    global $debug;
    if($debug)
        echo "INFO: ".$msg.PHP_EOL;
}

function print_error_line($msg){
    file_put_contents('php://stderr',"ERROR: ".$msg.PHP_EOL);
}

function print_var($var){
    global $debug;
    if($debug)
        print_r($var);
}
