<?php
class index extends Controller{
    public function action_index($params){   
        $x = DSMVC::getView('head');
        $x->title = "gzpot";
        $v = DSMVC::getView('default');
        $v->head = $x->display();
        $v->d = "heloooowwww";
        Response::send($v->display());
    }
}
