<?php
// language handler: whitelist ?lang to a safe charset (no traversal, no dot)
// i18n files are PATH_TRANSLATIONS . <lang> . '.php'
$lang = array_key_exists('lang', $_GET) ? $_GET['lang'] : false;
if ($lang !== false) {
    // accept only codes like "it", "en", "zh-cn"
    $lang = preg_match('/^[a-zA-Z][a-zA-Z0-9_\-]{0,9}$/', $lang) ? $lang : false;
}
$_SESSION['lang'] = $lang !== false
    ? $lang
    : (isset($_SESSION['lang']) ? $_SESSION['lang'] : DEFAULT_LANG);
