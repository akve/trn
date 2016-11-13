<?php
    if( php_sapi_name() !== 'cli' ) {
        die("Meant to be run from command line");
    }

    function find_wordpress_base_path() {
        $dir = dirname(__FILE__);
        do {
            //it is possible to check for other files here
            if( file_exists($dir."/wp-config.php") ) {
                return $dir;
            }
        } while( $dir = realpath("$dir/..") );
        return null;
    }

    define( 'BASE_PATH', find_wordpress_base_path()."/" );
    define('WP_USE_THEMES', false);
    global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
    require(BASE_PATH . 'wp-load.php');

    $amazonid = "A39YCPDPUJU43J";//?ie=UTF8&ref_=ya";
	$amazon = GetClass('AMAZON');

	// review: R10IOMYTHI8EPU
	// PERMALINK:
	//https://www.amazon.com/review/R10IOMYTHI8EPU/ref=cm_cr_rdp_perm?ie=UTF8&ASIN=B008CGMPNO

	// register at:
	// https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html
	// DOES NOT WORK.

	$amazon->FindProduct("B008CGMPNO");

	//$verified = $amazon->VerifyAmazonID($amazonid);


    echo $verified;
	ob_end_clean();
    die();

    ?>