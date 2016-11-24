<?php
/**
 *	Seller account class
 *	Handles account creation and login and authentication
 *	Also Handles seller product interactions
 *
 */

class SELLERACCOUNT
{
	# set default id to false
	private $id = false;
	public $Error = false;

	public function SetVar($var, $value)
	{
		$this->$var = $value;
	}

	public function SaveAccount()
	{
		# if there's an id present, we save the account
		# otherwise we're creating a new account
		if ($this->id) {
			# this is just an account update
		} else {
			# using base wordpress function for this
			$sanitized_user_login = sanitize_user($this->Username);
			$this->id = wp_create_user($sanitized_user_login, $this->Password, $this->Email);

			# let's also create the user in the buyer database
			$seller = array(
				"user_id" => $this->id,
				"contact_email" => $this->Email,
				"FirstName" => $this->FirstName,
				"LastName" => $this->LastName,
				"Company" => $this->Company,
				"Phone" => $this->Phone,
				"created" => time(),
				"updated" => time(),
			);

			# get the id from second insert
			$this->atn_id = DBInsert("wp_atn_sellers", $seller);

			# we have to create the hash after insertion because we use both ids
			$email_hash = $this->EmailHash($this->atn_id, $this->id, $this->Email);
			$u = "UPDATE wp_atn_sellers SET email_hash = :email_hash WHERE id = :id";
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
				"VerifyEmailHash" => BASE_URL."/wp-content/themes/TRN/remote.php?a=VerifySellerEmail&id=".$this->atn_id."&eh=".$email_hash,
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
			$s = "SELECT u.user_login, s.id FROM wp_users u LEFT JOIN wp_atn_sellers s ON s.user_id = u.ID WHERE user_email = :username ";
		else
			$s = "SELECT u.user_login, s.id FROM wp_users u LEFT JOIN wp_atn_sellers s ON s.user_id = u.ID WHERE user_login = :username ";

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
		$s = "SELECT * FROM wp_atn_sellers WHERE id = :id";
		$vars = array("id" => $id);
		$user = FetchOneQuery($s, $vars);

		$hash = $this->EmailHash($user['id'], $user['user_id'], $user['contact_email']);

		if ($this->EmailHash($user['id'], $user['user_id'], $user['contact_email'], $hash)) {
			#it's true so update the database
			$u = "UPDATE wp_atn_sellers SET email_verified = 1 WHERE id = :id";
			$vars = array("id" => $id);
			if (!UpdateQuery($u, $vars)) {
				$this->Error = "Database Error ve";
				return false;
			}

			# let's login
			# http://perfectpushupvideo.com/trntemp/wp-content/themes/TRN/remote.php?a=VerifyBuyerEmail&id=10&eh=rosxfesF0a8s2
			wp_set_auth_cookie($user['user_id']);

			return true;
		}

		return false;	
	}

	/**
	 *	Process a Product
	 *	Used for Adding a new product or editing an existing product
	 *
	 *	@return true or false based on insert or validation
	 */
	public function ProcessProduct()
	{
		# the first thing we do is validate the fields
		if (!$this->ValidateProductFields()) {
			return false;
		}

		# double check if the seller is logged in
		$seller = $this->isSeller();
		if (!$seller) {
			$this->Error = "Not Logged in";
			return false;
		}

		$this->id = $seller['id'];

		# once we know it's valid let's determine if it's a new product or not
		if ($this->productid) {
			# this product already exists
			# make the update statement
			$update = array(
				"seller_id" => $this->id,
				"asin" => $this->product['ASIN'],
				"product_name" => $this->product['Title'],
				"img_large" => $this->product['images']['high'],
				"img_med" => $this->product['images']['med'],
				"img_sm" => $this->product['images']['low'],
				"description" => stripslashes($this->product['Description']),
				"price" => $this->product['Price'],
				"discount_price" => $this->product['PAD'],
				"updated" => time(),
			);

			# add variables
			$non_req = array('a' => "startdate", 'b' => "enddate", "SupportEmail", "NTR", "CustomURL");
			foreach ($non_req as $date => $key) {
				if (isset($this->product[$key]) && $this->product[$key] != "") {
					if (!is_numeric($date))
						$update[$key] = $this->Timeconvert($this->product[$key]);
					elseif (isset($this->product[$key]))
						$update[$key] = $this->product[$key];
				}
			}

			$vars = array("productid" => $this->productid);
			$update = UpdateArrayQuery("trn_products", $update, " id = :productid ", $vars);

			if (!$update) {
				$this->Error = "Unable to Update Product";
				return false;
			}

			# now we handle the coupons
			# first delete all couponse that are not being used
			$d = "DELETE FROm trn_coupons WHERE productid = :productid AND seller_id = :seller_id AND status = 1";
			$vars = array(
				"productid" => $this->productid,
				"seller_id" => $seller['id'],
			);
			BasicQuery($d, $vars);

			# now we insert the coupons
			$coupons = explode("\n", $this->product['SUCC']);

			# loop through all the coupons and insert them
			foreach ($coupons as $coupon)
			{
				$ci = array(
					"seller_id" => $this->id,
					"productid" => $this->productid,
					"coupon" => trim($coupon),
					"created" => time(),
					"updated" => time(),
				);

				# before we insert we have to make sure it doesn't exist (used coupons might be there)
				$ec = "SELECT * FROM trn_coupons WHERE coupon = :coupon AND productid = :productid AND seller_id = :seller_id";
				$vars = array(
					"coupon" => trim($coupon),
					"productid" => $this->productid,
					"seller_id" => $seller['id'],
				);
				$existing = FetchOneQuery($ec, $vars);

				# for now we're going to skip these
				if (!empty($existing))
					continue;

				$couponid = DBInsert("trn_coupons", $ci);
			}

		} else {
			# this is a new product
			# create the insertion array
			$insert = array(
				"seller_id" => $this->id,
				"asin" => $this->product['ASIN'],
				"product_name" => $this->product['Title'],
				"img_large" => $this->product['images']['high'],
				"img_med" => $this->product['images']['med'],
				"img_sm" => $this->product['images']['low'],
				"description" => $this->product['Description'],
				"price" => $this->product['Price'],
				"discount_price" => $this->product['PAD'],
				"created" => time(),
				"updated" => time(),
			);

			# add variables
			$non_req = array('a' => "startdate", 'b' => "enddate", "SupportEmail", "NTR", "CustomURL");
			foreach ($non_req as $date => $key) {
				if (isset($this->product[$key]) && $this->product[$key] != "") {
					if (!is_numeric($date))
						$insert[$key] = $this->Timeconvert($this->product[$key]);
					elseif (isset($this->product[$key]))
						$insert[$key] = $this->product[$key];
				}
			}

			$this->productid = DBInsert("trn_products", $insert);

			# error message for product
			if (!$this->productid) {
				$this->Error = "Unable to Insert Product";
				return false;
			}

			# now we insert the coupons
			$coupons = explode("\n", $this->product['SUCC']);

			# loop through all the coupons and insert them
			foreach ($coupons as $coupon)
			{
				$ci = array(
					"seller_id" => $this->id,
					"productid" => $this->productid,
					"coupon" => trim($coupon),
					"created" => time(),
					"updated" => time(),
				);

				$couponid = DBInsert("trn_coupons", $ci);
			}
		}

		# everything is created return true
		return true;
	}

	public function GetAccountProducts()
	{
		# double check if the seller is logged in
		$seller = $this->isSeller();
		if (!$seller) {
			$this->Error = "Not Logged in";
			return false;
		}

		# now let's get a list of the products
		$s = "SELECT id, asin, active, product_name, img_large, img_med, img_sm, description, price, discount_price, enddate FROM trn_products WHERE seller_id = :seller_id ";
		$vars = array("seller_id" => $seller['id']);
		$products = FetchQuery($s, $vars);

		# let's clean the product list to only use what we need
		foreach($products as $p => $product)
		{
			$product = CleanPDO($product);

			# get a coupon count total
			$c = "SELECT COUNT(*) as coupon_count FROM trn_coupons WHERE seller_id = :seller_id AND status = 1 AND productid = :productid";
			$vars = array(
				"seller_id" => $seller['id'],
				"productid" => $product['id'],
			);
			$coupons = FetchOneQuery($c, $vars);

			$product['coupon_count_total'] = $coupons['coupon_count'];

			# get a coupon count of what's used
			$c = "SELECT COUNT(*) as coupon_count FROM trn_coupons WHERE seller_id = :seller_id AND status = 0 AND productid = :productid";
			$vars = array(
				"seller_id" => $seller['id'],
				"productid" => $product['id'],
			);
			$coupons = FetchOneQuery($c, $vars);

			$product['coupon_count_used'] = $coupons['coupon_count'];

			# now let's get the time remaining
			$enddate = $product['enddate'];
			unset($product['enddate']);

			if ($enddate > 0)
				$product['time_remaining'] = $enddate - time();
			else
				$product['time_remaining'] = 0;

			$products[$p] = $product;
		}

		return $products;
	}

	public function ChangeActivity()
	{
		$u = "UPDATE trn_products SET Pause = NOT Pause WHERE id = :id";
		$vars = array("id" => $this->product['id']);
		UpdateQuery($u, $vars);

		$p = "SELECT * FROM trn_products WHERE id = :id";
		$product = FetchOneQuery($p, $vars);

		return $product;
	}


	public function GetProduct()
	{
		if (!isset($this->productid))
			return false;

		# double check if the seller is logged in
		$seller = $this->isSeller();
		if (!$seller) {
			$this->Error = "Not Logged in";
			return false;
		}

		$p = "SELECT * FROM trn_products WHERE id = :id";
		$vars = array("id" => $this->productid);
		$product = FetchOneQuery($p, $vars);

		if (empty($product))
			return false;

		# get product coupons
		$c = "SELECT coupon FROM trn_coupons WHERE seller_id = :seller_id AND productid = :productid";
		$vars = array("seller_id" => $seller['id'], "productid" => $product['id']);
		$results = FetchQuery($c, $vars);

		$coupons = array();
		foreach($results as $coupon)
		{
			$coupons[] = $coupon['coupon'];
		}

		$product['coupons'] = implode("\n", $coupons);

		# let's convert any dates to javascript readable values
		if ($product['startdate'] != "")
			$product['startdate'] = date('D M d Y H:i:s O', $product['startdate']);

		if ($product['enddate'] != "")
			$product['enddate'] = date('D M d Y H:i:s O', $product['enddate']);


		return $product;
	}

	private function ValidateProductFields()
	{
		$required = array(
			"ASIN" => "ASIN required", 
			"Title" => "Title required",
			"Description" => "Description required",
			"Price" => "Price required",
			"SUCC" => "Coupon Codes required",
			"PAD" => "Discount Price required",
		);

		foreach($required as $k => $v)
		{
			if (!isset($this->product[$k]) || $this->product[$k] == "") {
				$this->Error = $v;
				return false;
			}
		}

		return true;
	}


	/**
	 *	Ths function handles redirects for the buy pages
	 */
	public function VerifySellerPage()
	{
		$pageid = get_the_ID();
		$url = sprintf("%s://%s",isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',$_SERVER['SERVER_NAME']);

		# we also have to check if this use is a seller (could be other logged in users)
		$seller = $this->isSeller();

		# certain pages need to be redirected if you're logged in or not
		switch($pageid) {
			case 21: # seller login
				if ($seller) {
					header('location: '.BASE_URL.'/seller-homepage/'); exit();
				}
				break;
			case 30: # add product
			case 32: # seller products
			case 20: # seller homepage
				if (!$seller) {
					header('location: '.BASE_URL.'/amazon-sellers/'); exit();
				}
				break;
			case 19: # seller signup
				if ($seller) {
					header('Location: '.BASE_URL.'/seller-homepage/'); exit();
				}
				break;
			case 28: # seller logout
				# logout no matter what
				wp_logout();
				header('Location: '.BASE_URL.'/amazon-sellers/'); exit();
				break;
			default:
				break;
		}
	}

	public function isSeller()
	{
		$userid = get_current_user_id();

		$s = "SELECT * FROM wp_atn_sellers WHERE user_id = :userid";
		$vars = array(
			"userid" => $userid,
		);
		$seller = FetchOneQuery($s, $vars);

		if (!empty($seller))
			return $seller;
		else
			return false;
	}

	private function Timeconvert($time)
	{
		$replace = strstr($time, "GMT");
		$time = str_replace($replace, "", $time);
		$totime = strtotime($time);

		return $totime;
	}

}