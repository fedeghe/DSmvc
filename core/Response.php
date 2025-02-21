<?php
class Response{
    private static $content = '';
	public static $id;
	public static function send($out, $headers = FALSE) {
        if ($headers) {
            foreach ($headers as $header) {
                header($header);
            }
        } else {
            header('Content-type: text/html');
        }
		self::$content .= $out;
	}
    public static function sendJson($json) {
        header('Content-type: application/json');
		self::$content .= $json;
	}

    public static function getContent() {
        if (self::$id) {
            Cache::set(md5(URL), self::$content);
        }
        return self::$content;
    }
}
