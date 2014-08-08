<?php
class Dispatcher{
	
	public static function dispatch(){

		$url = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
		
		!ONLINE && array_shift($url);

		// get controller name
		$controller = !empty($url[0]) ? $url[0]  : DEFAULT_CONTROLLER;

		// get action name of controller
		$action = 'action_' . (!empty($url[1]) ? $url[1] : DEFAULT_ACTION);

		//echo $controller;

		// get argument passed in to the action
		array_shift($url);
		array_shift($url);

		$arg = $url;

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
		Request::handle($controller, $action, $arg);
    }
}// End Dispatcher class
