<?php
/**
 *	Buyer account class
 *	Handles account creation and login and authentication
 *	Also Handles buyer product interactions
 *
 */

class BUYERACCOUNT
{
	# set default id to false
	private $id = false;

	public function SetVar($var, $value)
	{
		$this->$var = $value;
	}

	public function SaveAccount()
	{
		# if there's an id present, we save the account
		# otherwise we're creating a new account
		if ($this->atn_id) {
			# this is just an account update

			# at this point they have to save an amazon id so we have to verify it
			$amazon = GetClass('AMAZON');

			# let's clean the buyer link a bit for idiots
			preg_match_all("/(https?:\/\/)?(www.amazon.com)?(\/gp|gp\/pdp\/)?(\/profile\/)?(A[A-Z0-9]*)(.*)?/", $this->buyer['amazonid'], $matchid);//("/(https?:\/\/)?(www.amazon.com)?(\/gp|gp\/pdp\/)?(\/profile\/)?(A[A-Z0-9]*)(.*)?/", '$5', $this->buyer['amazonid']);//str_replace(array("http://", "https://", "www.amazon.com", "/gp/profile/"), "", $this->buyer['amazonid']);
			$this->buyer['amazonid'] = $matchid[5][0];

			$verifiedamazon = $amazon->VerifyAmazonID($this->buyer['amazonid']);

			if (!$verifiedamazon) {
				$this->Error = "Invalid Amazon ID";
				return false;
			}

			# first we update the buyer database
			$where = "id = :id";
			$u = UpdateArrayQuery("wp_atn_buyer", $this->buyer, $where, array("id" => $this->atn_id));			

			# now we update user email address (but that's for later when we actually make a settings area)
			$this->id = true;
		} else {
			# have to make sure that the email doesn't already exist
			$e = "SELECT * FROM wp_users WHERE user_email = :email";
			$vars = array("email" => $this->Email);
			$existing = FetchOneQuery($e, $vars);

			if (!empty($existing)) {
				$this->Error = "User Already Exists";
				return false;
			}
			# using base wordpress function for this
			$sanitized_user_login = sanitize_user($this->Username);
			$this->id = wp_create_user($sanitized_user_login, $this->Password, $this->Email);

			if (!$this->id) {
				$this->Error = "Error Creating the account";
				return false;
			}

			# let's also create the user in the buyer database
			# we're no longer making the amazonid required
			//if (!$this->AmazonID)
				//$this->AmazonID = "";

			$buyer = array(
				"user_id" => $this->id,
				"contact_email" => $this->Email,
				//"amazonid" => $this->AmazonID,
				"phone" => $this->Phone,
				"created" => time(),
				"updated" => time(),
			);

			# get the id from second insert
			$this->atn_id = DBInsert("wp_atn_buyer", $buyer);

			# we have to create the hash after insertion because we use both ids
			$email_hash = $this->EmailHash($this->atn_id, $this->id, $this->Email);
			$u = "UPDATE wp_atn_buyer SET email_hash = :email_hash WHERE id = :id";
			$vars = array(
				"email_hash" => $email_hash,
				"id" => $this->atn_id,
			);
			UpdateQuery($u, $vars);

			# once we have the new buyer inserted let's send an email
			$email = GetClass('EMAIL');
			$vars = array(
				"Logo" => file_get_contents(TEMPLATE_PATH."Panels/TRN-logo.html"),
				"Year" => date('Y'),
				"id" => $this->atn_id,
				"VerifyEmailHash" => BASE_URL."/wp-content/themes/TRN/remote.php?a=VerifyBuyerEmail&id=".$this->atn_id."&eh=".$email_hash,
			);

			$b = "SELECT * FROM wp_users WHERE ID = :id";
			$mysqlv = array("id" => $this->id);
			$user = FetchOneQuery($b, $mysqlv);
			# associate all product variables
			foreach ($user as $k => $v)
			{
				$vars[$k] = $v;
			}

			$body = $email->SetEmailTemplate('NewAccount', $vars);
			$subject = "TRN: Thank you for signing up!";
			$email->Send($this->Email, $body, $subject, "trn@trustreviewnetwork.com");

			# now that we know everything has gone through let's add the account to the mailchimp list
			$chimp = GetClass('MAILCHIMPUSER');

			// our subscriber list id = 7d5daf6b11
			$user = array(
				"fname" => $sanitized_user_login,
				"lname" => "",
			);
			$res = $chimp->AddSubscriber('7d5daf6b11', $this->Email, $user);

			# make sure you're logged out
			wp_logout();

			# after saving an account let's also login
			wp_set_auth_cookie($this->id);
		}

		return $this->id;
	}

	public function EmailHash($id, $atn_id, $email, $reverse = false)
	{
		$stringtohash = $id."|".$atn_id."|".$email;
		$crypt = crypt($stringtohash, $email);

		if (!$reverse)
			return $crypt;
		
		# this means we have to compare hashes
		if ($reverse == $crypt)
			return true;

		return false;
	}

	public function ProcessLogin()
	{
		# first let's check if the username is an email or login
		if (isEmail($this->Username))
			$s = "SELECT u.user_login, b.id FROM wp_users u LEFT JOIN wp_atn_buyer b ON b.user_id = u.ID WHERE user_email = :username AND blocked = 0";
		else
			$s = "SELECT u.user_login, b.id FROM wp_users u LEFT JOIN wp_atn_buyer b ON b.user_id = u.ID WHERE user_login = :username AND blocked = 0";

		$vars = array(
			"username" => $this->Username
		);

		$user = FetchOneQuery($s, $vars);

		$user_login = $user['user_login'];

		# create the array for wordpress
		$credentials = array(
			"user_login" => $user_login,
			"user_password" => $this->Password,
		);

		if (isset($this->Remeber))
			$credentials['remember'] = $this->Remember;

		# before we sign on we have to check if this is a buyer
		if (!is_numeric($user['id']) || $user['id'] == 0)
			return false;

		# login using the wordpress functions
		$login = wp_signon($credentials);

		if (isset($login->data->ID))
			return true;
		else
			return false;
	}

	public function VerifyEmail($id, $hash = false)
	{
		# let's try hashing the stuff
		$s = "SELECT * FROM wp_atn_buyer WHERE id = :id";
		$vars = array("id" => $id);
		$user = FetchOneQuery($s, $vars);

		$hash = $this->EmailHash($user['id'], $user['user_id'], $user['contact_email']);

		if ($this->EmailHash($user['id'], $user['user_id'], $user['contact_email'], $hash)) {
			#it's true so update the database
			$u = "UPDATE wp_atn_buyer SET email_verified = 1 WHERE id = :id";
			$vars = array("id" => $id);
			if (!UpdateQuery($u, $vars)) {
				$this->Error = "Database Error ve";
				return false;
			}

			# let's login
			# http://perfectpushupvideo.com/trntemp/wp-content/themes/TRN/remote.php?a=VerifyEmail&id=10&eh=rosxfesF0a8s2
			wp_set_auth_cookie($user['user_id']);

			return true;
		}

		return false;	
	}

	/**
	 *	Associate buyer to a specific campaign product
	 *	To associate we have to put product code into the buyer
	 *	We have to update whether the code is in use
	 *	We have to block buyer account from getting another coder
	 */
	public function AssociateToCampaign()
	{
		# get the user information
		$s = "SELECT * FROM wp_users u
		LEFT JOIN wp_atn_buyer b on b.user_id = u.ID
		WHERE u.ID = :ID ";
		$vars = array(
			"ID" => $this->id
		);
		$user = FetchOneQuery($s, $vars);
		
		# let's check if this buyer is already requestes a code without a review present
		/*if ($user['verified_flag'] == 0 && $user['current_coupon'] != "") {
			$this->Error = "You have a review pending!";
			return false;
		}*/

		# this has to be changed to let them have 5 reviews pending
		$v = "SELECT * FROM trn_coupon_tracking ct 
		LEFT JOIN wp_atn_reviews r ON r.buyer_id = ct.buyer_id AND r.asin = ct.asin
		WHERE ct.buyer_id = :id AND r.id IS NULL";
		$vars = array("id" => $user['id']);
		$vr = FetchQuery($v, $vars);

		if (count($vr) >= 5) {
			$this->Error = "You have reviews pending!";
			return false;
		}

		# let's also check if the buyer has already requested this product in the past
		$e = "SELECT * FROM wp_atn_reviews WHERE buyer_id = :buyer_id AND asin = :asin ";
		$vars = array("buyer_id" => $user['id'], "asin" => $this->product['asin']);
		$ex = FetchOneQuery($e, $vars);

		if (!empty($ex)) {
			$this->Error = "You have already received a coupon for this product! ";
			return false;
		}

		/*
		# let's get the campaign information
		$s = "SELECT c.*, coup.id as couponid, coup.coupon FROM wp_atn_campaign c 
		LEFT JOIN wp_atn_coupons coup ON coup.campaign_id = c.id AND coup.status = 'enabled'
		WHERE c.id = :campaignid";
		$vars = array(
			"campaignid" => $this->campaignid
		);
		$campaign = FetchOneQuery($s, $vars);*/

		# let's get the coupon information
		$s = "SELECT * FROM trn_coupons WHERE productid = :productid AND status = 1";
		$vars = array(
			"productid" => $this->product['id']
		);
		$coupon = FetchOneQuery($s, $vars);

		if (empty($coupon))
		{
			$this->Error = "There are no more available coupons.";
			return false;
		}

		# let's update the buyer table
		$b = "UPDATE wp_atn_buyer SET current_coupon = :coupon WHERE id = :id ";
		$vars = array(
			"coupon" => $coupon['id'],
			"id" => $user['id'],
		);
		if (!UpdateQuery($b, $vars)) {
			$this->Error = "Database Error 1";
			return false;
		}

		# now let's update the coupon table
		$b = "UPDATE trn_coupons SET status = 0 WHERE id = :id ";
		$vars = array(
			"id" => $coupon['id'],
		);
		if (!UpdateQuery($b, $vars)) {
			$this->Error = "Database Error 2";
			return false;
		}

		# last step is to insert the coupon code and the buyer id into a table to track all associations
		$tracking = array(
			"buyer_id" => $user['id'],
			"couponid" => $coupon['id'],
			"coupon" => $coupon['coupon'],
			"asin" => $this->product['asin'],
			"inserted" => time(),
		);

		# get the id from second insert
		$this->trackingid = DBInsert("trn_coupon_tracking", $tracking);

		# now' we're homefree so we can send out the email to the buyer
		$email = GetClass('EMAIL');
		$vars = array(
			"Logo" => file_get_contents(TEMPLATE_PATH."Panels/TRN-logo.html"),
			"Year" => date('Y'),
			"Code" => $coupon['coupon'],
		);

		# associate all product variables
		foreach ($this->product as $k => $v)
		{
			$vars[$k] = stripslashes($v);
		}

		$body = $email->SetEmailTemplate('ProductRequest', $vars);
		$subject = "TRN: Requested Product Promo Code";
		$email->Send($user['contact_email'], $body, $subject, "trn@trustreviewnetwork.com");

		return true;
	}

	public function ConfirmReview()
	{
		$review = GetClass('AMAZONCRON');

		# get the user information
		$s = "SELECT * FROM wp_users u
		LEFT JOIN wp_atn_buyer b on b.user_id = u.ID
		WHERE u.ID = :ID ";
		$vars = array(
			"ID" => $this->id
		);
		$user = FetchOneQuery($s, $vars);

		return $review->FindReview($user);
	}

	public function GetProducts()
	{
		# also have to get the associate code from the database
		$c = "SELECT * FROM trn_settings WHERE label = 'associatecode' ";
		$code = FetchOneQuery($c);

		$s = "SELECT * FROM trn_products WHERE active = 1";
		if (BAPO()) {
			$s = "SELECT * FROM trn_products";
		}
		$products = FetchQuery($s);

		$cleaned = array();
		foreach($products as $p => $values)
		{
			# unset products that aren't within the dates
			if (!BetweenDates($values['startdate'], $values['enddate']) && !BAPO()) {
				continue;
			}

			$values['associatecode'] = $code['value'];
			$cleaned[] = CleanPDO($values);
		}

		if ($this->query != "") {
			# instead of doing exact matches let's do a levenshtein ordering of the current products

			foreach($cleaned as &$product)
			{
				$product['levenshtein'] = $this->CalculateLevenshtein($product);
			}

			# now we have to order the products by it's levenshtein distance
			usort($cleaned, 'lev_sorter');
		}

		return $cleaned;
	}

	public function CalculateLevenshtein($r)
	{
		$levenshtein = 0;
		# this only happens if we have a posted query
		$q = $this->query;
		$q = explode(" ", $q);

		# put the product name into an array
		$prodname = explode(" ", $r['product_name']);

		foreach($q as $nested)
		{
			$nested = strtolower($nested);
			$wd = 99999;
			$aw = str_split($nested);
			foreach($prodname as $pn)
			{
				$pn = strtolower($pn);
				$bw = str_split($pn);
				$d = $this->LevenShtein($nested, $pn, 1, 1, 2);

				if ($d < $wd)
					$wd = $d;
			}

			$levenshtein += $wd;
		}				

		return $levenshtein;
	}

	public function LevenShtein($a,$b)
	{
		$al = strlen($a);
		$bl = strlen($b);
		if($al == 0) return $bl;
		if($bl == 0) return $al;

		$d = 0;

		$matrix = array();
		$aw = str_split($a);
		$bw = str_split($b);
		
		// increment along the first column of each row
		for($i = 0; $i <= $bl; $i++){
			$matrix[$i] = array($i);
		}

		// increment each column in the first row
		for($j = 0; $j <= $al; $j++){
			$matrix[0][$j] = $j;
		}

		// Fill in the rest of the matrix
		for($i = 1; $i <= $bl; $i++){
			for($j = 1; $j <= $al; $j++){
				$ii = $i-1;
				$jj = $j-1;
				if($b[$ii] == $a[$jj]){
					$matrix[$i][$j] = $matrix[$ii][$jj];
				} else {
					$sub = $matrix[$ii][$jj] + 4;
					$ins = $matrix[$i][$jj] + 2;
					$del = $matrix[$ii][$j] + 2;
					$matrix[$i][$j] = min($sub, $ins, $del);
				}
			}
		}


		$db = $matrix[$bl][$al];
		# we have a light addition to the levenshtein distance
		# with the levenshtein distance let's also calculate the similar characters
		$matches = array_intersect($aw, $bw);
		$matches = count($matches);
				
		// this helps a lot
		$d = $db - $matches;

		return $d;
	}

	/**
	 *	Ths function handles redirects for the buy pages
	 */
	public function VerifyBuyerPage()
	{
		$pageid = get_the_ID();
		$url = sprintf("%s://%s",isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',$_SERVER['SERVER_NAME']);

		# we also have to check if this use is a buyer (could be other logged in users)
		$buyer = $this->isBuyer();

		# need to verify amazon id
		$amazon = GetClass('AMAZON');
		$verified = $amazon->VerifyAmazonID($buyer['amazonid']);

		# default case we have to verify if the haven amazon id
		if ($buyer && !$verified && !in_array($pageid, array(26,51))) {
			# we redirect them to the amazon input amazon id page
			header('Location: '.BASE_URL.'/amazonid/'); exit();
		}

		# certain pages need to be redirected if you're logged in or not
		switch($pageid) {
			case 6: # buyer login
				if ($buyer) {
					header('location: '.BASE_URL.'/buyer-homepage/'); exit();
				}
				break;
			case 7: # buyer homepage
				if (!$buyer) {
					header('location: '.BASE_URL.'/buyer-login/'); exit();
				}
				break;
			case 12: # buyer signup
				if ($buyer) {
					header('Location: '.BASE_URL.'/buyer-homepage/'); exit();
				}
				break;
			case 26: # buyer logout
				# logout no matter what
				wp_logout();
				header('Location: '.BASE_URL.'/buyer-login/'); exit();
				break;
			case 51:
				if ($buyer && $verified) {
					header('location: '.BASE_URL.'/buyer-homepage/'); exit();
				}
			default:
				break;
		}
	}

	public function isBuyer()
	{
		$userid = get_current_user_id();

		$s = "SELECT * FROM wp_atn_buyer WHERE user_id = :userid";
		$vars = array(
			"userid" => $userid,
		);
		$buyer = FetchOneQuery($s, $vars);

		if (!empty($buyer))
			return $buyer;
		else
			return false;
	}
}