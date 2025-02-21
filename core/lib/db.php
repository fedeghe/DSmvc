<?php

defined('DSMVC') || die('No direct access allowed');


class db{
	protected $db;
	private $time;
	private $tmp_start, $tmp_end;
	private static $instance;

	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new static();
		}
		return self::$instance;
	}

	public function get_conn(){return $this->db;}

	private function __construct(){
		$this->time = 0;
	}

	public function __destruct(){
		$_SESSION[APP_NAME]['mysql_time'] =  $this->time;
		$this->disconnect();
	}

	protected function connect($c){
		$this->db = mysqli_connect(
			$c['host'],
			$c['user'],
			$c['pwd']
			,$c['db']
			// ,$c['port']
		);
		@mysqli_select_db($this->db, $c['db']);
	}

	protected function disconnect(){
		return @mysqli_close($this->db);
	}

	private function t_start(){
		$this->tmp_start = utility::microtime_float();
	}

	private function t_end(){
		$this->tmp_end = utility::microtime_float();
		$this->time += ($this->tmp_end - $this->tmp_start);
	}

	/**
	 *	Perform a query
	 *
	 * @param <type> $sql
	 * @param array [$pars] parametres to bind (use ? in $sql)
	 * @param <type> [$preparsed] set to true if alla parameters need not to be `mysql_real_escape_string`ed
	 * @param <type> [$debug] set to true to view parsed query
	 * @return <type>
	 */
	public function query($sql='',$pars = array(), $preparsed = false, $debug=false, $doit=true){
		$this->t_start();
		$s = $sql;
		if (is_array($pars) && count($pars)>0) {
			$pos = 0;
			foreach ($pars as $ph => $pv) {
				$place = strpos($s,"?",$pos);
				if ($place)
					$s = substr_replace(
						$s,
						$preparsed ? $pv : mysqli_real_escape_string($this->db, $pv),
						$place,
						1
					);
				$pos = $place;
			}
		}
		
		if ($debug) echo'<br />Query:'.date('H:i:s').'<br /><strong>'.$s.'</strong><br />--------------------------/<br />';

		if (!$doit) {
			return true;
		}

		$res = (trim($s)!='') ? mysqli_query($this->db, $s) : false;
		// echo $res ? 'true' : 'false';
		$this->t_end();
		return $res ;
	}

	public function insert_id ($link_identifier=false){
		$this->t_start();
		$res = @mysqli_insert_id($link_identifier ? $link_identifier : $this->db);
		$this->t_end();
		return $res ;
	}	
	
	public function affected_rows ($link_identifier=false ){
		$this->t_start();
		$res = @mysqli_affected_rows($link_identifier ? $link_identifier : $this->db);
		$this->t_end();
		return $res ;
	}

	public function close($link_identifier=false ){
		$this->t_start();
		$res = @mysqli_close($link_identifier ? $link_identifier : $this->db);
		$this->t_end();
		return $res ;
	}
		
	public function errno($link_identifier=false ){
		$this->t_start();
		$res =  @mysqli_errno($link_identifier ? $link_identifier : $this->db);
		$this->t_end();
		return $res ;
	}

	public function error($link_identifier=false ){
		$this->t_start();
		$res = @mysqli_error($link_identifier ? $link_identifier : $this->db);
		$this->t_end();
		return $res ;
	}

	public function fetch_array($result){
		$this->t_start();
		$res = @mysqli_fetch_array($result);
		$this->t_end();
		return $res ;
	}

	public function fetch_assoc ($result){
		$this->t_start();
		$res = @mysqli_fetch_assoc($result);
		$this->t_end();
		return $res ;
	}
	
	public function fetch_all_assoc ($result){
		$this->t_start();
		$ret = array();
		while($res = $this->fetch_assoc($result))$ret[] = $res;
		$this->t_end();
		return $ret ;
	}

	public function fetch_row ($result){
		$this->t_start();
		$res = @mysqli_fetch_row($result);
		$this->t_end();
		return $res ;
	}

	public function free_result ($result){
		$this->t_start();
		$res = @mysqli_free_result ($result);
		$this->t_end();
		return $res ;
	}

	public function num_rows($result){
		$this->t_start();
		$res = @mysqli_num_rows($result);
		$this->t_end();
		return $res ;
	}

	public function replacecontent($table, $field, $src, $replace, $where){
		return $this->query(
			'UPDATE `?`
				SET ? = REPLACE(?, "?","?")
			  WHERE ?;
			',array(
				$table,
				$field,
				$src,
				$replace,
				$where
			)
		);
	}

	public function real_escape_string ($unescaped_string, $link_identifier=false){
		$this->t_start();
		$res = @mysqli_real_escape_string ($link_identifier ? $link_identifier : $this->db, $unescaped_string);
		$this->t_end();
		return $res ;
	}

	public function select_db($db_name, $link_identifier=false){
		$this->t_start();
		$res = @mysqli_select_db($link_identifier ? $link_identifier : $this->db, $db_name);
		$this->t_end();
		return $res ;
	}

	/**
	* solo per serie di queries in cui non occorre usare risultati intermedi !!!!!!!!!!!!!!
	* SOLO SE LE TABELLE COINVOLTE SONO INNOdb !!!!!
	*/
	public function sql_array_transaction($queries) {
		$db = db::get_instance();
		$this->query('SET autocommit=0;');
		$this->query('START TRANSACTION;');
		//flag riuscita
		$ret= TRUE;
		$log  = Factory::get('log');

		foreach ($queries as $q) {
			if ($ret) {
				$ret = $ret && $this->query($q);
			}
		}
		$this->query( ($ret!=FALSE)? 'COMMIT; ':'ROLLBACK; ');
		return $ret;
	}

	public function sql2assoc($sql, $pars = array(), $key=FALSE, $debug=false){
		return $this->sql2x($sql, $pars, $key, $debug, true);
	}
	public function sql2array($sql, $pars = array(), $key=FALSE, $debug=false){
		return $this->sql2x($sql, $pars, $key, $debug, false);
	}
	
	/**
	*	gli passi un sql e ti restituisce l'array dei risultati
	*/
	private function sql2x ($sql, $pars = array(), $key=FALSE, $debug=false, $assoc = false) {
		$res = $this->query($sql, $pars, false, $debug);
		$func = 'fetch_' . ($assoc ? 'assoc' : 'array');
		$ret = array();

		if ($this->num_rows($res) > 0) {
			$ret =array();
			while ($row = $this->$func($res)) {
				if ($key) {
					$ret[$row[$key]] = $row;
				} else {
					$ret[] = $row;
				}
			}
		}
		return $ret;
	}

	public function there_are_some($sql){
		$res = $this->query($sql);
		$num = $this->num_rows($res);
		return $num > 0;
	}

	public function remove_duplicates($table, $filter, $unique_fields){
		$queries = array('
			CREATE TABLE temporary_table as
				SELECT * FROM `'.$table.'` '.$filter.';',
			'ALTER TABLE `'.$table.'` ADD UNIQUE myraise (`'.implode('`,`',$unique_fields).'`);',
			'RENAME TABLE temporary_table TO '.$table.';'
		);
		return $this->sql_array_transaction($queries);
	}

	/*
	 * tabelle come subscriptions e masters_subscriptions estrae la conta
	 */
	// subscriptions, masters_subscriptions, id_subscription, fk_subscription, label,
	public function count_assoc($table, $assoc, $table_pk, $assoc_fk, $select_field, $where="1", $count_alias = 'conta'){
		$table_alias = substr($table,0,5);
		$assoc_alias = substr($assoc,0,5);
		$query = '
			SELECT ?.?,
			       COUNT(?) as ?
			  FROM `?` as ?
		 LEFT JOIN `?` as ?
				ON (?.?=?.?)
			 WHERE ?
		  GROUP BY ?
		';
		$res = $this->sql2assoc(
			$query,
			array(
				$table_alias, $select_field,
				$assoc_fk, $count_alias,
				$table,	$table_alias,
				$assoc, $assoc_alias,
				$table_alias, $table_pk, $assoc_alias, $assoc_fk,
				$where, $table_pk
			)
		);

		return utility::array_makeassoc($res, 'label', 'conta');
	}

	/*
	 * Return the sqlcode to get arbitrary indexed split results
	 */
	public function field_split($field, $separator, $num, $alias="split_"){
		$patt = 'SUBSTRING_INDEX(%field%, "'.$separator.'", %index%)';
		$end=false;
		$i = 0;
		$split = array();

		while ($i < $num) {
			$split[] = str_replace(
				array('%field%','%index%'),
				array(
					str_replace(
						array('%field%','%index%'),
						array($field, $i + 1),
					 	$patt
					),
					'-1'
				),
				$patt
			).' AS '.(is_array($alias) ? $alias[$i++] : ($alias.$i++));
		}
		return implode(',',$split);
	}



	public function replace($table, $field, $what, $with){
		return $this->query('UPDATE ? SET ? = REPLACE(?,"?","?");', array($table, $field, $field, $what, $with));
	}
	
	public function optimize_table($table){
		return $this->query('OPTIMIZE TABLE `?` ',array($table));
	} 
}

