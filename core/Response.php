<?php

class Response{
	
    private static $content;
	public static $id;
	
	public static function send($out, $headers = FALSE) {

        if (self::$id) {
            Cache::set(md5(URL), $out);
        }

        if ($headers) {
            foreach ($headers as $header) {
                header($header);
            }
        } else {
            header('Content-type: text/html');
        }
		echo $out;
	}
	
}
