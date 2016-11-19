<?php

include('../../../wp-load.php');

//include('testinit.php');
$action = $_REQUEST['action'];

# kill it if there's no action
if (!$action || $action == "") exit();

//var_dump($_REQUEST);
$GLOBALDATA = json_decode(file_get_contents('php://input'), true);
//var_dump($GLOBALDATA);
class REMOTE {
	public function test() {
		echo "test ok";
	}

	public function query($data){
		$vars = array();

		if ($data["target"] == "buyers") {
			$sql = "select FIELDS from (select concat(wb.first_name,' ', wb.last_name) name, wb.*, 
					(select count(trackind_id) from trn_coupon_tracking where buyer_id = wb.id) total_orders,
					(select count(trackind_id) from trn_coupon_tracking where buyer_id = wb.id and got_review = 0) orders,
					(select count(trackind_id) from trn_coupon_tracking where buyer_id = wb.id and got_review > 0) reviews, 
					(select avg(review_score) from trn_coupon_tracking where buyer_id = wb.id and got_review > 0) avg_score
					from wp_atn_buyer wb 
					) innertbl ";
		} else {
			$sql = "SELECT * FROM wp_atn_buyers";
		}
		// calc totals
		$sqlTotal = str_replace("FIELDS", "count(*) cnt", $sql);
		$existing = FetchOneQuery($sqlTotal, $vars);
		$totals = $existing["cnt"];

		$sql = str_replace("FIELDS", "*", $sql);

		if ($data["where"]) {
			$sql .= " WHERE " . $data["where"]  . " ";
		}
		if ($data["order"]) {
			$sql .= " ORDER BY " . $data["order"] . " ";
		}

		if ($data["mode"] == "csv") {
			//header('Content-Type: text/csv; charset=utf-8');
			//header('Content-Disposition: attachment; filename=data.csv');

			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');

			$existing = FetchQuery($sql, $vars);
			if (count($existing) >0) {
				$row1 = $existing[0];
				$keys = array();
				$row1 = CleanPDO($row1);
				foreach($row1 as $key=>$value) {
					$keys[] = $key;
				}
				fputcsv($output, $keys);
				foreach($existing as $row) {
					CleanPDO($row);
					$row1 = array();
					foreach($keys as $key) {
						$row1[] = $row[$key];
					}
					fputcsv($output, $row1);
				}
			}
			/*foreach($existing as $row) {
				CleanPDO($row);
				fputcsv($output, $row);
			}*/

			//while ($row = mysql_fetch_assoc($rows)) fputcsv($output, $row);
		} else {
			if ($data["limit"]) {
				$sql .= " LIMIT " . $data["start"] ."," . $data["limit"] . " ";
			}
			//$vars = array("email" => $data["a"]);
			$existing = FetchQuery($sql, $vars);

			JSONOutput(array("totals" => $totals, "rows" => $existing));
		}
	}
	public function update($data) {


		if ($data["mode"] == "buyers") {
			$table = "wp_atn_buyer";
		}
		$changes = "";
		foreach($data["changes"] as $key => $value) {
			if ($changes != "") $changes .= ", ";
		   $changes .= $key . " = :" . $key;
		}

		$b = "UPDATE ".$table." SET ". $changes ." WHERE id = :id ";
		$data["changes"]["id"] = $data["id"];

		if (!UpdateQuery($b, $data["changes"])) {
			$this->Error = "Database Error 1";
			JSONOutput(array("error" => "Failed to update"));
			return false;
		}
		JSONOutput(array("status" => "OK"));

	}

	public function get_buyer($data){
		$s = "SELECT trn_products.*, trn_coupon_tracking.* FROM trn_coupon_tracking inner join trn_coupons on trn_coupons.id = trn_coupon_tracking.couponid  inner join trn_products on  trn_coupons.productid = trn_products.id  where buyer_id= " . $data['id'] . " order by inserted desc";
		//echo $s;

		$products = FetchQuery($s, $vars);
		$cleaned = array();
		foreach($products as $p => $values)
		{
			$cleaned[] = CleanPDO($values);
		}
		
		JSONOutput(array("products" => $cleaned));
	}
}

$remote = new REMOTE();
$remote->$action($GLOBALDATA);
?>