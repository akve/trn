<?php
/**
 * MailChimp API
 */

class MAILCHIMPUSER
{
	public function __construct()
	{
		require_once(BASE_PATH."/modules/MailChimp/MailChimp.php");
		$this->mc = new MailChimp('a7d88d6808fe1b3093a92cad73a29efa-us2');
	}

	public function AddSubscriber($listid, $email, $user)
	{
		# create the correct array
		$merg_vars = array(
			"email_address" => $email,
			"status" => "subscribed"
		);
			
		$result = $this->mc->post("lists/$listid/members", $merg_vars);

		# now let's get the subscriber and update the name
		$subscriber = $this->mc->subscriberHash($email);

		$update = array(
			"merge_fields" => array(
				"FNAME" => $user['fname'],
				"LNAME" => $user['lname'],
			)
		);

		$result = $this->mc->patch("lists/$listid/members/$subscriber", $update);

		return $result;
	}

	public function getLists()
	{
		$lists = $this->mc->get("lists");
		
		return $lists;
	}
}