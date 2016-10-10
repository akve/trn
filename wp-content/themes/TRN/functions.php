<?php
/**
 * Twenty Sixteen functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

/**
 * Twenty Sixteen only works in WordPress 4.4 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.4-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
}

if ( ! function_exists( 'twentysixteen_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * Create your own twentysixteen_setup() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 */


if(isset($_POST['action']) && $_POST['action'] == 'add_product') {
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		$seller->seller_add_product_post($_POST);
	 header("Location: admin.php?page=edit&asin=".$_POST['asin']."");
	 exit();

}

if(isset($_POST['action']) && $_POST['action'] == 'create_campaign') {
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		$seller->seller_create_campaign_post($_POST);
	 header("Location: admin.php?page=seller_list");
	 exit();

}

if(isset($_POST['action']) && $_POST['action'] == 'edit_product') {
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		$seller->seller_edit_product_post($_POST);
	 header("Location: admin.php?page=edit&asin=".$_POST['asin']."");
	 exit();

}

if(isset($_POST['action']) && $_POST['action'] == 'update_campaign') {
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		$seller->seller_update_campaign_post($_POST);
	 header("Location: admin.php?page=seller_manage&id=".$_POST['id']."");
	 exit();

}

if(isset($_POST['action']) && $_POST['action'] == 'add_coupon') {
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		$seller->seller_coupon_insert($_POST);
	 header("Location: admin.php?page=seller_coupons");
	 exit();
}

if(isset($_POST['action'])  &&  $_POST['action'] == 'upload' && isset($_POST['submit']) && isset($_FILES['filename']['tmp_name'])) {
	if($_FILES['filename']['tmp_name'] == '') {
		header("Location: admin.php?page=seller_coupons");
		exit();
	}
require_once('includes/class.sellers.php');
  global $wpdb;
  $user_ID = get_current_user_id();
  $seller_id = new stdClass();
  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
  $seller_id = $seller_id[0]->id;
  // get list of campaigns available to seller.
  $available_campaign_ids  = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT id FROM `wp_atn_campaign` Where `seller_id` = %s", $seller_id));
  $a = new stdClass();
  $a = $available_campaign_ids;
  $arr = array();
  foreach($a as $k => $v){
    $arr[] = $v->id;
  }

  if (isset($_POST['submit'])) {
          if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
         }
         $handle = fopen($_FILES['filename']['tmp_name'], "r");
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          	if(is_numeric($data[0]) && strlen($data[1]) < 32 && in_array($data[0],$arr)){
            $t = time();
              $import="INSERT into `perfedk3_trn`.`wp_atn_coupons`(`id`, `seller_id`, `campaign_id`, `coupon`, `status`, `created`, `updated`) values(NULL, ".$seller_id.",".$data[0].",'".$data[1]."','enabled',".$t.",".$t.")";
              $insert_coupons = $wpdb->query($import);
              }
        }
       fclose($handle);
}

header("Location: admin.php?page=seller_coupons");
exit();
}





function seller_product_image_upload(){
	//var_dump($_POST);exit();
	if(isset($_POST['action'])  &&  $_POST['action'] == 'change_image' && isset($_POST['submit']) && isset($_FILES['filename']['tmp_name'])) {
		if($_FILES['filename']['tmp_name'] == '') {
			header("Location: admin.php?page=edit&asin=".$_POST['asin']."");
			exit();
		}
	require_once('includes/class.sellers.php');
	  global $wpdb;
	  $user_ID = get_current_user_id();
	  $seller_id = new stdClass();
	  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
	  $seller_id = $seller_id[0]->id;
	  // get list of campaigns available to seller.

	    global $wpdb;
	    $asin = $_POST['asin'];
	    $user_ID = get_current_user_id();
	    $seller_id = new stdClass();
	    $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
	    $seller_id = $seller_id[0]->id;
	    if (isset($_POST['submit'])) {
         $upload = wp_upload_bits( $_FILES['filename']['name'], null, file_get_contents( $_FILES['filename']['tmp_name'] ));
         $image_path = $upload['url'];
         $t = time();
         $update = "UPDATE  `perfedk3_trn`.`wp_atn_product` SET `product_image` = '".$image_path."', `updated` = ".$t."";
         $update_product = $wpdb->query($update);
         $html ='Upload Complete';
         return $html;
	      }

	    header("Location: admin.php?page=edit&asin=".$asin."");
	    exit();
 	}
}

/*
//          if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
  //            echo "<h1>" . "File ". $_FILES['filename']['name'] ." uploaded successfully." . "</h1>";
  //            echo "<h2>Displaying contents:</h2>";
          //    readfile($_FILES['filename']['tmp_name']);
  //       }
*/


function twentysixteen_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Twenty Sixteen, use a find and replace
	 * to change 'twentysixteen' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'twentysixteen', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 9999 );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'twentysixteen' ),
		'social'  => __( 'Social Links Menu', 'twentysixteen' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'status',
		'audio',
		'chat',
	) );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'css/editor-style.css', twentysixteen_fonts_url() ) );
}
endif; // twentysixteen_setup
add_action( 'after_setup_theme', 'twentysixteen_setup' );

/**
 * Sets the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'twentysixteen_content_width', 840 );
}
add_action( 'after_setup_theme', 'twentysixteen_content_width', 0 );

/**
 * Registers a widget area.
 *
 * @link https://developer.wordpress.org/reference/functions/register_sidebar/
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'twentysixteen' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar.', 'twentysixteen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Content Bottom 1', 'twentysixteen' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Appears at the bottom of the content on posts and pages.', 'twentysixteen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => __( 'Content Bottom 2', 'twentysixteen' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Appears at the bottom of the content on posts and pages.', 'twentysixteen' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'twentysixteen_widgets_init' );

if ( ! function_exists( 'twentysixteen_fonts_url' ) ) :
/**
 * Register Google fonts for Twenty Sixteen.
 *
 * Create your own twentysixteen_fonts_url() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @return string Google fonts URL for the theme.
 */
function twentysixteen_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Merriweather, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Merriweather font: on or off', 'twentysixteen' ) ) {
		$fonts[] = 'Merriweather:400,700,900,400italic,700italic,900italic';
	}

	/* translators: If there are characters in your language that are not supported by Montserrat, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Montserrat font: on or off', 'twentysixteen' ) ) {
		$fonts[] = 'Montserrat:400,700';
	}

	/* translators: If there are characters in your language that are not supported by Inconsolata, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Inconsolata font: on or off', 'twentysixteen' ) ) {
		$fonts[] = 'Inconsolata:400';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;
}
endif;

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'twentysixteen_javascript_detection', 0 );

/**
 * Enqueues scripts and styles.
 *
 * @since Twenty Sixteen 1.0
 */
function twentysixteen_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'twentysixteen-fonts', twentysixteen_fonts_url(), array(), null );

	// Add Genericons, used in the main stylesheet.
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.4.1' );

	// Theme stylesheet.
	wp_enqueue_style( 'twentysixteen-style', get_stylesheet_uri() );

	// Load the Internet Explorer specific stylesheet.
	wp_enqueue_style( 'twentysixteen-ie', get_template_directory_uri() . '/css/ie.css', array( 'twentysixteen-style' ), '20150930' );
	wp_style_add_data( 'twentysixteen-ie', 'conditional', 'lt IE 10' );

	// Load the Internet Explorer 8 specific stylesheet.
	wp_enqueue_style( 'twentysixteen-ie8', get_template_directory_uri() . '/css/ie8.css', array( 'twentysixteen-style' ), '20151230' );
	wp_style_add_data( 'twentysixteen-ie8', 'conditional', 'lt IE 9' );

	// Load the Internet Explorer 7 specific stylesheet.
	wp_enqueue_style( 'twentysixteen-ie7', get_template_directory_uri() . '/css/ie7.css', array( 'twentysixteen-style' ), '20150930' );
	wp_style_add_data( 'twentysixteen-ie7', 'conditional', 'lt IE 8' );

	// Load the html5 shiv.
	wp_enqueue_script( 'twentysixteen-html5', get_template_directory_uri() . '/js/html5.js', array(), '3.7.3' );
	wp_script_add_data( 'twentysixteen-html5', 'conditional', 'lt IE 9' );

	wp_enqueue_script( 'twentysixteen-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151112', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'twentysixteen-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20151104' );
	}

	wp_enqueue_script( 'twentysixteen-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), '20151204', true );

	wp_localize_script( 'twentysixteen-script', 'screenReaderText', array(
		'expand'   => __( 'expand child menu', 'twentysixteen' ),
		'collapse' => __( 'collapse child menu', 'twentysixteen' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'twentysixteen_scripts' );




/**
 * Adds custom classes to the array of body classes.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $classes Classes for the body element.
 * @return array (Maybe) filtered body classes.
 */
function twentysixteen_body_classes( $classes ) {
	// Adds a class of custom-background-image to sites with a custom background image.
	if ( get_background_image() ) {
		$classes[] = 'custom-background-image';
	}

	// Adds a class of group-blog to sites with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	// Adds a class of no-sidebar to sites without active sidebar.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'twentysixteen_body_classes' );

/**
 * Converts a HEX value to RGB.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $color The original color, in 3- or 6-digit hexadecimal form.
 * @return array Array containing RGB (red, green, and blue) values for the given
 *               HEX code, empty array otherwise.
 */
function twentysixteen_hex2rgb( $color ) {
	$color = trim( $color, '#' );

	if ( strlen( $color ) === 3 ) {
		$r = hexdec( substr( $color, 0, 1 ).substr( $color, 0, 1 ) );
		$g = hexdec( substr( $color, 1, 1 ).substr( $color, 1, 1 ) );
		$b = hexdec( substr( $color, 2, 1 ).substr( $color, 2, 1 ) );
	} else if ( strlen( $color ) === 6 ) {
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
	} else {
		return array();
	}

	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function twentysixteen_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	840 <= $width && $sizes = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 62vw, 840px';

	if ( 'page' === get_post_type() ) {
		840 > $width && $sizes = '(max-width: ' . $width . 'px) 85vw, ' . $width . 'px';
	} else {
		840 > $width && 600 <= $width && $sizes = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 61vw, (max-width: 1362px) 45vw, 600px';
		600 > $width && $sizes = '(max-width: ' . $width . 'px) 85vw, ' . $width . 'px';
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'twentysixteen_content_image_sizes_attr', 10 , 2 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $attr Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size Registered image size or flat array of height and width dimensions.
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function twentysixteen_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	if ( 'post-thumbnail' === $size ) {
		is_active_sidebar( 'sidebar-1' ) && $attr['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 984px) 60vw, (max-width: 1362px) 62vw, 840px';
		! is_active_sidebar( 'sidebar-1' ) && $attr['sizes'] = '(max-width: 709px) 85vw, (max-width: 909px) 67vw, (max-width: 1362px) 88vw, 1200px';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'twentysixteen_post_thumbnail_sizes_attr', 10 , 3 );

/**
 * Modifies tag cloud widget arguments to have all tags in the widget same font size.
 *
 * @since Twenty Sixteen 1.1
 *
 * @param array $args Arguments for tag cloud widget.
 * @return array A new modified arguments.
 */
function twentysixteen_widget_tag_cloud_args( $args ) {
	$args['largest'] = 1;
	$args['smallest'] = 1;
	$args['unit'] = 'em';
	return $args;
}
add_filter( 'widget_tag_cloud_args', 'twentysixteen_widget_tag_cloud_args' );

/************* Buyer Seller Options *********************/

function init_theme_options() {

		// First, we register a section. This is necessary since all future options must belong to one.
		add_settings_section(
				'general_settings_section',         // ID used to identify this section and with which to register options
				'Sandbox Options',                  // Title to be displayed on the administration page
				'sandbox_general_options_callback', // Callback used to render the description of the section
				'general'                           // Page on which to add this section of options
		);
} // end sandbox_initialize_theme_options


function sandbox_general_options_callback() {
		echo '<p>Select which areas of content you wish to display.</p>';
}


function register_atr_setting() {

	register_setting('my-options-group','my-option-name',74);
}

function buyer_create_menu_page() {
	add_menu_page(
			 'Buyer Options',          // The title to be displayed on the corresponding page for this menu
			 'Buyers',                  // The text to be displayed for this actual menu item
			 'buyer',            // Which type of users can see this menu
			 'buyer',                  // The unique ID - that is, the slug - for this menu item
			 'buyer_menu_page_display',// The name of the function to call when rendering the menu for this page
			 ''
	 );


add_submenu_page(
		'buyer',                  // Register this submenu with the menu defined above
		'Offers',          // The text to the display in the browser when this menu item is active
		'Offers',                  // The text for this menu item
		'buyer',            // Which type of users can see this menu
		'buyer_offers',          // The unique ID - the slug - for this menu item
		'buyer_offers_display'   // The function used to render the menu for this page to the screen
);

}

function buyer_offers_display(){

}

add_action('admin_menu', 'buyer_create_menu_page');

$account_status = 'Not Yet Defined';

function buyer_menu_page_display() {
	$html = '<div class="wrap">';
				$html .= '<h2>Trusted Review Network - Buyers</h2><br /><br /><b>Phone Number:</b> <input type="text" name="phone_number" id="phone_number" size="16" value=""><input type="button" value="Update" onclick="alert(\'Update Phone Number\')">';
				$html .= '<br /><br /><b>Account Status:</b> ' . $GLOBALS['account_status'];
		$html .= '</div>';

		// Send the markup to the browser
		echo $html;
}


function seller_create_menu_page() {
	require_once('includes/class.sellers.php');
	$seller = new Sellers();
	$seller->seller_create_menu_page();

}

function seller_done(){

	echo 'Action Completd.';

}

function seller_manage_campaign(){

	require_once('includes/class.sellers.php');
	$seller = new Sellers();
	echo $seller->seller_manage_campaign();

}


function seller_coupon_display(){
	require_once('includes/class.sellers.php');
	$seller = new Sellers();
	echo $seller->seller_coupon_display();
}

function seller_create_campaign(){
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		echo $seller->seller_create_campaign();
}

add_action('admin_menu', 'seller_create_menu_page');


function seller_menu_page_display() {
	require_once('includes/class.sellers.php');
	$seller = new Sellers();
	echo $seller->seller_menu_page_display();

}

function seller_list_campaign() {
	require_once('includes/class.sellers.php');
	$seller = new Sellers();
	echo $seller->seller_list_campaign();

}


function wp_gear_manager_admin_scripts() {
wp_enqueue_script('media-upload');
wp_enqueue_script('thickbox');
wp_enqueue_script('jquery');
}

function wp_gear_manager_admin_styles() {
wp_enqueue_style('thickbox');
}

add_action('admin_print_scripts', 'wp_gear_manager_admin_scripts');
add_action('admin_print_styles', 'wp_gear_manager_admin_styles');


function seller_add_product()
{
	require_once('includes/class.sellers.php');
	$seller = new Sellers();
	echo $seller->seller_add_product();

}



function seller_edit_product()
{
		require_once('includes/class.sellers.php');
		$seller = new Sellers();
		echo $seller->seller_edit_product();
}





add_action('register_form','show_role_field');

function show_role_field(){ ?>
<input id="role" type="hidden" tabindex="20" size="25" value= "<?php if (isset($_GET['role'])){echo $_GET['role'];} ?>"  name="role"/>
	<?php
}

add_action('user_register', 'register_role');

function register_role($user_id, $password="", $meta=array()) {

// add sanitize for post.

	 $userdata = array();
	 $userdata['ID'] = $user_id;
	 $userdata['role'] = $_POST['role'];
	 $userdata['phone'] = $_POST['phone'];
	 //$userdata['asin'] = $_POST['asin'];
	 $userdata['verification_code'] = generate_verification_code();


	 $userdata['verification_flag'] = false;
	 update_user_meta( $user_id, 'phone', $input['phone'] );
	 //update_user_meta( $user_id, 'asin', $input['asin'] );
	 update_user_meta( $user_id, 'verification_code', $input['verification_code'] );
	 update_user_meta( $user_id, 'verification_flag', $input['verification_flag'] );

	 //only allow if user role is my_role

	 if ($userdata['role'] == "buyer"){
			wp_update_user($userdata);
			update_user_meta( $user_id, 'phone', $userdata['phone'] );
			update_user_meta( $user_id, 'verification_code', $userdata['verification_code'] );
			update_user_meta( $user_id, 'verification_flag', $userdata['verification_flag'] );

	 }
	 elseif ($userdata['role'] == "seller"){
			wp_update_user($userdata);
			//update_user_meta( $user_id, 'asin', $userdata['asin'] );
	 }
	 else {
		return false;
	 }
}

function generate_verification_code()
{
	$characters = '0123456789';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < 6; $i++) {
					$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
	return $randomString;
}

 function add_custom_column( $columns ) {

	// $columns['asin'] = __( 'ASIN', 'theme' );
	 $columns['phone'] = __( 'Phone', 'theme' );
	 $columns['verification_code'] = __( 'Verification Code', 'theme' );
	 $columns['verification_flag'] = __( 'Active', 'theme' );
	 return $columns;

 } // end theme_add_user_zip_code_column
 add_filter( 'manage_users_columns', 'add_custom_column' );



	function theme_show_custom_data( $value, $column_name, $user_id ) {
/*
		if( 'asin' == $column_name ) {
			return get_user_meta( $user_id, 'asin', true );
		} // end
*/
	 if( 'phone' == $column_name ) {
		 return get_user_meta( $user_id, 'phone', true );
	 } // end if

	 if( 'verification_code' == $column_name ) {
		 return get_user_meta( $user_id, 'verification_code', true );
	 } // end if

	 if( 'verification_flag' == $column_name ) {
		 return get_user_meta( $user_id, 'Active', true );
	 } // end if

}


add_action( 'manage_users_custom_column', 'theme_show_custom_data', 10, 3 );


#update buyer phone number
add_action( 'personal_options_update', 'save_buyer_phonenumber' );
add_action( 'edit_user_profile_update', 'save_buyer_phonenumber' );
//add_action( 'personal_options_update', 'save_seller_asin' );
add_action( 'edit_user_profile_update', 'save_seller_asin' );



function save_buyer_phonenumber( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */

	// clean/sanitize $post!
	update_usermeta( $user_id, 'phone', $_POST['phone'] );
}



function save_seller_asin( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */

	// clean/sanitize $post!
	update_usermeta( $user_id, 'asin', $_POST['asin'] );
}


/**
 *  My own functions that i'm using to customize this theme
 */
include('init.php');
include('admin_buyers.php');

/* ---------------------------------------------------------------- */
/*
 * Scripts for dushboard
*/
function admin_twentysixteen_scripts(){
	wp_enqueue_style( 'admin-styles', get_template_directory_uri(). '/css/admin-styles.css', array());
	
	wp_enqueue_script( 'admin-scripts', get_template_directory_uri() . '/js/admin-scripts.js', array( 'jquery' ), null, true );

	wp_localize_script( 'admin-scripts', 'kniTrnAS', array(
		'nonce' => wp_create_nonce( 'kni-trn-as' ),
		'url' => admin_url('admin-ajax.php')
	) );
}

add_action( 'admin_enqueue_scripts', 'admin_twentysixteen_scripts' );

/*
 * Approve seller
*/
add_action('wp_ajax_kni_trn_approve_seller', 'ajax_kni_trn_approve_seller');

function ajax_kni_trn_approve_seller (){
	if ( !wp_verify_nonce( $_POST['security'],'kni-trn-as' )){ 
		wp_die( 'Security Error');		
	}
	
	global $wpdb;
	$sellerID = $_POST['sellerID'];
	$table_name = $wpdb->prefix.'atn_sellers';
	$add = $wpdb->update( $table_name,
		array( 'Approval' =>1),
		array( 'ID' => (int)$sellerID  )
	);
	
	if($add){
		echo 'Seller was approved';
	} else {
		echo 'Something went wrong; $sellerID='.$sellerID;
	}
	
	die();
}

/*
 * Pause seller account
*/
add_action('wp_ajax_kni_trn_pause_seller', 'ajax_kni_trn_pause_seller');

function ajax_kni_trn_pause_seller (){
	if ( !wp_verify_nonce( $_POST['security'],'kni-trn-as' )){ 
		wp_die( 'Security Error');		
	}
	
	global $wpdb;
	$sellerID = $_POST['sellerID'];
	$table_name = $wpdb->prefix.'atn_sellers';
	$add = $wpdb->update( $table_name,
		array( 'Pause' =>1),
		array( 'ID' => (int)$sellerID  )
	);
	
	if($add){
		echo 'Seller account was paused';
	} else {
		echo 'Something went wrong; $sellerID='.$sellerID;
	}
	
	die();
}

/*
 * Pause seller promotion
*/
add_action('wp_ajax_kni_trn_pause_seller_promotion', 'ajax_kni_trn_pause_seller_promotion');

function ajax_kni_trn_pause_seller_promotion (){
	if ( !wp_verify_nonce( $_POST['security'],'kni-trn-as' )){ 
		wp_die( 'Security Error');		
	}
	
	global $wpdb;
	$productID = $_POST['productID'];
	$table_name = 'trn_products';
	$add = $wpdb->update( $table_name,
		array( 'active' => 0),
		array( 'ID' => (int)$productID  )
	);
	
	if($add){
		echo 'Promotion was paused';
	} else {
		echo 'Something went wrong; $sellerID='.$productID;
	}
	
	die();
}