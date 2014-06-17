<?php

class Request{

	private static $_controllerInstance = array();

	private function __construct(){}
	
	public static function handle($controller, $method, $arg = NULL){

		//echo $controller;
		
		//print_r($arg);
		
		$params = array();
			
		if (!isset(self::$_controllerInstance[$controller])){
			
			if (!class_exists($controller)){
				throw new Exception('Call to invalid controller class.');
			}

			self::$_controllerInstance[$controller] = new $controller;
			
		}
		
		//call before
		self::$_controllerInstance[$controller]->before();

		
		// bind variables
		if(is_array($arg) && count($arg)>0){
			
			for($i = 0; $i+1<count($arg); $i+=2){
				$params[$arg[$i]] = $arg[$i+1];
			}
			//add to the view
			self::$_controllerInstance[$controller]->_add_vars($params,true);
		}
		
		//method existance is managed by controller protected __call
		self::$_controllerInstance[$controller]->$method($params);

		self::$_controllerInstance[$controller]->after();
		return true;
	}

}//End Request class