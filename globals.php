<?php
#Modul pro globalni promenne a konstanty
#DKA:xkozak15

/**
 * Pole uchovavajici informace nutne pro beh programu
 * @see args_parser::parse_arguments
 */
$arguments = array();

$buffer_id = "buffer";

// flags pro debugvani
$debug = false;
$debug_lexical = $debug;

/**
 * Promenna obsahujici instanci konecny automat, tedy hlavni orientacni bod celeho skriptu
 */
$FI = null;

/**
 * vyctovy typ pro typy tokenu
 */
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

/*
 *  vyctovy typ pro stavy konencho automatu v lexikalni analyze
 */
abstract class LexicalDFISTates
{
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
    const symbol_1 = "symbol_1";
    const symbol_wait_for_quote = "symbol_wait_for_quote";
    const symbol_wait_for_double_quote = "symbol_wait_for_double_quote";
    const symbol_wait_for_double_quote_2 = "symbol_wait_for_double_quote 2";

    const state_1 = "state_1";
    const state_2 = "state_2";

    const dash = "dash";

    const comment = "comment";
}
