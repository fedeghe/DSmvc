<?php

class ctrl404 extends Controller{

    public function action_index(){

        Response::send('PAGE NOT FOUND :'. __FILE__);
    }
}
