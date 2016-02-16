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

/*

S->(START_TWO)

START_TWO->{STATES},{ALPHABET},{RULES},{START},{FINISH}

STATES -> state STATES_N
STATES_N -> epsilon
STATES_N -> ,state STATES_N

ALPHABET -> symbol ALPHABET_N
ALPHABET_N -> epsilon
ALPHABET_N -> ,symbol ALPHABET_N

FINISH -> state FINISH_N
FINISH_N -> epsilon
FINISH_N -> ,state FINISH_N

RULES -> RULE RULUES_N
RULE -> state symbol -> state
RULES_N -> epsilon
RULES_N -> ,RULE RULES_N

*/