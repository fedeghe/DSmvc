<?php

class Cache{

	private static $cachedir = PATH_CACHE;
	private static $expire = 60;
	private static $instance = NULL;

	private static $ext = '.html';


	

	public static function check() {
		$id = md5(URL);
		if (self::valid($id)) {
			echo self::get($id);
			die();
		} else {
			Response::$id = $id;
		}
	}
	
	// write data to cache file given an ID
	public static function set($id, $data) {

		$file = self::$cachedir . $id . self::$ext;
		if (file_exists($file)) {

			unlink($file);
		}
		// write data to cache
		if (!file_put_contents($file, serialize($data))) {

			throw new Exception('Error writing data to cache file.');
		}
	}
	
	// read data from cache file given an ID
	public static function get($id) {
		
		$file = glob(self::$cachedir . $id . self::$ext);
		if (!count($file)) {
			return false;
		}
        $file = array_shift($file);
		if (!$data = file_get_contents($file)) {

			throw new Exception('Error reading data from cache file.');
		}
		return unserialize($data);
	}
	
	// check if the cache file is valid or not
	public static function valid($id) {

		$file = glob(self::$cachedir . $id . self::$ext);
		if (!count($file)) {
			return false;
		}
        $file = array_shift($file);
		return (bool)(time() - filemtime($file) <= self::$expire);
	}
}// End Cache class