<?php
#Modul pro pomocne  a debugovaci funkce
#DKA:xkozak15

/**
 * Fce vypise danou informaci na standardni vystup, ale pouze za predpokladu, ze jsou povoleny debugovaci informace
 * @param $msg
 */
function print_info_line($msg){
    global $debug;
    if($debug)
        echo "INFO: ".$msg.PHP_EOL;
}

/**
 * Funkce vypise zpravu $msg na standardni chybovy vystup
 * @param $msg
 */
function print_error_line($msg){
    file_put_contents('php://stderr',"ERROR: ".$msg.PHP_EOL);
}

/**
 * Funkce vypise obsah promenne var, ale pouze za predpokladu, ze jsou povoleny debugovaci informace
 * @param $var
 */
function print_var($var){
    global $debug;
    if($debug)
        print_r($var);
}
