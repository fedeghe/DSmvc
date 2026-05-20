<?php

class Minifier {

    public static function minify($html) {
        if (trim($html) === "") return $html;

        // Rimuovi commenti HTML (tranne IE conditional comments)
        $html = preg_replace('/<!--(?!\s*(?:\[if[^\]]+\]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Rimuovi whitespace tra tag
        $html = preg_replace('~>\s+<~', '><', $html);

        // Collassa spazi multipli in singoli spazi
        $html = preg_replace('~\s{2,}~', ' ', $html);

        return trim($html);
    }
}
