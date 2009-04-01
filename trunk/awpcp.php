<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Plugin Name: Another Wordpress Classifieds Plugin
Plugin URI: http://www.awpcp.com
Description: AWPCP - A wordpress classifieds plugin
Version: 1.0.2
Author: A. Lewis
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


require(dirname(__FILE__).'/dcfunctions.php');
require(dirname(__FILE__).'/functions_awpcp.php');


if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down


$plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
$plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));

$wpcontenturl=WP_CONTENT_URL;
$wpcontentdir=WP_CONTENT_DIR;

$imagespath = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__)).'/images';
$imagesurl = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/images';

$nameofsite=get_option('blogname');
$siteurl=get_option('siteurl');
$thisadminemail=get_option('admin_email');


$tpd = basename(dirname(__FILE__));

$awpcp_db_version = "1.0.2";

define( 'MAINUPLOADURL', $wpcontenturl . '/uploads');
define('MAINUPLOADDIR', $wpcontentdir .'/uploads/');
define( 'AWPCPUPLOADURL', $wpcontenturl . '/uploads/awpcp');
define('AWPCPUPLOADDIR', $wpcontentdir .'/uploads/awpcp/');
define( 'AWPCPTHUMBSUPLOADURL', $wpcontenturl . '/uploads/awpcp/thumbs');
define('AWPCPTHUMBSUPLOADDIR', $wpcontentdir .'/uploads/awpcp/thumbs/');
//define('AWPCPURL', $wpcontenturl.'/plugins/'.$tpd.'/' );
define('AWPCPURL', $plugin_url.'/' );
define('MENUICO', $imagesurl .'/menuico.png');


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add css file and jquery codes to header
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpjs() {
	global $plugin_url;
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-form');
	wp_enqueue_script('jquery-ui', $plugin_url.'/js/ui.tabs.pack.js', array('jquery'));
	wp_enqueue_script('jquery-lava', $plugin_url.'/js/jquery.lavalamp.min.js', array('jquery'));
	wp_enqueue_script('jquery-ea', $plugin_url.'/js/jquery.easing.min.js', array('jquery'));
	wp_enqueue_script('jquery-sc', $plugin_url.'/js/scripts.js', array('jquery'));
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add actions and filters etc
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	add_action('init', 'awpcp_install');
	add_action ('wp_print_scripts', 'awpcpjs',1);
	add_action('admin_menu', 'awpcp_launch');
	add_action('wp_head', 'awpcp_addcss');
	add_action('wp_head', 'awpcp_insertjquery');
	add_filter("the_content", "awpcpui_homescreen");
	add_action( 'doadexpirations_hook', 'doadexpirations' );
	add_action( 'init', 'prepawpcprwrules' );
	add_action("plugins_loaded", "init_awpcpsbarwidget");




function prepawpcprwrules(){
global $wpdb;
$table_name6 = $wpdb->prefix . "awpcp_pagename";

	if($wpdb->get_var("show tables like '$table_name6'") != $table_name6) {
		// awpcp_pagename table is missing so do not proceed
	}

	else {

	//read the htacess file to make sure the plugin rules are not already appended
	$permastruc=get_option('permalink_structure');


		if(isset($permastruc) && !empty($permastruc)){


		if(get_awpcp_option('seofriendlyurls') == 1){


			$filecontent=file_get_contents(ABSPATH . '/.htaccess');


			$awpcppage=get_currentpagename();
			$pprefx = sanitize_title($awpcppage, $post_ID='');
			$pageid=get_page_id($pprefx);


				$rrules=0;
				if(preg_match("/\b\/\?pagename=$pprefx&a=\b/i","$filecontent")){
					$rrules=1;
				}

				if($rrules == 0){
					awpcp_rewrite();
					//add_action('generate_rewrite_rules', 'awpcp_rewrite');
				} else {	}
			}
		}
	}
}


function awpcp_rewrite() {

	global $wpdb,$wp_rewrite;

	$table_name6 = $wpdb->prefix . "awpcp_pagename";

		if($wpdb->get_var("show tables like '$table_name6'") != $table_name6) {
			// awpcp_pagename table is missing so do not proceed
		}

		else {


		$awpcppage=get_currentpagename();
		$pprefx = sanitize_title($awpcppage, $post_ID='');
		$pageid=get_page_id($pprefx);
		$wp_rewrite->rules=array();

		add_action('init', 'flush_rewrite_rules');


		$wp_rewrite->non_wp_rules = array(

			$pprefx.'/browsecat/(.+)/(.+)' => '?pagename='.$pprefx.'&a=browsecat&category_id=$1',
			$pprefx.'/showad/(.+)/(.+)' => '?pagename='.$pprefx.'&a=showad&id=$1',
			$pprefx.'/placead'  => '?pagename='.$pprefx.'&a=placead',
			$pprefx.'/browseads'  => '?pagename='.$pprefx.'&a=browseads',
			$pprefx.'/searchads'  => '?pagename='.$pprefx.'&a=searchads',
			$pprefx.'/editad'  => '?pagename='.$pprefx.'&a=editad',
			$pprefx.'/paypal'  => '?pagename='.$pprefx.'&a=paypal',
			$pprefx.'/paypalthankyou/(.+)' => '?pagename='.$pprefx.'&a=paypalthankyou&i=$1',
			$pprefx.'/cancelpaypal/(.+)' => '?pagename='.$pprefx.'&a=cancelpaypal&i=$1',
			$pprefx.'/2checkout'  => '?pagename='.$pprefx.'&a=2checkout',
			$pprefx.'/contact/(.+)/(.+)' => '?pagename='.$pprefx.'&a=contact&i=$1',
		);


		$wp_rewrite->rules = array_merge($wp_rewrite->non_wp_rules,$wp_rewrite->rules);
		$rwstring="# BEGIN WordPress\n";
		$rwstring.=$wp_rewrite->mod_rewrite_rules();
		$rwstring.="\n# END WordPress";

		htarw($rwstring);
	}
}

function htarw($string){
	file_put_contents(ABSPATH .'.htaccess',"$string");
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
		echo "\n
		<script type=\"text/javascript\">
		var JQuery = jQuery.noConflict();
		 JQuery(function() {
		JQuery('#container-1 > ul').tabs();

		});
		</script>\n\n
			<script type=\"text/javascript\">
			var JQuery2 = jQuery.noConflict();
			JQuery2(document).ready(function() {
				JQuery2(\"#loading\")
				.ajaxStart(function(){
					JQuery2(this).show();
				})
				.ajaxComplete(function(){
					JQuery2(this).hide();
				});
				var options = {
					beforeSubmit:  showRequest,
					success:       showResponse,
					url:       '".AWPCPURL."upload4jquery.php',  // your upload script
					dataType:  'json'
				};
				JQuery2('#Form1').submit(function() {
					document.getElementById('message').innerHTML = '';
					JQuery2(this).ajaxSubmit(options);
					return false;
				});
			});

			function showRequest(formData, jqForm, options) {
				var fileToUploadValue = JQuery2('input[@name=fileToUpload]').fieldValue();
				if (!fileToUploadValue[0]) {
					document.getElementById('message').innerHTML = 'Please select a file.';
					return false;
				}

				return true;
			}

			function showResponse(data, statusText)  {
				if (statusText == 'success') {

					if (data.img != '') {
						document.getElementById('result').innerHTML = '<img src=\"".AWPCPUPLOADURL."/thumbs/'+data.img+'\" />';
						document.getElementById('ustatmsg').innerHTML = data.ustatmsg;
						document.getElementById('message').innerHTML = data.error;
					} else {
						document.getElementById('message').innerHTML = data.error;
						document.getElementById('ustatmsg').innerHTML = data.ustatmsg;

					}

					if(data.showhideuploadform == '1'){
							document.getElementById(\"showhideuploadform\").style.display=\"none\";
					}
				} else {
					document.getElementById('message').innerHTML = 'Unknown error!';
					document.getElementById('ustatmsg').innerHTML = data.ustatmsg;
				}
			}

			</script>\n";
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
	  `category_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  PRIMARY KEY (`category_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


	CREATE TABLE " . $table_name2 . " (
	  `adterm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `adterm_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `amount` float(6,2) unsigned NOT NULL DEFAULT '0.00',
	  `recurring` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `rec_period` int(5) unsigned NOT NULL DEFAULT '0',
	  `rec_increment` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `buys` int(10) unsigned NOT NULL DEFAULT '0',
	  `imagesallowed` int(5) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`adterm_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


	CREATE TABLE " . $table_name3 . " (
	  `ad_id` int(10) NOT NULL AUTO_INCREMENT,
	  `adterm_id` int(10) NOT NULL,
	  `ad_category_id` int(10) NOT NULL,
	  `ad_category_parent_id` int(10) NOT NULL,
	  `ad_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_details` text COLLATE utf8_unicode_ci NOT NULL,
	  `ad_contact_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_contact_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_contact_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_postdate` date NOT NULL DEFAULT '0000-00-00',
	  `ad_last_updated` date NOT NULL,
	  `ad_startdate` datetime NOT NULL,
	  `ad_enddate` datetime NOT NULL,
	  `disabled` tinyint(1) NOT NULL DEFAULT '0',
	  `ad_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `ad_transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `payment_gateway` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `payment_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  PRIMARY KEY (`ad_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;



	CREATE TABLE " . $table_name4 . " (
	  `config_option` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `config_value` text COLLATE utf8_unicode_ci NOT NULL,
	  `config_diz` text COLLATE utf8_unicode_ci NOT NULL,
	  `option_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`config_option`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='0-checkbox, 1-text,2-textarea';


	CREATE TABLE " . $table_name5 . " (
	  `key_id` int(10) NOT NULL AUTO_INCREMENT,
	  `ad_id` int(10) unsigned NOT NULL DEFAULT '0',
	  `image_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `disabled` tinyint(1) NOT NULL,
	  PRIMARY KEY (`key_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


	CREATE TABLE " . $table_name6 . " (
	  `key_id` int(10) NOT NULL AUTO_INCREMENT,
	  `userpagename` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  PRIMARY KEY (`key_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


	INSERT INTO " . $table_name1 . " (`category_id`, `category_parent_id`, `category_name`) VALUES
	(1, 0, 'General');

	INSERT INTO " . $table_name2 . " (`adterm_id`, `adterm_name`, `amount`, `recurring`, `rec_period`, `rec_increment`, `buys`, `imagesallowed`) VALUES
	(1, '30 Day Listing', 9.99, 1, 31, 'D', 0, 6);


	INSERT INTO " . $table_name4 . " (`config_option`, `config_value`, `config_diz`, `option_type`) VALUES
		('userpagename', 'AWPCP', 'The name of the page for the user side access to the classifieds [CAUTION: Please make sure you do not already have a page using the same name because the current page will be overwritten]', 1),
		('freepay', '0', 'You can run a free or paid classified listing service. With the box checked you are running in pay mode. With the box unchecked you are running in free mode', 0),
		('paylivetestmode', '1', 'By default both paypal and 2checkout are running in live mode. If you prefer to test to make sure payments are being processed to your accounts uncheck this box to switch to sandbox test mode.', 0),
		('useadsense', '1', 'Should adsense ads be displayed in ads? Check to activate. Uncheck to deactivate.', 0),
		('adsense', 'Replace this text with your adsense code', 'Your adsense code (Best if 468 by 60 text or banner.', 2),
		('adsenseposition', '2', 'Where do you want to display your adsense code? 1= directly above the ad text body 2= directly under the ad text body 3=below the ad images if there are any.', 1),
		('addurationfreemode', '0', 'If you are running in free mode enter the value in days for the number of days ads can stay in the system before they expire. If you do not want the ads to expire leave the value set to zero [0] Note that [0] is treated like a period of25 years. Also note that expired ads are automatically deleted from the system.', 1),
		('imagesallowdisallow', '1', 'Uncheck to disallow images in ads. Check to allow. [Affects both free and paid]', 0),
		('imagesallowedfree', '4', 'If allowing images and running in free mode, how many images are allowed per ad?', 1),
		('maximagesize', '150000', 'If allowing images set the maximum file size that can be uploaded per image.', 1),
		('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?', 1),
		('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]', 1),
		('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]', 1),
		('activatepaypal', '1', 'Check to activate paypal as a payment gateway. Uncheck to deactivate. [If running in pay mode]', 0),
		('activate2checkout', '1', 'Check to activate 2Checkout as a payment gateway. Uncheck to deactivate. [If running in pay mode] ', 0),
		('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?', 0),
		('notifyofadposted', '1', 'Check this to notify you the admin when a new ad is posted. Leave unchecked if you do not want to be notified.', 0),
		('imagesapprove', '0', 'If allowing images, check this to hide images in ads until admin has approved the images, or leave unchecked to display images immediately', 0),
		('adapprove', '0', 'Check this to enable ad approval so a new ad has to first be approved by the adminstrator before someone can view it on the site. Uncheck if you prefer all ads to be automatically displayed.', 0),
		('disablependingads', '1', 'If running in Pay mode, uncheck this if you want ads to be visible even if payment status is Pending instead of Completed.', 0),
		('showadcount', '1', 'Uncheck this to disable showing how many ads a category contains. Check it if you want to display how many ads a category contains [User side only]', 0),
		('contactformcheckhuman', '1', 'Uncheck this box if you want to disable the math problem used to check if the person filling out the form to contact about an ad is human. ', '0'),
		('seofriendlyurls', '0', 'Search Engine Friendly URLs? Checking the box will make all URLs generated by the plugin more search engine friendly. Unchecking the box will revert to the default link structure. This will only work if your main wordpress installation is using something other than the default permalink structure.', 0),
		('allowhtmlinadtext', '0', 'Check this if you want to allow people to be able to use HTML in their ad text. Leave unchecked to disallow HTML. <b>It is highly recommended that you DO NOT ENABLE HTML as it could invite spam and other more malicious abuses.<\/b>', 0),
		('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business. If your ad does not pass approval your listing fee will be refunded.','The message to print after an ad has been posted if you are manually approving ads before they are displayed on the site', 2),
		('displayphonefield', '1', 'Uncheck this if you prefer to hide the phone input field. Check it to show the phone input field.', 0),
		('displayphonefieldreqop', '0', 'If showing the phone input field check this if the user is required to enter a phone number. [SUGGESTION: It is probably better to leave unchecked so phone number is optional.]', 0),
		('displaycityfield', '1', 'Uncheck this if you prefer to hide the city input field. Check it to show the city input field.', 0),
		('displaycityfieldreqop', '0', 'If showing the city input field check this if the user is required to enter a city. [SUGGESTION: It is probably better to leave unchecked so city is optional.]', 0),
		('displaystatefield', '1', 'Uncheck this if you prefer to hide the state input field. Check it to show the state input field.', 0),
		('displaystatefieldreqop', '0', 'If showing the state field check this if the user is required to enter a state. [SUGGESTION: It is probably better to leave unchecked so state is optional.]', 0),
		('displaycountryfield', '1', 'Uncheck this if you prefer to hide the country input field. Check it to show the country input field.', 0),
		('displaycountryfieldreqop', '0', 'If showing the country input field, check this if the user is required to enter a country. [SUGGESTION: It is probably better to leave unchecked so country is optional.]', 0),
		('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side', 2);";

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

	// Do the updates

	// Fix the UTF-8 Charset problem and add option contactformcheckhuman to awpcp_adsettings (March 25 2009)


	   $table_name1 = $wpdb->prefix . "awpcp_categories";
	   $table_name2 = $wpdb->prefix . "awpcp_adfees";
	   $table_name3 = $wpdb->prefix . "awpcp_ads";
	   $table_name4 = $wpdb->prefix . "awpcp_adsettings";
	   $table_name5 = $wpdb->prefix . "awpcp_adphotos";
	   $table_name6 = $wpdb->prefix . "awpcp_pagename";

	   $wpdb->query("ALTER TABLE " . $table_name1 . "  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
	   $wpdb->query("ALTER TABLE " . $table_name2 . "  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
	   $wpdb->query("ALTER TABLE " . $table_name3 . "  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
	   $wpdb->query("ALTER TABLE " . $table_name4 . "  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
	   $wpdb->query("ALTER TABLE " . $table_name5 . "  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
	   $wpdb->query("ALTER TABLE " . $table_name6 . "  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

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
	global $wpdb, $isclassifiedpage, $table_prefix;
	if ($isclassifiedpage == false){
		$isclassifiedpage = $wpdb->get_row("SELECT * FROM {$table_prefix}posts
			WHERE post_title = '$pagename'", ARRAY_A);
		if ($isclassifiedpage["post_title"]!="$pagename"){
			return false;
		}
	}
	return $isclassifiedpage;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Launch the main classifieds screen and add the menu items
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_launch(){
add_menu_page('AWPCP Classifieds Management System', 'Classifieds', '10', 'awpcp.php', 'awpcp_home_screen', MENUICO);
add_submenu_page('awpcp.php', 'AWPCP General Settings', 'Configure', '10', 'awpcp.php', 'awpcp_home_screen');
add_submenu_page('awpcp.php', 'Configure General Options ', 'Settings', '10', 'Configure1', 'awpcp_opsconfig_settings');
add_submenu_page('awpcp.php', 'Listing Fees Setup', 'Fees', '10', 'Configure2', 'awpcp_opsconfig_fees');
add_submenu_page('awpcp.php', 'Add/Edit Categories', 'Categories', '10', 'Configure3', 'awpcp_opsconfig_categories');
add_submenu_page('awpcp.php', 'Manage Ads', 'Manage', '10', 'awpcp.php', 'awpcp_home_screen');
add_submenu_page('awpcp.php', 'View Ad Listings', 'Listings', '10', 'Manage1', 'awpcp_manage_viewlistings');
add_submenu_page('awpcp.php', 'View Ad Images', 'Images', '10', 'Manage2', 'awpcp_manage_viewimages');
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

global $message,$user_identity,$wpdb;
$table_name4 = $wpdb->prefix . "awpcp_adsettings";


	echo "<div class=\"wrap\"><h2>AWPCP Classifieds Management System</h2>
	$message <div style=\"padding:20px;\">Thank you for using AWPCP. Please use this plugin knowing that is it is a work in progress and is by no means guaranteed to be a bug-free product. Development of this plugin is not a full-time undertaking. Consequently upgrades will be slow in coming; however, please feel free to report bugs and request new features.</div><div style=\"clear:both;\"></div>";

if($wpdb->get_var("show tables like '$table_name4'") != $table_name4) {

echo "<b>!!!!ALERT:</b>There appears to be a problem with the plugin. The plugin is activated but your database tables are missing. Please de-activate the plugin from your plugins page then try to reactivate it.";}

else {

	$cpagename=get_awpcp_option('userpagename');

	$isclassifiedpage = checkifclassifiedpage($cpagename);
	if ($isclassifiedpage == false){
		echo "<h2>Setup Process</h2>";

	echo "<p>It looks like you have not yet told the system how you want your classifieds to operate.</p>

	<p>Please begin by setting up the options for your site. The system needs to know a number of things about how you want to run your classifieds.</p>
	<a href=\"?page=Configure1\">Click here to setup your site options</a></p>";

	} else {



echo "
<div style=\"padding:10px;\">
<div style=\"float:left;width:50%;\">";
$totallistings=countlistings();
echo "<div style=\"background-color: #eeeeee;padding:10px;\"border:1px dotted #dddddd;>There are currently [<b>$totallistings</b>] ads in the system</div>";

if(get_awpcp_option(freepay) == '1'){
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

if(get_awpcp_option(freepay) == '1'){
echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\"> You currently have your system configured to run in pay mode. To change to <b>free</b> mode go to \"Manage General Options\" and check the box that accompanies the text [ <em>You can run a free or paid classified listing service. With the box checked you are running in pay mode. With the box unchecked you are running in free mode</em> ]</div>";}


echo "
<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\">Use the buttons on the right to configure your various options</div>
</div>
<div style=\"float:left;width:30%;margin:0 0 0 20px;\">
<ul>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure1\">Manage General Options</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure2\">Manage Listing Fees</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure3\">Manage Categoties</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage1\">Manage Listings</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage2\">Manage Images</a></li>
</ul></div>

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

global $wpdb;
global $message;

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

global $wpdb;
global $message;
global $clearedits;
global $clearaction;



$table_name1 = $wpdb->prefix . "awpcp_categories";
$cat_ID='';
$category_name='';
$aeaction='';
$category_parent_id='';

		///////////////////////////////////////////////////
		// Check for existence of a category ID
		//////////////////////////////////////////////////

	if(isset($_REQUEST['cat_ID']) && !empty($_REQUEST['cat_ID'])){

		if(isset($clearedits)){unset($cat_ID);}// Doing this to clear the edit box in order to prevent accidental modification of cateogory already edited

		else {
		$cat_ID=$_REQUEST['cat_ID'];
		$category_name=get_adcatname($cat_ID);
		$cat_parent_ID=get_cat_parent_ID($cat_ID);
		}

	}


	if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])){

	$action=$_REQUEST['action'];}

	if(isset($clearaction)){unset($action);}

	if($action == 'edit'){$aeaction="edit";$backfont="background:#FFFFcc";}
	if($action == 'delcat'){$aeaction="delete";$backfont="background:#D54E21;color:white;";}
	if($aeaction == 'edit'){$aeword1="You are currently editing the category shown in the box.";$aeword2="Edit Current Category";$aeword3="Parent Category";$addnewlink="<a href=\"?page=Configure3\">Add A New Category</a>";}
	elseif($aeaction == 'delete'){$aeword1="If you're sure that you want to delete this category please press the delete button"; $aeword2="Delete this category";$aeword3="The Category is listed in";$addnewlink="<a href=\"?page=Configure3\">Add A New Category</a>";}
	else {$aeword1="Enter the category name";$aeword2="Add New Category";$aeword3="Parent Category";$addnewlink='';$backfont="background:#eeeeee";}


		/////////////////////////////////
		// Start the page display
		/////////////////////////////////

echo "<div class=\"wrap\">
<h2>AWPCP Classifieds Management System: Categories Management</h2>
 $message <p style=\"padding:10px;\">Below you can add and edit your categories. Each category can have multiple subcategories; however subcategories cannot have sub-categories. In other words you can have a parent category with children but the children cannot then have children.</p>

 <div style=\"width:30%;float:left;padding:10px;border-bottom:1px solid #dddddd;border-top:1px dotted #dddddd;$backfont\">
 <form method=\"post\" id=\"awpcp_launch\">
 <input type=\"hidden\" name=\"category_id\" value=\"$cat_ID\" />
  <input type=\"hidden\" name=\"aeaction\" value=\"$aeaction\" />

<p>$aeword1</p>
<input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/>


<p style=\"margin-top:10px;\"> $aeword3</p>
<p><select name=\"category_parent_id\"><option value=\"0\">Save as Top Level Category</option>";
$categories=  get_categorynameid($cat_ID,$cat_parent_ID);
echo "$categories
</select></p>

 <p class=\"submit\"><input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" /> $addnewlink</p>
 </form>
 </div>
 <div style=\"padding:10px;float:left;width:60%\">

 <h3>Categories List</h3>";

 	///////////////////////////////////////////////////////////
 	// Show the paginated categories list for management
 	//////////////////////////////////////////////////////////

 $from="$table_name1";
 $where="category_name <> ''";
 $offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
 $results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);
 $pager1=create_pager($from,$where,$offset,$results,$tpname='');
 $pager2=create_pager($from,$where,$offset,$results,$tpname='');

 echo "$pager1 <form>";

 $items=array();
  $query="SELECT category_id,category_name,category_parent_id FROM $from WHERE $where ORDER BY category_name ASC LIMIT $offset,$results";
  if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
  	while ($rsrow=mysql_fetch_row($res)) {
  		$thecategory_id=$rsrow[0];
  		$thecategory_name="<a href=\"?page=Configure3&cat_ID=".$rsrow[0]."&action=edit\">".$rsrow[1]."</a>";
  		$thecategory_parent_id=$rsrow[2];

  		$thecategory_parent_name=get_adparentcatname($thecategory_parent_id);

  		$items[]="<tr><td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;\">$thecategory_name</td>
  		<td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;\">$thecategory_parent_name</td>
  		<td style=\"padding:5px;border-bottom:1px dotted #dddddd;font-size:smaller;\"><a href=\"?page=Configure3&cat_ID=$thecategory_id&action=edit\">Edit</a> | <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=delcat\">Delete</a></td></tr>";
  		}

  		$opentable="<table class=\"listcatsh\">
 	 	<tr><td style=\"width:40%;padding:5px;\">Category Name</td>
		<td style=\"width:40%;padding:5px;\">Parent</td>
		<td style=\"width:20%;padding:5px;;\">Action</td></tr>";
		$closetable="<tr><td style=\"width:40%;padding:5px;\">Category Name</td>
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

	elseif($laction == 'editad'){
	$editemail=get_adposteremail($actonid);
	$adaccesskey=get_adkey($actonid);
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$offset=addslashes_mq($_REQUEST['offset']);
	$results=addslashes_mq($_REQUEST['results']);

	load_ad_post_form($actonid,$action='editad',$awpcppagename,$adtermid='',$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='');

	}

	elseif($laction == 'dopost1'){

		$adid=addslashes_mq($_REQUEST['adid']);
		$adterm_id=addslashes_mq($_REQUEST['adterm_id']);
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

		processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg);

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

		$query="UPDATE  ".$table_name3." SET payment_status='$changeto' WHERE ad_id='$actonid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The ad has been approved</div>";

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


		$from="$table_name3";
		if(!isset($where) || empty($where)){
			$where="ad_title <> ''";
		}

			if(!ads_exist()){
				$showcategories="<p style=\"padding:10px\">There are currently no ads in the system</p>";
				$pager1='';
				$pager2='';
			}



			else {

					$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
					$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);



					$items=array();
					$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details,ad_postdate,disabled,payment_status FROM $from WHERE $where ORDER BY ad_postdate DESC LIMIT $offset,$results";
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
						$disabled=$rsrow[10];
						$paymentstatus=$rsrow[11];

						if(!isset($paymentstatus) || empty($paymentstatus)){
							$paymentstatus="N/A";
						}



 							$pager1=create_pager($from,$where,$offset,$results,$tpname='');
							$pager2=create_pager($from,$where,$offset,$results,$tpname='');
							$base=get_option(siteurl);
							$awpcppage=get_currentpagename();
							$awpcppagename = sanitize_title($awpcppage, $post_ID='');
							$pageid=get_page_id($awpcppagename);

								$ad_title="<a href=\"$base/?page_id=$pageid&a=showad&id=$ad_id\" target=\"_blank\">".$rsrow[2]."</a>";
								$handlelink="<a href=\"?page=Manage1&action=deletead&id=$ad_id&offset=$offset&results=$results\">Delete</a> | <a href=\"?page=Manage1&action=editad&id=$ad_id&offset=$offset&results=$results\">Edit</a>";

								$approvelink='';
								if(get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1){

									if($disabled == '1'){
										$approvelink="<a href=\"?page=Manage1&action=approvead&id=$ad_id&offset=$offset&results=$results\">Approve</a> | ";
									}
									else {
										$approvelink="<a href=\"?page=Manage1&action=rejectad&id=$ad_id&offset=$offset&results=$results\">Disable </a> | ";
									}
								}


							if(get_awpcp_option('freepay') == '1'){

							$changepaystatlink='';

								if($paymentstatus == 'Pending'){
									$changepaystatlink="<a href=\"?page=Manage1&action=cps&id=$ad_id&changeto=Completed\">Complete</a>";
								}

									$paymentstatus="<b>Payment Status</b> [ $paymentstatus <SUP>$changepaystatlink</SUP>]";



							}
							else {

								$paymentstatus="";
							}

							if(get_awpcp_option(imagesallowdisallow) == "1"){

								$totalimagesuploaded=get_total_imagesuploaded($ad_id);

									if($totalimagesuploaded >= '1'){
										$viewimages="<a href=\"?page=Manage1&action=viewimages&id=$ad_id\">View Images</a>";
									}
									else {
										$viewimages="";
									}

								$imagesnote="<b>Total Images</b> [ $totalimagesuploaded ] $viewimages";
							}
							else {$imagesnote="";}



							$items[]="<tr><td class=\"displayadsicell\">$ad_title :: <b>Manage</b> [ $approvelink $handlelink ] $paymentstatus $imagesnote</td></tr>";


							$opentable="<table class=\"listcatsh\"><tr><td style=\"padding:5px;\">Current Ads</td></tr>";
							$closetable="<td style=\"padding:5px;\">Current Ads</td></tr></table>";


							$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
							$showcategories="$theitems";
					}
					if(!isset($ad_id) || empty($ad_id) || $ad_id == '0'){
							$showcategories="<p style=\"padding:20px;\">There were no ads found</p>";
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
		</style>
		$pager1
		 $showcategories
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

							if($disabled == '1'){
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

	$query="SELECT config_option,option_type FROM ".$table_name4."";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	$myoptions=array();
	for ($i=0;$i<mysql_num_rows($res);$i++) {
		list($config_option,$option_type)=mysql_fetch_row($res);
		if (isset($_POST[$config_option])) {

			$myoptions[$config_option]=addslashes_mq($_POST[$config_option],true);
			$newuipagename=$myoptions['userpagename'];

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
		$query="UPDATE ".$table_name4." SET config_value='$v' WHERE config_option='$k'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	}



		// Create the classified user page if it does not exist
			if(empty($currentuipagename)){
			maketheclassifiedpage($newuipagename);}
			elseif(isset($currentuipagename) && !empty($currentuipagename)){
				if(findpage($currentuipagename)){updatetheclassifiedpagename($currentuipagename,$newuipagename);}
				elseif(!(findpage($currentuipagename))){deleteuserpageentry($currentuipagename);maketheclassifiedpage($newuipagename);};

			}
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The data has been updated!</div>";
	global $message;
}


		///////////////////////////////////////////////////////////////////////
		//	Start process of creating | updating  userside classified page
		//////////////////////////////////////////////////////////////////////

function maketheclassifiedpage($newuipagename){

add_action('init', 'flush_rewrite_rules');
global $wpdb,$table_prefix,$wp_rewrite;
$table_name6 = $wpdb->prefix . "awpcp_pagename";
$pdate = date("Y-m-d");

$post_name = sanitize_title($newuipagename, $post_ID='');

		$query="INSERT INTO {$table_prefix}posts SET post_author='1', post_date='$pdate', post_date_gmt='$pdate', post_content='[[AWPCPCLASSIFIEDSUI]]', post_title='$newuipagename', post_category='0', post_excerpt='', post_status='publish', comment_status='closed', ping_status='', post_password='', post_name='$post_name', to_ping='', pinged='', post_modified='$pdate', post_modified_gmt='$pdate', post_content_filtered='[[AWPCPCLASSIFIEDSUI]]', post_parent='', guid='', post_type='page', menu_order=''";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		$pageid=mysql_insert_id();
		$guid = get_option('home') . "/?page_id=$pageid";

		$query="UPDATE {$table_prefix}posts set guid='$guid' WHERE post_title='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$query="INSERT INTO ".$table_name6." SET userpagename='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
}

function updatetheclassifiedpagename($currentuipagename,$newuipagename){
global $wpdb,$table_prefix, $wp_rewrite;
$table_name6 = $wpdb->prefix . "awpcp_pagename";

$post_name = sanitize_title($newuipagename, $post_ID='');


		$query="UPDATE {$table_prefix}posts set post_title='$newuipagename', post_name='$post_name' WHERE post_title='$currentuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$query="UPDATE ".$table_name6." SET userpagename='$newuipagename' WHERE userpagename='$currentuipagename'";
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
	$ad_word_length=addslashes_mq($_REQUEST['ad_word_length']);
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

		$category_id=addslashes_mq($_REQUEST['category_id']);
		$category_name=addslashes_mq($_REQUEST['category_name']);
		$category_parent_id=addslashes_mq($_REQUEST['category_parent_id']);
		$aeaction=addslashes_mq($_REQUEST['aeaction']);

		if($aeaction == 'delete'){
			$query="DELETE FROM  ".$table_name1." WHERE category_id='$category_id'";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
			$handleword="deleted";
			$clearedits=true;
			$clearaction=true;

		}

		else {

			if($aeaction == 'edit'){
				$query="UPDATE ".$table_name1." SET category_name='$category_name',category_parent_id='$category_parent_id' WHERE category_id='$category_id'";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
				$handleword="updated";
				$clearedits=true;
				$clearaction=true;

			}else {
				$query="INSERT INTO ".$table_name1." SET category_name='$category_name',category_parent_id='$category_parent_id'";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
				$handleword="added";
			}

		}
		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">The new category/sub-category has been $handleword!</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	User Side functions and processes
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpui_homescreen($content){
global $classicontent;
$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');

if(is_page($awpcppagename)) {

if(!isset($classicontent) || empty($classicontent)){
$classicontent=awpcpui_process($awpcppagename);}

$content = preg_replace( "/\[\[AWPCPCLASSIFIEDSUI\]\]/", $classicontent, $content);

}
return $content;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	FUNCTION: display the home screen for user  side
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpui_process($awpcppagename) {

		global $plugin_url;

		//Retrieve the welcome message for the user
		$uiwelcome=get_awpcp_option('uiwelcome');

		if(isset($_REQUEST['a']) && !empty($_REQUEST['a'])){
			$action=$_REQUEST['a'];
		}

		if($action == 'placead'){
			load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$adpaymethod,$offset='',$results='',$ermsg='');
		}


		elseif($action == 'editad'){
			load_ad_edit_form($action,$awpcppagename);
		}

		elseif($action == 'browseads'){
			$where="disabled ='0'";
			display_ads($where);
		}

		elseif($action == 'browsecat'){
			if(isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id'])){
			$adcategory=$_REQUEST['category_id'];
			$where="(ad_category_id='".$adcategory."' OR ad_category_parent_id='".$adcategory."') AND disabled ='0'";
			}
					display_ads($where);
		}

		elseif($action == 'showad'){
			if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
				$adid=$_REQUEST['id'];
			}
			showad($adid);
		}

		elseif($action == 'contact'){
			if(isset($_REQUEST['i']) && !empty($_REQUEST['i'])){
				$adid=$_REQUEST['i'];
			}
			load_ad_contact_form($adid);
		}

		elseif($action == 'docontact1'){
			$adid=addslashes_mq($_REQUEST['adid']);
			$sendersname=addslashes_mq($_REQUEST['sendersname']);
			$checkhuman=addslashes_mq($_REQUEST['checkhuman']);
			$numval1=addslashes_mq($_REQUEST['numval1']);
			$numval2=addslashes_mq($_REQUEST['numval2']);
			$sendersemail=addslashes_mq($_REQUEST['sendersemail']);
			$contactmessage=addslashes_mq($_REQUEST['contactmessage']);

			processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);

		}

		elseif($action == 'searchads'){
			load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcategory='',$message='');
		}

		elseif($action == 'dosearch'){
			dosearch();
		}

		elseif($action == 'doadedit1'){
			$adaccesskey=addslashes_mq($_REQUEST['adaccesskey']);
			$editemail=addslashes_mq($_REQUEST['editemail']);
			$awpcpagename=addslashes_mq($_REQUEST['awpcpagename']);
			editadstep1($adaccesskey,$editemail,$awpcpagename);
		}

		elseif($action == 'dopost1'){
			$adid=addslashes_mq($_REQUEST['adid']);
			$adterm_id=addslashes_mq($_REQUEST['adterm_id']);
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

			processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg);

		}


		elseif($action == 'dp'){
			if(isset($_REQUEST['k']) && !empty($_REQUEST['k'])){
				$keyids=$_REQUEST['k'];
				list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
			}
			deletepic($picid,$adid,$adtermid,$adkey,$editemail);
		}

		elseif($action == 'paypal'){
			do_paypal();
		}

		elseif($action == '2checkout'){
			do_2checkout();
		}

		elseif($action == 'cancelpaypal'){
			cancelpaypal();
		}

		elseif($action == 'paypalthankyou'){
			paypalthankyou();
		}


		elseif($action == 'adpostfinish'){
			if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
				$adaction=$_REQUEST['adaction'];
			}
			if(isset($_REQUEST['ad_id']) && !empty($_REQUEST['ad_id'])){
				$theadid=$_REQUEST['ad_id'];
			}
			if(isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){
				$theadkey=$_REQUEST['adkey'];
			}

			if($adaction == 'editad'){
				showad($theadid);
			}

			else {
				ad_success_email($theadid,$txn_id='',$theadkey,$message,$gateway='');
			}
		}

		elseif($action == 'deletead'){
			if(isset($_REQUEST['k']) && !empty($_REQUEST['k'])){
				$keyids=$_REQUEST['k'];
				list($adid,$adkey,$editemail) = split('[_]', $keyids);
			}
			deletead($adid,$adkey,$editemail);

		}

		else {

			$quers='';
			global $siteurl;
			$permastruc=get_option('permalink_structure');
			if(!isset($permastruc) || empty($permastruc)){
			$pageid=get_page_id($awpcppagename);
			$quers="?page_id=$pageid&a=";}
			elseif(get_awpcp_option('seofriendlyurls') == '1'){
			$quers="$siteurl/$awpcppagename/";}
			else {$quers="?a=";}



			echo "
			<div id=\"classiwrapper\">
			<ul id=\"postsearchads\">
			<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
			<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
			<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
			<li class=\"search\"><a href=\"".$quers."searchads\">Search Ads</a></li>
			</ul>


			<div style=\"clear:both;\"></div>
			<div class=\"uiwelcome\">$uiwelcome</div>
			<div class=\"classifiedcats\">";
			//Display the categories

			global $wpdb;
			$table_name1 = $wpdb->prefix . "awpcp_categories";

			$table_cols=1;
			$query="SELECT category_id,category_name FROM ".$table_name1." WHERE category_parent_id='0' ORDER BY category_name ASC";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
			$myreturn='';

				if (mysql_num_rows($res)) {
				$myreturn="<table class=\"awpcpuitableouter\">";

				$i=1;

					while ($rsrow=mysql_fetch_row($res)) {

						if(get_awpcp_option('showadcount') == '1'){
							$adsincat1=total_ads_in_cat($rsrow[0]);
							$adsincat1="($adsincat1)";
						}
						else {
							$adsincat1='';
						}

						$modcatname1=cleanstring($rsrow[1]);
						$modcatname1=add_dashes($modcatname1);

						if (($i%$table_cols)==1) {$myreturn.="<tr>\n";}
						$myreturn.="\t<td>\n";
						if(get_awpcp_option('seofriendlyurls') == '1' && isset($permastruc)){
						$myreturn.="<table class=\"awpcpuitableinner\"><tr><td><a href=\"".$quers."browsecat/$rsrow[0]/$modcatname1\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</td></tr></table>";
						}else {
						$myreturn.="<table class=\"awpcpuitableinner\"><tr><td><a href=\"".$quers."browsecat&category_id=$rsrow[0]\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</td></tr></table>";
						}


						//Get sub categories if any
						$myreturn.="<div class=\"childitems\"><ul>";
						$mcid=$rsrow[0];
						$query="SELECT category_id,category_name FROM ".$table_name1." WHERE category_parent_id='$mcid' ORDER BY category_name ASC";
						if (!($res2=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							while ($rsrow2=mysql_fetch_row($res2)) {

								if(get_awpcp_option('showadcount') == '1'){
								$adsincat2=total_ads_in_cat($rsrow2[0]);
								$adsincat2="($adsincat2)";
								}
								else {$adsincat2='';}

								if(!empty($rsrow2[0])){
									$modcatname2=cleanstring($rsrow2[1]);
									$modcatname2=add_dashes($modcatname2);

									if(get_awpcp_option('seofriendlyurls') == '1' && isset($permastruc)){
										$myreturn.="<li><a href=\"".$quers."browsecat/$rsrow2[0]/$modcatname2\">$rsrow2[1]</a>$adsincat2</li>";
									}else {
										$myreturn.="<li><a href=\"".$quers."browsecat&category_id=$rsrow2[0]\">$rsrow2[1]</a>$adsincat2</li>";
									}
								}
							}
						$myreturn.="</ul></div></td>\n";
						$myreturn.="\n";
						$myreturn.="\t</td>\n";
						if ($i%$table_cols==0) {$myreturn.="</tr>\n";}
						$i++;
					}

						$rest=($i-1)%$table_cols;
						if ($rest!=0) {
							$colspan=$table_cols-$rest;
							$myreturn.="\t<td".(($colspan==1) ? ("") : (" colspan=\"$colspan\""))."></td>\n</tr>\n";
						}
						$myreturn.="</table>\n";
				}

				echo "$myreturn</div>";
				echo "<font style=\"font-size:smaller\">Powered by <a href=\"http://www.awpcp.com\" target=\"_blank\">AWPCP</a> </font></div>";
}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End function
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	FUNCTION: display the ad post form
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail,$adaccesskey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$adpaymethod,$offset,$results,$ermsg){


	$isadmin=0;
	global $user_level;
	get_currentuserinfo();

		if($user_level == '10'){
		$isadmin=1;
		}


		global $wpdb,$siteurl;
		$table_name2 = $wpdb->prefix . "awpcp_adfees";
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$images='';
		$displaydeleteadlink='';

		$quers='';
		$permastruc=get_option(permalink_structure);
		if(!isset($permastruc) || empty($permastruc)){
		$pageid=get_page_id($awpcppagename);
		$quers="?page_id=$pageid&a=";}
		elseif(get_awpcp_option('seofriendlyurls') == '1'){
		$quers="$siteurl/$awpcppagename/";}
		else {$quers="?a=";}

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

				if($action == 'editad'){

					$savedemail=get_adposteremail($adid);

					if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 )) {

					 $query="SELECT ad_title,ad_contact_name,ad_contact_email,ad_category_id,ad_contact_phone,ad_city,ad_state,ad_country,ad_details,ad_key from ".$table_name3." WHERE ad_id='$adid' AND ad_contact_email='$editemail' AND ad_key='$adaccesskey'";
					 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						while ($rsrow=mysql_fetch_row($res)) {
							list($adtitle,$adcontact_name,$adcontact_email,$adcategory,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$ad_key)=$rsrow;
						}

						$ikey="$adid";
						$ikey.="_";
						$ikey.="$adaccesskey";
						$ikey.="_";
						$ikey.="$editemail";
						$displaydeleteadlink="<p class=\"alert\"><a href=\"".$quers."deletead&k=$ikey\">Delete Ad</a></p>";
					}else {unset($action);}
				}


echo "
<div id=\"classiwrapper\">";

if(!is_admin()){

echo"<ul id=\"postsearchads\">
$liplacead
$lieditad
<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
<li class=\"search\"><a href=\"".$quers."searchads\">Search Ads</a></li>
</ul>";}

		////////////////////////////////////////////////////////////////////////////////
		// If running in pay mode get and display the payment option settings
		////////////////////////////////////////////////////////////////////////////////

		if(get_awpcp_option(freepay) == '1'){

			$paymethod='';

			if($action == 'editad'){
				$paymethod='';
			}

			else {

				if(adtermsset()){

				//configure the pay methods

				if($adpaymethod == 'paypal'){ $ischeckedP="checked"; } else { $ischeckedP=''; }
				if($adpaymethod == '2checkout'){ $ischecked2co="checked"; }else { $ischecked2co=''; }


					$paymethod="<div class=\"headeritem\">Payment gateway</div><p>Choose your payment gateway</p>";
					if(get_awpcp_option(activatepaypal) == '1'){
					$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"paypal\" $ischeckedP>PayPal<br/>";}
					if(get_awpcp_option(activate2checkout) == '1'){
					$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"2checkout\"  $ischecked2co>2Checkout</br/>";
					$paymethod.="<div style=\"clear:both;\"></div>";}

				}


			}
		}

echo "<div style=\"clear:both\"></div>";


						/////////////////////////////////////////////////////////////////////
						// Retrieve the categories to populate the select list
						/////////////////////////////////////////////////////////////////////

						$allcategories=get_categorynameidall($adcategory);

				if((get_awpcp_option(displayphonefield) == '1')
				&&(get_awpcp_option(displayphonefieldreqop) == '1')){
				$phonecheck="if(the.adcontact_phone.value==\"\") {
							alert('You did not fill out a phone number for the ad contact person. The information is required.');
							the.adcontact_phone.focus();
							return false;
						}";}else {$phonecheck='';}

				if((get_awpcp_option(displaycityfield) == '1')
				&&(get_awpcp_option(displaycityfieldreqop) == '1')){
				$citycheck="if(the.adcontact_city.value==\"\") {
							alert('You did not fill out your city. The information is required.');
							the.adcontact_city.focus();
							return false;
						}";}else {$citycheck='';}

				if((get_awpcp_option(displaystatefield) == '1')
				&&(get_awpcp_option(displaystatefieldreqop) == '1')){
				$statecheck="if(the.adcontact_state.value==\"\") {
							alert('You did not fill out your state. The information is required.');
							the.adcontact_state.focus();
							return false;
						}";}else {$statecheck='';}

				if((get_awpcp_option(displaycountryfield) == '1')
				&&(get_awpcp_option(displaycountryfieldreqop) == '1')){
				$countrycheck="if(the.adcontact_country.value==\"\") {
							alert('You did not fill out your country. The information is required.');
							the.adcontact_country.focus();
							return false;
						}";}else {$countrycheck='';}

				if(get_awpcp_option(freepay) == '1') {
				$paymethodcheck="if(!checked(the.adpaymethod)) {
							alert('You did not select your payment method. The information is required.');
							the.adpaymethod.focus();
							return false;
						}";}else {$paymethodcheck='';}

				if(get_awpcp_option(freepay) == '1') {
				$adtermcheck="if(the.adterm_id.value==\"\") {
							alert('You did not select your ad term choice. The information is required.');
							the.adterm_id.focus();
							return false;
						}";}else {$adtermcheck='';}


				$checktheform="<script type=\"text/javascript\">
					function checkform() {
						var the=document.myform;
						if (the.adtitle.value==\"\") {
							alert('You did not fill out an ad title');
							the.adtitle.focus();
							return false;
						}
						if (the.adcategory.value==\"\") {
							alert('You did not select an ad category');
							the.adcategory.focus();
							return false;
						}
						if (the.adcontact_name.value==\"\") {
							alert('You did not fill in the name of the ad contact person');
							the.adcontact_name.focus();
							return false;
						}
						if ((the.adcontact_email.value==\"\") || (the.adcontact_email.value.indexOf('@')==-1) || (the.adcontact_email.value.indexOf('.',the.adcontact_email.value.indexOf('@')+2)==-1) || (the.adcontact_email.value.lastIndexOf('.')==the.adcontact_email.value.length-1)) {
							alert('Either you did not enter your email address or the email address you entered is not valid.');
							the.adcontact_email.focus();
							return false;
						}

						$phonecheck
						$citycheck
						$statecheck
						$countrycheck
						$paymethodcheck
						$adtermcheck

						if (the.addetails.value==\"\") {
							alert('You did not fill in any details for your ad.');
							the.addetails.focus();
							return false;
						}


						return true;
					}


							function textCounter(field, countfield, maxlimit) {
							if (field.value.length > maxlimit) // if too long...trim it!
							field.value = field.value.substring(0, maxlimit);
							// otherwise, update 'characters left' counter
							else
							countfield.value = maxlimit - field.value.length;
							}

				</script>";

				$addetailsmaxlength=get_awpcp_option('maxcharactersallowed');
				$theformbody='';
				$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "\n\n", $addetails);

				if(get_awpcp_option('allowhtmlinadtext') == 1){
					$htmlstatus="HTML is allowed";
				}
				else {
					$htmlstatus="No HTML allowed";
				}

				if(!is_admin()){
				$theformbody.="$displaydeleteadlink<p>Fill out the form below to post your classified ad.</p>";
				$faction="id=\"awpcpui_process\"";
				} else {$faction="action=\"?page=Manage1\" id=\"awpcp_launch\"";}
				$theformbody.="$checktheform $ermsg<form method=\"post\" name=\"myform\" $faction onsubmit=\"return(checkform())\">
				<input type=\"hidden\" name=\"adid\" value=\"$adid\">
				<input type=\"hidden\" name=\"adaction\" value=\"$action\">
				<input type=\"hidden\" name=\"a\" value=\"dopost1\">
				<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\">
				<input type=\"hidden\" name=\"adkey\" value=\"$ad_key\">
				<input type=\"hidden\" name=\"editemail\" value=\"$editemail\">
				<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">
				<input type=\"hidden\" name=\"results\" value=\"$results\">
				<input type=\"hidden\" name=\"offset\" value=\"$offset\">
				<br/>
				<div class=\"headeritem\">Ad Details and Contact Information </div>
				<p>Ad Title<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"adtitle\" value=\"$adtitle\"></p>
				<p>Ad Category<br/><select name=\"adcategory\"><option value=\"\">Select your ad category</option>$allcategories</a></select></p>
				<p>Name of person to contact<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_name\" value=\"$adcontact_name\"></p>
				<p>Contact Person's Email (Please enter a valid email. The codes needed to edit your ad will be sent to your email address)<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_email\" value=\"$adcontact_email\"></p>";
				if(get_awpcp_option(displayphonefield) == '1'){
				$theformbody.="<p>Contact Person's Phone Number<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_phone\" value=\"$adcontact_phone\"></p>";}
				if(get_awpcp_option(displaycityfield) == '1'){
				$theformbody.="<p>City<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_city\" value=\"$adcontact_city\"></p>";}
				if(get_awpcp_option(displaystatefield) == '1'){
				$theformbody.="<p>State<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_state\" value=\"$adcontact_state\"></p>";}
				if(get_awpcp_option(displaycountryfield) == '1'){
				$theformbody.="<p>Country<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_country\" value=\"$adcontact_country\"></p>";}
				$theformbody.="<p>Ad Details<br/><input readonly type=\"text\" name=\"remLen\" size=\"4\" maxlength=\"5\" value=\"$addetailsmaxlength\"> characters left<br/><br/>$htmlstatus<br/><textarea name=\"addetails\" rows=\"10\" cols=\"50\" onKeyDown=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\" onKeyUp=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\">$addetails</textarea></p>";


	if(get_awpcp_option(freepay) == '0'){
	echo "$theformbody";}

	else {

		echo "$theformbody";

		if($action == 'editad'){
			$adtermscode='';
		}

		else {


			if(!isset($adterm_id) || empty($adterm_id)){

				if(adtermsset()){

					$adtermscode="<div class=\"headeritem\">Select Ad Term</div>";

						//////////////////////////////////////////////////
						// Get and configure pay options
						/////////////////////////////////////////////////


						$paytermslistitems=array();

						$query="SELECT * FROM  ".$table_name2."";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							if (mysql_num_rows($res)) {

							while ($rsrow=mysql_fetch_row($res)) {
							list($savedadtermid,$adterm_name,$amount,$recurring,$rec_period,$rec_increment)=$rsrow;
								 if($rec_increment == "M"){$termname="Month";}
								 if($rec_increment == "D"){$termname="Day";}
								 if($rec_increment == "W"){$termname="Week";}
								 if($rec_increment == "Y"){$termname="Year";}

								$termname=$termname;

								if($adtermid == $savedadtermid){
								$ischecked="checked"; }
								else { $ischecked=''; }

								$adtermscode.="<input type=\"radio\" name=\"adterm_id\" value=\"$savedadtermid\" $ischecked>$adterm_name ($amount for a $rec_period $termname listing)<br/>";
							}

						}

				}

					echo "$adtermscode<p>$paymethod</p>";

				}//end if adtermsset


		}
}
				echo "<input type=\"submit\" class=\"scbutton\" value=\"Continue\"></form>";
				echo "</div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display a form to the user when edit existing ad is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_edit_form($action,$awpcppagename,$editemail='',$adaccesskey='',$message=''){

$quers='';
global $siteurl;
$permastruc=get_option(permalink_structure);
if(!isset($permastruc) || empty($permastruc)){
$pageid=get_page_id($awpcppagename);
$quers="?page_id=$pageid&a=";}
elseif(get_awpcp_option('seofriendlyurls') == '1'){
$quers="$siteurl/$awpcppagename/";}
else {$quers="?a=";}

if($action == 'placead'){
$liplacead="<li class=\"postad\"><b>Placing Ad</b></li>";}
else {$liplacead="<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>";}
if($action== 'editad'){
$lieditad="<li class=\"edit\"><b>Editing Ad: Step 1</b></li>";}
else {$lieditad="<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>";}

$checktheform="<script type=\"text/javascript\">
	function checkform() {
		var the=document.myform;

		if ((the.editemail.value==\"\") || (the.editemail.value.indexOf('@')==-1) || (the.editemail.value.indexOf('.',the.editemail.value.indexOf('@')+2)==-1) || (the.editemail.value.lastIndexOf('.')==the.editemail.value.length-1)) {
			alert('Either you did not enter your email address or the email address you entered is not valid.');
			the.editemail.focus();
			return false;
		}

		if (the.adaccesskey.value==\"\") {
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
$liplacead
$lieditad
<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
<li class=\"search\"><a href=\"".$quers."searchads\">Search Ads</a></li>
</ul><div style=\"clear:both\"></div>";

echo "$message
$checktheform<form method=\"post\" name=\"myform\" action=\"\" onsubmit=\"return(checkform())\">
<input type=\"hidden\" name=\"awpcpagename\" value=\"$awpcpagename\">
<input type=\"hidden\" name=\"a\" value=\"doadedit1\">
<p>Enter your Email address<br/>
<input type=\"text\" name=\"editemail\" value=\"$editemail\" class=\"inputbox\"></p>
<p>Enter your ad access key<br/>
<input type=\"text\" name=\"adaccesskey\" value=\"$adaccesskey\" class=\"inputbox\"></p>
<input type=\"submit\" class=\"scbutton\" value=\"Continue\">
<br/>
</form>

</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Display a form to be filled out in order to contact the ad poster
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_contact_form($adid){

$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');


$quers='';
global $siteurl;
$permastruc=get_option(permalink_structure);
if(!isset($permastruc) || empty($permastruc)){
$pageid=get_page_id($awpcppagename);
$quers="?page_id=$pageid&a=";}
elseif(get_awpcp_option('seofriendlyurls') == '1'){
$quers="$siteurl/$awpcppagename/";}
else {$quers="?a=";}

$numval1=rand(1,100);
$numval2=rand(1,100);
$thesum=($numval1 + $numval2);

if(get_awpcp_option('contactformcheckhuman') == 1){

	$conditionscheckhuman="

			if (the.checkhuman.value==\"\") {
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

		if (the.sendersname.value==\"\") {
			alert('You did not enter your name. Please enter your name.');
			the.sendersname.focus();
			return false;
		}
		if ((the.sendersemail.value==\"\") || (the.sendersemail.value.indexOf('@')==-1) || (the.sendersemail.value.indexOf('.',the.sendersemail.value.indexOf('@')+2)==-1) || (the.sendersemail.value.lastIndexOf('.')==the.sendersemail.value.length-1)) {
			alert('Either you did not enter your email address or the email address you entered is not valid.');
			the.sendersemail.focus();
			return false;
		}
		if (the.contactmessage.value==\"\") {
			alert('You did not enter any message. Please enter a message');
			the.contactmessage.focus();
			return false;
		}

		$conditionscheckhuman

		return true;
	}

</script>";

if(!isset($message) || empty($message)){
$message="<p></p>";
}

echo "
<div id=\"classiwrapper\">
<ul id=\"postsearchads\">
<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
<li class=\"search\"><a href=\"".$quers."searchads\">Search Ads</a></li>
</ul><div style=\"clear:both\"></div>";
$theadtitle=get_adtitle($adid);
$modtitle=cleanstring(theadtitle);
$modtitle=add_dashes($modtitle);
if(get_awpcp_option('seofriendlyurls') == 1 && isset($permastruc)){
$thead="<a href=\"".$quers."showad/$adid/$modtitle\">$theadtitle</a>";}
else {
$thead="<a href=\"".$quers."showad&id=$adid\">$theadtitle</a>";}


echo "<p>You are responding to $thead.</p>$message
$checktheform<form method=\"post\" name=\"myform\" action=\"\" onsubmit=\"return(checkform())\">
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
		$subject="Re: $theadtitle";
		$body="This is a message in response to your ad posted at $nameofsite at $siteurl<br/><br/>";
		$body.="$contactmessage";

		if(send_email($sendersemail,$sendtoemail,$subject,$body,true)){
			echo "<div id=\"classiwrapper\">Your message has been sent. Thank you for patronizing $nameofsite</div>";
		}
		else {
			echo "There was a problem encountered during the attempt to transmit your message. We apologize. Please try again and if the problem persists, please contact the system administrator.";
		}
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display the ad search form
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_search_form($keywordphrase,$searchname,$searchcity,$searchstate,$searchcountry,$searchcategory,$message){

$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');

$quers='';
global $siteurl;
$permastruc=get_option(permalink_structure);
if(!isset($permastruc) || empty($permastruc)){
$pageid=get_page_id($awpcppagename);
$quers="?page_id=$pageid&a=";}
elseif(get_awpcp_option('seofriendlyurls') == '1'){
$quers="$siteurl/$awpcppagename/";}
else {$quers="?a=";}


$checktheform="<script type=\"text/javascript\">
	function checkform() {
		var the=document.myform;
		if (the.keywordphrase.value==\"\") {
			if( (the.searchname.value==\"\") && (the.searchcity.value==\"\") && (the.searchstate.value==\"\") && (the.searchcountry.value==\"\") && (the.searchcategory.value==\"\")){
				alert('You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for.');
				the.keywordphrase.focus();
				return false;
			}
		}

		return true;
	}

</script>";

if(!isset($message) || empty($message)){
$message="<p>Use the form below to conduct a broad or narrow search. For a broader search enter fewer parameters. For a narrower search enter as many parameters as needed to limit your search to a specific criteria</p>";
}

$allcategories=get_categorynameidall($searchcategory);

echo "
<div id=\"classiwrapper\">
<ul id=\"postsearchads\">
<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
<li class=\"search\"><b>Searching Ads</b></li>
</ul><div style=\"clear:both\"></div>";

echo "$message
$checktheform<form method=\"post\" name=\"myform\" action=\"\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">
<input type=\"hidden\" name=\"a\" value=\"dosearch\">
<p>Search for ads containing this word or phrase:<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"keywordphrase\" value=\"$keywordphrase\"></p>
<p>Limit search to Category<br/><select name=\"searchcategory\"><option value=\"\">Select your ad category</option>$allcategories</a></select></p>
<p>Search ads by persons named:<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"searchname\" value=\"$searchname\"></p>";

if(get_awpcp_option(displaycityfield) == '1'){
echo "<p>Search in these cities(separate cities by commas)<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchcity\"></p>";}

if(get_awpcp_option(displaystatefield) == '1'){
echo "<p>Search in these states (separate states by commas)<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\"></p>";}

if(get_awpcp_option(displaycountryfield) == '1'){
echo "<p>Search in these countries(separate countries by commas)<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchcountry\"></p>";}

	echo "<div align=\"right\"><input type=\"submit\" class=\"scbutton\" value=\"Continue\"></div></form>";
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

		$message='';

		$error=false;
		if(!isset($keywordphrase) && empty($keywordphrase) &&
			!isset($searchname) && empty($searchname) &&
				!isset($searchcity) && empty($searchcity) &&
				 !isset($searchstate) && empty($searchstate) &&
				  !isset($searchcountry) && empty($searchcountry) &&
				  	!isset($searchcategory) && empty ($searchcategory)) {
				  	$error=true;
					$message="You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for.";
			}


		if($error){
			load_ad_search_form($keywordphrase,$searchname,$searchcity,$searchstate,$searchcountry,$searchcategory,$message);
		}

		else {
		$where="disabled ='0'";
		if(isset($keywordphrase) && !empty($keywordphrase)){
			$where.=" AND ad_title LIKE '%$keywordphrase%' OR ad_details LIKE '%$keywordphrase%'";
		}

		if(isset($searchname) && !empty($searchname)){
			$where.=" AND ad_contact_name LIKE '%$searchname%'";
		}

		if(isset($searchcity) && !empty($searchcity)){

			$cities=explode(",",$searchcity);
			$city=array();

			for ($i=0;isset($cities[$i]);++$i) {
				$city[]=$cities[$i];
				$citieslist=join("','",$city);
			}
				$where.=" AND ad_city IN ('$citieslist')";

		}

		if(isset($searchstate) && !empty($searchstate)){

			$states=explode(",",$searchstate);
			$state=array();

			for ($i=0;isset($states[$i]);++$i) {
				$state[]=$states[$i];
				$stateslist=join("','",$state);
			}
				$where.=" AND ad_state IN ('$stateslist')";
		}

		if(isset($searchcountry) && !empty($searchcountry)){

			$countries=explode(",",$searchcountry);
			$country=array();

			for ($i=0;isset($countries[$i]);++$i) {
				$country[]=$countries[$i];
				$countrieslist=join("','",$country);
			}
				$where.=" AND ad_country IN ('$countrieslist')";
		}

		if(isset($searchcategory) && !empty($searchcategory)){
			$where.=" AND ad_category_id = '$searchcategory' OR ad_category_parent_id = '$searchcategory'";
		}

		display_ads($where);

		}


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: process first step of edit ad request
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function editadstep1($adaccesskey,$editemail,$awpcpagename){

		global $wpdb;
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_id,adterm_id FROM ".$table_name3." WHERE ad_key='$adaccesskey' AND ad_contact_email='$editemail'";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		 	while ($rsrow=mysql_fetch_row($res)) {
		 		list($adid,$adtermid)=$rsrow;
		}

		if(isset($adid) && !empty($adid)){
			load_ad_post_form($adid,$action='editad',$awpcpagename,$adtermid,$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='');
		}

		else {
		$message="<p class=\"messagealert\">The information you have entered does not match the information on file. Please make sure you are using the same email address you used to post your ad and the exact access key that was emailed to you when you posted your ad.</p>";
			load_ad_edit_form($action='editad',$awpcpagename,$editemail,$adaccesskey,$message);
		}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Process ad submission
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg) {

		global $wpdb;
		$table_name2 = $wpdb->prefix . "awpcp_adfees";
		$table_name3 = $wpdb->prefix . "awpcp_ads";
		$table_name5 = $wpdb->prefix . "awpcp_adphotos";

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
		if((get_awpcp_option(displayphonefield) == '1')
		&&(get_awpcp_option(displayphonefieldreqop) == '1')){
			if(!isset($adcontact_phone) || empty($adcontact_phone)) {
				$error=true;
				$adcphonemsg="<li>You did not enter your phone number. Your phone number is required.</li>";
			}
		}

		// If city field is checked and required make sure city value was entered
		if((get_awpcp_option(displaycityfield) == '1')
		&&(get_awpcp_option(displaycityfieldreqop) == '1')){
			if(!isset($adcontact_city) || empty($adcontact_city)){
				$error=true;
				$adcitymsg="<li>You did not enter your city. Your city is required.</li>";
			}
		}

		// If state field is checked and required make sure state value was entered
		if((get_awpcp_option(displaystatefield) == '1')
		&&(get_awpcp_option(displaystatefieldreqop) == '1')){
			if(!isset($adcontact_state) || empty($adcontact_state)){
				$error=true;
				$adstatemsg="<li>You did not enter your state. Your state is required.</li>";
			}
		}

		// If country field is checked and required make sure country value was entered
		if((get_awpcp_option(displaycountryfield) == '1')
		&&(get_awpcp_option(displaycountryfieldreqop) == '1')){
			if(!isset($adcontact_country) || empty($adcontact_country)){
				$error=true;
				$adcountrymsg="<li>You did not enter your country. Your country is required.</li>";
			}
		}

		// If running in pay mode make sure a payment method has been checked
		if(get_awpcp_option(freepay) == '1') {
			if(!isset($adpaymethod) || empty($adpaymethod)){
				$error=true;
				$adpaymethodmsg="<li>You did not select your payment method. The information is required.</li>";
			}
		}

		// If running in pay mode make sure an ad term has been selected
		if(get_awpcp_option(freepay) == '1') {
			if(!($adaction == 'delete') || ($adaction == 'editad')) {
				if(!isset($adterm_id) || empty ($adterm_id)) {
					$error=true;
					$adtermidmsg="<li>You did not select an ad term. The information is required.</li>";
				}
			}
		}


		if($error){
			$ermsg="<p>There has been an error found. Your message has not been sent. Please review the list of problems, correct them then try to send your message again.</p>";
			$ermsg.="<b>The errors:</b><br/>";
			$ermsg.="<ul>$adtitlemsg $adcategorymsg $adcnamemsg $adcemailmsg1 $adcemailmsg2 $adcphonemsg $adcitymsg $adstatemsg $adcountrymsg $addetailsmsg $adpaymethodmsg $adtermidmsg</ul>";

			load_ad_post_form($adid,$action,$awpcppagename,$adterm_id,$editemail,$adkey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$addetails,$adpaymethod,$offset,$results,$ermsg);
		}

		else {


		// Process ad delete request

		if($adaction == 'delete'){
			deletead($adid,$adkey,$editemail);
		}

		// Process ad edit request

		else {

			if($adaction == 'editad'){

				$isadmin=0;
				global $user_level;
				get_currentuserinfo();

				if($user_level == '10'){
					$isadmin=1;
				}

				$qdisabled='';
				if(!(is_admin())){
					if(get_awpcp_option('adapprove') == '1'){
					$disabled='1';}else {$disabled='0';}
					$qdisabled="disabled='$disabled',";
				}

				$adcategory_parent_id=get_cat_parent_ID($adcategory);

				$query="UPDATE ".$table_name3." SET ad_category_id='$adcategory',ad_category_parent_id='$adcategory_parent_id',ad_title='$adtitle',
				ad_details='$addetails',ad_contact_phone='$adcontact_phone',ad_contact_name='$adcontact_name',ad_contact_email='$adcontact_email',ad_city='$adcontact_city',ad_state='$adcontact_state',ad_country='$adcontact_country',
				$qdisabled ad_last_updated=now() WHERE ad_id='$adid' AND ad_key='$adkey'";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


				if($isadmin == 1 && is_admin()){
				$message="The ad has been edited successfully. <a href=\"?page=Manage1&offset=$offset&results=$results\">Back to view listings</a>";
				printmessage($message);
				}

				else {

					editimages($adterm_id,$adid,$adkey,$editemail);
				}

		}

		// Process new ad

		else {

			$key=time();

			if(get_awpcp_option('adapprove') == '1'){
			$disabled='1';}else {$disabled='0';}

			if($disabled == 0){
				if(get_awpcp_option('freepay') == '1'){
				$disabled='1';}
			}


				$adexpireafter='';
				$adstartdate=mktime();
				$adexpireafter=get_awpcp_option('addurationfreemode');

					if($adexpireafter == 0){
						$adexpireafter=9125;
					}else {$adexpireafter=$adexpireafter;}



			$adcategory_parent_id=get_cat_parent_ID($adcategory);



				$query="INSERT INTO ".$table_name3." SET ad_category_id='$adcategory',ad_category_parent_id='$adcategory_parent_id',ad_title='$adtitle',
				ad_details='$addetails',ad_contact_phone='$adcontact_phone',ad_contact_name='$adcontact_name',ad_contact_email='$adcontact_email',ad_city='$adcontact_city',ad_state='$adcontact_state',ad_country='$adcontact_country',
				ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $adexpireafter DAY,disabled='$disabled',ad_key='$key',ad_transaction_id='',ad_postdate=now()";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
				$ad_id=mysql_insert_id();


				////////////////////////////////////////////////////////////////////////////
				// Continue after inserting new ad into database
				////////////////////////////////////////////////////////////////////////////



				if(get_awpcp_option('freepay') == '1'){

				if(get_awpcp_option('imagesallowdisallow') == '1'){
				$txtuploadimages1="| Upload Images";}
				else {$txtuploadimages1="";}

					$uploadandpay="<div class=\"headeritem\">Step 2: Review Ad $txtuploadimages1 | Pay Fee</div>";

					///////////////////////////////////////////////////////////////////////////////////////////////////
					// Step:1 Find out how many images are allowed for the selected ad term if allow images is on
					///////////////////////////////////////////////////////////////////////////////////////////////////

					if(get_awpcp_option('imagesallowdisallow') == '1'){

						if(get_awpcp_option('freepay') == '1'){
						$numimgsallowed=get_numimgsallowed($adterm_id);}
						else {$numimgsallowed=get_awpcp_option('imagesallowedfree');}

					}

					////////////////////////////////////////////////////////////////////////////////////
					// Step:2 Show a sample of how the ad is going to look
					////////////////////////////////////////////////////////////////////////////////////

					if(!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="Not Supplied";}
					if(!isset($adcontact_phone) || empty($adcontact_phone)){$adcontact_phone="Not Supplied";}

					if(empty($ad_contact_city) && empty($adcontact_state) && empty($adcontact_country)){
						$location="";
					}

					else {
						$location="Location:";

						if(isset($adcontact_city)){
							$location.="$adcontact_city";
						}
						if(isset($adcontact_state)){
							$location.=" $adcontact_state";
						}
						if(isset($adcontact_country)){
							$location.=" $adcontact_country";
						}
					}


					$addetails="<div id=\"showad\"><div class=\"adtitle\">$adtitle</div><div class=\"adbyline\">Contact Person: $adcontact_name Phone: $adcontact_phone $location</div>
					<p class=\"addetails\">$addetails</p></div>";


					$uploadandpay.="$addetails";

					////////////////////////////////////////////////////////////////////////////////////
					// Step:3 Configure the upload form if images are allowed
					////////////////////////////////////////////////////////////////////////////////////

					if(get_awpcp_option('imagesallowdisallow') == '1'){

					$totalimagesuploaded=get_total_imagesuploaded($ad_id);

					if($totalimagesuploaded < $numimgsallowed){

					$max_image_size=get_awpcp_option('maximagesize');

					$showimageuploadform="<p>You can display [<b>$numimgsallowed</b>] images with your ad if desired.</p>";

						if(get_awpcp_option('imagesapprove') == 1){
							$showimageuploadform.="<p>Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it.</p>";
						}
					$showimageuploadform.="
					<div class=\"headeritem\">Image Upload</div>
					<p id=\"ustatmsg\">

					<form id=\"Form1\" name=\"Form1\" method=\"post\" ENCTYPE=\"Multipart/form-data\" action=\"\">
						<p id=\"showhideuploadform\">
					    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_image_size\" />
					    <input type=\"hidden\" name=\"ADID\" value=\"$ad_id\" />
					    <input type=\"hidden\" name=\"ADTERMID\" value=\"$adterm_id\" />
					    If adding images to your ad, select an image from your hard disk:<br/><br/>


					        <input type=\"file\" name=\"fileToUpload\" id=\"fileToUpload\" size=\"18\" />
					        <input type=\"Submit\" value=\"Submit\" id=\"buttonForm\" />

					    </p>
					</form>
					<img id=\"loading\" src=\"".AWPCPURL."images/loading.gif\" style=\"display:none;\" />

					<p id=\"message\">

					<p id=\"result\"><div style=\"clear:both\"></div>
					";} else { $showimageuploadform='';
					include(dirname(__FILE__).'/upload4jquery.php');
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

					$showpaybutton="<div class=\"headeritem\">Payment</div><p>Please click the  button below to submit payment for your ad listing.</p>";

						////////////////////////////////////////////////////////////////////////////
						// Print the paypal button option if paypal is activated
						////////////////////////////////////////////////////////////////////////////

						if($adpaymethod == 'paypal'){
						$base=get_option('siteurl');
						$custom="$ad_id";
						$custom.="_";
						$custom.="$key";

						$quers='';
						$permastruc=get_option('permalink_structure');
						if(!isset($permastruc) || empty($permastruc)){
							$pageid=get_page_id($awpcppagename);
							$quers="$base/?page_id=$pageid&a=";
						}
						elseif(get_awpcp_option('seofriendlyurls') == '1'){
							$quers="$base/$awpcppagename/";
						}
						else {
							$quers="$base/$awpcppagename/?a=";
						}

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

									if(get_awpcp_option('seofriendlyurls') == '1' && isset($permastruc)){
										$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou/$custom\" />";
									}else {
										$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou&i=$custom\" />";
									}
									$showpaybutton.="$codepaypalthank";
									if(get_awpcp_option('seofriendlyurls') == '1' && isset($permastruc)){
									$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal/$custom\" />";
									}else {
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
									<input type=\"hidden\" name=\"custom\" value=\"$custom\" />
									<input type=\"hidden\" name=\"src\" value=\"1\" />
									<input type=\"hidden\" name=\"sra\" value=\"1\" />
									<input class=\"button\" type=\"submit\" value=\"Pay With PayPal\">
									</form>";
						}

						/////////////////////////////////////////////////////////////////////////////
						// Print the  2Checkout button option if 2Checkout is activated
						/////////////////////////////////////////////////////////////////////////////

						elseif($adpaymethod == '2checkout'){

								$base=get_option('siteurl');
								$custom="$ad_id";
								$custom.="_";
								$custom.="$key";

						$quers='';
						$permastruc=get_option('permalink_structure');
						if(!isset($permastruc) || empty($permastruc)){
							$pageid=get_page_id($awpcppagename);
							$quers="$base/?page_id=$pageid&a=";
						}
						elseif(get_awpcp_option('seofriendlyurls') == '1'){
							$quers="$base/$awpcppagename/";
						}
						else {
							$quers="$base/$awpcppagename/?a=";
						}

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
									if(get_awpcp_option('paylivetestmode') == 0){
										$showpaybutton.="\n<input type=\"hidden\" name=\"demo\" value=\"Y\" />\n";
									}
									$showpaybutton.="<input name=\"submit\" class=\"button\" type=\"submit\" value=\"Pay With 2Checkout\" /></form>";

						}


				$uploadandpay.="$showpaybutton";

				////////////////////////////////////////////////////////////////////////////
				// Display the content
				////////////////////////////////////////////////////////////////////////////

				$classicontent=$uploadandpay;
				echo "$classicontent";


				}

				////////////////////////////////////////////////////////////////////////////
				// Configure the content in the event of the site running in free mode
				////////////////////////////////////////////////////////////////////////////

				elseif((get_awpcp_option('freepay') == '0') && (get_awpcp_option('imagesallowdisallow') == '1')){

					$imagesforfree=get_awpcp_option('imagesallowedfree');

					if($totalimagesuploaded < $imagesforfree){

					$max_image_size=get_awpcp_option('maximagesize');

										$showimageuploadform="<p>You can display [<b>$imagesforfree</b>] images with your ad if desired.</p>";
										if(get_awpcp_option('imagesapprove') == 1){
											$showimageuploadform.="<p>Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it.</p>";
										}

										$showimageuploadform.="
										<div class=\"headeritem\">Image Upload</div>
										<p id=\"ustatmsg\">

										<form id=\"Form1\" name=\"Form1\" method=\"post\" ENCTYPE=\"Multipart/form-data\" action=\"\">
											<p id=\"showhideuploadform\">
										    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_image_size\" />
										    <input type=\"hidden\" name=\"ADID\" value=\"$ad_id\" />
										    <input type=\"hidden\" name=\"ADTERMID\" value=\"$adterm_id\" />
										    If adding images to your ad, select an image from your hard disk:<br/><br/>


										        <input type=\"file\" name=\"fileToUpload\" id=\"fileToUpload\" size=\"18\" />
										        <input type=\"Submit\" value=\"Submit\" id=\"buttonForm\" />

										    </p>
										</form>
										<img id=\"loading\" src=\"".AWPCPURL."images/loading.gif\" style=\"display:none;\" />

										<p id=\"message\">

										<p id=\"result\"><div style=\"clear:both\"></div>
										";} else { $showimageuploadform='';
										include(dirname(__FILE__).'/upload4jquery.php');
										}
					$finishbutton="<p>Please click the finish button to complete the process of submitting your listing</p>
					<form method=\"post\" action=\"\">
					<input type=\"hidden\" name=\"a\" value=\"adpostfinish\">
					<input type=\"hidden\" name=\"ad_id\" value=\"$ad_id\" />
					<input type=\"hidden\" name=\"adkey\" value=\"$key\" />
					<input type=\"Submit\" value=\"Finish\"/>
										</form>";

					$showimageuploadform.="$finishbutton";

					$classicontent="$showimageuploadform";
					echo "$classicontent";



					}
					else {$classicontent="Your ad has been submitted. A key has been emailed to the email address provided. You may use that key to make changes to your ad.";
					echo "$classicontent";}
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

function editimages($adtermid,$adid,$adkey,$editemail) {

$savedemail=get_adposteremail($adid);

			global $wpdb;
			$table_name5 = $wpdb->prefix . "awpcp_adphotos";

			$finishbutton="<p>Please click the finish button to complete the process of editing listing</p>
							<form method=\"post\" action=\"\">
							<input type=\"hidden\" name=\"a\" value=\"adpostfinish\" />
							<input type=\"hidden\" name=\"ad_id\" value=\"$adid\" />
							<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />
							<input type=\"hidden\" name=\"adaction\" value=\"editad\" />
							<input type=\"Submit\" value=\"Finish\"/>
							</form>";

if(strcasecmp($editemail, $savedemail) == 0) {



		$imagecode="<div class=\"headeritem\">Manage your ad images</div>";
		if(!isset($adid) || empty($adid)){
			$imagecode.="There has been a problem encountered. The system is unable to continue processing the task in progress. Please start over and if you encounter the problem again, please contact a system administrator.";
		}

		else {

			if(get_awpcp_option('imagesallowdisallow') == '1'){

			if((get_awpcp_option('freepay') == '1') && isset($adtermid) && $adtermid != '0'){
			$numimgsallowed=get_numimgsallowed($adtermid);}
			else {$numimgsallowed=get_awpcp_option('imagesallowedfree');}

			$totalimagesuploaded=get_total_imagesuploaded($adid);

			if($totalimagesuploaded >= 1){

				$imagecode.="<p>Your images are displayed below. The total number of images you are allowed is: $numimgsallowed</p>";
					if(($numimgsallowed - $totalimagesuploaded) == '0'){
						$imagecode.="<p>If you want to change your images you will first need to delete the current images.</p>";
					}
					if(get_awpcp_option('imagesapprove') == 1){
						$imagecode.="<p>Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it.</p>";
					}
				$imagecode.="<div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";
				$theimage='';


				$query="SELECT key_id,image_name,disabled FROM ".$table_name5." WHERE ad_id='$adid' ORDER BY image_name ASC";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					while ($rsrow=mysql_fetch_row($res)) {
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

						if($disabled == 1){
							$transval="class=\"imgtransparency\"";
							$imgstat="<font style=\"font-size:smaller;\">Disabled</font>";
						}

						$dellink="<a href=\"?a=dp&k=$ikey\">Delete</a>";
						$theimage.="<li><a href=\"".AWPCPUPLOADURL."/$image_name\"><img $transval src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a><br/>$dellink $imgstat</li>";
					}

					$imagecode.=$theimage;
					$imagecode.="</ul></div></div>";
					$imagecode.="<div style=\"clear:both;\"></div>";
			}
			elseif($totalimagesuploaded < 1){
				$imagecode="<div class=\"headeritem\">Manage your ad images</div><p>You do not currently have any images uploaded. Use the upload form below to upload your images. If you do not wish to upload any images simply click the finish button. If uploading images, be careful not to click the finish button until after you've uploaded all your images. </p>";
			}

			if($totalimagesuploaded < $numimgsallowed){
			$max_image_size=get_awpcp_option('maximagesize');


			$showimageuploadform="<p>You can display [<b>$numimgsallowed</b>] images with your ad if desired.</p>";

			$showimageuploadform.="
			<div class=\"headeritem\">Image Upload</div>
			<p id=\"ustatmsg\">

			<form id=\"Form1\" name=\"Form1\" method=\"post\" ENCTYPE=\"Multipart/form-data\" action=\"\">
				<p id=\"showhideuploadform\">
				<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$max_image_size\" />
				<input type=\"hidden\" name=\"ADID\" value=\"$adid\" />
				<input type=\"hidden\" name=\"ADTERMID\" value=\"$adtermid\" />
				If adding images to your ad, select an image from your hard disk:<br/><br/>


				<input type=\"file\" name=\"fileToUpload\" id=\"fileToUpload\" size=\"18\" />
				<input type=\"Submit\" value=\"Submit\" id=\"buttonForm\" />

				</p>
			</form>
			<img id=\"loading\" src=\"".AWPCPURL."images/loading.gif\" style=\"display:none;\" />

			<p id=\"message\">

			<p id=\"result\"><div style=\"clear:both;\"></div>";} else { $showimageuploadform='';}
			include(dirname(__FILE__).'/upload4jquery.php');
			}
			$imagecode.=$showimageuploadform;



			$imagecode.="$finishbutton<div style=\"clear:both;\"></div>";


			//$classicontent=$imagecode;
			//global $classicontent;

			echo "<div id=\"classiwrapper\">$imagecode</div>";

		}

	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function deletepic($picid,$adid,$adtermid,$adkey,$editemail){
$isadmin=0;
$savedemail=get_adposteremail($adid);
global $user_level;
get_currentuserinfo();

	if($user_level == '10'){
	$isadmin=1;
	}

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
	$isadmin=0;
	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";
	$savedemail=get_adposteremail($adid);
	global $user_level;
	get_currentuserinfo();

	if($user_level == '10'){
	$isadmin=1;
	}

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

				if($isadmin == 1){
					$message="The ad has been deleted";
					return $message;
				}

				else {
					echo "<div id=\"classiwrapper\"> Your ad details and any photos you have uploaded have been deleted from the system. </div>";
				}
	}
	else {
				echo "Problem encountered. Cannot complete  request.";
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Process PayPal Payment
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function do_paypal() {


// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes_mq($value));
	$req .= "&$key=$value";
}

if(get_awpcp_option('paylivetestmode') == 0){
	$paypallink="www.sandbox.paypal.com";
}
else{
	$paypallink="www.paypal.com";
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Host: $paypallink\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n";
$header.="Connection: close\r\n\r\n";
$fp = fsockopen('$paypallink', 80, $errno, $errstr, 30);


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
if ($fp) {
	fputs ($fp, $header . $req."\r\n\r\n");
	$reply='';
	$headerdone=false;
	while(!feof($fp)) {
		$line=fgets($fp);
		if (strcmp($line,"\r\n")==0) {
			// read the header
			$headerdone=true;
		} elseif ($headerdone) {
			// header has been read. now read the contents
			$reply.=$line;
		}
	}
	fclose($fp);
	$reply=trim($reply);
	if (strcasecmp($reply,'VERIFIED')==0) {
		$payment_verified = true;
	} elseif (strcasecmp($reply,'INVALID')==0) {
		$payment_verified = false;
	}
} else {
	// HTTP ERROR
}


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
			$gateway="Paypal";

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


		if(!(in_array(number_format($mcgross,2),$myamounts) || in_array(number_format($payment_gross,2),$myamounts))) {
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

			if (!(strcasecmp($receiver_email, $pbizid) == 0)) {
				$message="There was an error process your transaction. If funds have been deducted from your account they have not been processed to our account. You will need to contact PayPal about the matter.";
				abort_payment($message,$ad_id,$txn_id,$gateway);
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

				if (isdupetransid($txn_id)) {
					$message="It appears this transaction has already been processed. If you do not see your ad in the system please contact the site adminstrator for assistance.";
					abort_payment($message,$ad_id,$txn_id,$gateway);
				}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// If the transaction ID is not a duplicate proceed with processing the transaction
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Begin updating based on payment status
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				if (strcasecmp($payment_status, "Completed") == 0) {

				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

					if(get_awpcp_option('adapprove') == '1'){
					$disabled='1';}else {$disabled='0';}

					$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled' WHERE ad_id='$ad_id' AND ad_key='$key'";
					if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					if (isset($item_number) && !empty($item_number)) {

						$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
					}


					$message="Payment Status: Completed";
					ad_success_email($ad_id,$txn_id,$key,$message,$gateway);


				} else if (strcasecmp($payment_status, "Refunded") == 0 || strcasecmp($payment_status, "Reversed") == 0 || strcasecmp ($payment_status, "Partially-Refunded") == 0) {

					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					// Disable the ad since the payment has been refunded
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


						if(get_awpcp_option(freepay) == '1'){

							$query="UPDATE  ".$table_name3." SET disabled='1',payment_status='$payment_status', WHERE ad_id='$ad_id' AND ad_key='$key'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							if (isset($item_number) && !empty($item_number)) {

								$query="UPDATE ".$table_name2." SET buys=buys-1 WHERE adterm_id='".addslashes_mq($item_number)."'";
								if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
							}
						}

				} else if (strcasecmp ($payment_status, "Pending") == 0 ) {

					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

						if(get_awpcp_option('disablependingads') == '1'){
						$disabled='1';}else {$disabled='0';}

						$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled' WHERE ad_id='$ad_id' AND ad_key='$key'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						if (isset($item_number) && !empty($item_number)) {

							$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
						}


						$message="Payment status is: Pending";
						ad_success_email($ad_id,$txn_id,$key,$message,$gateway);

				} else {
					$message="There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.";
					abort_payment($message,$ad_id,$txn_id,$gateway);
				}


	} //Close if payment verified

	else if (!$payment_verified) {
		$message="There has been a problem encountered. The payment was made to PayPal but the system has failed to complete the post processing of your transaction. Please contact the site administrator with this message.";
		abort_payment($message,$ad_id,$txn_id,$gateway);
	}

	if(!isset($message) || empty($message)){
		$message="There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.";
	}

echo "<div id=\"classiwrapper\">$message</div>";

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

if($x_response_code == 1){
$payment_verified=true;}
else {$payment_verified=false;}


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

					if(get_awpcp_option('adapprove') == '1'){
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

	else if (!$payment_verified) {
		$message="There has been a problem encountered. The payment was made to PayPal but the system has failed to complete the post processing of your transaction. Please contact the site administrator with this message.";
		abort_payment($message,$ad_id,$x_trans_id,$gateway);
	}

	if(!isset($message) || empty($message)){
		$message="There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.";
	}

echo "<div id=\"classiwrapper\">$message</div>";

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
$mailbodyadmin="Dear $nameofsite Administrator\n\n";
$mailbodyadmin.="There was a problem encountered during an attempt to submit payment for the listing titled \"$listingtitle\" on $nameofsite at $siteurl. The transaction was aborted due to:<br><br>";
$mailbodyadmin.="$message\n\n";
$mailbodyadmin.="For your reference the transaction id is: $transactionid and the ad ID is: $ad_id\n\n";
$mailbodyadmin.="$siteurl";

//email the buyer
send_email($thisadminemail,$adposteremail,$subjectuser,$mailbodyuser,true);

//email the administrator
send_email($thisadminemail,$thisadminemail,$subjectadmin,$mailbodyadmin,true);
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
	$mailbodyuser.="When contacting in reference to this transaction please provide the following transaction ID: $txn_id<br/><br/>";
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
$mailbodyadmin.="The ID associated with this ad is: $ad_id<br><br>";
$mailbodyadmin.="$siteurl<br><br>";

//email the buyer
send_email($thisadminemail,$adposteremail,$subjectuser,$mailbodyuser,true);

//email the administrator if the admin has this option set
	if(get_awpcp_option('notifyofadposted')){
	$sentok2=send_email($thisadminemail,$thisadminemail,$subjectadmin,$mailbodyadmin,true);
	}

$messagetouser="Your ad has been submitted and an email has been dispatched to your email account on file. This email contains important information you will need to manage your listing.";
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

$quers='';
						$permastruc=get_option('permalink_structure');
						if(!isset($permastruc) || empty($permastruc)){
						$pageid=get_page_id($awpcppagename);
						$quers="$base/?page_id=$pageid&a=";}
						elseif(get_awpcp_option('seofriendlyurls') == '1'){
						$quers="$base/$awpcppagename/";}
						else {$quers="$base/$awpcppagename/?a=";}

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
									if(get_awpcp_option('seofriendlyurls') == 1 && isset($permastruc)){
										$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou/$custom\" />";
									}else {
										$codepaypalthank="<input type=\"hidden\" name=\"return\" value=\"".$quers."paypalthankyou&i=$custom\" />";
									}
									$showpaybutton.="$codepaypalthank";
									if(get_awpcp_option('seofriendlyurls') == '1' && isset($permastruc)){
									$codepaypalcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"".$quers."cancelpaypal/$custom\" />";
									}else {
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

if(get_awpcp_option('adapprove') == '1'){
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



			 $query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details from ".$table_name3." WHERE ad_id='$ad_id'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($ad_title,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$addetails)=$rsrow;
				}

					////////////////////////////////////////////////////////////////////////////////////
					// Step:2 Show a sample of how the ad is going to look
					////////////////////////////////////////////////////////////////////////////////////

					if(!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="Not Supplied";}
					if(!isset($adcontact_phone) || empty($adcontact_phone)){$adcontact_phone="Not Supplied";}

					if(empty($ad_contact_city) && empty($adcontact_state) && empty($adcontact_country)){
						$location="";
					}

					else {

					$location="Location:";

						if(isset($adcontact_city)){
							$location.="$adcontact_city";
						}
						if(isset($adcontact_state)){
							$location.=" $adcontact_state";
						}
						if(isset($adcontact_country)){
							$location.=" $adcontact_country";
						}
					}

					echo "<div id=\"showad\"><div class=\"adtitle\">$ad_title</div><div class=\"adbyline\">Contact Person: $adcontact_name Phone: $adcontact_phone $location</div>
					<p class=\"addetails\">$addetails</p></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

					$theimage='';

					if(get_awpcp_option('imagesallowdisallow') == '1'){

					  $query="SELECT image_name FROM ".$table_name5." WHERE ad_id=$ad_id ORDER BY image_name ASC";
					  if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
  						while ($rsrow=mysql_fetch_row($res)) {
  						list($image_name)=$rsrow;

  						echo "<li><a href=\"".AWPCPUPLOADURL."/$image_name\"><img src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a></li>";

  						}

					}

					echo "</ul></div></div>";

echo "</div><div style=\"clear:both;\"></div>";

}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display listing of ad titles when browse ads is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function display_ads($where) {
$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');

						$quers='';
						global $siteurl;
						$permastruc=get_option('permalink_structure');
						if(!isset($permastruc) || empty($permastruc)){
						$pageid=get_page_id($awpcppagename);
						$quers="?page_id=$pageid&a=";}
						elseif(get_awpcp_option('seofriendlyurls') == '1'){
						$quers="$siteurl/$awpcppagename/";}
						else {$quers="?a=";}


echo "<div id=\"classiwrapper\">

<ul id=\"postsearchads\">
<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
<li class=\"search\"><a href=\"".$quers."searchads\">Search Ads</a></li>
</ul>


<div style=\"clear:both;\"></div>";

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$from="$table_name3";
if(!isset($where) || empty($where)){
	$where="disabled ='0'";
}

	if(get_awpcp_option('disablependingads') == 1) {
		if(get_awpcp_option('freepay') == 1){
			$where.=" AND payment_status != 'Pending'";
		}
	}

	if(!ads_exist()){
	 	$showcategories="<p style=\"padding:10px\">There are currently no ads in the system</p>";
 		$pager1='';
 		$pager2='';
	}


	else {

 			$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
			$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);



			$items=array();
			$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details,ad_postdate FROM $from WHERE $where ORDER BY ad_postdate DESC LIMIT $offset,$results";
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


					$pager1=create_pager($from,$where,$offset,$results,$awpcppagename);
					$pager2=create_pager($from,$where,$offset,$results,$awpcppagename);
					if(get_awpcp_option('seofriendlyurls') == '1'){
						if(isset($permastruc) && !empty($permastruc)){
						$ad_title="<a href=\"".$quers."showad/$ad_id/$modtitle\">".$rsrow[2]."</a>";
						$categorylink="<a href=\"".$quers."browsecat/$category_id/$modcatname\">$category_name</a>";
						}
						else {
						$ad_title="<a href=\"".$quers."showad&id=$ad_id\">".$rsrow[2]."</a>";
						$categorylink="<a href=\"".$quers."browsecat&category_id=$category_id\">$category_name</a>";
						}
					}
					else {
						$ad_title="<a href=\"".$quers."showad&id=$ad_id\">".$rsrow[2]."</a>";
						$categorylink="<a href=\"".$quers."browsecat&category_id=$category_id\">$category_name</a>";
					}



					$items[]="<tr><td class=\"displayadsicell\">$ad_title</td><td class=\"displayadsicell\">$categorylink</td></tr>";


					$opentable="<table class=\"displayads\"><tr><td style=\"width:60%;padding:5px;\">Headline</td><td style=\"width:40%;padding:5px;\">Category</td></tr>";
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


echo "
$pager1
 $showcategories
$pager2</div>";

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

						$quers='';
						global $siteurl;
						$permastruc=get_option(permalink_structure);
						if(!isset($permastruc) || empty($permastruc)){
						$pageid=get_page_id($awpcppagename);
						$quers="?page_id=$pageid&a=";}
						elseif(get_awpcp_option('seofriendlyurls') == '1'){
						$quers="$siteurl/$awpcppagename/";}
						else {$quers="?a=";}


if(isset($adid) && !empty($adid)){
echo "<div id=\"classiwrapper\">";
echo "<ul id=\"postsearchads\">
<li class=\"postad\"><a href=\"".$quers."placead\">Place An Ad</a></li>
<li class=\"edit\"><a href=\"".$quers."editad\">Edit Existing Ad</a></li>
<li class=\"browse\"><a href=\"".$quers."browseads\">Browse Ads</a></li>
<li class=\"search\"><a href=\"".$quers."searchads\">Search Ads</a></li>
</ul>


<div style=\"clear:both;\"></div>";

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
$table_name5 = $wpdb->prefix . "awpcp_adphotos";

if(get_awpcp_option('useadsense') == '1'){
$adsensecode=get_awpcp_option('adsense');
$showadsense="<p class=\"cl-adsense\">$adsensecode</p>";}
else {$showadsense='';}

			 $query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($ad_title,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$addetails)=$rsrow;
				}

					////////////////////////////////////////////////////////////////////////////////////
					// Step:2 Show a sample of how the ad is going to look
					////////////////////////////////////////////////////////////////////////////////////

					if(!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="Not Supplied";}
					if(!isset($adcontact_phone) || empty($adcontact_phone)){$adcontact_phone="Not Supplied";}


					if(empty($ad_contact_city) && empty($adcontact_state) && empty($adcontact_country)){
						$location="";
					}
					else {

						$location="Location:";
						if(isset($adcontact_city)){
							$location.="$adcontact_city";
						}
						if(isset($adcontact_state)){
							$location.=" $adcontact_state";
						}
						if(isset($adcontact_country)){
							$location.=" $adcontact_country";
						}
					}


					$modtitle=cleanstring($ad_title);
					$modtitle=add_dashes($modtitle);

					if(get_awpcp_option('seofriendlyurls') == 1 && isset($permastruc)){
						$codecontact="contact/$adid/$modtitle";}
					else {
						$codecontact="contact&i=$adid";
					}

					echo "<div id=\"showad\"><div class=\"adtitle\">$ad_title</div><div class=\"adbyline\"><a href=\"".$quers."$codecontact\">Contact $adcontact_name</a> Phone: $adcontact_phone $location</div>";
					if(get_awpcp_option('adsenseposition') == '1' ){
						echo "$showadsense";
					}

					$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "<br /><br />", $addetails);
					echo "<p class=\"addetails\">$addetails</p>";
					if(get_awpcp_option('adsenseposition') == '2'){
					echo "$showadsense";
					}
					echo "</div><div style=\"clear:both;\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

					$theimage='';

					if(get_awpcp_option('imagesallowdisallow') == '1'){

					$totalimagesuploaded=get_total_imagesuploaded($adid);

					if($totalimagesuploaded >=1){

					  $query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$adid' AND disabled='0' ORDER BY image_name ASC";
					  if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
  						while ($rsrow=mysql_fetch_row($res)) {
  						list($image_name)=$rsrow;

  						echo "<li><a href=\"".AWPCPUPLOADURL."/$image_name\"><img src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a></li>";

  						}

  					}

					}

					echo "</ul></div><div style=\"clear:both;\"></div>";
					if(get_awpcp_option('adsenseposition') == '3'){
					echo "$showadsense";
					}
					echo "</div>";

echo "</div>";
}
else {
display_ads($where='');}

}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Uninstall
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function awpcp_uninstall(){
global $message;

$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');

   $table_name1 = $wpdb->prefix . "awpcp_categories";
   $table_name2 = $wpdb->prefix . "awpcp_adfees";
   $table_name3 = $wpdb->prefix . "awpcp_ads";
   $table_name4 = $wpdb->prefix . "awpcp_adsettings";
   $table_name5 = $wpdb->prefix . "awpcp_adphotos";
   $table_name6 = $wpdb->prefix . "awpcp_pagename";



$tmessage="<p>These are the tables which you need to drop from your database:</p>";
$tmesage.="<p style=\"padding:25px 0px 0px 30px;\">";
$tmessage.="$table_name1<br/>";
$tmessage.="$table_name2<br/>";
$tmessage.="$table_name3<br/>";
$tmessage.="$table_name4<br/>";
$tmessage.="$table_name5<br/>";
$tmessage.="$table_name6<br/>";
$tmesage.="</p>";



	echo "<div class=\"wrap\"><h2>AWPCP Classifieds Management System: Uninstall Plugin</h2>
	$message <div style=\"padding:20px;\">Thank you for using AWPCP. You have arrived at this page by clicking the Uninstall link. For now this plugin does not provide a handy point and click method for uninstalling the database tables that are created when you activate the plugin. When you deactivate the plugin, the tables and their contents remain in the database, also the plugin classifieds page is still displayed to your users.
	<p>To fix this problem, you need to manually delete the page <b>$awpcppagename</b> and manually drop the plugin tables from your database.</p>
	<p>$tmessage</p>
	<p>A point and click method of uninstalling the plugin is planned for a future release.</p>
</div><div style=\"clear:both;\"></div>";



}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>