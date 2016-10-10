<?php
/**
 * Admin Class for the buyers
 */

class ADMINBUYERS
{
	public function HandlePage()
	{
		# angular handles the rest
		WPParseAndGet("admin_buyer");
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

	private function GetStatus($buyer)
	{
		# is there a coupon present?
		# they have to have multiple coupons for it to be locked
		$v = "SELECT * FROM trn_coupon_tracking ct 
		LEFT JOIN wp_atn_reviews r ON r.buyer_id = ct.buyer_id AND r.asin = ct.asin
		WHERE ct.buyer_id = :id AND r.id IS NULL";
		$vars = array("id" => $buyer['id']);
		$vr = FetchQuery($v, $vars);

		if (count($vr) >= 5) {
			$s = "SELECT * FROM trn_coupons c
			LEFT JOIN trn_products p ON p.id = c.productid
			WHERE coupon = :coupon";
			$vars = array("coupon" => $buyer['current_coupon']);
			$coupon = FetchOneQuery($s, $vars);

			$status = array(
				"product" => array(
						"asin" => $coupon['asin'],
						"name" => $coupon['product_name'],
						"img" => $coupon['img_sm'],
						"coupon" => $buyer['current_coupon'],
					),
				"status" => 1,
				"color" => "red",
				"status_text" => "Locked",
			);

			return $status;

		} else {
			# no coupon present so the account isn't locked down
			$status = array(
				"status" => 0,
				"color" => 'green',
				"status_text" => "Unlocked",
			);

			return $status;
		}
	}

	private function GetBlockedStatus($buyer)
	{
		if ($buyer['blocked'] == 0) {
			$visual = array(
				"status" => 0,
				"color" => "green",
				"status_text" => "UnBlocked",
			);
		} else {
			$visual = array(
				"status" => 1,
				"color" => "red",
				"status_text" => "Blocked",
			);
		}

		return $visual;
	}

	public function getBuyers()
	{
		$s = "SELECT id, user_id, contact_email, amazonid, verified_flag, phone, current_coupon, email_verified, blocked FROM wp_atn_buyer ";
		$buyers = FetchQuery($s);

		foreach($buyers as &$buyer) {
			# create the status based on other information
			$buyer['status'] = $this->GetStatus($buyer);
			$buyer['blocked'] = $this->GetBlockedStatus($buyer);
			$buyer['phone'] = preg_replace("/(\d{3})(\d{3})(\d{4})/", '($1) $2-$3', $buyer['phone']);
			$buyer['amazonid'] = $buyer['amazonid'] == "" ? "No" : "Yes";
			$buyer['couponreq'] = $this->CouponCount($buyer['id']);
			$buyer['reviewcount'] = $this->ReviewCount($buyer['id']);
			$buyer = CleanPDO($buyer);
		}

		return $buyers;
	}

	private function CouponCount($id) 
	{
		$s = "SELECT COUNT(*) as count FROM trn_coupon_tracking WHERE buyer_id = :id";
		$vars = array("id" => $id);
		$count = FetchOneQuery($s, $vars);

		return $count['count'];
	}

	private function ReviewCount($id) 
	{
		$s = "SELECT COUNT(*) as count FROM wp_atn_reviews WHERE buyer_id = :id";
		$vars = array("id" => $id);
		$count = FetchOneQuery($s, $vars);

		return $count['count'];
	}

	public function ChangeStatus()
	{
		# determine what the status is to begin with
		if ($this->buyer['status']['status'] == 1) {
			# already locked ,so we have to unlock it and keep a record
			$record = array(
				"buyerid" => $this->buyer['id'],
				"coupon" => $this->buyer['current_coupon'],
				"unlock_date" => time(),
			);

			DBInsert("trn_unlock_record", $record);

			$u = array(
				"current_coupon" => ""
			);
		} else {
			# currently unlocked so we lock it up
			$u = array(
				"current_coupon" => "locked"
			);
		}

		$vars = array("id" => $this->buyer['id']);
		UpdateArrayQuery("wp_atn_buyer", $u, "id = :id ", $vars);

		return $this->GetBuyer();
	}

	public function ChangeBlockedStatus()
	{
		if ($this->buyer['blocked']['status'] == 1)
			$blocked = 0;
		else
			$blocked = 1;

		$u = array(
			"blocked" => $blocked
		);

		$vars = array("id" => $this->buyer['id']);
		UpdateArrayQuery("wp_atn_buyer", $u, "id = :id", $vars);

		return $this->GetBuyer();
	}

	private function GetBuyer()
	{
		$s = "SELECT id, user_id, contact_email, amazonid, verified_flag, phone, current_coupon, email_verified, blocked FROM wp_atn_buyer WHERE id = :id ";
		$vars = array("id" => $this->buyer['id']);
		$buyer = FetchOneQuery($s, $vars);

		$buyer['status'] = $this->GetStatus($buyer);
		$buyer['blocked'] = $this->GetBlockedStatus($buyer);
		$buyer['phone'] = preg_replace("/(\d{3})(\d{3})(\d{4})/", '($1) $2-$3', $buyer['phone']);
		$buyer['amazonid'] = $buyer['amazonid'] == "" ? "No" : "Yes";
		$buyer['couponreq'] = $this->CouponCount($buyer['id']);
		$buyer['reviewcount'] = $this->ReviewCount($buyer['id']);

		return $buyer;
	}

	public function DeleteBuyer()
	{
		# delete the buyer from the the buyer table
		$vars = array("id" => $this->buyer['id']);
		$db = "DELETE FROM wp_atn_buyer WHERE id = :id";
		$db = UpdateQuery($db, $vars);

		# delete the buyer from the user table
		$vars = array("id" => $this->buyer['user_id']);
		$du = "DELETE FROM wp_users WHERE id = :id ";
		$du = UpdateQuery($du, $vars);

		if ($du && $db)
			return true;
		else
			return false;
	}
}