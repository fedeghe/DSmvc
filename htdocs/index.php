<?php

require_once(realpath(dirname(__FILE__).'/../core/config/bootstrap.php'));
// handle request and dispatch it to the appropriate controller
try{
	Dispatcher::dispatch();
}
// catch exceptions
catch (ClassNotFoundException $e){
	echo $e->getMessage();
	exit();
}
catch (Exception $e){
	echo $e->getMessage();
	exit();
}
// End front controller
