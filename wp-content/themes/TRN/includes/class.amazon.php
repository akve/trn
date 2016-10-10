<?php
/**
 *	API to connect to Amazon API
 *	Hanndles product searches, and review counting
 *
 *	
 *
 */

Class AMAZON
{
	# private variables that will be used to connect
	private $AssociateID = "aint0e-20";//"dowmini-20";
	private $AWSKeyID = "AKIAIYZVWG6ITPXD2SNA";//"AKIAJK6D2KO34SNIMCBQ";
	private $AWSSecretKey = "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";//"vLo1GMjUs02v4UQtQqalT/aWjkQB6qPgY5UZZkDt";
	private $AWShost = "webservices.amazon.com";
	private $AWSuri = "/onca/xml";
	private $Version = "2011-08-01";
	private $Service = "AWSECommerceService";

	/**
	 *	Find Product ASIN
	 *	This will get all relevant information from a product given it's ASIN number	
	 *
	 *	@param product ASIN
	 *	@return product Array
	 */

	public function FindProduct($asin)
	{
		# first thing we do is create the query string as an array
		$query = array(
			"Operation" => "ItemLookup",
			"ResponseGroup" => "ItemAttributes,Images,EditorialReview",
			"ItemId" => $asin,
			"IdType" => "ASIN",
			"Condition" => "All",
			"AssociateTag" => $this->AssociateID,
		);

		# let's convert this query into something that the api can use
		$query = $this->SignRequest($query);

		# this is the raw xml
		$xml = $this->CurlRequest($query);

		# convert into usable array
		$data = $this->ProcessXML($xml);

		$product = array(
			"attributes" => $data['Items']['Item']['ItemAttributes'],
			"description" => strip_tags($data['Items']['Item']['EditorialReviews']['EditorialReview']['Content']),
			"images" => $data['Items']['Item']['ImageSets'],
		);

		# we return the item attributes
		return $product;
	}

	/**
	 *	Get Review by permalink
	 *	This scrapes the page and searches for the userid and asin to check if the review is valid
	 *	This can also be used to scrape user pages because it searches for the same items
	 *
	 *	@param product ASIN
	 *	@param userid - counts reviews made by a specific userid
	 *	@return true/false if it's verified
	 */
	public function LinkReview($link, $asin = false, $userid = false)
	{
		# if no asin or userid return false
		if (!$asin || !$userid)
			return false;

		# first thing we do is get the page html
		$html = $this->CurlRequest($link);

		# scrape for asin
		preg_match("/".$asin."/", $html, $c_asin);

		# scrape for userid
		preg_match("/".$userid."/", $html, $c_userid);

		# to determin if the link is valid all we have to do is make sure that the compare arrays aren't empty
		if (!empty($c_userid) && !empty($c_asin)) {
			# we have to get the permalink
			preg_match("/http:\/\/www.amazon.com\/review\/(.{5,20})\/ref\=(.{1,40})\?ie=UTF8\&ASIN\=".$asin."\#wasThisHelpful\" \>/", $html, $permalink);
			$pl = "http://www.amazon.com/review/".$permalink[1]."/";

			# now we scrape this specific review page to get the star rating
			$reviewhtml = $this->CurlRequest($pl);
			preg_match("/\"(.{1,5}) out of 5 stars\"/", $reviewhtml, $stars);

			$values = array(
				"stars" => $stars[1],
			);

			return $values;
		}

		return false;
	}

	/**
	 * Verify Amazon Account
	 *
	 * @param string that could be amazon account
	 * @return true/false
	 */
	public function VerifyAmazonID($id)
	{
		if (!isset($id) || $id == "")
			return false;

		# create a link
		$link = "https://www.amazon.com/gp/profile/{$id}";

		# first thing we do is get the page html
		$html = $this->CurlRequest($link);

		# now we just scrape for a specific identifier
		preg_match("/Your Amazon.com/", $html, $verify);

		if (!empty($verify)) {
			return true;
		}

		return false;
	}

	/**
	 *	Sign Request
	 *	from a query, this adds the keys, ids,
	 *	timestamp and signature
	 *
	 *	@param base query
	 *	@return signed query
	 */
	public function SignRequest($params)
	{
		# some paramters
		$method = 'GET';
		$host = $this->AWShost;
		$uri = '/onca/xml';

		# additional parameters
		$params['Service'] = $this->Service;
		$params['AWSAccessKeyId'] = $this->AWSKeyID;
		# GMT timestamp
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		# API version
		$params['Version'] = $this->Version;

		# sort the parameters
		ksort($params);

		# create the canonicalized query
		$canonicalized_query = array();
		foreach ($params as $param=>$value)
		{
		$param = str_replace('%7E', '~', rawurlencode($param));
		$value = str_replace('%7E', '~', rawurlencode($value));
		$canonicalized_query[] = $param.'='.$value;
		}
		$canonicalized_query = implode('&', $canonicalized_query);

		# create the string to sign
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;

		# calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->AWSSecretKey, TRUE));

		# encode the signature for the request
		$signature = str_replace('%7E', '~', rawurlencode($signature));

		# create request
		$request = 'http://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;

		return $request;
	}

	/**
	 *	Curl Request
	 *	only need this if there's not already a curl function
	 *	actually probably better to use this because it's tailored to this
	 *
	 *	@param url
	 *	@return xml formatted response
	 */
	private function CurlRequest($url)
	{
		$ch = curl_init();
		# set url
		curl_setopt($ch, CURLOPT_URL, $url);
		# return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		# $output contains the output string
		$output = curl_exec($ch);
		# close curl resource to free up system resources
		curl_close($ch);

		return $output;
	}

	/**
	 *	Process XML
	 *	take the xml and return an array 
	 *	(maybe something else at a later date)
	 *
	 *	@param base query
	 *	@return signed query
	 */
	private function ProcessXML($xml_string)
	{
		$xml = simplexml_load_string($xml_string);
		$json = json_encode($xml);

		return json_decode($json, TRUE);
	}

	/**
	 *	Output json array
	 *	just here in case it's not in the init file of wherever we're putting this
	 *
	 *	@param an array
	 */
	private function JSONOutput($output)
	{
		if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
		header('Content-type: application/json');
		header('Content-Encoding: gzip');
		echo json_encode($output);
	}


}