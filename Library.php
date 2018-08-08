<?php

class Library {

	/**
	 * Add library to the application
	 * 
	 * @param string $library
	 */
	public static function add($library) {
		$connected = Application::get('flag.global.library.' . $library . '.connected');
		if (!$connected) {
			Factory::submodule('flag.global.library.' . $library . '.submodule')->add();
			Application::set('flag.global.library.' . $library . '.connected', true);
		}
	}
}