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

$fi = syntactic_analysis();

if($file != null)
    fclose($file);
