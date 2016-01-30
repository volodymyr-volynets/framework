<?php

class numbers_framework_controller_error {

	/**
	 * This would process error message sent from frontend
	 */
	public function action_index() {
		$input = request::input();
		if (!empty($input['token'])) {
			$crypt = new crypt();
			$token_data = $crypt->token_validate($input['token'], 1, true);
			if (!($token_data === false || $token_data['id'] !== 'general')) {
				$input['data'] = json_decode($input['data'], true);
				error_base::error_handler('javascript', $input['data']['message'], $input['data']['file'], $input['data']['line']);
			}
		}
		// we must set content type in application
		application::set('flag.global.__content_type', 'image/png');
		header("Content-Type: image/png");
		echo file_get_contents(__DIR__ . '/error.png');
		exit;
	}
}