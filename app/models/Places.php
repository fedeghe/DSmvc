<?php
class Places {
	public static function getAll() {
		$db = mydb::getInstance();
		$res = $db->query("
			SELECT ?
			  FROM `?`
			 WHERE fk_group = ?
		", array(
            '*',
            'places',
            $_SESSION['user']['fk_group']
        ),true, true);
        return json_encode($db->fetch_all_assoc($res));
	}
}