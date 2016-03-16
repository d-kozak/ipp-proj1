<?php
#Hlavni modul ridici beh celeho skriptu
#DKA:xkozak15

include 'include.php';

/*
 * Volani funkci pro nastaveni spravneho kodovani
 */
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_regex_encoding('UTF-8');
header('Content-type: text/plain; charset=utf-8');


// nejdrive se zpracuji argumenty
parse_arguments();

// pote probehne lexikalni a syntakcke analyza
// v ramci techto kontrol dojde pri uspechu k vytvoreni objektu konecneho automatu
if(!syntactic_analysis())
    exit(40);

// semanticke kontrola konecneho automatu
if(!$FI->check_fi())
    exit(41);

// V zavislosti na argumentech dojde k provedeni specificke operace nad automatem
if($arguments["op"] == Operation::no_eps)
    $FI->remove_epsilon_rules();
elseif($arguments["op"] == Operation::determinization)
    $FI->determinize();
elseif($arguments["op"] == Operation::wsfa)
    $FI->wsfa();
elseif($arguments["op"] == Operation::check_string)
    $FI->check_string($arguments["string"]);
elseif($arguments["op"] != Operation::validation){
    print_error_line("Internal error, none of known operations was chosen");
    exit(108);
}

// Na zaver dojde k vypsani konecneho automatu
// Vyjimka je v rozsireni STR, zda se pouze vypise 1 pri uspechu(retezec prijat) a 0 pri neuspechu
if($arguments["op"] != Operation::check_string)
    $FI->print_FI();

// Pokud se zapisovalo do souboru, dojde na zaver k jeho uzavreni
if($arguments["output"] != STDOUT)
    fclose($arguments["output"]);
