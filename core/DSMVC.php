<?php
defined('DSMVC') || die('No direct access allowed');

class DSMVC {
    public static $controller;

    public static $action;

    public static $ttr;

    public static $errors;

    private static $instance;

	private function __construct() {}

	public static function getInstance () {
		if(!isSet(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function getView($name) {
		return new View($name);
	}
}

