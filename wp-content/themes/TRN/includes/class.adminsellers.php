<?php
/**
 * Admin Class for the buyers
 */

class ADMINSELLERS
{
	public function HandlePage()
	{
		# angular handles the rest
		WPParseAndGet("admin_seller");
	}

	public function SetVar($var, $value)
	{
		$this->$var = $value;
	}

	public function CheckAdmin()
	{
		$userid = get_current_user_id();

		$s = "SELECT * FROM wp_users u
		LEFT JOIN wp_usermeta c ON c.user_id = u.ID AND c.meta_key LIKE 'wp_capabilities'
		WHERE u.ID = :userid";
		$vars = array(
			"userid" => $userid,
		);
		$admin = FetchOneQuery($s, $vars);

		$capabilities = unserialize($admin['meta_value']);

		if (isset($capabilities['administrator']) && $capabilities['administrator'] == 1) {
			return true;
		} else {
			JSONOutput(array("error" => "Invalid User"));
		}
	}

	public function getSellers()
	{
		$s = "SELECT id, user_id, contact_email, FirstName, LastName, Company, Phone, email_verified FROM wp_atn_sellers ";
		$sellers = FetchQuery($s);

		foreach($sellers as &$seller) {
			$buyer = CleanPDO($buyer);
		}

		return $sellers;
	}

	public function getSellerProducts()
	{
		$s = "SELECT id, asin, product_name, keywords FROM trn_products WHERE seller_id = :id";
		$vars = array("id" => $_REQUEST['id']);
		$products = FetchQuery($s, $vars);

		# we have to also get the # of reviews and # of couponse
		foreach($products as &$product)
		{
			# get reviews
			$r = "SELECT Count(*) as reviews FROM wp_atn_reviews WHERE asin = :asin";
			$vars = array("asin" => $product['asin']);
			$reviews = FetchOneQuery($r, $vars);
			$product['reviews'] = $reviews['reviews'];

			# now let's get the coupons
			$c = "SELECT Count(*) as total FROM trn_coupons WHERE productid = :id";
			$vars = array("id" => $product['id']);
			$total = FetchOneQuery($c, $vars);
			$product['total_coupons'] = $total['total'];

			$c = "SELECT Count(*) as total FROM trn_coupon_tracking WHERE asin = :asin";
			$vars = array("asin" => $product['asin']);
			$total = FetchOneQuery($c, $vars);
			$product['used_coupons'] = $total['total'];
		}

		return $products;
	}

	public function editProduct()
	{
		$product = CleanPDO($_POST['product']);
		$id = $product['id'];

		# items from the javascript
		unset($product['id']);
		unset($product['$$hashKey']);
		unset($product['loading']);
		unset($product['reviews']);
		unset($product['total_coupons']);
		unset($product['used_coupons']);

		$vars = array("id" => $id);
		$u = UpdateArrayQuery("trn_products", $product, "id = :id ", $vars);

		return $u;
	}

	public function deleteSeller()
	{
		# delete the buyer from the the buyer table
		$vars = array("id" => $this->seller['id']);
		$db = "DELETE FROM wp_atn_sellers WHERE id = :id";
		$db = UpdateQuery($db, $vars);

		# delete the buyer from the user table
		$vars = array("id" => $this->seller['user_id']);
		$du = "DELETE FROM wp_users WHERE id = :id ";
		$du = UpdateQuery($du, $vars);


		if ($du && $db)
			return true;
		else
			return false;
	}
}