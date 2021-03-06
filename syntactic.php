<?php
#Modul syntakticke analyzy
#DKA:xkozak15

/*
LL gramatika pro klasickou variantu, nasledujici funkce ji modeluji za pomoci rekurzivniho sestupu

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
RULES -> epsilon
RULE -> state symbol -> state
RULES_N -> epsilon
RULES_N -> ,RULE RULES_N

*/

/**
 * Hlavni funkce modulu, rekurzivne v sobe vola ostatni funkce, v zavislosti na vstupnich argumentech skriptu
 * spusti bud klasickou syntaktickou analyzu ci syntaktickou analyzu pro rozsireni RUL
 * @return bool true OK, false pri syntakticke chybe
 */
function syntactic_analysis()
{
    global $arguments;
    print_info_line("Started syntactic analysis");
    $res = null;
    if($arguments["just_rules"]){
        $res = JUST_RULES_START();
    } else {
        $res = S();
    }
    if ($res) {
        print_info_line("Syntactic ok");
    } else {
        print_error_line("Syntactic error");
        return false;
    }
    print_info_line("Finishes syntactic analysis");

    return true;
}

/**
 * Hlavni funkce klasicke syntakticke analyzy
 * @see syntactic_analysis
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

    // overeni, ze na konci neni smeti...
    if(get_and_print_next_token() != null){
        return false;
    }

    return true;
}

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

    print_var($states);

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

    print_var($alphabet);

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

    print_var($rules);

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
    print_var($startState);

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

    print_var($finishStates);

    $token = get_and_print_next_token();
    if ($token["id"] != Tokens::curly_bracket_close)
        return false;

    $FI = new FI($states,$alphabet,$rules,$startState,$finishStates);

    return true;
}

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
function RULES(&$rules)
{
    global $debug;
    if ($debug)
        print_info_line("\trule SET OF RULES");

    $token = get_and_print_next_token();
    if($token["id"] == Tokens::curly_bracket_close){
        // prazdna mnozina pravidel
        put_token_back($token);
        return true;
    }
    put_token_back($token);

    $rule = null;
    $result = RULE($rule);
    if (!$result)
        return false;
    $rules[] = $rule;

    return RULES_N($rules);

}

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
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


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////rozsireni rules ////////////////////////////////////////////////////////////////////////////////////
/*
LL gramatika:
START->RULE RULES_N
RULE->state symbol -> state DOT
DOT-> .
DOT-> epsilon
RULES_N->,RULE RULES_N
RULES_N->epsilon
*/

/**
 * Hlavni funkce syntakticke analyzy v rozsireni RUL
 * @see syntactic_analysis
 */
function JUST_RULES_START(){
    print_info_line("JUST_RULES_START");

    global $FI;

    $rules = array();
    $endStates = array();
    $res = JUST_RULES($rules,$endStates);
    if(!$res)
        return false;


    $FI = FI::createFromRules($rules,$endStates);

    return true;
}

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
function JUST_RULES(&$rules,&$endStates)
{
    global $debug;
    if ($debug)
        print_info_line("\tjust_SET OF RULES");

    $rule = null;
    $is_ending = false;
    $result = JUST_RULE($rule,$is_ending);
    if (!$result)
        return false;
    $rules[] = $rule;

    if($is_ending)
        $endStates[] = $rule->getRightState();

    return JUST_RULES_N($rules,$endStates);

}

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
function JUST_RULE(&$rule,&$isEnding)
{
    global $debug,$buffer_id;
    if ($debug)
        print_info_line("\tJUST_RULE");

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

    $token = get_and_print_next_token();
    if($token["id"] == Tokens::dot)
        $isEnding = true;
    else {
        $isEnding = false;
        put_token_back($token);
    }


    return true;
}

/**
 * pomocna funkce syntakticke analyzy
 * @see syntactic_analysis
 */
function JUST_RULES_N(&$rules,&$endStates)
{
    global $debug;
    if ($debug)
        print_info_line("\tJUST_RULES_N");

    $token = get_and_print_next_token();
    if ($token["id"] == Tokens::comma) {
        $rule = null;
        $isEnding = false;
        $result = JUST_RULE($rule,$isEnding);
        if ($result) {
            $rules[]= $rule;
            if($isEnding)
                $endStates[] = $rule->getRightState();
            return JUST_RULES_N($rules,$endStates);
        }
        else
            return false;

    } else
        return $token == null;
}