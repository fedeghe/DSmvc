<?php

class Minifier {

    public static function minify($html) {
        if (trim($html) === "") return $html;

        $protected = array();
        $counter = 0;

        $protect = function($matches) use (&$protected, &$counter) {
            $placeholder = "___PROTECTED_" . $counter . "___";
            $protected[$placeholder] = $matches[0];
            $counter++;
            return $placeholder;
        };

        // Protect <script> and <style> contents from being mangled
        $html = preg_replace_callback('~(<script\b[^>]*>)(.*?)(</script>)~is', $protect, $html);
        $html = preg_replace_callback('~(<style\b[^>]*>)(.*?)(</style>)~is', $protect, $html);

        // Remove HTML comments (except IE conditional comments)
        $html = preg_replace('/<!--(?!\s*(?:\[if[^\]]+\]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('~>\s+<~', '><', $html);

        // Collapse multiple spaces into single spaces
        $html = preg_replace('~\s{2,}~', ' ', $html);

        // Restore protected blocks
        foreach ($protected as $placeholder => $content) {
            $html = str_replace($placeholder, $content, $html);
        }

        return trim($html);
    }
}
