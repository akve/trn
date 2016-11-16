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
			$this->FindReview($user, "");
		}
	}

	public function FindReview($user, $customLink)
	{
		$amazon_user_id = $user['amazonid'];

		if ($user['current_coupon'] == "" || $user['current_coupon'] == "locked")
			return false;

		# now we get all associated products from the database
		$s = "SELECT * FROM trn_coupons c
		LEFT JOIN trn_products p ON p.id = c.productid
		WHERE c.id = :coupon";
		$vars = array("coupon" => $user['current_coupon']);
		$product = FetchOneQuery($s, $vars);

		# let's check if they've already made a review for this product
		$s = "SELECT * FROM wp_atn_reviews WHERE buyer_id = :buyerid AND asin = :asin ";
		$vars = array("buyerid" => $user['id'], "asin" => $product['asin']);
		$existing = FetchOneQuery($s, $vars);

		if ($existing) {
			# we should just clear out their coupon because they can't buy anything
			$u = "UPDATE wp_atn_buyer SET current_coupon = '' WHERE id = :id";
			$vars = array("id" => $user['id']);
			UpdateQuery($u, $vars);

			return false;
		}

		# now that we have a user and a product we can scrape their review page
		$link = "http://www.amazon.com/gp/cdp/member-reviews/{$amazon_user_id}/";

		if ($customLink) {
			$link = $customLink;
		}

		$asin = $product['asin'];

		# get the class that we made
		$amazon = GetClass('AMAZON');

		$verified = $amazon->LinkReview($link, $asin, $amazon_user_id);

		if ($verified)
		{
			# we only update the database if the review is verified
			# clear the current coupon
			$u = "UPDATE wp_atn_buyer SET current_coupon = '' WHERE id = :id";
			$vars = array("id" => $user['id']);
			UpdateQuery($u, $vars);

			# let's also track that it got reviewd in the process
			$track_reviews = array(
				"buyer_id" => $user['id'],
				"amazonid" => $amazon_user_id,
				"asin" => $asin,
				"stars" => $verified['stars'],
				"coupon" => $user['current_coupon'],
				"created" => time(),
			);

			DBInsert("wp_atn_reviews", $track_reviews);

			return true;
		}
		
		return false;
	}
}