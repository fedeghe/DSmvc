<?php
class Dispatcher{

	public static function dispatch(){


		Cache::check();

		$parse = parse_url(URL_COMPLETE);

		$url = explode('/', trim($parse['path'], '/'));

		!ON_DOMAIN && array_shift($url);

		// get controller name		
		if (!empty($url[0])){
			$controller = $url[0];
			array_shift($url);
		
			if (!empty($url[0])){
				$action = $url[0];
				array_shift($url);
			} else {
				$action = DEFAULT_ACTION;
			}

		} else {
			$controller = DEFAULT_CONTROLLER;
			$action = DEFAULT_ACTION;
		}

		DSMVC::$controller = preg_replace(URL_EXT, '', $controller);
		DSMVC::$action = preg_replace(URL_EXT, '', $action);

		//  debug(DSMVC::$controller);
		// debug(DSMVC::$action);

		// create controller instance and call the specified action
		try {

			class_exists(DSMVC::$controller);
			
		}catch(Exception $e) {
			if (defined('CONTROLLER404')) {
				DSMVC::$controller = CONTROLLER404;
				DSMVC::$action = 'action_index';
			} else {
				die("Controller `$controller.php` not found");
			}
		}

		Request::handle(DSMVC::$controller, 'action_' . DSMVC::$action, $url);
		
    }
}// End Dispatcher class
