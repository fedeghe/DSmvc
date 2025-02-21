<?php
defined('DSMVC') || die('No direct access allowed');

class auth {
	

	public static function destroy(){
		session_destroy();
		unset($_POST);
	}
	public static function gout($qs = ''){
		self::destroy();
		header('Location: '.URL_BASE.$qs);
	}	

	
	public static function can_enter(){
		return false;
	}
}