<?php

class ctrl404 extends Controller{

    public function action_index($_, $url){
        Response::send('PAGE NOT FOUND');
        Response::send('<br/>');
        Response::send('url :'. utility::h($url));
    }
}
