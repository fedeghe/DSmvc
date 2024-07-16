<?php

class ctrl404 extends Controller{

    public function action_index($_, $url){
        Response::send('PAGE NOT FOUND:'. __FILE__);
        Response::send('<br/>');
        Response::send('url :'. $url);
    }
}
