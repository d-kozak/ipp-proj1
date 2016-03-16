<?php
#Modul pro lexikalni analyzu
#DKA:xkozak15

/**
 * Pomocne promenne pro docasne uchovani informaci mezi jednotlivymi volani funkci
 */
$last_char = null;
$next_token = null;

/**
 * Funkce vrati token $token zpet, lexikalni analyza ho vrati pri dalsim volani get_next_token, pamet POUZE pro jednu polozku!
 * @param $token
 */
function put_token_back($token)
{
    global $next_token;
    if ($next_token != null) {
        print_error_line("next token variable should be always null when calling put_token_back");
        exit(10);
    }
    $next_token = $token;
}

/**
 * Pomocna debugovaci funkce, vypise obsah tokenu $tkn
 * @param $tkn
 */
function print_token($tkn)
{
    global $buffer_id;
    print_info_line("\tTOKEN:\n\t\ttype : " . $tkn["id"] . " \n\t\tvalue: " . $tkn[$buffer_id] . PHP_EOL);
}

/**
 * Funkce vrati token a vypise jeho obsah na stdout(pokud jsou povoleny debugovaci informace)
 * @return $token
 */
function get_and_print_next_token()
{
    $token = get_next_token();
    print_token($token);
    return $token;
}

/**
 * Funkce precte jeden znak z nactenych znaku(jsou predem cachovany
 * @see args_parser
 * @return mixed
 */
function read_one_char()
{
    global $arguments;
    //print_r($arguments["input"]);
    $char = array_shift($arguments["input"]);

    //print_r($arguments["input"]);
    //print_r($char);
    return $char;
}

/**
 * Hlavni funkce lexikalni analyzy, cte znak po znaku data ze vstupu a rozpoznava tokeny
 * vyuziti konecneho automatu viz dokumentace
 * @return $token token
 */
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
                        err_msg($state, $next_char);;
                } elseif ($next_char == '\'')
                    $state = LexicalDFISTates::symbol_1;
                elseif (ctype_alnum($next_char)) {
                    $token[$buffer_id] .= $next_char;
                    $state = LexicalDFISTates::state_1;
                } elseif (ctype_space($next_char)) {
                    continue;
                } else
                    err_msg($state, $next_char);
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
                    err_msg($state, $next_char);
                break;

            case LexicalDFISTates::state_2:
                if (ctype_alpha($next_char)) {
                    $token[$buffer_id] .= $next_char;
                    $state = LexicalDFISTates::state_1;
                } else if ($next_char == "_") {
                    $token[$buffer_id] .= $next_char;
                } else
                    err_msg($state, $next_char);
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
                    err_msg($state, $next_char);
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
                    err_msg($state, $next_char);
                }
                break;

            case LexicalDFISTates::dash:
                if ($next_char == ">") {
                    $token["id"] = Tokens::arrow;
                    return $token;
                } else
                    err_msg($state, $next_token);
                break;

            case LexicalDFISTates::comment:
                if ($next_char == "\n")
                    $state = LexicalDFISTates::start;
                break;

            default:
                print_error_line("Default state in get next token Fi");
                exit(105);

        }
    }
    return null;
}

/**
 * Funkce vypise, kde doslo k chybovemu stavu, vyuziti hlavne pri debugovani a dohledavani, kde se lexikalni chyba nachazi
 * @param $state
 * @param $next_char
 */
function err_msg($state, $next_char)
{
    print_error_line("bad char " . $next_char . " in state " . $state . " in get next token");
    echo "value = " . intval($next_char) . PHP_EOL;
    exit(40);
}