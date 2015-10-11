<?php

class controller_index {

	public $title = 'Home';
	public $acl = array(
		'login' => false,
		'tokens' => array()
	);

	public function action_index() {
		echo "<h3>Welcome to index controller!</h3>";
	}
}