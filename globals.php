<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:10
 */

$file_name = 'input/input2.txt';
$arguments = array();

$file = null;

$buffer_id = "buffer";


abstract class Tokens
{
    const bracket_open = 0;
    const bracket_close = 1;
    const curly_bracket_open = 2;
    const curly_bracket_close = 3;
    const comma = 4;
    const fi_state = 5;
    const fi_symbol = 6;
    const dash = 7;
    const operator_bigger = 7;
}

abstract  class LexicalDFISTates{
    const start = 50;
    const symbol_1 = 51;
    const symbol_wait_for_quote = 53;
    const symbol_wait_for_double_quote = 54;
    const symbol_wait_for_double_quote_2 = 55;

    const state_1 = 60;
    const state_2 = 61;

    const comment = 62;
}
