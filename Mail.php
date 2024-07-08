<?php

class Mail {

	/**
	 * Send an email
	 *
	 * Usage example:
	 *
	 * 	$result = mail::send([
	 * 		'to' => 'test@localhost',
	 *		'cc' => 'cc@localhost',
	 *		'bcc' => 'bcc@localhost',
	 *		'subject' => 'test subject',
	 * 		'message' => 'test message',
	 * 		'attachments' => [
	 *	 		['path' => 'path to file', 'name' => 'test.txt'],
	 * 			['data' => '!!!data!!!', 'name' => 'test.txt', 'type' => 'plain/text']
	 * 		]
	 * 	]);
	 *
	 * @param array $options
	 * @return array
	 */
	public static function send(array $options) : array {
		$result = [
			'success' => false,
			'error' => []
		];
		// mail delivery first
		$class = \Application::get('flag.global.mail.delivery.submodule', ['class' => true]);
		if (empty($class)) {
			Throw new \Exception('You need to specify mail delivery submodule');
		}
		// check if backend has been enabled
		if (!\Application::get($class, ['submodule_exists' => true])) {
			Throw new \Exception('You must enable ' . $class . ' first!');
		}
		$mail_delivery_object = new $class();
		$temp = $mail_delivery_object->send($options);
		if (!$temp['success']) {
			array_merge3($result['error'], $temp['error']);
		} else {
			$result['success'] = true;
		}
		// log
		$notifications = [];
		if (isset($options['notification_data'])) {
			$notifications = ['notifications' => $options['notification_data']];
		}
		\Log::add([
			'type' => 'Mail',
			'only_chanel' => 'default',
			'message' => 'Mail sent: [' . ($options['notification_name'] ?? 'Direct Mail') . '] ' . $options['subject'],
			'affected_rows' => $result['error'] ? 0 : 1,
			'error_rows' => $result['error'] ? 1 : 0,
			'trace' => $result['error'] ? \Object\Error\Base::debugBacktraceString(null, ['skip_params' => true]) : null,
			'affected_users' => ['email' => $options['to'], 'user_id' => $options['user_id'] ?? null, 'notification_code' => $options['notification_code'] ?? null],
		] + $notifications);
		return $result;
	}

	/**
	 * Send simple
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 */
	public static function sendSimple($to, string $subject, string $message) : array {
		return self::send(['to' => $to, 'subject' => $subject, 'message' => $message]);
	}
}