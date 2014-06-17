<?php
class Dispatcher{
	
	public static function dispatch(){

		$url = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
		
		!ONLINE && array_shift($url);

		// get controller name
		$controller = !empty($url[0]) ? $url[0]  : 'welcome';

		// get method name of controller
		$method = 'action_' . (!empty($url[1]) ? $url[1] : 'index');

		// get argument passed in to the method
		array_shift($url);
		array_shift($url);

		$arg = $url;

		// create controller instance and call the specified method
		class_exists($controller) OR die("Controller `$controller.php` NOT FOUND");
		/*
		debug(array(
			$controller,
			$method
		));
*/
		//die();
		Request::handle($controller, $method, $arg);
    }
}// End Dispatcher class
