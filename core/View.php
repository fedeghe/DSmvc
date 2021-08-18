<?php

class View {

	private $viewfile = 'default.phtml';
	private $properties;

	// factory method (chainable)
	public static function factory($viewfile = '') {
		return new self($viewfile);
	}

	// constructor
	public function __construct($viewfile = '') {
		$this->properties = array();
		
		if ($viewfile !== '') {
			$viewfile = PATH_VIEW.$viewfile . '.phtml';
			
			if (file_exists($viewfile)) {
				$this->viewfile = $viewfile;
			} else {
				echo 'View `'. $viewfile.'` not found!';
			}
		}
		// default title
		$this->title = APP_NAME . ' :: ' .DSMVC::$action;
	}

	public function  __destruct() {
		$this->properties = array();
	}

	// set undeclared view property
	public function __set($property, $value) {
		if (!isset($this->$property)) {
			$this->properties[$property] = $value;
		}
	}

	// get undeclared view property
	public function __get($property) {
		if (isset($this->properties[$property])) {
			return $this->properties[$property];
		}
	}

	public function set ($property, $value=false) {
		if (is_array($property)) {
			foreach ($property as $k => $v) {
				$this->properties[$k] = $v;	
			}
		} else {
			$this->properties[$property] = $value;
		}
		//chain
		return $this;
	}

	// parse view properties and return output
	public function display() {

		extract($this->properties);
		ob_start();
		include($this->viewfile);
		
		$content = ob_get_clean();

		if (AUTO_PARSE){
			$parser = Factory::getParser($content, $this->properties);
			$content = $parser->parse();
		}

		return $content;		
	}


}

// End View class
