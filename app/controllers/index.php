<?php

class index extends Controller{

    public function action_index($params){
        
        $x = DSMVC::getView('head');
        $Person = new Person('Federico', 'Ghedina');
        $x->title = "DSmvc ~ " . $Person->sayHello();

        $v = DSMVC::getView('default');
        $v->head = $x->display();

        $_SESSION['user'] = array(
            'fk_group' => 2
        );

        // only if the trial db is set
        // $places = Places::getAll();
        // $v->data = utility::print_d($places, true);

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
    
    public function after_index(){
        // Response::send(__FILE__.' - method<br />');
    }
    public function before_index(){
        // Response::send(__FILE__.' - method<br />');
    }

    public function after(){
        parent::after();
        // Response::send(__FILE__.' - controller<br />');
    }

    public function before(){
        parent::after();
        // Response::send(__FILE__.' - controller<br />');
    }
    

}
