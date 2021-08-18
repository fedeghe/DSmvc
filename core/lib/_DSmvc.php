<?php
class DSmvc {
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