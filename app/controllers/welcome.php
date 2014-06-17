<?php

class welcome extends Controller{

	public function action_index(){

		$x = new View('head');
		
		$x->title="DSmvc";

		$v = new View('default');
		
		$v->head = $x->display();

		//echo $this->nome;

		Response::send($v->display());
	}


	public function action_prova(){
		$x = new View('head');
		$x->title="prova";

		$v = new View('default');
		$v->head = $x->display();

		if($this->nome!='')$v->nome = $this->nome;

		Response::send($v->display());
	}
	
	
	public function after(){
		
		parent::after();
		//echo __FILE__.'<br />';
	
	}
	public function before(){
		
		parent::after();
		//echo __FILE__.'<br />';
	
	}
	

}
