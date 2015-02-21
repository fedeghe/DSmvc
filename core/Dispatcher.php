<?php
class Dispatcher{

	public static function dispatch(){

		$parse = parse_url($_SERVER['REQUEST_URI']);

		$url = explode('/', trim($parse['path'], '/'));

		!ONLINE && array_shift($url);

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

		// get action name of controller
		$action = 'action_' . $action;

		// create controller instance and call the specified action
		try {

			class_exists($controller);
			
		}catch(Exception $e) {
			if (defined('CONTROLLER404')) {
				$controller = CONTROLLER404;
				$action = 'action_index';
			} else {
				die("Controller `$controller.php` not found");
			}
		}
		/*
		debug(array(
			$controller,
			$action
		));
		*/
		Request::handle($controller, $action, $url);
    }
}// End Dispatcher class
