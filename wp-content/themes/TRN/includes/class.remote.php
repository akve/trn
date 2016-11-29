<?php
/**
 *	Remote class than handles all ajax requests
 *	
 *	@package none
 *
 */
class REMOTE
{
	/**
	 * Dynamically get the panels
	 */

	public function gettemplate()
	{
		$panel = $_REQUEST['p'];
		WPParseAndGet($panel);
	}

	public function getpanel()
	{
		$panel = "Panels/".$_REQUEST['p'];
		WPParseAndGet($panel);
	}

	public function ContactUs()
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$vars = array(
			//"Logo" => WPParseAndGet("Panels/TRN-logo", true),
			"Year" => date('Y'),
			"From" => $_POST['form']['FirstName']." ".$_POST['form']['LastName'],
			"FromEmail" => $_POST['form']['Email'],
			"Message" => $_POST['form']['Notes'],
		);

		# all we do is post this information in an email to someone in the TRN
		$email = GetClass('email');
		$body = $email->SetEmailTemplate('ContactEmail', $vars);

		$s = $email->Send("care@trustreveiwnetwork.com", $body, "New Contact Form Submission", "trn@trustreviewnetwork.com");

		if ($s) {
			JSONOutput(array(
				"success" => true,
				"message" => array(
					"class" => "success",
					"message" => "Your Message has been successfully sent!",
				),
			));
		} else {
			JSONOutput(array(
				"success" => false,
				"error" => array(
					"class" => "error",
					"message" => "There was an error sending your message please contact us at care@trustreveiwnetwork.com",
				),
			));
		}
	}
	/**
	 *	Send Verification
	 *	post a phone number and receive a text message with confirmation code
	 *
	 *	@param posted phone number
	 *	@return ajax return boolean
	 */
	public function SendVerification()
	{
		$phone = preg_replace("/[^0-9\+]/", "", $_REQUEST['phone']);
		$text = GetClass('PHONEVERIFY');

		# set phone number in to the database
		$text->SetPhone($phone);
		$created = $text->CreateVerification();

		# response from api
		if (BAPO()) {
			$response = true;
		} else {
			if ($created)
				$response = $text->SendVerification();
		}

		# once we get a response we can output a success value
		if ($response)
			JSONOutput(array("success" => true, vcode => $response));
		else
			JSONOutput(array("error" => $text->Error));
	}

	/**
	 *	Confirm Verification
	 *	phone number, and code get's input and returns boolean if code is correct
	 *
	 *	@param posted phone number
	 *	@param posted code
	 *	@return ajax return boolean
	 */
	public function ConfirmVerification()
	{
		$phone = preg_replace("/[^0-9\+]/", "", $_REQUEST['phone']);
		$code = $_REQUEST['code'];
		$text = GetClass('PHONEVERIFY');

		# let's get the verification and compare
		$text->SetPhone($phone);
		$compare = $text->GetVerification();

		if ($compare == $code)
			JSONOutput(true);
		else
			JSONOutput(false);
	}

	/**
	 *	Create Account
	 *	With the confirmed code, and phone number we can create the account
	 *
	 *	@param posted phone number
	 *	@param posted code
	 *	@param posted account information
	 *	@return ajax return boolean
	 */
	public function CreateBuyerAccount()
	{
		$accountinfo = $_REQUEST['account'];
		$phone = preg_replace("/[^0-9\+]/", "", $_REQUEST['phone']);
		$code = $_REQUEST['code'];
		$text = GetClass('PHONEVERIFY');
		$account = GetClass('BUYERACCOUNT');

		# always verift in case they're trying to manipulate the javascript
		$text->SetPhone($phone);
		
		/*$compare = "093358354";//$text->GetVerification();

		if ($compare != $code) {
			JSONOutput(false);		
			exit();
		}*/

		# set all the variables
		$account->SetVar("Phone", $phone);
		foreach ($accountinfo as $var => $value)
		{
			$account->SetVar($var, $value);
		}

		# save the account
		$result = $account->SaveAccount();

		if ($result)
			JSONOutput(array("status" => "ok", "result" => $result));
		else
			JSONOutput(array("status" => "error", "error" => $account->Error));
	}

	public function SaveBuyer()
	{
		$account = GetClass('BUYERACCOUNT');
		$account->SetVar("buyer", $_POST['buyer']);

		$buyer = $account->isBuyer();

		if (!$buyer) {
			JSONOutput(false);		
			exit();
		}

		$account->SetVar("atn_id", $buyer['id']);

		$save = $account->SaveAccount();

		if ($save)
			JSONOutput($save);
		else
			JSONOutput(array("error" => $account->Error));
	}

	public function ChangePasswordBuyer()
	{
		$account = GetClass('BUYERACCOUNT');
		$buyer = $account->isBuyer();

		if (!$buyer) {
			JSONOutput(array("error" => "Not a buyer"));		
			exit();
		}

		$output = wp_set_password( $_REQUEST['password'], $buyer['user_id'] );


		//$save = $account->SaveAccount();
		JSONOutput(array("status" => "ok"));
	}

	public function SaveSeller()
	{
		$account = GetClass('SELLERACCOUNT');
		$account->SetVar("seller", $_POST['seller']);

		$seller = $account->isSeller();

		if (!$seller) {
			JSONOutput(false);		
			exit();
		}

		$account->SetVar("id", $seller['id']);

		$save = $account->SaveAccount();

		if ($save)
			JSONOutput($save);
		else
			JSONOutput(array("error" => $account->Error));
	}

	public function ChangePasswordSeller()
	{
		$account = GetClass('SELLERACCOUNT');
		$buyer = $account->isSeller();

		if (!$buyer) {
			JSONOutput(array("error" => "Not a seller"));		
			exit();
		}

		$output = wp_set_password( $_REQUEST['password'], $buyer['user_id'] );


		//$save = $account->SaveAccount();
		JSONOutput(array("status" => "ok"));
	}


	public function BuyerLogin()
	{
		$logininfo = $_REQUEST['account'];

		$account = GetClass('BUYERACCOUNT');

		# set all the variables
		foreach ($logininfo as $var => $value)
		{
			$account->SetVar($var, $value);
		}

		$login = $account->ProcessLogin();

		if ($login)
			JSONOutput(true);
		else
			JSONOutput(false);
	}

	public function GetBuyerProducts()
	{
		$q = $_REQUEST['q'];

		$account = GetClass('BUYERACCOUNT');
		$account->SetVar("query", $q);

		$products = $account->GetProducts();

		JSONOutput($products);
	}

	public function GetBuyerProfile(){
		$account = GetClass('BUYERACCOUNT');
		$account->SetVar("query", $q);

		$products = $account->GetProfile();

		JSONOutput($products);
	}

	public function GetSellerProfile(){
		$account = GetClass('SELLERACCOUNT');
		$account->SetVar("query", $q);

		$products = $account->GetProfile();

		JSONOutput($products);
	}

	public function RequestProduct()
	{
		$product = $_REQUEST['product'];

		# we have to check if the user is logged in
		$this->id = get_current_user_id();

		if ($this->id == 0) {
			JSONOutput(false);
			exit();
		}

		$account = GetClass('BUYERACCOUNT');

		# let's associate some variables
		$account->SetVar("product", $product);

		# set the buyerid for reference
		$account->SetVar('id', $this->id);

		$response = $account->AssociateToCampaign();
		//echo $response;

		if ($response)
			JSONOutput(array("response" => $response));
		else
			JSONOutput(array("response" => false, "message" => $account->Error));
	}

	public function ConfirmReview()
	{
		$product = $_REQUEST['product'];
		$link = $_REQUEST['link'];

		# we have to check if the user is logged in
		$this->id = get_current_user_id();

		if ($this->id == 0) {
			JSONOutput(false);
			exit();
		}

		$account = GetClass('BUYERACCOUNT');

		# let's associate some variables
		$account->SetVar("product", $product);

		# set the buyerid for reference
		$account->SetVar('id', $this->id);

		$response = $account->ConfirmReview($link, $product);

		if ($response)
			JSONOutput(true);
		else
			JSONOutput(false);
	}

	public function VerifyBuyerEmail()
	{
		$id = $_REQUEST['id'];
		$hash = urldecode($_REQUEST['eh']);

		$account = GetClass('BUYERACCOUNT');

		if ($account->VerifyEmail($id, $hash))
			header('location: '.BASE_URL.'/buyer-homepage/');
		else
			header('location: '.BASE_URL);

		die();
	}

	public function SendForgotPassword()
	{
		$errors = new WP_Error();

		# first thing we do is get the user data from the email 
		$user_data = get_user_by( 'email', trim( $_POST['email'] ) );

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			$code = $errors->get_error_code();
			$message = $errors->get_error_messages($code);

			JSONOutput(array("error" => $message[0]));
			exit();
		}

		if ( !$user_data ) {
			$errors->add('invalidcombo', __('Invalid Email'));
			$code = $errors->get_error_code();
			$message = $errors->get_error_messages($code);

			JSONOutput(array("error" => $message[0]));
			exit();
		}

		# now that we know that the user is good let's start the password reset process
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$id = $user_data->ID;
		$key = get_password_reset_key( $user_data );

		$email = GetClass('EMAIL');
		$vars = array(
			"Logo" => file_get_contents(TEMPLATE_PATH."Panels/TRN-logo.html"),
			"Year" => date('Y'),
			"key" => $key,
			"user_login" => $user_login,
			"PasswordResetLink" => BASE_URL."/password-reset/?key=$key&q={$id}",
		);

		$body = $email->SetEmailTemplate('PasswordReset', $vars);
		$subject = "TRN: Password Reset";
		$res = $email->Send($user_email, $body, $subject, "trn@trustreviewnetwork.com");

		JSONOutput(array("success" => true, "response" => $res, "email" => $user_email));
	}

	public function ResetPassword()
	{
		$key = $_POST['key'];
		$id = $_POST['q'];

		# let's get the login from the id
		$user_data = get_user_by( 'ID', trim( $id ) );

		if ( !$user_data ) {
			$errors->add('invalidcombo', __('Invalid Reset Link'));
			$code = $errors->get_error_code();
			$message = $errors->get_error_messages($code);

			JSONOutput(array("error" => $message[0]));
			exit();
		}

		$user = check_password_reset_key( $key, $user_data->user_login );

		if ( !$user ) {
			$errors->add('invalidcombo', __('Invalid Reset Link Key'));
			$code = $errors->get_error_code();
			$message = $errors->get_error_messages($code);

			JSONOutput(array("error" => $message[0]));
			exit();
		}

		reset_password($user, $_POST['password']);

		# now let's get a location
		$s = "SELECT * FROM wp_atn_sellers WHERE id = :id AND contact_email = :email";
		$vars = array("id" => $id, "email" => $user_data->user_email);
		$seller = FetchOneQuery($s, $vars);

		if (!empty($seller))
			$location = "amazon-sellers";
		else
			$location = "buyer-login";

		JSONOutput(array("success" => true, "location" => $location));
	}

	/**
	 *	Create Seller Account
	 *	With the confirmed code, and phone number we can create the account
	 *
	 *	@param posted phone number
	 *	@param posted code
	 *	@param posted account information
	 *	@return ajax return boolean
	 */
	public function CreateSellerAccount()
	{
		$accountinfo = $_REQUEST['account'];
		$account = GetClass('SELLERACCOUNT');

		# set all the variables
		foreach ($accountinfo as $var => $value)
		{
			$account->SetVar($var, $value);
		}

		# save the account
		$result = $account->SaveAccount();

		if ($result)
			JSONOutput($result);
		else
			JSONOutput(false);
	}

	public function ChangeActivity()
	{
		$account = GetClass('SELLERACCOUNT');
		$account->SetVar('product', $_REQUEST['product']);

		$product = $account->ChangeActivity();
		
		JSONOutput($product);
	}

	public function ArchiveProduct()
	{
		$account = GetClass('SELLERACCOUNT');
		$account->SetVar('product', $_REQUEST['product']);

		$product = $account->ArchiveProduct();
		
		JSONOutput($product);
	}

	public function SellerLogin()
	{
		$logininfo = $_REQUEST['account'];
		$account = GetClass('SELLERACCOUNT');

		# set all the variables
		foreach ($logininfo as $var => $value)
		{
			$account->SetVar($var, $value);
		}

		$login = $account->ProcessLogin();

		if ($login)
			JSONOutput(true);
		else
			JSONOutput(false);
	}

	public function VerifySellerEmail()
	{
		$id = $_REQUEST['id'];
		$hash = urldecode($_REQUEST['eh']);

		$account = GetClass('SELLERACCOUNT');

		if ($account->VerifyEmail($id, $hash))
			header('location: '.BASE_URL.'/seller-products/');
		else
			header('location: '.BASE_URL);

		die();
	}

	public function ASINSearch()
	{
		$asin = $_REQUEST['asin'];

		$amazon = GetClass('AMAZON');

		$product = $amazon->FindProduct($asin);

		# return false if no product
		if (!isset($product['attributes']['Title'])) {
			JSONOutput(array(
				"error" => true,
				"response" => $product,
			));
			exit();
		}

		# let's clean up the data so that it matches out inputs
		$product = array(
			"populated" => true,
			"ASIN" => $asin,
			"Title" => $product['attributes']['Title'],
			"Description" => $product['description'],
			"Price" => $product['attributes']['ListPrice']['Amount']/100,
			"CustomURLDefault" => "http://www.amazon.com/dp/$asin/",
			"images" => array(
				"low" => $product['thumb_image'], //$product['images']['ImageSet'][0]['ThumbnailImage']['URL'],
				"med" => $product['med_image'], //$product['images']['ImageSet'][0]['MediumImage']['URL'],
				"high" => $product['big_image']// $product['images']['ImageSet'][0]['LargeImage']['URL'],
			),
		);

		JSONOutput(array(
			"product" => $product
		));
			
	}

	public function ProcessProduct()
	{
		$product = $_REQUEST['product'];
		$seller = GetClass("SELLERACCOUNT");

		# process product
		$seller->SetVar("product", $product);
		# set a productid if there is one
		if (isset($product['productid']))
			$seller->SetVar("productid", $product['productid']);

		$result = $seller->ProcessProduct();

		if ($result)
			JSONOutput(array(
				"success" => true,
			));
		else
			JSONOutput(array(
				"error" => $seller->Error,
			));
	}

	public function GetProduct()
	{
		$productid = $_REQUEST['productid'];
		$seller = GetClass('SELLERACCOUNT');

		$seller->SetVar("productid", $productid);
		$result = $seller->GetProduct();

		# let's clean up the data so that it matches out inputs
		if ($result) {
			$product = array(
				"populated" => true,
				"productid" => $result['id'],
				"ASIN" => $result['asin'],
				"Title" => $result['product_name'],
				"Description" => $result['description'],
				"Price" => $result['price'],
				"PAD" => $result['discount_price'],
				"CustomURLDefault" => "http://www.amazon.com/dp/".$result['asin']."/",
				"CustomURL" => $result['CustomURL'],
				"SupportEmail" => $result['SupportEmail'],
				"SUCC" => $result['coupons'],
				"NTR" => $result['NTR'],	
				"startdate" => $result['startdate'],
				"enddate" => $result['enddate'],
				"images" => array(
					"low" => $result['img_sm'],
					"med" => $result['img_med'],
					"high" => $result['img_large'],
				),
			);
		}

		if ($result)
			JSONOutput($product);
		else
			JSONOutput(array(
				"error" => $seller->Error,
			));
	}

	public function GetProducts()
	{
		$seller = GetClass("SELLERACCOUNT");

		$products = $seller->GetAccountProducts();

		JSONOutput($products);
	}

	/**
	 *	Header Items
	 *	the reason we pull it from the database rather than just populating it with html is because we want to restrict certain items to logged in status etc.
	 *
	 *	@return list of menu items
	 *
	 */
	public function GetHeaderItems()
	{
		// for now let's just push back some defaults
		$menu = array(
			array("name" => "Home", "link" => ""),
			//array("name" => "Amazon Sellers", "link" => "amazon-sellers/"),
			array("name" => "FAQs", "link" => "FAQs/"),
			array("name" => "Contact", "link" => "Contact/"),
			array("name" => "Log In", "link" => "buyer-login/", "buttonclass" => "login"),
			array("name" => "Join Now", "link" => "?join=true", "buttonclass" => "join-now"),
		);

		$buyer = GetClass("BUYERACCOUNT");
		$buyerData = $buyer->isBuyer();
		if ($buyer->isBuyer())
		{
			$menu = array(
				array("name" => "Products", "link" => "buyer-homepage/"),
				array("name" => "FAQs", "link" => "FAQs/"),
				array("name" => "Contact", "link" => "Contact/"),
				//array("name" => "Logout", "link" => "buyer-logout/"),
			);			
			$menu = array("menuitems" => $menu, "userdata" => $buyerData);

		}

		$seller = GetClass("SELLERACCOUNT");
		$sellerData = $seller->isSeller();
		if ($sellerData)
		{
			$sellerData['usertype'] = 'seller';
			$menu = array(
				array("name" => "View Products", "link" => "seller-products/"),
				array("name" => "Add Product", "link" => "add-product/"),
//				array("name" => "Logout", "link" => "seller-logout/"),
			);			
			$menu = array("menuitems" => $menu, "userdata" => $sellerData);
		}

		JSONOutput($menu);
	}

	/**
	 * Admin Remote functions
	 */

	public function getBuyers()
	{
		$admin = GetClass('ADMINBUYERS');
		$admin->CheckAdmin();
		$buyers = $admin->getBuyers();

		JSONOutput($buyers);
	}

	public function ChangeStatus()
	{
		$admin = GetClass('ADMINBUYERS');
		$admin->CheckAdmin();

		$admin->SetVar('buyer', $_REQUEST['buyer']);
		$buyer = $admin->ChangeStatus();

		JSONOutput($buyer);
	}

	public function ChangeBlockedStatus()
	{
		$admin = GetClass('ADMINBUYERS');
		$admin->CheckAdmin();

		$admin->SetVar('buyer', $_REQUEST['buyer']);
		$buyer = $admin->ChangeBlockedStatus();

		JSONOutput($buyer);
	}

	public function DeleteBuyer()
	{
		$admin = GetClass('ADMINBUYERS');
		$admin->CheckAdmin();

		$admin->SetVar('buyer', $_REQUEST['buyer']);
		$deleted = $admin->DeleteBuyer();

		JSONOutput($deleted);

	}

	public function getSettings()
	{
		$admin = GetClass('ADMINBUYERS');
		$admin->CheckAdmin();

		# don't need a full class for this just use what's existing
		$s = "SELECT * FROM trn_settings";
		$db = FetchQuery($s);

		$setting = array();
		foreach($db as $setting) {
			$settings[$setting['label']] = $setting['value'];
		}

		JSONOutput($settings);
	}

	public function saveSettings()
	{
		$admin = GetClass('ADMINBUYERS');
		$admin->CheckAdmin();

		$settings = $_REQUEST['settings'];

		foreach ($settings as $label => $setting)
		{
			$u = array(
				"value" => $setting,
			);

			$vars = array("label" => $label);
			UpdateArrayQuery("trn_settings", $u, "label = :label ", $vars);
		}

		JSONOutput(true);
	}

	/**
	 * Admin Remote Sellers
	 */

	public function getSellers()
	{
		$admin = GetClass('ADMINSELLERS');
		$admin->CheckAdmin();
		$sellers = $admin->getSellers();

		JSONOutput($sellers);
	}

	public function getSellerProducts()
	{
		$admin = GetClass('ADMINSELLERS');
		$admin->CheckAdmin();
		$products = $admin->getSellerProducts();

		JSONOutput($products);	
	}

	public function editProduct()
	{
		$admin = GetClass('ADMINSELLERS');
		$admin->CheckAdmin();
		$res = $admin->editProduct();

		JSONOutput($res);	
	}

	public function deleteSeller()
	{
		$admin = GetClass('ADMINSELLERS');
		$admin->CheckAdmin();

		$admin->SetVar('seller', $_REQUEST['seller']);
		$res = $admin->deleteSeller();

		JSONOutput($res);		
	}

	public function sellerQuery(){
		$seller = GetClass("SELLERACCOUNT");
	
		$seller->query($_REQUEST);
	}
}