<?php

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');

ini_set('upload_max_filesize','32M');
ini_set("soap.wsdl_cache_enabled", 0);
ini_set('pcre.backtrack_limit', '4M' ); 

date_default_timezone_set(date_default_timezone_get());

session_start();

$REQUEST_URI = $_SERVER['REQUEST_URI'];
$SERVER_NAME = $_SERVER['SERVER_NAME'];

// defines
include('define.php');


// lang set
include('langset.php');

function debug($a) {
    echo '<pre>'. print_R($a, true) . '</pre>';
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/*   
 *  Deep clean for POST, GET, COOKIE, REQUEST
 */
function deep_slashes($value) {
    switch(true){
        case is_array($value):
            $value = array_map('deep_slashes', $value);
        break;
        case is_object($value) :
            $vars = get_object_vars( $value );
            foreach ($vars as $key=>$data) {
                $value->{$key} = stripslashes_deep( $data );
            }
        break;
        // default:
        //     $value = mysqli::real_escape_string( $value );
        break;
    }
    return $value;
}
function strip_defined_ph($value) { 
    return preg_replace('!\[D\[([a-zA-Z0-9_\-]{1,})\]D\]!Uis', '', $value); 
}

if ( !function_exists('get_magic_quotes_gpc') ) {
    $_POST      = array_map( 'deep_slashes', $_POST );
    $_GET       = array_map( 'deep_slashes', $_GET );
    $_COOKIE    = array_map( 'deep_slashes', $_COOKIE );
    $_REQUEST   = array_map( 'deep_slashes', $_REQUEST );
}

// clean up
$_POST      = array_map( 'strip_defined_ph', $_POST );
$_GET       = array_map( 'strip_defined_ph', $_GET );
$_COOKIE    = array_map( 'strip_defined_ph', $_COOKIE );
$_REQUEST   = array_map( 'strip_defined_ph', $_REQUEST );

 

include_once('error_handling.php');

if (preg_match('/^http:\/\/localhost/', URL_ROOT) && !!ON_DOMAIN) {
    die('IT seems like ON_DOMAIN parameter should be set to TRUE in '. __FILE__);
}
if (!preg_match('/^http:\/\/localhost/', URL_ROOT) && !ON_DOMAIN) {
    die('IT seems like ON_DOMAIN parameter should be set to FALSE in '. __FILE__);
}

if (!ONLINE) {
    header("Location:/offline.php");
}

include(realpath(dirname(__FILE__).'/../Autoloader.php'));

