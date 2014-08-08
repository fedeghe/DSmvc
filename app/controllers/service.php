<?php

class service extends Controller{

	public function action_getUsers(){

		$a = func_get_args();
		
		//debug($a);

		$users = array(
			array(
				'name' => 'Federico',
				'surname' => 'Ghedina'
			),
			array(
				'name' => 'Cristiana',
				'surname' => 'Patti'
			),
			array(
				'name' => 'Gabriele',
				'surname' => 'Ghedina'
			),
			array(
				'name' => 'Francesca',
				'surname' => 'Ghedina'
			)
		);

		if (count($a[0])) array_push($users, $a[0]);

		Response::send(json_encode($users));
	}
}
