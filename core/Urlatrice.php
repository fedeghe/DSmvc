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


function isTrackable($userAgent) {
	$r = true;
    $black_list = array(
      "whatsapp",
      "facebot",
      "twitterbot"
    );
	foreach ($black_list as $value) {
		$r = $r & (stripos($userAgent, $value) === false);
	}
    return $r;
}


class urlatrice{
	private $db;

	public function __construct(){
		$this->db = Factory::get('mydb');
	}

	private $black_list = array(
		"whatsapp",
		"facebot",
		"twitterbot"
	  );

	private function isTrackable($userAgent) {
		$r = true;
		foreach ($this->black_list as $value) {
			$r = $r & (stripos($userAgent, $value) === false);
		}
		return $r;
	}

	private function justAdded($linkId, $userAgent, $ip) {
		$result = $this->db->query('
			SELECT COUNT(*) as count
			FROM `stats`
			WHERE fkLink = ?
			  AND userAgent = ?
			  AND ip = ?
			  AND date >= NOW() - INTERVAL ? MINUTE
		', array($linkId, $userAgent, $ip, DEBOUNCE_MINUTES));

		$row = $this->db->fetch_assoc($result);
		return $row['count'] > 0;
	}

	public function set($userId, $targetId, $alias, $type){
		// first get the target url
		$r = $this->db->query('
			SELECT *
			FROM `targets`
			WHERE id = ? AND fkUser = ?;
		',array(
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
					 FROM `shorts`
					WHERE fkTarget = ?
					  AND fkUser = ?
					  AND alias = ?
					  AND type=?;
				',array(
					$targetId,
					$userId,
					$alias,
					$type,
				))
			);

			if(!is_array($already)){
				//we can create it
				$this->db->query('
						INSERT IGNORE
						INTO `shorts`(fkUser, type, urlHash, fkTarget, alias)
						VALUES(?, ?, ?, ?, ?)
					',
					array(
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

	public function resolve($user, $urlHash, $type) {
		$urlHash = str_replace('`','',$urlHash);
		$ret = $this->db->query('
				SELECT `trg`.id,
						`trg`.url as url,
						`lnk`.id as linkId
				  FROM `shorts` as lnk
				  JOIN `targets` as trg
				    ON (lnk.fkTarget = trg.id)
				 WHERE lnk.fkUser = ?
				   AND lnk.urlHash = ?
				   AND lnk.active = 1
				   AND lnk.type=?;
			', array($user, $urlHash, $type)
		);
		$row = $this->db->fetch_assoc($ret);

		if (!isset($row['url']) || trim($row['url']) == '') {
			return false;
		}
		return array('url' => $row['url'], 'linkId' => $row['linkId']);
	}

	public function track($linkId, $user) {
		$ip = $_SERVER['REMOTE_ADDR'];
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$location = 'local';
		$latitude = 0;
		$longitude = 0;

		if (DB_NAME != 'urlo'){
			if (defined('IP_GEOLOCATION_API_TOKEN')) {
				$details = json_decode(
					@file_get_contents("https://api.ipgeolocation.io/v3/ipgeo?apiKey=".IP_GEOLOCATION_API_TOKEN."&ip={$ip}")
				);
				if ($details && isset($details->location)) {
					$location = $details->location->country_name.' | '. $details->location->city;
					$latitude = $details->location->latitude;
					$longitude = $details->location->longitude;
				}
			} else if(defined('IPINFO_TOKEN')){
				$details = json_decode(@file_get_contents("https://ipinfo.io/lite/{$ip}?token=".IPINFO_TOKEN));
				if ($details && isset($details->country)) {
					$location = $details->country;
				}
			}
		}

		if (
			$this->isTrackable($userAgent) &&
			!$this->justAdded($linkId, $userAgent, $ip)
		){
			$this->db->query('
				INSERT IGNORE
				INTO `stats`(fkLink, fkUser, location, userAgent, ip, lat, lon)
				VALUES (?, ?, ?, ?, ?, ?, ?)
			', array(
				$linkId,
				$user,
				$location,
				$userAgent,
				$ip,
				$latitude,
				$longitude
			));
		}
	}

	public function get($user, $urlHash, $type){
		$info = $this->resolve($user, $urlHash, $type);
		if (!$info) {
			return false;
		}
		$this->track($info['linkId'], $user);
		return $info['url'];
	}
}