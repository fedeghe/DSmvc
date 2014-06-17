<?php

class Cache{

	private $cachedir = 'cache/';
	private $expire = 60;
	private static $instance = NULL;
	
	// get Singleton instance of Cache class
	public static function getInstance($cachedir = '') {

		if (self::$instance === NULL) {
			self::$instance = new self($cachedir);
		}
		return self::$instance;
	}
	
	// constructor
	public function __construct($cachedir = ''){

		if ($cachedir !== '') {

			if (!is_dir($cachedir) or !is_writable($cachedir)) {

				throw new Exception('Cache directory must be a valid writeable directory.');	
			}
			$this->cachedir = $cachedir;
		}
	}
	
	// write data to cache file given an ID
	public function set($id, $data) {

		$file = $this->cachedir . $id;
		if (file_exists($file)) {

			unlink($file);
		}
		// write data to cache
		if (!file_put_contents($file, serialize($data))) {

			throw new Exception('Error writing data to cache file.');
		}
	}
	
	// read data from cache file given an ID
	public function get($id) {
		
		$file = glob($this->cachedir . $id);
        $file = array_shift($file);
		if (!$data = file_get_contents($file)) {

			throw new Exception('Error reading data from cache file.');
		}
		return unserialize($data);
	}
	
	// check if the cache file is valid or not
	public function valid($id) {

		$file = glob($this->cachedir . $id);
        $file = array_shift($file);
		return (bool)(time() - filemtime($file) <= $this->expire);
	}
}// End Cache class