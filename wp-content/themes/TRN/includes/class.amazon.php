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
	private $AssociateID = "Strolls20-20";// "aint0e-20";//"dowmini-20";
	private $AWSKeyID = "AKIAJXX7LE4RRPUHHWQA";//"AKIAIYZVWG6ITPXD2SNA";//"AKIAJK6D2KO34SNIMCBQ";
	private $AWSSecretKey = "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"; //"7VncBBsZ69WGAFagaxZAW7GIEWLkq5U/sE3MOU1S";//"vLo1GMjUs02v4UQtQqalT/aWjkQB6qPgY5UZZkDt";
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
			"Condition" => "All"
		);

		//var_dump($query);

		# let's convert this query into something that the api can use
//		$query = $this->SignRequest($query);


		$query = $this->aws_signed_request("com", $query, $this->AWSKeyID, $this->AWSSecretKey, $this->AssociateID, '2011-08-01');

		//echo $query;

		# this is the raw xml
		$xml = $this->CurlRequest($query);

//echo $xml;

		# convert into usable array
		$data = $this->ProcessXML($xml);



		$product = array(
			"attributes" => $data['Items']['Item']['ItemAttributes'],
			"description" => strip_tags($data['Items']['Item']['EditorialReviews']['EditorialReview']['Content']),
			"big_image" => $data['Items']['Item']['LargeImage']['URL'],
			"med_image" => $data['Items']['Item']['MediumImage']['URL'],
			"thumb_image" => $data['Items']['Item']['ThumbnailImage']['URL']
			//"images" => $data['Items']['Item']['ImageSets'],
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
		//echo $link;
		$html = $this->CurlRequest($link);
		//echo $asin;

		//echo $html;

		# scrape for asin
		preg_match("/".$asin."/", $html, $c_asin);

		# scrape for userid
		preg_match("/".$userid."/", $html, $c_userid);

		//var_dump($c_asin);
		//var_dump($c_userid);
		//echo "USERID ". $c_userid;

		# to determin if the link is valid all we have to do is make sure that the compare arrays aren't empty
		if (!empty($c_userid) && !empty($c_asin)) {
			# we have to get the permalink
			//echo $html;
			/*
			LOOKING FOR: 
			<div id="glimpse-ephemeral-metadata-Glimpse-REVIEW-R10IOMYTHI8EPU"
			    data-activity-timestamp="1341082342" 
			    data-asin="B008CGMPNO"
			    data-customer-id="A3OAVPLLMJTXQ0"
			    data-product-id="B008CGMPNO"
			    data-story-type="REVIEW"
			    data-question-id="" >
			*/
			preg_match("/amazon\.com\/review\/(.{5,20}))\//", $link, $url_link);
			if ($url_link && count($url_link) > 0) {
				// this is custom link
				preg_match("/\"(.{1,5}) out of 5 stars\"/", $html, $stars);
			} else {
				preg_match("/Glimpse-REVIEW-(.{5,20})\"([^>]+)".$asin."\"/", $html, $permalink);
				
				//preg_match("/http:\/\/www.amazon.com\/review\/(.{5,20})\/ref\=(.{1,40})\?ie=UTF8\&ASIN\=".$asin."\#wasThisHelpful\" \>/", $html, $permalink);
				//var_dump($permalink);
				$pl = "https://www.amazon.com/review/".$permalink[1]."/";
				//echo $pl;

				# now we scrape this specific review page to get the star rating
				$reviewhtml = $this->CurlRequest($pl);
				preg_match("/\"(.{1,5}) out of 5 stars\"/", $reviewhtml, $stars);
				//var_dump($stars);
			}

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


	public function aws_signed_request($region, $params, $public_key, $private_key, $associate_tag=NULL, $version='2011-08-01')
	{
	    /*
	    Copyright (c) 2009-2012 Ulrich Mierendorff

	    Permission is hereby granted, free of charge, to any person obtaining a
	    copy of this software and associated documentation files (the "Software"),
	    to deal in the Software without restriction, including without limitation
	    the rights to use, copy, modify, merge, publish, distribute, sublicense,
	    and/or sell copies of the Software, and to permit persons to whom the
	    Software is furnished to do so, subject to the following conditions:

	    The above copyright notice and this permission notice shall be included in
	    all copies or substantial portions of the Software.

	    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
	    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
	    DEALINGS IN THE SOFTWARE.
	    */
	    
	    /*
	    Parameters:
	        $region - the Amazon(r) region (ca,com,co.uk,de,fr,co.jp)
	        $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
	                        "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
	        $public_key - your "Access Key ID"
	        $private_key - your "Secret Access Key"
	        $version (optional)
	    */
	    
	    // some paramters
	    $method = 'GET';
	    $host = 'webservices.amazon.'.$region;
	    $uri = '/onca/xml';
	    
	    // additional parameters
	    $params['Service'] = 'AWSECommerceService';
	    $params['AWSAccessKeyId'] = $public_key;
	    // GMT timestamp
	    $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
	    // API version
	    $params['Version'] = $version;
	    if ($associate_tag !== NULL) {
	        $params['AssociateTag'] = $associate_tag;
	    }
	    
	    // sort the parameters
	    ksort($params);
	    
	    // create the canonicalized query
	    $canonicalized_query = array();
	    foreach ($params as $param=>$value)
	    {
	        $param = str_replace('%7E', '~', rawurlencode($param));
	        $value = str_replace('%7E', '~', rawurlencode($value));
	        $canonicalized_query[] = $param.'='.$value;
	    }
	    $canonicalized_query = implode('&', $canonicalized_query);
	    
	    // create the string to sign
	    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
	    
	    // calculate HMAC with SHA256 and base64-encoding
	    $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));
	    
	    // encode the signature for the request
	    $signature = str_replace('%7E', '~', rawurlencode($signature));
	    
	    // create request
	    $request = 'http://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
	    
	    return $request;
	}

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