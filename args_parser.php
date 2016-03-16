<?php
#Modul pro parsovani vstupnich argumentu
#DKA:xkozak15

/**
 * Funkce zpracuje vstupni argumenty, overi jejich platnost a nastavi globalni promenne,
 * ktere pote ridi beh skriptu
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

    // overeni vstupniho a vystupniho souboru, pripadne nastaveni STDIN a STDOUT
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
        exit(101);
    }
}

/**
 * Funkce nacte data ze zadaneho souboru, zmeni kodovani na utf-8 a ulozi je dole
 * @param $file soubor, ze ktereho se cte
 * @param $name jmeno souboru
 * @return array data souboru po uprave kodovani
 */
function parse_input_file_to_chars($file,$name){
    $filecontent = fread($file,filesize($name));

    $l = 0;
    // tento krok zajisti upravu kodovani
    $res = preg_split('/(.{'.$l.'})/us', $filecontent, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    return $res;
}

/**
 * Funkce vypise napovedu
 */
function print_help()
{
    echo "Skript v PHP5 pro determininizaci konecneho automatu\n";
    echo "Projektu do IPP 2015/2016\n";
    echo "Zakladni ovladani: \n";
    echo "\t-d,--determinization: determinizace\n";
    echo "\t-e:--no-epsilon_rules: odstraneni epsilon prechodu\n";
    echo "\t-i,--case-insensitive: skript nebude rozlisovat velka a mala pismena\n";
    echo "\t--wsfa: prevedeni automatu na dobre specifikovany\n";
    echo "\t\t Bez zvoleni prepinace skript pouze vypise nacteny automat v univerzalni vystupni forme\n";
    echo "\t-r,--rules-only: automat na vstupu je specifikovany ve zkracene forme(pouze pravidla\n";
    echo "\t--input: vstupni soubor, implicitne stdin\n";
    echo "\t--output: vystupni soubor, implicitne stdout\n";
    echo "Podoba vstupu:\n";
    echo "\t{{stavy},{abeceda},{pravidla},pocatecni_stav{koncove stavy}}\n";
    echo "\t\tstavy - identifikatory z C, nesmi zacinat ani koncit podtrzitkem\n";
    echo "\t\tabeceda - symboly v apostrofech,'X','{','V' ,...epsilon_prechod:='' (prazdne apostrofy)\n";
    echo "\t\tpravidla jsou ve forme: stav symbol -> stav\n";
    echo "\t\tZnak # pro komentar, platnost do konce radku\n";
    echo "\t\tWhitespace vcetne znaku konce radku jsou vynechavany\n";
    exit(0);
}

/**
 * Vyctovy typ pro jednotlive operace, ktere skript muze provest
 */
class Operation
{
    const validation = 0;
    const no_eps = 1;
    const determinization = 2;
    const wsfa = 3;
    const check_string = 4;
}