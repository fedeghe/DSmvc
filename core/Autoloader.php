<?php

//include_path file
include(PATH_CONFIG.'include_path.php');

//set all include_path
include(PATH_CONFIG.'set_include_path.php');

// define custom ClassNotFoundException exception class
class ClassNotFoundException extends Exception{}

class Autoloader {
	public static function load($classname) {
		
        include $classname.'.php';
        
		if (!class_exists($classname, FALSE)){
			throw new ClassNotFoundException('Class ' . $classname . ' NOT found.');
		}
		unset($classname);
	}
}

error_reporting(8191);


// specify parameters for autoloading classes
// spl_autoload_register(NULL, FALSE);

spl_autoload_extensions('.php');

spl_autoload_register(__NAMESPACE__ .'\Autoloader::load');




