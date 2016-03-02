<?php
/**
* David Kozak
* Prvni projekt do IPP
* Determinizace Konecneho automatu
* rozsireni wsfa,rules,string
*/
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:10
 */
$arguments = array();

$buffer_id = "buffer";

$debug = false;
$debug_lexical = $debug;

$FI = null;

abstract class Tokens
{
    /*
    const bracket_open = 0;
    const bracket_close = 1;
    const curly_bracket_open = 2;
    const curly_bracket_close = 3;
    const comma = 4;
    const fi_state = 5;
    const fi_symbol = 6;
    const arrow = 7;
    */

    const bracket_open = "bracket_open";
    const bracket_close = "bracket_close";
    const curly_bracket_open = "curly_bracket_open";
    const curly_bracket_close = "curly_bracket_close";
    const comma = "comma";
    const fi_state = "fi_state";
    const fi_symbol = "fi_symbol";
    const arrow = "arrow";

    const dot = "dot";
}

abstract  class LexicalDFISTates{
    /*
    const start = 50;
    const symbol_1 = 51;
    const symbol_wait_for_quote = 53;
    const symbol_wait_for_double_quote = 54;
    const symbol_wait_for_double_quote_2 = 55;

    const state_1 = 60;
    const state_2 = 61;

	const dash = 62;
	
    const comment = 63;
    */

    const start = "start";
    const symbol_1 =  "symbol_1";
    const symbol_wait_for_quote = "symbol_wait_for_quote";
    const symbol_wait_for_double_quote = "symbol_wait_for_double_quote";
    const symbol_wait_for_double_quote_2 = "symbol_wait_for_double_quote 2";

    const state_1 = "state_1";
    const state_2 = "state_2";

    const dash = "dash";

    const comment = "comment";
}
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:10
 */

$last_char = null;
$next_token = null;

function put_token_back($token)
{
    global $next_token;
    if ($next_token != null) {
        print_error_line("next token variable should be always null when calling put_token_back");
        exit(666);
    }
    $next_token = $token;
}

function print_token($tkn)
{
    global $buffer_id;
    print_info_line("\tTOKEN:\n\t\ttype : " . $tkn["id"] . " \n\t\tvalue: " . $tkn[$buffer_id] . PHP_EOL);
}

function get_and_print_next_token()
{
    $token = get_next_token();
    print_token($token);
    return $token;
}

function read_one_char()
{
    global $arguments;
    //print_r($arguments["input"]);
    $char = array_shift($arguments["input"]);

    //print_r($arguments["input"]);
    //print_r($char);
    return $char;
}

function get_next_token()
{
    global $arguments, $last_char, $buffer_id, $next_token, $debug_lexical;

    if ($next_token != null) {
        $tmp = $next_token;
        $next_token = null;
        return $tmp;
    }

    $token = array();

    $token[$buffer_id] = "";
    $state = LexicalDFISTates::start;

    while (!empty($arguments["input"])) {

        if ($last_char != null) {
            $next_char = $last_char;
            $last_char = null;
        } else
            $next_char = read_one_char();

        if ($next_char == null)
            break;

        // echo "Next char: " . $next_char . PHP_EOL;

        if ($debug_lexical)
            if ($state != LexicalDFISTates::comment)
                echo "state: " . $state . PHP_EOL;

        switch ($state) {
            case LexicalDFISTates::start:
                if ($next_char == "(") {
                    $token["id"] = Tokens::bracket_open;
                    return $token;
                } elseif ($next_char == ")") {
                    $token["id"] = Tokens::bracket_close;
                    return $token;
                } elseif ($next_char == "{") {
                    $token["id"] = Tokens::curly_bracket_open;
                    return $token;
                } elseif ($next_char == "}") {
                    $token["id"] = Tokens::curly_bracket_close;
                    return $token;
                } elseif ($next_char == ",") {
                    $token["id"] = Tokens::comma;
                    return $token;
                } elseif ($next_char == "-") {
                    $state = LexicalDFISTates::dash;
                } elseif ($next_char == "#") {
                    $state = LexicalDFISTates::comment;
                } elseif ($next_char == ".") {
                    if ($arguments["just_rules"]) {
                        $token["id"] = Tokens::dot;
                        return $token;
                    } else
                        mindfuck($state, $next_char);;
                } elseif ($next_char == '\'')
                    $state = LexicalDFISTates::symbol_1;
                elseif (ctype_alnum($next_char)) {
                    $token[$buffer_id] .= $next_char;
                    $state = LexicalDFISTates::state_1;
                } elseif (ctype_space($next_char)) {
                    continue;
                } else
                    mindfuck($state, $next_char);
                break;

            case LexicalDFISTates::state_1:
                if (!(ctype_alnum($next_char) && $next_char != "_")) {
                    $last_char = $next_char;
                    $token["id"] = Tokens::fi_state;
                    return $token;

                } else if ($next_char == "_") {
                    $token[$buffer_id] .= $next_char;
                    $state = LexicalDFISTates::state_2;

                } else if (ctype_alnum($next_char)) {
                    $token[$buffer_id] .= $next_char;

                } elseif ($arguments["just_rules"] && $next_char == ".") {
                    $next_token = ".";
                    $token["id"] = Tokens::fi_state;
                    return $token;
                } else
                    mindfuck($state, $next_char);
                break;

            case LexicalDFISTates::state_2:
                if (ctype_alpha($next_char)) {
                    $token[$buffer_id] .= $next_char;
                    $state = LexicalDFISTates::state_1;
                } else if ($next_char == "_") {
                    $token[$buffer_id] .= $next_char;
                } else
                    mindfuck($state, $next_char);
                break;

            case LexicalDFISTates::symbol_1:
                if ($next_char == '\'') {
                    $state = LexicalDFISTates::symbol_wait_for_double_quote;
                } else {
                    $token[$buffer_id] .= $next_char;
                    //echo $next_char." ";
                    $state = LexicalDFISTates::symbol_wait_for_quote;
                }
                break;
            case LexicalDFISTates::symbol_wait_for_quote:
                //echo $next_char.PHP_EOL;
                if ($next_char == '\'') {
                    $token["id"] = Tokens::fi_symbol;
                    return $token;
                } else
                    mindfuck($state, $next_char);
                break;

            case LexicalDFISTates::symbol_wait_for_double_quote:
                if ($next_char == '\'') {
                    $state = LexicalDFISTates::symbol_wait_for_double_quote_2;
                } else {
                    $last_char = $next_char;
                    $token["id"] = Tokens::fi_symbol;
                    $token[$buffer_id] = " ";
                    return $token;
                }
                break;

            case LexicalDFISTates::symbol_wait_for_double_quote_2:
                if ($next_char == '\'') {
                    $token["id"] = Tokens::fi_symbol;
                    $token[$buffer_id] = '\'\'';
                    return $token;
                } else {
                    mindfuck($state, $next_char);
                }
                break;

            case LexicalDFISTates::dash:
                if ($next_char == ">") {
                    $token["id"] = Tokens::arrow;
                    return $token;
                } else
                    mindfuck($state, $next_token);
                break;

            case LexicalDFISTates::comment:
                if ($next_char == "\n")
                    $state = LexicalDFISTates::start;
                break;

            default:
                print_error_line("Default state in get next token Fi");
                exit(666);

        }
    }
    return null;
}

function mindfuck($state, $next_char)
{
    print_error_line("bad char " . $next_char . " in state " . $state . " in get next token");
    echo "value = " . intval($next_char) . PHP_EOL;
    exit(666);
}/**
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
RULES -> epsilon
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

    // overeni, ze na konci neni smeti...
    if(get_and_print_next_token() != null){
        return false;
    }

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


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////rozsireni rules ////////////////////////////////////////////////////////////////////////////////////
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
}/**
 * Created by PhpStorm.
 * User: david
 * Date: 18.2.16
 * Time: 12:38
 */

function parse_arguments()
{
    global $arguments;


    $long_opts = array(
        "help",
        "input:",
        "output:",
        "no-epsilon-rules",
        "determinization",
        "case-insensitive",
        "wsfa",
        "analyze-string:",
        "rules-only"
    );

    $short_opts = "edir"; // e=no epsilon d=determinization  i=case insensitive

    $options = getopt($short_opts, $long_opts);

    /*
    if(empty($options)) {
        print_error_line("Please add arguments via --help");
        exit(1);
    }
    */

    // remove the colons..
    $long_opts[1] = substr($long_opts[1], 0, -1);
    $long_opts[2] = substr($long_opts[2], 0, -1);
    $long_opts[7] = substr($long_opts[7], 0, -1);

    print_var($options);

    if (isset($options[$long_opts[0]])) { //help
        if (count($options) > 2) {
            print_error_line("Parameter help cant be combined with other arguments");
            exit(1);
        }
        print_help();
    }

    if (isset($options[$long_opts[1]])) {
        $file = fopen($options[$long_opts[1]], "r");
        if (!$file) {
            print_error_line("Cant open input file " . $options[$long_opts[1]] . ", exiting");
            exit(2);
        }
        $chars = parse_input_file_to_chars($file,$options[$long_opts[1]]);
        $arguments["input"] = $chars;
    } else {
        $filecontent = file_get_contents("php://stdin");
        $l = 0;
        $res = preg_split('/(.{'.$l.'})/us', $filecontent, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        $arguments["input"] = $res;
    }

    if (isset($options[$long_opts[2]])) {
        $file = fopen($options[$long_opts[2]], "w");
        if (!$file) {
            print_error_line("Cant open output file " . $options[$long_opts[2]] . ", exiting");
            exit(3);
        }
        $arguments["output"] = $file;
    } else {
        $arguments["output"] = STDOUT;
    }

    if(isset($options[$long_opts[8]]) || isset($options["r"]))
        $arguments["just_rules"] = true;
    else
        $arguments["just_rules"] = false;


    $no_eps = false;
    $determinizaton = false;
    $wsfa = false;

    if (isset($options[$long_opts[3]]) || isset($options["e"])) {
        $no_eps = true;
    }

    if (isset($options[$long_opts[4]]) || isset($options["d"])) {
        $determinizaton = true;
    }

    if (isset($options[$long_opts[6]]))
        $wsfa = true;

    if (isset($options[$long_opts[5]]) || isset($options["i"])) {
        $arguments["case_in"] = true;
    } else {
        $arguments["case_in"] = false;
    }

    if (isset($options[$long_opts[7]])) {
        $arguments["string"] = $options[$long_opts[7]];
        $check_string = true;
    } else
        $check_string = false;

    if (($determinizaton && $no_eps) || ($determinizaton && $wsfa) || ($no_eps && $wsfa) || ($check_string && $wsfa) || ($check_string && $determinizaton) || ($check_string && $no_eps)) {
        print_error_line("Arguments determinization and no-epsilon-rules cant be combined,
        please choose just one, of them");
        exit(1);
    } else if (!$determinizaton && !$no_eps && !$wsfa && !$check_string) {
        $arguments["op"] = Operation::validation;
    } else if ($determinizaton) {
        $arguments["op"] = Operation::determinization;
    } else if ($no_eps)
        $arguments["op"] = Operation::no_eps;
    else if ($wsfa) {
        $arguments["op"] = Operation::wsfa;
    } else if ($check_string)
        $arguments["op"] = Operation::check_string;
    else {
        print_error_line("Internal error in args, this option should never happen");
        exit(666);
    }
}

function parse_input_file_to_chars($file,$name){
    //$filecontent = file_get_contents('input/input2.txt');
    //$filecontent = utf8_encode($filecontent);
    //$filecontent = utf8_decode($filecontent);

    $filecontent = fread($file,filesize($name));

    $l = 0;
    $res = preg_split('/(.{'.$l.'})/us', $filecontent, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    return $res;
}


function print_help()
{
    echo "Help me, please...";
    exit(0);
}

class Operation
{
    const validation = 0;
    const no_eps = 1;
    const determinization = 2;
    const wsfa = 3;
    const check_string = 4;
}/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 11:45
 */

function print_info_line($msg){
    global $debug;
    if($debug)
        echo "INFO: ".$msg.PHP_EOL;
}

function print_error_line($msg){
    file_put_contents('php://stderr',"ERROR: ".$msg.PHP_EOL);
}

function print_var($var){
    global $debug;
    if($debug)
        print_r($var);
}

/**
 * Created by PhpStorm.
 * User: david
 * Date: 18.2.16
 * Time: 9:05
 */
class SuperState
{
    private $states;

    /**
     * SuperState constructor.
     * @param $states
     */
    public function __construct($states)
    {
        if(!is_array($states)){
            print_error_line("Internal error, states passed to SuperState constructor should be an array");
            $states = [$states];
        }
        sort($states);
        $this->states = $states;
    }

    public function get_super_state_id(){
        $result = "";
        foreach($this->states as $state){
            $result .= $state."_";
        }
        return substr($result,0,-1);
    }

    function __toString()
    {
        $result = "";
        foreach($this->states as $state){
            $result .= $state."_";
        }
        return substr($result,0,-1);
    }

    public function get_iterator(){
        return new ArrayIterator($this->states);
    }

    /**
     * @return array
     */
    public function getStates()
    {
        return $this->states;
    }


}

/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 11:12
 */
class Rule
{
    private $left_state;
    private $character;
    private $right_state;

    /**
     * Rule constructor.
     * @param $left_state
     * @param $character
     * @param $right_state
     */
    public function __construct($left_state, $character, $right_state)
    {
        $this->left_state = $left_state;
        $this->character = $character;
        $this->right_state = $right_state;
    }

    public function check_rule(FI $FI)
    {
        $states = $FI->getStates();

        if (!in_array($this->left_state, $states)) {
            print_error_line("Levy stav " . $this->left_state . " pravidla neni v mnozine stavu");
            return false;

        } else if (!in_array($this->right_state, $states)) {
            print_error_line("Pravy stav " . $this->right_state . " pravidla neni v mnozine stavu");
            return false;

        }
        $alphabet = $FI->getAlphabet();
        $alphabet[] = " "; // pridani epsilonu do abecedy
        if (!in_array($this->character, $alphabet)) {
            print_error_line("Znak pravidla '" . $this->character . "' neni v abecede");
            return false;
        }
        return true;
    }

    public function is_epsilon_rule()
    {
        return $this->character == " ";
    }

    function __toString()
    {
        return $this->left_state . $this->character . $this->right_state;
    }


    /**
     * @return mixed
     */
    public function getLeftState()
    {
        return $this->left_state;
    }

    /**
     * @return mixed
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @return mixed
     */
    public function getRightState()
    {
        return $this->right_state;
    }

    /**
     * @param mixed $left_state
     */
    public function setLeftState($left_state)
    {
        $this->left_state = $left_state;
    }

    /**
     * @param mixed $character
     */
    public function setCharacter($character)
    {
        $this->character = $character;
    }

    /**
     * @param mixed $right_state
     */
    public function setRightState($right_state)
    {
        $this->right_state = $right_state;
    }


}

class FI
{
    private $states;
    private $alphabet;
    private $rules;
    private $startState;
    private $finishStates;

    public static function createFromRules(Array $rules,Array $endStates){
        print_var($rules);
        print_var($endStates);

        $states = array();
        $alphabet = array();
        foreach($rules as $rule) {
            $states[] = $rule->getLeftState();
            $alphabet[] = $rule->getCharacter();
        }
        $startState = $rules[0]->getLeftState();

        return new FI($states,$alphabet,$rules,$startState,$endStates);
    }

    /**
     * FI constructor.
     * @param $states
     * @param $alphabet
     * @param $rules
     * @param $startState
     * @param $finishStates
     */
    public function __construct($states, $alphabet, $rules, $startState, $finishStates)
    {
        global $arguments;

        if ($arguments["case_in"]) {
            foreach ($states as &$state) {
                $state = strtolower($state);
            }

            foreach ($alphabet as &$symbol) {
                $symbol = strtolower($symbol);
            }

            foreach ($rules as &$rule) {
                $rule->setLeftState(strtolower($rule->getLeftState()));
                $rule->setCharacter(strtolower($rule->getCharacter()));
                $rule->setRightState(strtolower($rule->getRightState()));
            }

            $startState = strtolower($startState);

            foreach ($finishStates as &$f_state) {
                $f_state = strtolower($f_state);
            }
        }

        $states = array_unique($states);
        $states = array_values($states);

        $alphabet = array_unique($alphabet);
        $alphabet = array_values($alphabet);

        $rules = array_unique($rules);
        $rules = array_values($rules);

        $finishStates = array_unique($finishStates);
        $finishStates = array_values($finishStates);

        $startState = is_array($startState) ? $startState[0] : $startState;

        $this->states = $states;
        $this->alphabet = $alphabet;
        $this->rules = $rules;
        $this->startState = $startState;
        $this->finishStates = $finishStates;
    }

    public function check_fi()
    {
        global $debug;

        if ($debug)
            print_info_line("Started semantic analysis");

        if (empty($this->alphabet)) {
            print_error_line("Alphabet is empty");
            return false;
        }

        if (!in_array($this->startState, $this->states)) {
            print_error_line("Start state " . $this->startState . " not found in states set");
            return false;
        }

        foreach ($this->finishStates as $f_state) {
            if (!in_array($f_state, $this->states)) {
                print_error_line("Finish state " . $f_state . " not found in states set");
                return false;
            }
        }

        if (!$this->check_states())
            return false;

        if ($debug)
            print_info_line("Finished semantic analysis");

        return true;
    }

    public function remove_epsilon_rules()
    {
        $new_rules = array();

        foreach ($this->getStates() as $state) {
            $epsilon_uzaver = $this->get_epsilon_uzaver($state);

            foreach ($epsilon_uzaver as $eps_state) {
                $non_epsilon_rules = $this->get_non_epsilon_rules($eps_state);

                foreach ($non_epsilon_rules as $non_eps_rule) {
                    $new_rules[] = new Rule($state, $non_eps_rule->getCharacter(), $non_eps_rule->getRightState());
                }
            }
        }

        print_info_line("rules without epsilon rules == ");
        print_var($new_rules);

        $new_finish_states = array();

        foreach ($this->getStates() as $state) {
            $intersection = array_intersect($this->get_epsilon_uzaver($state), $this->getFinishStates());
            if (!empty($intersection))
                $new_finish_states[] = $state;
        }

        print_info_line("new finish states without epsilon");
        print_var($new_finish_states);

        $this->rules = $new_rules;
        $this->finishStates = $new_finish_states;
    }

    public function get_epsilon_uzaver($state)
    {
        if (!in_array($state, $this->states)) {
            print_error_line("internal error, epsilon uzaver called with illegal state " . $state);
            exit(666);
        }

        $epsilon_uzaver = [$state];

        $changed = true;
        while ($changed) {
            $changed = false;

            foreach ($epsilon_uzaver as $eps_state) {
                $rules = $this->get_epsilon_rules($eps_state);

                foreach ($rules as $rule) {
                    $right_state = $rule->getRightState();

                    if (!in_array($right_state, $epsilon_uzaver)) {
                        $epsilon_uzaver[] = $right_state;
                        $changed = true;
                    }
                }
            }
        }

        print_info_line("Epsilon uzaver stavu " . $state . " je: ");
        print_var($epsilon_uzaver);
        return $epsilon_uzaver;
    }

    private function get_epsilon_rules($state)
    {
        $result = array();

        foreach ($this->rules as $rule) {
            if ($rule->getLeftState() == $state and $rule->is_epsilon_rule())
                $result[] = $rule;
        }

        return $result;
    }

    private function get_non_epsilon_rules($state)
    {
        $result = array();

        foreach ($this->rules as $rule) {
            if ($rule->getLeftState() == $state and !$rule->is_epsilon_rule())
                $result[] = $rule;
        }

        return $result;
    }

    private function check_states()
    {
        foreach ($this->rules as $rule) {
            if (!$rule->check_rule($this))
                return false;
        }
        return true;
    }

    public function determinize()
    {
        global $debug;
        $this->remove_epsilon_rules();
        if($debug)
            $this->print_FI();

        print_info_line("-----------------------Determinization started---------------------------");

        $Sd = new SuperState([$this->getStartState()]);
        $Qnew = [new SuperState([$Sd])];
        $Rd = [];
        $Qd = [];
        $Fd = [];

        while(!empty($Qnew)){
            $Qcarka = array_shift($Qnew);
            $Qd[] = $Qcarka;
            print_info_line("New qcarka: ");
            print_var($Qcarka);
            print_info_line("new qd: ");
            print_var($Qd);

            foreach($this->getAlphabet() as $symbol){
                $Qcarkacarka = array();
                foreach($Qcarka->getStates() as $qcarkastate){
                    $specific_rules = $this->get_rules_with_left_state_and_symbol($qcarkastate,$symbol);
                    $specific_states = $this->get_right_states_from_array_of_rules($specific_rules);

                    $Qcarkacarka = array_merge($Qcarkacarka,$specific_states);
                }
                print_info_line("Qcarkacarka for symbol " . $symbol . ":");
                print_var($Qcarkacarka);

                $Qcarkacarka = array_unique($Qcarkacarka);
                $newState = new SuperState($Qcarkacarka);
                if(!empty($Qcarkacarka)){
                    $rule = new Rule($Qcarka,$symbol,$newState);
                    if(!in_array($rule,$Rd)) {
                        $Rd[] = $rule;

                        print_info_line("new rule: ");
                        print_var($rule);
                    }
                }
                if(!in_array($newState,$Qd) && !empty($Qcarkacarka)){
                    if(!in_array($newState,$Qnew)) {
                        $Qnew[] =  $newState;
                    }
                    print_info_line("Qnew for next iteration: ");
                    print_var($Qnew);
                    print_info_line("Current Qd :");
                    print_var($Qd);
                }
            }
            if(!empty(array_intersect($this->getFinishStates(),$Qcarka->getStates()))){
                $Fd[] = $Qcarka;
            }
            //fgetc(STDIN);
            print_info_line("------------------------------------------------------");
        }

        $this->states = $Qd;
        $this->rules = $Rd;
        $this->startState = $Sd;
        $this->finishStates = $Fd;

        print_info_line("-----------------------Determinization finished---------------------------");
    }

    private function get_right_states_from_array_of_rules($rules){
        $result = array();
        foreach($rules as $rule){
            $result[] = $rule->getRightState();
        }
        return $result;
    }

    private function get_rules_with_left_state_and_symbol($left_state,$symbol){
        $result  = array();
        foreach($this->getRules() as $rule){
            if($rule->getLeftState() == $left_state && $rule->getCharacter() == $symbol){
                $result[] = $rule;
            }
        }
        return $result;
    }

    public function print_FI()
    {
        global $arguments;

        print_info_line("printing current FI");
        print_info_line("-------------------");

        $result = "(\n{";
        $this->add_states_for_printing($result);
        $result .= "},\n";

        $result .= "{";
        $this->add_alphabet_for_printing($result);
        $result .= "},\n";

        $result .= "{\n";
        $this->add_rules_for_printing($result);
        $result .= "},\n";

        $result .= $this->getStartState() . ",\n";

        $result .= "{";
        $this->add_finish_states_for_printing($result);
        $result .= "}\n)";

        fprintf($arguments["output"], $result);

        print_info_line("--------end--------");
    }

    function add_states_for_printing(&$result)
    {
        $states = $this->getStates();
        usort($states, "state_cmp");
        //sort($states);
        $len = count($states);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $len - 1)
                $result .= $states[$i] . ", ";
            else
                $result .= $states[$i];
        }
    }

    function add_finish_states_for_printing(&$result)
    {
        $states = $this->getFinishStates();
        usort($states,"state_cmp");
        $len = count($states);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $len - 1)
                $result .= $states[$i] . ", ";
            else
                $result .= $states[$i];
        }
    }

    function add_alphabet_for_printing(&$result)
    {
        $alphabet = $this->getAlphabet();
        sort($alphabet);
        $len = count($alphabet);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $len - 1)
                $result .= '\'' . $alphabet[$i] . '\', ';
            else
                $result .= '\'' . $alphabet[$i] . '\'';
        }
    }

    function add_rules_for_printing(&$result)
    {
        $rules = $this->getRules();
        if(!empty($rules)) {
            usort($rules, "rule_cmp");
            foreach ($rules as $rule) {
                $result .= $rule->getLeftState() . " '" . $rule->getCharacter() . "' -> " . $rule->getRightState() . ",\n";
            }
            $result = substr($result, 0, -2);
            $result .= "\n";
        }
    }

    public function wsfa()
    {
        print_info_line("-------------------here----------------");
        $this->determinize();
        $this->compute_ending_states();
        $this->complete_rules();
    }

    private function complete_rules()
    {
        $name = "qFalse";
        $new_rules = array();

        foreach ($this->getStates() as $state) {
            foreach ($this->getAlphabet() as $symbol) {
                if (!$this->contains_rule_with_left($state, $symbol)) {
                    $new_rules[] = new Rule($state, $symbol, $name);
                }
            }
        }

        if (!empty($new_rules)) {

            if (!in_array($name, $this->getStates())) {
                $this->states [] = $name;
                foreach ($this->getAlphabet() as $symbol) {
                    $this->rules[] = new Rule($name, $symbol, $name);
                }
            }

            $this->rules = array_merge($this->rules, $new_rules);
        }
    }

    private function contains_rule_with_left($left_state, $symbol)
    {
        foreach ($this->getRules() as $rule) {
            if ($rule->getLeftState() == $left_state and $rule->getCharacter() == $symbol)
                return true;
        }
        return false;
    }

    private function compute_ending_states()
    {

        $good_states = array();
        $stack = $this->getFinishStates();

        while (!empty($stack)) {

            $state = $stack[array_rand($stack, 1)];
            $stack = array_diff($stack, [$state]);

            foreach ($this->get_all_left_states_from_right_state($state) as $state) {
                if (!in_array($state, $good_states)) {
                    $good_states [] = $state;
                    $stack[] = $state;
                }
            }
        }
        $this->states = $good_states;
        if (!in_array($this->startState, $good_states))
            $this->states[] = "qFalse";
    }

    private function get_all_left_states_from_right_state($state)
    {
        $result = array();

        foreach ($this->getRules() as $rule) {
            if ($rule->getRightState() == $state)
                $result[] = $rule->getLeftState();
        }

        return $result;
    }

    public function check_string($string)
    {
        $this->check_string_symbols($string);
        $this->determinize();
        $current_state = $this->getStartState();

        //$this->print_FI();

        $strlen = strlen($string);
        for ($i = 0; $i < $strlen; $i++, $next_state = $current_state) {
            $char = substr($string, $i, 1);
            $next_state = null;

            print_info_line("Checking char: " . $char);
            foreach ($this->get_rules_with_left_state($current_state) as $rule) {
                print_info_line("Checking rule :" . $rule);

                if ($rule->getCharacter() == $char)
                    $next_state = $rule->getRightState();
            }
            if ($next_state == null) {
                print_error_line("There is no way to go from state " . $current_state . " with symbol " . $char);
                echo "0";
                exit(0);
            }
        }
        print_info_line("The string was accepted");
        echo "1";
        exit(0);
    }

    private function check_string_symbols($string)
    {
        $strlen = strlen($string);
        for ($i = 0; $i < $strlen; $i++) {
            $char = substr($string, $i, 1);

            if (!in_array($char, $this->alphabet)) {
                print_error_line("Symbol " . $char . " is not in alphabet");
                exit(1);
            }
        }
    }

    private function get_rules_with_left_state($state)
    {
        $rules = array();
        foreach ($this->rules as $rule) {
            if ($rule->getLeftState() == $state)
                $rules[] = $rule;
        }
        return $rules;
    }

    /**
     * @return mixed
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @return mixed
     */
    public function getAlphabet()
    {
        return $this->alphabet;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function getStartState()
    {
        return $this->startState;
    }

    /**
     * @return mixed
     */
    public function getFinishStates()
    {
        return $this->finishStates;
    }


}

function rule_cmp(Rule $a,Rule $b){
    $res = state_cmp($a->getLeftState(),$b->getLeftState());
    if($res != 0)
        return $res;
    $res = strcmp($a->getCharacter(),$b->getCharacter());
    if($res != 0)
        return $res;
    return state_cmp($a->getRightState(),$b->getRightState());
}

function state_cmp($a, $b)
{
    if(is_string($a) && is_string($b))
        return strcmp($a,$b);
    elseif ($a instanceof SuperState && $b instanceof SuperState) {
        return state_cmp($a->getStates(), $b->getStates());
    } elseif($a instanceof SuperState)
        return state_cmp($a->getStates(),$b);
    elseif($b instanceof SuperState)
        return state_cmp($a,$b->getStates());

    $len = min(count($a),count($b));

    for($i = 0 ; $i < $len ; $i++){
        if($a[$i] > $b[$i]){
            return 1;
        } elseif($a[$i] < $b[$i]){
            return -1;
        }
    }
    if(count($a) > count($b))
        return 1;
    elseif(count($b) > count($a))
        return -1;
    else return 0;
}/**
 * Created by PhpStorm.
 * User: david
 * Date: 14.2.16
 * Time: 22:53
 */

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_regex_encoding('UTF-8');
header('Content-type: text/plain; charset=utf-8');







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
    exit(40);

if(!$FI->check_fi())
    exit(41);

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
    exit(666);
}

if($arguments["op"] != Operation::check_string)
    $FI->print_FI();
/*
if($arguments["input"] != STDIN)
    fclose($arguments["input"]);
*/

if($arguments["output"] != STDOUT)
    fclose($arguments["output"]);
