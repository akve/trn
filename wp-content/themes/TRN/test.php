<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
# simple test file for testing apis
include('../../../wp-load.php');
$amazonid = "A39YCPDPUJU43J?ie=UTF8&ref_=ya";
$amazon = GetClass('AMAZON');

$verified = $amazon->VerifyAmazonID($amazonid);
/*
$email = GetClass('EMAIL');
$vars = array(
	"Logo" => file_get_contents(TEMPLATE_PATH."Panels/TRN-logo.html"),
	"Year" => date('Y'),
	"id" => 26,
	"VerifyEmailHash" => BASE_URL."/wp-content/themes/TRN/remote.php?a=VerifySellerEmail&id=26&eh=".$email_hash,
);

$body = $email->SetEmailTemplate('NewAccount', $vars);
$subject = "TRN: Thank you for signing up!";
$send = $email->Send("robin@beapartof.com", $body, $subject, "trn@trustreviewnetwork.com");

if ($send) {
	echo $body;
} else {
	echo "Failed";
}*/