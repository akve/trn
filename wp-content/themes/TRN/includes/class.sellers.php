<?php
class Sellers {


    var $asin;
    var $temp;



    function __construct()
    {
      //  $this->get_asin_data($asin);
    }

    function seller_create_menu_page() {
      add_menu_page(
           'Products',          // The title to be displayed on the corresponding page for this menu
           'Products',                  // The text to be displayed for this actual menu item
           'seller',            // Which type of users can see this menu
           'seller',                  // The unique ID - that is, the slug - for this menu item
           'seller_menu_page_display',// The name of the function to call when rendering the menu for this page
           ''
       );
//add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' )
      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          'List Campaigns',          // The text to the display in the browser when this menu item is active
          'List Campaigns',    // The text for this menu item
          'seller',            // Which type of users can see this menu
          'seller_list',          // The unique ID - the slug - for this menu item
          'seller_list_campaign'   // The function used to render the menu for this page to the screen

      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          'Coupons',          // The text to the display in the browser when this menu item is active
          'Coupons',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'seller_coupons',          // The unique ID - the slug - for this menu item
          'seller_coupon_display'   // The function used to render the menu for this page to the screen
      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',    // The text for this menu item
          'seller',            // Which type of users can see this menu
          'remove_confirm',          // The unique ID - the slug - for this menu item
          'remove_product_confirm'   // The function used to render the menu for this page to the screen remove_product_confirm

      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',    // The text for this menu item
          'seller',            // Which type of users can see this menu
          'remove',          // The unique ID - the slug - for this menu item
          'remove_product'   // The function used to render the menu for this page to the screen remove_product_confirm

      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'seller_create',          // The unique ID - the slug - for this menu item
          'seller_create_campaign'   // The function used to render the menu for this page to the screen
      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'seller_manage',          // The unique ID - the slug - for this menu item
          'seller_manage_campaign'   // The function used to render the menu for this page to the screen
      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'upload',          // The unique ID - the slug - for this menu item
          'seller_upload'   // The function used to render the menu for this page to the screen
      );

      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'change_image',          // The unique ID - the slug - for this menu item
          'seller_product_image_upload'   // The function used to render the menu for this page to the screen
      );
/*
      add_submenu_page(
          'seller',                  // Register this submenu with the menu defined above
          'List Campaigns',          // The text to the display in the browser when this menu item is active
          'List Campaigns',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'seller_list',          // The unique ID - the slug - for this menu item
          'seller_list_campaign'   // The function used to render the menu for this page to the screen
      );
*/


      add_submenu_page(
          'sellersub',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'edit',          // The unique ID - the slug - for this menu item
          'seller_edit_product'   // The function used to render the menu for this page to the screen
      );

      add_submenu_page(
          'sellersub',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'add',          // The unique ID - the slug - for this menu item
          'seller_add_product'   // The function used to render the menu for this page to the screen
      );

      add_submenu_page(
          'sellersub',                  // Register this submenu with the menu defined above
          '',          // The text to the display in the browser when this menu item is active
          '',                  // The text for this menu item
          'seller',            // Which type of users can see this menu
          'done',          // The unique ID - the slug - for this menu item
          'seller_done'   // The function used to render the menu for this page to the screen
      );

    }

function seller_coupon_display()
{
  global $wpdb;
  $user_ID = get_current_user_id();
  $seller_id = new stdClass();
  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
  $seller_id = $seller_id[0]->id;
$start = 0;
$next_link = '';
$prev_link = '';
  if(isset($_GET['r'])) {
      $r = filter_var($_GET['r'],FILTER_SANITIZE_NUMBER_INT);
      if($r  != '') { $start = $r; }
  }


  $coupon_count = $wpdb->get_results( $wpdb->prepare( "SELECT  `co`.`id` FROM  `wp_atn_coupons` as `co` INNER JOIN `wp_atn_campaign` as `c` ON `co`.`campaign_id` = `c`.`id` INNER JOIN `wp_atn_product` as `p` ON `c`.`asin` = `p`.`asin`  WHERE `c`.`seller_id` =  %s AND `co`.`status` != 'removed'  ORDER BY `co`.`updated` DESC ", $seller_id));

  $count = count($coupon_count);


  $next = $start + 10;
  if($next >= $count) { $next_link = ''; } else { $next_link = '<a href="admin.php?page=seller_coupons&r='.$next.'">next</a>';}
  if($start < 9) { $prev = 0;  } else { $prev = ($start - 10);}
  if($prev > 0 || $next == 20  ) { $prev_link = '<a href="admin.php?page=seller_coupons&r='.$prev.'">prev</a>';} else { $prev_link = '';}
  if($next_link == '' && $prev_link == '') { $pagiation = '';} else { $pageiation = $prev_link.' | '.$next_link;}
  // list campaigns
  $select_campaign = $this->CampaignSelector();

  $coupon_arr = $wpdb->get_results( $wpdb->prepare( "SELECT `co`.`id`,`co`.`seller_id`,`co`.`campaign_id`,`co`.`coupon`,`co`.`status`,`p`.`product_name`, `p`.`asin`  FROM  `wp_atn_coupons` as `co` INNER JOIN `wp_atn_campaign` as `c` ON `co`.`campaign_id` = `c`.`id` INNER JOIN `wp_atn_product` as `p` ON `c`.`asin` = `p`.`asin`  WHERE `c`.`seller_id` =  %s AND `co`.`status` != 'removed'  ORDER BY `co`.`updated` DESC LIMIT ".$start.", 10 ", $seller_id));

  $html = '<div class="wrap">';
  $html .= '<h2>Trusted Review Network - Manage Coupon Codes</h2><form id="update_coupons" name="update_coupons" method="post">';
  $html .= '<script type="text/javascript">function disable_coupon(id) { var r = new XMLHttpRequest(); r.open("POST", "admin.php", true);r.setRequestHeader("Content-type", "application/x-www-form-urlencoded");r.send("action=remove_coupon&id="+id+"");document.location.href="admin.php?page=seller_coupons"}</script>';
  $html .= '<br /><form name="insert_coupon" id="insert_coupon" method="post"><div><b>Coupon Insert</b><br />'.$select_campaign.' <input type="text" name="coupon_code" id="coupon_code" value="" placeholder="New Coupon Code"><input type="hidden" name="action" value="add_coupon"><input type="submit" value="Create Coupon"></div></form><hr width="90%"> <form enctype="multipart/form-data" action="admin.php?page=upload" method="post"><input type="file" name="filename"><input type="hidden" name="action" value="upload"><input type="submit" name="submit" id="submit" value="Upload Coupons From File"><br /><br /></form>';
  $html .= '<br /><b>Current Coupons</b><br /><center>'.$pageiation.'</center><br /><table bgcolor="white" width="90%"><tr><td valign="top" style="border-bottom: 1px solid #ddd; font-wieght:bold;" ><b>Product Name</b></td><td style="border-bottom: 1px solid #ddd; font-wieght:bold;"><b>ASIN</b></td><td style="border-bottom: 1px solid #ddd; font-wieght:bold;"><b>Coupon Code</b></td><td style="border-bottom: 1px solid #ddd; font-wieght:bold;"><b>Status</b></td><td style="border-bottom: 1px solid #ddd; width:25px"></td><tr>';
  foreach($coupon_arr as $coupon_row)
  {
    $c = new stdClass();
    $c = $coupon_row;
    $html .= '<tr><td style="border-bottom: 1px solid #ddd;">'.$c->product_name.'</td><td style="border-bottom: 1px solid #ddd;">'.$c->asin.'</td><td style="border-bottom: 1px solid #ddd;">'.$c->coupon.'</td><td style="border-bottom: 1px solid #ddd;">'.$c->status.'</td><td style="border-bottom: 1px solid #ddd; width:25px"><input style="width:20px;fon-size:7pt;" type="button" value="x" onclick="disable_coupon('.$c->id.')" ></td></tr>';
  }

$html .= '</table><br /><center>'.$pageiation.'</center>';

  return $html;
}


function seller_coupon_insert($_POSTLOCAL)
{
  global $wpdb;
  $user_ID = get_current_user_id();
  $seller_id = new stdClass();
  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
  $seller_id = $seller_id[0]->id;

  $campaign_id = $_POSTLOCAL['campaign_id'];
  $coupon = $_POSTLOCAL['coupon_code'];
  $status = 'enabled';
  $t = time();

  $q = "INSERT INTO `perfedk3_trn`.`wp_atn_coupons` (`id`, `seller_id`, `campaign_id`, `coupon`, `status`, `created`, `updated`) VALUES (NULL, ".$seller_id.", '".$campaign_id."', '".$coupon."','enabled', ".$t.", ".$t." )";
  $insert_coupon = $wpdb->query($q);
// update campaign updated field


  header("Location: admin.php?page=seller_coupons");
  exit();
}

function seller_remove_coupon($_POSTLOCAL)
{
// from ajax
  global $wpdb;
  $user_ID = get_current_user_id();
  $seller_id = new stdClass();
  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
  $seller_id = $seller_id[0]->id;

  $coupon_id = $_POSTLOCAL['id'];

  $status = 'removed';
  $t = time();

  $q = "UPDATE `perfedk3_trn`.`wp_atn_coupons` SET `status` = 'removed', `updated` = ".$t." WHERE `wp_atn_coupons`.`id` = ".$coupon_id." AND  `wp_atn_coupons`.`seller_id` = ".$seller_id."";
  $update_coupon = $wpdb->query($q);


}


function seller_upload($_POSTLOCAL,$_FILESLOCAL)
{
  global $wpdb;
  $user_ID = get_current_user_id();
  $seller_id = new stdClass();
  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
  $seller_id = $seller_id[0]->id;

  if (isset($_POSTLOCAL['submit'])) {
          if (is_uploaded_file($_FILESLOCAL['filename']['tmp_name'])) {
  //            echo "<h1>" . "File ". $_FILES['filename']['name'] ." uploaded successfully." . "</h1>";
  //            echo "<h2>Displaying contents:</h2>";
              readfile($_FILESLOCAL['filename']['tmp_name']);
         }
          //Import uploaded file to Database
         $handle = fopen($_FILESLOCAL['filename']['tmp_name'], "r");
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

// check if coupon code exists.
            $t = time();
              $import="INSERT into `perfedk3_trn`.`wp_atn_coupons`(`id`, `seller_id`, `campaign_id`, `coupon`, `status`, `created`, `updated`) values(NULL, ".$seller_id.",".$data[0].",'".$data[1]."','enabled',".$t.",".$t.")";
              $insert_coupons = $wpdb->query($import);
        }

       fclose($handle);

       $html ='Upload Complete';
       return $html;
    }

  header("Location: admin.php?page=seller_coupons");
  exit();
}


function seller_product_image_upload()
{

  $html = 'seller product image upload';
  return $html;
  die();

  header("Location: http://perfectpushupvideo.com/trntemp/wp-admin/admin.php?page=edit&asin=B018FK66TU");
exit();
  /*
  global $wpdb;
  $asin = $_POST['asin'];
  $user_ID = get_current_user_id();
  $seller_id = new stdClass();
  $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
  $seller_id = $seller_id[0]->id;
  if (isset($_POST['submit'])) {
//          if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
  //            echo "<h1>" . "File ". $_FILES['filename']['name'] ." uploaded successfully." . "</h1>";
  //            echo "<h2>Displaying contents:</h2>";
          //    readfile($_FILES['filename']['tmp_name']);
  //       }
              $upload = wp_upload_bits( $_FILES['filename']['name'], null, file_get_contents( $_FILES['filename']['tmp_name'] ) );
              $image_path = $upload['url']);

// check if coupon code exists.
            $t = time();
              $update = "UPDATE  `perfedk3_trn`.`wp_atn_product` SET `product_image` = '".$image_path."', `updated` = ".$t."";
              $update_product = $wpdb->query($update);

       $html ='Upload Complete';
       return $html;
    }

  header("Location: admin.php?page=edit&asin=".$asin."");
  exit();
  */
}

    function seller_add_this_product()
    {

      require_once('class.asin.php');
      //$role_object = get_role( 'Sellers' );
      global $wpdb;
      $user_ID = get_current_user_id();
      // add $cap capability to this role object
      //$role_object->add_cap( 'upload_files' );
     // $displayAsin = 'B0036A98ZO'; #filter_var(trim($_GET['asin']), FILTER_SANITIZE_EMAIL);
      $product_name = '';
      $product_image = '';
      $description = '';
      $html = '<div class="wrap">';
      $html .= '<script language="JavaScript">
    jQuery(document).ready(function() {
    jQuery(\'#upload_image_button\').click(function() {
    formfield = jQuery(\'#upload_image\').attr(\'name\');
    tb_show(\'\', \'media-upload.php?type=image&TB_iframe=true\');
    return false;
    });

    window.send_to_editor = function(html) {
    imgurl = jQuery(\'img\',html).attr(\'src\');
    jQuery(\'#upload_image\').val(imgurl);
    tb_remove();
    }
    });
    </script><h2>Trusted Review Network - Add Product</h2>';
      $html .= '<table bgcolor="white" width="80%"><tr><td valign="top" width="145" cellpadding="15" cellspacing="15"><form id="add_product"  method="post">';
      $hmtl .= '</td>';
      $html .= '<td valign="top"><span style="font-size:12pt;"> Product ASIN</span><br /><input type="text"  style="width:400px" id="asin" name="asin" value="">';

      $html .= '<br /><br /><div align="right"><input type="hidden" name="action" value="add_product"><input style="width:200px" type="submit" value="Save"></form></div>';
      $html .= '</td></tr></table>';
      return $html;
    }



    function seller_add_product()
    {

      require_once('class.asin.php');
      //$role_object = get_role( 'Sellers' );
      global $wpdb;
      $user_ID = get_current_user_id();
      // add $cap capability to this role object
      //$role_object->add_cap( 'upload_files' );
      $displayAsin = 'B0036A98ZO'; #filter_var(trim($_GET['asin']), FILTER_SANITIZE_EMAIL);
      $product_name = '';
      $product_image = '';
      $description = '';
      $html = '<div class="wrap">';
      $html .= '<script language="JavaScript">
    jQuery(document).ready(function() {
    jQuery(\'#upload_image_button\').click(function() {
    formfield = jQuery(\'#upload_image\').attr(\'name\');
    tb_show(\'\', \'media-upload.php?type=image&TB_iframe=true\');
    return false;
    });

    window.send_to_editor = function(html) {
    imgurl = jQuery(\'img\',html).attr(\'src\');
    jQuery(\'#upload_image\').val(imgurl);
    tb_remove();
    }
    });
    </script><h2>Trusted Review Network - Add Product</h2>';
      $html .= '<table bgcolor="white" style="padding-top:10px;" width="80%"><tr><td valign="top" width="145" cellpadding="15" cellspacing="15"><form id="add_product"  method="post">';
      $hmtl .= '</td>';
      $html .= '<td valign="top"><span style="font-size:12pt;"> Product ASIN</span><br /><input type="text"  style="width:400px" id="asin" name="asin" value=""><input style="width:200px" type="submit" value="Add Product">';
      $html .= '<br /><br /><div align="right"><input type="hidden" name="action" value="add_product"></form></div>';
      $html .= '</td></tr></table>';
      return $html;
    }


    function seller_add_product_post($_POSTLOCAL)
    {

        global $wpdb;
        $user_ID = get_current_user_id();
      // get seller_id

        $seller_id = new stdClass();
        $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
        $seller_id = $seller_id[0]->id;
       $asin = $_POSTLOCAL['asin'];
        require_once('class.asin.php');
          $asin_obj = new Asin();
            require_once('class.asin.php');
        $product = new stdClass();
           $product = $asin_obj->getProduct($asin);

        if(is_numeric($product)) {

          // var_dump($product);admin.php?page=edit&asin=020530902X
        header("Location: admin.php?page=edit&asin=".$asin."");
        exit();
        }
        else {

        header("Location: admin.php?page=seller");
        }
        exit();
    }


    function seller_edit_product_post($_POSTLOCAL)
    {
        global $wpdb;
        $user_ID = get_current_user_id();
      // get seller_id
        $seller_id = new stdClass();
        $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));

        $seller_id = $seller_id[0]->id;


        $asin = filter_var(trim($_POSTLOCAL['asin']), FILTER_SANITIZE_EMAIL);
        $product_name = filter_var(trim($_POSTLOCAL['product_name']),FILTER_SANITIZE_STRING);
        $product_image = filter_var(trim($_POSTLOCAL['product_image']),FILTER_SANITIZE_URL);
        $description = filter_var(trim($_POSTLOCAL['description']),FILTER_SANITIZE_STRING);
        $price = filter_var(trim($_POSTLOCAL['price']),FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);     //
        $t = time();



      $q = "UPDATE `perfedk3_trn`.`wp_atn_product` SET
      `product_name` =  '".$product_name."',
      `product_image` = '".$product_image."',
      `description` = '".$description."',
      `price` = '".$price."',
      `updated`= '".$t."'
       WHERE asin =  '".$asin."' AND seller_id = ".$seller_id."";

//echo $q;exit();



      $update_product = $wpdb->query($q);


    }




    function seller_edit_product()
    {


      require_once('class.asin.php');

      global $wpdb;
      $user_ID = get_current_user_id();

      $displayAsin = filter_var(trim($_GET['asin']), FILTER_SANITIZE_STRING);
        $asin = new Asin();
        $thisProduct = new stdClass();
        // admin.php?page=edit&asin='.$asin.'


        $productProfile = $asin->getProductProfile($displayAsin);
        $thisProduct = $productProfile['product'];
        $campaigns = $productProfile['campaign'];


        $list_campaigns = '<table width="90%">';


        if(count($campaigns) == 0) { $list_campaigns = 'No Campaigns Assigned'; }
        else {
          foreach($campaigns as $campaign){
            $thisCampaign = new stdClass();
            $thisCampaign = $campaign;

          $list_campaigns .= '<tr><td><a href="admin.php?page=seller_manage&id='.$thisCampaign->id.'">ID: '.$thisCampaign->id.'</a></td><td><a href="admin.php?page=seller_manage&id='.$thisCampaign->id.'">'.date('d M, Y',$thisCampaign->startdate).'</a> - <a href="admin.php?page=seller_manage&id='.$thisCampaign->id.'">'.date('d M, Y',$thisCampaign->enddate).'</a></td><td><a href="admin.php?page=seller_manage&id='.$thisCampaign->id.'">$'.$thisCampaign->discount.'</a></td></tr>';
          }
          $list_campaigns .= '</table>';
        }

        $coupon_count = 0;
        foreach($productProfile['coupons'] as $c)
        {
          $coupons = new stdClass();
          $coupons = $c;
          foreach($coupons as $coupon)
          {
        if($coupon->id != '') { $coupon_count++;}
          }
        }

        $html = '<div class="wrap">';
        $html .= '<h2>Trusted Review Network - Edit Product</h2>';
        $html .= '<table bgcolor="white" width="80%"><tr><td valign="top" width="145" cellpadding="15" cellspacing="15"><img src="'.$thisProduct[0]->product_image.'" style="width:100%; max-width:125px;"><input type="hidden" name="product_image" id="product_image" value="'.$thisProduct[0]->product_image.'">
        <form enctype="multipart/form-data" action="admin.php?page=change_image" method="post"><input type="file" id="filename" name="filename" style="display:none;"><label for="filename"> Browse </label><input type="hidden" name="asin" value="'.$displayAsin.'"><input type="hidden" name="action" value="change_image"> | <input type="submit" name="submit" id="submit" value="Update" style="display: none;"><label for="submit"> Update </label></form></td>';
        $html .= '<td valign="top"><script>function confirm_remove() { var txt; var r = confirm("Confirmation: Remove Product '.str_replace("&#39;","'",filter_var($thisProduct[0]->product_name, FILTER_SANITIZE_STRIPPED )).' and all Campaigns and Coupons"); if(r == true) { document.forms[\'remove_product\'].submit(); } }</script><form method="post" id="remove_product" name="remove_product">
        <div align="right"><input type="hidden" name="asin" value="'.$displayAsin.'"><input type="hidden" name="action" value="remove_product"><input type="button" value="x" onclick="confirm_remove()"> </div></form><form method="post" id="edit_product" name="edit_product"><span style="font-size:12pt;">Product ASIN</span><br /><input  style="width:400px" type="text"  id="asin" name="asin" value="'.$displayAsin.'">';
        $html .= '<br /><span style="font-size:12pt;">Product Name</span><br /><input style="width:400px" type="text"  id="product_name" name="product_name" value="'.filter_var($thisProduct[0]->product_name, FILTER_SANITIZE_STRIPPED ).'">';
        $html .= '<br /><span style="font-size:12pt;">Description</span><br /><textarea  id="description" name="description" rows="5" style="width:400px">'.$thisProduct[0]->description.'</textarea>';
        $html .= '<br /><div width="100%"><div style="width: 50%; float:left;"><span style="font-size:12pt;">Price</span><br /><input type="text" style="width: 100px;"  id="price" name="price" value="'.$thisProduct[0]->price.'"></div></div>';
        $html .= '<br clear=all /><br  /><div width="100%"><span style="font-size:12pt;">Campaigns</span><br /></div>';

        $html .= $list_campaigns;


        $html .= '<br /><br /><div width="100%"><span style="font-size:12pt;">Coupon Count:</span> '.$coupon_count.'

        </div>';

        $html .= '<br /><br /><div align="right"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="product_image" id="product_image" value="'.$thisProduct[0]->product_image.'"><input type="submit" value="Update "> | <input type="button" onclick="document.location.href=\'admin.php?page=seller_create&asin='.$displayAsin.'\'" value="Create Campaign"></div></form>';
        $html .= '</td></tr></table>';
        return $html;
    }


    function remove_product()
    {

      require_once('class.asin.php');
      global $wpdb;
      $user_ID = get_current_user_id();
      $displayAsin = filter_var(trim($_GET['asin']), FILTER_SANITIZE_STRING);

      $asin = new Asin();
      $thisProduct = new stdClass();


      $productProfile = $asin->getProductProfile($displayAsin);


      $thisProduct = $productProfile['product'];
      $q = "DELETE FROM `perfedk3_trn`.`wp_atn_product` WHERE `wp_atn_product`.`id` = ".$thisProduct[0]->id."";
 //     echo '<br >'.$q;
      $remove_product = $wpdb->query($q);

//      echo 'target product id:' . $thisProduct[0]->id;

      $campaigns = $productProfile['campaign'];


      $campaigns_arr = array();
      if(count($campaigns) == 0) { $list_campaigns = 'No Campaigns Assigned'; }
      else {
        $x =0;
        foreach($campaigns as $campaign){
          $thisCampaign = new stdClass();
          $thisCampaign = $campaign;

          $campaigns_arr[] = $thisCampaign->id;

          $q = "DELETE FROM `perfedk3_trn`.`wp_atn_campaign` WHERE `wp_atn_campaign`.`id` = ".$thisCampaign->id."";


          $remove_campaign = $wpdb->query($q);
          $x++;
        }
      }

      $coupon_arr = array();
      $coupon_count = 0;
      foreach($productProfile['coupons'] as $c)
      {
        $coupons = new stdClass();
        $coupons = $c;
        foreach($coupons as $coupon)
        {
      if($coupon->id != '') {

        $q = "DELETE FROM `perfedk3_trn`.`wp_atn_coupons` WHERE `wp_atn_coupons`.`id` = ".$coupon->id."";

        $remove_coupons = $wpdb->query($q);
         }
        }
      }




      $html = '<div class="wrap">';
      $html .= '<h2>This Product and its Campaigns and Coupons Have Been Removed</h2>';

      $html .= '</div>';


      return $html;

    }



    function remove_product_confirm()
    {
      require_once('class.asin.php');
      global $wpdb;
      $user_ID = get_current_user_id();
      $displayAsin = filter_var(trim($_GET['asin']), FILTER_SANITIZE_STRING);
        $asin = new Asin();
      $thisProduct = $asin->getProductProfile();
      $remove = $asin->removeProduct($displayAsin);
      $html = '<div class="wrap">';
      $html .= '<h2>Trusted Review Network - '.$thisProduct['product_name'].' ('.$thisProduct['asin'].') Removed</h2>';
      return $html;
    }


    function seller_manage_campaign()
    {
      require_once('class.asin.php');
      global $wpdb;
      $user_ID = get_current_user_id();
      $campaign_id = $_GET['id'];
      $discountValue = '';
        $starttime = '';
        $endtime = '';
        $email = '';

        $notes = '';
        $thisCampaign = new stdClass();
        $asin = new Asin();
        $thisCampaign = $asin->getCampaign($campaign_id);
        $startDate = $this->DateSelector( "Start", $thisCampaign[0]->startdate,0);
        $endDate = $this->DateSelector( "End", $thisCampaign[0]->enddate,0);

//change coupons process. Use coupons table, not couponcode field.


        $html = '<div class="wrap">';
        $html .= '<h2>Trusted Review Network - Manage Campaign</h2><form id="update_campaign" name="update_campaign" method="post">';
        $html .= '<table bgcolor="white" width="80%"><tr><td valign="top" width="145" cellpadding="15" cellspacing="15"><img src="'.$thisCampaign[0]->product_image.'" style="width:100%; max-width:125px;"></td>';
        $html .= '<td valign="top"><span style="font-size:12pt;">Campaign ID: '.$thisCampaign[0]->id.'<Br /><Br />Product ASIN</span><br /><input  style="width:400px" type="text" readonly id="asin" name="asin" value="'.$thisCampaign[0]->asin.'">';

        $html .= '<br /><span style="font-size:12pt;">Title</span><br /><input style="width:400px" type="text" readonly id="title" name="title" value="'.$thisCampaign[0]->product_name.'">';
        $html .= '<br /><div width="100%"><div style="width: 20%; float:left;"><span style="font-size:12pt;">Price</span><br />$<input type="text" style="width: 150px;" readonly id="price" name="price" value="'.$thisCampaign[0]->price.'"></div><div ><span style="font-size:12pt;">Discount Price</span><br />$<input type="text"  style="width: 150px;" id="discount" name="discount" value="'.$thisCampaign[0]->discount.'"></div></div>';
        $html .= '<br /><span style="font-size:12pt;">Campaign Start Date</span><br />'.$startDate.'';
        $html .= '<br /><span style="font-size:12pt;">Campaign End Date (11:59 EST)</span><br />'.$endDate.'';
        $html .= '<br /><br /><span style="font-size:12pt;">Coupon Support Email</span><br><input type="text" name="email" id="email" value="'.$thisCampaign[0]->email.'" style="width: 400px;">';

        $html .= '<br /><br /><span style="font-size:12pt;">Notes To Reviewer</span><br /><textarea name="notes" id="notes" rows="5" style="width: 400px;">'.$thisCampaign[0]->notes.'</textarea>';
        $html .= '<br /><br /><div align="right"><input type="hidden" name="id" value="'.$thisCampaign[0]->id.'"><input type="hidden" name="action" value="update_campaign"><input type="submit" value="Update Campaign"> | <input type="button" onclick="document.location.href=\'admin.php?page=seller_list\'" value="Cancel"></div>';
        $html .= '</td></tr></table>';
        return $html;
    }

        function seller_update_campaign_post($_POSTLOCAL)
        {
            global $wpdb;
            $user_ID = get_current_user_id();
          // get seller_id
            $seller_id = new stdClass();
            $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
            $seller_id = $seller_id[0]->id;

            $id = filter_var(trim($_POSTLOCAL['id']),FILTER_SANITIZE_NUMBER_INT);

            //$startdate = filter_var(trim($_POST['startdate']),FILTER_SANITIZE_NUMBER_INT);
            // odd - the date is decrementing one day on update. Lets increment one day here to compensate
            $startdate = strtotime("".$_POSTLOCAL['StartDay'] . "-" . $_POST['StartMonth'] . "-" . $_POST['StartYear']." + 1 day");
            $enddate = strtotime("".$_POSTLOCAL['EndDay'] . "-" . $_POST['EndMonth'] . "-" . $_POST['EndYear']." + 1 day");
/*
            $startdate = filter_var(trim($_POST['startdate']),FILTER_SANITIZE_NUMBER_INT);
            $enddate = filter_var(trim($_POST['enddate']),FILTER_SANITIZE_NUMBER_INT);
*/
            $email = filter_var(trim($_POSTLOCAL['email']), FILTER_SANITIZE_EMAIL);

            $discount = filter_var(trim($_POSTLOCAL['discount']),FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
            $notes = filter_var(trim($_POSTLOCAL['notes']),FILTER_SANITIZE_STRING);
            $t = time();



          $q = "UPDATE `perfedk3_trn`.`wp_atn_campaign` SET
          `startdate` =  '".$startdate."',
          `enddate` = '".$enddate."',
          `email` = '".$email."',
          `discount` = '".$discount."',
          `notes` = '".$notes."',
          `updated`= '".$t."'
           WHERE id =  '".$id."' AND seller_id = ".$seller_id."";

    //echo $q;exit();



          $update_product = $wpdb->query($q);


        }



    function seller_create_campaign()
    {
      require_once('class.asin.php');
      global $wpdb;
      $user_ID = get_current_user_id();
      $displayAsin = filter_var(trim($_GET['asin']), FILTER_SANITIZE_EMAIL);

        $discountValue = '';
        $startYear = '';
        $startMonth = '';
        $starDay = '';
        $endYear = '';
        $endMonth = '';
        $endDay = '';
        $email = '';

        $notes = '';
        $startDate = $this->DateSelector( "Start");
        $endDate = $this->DateSelector( "End");

        $asin = new Asin();
        $thisProduct = new stdClass();
        $thisProduct = $asin->getProduct($displayAsin);

        $html = '<div class="wrap">';
        $html .= '<h2>Trusted Review Network - Create Campaign</h2><form name="create_campaign" id="create_campaign" method="post">';
        $html .= '<table bgcolor="white" width="80%"><tr><td valign="top" width="145" cellpadding="15" cellspacing="15"><img src="'.$thisProduct[0]->product_image.'" style="width:100%; max-width:125px;"></td>';
        $html .= '<td valign="top"><span style="font-size:12pt;">Product ASIN</span><br /><input  style="width:400px" type="text" readonly id="asin" name="asin" value="'.$displayAsin.'">';

        $html .= '<br /><span style="font-size:12pt;">Title</span><br /><input style="width:400px" type="text" readonly id="title" name="title" value="'.$thisProduct[0]->product_name.'">';
        $html .= '<br /><div width="100%"><div style="width: 20%; float:left;"><span style="font-size:12pt;">Price</span><br />$<input type="text" style="width: 125px;" readonly id="price" name="price" value="'.$thisProduct[0]->price.'"></div><div style="margin-left: 20%;"><span style="width: 125px;font-size:12pt;">Discount Price</span><br />$<input type="text"  style="width: 125px;" id="discount" name="discount" value="'.$discountValue.'"></div></div>';
        $html .= '<br /><span style="font-size:12pt;">Campaign Start Date</span><br />'.$startDate.'';
        $html .= '<br /><span style="font-size:12pt;">Campaign End Date (11:59 EST)</span><br />'.$endDate.'';
        $html .= '<br /><br /><span style="font-size:12pt;">Coupon Support Email</span><br><input type="text" name="email" id="email" value="'.$email.'" style="width: 400px;" wrap="hard">';

        $html .= '<br /><br /><span style="font-size:12pt;">Notes To Reviewer</span><br /><textarea name="notes" id="notes" rows="5" style="width: 400px;">'.$notes.'</textarea>';
        $html .= '<br /><br /><div align="right"><input type="hidden" name="action" value="create_campaign"><input type="submit" value="Create Campaign"> | <input type="button" onclick="document.location.href=\'admin.php?page=seller\'" value="Cancel"></form></div>';
        $html .= '</td></tr></table>';
        return $html;
    }

    function getCouponsCodes($campaignid)
    {
        $codes = '';


        return $codes;
    }


    function seller_create_campaign_post($_POSTLOCAL)
    {
        global $wpdb;
        $user_ID = get_current_user_id();
      // get seller_id


        $seller_id = new stdClass();
        $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
        $seller_id = $seller_id[0]->id;

        // temp url for image until media library connection can be made.


        //$startdate = filter_var(trim($_POST['startdate']),FILTER_SANITIZE_NUMBER_INT);
        $startdate = strtotime("".$_POSTLOCAL['StartDay'] . "-" . $_POSTLOCAL['StartMonth'] . "-" . $_POSTLOCAL['StartYear']."");

        $enddate = strtotime("".$_POSTLOCAL['EndDay'] . "-" . $_POSTLOCAL['EndMonth'] . "-" . $_POSTLOCAL['EndYear']."");

        $asin = filter_var(trim($_POSTLOCAL['asin']), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($_POSTLOCAL['email']),FILTER_SANITIZE_EMAIL);


        $discount = filter_var(trim($_POSTLOCAL['discount']),FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
        $notes = filter_var(trim($_POSTLOCAL['notes']),FILTER_SANITIZE_STRING);
        $t = time();

      $q = "INSERT INTO  `perfedk3_trn`.`wp_atn_campaign`  (
      `id` ,
      `seller_id` ,
      `asin` ,
      `startdate` ,
      `enddate` ,
      `email` ,
      `discount` ,
      `notes`,
      `created`,
      `updated`
      ) VALUES ('', '".$seller_id."', '".$asin."', '".$startdate."', '".$enddate."','".$email."',  '".$discount."', '".$notes."','".$t."', '".$t."')";


      $add_campaign = $wpdb->query($q);

    }


    function  DateSelector($inName, $useDate=0,$type=0)
        {
          $str = '';

            /* create array so we can name months */
            $monthName = ARRAY(1=> "January", "February", "March",
                "April", "May", "June", "July", "August",
                "September", "October", "November", "December");

            /* if date invalid or not supplied, use current time */
            IF($useDate == 0)
            {
                $useDate = TIME();
            }

            /* make month selector */

            $str = "<SELECT NAME=" . $inName . "Month>\n";
            FOR($currentMonth = 1; $currentMonth <= 12; $currentMonth++)
            {
                $str.= "<OPTION VALUE=\"";
                str_pad(INTVAL($currentMonth),2,'0',STR_PAD_LEFT);
                $str.= $currentMonth;
                $str.= "\"";
                IF(INTVAL(DATE( "m", $useDate))==$currentMonth)
                {
                    $str.= " SELECTED";
                    $thisMonth = $monthName[$currentMonth];
                }
                $str.= ">" . $monthName[$currentMonth] . "\n";
            }
            $str.= "</SELECT>";

            /* make day selector */
            $str.= "<SELECT NAME=" . $inName . "Day>\n";
            FOR($currentDay=1; $currentDay <= 31; $currentDay++)
            {
                str_pad($currentDay, 2, '0',STR_PAD_LEFT);
                $str.= "<OPTION VALUE=\"".$currentDay."\"";
                IF(INTVAL(DATE( "d", $useDate))==$currentDay)
                {
                    $str.= " SELECTED";
                    $thisDay = $currentDay;
                }
                $str.= ">$currentDay\n";
            }
            $str.= "</SELECT>";

            /* make year selector */
            $str.= "<SELECT NAME=" . $inName . "Year>\n";
            $startYear = DATE( "Y", $useDate);
            FOR($currentYear = $startYear - 5; $currentYear <= $startYear+5;$currentYear++)
            {
                $str.= "<OPTION VALUE=\"$currentYear\"";
                IF(DATE( "Y", $useDate)==$currentYear)
                {
                    $str.= " SELECTED";
                    $thisYear = $currentYear;
                }
                $str.= ">$currentYear\n";
            }
            $str.= "</SELECT>";
if($type == 1) { $str = $thisMonth . ' ' . $thisDay . ', ' . $thisYear; }
            return $str;
        }


        function CampaignSelector($selected='')
        {


          global $wpdb;
          $user_ID = get_current_user_id();
          $seller_id = new stdClass();
          $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
          $seller_id = $seller_id[0]->id;
          $str = '';

          $campaign_lists = $wpdb->get_results( $wpdb->prepare( "SELECT `c`.`id`, `c`.`startdate`, `c`.`enddate`, `c`.`asin`, `p`.`product_name`  FROM `wp_atn_campaign` as `c`  INNER JOIN `wp_atn_product` as `p` ON `c`.`asin` = `p`.`asin`  WHERE `c`.`seller_id` =  %s ORDER BY `c`.`updated` DESC", $seller_id));

          $str = '<select name="campaign_id" id="campaign_id"><option value="">Select Campaign</option>';
          foreach($campaign_lists as $k => $v)
          {

            $cl = new stdClass();
            $cl = $v;
            $str .= '<option value="'.$cl->id.'" ';
              if($selected == $cl->id)
              {
                  $str .= ' SELECTED ';
              }
              $startdate = date('d-m-Y',$cl->startdate);
              $enddate = date('d-m-Y', $cl->enddate);

              $str .= ' >ID: '.$cl->id.' '.$cl->product_name.' ('.$cl->asin.') '.$startdate.' TO '.$enddate.' </option>';
          }
            $str .= '</select>';

          return $str;
        }




        function seller_menu_page_display() {


            require_once('class.asin.php');
              $listProducts = new stdClass();
              $asin = new Asin();
              $start = 0;
              $next_link = '';
              $prev_link = '';
                if(isset($_GET['r'])) {
                    $r = filter_var($_GET['r'],FILTER_SANITIZE_NUMBER_INT);
                    if($r  != '') { $start = $r; }
                }

              $count = count($asin->countProducts());

              $listProducts = $asin->listProducts($start);
              $next = $start + 5;

              if($next >= $count) { $next_link = ''; } else {  $next_link = '<a href="admin.php?page=seller&r='.$next.'">next</a>'; }
              if($start < 9) { $prev = 0;  } else { $prev = ($start - 5);}
              if($prev > 0 || $next == 10  ) { $prev_link = '<a href="admin.php?page=seller&r='.$prev.'">prev</a>';} else { $prev_link = '';}
              if($next_link == '' && $prev_link == '') { $pagiation = '';} else { $pageiation = $prev_link.' | '.$next_link;}

            $thisProductRow = '<center>'.$pageiation.'</center><br /><table bgcolor="white" width=100%" cellpadding="5" cellspacing="5" border="0"><tr><td width="125" valign="top"></td><td valign="top" width="*"></td></tr>';

              foreach($listProducts as $product){
                  $thisasin = new Asin();
                  $v = $product->asin;
                 $thisProduct = new stdClass();
                 $thisProduct = $thisasin->getProduct($v);
                 $product_image = $thisProduct[0]->product_image;
                 $product_name = $thisProduct[0]->product_name;
                 $description = $thisProduct[0]->description;
                 $price = $thisProduct[0]->price;
                 $asin = $thisProduct[0]->asin;
              $thisProductRow .= '<tr><td valign="top" style="width: 130px;"><img src="'.$product_image.'" style="width:100%; max-width:125px;"></td>
              <td valign="top"><div id="productDesciption"  style=" padding-top:2%; font-size:16pt; color:black"><a href="admin.php?page=edit&asin='.$asin.'"><b>'.$product_name.'</b></a><br /><Br /> $'.$price.'</div><br /><br />'.$description.'<br /><Br />ASIN: '.$asin.'</td>
              </tr><tr><td>  </td><td><div align="right" style=" vertical-align: text-bottom;"></div></td></tr>
              <tr><td colspan="2"><hr width="100%"></td></tr>';
       }

            $thisProductRow .= '</table><br /><center>'.$pageiation.'</center>';
            $user_id = get_current_user_id();
          // change ASIN to API Key on all pages. The API key is used to fetch products from User Account.
            $this_apikey = get_user_meta( $user_id, 'apikey', true );
            $html = '<div class="wrap">';
               $html .= '<h2>Trusted Review Network - Current Products</h2>';
                 $html .= '<div align="right"><input type="button" value="Add A Product" onclick="document.location.href=\'admin.php?page=add\';"></div>';
                  $html .= $thisProductRow;
                  $html .= '</div>';
              // Send the markup to the browser
              return $html;

        }


        function seller_list_campaign() {

          $start = 0;
          $next_link = '';
          $prev_link = '';
            if(isset($_GET['r'])) {
                $r = filter_var($_GET['r'],FILTER_SANITIZE_NUMBER_INT);
                if($r  != '') { $start = $r; }
              }
                  global $wpdb;
                   $user_ID = get_current_user_id();
                   $seller_id = new stdClass();
                   $seller_id  = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `wp_atn_sellers` Where `user_id` = %s", $user_ID));
                   $seller_id = $seller_id[0]->id;

                   $campaign_arr = $wpdb->get_results( $wpdb->prepare( "SELECT  `c`.`id`,`c`.`asin`,`c`.`startdate`,`c`.`enddate`,`c`.`discount`,`p`.`product_name`, `p`.`product_image`, `p`.`description`, `p`.`price`  FROM `wp_atn_campaign` as `c` JOIN `wp_atn_product` as `p` ON `c`.`asin` = `p`.`asin` WHERE `c`.`seller_id` =  %s ", $seller_id));

                   $count = count($campaign_arr);

                   if($count == 0) {
                      $add_campaign = '<a href="admin.php?page=seller">No Active Campaigns</a> - <a href="admin.php?page=seller">Click To Add a Campaign To A Product</a>'; }
                      else {
                        $add_campaign = '';
                    }


           require_once('class.asin.php');
              $asin = new Asin();
              $listCampaigns = $asin->listCampaigns($start);



              $next = $start + 5;
              if($next >= $count) { $next_link = ''; } else { $next_link = '<a href="admin.php?page=seller_list&r='.$next.'">next</a>';}
              if($start < 9) { $prev = 0;  } else { $prev = ($start - 5);}
              if($prev > 0 || $next == 10  ) { $prev_link = '<a href="admin.php?page=seller_list&r='.$prev.'">prev</a>';} else { $prev_link = '';}
              if($next_link == '' && $prev_link == '') { $pagiation = '';} else { $pageiation = $prev_link.' | '.$next_link;}



              $thisCampaignRow = '<center>'.$pageiation.'</center><Br /><table bgcolor="white" width=100%" cellpadding="5" cellspacing="5" border="0"><tr><td width="125" valign="top"><!-- Product Thumb --></td><td valign="top" width="*"><!-- Description --></td></tr>';

              foreach($listCampaigns as $Campaign)
                  {
                    $thisCampaign = new stdClass();
                    $thisCampaign = $Campaign;
                    $product_image = $thisCampaign->product_image;
                    $product_name = $thisCampaign->product_name;
                    $description = $thisCampaign->description;
                    $price = $thisCampaign->price;
                    $startdate = $this->DateSelector('Start', $thisCampaign->startdate,1);
                    $enddate = $this->DateSelector('End', $thisCampaign->enddate,1);

                    $displayCouponPrice = $thisCampaign->discount;
                    $asin = $thisCampaign->asin;
                    $id = $thisCampaign->id;

                   $requests_count = $this->CountCampaignRequests($id);
                   $approved_count = $this->CountCampaignApproved($id);


                    $thisCampaignRow .= '<tr><td valign="top"><img src="'.$product_image.'" style="width:100%; max-width:125px;"></td><td valign="top"><div id="productDesciption" style="padding-left:10%; padding-top:2%"><span style="font-size: 16pt; font-weight: bold;">ID: '.$id.' '.$product_name.'</span><br /><br />'.$description.'<br /><Br />ASIN: '.$asin.'<br /><Br />Start Date: '.$startdate.' <Br />End Date: '.$enddate.'<br /></td><td>   <span style="font-size:12pt"><span style="width:150px;">Listed Price:</span> $'.$price.'<br /><br /><span style="width:150px;">Discount:</span> $'.$displayCouponPrice.'</span>    </td></tr>
                    <tr><td></td><td><div align="right" style=" vertical-align: text-bottom;"><a href="admin.php?page=seller_manage&id='.$id.'">EDIT</a> |  Requests: '.$requests_count.' | Approved: '.$approved_count.'</div></td></tr><tr><td colspan="3"><hr width="100%"></td></tr>';
                  }

                  $thisCampaignRow .= '</table><br /><center>'.$pageiation.'</center><Br />';
              //$x = $asin->showConstant();

            $user_id = get_current_user_id();


          // change ASIN to API Key on all pages. The API key is used to fetch products from User Account.

            $this_apikey = get_user_meta( $user_id, 'apikey', true );

            $html = '<div class="wrap">';
               $html .= '<h2>Trusted Review Network - Current Campaigns</h2>';
               $html .= $add_campaign;
               $html .= $thisCampaignRow;
               $html .= '</div>';

              // Send the markup to the browser
              return $html;

        }


        public function CountCampaignRequests($campaign_id)
        {

           global $wpdb;
          $request_count = 0;
          /*
          $active_buyers = $wpdb->get_results( $wpdb->prepare( "SELECT `current_coupon` FROM `wp_atn_buyer` WHERE `verified_flag` = 1 AND `current_coupon` != ''"));

          $campaign_coupons = $wpdb->get_results( $wpdb->prepare( "SELECT `coupon` FROM `wp_atn_coupons` WHERE `campaign_id` =  %s", $campaign_id));
          var_dump($campaign_coupons);

          $coupons_array = (array) $campaign_coupons;//     VAR_DUMP($campaign_coupons);
          var_dump($coupons_array);

          foreach($active_buyers as $k)
          {
            $arr = new stdClass();
            $arr = $k;
            $this_coupon = $arr->current_coupon;
            if(in_array($this_coupon, $coupons_array)) { $request_count++; }

          }
*/
            return $request_count;
        }



        public function CountCampaignApproved($campaign_id)
        {

           global $wpdb;
          $approved_count = 0;
          $active_buyers = $wpdb->get_results( $wpdb->prepare( "SELECT `current_coupon` FROM `wp_atn_buyer` WHERE `verified_flag` = 1 AND `current_coupon` != ''"));

          $campaign_coupons = $wpdb->get_results( $wpdb->prepare( "SELECT `coupon` FROM `wp_atn_coupons` WHERE `campaign_id` =  %s", $campaign_id));
          //var_dump($campaign_coupons);

          $coupons = array();
          foreach($campaign_coupons as $c)
          {
            $carr = new stdClass();
            $carr = $c;
            $coupons[] = $carr->coupon;
          }

          foreach($active_buyers as $k)
          {
            $arr = new stdClass();
            $arr = $k;
            $this_coupon = $arr->current_coupon;
            if(in_array($this_coupon, $coupons)) { $approved_count++; }

          }

            return $approved_count;
        }


}


  ?>
