<?php

//locker
defined('DSMVC') || define('DSMVC', 1);

defined('APP_NAME') || define('APP_NAME', 'dsMVC');

// use a virtual host or a localhost folder ?
defined('ON_DOMAIN') || define('ON_DOMAIN', true);

// online site OR go hard to offline page ? 
defined('ONLINE') || define('ONLINE', true);

defined('DB') || define('DB', 'local');
	


defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('US') || define('US', '/');
defined('DEFAULT_CONTROLLER') || define('DEFAULT_CONTROLLER', 'index');
defined('DEFAULT_ACTION') || define('DEFAULT_ACTION', 'index');
defined('URL_EXT') || define('URL_EXT', "/\.html|\.php/");
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

defined('PATH_CACHE') || define('PATH_CACHE', PATH_ROOT.'cache'.DS);
defined('CACHE_ACTIVE') || define('CACHE_ACTIVE', false);


defined('AUTO_PARSE') || define('AUTO_PARSE', true);
defined('PROTOCOL') || define('PROTOCOL', 'http' . (((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on')) ? 's' : ''));
defined('LOCAL_FOLDER') || define('LOCAL_FOLDER', ON_DOMAIN ? '' : basename(dirname(dirname(dirname(__FILE__)))));
defined('BASE_FOLDER') || define('BASE_FOLDER', ON_DOMAIN ? '' :  LOCAL_FOLDER . US );
defined('URL_ROOT') || define('URL_ROOT', PROTOCOL . ':' . US . US . $SERVER_NAME . US);
defined('URL_BASE') || define('URL_BASE', URL_ROOT . BASE_FOLDER);
defined('URL_COMPLETE') || define('URL_COMPLETE', $REQUEST_URI);
defined('URL') || define('URL', parse_url($REQUEST_URI)['path']);

defined('PATH_JS') || define('PATH_JS', PATH_ROOT.'htdocs'.DS.'js'.DS);
defined('PATH_CSS') || define('PATH_CSS', PATH_ROOT.'htdocs'.DS.'css'.DS);


defined('DEFAULT_LANG') || define('DEFAULT_LANG', 'en');
//  activate to pick up labels and get files for lang
defined('CUMULATE_LANG') || define('CUMULATE_LANG', true);

defined('ERROR_LEVEL') || define('ERROR_LEVEL', 1/* 2<<13 -1 */ );
defined('STACKTRACE_ENABLED') || define('STACKTRACE_ENABLED', true);


