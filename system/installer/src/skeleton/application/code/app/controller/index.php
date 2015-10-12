<?php

class controller_index {

	public $title = 'Home';
	public $acl = array(
		'login' => false,
		'tokens' => array()
	);

	public function action_index() {
		echo "<h3>This is index controller</h3>";
	}
}