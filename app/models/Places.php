<?php
class Places {
	public static function getAll() {
		$db = mydb::getInstance();
        if(!$db) return Array();
		$res = $db->query("
			SELECT ? FROM `?`
		", array(
            '*',
            'hs'
        ),true, false);
        // return json_encode($db->fetch_all_assoc($res));
        return $db->fetch_all_assoc($res);
	}
    public static function getSome() {
		$db = mydb::getInstance();
		$res = $db->query("
			SELECT ? FROM `?` LIMIT 0, 5
		", array(
            '*',
            'hs'
        ),true, false);
        // return json_encode($db->fetch_all_assoc($res));
        return $db->fetch_all_assoc($res);
	}
}