<?php
//  system/core/parser.class.php
//  DSmaheorana
//
//  Created by Federico Ghedina on 07/11/10.
//  Copyright 2010 Federico Ghedina. All rights reserved.
//

/**
 *
 *	[$varname$]
 *	[D[]D]		defined
 *	[S[]S]		session
 *	[P[]P]		post
 *	[G[]G]		get
 *	{{}}		chunks
 *	[[]]		snippet
 *	{J{}J}
 *	{C{}JC}
 *	[T[]T]		translation
 *
 */

class Parser
{
    private $rulez;
    private $content;
    private $variables;
    private $_anti_recursion_counter;
    private $_anti_recursion_cutoff;
    private $queue;

    private $cumulated_labels_for_translation = array();

    public function __construct($content, $vars)
    {
        $this->_anti_recursion_counter = 0;
        $this->_anti_recursion_cutoff = 1000;
        $this->queue = array();
        $this->content = $content;
        $this->variables = $vars;


        $min = '([a-zA-Z0-9_]{1,})';
        $base = '([a-zA-Z0-9_\-]{1,})';
        $lng = '(.*)';
        $file = '([a-zA-Z0-9_\-./]*)';
        $this->rulez = array(
            'Tvars'    => array('preg' => '!\[\$' . $base . '\$\]!Uis',            'params' => array('pre' => '[$', 'post' => '$]')),
            'defV'    => array('preg' => '!\[D\[' . $base . '\]D\]!Uis',            'params' => array('pre' => '[D[', 'post' => ']D]')),
            'sessV'    => array('preg' => '!\[S\[' . $base . '\]S\]!Uis',            'params' => array('pre' => '[S[', 'post' => ']S]')),
            'postV'    => array('preg' => '!\[P\[' . $base . '\]P\]!Uis',            'params' => array('pre' => '[P[', 'post' => ']P]')),
            'getV'    => array('preg' => '!\[G\[' . $base . '\]G\]!Uis',            'params' => array('pre' => '[G[', 'post' => ']G]')),
            'chu'    => array('preg' => '!\{\{' . $file . '\}\}!Uis',            'params' => array('pre' => '{{', 'post' => '}}')),
            'sniNP'    => array('preg' => '!\[\[' . $file . '\]\]!Uis',            'params' => array('pre' => '[[', 'post' => ']]')),
            'sniP'    => array('preg' => '!\[\[' . $file . '\s?[&](.*)\]\]!Uis',    'params' => array('pre' => '[[', 'post' => ']]')),

            'js'    => array('preg' => '!\{J\{' . $file . '\}J\}!Uis',            'params' => array('path' => PATH_JS, 'pre' => '{J{', 'post' => '}J}')),
            'css'    => array('preg' => '!\{C\{' . $file . '\}C\}!Uis',            'params' => array('path' => PATH_CSS, 'pre' => '{C{', 'post' => '}C}')),

            'view'        => array('preg' => '!\[V\[' . $file . '(\s[&](.*))?\]V\]!Uis',    'params' => array('pre' => '[V[', 'post' => ']V]')),

            'trans_soft' => array('preg' => '!\[t\[' . $lng . '\]t]!Uis',            'params' => array('pre' => '[t[', 'post' => ']t]')),

            'trans'    => array('preg' => '!\[T\[' . $lng . '\]T]!Uis',            'params' => array('pre' => '[T[', 'post' => ']T]')),
            'php'    => array('preg' => '!\[\[php\s' . $min . '::(.*)\]\]!Uis', 'params' => array('pre' => '[[php ', 'post' => ']]'))
        );
    }

    /**
     *
     */

    public function set_content($content)
    {
        $this->content = $content;
    }

    public function set_variables($variables)
    {
        $this->variables = $variables;
    }

    public function parse()
    {

        extract($this->variables);

        foreach ($this->rulez as $type => $properties) {

            if (preg_match_all($properties['preg'], $this->content, $out)) {

                array_push($this->queue, $out);

                //  out[1] hanno il contenuto (la var)
                $pars = isset($properties['params']) ? $properties['params'] : FALSE;
                $f = 'get_' . $type;
                $this->$f($out, $pars);
            }
        }

        //go deep down as permitted if necessary!
        while (
            preg_match($this->rulez['Tvars']['preg'],        $this->content)
            || preg_match($this->rulez['defV']['preg'],    $this->content)
            || preg_match($this->rulez['sessV']['preg'],    $this->content)
            || preg_match($this->rulez['postV']['preg'],    $this->content)
            || preg_match($this->rulez['getV']['preg'],    $this->content)
            || preg_match($this->rulez['chu']['preg'],    $this->content)
            || preg_match($this->rulez['sniNP']['preg'],    $this->content)
            || preg_match($this->rulez['sniP']['preg'],    $this->content)
            || preg_match($this->rulez['js']['preg'],        $this->content)
            || preg_match($this->rulez['css']['preg'],    $this->content)
            || preg_match($this->rulez['view']['preg'],    $this->content)
            || preg_match($this->rulez['trans']['preg'],    $this->content)
            || preg_match($this->rulez['php']['preg'],    $this->content)
        ) {

            if ($this->_anti_recursion_counter > $this->_anti_recursion_cutoff) {
                throw new ParseRecursionException('<br /><h3>Parser is going too deep! Request aborted</h3>');
                die();
                exit;
            } else {
                $this->_anti_recursion_counter++;
                return $this->parse($this->content);
            }
        }


        //maybe cumulate label must be merged
        if (CUMULATE_LANG) {
            $lang_path = PATH_TRANSLATIONS . $_SESSION['lang'] . '.php';

            //get actual
            $mah_trans = array();
            if (file_exists($lang_path)) {
                include($lang_path);
                $mah_trans = $trans;
            }

            $new = array_merge($mah_trans, $this->cumulated_labels_for_translation);
            ksort($new);
            $data = '<?php' . "\n" . '$trans = array(';

            foreach ($new as $k => $v) {
                $data .= "\n\t" . '"' . $k . '" => "' . $v . '"' . ",";
            }
            $data .= "\n);";
            file_put_contents($lang_path, $data);
        }


        //
        //// something more ?
        //
        $this->content = preg_replace('/^\n$/', '', $this->content);
        return  $this->content;
    }

    private function get_php($out, $params)
    {
        $pre = $params['pre'];
        $post = $params['post'];
        //utility::pd($out);
        //foreach($out[1] as $k => $val)	$this->content = str_replace($pre.$val.$post, isSet($$val)?$$val:'', $this->content);

        foreach ($out[1] as $k => $val) {
            ob_start();
            eval($out[1][$k] . '::' . $out[2][$k] . ';');
            $tmp = ob_get_clean();
            $this->content = str_replace($out[0][$k], $tmp, $this->content);
        }
    }

    /**
     * parse [$variable$]
     */
    private function get_Tvars($out, $params){
        extract($this->variables);
        $pre = $params['pre'];
        $post = $params['post'];
        foreach ($out[1] as $k => $val)    $this->content = str_replace($pre . $val . $post, isset($$val) ? $$val : '', $this->content);
        //foreach($out[1] as $k => $val)	$this->content = str_replace($out[0][$k], isSet($$val)?$$val:'', $this->content);
    }


    /**
     * parse [D[defined_variables]D]<?php echo '
     */
    private function get_defV($out, $params){
        $pre = $params['pre'];
        $post = $params['post'];
        foreach ($out[1] as $k => $val)
            $this->content = str_replace($pre . $val . $post, defined($val) ? constant($val) : '', $this->content);
    }

    private function get_view($out, $params){

        foreach ($out[1] as $k => $val) {
            ob_start();
            if (file_exists(PATH_VIEW . $val . '.phtml')) {
                include(PATH_VIEW . $val . '.phtml');
            }
            $view = ob_get_clean();
            $this->content = str_replace(
                $params['pre'] . $val . $params['post'],
                $view,
                $this->content
            );
        }
    }

    /**
     * parse {{chunks}}
     */
    private function get_chu($out, $params){
        $pre = $params['pre'];
        $post = $params['post'];
        foreach ($out[1] as $k => $val) {
            ob_start();
            switch (true) {
                case file_exists(PATH_CHU_SIS . $val . '.phtml'):
                    include(PATH_CHU_SIS . $val . '.phtml');
                    break;
                case file_exists(PATH_CHU . $val . '.phtml'):
                    include(PATH_CHU . $val . '.phtml');
                    break;
            }
            $chu = ob_get_clean();
            $this->content = str_replace($pre . $val . $post, $chu, $this->content);
        }
    }

    /**
     * parse [[snippet]]
     */
    private function get_sniNP($out, $params){
        $pre = $params['pre'];
        $post = $params['post'];
        foreach ($out[1] as $k => $snipp) {
            ob_start();
            switch (true) {
                case file_exists(PATH_SNI_SIS . $snipp . '.php'):
                    include(PATH_SNI_SIS . $snipp . '.php');
                    break;
                    //or inside a folder
                case file_exists(PATH_SNI_SIS . $snipp . DS . $snipp . '.php'):
                    include(PATH_SNI_SIS . $snipp . DS . $snipp . '.php');
                    break;
                    //
                case file_exists(PATH_SNI . $snipp . '.php'):
                    include(PATH_SNI . $snipp . '.php');
                    break;
                    //or inside a folder
                case file_exists(PATH_SNI . $snipp . DS . $snipp . '.php'):
                    include(PATH_SNI . $snipp . DS . $snipp . '.php');
                    break;
            }
            $sni = ob_get_clean();
            $this->content = str_replace($pre . $snipp . $post, $sni, $this->content);
        }
    }

    /**
     * parse [[snippet &par1=`val1`&par2=`val2`]]
     */
    private function get_sniP($out, $params){

        for ($i = 0; $i < count($out[0]); $i++) {
            //0 match completo
            //1 primo match ...nome dello snippet
            //2 secondo match....parametri
            $params = explode('&', $out[2][$i]);
            //per ricordarmi di quali fare unset
            $vars_names = array();
            foreach ($params as $k) {
                preg_match('/(.*)=`(.*)`/Uis', $k, $matches);
                list($nome, $val) = explode('=`', $k);
                $vars_names[$matches[1]] = $matches[2];
                $vars_names[] = $matches[1];
            }

            $more = in_array('snippetpath', $vars_names) ? $snippetpath . DS : '';
            //if($more!=='')die('<h1>'.$more.'</h1>');
            $snipp = $out[1][$i];
            $placeholder = $out[0][$i];
            ob_start();
            extract($vars_names);
            if (file_exists(PATH_SNI_SIS . $more . $snipp . '.php')) {
                include(PATH_SNI_SIS . $more . $snipp . '.php');
            }
            if (file_exists(PATH_SNI . $more . $snipp . '.php')) {
                include(PATH_SNI . $more . $snipp . '.php');
            }
            //ora non mi servono + i params
            $sni = ob_get_clean();
            foreach ($vars_names as $k => $var) {
                unset($$k);
            }
            $this->content = str_replace($placeholder, $sni, $this->content);
        }
    }



    /**
     * parse for session vars [S[xxx]S]
     */
    private function get_sessV($out, $params){
        foreach ($out[1] as $k => $val)
            $this->content = str_replace(
                $params['pre'] . $val . $params['post'],
                isset($_SESSION[$val]) ? $_SESSION[$val] : '',
                $this->content
            );
    }
    /**
     * parse for get vars [G[xxx]G]
     */
    private function get_getV($out, $params){
        foreach ($out[1] as $k => $val)
            $this->content = str_replace(
                $params['pre'] . $val . $params['post'],
                isset($_GET[$val]) ? $_GET[$val] : '',
                $this->content
            );
    }



    /**
     * parse for post vars [P[xxx]P]
     */
    private function get_postV($out, $params){
        foreach ($out[1] as $k => $val)
            $this->content = str_replace(
                $params['pre'] . $val . $params['post'],
                isset($_POST[$val]) ? $_POST[$val] : '',
                $this->content
            );
    }

    private function get_js($out, $params){
        foreach ($out[1] as $k => $val) {
            if (file_exists(BASE_FOLDER . $val)) {
                $this->content = str_replace(
                    $params['pre'] . $val . $params['post'],
                    '<script src="' . URL_BASE . $val . '"></script>',
                    $this->content
                );
            }
        }
    }

    private function get_css($out, $params){
        foreach ($out[1] as $k => $val) {
            if (file_exists(BASE_FOLDER . $val)) {
                $this->content = str_replace(
                    $params['pre'] . $val . $params['post'],
                    '<link rel="stylesheet" href="' . URL_BASE . $val . '">',
                    $this->content
                );
            }
        }
    }

    private function get_trans_soft($out, $params){
        $this->get_trans($out, $params, true);
    }

    private function get_trans($out, $params, $soft = FALSE){

        $dest_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : DEFAULT_LANG;

        $trans = array();
        if (file_exists(PATH_TRANSLATIONS . $dest_lang . '.php')) {
            include(PATH_TRANSLATIONS . $dest_lang . '.php');
        }

        $pre = $params['pre'];
        $post = $params['post'];

        // echo PATH_TRANSLATIONS . $dest_lang . '.php';
        foreach ($out[1] as $k => $val) {
            // copy for editing
            $label = $val;

            // check if user want to overload lang... this allows using a placeholder like [T[hello>zh]T]
            // just to force the chinese tranlation of a label without looking at the session lang
            if (strpos($label, '>') !== false) {
                list($label, $dest_lang) = explode('>', $val);

                $exists = file_exists(PATH_TRANSLATIONS . $dest_lang . '.php');

                $transL = array();
                if ($exists) {
                    include(PATH_TRANSLATIONS . $dest_lang . '.php');
                    $transL = $trans;
                    // debug($trans);
                }

                // that reduce usage, in fact cannot use in a attribute like a title or a alt
                $x = ($exists && isset($transL[$label]) && $transL[$label] != $label) ?
                    $transL[$label]
                    : ($soft ?  $label : '<i class="untranslated" title="not translated">' . $label . '</i>');
            } else {

                // debug($trans);
                // same here
                $x = (isset($trans[$label]) && $trans[$label] != $label) ?
                    $trans[$label]
                    : ($soft ? $label : '<i class="untranslated" title="not translated">' . $label . '</i>');
            }

            // replace
            $this->content = str_replace($params['pre'] . $val . $params['post'], $x, $this->content);

            // pick up labels if CUMULATE_LANG is true
            if (CUMULATE_LANG && !array_key_exists($label, $trans)) {
                $this->cumulated_labels_for_translation[$label] = $label;
            }
        }
    }

    public function view_stats(){
        echo '<h3>' . __CLASS__ . ': ' . $_anti_recursion_counter . '</h3>';
    }
}// end class
