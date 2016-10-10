<?php
/**
 *	This is the Phone verification API
 *	when someone enters their phone number this will put a verification number in the database 
 *	and also send that number to that person
 *
 */

class PHONEVERIFY
{
	private $phone;
	# currently just a test number
	private $twilionumber = "+14243292114";//'+15005550006';//"+14378000252";
	private $VerficiationCode;
	private $phid = false;
	private $codelength = 9;
	private $twiliossid = "SKce3e58753dcae44308928e9e410735b5";
	private $teiliosecret = "HGIKKsVQWmUeBUcKbowZkAWf4AKNduco";
	private $gatewayssid = "AC54513706e6aad061048433691fb8cba0";//"ACc7fbd17753df1f6d8e5f30feb661353c";
	private $gatewaytoken = "6cc684b16d0ea6b2da35f1267b1128ec";//"0ee67ee4ec0b7f73eba395a1b6939525";

	public function __construct()
	{
		//echo "Class Initiated";
	}
	/**
	 *	Set the phone number
	 *	This automatically adds in the area code, for which we only have usa/canada right now
	 *
	 *	@param phone number
	 */
	public function SetPhone($phone)
	{
		$this->phone = "+1".preg_replace("/[^0-9]/", "", $phone);
	}

	/**
	 *	This Inserts the verification into that database so that we can refer to it at some point
	 */
	public function CreateVerification()
	{
		$insert = array(
			"phone" => $this->phone,
			"code" => $this->GenerateCode(),
			"timestamp" => time(),
		);

		# if there's an existing phone number that's exactly the same we return an error
		$s = "SELECT * FROM wp_atn_buyer WHERE phone = :phone ";
		$vars = array("phone" => substr($this->phone, 2));
		$existing = FetchOneQuery($s, $vars);

		if (!empty($existing)) {
			$this->Error = "Account already exists";
			return false;
		}


		# delete existing records with this phone number
		$s = "DELETE FROM trn_phone_verification WHERE phone = :phone ";
		$vars = array(
			"phone" => $this->phone
		);
		$q = BasicQuery($s, $vars);

		$this->phid = DBInsert("trn_phone_verification", $insert);

		# save a session variable for double verification
		$_SESSION['VerificationCode'] = $this->VerficiationCode;

		return true;
	}

	/**
	 *	This gets the verification code from the database
	 *
	 *	@return correct code from database
	 */
	public function GetVerification()
	{
		$phone = $this->phone;
		$s = "SELECT * FROM trn_phone_verification WHERE phone = '$phone'";
		$data = FetchQuery($s);

		# it's the first itteration because it's only 1
		$entry = CleanPDO($data[0]);

		if ($entry['code'] == $_SESSION['VerificationCode'])
			return $entry['code'];
		else
			return false;
	}


	/**
	 *	Generates a random string that we can send to people's phones for verification
	 *
	 *	@return generated code
	 */
	private function GenerateCode()
	{
		$characters = "0123456789";//ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$charLen = strlen($characters);
		$rand = "";
		for ($i = 0; $i < $this->codelength; $i++) {
			$rand .= $characters[rand(0,$charLen -1)];
		}

		# save a temporary number in this class
		$this->VerficiationCode = $rand;

		return $rand;
	}

	public function SendVerification()
	{
		$gateway_from = $this->twilionumber; // from number
		$gateway_to = $this->phone; // to number
		$message = "Your verification code is : ".$this->VerficiationCode;
		$gateway_username = $this->gatewayssid; // id
		$gateway_password = $this->gatewaytoken; // token
						
		// Twilio.
		$url = "https://api.twilio.com/2010-04-01/Accounts/".$this->gatewayssid."/SMS/Messages";
		$data = array (
			'From' => $gateway_from,
			'To' => $gateway_to,
			'Body' => $message,
		);
		$post = http_build_query($data);
		$result = $this->CurlRequest($url, $post);

		$processed = $this->ProcessXML($result);

		if (!$processed)
			$this->Error = "We could not send Verification";

		return $processed;
	}

	/**
	 *	Process XML
	 *	take the xml and return an array 
	 *	(maybe something else at a later date)
	 *
	 *	@param base query
	 *	@return signed query
	 */
	private function ProcessXML($xml_string)
	{
		$xml = simplexml_load_string($xml_string);
		$json = json_encode($xml);

		return json_decode($json, TRUE);
	}


	/**
	 *	Curl Request
	 *	only need this if there's not already a curl function
	 *	actually probably better to use this because it's tailored to this
	 *
	 *	@param url
	 *	@return xml formatted response
	 */
	private function CurlRequest($url, $post = false)
	{
		$x = curl_init($url);
		# set url
		curl_setopt($x, CURLOPT_POST, true);
		curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($x, CURLOPT_USERPWD, $this->gatewayssid.":".$this->gatewaytoken);
		if ($post)
			curl_setopt($x, CURLOPT_POSTFIELDS, $post);

		$output = curl_exec($x);
		# close curl resource to free up system resources
		curl_close($x);

		return $output;
	}

}