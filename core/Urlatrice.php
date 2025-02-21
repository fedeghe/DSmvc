<?php
defined('DSMVC') OR die('No direct access allowed!');

function getHash( $target, $length = 8 ){
	// Create a raw binary sha256 hash and base64 encode it.
	$hash_base64 = base64_encode( hash( 'sha256', $target, true ) );
	// Replace non-urlsafe chars to make the string urlsafe.
	$hash_urlsafe = strtr( $hash_base64, '+/', '-_' );
	// Trim base64 padding characters from the end.
	$hash_urlsafe = rtrim( $hash_urlsafe, '=' );
	// Shorten the string before returning.
	return substr( $hash_urlsafe, 0, $length );
}

function getLocation($ip) {
	$json = file_get_contents("http://ipinfo.io/$ip/geo");
	// $json = json_decode($json, true);
	return html_entity_decode($json);
}
// https://urlo.com:8890/m/epYduWFd/1
// https://urlo.com:8890/m/47DEQpj8/1

class urlatrice{
	private $db;

	public function __construct(){
		$this->db = Factory::get('mydb');
	}
	

	public function set($userId, $targetId, $alias, $type){

		// first get the target url
		
		$r = $this->db->query('
			SELECT *
			FROM `?`
			WHERE id = ? AND fkUser = ?;
		',array(
			'targets',
			$targetId,
			$userId,
		));
		$target = $this->db->fetch_row($r);
		// utility::print_d($target, true);
		
		if(is_array($target)){
			$urlHash = getHash($target['url'].rand());
			// check one is not already there
			$already = $this->db->fetch_assoc(
				$this->db->query('
				   SELECT *
					 FROM `?`
					WHERE fkTarget = ?
					  AND fkUser = ?
					  AND alias = "?"
					  AND type="'.$type.'";
				',array(
					'shorts',
					$targetId,
					$userId,
					$alias,
				))
				);
			if(!is_array($already)){
				//we can create it
				$this->db->query('
						INSERT IGNORE
						INTO `?`(fkUser, type, urlHash, fkTarget, alias)
						VALUES(?, "?", "?", "?", "?")
					',
					array(
						'shorts',
						$userId,
						$type,
						$urlHash,
						$targetId,
						$alias
					)
				);
				return $urlHash;
			} else {
				return $already['urlHash'];
			}
		} else {
			return false;
		}
		
	}


	public function get($user, $urlHash, $type){
		$ip = $_SERVER['REMOTE_ADDR'];
		$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
		$city = $details->city;

		$urlHash = str_replace('`','',$urlHash);
		$ret = $this->db->query('
				SELECT `trg`.id,
						`trg`.url as url,
						`lnk`.id as linkId
				  FROM `shorts` as lnk
				  JOIN `targets` as trg
				    ON (lnk.fkTarget = trg.id)
				 WHERE lnk.fkUser = ? AND lnk.urlHash = "?" AND lnk.active = 1 
				   AND lnk.type="'.$type.'";
			',array(
				$user,
				$urlHash
			)
		);

		$row = $this->db->fetch_assoc($ret);

		if( !isset( $row['url'] ) || trim($row['url']) == ''  ){
			return false;
		}
		$this->db->query('
			INSERT IGNORE
			  INTO `?`(fkLink, fkUser, location, userAgent, ip)
			VALUES (?, ?, "?", "?", "?")
		', array(
			'stats',
			$row['linkId'],
			$user,
			$city,
			$_SERVER['HTTP_USER_AGENT'],
			$ip
		));
		
		return $row['url'];
	}
}