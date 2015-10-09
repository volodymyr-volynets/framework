<?php

class executioner {

	public static $steps = array();

	public function action_index() {
		$steps = self::$steps;
		// error verification
		if (empty($steps)) {
			throw new Exception('No steps?');
		}
		// building form first
		$input = request::input();
		$ms = '<div class="form">';
			$ms.= '<table width="100%" class="simple">';
			if (sizeof($steps)>1) {
				$ms.= '<tr><td width="1%">' . h::radio(array('name'=>'execute_step','class'=>'radio', 'value'=>'all', 'checked'=>(@$input['execute_step']=='all' ? true : false))) . '</td><td>All steps</td></tr>';
			}
			foreach ($steps as $k=>$v) {
				$ms.= '<tr><td width="1%">' . h::radio(array('name'=>'execute_step','class'=>'radio', 'value'=>$k, 'checked'=>(@$input['execute_step']==$k ? true : false))) . '</td><td>Step ' . $k . ' ' . $v['name'] . '</td></tr>';
				// appending extra html
				if (!empty($v['html'])) {
					$ms.= '<tr><td width="1%"></td><td>' . $v['html'] . '</td></tr>';
				}
			}
			$ms.= '</table>';
			$ms.= '<br />';
			$ms.= h::submit(array('name'=>'submit', 'class'=>'button yellow'));
		$ms.= '</div>';
		$ms = h::form(array('name'=>'executioner', 'value'=>$ms));
		echo h::frame($ms, 'simple');

		// executing steps
		if (!empty($input['execute_step'])) {
			$time_start = time();
			//echo '<hr />';
			foreach ($steps as $k=>$v) {
				// double check
				if (!isset($v['execute'])) {
					throw new Exception('execute?');
				}
				// executing
				if ($input['execute_step']=='all' || $input['execute_step']==$k) {
					echo '<hr /><h4>Step [' . $k . '. ' . $v['name'] . ']: </h4>';
					// redirecting request if set
					$params = array();
					if (!empty($input['redirect_request'][$k])) {
						$params = $input['redirect_request'][$k];
					} else if (!empty($v['params'])) {
						$params = $v['params'];
					}

					// we will be keeping alive
					alive::start();
					$result2 = call_user_func_array($v['execute'], $params);
					alive::stop();

					// processing result
					if (is_scalar($result2)) {
						echo $result2;
					} else if (is_array($result2)) {
						if (@$result2['hint']) {
							layout::add_message($result2['hint'], 'info');
						}
						if (@$result2['error']) {
							layout::add_message($result2['error'], 'error');
							// important: we terminate batch
							echo 'Batch terminated due to error!';
							break;
						}
					}
				}
			}
			// time
			$time_end = time();
			echo '<hr />';
			echo '<b>Run time: ' . ($time_end - $time_start) . 'sec</b>';
		}
	}
}