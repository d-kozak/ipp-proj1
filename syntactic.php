<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:07
 */

function syntactic_analysis()
{
    /*
    while(($token = get_next_token()) != null){
        echo "token: ";
        print_r($token);
        echo PHP_EOL;
        //fgetc(STDIN);
    }
    return 1;
    */
    print_info_line("Started syntactic analysis");
    if(S()){
        print_info_line("Syntactic ok");
    } else {
        print_error_line("Syntactic error");
        exit(41);
    }
    print_info_line("Finishes syntactic analysis");
}

/*

S->(START_TWO)

START_TWO->{STATES},{ALPHABET},{RULES},state,{FINISH}

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

function S()
{
    global $debug;
    if ($debug)
        print_info_line("\trule S");

    $token1 = get_and_print_next_token();
    if ($token1["id"] != Tokens::bracket_open)
        return false;

    $result = START_TWO();
    if (!$result)
        return false;

    $token1 = get_and_print_next_token();
    if ($token1["id"] != Tokens::bracket_close)
        return false;

    return true;
}

function START_TWO()
{
    global $debug;
    if ($debug)
        print_info_line("\trule S-2");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $result = STATES();
    if (!$result)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;


    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $result = ALPHABET();
    if (!$result)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;


    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $result = RULES();
    if (!$result)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_state)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $result = FINISH();
    if (!$result)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    return true;
}

function STATES()
{
    global $debug;
    if ($debug)
        print_info_line("\trule STATES");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_state)
        return false;

    return STATES_N();

}

function STATES_N()
{
    global $debug;
    if ($debug)
        print_info_line("\trule STATES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $token = get_and_print_next_token();

        if ($token["id"] == Tokens::fi_state)
            return STATES_N();
        else return false;
    } elseif($token["id"] == Tokens::curly_bracket_close){
        put_token_back($token);
        return true;
    } else {
        return false;
    }

}

function ALPHABET()
{
    global $debug;
    if ($debug)
        print_info_line("\trule ALPHABET");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_symbol)
        return false;

    return ALPHABET_N();

}

function ALPHABET_N()
{
    global $debug;
    if ($debug)
        print_info_line("\trule STATES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $token = get_and_print_next_token();

        if ($token["id"] == Tokens::fi_symbol)
            return ALPHABET_N();
        else return false;
    } elseif($token["id"] == Tokens::curly_bracket_close){
        put_token_back($token);
        return true;
    } else {
        return false;
    }

}

function RULES()
{
    global $debug;
    if ($debug)
        print_info_line("\trule SET OF RULES");

    $result = RULE();
    if(!$result)
        return false;

    return RULES_N();

}

function RULE(){
    global $debug;
    if ($debug)
        print_info_line("\trule RULE");

    $token = get_and_print_next_token();
    if($token["id"] != Tokens::fi_state)
        return false;


    $token = get_and_print_next_token();
    if($token["id"] != Tokens::fi_symbol)
        return false;

    $token = get_and_print_next_token();
    if($token["id"] != Tokens::arrow)
        return false;

    $token = get_and_print_next_token();
    if($token["id"] != Tokens::fi_state)
        return false;

    return true;
}

function RULES_N()
{
    global $debug;
    if ($debug)
        print_info_line("\trule RULES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $result = RULE();
        if($result)
            return RULES_N();
        else
            return false;

    } elseif($token["id"] == Tokens::curly_bracket_close){
        put_token_back($token);
        return true;
    } else {
        return false;
    }

}

function FINISH(){
    global $debug;
    if ($debug)
        print_info_line("\trule FINISH");

    $token = get_and_print_next_token();
    if($token["id"] == Tokens::curly_bracket_close){
        put_token_back($token);
        return true; // prazdna mnozina
    }

    if($token["id"] != Tokens::fi_state){
        return false;
    }

    return FINISH_N();
}

function FINISH_N(){
    global $debug;
    if ($debug)
        print_info_line("\trule FINISH_N");

    $token = get_and_print_next_token();
    if($token["id"] == Tokens::curly_bracket_close){
        put_token_back($token);
        return true; // prazdna mnozina
    }elseif($token["id"] == Tokens::comma){
        $token = get_and_print_next_token();
        if($token["id"] != Tokens::fi_state)
            return false;
        return FINISH_N();
    }
    return false;
}