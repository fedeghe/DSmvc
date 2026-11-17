<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
// mb_http_input('G');
mb_language('uni');
mb_regex_encoding('UTF-8');
ini_set('upload_max_filesize','32M');
ini_set("soap.wsdl_cache_enabled", 0);
ini_set('pcre.backtrack_limit', '4M' ); 

date_default_timezone_set(date_default_timezone_get());

// print_r($_GET, true);

// harden session cookie before starting the session
session_set_cookie_params(array(
    'lifetime' => 0,
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on'
));
session_start();
$REQUEST_URI = $_SERVER['REQUEST_URI'];
$SERVER_NAME = $_SERVER['SERVER_NAME'];
// defines
include(realpath(dirname(__FILE__).'/../../config.php'));
include('define.php');


// lang set
include('langset.php');
include_once('error_handling.php');
include(realpath(dirname(__FILE__).'/../Autoloader.php'));
Csrf::init();

function debug($a) {
    echo '<pre>'. print_R($a, true) . '</pre>';
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
// https://stackoverflow.com/questions/1162491/alternative-to-mysql-real-escape-string-without-connecting-to-db
function mres($value){
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
    return str_replace($search, $replace, $value);
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
                $value->{$key} = deep_slashes( $data );
            }
        break;
        default:
            $value = mres( $value );
        break;
    }
    return $value;
}
/*
 * Deep clean for POST, GET, COOKIE, REQUEST: remove any parser marker
 * so user-supplied values can never become interpretable template markup
 * ([D[]D], [S[]S], [P[]P], [G[]G], [V[]V], [[...]], {{...}}, {J{}J}, {C{}C}, [T[]T], [t[]t])
 */
function strip_defined_ph($value) {
    return preg_replace(
        array(
            '!\[(?:D|S|P|G|V)\[[^\[\]]*\](?:D|S|P|G|V)\]!Uis',
            '!\[(?:T|t)\[[^\[\]]*\](?:T|t)\]!Uis',
            '!\[\[[^\[\]]*\]\]!Uis',
            '!\{\{[^{}]*\}\}!Uis',
            '!\{[JC]\{[^{}]*\}[JC]\}!Uis'
        ),
        '',
        $value
    );
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

if (preg_match('#^https?://localhost#', URL_ROOT) && !!ON_DOMAIN) {
    die('IT seems like ON_DOMAIN parameter should be set to TRUE in '. __FILE__);
}
if (!preg_match('/^http:\/\/localhost/', URL_ROOT) && !ON_DOMAIN) {
    die('IT seems like ON_DOMAIN parameter should be set to FALSE in '. __FILE__);
}

if (!ONLINE) {
    header("Location:/offline.php");
    exit;
}



