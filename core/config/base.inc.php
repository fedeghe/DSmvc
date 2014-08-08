<?php

session_start();

function debug($a) {
    echo '<pre>'. print_R($a, true) . '</pre>';
}

// use a virtual host or a localhost folder ?
defined('ONLINE') || define('ONLINE', true);

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('US') || define('US', '/');
defined('DEFAULT_CONTROLLER') || define('DEFAULT_CONTROLLER', 'index');
defined('DEFAULT_ACTION') || define('DEFAULT_ACTION', 'index');
defined('CONTROLLER404') || define('CONTROLLER404', 'ctrl404');


defined('PATH_ROOT') || define('PATH_ROOT', realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS).DS);
defined('PATH_VIEW') || define('PATH_VIEW', PATH_ROOT.'app'.DS.'views'.DS);
defined('PATH_MODEL') || define('PATH_MODEL', PATH_ROOT.'app'.DS.'models'.DS);
defined('PATH_CONTROLLER') || define('PATH_CONTROLLER', PATH_ROOT.'app'.DS.'controllers'.DS);
defined('PATH_CONFIG') || define('PATH_CONFIG', PATH_ROOT.'core'.DS.'config'.DS);
defined('PATH_APP') || define('PATH_APP', PATH_ROOT.'app'.DS);
defined('PATH_CORE') || define('PATH_CORE', PATH_ROOT.'core'.DS);
defined('PATH_TRANSLATIONS') || define('PATH_TRANSLATIONS', PATH_ROOT.'app'.DS.'i18n'.DS);
defined('PATH_CHU') || define('PATH_CHU', PATH_APP.'chunks'.DS);
defined('PATH_CHU_SIS') || define('PATH_CHU_SIS', PATH_CORE.'lib'.DS.'chunks'.DS);
defined('PATH_SNI') || define('PATH_SNI', PATH_APP.'snippets'.DS);
defined('PATH_SNI_SIS') || define('PATH_SNI_SIS', PATH_CORE.'lib'.DS.'snippets'.DS);
defined('AUTO_PARSE') || define('AUTO_PARSE', true);
defined('PROTOCOL') || define('PROTOCOL', 'http' . (((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on')) ? 's' : ''));
defined('LOCAL_FOLDER') || define('LOCAL_FOLDER', ONLINE ? '' : basename(dirname(dirname(  dirname(__FILE__) ))));
defined('BASE_FOLDER') || define('BASE_FOLDER', ONLINE ? '' :  LOCAL_FOLDER . US );
defined('URL_ROOT') || define('URL_ROOT', PROTOCOL . ':' . US . US . $_SERVER['SERVER_NAME'] . US);
defined('URL_BASE') || define('URL_BASE', URL_ROOT . BASE_FOLDER);
defined('DEFAULT_LANG') || define('DEFAULT_LANG', 'en');
//  activate to pick up labels and get files for lang
defined('CUMULATE_LANG') || define('CUMULATE_LANG', false);
$_SESSION['lang'] = DEFAULT_LANG;


if (preg_match('/^http:\/\/localhost/', URL_ROOT) && !!ONLINE) {
    die('IT seems like ONLINE parameter should be set to TRUE in '. __FILE__);
}
if (!preg_match('/^http:\/\/localhost/', URL_ROOT) && !ONLINE) {
    die('IT seems like ONLINE parameter should be set to FALSE in '. __FILE__);
}




/*   
 * 
 *  Pulisce POST, GET, COOKIE, REQUEST profondamente (slashando tutto il necessario)
 * 
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
        default:
            $value = @mysql_real_escape_string( $value );
            //stripslashes($value);
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



$_POST      = array_map( 'strip_defined_ph', $_POST );
$_GET       = array_map( 'strip_defined_ph', $_GET );
$_COOKIE    = array_map( 'strip_defined_ph', $_COOKIE );
$_REQUEST   = array_map( 'strip_defined_ph', $_REQUEST );








/*
debug(array(
    'ONLINE' => ONLINE,
    'PATH_ROOT' => PATH_ROOT,
    'PATH_VIEW' => PATH_VIEW,
    'PATH_MODEL' => PATH_MODEL,
    'PATH_CONTROLLER' => PATH_CONTROLLER,
    'PATH_CONFIG' => PATH_CONFIG,
    'PATH_APP' => PATH_APP,
    'PATH_CORE' => PATH_CORE,
    'PATH_CHU' => PATH_CHU,
    'PATH_CHU_SIS' => PATH_CHU_SIS,
    'PATH_SNI' => PATH_SNI,
    'PATH_SNI_SIS' => PATH_SNI_SIS,
    'AUTO_PARSE' => AUTO_PARSE,
    'LOCAL_FOLDER' => LOCAL_FOLDER,
    'BASE_FOLDER' => BASE_FOLDER,
    'URL_ROOT' => URL_ROOT,
    'URL_BASE' => URL_BASE
));
*/