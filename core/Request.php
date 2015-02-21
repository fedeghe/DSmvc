<?php
class Request{

	private static $_controllerInstance = array();

	private function __construct () {}
	
	public static function handle ($controller, $action, $arg = NULL) {

		$params = array();
		preg_match('/^action_(.*)/', $action, $matches);
		$actionName = $matches[1];
			
		if (!isset(self::$_controllerInstance[$controller])){
			if (!class_exists($controller)){
				throw new Exception('Call to invalid controller class.');
			}
			self::$_controllerInstance[$controller] = new $controller;
		}

		// bind variables
		if(is_array($arg) && count($arg)>0){

			for($i = 0; $i + 1 < count($arg); $i += 2){
				$params[$arg[$i]] = $arg[$i + 1];
			}
			
			
		}
		//add GET
		foreach ($_GET as $k => $v) {
			if(!empty($v)) $params[$k] = $v;
		}

		//add to the view
		if(count($params)) self::$_controllerInstance[$controller]->_add_vars($params,true);

		

		// call before
		self::$_controllerInstance[$controller]->before();

		if (method_exists(self::$_controllerInstance[$controller], 'before_' . $actionName)) {

			call_user_func_array(array(self::$_controllerInstance[$controller], 'before_'.$actionName), $params);
		}
		
		//action existance is managed by controller protected __call
		self::$_controllerInstance[$controller]->$action($params);


		if (method_exists(self::$_controllerInstance[$controller], 'after_' . $actionName)) {

			call_user_func_array(array(self::$_controllerInstance[$controller], 'after_'.$actionName), $params);
		}

		// call after
		self::$_controllerInstance[$controller]->after();
		return true;
	}

}//End Request class