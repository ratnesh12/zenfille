#!/usr/bin/php
<?php

define('CMD' , 1);

/* limits*/
set_time_limit(0);
ini_set('memory_limit', '4000000M');

/* browser checking */
if (isset($_SERVER['HTTP_HOST'])) die('Permission denied.');

/*  setting parameters*/

unset($argv[0]); /* unset 1 parameter */
$_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = '/' . implode('/', $argv) . '/';

$third = explode('/' , $argv[2]);

if(!empty($third)){

    switch($third[0]) {
        case 'dev':
              $_SERVER['HTTP_HOST'] = 'dev.zenfile.com';
        break;
        case 'live':
              $_SERVER['HTTP_HOST'] = 'zenfile.com';
        break;
    }
}

/* running framework */
include(dirname(__FILE__) . '/index.php');