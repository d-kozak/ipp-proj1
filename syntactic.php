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
    if (S()) {
        print_info_line("Syntactic ok");
    } else {
        print_error_line("Syntactic error");
        return false;
    }
    print_info_line("Finishes syntactic analysis");

    return true;
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
    global $debug,$buffer_id,$FI;
    if ($debug)
        print_info_line("\trule S-2");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $states = array();
    $result = STATES($states);
    if (!$result)
        return false;

    print_r($states);

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;


    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $alphabet = array();
    $result = ALPHABET($alphabet);
    if (!$result)
        return false;

    print_r($alphabet);

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;


    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $rules = array();
    $result = RULES($rules);
    if (!$result)
        return false;

    print_r($rules);

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_state)
        return false;

    $startState = $token[$buffer_id];
    print_r($startState);

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::comma)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_open)
        return false;

    $finishStates = array();
    $result = FINISH($finishStates);
    if (!$result)
        return false;

    print_r($finishStates);

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $FI = new FI($states,$alphabet,$rules,$startState,$finishStates);

    return true;
}

function STATES(&$states)
{
    global $debug, $buffer_id;
    if ($debug)
        print_info_line("\trule STATES");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_state)
        return false;

    $states[] = $token[$buffer_id];

    return STATES_N($states);

}

function STATES_N(&$states)
{
    global $debug, $buffer_id;
    if ($debug)
        print_info_line("\trule STATES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $token = get_and_print_next_token();

        if ($token["id"] == Tokens::fi_state) {
            $states[] = $token[$buffer_id];
            return STATES_N($states);
        } else return false;
    } elseif ($token["id"] == Tokens::curly_bracket_close) {
        put_token_back($token);
        return true;
    } else {
        return false;
    }

}

function ALPHABET(&$alphabet)
{
    global $debug, $buffer_id;
    if ($debug)
        print_info_line("\trule ALPHABET");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_symbol)
        return false;

    $alphabet[] = $token[$buffer_id];

    return ALPHABET_N($alphabet);

}

function ALPHABET_N(&$alphabet)
{
    global $debug, $buffer_id;
    if ($debug)
        print_info_line("\trule STATES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $token = get_and_print_next_token();

        if ($token["id"] == Tokens::fi_symbol) {
            $alphabet[] = $token[$buffer_id];
            return ALPHABET_N($alphabet);
        } else return false;
    } elseif ($token["id"] == Tokens::curly_bracket_close) {
        put_token_back($token);
        return true;
    } else {
        return false;
    }

}

function RULES(&$rules)
{
    global $debug;
    if ($debug)
        print_info_line("\trule SET OF RULES");

    $rule = null;
    $result = RULE($rule);
    if (!$result)
        return false;
    $rules[] = $rule;

    return RULES_N($rules);

}

function RULE(&$rule)
{
    global $debug,$buffer_id;
    if ($debug)
        print_info_line("\trule RULE");

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_state)
        return false;
    $left_state = $token[$buffer_id];

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_symbol)
        return false;

    $left_symbol = $token[$buffer_id];

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::arrow)
        return false;

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::fi_state)
        return false;

    $right_state = $token[$buffer_id];

    $rule = new Rule($left_state,$left_symbol,$right_state);

    return true;
}

function RULES_N(&$rules)
{
    global $debug;
    if ($debug)
        print_info_line("\trule RULES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $rule = null;
        $result = RULE($rule);
        if ($result) {
            $rules[]= $rule;
            return RULES_N($rules);
        }
        else
            return false;

    } elseif ($token["id"] == Tokens::curly_bracket_close) {
        put_token_back($token);
        return true;
    } else {
        return false;
    }

}

function FINISH(&$finish_states)
{
    global $debug,$buffer_id;
    if ($debug)
        print_info_line("\trule FINISH");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::curly_bracket_close) {
        put_token_back($token);
        return true; // prazdna mnozina
    }

    if ($token["id"] != Tokens::fi_state) {
        return false;
    }

    $finish_states[] = $token[$buffer_id];

    return FINISH_N($finish_states);
}

function FINISH_N(&$finish_states)
{
    global $debug,$buffer_id;
    if ($debug)
        print_info_line("\trule FINISH_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::curly_bracket_close) {
        put_token_back($token);
        return true; // prazdna mnozina
    } elseif ($token["id"] == Tokens::comma) {
        $token = get_and_print_next_token();
        if ($token["id"] != Tokens::fi_state)
            return false;

        $finish_states[] = $token[$buffer_id];

        return FINISH_N($finish_states);
    }
    return false;
}