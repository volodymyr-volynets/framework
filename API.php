<?php

class API {

	/**
	 * Send
	 *
	 * @param string $api
	 * @param string $method
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	public static function send(string $api, string $method, array $data) : array {
		// mail delivery first
		$class = \Application::get("apis.{$api}.submodule", ['class' => true]);
		if (empty($class)) {
			Throw new Exception('You need to specify API delivery submodule');
		}
		// check if backend has been enabled
		if (!Can::systemModuleExists('UA')) {
			Throw new Exception('You must enable UA module first!');
		}
		// initialize the object
		$object = new $class();
		return $object->send($method, $data, \Application::get("apis.{$api}"));
	}
}