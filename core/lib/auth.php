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
		exit;
	}	

	
	public static function can_enter(){
		return false;
	}

	/**
	 * call after setting $_SESSION['user'] in a login handler:
	 * rotates the session id to prevent session fixation
	 */
	public static function regenerate(){
		session_regenerate_id(true);
	}
}