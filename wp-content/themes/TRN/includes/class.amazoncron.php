<?php
/**
 * Cronjob that runs every X hours to check for reviews
 *
 */
class AMAZONCRON
{
	/**
	 *	Main Cron Function
	 *	This will loop through all the users then subsequently loop through 
	 *	all their request products and check if the review is valid
	 *
	 *	If the review is valid it will open up their account to be able to get product codes again
	 */

	public function CheckReviews()
	{
		$dt = time() - 86400 + 1000; // check until yesterday, plus 1000 seconds as check time
		$s = "SELECT b.*, p.id productid FROM trn_coupon_tracking ct
					inner join wp_atn_buyer b on b.id = ct.buyer_id 
					inner join trn_coupons c on c.id = ct.couponid
					inner join trn_products p on c.productid = p.id
					inner join wp_atn_sellers s on c.seller_id = s.id
					where ct.got_review = 0 and ifnull(last_check,0) < " . $dt;
		$users = FetchQuery($s);

		foreach($users as $user)
		{
			$this->FindReview($user, "", $user["productid"]); // TBD!!!
		}
	}

	public function FindReview($user, $customLink, $current_product)
	{
		$amazon_user_id = $user['amazonid'];



		//if ($user['current_coupon'] == "" || $user['current_coupon'] == "locked")
		//	return false;

		# now we get all associated products from the database
		$s = "SELECT * FROM trn_coupons c
		LEFT JOIN trn_products p ON p.id = c.productid
		LEFT JOIN trn_coupon_tracking t ON buyer_id = :bid and t.couponid = c.id
		WHERE p.id = :pid";
		//echo $s;
		$vars = array("pid" => $current_product, "bid" => $user['id']);
		//var_dump($vars);
		$product = FetchOneQuery($s, $vars);

		//var_dump($product);

		# let's check if they've already made a review for this product
		/*$s = "SELECT * FROM wp_atn_reviews WHERE buyer_id = :buyerid AND asin = :asin ";
		$vars = array("buyerid" => $user['id'], "asin" => $product['asin']);
		$existing = FetchOneQuery($s, $vars);
		*/
		/*if ($existing) {
			# we should just clear out their coupon because they can't buy anything
			$u = "UPDATE wp_atn_buyer SET current_coupon = '' WHERE id = :id";
			$vars = array("id" => $user['id']);
			UpdateQuery($u, $vars);

			return false;
		}*/
		if ($product['got_review'] > 0) {
			//$this->Error("already reviewed");
			return false;
		}

		# now that we have a user and a product we can scrape their review page
		//$link = "https://www.amazon.com/gp/cdp/member-reviews/{$amazon_user_id}/";
		$link = "https://www.amazon.com/glimpse/timeline/{$amazon_user_id}?isWidgetOnly=true&filter=review";
		//$link = "http://www.amazon.com/gp/cdp/member-reviews/{$amazon_user_id}/";

		if ($customLink) {
			$link = $customLink;
		}

		$asin = $product['asin'];

		# get the class that we made
		$amazon = GetClass('AMAZON');

		$verified = $amazon->LinkReview($link, $asin, $amazon_user_id);
		//echo $link;
		//echo $verified;

		if ($verified)
		{
			# we only update the database if the review is verified
			# clear the current coupon
			/*$u = "UPDATE wp_atn_buyer SET current_coupon = '' WHERE id = :id";
			$vars = array("id" => $user['id']);
			UpdateQuery($u, $vars);*/

			$u = "UPDATE trn_coupon_tracking SET got_review = :reviewdate,review_score=:stars WHERE trackind_id = :id";
			$vars = array("id" => $product['trackind_id'], "stars" => $verified['stars'], "reviewdate" => time());
			UpdateQuery($u, $vars);

			# let's also track that it got reviewd in the process
			/*$track_reviews = array(
				"buyer_id" => $user['id'],
				"amazonid" => $amazon_user_id,
				"asin" => $asin,
				"stars" => $verified['stars'],
				"coupon" => $user['current_coupon'],
				"created" => time(),
			);

			DBInsert("wp_atn_reviews", $track_reviews);*/

			return true;
		} else {
			$u = "UPDATE trn_coupon_tracking SET last_check = :reviewdate WHERE trackind_id = :id";
			$vars = array("id" => $product['trackind_id'], "reviewdate" => time());
			UpdateQuery($u, $vars);
		}
		
		return false;
	}

	public function SendReviewNotifications(){
		$dt2 = time() - (86400 * 30); // max 30 days late order
		$dt = time() - (86400 * (TESTMODE=="true"?1:7)); // 7 days to check

		$s = "SELECT b.*, p.id productid, p.product_name, p.asin, ct.last_order_check, ct.inserted, ct.trackind_id FROM trn_coupon_tracking ct
					inner join wp_atn_buyer b on b.id = ct.buyer_id 
					inner join trn_coupons c on c.id = ct.couponid
					inner join trn_products p on c.productid = p.id
					inner join wp_atn_sellers s on c.seller_id = s.id
					where ct.got_review = 0 and ifnull(last_order_check,0) < " . $dt . " and ifnull(ct.inserted,0) > " . $dt2 . " and ifnull(ct.inserted,0) < " . $dt;

		//$s .= " and buyer_id = 3278"; // security measure! don't send emails ) 			
		echo $s;
		$users = FetchQuery($s);
		var_dump($users);

		foreach($users as $user)
		{
			// update tracking to send once
			$u = "UPDATE trn_coupon_tracking SET last_order_check = :reviewdate WHERE trackind_id = :id";
			$vars = array("id" => $user['trackind_id'], "reviewdate" => time());
			UpdateQuery($u, $vars);

			$timepassed = intval((time() - $user['inserted']) / 86400); 
			echo $timepassed;
			$template = 0;
			if (TESTMODE=="true") {
				$template = 1;
			} else {
				if ($timepassed >= 7 && $timepassed < 10) {
					$template = 1;
				}
				if ($timepassed >= 14 && $timepassed < 17) {
					$template = 2;
				}
				if ($timepassed >= 20 && $timepassed < 22) {
					$template = 3;
				}
			}
			var_dump(array("buyer" => $user['id'], "product" => $user["product_name"], "template" => $template, "timepassed" => $timepassed));

			# once we have the new buyer inserted let's send an email
			$email = GetClass('EMAIL');
			$d = $user['inserted'];
			$d = new DateTime("@$d");
			$file = file_get_contents(TEMPLATE_PATH."Panels/TRN-logo.html");
	 		$file = preg_replace("/%%TEMPLATE_Directory%%/", get_template_directory_uri(), $file);
			$vars = array(
				"Logo" => $file,
				"timepassed" => $timepassed,
				"bought_on" => date_format($d,"m/d/Y"),
				"Year" => date('Y'),
				"link" => BASE_URL."buyer-profile"
			);

			foreach ($user as $k => $v)
			{
				$vars[$k] = $v;
			}
			var_dump($vars);

			$body = $email->SetEmailTemplate('ReviewReminder1' , $vars); // . $template
			$subject = "TRN: Review reminder";
			$email->Send($vars['contact_email'], $body, $subject, "trn@trustreviewnetwork.com");

		}
	}
}