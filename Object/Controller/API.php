<?php

namespace Object\Controller;
abstract class API {

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
	public $content_type;

	/**
	 * API Input
	 *
	 * @var array
	 */
	public $input = [];

	/**
	 * API values (processsed input)
	 *
	 * @var array
	 */
	public $values = [];

	/**
	 * API model
	 *
	 * @var string
	 */
	public $model;

	/**
	 * Validator
	 *
	 * @var \Validator
	 */
	public $validator;

	/**
	 * API object
	 *
	 * @var object
	 */
	protected $object;

	/**
	 * API pk
	 *
	 * @var array
	 */
	public $pk;

	/**
	 * API Route options
	 *
	 * @var array
	 */
	public $route_options = [];

	/**
	 * API group
	 *
	 * @var array
	 */
	public $group = [];

	/**
	 * API name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = 'V1';

	/**
	 * Content type
	 *
	 * @var array
	 */
	public static $content_types = [
		'json' => 'application/json',
		'xml' => 'application/xml'
	];

	/**
	 * Constructor
	 *
	 * @param array $options
	 * 		skip_constructor_loading
	 */
	public function __construct(array $options = []) {
		if (!empty($this->model)) {
			/** @var \Object\Table $this->api_object */
			$this->object = \Factory::model($this->model, true, [['skip_db_object' => $options['skip_constructor_loading'] ?? true]]);
		}
		// pk
		if (empty($this->pk)) {
			$this->pk = $this->object->pk;
			// we need to unset tenant
			if ($this->object->tenant) {
				$temp = array_search($this->object->column_prefix . 'tenant_id', $this->pk, true);
				if ($temp !== false) {
					unset($this->pk[$temp]);
				}
			}
			$this->route_options['pk'] = $this->pk;
		} else {
			$this->route_options['pk'] = $this->pk;
		}
		// skip constructor loading
		if (!empty($options['skip_constructor_loading'])) {
			return;
		}
		//parent::__construct();
		// detect input type
		$this->input = \Request::input();
		// raw data from the request
		$raw = file_get_contents('php://input');
		if (!empty($raw)) {
			// json
			if (is_json($raw)) {
				$this->input = array_merge_hard($this->input, json_decode($raw, true));
			} else if (is_xml($raw)) {
				$xml = simplexml_load_string($raw);
				$this->input = array_merge_hard($this->input, xml2array($xml));
			}
		}
		// content type
		$this->content_type = \Application::get('flag.global.__content_type');
		if (!in_array($this->content_type, self::$content_types)) {
			$this->content_type = 'application/json';
		}
	}

	/**
	 * Handle output
	 *
	 * @param mixed $result
	 */
	public function handleOutput($result) {
		// We allow CORS by refferer.
		if(!empty($this->input['cors'])) {
			header('Access-Control-Allow-Origin: ' . rtrim($_SERVER['HTTP_REFERER'], '/'));
			header('Access-Control-Allow-Methods: POST');
			header('Access-Control-Allow-Headers: Accept, Content-Type, Authorization');
		}
		switch ($this->content_type) {
			case 'application/xml':
				\Layout::renderAs($result, 'application/xml');
				break;
			case 'application/json':
			default:
				\Layout::renderAs($result, 'application/json');
		}
	}
}