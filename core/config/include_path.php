<?php
$include_path = array(
	'PATH_CONTROLLERS'	=>	realpath(PATH_APP.'controllers'.DS),
	'PATH_MODELS'		=>	realpath(PATH_APP.'models'.DS),
	'PATH_VIEWS'		=>	realpath(PATH_APP.'views'.DS),
    'LIB'		        =>	realpath(PATH_APP.'lib'.DS),
	'CORE'				=>	realpath(PATH_CORE),
	'CORE_LIB'			=>	realpath(PATH_CORE.'lib'.DS),
	'CORE_EXCEPTIONS'	=>	realpath(PATH_CORE.'lib'.DS.'exceptions'.DS)
);