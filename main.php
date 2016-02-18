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

$res = syntactic_analysis();
if(!$res)
    exit(41);

if(!$FI->check_fi())
    exit(42);

foreach($FI->getStates() as $state){
    print_info_line("exploring state " . $state);
    print_r($FI->get_epsilon_uzaver($state));
    print_info_line("finished exploring");
}

$FI->remove_epsilon_rules();

$FI->print_FI();

$FI->determinize();

if($file != null)
    fclose($file);
