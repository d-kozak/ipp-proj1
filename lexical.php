<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 20:10
 */

$last_char = null;

function get_next_token()
{
    global $file, $file_name, $last_char, $buffer_id;
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
                    $token["id"] = Tokens::dash;
                    return $token;
                } elseif ($next_char == ">") {
                    $token["id"] = Tokens::operator_bigger;
                    return $token;
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
                    mindfuck($state,$next_char);
                }
                break;

            case LexicalDFISTates::symbol_wait_for_double_quote_2:
                if ($next_char == '\'') {
                    $token["id"] = Tokens::fi_symbol;
                    return $token;
                } else {
                    mindfuck($state,$next_char);
                }
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