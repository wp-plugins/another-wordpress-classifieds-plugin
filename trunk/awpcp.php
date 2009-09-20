<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Plugin Name: Another Wordpress Classifieds Plugin
Plugin URI: http://www.awpcp.com
Description: AWPCP - A wordpress classifieds plugin
Version: 1.0.5.4
Author: A Lewis
Author URI: http://www.awpcp.com
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// Another Wordpress Classifieds Plugin provides the ability for you to add classified ads to your wordpress blog. This plugin has been developed by a hobbyist programmer who does not pretend to have the skill of an PHP expert a MYSQL expert or an expert wordpress developer.
// Use this plugin knowing it comes with no guarantee that the methods of coding used are up to PHP, MYSQL or wordpress plugin development expert standards.
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

dcfunctions.php contains its own copyright notice. Please read and adhere to the terms outlined in dcfunctions.php

AWPCP Classifieds icon courtesy of http://www.famfamfam.com/lab/icons/silk/
Easy PHP Upload: http://www.finalwebsites.com/forums/topic/php-ajax-upload-example

*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

session_start();

if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

$wpcontenturl=WP_CONTENT_URL;
$wpcontentdir=WP_CONTENT_DIR;
$wpinc=WPINC;


$awpcp_plugin_path = WP_CONTENT_DIR.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
$awpcp_plugin_url = WP_CONTENT_URL.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

$imagespath = WP_CONTENT_DIR.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'images';
$imagesurl = WP_CONTENT_URL.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'images';


$nameofsite=get_option('blogname');
$siteurl=get_option('siteurl');
$thisadminemail=get_option('admin_email');

require("$awpcp_plugin_path/dcfunctions.php");
require("$awpcp_plugin_path/functions_awpcp.php");

if( file_exists("$awpcp_plugin_path/awpcp_category_icons_module.php") )
{
	require("$awpcp_plugin_path/awpcp_category_icons_module.php");
	$hascaticonsmodule=1;
}

if( file_exists("$awpcp_plugin_path/awpcp_region_control_module.php") )
{
	require("$awpcp_plugin_path/awpcp_region_control_module.php");
	$hasregionsmodule=1;
}

if( file_exists("$awpcp_plugin_path/awpcp_remove_powered_by_module.php") )
{
	require("$awpcp_plugin_path/awpcp_remove_powered_by_module.php");
	$haspoweredbyremovalmodule=1;
}


$awpcp_db_version = "1.0.5.4";

if(field_exists($field='uploadfoldername'))
{
	$uploadfoldername=get_awpcp_option('uploadfoldername');
}
else
{
	$uploadfoldername="uploads";
}

if( defined( 'WPLOCKDOWN' ) && constant( 'WPLOCKDOWN' ) ) {     echo "Sorry. My blog is locked down. Updates will appear shortly"; }

define( 'MAINUPLOADURL', $wpcontenturl .'/' .$uploadfoldername);
define('MAINUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername);
define( 'AWPCPUPLOADURL', $wpcontenturl .'/' .$uploadfoldername .'/awpcp');
define('AWPCPUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername .'/awpcp/');
define( 'AWPCPTHUMBSUPLOADURL', $wpcontenturl .'/' .$uploadfoldername .'/awpcp/thumbs');
define('AWPCPTHUMBSUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername .'/awpcp/thumbs/');
define('AWPCPURL', $awpcp_plugin_url );
define('MENUICO', $imagesurl .'/menuico.png');


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add css file and jquery codes to header
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpjs() {
	global $awpcp_plugin_url;
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-form');

	if( !get_awpcp_option('awpcp_thickbox_disabled') )
	{
		wp_enqueue_script('thickbox');
	}

	wp_enqueue_script('jquery-chuch', $awpcp_plugin_url.'js/checkuncheckboxes.js', array('jquery'));


}

function awpcp_insert_thickbox() {
    global $siteurl,$wpinc;

    echo "\n".'

    <link rel="stylesheet" href="'.$siteurl.'/'.$wpinc.'/js/thickbox/thickbox.css" type="text/css" media="screen" />

    <script type="text/javascript">
    var tb_pathToImage = "'.$siteurl.'/'.$wpinc.'/js/thickbox/loadingAnimation.gif";
    var tb_closeImage = "'.$siteurl.'/'.$wpinc.'/js/thickbox/tb-close.png";
    </script>

    ';
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add actions and filters etc
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	add_action('init', 'awpcp_install');
	add_action ('wp_print_scripts', 'awpcpjs',1);
	add_action('wp_head', 'awpcp_addcss');
	add_action('wp_head', 'awpcp_insert_thickbox', 10);
	add_action('wp_head', 'awpcp_insertjquery');
	add_action( 'doadexpirations_hook', 'doadexpirations' );
	add_action('admin_menu', 'awpcp_launch');
	add_action("plugins_loaded", "init_awpcpsbarwidget");
	add_filter("the_content", "awpcpui_homescreen");



function awpcp_rewrite($wp_rewrite) {
global $wp_rewrite,$wpdb;
$table_name6 = $wpdb->prefix . "awpcp_pagename";



	//$tableexists=checkfortable($table_name6);
	$awpcppage=get_currentpagename();
	$pprefx = sanitize_title($awpcppage, $post_ID='');


					$wp_rewrite->non_wp_rules =
					array($pprefx.'/browsecat/(.+)/(.+)' => 'index.php?pagename='.$pprefx.'&a=browsecat&category_id=$1',
						$pprefx.'/showad/(.+)/(.+)' => 'index.php?pagename='.$pprefx.'&a=showad&id=$1',
						$pprefx.'/placead'  => 'index.php?pagename='.$pprefx.'&a=placead',
						$pprefx.'/browseads'  => 'index.php?pagename='.$pprefx.'&a=browseads',
						$pprefx.'/categoriesview'  => 'index.php?pagename='.$pprefx.'&a=categoriesview',
						$pprefx.'/searchads'  => 'index.php?pagename='.$pprefx.'&a=searchads',
						$pprefx.'/editad'  => 'index.php?pagename='.$pprefx.'&a=editad',
						$pprefx.'/paypal'  => 'index.php?pagename='.$pprefx.'&a=paypal',
						$pprefx.'/paypalthankyou/(.+)' => 'index.php?pagename='.$pprefx.'&a=paypalthankyou&i=$1',
						$pprefx.'/cancelpaypal/(.+)' => 'index.php?pagename='.$pprefx.'&a=cancelpaypal&i=$1',
						$pprefx.'/2checkout'  => 'index.php?pagename='.$pprefx.'&a=2checkout',
						$pprefx.'/setregion/(.+)/(.+)' => 'index.php?pagename='.$pprefx.'&a=setregion&regionid=$1',
						$pprefx.'/contact/(.+)/(.+)' => 'index.php?pagename='.$pprefx.'&a=contact&i=$1'
					);

					$wp_rewrite->rules = $wp_rewrite->non_wp_rules + $wp_rewrite->rules;

}

		// Do rewrite
			if(get_awpcp_option('seofriendlyurls') == 1)
			{
				if( file_exists(ABSPATH . '.htaccess') )
				{

					$filecontent=file_get_contents(ABSPATH . '.htaccess');
					$awpcppage=get_currentpagename();
					$pprefx = sanitize_title($awpcppage, $post_ID='');

						if(!(preg_match("/\b\/\?pagename=$pprefx&a=\b/i","$filecontent")))
						{

							$permastruc=get_option('permalink_structure');

							if( isset($permastruc) && !empty($permastruc) )
							{
								add_action('generate_rewrite_rules', 'awpcp_rewrite');
							}
						}
					}
				}

function flush_rewrite_rules()
{
global $wp_rewrite;
$wp_rewrite->flush_rules();
}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// The funtion to add the reference to the plugin css style sheet to the header of the index page
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function awpcp_addcss() {
			$awpcpstylesheet="awpcpstyle.css";
			 echo "\n".'<style type="text/css" media="screen">@import "'.AWPCPURL.'css/'.$awpcpstylesheet.'";</style>';
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// The funtion to add the javascript codes to the header of the index page
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		function awpcp_insertjquery() {
		global $awpcp_plugin_path;
		$awpcp_plugin_path=str_replace("\\","\\\\",$awpcp_plugin_path);
		echo "\n
			<script type=\"text/javascript\">
			var AWPCPJQUERY = jQuery.noConflict();
			AWPCPJQUERY(document).ready(function() {
				AWPCPJQUERY('#loading')
				.ajaxStart(function(){
					AWPCPJQUERY(this).show();
				})
				.ajaxComplete(function(){
					AWPCPJQUERY(this).hide();
				});

				var MAX_FILE_SIZE = AWPCPJQUERY('input[name=MAX_FILE_SIZE]').val();
				var ADID = AWPCPJQUERY('input[name=ADID]').val();
				var ADTERMID = AWPCPJQUERY('input[name=ADTERMID]').val();
				var THEPLUGINPATH = '$awpcp_plugin_path';


				var dataString = 'MAX_FILE_SIZE='+ MAX_FILE_SIZE + '&ADID=' + ADID + '&ADTERMID=' + ADTERMID + '&THEPLUGINPATH=' + THEPLUGINPATH;


				var options = {
					beforeSubmit:  awpcpshowRequest,
					success:       awpcpshowResponse,
					url:       '".AWPCPURL."upload4jquery.php?+dataString',  // your upload script
					dataType:  'json'
				};
				AWPCPJQUERY('#AWPCPForm1').submit(function() {
					document.getElementById('message').innerHTML = '';
					AWPCPJQUERY(this).ajaxSubmit(options);
					return false;
				});
			});

			function awpcpshowRequest(formData, jqForm, options) {
				var AWPCPfileToUploadValue = AWPCPJQUERY('input[name=AWPCPfileToUpload]').fieldValue();
				if (!AWPCPfileToUploadValue[0]) {
					document.getElementById('message').innerHTML = 'Please select a file.';
					return false;
				}

				return true;
			}

			function awpcpshowResponse(data, statusText)  {
				if (statusText == 'success') {

					if (data.img != '') {
						document.getElementById('result').innerHTML = '<img src=\"".AWPCPUPLOADURL."/thumbs/'+data.img+'\" />';
						document.getElementById('ustatmsg').innerHTML = data.ustatmsg;
						document.getElementById('message').innerHTML = data.error;
					} else {
						document.getElementById('message').innerHTML = data.error;
						document.getElementById('ustatmsg').innerHTML = data.ustatmsg;

					}

					if(data.showhideuploadform == 1){
							document.getElementById(\"showhideuploadform\").style.display=\"none\";
					}
				} else {
					document.getElementById('message').innerHTML = 'Unknown error!';
					document.getElementById('ustatmsg').innerHTML = data.ustatmsg;
				}
			}

		</script>

	\n";
		}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// PROGRAM FUNCTIONS
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTIONS: Installation | Update
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Create the database tables if they do not not exist
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_install() {

global $wpdb,$awpcp_db_version;


   $table_name1 = $wpdb->prefix . "awpcp_categories";
   $table_name2 = $wpdb->prefix . "awpcp_adfees";
   $table_name3 = $wpdb->prefix . "awpcp_ads";
   $table_name4 = $wpdb->prefix . "awpcp_adsettings";
   $table_name5 = $wpdb->prefix . "awpcp_adphotos";
   $table_name6 = $wpdb->prefix . "awpcp_pagename";


if($wpdb->get_var("show tables like '$table_name1'") != $table_name1) {

    $sql = "CREATE TABLE " . $table_name1 . " (
	  `category_id` int(10) NOT NULL AUTO_INCREMENT,
	  `category_parent_id` int(10) NOT NULL,
	  `category_name` varchar(255) NOT NULL DEFAULT '',
	  PRIMARY KEY (`category_id`)
	) ENGINE=MyISAM;

	INSERT INTO " . $table_name1 . " (`category_id`, `category_parent_id`, `category_name`) VALUES
	(1, 0, 'General');


	CREATE TABLE " . $table_name2 . " (
	  `adterm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `adterm_name` varchar(100) NOT NULL DEFAULT '',
	  `amount` float(6,2) unsigned NOT NULL DEFAULT '0.00',
	  `recurring` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `rec_period` int(5) unsigned NOT NULL DEFAULT '0',
	  `rec_increment` varchar(5) NOT NULL DEFAULT '',
	  `buys` int(10) unsigned NOT NULL DEFAULT '0',
	  `imagesallowed` int(5) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`adterm_id`)
	) ENGINE=MyISAM;

	INSERT INTO " . $table_name2 . " (`adterm_id`, `adterm_name`, `amount`, `recurring`, `rec_period`, `rec_increment`, `buys`, `imagesallowed`) VALUES
	(1, '30 Day Listing', 9.99, 1, 31, 'D', 0, 6);


	CREATE TABLE " . $table_name3 . " (
	  `ad_id` int(10) NOT NULL AUTO_INCREMENT,
	  `adterm_id` int(10) NOT NULL DEFAULT '0',
	  `ad_category_id` int(10) NOT NULL,
	  `ad_category_parent_id` int(10) NOT NULL,
	  `ad_title` varchar(255) NOT NULL DEFAULT '',
	  `ad_details` text NOT NULL,
	  `ad_contact_name` varchar(255) NOT NULL DEFAULT '',
	  `ad_contact_phone` varchar(255) NOT NULL DEFAULT '',
	  `ad_contact_email` varchar(255) NOT NULL DEFAULT '',
	  `websiteurl` varchar( 375 ) NOT NULL,
	  `ad_city` varchar(255) NOT NULL DEFAULT '',
	  `ad_state` varchar(255) NOT NULL DEFAULT '',
	  `ad_country` varchar(255) NOT NULL DEFAULT '',
	  `ad_county_village` varchar(255) NOT NULL DEFAULT '',
	  `ad_item_price` int(25) NOT NULL,
	  `ad_views` int(10) NOT NULL,
	  `ad_postdate` date NOT NULL DEFAULT '0000-00-00',
	  `ad_last_updated` date NOT NULL,
	  `ad_startdate` datetime NOT NULL,
	  `ad_enddate` datetime NOT NULL,
	  `disabled` tinyint(1) NOT NULL DEFAULT '0',
	  `ad_key` varchar(255) NOT NULL DEFAULT '',
	  `ad_transaction_id` varchar(255) NOT NULL DEFAULT '',
	  `payment_gateway` varchar(255) NOT NULL DEFAULT '',
	  `payment_status` varchar(255) NOT NULL DEFAULT '',
	  FULLTEXT KEY `titdes` (`ad_title`,`ad_details`),
	  PRIMARY KEY (`ad_id`)
	) ENGINE=MyISAM;



	CREATE TABLE " . $table_name4 . " (
	  `config_option` varchar(50) NOT NULL DEFAULT '',
	  `config_value` text NOT NULL,
	  `config_diz` text NOT NULL,
	  `option_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`config_option`)
	) ENGINE=MyISAM COMMENT='0-checkbox, 1-text,2-textarea';

		INSERT INTO " . $table_name4 . " (`config_option`, `config_value`, `config_diz`, `option_type`) VALUES
			('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: Make sure page does not already exist]', 1),
			('freepay', '0', 'Charge Listing Fee?', 0),
			('requireuserregistration', '0', 'Require user registration?', 0),
			('main_page_display', '1', 'Main page layout [ 1 for ad listings | 2 for categories ]', 1),
			('paylivetestmode', '1', 'Put Paypal and 2Checkout in test mode.', 0),
			('useadsense', '1', 'Activate adsense', 0),
			('adsense', 'Adsense code', 'Your adsense code [ Best if 468 by 60 text or banner. ]', 2),
			('adsenseposition', '2', 'Adsense position. [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]', 1),
			('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiry].', 1),
			('imagesallowdisallow', '0', 'Uncheck to disallow images in ads. [Affects both free and paid]', 0),
			('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site', 0),
			('imagesallowedfree', '4', ' Free mode number of images allowed?', 1),
			('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]', 1),
			('maximagesize', '150000', 'Image Maximum file size.', 1),
			('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?', 1),
			('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]', 1),
			('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments', 1),
			('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]', 1),
			('activatepaypal', '1', 'Activate PayPal', 0),
			('activate2checkout', '1', 'Activate 2Checkout ', 0),
			('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?', 0),
			('notifyofadposted', '1', 'Notify admin of new ad.', 0),
			('imagesapprove', '0', 'Hide images until admin approves them', 0),
			('adapprove', '0', 'Disable ad until admin approves', 0),
			('disablependingads', '1', 'Enable paid ads that are pending payment.', 0),
			('showadcount', '1', 'Show how many ads a category contains.', 0),
			('displayadviews', '1', 'Show ad views', 0),
			('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 1),
			('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 1),
			('smtppassword', '', 'SMTP password [ if emails not processing normally]', 1),
			('onlyadmincanplaceads', '0', 'Only admin can post ads', '0'),
			('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '0'),
			('contactformcheckhumanhighnumval', '100', 'Math validation highest number', '1'),
			('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Contact Form Subject Line', '1'),
			('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Contact form body message', '2'),
			('seofriendlyurls', '0', 'Search Engine Friendly URLs? [ Does not work in some instances ]', 0),
			('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', 0),
			('hyperlinkurlsinadtext', '1', 'Make URLs in ad text clickable', '0'),
			('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval', 2),
			('displayphonefield', '1', 'Show phone field', 0),
			('displayphonefieldreqop', '0', 'Require phone', 0),
			('displaycityfield', '1', 'Show city field.', 0),
			('displaycityfieldreqop', '0', 'Require city', 0),
			('displaystatefield', '1', 'Show state field.', 0),
			('displaystatefieldreqop', '0', 'Require state', 0),
			('displaycountryfield', '1', 'Show country field.', 0),
			('displaycountryfieldreqop', '0', 'Require country', 0),
			('displaycountyvillagefield', '0', 'Show County/village/other.', 0),
			('displaycountyvillagefieldreqop', '0', 'Require county/village/other.', 0),
			('displaypricefield', '1', 'Show price field.', 0),
			('displaypricefieldreqop', '0', 'Require price.', 0),
			('displaywebsitefield', '1', 'Show website field', 0),
			('displaywebsitefieldreqop', '0', 'Require website', 0),
			('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.', 0),
			('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side', 2),
		('showlatestawpcpnews', '1', 'Allow AWPCP RSS.', 0);



	CREATE TABLE " . $table_name5 . " (
	  `key_id` int(10) NOT NULL AUTO_INCREMENT,
	  `ad_id` int(10) unsigned NOT NULL DEFAULT '0',
	  `image_name` varchar(100) NOT NULL DEFAULT '',
	  `disabled` tinyint(1) NOT NULL,
	  PRIMARY KEY (`key_id`)
	) ENGINE=MyISAM;


	CREATE TABLE " . $table_name6 . " (
	  `key_id` int(10) NOT NULL AUTO_INCREMENT,
	  `userpagename` varchar(100) NOT NULL DEFAULT '',
	  PRIMARY KEY (`key_id`)
	) ENGINE=MyISAM;


	";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);


      add_option("awpcp_db_version", $awpcp_db_version);

      wp_schedule_event( time(), 'hourly', 'doadexpirations_hook' );
   }

  else {

	global $wpdb,$awpcp_db_version;
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	Update the database tables in the event of a new version of plugin
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$installed_ver = get_option( "awpcp_db_version" );


    if( $installed_ver != $awpcp_db_version ) {


   	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.5.4 updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


   	$ad_websiteurl_column="websiteurl";

   	$ad_websiteurl_field=mysql_query("SELECT $ad_websiteurl_column FROM $table_name3;");

   		if (mysql_errno())
   		{

   			$wpdb->query("ALTER TABLE " . $table_name3 . "  ADD `websiteurl` VARCHAR( 500 ) NOT NULL AFTER `ad_contact_email`");
		}

    if(!field_exists($field='displaywebsitefield'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('displaywebsitefield', '1', 'Show website field', 0);");
	}

    if(!field_exists($field='displaywebsitefieldreqop'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('displaywebsitefieldreqop', '0', 'Require website', 0);");
	}


	 $query=("ALTER TABLE " . $table_name3 . "  DROP INDEX (`ad_title`,`ad_details`)");
	 @mysql_query($query);
	 $query=("ALTER TABLE " . $table_name3 . "  ADD FULLTEXT KEY `titdes` (`ad_title`,`ad_details`)");
	 @mysql_query($query);


   	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.5.3 updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if(!field_exists($field='smtphost'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
   	 	('smtphost', 'mail.example.com', 'If emails are not going out you can fill in your SMTP host here to try using the SMTP alternative', 1);");
	}

    if(!field_exists($field='smtpusername'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('smtpusername', 'smtp_username', 'If emails are not going out you can fill in your SMTP username here to try using the SMTP alternative', 1);");
	}

    if(!field_exists($field='smtppassword'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('smtppassword', '', 'If emails are not going out you can fill in your SMTP password here to try using the SMTP alternative', 1);");
	}



    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.5.1 updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Increase the length value for the ad_item_price field

	$wpdb->query("ALTER TABLE " . $table_name3 . " CHANGE `ad_item_price` `ad_item_price` INT( 25 ) NOT NULL");



    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //1.0.5 updates
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if(!field_exists($field='main_page_display'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('main_page_display', '1', 'You can either display your ad categories on your main page or your ad listings. By default your categories are displayed. To display your listings instead change the value 1 to 2', 1);");
	}

    if(!field_exists($field='awpcp_thickbox_disabled'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
    	('awpcp_thickbox_disabled', '0', 'Check this box to turn off the thickbox/lightbox feature if it conflicts with other elements of your site', 0);");
	}

    if(!field_exists($field='contactformcheckhumanhighnumval'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('contactformcheckhumanhighnumval', '100', 'The value of the high number to use when selecting random values for addition problem used in the contact form to check if the form is being submitted by a person ', '1');");
	}

    if(!field_exists($field='contactformsubjectline'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('contactformsubjectline', 'A response to your ad posted at AWPCP Demo Site', 'Text for subject line of email sent to ad poster when someone uses contact form to send message ', '1');");
	}

    if(!field_exists($field='contactformbodymessage'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('contactformbodymessage', 'This is a message in response to your ad posted at AWPCP Demo Site at http://www.awpcp.com', 'Text for the body of message used in the email sent to ad poster when someone uses contact form. Note that the actual message submitted via the contact form will be appended below this note', '2');");
	}

    if(!field_exists($field='displaycountyvillagefield'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('displaycountyvillagefield', '0', 'Check this if you want to display a field for county/village/other. Leave unchecked if field not needed.', 0);");
	}

    if(!field_exists($field='displaycountyvillagefieldreqop'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('displaycountyvillagefieldreqop', '0', 'If showing the county/village/other input field, check this if the user is required to enter a value for county/village/other.', 0);");
	}

    if(!field_exists($field='buildsearchdropdownlists'))
	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Check here to use drop down lists for the regions fields where data is available. Note that with the regions module installed the value for this option is overriden.', 0);");
	}


	$ad_county_village_column="ad_county_village";

	$ad_county_vilalge_field=mysql_query("SELECT $ad_county_village_column FROM $table_name3;");

	if (mysql_errno())
	{
		$wpdb->query("ALTER TABLE " . $table_name3 . "  ADD `ad_county_village` varchar(255) NOT NULL AFTER `ad_country`");
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 1.0.4.9 updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Add field uploadfoldername on already installed sites that do not have the field set

   	if(!field_exists($field='uploadfoldername'))
   	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('uploadfoldername', 'uploads', 'Name of your main wp upload folder. This folder must exist and be located in your wp-content directory', 1);");
	}

	// Add field displayadviews for admin to decide whether or not to display number of times an ad has been viewed

   	if(!field_exists($field='displayadviews'))
   	{
		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
		('displayadviews', '1', 'Uncheck this to disable showing how many times an ad has been viewed. Check it if you want to display how many times an ad has been viewed', 0);");
	}

	// Add field ad_views to table awpcp_ads to track ad views
	$ad_views_column="ad_views";

	$ad_views_field=mysql_query("SELECT $ad_views_column FROM $table_name3;");

		if (mysql_errno())
		{
			$wpdb->query("ALTER TABLE " . $table_name3 . "  ADD `ad_views` int(10) NOT NULL AFTER `ad_item_price`");
		}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 1.0.4.8 updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Add Price field on already installed sites that do not have the fields set

        	if(!field_exists($field='displaypricefield')){

					$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
					('displaypricefield', '1', 'Uncheck this if you prefer to hide the item price input field. Check it to show the item price input field.', 0);");
			}

         	if(!field_exists($field='displaypricefieldreqop')){

 					$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
					('displaypricefieldreqop', '0', 'If showing the item price input field, check this if the user is required to enter an item price.', 0);");
			}

	// Insert new field ad_item_price into awpcp_ads table
	$ad_itemprice_column="ad_item_price";

	$ad_itemprice_field=mysql_query("SELECT $ad_itemprice_column FROM $table_name3;");

		if (mysql_errno())
		{

			$wpdb->query("ALTER TABLE " . $table_name3 . "  ADD `ad_item_price` INT( 10 ) NOT NULL AFTER `ad_country`");
		}

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 1.0.4.7 updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    	if(!field_exists($field='showlatestawpcpnews')){

				$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
				('showlatestawpcpnews', '1', 'Uncheck this to remove the news feed that shows the latest news about Another Wordpress Classifieds Plugin. If you want to be made immediately aware of bug fixes and new features added to the plugin you should leave the value checked.', 0);");
			}

			if(!field_exists($field='requireuserregistration')){

				$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
				('requireuserregistration', '0', 'You can require users to be registered before they can post an ad. With the box checked you are running in registration required mode. With the box unchecked anyone can post an ad whether or not they are registered.', 0);");
		}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.4.4 installation updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 		if(!field_exists($field='paypalcurrencycode')){

				$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
				('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments', 1);");
		}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.4.2 installation updates - no database changes
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.4.1 installation updates - checking for fields notice_awaiting_approval_ad,displayphonefiled,displayphonefieldreqop,displaycityfield,displaycityfieldreqop
	// displaystatefield, displaystatefieldreqop, displaycountryfield, displaycountryfieldreqop and uiwelcome - In 1.0.4 these fields were not inserted
    // due to misplaced semi-colon after field hyperlinkurlintext
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if(!field_exists($field='notice_awaiting_approval_ad')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','The message to print after an ad has been posted if you are manually approving ads before they are displayed on the site', 2);");
		}

		if(!field_exists($field='displayphonefield')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displayphonefield', '1', 'Uncheck this if you prefer to hide the phone input field. Check it to show the phone input field.', 0);");
		}

		if(!field_exists($field='displayphonefieldreqop')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displayphonefieldreqop', '0', 'If showing the phone input field check this if the user is required to enter a phone number. [SUGGESTION: It is probably better to leave unchecked so phone number is optional.]', 0);");
		}

		if(!field_exists($field='displaycityfield')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displaycityfield', '1', 'Uncheck this if you prefer to hide the city input field. Check it to show the city input field.', 0);");
		}

		if(!field_exists($field='displaycityfieldreqop')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displaycityfieldreqop', '0', 'If showing the city input field check this if the user is required to enter a city. [SUGGESTION: It is probably better to leave unchecked so city is optional.]', 0);");
		}

		if(!field_exists($field='displaystatefield')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displaystatefield', '1', 'Uncheck this if you prefer to hide the state input field. Check it to show the state input field.', 0);");
		}

		if(!field_exists($field='displaystatefieldreqop')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displaystatefieldreqop', '0', 'If showing the state field check this if the user is required to enter a state. [SUGGESTION: It is probably better to leave unchecked so state is optional.]', 0);");
		}

		if(!field_exists($field='displaycountryfield')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displaycountryfield', '1', 'Uncheck this if you prefer to hide the country input field. Check it to show the country input field.', 0);");
		}

		if(!field_exists($field='displaycountryfieldreqop')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('displaycountryfieldreqop', '0', 'If showing the country input field, check this if the user is required to enter a country. [SUGGESTION: It is probably better to leave unchecked so country is optional.]', 0);");
		}


		if(!field_exists($field='uiwelcome')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side', 2);");
		}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.4 installation updates - no database changes
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 1.0.3 installation updates
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if(!field_exists($field='onlyadmincanplaceads')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('onlyadmincanplaceads', '0', 'Check this box if you want to prevent anyone but the adminstrator from being able to post ads ', '0');");

		}
		if(!field_exists($field='hyperlinkurlsinadtext')){

			$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
			('hyperlinkurlsinadtext', '1', 'Uncheck this box if you do not want to make any URLs users place in ad text to be clickable ', '0');");

		}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 1.0.2 installation updates
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Fix the UTF-8 Charset problem and add option contactformcheckhuman to awpcp_adsettings (March 25 2009)


	   $table_name1 = $wpdb->prefix . "awpcp_categories";
	   $table_name2 = $wpdb->prefix . "awpcp_adfees";
	   $table_name3 = $wpdb->prefix . "awpcp_ads";
	   $table_name4 = $wpdb->prefix . "awpcp_adsettings";
	   $table_name5 = $wpdb->prefix . "awpcp_adphotos";
	   $table_name6 = $wpdb->prefix . "awpcp_pagename";

	   $wpdb->query("ALTER TABLE " . $table_name1 . "  DEFAULT CHARACTER SET utf8");
	   $wpdb->query("ALTER TABLE " . $table_name2 . "  DEFAULT CHARACTER SET utf8");
	   $wpdb->query("ALTER TABLE " . $table_name3 . "  DEFAULT CHARACTER SET utf8");
	   $wpdb->query("ALTER TABLE " . $table_name4 . "  DEFAULT CHARACTER SET utf8");
	   $wpdb->query("ALTER TABLE " . $table_name5 . "  DEFAULT CHARACTER SET utf8");
	   $wpdb->query("ALTER TABLE " . $table_name6 . "  DEFAULT CHARACTER SET utf8");




		if(!field_exists($field='contactformcheckhuman')){

	  		$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `option_type`	) VALUES
						('contactformcheckhuman', '1', 'Uncheck this box if you want to disable the math problem used to check if the person filling out the form to contact about an ad is human. ', '0');");

		}



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        update_option( "awpcp_db_version", $awpcp_db_version );

  	}
  }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// End database creation/updating functions
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the user side classified page exists
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function checkifclassifiedpage($pagename){
$awpcppagename = sanitize_title($pagename, $post_ID='');

	global $wpdb, $isclassifiedpage, $table_prefix;

		$query="SELECT * FROM {$table_prefix}posts WHERE post_title='$pagename' AND post_name='$awpcppagename'";
		 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
			if (mysql_num_rows($res) && mysql_result($res,0,0))
			{
				$myreturn=true;
			}
			return $myreturn;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Launch the main classifieds screen and add the menu items
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_launch(){
global $awpcp_plugin_path;
add_menu_page('AWPCP Classifieds Management System', 'Classifieds', '10', 'awpcp.php', 'awpcp_home_screen', MENUICO);
add_submenu_page('awpcp.php', 'Configure General Options ', 'Settings', '10', 'Configure1', 'awpcp_opsconfig_settings');
add_submenu_page('awpcp.php', 'Listing Fees Setup', 'Fees', '10', 'Configure2', 'awpcp_opsconfig_fees');
add_submenu_page('awpcp.php', 'Add/Edit Categories', 'Categories', '10', 'Configure3', 'awpcp_opsconfig_categories');
add_submenu_page('awpcp.php', 'View Ad Listings', 'Listings', '10', 'Manage1', 'awpcp_manage_viewlistings');
add_submenu_page('awpcp.php', 'View Ad Images', 'Images', '10', 'Manage2', 'awpcp_manage_viewimages');
if( file_exists("$awpcp_plugin_path/awpcp_region_control_module.php") )
{
	add_submenu_page('awpcp.php', 'Manage Regions', 'Regions', '10', 'Configure4', 'awpcp_opsconfig_regions');
}
add_submenu_page('awpcp.php', 'Uninstall AWPCP', 'Uninstall', '10', 'Manage3', 'awpcp_uninstall');

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_manage(){}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Display the admin home screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function awpcp_home_screen(){

global $message,$user_identity,$wpdb,$awpcp_plugin_path,$imagesurl,$awpcp_db_version,$haspoweredbyremovalmodule,$hasregionsmodule,$hascaticonsmodule;
$table_name4 = $wpdb->prefix . "awpcp_adsettings";


	echo "<div class=\"wrap\"><h2>AWPCP Classifieds Management System</h2>
	$message <div style=\"padding:20px;\">Thank you for using Another Wordpress Classifieds Plugin. This is version <b>$awpcp_db_version</b> of the plugin. As a reminder, please use this plugin knowing that is it is a work in progress and is by no means guaranteed to be a bug-free product. Development of this plugin is not a full-time undertaking. Consequently upgrades will be slow in coming; however, please feel free to report bugs and request new features.</div>";


$tableexists=checkfortable($table_name4);

if(!$tableexists) {

echo "<b>!!!!ALERT:</b>There appears to be a problem with the plugin. The plugin is activated but your database tables are missing. Please de-activate the plugin from your plugins page then try to reactivate it.";}

else {

	$cpagename=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename);
	if ($isclassifiedpage == false){
		echo "<h2>Setup Process</h2>";

	echo "<p>It looks like you have not yet told the system how you want your classifieds to operate.</p>

	<p>Please begin by setting up the options for your site. The system needs to know a number of things about how you want to run your classifieds.</p>
	<a href=\"?page=Configure1\">Click here to setup your site options</a></p>";

	} else {


$awpcp_classifieds_page_conflict_check=checkforduplicate($cpagename);
if( $awpcp_classifieds_page_conflict_check > 1)
{
	echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\"><img src=\"$imagesurl/Warning.png\" border=\"0\" alt=\"Alert\" style=\"float:left;margin-right:10px;\"> It appears you have a potential problem that could result in the malfunctioning of Another Wordpress Classifieds plugin. A check of your database was performed and [$awpcp_classifieds_page_conflict_check] entries were found that share the same post_name value as your classifieds page. If for some reason you uninstall and then reinstall this plugin and the duplicate pages remain in your database, it could break the plugin and prevent it from working. To fix this problem you can manually delete the duplicate pages and leave only the page with the ID of your real classifieds page, or you can use the link below to rebuild your classifieds page. The process will include first deleting all existing pages with a post name value of <b>$awpcppagename</b>. Note that if you recreate the page, it will be assigned a new page ID so if you are referencing the classifieds page ID anywhere outside of the classifieds program you will need to adjust the old ID to the new ID.<p><a href=\"?page=Configure1&action=recreatepage\">Recreate the classifieds page to fix the conflict</a></p></div>";
}

echo "
<div style=\"padding:10px;\">
<div style=\"float:left;width:50%;\">";
$totallistings=countlistings();
echo "<div style=\"background-color: #eeeeee;padding:10px;\"border:1px dotted #dddddd;>There are currently [<b>$totallistings</b>] ads in the system</div>";

if(get_awpcp_option(freepay) == 1){
	if(adtermsset()){echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">You have setup your listing fees. To edit your fees use the \"Manage Listing Fees\" option.</div>";}
	else {echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">ALERT! You have not configured your Listing fees. Use the \"Manage Listing Fees\" option to set up your listing fees. Once that is completed, if you are running in pay mode, the options will automatically appear on the listing form for users to fill out.</div>";}
} else {echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">You currently have your system configured to run in free mode. To change to <b>pay</b> mode go to \"Manage General Options\" and uncheck the box that accompanies the text [ <em>You can run a free or paid classified listing service. With the box checked you are running in pay mode. With the box unchecked you are running in free mode</em> ]</div>";}


if(categoriesexist()){
$totalcategories=countcategories();
$totalparentcategories=countcategoriesparents();
$totalchildrencategories=countcategorieschildren();

echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">There are currently [<b>$totalcategories</b>] categories (<b>$totalparentcategories</b> are top level and <b>$totalchildrencategories</b> are sub level)<br/>
Use the \"Manage Categories\" option to set up your edit your current categories or add new categories.</div>";
}
else {echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">ALERT! You have not setup any categories. Use the \"Manage Categories\" option to set up your categories.</div>";}

if(get_awpcp_option(freepay) == 1){
echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\"> You currently have your system configured to run in pay mode. To change to <b>free</b> mode go to \"Manage General Options\" and check the box that accompanies the text [ <em>You can run a free or paid classified listing service. With the box checked you are running in pay mode. With the box unchecked you are running in free mode</em> ]</div>";}




echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">Use the buttons on the right to configure your various options</div>";

if(get_awpcp_option(showlatestawpcpnews)){
	echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\"><h4>Latest News About Another Wordpress Classifieds Plugin</h4><div style=\"margin:15px 25px 25px 0px;\">";
	$i=0;
	require_once $awpcp_plugin_path.'/classes/feed_reader.class.php';
	$fr=new feedReader();
	$ok=$fr->getFeed("http://feeds2.feedburner.com/Awpcp?num=10");

	$fr->parseFeed();

	$awpcp_news=$fr->getFeedOutputData();

		if(isset($awpcp_news['item']) && !empty($awpcp_news['item'])){
		$awpcp_news=$awpcp_news['item'];

			for ($i=0;$i<5;$i++) {

			$awpcpnewslink=$awpcp_news[$i]['link'];
			$awpcpnewstitle=$awpcp_news[$i]['title'];
			$awpcpnewsdescription=$awpcp_news[$i]['description'];
			$awpcpnewsdescription = strip_tags($awpcpnewsdescription);
			$awpcpnewsdescription = str_replace(' ',' ',$awpcpnewsdescription );
			$awpcpnewsdescription=awpcpLimitText($awpcpnewsdescription,10,150,"");

			$awpcpnewsline="<span style=\"margin:0; padding:3px 0 3px 0px;\"><a style=\"text-decoration:none;\" href=\"$awpcpnewslink\" target=\"_blank\">$awpcpnewstitle</a></span><p style=\"padding:0 0 15px 0; margin:0;\">$awpcpnewsdescription <a style=\"font-size:x-small\" href=\"$awpcpnewslink\" target=\"_blank\">Read full text</a></p>";
			echo $awpcpnewsline;
			}

		}

	echo "</div></div>";
}

echo "

</div>
<div style=\"float:left;width:30%;margin:0 0 0 20px;\">
<ul>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure1\">Manage General Options</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure2\">Manage Listing Fees</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure3\">Manage Categories</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage1\">Manage Listings</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage2\">Manage Images</a></li>
</ul>";

if(get_awpcp_option(showlatestawpcpnews))
{
	echo "<p><b>Premium Modules</b></p>
	<em>Installed</em><br/><ul>";
	if( ($hasregionsmodule != 1) && ($hascaticonsmodule != 1) )
	{
		echo "<li>No premium modules installed</li>";
	}
	else
	{
		if( ($hasregionsmodule == 1) )
		{
			echo "<li>Regions Control Module</li>";
		}
		if( ($hascaticonsmodule == 1) )
		{
			echo "<li>Category Icons Module</li>";
		}
	}

	echo "</ul><em>Uninstalled</em><ul>";

	if( ($hasregionsmodule != 1) )
	{
		echo "<li><a href=\"http://www.awpcp.com/premium-modules/regions-control-module\">Regions Control Module</a></li>";
	}
	if( ($hascaticonsmodule != 1) )
	{
		echo "<li><a href=\"http://www.awpcp.com/premium-modules/category-icons-module/\">Category Icons Module</a></li>";
	}
	if( ($hasregionsmodule == 1) && ($hascaticonsmodule == 1) )
	{
		echo "<li>No uninstalled premium modules</li>";
	}

	echo "</ul><p><b>Other Modules</b></p>
	<em>Installed</em><br/><ul>";

	if( ($haspoweredbyremovalmodule != 1) )
	{
		echo "<li>No \"Other\" modules installed</li>";
	}
	else
	{
		if( ($haspoweredbyremovalmodule == 1) )
		{
			echo "<li>Powered-By Link Removal Module</li>";
		}
	}

	echo "</ul><em>Uninstalled</em><ul>";

	if( ($haspoweredbyremovalmodule != 1) )
	{
		echo "<li><a href=\"http://www.awpcp.com/premium-modules/powered-by-link-removal-module/\">Powered-By Link Removal Module</a></li>";
	}
	else
	{
		echo "No uninstalled \"Other\" modules";
	}

	echo "</ul>";
}

echo "
</div>

</div>

</div>";}
}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Manage the General settings
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage general configuration options
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_settings(){

global $wpdb,$table_prefix;
global $message;

if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
{
	if($_REQUEST['action'] == 'recreatepage')
	{
			$cpagename=get_awpcp_option('userpagename');
			$awpcppagename = sanitize_title($cpagename, $post_ID='');

			$pageswithawpcpname=array();

			$query="SELECT ID FROM {$table_prefix}posts WHERE post_name = '$awpcppagename'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			if (mysql_num_rows($res))
			{
			 	while ($rsrow=mysql_fetch_row($res))
			 	{
			 		$pageswithawpcpname[]=$rsrow[0];
			 	}

			}

				foreach ( $pageswithawpcpname as $pagewithawpcpname )
				{

					//Delete the pages
					$query="DELETE FROM {$table_prefix}posts WHERE ID = '$pagewithawpcpname'";
					@mysql_query($query);

					$query="DELETE FROM {$table_prefix}postmeta WHERE post_id = '$pagewithawpcpname'";
					@mysql_query($query);

					$query="DELETE FROM {$table_prefix}comments WHERE comment_post_ID = '$pagewithawpcpname'";
					@mysql_query($query);
				}

					deleteuserpageentry($cpagename);
					maketheclassifiedpage($cpagename);

				echo "<div style=\"padding:50px;font-weight:bold;\"><p>The page has been recreated</p><h3><a href=\"?page=awpcp.php\">Back to Control Panel</a></h3></div>";
				die;

	}

}

$table_name4 = $wpdb->prefix . "awpcp_adsettings";

		/////////////////////////////////
		// Start the page display
		/////////////////////////////////

echo "<div class=\"wrap\">
<h2>AWPCP Classifieds Management System: Settings Configuration</h2>
 $message <p style=\"padding:10px;border:1px solid#dddddd;\">Below you can modify the settings for your classifieds system. With options including turning on/off images in ads, turning on/off HTML in ads, including adsense in ads (will insert 468X60 text ad above ad content and 468X60 image ad below ad content). Also provide your paypal business email and 2checout ID. The system provides only these 2 payment gateways at this time.</p>
<form method=\"post\" id=\"awpcp_launch\">
<p><input class=\"button\" name=\"savesettings\" type=\"submit\" value=\"Save Settings\" /></p>";

		//////////////////////////////////////
		// Retrieve the currently saved data
		/////////////////////////////////////

$query="SELECT config_option,config_value,config_diz,option_type FROM ".$table_name4."";
if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		/////////////////////////////////////////
		// Setup the data items for display
		/////////////////////////////////////////

$options=array();

while($rsrow=mysql_fetch_row($res)) {
	list($config_option,$config_value,$config_diz,$option_type)=$rsrow;

if($config_option == 'smtppassword')
{
	if(get_awpcp_option('smtppassword') )
	{
		$config_diz.="<br><b>**Your password is saved but not shown below. Leave the field blank unless you are changing your SMTP password</b>";
		$config_value='';
	}
}


	if ($option_type==0) {	// checkbox
		$field="<input type=\"checkbox\" name=\"$config_option\" value=\"1\" ";
		if (!empty($config_value)) {
			$field.="checked";
		}
		$field.=" />";
	} elseif ($option_type==1) {	// text input
		$field="<input class=\"regular-text\" size=\"30\" type=\"text\" class=\"inputbox\" name=\"$config_option\" value=\"$config_value\" />";
	}elseif ($option_type==2) {	// textarea input
		$field="<textarea name=\"$config_option\" rows=\"5\" cols=\"50\">$config_value</textarea>";
	}

		/////////////////////////////////////////
		// Display the data items
		////////////////////////////////////////

echo "
<p style=\"display:block;margin-bottom:25px;\">
$config_diz<br/>
$field</p>";
}

echo "
<input class=\"button\" type=\"submit\" name=\"savesettings\" value=\"Save Settings\" /></form></div>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage general configuration options
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_fees(){

global $wpdb;
global $message;

$table_name2 = $wpdb->prefix . "awpcp_adfees";


		/////////////////////////////////
		// Start the page display
		/////////////////////////////////

echo "<div class=\"wrap\">
<h2>AWPCP Classifieds Management System: Listing Fees Management</h2>
 $message <p style=\"padding:10px;border:1px solid#dddddd;\">Below you can add and edit your listing fees. As an example you can add an entry set at $9.99 for a 30 day listing, then another entry set at $17.99 for a 60 day listing. For each entry you can set a specific number of images a user can upload. If you have allow images turned off in your main configuration settings the value you add here will not matter as an upload option will not be included in the ad post form. You can also set a text limit for the ads. The value is in words.</p>";

 		///////////////////////////////////////
 		// Handle case of adding new settings
 		//////////////////////////////////////

 if(isset($_REQUEST['addnewlistingfeeplan']) && !empty($_REQUEST['addnewlistingfeeplan'])){

 $rec_increment_op="<option value='D' ".(($rec_increment=='D') ? ("selected") : ("")).">Days</option>\n";

 echo "
 		<form method=\"post\" id=\"awpcp_launch\">
 		<p style=\"display:block;margin-bottom:25px;border-bottom:1px dotted #dddddd;\">
 		<p>Name this fee option (Example: 30 day Listing)<br/>
 		<input class=\"regular-text\" size=\"30\" type=\"text\" class=\"inputbox\" name=\"adterm_name\" value=\"$adterm_name\" /></p>
 		<p>Price for this fee option (Please enter in x.xx format)<br/>
 		<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"amount\" value=\"$amount\" /></p>
 		<p>Number of days an ad purchased using this option can run<br/>
 		<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"rec_period\" value=\"$rec_period\" /></p>
 		<p>Number of images that can be used with this fee option<br/>
 		<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"imagesallowed\" value=\"$imagesallowed\" /></p>
 		<p>Term plans to run in measures of <br/>
 		<select name=\"rec_increment\" size=\"1\">$rec_increment_op</select></p>
 		<input class=\"button\" type=\"submit\" name=\"addnewfeesetting\" value=\"Add New Plan\" />
 		</form>";

 	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The new plan has been added!</div>";
 }

 	else {

		//////////////////////////////////////
		// Retrieve the currently saved data
		/////////////////////////////////////


$query="SELECT adterm_id,adterm_name,amount,rec_period,rec_increment,imagesallowed FROM ".$table_name2."";
if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
$plans=array();
if (mysql_num_rows($res)) {

	while ($rsrow=mysql_fetch_row($res)) {
	list($adterm_id,$adterm_name,$amount,$rec_period,$rec_increment,$imagesallowed)=$rsrow;
		$rec_increment_op="<option value='D' ".(($rec_increment=='D') ? ("selected") : ("")).">Days</option>\n";


				/////////////////////////////////////////
				// Display the items
				////////////////////////////////////////

		echo "
		<div style=\"padding:20px;border-bottom:1px solid #dddddd;border-top:1px dotted #dddddd;background:#eeeeee;\">
		<form method=\"post\" id=\"awpcp_launch\"><input type=\"hidden\" name=\"adterm_id\" value=\"$adterm_id\">
		<p>Name this fee option (Example: 30 day Listing)<br/>
		<input class=\"regular-text\" size=\"30\" type=\"text\" class=\"inputbox\" name=\"adterm_name\" value=\"$adterm_name\" /></p>
		<p>Price for this fee option (Please enter in x.xx format)<br/>
		<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"amount\" value=\"$amount\" /></p>
		<p>Number of days an ad purchased using this option can run<br/>
		<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"rec_period\" value=\"$rec_period\" /></p>
		<p>Number of images that can be used with this fee option<br/>
		<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"imagesallowed\" value=\"$imagesallowed\" /></p>
		<p>Term plans to run in measures of <br/>
		<select name=\"rec_increment\" size=\"1\">$rec_increment_op</select></p>
		<input class=\"button\" type=\"submit\" name=\"savefeesetting\" value=\"Save\" /> <input class=\"button\" type=\"submit\" name=\"deletefeesetting\" value=\"Delete\" />
		</form></div><br/>";
	}
}
	echo "
		<form method=\"post\" id=\"awpcp_opsconfig_fees\">
		<p style=\"padding:10px;\"><input class=\"button\" type=\"submit\" name=\"addnewlistingfeeplan\" value=\"Add a new listing fee plan\" /></p>
		</form>";
	}

}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage existing listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_categories(){


global $wpdb, $message, $imagesurl, $clearform,$hascaticonsmodule;




$table_name1 = $wpdb->prefix . "awpcp_categories";
$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);


$cat_ID='';
$category_name='';
$aeaction='';
$category_parent_id='';
$promptmovetocat='';
$aeaction='';

		///////////////////////////////////////////////////
		// Check for existence of a category ID and action

	if( isset($_REQUEST['editcat']) && !empty($_REQUEST['editcat']) )
	{
		$cat_ID=$_REQUEST['editcat'];
		$action = "edit";
	}

	elseif( isset($_REQUEST['delcat']) && !empty($_REQUEST['delcat']) )
	{
		$cat_ID=$_REQUEST['delcat'];
		$action = "delcat";
	}

	elseif( isset($_REQUEST['managecaticon']) && !empty($_REQUEST['managecaticon']) )
	{

		$cat_ID=$_REQUEST['managecaticon'];
		$action = "managecaticon";

	}


	elseif(isset($_REQUEST['cat_ID']) && !empty($_REQUEST['cat_ID']))
		{
			$cat_ID=$_REQUEST['cat_ID'];
		}


	if( !isset($action)  || empty($action) )
	{
		if( isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
		{
			$action=$_REQUEST['action'];
		}

	}


	if( $action == 'edit' )
	{
		$aeaction='edit';
	}

	if( $action == 'editcat' )
	{
			$aeaction='edit';
	}

	if( $action == 'delcat' )
	{
		$aeaction='delete';
	}

	if( $action == 'managecaticon' )
	{

		echo "<div class=\"wrap\">
		<h2>AWPCP Classifieds Management System: Categories Management</h2>
		";

			global $awpcp_plugin_path;

			if($hascaticonsmodule == 1 )
			{
				if( is_installed_category_icon_module() )
				{
					load_category_icon_management_page($defaultid=$cat_ID,$offset,$results);
				}
			}

		echo "</div>";
		die;
	}

	if( $action == 'setcategoryicon' )
	{

			global $awpcp_plugin_path;

			if($hascaticonsmodule == 1 )
			{
				if( is_installed_category_icon_module() )
				{


					if( isset($_REQUEST['cat_ID']) && !empty($_REQUEST['cat_ID']) )
					{
						$thecategory_id=$_REQUEST['cat_ID'];
					}

					if( isset($_REQUEST['category_icon']) && !empty($_REQUEST['category_icon']) )
					{
						$theiconfile=$_REQUEST['category_icon'];
					}

					if( isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) )
					{
						$offset=$_REQUEST['offset'];
					}

					if( isset($_REQUEST['results']) && !empty($_REQUEST['results']) )
					{
						$results=$_REQUEST['results'];
					}

					$message=set_category_icon($thecategory_id,$theiconfile,$offset,$results);
					if( isset($message) && !empty($message) )
					{
						$clearform=1;
					}


				}
			}
	}

	if( isset($clearform) && ( $clearform == 1) )
	{
		unset($cat_ID,$action, $aeaction);
	}

		$category_name=get_adcatname($cat_ID);
		$cat_parent_ID=get_cat_parent_ID($cat_ID);

			if($aeaction == 'edit')
			{
				$aeword1="You are currently editing the category shown below.";
				$aeword2="Edit Current Category";
				$aeword3="Parent Category";
				$addnewlink="<a href=\"?page=Configure3\">Add A New Category</a>";
			}
			elseif($aeaction == 'delete')
			{
				if( $cat_ID != 1)
				{

					$aeword1="If you're sure that you want to delete this category please press the delete button";
					$aeword2="Delete this category";
					$aeword3="Parent Category";
					$addnewlink="<a href=\"?page=Configure3\">Add A New Category</a>";

					if(ads_exist_cat($cat_ID))
					{
						if( category_is_child($cat_ID) ) {
							$movetocat=get_cat_parent_ID($cat_ID);
						}
						else
						{
							$movetocat=1;
						}

						$movetoname=get_adcatname($movetocat);
						if( empty($movetoname) )
						{
							$movetoname="Untitled";
						}

						$promptmovetocat="<p>The category contains ads. The ads will be moved to <b>\"$movetoname\"</b> if you do not select a category to move them to.</p>";

						$defaultcatname=get_adcatname($catid=1);

						if( empty($defaultcatname) )
						{
							$defaultcatname="Untitled";
						}

						if(category_has_children($cat_ID))
						{
							$promptmovetocat.="<p>The category also has children. The children will be adopted by <b>\"$defaultcatname\"</b> if you do not specify a move-to category. <b>Note:</b> The move-to category specified applies to both ads and categories.</p>";
						}
						$promptmovetocat.="<p align=\"center\"><select name=\"movetocat\"><option value=\"0\">Please select a Move-To category</option>";
						$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude=$cat_ID);
						$promptmovetocat.="$categories</select>";
					}

					$thecategoryparentname=get_adparentcatname($cat_parent_ID);
				}
				else
				{
					$aeword1="Sorry but you cannot delete <b>$category_name</b>. It is the default category. The default category cannot be deleted.";
					$aeword2='';
					$aeword3='';
					$addnewlink="<a href=\"?page=Configure3\">Add A New Category</a>";
				}
			}
			else
			{
				if( empty($aeaction) )
				{
					$aeaction="newcategory";
				}

				$aeword1="Enter the category name";
				$aeword2="Add New Category";
				$aeword3="List Category Under";
				$addnewlink='';

			}


			if($aeaction == 'delete')
			{
				if($cat_ID == 1)
				{
					$categorynameinput='';
					$selectinput='';
				}
				else
				{
					$categorynameinput="<p style=\"background:transparent url($imagesurl/delete_ico.png) left center no-repeat;padding-left:20px;\">Category to Delete: $category_name</p>";
					$selectinput="<p style=\"background:#D54E21;padding:3px;color:#ffffff;\">$thecategoryparentname</p>";
					$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
				}
			}
			elseif($aeaction == 'edit')
			{
					$categorynameinput="<p style=\"background:transparent url($imagesurl/edit_ico.png) left center no-repeat;padding-left:20px;\">Category to Edit: $category_name</p><p><input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/></p>";
					$selectinput="<p><select name=\"category_parent_id\"><option value=\"0\">Save as Top Level Category</option>";
					$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude='');
					$selectinput.="$categories
					</select></p>";
					$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
			}
			else {
				$categorynameinput="<p style=\"background:transparent url($imagesurl/post_ico.png) left center no-repeat;padding-left:20px;\">Add a New Category</p><input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/>";
				$selectinput="<p><select name=\"category_parent_id\"><option value=\"0\">Save as Top Level Category</option>";
				$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude='');
				$selectinput.="$categories
				</select></p>";
				$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
			}

				/////////////////////////////////
				// Start the page display
				/////////////////////////////////

		echo "<div class=\"wrap\">
		<h2>AWPCP Classifieds Management System: Categories Management</h2>
		 $message <div style=\"padding:10px;\"><p>Below you can add and edit your categories. For more information about managing your categories visit <a href=\"http://www.awpcp.com/about/categories/\">Useful Information for Classifieds Categories Management</a>  </p>
		 <b>Icon Meanings:</b> <img src=\"$imagesurl/edit_ico.png\" alt=\"Edit Category\" border=\"0\"> Edit Category <img src=\"$imagesurl/delete_ico.png\" alt=\"Delete Category\" border=\"0\"> Delete Category
		 ";

		 if($hascaticonsmodule == 1 )
		 {
			 if( is_installed_category_icon_module() )
			 {
				echo "<img src=\"$imagesurl/icon_manage_ico.png\" alt=\"Manage Category Icon\" border=\"0\"> Manage Category icon";
			 }
		}



		 echo "
		 </div>

		 <div style=\"width:30%;float:left;padding:10px;border-bottom:1px solid #dddddd;border-top:1px dotted #dddddd;$backfont\">
		 <form method=\"post\" id=\"awpcp_launch\">
		 <input type=\"hidden\" name=\"category_id\" value=\"$cat_ID\" />
		  <input type=\"hidden\" name=\"aeaction\" value=\"$aeaction\" />
		  <input type=\"hidden\" name=\"offset\" value=\"$offset\" />
		  <input type=\"hidden\" name=\"results\" value=\"$results\" />

		<p>$aeword1</p>
		$categorynameinput


		<p style=\"margin-top:10px;\"> $aeword3</p>
		$selectinput

		$promptmovetocat

		 <p class=\"submit\">$submitbuttoncode $addnewlink</p>
		 </form>
		 </div>
		 <div style=\"margin:0;padding:0px 0px 10px 10px;float:left;width:60%\">";

			///////////////////////////////////////////////////////////
			// Show the paginated categories list for management
			//////////////////////////////////////////////////////////

		 $from="$table_name1";
		 $where="category_name <> ''";

		 $pager1=create_pager($from,$where,$offset,$results,$tpname='');
		 $pager2=create_pager($from,$where,$offset,$results,$tpname='');

		 echo "$pager1 <form name=\"mycats\" id=\"mycats\" method=\"post\">
		 <p><input type=\"submit\" name=\"deletemultiplecategories\" class=\"button\" value=\"Delete Selected Categories\">
		 <input type=\"submit\" name=\"movemultiplecategories\" class=\"button\" value=\"Move Selected Categories\">
		 <select name=\"moveadstocategory\"><option value=\"0\">Select Move-To category</option>";
					$movetocategories=  get_categorynameid($cat_id = 0,$cat_parent_id= 0,$exclude);
					echo "$movetocategories</select></p>
		<p>If deleting categories
		<input type=\"radio\" name=\"movedeleteads\" value=\"1\" checked>Move Ads if any <input type=\"radio\" name=\"movedeleteads\" value=\"2\">Delete Ads if any</p>";

		 	$items=array();
		  $query="SELECT category_id,category_name,category_parent_id FROM $from WHERE $where ORDER BY category_name ASC LIMIT $offset,$results";
		  if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$thecategoryicon='';

				if( function_exists('get_category_icon') )
				{
					$category_icon=get_category_icon($rsrow[0]);
				}

				if( isset($category_icon) && !empty($category_icon) )
				{
					$caticonsurl="$imagesurl/caticons/$category_icon";
					$thecategoryicon="<img style=\"vertical-align:middle;margin-right:5px;\" src=\"$caticonsurl\" alt=\"$rsrow[1]\" border=\"0\">";
				}

				$thecategory_id=$rsrow[0];
				$thecategory_name="$thecategoryicon<a href=\"?page=Manage1&showadsfromcat_id=".$rsrow[0]."\">".$rsrow[1]."</a>";
				$thecategory_parent_id=$rsrow[2];

				$thecategory_parent_name=get_adparentcatname($thecategory_parent_id);
				$totaladsincat=total_ads_in_cat($thecategory_id);

				if($hascaticonsmodule == 1 )
				{
					if( is_installed_category_icon_module() )
					{
						$managecaticon="<a href=\"?page=Configure3&cat_ID=$thecategory_id&action=managecaticon&offset=$offset&results=$results\"><img src=\"$imagesurl/icon_manage_ico.png\" alt=\"Manage Category Icon\" border=\"0\"></a>";
					}
				}

				$items[]="<tr><td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\"><input type=\"checkbox\" name=\"category_to_delete_or_move[]\" value=\"$thecategory_id\">$thecategory_name ($totaladsincat)</td>
				<td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\">$thecategory_parent_name</td>
				<td style=\"padding:5px;border-bottom:1px dotted #dddddd;font-size:smaller;font-weight:normal;\"> <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=editcat&offset=$offset&results=$results\"><img src=\"$imagesurl/edit_ico.png\" alt=\"Edit Category\" border=\"0\"></a> <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=delcat&offset=$offset&results=$results\"><img src=\"$imagesurl/delete_ico.png\" alt=\"Delete Category\" border=\"0\"></a> $managecaticon</td></tr>";
			}

				$opentable="<table class=\"listcatsh\">
				<tr><td style=\"width:40%;padding:5px;\"><input type=\"checkbox\" onclick=\"CheckAll()\">Category Name (Total Ads)</td>
				<td style=\"width:40%;padding:5px;\">Parent</td>
				<td style=\"width:20%;padding:5px;;\">Action</td></tr>";
				$closetable="<tr><td style=\"width:40%;padding:5px;\">Category Name (Total Ads)</td>
				<td style=\"width:40%;padding:5px;\">Parent</td>
				<td style=\"width:20%;padding:5px;\">Action</td></tr>
				</table>";

				$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
				$showcategories.="$theitems";

		echo "
		<style>
		table.listcatsh { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
		table.listcatsh td { width:33%;font-size: 12px; border: none; background-color: #F4F4F4;
		vertical-align: middle; font-weight: bold; }
		table.listcatsh tr.special td { border-bottom: 1px solid #ff0000;  }
		table.listcatsc { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
		table.listcatsc td { width:33%;border: none;
		vertical-align: middle; padding: 5px; font-weight: normal; }
		table.listcatsc tr.special td { border-bottom: 1px solid #ff0000;  }
		</style>
		 $showcategories
		</form>$pager2</div>";

			 	if($hascaticonsmodule != 1 )
				{
					echo "<div class=\"fixfloat\"><p style=\"padding-top:25px;\">There is a premium module available that allows you to add icons to your categories. If you are interested in adding icons to your categories <a href=\"http://www.awpcp.com/premium-modules/\">Click here to find out about purchasing the Category Icons Module</a></p></div>";
				}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function awpcp_manage_viewimages(){
global $message;

echo "<div class=\"wrap\">
<h2>AWPCP Classifieds Management System: Manage Images</h2>
 $message <p style=\"padding:10px;border:1px solid#dddddd;\">Below you can manage the images users have uploaded. Your options are to delete images, and in the event you are operating with image approval turned on you can approve or disable images.</p>";

global $wpdb;
$table_name5 = $wpdb->prefix . "awpcp_adphotos";
$where='';

	if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])){
		$laction=$_REQUEST['action'];
	}

	if(empty($_REQUEST['action'])){
		if(isset($_REQUEST['a']) && !empty($_REQUEST['a'])){
			$laction=$_REQUEST['a'];
		}
	}

	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		$actonid=$_REQUEST['id'];
		$where="ad_id='$actonid'";
	}


	if($laction == 'approvepic'){

		if(isset($_REQUEST['kid']) && !empty($_REQUEST['kid'])){
			$keyids=$_REQUEST['kid'];
			list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
		}

		$query="UPDATE  ".$table_name5." SET disabled='0' WHERE ad_id='$adid' AND key_id='$picid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The image has been enabled and can now be viewed.</div>";

	}

	elseif($laction == 'rejectpic'){

		if(isset($_REQUEST['kid']) && !empty($_REQUEST['kid'])){
			$keyids=$_REQUEST['kid'];
			list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
		}

		$query="UPDATE  ".$table_name5." SET disabled='1' WHERE ad_id='$adid' AND key_id='$picid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The image has been disabled and can no longer be viewed.</div>";


	}

	elseif($laction == 'deletepic'){

		if(isset($_REQUEST['kid']) && !empty($_REQUEST['kid'])){
			$keyids=$_REQUEST['kid'];
			list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
		}

		$message=deletepic($picid,$adid,$adtermid,$adkey,$editemail);
		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";


	}

	viewimages($where);
}

function awpcp_manage_viewlistings(){
global $message;

echo "<div class=\"wrap\">
<h2>AWPCP Classifieds Management System: Manage Ad Listings</h2>
 $message";



global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])){
		$laction=$_REQUEST['action'];
	}

	if(empty($_REQUEST['action'])){
		if(isset($_REQUEST['a']) && !empty($_REQUEST['a'])){
			$laction=$_REQUEST['a'];
		}
	}

	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		$actonid=$_REQUEST['id'];
	}


	if($laction == 'deletead'){

		$message=deletead($actonid,$adkey='',$editemail='');
		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";

	}

	elseif($laction == 'editad')
	{
		$editemail=get_adposteremail($actonid);
		$adaccesskey=get_adkey($actonid);
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
		$offset=addslashes_mq($_REQUEST['offset']);
		$results=addslashes_mq($_REQUEST['results']);

		load_ad_post_form($actonid,$action='editad',$awpcppagename,$adtermid='',$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
	}

	elseif($laction == 'dopost1')
	{

		$adid=addslashes_mq($_REQUEST['adid']);
		$adterm_id=addslashes_mq($_REQUEST['adtermid']);
		$adkey=addslashes_mq($_REQUEST['adkey']);
		$editemail=addslashes_mq($_REQUEST['editemail']);
		$adtitle=addslashes_mq($_REQUEST['adtitle']);
		$adtitle=strip_html_tags($adtitle);
		$adcontact_name=addslashes_mq($_REQUEST['adcontact_name']);
		$adcontact_name=strip_html_tags($adcontact_name);
		$adcontact_phone=addslashes_mq($_REQUEST['adcontact_phone']);
		$adcontact_phone=strip_html_tags($adcontact_phone);
		$adcontact_email=addslashes_mq($_REQUEST['adcontact_email']);
		$adcategory=addslashes_mq($_REQUEST['adcategory']);
		$adcontact_city=addslashes_mq($_REQUEST['adcontact_city']);
		$adcontact_city=strip_html_tags($adcontact_city);
		$adcontact_state=addslashes_mq($_REQUEST['adcontact_state']);
		$adcontact_state=strip_html_tags($adcontact_state);
		$adcontact_country=addslashes_mq($_REQUEST['adcontact_country']);
		$adcontact_country=strip_html_tags($adcontact_country);
		$ad_county_village=addslashes_mq($_REQUEST['adcontact_countyvillage']);
		$ad_county_village=strip_html_tags($ad_county_village);
		$ad_item_price=addslashes_mq($_REQUEST['ad_item_price']);
		$ad_item_price=str_replace(",", '', $ad_item_price);
		$addetails=addslashes_mq($_REQUEST['addetails']);
		$websiteurl=addslashes_mq($_REQUEST['websiteurl']);
		$checkhuman=addslashes_mq($_REQUEST['checkhuman']);
		$numval1=addslashes_mq($_REQUEST['numval1']);
		$numval2=addslashes_mq($_REQUEST['numval2']);


		if(get_awpcp_option('allowhtmlinadtext') == 0){
		$addetails=strip_html_tags($addetails);
		}
		$adpaymethod=addslashes_mq($_REQUEST['adpaymethod']);
		if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
		$adaction=addslashes_mq($_REQUEST['adaction']);} else {$adaction='';}
		$awpcppagename=addslashes_mq($_REQUEST['awpcppagename']);
		$offset=addslashes_mq($_REQUEST['offset']);
		$results=addslashes_mq($_REQUEST['results']);

		processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);

	}

	elseif($laction == 'approvead'){

		$query="UPDATE  ".$table_name3." SET disabled='0' WHERE ad_id='$actonid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The ad has been approved</div>";

	}

	elseif($laction == 'rejectad'){

		$query="UPDATE  ".$table_name3." SET disabled='1' WHERE ad_id='$actonid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The ad has been disabled</div>";


	}

		elseif($laction == 'cps'){
			if(isset($_REQUEST['changeto']) && !empty($_REQUEST['changeto'])){
				$changeto=$_REQUEST['changeto'];
			}

		$query="UPDATE  ".$table_name3." SET payment_status='$changeto', disabled='0' WHERE ad_id='$actonid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The ad has been approved</div>";

	}

	elseif($laction == 'viewad')
	{

		if(isset($actonid) && !empty($actonid))
		{

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";
			$table_name5 = $wpdb->prefix . "awpcp_adphotos";

			$awpcppage=get_currentpagename();
			$awpcppagename = sanitize_title($awpcppage, $post_ID='');
			$quers=setup_url_structure($awpcppagename);

			if(get_awpcp_option('useadsense') == 1)
			{
				$adsensecode=get_awpcp_option('adsense');
				$showadsense="<p class=\"cl-adsense\">$adsensecode</p>";
			}
			else
			{
				$showadsense='';
			}

			$query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details from ".$table_name3." WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

				while ($rsrow=mysql_fetch_row($res))
				{
					list($ad_title,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails)=$rsrow;
				}

			////////////////////////////////////////////////////////////////////////////////////
			// Step:2 Show a sample of how the ad is going to look
			////////////////////////////////////////////////////////////////////////////////////

			if(!isset($adcontact_name) || empty($adcontact_name))
			{
				$adcontact_name="";
			}
			if(!isset($adcontact_phone) || empty($adcontact_phone))
			{
				$adcontactphone="";
			}
			else
			{
				$adcontactphone="Phone: $adcontact_phone";
			}


			if( empty($adcontact_city) && empty($adcontact_state) && empty($adcontact_country) && empty($ad_county_village))
			{
				$location="";
			}
			else
			{
				$location="Location: ";

				if( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
				{
					//city is set, state is set, country is set
					$location.="$adcontact_city, $adcontact_state $adcontact_country";
				}
				elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( !isset($adcontact_country) || empty($adcontact_country) ) )
				{
					//city is set, state is set, country is missing
					$location.="$adcontact_city, $adcontact_state";
				}
				elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && (!isset($adcontact_state) || empty($adcontact_state)) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
				{
					// city is set, state is missing and country is set
					$location.="$adcontact_city, $adcontact_country";
				}
				elseif( (!isset($adcontact_city) || empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
				{
					//city is missing but state is set and country is set
					$location.="$adcontact_state, $adcontact_country";
				}
				elseif( (isset($adcontact_city) && !empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) && (!isset($adcontact_country) || empty($adcontact_country)) )
				{
					//  city is set, state is missing, country is missing
					$location.="$adcontact_city";
				}
				elseif( (isset($adcontact_state) && !empty($adcontact_state)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_country) || empty($adcontact_country)) )
				{
					//  state is set, city is missing, country is missing
					$location.="$adcontact_state";
				}
				elseif( (isset($adcontact_country) && !empty($adcontact_country)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) )
				{
					//  country is set, city is missing, state is missing
					$location.="$adcontact_state";
				}
				else
				{
					if(isset($adcontact_city) && !empty($adcontact_city))
					{
						$location.=" $adcontact_city";
					}
					if(isset($adcontact_state) && !empty($adcontact_state))
					{
						$location.=" $adcontact_state";
					}
					if(isset($adcontact_country) && !empty($adcontact_country))
					{
						$location.=" $adcontact_country";
					}
				}
			}


			$modtitle=cleanstring($ad_title);
			$modtitle=add_dashes($modtitle);

			if( get_awpcp_option('seofriendlyurls') )
			{
				if( isset($permastruc) && !empty($permastruc) )
				{
					$codecontact="contact/$actonid/$modtitle";
				}
				else
				{
					$codecontact="contact&i=$actonid";
				}
			}
			else
			{
				$codecontact="contact&i=$actonid";
			}

					$aditemprice='';

					if( get_awpcp_option('displaypricefield') == 1 )
					{
						if( !empty($ad_item_price) )
						{
							$itempricereconverted=($ad_item_price/100);
							if($itempricereconverted >=1 )
							{
								$awpcpthecurrencysymbol=awpcp_get_currency_code();
								$aditemprice="<span style=\"padding-left:100px;\"><b>Price:</b> <b style=\"color:#ff0000;\">$awpcpthecurrencysymbol $itempricereconverted</b></span>";
							}
						}
					}

			echo "<div id=\"showad\"><div class=\"adtitle\">$ad_title $aditemprice</div><div class=\"adbyline\"><a href=\"".$quers."$codecontact\">Contact $adcontact_name</a> $adcontactphone $location</div>";


			if(get_awpcp_option('adsenseposition') == 1 ){
				echo "$showadsense";
			}

			if(get_awpcp_option('hyperlinkurlsinadtext')){
				$addetails=preg_replace("/(http:\/\/[^\s]+)/","<a href=\"\$1\">\$1</a>",$addetails);
			}

			$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "<br /><br />", $addetails);



			echo "<p class=\"addetails\">$addetails</p>";

			if(get_awpcp_option('adsenseposition') == 2)
			{
				echo "$showadsense";
			}

			echo "</div><div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul style=\"list-style:none;float:left;\">";

			$theimage='';

			if(get_awpcp_option('imagesallowdisallow') == 1)
			{

				$totalimagesuploaded=get_total_imagesuploaded($actonid);

				if($totalimagesuploaded >=1)
				{

					$query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$actonid' AND disabled='0' ORDER BY image_name ASC";
					if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						while ($rsrow=mysql_fetch_row($res))
						{
							list($image_name)=$rsrow;

							echo "<li style=\"list-style:none;float:left;padding-right:10px;\"><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a></li>";

						}

				}

			}

			echo "</ul></div><div class=\"fixfloat\"></div>";

			if(get_awpcp_option('adsenseposition') == 3)
			{
				echo "$showadsense";
			}
			echo "</div>";

				// start insert delete | edit | approve/disable admin links

				$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
				$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);

					$deletelink=  "<a href=\"?page=Manage1&action=deletead&id=$actonid&offset=$offset&results=$results\">Delete</a>";
					$editlink=" |  <a href=\"?page=Manage1&action=editad&id=$actonid&offset=$offset&results=$results\">Edit</a>";


					echo"$deletelink $editlink";


				if(get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1)
				{

					$adstatusdisabled=check_if_ad_is_disabled($actonid);

					if($adstatusdisabled)
					{
						$approvelink=" | <a href=\"?page=Manage1&action=approvead&id=$actonid&offset=$offset&results=$results\">Approve</a> ";
					}
					else
					{
						$approvelink=" | <a href=\"?page=Manage1&action=rejectad&id=$actonid&offset=$offset&results=$results\">Disable </a> ";
					}

					echo"$approvelink";
				}

					echo "</div>";

				// end insert delete | edit | approve/disable admin links

			echo "<p style=\"margin-bottom:25px;\"></p></div>";
		}
		else
		{
			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">No ad ID was supplied</div>";

		}

	}

	elseif($laction == 'viewimages'){
		if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
			$picid=$_REQUEST['id'];
			$where="ad_id='$picid'";
		}
		else {
			$where='';
		}

		viewimages($where);
	}

	elseif($laction == 'lookupadby')
	{

		if(isset($_REQUEST['lookupadbychoices']) && !empty($_REQUEST['lookupadbychoices']))
		{
			$lookupadbytype=$_REQUEST['lookupadbychoices'];
		}
		else
		{
			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">You need to check whether you want to look up the ad by title or by id</div>";
		}
		if(isset($_REQUEST['lookupadidortitle']) && !empty($_REQUEST['lookupadidortitle']))
		{
			$lookupadbytypevalue=$_REQUEST['lookupadidortitle'];
		}
		else
		{
			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">You need enter either an ad title or an ad id to look up</div>";
		}
		if($lookupadbytype == 'adid')
		{
			if(!is_numeric($lookupadbytypevalue))
			{
				echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">You indicated you wanted to look up the ad by ID but you entered an invalid ID. Please try again</div>";
			}
			else
			{
				$where="ad_id='$lookupadbytypevalue'";
			}
		}
		elseif($lookupadbytype == 'adtitle')
		{
			$where="ad_title='$lookupadbytypevalue'";
		}
		elseif($lookupadbytype == 'titdet')
		{
			$where="MATCH (ad_title,ad_details) AGAINST (\"$lookupadbytypevalue\")";
		}
	}
		if(isset($_REQUEST['showadsfromcat_id']) && !empty($_REQUEST['showadsfromcat_id'])){
			$thecat_id=$_REQUEST['showadsfromcat_id'];
			$where="ad_title <> '' AND (ad_category_id='$thecat_id' OR ad_category_parent_id='$thecat_id')";
		}



		$from="$table_name3";
		if(!isset($where) || empty($where)){
			$where="ad_title <> ''";
		}

			if(!ads_exist()){
				$showadstomanage="<p style=\"padding:10px\">There are currently no ads in the system</p>";
				$pager1='';
				$pager2='';
			}



			else {

					$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
					$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);


if(isset($_REQUEST['sortby']))
{
	$sortby=$_REQUEST['sortby'];
	if($sortby == 'titleza')
	{
		$orderby="ad_title DESC";
	}
	elseif($sortby == 'titleaz')
	{
		$orderby="ad_title ASC";
	}
	elseif($sortby == 'awaitingapproval')
	{
		$orderby="disabled DESC, ad_key DESC";
	}
	elseif($sortby == 'paidfirst')
	{
		$orderby="payment_status DESC, ad_key DESC";
	}
}

if(!isset($sortby) || empty($sortby))
{
	$orderby="ad_key DESC";
}

					$items=array();
					$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_details,ad_postdate,disabled,payment_status FROM $from WHERE $where ORDER BY $orderby LIMIT $offset,$results";
					if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					while ($rsrow=mysql_fetch_row($res)) {
						$ad_id=$rsrow[0];
						$awpcppage=get_currentpagename();
						$awpcppagename = sanitize_title($awpcppage, $post_ID='');
						$modtitle=cleanstring($rsrow[2]);
						$modtitle=add_dashes($modtitle);
						$tcname=get_adcatname($rsrow[1]);
						$modcatname=cleanstring($tcname);
						$modcatname=add_dashes($modcatname);
						$category_id=$rsrow[1];
						$category_name=get_adcatname($category_id);
						$disabled=$rsrow[11];
						$paymentstatus=$rsrow[12];

						if(!isset($paymentstatus) || empty($paymentstatus)){
							$paymentstatus="N/A";
						}



 							$pager1="<p>".create_pager($from,$where,$offset,$results,$tpname='')."</p>";
							$pager2="<p>".create_pager($from,$where,$offset,$results,$tpname='')."</p>";
							$base=get_option(siteurl);
							$awpcppage=get_currentpagename();
							$awpcppagename = sanitize_title($awpcppage, $post_ID='');
							$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

								$ad_title="<input type=\"checkbox\" name=\"awpcp_ad_to_delete[]\" value=\"$ad_id\"><a href=\"?page=Manage1&action=viewad&id=$ad_id&offset=$offset&results=$results\">".$rsrow[2]."</a>";
								$handlelink="<a href=\"?page=Manage1&action=deletead&id=$ad_id&offset=$offset&results=$results\">Delete</a> | <a href=\"?page=Manage1&action=editad&id=$ad_id&offset=$offset&results=$results\">Edit</a>";

								$approvelink='';
								if(get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1){

									if($disabled == 1){
										$approvelink="<a href=\"?page=Manage1&action=approvead&id=$ad_id&offset=$offset&results=$results\">Approve</a> | ";
									}
									else {
										$approvelink="<a href=\"?page=Manage1&action=rejectad&id=$ad_id&offset=$offset&results=$results\">Disable </a> | ";
									}
								}


							if(get_awpcp_option('freepay') == 1){

							$paymentstatushead="<th>Payment Status</th>";

							$changepaystatlink='';

								if($paymentstatus == 'Pending'){
									$changepaystatlink="<a href=\"?page=Manage1&action=cps&id=$ad_id&changeto=Completed&sortby=$sortby\">Complete</a>";
								}

									$paymentstatus="<td> $paymentstatus <SUP>$changepaystatlink</SUP></td>";



							}
							else {
								$paymentstatushead="";
								$paymentstatus="";
							}

							if(get_awpcp_option(imagesallowdisallow) == 1){

								$imagesnotehead="<th>Total Images</th>";

								$totalimagesuploaded=get_total_imagesuploaded($ad_id);

									if($totalimagesuploaded >= 1){
										$viewimages="[ $totalimagesuploaded ] <a href=\"?page=Manage1&action=viewimages&id=$ad_id&sortby=$sortby\">View</a>";
									}
									else {
										$viewimages="No Images";
									}

								$imagesnote="<td> $viewimages</td>";
							}
							else {$imagesnotehead="";$imagesnote="";}



							$items[]="<tr><td class=\"displayadscell\" width=\"200\">$ad_title</td><td> $approvelink $handlelink</td>$paymentstatus $imagesnote</tr>";


							$opentable="<table class=\"widefat fixed\"><thead><tr><th><input type=\"checkbox\" onclick=\"CheckAllAds()\"> Ad Headline</th><th>Manage Ad</th>$paymentstatushead $imagesnotehead</tr></thead>";
							$closetable="</table>";


							$theadlistitems=smart_table($items,intval($results/$results),$opentable,$closetable);
							$showadstomanage="$theadlistitems";
							$showadstomanagedeletemultiplesubmitbutton="<p><input type=\"submit\" name=\"deletemultipleads\" class=\"button\" value=\"Delete Checked Ads\"></p>";

					}
					if(!isset($ad_id) || empty($ad_id) || $ad_id == '0' ){
							$showadstomanage="<p style=\"padding:20px;\">There were no ads found</p>";
							$showadstomanagedeletemultiplesubmitbutton="";
							$pager1='';
							$pager2='';
					}
			}

		echo "
		<style>
		table.listcatsh { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
		table.listcatsh td { width:20%;font-size: 12px; border: none; background-color: #F4F4F4;
		vertical-align: middle; font-weight: normal; }
		table.listcatsh tr.special td { border-bottom: 1px solid #ff0000;  }
		table.listcatsc { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
		table.listcatsc td { width:20%;border: none;
		vertical-align: middle; padding: 5px; font-weight: normal; }
		table.listcatsc tr.special td { border-bottom: 1px solid #ff0000;  }
		.sortadsby { margin:5px 0px 5px 0px;}
		.sortadsby a { 	text-decoration:none; }
		.sortadsby a:hover { text-decoration:underline;	}
		</style>
		$pager1";
		echo "<div class=\"sortadsby\">Sort Ads By: ";

		if(!isset($sortby) || empty ($sortby))
		{
			$sortby="mostrecent";
		}

		if($sortby == 'mostrecent')
		{
			echo "<b>Most Recent</b>";
		}
		else
		{
			echo "<a href=\"?page=Manage1&sortby=mostrecent\">Most Recent</a>";
		}
		if($sortby == 'titleza')
		{
			echo " | <b> Title Z-A </b>";
		}
		else
		{
			echo " | <a href=\"?page=Manage1&sortby=titleza\">Title Z-A</a>";
		}
		if($sortby == 'titleaz')
		{
			echo " | <b> Title A-Z </b>";
		}
		else
		{
			echo " | <a href=\"?page=Manage1&sortby=titleaz\">Title A-Z</a>";
		}
		if(get_awpcp_option('adapprove') == 1)
		{
			if($sortby == 'awaitingapproval')
			{
				echo " | <b>Awaiting Approval</b>";
			}
			else
			{
				echo " | <a href=\"?page=Manage1&sortby=awaitingapproval\">Awaiting Approval</a>";
			}
		}
		if(get_awpcp_option('freepay') == 1)
		{
			if($sortby == 'paidfirst')
			{
				echo " | <b>Paid Ads First</b>";
			}
			else
			{
				echo " | <a href=\"?page=Manage1&sortby=paidfirst\">Paid Ads First</a>";
			}

		}
		echo "</div>
		<b>Look Up Ad By</b><br/>
		<form method=\"post\"
		<p>
		<input type=\"radio\" name=\"lookupadbychoices\" value=\"adid\"/>Ad ID
		<input type=\"radio\" name=\"lookupadbychoices\" value=\"adtitle\"/>Ad Title
		<input type=\"radio\" name=\"lookupadbychoices\" value=\"titdet\"/>Key Word
		</p>
		<p><input type=\"text\" name=\"lookupadidortitle\" value=\"$lookupadidortitle\"/></p>
		<input type=\"hidden\" name=\"action\" value=\"lookupadby\">
		<input type=\"submit\" class=\"button\" value=\"Look Up Ad\">
		</form>
		<form name=\"manageads\" id=\"manageads\" method=\"post\">
		 $showadstomanage
		$showadstomanagedeletemultiplesubmitbutton
		</form>
		$pager2";


	echo "</div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display images for admin view
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function viewimages($where){

global $wpdb;
$table_name5 = $wpdb->prefix . "awpcp_adphotos";

$from="$table_name5";

		if(!isset($where) || empty($where)){
			$where="image_name <> ''";
		}




			if(!images_exist()){

				$imagesallowedstatus='';

				if(get_awpcp_option('imagesallowdisallow') == 0){
					$imagesallowedstatus="You are not currently allowing users to upload images with their ad. To allow users to upload images please <a href=\"?page=Configure1\">change the related setting in your general options configuration</a>.";
				}

				$showimages="<p style=\"padding:10px\">There are currently no images in the system. $imagesallowedstatus</p>";
				$pager1='';
				$pager2='';
			}



			else {

					$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
					$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);

					$items=array();
					$query="SELECT key_id,ad_id,image_name,disabled FROM $from WHERE $where ORDER BY image_name DESC LIMIT $offset,$results";
					if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					while ($rsrow=mysql_fetch_row($res)) {
						list($ikey,$adid,$image_name,$disabled)=$rsrow;
						$adtermid=get_adterm_id($adid);
						$editemail=get_adposteremail($adid);
						$adkey=get_adkey($adid);

						$ikey.="_";
						$ikey.="$adid";
						$ikey.="_";
						$ikey.="$adtermid";
						$ikey.="_";
						$ikey.="$adkey";
						$ikey.="_";
						$ikey.="$editemail";



						$dellink="<a href=\"?page=Manage2&action=deletepic&kid=$ikey&id=$adid&offset=$offset&results=$results\">Delete</a>";

						$transval='';
						if($disabled == 1){
							$transval="style=\"-moz-opacity:.20; filter:alpha(opacity=20); opacity:.20;\"";
						}

						$approvelink='';
						if(get_awpcp_option('imagesapprove') == 1){

							if($disabled == 1){
								$approvelink=" | <a href=\"?page=Manage2&action=approvepic&kid=$ikey&id=$adid&offset=$offset&results=$results\">Approve</a>";
							}
							else {
								$approvelink=" | <a href=\"?page=Manage2&action=rejectpic&kid=$ikey&id=$adid&offset=$offset&results=$results\">Disable</a>";
							}
						}

						$theimages="<a href=\"".AWPCPUPLOADURL."/$image_name\"><img $transval src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a><br/>$dellink $approvelink";


 							$pager1=create_pager($from,$where,$offset,$results,$tpname='');
							$pager2=create_pager($from,$where,$offset,$results,$tpname='');




							$items[]="<td class=\"displayadsicell\">$theimages</td>";


							$opentable="<table class=\"listcatsh\"><tr>";
							$closetable="</tr></table>";


							$theitems=smart_table($items,intval($results/2),$opentable,$closetable);
							$showcategories="$theitems";
					}
					if(!isset($ikey) || empty($ikey) || $ikey == '0'){
							$showcategories="<p style=\"padding:20px;\">There were no images found</p>";
							$pager1='';
							$pager2='';
					}
			}

		echo "
		<style>
		table.listcatsh { width: 100%; padding: 0px; border: none;}
		table.listcatsh td { text-align:center;width:10%;font-size: 12px; border: none; background-color: #F4F4F4;
		vertical-align: middle; font-weight: normal; }
		table.listcatsh tr.special td { border-bottom: 1px solid #ff0000;  }
		table.listcatsc { width: 100%; padding: 0px; border: none; border: 1px solid #dddddd;}
		table.listcatsc td { text-align:center;width:10%;border: none;
		vertical-align: middle; padding: 5px; font-weight: normal; }
		table.listcatsc tr.special td { border-bottom: 1px solid #ff0000;  }
		</style>
		$pager1
		 $showcategories
		 $pager2";


	echo "</div>";
	die;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	Begin processor actions
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start process of saving configuration options
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_REQUEST['savesettings']) && !empty($_REQUEST['savesettings'])){

global $wpdb;
$table_name4 = $wpdb->prefix . "awpcp_adsettings";
$currentuipagename=get_currentpagename();
$error=false;

	$query="SELECT config_option,option_type FROM ".$table_name4."";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	$myoptions=array();
	for ($i=0;$i<mysql_num_rows($res);$i++) {
		list($config_option,$option_type)=mysql_fetch_row($res);
		if (isset($_POST[$config_option])) {

			$myoptions[$config_option]=addslashes_mq($_POST[$config_option],true);
			$newuipagename=$myoptions['userpagename'];

			if( !empty($myoptions['smtppassword']) )
			{
				$myoptions['smtppassword']=md5($myoptions['smtppassword']);
			}
			else
			{
				$myoptions['smtppassword']=get_awpcp_option('smtppassword');
			}


		} else {
			if ($option_type==0) {
				$myoptions[$config_option]=0;
			} elseif ($option_type==1) {
				$myoptions[$config_option]='';
			}elseif ($option_type==2) {
				$myoptions[$config_option]='';
			}
		}
	}

	while (list($k,$v)=each($myoptions)) {

	$mycurrencycode=$myoptions['paypalcurrencycode'];
	$currencycodeslist=array('AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK','DKK','PLN','NOK','HUF','CZK','ILS','MXN');


			if (!in_array($mycurrencycode,$currencycodeslist)) {

				$error=true;
				$message="<div style=\"background-color:#eeeeee;border:1px solid #ff0000;padding:5px;\" id=\"message\" class=\"updated fade\">";
				$message.= "There is a problem with the currency code you have entered [$mycurrencycode]. It does not match any of the codes in the list of available currencies provided by PayPal. ";
				$message.= "The available currency codes are:<br/>";

					for ($i=0;isset($currencycodeslist[$i]);++$i) {
					$message.="	$currencycodeslist[$i] | ";
					}

				$message.="</div>";

			}


		if(!$error){

		$query="UPDATE ".$table_name4." SET config_value='$v' WHERE config_option='$k'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


		// Create the classified user page if it does not exist
			if(empty($currentuipagename))
			{
				maketheclassifiedpage($newuipagename);
			}
			elseif(isset($currentuipagename) && !empty($currentuipagename))
			{

				if(findpage($currentuipagename))
				{
					if($currentuipagename != '$newuipagename')
					{
						deleteuserpageentry($currentuipagename);
						updatetheclassifiedpagename($currentuipagename,$newuipagename);
					}

				}
				elseif(!(findpage($currentuipagename)))
				{
					deleteuserpageentry($currentuipagename);
					maketheclassifiedpage($newuipagename);
				}

			}

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The data has been updated!</div>";

		}

	}

	global $message;
}


		///////////////////////////////////////////////////////////////////////
		//	Start process of creating | updating  userside classified page
		//////////////////////////////////////////////////////////////////////

function maketheclassifiedpage($newuipagename)
{

	add_action('init', 'flush_rewrite_rules');
	global $wpdb,$table_prefix,$wp_rewrite;
	$table_name6 = $wpdb->prefix . "awpcp_pagename";
	$pdate = date("Y-m-d");

	// First delete any pages already existing with the title and post name of the new page to be created
	checkfortotalpageswithawpcpname($newuipagename);


		$post_name = sanitize_title($newuipagename, $post_ID='');

		$query="INSERT INTO {$table_prefix}posts SET post_author='1', post_date='$pdate', post_date_gmt='$pdate', post_content='[[AWPCPCLASSIFIEDSUI]]', post_title='$newuipagename', post_excerpt='', post_status='publish', comment_status='closed', post_name='$post_name', to_ping='', pinged='', post_modified='$pdate', post_modified_gmt='$pdate', post_content_filtered='[[AWPCPCLASSIFIEDSUI]]', post_parent='0', guid='', post_type='page', menu_order='0'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		$awpcpwppostpageid=mysql_insert_id();
		$guid = get_option('home') . "/?page_id=$awpcpwppostpageid";

		$query="UPDATE {$table_prefix}posts set guid='$guid' WHERE post_title='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$query="INSERT INTO ".$table_name6." SET userpagename='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
}

function updatetheclassifiedpagename($currentuipagename,$newuipagename)
{
	global $wpdb,$table_prefix, $wp_rewrite;
	$table_name6 = $wpdb->prefix . "awpcp_pagename";

	$post_name = sanitize_title($newuipagename, $post_ID='');

		$query="UPDATE {$table_prefix}posts set post_title='$newuipagename', post_name='$post_name' WHERE post_title='$currentuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$query="INSERT INTO ".$table_name6." SET userpagename='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
}




////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start process of updating|deleting|adding new listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////
	// Handle adding a listing fee plan
	/////////////////////////////////////////////////

if(isset($_REQUEST['addnewfeesetting']) && !empty($_REQUEST['addnewfeesetting'])){

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

	$adterm_name=addslashes_mq($_REQUEST['adterm_name']);
	$amount=addslashes_mq($_REQUEST['amount']);

	$rec_period=addslashes_mq($_REQUEST['rec_period']);
	$rec_increment=addslashes_mq($_REQUEST['rec_increment']);
	$imagesallowed=addslashes_mq($_REQUEST['imagesallowed']);
	$query="INSERT INTO ".$table_name2." SET adterm_name='$adterm_name',amount='$amount',recurring=1,rec_period='$rec_period',rec_increment='$rec_increment',imagesallowed='$imagesallowed'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The item has been added!</div>";
	global $message;
}


	//////////////////////////////////////////////////
	// Handle updating of a listing fee plan
	/////////////////////////////////////////////////

if(isset($_REQUEST['savefeesetting']) && !empty($_REQUEST['savefeesetting'])){

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

	$adterm_id=addslashes_mq($_REQUEST['adterm_id']);
	$adterm_name=addslashes_mq($_REQUEST['adterm_name']);
	$amount=addslashes_mq($_REQUEST['amount']);
	$rec_period=addslashes_mq($_REQUEST['rec_period']);
	$rec_increment=addslashes_mq($_REQUEST['rec_increment']);
	$imagesallowed=addslashes_mq($_REQUEST['imagesallowed']);
	$ad_word_length=addslashes_mq($_REQUEST['ad_word_length']);
	$query="UPDATE ".$table_name2." SET adterm_name='$adterm_name',amount='$amount',recurring=1,rec_period='$rec_period',rec_increment='$rec_increment', imagesallowed='$imagesallowed' WHERE adterm_id='$adterm_id'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The item has been updated!</div>";
	global $message;
}

	//////////////////////////////////////////////////
	// Handle deleting of a listing fee plan
	/////////////////////////////////////////////////

if(isset($_REQUEST['deletefeesetting']) && !empty($_REQUEST['deletefeesetting'])){

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

		$adterm_id=addslashes_mq($_REQUEST['adterm_id']);
		$query="DELETE FROM  ".$table_name2." WHERE adterm_id='$adterm_id'";
		if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The data has been deleted!</div>";
	global $message;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start process of adding | editing ad categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_REQUEST['createeditadcategory']) && !empty($_REQUEST['createeditadcategory'])){

		global $wpdb;
		$table_name1 = $wpdb->prefix . "awpcp_categories";
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$category_id=addslashes_mq($_REQUEST['category_id']);
		$category_name=addslashes_mq($_REQUEST['category_name']);
		$category_parent_id=addslashes_mq($_REQUEST['category_parent_id']);
		$aeaction=addslashes_mq($_REQUEST['aeaction']);
		$movetocat=addslashes_mq($_REQUEST['movetocat']);
		$deletetheads=$_REQUEST['deletetheads'];



			if($aeaction == 'newcategory')
			{
					$query="INSERT INTO ".$table_name1." SET category_name='$category_name',category_parent_id='$category_parent_id'";
					@mysql_query($query);
					$themessagetoprint="The new category has sucessfully added.";

			}

			elseif($aeaction == 'delete')
			{


				// Make sure this is not the default category. If it is the default category alert that the default category can only be renamed not deleted
				if($category_id == 1)
				{
					$themessagetoprint="Sorry but you cannot delete the default category. The default category can only be renamed";
				}

				else
				{
					//Proceed with the delete instructions

					// Move any ads that the category contains if move-to category value is set and does not equal zero

					if( isset($movetocat) && !empty($movetocat) && ($movetocat != 0) )
					{

						$movetocatparent=get_cat_parent_ID($movetocat);

						$query="UPDATE ".$table_name3." SET ad_category_id='$movetocat' ad_category_parent_id='$movetocatparent' WHERE ad_category_id='$category_id'";
						@mysql_query($query);

						// Must also relocate ads where the main category was a child of the category being deleted
						$query="UPDATE ".$table_name3." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$category_id'";
						@mysql_query($query);

						// Must also relocate any children categories to the the move-to-cat
						$query="UPDATE ".$table_name1." SET category_parent_id='$movetocat' WHERE category_parent_id='$category_id'";
						@mysql_query($query);

					}


					// Else if the move-to value is zero move the ads to the parent category if category is a child or the default category if
					// category is not a child

					elseif( !isset($movetocat) || empty($movetocat) || ($movetocat == 0) )
					{

						// If the category has a parent move the ads to the parent otherwise move the ads to the default

						if( category_is_child($category_id) )
						{

							$movetocat=get_cat_parent_ID($category_id);
						}
						else
						{
							$movetocat=1;
						}

						$movetocatparent=get_cat_parent_ID($movetocat);



							// Adjust any ads transferred from the main category
							$query="UPDATE ".$table_name3." SET ad_category_id='$movetocat', ad_category_parent_id='$movetocatparent' WHERE ad_category_id='$category_id'";
							@mysql_query($query);

							// Must also relocate any children categories to the the move-to-cat
							$query="UPDATE ".$table_name1." SET category_parent_id='$movetocat' WHERE category_parent_id='$category_id'";
							@mysql_query($query);

							// Adjust  any ads transferred from children categories
							$query="UPDATE ".$table_name3." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$category_id'";
							if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


					}

					$query="DELETE FROM  ".$table_name1." WHERE category_id='$category_id'";
					@mysql_query($query);
					$themessagetoprint="The category has been deleted";

				}

			}

			elseif($aeaction == 'edit')
			{
					$query="UPDATE ".$table_name1." SET category_name='$category_name',category_parent_id='$category_parent_id' WHERE category_id='$category_id'";
					@mysql_query($query);

					$query="UPDATE ".$table_name3." SET ad_category_parent_id='$category_parent_id' WHERE ad_category_id='$category_id'";
					@mysql_query($query);

					$themessagetoprint="The category edit has been sucessfully completed";

			}


			$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
			$clearform=1;



}

// Move multiple categories

if( isset($_REQUEST['movemultiplecategories']) && !empty($_REQUEST['movemultiplecategories']) )
{



	global $wpdb;
	$table_name1 = $wpdb->prefix . "awpcp_categories";
	$table_name3 = $wpdb->prefix . "awpcp_ads";

	// First get the array of categories to be deleted
	$categoriestomove=addslashes_mq($_REQUEST['category_to_delete_or_move']);

	// Next get the value for where the admin wants to move the ads
	if( isset($_REQUEST['moveadstocategory']) && !empty($_REQUEST['moveadstocategory'])  && ($_REQUEST['moveadstocategory'] != 0) )
	{
		$moveadstocategory=addslashes_mq($_REQUEST['moveadstocategory']);

		// Next loop through the categories and move them to the new category

		foreach($categoriestomove as $cattomove){

			if($cattomove != $moveadstocategory)
			{

				// First update all the ads in the category to take on the new parent ID
				$query="UPDATE ".$table_name3." SET ad_category_parent_id='$moveadstocategory' WHERE ad_category_id='$cattomove'";
				@mysql_query($query);

				$query="UPDATE ".$table_name1." SET category_parent_id='$moveadstocategory' WHERE category_id='$cattomove'";
				@mysql_query($query);
			}

		}

		$themessagetoprint="With the exception of any category that was being moved to itself, the categories have been moved.";


	}
	else
	{
		$themessagetoprint="The categories have not been moved because you did not indicate where you want the categories to be moved to.";
	}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
}

// Delete multiple categories

if( isset($_REQUEST['deletemultiplecategories']) && !empty($_REQUEST['deletemultiplecategories']) )
{


global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";
$table_name3 = $wpdb->prefix . "awpcp_ads";

	// First get the array of categories to be deleted
	$categoriestodelete=addslashes_mq($_REQUEST['category_to_delete_or_move']);


	// Next get the value of move/delete ads
	if( isset($_REQUEST['movedeleteads']) && !empty($_REQUEST['movedeleteads']) )
	{
		$movedeleteads=addslashes_mq($_REQUEST['movedeleteads']);
	}
	else
	{
		$movedeleteads=1;
	}

	// Next get the value for where the admin wants to move the ads
	if( isset($_REQUEST['moveadstocategory']) && !empty($_REQUEST['moveadstocategory'])  && ($_REQUEST['moveadstocategory'] != 0) )
	{
		$moveadstocategory=addslashes_mq($_REQUEST['moveadstocategory']);
	}
	else
	{
		$moveadstocategory=1;
	}


	// Next make sure there is a default category with an ID of 1 because any ads that exist in the
	// categories will need to be moved to a default category if admin has checked move ads but
	// has not selected a move to category


				if( ($moveadstocategory == 1) && (!(defaultcatexists($defid='1'))) )
				{
					createdefaultcategory($idtomake='1',$titletocallit='Untitled');
				}

	// Next loop through the categories and move all their ads


	foreach($categoriestodelete as $cattodel){


		// Make sure this is not the default category which cannot be deleted
		if($cattodel != 1)
		{

			// If admin has instructed moving ads move the ads
			if($movedeleteads == 1)
			{

				// Now move the ads if any
				$movetocat=$moveadstocategory;
				$movetocatparent=get_cat_parent_ID($movetocat);


				// Move the ads in the category main
					$query="UPDATE ".$table_name3." SET ad_category_id='$movetocat',ad_category_parent_id='$movetocatparent' WHERE ad_category_id='$cattodel'";
					@mysql_query($query);

				// Must also relocate ads where the main category was a child of the category being deleted
					$query="UPDATE ".$table_name3." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$cattodel'";
					@mysql_query($query);

				// Must also relocate any children categories that do not exist in the categories to delete loop to the the move-to-cat
					$query="UPDATE ".$table_name1." SET category_parent_id='$movetocat' WHERE category_parent_id='$cattodel' AND category_id !IN '$categoriestodelete";
					@mysql_query($query);
			}
			elseif($movedeleteads == 2)
			{

				$movetocat=$moveadstocategory;

					// If the category has children move the ads in the child categories to the default category

					if( category_has_children($cattodel) )
					{

						//  Relocate the ads ads in any children categories of the category being deleted

						$query="UPDATE ".$table_name3." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$cattodel'";
						@mysql_query($query);

						// Relocate any children categories that exist under the category being deleted
						$query="UPDATE ".$table_name1." SET category_parent_id='$movetocat' WHERE category_parent_id='$cattodel'";
						@mysql_query($query);
					}


						// Now delete the ads because the admin has checked Delete ads if any
						massdeleteadsfromcategory($cattodel);
			}

					// Now delete the categories
					$query="DELETE FROM  ".$table_name1." WHERE category_id='$cattodel'";
					@mysql_query($query);
					$themessagetoprint="The categories have been deleted";

		}

	}



	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start Process of deleting multiple ads
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if(isset($_REQUEST['deletemultipleads']) && !empty($_REQUEST['deletemultipleads']))
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	if(isset($_REQUEST['awpcp_ad_to_delete']) && !empty($_REQUEST['awpcp_ad_to_delete']))
	{
		$theawpcparrayofadstodelete=$_REQUEST['awpcp_ad_to_delete'];
	}

	if(!isset($theawpcparrayofadstodelete) || empty($theawpcparrayofadstodelete) )
	{
		$themessagetoprint="No ads have been selected, therefore there is nothing to delete";
	}

	else
	{

		foreach ($theawpcparrayofadstodelete as $theawpcpadtodelete)
		{

			$fordeletionid[]=$theawpcpadtodelete;
		}

		$listofadstodelete=join("','",$fordeletionid);

		// Delete the ad images
		$query="SELECT image_name FROM ".$table_name5." WHERE ad_id IN ('$listofadstodelete')";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			for ($i=0;$i<mysql_num_rows($res);$i++)
			{
				$photo=mysql_result($res,$i,0);

				if (file_exists(AWPCPUPLOADDIR.'/'.$photo))
				{
				@unlink(AWPCPUPLOADDIR.'/'.$photo);
				}
				if (file_exists(AWPCPTHUMBSUPLOADDIR.'/'.$photo))
				{
				@unlink(AWPCPTHUMBSUPLOADDIR.'/'.$photo);
				}
			}

		$query="DELETE FROM ".$table_name5." WHERE ad_id IN ('$listofadstodelete')";
		@mysql_query($query);


		// Delete the ads
		$query="DELETE FROM ".$table_name3." WHERE ad_id IN ('$listofadstodelete')";
		@mysql_query($query);

		$themessagetoprint="The ads have been deleted";

	}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End Process of deleting multiple ads
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	User Side functions and processes
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpui_homescreen($content){
global $classicontent;
$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');

$awpcppageid=awpcp_get_page_id($awpcppagename);

if( !is_page($awpcppagename) || !is_page($awpcppageid) ) {

}

else
{

	if(!isset($classicontent) || empty($classicontent))
	{
		$classicontent=awpcpui_process($awpcppagename);
	}

	$content = preg_replace( "/\[\[AWPCPCLASSIFIEDSUI\]\]/", $classicontent, $content);
}

return $content;

}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	FUNCTION: display the home screen for user  side
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpui_process($awpcppagename) {


		global $awpcp_plugin_url,$hasregionsmodule;

		//Retrieve the welcome message for the user


		if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
		{
			$action=$_REQUEST['a'];
		}

		if($action == 'placead')
		{
			load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websiteurl,$checkhuman,$numval1,$numval2);
		}


		elseif($action == 'editad')
		{
			load_ad_edit_form($action,$awpcppagename);
		}

		elseif($action == 'browseads')
		{
			$where="disabled ='0'";

			if($hasregionsmodule ==  1)
			{
				if(isset($_SESSION['theactiveregionid']) )
				{
					$theactiveregionid=$_SESSION['theactiveregionid'];
					$theactiveregionname=get_theawpcpregionname($theactiveregionid);

					$where.=" AND ad_city='$theactiveregionname' OR ad_state='$theactiveregionname' OR ad_country='$theactiveregionname' OR ad_county_village='$theactiveregionname'";
				}
			}
			display_ads($where,$byl='',$hidepager='');
		}

		elseif($action == 'browsecat')
		{
			if(isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
			{
				$adcategory=$_REQUEST['category_id'];
			}
			if($adcategory == -1)
			{
				$where="disabled='0'";
			}
			else
			{
				$where="(ad_category_id='".$adcategory."' OR ad_category_parent_id='".$adcategory."') AND disabled ='0'";
			}


			if($hasregionsmodule ==  1)
			{
				if(isset($_SESSION['theactiveregionid']) )
				{
					$theactiveregionid=$_SESSION['theactiveregionid'];
					$theactiveregionname=get_theawpcpregionname($theactiveregionid);

					$where.=" AND (ad_city='$theactiveregionname' OR ad_state='$theactiveregionname' OR ad_country='$theactiveregionname' OR ad_county_village='$theactiveregionname')";
				}
			}

			display_ads($where,$byl='',$hidepager='');
		}

		elseif($action == 'showad')
		{
			if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
			{
				$adid=$_REQUEST['id'];
			}
			showad($adid);
		}

		elseif($action == 'contact')
		{
			if(isset($_REQUEST['i']) && !empty($_REQUEST['i']))
			{
				$adid=$_REQUEST['i'];
			}
			load_ad_contact_form($adid);
		}

		elseif($action == 'docontact1')
		{
			$adid=addslashes_mq($_REQUEST['adid']);
			$sendersname=addslashes_mq($_REQUEST['sendersname']);
			$checkhuman=addslashes_mq($_REQUEST['checkhuman']);
			$numval1=addslashes_mq($_REQUEST['numval1']);
			$numval2=addslashes_mq($_REQUEST['numval2']);
			$sendersemail=addslashes_mq($_REQUEST['sendersemail']);
			$contactmessage=addslashes_mq($_REQUEST['contactmessage']);

			processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);

		}

		elseif($action == 'searchads')
		{
			load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');
		}

		elseif($action == 'dosearch')
		{
			dosearch();
		}

		elseif($action == 'doadedit1')
		{
			$adaccesskey=addslashes_mq($_REQUEST['adaccesskey']);
			$editemail=addslashes_mq($_REQUEST['editemail']);
			$awpcppagename=addslashes_mq($_REQUEST['awpcppagename']);
			editadstep1($adaccesskey,$editemail,$awpcppagename);
		}

		elseif($action == 'resendaccesskey')
		{
			$editemail=addslashes_mq($_REQUEST['editemail']);
			$awpcppagename=addslashes_mq($_REQUEST['awpcppagename']);
			resendadaccesskeyform($editemail,$awpcppagename);
		}

		elseif($action == 'dopost1')
		{
			$adid=addslashes_mq($_REQUEST['adid']);
			$adterm_id=addslashes_mq($_REQUEST['adtermid']);
			$adkey=addslashes_mq($_REQUEST['adkey']);
			$editemail=addslashes_mq($_REQUEST['editemail']);
			$adtitle=addslashes_mq($_REQUEST['adtitle']);
			$adtitle=strip_html_tags($adtitle);
			$adcontact_name=addslashes_mq($_REQUEST['adcontact_name']);
			$adcontact_name=strip_html_tags($adcontact_name);
			$adcontact_phone=addslashes_mq($_REQUEST['adcontact_phone']);
			$adcontact_phone=strip_html_tags($adcontact_phone);
			$adcontact_email=addslashes_mq($_REQUEST['adcontact_email']);
			$adcategory=addslashes_mq($_REQUEST['adcategory']);
			$adcontact_city=addslashes_mq($_REQUEST['adcontact_city']);
			$adcontact_city=strip_html_tags($adcontact_city);
			$adcontact_state=addslashes_mq($_REQUEST['adcontact_state']);
			$adcontact_state=strip_html_tags($adcontact_state);
			$adcontact_country=addslashes_mq($_REQUEST['adcontact_country']);
			$adcontact_country=strip_html_tags($adcontact_country);
			$ad_county_village=addslashes_mq($_REQUEST['adcontact_countyvillage']);
			$ad_county_village=strip_html_tags($ad_county_village);
			$ad_item_price=addslashes_mq($_REQUEST['ad_item_price']);
			$ad_item_price=str_replace(",", '', $ad_item_price);
			$addetails=addslashes_mq($_REQUEST['addetails']);
			if(get_awpcp_option('allowhtmlinadtext') == 0){
			$addetails=strip_html_tags($addetails);
			}
			$adpaymethod=addslashes_mq($_REQUEST['adpaymethod']);
			if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
			$adaction=addslashes_mq($_REQUEST['adaction']);} else {$adaction='';}
			$awpcppagename=addslashes_mq($_REQUEST['awpcppagename']);
			$offset=addslashes_mq($_REQUEST['offset']);
			$results=addslashes_mq($_REQUEST['results']);
			$websiteurl=addslashes_mq($_REQUEST['websiteurl']);
			$checkhuman=addslashes_mq($_REQUEST['checkhuman']);
			$numval1=addslashes_mq($_REQUEST['numval1']);
			$numval2=addslashes_mq($_REQUEST['numval2']);


			processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);

		}


		elseif($action == 'dp')
		{
			if(isset($_REQUEST['k']) && !empty($_REQUEST['k']))
			{
				$keyids=$_REQUEST['k'];
				list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
			}
			deletepic($picid,$adid,$adtermid,$adkey,$editemail);
		}

		elseif($action == 'paypal')
		{
			do_paypal();
		}

		elseif($action == '2checkout')
		{
			do_2checkout();
		}

		elseif($action == 'cancelpaypal')
		{
			cancelpaypal();
		}

		elseif($action == 'paypalthankyou')
		{
			paypalthankyou();
		}


		elseif($action == 'adpostfinish')
		{
			if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction']))
			{
				$adaction=$_REQUEST['adaction'];
			}
			if(isset($_REQUEST['ad_id']) && !empty($_REQUEST['ad_id']))
			{
				$theadid=$_REQUEST['ad_id'];
			}
			if(isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
			{
				$theadkey=$_REQUEST['adkey'];
			}

			if($adaction == 'editad')
			{
				showad($theadid);
			}

			else
			{
				ad_success_email($theadid,$txn_id='',$theadkey,$message,$gateway='');
			}
		}

		elseif($action == 'deletead')
		{
			if(isset($_REQUEST['k']) && !empty($_REQUEST['k']))
			{
				$keyids=$_REQUEST['k'];
				list($adid,$adkey,$editemail) = split('[_]', $keyids);
			}
			deletead($adid,$adkey,$editemail);

		}
		elseif($action == 'setregion')
		{
			if($hasregionsmodule ==  1)
			{
				if(isset($_REQUEST['regionid']) && !empty($_REQUEST['regionid']))
				{
					$theregionidtoset=$_REQUEST['regionid'];
				}
				if( isset($_SESSION['theactiveregionid']) )
				{
					unset($_SESSION['theactiveregionid']);
				}

				$_SESSION['theactiveregionid']=$theregionidtoset;

				if(region_is_a_country($theregionidtoset))
				{
					$_SESSION['regioncountryID']=$theregionidtoset;
				}

				if(region_is_a_state($theregionidtoset))
				{
					$thestateparentid=get_theawpcpregionparentid($theregionidtoset);
					$_SESSION['regioncountryID']=$thestateparentid;
					$_SESSION['regionstatownID']=$theregionidtoset;
				}

				if(region_is_a_city($theregionidtoset))
				{
					$thecityparentid=get_theawpcpregionparentid($theregionidtoset);
					$thestateparentid=get_theawpcpregionparentid($thecityparentid);
					$_SESSION['regioncountryID']=$thestateparentid;
					$_SESSION['regionstatownID']=$thecityparentid;
					$_SESSION['regioncityID']=$theregionidtoset;
				}




			}

			awpcp_display_the_classifieds_page_body($awpcppagename);

		}

		elseif($action == 'unsetregion')
		{
				if( isset($_SESSION['theactiveregionid']) )
				{
					unset($_SESSION['theactiveregionid']);
				}

			awpcp_display_the_classifieds_page_body($awpcppagename);
		}

		elseif( $action == 'setsessionregionid' )
		{
			global $hasregionsmodule;

			if($hasregionsmodule ==  1)
			{
				if(isset($_REQUEST['sessionregion']) && !empty($_REQUEST['sessionregion']) )
				{
					$sessionregionid=$_REQUEST['sessionregion'];
				}
				if(isset($_REQUEST['sessionregionIDval']) && !empty($_REQUEST['sessionregionIDval']) )
				{
					$sessionregionIDval=$_REQUEST['sessionregionIDval'];
				}

					if($sessionregionIDval == 1)
					{
						$_SESSION['regioncountryID']=$sessionregionid;
					}

					elseif($sessionregionIDval == 2)
					{
						$_SESSION['regionstatownID']=$sessionregionid;
					}

					elseif($sessionregionIDval == 3)
					{
						$_SESSION['regioncityID']=$sessionregionid;
					}
				}

				load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websiteurl,$checkhuman,$numval1,$numval2);

		}
		elseif( $action == 'cregs' )
		{
			if(isset($_REQUEST['loadwhich']) && !empty($_REQUEST['loadwhich']) )
			{
				$loadwhich=$_REQUEST['loadwhich'];
			}

			if(isset($_SESSION['regioncountryID']) )
			{
				unset($_SESSION['regioncountryID']);
			}
			if(isset($_SESSION['regionstatownID']) )
			{
				unset($_SESSION['regionstatownID']);
			}
			if(isset($_SESSION['regioncityID']) )
			{
				unset($_SESSION['regioncityID']);
			}
			if( isset($_SESSION['theactiveregionid']) )
			{
				unset($_SESSION['theactiveregionid']);
			}

			if($loadwhich == 'p')
			{
				load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websieurl='',$checkhuman='',$numval1='',$numval2='');
			}
			elseif($loadwhich == 's')
			{
				load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');
			}
			else
			{
				display_ads($where='',$byl='1',$hidepager='1');
			}

		}
		elseif( $action == 'categoriesview' )
		{
			awpcp_display_the_classifieds_page_body($awpcppagename);
		}
		else
		{

			if(get_awpcp_option('main_page_display') == 2)
			{
				//Display latest ads on mainpage
				display_ads($where='',$byl='1',$hidepager='');
			}

			else
			{
				awpcp_display_the_classifieds_page_body($awpcppagename);
			}
		}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End function display the home screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: configure the menu place ad edit exisiting ad browse ads search ads
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_menu_items()
{

			$awpcppage=get_currentpagename();
			$awpcppagename = sanitize_title($awpcppage, $post_ID='');
			$quers=setup_url_structure($awpcppagename);

				echo "

					<ul id=\"postsearchads\">";

					$isadmin=checkifisadmin();

					if(!(get_awpcp_option('onlyadmincanplaceads'))){

						echo "
								<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
								<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
							";
					}

					elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1)){
						echo "
								<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
								<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
							";
					}

					echo "
							<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
							<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
							</ul>


						<div class=\"fixfloat\"></div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION: configure the menu place ad edit exisiting ad browse ads search ads
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: show the classifieds page body
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcp_display_the_classifieds_page_body($awpcppagename)
{
	global $hasregionsmodule;

	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	$quers=setup_url_structure($awpcppagename);

	$permastruc=get_option('permalink_structure');

		echo "<div id=\"classiwrapper\">";


				// Welcome text

				$uiwelcome=get_awpcp_option('uiwelcome');
				echo "<div class=\"uiwelcome\">$uiwelcome</div>";

				// Place the menu items place ad edit exisiting ad browse ads search ads
				awpcp_menu_items();

						if($hasregionsmodule ==  1)
						{
							if( isset($_SESSION['theactiveregionid']) )
							{
								$theactiveregionid=$_SESSION['theactiveregionid'];

								$theactiveregionname=get_theawpcpregionname($theactiveregionid);


								echo "<h2>You are currently browsing in <b>$theactiveregionname</b></h2><SUP><a href=\"?a=unsetregion\">Clear $theactiveregionname session</a></SUP>";
							}
						}


					echo "
						<div class=\"classifiedcats\">
					";

					//Display the categories
					awpcp_display_the_classifieds_category($awpcppagename);

					echo "</div>";

					if( field_exists($field='removepoweredbysign') && !(get_awpcp_option('removepoweredbysign')) )
					{
						echo "<p><font style=\"font-size:smaller\">Powered by <a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
					}
					elseif( field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign')) )
					{

					}
					else
					{
						echo "<p><font style=\"font-size:smaller\">Powered by <a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
					}

					echo "</div>";
}

function awpcp_display_the_classifieds_category($awpcppagename)
{

	global $wpdb,$imagesurl,$hasregionsmodule;
	$table_name1 = $wpdb->prefix . "awpcp_categories";

	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	$quers=setup_url_structure($awpcppagename);

	$permastruc=get_option('permalink_structure');

	$table_cols=1;




	$query="SELECT category_id,category_name FROM ".$table_name1." WHERE category_parent_id='0' AND category_name <> '' ORDER BY category_name ASC";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	if (mysql_num_rows($res))
	{
		$i=1;


		//////////////////////////////////////////////////////////////////////
		// For use with regions module if sidelist is enabled
		/////////////////////////////////////////////////////////////////////

		if($hasregionsmodule ==  1)
		{
			if(get_awpcp_option('showregionssidelist') )
			{
				$awpcp_regions_sidelisted_type2=awpcp_regions_sidelisted_type2();
				$awpcp_regions_sidelisted_type3=awpcp_regions_sidelisted_type3();
				$awpcp_regions_sidelisted_type4=awpcp_regions_sidelisted_type4();
				$awpcp_regions_sidelisted_type5=awpcp_regions_sidelisted_type5();

				$awpcpregions_sidepanel="<div class=\"awpcpcatlayoutright\"><ul>";
				$awpcpregions_sidepanel.="$awpcp_regions_sidelisted_type2";
				$awpcpregions_sidepanel.="$awpcp_regions_sidelisted_type3";
				$awpcpregions_sidepanel.="$awpcp_regions_sidelisted_type4";
				$awpcpregions_sidepanel.="$awpcp_regions_sidelisted_type5";
				$awpcpregions_sidepanel.="</ul></div>";
				$usingsidelist=1;
			}
		}

		$myreturn='<div id="awpcpcatlayout">';// Open the container division

		if($usingsidelist)
		{
			$myreturn.="$awpcpregions_sidepanel<div class=\"awpcpcatlayoutleft\">";
		}

		while ($rsrow=mysql_fetch_row($res))
		{

				$myreturn.="<div id=\"showcategoriesmainlist\"><ul>";

				if(get_awpcp_option('showadcount') == 1)
				{
					$adsincat1=total_ads_in_cat($rsrow[0]);
					$adsincat1="($adsincat1)";
				}
				else
				{
					$adsincat1='';
				}

				$myreturn.="<li>";

				if( function_exists('get_category_icon') )
				{
					$category_icon=get_category_icon($rsrow[0]);
				}

				if( isset($category_icon) && !empty($category_icon) )
				{
					$caticonsurl="<img class=\"categoryicon\" src=\"$imagesurl/caticons/$category_icon\" alt=\"$rsrow[1]\" border=\"0\">";
				}
				else
				{
					$caticonsurl='';
				}


				$modcatname1=cleanstring($rsrow[1]);
				$modcatname1=add_dashes($modcatname1);

				if( get_awpcp_option('seofriendlyurls') )
				{

					if(isset($permastruc) && !empty($permastruc))
					{
						$myreturn.="<p class=\"maincategoryclass\">$caticonsurl<a href=\"".$quers."browsecat/$rsrow[0]/$modcatname1\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</p>";
					}
					else
					{
						$myreturn.="<p class=\"maincategoryclass\">$caticonsurl<a href=\"".$quers."browsecat&category_id=$rsrow[0]\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</p>";
					}
				}
				else
				{
					$myreturn.="<p class=\"maincategoryclass\">$caticonsurl<a href=\"".$quers."browsecat&category_id=$rsrow[0]\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</p>";
				}

				// Start configuration of sub categories

				$myreturn.="<ul class=\"showcategoriessublist\">";


				$mcid=$rsrow[0];

				$query="SELECT category_id,category_name FROM ".$table_name1." WHERE category_parent_id='$mcid' AND category_name <> '' ORDER BY category_name ASC";
				if (!($res2=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

				if (mysql_num_rows($res2))
				{

					while ($rsrow2=mysql_fetch_row($res2))
					{

						if(get_awpcp_option('showadcount') == 1)
						{
							$adsincat2=total_ads_in_cat($rsrow2[0]);
							$adsincat2="($adsincat2)";
						}
						else
						{
							$adsincat2='';
						}

						if( function_exists('get_category_icon') )
						{
							$sub_category_icon=get_category_icon($rsrow2[0]);
						}

						if( isset($sub_category_icon) && !empty($sub_category_icon) )
						{
							$subcaticonsurl="<img class=\"categoryicon\" src=\"$imagesurl/caticons/$sub_category_icon\" alt=\"$rsrow2[1]\" border=\"0\">";
						}
						else
						{
							$subcaticonsurl='';
						}


						$myreturn.="<li>";

						$modcatname2=cleanstring($rsrow2[1]);
						$modcatname2=add_dashes($modcatname2);

						if( get_awpcp_option('seofriendlyurls') )
						{
							if(isset($permastruc) && !empty($permastruc))
							{
								$myreturn.="$subcaticonsurl<a href=\"".$quers."browsecat/$rsrow2[0]/$modcatname2\">$rsrow2[1]</a> $adsincat2";
							}
							else
							{
								$myreturn.="$subcaticonsurl<a href=\"".$quers."browsecat&category_id=$rsrow2[0]\">$rsrow2[1]</a> $adsincat2";
							}
						}
						else
						{
							$myreturn.="$subcaticonsurl<a href=\"".$quers."browsecat&category_id=$rsrow2[0]\">$rsrow2[1]</a> $adsincat2";
						}

						$myreturn.="</li>";

					} // Close while loop #2


					$myreturn.="</ul>"; // Close sub categories list
					$myreturn.="</li>"; // Close top level item li
					$i++;

				} // Close if (mysql_num_rows($res2)) #2

				$myreturn.="</ul></div>\n";

		} // Close while loop #1

	} // Close if (mysql_num_rows($res)) #1

		if($usingsidelist)
		{
			$myreturn.='</div>'; // To close div class awpcplayoutleft
		}

		$myreturn.='</div>';// Close the container division

		echo "$myreturn";
		echo "<div class=\"fixfloat\"></div>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION: show the categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	FUNCTION: display the ad post form
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail,$adaccesskey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2)
{

	global $wpdb,$siteurl,$hasregionsmodule;

	$isadmin=checkifisadmin();

	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option(permalink_structure);

		if(get_awpcp_option('onlyadmincanplaceads') && ($isadmin != 1))
		{

			echo "
				<div id=\"classiwrapper\">
				<p>You do not have permission to perform the function you are trying to perform. Access to this page has been denied.</p>
				</div>
			";
		}

		elseif(get_awpcp_option('requireuserregistration') && !is_user_logged_in())
		{



			$putregisterlink="<a href=\"$siteurl/wp-login.php?action=register\" title=\"Register\"><b>Register</b></a>";


			echo "
				<div id=\"classiwrapper\">
				<p>Only registered users can post ads. Please $putregisterlink in order to post an ad. If you are already registered, please login below in order to post your ad.</p>
				<h2>Login</h2>
				  <form name=\"loginform\" id=\"loginform\" action=\"$siteurl/wp-login.php\" method=\"post\">
				  	<p>
				  		<label>Username<br>
				  		<input name=\"log\" id=\"user_login\" value=\"\" class=\"textinput\" size=\"20\" tabindex=\"10\" type=\"text\"></label>
				  	</p>

				  	<p>
				  		<label>Password<br>
				  		<input name=\"pwd\" id=\"user_pass\" value=\"\" class=\"textinput\" size=\"20\" tabindex=\"20\" type=\"password\"></label>
				  	</p>
				  	<p><label><input name=\"rememberme\" id=\"rememberme\" value=\"forever\" tabindex=\"90\" type=\"checkbox\"> Remember Me</label></p>
				  	<p align=\"center\">
				  		<input name=\"wp-submit\" id=\"wp-submit\" value=\"Log In\" class=\"submitbutton\" tabindex=\"100\" type=\"submit\">
				  		<input name=\"redirect_to\" value=\"".$quers."placead\" type=\"hidden\">
				  		<input name=\"testcookie\" value=\"1\" type=\"hidden\">
				  	</p>
  				</form>
				</div>
			";

		}


		else {


		$table_name2 = $wpdb->prefix . "awpcp_adfees";
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$images='';
		$displaydeleteadlink='';


			if($action == 'placead'){
				$liplacead="<li class=\"postad\"><b>Placing Ad: Step 1</b></li>";
			}
			else {
				$liplacead="<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>";
			}
			if($action== 'editad'){
				$lieditad="<li class=\"edit\"><b>Editing Ad:Step 2</b></li>";}
			else {
				$lieditad="<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>";
			}

				if($action == 'editad')
				{

					$savedemail=get_adposteremail($adid);

					if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
					{

					 	$query="SELECT ad_title,ad_contact_name,ad_contact_email,ad_category_id,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details,ad_key from ".$table_name3." WHERE ad_id='$adid' AND ad_contact_email='$editemail' AND ad_key='$adaccesskey'";
					 	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						while ($rsrow=mysql_fetch_row($res))
						{
							list($adtitle,$adcontact_name,$adcontact_email,$adcategory,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$ad_key)=$rsrow;
						}

						$ad_item_price=($ad_item_price/100);

						$ikey="$adid";
						$ikey.="_";
						$ikey.="$adaccesskey";
						$ikey.="_";
						$ikey.="$editemail";


						if( get_awpcp_option('seofriendlyurls') )
						{
							if(isset($permastruc) && !empty($permastruc))
							{
								$displaydeleteadlink="<p class=\"alert\"><a href=\"".$quers."?a=deletead&k=$ikey\">Delete Ad</a></p>";

							}
							else
							{

							$displaydeleteadlink="<p class=\"alert\"><a href=\"".$quers."deletead&k=$ikey\">Delete Ad</a></p>";

							}
						}
						else
						{
							$displaydeleteadlink="<p class=\"alert\"><a href=\"".$quers."deletead&k=$ikey\">Delete Ad</a></p>";
						}

					}
					else
					{
						unset($action);
					}
				}


echo "
<div id=\"classiwrapper\">";

if(!is_admin()){

	echo "
			<ul id=\"postsearchads\">
		";
			if(!(get_awpcp_option('onlyadmincanplaceads'))){
				echo "
						$liplacead
						$lieditad
					";
			}

			elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1)){
					echo "
							$liplacead
							$lieditad
						";
				}
	echo "
			<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
			<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
			</ul>
		";
}

		////////////////////////////////////////////////////////////////////////////////
		// If running in pay mode get and display the payment option settings
		////////////////////////////////////////////////////////////////////////////////

		if(get_awpcp_option(freepay) == 1){

			$paymethod='';

			if($action == 'editad'){
				$paymethod='';
			}

			else {

				if(adtermsset()){

				//configure the pay methods

				if($adpaymethod == 'paypal'){ $ischeckedP="checked"; } else { $ischeckedP=''; }
				if($adpaymethod == '2checkout'){ $ischecked2co="checked"; }else { $ischecked2co=''; }

					$paymethod="<div id=\"showhidepaybutton\" style=\"display:none;\"><h2>Payment gateway</h2><p>Choose your payment gateway</p>";
					if(get_awpcp_option(activatepaypal) == 1)
					{
						$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"paypal\" $ischeckedP>PayPal<br/>";
					}
					if(get_awpcp_option(activate2checkout) == 1)
					{
						$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"2checkout\"  $ischecked2co>2Checkout</br/>";
					}
					$paymethod.="</div><div class=\"fixfloat\"></div>";

				}


			}
		}

echo "<div class=\"fixfloat\"></div>";


						/////////////////////////////////////////////////////////////////////
						// Retrieve the categories to populate the select list
						/////////////////////////////////////////////////////////////////////

						$allcategories=get_categorynameidall($adcategory);

						// Setup javascript checkpoints

				if((get_awpcp_option('displayphonefield') == 1)
				&&(get_awpcp_option('displayphonefieldreqop') == 1)){
				$phonecheck="if(the.adcontact_phone.value==='') {
							alert('You did not fill out a phone number for the ad contact person. The information is required.');
							the.adcontact_phone.focus();
							return false;
						}";}else {$phonecheck='';}

				if((get_awpcp_option('displaycityfield') == 1)
				&&(get_awpcp_option('displaycityfieldreqop') == 1)){
				$citycheck="if(the.adcontact_city.value==='') {
							alert('You did not fill out your city. The information is required.');
							the.adcontact_city.focus();
							return false;
						}";}else {$citycheck='';}

				if((get_awpcp_option('displaystatefield') == 1)
				&&(get_awpcp_option('displaystatefieldreqop') == 1)){
				$statecheck="if(the.adcontact_state.value==='') {
							alert('You did not fill out your state. The information is required.');
							the.adcontact_state.focus();
							return false;
						}";}else {$statecheck='';}

				if((get_awpcp_option('displaycountyvillagefield') == 1)
				&&(get_awpcp_option('displaycountyvillagefieldreqop') == 1)){
				$countyvillagecheck="if(the.adcontact_countyvillage.value==='') {
							alert('You did not fill out your county/village/other. The information is required.');
							the.adcontact_countyvillage.focus();
							return false;
						}";}else {$countyvillagecheck='';}

				if((get_awpcp_option('displaycountryfield') == 1)
				&&(get_awpcp_option('displaycountryfieldreqop') == 1)){
				$countrycheck="if(the.adcontact_country.value==='') {
							alert('You did not fill out your country. The information is required.');
							the.adcontact_country.focus();
							return false;
						}";}else {$countrycheck='';}

				if((get_awpcp_option('displaywebsitefield') == 1)
				&&(get_awpcp_option('displaywebsitefieldreqop') == 1)){
				$websitecheck="if(the.websiteurl.value==='') {
							alert('You did enter your website address. The information is required.');
							the.websiteurl.focus();
							return false;
						}";}else {$websitecheck='';}

				if((get_awpcp_option('displaypricefield') == 1)
				&&(get_awpcp_option('displaypricefieldreqop') == 1)){
				$itempricecheck="if(the.ad_item_price.value==='') {
							alert('You did not enter a value for the item price.');
							the.ad_item_price.focus();
							return false;
						}";}else {$itempricecheck='';}

				if( (get_awpcp_option('freepay') == 1) && ($action == 'placead') ) {
				$paymethodcheck="if(!checked(the.adpaymethod)) {
							alert('You did not select your payment method. The information is required.');
							the.adpaymethod.focus();
							return false;
						}";}else {$paymethodcheck='';}

				if( (get_awpcp_option('freepay') == 1) && ($action == 'placead') ) {
				$adtermcheck="if(the.adterm_id.value==='') {
							alert('You did not select your ad term choice. The information is required.');
							the.adterm_id.focus();
							return false;
						}";}else {$adtermcheck='';}

				if(get_awpcp_option('contactformcheckhuman') == 1)
				{

					if(isset($numval1) && !empty($numval1))
					{
						$numval1=$numval1;
					}
					else
					{
						$numval1=rand(1,get_awpcp_option('contactformcheckhumanhighnumval'));
					}
					if(isset($numval2) && !empty($numval2))
					{
						$numval2=$numval2;
					}
					else
					{
						$numval2=rand(1,get_awpcp_option('contactformcheckhumanhighnumval'));
					}

					$thesum=($numval1 +  $numval2);

					$checkhumancheck="

					if (the.checkhuman.value==='')
					{
						alert('You did not enter the sum of $numval1 plus $numval2. Please enter the sum of $numval1 plus $numval2');
						the.checkhuman.focus();
						return false;
					}
					if (the.checkhuman.value != $thesum)
					{
						alert('The value you have entered  for the sum of $numval1 plus $numval2 is incorrect. Please enter the correct sum of $numval1 plus $numval2');
						the.checkhuman.focus();
						return false;
					}

					";

				}

				$checktheform="<script type=\"text/javascript\">
					function checkform() {
						var the=document.adpostform;
						if (the.adtitle.value==='') {
							alert('You did not fill out an ad title');
							the.adtitle.focus();
							return false;
						}
						if (the.adcategory.value==='') {
							alert('You did not select an ad category');
							the.adcategory.focus();
							return false;
						}
						if (the.adcontact_name.value==='') {
							alert('You did not fill in the name of the ad contact person');
							the.adcontact_name.focus();
							return false;
						}
						if ((the.adcontact_email.value==='') || (the.adcontact_email.value.indexOf('@')==-1) || (the.adcontact_email.value.indexOf('.',the.adcontact_email.value.indexOf('@')+2)==-1) || (the.adcontact_email.value.lastIndexOf('.')==the.adcontact_email.value.length-1)) {
							alert('Either you did not enter your email address or the email address you entered is not valid.');
							the.adcontact_email.focus();
							return false;
						}

						$phonecheck;
						$citycheck;
						$statecheck;
						$countrycheck;
						$websitecheck;
						$countyvillagecheck;
						$itempricecheck
						$paymethodcheck;
						$adtermcheck;
						$checkhumancheck;


						if (the.addetails.value==='') {
							alert('You did not fill in any details for your ad.');
							the.addetails.focus();
							return false;
						}


						return true;
					}


							function textCounter(field, countfield, maxlimit) {
							if (field.value.length > maxlimit)
							{ // if too long...trim it!
								field.value = field.value.substring(0, maxlimit);
							}
								// otherwise, update 'characters left' counter

								else {
									countfield.value = maxlimit - field.value.length;
								}
							}


							 function awpcp_toggle_visibility(id) {
								 var e = document.getElementById(id);
								 if(e.style.display == 'block')
								 {
								      e.style.display = 'none';
								  }
								 else
								 {
									 e.style.display = 'block';
								}
				    		}

				    		 function awpcp_toggle_visibility_reverse(id) {
								 var e = document.getElementById(id);
									if(e.style.display == 'block')
									{
										 e.style.display = 'none';
									 }
									 else
									{
										 e.style.display = 'none';
									}
				    		}

				</script>";



				$addetailsmaxlength=get_awpcp_option('maxcharactersallowed');

				$theformbody='';

				$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "\n\n", $addetails);

				if(get_awpcp_option('allowhtmlinadtext') == 1)
				{
					$htmlstatus="HTML is allowed";
				}
				else
				{
					$htmlstatus="No HTML allowed";
				}

				$readonlyacname='';
				$readonlyacem='';

				if( get_awpcp_option('requireuserregistration') && is_user_logged_in() && !is_admin() )
				{

					global $current_user;
				    get_currentuserinfo();

				    $adcontact_name=$current_user->user_login;
				    $adcontact_email=$current_user->user_email;
				    $readonlyacname="readonly";
				    $readonlyacem="readonly";


				}


				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// START configuration of dropdown lists used with regions module if regions module exists and pre-set regions exist
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				if( $hasregionsmodule ==  1 )
				{
					if($action == 'editad')
					{
						// Do nothing
					}
					else
					{
						if(isset($_SESSION['regioncountryID']) )
						{
							$thesessionregionidval1=$_SESSION['regioncountryID'];
						}

						if(isset($_SESSION['regionstatownID']) )
						{
							$thesessionregionidval2=$_SESSION['regionstatownID'];
						}

						if(isset($_SESSION['regioncityID']) )
						{
							$thesessionregionidval3=$_SESSION['regioncityID'];
						}


						if( !isset($thesessionregionidval1) || empty($thesessionregionidval1) )
						{
							if(get_awpcp_option('displaycountryfield') )
							{
								if( regions_countries_exist() )
								{
									set_session_regionID(1);
									$formdisplayvalue="none";
								}

							}

						}

						elseif( isset($thesessionregionidval1) && !isset ($thesessionregionidval2) )
						{
							if(get_awpcp_option('displaystatefield') )
							{
								if( regions_states_exist($thesessionregionidval1) )
								{
									set_session_regionID(2);
									$formdisplayvalue="none";
								}
							}
						}

						elseif( isset($thesessionregionidval1) && isset($thesessionregionidval2) && !isset ($thesessionregionidval3) )
						{
							if(get_awpcp_option('displaycityfield') )
							{
								if( regions_cities_exist($thesessionregionidval2) )
								{
									set_session_regionID(3);
									$formdisplayvalue="none";
								}

							}
						}
					}
				}


				if(!isset($formdisplayvalue) || empty($formdisplayvalue) )
				{
					$formdisplayvalue="block";
				}

				if($action== 'editad' )
				{
					$editorposttext="Your ad details have been filled out in the form below. Make any changes needed then resubmit the ad to update it";
				}
				else
				{
					$editorposttext="Fill out the form below to post your classified ad.";
				}

				echo "<div style=\"display:$formdisplayvalue\">";

				if(!is_admin())
				{
					$theformbody.="$displaydeleteadlink<p>$editorposttext";

					if(! ($action== 'editad' ) )
					{
						if($hasregionsmodule == 1)
						{
							$theformbody.=" If you have made an error in setting up the location where you want to post your ad <a href=\"?a=cregs&loadwhich=p\">click here to unset the saved locations</a>";
						}
					}

					$theformbody.="</p>";

					$faction="id=\"awpcpui_process\"";
				}
				else
				{
					$faction="action=\"?page=Manage1\" id=\"awpcp_launch\"";
				}

				$theformbody.="$checktheform $ermsg<form method=\"post\" name=\"adpostform\" id=\"adpostform\" $faction onsubmit=\"return(checkform())\">
				<input type=\"hidden\" name=\"adid\" value=\"$adid\">
				<input type=\"hidden\" name=\"adaction\" value=\"$action\">
				<input type=\"hidden\" name=\"a\" value=\"dopost1\">";
				if($action == 'editad')
				{
					$theformbody.="<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\">";
				}
				$theformbody.="<input type=\"hidden\" name=\"adkey\" value=\"$ad_key\">
				<input type=\"hidden\" name=\"editemail\" value=\"$editemail\">
				<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">
				<input type=\"hidden\" name=\"results\" value=\"$results\">
				<input type=\"hidden\" name=\"offset\" value=\"$offset\">
				<input type=\"hidden\" name=\"numval1\" value=\"$numval1\">
				<input type=\"hidden\" name=\"numval2\" value=\"$numval2\">
				<br/>
				<h2>Ad Details and Contact Information </h2>
				<p>Ad Title<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"adtitle\" value=\"$adtitle\"></p>
				<p>Ad Category<br/><select name=\"adcategory\"><option value=\"\">Select your ad category</option>$allcategories</a></select></p>";

				if(get_awpcp_option(displaywebsitefield) == 1)
				{
					$theformbody.="<p>Website URL<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"websiteurl\" value=\"$websiteurl\" /></select></p>";
				}

				$theformbody.="<p>Name of person to contact<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_name\" value=\"$adcontact_name\" $readonlyacname></p>
				<p>Contact Person's Email (Please enter a valid email. The codes needed to edit your ad will be sent to your email address)<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_email\" value=\"$adcontact_email\" $readonlyacem></p>";

				if(get_awpcp_option(displayphonefield) == 1)
				{
					$theformbody.="<p>Contact Person's Phone Number<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_phone\" value=\"$adcontact_phone\"></p>";
				}


				if(get_awpcp_option(displaycountryfield) )
				{
					$theformbody.="<p>Country<br/>";

					if($hasregionsmodule ==  1)
					{
						$opsitemregcountrylist=awpcp_region_create_country_list($adcontact_country,$byvalue='');

						if(!isset($opsitemregcountrylist) || empty($opsitemregcountrylist) )
						{
							$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_country\" value=\"$adcontact_country\">";
						}
						else
						{
							$theformbody.="<select name=\"adcontact_country\">";
							$theformbody.="$opsitemregcountrylist";
							$theformbody.="</select>";
						}
					}
					else
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_country\" value=\"$adcontact_country\">";
					}

						$theformbody.="</p>";

				}


				if(get_awpcp_option(displaystatefield) )
				{
					$theformbody.="<p>State<br/>";

					if($hasregionsmodule ==  1)
					{
						if(!regions_states_exist($thesessionregionidval1) )
						{
							$opsitemregstatownlist='';
						}
						else
						{
							$opsitemregstatownlist=awpcp_region_create_statown_list($adcontact_state,$byvalue='',$adcontact_country='');
						}

						if(!isset($opsitemregstatownlist) || empty($opsitemregstatownlist) )
						{
							$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_state\" value=\"$adcontact_state\">";
						}
						else
						{
							$theformbody.="<select name=\"adcontact_state\">";
							$theformbody.="$opsitemregstatownlist";
							$theformbody.="</select>";
						}
					}
					else
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_state\" value=\"$adcontact_state\">";
					}

						$theformbody.="</p>";
				}



				if(get_awpcp_option(displaycityfield) )
				{
					$theformbody.="<p>City<br/>";

					if($hasregionsmodule ==  1)
					{
						$opsitemregcitylist=awpcp_region_create_city_list($adcontact_city,$byvalue='',$thecitystate);

						if(!isset($opsitemregcitylist) || empty($opsitemregcitylist) )
						{
							$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_city\" value=\"$adcontact_city\">";
						}
						else
						{
							$theformbody.="<select name=\"adcontact_city\">";
							$theformbody.="$opsitemregcitylist";
							$theformbody.="</select>";
						}
					}
					else
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_city\" value=\"$adcontact_city\">";
					}

						$theformbody.="</p>";
				}

				if(get_awpcp_option(displaycountyvillagefield) )
				{
					$theformbody.="<p>County/Village/Other<br/>";

					if($hasregionsmodule ==  1)
					{
						$opsitemregcountyvillagelist=awpcp_region_create_county_village_list($ad_county_village);

						if(!isset($opsitemregcountyvillagelist) || empty($opsitemregcountyvillagelist) )
						{
							$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_countyvillage\" value=\"$ad_county_village\">";
						}
						else
						{
							$theformbody.="<select name=\"adcontact_countyvillage\">";
							$theformbody.="$opsitemregcountyvillagelist";
							$theformbody.="</select>";
						}
					}
					else
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_countyvillage\" value=\"$ad_county_village\">";
					}

						$theformbody.="</p>";
				}

				if(get_awpcp_option(displaypricefield) == 1){
				$theformbody.="<p>Item Price<br/><input size=\"10\" type=\"text\" class=\"inputboxprice\" maxlength=\"10\" name=\"ad_item_price\" value=\"$ad_item_price\"></p>";}
				$theformbody.="<p>Ad Details<br/><input readonly type=\"text\" name=\"remLen\" size=\"10\" maxlength=\"5\" class=\"inputboxmini\" value=\"$addetailsmaxlength\"> characters left<br/><br/>$htmlstatus<br/><textarea name=\"addetails\" rows=\"10\" cols=\"50\" class=\"textareainput\" onKeyDown=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\" onKeyUp=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\">$addetails</textarea></p>";

				if(get_awpcp_option('contactformcheckhuman') == 1)
				{

					$theformbody.="<p>Enter the value of the following sum: <b>$numval1 + $numval2</b><br>
					<input type=\"text\" name=\"checkhuman\" value=\"$checkhuman\" size=\"5\"></p>";




				}


	if(get_awpcp_option(freepay) == '0')
	{
		echo "$theformbody";
	}

	else
	{

		echo "$theformbody";

		if($action == 'editad')
		{
			$adtermscode='';
		}

		else
		{


			if(!isset($adterm_id) || empty($adterm_id))
			{

				if(adtermsset())
				{

					$adtermscode="<h2>Select Ad Term</h2>";

						//////////////////////////////////////////////////
						// Get and configure pay options
						/////////////////////////////////////////////////


						$paytermslistitems=array();

						$query="SELECT * FROM  ".$table_name2."";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							if (mysql_num_rows($res))
							{

							while ($rsrow=mysql_fetch_row($res))
							{
								list($savedadtermid,$adterm_name,$amount,$recurring,$rec_period,$rec_increment)=$rsrow;
								 if($rec_increment == "M"){$termname="Month";}
								 if($rec_increment == "D"){$termname="Day";}
								 if($rec_increment == "W"){$termname="Week";}
								 if($rec_increment == "Y"){$termname="Year";}

								$termname=$termname;

								if($adtermid == $savedadtermid)
								{
									$ischecked="checked";
								}
								else
								{
									$ischecked='';
								}

								$awpcpthecurrencysymbol=awpcp_get_currency_code();

								$adtermscode.="<input type=\"radio\" name=\"adtermid\"";

								if($amount > 0)
								{
									$adtermscode.="onclick=\"awpcp_toggle_visibility('showhidepaybutton');\"";
								}

								if($amount <= 0)
								{
									$adtermscode.="onclick=\"awpcp_toggle_visibility_reverse('showhidepaybutton');\"";
								}

								$adtermscode.="value=\"$savedadtermid\" $ischecked>$adterm_name ($awpcpthecurrencysymbol$amount for a $rec_period $termname listing)<br/>";
							}

						}

				}

					echo "$adtermscode<p>$paymethod</p>";

				}//end if adtermsset
			}
		}

	}
				echo "<input type=\"submit\" class=\"scbutton\" value=\"Continue\"></form>";
				echo "</div>";
				echo "</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display a form to the user when edit existing ad is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_edit_form($action,$awpcppagename,$editemail='',$adaccesskey='',$message=''){


	$isadmin=checkifisadmin();

		if(get_awpcp_option('onlyadmincanplaceads') && ($isadmin != '1')){

		echo "
				<div id=\"classiwrapper\">
				<p>You do not have permission to perform the function you are trying to perform. Access to this page has been denied.</p>
				</div>
			";
		}

		else {


		$quers=setup_url_structure($awpcppagename);

			if($action == 'placead'){
			$liplacead="<li class=\"postad\"><b>Placing Ad</b></li>";}
			else {$liplacead="<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>";}
			if($action== 'editad'){
			$lieditad="<li class=\"edit\"><b>Editing Ad: Step 1</b></li>";}
			else {$lieditad="<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>";}

			$checktheform="<script type=\"text/javascript\">
				function checkform() {
					var the=document.myform;

					if ((the.editemail.value==='') || (the.editemail.value.indexOf('@')==-1) || (the.editemail.value.indexOf('.',the.editemail.value.indexOf('@')+2)==-1) || (the.editemail.value.lastIndexOf('.')==the.editemail.value.length-1)) {
						alert('Either you did not enter your email address or the email address you entered is not valid.');
						the.editemail.focus();
						return false;
					}

					if (the.adaccesskey.value==='') {
						alert('You did not enter the access key. The access key was emailed to you when you first submitted your ad. You need this key in order to edit your ad.');
						the.adaccesskey.focus();
						return false;
					}

					return true;
				}

			</script>";

			if(!isset($message) || empty($message)){
			$message="<p>Please enter the email address you used when you created your ad in addition to the ad access key that was emailed to you after your ad was submitted</p>";
			}

		echo "
				<div id=\"classiwrapper\">
				<ul id=\"postsearchads\">
			";

						if(!(get_awpcp_option('onlyadmincanplaceads'))){
							echo "
									$liplacead
									$lieditad
								";
						}

						elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1)){
							echo "
									$liplacead
									$lieditad
								";
						}

		echo "
				<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
				<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
				</ul><div class=\"fixfloat\"></div>
			";

		echo "$message
		$checktheform<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">
		<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">
		<input type=\"hidden\" name=\"a\" value=\"doadedit1\">
		<p>Enter your Email address<br/>
		<input type=\"text\" name=\"editemail\" value=\"$editemail\" class=\"inputbox\"></p>
		<p>Enter your ad access key<br/>
		<input type=\"text\" name=\"adaccesskey\" value=\"$adaccesskey\" class=\"inputbox\"></p>
		<input type=\"submit\" class=\"scbutton\" value=\"Continue\"> <a href=\"?a=resendaccesskey\">Resend Ad Access Key</a>
		<br/>
		</form>

		</div>";

	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display a form to the user for resend access key request
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function resendadaccesskeyform($editemail,$awpcppagename){

global $nameofsite,$wpdb,$siteurl,$thisadminemail;
$table_name3 = $wpdb->prefix . "awpcp_ads";

if(!isset($awpcppagename) || empty($awpcppagename) )
{
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
}

		$quers=setup_url_structure($awpcppagename);

			if($action == 'placead'){
			$liplacead="<li class=\"postad\"><b>Placing Ad</b></li>";}
			else {$liplacead="<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>";}
			if($action== 'editad'){
			$lieditad="<li class=\"edit\"><b>Editing Ad: Step 1</b></li>";}
			else {$lieditad="<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>";}

			$checktheform="<script type=\"text/javascript\">
				function checkform() {
					var the=document.myform;

					if ((the.editemail.value==='') || (the.editemail.value.indexOf('@')==-1) || (the.editemail.value.indexOf('.',the.editemail.value.indexOf('@')+2)==-1) || (the.editemail.value.lastIndexOf('.')==the.editemail.value.length-1)) {
						alert('Either you did not enter your email address or the email address you entered is not valid.');
						the.editemail.focus();
						return false;
					}


					return true;
				}

			</script>";

			if(!isset($message) || empty($message)){
			$message="<p>Please enter the email address you used when you created your ad. Your access key will be sent to that email account. The email address you enter must match up with the email address we have on file.</p>";
			}

		echo "
				<div id=\"classiwrapper\">
				<ul id=\"postsearchads\">
			";

						if(!(get_awpcp_option('onlyadmincanplaceads'))){
							echo "
									$liplacead
									$lieditad
								";
						}

						elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1)){
							echo "
									$liplacead
									$lieditad
								";
						}

		echo "
				<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
				<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
				</ul><div class=\"fixfloat\"></div>
			";

if( isset($editemail) && !empty($editemail) )
{
	// Get the ad titles and access keys in the database that are associated with the email address
	$query="SELECT ad_title,ad_key,ad_contact_name FROM ".$table_name3." WHERE ad_contact_email='$editemail'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	$adtitlekeys=array();

		while ($rsrow=mysql_fetch_row($res)) {
		 	list($adtitle,$adkey,$adpostername)=$rsrow;

		 	$adtitlekeys[]="$adtitle: $adkey";

		}

		$totaladsfound=count($adtitlekeys);

		if($totaladsfound > 0 )
		{

			$resendakeymessage="Dear $adpostername<br><br>You have asked to have your ad key resent. [$totaladsfound] ads were found sharing your email address.<ol>";
			$resendakeymessagealt="Dear $adpostername\n\nYou have asked to have your ad key resent. [$totaladsfound] ads were found sharing your email address.\n\n";


			foreach ($adtitlekeys as $theadtitleandkey)
			{
		 		$resendakeymessage.="<li>$theadtitleandkey</li>";
		 		$resendakeymessagealt.="$theadtitleandkey\n";
		 	}

			$resendakeymessage.="</ol><br>Thank you for using the services provided by $nameofsite<br>$siteurl";
			$resendakeymessagealt.="\n$nameofsite\n$siteurl";

			$subject="The $nameofsite ad access key you requested";
			$from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

			if(send_email($thisadminemail,$editemail,$subject,$resendakeymessage,true)){
				echo "Your access key has been emailed to [ $editemail ].";
			}

			// If function send_mail did not work try function mail()
			elseif(mail($editemail, $subject, $resendakeymessagealt, $from_header))
			{
				echo "Your access key has been emailed to [ $editemail ]. ";
			}
			else
			{

				$host = get_awpcp_option('smtphost');
				$username = get_awpcp_option('smtpusername');
				$password = get_awpcp_option('smtppassword');

				$headers = array ('From' => $from_header,
				  'To' => $editemail,
				  'Subject' => $subject);
				$smtp = Mail::factory('smtp',
				  array ('host' => $host,
					'auth' => true,
					'username' => $username,
					'password' => $password));

				$mail = $smtp->send($editemail, $headers, $resendakeymessagealt);

				if (PEAR::isError($mail))
				{
				  echo "<div class=\"classiwrapper\">There was a problem encountered during the attempt to resend your access key. We apologize. Please try again and if the problem persists, please contact the system administrator.</div>";
				}
				else
				{
				  echo "<div class=\"classiwrapper\">Your access key has been emailed to [ $editemail ]</div>";
				}

			}
		}
		else
		{

			echo "There were no ads found registered with the email address provided";

		}
}
else
{


		echo "
		$checktheform<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">
		<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">
		<input type=\"hidden\" name=\"a\" value=\"resendaccesskey\">
		<p>Enter your Email address<br/>
		<input type=\"text\" name=\"editemail\" value=\"$editemail\" class=\"inputbox\"></p>
		<input type=\"submit\" class=\"scbutton\" value=\"Continue\">
		<br/>
		</form>
		";

	}

	echo "</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Display a form to be filled out in order to contact the ad poster
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_contact_form($adid){

$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');


$quers=setup_url_structure($awpcppagename);

$contactformcheckhumanhighnumval=get_awpcp_option('contactformcheckhumanhighnumval');

$numval1=rand(1,$contactformcheckhumanhighnumval);
$numval2=rand(1,$contactformcheckhumanhighnumval);

$thesum=($numval1 + $numval2);

if(get_awpcp_option('contactformcheckhuman') == 1){

	$conditionscheckhuman="

			if (the.checkhuman.value==='') {
				alert('You did not enter the sum of $numval1 plus $numval2. Please enter the sum of $numval1 plus $numval2');
				the.checkhuman.focus();
				return false;
			}
			if (the.checkhuman.value != $thesum) {
				alert('The value you have entered  for the sum of $numval1 plus $numval2 is incorrect. Please enter the correct sum of $numval1 plus $numval2');
				the.checkhuman.focus();
				return false;
			}

		";
} else { $conditionscheckhuman =""; }

$checktheform="<script type=\"text/javascript\">
	function checkform() {
		var the=document.myform;

		if (the.sendersname.value==='') {
			alert('You did not enter your name. Please enter your name.');
			the.sendersname.focus();
			return false;
		}
		if ((the.sendersemail.value==='') || (the.sendersemail.value.indexOf('@')==-1) || (the.sendersemail.value.indexOf('.',the.sendersemail.value.indexOf('@')+2)==-1) || (the.sendersemail.value.lastIndexOf('.')==the.sendersemail.value.length-1)) {
			alert('Either you did not enter your email address or the email address you entered is not valid.');
			the.sendersemail.focus();
			return false;
		}
		if (the.contactmessage.value==='') {
			alert('You did not enter any message. Please enter a message');
			the.contactmessage.focus();
			return false;
		}

		$conditionscheckhuman;

		return true;
	}

</script>";

if(!isset($message) || empty($message)){
$message="<p></p>";
}

echo "
		<div id=\"classiwrapper\">
		<ul id=\"postsearchads\">
	";

				$isadmin=checkifisadmin();

				if(!(get_awpcp_option('onlyadmincanplaceads'))){
					echo "
							<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
							<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
						";
				}

				elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1)){
					echo "
							<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
							<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
						";
				}

echo "
		<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
		<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
		</ul><div class=\"fixfloat\"></div>
	";

$theadtitle=get_adtitle($adid);
$modtitle=cleanstring($theadtitle);
$modtitle=add_dashes($modtitle);

$permastruc=get_option('permalink_structure');

			if( get_awpcp_option('seofriendlyurls') )
			{
				if(isset($permastruc) && !empty($permastruc))
				{
					$thead="<a href=\"".$quers."showad/$adid/$modtitle\">$theadtitle</a>";
				}
				else
				{
					$thead="<a href=\"".$quers."showad&id=$adid\">$theadtitle</a>";
				}
			}

			else
			{
				$thead="<a href=\"".$quers."showad&id=$adid\">$theadtitle</a>";
			}


echo "<p>You are responding to $thead.</p>$message
$checktheform<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">
<input type=\"hidden\" name=\"adid\" value=\"$adid\">
<input type=\"hidden\" name=\"a\" value=\"docontact1\">
<input type=\"hidden\" name=\"numval1\" value=\"$numval1\">
<input type=\"hidden\" name=\"numval2\" value=\"$numval2\">
<p>Your Name<br/>
<input type=\"text\" name=\"sendersname\" value=\"$sendersname\" class=\"inputbox\"></p>
<p>Enter your Email address<br/>
<input type=\"text\" name=\"sendersemail\" value=\"$sendersemail\" class=\"inputbox\"></p>
<p>Enter your message below<br/>
<textarea name=\"contactmessage\" rows=\"5\" cols=\"50\">$contactmessage</textarea></p>";

if(get_awpcp_option('contactformcheckhuman') == 1){
echo "<p>Enter the value of the following sum: <b>$numval1 + $numval2</b><br>
<input type=\"text\" name=\"checkhuman\" value=\"$checkhuman\" size=\"5\"></p>";
}

echo "<input type=\"submit\" class=\"scbutton\" value=\"Continue\">
<br/></form></div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Process the request to contact the poster of the ad
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg){
global $nameofsite,$siteurl;

	$error=false;
	$adidmsg='';
	$sendersnamemsg='';
	$checkhumanmsg='';
	$sendersemailmsg='';
	$contactmessagemsg='';
	$sumwrongmsg='';
	$sendersemailwrongmsg='';

	$thesum=($numval1 +  $numval2);

	if(!isset($adid) || empty($adid)){
		$error=true;
		$adidmsg="<li>The ad could not be identified due to a missing ad identification number</li>";
	}
	if(!isset($sendersname) || empty($sendersname)){
		$error=true;
		$sendersnamemsg="<li>You did not enter your name. You must include a name for this message to be relayed on your behalf</li>";
	}

	if(get_awpcp_option('contactformcheckhuman') == 1){

		if(!isset($checkhuman) || empty($checkhuman)){
			$error=true;
			$checkhumanmsg="<li>You did not enter the value for the sum of <b>$numval1 plus $numval2</b>. Please enter the value for the sum of <b>$numval1 plus $numval2</b> </li>";
		}
		if($checkhuman != $thesum){
				$error=true;
				$sumwrongmsg="<li>The value you entered for the sum of <b>$numval1 plus $numval2</b> was incorrect</li>";
		}
	}

	if(!isset($contactmessage) || empty($contactmessage)){
		$error=true;
		$contactmessagemsg="<li>There was no text entered for your message.</li>";
	}

	if(!isset($sendersemail) || empty($sendersemail)){
		$error=true;
		$sendersemailmsg="<li>You did not enter your name. You must include a name for this message to be relayed on your behalf</li>";
	}
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sendersemail)){
		$error=true;
		$sendersemailwrongmsg="<li>The email address you entered was not a valid email address. Please check for errors and try again.</li>";
	}

	if($error){
		$ermsg="<p>There has been an error found. Your message has not been sent. Please review the list of problems, correct them then try to send your message again.</p>";
		$ermsg.="<b>The errors:</b><br/>";
		$ermsg.="<ul>$adidmsg $sendersnamemsg $checkhumanmsg $contactmessagemsg $sumwrongmsg $sendersemailmsg $sendersemailwrongmsg</ul>";

		processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);
	}
	else {
		$sendersname=strip_html_tags($sendersname);
		$contactmessage=strip_html_tags($contactmessage);
		$theadtitle=get_adtitle($adid);
		$sendtoemail=get_adposteremail($adid);
		$contactformsubjectline=get_awpcp_option('contactformsubjectline');
		if(isset($contactformsubjectline) && !empty($contactformsubjectline) )
		{
			$subject="$contactformsubjectline";
		}
		else
		{
			$subject="Re: $theadtitle";
		}
		$contactformbodymessage=get_awpcp_option('contactformbodymessage');
		if(isset($contactformbodymessage) && !empty($contactformbodymessage) )
		{
			$body="$contactformbodymessage";
			$body.="<br><br>Message<br><br>$contactmessage";
			$body.="<br><br>$nameofsite<br>$siteurl";
			$bodyalt="strip_html_tags($contactformbodymessage)";
			$bodyalt.="\n\nMessage\n\n$contactmessage";
			$bodyalt.="\n\n$nameofsite\n$siteurl";
		}
		else
		{
			$body="This is a message in response to your ad posted at $nameofsite at $siteurl<br/><br/>";
			$body.="$contactmessage";
			$body.="<br><br>$nameofsite<br>$siteurl";
			$bodyalt="This is a message in response to your ad posted at $nameofsite at $siteurl\n\n";
			$bodyalt.="$contactmessage\n";
			$bodyalt.="\n\n$nameofsite\n$siteurl";
		}

		$from_header = "From: ". $sendersname . " <" . $sendersemail . ">\r\n";

		if(send_email($sendersemail,$sendtoemail,$subject,$body,true)){
			echo "<div id=\"classiwrapper\">Your message has been sent. Thank you for using $nameofsite</div>";
		}

		// If function send_mail did not work try function mail()
		elseif(mail($sendtoemail, $subject, $bodyalt, $from_header))
		{
			echo "<div id=\"classiwrapper\">Your message has been sent. Thank you for using $nameofsite</div>";
		}

		else
		{
				$host = get_awpcp_option('smtphost');
				$username = get_awpcp_option('smtpusername');
				$password = get_awpcp_option('smtppassword');

				$headers = array ('From' => $from_header,
				  'To' => $sendtoemail,
				  'Subject' => $subject);
				$smtp = Mail::factory('smtp',
				  array ('host' => $host,
					'auth' => true,
					'username' => $username,
					'password' => $password));

				$mail = $smtp->send($sendtoemail, $headers, $bodyalt);

				if (PEAR::isError($mail))
				{
				  echo("<p>There was a problem encountered during the attempt to contact the ad poster. We apologize. Please try again and if the problem persists, please contact the system administrator.</p>");
				}
				else
				{
				  echo "<div id=\"classiwrapper\">Your message has been sent. Thank you for using $nameofsite</div>";
				}

		}
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display the ad search form
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_search_form($keywordphrase,$searchname,$searchcity,$searchstate,$searchcountry,$searchcountyvillage,$searchcategory,$searchpricemin,$searchpricemax,$message){

global $hasregionsmodule;

$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');

$quers=setup_url_structure($awpcppagename);


$checktheform="<script type=\"text/javascript\">
	function checkform()
	{
		var the=document.myform;
		if (the.keywordphrase.value==='')
		{
			if( (the.searchname.value==='') && (the.searchcity.value==='') && (the.searchstate.value==='') && (the.searchcountry.value==='') && (the.searchcountyvillage.value==='') && (the.searchcategory.value==='') && (the.searchpricemin.value==='') && (the.searchpricemax.value==='') )
			{
				alert('You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for.');
				the.keywordphrase.focus();
				return false;
			}
		}

		return true;
	}

</script>";

				if( isset($_SESSION['regioncountryID']) || isset($_SESSION['regionstatownID']) || isset($_SESSION['regioncityID']) )
				{
					$searchinginregion='';

					if(isset($_SESSION['regioncityID']) && !empty($_SESSION['regioncityID']))
					{
						$regioncityname=get_theawpcpregionname($_SESSION['regioncityID']);
						$searchinginregion.="$regioncityname";
					}
					if(isset($_SESSION['regionstatownID']) && !empty($_SESSION['regionstatownID']))
					{
						$regionstatownname=get_theawpcpregionname($_SESSION['regionstatownID']);
						$searchinginregion.=" $regionstatownname";
					}
					if(isset($_SESSION['regioncountryID']) && !empty($_SESSION['regioncountryID']))
					{
						$regioncountryname=get_theawpcpregionname($_SESSION['regioncountryID']);
						$searchinginregion.=" $regioncountryname";
					}

					$clearthesessionlink="<p>You are searching in: $searchinginregion.  <a href=\"?a=cregs&loadwhich=s\">Search in different location</a></p>";
				}
				else
				{
					$clearthesessionlink='';
				}

if(!isset($message) || empty($message))
{
	$message="<p>Use the form below to conduct a broad or narrow search. For a broader search enter fewer parameters. For a narrower search enter as many parameters as needed to limit your search to a specific criteria. $clearthesessionlink</p>";
}

$allcategories=get_categorynameidall($searchcategory);

if(!isset($adcontact_country) || empty($adcontact_country) )
{
	if( isset($_SESSION['regioncountryID']) && !empty ($_SESSION['regioncountryID']) )
	{
		$adcontact_country=$_SESSION['regioncountryID'];
	}
}

if(!isset($adcontact_state) || empty($adcontact_state) )
{
	if( isset($_SESSION['regionstatownID']) && !empty ($_SESSION['regionstatownID']) )
	{
		$adcontact_state=$_SESSION['regionstatownID'];
	}
}

if(!isset($adcontact_city) || empty($adcontact_city) )
{
	if( isset($_SESSION['regioncityID']) && !empty ($_SESSION['regioncityID']) )
	{
		$adcontact_city=$_SESSION['regioncityID'];
	}
}

echo "
		<div id=\"classiwrapper\">
		<ul id=\"postsearchads\">
	";
				$isadmin=checkifisadmin();

				if(!(get_awpcp_option('onlyadmincanplaceads')))
				{
					echo "
							<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
							<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
						";
				}

				elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1))
				{
					echo "
							<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
							<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
						";
				}
echo "
		<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
		<li class=\"searchcads\"><b>Searching Ads</b></li>
		</ul><div class=\"fixfloat\"></div>
	";

echo "$message
$checktheform<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">
<input type=\"hidden\" name=\"a\" value=\"dosearch\">
<p>Search for ads containing this word or phrase:<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"keywordphrase\" value=\"$keywordphrase\"></p>
<p>Search in Category<br><select name=\"searchcategory\"><option value=\"\">Select Option</option>$allcategories</select></p>
<p>For Ads Posted By<br/><select name=\"searchname\"><option value=\"\">Select Option</option>";
create_ad_postedby_list($searchname);
echo "</select></p>";


if(get_awpcp_option(displaypricefield) == 1)
{
	if( price_field_has_values() )
	{


		echo "<p>Min Price
		<select name=\"searchpricemin\"><option value=\"\">Select</option>";
		create_price_dropdownlist_min($searchpricemin);
		echo"</select> Max Price

		<select name=\"searchpricemax\"><option value=\"\">Select</option>";
		create_price_dropdownlist_max($searchpricemax);
		echo "</select></p>";
	}
	else
	{
		echo "<input type=\"hidden\" name=\"searchpricemin\" value=\"\">";
		echo "<input type=\"hidden\" name=\"searchpricemax\" value=\"\">";
	}
}

if(get_awpcp_option(displaycountryfield) == 1){

	echo "<p>Refine to Country<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_countries_exist() )
			{

				echo "<select name=\"searchcountry\">";
				if(!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
				{
					echo "<option value=\"\">Select Option</option>";
				}

				$opsitemregcountrylist=awpcp_region_create_country_list($searchcountry,$byvalue='');
				echo "$opsitemregcountrylist";
				echo "</select>";
			}
			else
			{

				if(!isset($adcontact_country) || empty($adcontact_country) )
				{
					if(!get_awpcp_option('buildsearchdropdownlists'))
					{
						echo "
							(separate countries by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
						";
					}
					elseif(get_awpcp_option('buildsearchdropdownlists'))
					{
						if( adstablehascountries() )
						{

							echo "<select name=\"searchcountry\">";
							if(!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
							{
								echo "<option value=\"\">Select Option</option>";
							}
							create_dropdown_from_current_countries($searchcountry);
							echo "</select>";
						}
						else
						{
							echo "
								(separate countries by commas)<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
							";
						}
					}
				}
				else
				{
						echo "
							(separate countries by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "
				(separate countries by commas)<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
				";
			}
			elseif(get_awpcp_option('buildsearchdropdownlists'))
			{
				if( adstablehascountries() )
				{

					echo "<select name=\"searchcountry\">";
					if(!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
					{
						echo "<option value=\"\">Select Option</option>";
					}
					create_dropdown_from_current_countries($searchcountry);
					echo "</select>";
				}
				else
				{
					echo "
						(separate countries by commas)<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
					";
				}
			}
		}

	echo "</p>";
}

if(get_awpcp_option(displaystatefield) == 1){

	echo "<p>Refine to State<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_states_exist($adcontact_country) )
			{

				echo "<select name=\"searchstate\">";
				if(!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
				{
					echo "<option value=\"\">Select Option</option>";
				}
				$opsitemregstatelist=awpcp_region_create_statown_list($searchstate,$byvalue='',$adcontact_country);
				echo "$opsitemregstatelist";
				echo "</select>";
			}
			else
			{

				if( !isset($adcontact_country) || empty($adcontact_country) )
				{
					if(!get_awpcp_option('buildsearchdropdownlists'))
					{
						echo "
							(separate states by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
						";
					}
					elseif(get_awpcp_option('buildsearchdropdownlists'))
					{

						if( adstablehasstates() )
						{

							echo "<select name=\"searchstate\">";
							if(!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
							{
								echo "<option value=\"\">Select Option</option>";
							}
							create_dropdown_from_current_states($searchstate);
							echo "</select>";

						}
						else
						{
							echo "
								(separate states by commas)<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
							";
						}
					}
				}
				else
				{
						echo "
							(separate states by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "
				(separate states by commas)<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
				";
			}
			elseif(get_awpcp_option('buildsearchdropdownlists'))
			{
				if( adstablehasstates() )
				{

					echo "<select name=\"searchstate\">";
					if(!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
					{
						echo "<option value=\"\">Select Option</option>";
					}
					create_dropdown_from_current_states($searchstate);
					echo "</select>";

				}
				else
				{
					echo "
						(separate states by commas)<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
					";
				}
			}
		}

	echo "</p>";

}

if(get_awpcp_option(displaycityfield) == 1)
{
	echo "<p>Refine to City<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_cities_exist($adcontact_state) )
			{

				echo "<select name=\"searchcity\">";
				if(!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
				{
					echo "<option value=\"\">Select Option</option>";
				}
				$opsitemregcitylist=awpcp_region_create_city_list($searchcity,$byvalue='',$adcontact_state);
				echo "$opsitemregcitylist";
				echo "</select>";
			}
			else
			{
				if( !isset($adcontact_state) || empty($adcontact_state) )
				{
					if(!get_awpcp_option('buildsearchdropdownlists'))
					{
						echo "
						(separate cities by commas)<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\">
						";
					}
					elseif(get_awpcp_option('buildsearchdropdownlists'))
					{

						if( adstablehascities() )
						{

							echo "<select name=\"searchcity\">";
							if(!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
							{
								echo "<option value=\"\">Select Option</option>";
							}
							create_dropdown_from_current_cities($searchcity);
							echo "</select>";

						}
						else
						{
							echo "
								(separate cities by commas)<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\">
							";
						}
					}
				}
				else
				{
						echo "
							(separate cities by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "
				(separate cities by commas)<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchcity\">
				";
			}
			elseif(get_awpcp_option('buildsearchdropdownlists'))
			{
				if( adstablehascities() )
				{

					echo "<select name=\"searchcity\">";
					if(!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
					{
						echo "<option value=\"\">Select Option</option>";
					}
					create_dropdown_from_current_cities($searchcity);
					echo "</select>";

				}
				else
				{
					echo "
						(separate cities by commas)<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchcity\">
					";
				}
			}
		}

	echo "</p>";
}


if(get_awpcp_option(displaycountyvillagefield) == 1)
{
	echo "<p>Refine to County/Village/Other<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_counties_exist($adcontact_city) )
			{

				echo "<select name=\"searchcountyvillage\"><option value=\"\">Select Option</option>";
				$opsitemregcountyvillagelist=awpcp_region_create_county_village_list($searchcountyvillage);
				echo "$opsitemregcountyvillagelist";
				echo "</select>";
			}
			else
			{

				if( !isset($adcontact_city) || empty($adcontact_city) )
				{

					if(!get_awpcp_option('buildsearchdropdownlists'))
					{
						echo "
						(separate cities by commas)<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
						";
					}
					elseif(get_awpcp_option('buildsearchdropdownlists'))
					{
						if( adstablehascounties() )
						{

							echo "<select name=\"searchcountyvillage\"><option value=\"\">Select Option</option>";
							create_dropdown_from_current_counties($searchcountyvillage);
							echo "</select>";

						}
						else
						{
							echo "
								(separate counties by commas)<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
							";
						}
					}
				}
				else
				{
						echo "
							(separate counties by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "
				(separate cities by commas)<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
				";
			}
			elseif(get_awpcp_option('buildsearchdropdownlists'))
			{
				if( adstablehascounties() )
				{

					echo "<select name=\"searchcountyvillage\"><option value=\"\">Select Option</option>";
					create_dropdown_from_current_counties($searchcountyvillage);
					echo "</select>";

				}
				else
				{
					echo "
						(separate counties by commas)<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
					";
				}
			}
		}

	echo "</p>";
}

	echo "<div align=\"center\"><input type=\"submit\" class=\"scbutton\" value=\"Start Search\"></div></form>";
	echo "</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function dosearch() {

		global $wpdb;
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$keywordphrase=addslashes_mq($_REQUEST['keywordphrase']);
		$searchname=addslashes_mq($_REQUEST['searchname']);
		$searchcity=addslashes_mq($_REQUEST['searchcity']);
		$searchstate=addslashes_mq($_REQUEST['searchstate']);
		$searchcountry=addslashes_mq($_REQUEST['searchcountry']);
		$searchcategory=addslashes_mq($_REQUEST['searchcategory']);
		$searchpricemin=addslashes_mq($_REQUEST['searchpricemin']);
		$searchpricemax=addslashes_mq($_REQUEST['searchpricemax']);
		$searchcountyvillage=addslashes_mq($_REQUEST['searchcountyvillage']);

		$message='';

		$error=false;
		$theerrorslist="<h3>Cannot process your request due to the following error:</h3><ul>";
		if(!isset($keywordphrase) && empty($keywordphrase) &&
			!isset($searchname) && empty($searchname) &&
				!isset($searchcity) && empty($searchcity) &&
				 	!isset($searchstate) && empty($searchstate) &&
				  		!isset($searchcountry) && empty($searchcountry) &&
				  			!isset($searchpricemin) && empty($searchpricemin) &&
				  	 			!isset($searchpricemax) && empty($searchpricemax) &&
				  					!isset($searchcategory) && empty ($searchcategory) &&
				  						!isset($searchcountyvillage) && wmpty ($searchcountyvillage)) {
				  	$error=true;
					$theerrorslist.="<li>You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for.</li>";
			}

			if( !empty($searchpricemin) )
			{
				if( !is_numeric($searchpricemin) )
		   		{
						$error=true;
						$theerrorslist.="<li>You have entered an invalid minimum price. Make sure your price contains numbers only. Please do not include currency symbols.</li>";
				}
			}

		  	if( !empty($searchpricemax) )
		  	{
		  		if(	!is_numeric($searchpricemax) )
		   		{
						$error=true;
						$theerrorslist.="<li>You have entered an invalid maximum price. Make sure your price contains numbers only. Please do not include currency symbols.</li>";
				}
			}

			if( empty($searchpricemin) && !empty($searchpricemax) )
			{
				$searchpricemin=1;
			}

			$theerrorslist.="</ul>";
			$message="<p>$theerrorslist</p>";

		if($error){
			load_ad_search_form($keywordphrase,$searchname,$searchcity,$searchstate,$searchcountry,$searchcountyvillage,$searchcategory,$searchpricemin,$searchpricemax,$message);
		}

		else {
		$where="disabled ='0'";
		if(isset($keywordphrase) && !empty($keywordphrase)){
			$where.=" AND MATCH (ad_title,ad_details) AGAINST (\"$keywordphrase\")";
		}

		if(isset($searchname) && !empty($searchname)){
			$where.=" AND ad_contact_name = '$searchname'";
		}

		if(isset($searchcity) && !empty($searchcity)){

			if(is_array( $searchcity ) )
			{

				$cities=explode(",",$searchcity);
				$city=array();

				for ($i=0;isset($cities[$i]);++$i) {
					$city[]=$cities[$i];
					$citieslist=join("','",$city);
				}

				$where.=" AND ad_city IN ('$citieslist')";
			}
			else
			{
				$where.=" AND ad_city ='$searchcity'";
			}

		}

		if(isset($searchstate) && !empty($searchstate))
		{
			if(is_array( $searchstate ) )
			{

				$states=explode(",",$searchstate);
				$state=array();

				for ($i=0;isset($states[$i]);++$i) {
					$state[]=$states[$i];
					$stateslist=join("','",$state);
				}
					$where.=" AND ad_state IN ('$stateslist')";
			}
			else
			{
				$where.=" AND ad_state ='$searchstate'";
			}
		}

		if(isset($searchcountry) && !empty($searchcountry)){

			if(is_array( $searchcountry ) )
			{
				$countries=explode(",",$searchcountry);
				$country=array();

				for ($i=0;isset($countries[$i]);++$i) {
					$country[]=$countries[$i];
					$countrieslist=join("','",$country);
				}
					$where.=" AND ad_country IN ('$countrieslist')";
			}
			else
			{
				$where.=" AND ad_country ='$searchcountry'";
			}
		}

		if(isset($searchcountyvillage) && !empty($searchcountyvillage)){

			if(is_array( $searchcountyvillage ) )
			{

				$counties=explode(",",$searchcountyvillage);
				$county=array();

				for ($i=0;isset($counties[$i]);++$i) {
					$county[]=$counties[$i];
					$countieslist=join("','",$county);
				}
					$where.=" AND ad_county_village IN ('$countieslist')";
			}
			else
			{
				$where.=" AND ad_county_village ='$searchcountyvillage'";
			}

		}

		if(isset($searchcategory) && !empty($searchcategory)){
			$where.=" AND ad_category_id = '$searchcategory' OR ad_category_parent_id = '$searchcategory'";
		}

		if(isset($searchpricemin) && !empty($searchpricemin)){
			$searchpricemincents=($searchpricemin * 100);
			$where.=" AND ad_item_price >= '$searchpricemincents'";
		}

		if(isset($searchpricemax) && !empty($searchpricemax)){
			$searchpricemaxcents=($searchpricemax * 100);
			$where.=" AND ad_item_price <= '$searchpricemaxcents'";
		}

		display_ads($where,$byl='',$hidepager='');

		}


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: process first step of edit ad request
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function editadstep1($adaccesskey,$editemail,$awpcppagename){

		global $wpdb;
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_id,adterm_id FROM ".$table_name3." WHERE ad_key='$adaccesskey' AND ad_contact_email='$editemail'";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		 	while ($rsrow=mysql_fetch_row($res)) {
		 		list($adid,$adtermid)=$rsrow;
		}

		if(isset($adid) && !empty($adid)){
			load_ad_post_form($adid,$action='editad',$awpcppagename,$adtermid,$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
		}

		else {
		$message="<p class=\"messagealert\">The information you have entered does not match the information on file. Please make sure you are using the same email address you used to post your ad and the exact access key that was emailed to you when you posted your ad.</p>";
			load_ad_edit_form($action='editad',$awpcppagename,$editemail,$adaccesskey,$message);
		}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Process ad submission
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2)
{


		global $wpdb;
		$table_name2 = $wpdb->prefix . "awpcp_adfees";
		$table_name3 = $wpdb->prefix . "awpcp_ads";
		$table_name5 = $wpdb->prefix . "awpcp_adphotos";

		$permastruc=get_option(permalink_structure);

		// Check the form to make sure no required information is missing

			$adtitlemsg='';
			$adcnamemsg='';
			$adcemailmsg1='';
			$adcemailmsg2='';
			$adcphonemsg='';
			$adcitymsg='';
			$adstatemsg='';
			$adcountrymsg='';
			$addetailsmsg='';
			$adcategorymsg='';
			$adpaymethodmsg='';
			$adtermidmsg='';
			$aditempricemsg1='';
			$aditempricemsg2='';
			$adcountyvillagemsg='';
			$websiteurlmsg1='';
			$websiteurlmsg2='';
			$checkhumanmsg='';
			$sumwrongmsg='';

			$error=false;


		// Check for ad title
		if(!isset($adtitle) || empty($adtitle)){
			$error=true;
			$adtitlemsg="<li>You did not enter a title for your ad.</li>";
		}

		// Check for ad details
		if(!isset($addetails) || empty($addetails)){
			$error=true;
			$addetailsmsg="<li>You did not enter any text for your ad. Please enter some text for your ad.</li>";
		}

		// Check for ad category
		if(!isset($adcategory) || empty($adcategory)){
			$error=true;
			$adcategorymsg="<li>You did not select a category for your ad. Please select a category for your ad.</li>";
		}

		// Check for ad poster's name
		if(!isset($adcontact_name) || empty($adcontact_name)){
			$error=true;
			$adcnamemsg="<li>You did not enter your name. Your name is required.</li>";
		}

		// Check for ad poster's email address
		if(!isset($adcontact_email) || empty($adcontact_email)){
			$error=true;
			$adcemailmsg1="<li>You did not enter your email. Your email is required.</li>";
		}

		// Check if email address entered is in a valid email address format
		if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $adcontact_email)){
			$error=true;
			$adcemailmsg2="<li>The email address you entered was not a valid email address. Please check for errors and try again.</li>";
		}

		// If phone field is checked and required make sure phone value was entered
		if((get_awpcp_option('displayphonefield') == 1)
		&&(get_awpcp_option('displayphonefieldreqop') == 1)){
			if(!isset($adcontact_phone) || empty($adcontact_phone)) {
				$error=true;
				$adcphonemsg="<li>You did not enter your phone number. Your phone number is required.</li>";
			}
		}

		// If city field is checked and required make sure city value was entered
		if((get_awpcp_option('displaycityfield') == 1)
		&&(get_awpcp_option('displaycityfieldreqop') == 1)){
			if(!isset($adcontact_city) || empty($adcontact_city)){
				$error=true;
				$adcitymsg="<li>You did not enter your city. Your city is required.</li>";
			}
		}

		// If state field is checked and required make sure state value was entered
		if((get_awpcp_option('displaystatefield') == 1)
		&&(get_awpcp_option('displaystatefieldreqop') == 1)){
			if(!isset($adcontact_state) || empty($adcontact_state)){
				$error=true;
				$adstatemsg="<li>You did not enter your state. Your state is required.</li>";
			}
		}

		// If country field is checked and required make sure country value was entered
		if((get_awpcp_option('displaycountryfield') == 1)
		&&(get_awpcp_option('displaycountryfieldreqop') == 1)){
			if(!isset($adcontact_country) || empty($adcontact_country)){
				$error=true;
				$adcountrymsg="<li>You did not enter your country. Your country is required.</li>";
			}
		}

		// If county/village field is checked and required make sure county/village value was entered
		if((get_awpcp_option('displaycountyvillagefield') == 1)
		&&(get_awpcp_option('displaycountyvillagefieldreqop') == 1)){
			if(!isset($ad_county_village) || empty($ad_county_village)){
				$error=true;
				$adcountyvillagemsg="<li>You did not enter your county/village. Your county/village is required.</li>";
			}
		}

		if( $adaction == 'placead' )
		{
			// If running in pay mode make sure a payment method has been checked
			if(get_awpcp_option(freepay) == 1)
			{
				if(get_adfee_amount($adterm_id) > 0)
				{
					if(!isset($adpaymethod) || empty($adpaymethod))
					{
						$error=true;
						$adpaymethodmsg="<li>You did not select your payment method. The information is required.</li>";
					}
				}
			}

			// If running in pay mode make sure an ad term has been selected
			if(get_awpcp_option(freepay) == 1) {
				if(!($adaction == 'delete') || ($adaction == 'editad')) {
					if(!isset($adterm_id) || empty ($adterm_id)) {
						$error=true;
						$adtermidmsg="<li>You did not select an ad term. The information is required.</li>";
					}
				}
			}
		}

		// If price field is checked and required make sure a price has been entered
		if((get_awpcp_option('displaypricefield') == 1)
		&&(get_awpcp_option('displaypricefieldreqop') == 1))
		{
			if(!isset($ad_item_price) || empty($ad_item_price))
			{
				$error=true;
				$aditempricemsg1="<li>You did not enter the price of your item. The item price is required.</li>";
			}
		}

		// Make sure the item price is a numerical value
		if((get_awpcp_option('displaypricefield') == 1)
		&&(get_awpcp_option('displaypricefieldreqop') == 1))
		{
  			if( !is_numeric($ad_item_price) )
   			{
				$error=true;
				$aditempricemsg2="<li>You have entered an invalid item price. Make sure your price contains numbers only. Please do not include currency symbols.</li>";
			}
		}

		// If website field is checked and required make sure website value was entered
		if((get_awpcp_option('displaywebsitefield') == 1)
		&&(get_awpcp_option('displaywebsitefieldreqop') == 1)){
			if(!isset($websiteurl) || empty($websiteurl)) {
				$error=true;
				$websiteurlmsg1="<li>You did not enter your website address. Your website address is required.</li>";
			}
		}

		//If they have submitted a website address make sure it is correctly formatted

		if(isset($websiteurl) && !empty($websiteurl) )
		{
			if( !isValidURL($websiteurl) )
			{
				$error=true;
				$websiteurlmsg2="<li>Your website address is not properly formatted. Please make sure you have included the http:// part of your website address</li>";
			}
		}

		$thesum=($numval1 +  $numval2);

		if(get_awpcp_option('contactformcheckhuman') == 1)
		{

			if(!isset($checkhuman) || empty($checkhuman))
			{
				$error=true;
				$checkhumanmsg="<li>You did not enter the value for the sum of <b>$numval1 plus $numval2</b>. Please enter the value for the sum of <b>$numval1 plus $numval2</b> </li>";
			}
			if($checkhuman != $thesum){
				$error=true;
				$sumwrongmsg="<li>The value you entered for the sum of <b>$numval1 plus $numval2</b> was incorrect</li>";
			}
		}


		if($error){
			$ermsg="<p>There has been an error found. Your message has not been sent. Please review the list of problems, correct them then try to send your message again.</p>";
			$ermsg.="<b>The errors:</b><br/>";
			$ermsg.="<ul>$adtitlemsg $adcategorymsg $adcnamemsg $adcemailmsg1 $adcemailmsg2 $adcphonemsg $adcitymsg $adstatemsg $adcountrymsg $addetailsmsg $adpaymethodmsg $adtermidmsg $aditempricemsg1 $aditempricemsg2 $websiteurlmsg1 $websiteurlmsg2 $checkhumanmsg $sumwrongmsg</ul>";

			load_ad_post_form($adid,$action,$awpcppagename,$adterm_id,$editemail,$adkey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);
		}

		else {


		// Process ad delete request

		if($adaction == 'delete'){
			deletead($adid,$adkey,$editemail);
		}

		// Process ad edit request

		else {

			if($adaction == 'editad'){

			$isadmin=checkifisadmin();

				$qdisabled='';
				if(!(is_admin()))
				{
					if(get_awpcp_option('adapprove') == 1)
					{
						$disabled='1';
					}
					else
					{
						$disabled='0';
					}
					$qdisabled="disabled='$disabled',";
				}

				$adcategory_parent_id=get_cat_parent_ID($adcategory);
				$itempriceincents=($ad_item_price * 100);

				$query="UPDATE ".$table_name3." SET ad_category_id='$adcategory',ad_category_parent_id='$adcategory_parent_id',ad_title='$adtitle',
				ad_details='$addetails',websiteurl='$websiteurl',ad_contact_phone='$adcontact_phone',ad_contact_name='$adcontact_name',ad_contact_email='$adcontact_email',ad_city='$adcontact_city',ad_state='$adcontact_state',ad_country='$adcontact_country',ad_county_village='$ad_county_village',ad_item_price='$itempriceincents',
				$qdisabled ad_last_updated=now() WHERE ad_id='$adid' AND ad_key='$adkey'";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


				if($isadmin == 1 && is_admin())
				{
					$message="The ad has been edited successfully. <a href=\"?page=Manage1&offset=$offset&results=$results\">Back to view listings</a>";
					printmessage($message);
				}

				else
				{

					if(get_awpcp_option('imagesallowdisallow'))
					{
						if(get_awpcp_option('freepay') == 1)
						{
							$totalimagesallowed=get_numimgsallowed($adterm_id);
						}
						elseif(ad_term_id_set($adid))
						{
							$totalimagesallowed=get_numimgsallowed($adterm_id);
						}
						else
						{
							$totalimagesallowed=get_awpcp_option('imagesallowedfree');

						}


						if( $totalimagesallowed > 0 )
						{
							editimages($adterm_id,$adid,$adkey,$editemail);
						}
						else
						{
							$messagetouser="Your changes have been saved";

							echo "<h3>$messagetouser</h3>";
							showad($adid);

						}
					}
					else
					{
						$messagetouser="Your changes have been saved";

						echo "<h3>$messagetouser</h3>";
						showad($adid);

					}
				}

		}

		// Process new ad

		else {

			$key=time();

			if(isset($adterm_id) && !empty($adterm_id))
			{
				$feeamt=get_adfee_amount($adterm_id);
			}
			else
			{
				$feeamt=0;
			}

			if(get_awpcp_option('adapprove') == 1)
			{
				$disabled='1';
			}
			else
			{
				$disabled='0';
			}

			if($disabled == 0)
			{

				if(get_awpcp_option('freepay') == 1)
				{

					if($feeamt <= '0')
					{
						$disabled='0';
					}
					else
					{
						$disabled='1';
					}
				}
			}


				$adexpireafter='';
				$adstartdate=mktime();
				$adexpireafter=get_awpcp_option('addurationfreemode');

					if($adexpireafter == 0)
					{
						$adexpireafter=9125;
					}
					else
					{
						$adexpireafter=$adexpireafter;
					}



			$adcategory_parent_id=get_cat_parent_ID($adcategory);
			$itempriceincents=($ad_item_price * 100);



				$query="INSERT INTO ".$table_name3." SET ad_category_id='$adcategory',ad_category_parent_id='$adcategory_parent_id',ad_title='$adtitle',ad_details='$addetails',ad_contact_phone='$adcontact_phone',ad_contact_name='$adcontact_name',ad_contact_email='$adcontact_email',ad_city='$adcontact_city',ad_state='$adcontact_state',ad_country='$adcontact_country',ad_county_village='$ad_county_village',ad_item_price='$itempriceincents',websiteurl='$websiteurl',";

				if( isset($adterm_id) && !empty($adterm_id) )
				{
					$query.="adterm_id='$adterm_id',";
				}
				else
				{
					$query.="adterm_id='0',";
				}


				$query.="ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $adexpireafter DAY,disabled='$disabled',ad_key='$key',ad_transaction_id='',ad_postdate=now()";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

				$ad_id=mysql_insert_id();


				////////////////////////////////////////////////////////////////////////////
				// Continue after inserting new ad into database
				////////////////////////////////////////////////////////////////////////////



				if(get_awpcp_option('freepay') == 1)
				{


					$uploadandpay='';


					///////////////////////////////////////////////////////////////////////////////////////////////////
					// Step:1 Find out how many images are allowed for the selected ad term if allow images is on
					///////////////////////////////////////////////////////////////////////////////////////////////////

					if(get_awpcp_option('imagesallowdisallow') == 1)
					{

						if(get_awpcp_option('freepay') == 1)
						{
							$numimgsallowed=get_numimgsallowed($adterm_id);
						}
						else
						{
							$numimgsallowed=get_awpcp_option('imagesallowedfree');
						}

						$feeamt=get_adfee_amount($adterm_id);

					}

					if( $numimgsallowed > 0)
					{
						$txtuploadimages1="| Upload Images";
					}
					else
					{
						$txtuploadimages1="";
					}

					if($feeamt <= 0)
					{
						$txtpayfee='';
					}
					else
					{
						$txtpayfee=" | Pay Fee";
					}

					$uploadandpay="<h2>Step 2: Review Ad $txtuploadimages1 $txtpayfee</h2>";

					////////////////////////////////////////////////////////////////////////////////////
					// Step:2 Show a sample of how the ad is going to look
					////////////////////////////////////////////////////////////////////////////////////

					if(!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="";}
					if(!isset($adcontact_phone) || empty($adcontact_phone))
					{
						$adcontactphone="";
					}
					else
					{
						$adcontactphone="Phone: $adcontact_phone";
					}

					if( empty($adcontact_city) && empty($adcontact_state) && empty($adcontact_country) && empty($ad_county_village))
					{
						$location="";
					}
					else
					{
						$location="Location: ";

						if( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							//city is set, state is set, country is set
							$location.="$adcontact_city, $adcontact_state $adcontact_country";
						}
						elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( !isset($adcontact_country) || empty($adcontact_country) ) )
						{
							//city is set, state is set, country is missing
							$location.="$adcontact_city, $adcontact_state";
						}
						elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && (!isset($adcontact_state) || empty($adcontact_state)) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							// city is set, state is missing and country is set
							$location.="$adcontact_city, $adcontact_country";
						}
						elseif( (!isset($adcontact_city) || empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							//city is missing but state is set and country is set
							$location.="$adcontact_state, $adcontact_country";
						}
						elseif( (isset($adcontact_city) && !empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) && (!isset($adcontact_country) || empty($adcontact_country)) )
						{
							//  city is set, state is missing, country is missing
							$location.="$adcontact_city";
						}
						elseif( (isset($adcontact_state) && !empty($adcontact_state)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_country) || empty($adcontact_country)) )
						{
							//  state is set, city is missing, country is missing
							$location.="$adcontact_state";
						}
						elseif( (isset($adcontact_country) && !empty($adcontact_country)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) )
						{
							//  country is set, city is missing, state is missing
							$location.="$adcontact_state";
						}
						else
						{
							if(isset($adcontact_city) && !empty($adcontact_city))
							{
								$location.=" $adcontact_city";
							}
							if(isset($adcontact_state) && !empty($adcontact_state))
							{
								$location.=" $adcontact_state";
							}
							if(isset($adcontact_country) && !empty($adcontact_country))
							{
								$location.=" $adcontact_country";
							}
						}
					}

					$addetails="<div id=\"showad\"><div class=\"adtitle\">$adtitle</div><div class=\"adbyline\">Contact $adcontact_name $adcontactphone $location</div>
					<p class=\"addetails\">$addetails</p></div>";


					$uploadandpay.="$addetails";

					////////////////////////////////////////////////////////////////////////////////////
					// Step:3 Configure the upload form if images are allowed
					////////////////////////////////////////////////////////////////////////////////////

					if( (get_awpcp_option('imagesallowdisallow') == 1) && ( $numimgsallowed > '0' ) )
					{

						$totalimagesuploaded=get_total_imagesuploaded($ad_id);

						if($totalimagesuploaded < $numimgsallowed)
						{

							$max_image_size=get_awpcp_option('maximagesize');

							$showimageuploadform="<p>You can display [<b>$numimgsallowed</b>] images with your ad if desired.</p>";

							if(get_awpcp_option('imagesapprove') == 1)
							{
								$showimageuploadform.="<p>Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it.</p>";
							}

							$showimageuploadform.="
							<h2>Image Upload</h2>
							<p id=\"ustatmsg\">

							<form id=\"AWPCPForm1\" name=\"AWPCPForm1\" method=\"post\" ENCTYPE=\"Multipart/form-data\">
								<p id=\"showhideuploadform\">
								<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_image_size\" />
								<input type=\"hidden\" name=\"ADID\" value=\"$ad_id\" />
								<input type=\"hidden\" name=\"ADTERMID\" value=\"$adterm_id\" />
								If adding images to your ad, select an image from your hard disk:<br/><br/>


									<input type=\"file\" name=\"AWPCPfileToUpload\" id=\"AWPCPfileToUpload\" size=\"18\" />
									<input type=\"Submit\" value=\"Submit\" id=\"awpcp_buttonForm\" />

								</p>
							</form>
							<img id=\"loading\" src=\"".AWPCPURL."images/loading.gif\" width=\"51\" height=\"19\" style=\"display:none;\" />

							<p id=\"message\">

							<p id=\"result\"><div class=\"fixfloat\"></div>";

						}
						else
						{
							$showimageuploadform="";
						}

						$uploadandpay.="$showimageuploadform";
					}


					////////////////////////////////////////////////////////////////////////////
					// Step:4 Get the information needed about the specific ad term
					////////////////////////////////////////////////////////////////////////////

						$query="SELECT adterm_name,amount FROM ".$table_name2." WHERE adterm_id='$adterm_id'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
						while ($rsrow=mysql_fetch_row($res)) {
						list($adterm_name,$amount)=$rsrow;
						}

					////////////////////////////////////////////////////////////////////////////
					// Step:5 Setup the payment buttons
					////////////////////////////////////////////////////////////////////////////

					if($amount <= 0)
					{
						$showpaybutton='';
					}

					else
					{

						$showpaybutton="<h2>Payment</h2><p>Please click the  button below to submit payment for your ad listing.</p>";

						////////////////////////////////////////////////////////////////////////////
						// Print the paypal button option if paypal is activated
						////////////////////////////////////////////////////////////////////////////

						if($adpaymethod == 'paypal')
						{
							$base=get_option('siteurl');
							$custom="$ad_id";
							$custom.="_";
							$custom.="$key";

							$quers=setup_url_structure($awpcppagename);

							if(get_awpcp_option('paylivetestmode') == 1)
							{
								$paypalurl="http://www.paypal.com/cgi-bin/webscr";
							}
							else
							{
								$paypalurl="https://www.sandbox.paypal.com/cgi-bin/webscr";
							}


							$showpaybutton.="
							<form action=\"$paypalurl\" method=\"post\">
							<input type=\"hidden\" name=\"cmd\" value=\"_xclick\" />";

							if(get_awpcp_option('paylivetestmode') == 0)
							{
								$showpaybutton.="<input type=\"hidden\" name=\"test_ipn\" value=\"1\" />";
							}

							$showpaybutton.="
							<input type=\"hidden\" name=\"business\" value=\"".get_awpcp_option('paypalemail')."\" />
							<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />";

							if( get_awpcp_option('seofriendlyurls') )
							{
								if(isset($permastruc) && !empty($permastruc))
								{
									$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou/$custom\" />";
								}
								else
								{
								$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou&i=$custom\" />";
								}
							}
							else
							{
								$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou&i=$custom\" />";
							}

							$showpaybutton.="$codepaypalthank";

							if( get_awpcp_option('seofriendlyurls') )
							{
								if(isset($permastruc) && !empty($permastruc))
								{
									$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal/$custom\" />";
								}
								else
								{
									$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal&i=$custom\" />";
								}
							}
							else
							{
								$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal&i=$custom\" />";
							}

							$showpaybutton.="$codepaypalcancel";
							$showpaybutton.="<input type=\"hidden\" name=\"notify_url\" value=\"".$quers."paypal\" />
							<input type=\"hidden\" name=\"no_note\" value=\"1\" />
							<input type=\"hidden\" name=\"quantity\" value=\"1\" />
							<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />
							<input type=\"hidden\" name=\"rm\" value=\"2\" />
							<input type=\"hidden\" name=\"item_name\" value=\"$adterm_name\" />
							<input type=\"hidden\" name=\"item_number\" value=\"$adterm_id\" />
							<input type=\"hidden\" name=\"amount\" value=\"$amount\" />
							<input type=\"hidden\" name=\"currency_code\" value=\"".get_awpcp_option('paypalcurrencycode')."\" />
							<input type=\"hidden\" name=\"custom\" value=\"$custom\" />
							<input type=\"hidden\" name=\"src\" value=\"1\" />
							<input type=\"hidden\" name=\"sra\" value=\"1\" />";


							$showpaybutton.="
							<input class=\"button\" type=\"submit\" value=\"Pay With PayPal\">
							</form>";
						}

						/////////////////////////////////////////////////////////////////////////////
						// Print the  2Checkout button option if 2Checkout is activated
						/////////////////////////////////////////////////////////////////////////////

						elseif($adpaymethod == '2checkout')
						{

								$custom="$ad_id";
								$custom.="_";
								$custom.="$key";

							$quers=setup_url_structure($awpcppagename);

							$showpaybutton.="
							<form action=\"https://www2.2checkout.com/2co/buyer/purchase\" method=\"post\">
									<input type=\"hidden\" name=\"x_login\" value=\"".get_awpcp_option('2checkout')."\" />
									<input type=\"hidden\" name=\"id_type\" value=\"1\" />
									<input type=\"hidden\" name=\"fixed\" value=\"Y\" />
									<input type=\"hidden\" name=\"pay_method\" value=\"CC\" />
									<input type=\"hidden\" name=\"x_receipt_link_url\" value=\"".$quers."2checkout\">
									<input type=\"hidden\" name=\"x_invoice_num\" value=\"1\" />
									<input type=\"hidden\" name=\"x_amount\" value=\"$amount\" />
									<input type=\"hidden\" name=\"c_prod\" value=\"$adterm_id\" />
									<input type=\"hidden\" name=\"c_name\" value=\"$adterm_name\" />
									<input type=\"hidden\" name=\"c_description\" value=\"$adterm_name\" />
									<input type=\"hidden\" name=\"c_tangible\" value=\"N\" />
									<input type=\"hidden\" name=\"item_number\" value=\"$adterm_id\" />
									<input type=\"hidden\" name=\"custom\" value=\"$custom\" />";

									if(get_awpcp_option('paylivetestmode') == 0)
									{
										$showpaybutton.="\n<input type=\"hidden\" name=\"demo\" value=\"Y\" />\n";
									}
										$showpaybutton.="<input name=\"submit\" class=\"button\" type=\"submit\" value=\"Pay With 2Checkout\" /></form>";

						}
					}

						$uploadandpay.="$showpaybutton";

						if( $feeamt <= 0 )
						{

							$finishbutton="<p>Please click the finish button to complete the process of submitting your listing</p>
										<form method=\"post\" id=\"awpcpui_process\">
										<input type=\"hidden\" name=\"a\" value=\"adpostfinish\">
										<input type=\"hidden\" name=\"ad_id\" value=\"$ad_id\" />
										<input type=\"hidden\" name=\"adkey\" value=\"$key\" />
										<input type=\"hidden\" name=\"adterm_id\" value=\"$adterm_id\" />
										<input type=\"Submit\" value=\"Finish\"/>
										</form>";
							$uploadandpay.="$finishbutton";
						}

						////////////////////////////////////////////////////////////////////////////
						// Display the content
						////////////////////////////////////////////////////////////////////////////

						$classicontent=$uploadandpay;
						echo "$classicontent";

				}

				////////////////////////////////////////////////////////////////////////////
				// Configure the content in the event of the site running in free mode
				////////////////////////////////////////////////////////////////////////////

				elseif((get_awpcp_option('freepay') == '0') && (get_awpcp_option('imagesallowdisallow') == 1))
				{

					$imagesforfree=get_awpcp_option('imagesallowedfree');

					if($totalimagesuploaded < $imagesforfree)
					{

						$max_image_size=get_awpcp_option('maximagesize');

										$showimageuploadform="<p>You can display [<b>$imagesforfree</b>] images with your ad if desired.</p>";

										if(get_awpcp_option('imagesapprove') == 1)
										{
											$showimageuploadform.="<p>Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it.</p>";
										}

										$showimageuploadform.="
										<h2>Image Upload</h2>
									<p id=\"ustatmsg\">

										<form id=\"AWPCPForm1\" name=\"AWPCPForm1\" method=\"post\" ENCTYPE=\"Multipart/form-data\">
											<p id=\"showhideuploadform\">
										    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_image_size\" />
										    <input type=\"hidden\" name=\"ADID\" value=\"$ad_id\" />
										    <input type=\"hidden\" name=\"ADTERMID\" value=\"$adterm_id\" />
										    If adding images to your ad, select an image from your hard disk:<br/><br/>


										        <input type=\"file\" name=\"AWPCPfileToUpload\" id=\"AWPCPfileToUpload\" size=\"18\" />
										        <input type=\"Submit\" value=\"Submit\" id=\"awpcp_buttonForm\" />

										    </p>
										</form>
										<img id=\"loading\" src=\"".AWPCPURL."images/loading.gif\" width=\"51\" height=\"19\" style=\"display:none;\" />


										<p id=\"message\">

										<p id=\"result\"><div class=\"fixfloat\"></div>";


					}
					else
					{
						$showimageuploadform="";
					}

					$finishbutton="<p>Please click the finish button to complete the process of submitting your listing</p>
					<form method=\"post\" id=\"awpcpui_process\">
					<input type=\"hidden\" name=\"a\" value=\"adpostfinish\">
					<input type=\"hidden\" name=\"ad_id\" value=\"$ad_id\" />
					<input type=\"hidden\" name=\"adkey\" value=\"$key\" />
					<input type=\"Submit\" value=\"Finish\"/>
										</form>";

					$showimageuploadform.="$finishbutton";

					$classicontent="$showimageuploadform";

					echo "$classicontent";
					}

				else
				{
						if(isset($_SESSION['regioncountryID']) )
						{
							unset($_SESSION['regioncountryID']);
						}
						if(isset($_SESSION['regionstatownID']) )
						{
							unset($_SESSION['regionstatownID']);
						}
						if(isset($_SESSION['regioncityID']) )
						{
							unset($_SESSION['regioncityID']);
						}

						ad_success_email($ad_id,$txn_id='',$key,$message,$gateway='');


				}
			}
		}

	}

}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: configure the page to display to user for purpose of editing images during ad editing process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function editimages($adtermid,$adid,$adkey,$editemail)
{

	global $wpdb;
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	$savedemail=get_adposteremail($adid);

		if(strcasecmp($editemail, $savedemail) == 0)
		{

			$imagecode="<h2>Manage your ad images</h2>";

			if(!isset($adid) || empty($adid))
			{
				$imagecode.="There has been a problem encountered. The system is unable to continue processing the task in progress. Please start over and if you encounter the problem again, please contact a system administrator.";
			}

			else
			{

			// First make sure images are allowed

			if(get_awpcp_option('imagesallowdisallow') == 1)
			{

				// Next figure out how many images user is allowed to upload

				if((get_awpcp_option('freepay') == 1) && isset($adtermid) && $adtermid != '0')
				{
					$numimgsallowed=get_numimgsallowed($adtermid);
				}
				elseif((!get_awpcp_option('freepay')) && (ad_term_id_set($adid)))
				{
					$numimgsallowed=get_numimgsallowed($adtermid);
				}
				else
				{
					$numimgsallowed=get_awpcp_option('imagesallowedfree');
				}

				// Next figure out how many (if any) images the user has previously uploaded

				$totalimagesuploaded=get_total_imagesuploaded($adid);


				// Next determine if the user has reached their image quota and act accordingly

				if($totalimagesuploaded >= 1)
				{

					$imagecode.="<p>Your images are displayed below. The total number of images you are allowed is: $numimgsallowed</p>";

					if(($numimgsallowed - $totalimagesuploaded) == '0')
					{
						$imagecode.=	"<p>If you want to change your images you will first need to delete the current images.</p>";
					}

					if(get_awpcp_option('imagesapprove') == 1)
					{
						$imagecode.=	"<p>Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it.</p>";
					}

					// Display the current images

					$imagecode.="<div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

					$theimage='';


					$query="SELECT key_id,image_name,disabled FROM ".$table_name5." WHERE ad_id='$adid' ORDER BY image_name ASC";
					if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					while ($rsrow=mysql_fetch_row($res))
					{
						list($ikey,$image_name,$disabled)=$rsrow;



						$ikey.="_";
						$ikey.="$adid";
						$ikey.="_";
						$ikey.="$adtermid";
						$ikey.="_";
						$ikey.="$adkey";
						$ikey.="_";
						$ikey.="$editemail";

						$transval='';

						if($disabled == 1)
						{
							$transval="class=\"imgtransparency\"";
							$imgstat="<font style=\"font-size:smaller;\">Disabled</font>";
						}

						$dellink="<a href=\"?a=dp&k=$ikey\">Delete</a>";
						$theimage.="<li><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img $transval src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a><br/>$dellink $imgstat</li>";
					}

					$imagecode.=$theimage;
					$imagecode.="</ul></div></div>";
					$imagecode.="<div class=\"fixfloat\"></div>";
				}

				elseif($totalimagesuploaded < 1)
				{

					$imagecode.="You do not currently have any images uploaded. Use the upload form below to upload your images. If you do not wish to upload any images simply click the finish button. If uploading images, be careful not to click the finish button until after you've uploaded all your images. </p>";
				}


				if($totalimagesuploaded < $numimgsallowed)
				{
					$max_image_size=get_awpcp_option('maximagesize');

					$showimageuploadform="

								<h2>Image Upload</h2>

								<p id=\"ustatmsg\">

								<form id=\"AWPCPForm1\" name=\"AWPCPForm1\" method=\"post\" ENCTYPE=\"Multipart/form-data\">
									<p id=\"showhideuploadform\">
									<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_image_size\" />
									<input type=\"hidden\" name=\"ADID\" value=\"$adid\" />
									<input type=\"hidden\" name=\"ADTERMID\" value=\"$adtermid\" />
									If adding images to your ad, select an image from your hard disk:<br/><br/>


									<input type=\"file\" name=\"AWPCPfileToUpload\" id=\"AWPCPfileToUpload\" size=\"18\" />
									<input type=\"Submit\" value=\"Submit\" id=\"awpcp_buttonForm\" />

									</p>
								</form>
								<img id=\"loading\" src=\"".AWPCPURL."images/loading.gif\" width=\"51\" height=\"19\" style=\"display:none;\" />




								<p id=\"message\">

								<p id=\"result\"><div class=\"fixfloat\"></div>";

				}
				else
				{
					$showimageuploadform="";
				}

			}

			$imagecode.=$showimageuploadform;

				$finishbutton="
						<p>Please click the finish button to complete the process of editing listing</p>
						<form method=\"post\" id=\"awpcpui_process\">
						<input type=\"hidden\" name=\"a\" value=\"adpostfinish\" />
						<input type=\"hidden\" name=\"ad_id\" value=\"$adid\" />
						<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />
						<input type=\"hidden\" name=\"adaction\" value=\"editad\" />
						<input type=\"Submit\" value=\"Finish\"/>
						</form>

						";

			$imagecode.="$finishbutton<div class=\"fixfloat\"></div>";

			}

			echo "<div id=\"classiwrapper\">$imagecode</div>";
		}



}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function deletepic($picid,$adid,$adtermid,$adkey,$editemail){

$isadmin=checkifisadmin();
$savedemail=get_adposteremail($adid);

	if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 )) {


		global $wpdb;
		$table_name5 = $wpdb->prefix . "awpcp_adphotos";

		echo "<div id=\"classiwrapper\">";

			$query="SELECT image_name FROM ".$table_name5." WHERE key_id='$picid' AND ad_id='$adid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
			$pic=mysql_result($res,0,0);

			$query="DELETE FROM ".$table_name5." WHERE key_id='$picid' AND ad_id='$adid' AND image_name='$pic'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
					if (file_exists(AWPCPUPLOADDIR.'/'.$pic)) {
						@unlink(AWPCPUPLOADDIR.'/'.$pic);
					}
					if (file_exists(AWPCPTHUMBSUPLOADDIR.'/'.$pic)) {
						@unlink(AWPCPTHUMBSUPLOADDIR.'/'.$pic);
					}


			//	$classicontent=$imagecode;
			//	global $classicontent;

		if($isadmin == 1 && is_admin()){
			$message="The image has been deleted";
			return $message;
		}

		else {

			editimages($adtermid,$adid,$adkey,$editemail);
		}

	}
	else {
			echo "Problem encountered. Cannot complete  request.";
	}
echo "</div>";
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: delete ad by specified ad ID
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function deletead($adid,$adkey,$editemail) {

		$isadmin=checkifisadmin();


			if(get_awpcp_option('onlyadmincanplaceads') && ($isadmin != '1')){

			echo "
					<div id=\"classiwrapper\">
					<p>You do not have permission to perform the function you are trying to perform. Access to this page has been denied.</p>
					</div>
				";
			}

		else {

			global $wpdb,$nameofsite;
			$table_name3 = $wpdb->prefix . "awpcp_ads";
			$table_name5 = $wpdb->prefix . "awpcp_adphotos";
			$savedemail=get_adposteremail($adid);


			if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 )) {

					// Delete ad image data from database and delete images from server

					$query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$adid'";
					if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					for ($i=0;$i<mysql_num_rows($res);$i++) {
						$photo=mysql_result($res,$i,0);
							if (file_exists(AWPCPUPLOADDIR.'/'.$photo)) {
								@unlink(AWPCPUPLOADDIR.'/'.$photo);
							}
							if (file_exists(AWPCPTHUMBSUPLOADDIR.'/'.$photo)) {
								@unlink(AWPCPTHUMBSUPLOADDIR.'/'.$photo);
							}
					}

						$query="DELETE FROM ".$table_name5." WHERE ad_id='$adid'";
						if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						// Now delete the ad
						$query="DELETE FROM  ".$table_name3." WHERE ad_id='$adid'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						if(($isadmin == 1) && is_admin()) {
							$message="The ad has been deleted";
							return $message;
						}

						else {
									$awpcppage=get_currentpagename();
									$awpcppagename = sanitize_title($awpcppage, $post_ID='');
									$quers=setup_url_structure($awpcppagename);


							awpcp_menu_items();

							echo "<div id=\"classiwrapper\"> Your ad details and any photos you have uploaded have been deleted from the system. Thank you for using $nameofsite </div>";
						}
			}
			else {
						echo "Problem encountered. Cannot complete  request.";
			}

		}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Process PayPal Payment
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function do_paypal()
{


// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value)
{
	$value = urlencode(stripslashes_mq($value));
	$req .= "&$key=$value";
}

if(get_awpcp_option('paylivetestmode') == 0)
{
	$paypallink="www.sandbox.paypal.com";
}
else
{
	$paypallink="www.paypal.com";
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Host: $paypallink\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n";
$header.="Connection: close\r\n\r\n";
$fp = fsockopen($paypallink, 80, $errno, $errstr, 30);


// assign posted variables to local variables
$item_name            = $_POST['item_name'];
$item_number          = $_POST['item_number'];
$receiver_email       = $_POST['receiver_email'];
$quantity             = $_POST['quantity'];
$business             = $_POST['business'];
$mcgross	      	  = $_POST['mc_gross'];
$payment_gross	      = $_POST['payment_gross'];
$fee                  = $_POST['mc_fee'];
$tax                  = $_POST['tax'];
$payment_currency     = $_POST['mc_currency'];
$exchange_rate        = $_POST['exchange_rate'];
$payment_status       = $_POST['payment_status'];
$payment_type         = $_POST['payment_type'];
$payment_date         = $_POST['payment_date'];
$txn_id               = $_POST['txn_id'];
$txn_type             = $_POST['txn_type'];
$first_name           = $_POST['first_name'];
$last_name            = $_POST['last_name'];
$payer_email          = $_POST['payer_email'];
$address_street       = $_POST['address_street'];
$address_zip          = $_POST['address_zip'];
$address_city         = $_POST['address_city'];
$address_state        = $_POST['address_state'];
$address_country      = $_POST['address_country'];
$address_country_code = $_POST['address_country_code'];
$residence_country    = $_POST['residence_country'];
$custom               = $_POST['custom'];


$pbizid=get_awpcp_option('paypalemail');


$payment_verified=false;
if ($fp)
{
	fputs ($fp, $header . $req."\r\n\r\n");
	$reply='';
	$headerdone=false;
	while(!feof($fp))
	{
		$line=fgets($fp);
		if (strcmp($line,"\r\n")==0)
		{
			// read the header
			$headerdone=true;
		}
		elseif ($headerdone)
		{
			// header has been read. now read the contents
			$reply.=$line;
		}
	}
	fclose($fp);
	$reply=trim($reply);
	if (strcasecmp($reply,'VERIFIED')==0)
	{
		$payment_verified = true;
	}
	elseif (strcasecmp($reply,'INVALID')==0)
	{
		$payment_verified = false;
	}
}
else
{
	// HTTP ERROR
}


	if ($payment_verified)
	{


		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Configure the data that will be needed for use depending on conditions met
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////

		////////////////////////////////////////////////////////////////////////////////////
		// Split the data returned in $custom
		////////////////////////////////////////////////////////////////////////////////////

			$adidkey = $custom;
			list($ad_id,$key) = split('[_]', $adidkey);
			$ad_id=addslashes_mq($ad_id);
			$key=addslashes_mq($key);

			////////////////////////////////////////////////////////////////////////////////////
			// Get the item ID in order to calculate length of term
			////////////////////////////////////////////////////////////////////////////////////

			$adtermid=$item_number;

			////////////////////////////////////////////////////////////////////////////////////
			// Set the value of field: premiumstart
			////////////////////////////////////////////////////////////////////////////////////

			$ad_startdate=mktime();

			////////////////////////////////////////////////////////////////////////////////////
			// Determine when ad term ends based on start time and term length
			////////////////////////////////////////////////////////////////////////////////////

			$days=get_num_days_in_term($adtermid);


			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";
			$table_name2 = $wpdb->prefix . "awpcp_adfees";
			$gateway="Paypal";

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Make sure the incoming payment amount received matches at least one of the payment ids in the system
	////////////////////////////////////////////////////////////////////////////////////////////////////////////


		$myamounts=array();

			$query="SELECT amount FROM ".$table_name2."";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

				while ($rsrow=mysql_fetch_row($res))
				{
					$myamounts[]=number_format($rsrow[0],2);
				}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// If the incoming payment amount does not match the system amounts
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////


		if(!(in_array(number_format($mcgross,2),$myamounts) || in_array(number_format($payment_gross,2),$myamounts)))
		{
			$message="The amount you have paid does not match any of our listing fee amounts. Please contact us to clarify the problem.";
			abort_payment($message,$ad_id,$txn_id,$gateway);
		}


	////////////////////////////////////////////////////////////////////////////////////
	// If the amount matches
	////////////////////////////////////////////////////////////////////////////////////


	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Compare the incoming receiver email with the system receiver email
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the emails do not match
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if (!(strcasecmp($receiver_email, $pbizid) == 0))
			{
				$message="There was an error process your transaction. If funds have been deducted from your account they have not been processed to our account. You will need to contact PayPal about the matter.";
				abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
			}

			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the emails do match
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Check for duplicate transaction ID
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the transaction ID is a duplicate of an ID already in the system
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				if (isdupetransid($txn_id))
				{
					$message="It appears this transaction has already been processed. If you do not see your ad in the system please contact the site adminstrator for assistance.";
					abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
				}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the transaction ID is not a duplicate proceed with processing the transaction
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Begin updating based on payment status
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				if(strcasecmp($payment_status, "Completed") == 0)
				{

				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

					if(get_awpcp_option('adapprove') == 1)
					{
						$disabled='1';
					}
					else
					{
						$disabled='0';
					}

					$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled' WHERE ad_id='$ad_id' AND ad_key='$key'";
					if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					if (isset($item_number) && !empty($item_number))
					{

						$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
					}


					$message="Payment Status: Completed";
					ad_success_email($ad_id,$txn_id,$key,$message,$gateway);



				}
				elseif(strcasecmp($payment_status, "Refunded") == 0 || strcasecmp($payment_status, "Reversed") == 0 || strcasecmp ($payment_status, "Partially-Refunded") == 0)
				{

					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					// Disable the ad since the payment has been refunded
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


						if(get_awpcp_option(freepay) == 1)
						{

							$query="UPDATE  ".$table_name3." SET disabled='1',payment_status='$payment_status', WHERE ad_id='$ad_id' AND ad_key='$key'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							if (isset($item_number) && !empty($item_number))
							{
								$query="UPDATE ".$table_name2." SET buys=buys-1 WHERE adterm_id='".addslashes_mq($item_number)."'";
								if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
							}
						}

				}
				elseif(strcasecmp ($payment_status, "Pending") == 0 )
				{

					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

						if(get_awpcp_option('disablependingads') == 1)
						{
							$disabled='1';
						}
						else
						{
							$disabled='0';
						}

						$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled' WHERE ad_id='$ad_id' AND ad_key='$key'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						if (isset($item_number) && !empty($item_number))
						{

							$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
						}


						$message="Payment status is: Pending";
						ad_success_email($ad_id,$txn_id,$key,$message,$gateway);


				}
				else
				{
					$message="There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.";
					abort_payment($message,$ad_id,$txn_id,$gateway);

				}

	} //Close if payment verified

	else
	{

		if(!isset($message) || empty($message))
		{
			$message="There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.";
		}

		echo "<div id=\"classiwrapper\">$message</div>";
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_2checkout() {


// Get the data that comes back from 2checkout

$x_2checked = $_POST['x_2checked'];
$x_MD5_Hash = $_POST['x_MD5_Hash'];
$x_trans_id = $_POST['x_trans_id'];
$x_amount = $_POST['x_amount'];
$card_holder_name = $_POST['card_holder_name'];
$x_Country = $_POST['x_Country'];
$x_City = $_POST['x_City'];
$x_State = $_POST['x_State'];
$x_Zip = $_POST['x_Zip'];
$x_Address = $_POST['x_Address'];
$x_Email = $_POST['x_Email'];
$x_Phone = $_POST['x_Phone'];
$x_Login = $_POST['x_Phone'];
$demo = $_POST['demo'];
$x_response_code= $_POST['x_response_code'];
$x_response_reason_code = $_POST['x_response_reason_code'];
$x_response_reason_text = $_POST['x_response_reason_text'];
$item_number = $_POST['item_number'];
$custom = $_POST['custom'];

if($x_response_code == 1)
{
	$payment_verified=true;
}
else
{
	$payment_verified=false;
}


$pbizid=get_awpcp_option('2checkout');


	if ($payment_verified) {


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Configure the data that will be needed for use depending on conditions met
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////

		////////////////////////////////////////////////////////////////////////////////////
		// Split the data returned in $custom
		////////////////////////////////////////////////////////////////////////////////////

			$adidkey = $custom;
			list($ad_id,$key) = split('[_]', $adidkey);
			$ad_id=addslashes_mq($ad_id);
			$key=addslashes_mq($key);

			////////////////////////////////////////////////////////////////////////////////////
			// Get the item ID in order to calculate length of term
			////////////////////////////////////////////////////////////////////////////////////

			$adtermid=$item_number;

			////////////////////////////////////////////////////////////////////////////////////
			// Set the value of field: premiumstart
			////////////////////////////////////////////////////////////////////////////////////

			$ad_startdate=mktime();

			////////////////////////////////////////////////////////////////////////////////////
			// Determine when ad term ends based on start time and term length
			////////////////////////////////////////////////////////////////////////////////////

			$days=get_num_days_in_term($adtermid);


			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";
			$table_name2 = $wpdb->prefix . "awpcp_adfees";
			$gateway="2checkout";

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Make sure the incoming payment amount received matches at least one of the payment ids in the system
	////////////////////////////////////////////////////////////////////////////////////////////////////////////


		$myamounts=array();

			$query="SELECT amount FROM ".$table_name2."";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

				while ($rsrow=mysql_fetch_row($res)) {
					$myamounts[]=number_format($rsrow[0],2);
				}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// If the incoming payment amount does not match the system amounts
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////


		if(!(in_array(number_format($x_amount,2),$myamounts))) {
			$message="The amount you have paid does not match any of our listing fee amounts. Please contact us to clarify the problem.";
			abort_payment($message,$ad_id,$x_trans_id,$gateway);
		}


	////////////////////////////////////////////////////////////////////////////////////
	// If the amount matches
	////////////////////////////////////////////////////////////////////////////////////


	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Compare the incoming receiver ID with the system receiver ID
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the vendor IDs do not match
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if (!(strcasecmp($x_Login, $pbizid) == 0)) {
				$message="There was an error process your transaction. If funds have been deducted from your account they have not been processed to our account. You will need to contact 2Checkout about the matter.";
				abort_payment($message,$ad_id,$x_trans_id,$gateway);
			}

			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the vendor IDs do match
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Check for duplicate transaction ID
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the transaction ID is a duplicate of an ID already in the system
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				if (isdupetransid($x_trans_id)) {
					$message="It appears this transaction has already been processed. If you do not see your ad in the system please contact the site adminstrator for assistance.";
					abort_payment($message,$ad_id,$x_trans_id,$gateway);
				}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the transaction ID is not a duplicate proceed with processing the transaction
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Begin updating based on payment status
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

					if(get_awpcp_option('adapprove') == 1){
					$disabled='1';}else {$disabled='0';}

					$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$x_trans_id',payment_status='Completed',payment_gateway='2Checkout',disabled='$disabled' WHERE ad_id='$ad_id' AND ad_key='$key'";
					if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					if (isset($item_number) && !empty($item_number)) {

						$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
					}


					$message="Payment Status: Completed";
					ad_success_email($ad_id,$x_trans_id,$key,$message,$gateway);


	} //Close if payment verified

	elseif( !($payment_verified) )
	{
		$message="There has been a problem encountered. The payment was made but the system has failed to complete the post processing of your transaction. Please contact the site administrator with this message.";
		abort_payment($message,$ad_id,$x_trans_id,$gateway);

	}

	else
	{
		if(!isset($message) || empty($message))
		{
			$message="There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.";
		}

		echo "<div id=\"classiwrapper\">$message</div>";
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: email adminstrator and ad poster if there was a problem encountered when paypal payment procedure was attempted
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function abort_payment($message,$ad_id,$transactionid,$gateway){
//email the administrator and the user to notify that the payment process was aborted

global $nameofsite,$siteurl,$thisadminemail;
$adposteremail=get_adposteremail($ad_id);
$admostername=get_adpostername($ad_id);
$listingtitle=get_adtitle($ad_id);
$subjectuser="Your classifieds listing at $nameofsite";
$subjectadmin="Problem encountered during the $gateway payment procedure for ad $listingtitle";
$mailbodyuser="Dear $adpostername<br><br>";
$mailbodyuser.="There was a problem encountered during your attempt to submit payment for the listing titled \"$listingtitle\" on $nameofsite at $siteurl. The transaction was aborted due to:<br><br>";
$mailbodyuser.="$message<br><br>";
$mailbodyuser.="If funds were removed from the account you tried to use to make a payment please contact $thisadminemail or $gateway for help with fixing the problem.<br><br>";
$mailbodyuser.="When contacting in reference to this matter, please provide the following transaction ID: $transactionid<br><br>";
$mailbodyuser.="Thank you for your business<br>";
$mailbodyuser.="$nameofsite Administrator<br>";
$mailbodyuser.="$siteurl";
$mailbodyuseralt="Dear $adpostername\n\n";
$mailbodyuseralt.="There was a problem encountered during your attempt to submit payment for the listing titled \"$listingtitle\" on $nameofsite at $siteurl. The transaction was aborted due to:\n\n";
$mailbodyuseralt.="$message\n\n";
$mailbodyuseralt.="If funds were removed from the account you tried to use to make a payment please contact $thisadminemail or $gateway for help with fixing the problem.\n\n";
$mailbodyuseralt.="When contacting in reference to this matter, please provide the following transaction ID: $transactionid\n\n";
$mailbodyuseralt.="Thank you for your business\n";
$mailbodyuseralt.="$nameofsite Administrator\n";
$mailbodyuseralt.="$siteurl";
$mailbodyadmin="Dear $nameofsite Administrator\n\n";
$mailbodyadmin.="There was a problem encountered during an attempt to submit payment for the listing titled \"$listingtitle\" on $nameofsite at $siteurl. The transaction was aborted due to:<br><br>";
$mailbodyadmin.="$message\n\n";
$mailbodyadmin.="For your reference the transaction id is: $transactionid and the ad ID is: $ad_id\n\n";
$mailbodyadmin.="$siteurl";




			$from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

			//email the buyer
			if(send_email($thisadminemail,$adposteremail,$subjectuser,$mailbodyuser,true)){
				// Do nothing
			}

			// If function send_mail did not work try function mail()
			elseif(mail($adposteremail, $subjectuser, $mailbodyuseralt, $from_header))
			{
				// Do nothing
			}
			else
			{

				$host = get_awpcp_option('smtphost');
				$username = get_awpcp_option('smtpusername');
				$password = get_awpcp_option('smtppassword');

				$headers = array ('From' => $from_header,
				  'To' => $adposteremail,
				  'Subject' => $subjectuser);
				$smtp = Mail::factory('smtp',
				  array ('host' => $host,
					'auth' => true,
					'username' => $username,
					'password' => $password));

				$mail = $smtp->send($adposteremail, $headers, $mailbodyuseralt);

			}

			//email the administrator

				if(send_email($thisadminemail,$thisadminemail,$subjectadmin,$mailbodyadmin,true)){
					// Do nothing
				}

				// If function send_mail did not work try function mail()
				elseif(mail($thisadminemail, $subjectadmin, $mailbodyadmin, $from_header))
				{
					// Do nothing
				}
				else
				{

					$host = get_awpcp_option('smtphost');
					$username = get_awpcp_option('smtpusername');
					$password = get_awpcp_option('smtppassword');

					$headers = array ('From' => $from_header,
					  'To' => $thisadminemail,
					  'Subject' => $subjectadmin);
					$smtp = Mail::factory('smtp',
					  array ('host' => $host,
						'auth' => true,
						'username' => $username,
						'password' => $password));

					$mail = $smtp->send($thisadminemail, $headers, $mailbodyadmin);

			}
}



function abort_payment_no_email()
{

	die;

}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Send out notifications that listing has been successfully posted
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function ad_success_email($ad_id,$transactionid,$key,$message,$gateway){

global $nameofsite,$siteurl,$thisadminemail;

$adposteremail=get_adposteremail($ad_id);
$adpostername=get_adpostername($ad_id);
$listingtitle=get_adtitle($ad_id);
$subjectuser="Your classifieds listing at $nameofsite";
$subjectadmin="New ad submitted: $listingtitle";
$mailbodyuser="Dear $adpostername<br/><br/>";
$mailbodyuser.="Your listing \"$listingtitle\" has been successfully submitted at $nameofsite at $siteurl.<br/><br/>";
if (strcasecmp ($gateway, "paypal") == 0 ) {
	$mailbodyuser.="Your payment has been processed. The status of your payment is shown below:<br/><br/>";
	$mailbodyuser.="$message<br/><br/>";
	$mailbodyuser.="If the status indicates your payment is pending it means funds have not yet been deducted from your payment account.<br/><br/>";
	$mailbodyuser.="If you have any questions about the transaction please contact $thisadminemail.<br/><br/>";
	$mailbodyuser.="When contacting in reference to this transaction please provide the following transaction ID: $transactionid<br/><br/>";
}
$mailbodyuser.="Please note that in order to edit your listing you will need the below access key as well as your ad ID ($ad_id) and your email ($adposteremail):<br/><br/>";
$mailbodyuser.="Access Key: $key<br/><br/>";
$mailbodyuser.="Thank you for your business<br/>";
$mailbodyuser.="$nameofsite Administrator<br/>";
$mailbodyuser.="$siteurl<br><br/>";
$mailbodyadmin="Dear $nameofsite Administrator<br><br>";
$mailbodyadmin.="A new listing has been submitted to the $nameofsite database at $siteurl.<br/><br/>";
$mailbodyadmin.="The listing title: $listingtitle.<br><br>";

if (strcasecmp ($gateway, "paypal") == 0 || strcasecmp ($gateway, "2checkout") == 0) {
	$mailbodyadmin.="The $gateway payment transaction was processed and the status of the payment is displayed below:<br><br>";
	$mailbodyadmin.="$message<br><br>";
	$mailbodyadmin.="For your reference the transaction id is: $transactionid<br><br>";
}

				$awpcppage=get_currentpagename();
				$awpcppagename = sanitize_title($awpcppage, $post_ID='');
				$permastruc=get_option(permalink_structure);
				$quers=setup_url_structure($awpcppagename);

				$modtitle=cleanstring($listingtitle);
				$modtitle=add_dashes($listingtitle);

				if( get_awpcp_option('seofriendlyurls') )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
						 	$adlink=$quers."showad/$ad_id/$modtitle";
						}
						else
						{
						 	$adlink=$quers."showad&id=$ad_id";
						}
					}
					else
					{
						$adlink=$quers."showad&id=$ad_id";
					}


$mailbodyadmin.="The ad can be viewed by visiting <a href=\"$adlink\">$adlink</a><br>The ID associated with this ad is: $ad_id<br><br>";
$mailbodyadmin.="$siteurl<br><br>";
$mailbodyuseralt="Dear $adpostername\n\n";
$mailbodyuseralt.="Your listing \"$listingtitle\" has been successfully submitted at $nameofsite at $siteurl.\n\n";
if (strcasecmp ($gateway, "paypal") == 0 ) {
	$mailbodyuseralt.="Your payment has been processed. The status of your payment is shown below:\n\n";
	$mailbodyuseralt.="$message\n\n";
	$mailbodyuseralt.="If the status indicates your payment is pending it means funds have not yet been deducted from your payment account.\n\n";
	$mailbodyuseralt.="If you have any questions about the transaction please contact $thisadminemail.\n\n";
	$mailbodyuseralt.="When contacting in reference to this transaction please provide the following transaction ID: $txn_id\n\n";
}
$mailbodyuseralt.="Please note that in order to edit your listing you will need the below access key as well as your ad ID ($ad_id) and your email ($adposteremail):\n\n";
$mailbodyuseralt.="Access Key: $key\n\n";
$mailbodyuseralt.="Thank you for your business\n";
$mailbodyuseralt.="$nameofsite Administrator\n";
$mailbodyuseralt.="$siteurl\n\n";
$mailbodyadminalt="Dear $nameofsite Administrator\n\n";
$mailbodyadminalt.="A new listing has been submitted to the $nameofsite database at $siteurl.\n\n";
$mailbodyadminalt.="The listing title: $listingtitle.\n\n";

if (strcasecmp ($gateway, "paypal") == 0 || strcasecmp ($gateway, "2checkout") == 0) {
	$mailbodyadminalt.="The $gateway payment transaction was processed and the status of the payment is displayed below:\n\n";
	$mailbodyadminalt.="$message\n\n";
	$mailbodyadminalt.="For your reference the transaction id is: $transactionid\n\n";
}
$mailbodyadminalt.="The ad can be viewed by visiting $adlink\nThe ID associated with this ad is: $ad_id\n\n";
$mailbodyadminalt.="$siteurl\n\n";

$from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

$messagetouser="Your ad has been submitted and an email has been dispatched to your email account on file. This email contains important information you will need to manage your listing.";
if(get_awpcp_option('adapprove') == 1){
$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
$messagetouser.="<p>$awaitingapprovalmsg</p>";
}

//email the buyer
if(send_email($thisadminemail,$adposteremail,$subjectuser,$mailbodyuser,true))
{

	//email the administrator if the admin has this option set
	if(get_awpcp_option('notifyofadposted'))
	{
		$sentok2=send_email($thisadminemail,$thisadminemail,$subjectadmin,$mailbodyadmin,true);
	}

	$printmessagetouser="$messagetouser";
}


// If function send_mail did not work try function mail()
elseif(mail($adposteremail, $subjectuser, $mailbodyuseralt, $from_header))
{
	//email the administrator if the admin has this option set using mail()
	if(get_awpcp_option('notifyofadposted'))
	{
		$sentok2=mail($thisadminemail,$subjectadmin,$mailbodyadminalt,$from_header);
	}

	$printmessagetouser="$messagetouser";
}

else
{


				$awpcp_smtp_host = get_awpcp_option('smtphost');
				$awpcp_smtp_username = get_awpcp_option('smtpusername');
				$awpcp_smtp_password = get_awpcp_option('smtppassword');

				if(isset($awpcp_smtp_host) && !empty($awpcp_smtp_host) && isset($awpcp_smtp_username) && !empty($awpcp_smtp_username) && isset($awpcp_smtp_password) && !empty($awpcp_empty) )
				{

					$headers = array ('From' => $from_header,
					  'To' => $sendtoemail,
					  'Subject' => $subject);
					$smtp = Mail::factory('smtp',
					  array ('host' => $awpcp_smtp_host,
						'auth' => true,
						'username' => $awpcp_smtp_username,
						'password' => $awpcp_smtp_password));

					$mail = $smtp->send($sendtoemail, $headers, $bodyalt);

					if (PEAR::isError($mail))
					{
					 $printmessagetouser="Sorry but there has been a problem encountered transmitting your ad information to your email address. Please contact the site administrator to request this information.";
					}
					else
					{
					 $printmessagetouser="$messagetouser";
					}
				}

				else
				{

					 $printmessagetouser="Sorry but there has been a problem encountered transmitting your ad information to your email address. Please contact the site administrator to request this information.";

				}

}


	echo "<div id=\"classiwrapper\">";

			$awpcppage=get_currentpagename();
			$awpcppagename = sanitize_title($awpcppage, $post_ID='');
			$quers=setup_url_structure($awpcppagename);

			awpcp_menu_items();

	echo "$printmessagetouser</div>";



}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: notify user of successful edit
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function ad_success_edit(){
global $nameofsite;
$messagetouser="Your ad has been edited successfully. Thank you for using $nameofsite.";
if(get_awpcp_option('adapprove') == 1){
$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
$messagetouser.="<p>$awaitingapprovalmsg</p>";
}

	if(!(strcasecmp ($gateway, "paypal") == 0 )) {
	echo "<div id=\"classiwrapper\">$messagetouser</div>";
	}


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: If user decides not to go through with paying for ad via paypal and clicks on cancel on the paypal website
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cancelpaypal () {

$base=get_option('siteurl');

echo "<div id=\"classiwrapper\">";

if(isset($_REQUEST['i']) && !empty($_REQUEST['i'])){
$adinfo=$_REQUEST['i'];}

list($ad_id,$key) = split('[_]', $adinfo);
$adterm_id=get_adterm_id($ad_id);
$adterm_name=get_adterm_name($adterm_id);
$amount=get_adfee_amount($adterm_id);
$base=get_option('siteurl');
$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');
$custom="$ad_id";
$custom.="_";
$custom.="$key";

$permastruc=get_option(permalink_structure);
$quers=setup_url_structure($awpcppagename);

						if(get_awpcp_option('paylivetestmode') == 1){
							$paypalurl="http://www.paypal.com/cgi-bin/webscr";
						}else {
							$paypalurl="https://www.sandbox.paypal.com/cgi-bin/webscr";
						}



						$showpaybutton.="
									<form action=\"$paypalurl\" method=\"post\">
									<input type=\"hidden\" name=\"cmd\" value=\"_xclick\" />";

										if(get_awpcp_option('paylivetestmode') == 0){
										$showpaybutton.="<input type=\"hidden\" name=\"test_ipn\" value=\"1\" />";
										}

									$showpaybutton.="
									<input type=\"hidden\" name=\"business\" value=\"".get_awpcp_option('paypalemail')."\" />
									<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />";
									if( get_awpcp_option('seofriendlyurls') )
									{
										if(isset($permastruc) && !empty($permastruc))
										{
											$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou/$custom\" />";
										}
										else
										{
											$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou&i=$custom\" />";
										}
									}
									else
									{
										$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou&i=$custom\" />";
									}
									$showpaybutton.="$codepaypalthank";
									if( get_awpcp_option('seofriendlyurls') )
									{
										if(isset($permastruc) && !empty($permastruc))
										{
											$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal/$custom\" />";
										}
										else
										{
											$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal&i=$custom\" />";
										}
									}
									else
									{
										$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal&i=$custom\" />";
									}
									$showpaybutton.="$codepaypalcancel";
									$showpaybutton.="<input type=\"hidden\" name=\"notify_url\" value=\"".$quers."paypal\" />
									<input type=\"hidden\" name=\"no_note\" value=\"1\" />
									<input type=\"hidden\" name=\"quantity\" value=\"1\" />
									<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />
									<input type=\"hidden\" name=\"rm\" value=\"2\" />
									<input type=\"hidden\" name=\"item_name\" value=\"$adterm_name\" />
									<input type=\"hidden\" name=\"item_number\" value=\"$adterm_id\" />
									<input type=\"hidden\" name=\"amount\" value=\"$amount\" />
									<input type=\"hidden\" name=\"currency_code\" value=\"".get_awpcp_option('paypalcurrencycode')."\" />
									<input type=\"hidden\" name=\"custom\" value=\"$custom\" />
									<input type=\"hidden\" name=\"src\" value=\"1\" />
									<input type=\"hidden\" name=\"sra\" value=\"1\" />
									<input class=\"button\" type=\"submit\" value=\"Pay With PayPal\">
									</form>";

echo "You have chosen to cancel the payment process. Your ad cannot be activated until you pay the listing fee. You can click the link below to delete your ad information, or you can click the button to make your payment now.
<p>If you do not delete your ad it will be automatically deleted after 48 hours</p>";

$savedemail=get_adposteremail($ad_id);
$ikey="$ad_id";
$ikey.="_";
$ikey.="$key";
$ikey.="_";
$ikey.="$savedemail";

echo "<p><a href=\"".$quers."deletead&k=$ikey\">Delete Ad Details</a></p>
<p>$showpaybutton</p></div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Thank you page to display to user after successfully completing payment via paypal
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function paypalthankyou () {

echo "<div id=\"classiwrapper\">";

if(isset($_REQUEST['i']) && !empty($_REQUEST['i'])){
$adinfo=$_REQUEST['i'];}
else {$adinfo='$adinfo';}

list($ad_id,$key) = split('[_]', $adinfo);

if(get_awpcp_option('adapprove') == 1){
$adawaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
$adawaitingapprovalmessage="<p>$adawaitingapprovalmsg</p>";
}
else {
$adawaitingapprovalmessage='';
}

echo "<p>Thank you for listing your ad with us. Your transaction was successfull. Your ad payment has been processed. $adawaitingapprovalmessage</p>
<p>Your ad as it will appear in the system</p>";

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
$table_name5 = $wpdb->prefix . "awpcp_adphotos";



			 $query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_details from ".$table_name3." WHERE ad_id='$ad_id'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($ad_title,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$addetails)=$rsrow;
				}

					////////////////////////////////////////////////////////////////////////////////////
					// Step:2 Show a sample of how the ad is going to look
					////////////////////////////////////////////////////////////////////////////////////

					if(!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="";}
					if(!isset($adcontact_phone) || empty($adcontact_phone))
					{
						$adcontact_phone="";
					}
					else
					{
						$adcontactphone="Phone: $adcontact_phone";
					}

					if( empty($adcontact_city) && empty($adcontact_state) && empty($adcontact_country) && empty($ad_county_village))
					{
						$location="";
					}
					else
					{
						$location="Location: ";

						if( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							//city is set, state is set, country is set
							$location.="$adcontact_city, $adcontact_state $adcontact_country";
						}
						elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( !isset($adcontact_country) || empty($adcontact_country) ) )
						{
							//city is set, state is set, country is missing
							$location.="$adcontact_city, $adcontact_state";
						}
						elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && (!isset($adcontact_state) || empty($adcontact_state)) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							// city is set, state is missing and country is set
							$location.="$adcontact_city, $adcontact_country";
						}
						elseif( (!isset($adcontact_city) || empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							//city is missing but state is set and country is set
							$location.="$adcontact_state, $adcontact_country";
						}
						elseif( (isset($adcontact_city) && !empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) && (!isset($adcontact_country) || empty($adcontact_country)) )
						{
							//  city is set, state is missing, country is missing
							$location.="$adcontact_city";
						}
						elseif( (isset($adcontact_state) && !empty($adcontact_state)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_country) || empty($adcontact_country)) )
						{
							//  state is set, city is missing, country is missing
							$location.="$adcontact_state";
						}
						elseif( (isset($adcontact_country) && !empty($adcontact_country)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) )
						{
							//  country is set, city is missing, state is missing
							$location.="$adcontact_state";
						}
						else
						{
							if(isset($adcontact_city) && !empty($adcontact_city))
							{
								$location.=" $adcontact_city";
							}
							if(isset($adcontact_state) && !empty($adcontact_state))
							{
								$location.=" $adcontact_state";
							}
							if(isset($adcontact_country) && !empty($adcontact_country))
							{
								$location.=" $adcontact_country";
							}
						}
					}

					echo "<div id=\"showad\"><div class=\"adtitle\">$ad_title</div><div class=\"adbyline\">Contact $adcontact_name $adcontactphone $location</div>
					<p class=\"addetails\">$addetails</p></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

					$theimage='';

					if(get_awpcp_option('imagesallowdisallow') == 1){

					  $query="SELECT image_name FROM ".$table_name5." WHERE ad_id=$ad_id ORDER BY image_name ASC";
					  if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
  						while ($rsrow=mysql_fetch_row($res)) {
  						list($image_name)=$rsrow;

  						echo "<li><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a></li>";

  						}

					}

					echo "</ul></div></div>";

echo "</div><div class=\"fixfloat\"></div>";

}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display listing of ad titles when browse ads is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function display_ads($where,$byl,$hidepager) {

global $wpdb,$imagesurl,$hasregionsmodule;

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');

	$quers=setup_url_structure($awpcppagename);


	echo "
			<div id=\"classiwrapper\">";

			if($hidepager == 1)
			{

				$uiwelcome=get_awpcp_option('uiwelcome');
				echo "<div class=\"uiwelcome\">$uiwelcome</div>";
			}

			echo "
			<ul id=\"postsearchads\">
			";

					$isadmin=checkifisadmin();

					if(!(get_awpcp_option('onlyadmincanplaceads')))
					{
						echo "
								<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
								<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
							";
					}

					elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1))
					{
						echo "
								<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
								<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
							";
					}
		echo "
			<li class=\"browse\"><a href=\"".$quers."categoriesview\">Browse Categories</a></li>
			<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
			</ul>
			<div class=\"fixfloat\"></div>
		";

						if($hasregionsmodule ==  1)
						{
							if( isset($_SESSION['theactiveregionid']) )
							{
								$theactiveregionid=$_SESSION['theactiveregionid'];

								$theactiveregionname=get_theawpcpregionname($theactiveregionid);


								echo "<h2>You are currently browsing in <b>$theactiveregionname</b></h2><SUP><a href=\"?a=unsetregion\">Clear $theactiveregionname session</a></SUP>";
							}
						}


	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	$from="$table_name3";

	if(!isset($where) || empty($where))
	{
		if($hasregionsmodule == 1)
		{
			if(isset($theactiveregionname) && !empty($theactiveregionname) )
			{
				$where="disabled ='0' AND (ad_city ='$theactiveregionname' OR ad_state='$theactiveregionname' OR ad_country='$theactiveregionname' OR ad_county_village='$theactiveregionname')";
			}
			else
			{
				$where="disabled ='0'";
			}
		}
		else
		{
			$where="disabled ='0'";
		}

	}

	if(get_awpcp_option('disablependingads') == 1)
	{
		if(get_awpcp_option('freepay') == 1)
		{
			$where.=" AND payment_status != 'Pending'";
		}
	}

	if(!ads_exist())
	{
	 	$showcategories="<p style=\"padding:10px\">There are currently no ads in the system</p>";
 		$pager1='';
 		$pager2='';
	}


	else
	{

			$permastruc=get_option('permalink_structure');
			$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);
			$awpcp_image_display_list=array();

			if( get_awpcp_option('seofriendlyurls') )
			{
				if(isset($permastruc) && !empty($permastruc))
				{
					$tpname="$awpcppagename";
				}
				else
				{
					$tpname="";
				}
			}

			else
			{
				$tpname="";
			}

			$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
			$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);

			if(!isset($hidepager) || empty($hidepager) )
			{
				$pager1=create_pager($from,$where,$offset,$results,$tpname);
				$pager2=create_pager($from,$where,$offset,$results,$tpname);
			}
			else
			{
				$pager1='';
				$pager2='';
			}

			$items=array();
			$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details,ad_postdate,ad_enddate,ad_views FROM $from WHERE $where ORDER BY ad_postdate DESC, ad_title ASC LIMIT $offset,$results";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$ad_id=$rsrow[0];
				$awpcppage=get_currentpagename();
				$awpcppagename = sanitize_title($awpcppage, $post_ID='');

				$modtitle=cleanstring($rsrow[2]);
				$modtitle=add_dashes($modtitle);
				$tcname=get_adcatname($rsrow[1]);
				$modcatname=cleanstring($tcname);
				$modcatname=add_dashes($modcatname);
				$category_id=$rsrow[1];
				$category_name=get_adcatname($category_id);
				$addetailssummary=awpcpLimitText($rsrow[8],10,50,"");



					if( get_awpcp_option('seofriendlyurls') )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
						 	$ad_title="<a href=\"".$quers."showad/$ad_id/$modtitle\">".$rsrow[2]."</a>";
						 	$categorylink="<a href=\"".$quers."browsecat/$category_id/$modcatname\">$category_name</a>";
						}
						else
						{
						 	$ad_title="<a href=\"".$quers."showad&id=$ad_id\">".$rsrow[2]."</a>";
						 	$categorylink="<a href=\"".$quers."browsecat&category_id=$category_id\">$category_name</a>";
						}
					}
					else
					{
						$ad_title="<a href=\"".$quers."showad&id=$ad_id\">".$rsrow[2]."</a>";
						$categorylink="<a href=\"".$quers."browsecat&category_id=$category_id\">$category_name</a>";
					}

					if( isset($rsrow[5]) && !empty($rsrow[5]) )
					{
						$awpcp_city_display="<td class=\"displayadscelllocation\"  width=\"75\" style=\"text-align:center;\">$rsrow[5]</td>";
					}
					elseif(!isset($rsrow[5]) || empty($rsrow['5']) )
					{
						if( isset($rsrow[6]) && !empty($rsrow[6]))
						{
							$awpcp_city_display="<td class=\"displayadscelllocation\" width=\"75\" style=\"text-align:center;\">$rsrow[6]</td>";
						}
						elseif(!isset($rsrow[6]) || empty($rsrow['6']) )
						{
							if( isset($rsrow[7]) && !empty($rsrow[7]))
							{
								$awpcp_city_display="<td class=\"displayadscelllocation\" width=\"75\" style=\"text-align:center;\">$rsrow[7]</td>";
							}
							elseif(!isset($rsrow[7]) || empty($rsrow['7']) )
							{
								$awpcp_city_display="<td class=\"displayadscelllocation\" width=\"75\" style=\"text-align:center;\">N/A</td>";
							}

						}

					}
					else
					{
						$awpcp_city_display="<td class=\"displayadscelllocation\"  width=\"75\" style=\"text-align:center;\">N/A</td>";
					}


					if(get_awpcp_option('imagesallowdisallow'))
					{

						$awpcp_image_display_head="<td class=\"displayadshead\" width=\"5%\" style=\"text-align:center;\"></td>";

						if( get_awpcp_option('seofriendlyurls') )
						{
							if(isset($permastruc) && !empty($permastruc))
							{
								$awpcp_image_display="<td class=\"displayadscellimg\" width=\"5%\"><a href=\"".$quers."showad/$ad_id/$modtitle\">";
							}
						else
						{
						 	$awpcp_image_display="<td class=\"displayadscellimg\" width=\"5%\"><a href=\"".$quers."showad&id=$ad_id\">";
						}
					}
					else
					{
					 	$awpcp_image_display="<td class=\"displayadscellimg\" width=\"5%\"><a href=\"".$quers."showad&id=$ad_id\">";
					}


						$totalimagesuploaded=get_total_imagesuploaded($ad_id);

						if($totalimagesuploaded >=1)
						{
							$awpcp_image_name=get_a_random_image($ad_id);
							$awpcp_image_name_srccode="<img src=\"".AWPCPTHUMBSUPLOADURL."/$awpcp_image_name\" border=\"0\" width=\"60\" alt=\"$modtitle\">";
  						}
  						else
  						{
  							$awpcp_image_name_srccode="<img src=\"$imagesurl/adhasnoimage.gif\" border=\"0\" alt=\"$modtitle\">";
  						}

  						$awpcp_image_display.="$awpcp_image_name_srccode</a></td>";



					}

					if( get_awpcp_option('displayadviews') )
					{
						$awpcp_display_adviews_head="<td class=\"displayadshead\" width=\"5%\" style=\"text-align:center;\">VIEWS</td>";
						$awpcp_display_adviews="<td class=\"displayadscellviews\" width=\"5%\" style=\"text-align:center;\">$rsrow[11]</td>";
					}



					$items[]="<tr>$awpcp_image_display<td class=\"displayadscellheadline\" width=\"50%\" valign=\"top\">$ad_title<br>$addetailssummary...</td>$awpcp_city_display<td class=\"displayadscellposted\" width=\"15%\" style=\"text-align:center;\">$rsrow[9]</td>$awpcp_display_adviews</tr>";


					$opentable="<table><tr>$awpcp_image_display_head<td class=\"displayadshead\"  width=\"50%\">HEADLINE</td><td class=\"displayadshead\" width=\"25%\" style=\"text-align:center;\">LOCATION</td><td class=\"displayadshead\" width=\"15%\" style=\"text-align:center;\">POSTED</td>$awpcp_display_adviews_head</tr>";
					$closetable="</table>";


					$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
					$showcategories="$theitems";
			}
			if(!isset($ad_id) || empty($ad_id) || $ad_id == '0'){
					$showcategories="<p style=\"padding:20px;\">There were no ads found</p>";
					$pager1='';
					$pager2='';
			}
	}


echo "<div class=\"fixfloat\"></div><div class=\"pager\">$pager1</div>";
echo "<div class=\"changecategoryselect\"><form method=\"post\"><select name=\"category_id\"><option value=\"-1\">Select Category</a>";
$allcategories=get_categorynameidall($adcategory='');
echo "$allcategories";
echo "</select><input type=\"hidden\" name=\"a\" value=\"browsecat\"><input class=\"button\" type=\"submit\" value=\"Change Category\"></form></div><div class=\"fixfloat\"></div>";
echo "$showcategories";
echo "<div class=\"pager\">$pager2</div>";
if($byl)
{
	if( field_exists($field='removepoweredbysign') && !(get_awpcp_option('removepoweredbysign')) )
	{
		echo "<p><font style=\"font-size:smaller\">Powered by <a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
	}
	elseif( field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign')) )
	{

	}
	else
	{
		echo "<p><font style=\"font-size:smaller\">Powered by <a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
	}
}
echo"</div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: show the ad when at title is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function showad($adid){


if(!isset($adid) || empty($adid)){
	if(isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){
	$adid=$_REQUEST['adid'];}
}

$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');
$permastruc=get_option(permalink_structure);
$quers=setup_url_structure($awpcppagename);


if(isset($adid) && !empty($adid)){
echo "<div id=\"classiwrapper\">";
echo "<ul id=\"postsearchads\">";

				$isadmin=checkifisadmin();

				if(!(get_awpcp_option('onlyadmincanplaceads'))){
					echo "
							<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
							<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
						";
				}

				elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1)){
					echo "
							<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
							<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
						";
				}
echo "
		<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
		<li class=\"searchcads\"><a href=\"".$quers."searchads\">Search Ads</a></li>
		</ul>
		<div class=\"fixfloat\"></div>
	";

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	//update the ad views
 	$query="UPDATE ".$table_name3." SET ad_views=(ad_views + 1) WHERE ad_id='$adid'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


if(get_awpcp_option('useadsense') == 1){
$adsensecode=get_awpcp_option('adsense');
$showadsense="<p class=\"cl-adsense\">$adsensecode</p>";}
else {$showadsense='';}

			 $query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details,websiteurl from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($ad_title,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$websiteurl)=$rsrow;
				}

					////////////////////////////////////////////////////////////////////////////////////
					// Step:2 Show a sample of how the ad is going to look
					////////////////////////////////////////////////////////////////////////////////////

					if(!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="";}
					if(!isset($adcontact_phone) || empty($adcontact_phone))
					{
						$adcontactphone="";
					}
					else
					{
						$adcontactphone="<br/>Phone: $adcontact_phone";
					}


					if( empty($adcontact_city) && empty($adcontact_state) && empty($adcontact_country) && empty($ad_county_village))
					{
						$location="";
					}
					else
					{
						$location="<br/>Location: ";

						if( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							//city is set, state is set, country is set
							$location.="$adcontact_city, $adcontact_state $adcontact_country";
						}
						elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( !isset($adcontact_country) || empty($adcontact_country) ) )
						{
							//city is set, state is set, country is missing
							$location.="$adcontact_city, $adcontact_state";
						}
						elseif( ( isset($adcontact_city) && !empty($adcontact_city) ) && (!isset($adcontact_state) || empty($adcontact_state)) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							// city is set, state is missing and country is set
							$location.="$adcontact_city, $adcontact_country";
						}
						elseif( (!isset($adcontact_city) || empty($adcontact_city) ) && ( isset($adcontact_state) && !empty($adcontact_state) ) && ( isset($adcontact_country) && !empty($adcontact_country) ) )
						{
							//city is missing but state is set and country is set
							$location.="$adcontact_state, $adcontact_country";
						}
						elseif( (isset($adcontact_city) && !empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) && (!isset($adcontact_country) || empty($adcontact_country)) )
						{
							//  city is set, state is missing, country is missing
							$location.="$adcontact_city";
						}
						elseif( (isset($adcontact_state) && !empty($adcontact_state)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_country) || empty($adcontact_country)) )
						{
							//  state is set, city is missing, country is missing
							$location.="$adcontact_state";
						}
						elseif( (isset($adcontact_country) && !empty($adcontact_country)) && (!isset($adcontact_city) || empty($adcontact_city)) && (!isset($adcontact_state) || empty($adcontact_state)) )
						{
							//  country is set, city is missing, state is missing
							$location.="$adcontact_state";
						}
						else
						{
							if(isset($adcontact_city) && !empty($adcontact_city))
							{
								$location.=" $adcontact_city";
							}
							if(isset($adcontact_state) && !empty($adcontact_state))
							{
								$location.=" $adcontact_state";
							}
							if(isset($adcontact_country) && !empty($adcontact_country))
							{
								$location.=" $adcontact_country";
							}
						}
					}

					$modtitle=cleanstring($ad_title);
					$modtitle=add_dashes($modtitle);

					if( get_awpcp_option('seofriendlyurls') )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$codecontact="contact/$adid/$modtitle";
						}
						else
						{
							$codecontact="contact&i=$adid";
						}
					}
					else
					{
						$codecontact="contact&i=$adid";
					}

					$aditemprice='';

					if( get_awpcp_option('displaypricefield') == 1)
					{
						if( !empty($ad_item_price) )
						{
							$itempricereconverted=($ad_item_price/100);
							$itempricereconverted=number_format($itempricereconverted, 2, '.', ',');
							if($itempricereconverted >=1 )
							{
								$awpcpthecurrencysymbol=awpcp_get_currency_code();
								$aditemprice="<br/><span class=\"itemprice\"><b>Price:</b> <b class=\"price\">$awpcpthecurrencysymbol $itempricereconverted</b></span>";
							}
						}
					}

					if( get_awpcp_option('displayadviews') )
					{

						$awpcpadviews_total=get_numtimesadviewd($adid);
						$awpcpadviews="<br/>Views:  $awpcpadviews_total";

					}


					echo "<div id=\"showad\"><div class=\"adtitle\"> $ad_title </div>
					<div class=\"adbyline\"><a href=\"".$quers."$codecontact\">Contact $adcontact_name</a>
					$adcontactphone $location";
					if(isset($websiteurl) && !empty($websiteurl))
					{
						echo "<br/>Website: $websiteurl";
					}
					echo "$awpcpadviews $aditemprice</div>";
					if( !empty($awpcpadviews) || !empty($aditemprice) )
					{
						echo "<div class=\"fixfloat\"></div>";
					}



					if(get_awpcp_option('adsenseposition') == 1)
					{
						echo "$showadsense";
					}

					if(get_awpcp_option('hyperlinkurlsinadtext')){
						$addetails=preg_replace("/(http:\/\/[^\s]+)/","<a href=\"\$1\">\$1</a>",$addetails);
					}

					$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "<br /><br />", $addetails);



					echo "<p class=\"addetails\">$addetails</p>";
					if(get_awpcp_option('adsenseposition') == 2){
					echo "$showadsense";
					}
					echo "</div><div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

					$theimage='';

					if(get_awpcp_option('imagesallowdisallow') == 1){

					$totalimagesuploaded=get_total_imagesuploaded($adid);

					if($totalimagesuploaded >=1){

					  $query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$adid' AND disabled='0' ORDER BY image_name ASC";
					  if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
  						while ($rsrow=mysql_fetch_row($res)) {
  						list($image_name)=$rsrow;

  						echo "<li><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a></li>";

  						}

  					}

					}

					echo "</ul></div><div class=\"fixfloat\"></div>";
					if(get_awpcp_option('adsenseposition') == 3)
					{
						echo "$showadsense";
					}
					echo "</div>";


echo "</div>";
}
else {
display_ads($where='',$byl='',$hidepager='');}

}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Uninstall
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function awpcp_uninstall(){
global $message;

	if( isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
	{
		if($_REQUEST['action'] == 'douninstall')
		{
			douninstall();
		}
	}

	if( !isset($_REQUEST['action']) || empty($_REQUEST['action']) )
	{

		$dirname=AWPCPUPLOADDIR;

		echo "<div class=\"wrap\"><h2>AWPCP Classifieds Management System: Uninstall Plugin</h2>
		$message <div style=\"padding:20px;\">Thank you for using AWPCP. You have arrived at this page by clicking the Uninstall link. If you are certain you wish to uninstall the plugin, please click the link below to proceed. Please note that all your data related to the plugin, your ads, images and everything else created by the plugin will be destroyed.
		<p><b>Important Information</b></p>
		<blockquote>
		<p>1. If you plan to use the data created by the plugin please export the data from your mysql database before clicking the uninstall link. </p>
		<p>2. If you want to keep your user uploaded images, please download $dirname to your local drive for later use or rename the folder to something else so the uninstaller can bypass it.</p>
		</blockquote>
		<a href=\"?page=Manage3&action=douninstall\">Proceed with Uninstalling Another Wordpress Classifieds Plugin</a>
		</div><div class=\"fixfloat\"></div>";
	}
}

function douninstall() {

global $wpdb,$awpcp_plugin_path,$table_prefix;

	//Remove the upload folders with uploaded images

	$dirname=AWPCPUPLOADDIR;

	if (file_exists($dirname))
	{

		require_once $awpcp_plugin_path.'/classes/fileop.class.php';

		$fileop=new fileop();
		$fileop->delete($dirname);

	}

	// Delete the classifieds page(s)

	$awpcppage=get_currentpagename();
	checkfortotalpageswithawpcpname($awpcppage);



	// Drop the tables
	   $table_name1 = $wpdb->prefix . "awpcp_categories";
	   $table_name2 = $wpdb->prefix . "awpcp_adfees";
	   $table_name3 = $wpdb->prefix . "awpcp_ads";
	   $table_name4 = $wpdb->prefix . "awpcp_adsettings";
	   $table_name5 = $wpdb->prefix . "awpcp_adphotos";
	   $table_name6 = $wpdb->prefix . "awpcp_pagename";
	   $table_name7 = $wpdb->prefix . "awpcp_regions";




		$wpdb->query("DROP TABLE " . $table_name1);
		$wpdb->query("DROP TABLE " . $table_name2);
		$wpdb->query("DROP TABLE " . $table_name3);
		$wpdb->query("DROP TABLE " . $table_name4);
		$wpdb->query("DROP TABLE " . $table_name5);
		$wpdb->query("DROP TABLE " . $table_name6);

		$table7exists=checkfortable($table_name7);

			if($table7exists)
			{
				$wpdb->query("DROP TABLE " . $table_name7);
			}



	// Remove the version number from the options table
	$query="DELETE FROM {$table_prefix}options WHERE option_name='awpcp_db_version'";
	@mysql_query($query);

	//Remove widget entries from options table
	$query="DELETE FROM {$table_prefix}options WHERE option_name='widget_awpcplatestads'";
	@mysql_query($query);

	unregister_sidebar_widget('AWPCPClassifieds', 'widget_awpcplatestads');
	unregister_widget_control('AWPCPClassifieds', 'widget_awpcplatestads_options', 350, 120);


	// Clear the ad expiration schedule
	wp_clear_scheduled_hook('doadexpirations_hook');

		$thepluginfile="another-wordpress-classifieds-plugin/awpcp.php";
		$current = get_settings('active_plugins');
		array_splice($current, array_search( $thepluginfile, $current), 1 );
		update_option('active_plugins', $current);
		do_action('deactivate_' . $thepluginfile );
		echo "<div style=\"padding:50px;font-weight:bold;\"><p>Almost done...</p><h1>One More Step</h1><a href=\"plugins.php?deactivate=true\">Please click here to complete the uninstallation process</a></h1></div>";
		die;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>