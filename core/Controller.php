<?php
class Controller{

	private $vars;

	public function __construct(){$this->vars = array();}
	
	protected function after(){	}
	
	protected function before(){ }
	
	public function __call($name, $arguments) {
		$bt = debug_backtrace();
		$controller = $bt[1]['class'];
		//echo '<pre>'.print_R(debug_backtrace(), true).'</pre>';
		echo '<u><b>'.$name.'</b></u> action <b>NOT FOUND</b> in '.__CLASS__.' <b>'.$controller.'</b>';
	}

	public  function __get($name){
		return array_key_exists($name, $this->vars) ? $this->vars[$name]:false ;
	}
	
	public  function __set($name, $val){
		if( in_array($name, $this->vars))return true;
		$this->vars[$name] = $val;
		return true;
	}

	public function index(){
		echo 'DEFAULT: '.__FILE__.' @ '.__LINE__;
	}

	public function _add_vars($arr, $url_decode=false){
		foreach($arr as $k => $v)
			$this->__set($k, $url_decode ? urldecode($v) : $v);
	}


}// End DefaultController class
