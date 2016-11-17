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
		$s = "SELECT * FROM wp_atn_buyer WHERE amazonid != '' AND current_coupon != '' AND current_coupon != 'locked'";
		$users = FetchQuery($s);

		foreach($users as $user)
		{
			$this->FindReview($user, "", ""); // TBD!!!
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
		}
		
		return false;
	}
}