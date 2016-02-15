<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14.2.16
 * Time: 22:53
 */

include 'functions.php';
include 'classes.php';
include 'algorithms.php';
include 'parser.php';

$var = "({A,B,C},{'x','y','z'},{A'x'->B,B'y'->C},A,{C})";

parse_input($var);
