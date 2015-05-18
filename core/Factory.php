<?php 

//
//  system/core/Factory.class.php
//  DSmaheorana
//
//  Created by Federico Ghedina on 07/11/10.
//  Copyright 2010 Federico Ghedina. All rights reserved.
//

/**
 * Factory class for classes without parameters
 */
class Factory{

	private static $instances=array();

	/**
	 *	Return an object instance from the instances pool
	 * 
	 * @param <type> $name
	 * @param <type> $force forse creation(pool indepentent)
	 * @return <type>
	 *
	 */
	public static function get($name, $force=false){
		if(!array_key_exists($name, self::$instances) || $force){
			self::$instances[$name] = new $name();
		}
		return self::$instances[$name];
	}


	/**
	 * Return a View Object
	 *
	 * @param string $name
	 * @return object
	 */
	public static function getView($name){
		if(!array_key_exists('view_'.$name, self::$instances)){
			self::$instances['view_'.$name] = new View($name);
		}
		return self::$instances['view_'.$name];
	}



	/**
	 *returns a parser object
	 *
	 * @param string $content to be parsed
	 * @param array $vars associative array to be extracted from extract()
	 * @return object parser instance
	 */
	public static function getParser($content, $vars){
		if(!array_key_exists('tools_parser', self::$instances)){
			return self::$instances['tools_parser'] = new Parser($content, $vars);
		}else{
			self::$instances['tools_parser']->set_content($content);
			self::$instances['tools_parser']->set_variables($vars);
			return self::$instances['tools_parser'];
		}
		
	}

	
	public static function getall(){
		return self::$instances;
	}

}
