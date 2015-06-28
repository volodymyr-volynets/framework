<?php

class sendmail {
	
	/**
	 * Send an email, usage example:
	 * 
	 * 	$m = model_mail::send(array(
	 * 		'to' => 'test@localhost',
	 * 		'message' => 'test message',
	 * 		'attachments' => array(
	 *	 		array('filepath'=>'../public_html/img/icons/calendar16.png', 'filename'=>'calendar16.png'),
	 * 			array('filedata'=>'!!!data!!!', 'filename'=>'calendar16.png')
	 * 		)
	 * 	));
	 * 
	 * @param unknown_type $options
	 * @throws Exception
	 * @return Ambigous <string, multitype:boolean multitype: >
	 */
	public static function send($options) {
		$result = array(
			'success' => false,
			'error' => array()
		);
		
		// create a new output buffer, this function echoes on the screen
		ob_start();
		
		$mail = new PHPMailer();
		
		// Set mailer to use SMTP
		/*
		$smtp = application::get(array('mail', 'smtp'));
 		if (!empty($smtp)) {
 			$mail->IsSMTP();
 			$mail->Host = $smtp['host'];
 			$mail->Port = $smtp['port'];
 		}
 		*/
		
		// company mail from & reply to fields
		$company_email = session::get('company_email');
		if (!empty($company_email)) {
			$company_name = session::get('company_name');
			$mail->SetFrom($company_email, $company_name);
		} else {
			$sender = application::get(array('mail', 'sender'));
			if (!empty($sender)) {
				$mail->SetFrom($sender['email'], $sender['name']);
			}
		}
		
		// override from field
		if (!empty($options['from'])) {
			$mail->SetFrom($options['from'], @$options['from_name']);
		}
		
		// to fields
		if (empty($options['to'])) {
			Throw new Exception('to field?');
		}
		$options['to'] = is_array($options['to']) ? $options['to'] : array($options['to']);
		foreach ($options['to'] as $v) $mail->AddAddress($v);
		
		// cc field
		if (!empty($options['cc'])) {
			$options['cc'] = is_array($options['cc']) ? $options['cc'] : array($options['cc']);
			foreach ($options['cc'] as $v) $mail->AddCC($v);
		}
		
		// bcc field
		if (!empty($options['bcc'])) {
			$options['bcc'] = is_array($options['bcc']) ? $options['bcc'] : array($options['bcc']);
			foreach ($options['bcc'] as $v) $mail->AddBCC($v);
		}
		
		// Set word wrap to 50 characters
		$mail->WordWrap = 50;
		
		// process attachements
		if (!empty($options['attachments'])) {
			foreach ($options['attachments'] as $k=>$v) {
				if (isset($v['filepath'])) {
					$mail->AddAttachment($v['filepath'], @$v['filename']);
				} else if (isset($v['filedata']) && !empty($v['filename'])) {
					$mail->AddStringAttachment($v['filedata'], $v['filename']);
				} else {
					Throw new Exception('Mail - unknown attachement type!');
				}
			}
		}
		
		// Set email format to HTML
		$mail->IsHTML(true);
		
		$mail->Subject = @$options['subject'];
		$mail->Body = @$options['message'];
		$mail->AltBody = @$options['text_message'] ? $options['text_message'] : strip_tags($options['message']);
		
		$flag = $mail->Send();
		$buffer = ob_get_clean();
		
		if(!$flag) {
			$result['error'][] = 'Message could not be sent. ' . $mail->ErrorInfo;
		} else {
			$result['success'] = true;
		}
		return $result;
	}
	
	/**
	 * Send simple
	 * 
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 */
	public static function send_simple($to, $subject, $message) {
		return self::send(array('to'=>$to, 'subject'=>$subject, 'message'=>$message));
	}
}