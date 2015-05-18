<?php
$_SESSION['lang'] = array_key_exists('lang', $_GET) ?
    $_GET['lang']
    :
    (isSet($_SESSION['lang']) ? $_SESSION['lang'] : DEFAULT_LANG);