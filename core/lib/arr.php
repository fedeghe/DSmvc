<?php

defined('DSMVC') || die('No direct access allowed');

class arr{


	/**
	 * ordina un array associativo in base a le chiavi passate nel secondo array
	 *
	 * @param <type> $array associativo da ordinare
	 * @param <type> $orderArray array delle sole chiavi con l'ordine desidarato
	 * @return <type> l'array ordinato
	 *
	 */
	public static function sortArrayByArray($array,$orderArray) {
		$ordered = array();
		foreach($orderArray as $key) {
			if(array_key_exists($key,$array)) {
				$ordered[$key] = $array[$key];
				unset($array[$key]);
			}
		}
		return $ordered + $array;
	}

	/**
	 *
	 * @return <type>
	 */
	public static function arr2treearr($arr, $id, $fk, $root = 0){
		$parents = array();
		$tuples = array();
		foreach($arr as $row) {
			if(!isset($parents[$row[$fk]]))$parents[$row[$fk]]=array();
			if(!isset($tuples[$row[$id]]))$tuples[$row[$id]]=array();
			$parents[$row[$fk]][$row[$id]] = $row[$id];
			$tuples[$row[$id]] = $row;
		}
		return self::fold($parents[$root], $parents, $tuples, '', array());
	}
		// appoggio a treearray
	private static function fold($actual_parent, $parents, $tuples, $out, $arr){
		foreach($actual_parent as $catID){
			$arr[$catID]['element']=array();
			$arr[$catID]['element'] = $tuples[$catID];
			if(array_key_exists($catID, $parents)){
				$arr[$catID]['sons']=array();
				$arr[$catID]['sons'] =  self::fold($parents[$catID], $parents, $tuples, $out, $arr[$catID]['sons']);
			}
		}
		return $arr;
	}

/*
	public function arr2attr($arr) {
		$att ='';
		if(is_array($arr)) foreach($arr as $k => $v)	$att.= ' '.$k.'="'.$v.'"';
		return $att;
	}
*/
	public static function arr2attr($arr, $noQuot=false){
		$ret = '';
		if (is_array($arr) && count($arr) > 0) {
			foreach ($arr as $k => $val)
				$ret .= $k . '='.($noQuot?'':'"') . $val .($noQuot?'':'"').' ';
		}
		return $ret;
	}
	
	
	
	/**
	 *
	 * 	Se viene passato $md a TRUE allora tutti i valori in get vengono md5izzati,
	 * 	questo per rendere molto difficile che un uente metta in blacklist un altro utente tramite url)
	 */
	public static function arr2get($par=null, $md = FALSE) {

		if( !is_null($par) ){

		    $params = '';
		    $i = 0;

		    foreach ($par as $k => $p) {
				$val = ($md) ? md5($p) : $p;
				$params.= ( ($i == 0) ? '?' : '&') . "$k=$val";
				$i++;
		    }

		    return $params;

		} else {
		    return '';
		}
	}
	
	
	
	/**
	 * Check if tha passed var is a nonempty array
	 *
	 * @param array $arr var to check if is a nonempty array
	 * @return boolean
	 */

	public static function array_not_empty($arr){
		return ( is_array($arr) && count($arr)>0 );
	}

	/**
	 * Check if tha passed var is a empty array
	 *
	 * @param array $arr var to check if is a empty array
	 * @return boolean
	 */

	public static function array_empty($arr){
		return ( is_array($arr) && count($arr)==0 );
	}
	
	
	
	
	/**
	 *
	 *  Obtain an associative array from a matrix, with associative columns:
	 *
	 *  From $arr as
	 *
	 *	array(
	 *		array(
	 *			'key1'=>'val1_1',
	 *			'key2'=>'val1_2'
	 *		),
	 *		array(
	 *			'key1'=>'val2_1',
	 *			'key2'=>'val2_2'
	 *		),
	 *	)
	 *
	 * called with 'key1', 'key2' as 2° and 3° param returns
	 *
	 *	array(
	 *		'val1_1'=>'val1_2',
	 *		'val2_1'=>'val2_2'
	 *	)
	 *
	 *
	 * @param array  $arr
	 * @param string $key
	 * @param string $val
	 * @return array
	 */
	public static function array_makeassoc($arr=null, $key_label=null, $val_label=null){

	    if( is_null($arr) or is_null($key_label) or is_null($val_label) ){ return array(); }

	    $out = array();
	    foreach($arr as $k => $v){
			if( isSet($v[$key_label]) && array_key_exists($val_label, $v) ){
				$out[$v[$key_label]] = $v[$val_label];
			}
	    }

	    return $out;

	}
	
	
	/**
	 * Obtain from an array like
	 * array(
	 *		array(
	 *			'chiave'=>'valore1'
	 *		),
	 *		array(
	 *			'chiave'=>'valore2'
	 *		),
	 *		...
	 * )
	 *
	 * something like
	 * array(
	 *		0 =>'valore1',
	 *		1 =>'valore2'
	 * )
	 * calling it as array_simplify($arr, 'chiave')
	 * @param <type> $arr
	 * @param <type> $key
	 * @return <type>
	 *
	 */
	public static function array_simplify($arr, $key){
		if( is_null($arr) or is_null($key) ){ return array(); }
		$res = array();
		foreach($arr as $a)$res[] = $a[$key];
		return $res;
	}
	
	
	
	/**
	 * If $arr is Array and exists key $key return the element
	 *
	 * @param <type> $arr
	 * @param <type> $key
	 */
	public static function if_in($arr, $key, $else = false){
		return (is_array($arr) && array_key_exists($key, $arr)) ? $arr[$key] : $else;
	}



	/**
	 *  trasform an associative array in a string key1="value1" key2="value2"
	 *
	 * @param array $opt
	 * @return string
	 */
	public static function assoc2attrib($opt){
		$out = ' ';
		if(is_array($opt) && count($opt) > 0){
			foreach($opt as $k => $val)
				$out .= $k.'="'.$val.'" ';
		}
		return ($out==' ')?'':$out;
	}
	
	
	
	
	/**
	 *  trasform an associative array in a string key1:value1;key2:value2;
	 *
	 * @param array $opt
	 * @return string
	 */
	public static function assoc2style($opt){
		$out = ' ';
		if(is_array($opt) && count($opt) > 0){
			foreach($opt as $k => $val)
				$out .= $k.':'.$val.';';
		}
		return ($out==' ')?'':$out;
	}



		/**
	  Partiziona un doppio array associativo secondo una chiave
	 */
	public static function array_partition($arr=null, $key=false) {

		if( is_null($arr) ){ return array(); }

		$out = array();
		foreach ($arr as $k => $el) {
			if ( $key && array_key_exists($key, $el) && array_key_exists($el[$key], $out) && is_array($out[$el[$key]] ) ) {
				array_push($out[$el[$key]], $el);
			} else {
				$out[$el[$key]] = array($el);
			}
		}
		return $out;
	}

	
	
	
	
	/**
	 * Da un array tipo
	 * array(
	 *
	 *		0 =>array(
	 *			'k1'=>'v11',
	 *			'k2'=>'v12'
	 *		),
	 *		1 =>array(
	 *			'k1'=>'v21',
	 *			'k2'=>'v22'
	 *		),
	 *		...
	 * )
	 * retstituisce
	 * array(
	 *		'v11'=>array(
	 *			'k1'=>'v11',
	 *			'k2'=>'v12'
	 *		),
	 *		'v21'=>array(
	 *			'k1'=>'v21',
	 *			'k2'=>'v22'
	 *		),
	 * )
	 * passando k1 come chiave
	 *
	 * @param <type> $arr
	 * @param <type> $key
	 *
	 */
	public static function array_use_inner_key($arr, $key){
		$res = array();
		foreach($arr as $el)$res[$el[$key]] = $el;
		return $res;
	}


}