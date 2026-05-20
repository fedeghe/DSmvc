<?php

class Minifier {

    public static function minify($html) {
        if (trim($html) === "") return $html;

        // Remove HTML comments (except IE conditional comments)
        $html = preg_replace('/<!--(?!\s*(?:\[if[^\]]+\]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Remove whitespace between tags
        $html = preg_replace('~>\s+<~', '><', $html);

        // Collapse multiple spaces into single spaces
        $html = preg_replace('~\s{2,}~', ' ', $html);

        return trim($html);
    }
}
