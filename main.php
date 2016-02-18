<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14.2.16
 * Time: 22:53
 */

include 'include.php';



// TODO zeptat se Jaromira na kodovani
/*
echo mb_internal_encoding().PHP_EOL;
echo mb_internal_encoding('UTF-8').PHP_EOL;
echo mb_internal_encoding().PHP_EOL;

mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_regex_encoding('UTF-8');

exit; */

parse_arguments();

if(!syntactic_analysis())
    exit(41);

if(!$FI->check_fi())
    exit(42);

if($arguments["op"] == Operation::no_eps)
    $FI->remove_epsilon_rules();
elseif($arguments["op"] == Operation::determinization)
    $FI->determinize();
elseif($arguments["op"] != Operation::validation){
    print_error_line("Internal error, not of known operations was chosen");
    exit(666);
}

$FI->print_FI();

if($arguments["input"] != STDIN)
    fclose($arguments["input"]);

if($arguments["output"] != STDOUT)
    fclose($arguments["output"]);
