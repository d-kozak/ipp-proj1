<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 12:13
 */

function parse_input($input){
    print_info_line(PHP_EOL."============Parsing new input==========".PHP_EOL);
    print_info_line(PHP_EOL."===========Input info start==========".PHP_EOL);
    print_info_line($input);
    print_info_line(PHP_EOL."===========Input info end==========".PHP_EOL);

    if (preg_match_all("/\({(.*?)},{(.*?)},{(.*)},(.*?),{(.*?)}\)/", $input, $result)) {
        $states = split(",", $result[1][0]);
        $alphabet = split(",",$result[2][0]);
        $rules = split(",",$result[3][0]);
        $start_state = $result[4];
        $finish_states = split(",",$result[5][0]);

        $FI = new FI($states,$alphabet,$rules,$start_state,$finish_states);

        print_var($FI->getStates());
        print_var($FI->getAlphabet());
        print_var($FI->getRules());
        print_var($FI->getStartState());
        print_var($FI->getFinishStates());
    }
    print_info_line(PHP_EOL."============Parsing finished==========".PHP_EOL);
}