<?php

defined('DSMVC') OR die('No direct access allowed!');

class mydb extends db{ 

	private $pars;
	
	private static $instance = NULL;

	public function __construct($location = false){

		$loc = $location ? $location : DB;

		switch ($loc) {
			case 'local':
				$this->pars = array(
					'user' => 'root',
					'pwd' => 'maremma',
					'host' => '127.0.0.1',
					'db' => 'wavescores'
				);
			break;
			default:
				return 'impossible to connect';
			break;
		}
		$this->connect($this->pars);
	}

	public static function getInstance(){
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}	
}