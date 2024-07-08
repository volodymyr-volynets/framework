<?php

namespace Object\Controller;
abstract class API extends \Object\Controller {

	/**
	 * ACL settings
	 *
	 * Permissions only
	 *
	 * @var array
	 */
	public $acl = [
		'public' => true,
		'authorized' => true,
		'permission' => true
	];

	/**
	 * API Content Type
	 *
	 * @var string
	 */
	public $api_content_type;

	/**
	 * API Input
	 *
	 * @var array
	 */
	public $api_input = [];

	/**
	 * Content type
	 *
	 * @var array
	 */
	public static $content_types = [
		'json' => 'application/json',
		'xml' => 'application/xml'
	];

	public function __construct() {
		parent::__construct();
		// detect input type
		$this->api_input = \Request::input(null, true);
		// raw data from the request
		$raw = file_get_contents('php://input');
		if (!empty($raw)) {
			// json
			if (is_json($raw)) {
				$this->api_input = array_merge_hard($this->api_input, json_decode($raw, true));
			} else if (is_xml($raw)) {
				$xml = simplexml_load_string($raw);
				$this->api_input = array_merge_hard($this->api_input, xml2array($xml));
			}
		}
		// content type
		$this->api_content_type = \Application::get('flag.global.__content_type');
		if (!in_array($this->api_content_type, self::$content_types)) {
			$this->api_content_type = 'application/json';
		}
	}

	/**
	 * Get structure
	 */
	abstract public function actionGetStructure();

	/**
	 * Handle output
	 *
	 * @param mixed $result
	 */
	public function handleOutput($result) {
		// We allow CORS by refferer.
		if(!empty($this->api_input['cors'])) {
			header('Access-Control-Allow-Origin: ' . rtrim($_SERVER['HTTP_REFERER'], '/'));
			header('Access-Control-Allow-Methods: POST');
			header('Access-Control-Allow-Headers: Accept, Content-Type, Authorization');
		}
		switch ($this->api_content_type) {
			case 'application/xml':
				\Layout::renderAs($result, 'application/xml');
				break;
			case 'application/json':
			default:
				\Layout::renderAs($result, 'application/json');
		}
	}
}