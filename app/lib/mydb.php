<?php

defined('DSMVC') OR die('No direct access allowed!');

class mydb extends db{ 
	private $pars;
	public function __construct($location = false){
		$loc = $location ? $location : DB;
		switch ($loc) {
			case 'local':
				$this->pars = array(
					'user' => DB_USER,
					'pwd' => DB_PWD,
					'host' => DB_HOST,
					'db' => DB_NAME,
					'port' => DB_PORT
				);
			break;
			default:
				return 'impossible to connect';
			break;
		}
		$this->connect($this->pars);
	}
}
