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
	 *	Perform a query using real MySQLi prepared statements
	 *
	 * @param string $sql
	 * @param array  $pars      parameters to bind (use ? in $sql)
	 * @param bool   $preparsed deprecated, kept for compatibility
	 * @param bool   $debug     set to true to view parsed query
	 * @param bool   $doit      set to false to skip execution and return true
	 * @return mixed
	 */
	public function query($sql='', $pars = array(), $debug=false){
		$this->t_start();

		if ($debug) echo'<br />Query:'.date('H:i:s').'<br /><strong>'.$sql.'</strong><br />Params: '.print_r($pars, true).'<br />--------------------------/<br />';

		if (trim($sql) == '') {
			$this->t_end();
			return false;
		}

		$pars = is_array($pars) ? array_values($pars) : array();

		// No params — simple query for backward compatibility
		if (count($pars) == 0) {
			$res = mysqli_query($this->db, $sql);
			$this->t_end();
			return $res;
		}

		$stmt = mysqli_prepare($this->db, $sql);
		if (!$stmt) {
			if ($debug) echo 'Prepare failed: '.mysqli_error($this->db).'<br />';
			$this->t_end();
			return false;
		}

		$types = '';
		$bindValues = array();
		foreach ($pars as $pv) {
			if (is_int($pv) || is_bool($pv)) {
				$types .= 'i';
				$pv = is_bool($pv) ? ($pv ? 1 : 0) : $pv;
			} elseif (is_float($pv) || is_double($pv)) {
				$types .= 'd';
			} else {
				$types .= 's';
			}
			$bindValues[] = $pv;
		}

		if (count($bindValues) > 0) {
			$refs = array();
			$refs[] = $types;
			foreach ($bindValues as $key => $value) {
				$refs[] = &$bindValues[$key];
			}
			call_user_func_array(array($stmt, 'bind_param'), $refs);
		}

		$success = mysqli_stmt_execute($stmt);
		if (!$success) {
			if ($debug) echo 'Execute failed: '.mysqli_stmt_error($stmt).'<br />';
			mysqli_stmt_close($stmt);
			$this->t_end();
			return false;
		}

		$res = mysqli_stmt_get_result($stmt);
		mysqli_stmt_close($stmt);

		// For INSERT/UPDATE/DELETE get_result returns false, return true on success
		if ($res === false) {
			$this->t_end();
			return true;
		}

		$this->t_end();
		return $res;
	}

	/**
	 * Escape a MySQL identifier (table or column name) for safe concatenation.
	 * Prepared statements cannot bind identifiers, only values.
	 */
	public function escape_identifier($identifier) {
		return '`' . str_replace('`', '``', $identifier) . '`';
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
			'UPDATE '.$this->escape_identifier($table).'
				SET '.$this->escape_identifier($field).' = REPLACE('.$this->escape_identifier($field).', ?, ?)
			  WHERE '.$where.';',
			array($src, $replace)
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
			SELECT '.$this->escape_identifier($table_alias).'.'.$this->escape_identifier($select_field).',
			       COUNT('.$this->escape_identifier($assoc_fk).') as '.$this->escape_identifier($count_alias).'
			  FROM '.$this->escape_identifier($table).' as '.$this->escape_identifier($table_alias).'
			 LEFT JOIN '.$this->escape_identifier($assoc).' as '.$this->escape_identifier($assoc_alias).'
				ON ('.$this->escape_identifier($table_alias).'.'.$this->escape_identifier($table_pk).'='.$this->escape_identifier($assoc_alias).'.'.$this->escape_identifier($assoc_fk).')
			 WHERE '.$where.'
		  GROUP BY '.$this->escape_identifier($table_pk).'
		';
		$res = $this->sql2assoc($query);

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
		return $this->query(
			'UPDATE '.$this->escape_identifier($table).' SET '.$this->escape_identifier($field).' = REPLACE('.$this->escape_identifier($field).',?,?);',
			array($what, $with)
		);
	}

	public function optimize_table($table){
		return $this->query('OPTIMIZE TABLE '.$this->escape_identifier($table));
	}
}

