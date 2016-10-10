<?php
/**
 *	TRN - Amazon theme
 *
 *	I hate wordpress structure, we're customizing this theme as much as posible
 *	We still have to get all the variables from the database, so that stays relatively the same
 */

# verify buyer page
$buyer = GetClass('BUYERACCOUNT');
$buyer->VerifyBuyerPage();

# verify seller page
$seller = GetClass('SELLERACCOUNT');
$seller->VerifySellerPage();

# still using regular wordpress header and footer functions because they're here
get_header();

get_template_part( 'template-parts/content', 'page' );

get_footer(); 