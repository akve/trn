<?php

class TRNEMAIL
{
	private $key = "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";

	public function MandrillEmail()
	{
		require_once(BASE_PATH.'/src/Mandrill.php'); 
		$GLOBALS['mandrill'] = new Mandrill('YJ-mxHYF9D_Zg7BYTvQebw');
	}

	public function SetMailer()
	{
		require_once(BASE_PATH."/PHPMailer_5.2.1/class.phpmailer.php");
		$GLOBALS['mail'] = new PHPMailer(); // defaults to using php "mail()"

		$GLOBALS['mail']->IsSMTP(); // telling the class to use SMTP
		$GLOBALS['mail']->Host      = ""; // SMTP server
		$GLOBALS['mail']->SMTPAuth  = true;                  // enable SMTP authentication
		$GLOBALS['mail']->Host      = ""; // sets the SMTP server
		$GLOBALS['mail']->Port      = 25;                    // set the SMTP port for the GMAIL server
		$GLOBALS['mail']->Username  = ""; // SMTP account username
		$GLOBALS['mail']->Password  = "";        // SMTP account password
		$GLOBALS['mail']->CharSet 	= 'utf-8';
	}

	public function AddAttachment($attachment) {
		$this->attachment = $attachment;
	}

	public function SetEmailTemplate($template, $vars)
	{
		# first thing we do is get the template file
		$body = file_get_contents(EMAIL_TEMPLATE_PATH."{$template}.html");

		# now we replace all the template vars with whatever get's input
		foreach($vars as $var => $value)
		{
			$body = str_replace("%%EMAIL_{$var}%%", $value, $body);
		}

		return $body;
	}

	public function InitiateSendGrid()
	{
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		echo BASE_PATH.'/modules/SendGrid/sendgrid-php.php';
		require_once(BASE_PATH.'/modules/SendGrid/sendgrid-php.php');
		//$this->sendgrid = new SendGrid($this->key);
		//$this->email    = new SendGrid\Email();
	}

	public function Send($to, $body, $subject, $from = "noreply@trustreviewnetwork.com", $fromname = "Trust Review Network")
	{
		# initiate the sendgrid class
		$this->InitiateSendGrid();

		# create the email object
		$this->email->addTo($to)
					->setFrom($from)
					->setFromName($fromname)
					->setSubject($subject)
					->setHtml($body);

		$response = $this->sendgrid->send($this->email);

		return $response;
	}

	public function SendMandrill($to, $body, $subject, $from, $mandrill=true)
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

			if ($mailed['reject_reason'] == '') {
				$mailsent = true;
			} else {
				$mailsent = false;
				$failmail = $result;
			}

			return $mailsent;
		} else {
			$this->SetMailer();

			$GLOBALS['mail']->SetFrom($from);
			$GLOBALS['mail']->AddReplyTo($from, $name);
			$GLOBALS['mail']->AddAddress($to, "");
			$GLOBALS['mail']->MsgHTML($body);

			if ($GLOBALS['mail']->Send()) {
				return true;
			} else {
				return false;
			}
		}
	}

}