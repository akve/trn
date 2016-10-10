<?php

class EMAIL
{
	public function MandrillEmail()
	{
		require_once(BASE_PATH.'/src/Mandrill.php'); 
		//$GLOBALS['mandrill'] = new Mandrill('YJ-mxHYF9D_Zg7BYTvQebw');
		$GLOBALS['mandrill'] = new Mandrill('WuHwRknEJGH6K5bRQcip-A');
	}

	public function AddAttachment($attachment) {
		$this->attachment = $attachment;
	}

	public function SetEmailTemplate($template, $vars)
	{
		# first thing we do is get the template file
		$body = WPParseAndGet("emails/{$template}", true);

		# now we replace all the template vars with whatever get's input
		foreach($vars as $var => $value)
		{
			$body = str_replace("%%EMAIL_{$var}%%", $value, $body);
		}

		return $body;
	}

	public function Send($to, $body, $subject, $from, $fromname = "The Trust Review Network", $mandrill=true)
	{
		if ($mandrill)
			$this->MandrillEmail();
		
		if (isset($GLOBALS['mandrill'])) {
			$message = array(
				'subject' => $subject,
				'from_email' => $from,
				'from_name' => $fromname,
				'html' => $body,
				'to' => array(array('email' => $to, 'name' => '')),
				//'signing_domain' => '',
			);

			if (isset($this->attachment) && is_array($this->attachment) && !empty($this->attachment)) {
				$message['attachments'] = $this->attachment;
			}

			$async = false;
			$ip_pool = 'Main Pool';
			$mailed = $GLOBALS['mandrill']->messages->send($message, $async, $ip_pool);
			$GLOBALS['mailed'] = $mailed;

			if (!isset($mailed['reject_reason']) || $mailed['reject_reason'] == '') {
				$mailsent = true;
			} else {
				$mailsent = false;
				$failmail = $result;
			}

			return $mailsent;
		} else {
			return false;
		}
	}

}