<?php
/**
 * This file creates and handles the Admin Buyers Section
 */

# first thing we do is add the menu item into the admin section
add_action('admin_menu', 'trn_create_buyers_menu');
add_action('admin_menu', 'trn_create_sellers_menu');
add_action('admin_menu', 'trn_create_settings_menu');
add_action('admin_menu', 'trn_menu');

function trn_create_buyers_menu() {
	add_menu_page('Manage Buyers', 'Manage Buyers', 'administrator', 'trn_manage_buyers', 'trn_manage_buyers_page');
}

function trn_create_sellers_menu() {
	add_menu_page('Manage Sellers', 'Manage Sellers', 'administrator', 'trn_manage_sellers', 'trn_manage_sellers_page');
}

function trn_create_settings_menu() {
	add_menu_page("TRN Settings", "TRN Settings", 'administrator', 'trn_manage_settings', 'trn_manage_settings_page');
}

function trn_menu() {
	add_menu_page("TRN-New", "TRN Manage", 'administrator', 'trn_manage', 'trn_manage_page');
}

/**
 * This function handles the buyer functions
 * pass it to a class for easier use
 */
function trn_manage_buyers_page() {
	$buyers = GetClass('ADMINBUYERS');
	$buyers->HandlePage();
}

function trn_manage_sellers_page() {
	$sellers = GetClass('ADMINSELLERS');
	$sellers->HandlePage();
}

function trn_manage_settings_page() {
	# angular handles the rest
	WPParseAndGet("admin_settings");
}

function trn_manage_page() {
	# angular handles the rest
	WPParseAndGet("admin");
}