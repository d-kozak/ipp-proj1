<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:10
 */

$last_char = null;
$next_token = null;

function put_token_back($token){
    global $next_token;
    if($next_token != null){
        print_error_line("next token variable should be always null when calling put_token_back");
        exit(666);
    }
    $next_token = $token;
}

function print_token($tkn){
    global $buffer_id;
    echo "\tTOKEN:\n\t\ttype : " .$tkn["id"] . " \n\t\tvalue: " . $tkn[$buffer_id] . PHP_EOL;
}

function get_and_print_next_token(){
    $token = get_next_token();
    print_token($token);
    return $token;
}

function get_next_token()
{
    global $file, $file_name, $last_char, $buffer_id,$next_token,$debug_lexical;

    if($next_token != null){
        $tmp = $next_token;
        $next_token = null;
        return $tmp;
    }

    if ($file == null) {
        $file = fopen($file_name, "r") or die("Cant open input file " . $file_name);
    }
    $token = array();

    $token[$buffer_id] = "";
    $state = LexicalDFISTates::start;

    while (!feof($file)) {

        if ($last_char != null) {
            $next_char = $last_char;
            $last_char = null;
        } else
            $next_char = fgetc($file);

        if($next_char == null)
            break;

        if($debug_lexical)
            if($state != LexicalDFISTates::comment)
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
                } elseif($next_char == "#"){
                    $state = LexicalDFISTates::comment;
                }
                elseif ($next_char == '\'')
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

                }else
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
                    $state = LexicalDFISTates::symbol_wait_for_quote;
                }
                break;
            case LexicalDFISTates::symbol_wait_for_quote:
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
                    $token[$buffer_id] = '\'';
                    return $token;
                } else {
                    mindfuck($state,$next_char);
                }
                break;

            case LexicalDFISTates::dash:
                if($next_char == ">"){
                    $token["id"] = Tokens::arrow;
                    return $token;
                } else
                    mindfuck($state,$next_token);
                break;

            case LexicalDFISTates::comment:
                if($next_char == "\n")
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
    echo "value = " . intval($next_char) .PHP_EOL;
    exit(666);
}