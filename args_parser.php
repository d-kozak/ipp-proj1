<?php
/**
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
        "analyze-string:"
    );

    $short_opts = "edi"; // e=no epsilon d=determinization  i=case insensitive

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
}