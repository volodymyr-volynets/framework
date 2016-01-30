<?php

class library {

	/**
	 * Add library to the application
	 * 
	 * @param string $library
	 */
	public static function add($library) {
		$connected = application::get('flag.global.library.' . $library . '.connected');
		if (!$connected) {
			factory::submodule('flag.global.library.' . $library . '.submodule')->add();
			application::set('flag.global.library.' . $library . '.connected', true);
		}
	}
}