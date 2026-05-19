<?php
defined('DSMVC') || die('No direct access allowed');

class Csrf {
    public static function init() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function getToken() {
        self::init();
        return $_SESSION['csrf_token'];
    }

    public static function validate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        self::init();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
    }

    public static function field() {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '" />';
    }
}
