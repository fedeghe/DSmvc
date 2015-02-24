<?php

class index extends Controller{

    public function action_index($params){
        
        $x = new View('head');
        $Person = new Person('Federico', 'Ghedina');
        $x->title = "DSmvc ~ " . $Person->sayHello();

        $v = new View('default');
        $v->head = $x->display();

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
        // echo __FILE__.'<br />';
    }

    public function before(){
        parent::after();
        // echo __FILE__.'<br />';
    }
    

}
