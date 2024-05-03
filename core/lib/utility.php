<?php
defined('DSMVC') || die('No direct access allowed');
class utility{
	/**
	 * prints variable dump
	 *
	 * @param all $arr
	 */
	public static function pd($arr=false, $out = false){
		$tmp = '<pre style="-moz-border-radius-bottomright:15px;-moz-border-radius-topright:15px;color:#00ff00;padding:10px;border:1px dashed red; border-left:5px solid red;margin:5px;background-color:#333333">'.print_r($arr, true).'</pre>';
		if(!$out){echo $tmp;}
		else{return $tmp;}
	}

	public static function get_pars($arr){
		$ret = array();
		foreach($arr[0] as $k => $key){
			$ret[$key] = $arr[1][$k];
		}
		return $ret;
	}

	/* per fissare io messaggio da mostrare al caricamento della pagina
	 */
	public static function mem($tpl=false){
		$template = $tpl ? $tpl : '<strong>MEM~%MEM%K (%PEAK%)</strong>';
		return str_replace(
			array('%MEM%', '%PEAK%'),
			array(memory_get_usage(true)/(2<<10), memory_get_peak_usage(true)/(2<<10) ),
			$template
		);
	}
	/**
	 *  Get caller info of the function who invokes gat_caller
	 *
	 * @param void
	 * @return array('file','line')
	 */
	public static function get_caller(){

		$debug_backTrace = debug_backtrace();
		//two steps behind
		array_shift($debug_backTrace);
		$caller = array_shift($debug_backTrace);

		return array(
			'file'=>$caller['file'],
			'line'=>$caller['line']
		);
	}
	
	/**
	 * Return a complete <select> html tag
	 *
	 *
	 * @param <type> $keys
	 * @param <type> $vals
	 * @param <type> $opt
	 * @param <type> $selected
	 * @param <type> $first
	 * @return string
	 *
	 */
	public static function get_combo($keys, $vals, $opt=array(), $selected='', $first=FALSE, $optgroup=FALSE, $k_v='vals') {
		$optgrouped = (is_array($optgroup) && count($optgroup)>0 );
		$group = $optgrouped ? current($optgroup) : false;
		
		$ret = '<select ';
		$ret .= arr::arr2attr($opt);
		$ret.='>';
		if ($first)$ret.='<option value="">- ' . $first . ' -</option>';
		
		for ($i = 0, $len = count($keys); $i < $len; $i++) {
			if($optgrouped && $i>0 && $optgroup[$keys[$i]] != $group){
				$ret .= '</optgroup>';
			}
			if($optgrouped && ($i==0 || $optgroup[$keys[$i]] != $group) ){
				$ret .= '<optgroup label="'.$optgroup[$keys[$i]].'">';
			}
			$ret .= '<option value="' . $keys[$i] . '" '.(($selected == ${$k_v}[$i]) ? ' selected="selected" ' : '' ) . ' >' . $vals[$i] . '</option>';
			
			if($optgrouped){
				$group = $optgroup[$keys[$i]];
			}
		}
		$ret.='</select>';
		return $ret;
	}

	/**
	 * Clean this copy of invalid non ASCII √§√≥characters
	 *
	 * @param <type> $str
	 */
	public static function clean_non_ascii($str){
		return preg_replace('/[^(\x20-\x7F)]*/','', $str);
	}

	/**
	 *	get backtrace call
	 *
	 * @param integer $depth
	 * @return array
	 */
  	public static function _getBacktraceVars($depth=1){
		$backtrace = debug_backtrace();
		if (strcasecmp(@$backtrace[$depth+1]['class'], 'Log_composite') == 0) {
			$depth++;
		}
		$file = @$backtrace[$depth]['file'];
		$line = @$backtrace[$depth]['line'];
		$func = @$backtrace[$depth + 1]['function'];
		if (in_array($func, array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug'))) {
			$file = @$backtrace[$depth + 1]['file'];
			$line = @$backtrace[$depth + 1]['line'];
			$func = @$backtrace[$depth + 2]['function'];
		}
		if (is_null($func)) { $func = '(none)'; }
		return array($file, $line, $func);
	}

	public static function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}

	public static function print_d($var, $out = FALSE, $label=FALSE) {
		$ret = '<pre style="color:#DF0000; border:1px dotted red; background-color:#ffff00; padding:10px;">';
		ob_start();
		if ($label)echo $label . '<br />';
		switch (true) {
			case is_object($var):
				echo '<h5>OBJECT</h5>';
				var_dump($var);
				break;
			case is_array($var):
				echo '<h5>ARRAY ( #'.count($var).' )</h5>';
				echo print_r($var, true);
				break;
			default:
				echo '<h5>VAR</h5>';
				echo $var;
				break;
		}
		$ret.=ob_get_clean();
		$ret.='</pre>';
		if ($out) {
			return $ret;
		} else {
			echo $ret;
		}
	}

	/*
	 * prints call stack
	 *
	 */
	public static function trace(){
		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
	}

	/**
	 * uses tinyurl service to recude a url
	 *
	 * @param url $url
	 * @return url
	 */
	private static function _fetchTinyUrl($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_ENCODING , "gzip");
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	/*
	 * 
	 * 
	 * thanks to http://www.bobulous.org.uk/coding/php-xml-feeds.html
	 */
	public static function make_safe($string) {
		$string = preg_replace('#<!\[CDATA\[.*?\]\]>#s', '', $string);
		$string = strip_tags($string);
		// The next line requires PHP 5.2.3, unfortunately.
		//$string = htmlentities($string, ENT_QUOTES, 'UTF-8', false);
		// Instead, use this set of replacements in older versions of PHP.
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = str_replace('(', '&#40;', $string);
		$string = str_replace(')', '&#41;', $string);
		$string = str_replace('"', '&quot;', $string);
		$string = str_replace('\'', '&#039;', $string);
		return $string;
	}
	
	//funzione usata nelle funzioni pack
    public static function md5time($file){
		return md5( date ("F d Y H:i:s.", filemtime($file)) );
	}
	
	public static function validUrl($ret){
		return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}
	
	/**
	 * Validate an email address.
	 * Provide email address (raw input)
	 * Returns true if the email address has the email 
	 * address format and the domain exists.
	 */
	 public static function validEmail($email){
		 $atIndex = strrpos($email, "@");
		 if (is_bool($atIndex) && !$atIndex) {
			 return false;
		} else {
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			
			switch(true){
			
				case ($localLen < 1 || $localLen > 64):
				// local part length exceeded
					return false;
				break;
				case ($domainLen < 1 || $domainLen > 255):
					// domain part length exceeded
					return false;
				break;
				case ($local[0] == '.' || $local[$localLen-1] == '.'):
					// local part starts or ends with '.'
					return false;
				break;
				case (preg_match('/\\.\\./', $local)):
					// local part has two consecutive dots
					return false;
				break;
				case (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)):
					// character not valid in domain part
					return false;
				break;
				case (preg_match('/\\.\\./', $domain)):
					// domain part has two consecutive dots
					return false;
				break;
				case (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))):
					// character not valid in local part unless 
					// local part is quoted
					if (!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))){
						return false;
					}
				break;
			}
			if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))){
				// domain not found in DNS
				return false;
			}
		}
		return true;
	}
	/*
	public static function hl_code($code){
		return highlight_string($code, true);
	}
	*/
	public static function hl_code($is_php =true){
		static $on=false;
		if (!$on) ob_start();
		else {
			$out = ob_get_contents();
			$buffer= ($is_php?"<?php \n":"").$out.($is_php?" ":"").($is_php?"\n":"");
			ob_end_clean();
			highlight_string($buffer);
		}
		$on=!$on;		
	}

	public static function is_keyword($keyword){
		$kw = array(
			'abstract','and','array', 'as', 'break', 'case', 'catch', 'cfunction', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'do',
			'else', 'elseif', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'extends', 'final', 'for', 'foreach', 'function',
			'global', 'goto', 'if', 'implements', 'interface', 'instanceof', 'namespace', 'new', 'old_function', 'or', 'private', 'protected',
			'public', 'static', 'switch', 'throw', 'try', 'use', 'var', 'while', 'xor', '__CLASS__', '__DIR__', '__FILE__', '__LINE__', '__FUNCTION__', '__METHOD__', '__NAMESPACE__'
		);
		return in_array($keyword, $kw);		
	}
	
	/**
	* Check a string of base64 encoded data to make sure it has actually
	* been encoded.
	*
	* @param $encodedString string Base64 encoded string to validate.
	* @return Boolean Returns true when the given string only contains
	* base64 characters; returns false if there is even one non-base64 character.
	*/
	public static function checkBase64Encoded($encodedString) {
		$length = strlen($encodedString);
		// Check every character.
		for ($i = 0; $i < $length; ++$i) {
			$c = $encodedString[$i];
			if (
				($c < '0' || $c > '9') &&
				($c < 'a' || $c > 'z')	&&
				($c < 'A' || $c > 'Z')	&&
				($c != '+')	&&
				($c != '/')	&&
				($c != '=')
			){
				// Bad character found.
				return false;
			}	
		}
        // Only good characters found.
		return true;
	}
	
	public static function swehtml($input) {
	 	return str_replace(
	 		array("Ã¥",	"Ã¤", "Ã¶", "Ã…", "Ã„", "Ã–", 'Ã¼', 'ÃŸ', 'Ãœ', 'Ã©', 'Ã£', 'Â©'),
    		array("å", "ä", "ö", "Å", "Ä", "Ö", 'ü', 'ß', 'Ü', 'é', 'ã', '©'),
	 		$input
	 	);
	}
}
