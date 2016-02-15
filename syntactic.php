<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:07
 */

function syntactic_analysis(){
    while(($token = get_next_token()) != null){
        echo "token: ";
        print_r($token);
        echo PHP_EOL;
        //fgetc(STDIN);
    }
    return 1;
}