<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14.2.16
 * Time: 22:53
 */

include 'include.php';

$fi = syntactic_analysis();

if($file != null)
    fclose($file);
