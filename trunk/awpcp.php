<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Plugin Name: Another Wordpress Classifieds Plugin
Plugin URI: http://www.awpcp.com
Description: AWPCP - A plugin that provides the ability to run a free or paid classified ads service on your wordpress blog
Version: 1.0.5.7
Author: A Lewis
Author URI: http://www.antisocialmediallc.com
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

dcfunctions.php courtesy of Dan Caragea http://www.datemill.com (contains its own copyright notice. Please read and adhere to the terms outlined in dcfunctions.php)
fileop.class.php courtesy of Dan Caragea http://www.datemill.com
AWPCP Classifieds icon courtesy of http://www.famfamfam.com/lab/icons/silk/

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

require("$awpcp_plugin_path"."dcfunctions.php");
require("$awpcp_plugin_path"."functions_awpcp.php");
require("$awpcp_plugin_path"."upload_awpcp.php");

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


$awpcp_db_version = "1.0.5.7";

if(field_exists($field='uploadfoldername'))
{
	$uploadfoldername=get_awpcp_option('uploadfoldername');
}
else
{
	$uploadfoldername="uploads";
}

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
	add_action( 'doadexpirations_hook', 'doadexpirations' );
	add_action('admin_menu', 'awpcp_launch');
	add_action("plugins_loaded", "init_awpcpsbarwidget");
	add_shortcode('AWPCPCLASSIFIEDSUI', 'awpcpui_homescreen');
	add_shortcode('AWPCPSHOWAD','showad');
	add_shortcode('AWPCPPLACEAD','awpcpui_postformscreen');
	add_shortcode('AWPCPBROWSEADS','awpcpui_browseadsscreen');
	add_shortcode('AWPCPEDITAD','awpcpui_editformscreen');
	add_shortcode('AWPCPPAYMENTTHANKYOU','awpcpui_paymentthankyouscreen');
	add_shortcode('AWPCPCANCELPAYMENT','awpcp_cancelpayment');
	add_shortcode('AWPCPREPLYTOAD','awpcpui_contactformscreen');
	add_shortcode('AWPCPSEARCHADS','awpcpui_searchformscreen');
	add_shortcode('AWPCPBROWSECATS','awpcpui_browsecatsscreen');
	add_filter('wp_list_pages_excludes', 'exclude_awpcp_child_pages');

	function exclude_awpcp_child_pages($output = '')
	{
		$cpagename_awpcp=get_currentpagename();

		if(isset($cpagename_awpcp) && !empty($cpagename_awpcp))
		{
			$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');
		}

		$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

		$awpcpchildpages=array();
		global $wpdb,$table_prefix;

		$query="SELECT ID FROM {$table_prefix}posts WHERE post_parent='$awpcpwppostpageid' AND post_content LIKE '%AWPCP%'";
		 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		 	while ($rsrow=mysql_fetch_row($res))
 			{
 				$awpcpchildpages[]=$rsrow[0];
 			}

		foreach ($awpcpchildpages as $awpcppageidstoexclude)
		{
			array_push($output, $awpcppageidstoexclude);
		}

		return $output;


	}


function awpcp_rules_rewrite($wp_rewrite)
{
	global $siteurl;
	$awpcppage=get_currentpagename();
	$pprefx = sanitize_title($awpcppage, $post_ID='');

		$showadspagename=sanitize_title(get_awpcp_option('showadspagename'),$post_ID='');
		$replytoadpagename=sanitize_title(get_awpcp_option('replytoadpagename'),$post_ID='');
		$showadspageguid=awpcp_get_guid($awpcpshowadspageid=awpcp_get_page_id($showadspagename));
		$replytoadsadspageguid=awpcp_get_guid($awpcpreplytoadspageid=awpcp_get_page_id($replytoadpagename));
		$awpcppageguid=awpcp_get_guid($awpcppageid=awpcp_get_page_id($pprefx));
		$browsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'),$post_ID='');
		$browsecatspageguid=awpcp_get_guid($awpcpbrowsecatspageid=awpcp_get_page_id($browsecatspagename));
		$paymentcancelpagename=sanitize_title(get_awpcp_option('paymentcancelpagename'),$post_ID='');
		$paymentcancelpageguid=awpcp_get_guid($awpcppaymentcancelpageid=awpcp_get_page_id($paymentcancelpagename));
		$paymentthankyoupagename=sanitize_title(get_awpcp_option('paymentthankyoupagename'),$post_ID='');
		$paymentthankyoupageguid=awpcp_get_guid($awpcppaymentcancelpageid=awpcp_get_page_id($paymentthankyoupagename));
		$categoriesviewpagename=sanitize_title(get_awpcp_option('categoriesviewpagename'),$post_ID='');
		$browsecatspageguid=awpcp_get_guid($awpcpbrowsecatspageid=awpcp_get_page_id($browsecatspagename));

	$awpcp_rules = array(
	$pprefx.'/'.$showadspagename.'/(.+?)/(.+?)' => $showadspageguid.'&id='.$wp_rewrite->preg_index(1),
	$pprefx.'/'.$replytoadpagename.'/(.+?)/(.+?)' => $replytoadsadspageguid.'&id='.$wp_rewrite->preg_index(1),
	$pprefx.'/'.$browsecatspagename.'/(.+?)/(.+?)' => $browsecatspageguid.'&category_id='.$wp_rewrite->preg_index(1),
	$pprefx.'/'.$paymentthankyoupagename.'/(.+?)' => $paymentthankyoupageguid.'&i='.$wp_rewrite->preg_index(1),
	$pprefx.'/'.$paymentcancelpagename.'/(.+?)' => $paymentcancelpageguid.'&i='.$wp_rewrite->preg_index(1),

	$pprefx.'/'.$categoriesviewpagename => $awpcppageguid.'&layout=2'
	);

    $wp_rewrite->rules = $awpcp_rules + $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules', 'awpcp_rules_rewrite');


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// The function to add the reference to the plugin css style sheet to the header of the index page
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function awpcp_addcss()
		{
			$awpcpstylesheet="awpcpstyle.css";
			 echo "\n".'<style type="text/css" media="screen">@import "'.AWPCPURL.'css/'.$awpcpstylesheet.'";</style>';
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
	  `ad_fee_paid` float(7,2) NOT NULL,
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
	  `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `option_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`config_option`)
	) ENGINE=MyISAM COMMENT='0-checkbox, 1-text,2-textarea';

		INSERT INTO " . $table_name4 . " (`config_option`, `config_value`, `config_diz`,`config_group_id`, `option_type`) VALUES
		('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: existing page will be overwritten]','10','1'),
		('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('browseadspagename', 'Browse Ads', 'Name browse ads apge. [CAUTION: existing page will be overwritten]','10','1'),
		('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1'),
		('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1'),
		('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1'),
		('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [ CAUTION: existing page will be overwritten ]','10','1'),
		('editadpagename', 'Edit Ad', 'Name for edit ad page. [ CAUTION: existing page will be overwritten ]','10','1'),
		('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page ]','10','1'),
		('freepay', '0', 'Charge Listing Fee?','3','0'),
		('requireuserregistration', '0', 'Require user registration?','7','0'),
		('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php **Only needed if registration is required and your login url is mod-rewritten ] ','7','1'),
		('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1'),
		('main_page_display', '0', 'Main page layout [ check for ad listings ] [ Uncheck for categories ]','1','0'),
		('activatemylayoutdisplayads', '0', 'Activate display ad mod file [applies only if you created your own layout for the display_ads function in the file awpcp_display_ad_my_layout.php]','1','0'),
		('activatemylayoutshowad', '0', 'Activate show ad mod file [applies only if you created your own layout for the showad function in the file awpcp_showad_my_layout.php]','1','0'),
		('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1'),
		('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0'),
		('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0'),
		('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0'),
		('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0'),
		('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0'),
		('paylivetestmode', '0', 'Put Paypal and 2Checkout in test mode.','3','0'),
		('useadsense', '1', 'Activate adsense','5','0'),
		('adsense', 'Adsense code', 'Your adsense code [ Best if 468 by 60 text or banner. ]','5',2),
		('adsenseposition', '2', 'Adsense position. [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1'),
		('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiry].','2','1'),
		('imagesallowdisallow', '1', 'Uncheck to disallow images in ads. [Affects both free and paid]','4','0'),
		('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0'),
		('imagesallowedfree', '4', ' Free mode number of images allowed?','4','1'),
		('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1'),
		('maximagesize', '150000', 'Maximum size per image user can upload to system.','4','1'),
		('minimagesize', '300', 'Minimum size per image user can upload to system','4','1'),
		('imgthumbwidth', '125', 'Width for thumbnails created upon upload.','4','1'),
		('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?','2','1'),
		('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]','3','1'),
		('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments','3','1'),
		('displaycurrencycode', 'USD', 'The currency to show on your payment pages','3','1'),
		('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1'),
		('activatepaypal', '1', 'Activate PayPal','3','0'),
		('activate2checkout', '1', 'Activate 2Checkout ','3','0'),
		('paypalpaymentsrecurring', '0', 'Use recurring payments paypal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0'),
		('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0'),
		('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0'),
		('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1'),
		('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2'),
		('notifyofadposted', '1', 'Notify admin of new ad.','2','0'),
		('imagesapprove', '0', 'Hide images until admin approves them','4','0'),
		('adapprove', '0', 'Disable ad until admin approves','2','0'),
		('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0'),
		('showadcount', '1', 'Show how many ads a category contains.','2','0'),
		('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0'),
		('displayadviews', '1', 'Show ad views','2','0'),
		('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1'),
		('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1'),
		('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1'),
		('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0'),
		('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0'),
		('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1'),
		('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1'),
		('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2'),
		('resendakeyformsubjectline', 'The classified ad activation key you requested', 'Subject line for email sent out when someone requests their activation key resent','8', '1'),
		('resendakeyformbodymessage', 'You asked to have your classified ad activation key resent. Below are all the activation keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their activation key resent', '8','2'),
		('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1'),
		('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.','Message body text for email sent out when the payment processing does not complete', '8','2'),
		('seofriendlyurls', '0', 'Search Engine Friendly URLs? [ Does not work in some instances ]', '11','0'),
		('pathvaluecontact', '3', 'If contact page link not working in seo mode change value until correct path is found. Start at 1', '11','1'),
		('pathvalueshowad', '3', 'If show ad links not working in seo mode change value until correct path is found. Start at 1', '11','1'),
		('pathvaluebrowsecats', '2', 'If browse categories links not working in seo mode change value until correct path is found. Start at 1', '11','1'),
		('pathvalueviewcategories', '2', 'If the menu link to view categories layout is not working in seo mode change value until correct path is found. Start at 1', '11','1'),
		('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1'),
		('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1'),
		('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0'),
		('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2'),
		('hyperlinkurlsinadtext', '1', 'Make URLs in ad text clickable', '2','0'),
		('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0'),
		('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2'),
		('displayphonefield', '1', 'Show phone field','6','0'),
		('displayphonefieldreqop', '0', 'Require phone','6','0'),
		('displaycityfield', '1', 'Show city field.','6','0'),
		('displaycityfieldreqop', '0', 'Require city','6','0'),
		('displaystatefield', '1', 'Show state field.','6','0'),
		('displaystatefieldreqop', '0', 'Require state','6','0'),
		('displaycountryfield', '1', 'Show country field.','6','0'),
		('displaycountryfieldreqop', '0', 'Require country','6','0'),
		('displaycountyvillagefield', '0', 'Show County/village/other.','6','0'),
		('displaycountyvillagefieldreqop', '0', 'Require county/village/other.','6','0'),
		('displaypricefield', '1', 'Show price field.','6','0'),
		('displaypricefieldreqop', '0', 'Require price.','6','0'),
		('displaywebsitefield', '1', 'Show website field','6','0'),
		('displaywebsitefieldreqop', '0', 'Require website','6','0'),
		('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0'),
		('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2'),
		('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0');


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

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 	// Fix the shortcode issue if present in installed version
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$wpdb->query("UPDATE " .$wpdb->prefix . "posts set post_content='[AWPCPCLASSIFIEDSUI]' WHERE post_content='[[AWPCPCLASSIFIEDSUI]]'");


 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Update ad_settings table to ad field config groud ID if field does not exist in installed version
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$cgid_column_name="config_group_id";
	$cgid_column_name_exists=mysql_query("SELECT $cgid_column_name FROM $table_name4;");

   	if (mysql_errno())
   	{
		$query=("ALTER TABLE " . $table_name4 . "  ADD `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER config_diz");
	 	@mysql_query($query);

		$myconfig_group_ops_1=array('showlatestawpcpnews','uiwelcome','main_page_display','contactformcheckhuman', 'contactformcheckhumanhighnumval','awpcptitleseparator','showcityinpagetitle','showstateinpagetitle','showcountryinpagetitle','showcategoryinpagetitle','showcountyvillageinpagetitle','activatemylayoutshowad','activatemylayoutdisplayads');
		$myconfig_group_ops_2=array('addurationfreemode','maxcharactersallowed','notifyofadexpiring', 'notifyofadposted', 'adapprove', 'disablependingads', 'showadcount', 'displayadviews','onlyadmincanplaceads','allowhtmlinadtext', 'hyperlinkurlsinadtext', 'notice_awaiting_approval_ad', 'buildsearchdropdownlists','visitwebsitelinknofollow');
		$myconfig_group_ops_3=array('freepay','paylivetestmode','paypalemail', 'paypalcurrencycode', 'displaycurrencycode', '2checkout', 'activatepaypal', 'activate2checkout','twocheckoutpaymentsrecurring','paypalpaymentsrecurring');
		$myconfig_group_ops_4=array('imagesallowdisallow', 'awpcp_thickbox_disabled','imagesapprove', 'imagesallowedfree', 'uploadfoldername', 'maximagesize','minimagesize', 'imgthumbwidth');
		$myconfig_group_ops_5=array('useadsense', 'adsense', 'adsenseposition');
		$myconfig_group_ops_6=array('displayphonefield', 'displayphonefieldreqop', 'displaycityfield', 'displaycityfieldreqop', 'displaystatefield','displaystatefieldreqop', 'displaycountryfield', 'displaycountryfieldreqop', 'displaycountyvillagefield', 'displaycountyvillagefieldreqop', 'displaypricefield', 'displaypricefieldreqop', 'displaywebsitefield', 'displaywebsitefieldreqop');
		$myconfig_group_ops_7=array('requireuserregistration', 'postloginformto', 'registrationurl');
		$myconfig_group_ops_8=array('contactformsubjectline','contactformbodymessage','listingaddedsubject','listingaddedbody','resendakeyformsubjectline','resendakeyformbodymessage','paymentabortedsubjectline','paymentabortedbodymessage');
		$myconfig_group_ops_9=array('smtphost','smtpusername','smtppassword');
		$myconfig_group_ops_10=array('userpagename','showadspagename','placeadpagename','browseadspagename','browsecatspagename','editadpagename','paymentthankyoupagename','paymentcancelpagename','replytoadpagename','searchadspagename','categoriesviewpagename');
		$myconfig_group_ops_11=array('seofriendlyurls','pathvaluecontact','pathvalueshowad','pathvaluebrowsecategory','pathvalueviewcategories','pathvaluecancelpayment','pathvaluepaymentthankyou');


		foreach($myconfig_group_ops_1 as $myconfig_group_op_1){add_config_group_id($cvalue='1',$myconfig_group_op_1);}
		foreach($myconfig_group_ops_2 as $myconfig_group_op_2){add_config_group_id($cvalue='2',$myconfig_group_op_2);}
		foreach($myconfig_group_ops_3 as $myconfig_group_op_3){add_config_group_id($cvalue='3',$myconfig_group_op_3);}
		foreach($myconfig_group_ops_4 as $myconfig_group_op_4){add_config_group_id($cvalue='4',$myconfig_group_op_4);}
		foreach($myconfig_group_ops_5 as $myconfig_group_op_5){add_config_group_id($cvalue='5',$myconfig_group_op_5);}
		foreach($myconfig_group_ops_6 as $myconfig_group_op_6){add_config_group_id($cvalue='6',$myconfig_group_op_6);}
		foreach($myconfig_group_ops_7 as $myconfig_group_op_7){add_config_group_id($cvalue='7',$myconfig_group_op_7);}
		foreach($myconfig_group_ops_8 as $myconfig_group_op_8){add_config_group_id($cvalue='8',$myconfig_group_op_8);}
		foreach($myconfig_group_ops_9 as $myconfig_group_op_9){add_config_group_id($cvalue='9',$myconfig_group_op_9);}
		foreach($myconfig_group_ops_10 as $myconfig_group_op_10){add_config_group_id($cvalue='10',$myconfig_group_op_10);}
		foreach($myconfig_group_ops_11 as $myconfig_group_op_11){add_config_group_id($cvalue='11',$myconfig_group_op_11);}

		$wpdb->query("UPDATE " . $table_name4 . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Main page layout [ check for ad listings ] [ Uncheck for categories ]' WHERE `config_option` = 'main_page_display'");
		$wpdb->query("UPDATE " . $table_name4 . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Put Paypal and 2Checkout in test mode' WHERE `config_option` = 'paylivetestmode'");

	 }

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Match up the ad settings fields of current versions and upgrading versions
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if(!field_exists($field='userpagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: Make sure page does not already exist]','10','1');");}
	if(!field_exists($field='showadspagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='placeadpagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='browseadspagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('browseadspagename', 'Browse Ads', 'Name browse ads apge. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='searchadspagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='paymentthankyoupagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='paymentcancelpagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='replytoadpagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='browsecatspagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='editadpagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('editadpagename', 'Edit Ad', 'Name for edit ad page. [CAUTION: existing page will be overwritten]','10','1');");}
	if(!field_exists($field='categoriesviewpagename')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page]','10','1');");}
	if(!field_exists($field='freepay')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('freepay', '0', 'Charge Listing Fee?','3','0');");}
	if(!field_exists($field='requireuserregistration')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('requireuserregistration', '0', 'Require user registration?','7','0');");}
	if(!field_exists($field='postloginformto')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
	if(!field_exists($field='registrationurl')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
	if(!field_exists($field='main_page_display')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('main_page_display', '0', 'Main page layout [ check for ad listings | Uncheck for categories ]','1','0');");}
	if(!field_exists($field='activatemylayoutdisplayads')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('activatemylayoutdisplayads', '0', 'Activate display ad mod file [applies only if you created your own layout for the display_ads function in the file awpcp_display_ad_my_layout.php]','1','0');");}
	if(!field_exists($field='activatemylayoutshowad')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('activatemylayoutshowad', '0', 'Activate show ad mod file [applies only if you created your own layout for the showad function in the file awpcp_showad_my_layout.php]','1','0');");}
	if(!field_exists($field='awpcptitleseparator')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1');");}
	if(!field_exists($field='showcityinpagetitle')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0');");}
	if(!field_exists($field='showstateinpagetitle')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0');");}
	if(!field_exists($field='showcountryinpagetitle')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0');");}
	if(!field_exists($field='showcountyvillageinpagetitle')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0');");}
	if(!field_exists($field='showcategoryinpagetitle')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0');");}
	if(!field_exists($field='paylivetestmode')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paylivetestmode', '0', 'Put Paypal and 2Checkout in test mode.','3','0');");}
	if(!field_exists($field='useadsense')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('useadsense', '1', 'Activate adsense','5','0');");}
	if(!field_exists($field='adsense')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adsense', 'Adsense code', 'Your adsense code [ Best if 468 by 60 text or banner. ]','5','2');");}
	if(!field_exists($field='adsenseposition')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adsenseposition', '2', 'Adsense position. [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1');");}
	if(!field_exists($field='addurationfreemode')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiry].','2','1');");}
	if(!field_exists($field='imagesallowdisallow')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imagesallowdisallow', '1', 'Uncheck to disallow images in ads. [Affects both free and paid]','4','0');");}
	if(!field_exists($field='awpcp_thickbox_disabled')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0');");}
	if(!field_exists($field='imagesallowedfree')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imagesallowedfree', '4', ' Free mode number of images allowed?','4','1');");}
	if(!field_exists($field='uploadfoldername')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1');");}
	if(!field_exists($field='maximagesize')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('maximagesize', '150000', 'Maximum size per image user can upload to system.','4','1');");}
	if(!field_exists($field='minimagesize')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('minimagesize', '300', 'Minimum size per image user can upload to system','4','1');");}
	if(!field_exists($field='imgthumbwidth')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imgthumbwidth', '125', 'Width for thumbnails created upon upload.','4','1');");}
	if(!field_exists($field='maxcharactersallowed')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?','2','1');");}
	if(!field_exists($field='paypalemail')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]','3','1');");}
	if(!field_exists($field='paypalcurrencycode')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments','3','1');");}
	if(!field_exists($field='displaycurrencycode')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycurrencycode', 'USD', 'The currency to show on your payment pages','3','1');");}
	if(!field_exists($field='2checkout')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1');");}
	if(!field_exists($field='activatepaypal')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('activatepaypal', '1', 'Activate PayPal','3','0');");}
	if(!field_exists($field='activate2checkout')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('activate2checkout', '1', 'Activate 2Checkout ','3','0');");}
	if(!field_exists($field='paypalpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paypalpaymentsrecurring', '0', 'Use recurring payments paypal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
	if(!field_exists($field='twocheckoutpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
	if(!field_exists($field='notifyofadexpiring')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0');");}
	if(!field_exists($field='notifyofadposted')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('notifyofadposted', '1', 'Notify admin of new ad.','2','0');");}
	if(!field_exists($field='listingaddedsubject')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1');");}
	if(!field_exists($field='listingaddedbody')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2');");}
	if(!field_exists($field='imagesapprove')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imagesapprove', '0', 'Hide images until admin approves them','4','0');");}
	if(!field_exists($field='adapprove')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adapprove', '0', 'Disable ad until admin approves','2','0');");}
	if(!field_exists($field='disablependingads')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0');");}
	if(!field_exists($field='showadcount')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showadcount', '1', 'Show how many ads a category contains.','2','0');");}
	if(!field_exists($field='noadsinparentcat')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0');");}
	if(!field_exists($field='displayadviews')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayadviews', '1', 'Show ad views','2','0');");}
	if(!field_exists($field='smtphost')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1');");}
	if(!field_exists($field='smtpusername')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1');");}
	if(!field_exists($field='smtppassword')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1');");}
	if(!field_exists($field='onlyadmincanplaceads')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0');");}
	if(!field_exists($field='contactformcheckhuman')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0');");}
	if(!field_exists($field='contactformcheckhumanhighnumval')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1');");}
	if(!field_exists($field='contactformsubjectline')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1');");}
	if(!field_exists($field='contactformbodymessage')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2');");}
	if(!field_exists($field='resendakeyformsubjectline')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('resendakeyformsubjectline', 'The classified ad activation key you requested', 'Subject line for email sent out when someone requests their activation key resent','8', '1');");}
	if(!field_exists($field='resendakeyformbodymessage')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('resendakeyformbodymessage', 'You asked to have your classified ad activation key resent. Below are all the activation keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their activation key resent', '8','2');");}
	if(!field_exists($field='paymentabortedsubjectline')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1');");}
	if(!field_exists($field='paymentabortedbodymessage')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.', 'Message body text for email sent out when the payment processing does not complete','8','2');");}
	if(!field_exists($field='seofriendlyurls')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('seofriendlyurls', '0', 'Search Engine Friendly URLs? [ Does not work in some instances ]', '11','0');");}
	if(!field_exists($field='pathvaluecontact')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluecontact', '3', 'If contact page link not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	if(!field_exists($field='pathvalueshowad')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvalueshowad', '3', 'If show ad links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	if(!field_exists($field='pathvaluebrowsecats')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluebrowsecats', '2', 'If browse categories links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	if(!field_exists($field='pathvalueviewcategories')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvalueviewcategories', '2', 'If the view categories link is not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	if(!field_exists($field='pathvaluecancelpayment')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
	if(!field_exists($field='pathvaluepaymentthankyou')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
	if(!field_exists($field='allowhtmlinadtext')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0');");}
	if(!field_exists($field='htmlstatustext')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2');");}
	if(!field_exists($field='hyperlinkurlsinadtext')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('hyperlinkurlsinadtext', '1', 'Make URLs in ad text clickable', '2','0');");}
	if(!field_exists($field='visitwebsitelinknofollow')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0');");}
	if(!field_exists($field='notice_awaiting_approval_ad')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2');");}
	if(!field_exists($field='displayphonefield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayphonefield', '1', 'Show phone field','6','0');");}
	if(!field_exists($field='displayphonefieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayphonefieldreqop', '0', 'Require phone','6','0');");}
	if(!field_exists($field='displaycityfield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycityfield', '1', 'Show city field.','6','0');");}
	if(!field_exists($field='displaycityfieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycityfieldreqop', '0', 'Require city','6','0');");}
	if(!field_exists($field='displaystatefield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaystatefield', '1', 'Show state field.','6','0');");}
	if(!field_exists($field='displaystatefieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaystatefieldreqop', '0', 'Require state','6','0');");}
	if(!field_exists($field='displaycountryfield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountryfield', '1', 'Show country field.','6','0');");}
	if(!field_exists($field='displaycountryfieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountryfieldreqop', '0', 'Require country','6','0');");}
	if(!field_exists($field='displaycountyvillagefield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountyvillagefield', '0', 'Show County/village/other.','6','0');");}
	if(!field_exists($field='displaycountyvillagefieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountyvillagefieldreqop', '0', 'Require county/village/other.','6','0');");}
	if(!field_exists($field='displaypricefield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaypricefield', '1', 'Show price field.','6','0');");}
	if(!field_exists($field='displaypricefieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaypricefieldreqop', '0', 'Require price.','6','0');");}
	if(!field_exists($field='displaywebsitefield')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaywebsitefield', '1', 'Show website field','6','0');");}
	if(!field_exists($field='displaywebsitefieldreqop')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaywebsitefieldreqop', '0', 'Require website','6','0');");}
	if(!field_exists($field='buildsearchdropdownlists')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0');");}
	if(!field_exists($field='uiwelcome')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2');");}
	if(!field_exists($field='showlatestawpcpnews')){$wpdb->query("INSERT  INTO " . $table_name4 . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0');");}


 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Create additional classifieds pages if they do not exist
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		$tableexists=checkfortable($table_name6);
		if($tableexists)
		{
			$cpagename_awpcp=get_currentpagename();
			if(isset($cpagename_awpcp) && !empty($cpagename_awpcp))
			{
				$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

				$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

				$showadspagename=get_awpcp_option('showadspagename');
				$replytoadpagename=get_awpcp_option('replytoadpagename');
				$editadpagename=get_awpcp_option('editadpagename');
				$placeadpagename=get_awpcp_option('placeadpagename');
				$browseadspagename=get_awpcp_option('browseadspagename');
				$browsecatspagename=get_awpcp_option('browsecatspagename');
				$searchadspagename=get_awpcp_option('searchadspagename');
				$paymentthankyoupagename=get_awpcp_option('paymentthankyoupagename');
				$paymentcancelpagename=get_awpcp_option('paymentcancelpagename');

				if(!findpage($showadspagename,$shortcode='[AWPCPSHOWAD]'))
				{
					maketheclassifiedsubpage($showadspagename,$awpcpwppostpageid,$shortcode='[AWPCPSHOWAD]');
				}
				if(!findpage($placeadpagename,$shortcode='[AWPCPPLACEAD]'))
				{
					maketheclassifiedsubpage($placeadpagename,$awpcpwppostpageid,$shortcode='[AWPCPPLACEAD]');
				}
				if(!findpage($browseadspagename,$shortcode='[AWPCPBROWSEADS]'))
				{
					maketheclassifiedsubpage($browseadspagename,$awpcpwppostpageid,$shortcode='[AWPCPBROWSEADS]');
				}
				if(!findpage($searchadspagename,$shortcode='[AWPCPSEARCHADS]'))
				{
					maketheclassifiedsubpage($searchadspagename,$awpcpwppostpageid,$shortcode='[AWPCPSEARCHADS]');
				}
				if(!findpage($paymentthankyoupagename,$shortcode='[AWPCPPAYMENTTHANKYOU]'))
				{
					maketheclassifiedsubpage($paymentthankyoupagename,$awpcpwppostpageid,$shortcode='[AWPCPPAYMENTTHANKYOU]');
				}
				if(!findpage($paymentcancelpagename,$shortcode='[AWPCPCANCELPAYMENT]'))
				{
					maketheclassifiedsubpage($paymentcancelpagename,$awpcpwppostpageid,$shortcode='[AWPCPCANCELPAYMENT]');
				}
				if(!findpage($editadpagename,$shortcode='[AWPCPEDITAD]'))
				{
					maketheclassifiedsubpage($editadpagename,$awpcpwppostpageid,$shortcode='[AWPCPEDITAD]');
				}
				if(!findpage($replytoadpagename,$shortcode='[AWPCPREPLYTOAD]'))
				{
					maketheclassifiedsubpage($replytoadpagename,$awpcpwppostpageid,$shortcode='[AWPCPREPLYTOAD]');
				}
				if(!findpage($browsecatspagename,$shortcode='[AWPCPBROWSECATS]'))
				{
					maketheclassifiedsubpage($browsecatspagename,$awpcpwppostpageid,$shortcode='[AWPCPBROWSECATS]');
				}
			}
		}

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Add new field websiteurl to awpcp_ads
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

   	$ad_websiteurl_column="websiteurl";

   	$ad_websiteurl_field=mysql_query("SELECT $ad_websiteurl_column FROM $table_name3;");

   		if (mysql_errno())
   		{
   			$query=("ALTER TABLE " . $table_name3 . "  ADD `websiteurl` VARCHAR( 500 ) NOT NULL AFTER `ad_contact_email`");
   			 @mysql_query($query);
		}

	 $query=("ALTER TABLE " . $table_name3 . "  DROP INDEX `titdes`");
	 @mysql_query($query);
	 $query=("ALTER TABLE " . $table_name3 . "  ADD FULLTEXT KEY `titdes` (`ad_title`,`ad_details`)");
	 @mysql_query($query);

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Add new field ad_fee_paid for sorting ads by paid listings first
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    	$ad_fee_paid_column="$ad_fee_paid";

		$ad_fee_paid_field=mysql_query("SELECT $ad_fee_paid_column FROM $table_name3;");

		if (mysql_errno())
		{
			 $query=("ALTER TABLE " . $table_name3 . "  ADD `ad_fee_paid` float(7,2) NOT NULL AFTER `adterm_id`");
			 @mysql_query($query);
		}

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Increase the length value for the ad_item_price field
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$wpdb->query("ALTER TABLE " . $table_name3 . " CHANGE `ad_item_price` `ad_item_price` INT( 25 ) NOT NULL");

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Ad new field add_county_village to awpcp_ads
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$ad_county_village_column="ad_county_village";

	$ad_county_vilalge_field=mysql_query("SELECT $ad_county_village_column FROM $table_name3;");

	if (mysql_errno())
	{
		$query=("ALTER TABLE " . $table_name3 . "  ADD `ad_county_village` varchar(255) NOT NULL AFTER `ad_country`");
	}

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Add field ad_views to table awpcp_ads to track ad views
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$ad_views_column="ad_views";

	$ad_views_field=mysql_query("SELECT $ad_views_column FROM $table_name3;");

		if (mysql_errno())
		{
			$wpdb->query("ALTER TABLE " . $table_name3 . "  ADD `ad_views` int(10) NOT NULL AFTER `ad_item_price`");
		}

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Insert new field ad_item_price into awpcp_ads table
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	$ad_itemprice_column="ad_item_price";

	$ad_itemprice_field=mysql_query("SELECT $ad_itemprice_column FROM $table_name3;");

		if (mysql_errno())
		{
			$wpdb->query("ALTER TABLE " . $table_name3 . "  ADD `ad_item_price` INT( 10 ) NOT NULL AFTER `ad_country`");
		}

 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Fix the UTF-8 Charset problem and add option contactformcheckhuman to awpcp_adsettings (March 25 2009)
 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        update_option( "awpcp_db_version", $awpcp_db_version );

  	}
  }
}


function flush_rewrite_rules()
{
global $wp_rewrite;
$wp_rewrite->flush_rules();
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


function awpcp_home_screen()
{

	global $message,$user_identity,$wpdb,$awpcp_plugin_path,$imagesurl,$awpcp_db_version,$haspoweredbyremovalmodule,$hasregionsmodule,$hascaticonsmodule;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";


	echo "<div class=\"wrap\"><h2>";
	_e("AWPCP Classifieds Management System","AWPCP");
	echo "</h2><p>";
	_e("You are using version","AWPCP");
	echo " <b>$awpcp_db_version</b> </p>
	$message <div style=\"padding:20px;\">";
	_e("Thank you for using Another Wordpress Classifieds Plugin. As a reminder, please use this plugin knowing that is it is a work in progress and is by no means guaranteed to be a bug-free product. Development of this plugin is not a full-time undertaking. Consequently upgrades will be slow in coming; however, please feel free to report bugs and request new features via the plugin website.","AWPCP");
	echo "</div>";


$tableexists=checkfortable($table_name4);

if(!$tableexists)
{
	echo "<b>";
	_e("!!!!ALERT","AWPCP");
	echo ":</b>";
	_e("There appears to be a problem with the plugin. The plugin is activated but your database tables are missing. Please de-activate the plugin from your plugins page then try to reactivate it.","AWPCP");
}

else
{

	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		echo $awpcpsetuptext;

	} else {


$awpcp_classifieds_page_conflict_check=checkforduplicate($cpagename_awpcp);
if( $awpcp_classifieds_page_conflict_check > 1)
{
	echo "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\"><img src=\"$imagesurl/Warning.png\" border=\"0\" alt=\"Alert\" style=\"float:left;margin-right:10px;\">";
	_e("It appears you have a potential problem that could result in the malfunctioning of Another Wordpress Classifieds plugin. A check of your database was performed and duplicate entries were found that share the same post_name value as your classifieds page. If for some reason you uninstall and then reinstall this plugin and the duplicate pages remain in your database, it could break the plugin and prevent it from working. To fix this problem you can manually delete the duplicate pages and leave only the page with the ID of your real classifieds page, or you can use the link below to rebuild your classifieds page. The process will include first deleting all existing pages with a post name value identical to your classifieds page. Note that if you recreate the page, it will be assigned a new page ID so if you are referencing the classifieds page ID anywhere outside of the classifieds program you will need to adjust the old ID to the new ID.","AWPCP");
	echo "<br/>";
	_e("Number of duplicate pages","AWPCP");
	echo ": [<b>$awpcp_classifieds_page_conflict_check</b>]";
	echo "<br/>";
	_e("Duplicated post name","AWPCP");
	echo ":[<b>$awpcppagename</b>]";
	echo "<p><a href=\"?page=Configure1&action=recreatepage\">";
	_e("Recreate the classifieds page to fix the conflict","AWPCP");
	echo "</a></p></div>";
}

echo "<div style=\"float:left;width:50%;\">";
echo "<div class=\"postbox\">";
echo "<div style=\"background:#eeeeee; padding:10px;color:#444444;\"><strong>";
_e("Another Wordpress Classifieds Plugin Stats","AWPCP");
echo "</strong></div>";

$totallistings=countlistings();
echo "<div style=\"padding:10px;\">";
_e("Number of listings currently in the system","AWPCP");
echo ": [<b>$totallistings</b>]";
echo "</div>";

if(get_awpcp_option(freepay) == 1)
{
	if(adtermsset())
	{
		echo "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
		_e("You have setup your listing fees. To edit your fees use the 'Manage Listing Fees' option.","AWPCP");
		echo "</div>";
	}
	else
	{
		echo "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
		_e("You have not configured your Listing fees. Use the 'Manage Listing Fees' option to set up your listing fees. Once that is completed, if you are running in pay mode, the options will automatically appear on the listing form for users to fill out.","AWPCP");
		echo "</div>";
	}
}
else
{
	echo "<div style=\"padding:10px;\">";
	_e("You currently have your system configured to run in free mode. To change to 'pay' mode go to 'Manage General Options' and uncheck the box that accompanies the text 'Charge listing fee?'","AWPCP");
	echo "</div>";
}


if(categoriesexist())
{
	$totalcategories=countcategories();
	$totalparentcategories=countcategoriesparents();
	$totalchildrencategories=countcategorieschildren();

	echo "<div style=\"padding:10px;border-top:1px solid #dddddd;\"><ul>";
	echo "<li style=\"margin-bottom:6px;list-style:none;\">";
	_e("Total number of categories in the system","AWPCP");
	echo ": [<b>$totalcategories</b>]</li>";
	echo "<li style=\"margin-bottom:6px;list-style:none;\">";
	_e("Number of Top Level parent categories","AWPCP");
	echo ": [<b>$totalparentcategories</b>]</li>";
	echo "<li style=\"margin-bottom:6px;list-style:none;\">";
	_e("Number of sub level children categories","AWPCP");
	echo ": [<b>$totalchildrencategories</b>]</li>";
	echo "</ul><p>";
	_e("Use the 'Manage Categories' option to edit/delete current categories or add new categories.","AWPCP");
	echo "</p></div>";
}
else
{
	echo "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
	_e("You have not setup any categories. Use the 'Manage Categories' option to set up your categories.","AWPCP");
	echo "</div>";
}

if(get_awpcp_option('freepay') == 1)
{
	echo "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
	_e("You currently have your system configured to run in pay mode. To change to 'free' mode go to 'Manage General Options' and check the box that accompanies the text 'Charge listing fee?'","AWPCP");
	echo "</div>";
}

echo "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
_e("Use the buttons on the right to configure your various options","AWPCP");
echo "</div>";
echo "</div>";

if(get_awpcp_option('showlatestawpcpnews'))
{
	echo "<div class=\"postbox\">";
	echo "<div style=\"background:#eeeeee; padding:10px;color:#444444;\"><strong>";
	_e("Latest News About Another Wordpress Classifieds Plugin","AWPCP");
	echo "</strong></div>";

		$widgets = get_option( 'dashboard_widget_options' );
		@extract( @$widgets['dashboard_secondary'], EXTR_SKIP );
		$awpcpfeedurl="http://feeds2.feedburner.com/Awpcp";
		$awpcpgetrss = @fetch_feed( $awpcpfeedurl );
			$widgets['dashboard_primary'] = array(
			'items' => 5,
			'show_summary' => 1,
			'show_author' => 0,
			'show_date' => 1
		);

		if ( is_wp_error($awpcpgetrss) ) {
			if ( is_admin() || current_user_can('manage_options') ) {
				echo '<div class="rss-widget"><p>';
				printf(__('<strong>RSS Error</strong>: %s'), $awpcpgetrss->get_error_message());
				echo '</p></div>';
			}
		} elseif ( !$awpcpgetrss->get_item_quantity() ) {
			return false;
		} else {
			echo '<div style="padding:10px;">';
			wp_widget_rss_output( $awpcpgetrss, $widgets['dashboard_primary'] );
			echo '</div>';
	}

	echo "</div>";
}

echo "
</div>
</div>
<div style=\"float:left;width:30%;margin:0 0 0 20px;\">
<ul>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure1\">";_e("Manage General Options","AWPCP"); echo "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure2\">";_e("Manage Listing Fees","AWPCP"); echo "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure3\">";_e("Manage Categories","AWPCP"); echo "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage1\">";_e("Manage Listings","AWPCP"); echo "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage2\">";_e("Manage Images","AWPCP"); echo "</a></li>
</ul>";

if(get_awpcp_option(showlatestawpcpnews))
{

echo "<p>
<a href=\"http://www.awpcp.com\">";
_e("Plugin Support Site","AWPCP");
echo "</a></p>";
	echo "<p><b>";_e("Premium Modules","AWPCP");echo"</b></p>
	<em>";_e("Installed","AWPCP");echo "</em><br/><ul>";
	if( ($hasregionsmodule != 1) && ($hascaticonsmodule != 1) )
	{
		echo "<li>"; _e("No premium modules installed","AWPCP"); echo "</li>";
	}
	else
	{
		if( ($hasregionsmodule == 1) )
		{
			echo "<li>"; _e("Regions Control Module","AWPCP"); echo "</li>";
		}
		if( ($hascaticonsmodule == 1) )
		{
			echo "<li>"; _e("Category Icons Module","AWPCP"); echo "</li>";
		}
	}

	echo "</ul><em>"; _e("Uninstalled","AWPCP"); echo "</em><ul>";

	if( ($hasregionsmodule != 1) )
	{
		echo "<li><a href=\"http://www.awpcp.com/premium-modules/regions-control-module\">"; _e("Regions Control Module","AWPCP"); echo "</a></li>";
	}
	if( ($hascaticonsmodule != 1) )
	{
		echo "<li><a href=\"http://www.awpcp.com/premium-modules/category-icons-module/\">"; _e("Category Icons Module","AWPCP"); echo "</a></li>";
	}
	if( ($hasregionsmodule == 1) && ($hascaticonsmodule == 1) )
	{
		echo "<li>"; _e("No uninstalled premium modules","AWPCP"); echo "</li>";
	}

	echo "</ul><p><b>"; _e("Other Modules","AWPCP"); echo "</b></p>
	<em>"; _e("Installed","AWPCP"); echo "</em><br/><ul>";

	if( ($haspoweredbyremovalmodule != 1) )
	{
		echo "<li>"; _e("No [Other] modules installed","AWPCP"); echo "</li>";
	}
	else
	{
		if( ($haspoweredbyremovalmodule == 1) )
		{
			echo "<li>"; _e("Powered-By Link Removal Module","AWPCP"); echo "</li>";
		}
	}

	echo "</ul><em>"; _e("Uninstalled","AWPCP"); echo "</em><ul>";

	if( ($haspoweredbyremovalmodule != 1) )
	{
		echo "<li><a href=\"http://www.awpcp.com/premium-modules/powered-by-link-removal-module/\">"; _e("Powered-By Link Removal Module","AWPCP"); echo "</a></li>";
	}
	else
	{
		_e("No uninstalled [Other] modules","AWPCP");
	}

	echo "</ul>";
}

echo "

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

function awpcp_opsconfig_settings()
{

	global $wpdb,$table_prefix;
	global $message;



	if(isset($_REQUEST['mspgs']) && !empty($_REQUEST['mspgs']) )
	{
		$makesubpages=$_REQUEST['mspgs'];
	}

	if(!isset($makesubpages) && empty($makesubpages))
	{
		$makesubpages='';
	}

	if(isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
	{
		if($_REQUEST['action'] == 'recreatepage')
		{
				$cpagename_awpcp=get_awpcp_option('userpagename');
				$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

				$pageswithawpcpname=array();

				$query="SELECT ID FROM {$table_prefix}posts WHERE post_title='$cpagename_awpcp' AND post_name = '$awpcppagename' AND post_content LIKE '%AWPCP%'";
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
						$query="DELETE FROM {$table_prefix}posts WHERE ID = '$pagewithawpcpname' OR (post_parent='$pagewithawpcpname' AND post_content LIKE '%AWPCP%')";
						@mysql_query($query);

						//$query="DELETE FROM {$table_prefix}postmeta WHERE post_id = '$pagewithawpcpname'";
						//@mysql_query($query);

						//$query="DELETE FROM {$table_prefix}comments WHERE comment_post_ID = '$pagewithawpcpname'";
						//@mysql_query($query);
					}

						deleteuserpageentry();
						maketheclassifiedpage($cpagename_awpcp,$makesubpages='1');

					echo "<div style=\"padding:50px;font-weight:bold;\"><p>";
					_e("The page has been recreated","AWPCP");
					echo "</p><h3><a href=\"?page=awpcp.php\">";
					_e("Back to Control Panel","AWPCP");
					echo "</a></h3></div>";
					die;

		}

	}

	$table_name4 = $wpdb->prefix . "awpcp_adsettings";

			/////////////////////////////////
			// Start the page display
			/////////////////////////////////

	echo "<div class=\"wrap\">
	<h2>";
	_e("AWPCP Classifieds Management System Settings Configuration","AWPCP");
	echo"</h2>
	 $message <p style=\"padding:10px;\">";
	 _e("Below you can modify the settings for your classifieds system. With options including turning on/off images in ads, turning on/off HTML in ads, including adsense in ads (will insert 468X60 text ad above ad content and 468X60 image ad below ad content). Also provide your paypal business email and 2checout ID. The system provides only these 2 payment gateways at this time.","AWPCP");
	 echo "</p>";
	 echo "<div style=\"width:90%;margin:0 auto;display:block;padding:5px;\"><ul>";
	  echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=1\">";
	  _e("General Settings","AWPCP");
	 echo "</a></li> ";
	  echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=10\">";
	  _e("Classified Pages Setup","AWPCP");
	 echo "</a></li> ";
	 	  echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=11\">";
	 	  _e("SEO Settings","AWPCP");
	 echo "</a></li> ";
		echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=2\">";
	   _e("Ad/Listing Settings","AWPCP");
	 echo "</a></li> ";
	  echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=3\">";
	 _e(" Payment Settings","AWPCP");
	 echo "</a></li> ";
	   echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=4\">";
	  _e(" Image Settings","AWPCP");
	 echo "</a></li> ";
	   echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=5\">";
	  _e(" Adsense Settings","AWPCP");
	 echo "</a></li> ";
		echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=6\">";
	   _e(" Optional Form Field Settings","AWPCP");
	 echo "</a></li> ";
		 echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=7\">";
		_e(" Registration Settings","AWPCP");
	 echo "</a></li> ";
		  echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=8\">";
		 _e(" Email Text Settings","AWPCP");
	 echo "</a></li> ";
		  echo "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=9\">";
		 _e(" SMTP Settings","AWPCP");
	 echo "</a></li> ";
	 echo "</ul></div><div style=\"clear:both;\"></div>";
	 echo "
	<form method=\"post\" id=\"awpcp_launch\">
	<p><input class=\"button\" name=\"savesettings\" type=\"submit\" value=\"";
	_e("Save Settings","AWPCP");
	echo "\" /></p>";

			//////////////////////////////////////
			// Retrieve the currently saved data
			/////////////////////////////////////
	if(!isset($_REQUEST['cgid']) && empty($_REQUEST['cgid'])){ $cgid=10;} else { $cgid=$_REQUEST['cgid']; }

	$query="SELECT config_option,config_value,config_diz,option_type FROM ".$table_name4." WHERE config_group_id='$cgid'";
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
			$config_diz.="<br><b>**";
			_e("Your password is saved but not shown below. Leave the field blank unless you are changing your SMTP password","AWPCP");
			echo "</b>";
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
			$field="<input  size=\"30\" type=\"text\" style=\"border:1px solid#dddddd;width:75%;\" name=\"$config_option\" value=\"$config_value\" />";
		}elseif ($option_type==2) {	// textarea input
			$field="<textarea name=\"$config_option\" rows=\"5\" cols=\"75\" style=\"border:1px solid#dddddd;width:75%;\">$config_value</textarea>";
		}

			/////////////////////////////////////////
			// Display the data items
			////////////////////////////////////////

	echo "
	<p style=\"display:block;margin-bottom:25px;\">
	<div style=\"padding:5px;width:75%;\">$config_diz $field</div>
	</p>";
	}

	echo "
	<input type=\"hidden\" name=\"cgid\" value=\"$cgid\">
	<input type=\"hidden\" name=\"makesubpages\" value=\"$makesubpages\">
	<p><input class=\"button\" name=\"savesettings\" type=\"submit\" value=\"";
	_e("Save Settings","AWPCP");
	echo "\" /></p></form></div>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage general configuration options
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_fees()
{

	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		echo $awpcpsetuptext;

	} else {

	global $wpdb;
	global $message;

	$table_name2 = $wpdb->prefix . "awpcp_adfees";


			/////////////////////////////////
			// Start the page display
			/////////////////////////////////

	echo "<div class=\"wrap\">";
	echo "<h2>";
	_e("AWPCP Classifieds Management System: Listing Fees Management","AWPCP");
	echo "</h2>";
	if(isset($message) && !empty($message))
	{
		echo $message;
	}
	echo "<p style=\"padding:10px;\">";
	 _e("Below you can add and edit your listing fees. As an example you can add an entry set at $9.99 for a 30 day listing, then another entry set at $17.99 for a 60 day listing. For each entry you can set a specific number of images a user can upload. If you have allow images turned off in your main configuration settings the value you add here will not matter as an upload option will not be included in the ad post form. You can also set a text limit for the ads. The value is in words.","AWPCP");
	 echo "</p>";

			///////////////////////////////////////
			// Handle case of adding new settings

			$rec_increment_op="<option value='D' ".(($rec_increment=='D') ? ("selected") : ("")).">";
			$rec_increment_op.=__("Days","AWPCP");
			$rec_increment_op.="</option>\n";//////////////////////////////////////

	 if(isset($_REQUEST['addnewlistingfeeplan']) && !empty($_REQUEST['addnewlistingfeeplan']))
	 {

				$awpcpfeeform="<form method=\"post\" id=\"awpcp_launch\">";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Plan Name [eg; 30 day Listing]","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"30\" type=\"text\" class=\"inputbox\" name=\"adterm_name\" value=\"$adterm_name\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Price [x.xx format]","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"amount\" value=\"$amount\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Term Duration","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"rec_period\" value=\"$rec_period\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Images Allowed","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"imagesallowed\" value=\"$imagesallowed\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Term Increment","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<select name=\"rec_increment\" size=\"1\">$rec_increment_op</select></p>";
				$awpcpfeeform.="<input class=\"button\" type=\"submit\" name=\"addnewfeesetting\" value=\"";
				$awpcpfeeform.=__("Add New Plan","AWPCP");
				$awpcpfeeform.="\" />";
				$awpcpfeeform.="</form>";

		echo "<div class=\"postbox\" style=\"padding:20px; width:300px;\">$awpcpfeeform</div>";

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
		$message.=__("The new plan has been added!","AWPCP");
		$message.="</div>";
	 }

	 else
	 {

			//////////////////////////////////////
			// Retrieve the currently saved data
			/////////////////////////////////////
		echo "<ul>";

		$query="SELECT adterm_id,adterm_name,amount,rec_period,rec_increment,imagesallowed FROM ".$table_name2."";
		if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$plans=array();

		if (mysql_num_rows($res))
		{

			while ($rsrow=mysql_fetch_row($res))
			{
				list($adterm_id,$adterm_name,$amount,$rec_period,$rec_increment,$imagesallowed)=$rsrow;


					/////////////////////////////////////////
					// Display the items
					////////////////////////////////////////

				$awpcpfeeform="<form method=\"post\" id=\"awpcp_launch\">";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Plan Name [eg; 30 day Listing]","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"30\" type=\"text\" class=\"inputbox\" name=\"adterm_name\" value=\"$adterm_name\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Price [x.xx format]","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"amount\" value=\"$amount\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Term Duration","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"rec_period\" value=\"$rec_period\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Images Allowed","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<input class=\"regular-text\" size=\"5\" type=\"text\" class=\"inputbox\" name=\"imagesallowed\" value=\"$imagesallowed\" /></p>";
				$awpcpfeeform.="<p>";
				$awpcpfeeform.=__("Term Increment","AWPCP");
				$awpcpfeeform.="<br/>";
				$awpcpfeeform.="<select name=\"rec_increment\" size=\"1\">$rec_increment_op</select></p>";
				$awpcpfeeform.="<input class=\"button\" type=\"submit\" name=\"addnewfeesetting\" value=\"";
				$awpcpfeeform.=__("Update Plan","AWPCP");
				$awpcpfeeform.="\" />";
				$awpcpfeeform.="</form>";

				echo "<li class=\"postbox\" style=\"float:left;width:280px;padding:10px; margin-right:20px;\">$awpcpfeeform</li>";
			}

			echo "</ul>";
		}


			echo "<div style=\"clear:both;\"></div>
			<form method=\"post\" id=\"awpcp_opsconfig_fees\">
			<p style=\"padding:10px;\"><input class=\"button\" type=\"submit\" name=\"addnewlistingfeeplan\" value=\"";
			_e("Add a new listing fee plan","AWPCP");
			echo "\" /></p>
			</form>";
		}
			echo "</div><br/>";

	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage existing listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_categories()
{

	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		echo $awpcpsetuptext;

	} else {

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

			echo "<div class=\"wrap\"><h2>";
			_e("AWPCP Classifieds Management System Categories Management","AWPCP");
			echo "</h2>
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
					$aeword1=__("You are currently editing the category shown below","AWPCP");
					$aeword2=__("Edit Current Category","AWPCP");
					$aeword3=__("Parent Category","AWPCP");
					$addnewlink="<a href=\"?page=Configure3\">";
					$addnewlink.=__("Add A New Category","AWPCP");
					$addnewlink.="</a>";
				}
				elseif($aeaction == 'delete')
				{
					if( $cat_ID != 1)
					{

						$aeword1=__("If you're sure that you want to delete this category please press the delete button","AWPCP");
						$aeword2=__("Delete this category","AWPCP");
						$aeword3=__("Parent Category","AWPCP");
						$addnewlink="<a href=\"?page=Configure3\">";
						$addnewlink.=__("Add A New Category","AWPCP");
						$addnewlink.="</a>";

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
								$movetoname=__("Untitled","AWPCP");
							}

							$promptmovetocat="<p>";
							$promptmovetocat.=__("If you do not select a category to move them to the category contains ads. The ads will be moved to:","AWPCP");
							$promptmovetocat.="<b>$movetoname</b></p>";

							$defaultcatname=get_adcatname($catid=1);

							if( empty($defaultcatname) )
							{
								$defaultcatname=__("Untitled","AWPCP");
							}

							if(category_has_children($cat_ID))
							{
								$promptmovetocat.="<p>";
								$promptmovetocat.=__("The category also has children. If you do not specify a move-to category the children will be adopted by","AWPCP");
								$promptmovetocat.="<b>$defaultcatname</b><p><b>";
								$promptmovetocat.=__("Note","AWPCP");
								$promptmovetocat.=":</b>";
								$promptmovetocat.=__("The move-to category specified applies to both ads and categories","AWPCP");
								$promptmovetocat.="</p>";
							}
							$promptmovetocat.="<p align=\"center\"><select name=\"movetocat\"><option value=\"0\">";
							$promptmovetocat.=__("Please select a Move-To category","AWPCP");
							$promptmovetocat.="</option>";
							$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude=$cat_ID);
							$promptmovetocat.="$categories</select>";
						}

						$thecategoryparentname=get_adparentcatname($cat_parent_ID);
					}
					else
					{
						$aeword1=__("Sorry but you cannot delete ","AWPCP");
						$aeword1.="<b>$category_name</b>";
						$aeword1.=__(" It is the default category. The default category cannot be deleted","AWPCP");
						$aeword2='';
						$aeword3='';
						$addnewlink="<a href=\"?page=Configure3\">";
						$addnewlink.=__("Add A New Category","AWPCP");
						$addnewlink.="</a>";
					}
				}
				else
				{
					if( empty($aeaction) )
					{
						$aeaction="newcategory";
					}

					$aeword1=__("Enter the category name","AWPCP");
					$aeword2=__("Add New Category","AWPCP");
					$aeword3=__("List Category Under","AWPCP");
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
						$categorynameinput="<p style=\"background:transparent url($imagesurl/delete_ico.png) left center no-repeat;padding-left:20px;\">";
						$categorynameinput.=__("Category to Delete","AWPCP");
						$categorynameinput.=": $category_name</p>";
						$selectinput="<p style=\"background:#D54E21;padding:3px;color:#ffffff;\">$thecategoryparentname</p>";
						$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
					}
				}
				elseif($aeaction == 'edit')
				{
						$categorynameinput="<p style=\"background:transparent url($imagesurl/edit_ico.png) left center no-repeat;padding-left:20px;\">";
						$categorynameinput.=__("Category to Edit","AWPCP");
						$categorynameinput.=": $category_name</p><p><input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/></p>";
						$selectinput="<p><select name=\"category_parent_id\"><option value=\"0\">";
						$selectinput.=__("Save as Top Level Category","AWPCP");
						$selectinput.="</option>";
						$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude='');
						$selectinput.="$categories
						</select></p>";
						$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
				}
				else {
					$categorynameinput="<p style=\"background:transparent url($imagesurl/post_ico.png) left center no-repeat;padding-left:20px;\">";
					$categorynameinput.=__("Add a New Category","AWPCP");
					$categorynameinput.="</p><input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/>";
					$selectinput="<p><select name=\"category_parent_id\"><option value=\"0\">";
					$selectinput.=__("Save as Top Level Category","AWPCP");
					$selectinput.="</option>";
					$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude='');
					$selectinput.="$categories
					</select></p>";
					$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
				}

					/////////////////////////////////
					// Start the page display
					/////////////////////////////////

			echo "<div class=\"wrap\"><h2>";
			_e("AWPCP Classifieds Management System Categories Management","AWPCP");
			echo "</h2>";
			if(isset($message) && !empty($message))
			{
				echo $message;
			}
			echo "<div style=\"padding:10px;\"><p>";
			_e("Below you can add and edit your categories. For more information about managing your categories visit the link below.","AWPCP");
			echo "</p><p><a href=\"http://www.awpcp.com/about/categories/\">";
			_e("Useful Information for Classifieds Categories Management","AWPCP");
			echo "</a></p><b>";
			_e("Icon Meanings","AWPCP");
			echo ":</b> &nbsp;&nbsp;&nbsp;<img src=\"$imagesurl/edit_ico.png\" alt=\"";
			_e("Edit Category","AWPCP");
			echo "\" border=\"0\">";
			_e("Edit Category","AWPCP");
			echo " &nbsp;&nbsp;&nbsp;<img src=\"$imagesurl/delete_ico.png\" alt=\"";
			_e("Delete Category","AWPCP");
			echo "\" border=\"0\">";
			_e("Delete Category","AWPCP");


			 if($hascaticonsmodule == 1 )
			 {
				 if( is_installed_category_icon_module() )
				 {
					echo " &nbsp;&nbsp;&nbsp;<img src=\"$imagesurl/icon_manage_ico.png\" alt=\"";
					_e("Manage Category Icon","AWPCP");
					echo "\" border=\"0\">";
					_e("Manage Category icon","AWPCP");
				 }
			}


					if($hascaticonsmodule != 1 )
					{
						echo "<div class=\"fixfloat\"><p style=\"padding-top:25px;\">";
						_e("There is a premium module available that allows you to add icons to your categories. If you are interested in adding icons to your categories ","AWPCP");
						echo "<a href=\"http://www.awpcp.com/premium-modules/\">";
						_e("Click here to find out about purchasing the Category Icons Module","AWPCP");
						echo "</a></p></div>";
					}

			 echo "
			 </div>
			 <div class=\"postbox\" style=\"width:30%;float:left;padding:10px;$backfont\">
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
			 <p><input type=\"submit\" name=\"deletemultiplecategories\" class=\"button\" value=\"";
			 _e("Delete Selected Categories","AWPCP");
			 echo "\">
			 <input type=\"submit\" name=\"movemultiplecategories\" class=\"button\" value=\"";
			 _e("Move Selected Categories","AWPCP");
			 echo "\">
			 <select name=\"moveadstocategory\"><option value=\"0\">";
			 _e("Select Move-To category","AWPCP");
			 echo "</option>";
						$movetocategories=  get_categorynameid($cat_id = 0,$cat_parent_id= 0,$exclude);
						echo "$movetocategories</select></p>
			<p>";
			_e("If deleting categories","AWPCP");
			echo "<input type=\"radio\" name=\"movedeleteads\" value=\"1\" checked>";
			_e("Move Ads if any","AWPCP");
			echo "<input type=\"radio\" name=\"movedeleteads\" value=\"2\">";
			_e("Delete Ads if any","AWPCP");
			echo "</p>";

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
							$managecaticon="<a href=\"?page=Configure3&cat_ID=$thecategory_id&action=managecaticon&offset=$offset&results=$results\"><img src=\"$imagesurl/icon_manage_ico.png\" alt=\"";
							$managecaticon.=__("Manage Category Icon","AWPCP");
							$managecaticon.="\" border=\"0\"></a>";
						}
					}
					$awpcpeditcategoryword=__("Edit Category","AWPCP");
					$awpcpdeletecategoryword=__("Delete Category","AWPCP");

					$items[]="<tr><td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\"><input type=\"checkbox\" name=\"category_to_delete_or_move[]\" value=\"$thecategory_id\">$thecategory_name ($totaladsincat)</td>
					<td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\">$thecategory_parent_name</td>
					<td style=\"padding:5px;border-bottom:1px dotted #dddddd;font-size:smaller;font-weight:normal;\"> <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=editcat&offset=$offset&results=$results\"><img src=\"$imagesurl/edit_ico.png\" alt=\"$awpcpeditcategoryword\" border=\"0\"></a> <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=delcat&offset=$offset&results=$results\"><img src=\"$imagesurl/delete_ico.png\" alt=\"$awpcpdeletecategoryword\" border=\"0\"></a> $managecaticon</td></tr>";
				}

					$opentable="<table class=\"listcatsh\"><tr><td style=\"width:40%;padding:5px;\"><input type=\"checkbox\" onclick=\"CheckAll()\">";
					$opentable.=__("Category Name (Total Ads)","AWPCP");
					$opentable.="</td><td style=\"width:40%;padding:5px;\">";
					$opentable.=__("Parent","AWPCP");
					$opentable.="</td><td style=\"width:20%;padding:5px;;\">";
					$opentable.=__("Action","AWPCP");
					$opentable.="</td></tr>";
					$closetable="<tr><td style=\"width:40%;padding:5px;\">";
					$closetable.=__("Category Name (Total Ads)","AWPCP");
					$closetable.="</td><td style=\"width:40%;padding:5px;\">";
					$closetable.=__("Parent","AWPCP");
					$closetable.="</td><td style=\"width:20%;padding:5px;\">";
					$closetable.=__("Action","AWPCP");
					$closetable.="</td></tr></table>";

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

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage view images
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_manage_viewimages()
{

	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		echo $awpcpsetuptext;

	} else {

		global $message,$wpdb;
		$table_name5 = $wpdb->prefix . "awpcp_adphotos";
		$where='';

		echo "<div class=\"wrap\"><h2>";
		_e("AWPCP Classifieds Management System Manage Images","AWPCP");
		echo "</h2>";
		if(isset($message) && !empty($message))
		{
			echo $message;
		}
		echo "<p style=\"padding:10px;border:1px solid#dddddd;\">";
		_e("Below you can manage the images users have uploaded. Your options are to delete images, and in the event you are operating with image approval turned on you can approve or disable images","AWPCP");
		echo "</p>";


		if(isset($_REQUEST['action']) && !empty($_REQUEST['action']))
		{
			$laction=$_REQUEST['action'];
		}

		if(empty($_REQUEST['action']))
		{
			if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
			{
				$laction=$_REQUEST['a'];
			}
		}

		if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$actonid=$_REQUEST['id'];
			$where="ad_id='$actonid'";
		}

		if($laction == 'approvepic')
		{
			if(isset($_REQUEST['kid']) && !empty($_REQUEST['kid']))
			{
				$keyids=$_REQUEST['kid'];
				list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
			}

			$query="UPDATE  ".$table_name5." SET disabled='0' WHERE ad_id='$adid' AND key_id='$picid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			_e("The image has been enabled and can now be viewed","AWPCP");
			echo "</div>";

		}
		elseif($laction == 'rejectpic')
		{
			if(isset($_REQUEST['kid']) && !empty($_REQUEST['kid']))
			{
				$keyids=$_REQUEST['kid'];
				list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
			}

			$query="UPDATE  ".$table_name5." SET disabled='1' WHERE ad_id='$adid' AND key_id='$picid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			_e("The image has been disabled and can no longer be viewed","AWPCP");
			echo "</div>";


		}
		elseif($laction == 'deletepic')
		{
			if(isset($_REQUEST['kid']) && !empty($_REQUEST['kid']))
			{
				$keyids=$_REQUEST['kid'];
				list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
			}

			$message=deletepic($picid,$adid,$adtermid,$adkey,$editemail);
			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";

		}

		viewimages($where);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage view images
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage view listings
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_manage_viewlistings()
{

	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		echo $awpcpsetuptext;

	} else {

		global $wpdb,$imagesurl,$message;

		echo "<div class=\"wrap\"><h2>";
		_e("AWPCP Classifieds Management System Manage Ad Listings","AWPCP");
		echo "</h2>";
		if(isset($message) && !empty($message))
		{
			echo $message;
		}

		$table_name3 = $wpdb->prefix . "awpcp_ads";
		$table_name5 = $wpdb->prefix . "awpcp_adphotos";

		if(isset($_REQUEST['action']) && !empty($_REQUEST['action']))
		{
			$laction=$_REQUEST['action'];
		}

		if(empty($_REQUEST['action']))
		{
			if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
			{
				$laction=$_REQUEST['a'];
			}
		}

		if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$actonid=$_REQUEST['id'];
		}


		if($laction == 'deletead')
		{
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


			if(get_awpcp_option('allowhtmlinadtext') == 0)
			{
				$addetails=strip_html_tags($addetails);
			}
			$adpaymethod=addslashes_mq($_REQUEST['adpaymethod']);
			if(!isset($adpaymethod) || empty($adpaymethod))
			{
				$adpaymethod="paypal";
			}
			if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
			$adaction=addslashes_mq($_REQUEST['adaction']);} else {$adaction='';}
			$awpcppagename=addslashes_mq($_REQUEST['awpcppagename']);
			$offset=addslashes_mq($_REQUEST['offset']);
			$results=addslashes_mq($_REQUEST['results']);

			processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);

		}
		elseif($laction == 'approvead')
		{
			$query="UPDATE  ".$table_name3." SET disabled='0' WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			_e("The ad has been approved","AWPCP");
			echo "</div>";
		}
		elseif($laction == 'rejectad')
		{
			$query="UPDATE  ".$table_name3." SET disabled='1' WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			_e("The ad has been disabled","AWPCP");
			echo "</div>";
		}
		elseif($laction == 'cps')
		{
			if(isset($_REQUEST['changeto']) && !empty($_REQUEST['changeto']))
			{
				$changeto=$_REQUEST['changeto'];
			}

			$query="UPDATE  ".$table_name3." SET payment_status='$changeto', disabled='0' WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			_e("The ad payment status has been changed","AWPCP");
			echo "</div>";

		}
		elseif($laction == 'viewad')
		{
			if(isset($actonid) && !empty($actonid))
			{

				echo "<div class=\"postbox\" style=\"padding:20px;width:95%;\">";

					// start insert delete | edit | approve/disable admin links

					$offset=(isset($_REQUEST['offset'])) ? (addslashes_mq($_REQUEST['offset'])) : ($offset=0);
					$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? addslashes_mq($_REQUEST['results']) : ($results=10);

						$deletelink=  "<a href=\"?page=Manage1&action=deletead&id=$actonid&offset=$offset&results=$results\">";
						$deletelink.=__("Delete","AWPCP");
						$deletelink.="</a>";
						$editlink=" |  <a href=\"?page=Manage1&action=editad&id=$actonid&offset=$offset&results=$results\">";
						$editlink.=__("Edit","AWPCP");
						$editlink.="</a>";


						echo "<div style=\"padding:10px 0px;; margin-bottom:20px;\"><b>";
						_e("Manage Listing: ","AWPCP");
						echo "</b>";
						echo "$deletelink $editlink";

					if(get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1)
					{
						$adstatusdisabled=check_if_ad_is_disabled($actonid);

						if($adstatusdisabled)
						{
							$approvelink=" | <a href=\"?page=Manage1&action=approvead&id=$actonid&offset=$offset&results=$results\">";
							$approvelink.=__("Approve","AWPCP");
							$approvelink.="</a> ";
						}
						else
						{
							$approvelink=" | <a href=\"?page=Manage1&action=rejectad&id=$actonid&offset=$offset&results=$results\">";
							$approvelink.=__("Disable","AWPCP");
							$approvelink.="</a> ";
						}

						echo "$approvelink";
					}

						echo "</div>";

					// end insert delete | edit | approve/disable admin links

					showad($actonid,$omitmenu='1');

				echo "</div>";
			}
			else
			{
				echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
				_e("No ad ID was supplied","AWPCP");
				echo "</div>";

			}

		}
		elseif($laction == 'viewimages')
		{
			if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
			{
				$picid=$_REQUEST['id'];
				$where="ad_id='$picid'";
			}
			else
			{
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
				echo "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
				_e("You need to check whether you want to look up the ad by title id or keyword","AWPCP");
				echo "</div>";
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
			if(!isset($where) || empty($where))
			{
				$where="ad_title <> ''";
			}

				if(!ads_exist())
				{
					$showadstomanage="<p style=\"padding:10px\">";
					$showadstomanage.=__("There are currently no ads in the system","AWPCP");
					$showadstomanage.="</p>";
					$pager1='';
					$pager2='';
				}
				else
				{
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
						elseif($sortby == 'mostrecent')
						{
							$orderby="ad_key DESC";
						}
					}

					if(!isset($sortby) || empty($sortby))
					{
						$orderby="ad_key DESC";
					}

						$items=array();
						$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_details,ad_postdate,disabled,payment_status FROM $from WHERE $where ORDER BY $orderby LIMIT $offset,$results";
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
							$disabled=$rsrow[11];
							$paymentstatus=$rsrow[12];

							if(!isset($paymentstatus) || empty($paymentstatus))
							{
								$paymentstatus="N/A";
							}

								$pager1="<p>".create_pager($from,$where,$offset,$results,$tpname='')."</p>";
								$pager2="<p>".create_pager($from,$where,$offset,$results,$tpname='')."</p>";
								$base=get_option(siteurl);
								$awpcppage=get_currentpagename();
								$awpcppagename = sanitize_title($awpcppage, $post_ID='');
								$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

									$ad_title="<input type=\"checkbox\" name=\"awpcp_ad_to_delete[]\" value=\"$ad_id\"><a href=\"?page=Manage1&action=viewad&id=$ad_id&offset=$offset&results=$results\">".$rsrow[2]."</a>";
									$handlelink="<a href=\"?page=Manage1&action=deletead&id=$ad_id&offset=$offset&results=$results\">";
									$handlelink.=__("Delete","AWPCP");
									$handlelink.="</a> | <a href=\"?page=Manage1&action=editad&id=$ad_id&offset=$offset&results=$results\">";
									$handlelink.=__("Edit","AWPCP");
									$handlelink.="</a>";

									$approvelink='';

									if(get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1)
									{
										if($disabled == 1)
										{
											$approvelink="<a href=\"?page=Manage1&action=approvead&id=$ad_id&offset=$offset&results=$results\">";
											$approvelink.=__("Approve","AWPCP");
											$approvelink.="</a> | ";
										}
										else
										{
											$approvelink="<a href=\"?page=Manage1&action=rejectad&id=$ad_id&offset=$offset&results=$results\">";
											$approvelink.=__("Disable","AWPCP");
											$approvelink.="</a> | ";
										}
									}


								if(get_awpcp_option('freepay') == 1)
								{
									$paymentstatushead="<th>";
									$paymentstatushead.=__("Payment Status","AWPCP");
									$paymentstatushead.="</th>";

									$changepaystatlink='';

									if($paymentstatus == 'Pending')
									{
										$changepaystatlink="<a href=\"?page=Manage1&action=cps&id=$ad_id&changeto=Completed&sortby=$sortby\">";
										$changepaystatlink.=__("Complete","AWPCP");
										$changepaystatlink.="</a>";
									}

										$paymentstatus="<td> $paymentstatus <SUP>$changepaystatlink</SUP></td>";
								}
								else
								{
									$paymentstatushead="";
									$paymentstatus="";
								}

								if(get_awpcp_option(imagesallowdisallow) == 1)
								{

									$imagesnotehead="<th>";
									$imagesnotehead.=__("Total Images","AWPCP");
									$imagesnotehead.="</th>";

									$totalimagesuploaded=get_total_imagesuploaded($ad_id);

										if($totalimagesuploaded >= 1)
										{
											$viewimages="[ $totalimagesuploaded ] <a href=\"?page=Manage1&action=viewimages&id=$ad_id&sortby=$sortby\">";
											$viewimages.=__("View","AWPCP");
											$viewimages.="</a>";
										}
										else
										{
											$viewimages=__("No Images","AWPCP");
										}

									$imagesnote="<td> $viewimages</td>";
								}
								else {$imagesnotehead="";$imagesnote="";}

								$items[]="<tr><td class=\"displayadscell\" width=\"200\">$ad_title</td><td> $approvelink $handlelink</td>$paymentstatus $imagesnote</tr>";


								$opentable="<table class=\"widefat fixed\"><thead><tr><th><input type=\"checkbox\" onclick=\"CheckAllAds()\">";
								$opentable.=__("Ad Headline","AWPCP");
								$opentable.="</th><th>";
								$opentable.=__("Manage Ad","AWPCP");
								$opentable.="</th>$paymentstatushead $imagesnotehead</tr></thead>";
								$closetable="</table>";


								$theadlistitems=smart_table($items,intval($results/$results),$opentable,$closetable);
								$showadstomanage="$theadlistitems";
								$showadstomanagedeletemultiplesubmitbutton="<input type=\"submit\" name=\"deletemultipleads\" class=\"button\" value=\"";
								$showadstomanagedeletemultiplesubmitbutton.=__("Delete Checked Ads","AWPCP");
								$showadstomanagedeletemultiplesubmitbutton.="\"></p>";

						}
						if(!isset($ad_id) || empty($ad_id) || $ad_id == '0' )
						{
								$showadstomanage="<p style=\"padding:20px;\">";
								$showadstomanage.=__("There were no ads found","AWPCP");
								$showadstomanage.="</p>";
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
			#listingsops { padding:10px; }
			#adssort { padding:10px; height:150px;}
			#listingsops .deletechekedbuttom { width:30%; float:left;margin:5px 0px 5px 0px;}
			#listingsops .sortadsby { width:60%; float:left;margin:5px 0px 5px 0px;}
			#listingsops .sortadsby a { 	text-decoration:none; }
			#listingsops .sortadsby a:hover { text-decoration:underline;	}
			#lookupadsby { padding:10px; }
			#lookupadsby .lookupadsbytitle { float:left; margin:4px 20px 0px 0px; }
			#lookupadsby .lookupadsbyform { float:left; margin:0;  }
			</style>
			";
			echo "
			<div id=\"lookupadsby\"><div class=\"lookupadsbytitle\">
			<b>";
			_e("Look Up Ad By","AWPCP");
			echo "</b></div>
			<div class=\"lookupadsbyform\">
			<form method=\"post\">
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"adid\"/>Ad ID
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"adtitle\"/>Ad Title
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"titdet\"/>Key Word
			<input type=\"text\" name=\"lookupadidortitle\" value=\"$lookupadidortitle\"/>
			<input type=\"hidden\" name=\"action\" value=\"lookupadby\">
			<input type=\"submit\" class=\"button\" value=\"Look Up Ad\">
			</form>
			</div>
			</div>
			<div style=\"clear:both;\"></div>

			$pager1
			<form name=\"manageads\" id=\"manageads\" method=\"post\">
			<div id=\"listingsops\">
			<div class=\"deletechekedbuttom\">$showadstomanagedeletemultiplesubmitbutton</div>
			<div class=\"sortadsby\">";
			_e("Sort Ads By","AWPCP");
			echo ": ";

			if($sortby == 'mostrecent')
			{
				echo "<b>| ";
				_e("Most Recent","AWPCP");
				echo " |</b>";
			}
			else
			{
				echo "<a href=\"?page=Manage1&sortby=mostrecent\">";
				_e("Most Recent","AWPCP");
				echo "</a>";
			}
			echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			if($sortby == 'titleza')
			{
				echo "<b>| ";
				_e("Title Z-A","AWPCP");
				echo " |</b>";
			}
			else
			{
				echo "<a href=\"?page=Manage1&sortby=titleza\">";
				_e("Title Z-A","AWPCP");
				echo "</a>";
			}
			echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			if($sortby == 'titleaz')
			{
				echo "<b>| ";
				_e("Title A-Z","AWPCP");
				echo " |</b>";
			}
			else
			{
				echo "<a href=\"?page=Manage1&sortby=titleaz\">";
				_e("Title A-Z","AWPCP");
				echo "</a>";
			}
			echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			if(get_awpcp_option('adapprove') == 1)
			{
				if($sortby == 'awaitingapproval')
				{
					echo "<b>| ";
					_e("Awaiting Approval","AWPCP");
					echo " |</b>";
				}
				else
				{
					echo "<a href=\"?page=Manage1&sortby=awaitingapproval\">";
					_e("Awaiting Approval","AWPCP");
					echo "</a>";
				}
			}
			echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			if(get_awpcp_option('freepay') == 1)
			{
				if($sortby == 'paidfirst')
				{
					echo "<b>| ";
					_e("Paid Ads First","AWPCP");
					echo " |</b>";
				}
				else
				{
					echo "<a href=\"?page=Manage1&sortby=paidfirst\">";
					_e("Paid Ads First","AWPCP");
					echo "</a>";
				}

			}
			echo "
			</div>
			</div>

			 $showadstomanage
		<div id=\"listingsops\">$showadstomanagedeletemultiplesubmitbutton</div>
			</form>
			$pager2";


		echo "</div>";
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION: Manage view listings
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display images for admin view
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function viewimages($where)
{
	global $wpdb;
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	$from="$table_name5";

		if(!isset($where) || empty($where))
		{
			$where="image_name <> ''";
		}
			if(!images_exist())
			{
				$imagesallowedstatus='';

				if(get_awpcp_option('imagesallowdisallow') == 0)
				{
					$imagesallowedstatus=__("You are not currently allowing users to upload images with their ad. To allow users to upload images please change the related setting in your general options configuration","AWPCP");
					$imagesallowedstatus.="<p><a href=\"?page=Configure1\">";
					$imagesallowedstatus.=__("Click here to change allowed images status","AWPCP");
					$imagesallowedstatus.="</a></p>";
				}

				$showimages="<p style=\"padding:10px\">";
				$showimages.=__("There are currently no images in the system","AWPCP");
				$showimages="$imagesallowedstatus</p>";
				$pager1='';
				$pager2='';
			}
			else
			{
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



						$dellink="<a href=\"?page=Manage2&action=deletepic&kid=$ikey&id=$adid&offset=$offset&results=$results\">";
						$dellink.=__("Delete","AWPCP");
						$dellink.="</a>";

						$transval='';
						if($disabled == 1){
							$transval="style=\"-moz-opacity:.20; filter:alpha(opacity=20); opacity:.20;\"";
						}

						$approvelink='';
						if(get_awpcp_option('imagesapprove') == 1){

							if($disabled == 1){
								$approvelink=" | <a href=\"?page=Manage2&action=approvepic&kid=$ikey&id=$adid&offset=$offset&results=$results\">";
								$approvelink.=__("Approve","AWPCP");
								$approvelink.="</a>";
							}
							else {
								$approvelink=" | <a href=\"?page=Manage2&action=rejectpic&kid=$ikey&id=$adid&offset=$offset&results=$results\">";
								$approvelink.=__("Disable","AWPCP");
								$approvelink.="</a>";
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
					if(!isset($ikey) || empty($ikey) || $ikey == '0')
					{
							$showcategories="<p style=\"padding:20px;\">";
							$showcategories.=__("There were no images found","AWPCP");
							$showcategories.="</p>";
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

if(isset($_REQUEST['savesettings']) && !empty($_REQUEST['savesettings']))
{

	global $wpdb;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";
	$currentuipagename=get_currentpagename();

	$awpcppagename = sanitize_title($currentuipagename, $post_ID='');
	$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

	$currentshowadspagename=get_awpcp_option('showadspagename');
	$currentplaceadpagename=get_awpcp_option('placeadpagename');
	$currentbrowseadspagename=get_awpcp_option('browseadspagename');
	$currentbrowsecatspagename=get_awpcp_option('browsecatspagename');
	$currentpaymentthankyoupagename=get_awpcp_option('paymentthankyoupagename');
	$currentpaymentcancelpagename=get_awpcp_option('paymentcancelpagename');
	$currentreplytoadpagename=get_awpcp_option('replytoadpagename');
	$currenteditadpagename=get_awpcp_option('editadpagename');
	$currentcategoriesviewpagename=get_awpcp_option('categoriesviewpagename');
	$currentsearchadspagename=get_awpcp_option('searchadspagename');
	$error=false;

	if(!isset($_REQUEST['cgid']) && empty($_REQUEST['cgid'])){$cgid=10;} else{ $cgid=$_REQUEST['cgid'];}
	if(!isset($_REQUEST['makesubpages']) && empty($_REQUEST['makesubpages'])){$makesubpages='';} else{ $makesubpages=$_REQUEST['makesubpages'];}


	$query="SELECT config_option,option_type FROM ".$table_name4." WHERE config_group_id='$cgid'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	$myoptions=array();

	for ($i=0;$i<mysql_num_rows($res);$i++)
	{
		list($config_option,$option_type)=mysql_fetch_row($res);

		if (isset($_POST[$config_option]))
		{

			$myoptions[$config_option]=addslashes_mq($_POST[$config_option],true);

			$newuipagename='';
			$showadspagename='';
			$placeadpagename='';
			$browseadspagename='';
			$browsecatspagename='';
			$searchadspagename='';
			$paymentthankyoupagename='';
			$paymentcancelpagename='';
			$editadpagename='';
			$replytoadpagename='';

			if($cgid == 10)
			{
				$newuipagename=$myoptions['userpagename'];

				if( !empty($myoptions['showadspagename']) )
				{
					$showadspagename=$myoptions['showadspagename'];
				}
				if( !empty($myoptions['placeadpagename']) )
				{
					$placeadpagename=$myoptions['placeadpagename'];
				}
				if( !empty($myoptions['browseadspagename']) )
				{
					$browseadspagename=$myoptions['browseadspagename'];
				}
				if( !empty($myoptions['searchadspagename']) )
				{
					$searchadspagename=$myoptions['searchadspagename'];
				}
				if( !empty($myoptions['paymentthankyoupagename']) )
				{
					$paymentthankyoupagename=$myoptions['paymentthankyoupagename'];
				}
				if( !empty($myoptions['paymentcancelpagename']) )
				{
					$paymentcancelpagename=$myoptions['paymentcancelpagename'];
				}
				if( !empty($myoptions['editadpagename']) )
				{
					$editadpagename=$myoptions['editadpagename'];
				}
				if( !empty($myoptions['replytoadpagename']) )
				{
					$replytoadpagename=$myoptions['replytoadpagename'];
				}
				if( !empty($myoptions['browsecatspagename']) )
				{
					$browsecatspagename=$myoptions['browsecatspagename'];
				}
			}

			if( !empty($myoptions['smtppassword']) )
			{
				$myoptions['smtppassword']=md5($myoptions['smtppassword']);
			}
			else
			{
				$myoptions['smtppassword']=get_awpcp_option('smtppassword');
			}
		}
		else
		{
			if ($option_type==0)
			{
				$myoptions[$config_option]=0;
			} elseif ($option_type==1) {
				$myoptions[$config_option]='';
			}elseif ($option_type==2) {
				$myoptions[$config_option]='';
			}
		}
	}

	while (list($k,$v)=each($myoptions))
	{

		if (($cgid == 3))
		{
			$mycurrencycode=$myoptions['paypalcurrencycode'];
			$currencycodeslist=array('AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK','DKK','PLN','NOK','HUF','CZK','ILS','MXN');


			if(!in_array($mycurrencycode,$currencycodeslist))
			{

				$error=true;
				$message="<div style=\"background-color:#eeeeee;border:1px solid #ff0000;padding:5px;\" id=\"message\" class=\"updated fade\">";
				$message.= __("There is a problem with the currency code you have entered. It does not match any of the codes in the list of available currencies provided by PayPal.","AWPCP");
				$message.="<p>";
				$message.=__("The available currency codes are","AWPCP");
				$message.=":<br/>";

					for ($i=0;isset($currencycodeslist[$i]);++$i) {
					$message.="	$currencycodeslist[$i] | ";
					}

				$message.="</p></div>";

			}
		}


		if(!$error)
		{

			$query="UPDATE ".$table_name4." SET config_value='$v' WHERE config_option='$k'";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		}
	}
			if (($cgid == 10))
			{
				// Create the classified user page if it does not exist
				if(empty($currentuipagename))
				{
					maketheclassifiedpage($newuipagename,$makesubpages=1);
				}
				elseif(isset($currentuipagename) && !empty($currentuipagename))
				{

					if(findpage($currentuipagename,$shortcode='[AWPCPCLASSIFIEDSUI]'))
					{
						if($currentuipagename != '$newuipagename')
						{
							deleteuserpageentry($currentuipagename);
							updatetheclassifiedpagename($currentuipagename,$newuipagename);
						}

					}

					elseif(!(findpage($currentuipagename,$shortcode='[AWPCPCLASSIFIEDSUI]')))
					{
						deleteuserpageentry($currentuipagename);
						maketheclassifiedpage($newuipagename,$makesubpages=1);
					}

				}



			}

			$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			$message.=__("The data has been updated","AWPCP");
			$message.="!</div>";

	global $message;
}


		///////////////////////////////////////////////////////////////////////
		//	Start process of creating | updating  userside classified page
		//////////////////////////////////////////////////////////////////////

function maketheclassifiedpage($newuipagename,$makesubpages)
{

	add_action('init', 'flush_rewrite_rules');
	global $wpdb,$table_prefix,$wp_rewrite;
	$table_name6 = $wpdb->prefix . "awpcp_pagename";
	$pdate = date("Y-m-d");

	// First delete any pages already existing with the title and post name of the new page to be created
	checkfortotalpageswithawpcpname($newuipagename);


		$post_name = sanitize_title($newuipagename, $post_ID='');

		$query="INSERT INTO {$table_prefix}posts SET post_author='1', post_date='$pdate', post_date_gmt='$pdate', post_content='[AWPCPCLASSIFIEDSUI]', post_title='$newuipagename', post_excerpt='', post_status='publish', comment_status='closed', post_name='$post_name', to_ping='', pinged='', post_modified='$pdate', post_modified_gmt='$pdate', post_content_filtered='[AWPCPCLASSIFIEDSUI]', post_parent='0', guid='', post_type='page', menu_order='0'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		$awpcpwppostpageid=mysql_insert_id();
		$guid = get_option('home') . "/?page_id=$awpcpwppostpageid";

		$query="UPDATE {$table_prefix}posts set guid='$guid' WHERE post_title='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

		$query="INSERT INTO ".$table_name6." SET userpagename='$newuipagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	if($makesubpages)
	{
		$showadspagename=get_awpcp_option('showadspagename');
		$placeadpagename=get_awpcp_option('placeadpagename');
		$browseadspagename=get_awpcp_option('browseadspagename');
		$browsecatspagename=get_awpcp_option('browsecatspagename');
		$searchadspagename=get_awpcp_option('searchadspagename');
		$paymentthankyoupagename=get_awpcp_option('paymentthankyoupagename');
		$paymentcancelpagename=get_awpcp_option('paymentcancelpagename');
		$editadpagename=get_awpcp_option('editadpagename');
		$replytoadpagename=get_awpcp_option('replytoadpagename');

		maketheclassifiedsubpage($showadspagename,$awpcpwppostpageid,$shortcode='[AWPCPSHOWAD]');
		maketheclassifiedsubpage($placeadpagename,$awpcpwppostpageid,$shortcode='[AWPCPPLACEAD]');
		maketheclassifiedsubpage($browseadspagename,$awpcpwppostpageid,$shortcode='[AWPCPBROWSEADS]');
		maketheclassifiedsubpage($searchadspagename,$awpcpwppostpageid,$shortcode='[AWPCPSEARCHADS]');
		maketheclassifiedsubpage($paymentthankyoupagename,$awpcpwppostpageid,$shortcode='[AWPCPPAYMENTTHANKYOU]');
		maketheclassifiedsubpage($paymentcancelpagename,$awpcpwppostpageid,$shortcode='[AWPCPCANCELPAYMENT]');
		maketheclassifiedsubpage($editadpagename,$awpcpwppostpageid,$shortcode='[AWPCPEDITAD]');
		maketheclassifiedsubpage($replytoadpagename,$awpcpwppostpageid,$shortcode='[AWPCPREPLYTOAD]');
		maketheclassifiedsubpage($browsecatspagename,$awpcpwppostpageid,$shortcode='[AWPCPBROWSECATS]');
	}

}

function maketheclassifiedsubpage($theawpcppagename,$awpcpwppostpageid,$awpcpshortcodex)
{
	add_action('init', 'flush_rewrite_rules');
	global $wpdb,$table_prefix,$wp_rewrite;

	$pdate = date("Y-m-d");

	// First delete any pages already existing with the title and post name of the new page to be created
	//checkfortotalpageswithawpcpname($theawpcppagename);

		$post_name = sanitize_title($theawpcppagename, $post_ID='');

		$query="INSERT INTO {$table_prefix}posts SET post_author='1', post_date='$pdate', post_date_gmt='$pdate', post_content='$awpcpshortcodex', post_title='$theawpcppagename', post_excerpt='', post_status='publish', comment_status='closed', post_name='$post_name', to_ping='', pinged='', post_modified='$pdate', post_modified_gmt='$pdate', post_content_filtered='$awpcpshortcodex', post_parent='$awpcpwppostpageid', guid='', post_type='page', menu_order='0'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		$newawpcpwppostpageid=mysql_insert_id();
		$guid = get_option('home') . "/?page_id=$newawpcpwppostpageid";

		$query="UPDATE {$table_prefix}posts set guid='$guid' WHERE post_title='$theawpcppagename'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
}

function updatetheclassifiedsubpage($currentsubpagename,$subpagename,$shortcode)
{
	global $wpdb,$table_prefix;

	$post_name = sanitize_title($subpagename, $post_ID='');

		$query="UPDATE {$table_prefix}posts set post_title='$subpagename', post_name='$post_name' WHERE post_title='$currentsubpagename' AND post_content LIKE '%$shortcode%'";
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
//	Start process of updating|deleting|adding new listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//////////////////////////////////////////////////
	// Handle adding a listing fee plan
	/////////////////////////////////////////////////

if(isset($_REQUEST['addnewfeesetting']) && !empty($_REQUEST['addnewfeesetting']))
{

	global $wpdb;
	$table_name2 = $wpdb->prefix . "awpcp_adfees";

	$adterm_name=addslashes_mq($_REQUEST['adterm_name']);
	$amount=addslashes_mq($_REQUEST['amount']);

	$rec_period=addslashes_mq($_REQUEST['rec_period']);
	$rec_increment=addslashes_mq($_REQUEST['rec_increment']);
	$imagesallowed=addslashes_mq($_REQUEST['imagesallowed']);
	$query="INSERT INTO ".$table_name2." SET adterm_name='$adterm_name',amount='$amount',recurring=1,rec_period='$rec_period',rec_increment='$rec_increment',imagesallowed='$imagesallowed'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
	$message.=__("The item has been added","AWPCP");
	$message.="!</div>";
	global $message;
}

	//////////////////////////////////////////////////
	// Handle updating of a listing fee plan
	/////////////////////////////////////////////////

if(isset($_REQUEST['savefeesetting']) && !empty($_REQUEST['savefeesetting']))
{

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
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
	$message.=__("The item has been updated","AWPCP");
	$message.="!</div>";
	global $message;
}

	//////////////////////////////////////////////////
	// Handle deleting of a listing fee plan
	/////////////////////////////////////////////////

if(isset($_REQUEST['deletefeesetting']) && !empty($_REQUEST['deletefeesetting']))
{

	global $wpdb;
	$table_name2 = $wpdb->prefix . "awpcp_adfees";

		$adterm_id=addslashes_mq($_REQUEST['adterm_id']);
		$query="DELETE FROM  ".$table_name2." WHERE adterm_id='$adterm_id'";
		if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
	$message.=__("The data has been deleted","AWPCP");
	$message.="!</div>";
	global $message;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start process of adding | editing ad categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_REQUEST['createeditadcategory']) && !empty($_REQUEST['createeditadcategory']))
{

		global $wpdb;
		$table_name1 = $wpdb->prefix . "awpcp_categories";
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$category_id=addslashes_mq($_REQUEST['category_id']);


		if(isset($_REQUEST['$movetocat']) && !empty($_REQUEST['$movetocat']))
		{
			$movetocat=addslashes_mq($_REQUEST['movetocat']);
		}
		if(isset($_REQUEST['$deletetheads']) && !empty($_REQUEST['$deletetheads']))
		{
				$deletetheads=$_REQUEST['deletetheads'];
		}

		$aeaction=addslashes_mq($_REQUEST['aeaction']);


			if($aeaction == 'newcategory')
			{
					$category_name=addslashes_mq($_REQUEST['category_name']);
					$category_parent_id=addslashes_mq($_REQUEST['category_parent_id']);
					$query="INSERT INTO ".$table_name1." SET category_name='".$category_name."',category_parent_id='".$category_parent_id."'";
					@mysql_query($query);
					$themessagetoprint=__("The new category has sucessfully added","AWPCP");

			}

			elseif($aeaction == 'delete')
			{
					if(isset($_REQUEST['category_name']) && !empty($_REQUEST['category_name']))
					{
						$category_name=addslashes_mq($_REQUEST['category_name']);
					}
					if(isset($_REQUEST['category_parent_id']) && !empty($_REQUEST['category_parent_id']))
					{
						$category_parent_id=addslashes_mq($_REQUEST['category_parent_id']);
					}


				// Make sure this is not the default category. If it is the default category alert that the default category can only be renamed not deleted
				if($category_id == 1)
				{
					$themessagetoprint=__("Sorry but you cannot delete the default category. The default category can only be renamed","AWPCP");
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

					$themessagetoprint=__("The category has been deleted","AWPCP");
				}
			}
			elseif($aeaction == 'edit')
			{

					if(isset($_REQUEST['category_name']) && !empty($_REQUEST['category_name']))
					{
						$category_name=addslashes_mq($_REQUEST['category_name']);
					}
					if(isset($_REQUEST['category_parent_id']) && !empty($_REQUEST['category_parent_id']))
					{
						$category_parent_id=addslashes_mq($_REQUEST['category_parent_id']);
					}

					$query="UPDATE ".$table_name1." SET category_name='$category_name',category_parent_id='$category_parent_id' WHERE category_id='$category_id'";
					@mysql_query($query);

					$query="UPDATE ".$table_name3." SET ad_category_parent_id='$category_parent_id' WHERE ad_category_id='$category_id'";
					@mysql_query($query);

					$themessagetoprint=__("The category edit has been sucessfully completed","AWPCP");

			}
			else
			{
				$themessagetoprint=__("No instructions provided therefore no action taken","AWPCP");
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

		foreach($categoriestomove as $cattomove)
		{

			if($cattomove != $moveadstocategory)
			{

				// First update all the ads in the category to take on the new parent ID
				$query="UPDATE ".$table_name3." SET ad_category_parent_id='$moveadstocategory' WHERE ad_category_id='$cattomove'";
				@mysql_query($query);

				$query="UPDATE ".$table_name1." SET category_parent_id='$moveadstocategory' WHERE category_id='$cattomove'";
				@mysql_query($query);
			}

		}

		$themessagetoprint=__("With the exception of any category that was being moved to itself, the categories have been moved","AWPCP");
	}
	else
	{
		$themessagetoprint=__("The categories have not been moved because you did not indicate where you want the categories to be moved to","AWPCP");
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

	foreach($categoriestodelete as $cattodel)
	{
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

					$themessagetoprint=__("The categories have been deleted","AWPCP");
		}

	}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
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
		$themessagetoprint=__("No ads have been selected, therefore there is nothing to delete","AWPCP");
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

		$themessagetoprint=__("The ads have been deleted","AWPCP");

	}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End Process of deleting multiple ads
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	User Side functions and processes
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Home Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_homescreen()
{
	global $classicontent;
	if(!isset($classicontent) || empty($classicontent)){$classicontent=awpcpui_process($awpcppagename);	}
	echo $classicontent;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Post Ad Form Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_postformscreen()
{
	global $adpostform_content;
	if(!isset($adpostform_content) || empty($adpostform_content)){$adpostform_content=awpcpui_process_placead();}
	echo $adpostform_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Edit Form Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_editformscreen()
{
	global $editpostform_content;
	if(!isset($editpostform_content) || empty($editpostform_content)){$editpostform_content=awpcpui_process_editad();}
	echo $editpostform_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Contact Form Screen Configure
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_contactformscreen()
{
	global $contactpostform_content;
	if(!isset($contactpostform_content) || empty($contactpostform_content)){$contactpostform_content=awpcpui_process_contact();}
	echo $contactpostform_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Payment Thank you screen Configure
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_paymentthankyouscreen()
{
	global $paymentthankyou_content;
	if(!isset($paymentthankyou_content) || empty($paymentthankyou_content)){$paymentthankyou_content=paymentthankyou();}
	echo $paymentthankyou_content;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Browse Ads Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_browseadsscreen()
{
	global $browseads_content;
	if(!isset($browseads_content) || empty($browseads_content)){$browseads_content=awpcpui_process_browseads();}
	echo $browseads_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Browse Cats Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_browsecatsscreen()
{
	global $browsecats_content;
	if(!isset($browsecats_content) || empty($browsecats_content)){$browsecats_content=awpcpui_process_browsecats();}
	echo $browsecats_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Search Ads Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_searchformscreen()
{
	global $searchform_content;
	if(!isset($searchform_content) || empty($searchform_content)){$searchform_content=awpcpui_process_searchads();}
	echo $searchform_content;
}

function awpcpui_process_editad()
{

	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if($action == 'editad')
	{
		load_ad_edit_form($action,$awpcppagename,$usereditemail,$adaccesskey,$message);
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
	elseif($action == 'dp')
	{
		if(isset($_REQUEST['k']) && !empty($_REQUEST['k']))
		{
			$keyids=$_REQUEST['k'];
			list($picid,$adid,$adtermid,$adkey,$editemail) = split('[_]', $keyids);
		}

		deletepic($picid,$adid,$adtermid,$adkey,$editemail);
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
		if(get_awpcp_option('allowhtmlinadtext') == 0)
		{
			$addetails=strip_html_tags($addetails);
		}
		$adpaymethod=addslashes_mq($_REQUEST['adpaymethod']);
		if(!isset($adpaymethod) || empty($adpaymethod))
		{
			$adpaymethod="paypal";
		}
		if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction']))
		{
			$adaction=addslashes_mq($_REQUEST['adaction']);
		}
		else
		{
			$adaction='';
		}
		$awpcppagename=addslashes_mq($_REQUEST['awpcppagename']);
		$offset=addslashes_mq($_REQUEST['offset']);
		$results=addslashes_mq($_REQUEST['results']);
		$websiteurl=addslashes_mq($_REQUEST['websiteurl']);
		$checkhuman=addslashes_mq($_REQUEST['checkhuman']);
		$numval1=addslashes_mq($_REQUEST['numval1']);
		$numval2=addslashes_mq($_REQUEST['numval2']);

		processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);
	}
	elseif($action == 'awpcpuploadfiles')
	{
		handleimagesupload();
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
			showad($theadid,$omitmenu='');
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
	else
	{
		load_ad_edit_form($action='editad',$awpcppagename,$usereditemail,$adaccesskey,$message);
	}
}

function awpcpui_process_contact()
{
	$permastruc=get_option('permalink_structure');

	$pathvaluecontact=get_awpcp_option('pathvaluecontact');

		if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
		{
			$action=$_REQUEST['a'];
		}

		if(isset($_REQUEST['i']) && !empty($_REQUEST['i']))
		{
			$adid=$_REQUEST['i'];
		}

		if(!isset($adid) || empty($adid))
		{
			if( get_awpcp_option('seofriendlyurls') )
			{
				if(isset($permastruc) && !empty($permastruc))
				{

					$awpcpreplytoad_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
					$awpcpreplytoad_requested_url .= $_SERVER['HTTP_HOST'];
					$awpcpreplytoad_requested_url .= $_SERVER['REQUEST_URI'];

					$awpcpparsedreplytoadURL = parse_url ($awpcpreplytoad_requested_url);
					$awpcpsplitreplytoadPath = preg_split ('/\//', $awpcpparsedreplytoadURL['path'], 0, PREG_SPLIT_NO_EMPTY);

					$adid=$awpcpsplitreplytoadPath[$pathvaluecontact];

				}
			}


		}

		if($action == 'contact')
		{
			load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);
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
		else
		{
			load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);
		}
}

function awpcpui_process_searchads()
{

		if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
		{
			$action=$_REQUEST['a'];
		}

		if($action == 'searchads')
		{
			load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');
		}
		elseif($action == 'dosearch')
		{
			dosearch();
		}
		elseif( $action == 'cregs' )
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
			if( isset($_SESSION['theactiveregionid']) )
			{
				unset($_SESSION['theactiveregionid']);
			}

			load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');

		}
		else
		{
			load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');
		}
}

function awpcpui_process_browseads()
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

function awpcpui_process_browsecats()
{

	$pathvaluebrowsecats=get_awpcp_option('pathvaluebrowsecats');

	if(isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
	{
		$adcategory=$_REQUEST['category_id'];
	}
	else
	{
		$awpcpbrowsecats_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
		$awpcpbrowsecats_requested_url .= $_SERVER['HTTP_HOST'];
		$awpcpbrowsecats_requested_url .= $_SERVER['REQUEST_URI'];
		$awpcpparsedbrowsecatsURL = parse_url ($awpcpbrowsecats_requested_url);
		$awpcpsplitbrowsecatsPath = preg_split ('/\//', $awpcpparsedbrowsecatsURL['path'], 0, PREG_SPLIT_NO_EMPTY);

		$adcategory=$awpcpsplitbrowsecatsPath[$pathvaluebrowsecats];

	}

	if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if( ($action == 'browsecat') )
	{
			if($adcategory == -1)
			{
				$where="";
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


	}
	elseif(!isset($action) && isset($adcategory) )
	{
			if($adcategory == -1)
			{
				$where="";
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
	}
	else
	{
		$where="";
	}

	if($adcategory == -1)
	{
		echo"<p><b>";
		_e("No specific category was selected for browsing so you are viewing listings from all categories","AWPCP");
		echo "</b></p>";
	}

	display_ads($where,$byl='',$hidepager='');

}


function awpcpui_process_placead()
{

		if(isset($_REQUEST['a']) && !empty($_REQUEST['a']))
		{
			$action=$_REQUEST['a'];
		}

		if($action == 'placead')
		{
			load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websiteurl,$checkhuman,$numval1,$numval2);
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
			if(!isset($adpaymethod) || empty($adpaymethod))
			{
				$adpaymethod="paypal";
			}
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
		elseif($action == 'awpcpuploadfiles')
		{

			handleimagesupload();

		}
		elseif($action == 'loadpaymentpage')
		{
					$adid=addslashes_mq($_REQUEST['ad_id']);
					$key=addslashes_mq($_REQUEST['adkey']);
					$adterm_id=addslashes_mq($_REQUEST['adterm_id']);
					$adpaymethod=addslashes_mq($_REQUEST['adpaymethod']);

					processadstep3($adid,$adterm_id,$key,$adpaymethod);

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
				showad($theadid,$omitmenu='');
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


			load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websieurl='',$checkhuman='',$numval1='',$numval2='');

		}
		else
		{
			load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websiteurl,$checkhuman,$numval1,$numval2);
		}

}

function awpcpui_process($awpcppagename)
{
	/*global $wp_rewrite;
	$therwrules=$wp_rewrite->rewrite_rules();
	print_r($therwrules);*/

	$awpcppage=get_currentpagename();
	$pathvalueviewcategories=get_awpcp_option('pathvalueviewcategories');

	$categoriesviewpagename=sanitize_title(get_awpcp_option('categoriesviewpagename'), $post_ID='');

		global $awpcp_plugin_url,$hasregionsmodule;

		$awpcpbrowse_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
		$awpcpbrowse_requested_url .= $_SERVER['HTTP_HOST'];
		$awpcpbrowse_requested_url .= $_SERVER['REQUEST_URI'];

		$awpcpparsedbrowseadsURL = parse_url ($awpcpbrowse_requested_url);
		$awpcpsplitbrowseadPath = preg_split ('/\//', $awpcpparsedbrowseadsURL['path'], 0, PREG_SPLIT_NO_EMPTY);


		$browsestat=$awpcpsplitbrowseadPath[$pathvalueviewcategories];

		//print_r($awpcpsplitbrowseadPath);

		//echo $browsestat;


	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	$awpcp_nothinghereyet=__("Nothing here yet","AWPCP");

	$isadmin=checkifisadmin();

	$isclassifiedpage = checkifclassifiedpage($awpcppage);
	if( ($isclassifiedpage == false) && ($isadmin == 1))
	{
		_e("Hi admin, you need to go to your dashboard and setup your classifieds.","AWPCP");

	}
	elseif(($isclassifiedpage == false) && ($isadmin != 1))
	{
		echo $awpcp_nothinghereyet;
	}
	elseif($browsestat == $categoriesviewpagename)
	{
		awpcp_display_the_classifieds_page_body($awpcppagename);
	}
	elseif( isset($_REQUEST['layout']) && ($_REQUEST['layout'] == 2) )
	{
		awpcp_display_the_classifieds_page_body($awpcppagename);
	}
	else
	{
		awpcp_load_classifieds($awpcppagename);
	}

}

function awpcp_load_classifieds($awpcppagename)
{
	if(get_awpcp_option('main_page_display') == 1)
	{
		//Display latest ads on mainpage
		display_ads($where='',$byl='1',$hidepager='');
	}
	else
	{
		awpcp_display_the_classifieds_page_body($awpcppagename);
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
	$permastruc=get_option('permalink_structure');
	$quers=setup_url_structure($awpcppagename);
	$awpcp_page_id=awpcp_get_page_id($awpcppagename);
	$placeadpagenameunsani=get_awpcp_option('placeadpagename');
	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$editadpagenameunsani=get_awpcp_option('editadpagename');
	$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
	$searchadspagenameunsani=get_awpcp_option('searchadspagename');
	$searchadspagename=sanitize_title(get_awpcp_option('searchadspagename'), $post_ID='');
	$browseadspagenameunsani=get_awpcp_option('browseadspagename');
	$browseadspagename=sanitize_title(get_awpcp_option('browseadspagename'), $post_ID='');
	$browsecatspagenameunsani=get_awpcp_option('browsecatspagename');
	$browsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='');
	$awpcp_placead_pageid=awpcp_get_page_id($awpcp_placead_pagename=(sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='')));
	$awpcp_editad_pageid=awpcp_get_page_id($awpcp_editad_pagename=(sanitize_title(get_awpcp_option('editadpagename'), $post_ID='')));
	$awpcp_browseads_pageid=awpcp_get_page_id($awpcp_browseads_pagename=(sanitize_title(get_awpcp_option('browseadspagename'), $post_ID='')));
	$awpcp_searchads_pageid=awpcp_get_page_id($awpcp_searchads_pagename=(sanitize_title(get_awpcp_option('searchadspagename'), $post_ID='')));
	$awpcp_browsecats_pageid=awpcp_get_page_id($awpcp_browsecats_pagename=(sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='')));
	$categoriesviewpagename=sanitize_title(get_awpcp_option('categoriesviewpagename'),$post_ID='');
	$categoriesviewpagenameunsani=get_awpcp_option('categoriesviewpagename');



		if(isset($permastruc) && !empty($permastruc))
		{
			$url_placead="$quers/$placeadpagename";
			$url_browseads="$quers/$browseadspagename";
			$url_searchads="$quers/$searchadspagename";
			$url_editad="$quers/$editadpagename";
			$url_browsecats="$quers/$categoriesviewpagename";
		}
		else
		{
			$url_placead="$quers/?page_id=$awpcp_placead_pageid";
			$url_editad="$quers/?page_id=$awpcp_editad_pageid";
			$url_searchads="$quers/?page_id=$awpcp_searchads_pageid";
			$url_browseads="$quers/?page_id=$awpcp_browseads_pageid";
			$url_browsecats="$quers/?page_id=$awpcp_page_id&layout=2";
		}

			if($action == 'placead')
			{
				$liplacead="<li class=\"postad\"><b>$placeadpagenameunsani";
				$liplacead.=__(" Step 1","AWPCP");
				$liplacead.="</b></li>";
			}
			else
			{
				$liplacead="<li class=\"postad\"><a href=\"$url_placead\">$placeadpagenameunsani";
				$liplacead.="</a></li>";
			}
			if($action== 'editad')
			{
				$lieditad="<li class=\"edit\"><b>$editadpagenameunsani";
				$lieditad.=__(" Step 2","AWPCP");
				$lieditad.="</b></li>";
			}
			else
			{
				$lieditad="<li class=\"edit\"><a href=\"$url_editad\">$editadpagenameunsani";
				$lieditad.="</a></li>";
			}

			wp_reset_query();
			if(is_page($browseadspagename))
			{
				$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browsecats\">$categoriesviewpagenameunsani";
				$browseads_browsecats.="</a></li>";
			}
			elseif(is_page($browsecatspagename))
			{
				$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browseads\">$browseadspagenameunsani";
				$browseads_browsecats.="</a></li>";
			}
			else
			{
				$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browseads\">$browseadspagenameunsani";
				$browseads_browsecats.="</a></li>";
			}

						echo "<ul id=\"postsearchads\">";

					$isadmin=checkifisadmin();

					if(!(get_awpcp_option('onlyadmincanplaceads')))
					{
						echo "$liplacead";
						echo "$lieditad";
						echo "$browseads_browsecats";
						echo "<li class=\"searchcads\"><a href=\"$url_searchads\">$searchadspagenameunsani";
						echo "</a></li>";
					}
					elseif(get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1))
					{
						echo "$liplacead";
						echo "$lieditad";
						echo "$browseads_browsecats";
						echo "<li class=\"searchcads\"><a href=\"$url_searchads\">$searchadspagenameunsani";
						echo "</a></li>";
					}
					else
					{
						echo "$browseads_browsecats";
						echo "<li class=\"searchcads\"><a href=\"$url_searchads\">$searchadspagenameunsani";
						echo "</a></li>";
					}

					echo "</ul><div class=\"fixfloat\"></div>";
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
		$uiwelcome=get_awpcp_option('uiwelcome');
		echo "<div class=\"uiwelcome\">$uiwelcome</div>";

		// Place the menu items
		awpcp_menu_items();

		if($hasregionsmodule ==  1)
		{
			if( isset($_SESSION['theactiveregionid']) )
			{
				$theactiveregionid=$_SESSION['theactiveregionid'];
				$theactiveregionname=get_theawpcpregionname($theactiveregionid);
				echo "<h2>";
				_e("You are currently browsing in","AWPCP");
				echo "<b>$theactiveregionname</b></h2><SUP><a href=\"?a=unsetregion\">";
				_e("Clear session for ","AWPCP");
				echo "$theactiveregionname</a></SUP>";
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
						echo "<p><font style=\"font-size:smaller\">";
						_e("Powered by","AWPCP");
						echo "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
					}
					elseif( field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign')) )
					{

					}
					else
					{
						echo "<p><font style=\"font-size:smaller\">";
						_e("Powered by","AWPCP");
						echo "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
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

	$awpcp_page_id=awpcp_get_page_id($awpcppagename);
	$browsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='');
	$awpcp_browsecats_pageid=awpcp_get_page_id($awpcp_browsecats_pagename=(sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='')));

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

				if(get_awpcp_option('seofriendlyurls'))
				{
					if(isset($permastruc) && !empty($permastruc))
					{
						$url_browsecats="$quers/$browsecatspagename/$rsrow[0]/$modcatname1";
					}
					else
					{
						$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&category_id=$rsrow[0]";
					}
				}
				elseif(!(get_awpcp_option('seofriendlyurls')) )
				{
					if(isset($permastruc) && !empty($permastruc))
					{
						$url_browsecats="$quers/$browsecatspagename/$rsrow[0]/$modcatname1";
					}
					else
					{
						$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&category_id=$rsrow[0]";
					}
				}

					$myreturn.="<p class=\"maincategoryclass\">$caticonsurl<a href=\"$url_browsecats\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</p>";

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

						if(get_awpcp_option('seofriendlyurls'))
						{
							if(isset($permastruc) && !empty($permastruc))
							{
								$url_browsecats2="$quers/$browsecatspagename/$rsrow2[0]/$modcatname2";
							}
							else
							{
								$url_browsecats2="$quers/?page_id=$awpcp_browsecats_pageid&category_id=$rsrow2[0]";
							}
						}
						elseif(!(get_awpcp_option('seofriendlyurls')) )
						{
							if(isset($permastruc) && !empty($permastruc))
							{
								$url_browsecats2="$quers/$browsecatspagename/$rsrow2[0]/$modcatname2";
							}
							else
							{
								$url_browsecats2="$quers/?page_id=$awpcp_browsecats_pageid&category_id=$rsrow2[0]";
							}
						}

						$myreturn.="$subcaticonsurl<a href=\"$url_browsecats2\">$rsrow2[1]</a> $adsincat2";

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

	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option(permalink_structure);

	$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
	$editadpageid=awpcp_get_page_id($editadpagename);
	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$placeadpageid=awpcp_get_page_id($placeadpagename);

					if( get_awpcp_option('seofriendlyurls') )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_placeadpage="$quers/$placeadpagename";
							$awpcpquerymark="?";
						}
						else
						{
							$url_placeadpage="$quers/?page_id=$placeadpageid";
							$awpcpquerymark="&";
						}
					}
					elseif(!(get_awpcp_option('seofriendlyurls') ) )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_placeadpage="$quers/$placeadpagename";
							$awpcpquerymark="?";
						}
						else
						{
							$url_placeadpage="$quers/?page_id=$placeadpageid";
							$awpcpquerymark="&";
						}
					}

		if(get_awpcp_option('onlyadmincanplaceads') && ($isadmin != 1))
		{

			echo "<div id=\"classiwrapper\"><p>";
			_e("You do not have permission to perform the function you are trying to perform. Access to this page has been denied","AWPCP");
			echo "</p></div>";
		}

		elseif(get_awpcp_option('requireuserregistration') && !is_user_logged_in())
		{

			$postloginformto=get_awpcp_option('postloginformto');
			if(!isset($postloginformto) || empty($postloginformto))
			{
				$postloginformto="$siteurl/wp-login.php";
			}
			$registrationurl=get_awpcp_option('registrationurl');
			if(!isset($registrationurl) || empty($registrationurl))
			{
				$registrationurl="$siteurl/wp-login.php?action=register";
			}
			$putregisterlink="<a href=\"$registrationurl\" title=\"Register\"><b>";
			$putregisterlink.=__("Register","AWPCP");
			$putregisterlink.="</b></a>";

			echo "<div id=\"classiwrapper\"><p>";
			_e("Only registered users can post ads. If you are already registered, please login below in order to post your ad.","AWPCP");
			echo "</p><h2>";
			_e("Login","AWPCP");
			echo "</h2>
				  <form name=\"loginform\" id=\"loginform\" action=\"$postloginformto\" method=\"post\">
				  	<p>
				  		<label>";
				  		_e("Username","AWPCP");
				  		echo "<br>
				  		<input name=\"log\" id=\"user_login\" value=\"\" class=\"textinput\" size=\"20\" tabindex=\"10\" type=\"text\"></label>
				  	</p>

				  	<p>
				  		<label>";
				  		_e("Password","AWPCP");
				  		echo "<br>
				  		<input name=\"pwd\" id=\"user_pass\" value=\"\" class=\"textinput\" size=\"20\" tabindex=\"20\" type=\"password\"></label>
				  	</p>
				  	<p><label><input name=\"rememberme\" id=\"rememberme\" value=\"forever\" tabindex=\"90\" type=\"checkbox\">";_e("Remember Me","AWPCP");
				  	echo "</label></p>
				  	<p align=\"center\">
				  		<input name=\"login-submit\" id=\"wp-submit\" value=\"";_e("Log In","AWPCP");echo "\" class=\"submitbutton\" tabindex=\"100\" type=\"submit\">
				  		<input name=\"redirect_to\" value=\"$url_placeadpage\" type=\"hidden\">
				  		<input name=\"testcookie\" value=\"1\" type=\"hidden\">
				  	</p>
  				</form><p>$putregisterlink</p>
				</div>";

		}
		else
		{
			$table_name2 = $wpdb->prefix . "awpcp_adfees";
			$table_name3 = $wpdb->prefix . "awpcp_ads";

			$images='';
			$displaydeleteadlink='';

				if($action == 'editad')
				{
					$savedemail=get_adposteremail($adid);

					if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
					{

					 	$query="SELECT ad_title,ad_contact_name,ad_contact_email,ad_category_id,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details,ad_key,websiteurl from ".$table_name3." WHERE ad_id='$adid' AND ad_contact_email='$editemail' AND ad_key='$adaccesskey'";
					 	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						while ($rsrow=mysql_fetch_row($res))
						{
							list($adtitle,$adcontact_name,$adcontact_email,$adcategory,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$ad_key,$websiteurl)=$rsrow;
						}

						if(isset($ad_item_price) && !empty($ad_item_price))
						{
							$ad_item_price=($ad_item_price/100);
						}
						else
						{
							$ad_item_price='';
						}

						$ikey="$adid";
						$ikey.="_";
						$ikey.="$adaccesskey";
						$ikey.="_";
						$ikey.="$editemail";


							if(isset($permastruc) && !empty($permastruc))
							{
								$displaydeleteadlink="<p class=\"alert\"><a href=\"$quers/$editadpagename?a=deletead&k=$ikey\">";
								$displaydeleteadlink.=__("Delete Ad","AWPCP");
								$displaydeleteadlink.="</a></p>";
							}
							else
							{
								$displaydeleteadlink="<p class=\"alert\"><a href=\"$quers/?page_id=$editadpageid&a=deletead&k=$ikey\">";
								$displaydeleteadlink.=__("Delete Ad","AWPCP");
								$displaydeleteadlink.="</a></p>";
							}
					}
					else
					{
						unset($action);
					}
				}
	echo "<div id=\"classiwrapper\">";

	if(!is_admin())
	{
		awpcp_menu_items();
	}

		////////////////////////////////////////////////////////////////////////////////
		// If running in pay mode get and display the payment option settings
		////////////////////////////////////////////////////////////////////////////////

		if(get_awpcp_option(freepay) == 1)
		{
			$paymethod='';

			if($action == 'editad')
			{
				$paymethod='';
			}

			else
			{
				if(adtermsset() && !is_admin())
				{
					//configure the pay methods

					if($adpaymethod == 'paypal'){ $ischeckedP="checked"; } else { $ischeckedP=''; }
					if($adpaymethod == '2checkout'){ $ischecked2co="checked"; }else { $ischecked2co=''; }

						$paymethod="<div id=\"showhidepaybutton\" style=\"display:none;\"><h2>";
						$payment.=__("Payment gateway","AWPCP");
						$payment.="</h2><p>";
						$payment.=__("Choose your payment gateway","AWPCP");
						$payment.="</p>";

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
				&&(get_awpcp_option('displayphonefieldreqop') == 1))
				{
				$phoneerrortxt=__("You did not fill out a phone number for the ad contact person. The information is required","AWPCP");
				$phonecheck="if(the.adcontact_phone.value==='')
				{
							alert('$phoneerrortxt');
							the.adcontact_phone.focus();
							return false;
						}";
				}else {$phonecheck='';}

				if((get_awpcp_option('displaycityfield') == 1)
				&&(get_awpcp_option('displaycityfieldreqop') == 1))
				{
					$cityerrortxt=__("You did not fill out your city. The information is required","AWPCP");
					$citycheck="if(the.adcontact_city.value==='') {
							alert('$cityerrortxt');
							the.adcontact_city.focus();
							return false;
						}";
				}else {$citycheck='';}

				if((get_awpcp_option('displaystatefield') == 1)
				&&(get_awpcp_option('displaystatefieldreqop') == 1))
				{
					$stateerrortxt=__("You did not fill out your state. The information is required","AWPCP");
					$statecheck="if(the.adcontact_state.value==='') {
							alert('$stateerrortxt');
							the.adcontact_state.focus();
							return false;
						}";
				}else {$statecheck='';}

				if((get_awpcp_option('displaycountyvillagefield') == 1)
				&&(get_awpcp_option('displaycountyvillagefieldreqop') == 1))
				{
					$countyvillageerrortxt=__("You did not fill out your county/village/other. The information is required","AWPCP");
					$countyvillagecheck="if(the.adcontact_countyvillage.value==='') {
							alert('$countyvillageerrortxt');
							the.adcontact_countyvillage.focus();
							return false;
						}";
				}else {$countyvillagecheck='';}

				if((get_awpcp_option('displaycountryfield') == 1)
				&&(get_awpcp_option('displaycountryfieldreqop') == 1))
				{
					$countryerrortxt=__("You did not fill out your country. The information is required","AWPCP");
					$countrycheck="if(the.adcontact_country.value==='') {
							alert('$countryerrortxt');
							the.adcontact_country.focus();
							return false;
						}";}else {$countrycheck='';}

				if((get_awpcp_option('displaywebsitefield') == 1)
				&&(get_awpcp_option('displaywebsitefieldreqop') == 1))
				{
					$websiteerrortxt=__("You did not fill out your website address. The information is required","AWPCP");
					$websitecheck="if(the.websiteurl.value==='') {
							alert('$websiteerrortxt');
							the.websiteurl.focus();
							return false;
						}";
				}else {$websitecheck='';}

				if((get_awpcp_option('displaypricefield') == 1)
				&&(get_awpcp_option('displaypricefieldreqop') == 1))
				{
					$itempriceerrortxt=__("You did not enter a value for the item price. The information is required","AWPCP");
					$itempricecheck="if(the.ad_item_price.value==='') {
							alert('$itempriceerrortxt');
							the.ad_item_price.focus();
							return false;
						}";
				}else {$itempricecheck='';}

				if( (get_awpcp_option('freepay') == 1) && ($action == 'placead') && !is_admin())
				{
					$paymethoderrortxt=__("You did not select your payment method. The information is required","AWPCP");
					$paymethodcheck="if(!checked(the.adpaymethod)) {
							alert('$paymethoderrortxt');
							the.adpaymethod.focus();
							return false;
						}";
				}else {$paymethodcheck='';}

				if( (get_awpcp_option('freepay') == 1) && ($action == 'placead') && !is_admin() )
				{
					$adtermerrortxt=__("You did not select your ad term choice. The information is required","AWPCP");
					$adtermcheck="if(the.adterm_id.value==='') {
							alert('$adtermerrortxt');
							the.adterm_id.focus();
							return false;
						}";
				}else {$adtermcheck='';}

				if((get_awpcp_option('contactformcheckhuman') == 1) && !is_admin())
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

					$checkhumanerrortxt1=__("You did not solve the math problem. Please solve the math problem to proceed.","AWPCP");
					$checkhumanerrortxt2=__("Your answer to the math problem was not correct. Please try again.","AWPCP");

					$checkhumancheck="

					if (the.checkhuman.value==='')
					{
						alert('$checkhumanerrortxt1');
						the.checkhuman.focus();
						return false;
					}
					if (the.checkhuman.value != $thesum)
					{
						alert('$checkhumanerrortxt2');
						the.checkhuman.focus();
						return false;
					}

					";
				}

				$adtitleerrortxt=__("You did not fill out an ad title. The information is required","AWPCP");
				$adcategoryerrortxt=__("You did not select an ad category. The information is required","AWPCP");
				$adcontactemailerrortxt=__("Either you did not enter your email address or the email address you entered is not valid","AWPCP");
				$adcontactnameerrortxt=__("You did not fill in the name of the ad contact person. The information is required","AWPCP");
				$addetailserrortxt=__("You did not fill in any details for your ad. The information is required","AWPCP");

				$checktheform="<script type=\"text/javascript\">
					function checkform() {
						var the=document.adpostform;
						if (the.adtitle.value==='') {
							alert('$adtitleerrortxt');
							the.adtitle.focus();
							return false;
						}
						if (the.adcategory.value==='') {
							alert('$adcategoryerrortxt');
							the.adcategory.focus();
							return false;
						}
						if (the.adcontact_name.value==='') {
							alert('$adcontactnameerrortxt');
							the.adcontact_name.focus();
							return false;
						}
						if ((the.adcontact_email.value==='') || (the.adcontact_email.value.indexOf('@')==-1) || (the.adcontact_email.value.indexOf('.',the.adcontact_email.value.indexOf('@')+2)==-1) || (the.adcontact_email.value.lastIndexOf('.')==the.adcontact_email.value.length-1)) {
							alert('$adcontactemailerrortxt');
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

						if (the.addetails.value==='')
						{
							alert('$addetailserrortxt');
							the.addetails.focus();
							return false;
						}

						return true;
					}

							function textCounter(field, countfield, maxlimit)
							{
								if (field.value.length > maxlimit)
								{ // if too long...trim it!
									field.value = field.value.substring(0, maxlimit);
								}
								// otherwise, update 'characters left' counter

								else
								{
									countfield.value = maxlimit - field.value.length;
								}
							}


							 function awpcp_toggle_visibility(id)
							 {
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

				    		 function awpcp_toggle_visibility_reverse(id)
				    		 {
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
				$htmlstatus=get_awpcp_option('htmlstatustext');
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
					$editorposttext=__("Your ad details have been filled out in the form below. Make any changes needed then resubmit the ad to update it","AWPCP");
				}
				else
				{
					$editorposttext=__("Fill out the form below to post your classified ad. ","AWPCP");
				}

				echo "<div style=\"display:$formdisplayvalue\">";

				if(!is_admin())
				{
					$theformbody.="$displaydeleteadlink<p>$editorposttext";

					if(! ($action== 'editad' ) )
					{
						if($hasregionsmodule == 1)
						{
							$theformbody.=__("If you have made an error in setting up the location where you want to post your ad click the link below to unset your saved locations","AWPCP");
							$theformbody.="<div class=\"unsetsavedlocationslink\"><a href=\"$url_placeadpage".$awpcpquerymark."a=cregs\">";
							$theformbody.=__("Click here to unset your saved locations","AWPCP");
							$theformbody.="</a></div>";
						}
					}

					$theformbody.="</p>";

					$faction="id=\"awpcpui_process\"";
				}
				else
				{
					$faction="action=\"?page=Manage1\" id=\"awpcp_launch\"";
				}

				$theformbody.="$checktheform $ermsg";
				$theformbody.="<form method=\"post\" name=\"adpostform\" id=\"adpostform\" $faction onsubmit=\"return(checkform())\">";
				$theformbody.="<input type=\"hidden\" name=\"adid\" value=\"$adid\">";
				$theformbody.="<input type=\"hidden\" name=\"adaction\" value=\"$action\">";
				$theformbody.="<input type=\"hidden\" name=\"a\" value=\"dopost1\">";

				if($action == 'editad')
				{
					$theformbody.="<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\">";
				}

				$theformbody.="<input type=\"hidden\" name=\"adkey\" value=\"$ad_key\">";
				$theformbody.="<input type=\"hidden\" name=\"editemail\" value=\"$editemail\">";
				$theformbody.="<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">";
				$theformbody.="<input type=\"hidden\" name=\"results\" value=\"$results\">";
				$theformbody.="<input type=\"hidden\" name=\"offset\" value=\"$offset\">";
				$theformbody.="<input type=\"hidden\" name=\"numval1\" value=\"$numval1\">";
				$theformbody.="<input type=\"hidden\" name=\"numval2\" value=\"$numval2\">";
				$theformbody.="<br/>";
				$theformbody.="<h2>";
				$theformbody.=__("Ad Details and Contact Information","AWPCP");
				$theformbody.="</h2><p>";
				$theformbody.=__("Ad Title","AWPCP");
				$theformbody.="<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"adtitle\" value=\"$adtitle\"></p>";
				$theformbody.="<p>";
				$theformbody.=__("Ad Category","AWPCP");
				$theformbody.="<br/><select name=\"adcategory\"><option value=\"\">";
				$theformbody.=__("Select your ad category","AWPCP");
				$theformbody.="</option>$allcategories</a></select></p>";

				if(get_awpcp_option(displaywebsitefield) == 1)
				{
					$theformbody.="<p>Website URL<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"websiteurl\" value=\"$websiteurl\" /></select></p>";
				}

				$theformbody.="<p>";
				$theformbody.=__("Name of person to contact","AWPCP");
				$theformbody.="<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_name\" value=\"$adcontact_name\" $readonlyacname></p>";
				$theformbody.="<p>";
				$theformbody.=__("Contact Person's Email [Please enter a valid email. The codes needed to edit your ad will be sent to your email address]","AWPCP");
				$theformbody.="<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_email\" value=\"$adcontact_email\" $readonlyacem></p>";

				if(get_awpcp_option(displayphonefield) == 1)
				{
					$theformbody.="<p>";
					$theformbody.=__("Contact Person's Phone Number","AWPCP");
					$theformbody.="<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_phone\" value=\"$adcontact_phone\"></p>";
				}


				if(get_awpcp_option(displaycountryfield) )
				{
					$theformbody.="<p>";
					$theformbody.=__("Country","AWPCP");
					$theformbody.="<br/>";

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
					$theformbody.="<p>";
					$theformbody.=__("State","AWPCP");
					$theformbody.="<br/>";

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
					$theformbody.="<p>";
					$theformbody.=__("City","AWPCP");
					$theformbody.="<br/>";

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
					$theformbody.="<p>";
					$theformbody.=__("County/Village/Other","AWPCP");
					$theformbody.="<br/>";

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

				if(get_awpcp_option(displaypricefield) == 1)
				{
					$theformbody.="<p>";
					$theformbody.=__("Item Price","AWPCP");
					$theformbody.="<br/><input size=\"10\" type=\"text\" class=\"inputboxprice\" maxlength=\"10\" name=\"ad_item_price\" value=\"$ad_item_price\"></p>";
				}
					$theformbody.="<p>";
					$theformbody.=__("Ad Details","AWPCP");
					$theformbody.="<br/><input readonly type=\"text\" name=\"remLen\" size=\"10\" maxlength=\"5\" class=\"inputboxmini\" value=\"$addetailsmaxlength\">";
					$theformbody.=__("characters left","AWPCP");
					$theformbody.="<br/><br/>$htmlstatus<br/><textarea name=\"addetails\" rows=\"10\" cols=\"50\" class=\"textareainput\" onKeyDown=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\" onKeyUp=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\">$addetails</textarea></p>";

				if((get_awpcp_option('contactformcheckhuman') == 1) && !is_admin())
				{
					$theformbody.="<p>";
					$theformbody.=__("Enter the value of the following sum","AWPCP");
					$theformbody.=": <b>$numval1 + $numval2</b><br><input type=\"text\" name=\"checkhuman\" value=\"$checkhuman\" size=\"5\"></p>";
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

				if(adtermsset() && !is_admin())
				{

					$adtermscode="<h2>";
					$adtermscode.=__("Select Ad Term","AWPCP");
					$adtermscode.="</h2>";

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

								$awpcpduration=__("Duration","AWPCP");

								$adtermscode.="value=\"$savedadtermid\" $ischecked>$adterm_name ($awpcpthecurrencysymbol$amount $awpcpduration: $rec_period $termname )<br/>";
							}

						}

				}

					echo "$adtermscode<p>$paymethod</p>";

				}//end if adtermsset
			}
		}
				echo "<input type=\"submit\" class=\"scbutton\" value=\"";
				_e("Continue","AWPCP");
				echo "\"></form>";
				echo "</div>";
				echo "</div>";


	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display a form to the user when edit existing ad is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_edit_form($action,$awpcppagename,$usereditemail,$adaccesskey,$message)
{

	$isadmin=checkifisadmin();
	$permastruc=get_option('permalink_structure');
	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}
	$quers=setup_url_structure($awpcppagename);
	$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
	$editadpageid=awpcp_get_page_id($editadpagename);

	if(isset($permastruc) && !empty($permastruc))
	{
		$url_editpage="$quers/$editadpagename";
		$awpcpquerymark="?";
	}
	else
	{
		$url_editpage="$quers/?page_id=$editadpageid";
		$awpcpquerymark="&";
	}

		if(get_awpcp_option('onlyadmincanplaceads') && ($isadmin != '1'))
		{

			echo "<div id=\"classiwrapper\"><p>";
			_e("You do not have permission to perform the function you are trying to perform. Access to this page has been denied","AWPCP");
			echo "</p></div>";
		}

		else
		{

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

			if(!isset($message) || empty($message))
			{
				$message="<p>";
				$message.=__("Please enter the email address you used when you created your ad in addition to the ad access key that was emailed to you after your ad was submitted","AWPCP");
				$message.="</p>";
			}

		echo "<div id=\"classiwrapper\">";
		awpcp_menu_items();

		if(isset($message) && !empty($message))
		{
			echo $message;
		}
		echo $checktheform;
		echo "<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
		echo "<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">";
		echo "<input type=\"hidden\" name=\"a\" value=\"doadedit1\">";
		echo "<p>";
		_e("Enter your Email address","AWPCP");
		echo "<br/>";
		echo "<input type=\"text\" name=\"editemail\" value=\"$usereditemail\" class=\"inputbox\"></p>";
		echo "<p>";
		_e("Enter your ad access key","AWPCP");
		echo "<br/>";
		echo "<input type=\"text\" name=\"adaccesskey\" value=\"$adaccesskey\" class=\"inputbox\"></p>";
		echo "<input type=\"submit\" class=\"scbutton\" value=\"";
		_e("Continue","AWPCP");
		echo "\"> <a href=\"$url_editpage".$awpcpquerymark."a=resendaccesskey\">";
		_e("Resend Ad Access Key","AWPCP");
		echo "</a>";
		echo "<br/>";
		echo "</form>";
		echo "</div>";

	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display a form to the user for resend access key request
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function resendadaccesskeyform($editemail,$awpcppagename)
{

	global $nameofsite,$wpdb,$siteurl,$thisadminemail,$message;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

	if(!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

		$awpcp_resendakeysubject=get_awpcp_option('resendakeyformsubjectline');
		$awpcp_resendakeybody=get_awpcp_option('resendakeyformbodymessage');



		$quers=setup_url_structure($awpcppagename);
		$awpcpresendemailerrortxt=__("Either you did not enter your email address or the email address you entered is not valid","AWPCP");


			$checktheform="<script type=\"text/javascript\">
				function checkform() {
					var the=document.myform;

					if ((the.editemail.value==='') || (the.editemail.value.indexOf('@')==-1) || (the.editemail.value.indexOf('.',the.editemail.value.indexOf('@')+2)==-1) || (the.editemail.value.lastIndexOf('.')==the.editemail.value.length-1)) {
						alert('$awpcpresendemailerrortxt');
						the.editemail.focus();
						return false;
					}

					return true;
				}

			</script>";

			if(!isset($message) || empty($message))
			{
				$message="<p>";
				$message.=__("Please enter the email address you used when you created your ad. Your access key will be sent to that email account. The email address you enter must match up with the email address we have on file","AWPCP");
				$message.="</p>";
			}

		if( isset($editemail) && !empty($editemail) )
		{
			// Get the ad titles and access keys in the database that are associated with the email address
			$query="SELECT ad_title,ad_key,ad_contact_name FROM ".$table_name3." WHERE ad_contact_email='$editemail'";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			$adtitlekeys=array();

			while ($rsrow=mysql_fetch_row($res))
			{
				list($adtitle,$adkey,$adpostername)=$rsrow;

				$adtitlekeys[]="$adtitle: $adkey";

			}

			$totaladsfound=count($adtitlekeys);

			if($totaladsfound > 0 )
			{

				$resendakeymessage="$awpcp_resendakeybody";
				$resendakeymessage.="<br/><br/>";
				$resendakeymessage.=__("Total ads found sharing your email address","AWPCP");
				$resendakeymessage.=": [$totaladsfound]";
				$resendakeymessage.="<br/><br/>";


				foreach ($adtitlekeys as $theadtitleandkey)
				{
					$resendakeymessage.="$theadtitleandkey <br/>";
				}

				$resendakeymessage.="\n$nameofsite\n$siteurl";

				$subject="$awpcp_resendakeysubject";
				$from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

				if(send_email($thisadminemail,$editemail,$subject,$resendakeymessage,false))
				{
					$resendakeymessage=str_replace("<br/>", "\n", $resendakeymessage);
					$resendakeymessage=str_replace("<br/><br/>", "\n\n", $resendakeymessage);

					if(!(mail($editemail, $subject, $resendakeymessagealt, $from_header)))
					{
						$awpcp_smtp_host = get_awpcp_option('smtphost');
						$awpcp_smtp_username = get_awpcp_option('smtpusername');
						$awpcp_smtp_password = get_awpcp_option('smtppassword');
						$resendakeymessage=str_replace("<br/>", "\n", $resendakeymessage);
						$resendakeymessage=str_replace("<br/><br/>", "\n\n", $resendakeymessage);

						$headers = array ('From' => $from_header,
						  'To' => $editemail,
						  'Subject' => $subject);
						$smtp = Mail::factory('smtp',
						  array ('host' => $awpcp_smtp_host,
							'auth' => true,
							'username' => $awpcp_smtp_username,
							'password' => $awpcp_smtp_password));

						$mail = $smtp->send($editemail, $headers, $resendakeymessage);

						if (PEAR::isError($mail))
						{
						  $awpcpactivationmailsent=0;
						}
						else
						{
							$awpcpactivationmailsent=1;
						}
					}
					else
					{
						$awpcpactivationmailsent=1;
					}
				}
				else
				{
					$awpcpactivationmailsent=1;
				}

				if($awpcpactivationmailsent)
				{
						  $awpcpresendprocessresponse=__("Your access key has been emailed to","AWPCP");
						  $awpcpresendprocessresponse.=" [ $editemail ]";
				}
				else
				{
					$awpcpresendprocessresponse=__("There was a problem encountered during the attempt to resend your access key. We apologize. Please try again and if the problem persists, please contact the system administrator","AWPCP");
				}
			}
			else
			{
				$awpcpresendprocessresponse=__("There were no ads found registered with the email address provided","AWPCP");
			}
	}
	else
	{
		$awpcpresendprocessresponse=$checktheform;
		$awpcpresendprocessresponse="$message";
		$awpcpresendprocessresponse.="<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
		$awpcpresendprocessresponse.="<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\">";
		$awpcpresendprocessresponse.="<input type=\"hidden\" name=\"a\" value=\"resendaccesskey\">";
		$awpcpresendprocessresponse.="<p>";
		$awpcpresendprocessresponse.=__("Enter your Email address","AWPCP");
		$awpcpresendprocessresponse.="<br/>";
		$awpcpresendprocessresponse.="<input type=\"text\" name=\"editemail\" value=\"$editemail\" class=\"inputbox\"></p>";
		$awpcpresendprocessresponse.="<input type=\"submit\" class=\"scbutton\" value=\"";
		$awpcpresendprocessresponse.=__("Continue","AWPCP");
		$awpcpresendprocessresponse.="\"><br/></form>";

	}

		echo "<div id=\"classiwrapper\">";
		awpcp_menu_items();
		echo $awpcpresendprocessresponse;
		echo "</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Display a form to be filled out in order to contact the ad poster
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$message)
{

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');

	$quers=setup_url_structure($awpcppagename);

	$contactformcheckhumanhighnumval=get_awpcp_option('contactformcheckhumanhighnumval');

	$numval1=rand(1,$contactformcheckhumanhighnumval);
	$numval2=rand(1,$contactformcheckhumanhighnumval);

	$thesum=($numval1 + $numval2);

	if(get_awpcp_option('contactformcheckhuman') == 1)
	{
		$nosumvalueerror=__("You did not enter the solution to the Math problem","AWPCP");
		$wrongsumvalueerror=__("The solution you submitted for the Math problem was not correct","AWPCP");

		$conditionscheckhuman="

				if (the.checkhuman.value==='') {
					alert('$nosumvalueerror');
					the.checkhuman.focus();
					return false;
				}
				if (the.checkhuman.value != $thesum) {
					alert('$wrongsumvalueerror');
					the.checkhuman.focus();
					return false;
				}

			";
	}
	else
	{
		$conditionscheckhuman ="";
	}

	$awpcpusernamemissing=__("You did not enter your name. Please enter your name","AWPCP");
	$awpcpemailinvalid=__("Either you did not enter your email address or the email address you entered is not valid","AWPCP");
	$awpcpmessagebodymissing=__("You did not enter any message. Please enter a message","AWPCP");

	$checktheform="<script type=\"text/javascript\">
	function checkform() {
		var the=document.myform;

		if (the.sendersname.value==='') {
			alert('$awpcpusernamemissing');
			the.sendersname.focus();
			return false;
		}
		if ((the.sendersemail.value==='') || (the.sendersemail.value.indexOf('@')==-1) || (the.sendersemail.value.indexOf('.',the.sendersemail.value.indexOf('@')+2)==-1) || (the.sendersemail.value.lastIndexOf('.')==the.sendersemail.value.length-1)) {
			alert('$awpcpemailinvalid');
			the.sendersemail.focus();
			return false;
		}
		if (the.contactmessage.value==='') {
			alert('$awpcpmessagebodymissing');
			the.contactmessage.focus();
			return false;
		}

		$conditionscheckhuman;

		return true;
	}</script>";

		echo "<div id=\"classiwrapper\">";
		awpcp_menu_items();
		$isadmin=checkifisadmin();

		$theadtitle=get_adtitle($adid);
		$modtitle=cleanstring($theadtitle);
		$modtitle=add_dashes($modtitle);

		$permastruc=get_option('permalink_structure');
		$showadspagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');

					$url_showad=url_showad($adid);
					$thead="<a href=\"$url_showad\">$theadtitle</a>";


		echo "<p>";
		_e("You are responding to ","AWPCP");
		echo "$thead</p>";
		if(isset($message) && !empty($message))
		{
			echo "$message";
		}
		echo $checktheform;
		echo "<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
		echo "<input type=\"hidden\" name=\"adid\" value=\"$adid\">";
		echo "<input type=\"hidden\" name=\"a\" value=\"docontact1\">";
		echo "<input type=\"hidden\" name=\"numval1\" value=\"$numval1\">";
		echo "<input type=\"hidden\" name=\"numval2\" value=\"$numval2\">";
		echo "<p>";
		_e("Your Name","AWPCP");
		echo "<br/>";
		echo "<input type=\"text\" name=\"sendersname\" value=\"$sendersname\" class=\"inputbox\"></p>";
		echo "<p>";
		_e("Enter your Email address","AWPCP");
		echo "<br/>";
		echo "<input type=\"text\" name=\"sendersemail\" value=\"$sendersemail\" class=\"inputbox\"></p>";
		echo "<p>";
		_e("Enter your message below","AWPCP");
		echo "<br/>";
		echo "<textarea name=\"contactmessage\" rows=\"5\" cols=\"90%\" class=\"textareainput\">$contactmessage</textarea></p>";

		if(get_awpcp_option('contactformcheckhuman') == 1)
		{
			echo "<p>";
			_e("Enter the value of the following sum","AWPCP");
			echo ": <b>$numval1 + $numval2</b><br>";
			echo "<input type=\"text\" name=\"checkhuman\" value=\"$checkhuman\" size=\"5\"></p>";
		}

		echo "<input type=\"submit\" class=\"scbutton\" value=\"";
		_e("Continue","AWPCP");
		echo "\">";
		echo "<br/></form></div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Process the request to contact the poster of the ad
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg)
{

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

	if(!isset($adid) || empty($adid))
	{
		$error=true;
		$adidmsg="<li>";
		$adidmsg.=__("The ad could not be identified due to a missing ad identification number","AWPCP");
		$adidmsg.="</li>";
	}
	if(!isset($sendersname) || empty($sendersname))
	{
		$error=true;
		$sendersnamemsg="<li>";
		$sendersnamemsg.=__("You did not enter your name. You must include a name for this message to be relayed on your behalf","AWPCP");
		$sendersnamemsg.="</li>";
	}

	if(get_awpcp_option('contactformcheckhuman') == 1)
	{

		if(!isset($checkhuman) || empty($checkhuman))
		{
			$error=true;
			$checkhumanmsg="<li>";
			$checkhumanmsg.=__("You did not solve the Math Problem","AWPCP");
			$checkhumanmsg.="</li>";
		}
		if($checkhuman != $thesum)
		{
				$error=true;
				$sumwrongmsg="<li>";
				$sumwrongmsg.=__("Your solution to the Math problem was incorrect","AWPCP");
				$sumwrongmsg.="</li>";
		}
	}

	if(!isset($contactmessage) || empty($contactmessage))
	{
		$error=true;
		$contactmessagemsg="<li>";
		$contactmessagemsg.=__("There was no text entered for your message","AWPCP");
		$contactmessagemsg.="</li>";
	}

	if(!isset($sendersemail) || empty($sendersemail))
	{
		$error=true;
		$sendersemailmsg="<li>";
		$sendersemailmsg.=__("You did not enter your name. You must include a name for this message to be relayed on your behalf","AWPCP");
		$sendersemailmsg.="</li>";
	}
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sendersemail))
	{
		$error=true;
		$sendersemailwrongmsg="<li>";
		$sendersemailwrongmsg.=__("The email address you entered was not a valid email address. Please check for errors and try again","AWPCP");
		$sendersemailwrongmsg.="</li>";
	}

	if($error)
	{
		$ermsg="<p>";
		$ermsg.=__("There has been an error found. Your message has not been sent. Please review the list of problems, correct them then try to send your message again","AWPCP");
		$ermsg.="</p>";
		$ermsg.="<b>";
		$ermsg.=__("The errors","AWPCP");
		$ermsg.=":</b><br/>";
		$ermsg.="<ul>$adidmsg $sendersnamemsg $checkhumanmsg $contactmessagemsg $sumwrongmsg $sendersemailmsg $sendersemailwrongmsg</ul>";

		load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);
	}
	else
	{
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
			$subject=__("Regarding","AWPCP");
			$subject.=": $theadtitle";
		}

		$contactformbodymessage=get_awpcp_option('contactformbodymessage');
		$contactformbodymessage.="<br/><br/>";
		$contactformbodymessage.=__("Message","AWPCP");
		$contactformbodymessage.="<br/><br/>";
		$contactformbodymessage.=$contactmessage;
		$contactformbodymessage.="<br/><br/>";
		$contactformbodymessage.="$nameofsite";
		$contactformbodymessage.="<br/>";
		$contactformbodymessage.=$siteurl;


		$from_header = "From: ". $sendersname . " <" . $sendersemail . ">\r\n";


				if(send_email($sendersemail,$sendtoemail,$subject,$contactformbodymessage,false))
				{

					$contactformbodymessage=str_replace("<br/>", "\n", $contactformbodymessage);
					$contactformbodymessage=str_replace("<br/><br/>", "\n\n", $contactformbodymessage);

					if(!(mail($sendtoemail, $subject, $contactformbodymessage, $from_header)))
					{
						$awpcp_smtp_host = get_awpcp_option('smtphost');
						$awpcp_smtp_username = get_awpcp_option('smtpusername');
						$awpcp_smtp_password = get_awpcp_option('smtppassword');

						$contactformbodymessage=str_replace("<br/>", "\\n", $contactformbodymessage);
						$contactformbodymessage=str_replace("<br/><br/>", "\n\n", $contactformbodymessage);

						$headers = array ('From' => $from_header,
						  'To' => $sendtoemail,
						  'Subject' => $subject);
						$smtp = Mail::factory('smtp',
						  array ('host' => $awpcp_smtp_host,
							'auth' => true,
							'username' => $awpcp_smtp_username,
							'password' => $awpcp_smtp_password));

						$mail = $smtp->send($sendtoemail, $headers, $contactformbodymessage);

						if (PEAR::isError($mail))
						{
						  $contactemailmailsent=0;
						}
						else
						{
							$contactemailmailsent=1;
						}
					}
					else
					{
						$contactemailmailsent=1;
					}
				}
				else
				{
					$contactemailmailsent=1;
				}

				if($contactemailmailsent)
				{
						  $contactformprocessresponse=__("Your message has been sent","AWPCP");
				}
				else
				{
					$contactformprocessresponse=__("There was a problem encountered during the attempt to send your message. Please try again and if the problem persists, please contact the system administrator","AWPCP");
				}
	}

	$contactpostform_content=$contactformprocessresponse;
		echo "<div id=\"classiwrapper\">";
		awpcp_menu_items();
		echo $contactformprocessresponse;
		echo "</div>";

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
$searchadspagename = sanitize_title(get_awpcp_option('searchadspagename'), $post_ID='');
$searchadspageid = awpcp_get_page_id($searchadspagename);

$quers=setup_url_structure($awpcppagename);

					if( get_awpcp_option('seofriendlyurls') )
					{

						if(isset($permastruc) && !empty($permastruc))
						{
							$url_searchpage="$quers/$searchadspagename";
							$awpcpquerymark="?";
						}
						else
						{
							$url_searchpage="$quers/?page_id=$searchadspageid";
							$awpcpquerymark="&";
						}
					}
					elseif(!(get_awpcp_option('seofriendlyurls') ) )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_searchpage="$quers/$searchadspagename";
							$awpcpquerymark="?";
						}
						else
						{
							$url_searchpage="$quers/?page_id=$searchadspageid";
							$awpcpquerymark="&";
						}
					}
$nosearchkeyworderror=__("You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for","AWPCP");

$checktheform="<script type=\"text/javascript\">
	function checkform()
	{
		var the=document.myform;
		if (the.keywordphrase.value==='')
		{
			if( (the.searchname.value==='') && (the.searchcity.value==='') && (the.searchstate.value==='') && (the.searchcountry.value==='') && (the.searchcountyvillage.value==='') && (the.searchcategory.value==='') && (the.searchpricemin.value==='') && (the.searchpricemax.value==='') )
			{
				alert('$nosearchkeyworderror');
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

					$clearthesessionlink="<p>";
					$clearthesessionlink.=__("You are searching in","AWPCP");
					$clearthesessionlink.=": $searchinginregion";
					$clearthesessionlink.="  <a href=\"$url_searchpage".$awpcpquerymark."a=cregs\">Search in different location</a></p>";
				}
				else
				{
					$clearthesessionlink='';
				}

if(!isset($message) || empty($message))
{
	$message="<p>";
	$message.=__("Use the form below to conduct a broad or narrow search. For a broader search enter fewer parameters. For a narrower search enter as many parameters as needed to limit your search to a specific criteria","AWPCP");
	$message.=" $clearthesessionlink</p>";
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

	echo "<div id=\"classiwrapper\">";
	$isadmin=checkifisadmin();
	awpcp_menu_items();
	if(isset($message) && !empty($message))
	{
		echo $message;
	}
	echo $checktheform;
	echo "<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
	echo "<input type=\"hidden\" name=\"a\" value=\"dosearch\">";
	echo "<p>";
	_e("Search for ads containing this word or phrase","AWPCP");
	echo ":<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"keywordphrase\" value=\"$keywordphrase\"></p>";
	echo "<p>";
	_e("Search in Category","AWPCP");
	echo "<br><select name=\"searchcategory\"><option value=\"\">";
	_e("Select Option","AWPCP");
	echo "</option>$allcategories</select></p>";
	echo "<p>";
	_e("For Ads Posted By","AWPCP");
	echo "<br/><select name=\"searchname\"><option value=\"\">";
	_e("Select Option","AWPCP");
	echo "</option>";
	create_ad_postedby_list($searchname);
	echo "</select></p>";


	if(get_awpcp_option(displaypricefield) == 1)
	{
		if( price_field_has_values() )
		{


			echo "<p>";
			_e("Min Price","AWPCP");
			echo "<select name=\"searchpricemin\"><option value=\"\">";
			_e("Select","AWPCP");
			echo "</option>";
			create_price_dropdownlist_min($searchpricemin);
			echo"</select>";
			_e("Max Price","AWPCP");
			echo "<select name=\"searchpricemax\"><option value=\"\">";
			_e("Select","AWPCP");
			echo "</option>";
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

	echo "<p>";
	_e("Refine to Country","AWPCP");
	echo "<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_countries_exist() )
			{

				echo "<select name=\"searchcountry\">";
				if(!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
				{
					echo "<option value=\"\">";
					_e("Select Option","AWPCP");
					echo "</option>";
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
								echo "<option value=\"\">";
								_e("Select Option","AWPCP");
								echo "</option>";
							}
							create_dropdown_from_current_countries($searchcountry);
							echo "</select>";
						}
						else
						{
							echo "(";
							_e("separate countries by commas","AWPCP");
							echo ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
							";
						}
					}
				}
				else
				{
						echo "(";
						_e("separate countries by commas","AWPCP");
						echo ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "(";
				_e("separate countries by commas","AWPCP");
				echo ")<br/>
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
						echo "<option value=\"\">";
						_e("Select Option","AWPCP");
						echo "</option>";
					}
					create_dropdown_from_current_countries($searchcountry);
					echo "</select>";
				}
				else
				{
					echo "(";
					_e("separate countries by commas","AWPCP");
					echo ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\">
					";
				}
			}
		}

	echo "</p>";
}

if(get_awpcp_option(displaystatefield) == 1)
{

	echo "<p>";
	_e("Refine to State","AWPCP");
	echo "<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_states_exist($adcontact_country) )
			{

				echo "<select name=\"searchstate\">";
				if(!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
				{
					echo "<option value=\"\">";
					_e("Select Option","AWPCP");
					echo "</option>";
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
						echo "(";
						_e("separate states by commas","AWPCP");
						echo ")<br/>
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
								echo "<option value=\"\">";
								_e("Select Option","AWPCP");
								echo "</option>";
							}
							create_dropdown_from_current_states($searchstate);
							echo "</select>";

						}
						else
						{
							echo "(";
							_e("separate states by commas","AWPCP");
							echo ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
							";
						}
					}
				}
				else
				{
						echo "(";
						_e("separate states by commas","AWPCP");
						echo ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "(";
				_e("separate states by commas","AWPCP");
				echo ")<br/>
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
						echo "<option value=\"\">";
						_e("Select Option","AWPCP");
						echo "</option>";
					}
					create_dropdown_from_current_states($searchstate);
					echo "</select>";

				}
				else
				{
					echo "(";
					_e("separate states by commas","AWPCP");
					echo ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\">
					";
				}
			}
		}

	echo "</p>";

}

if(get_awpcp_option(displaycityfield) == 1)
{
	echo "<p>";
	_e("Refine to City","AWPCP");
	echo "<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_cities_exist($adcontact_state) )
			{

				echo "<select name=\"searchcity\">";
				if(!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
				{
					echo "<option value=\"\">";
					_e("Select Option","AWPCP");
					echo "</option>";
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
						echo "(";
						_e("separate cities by commas","AWPCP");
						echo ")<br/>
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
								echo "<option value=\"\">";
								_e("Select Option","AWPCP");
								echo "</option>";
							}
							create_dropdown_from_current_cities($searchcity);
							echo "</select>";

						}
						else
						{
							echo "(";
							_e("separate cities by commas","AWPCP");
							echo ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\">
							";
						}
					}
				}
				else
				{
						echo "(";
						_e("separate cities by commas","AWPCP");
						echo ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "(";
				_e("separate cities by commas","AWPCP");
				echo ")<br/>
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
						echo "<option value=\"\">";
						_e("Select Option","AWPCP");
						echo "</option>";
					}
					create_dropdown_from_current_cities($searchcity);
					echo "</select>";

				}
				else
				{
					echo "(";
					_e("separate cities by commas","AWPCP");
					echo ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchcity\">
					";
				}
			}
		}

	echo "</p>";
}


if(get_awpcp_option(displaycountyvillagefield) == 1)
{
	echo "<p>";
	_e("Refine to County/Village/Other","AWPCP");
	echo "<br>";

		if($hasregionsmodule ==  1)
		{
			if( regions_counties_exist($adcontact_city) )
			{

				echo "<select name=\"searchcountyvillage\"><option value=\"\">";
				_e("Select Option","AWPCP");
				echo "</option>";
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
						echo "(";
						_e("separate counties by commas","AWPCP");
						echo ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
						";
					}
					elseif(get_awpcp_option('buildsearchdropdownlists'))
					{
						if( adstablehascounties() )
						{

							echo "<select name=\"searchcountyvillage\"><option value=\"\">";
							_e("Select Option","AWPCP");
							echo "</option>";
							create_dropdown_from_current_counties($searchcountyvillage);
							echo "</select>";
						}
						else
						{
							echo "(";
							_e("separate counties by commas","AWPCP");
							echo ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
							";
						}
					}
				}
				else
				{
						echo "(";
						_e("separate counties by commas","AWPCP");
						echo ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
						";
				}
			}

		}
		else
		{
			if(!get_awpcp_option('buildsearchdropdownlists'))
			{
				echo "(";
				_e("separate counties by commas","AWPCP");
				echo ")<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
				";
			}
			elseif(get_awpcp_option('buildsearchdropdownlists'))
			{
				if( adstablehascounties() )
				{

					echo "<select name=\"searchcountyvillage\"><option value=\"\">";
					_e("Select Option","AWPCP");
					echo "</option>";
					create_dropdown_from_current_counties($searchcountyvillage);
					echo "</select>";

				}
				else
				{
					echo "(";
					_e("separate counties by commas","AWPCP");
					echo ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\">
					";
				}
			}
		}

	echo "</p>";
}

	echo "<div align=\"center\"><input type=\"submit\" class=\"scbutton\" value=\"";
	_e("Start Search","AWPCP");
	echo "\"></div></form>";
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
		$theerrorslist="<h3>";
		$theerrorslist.=__("Cannot process your request due to the following error","AWPCP");
		$theerrorslist.=":</h3><ul>";
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
					$theerrorslist.="<li>";
					$theerrorslist.=__("You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for","AWPCP");
					$theerrorslist.="</li>";
			}

			if( !empty($searchpricemin) )
			{
				if( !is_numeric($searchpricemin) )
		   		{
						$error=true;
						$theerrorslist.="<li>";
						$theerrorslist.=__("You have entered an invalid minimum price. Make sure your price contains numbers only. Please do not include currency symbols","AWPCP");
						$theerrorslist.="</li>";
				}
			}

		  	if( !empty($searchpricemax) )
		  	{
		  		if(	!is_numeric($searchpricemax) )
		   		{
						$error=true;
						$theerrorslist.="<li>";
						$theerrorslist.=__("You have entered an invalid maximum price. Make sure your price contains numbers only. Please do not include currency symbols","AWPCP");
						$theerrorslist.="</li>";
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

		else
		{
			$where="disabled ='0'";

			if(isset($keywordphrase) && !empty($keywordphrase))
			{
				$where.=" AND MATCH (ad_title,ad_details) AGAINST (\"$keywordphrase\")";
			}

			if(isset($searchname) && !empty($searchname))
			{
				$where.=" AND ad_contact_name = '$searchname'";
			}

			if(isset($searchcity) && !empty($searchcity))
			{

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

		if(isset($searchcountry) && !empty($searchcountry))
		{
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

		if(isset($searchcategory) && !empty($searchcategory))
		{
			$where.=" AND ad_category_id = '$searchcategory' OR ad_category_parent_id = '$searchcategory'";
		}

		if(isset($searchpricemin) && !empty($searchpricemin))
		{
			$searchpricemincents=($searchpricemin * 100);
			$where.=" AND ad_item_price >= '$searchpricemincents'";
		}

		if(isset($searchpricemax) && !empty($searchpricemax))
		{
			$searchpricemaxcents=($searchpricemax * 100);
			$where.=" AND ad_item_price <= '$searchpricemaxcents'";
		}

		display_ads($where,$byl='',$hidepager='');

		}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: process first step of edit ad request
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function editadstep1($adaccesskey,$editemail,$awpcppagename)
{

		global $wpdb;
		$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_id,adterm_id FROM ".$table_name3." WHERE ad_key='$adaccesskey' AND ad_contact_email='$editemail'";
			if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		 	while ($rsrow=mysql_fetch_row($res))
		 	{
		 		list($adid,$adtermid)=$rsrow;
			}

			if(isset($adid) && !empty($adid))
			{
				load_ad_post_form($adid,$action='editad',$awpcppagename,$adtermid,$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
			}

			else
			{
				$message="<p class=\"messagealert\">";
				$message.=__("The information you have entered does not match the information on file. Please make sure you are using the same email address you used to post your ad and the exact access key that was emailed to you when you posted your ad","AWPCP");
				$message.="</p>";

				load_ad_edit_form($action='editad',$awpcppagename,$editemail,$adaccesskey,$message);
			}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2)
{

	global $wpdb,$imagesurl;
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
	$noadsinparentcatmsg='';

	$error=false;


	// Check for ad title
	if(!isset($adtitle) || empty($adtitle))
	{
		$error=true;
		$adtitlemsg="<li class=\"erroralert\">";
		$adtitlemsg.=__("You did not enter a title for your ad","AWPCP");
		$adtitlemsg.="</li>";
	}

	// Check for ad details
	if(!isset($addetails) || empty($addetails))
	{
		$error=true;
		$addetailsmsg="<li class=\"erroralert\">";
		$addetailsmsg.=__("You did not enter any text for your ad. Please enter some text for your ad","AWPCP");
		$addetailsmsg.="</li>";
	}

	// Check for ad category
	if(!isset($adcategory) || empty($adcategory))
	{
		$error=true;
		$adcategorymsg="<li class=\"erroralert\">";
		$adcategorymsg.=__("You did not select a category for your ad. Please select a category for your ad","AWPCP");
		$adcategorymsg.="</li>";
	}

	// Check for ad poster's name
	if(!isset($adcontact_name) || empty($adcontact_name))
	{
		$error=true;
		$adcnamemsg="<li class=\"erroralert\">";
		$adcnamemsg.=__("You did not enter your name. Your name is required","AWPCP");
		$adcnamemsg.="</li>";

	}

	// Check for ad poster's email address
	if(!isset($adcontact_email) || empty($adcontact_email))
	{
		$error=true;
		$adcemailmsg1=="<li class=\"erroralert\">";
		$adcemailmsg1.=__("You did not enter your email. Your email is required","AWPCP");
		$adcemailmsg1.="</li>";
	}

	// Check if email address entered is in a valid email address format
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $adcontact_email))
	{
		$error=true;
		$adcemailmsg2="<li class=\"erroralert\">";
		$adcemailmsg2.=__("The email address you entered was not a valid email address. Please check for errors and try again","AWPCP");
		$adcemailmsg2.="</li>";
	}

	// If phone field is checked and required make sure phone value was entered
	if((get_awpcp_option('displayphonefield') == 1)
	&&(get_awpcp_option('displayphonefieldreqop') == 1))
	{
		if(!isset($adcontact_phone) || empty($adcontact_phone))
		{
			$error=true;
			$adcphonemsg="<li class=\"erroralert\">";
			$adcphonemsg.=__("You did not enter your phone number. Your phone number is required","AWPCP");
			$adcphonemsg.="</li>";
		}
	}

	// If city field is checked and required make sure city value was entered
	if((get_awpcp_option('displaycityfield') == 1)
	&&(get_awpcp_option('displaycityfieldreqop') == 1))
	{
		if(!isset($adcontact_city) || empty($adcontact_city))
		{
			$error=true;
			$adcitymsg="<li class=\"erroralert\">";
			$adcitymsg.=__("You did not enter your city. Your city is required","AWPCP");
			$adcitymsg.="</li>";
		}
	}

	// If state field is checked and required make sure state value was entered
	if((get_awpcp_option('displaystatefield') == 1)
	&&(get_awpcp_option('displaystatefieldreqop') == 1))
	{
		if(!isset($adcontact_state) || empty($adcontact_state))
		{
			$error=true;
			$adstatemsg="<li class=\"erroralert\">";
			$adstatemsg.=__("You did not enter your state. Your state is required","AWPCP");
			$adstatemsg.="</li>";
		}
	}

	// If country field is checked and required make sure country value was entered
	if((get_awpcp_option('displaycountryfield') == 1)
	&&(get_awpcp_option('displaycountryfieldreqop') == 1))
	{
		if(!isset($adcontact_country) || empty($adcontact_country))
		{
			$error=true;
			$adcountrymsg="<li class=\"erroralert\">";
			$adcountrymsg.=__("You did not enter your country. Your country is required","AWPCP");
			$adcountrymsg.="</li>";
		}
	}

	// If county/village field is checked and required make sure county/village value was entered
	if((get_awpcp_option('displaycountyvillagefield') == 1)
	&&(get_awpcp_option('displaycountyvillagefieldreqop') == 1))
	{
		if(!isset($ad_county_village) || empty($ad_county_village))
		{
			$error=true;
			$adcountyvillagemsg="<li class=\"erroralert\">";
			$adcountyvillagemsg.=__("You did not enter your county/village. Your county/village is required","AWPCP");
			$adcountyvillagemsg.="</li>";
		}
	}

	if(get_awpcp_option('noadsinparentcat'))
	{

		if(!category_is_child($adcategory))
		{
			$awpcpcatname=get_adcatname($adcategory);
			$error=true;
			$noadsinparentcatmsg="<li class=\"erroralert\">";
			$noadsinparentcatmsg.=__("You can not list your ad in top level categories. You need to select a sub category of $awpcpcatname to list your ad under","AWPCP");
			$noadsinparentcatmsg.="</li>";
		}

	}

	if( $adaction == 'placead' )
	{
		// If running in pay mode make sure a payment method has been checked
		if((get_awpcp_option('freepay') == 1) && !is_admin())
		{
			if(get_adfee_amount($adterm_id) > 0)
			{
				if(!isset($adpaymethod) || empty($adpaymethod))
				{
					$error=true;
					$adpaymethodmsg="<li class=\"erroralert\">";
					$adpaymethodmsg.=__(">You did not select your payment method. The information is required.","AWPCP");
					$adpaymethodmsg.="</li>";
				}
			}
		}

		// If running in pay mode make sure an ad term has been selected
		if((get_awpcp_option('freepay') == 1) && !is_admin())
		{
			if(!($adaction == 'delete') || ($adaction == 'editad'))
			{
				if(!isset($adterm_id) || empty ($adterm_id))
				{
					$error=true;
					$adtermidmsg="<li class=\"erroralert\">";
					$adtermidmsg.=__("You did not select an ad term. The information is required","AWPCP");
					$adtermidmsg.="</li>";
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
			$aditempricemsg1="<li class=\"erroralert\">";
			$aditempricemsg1.=__("You did not enter the price of your item. The item price is required.","AWPCP");
			$aditempricemsg1.="</li>";
		}
	}

	// Make sure the item price is a numerical value
	if((get_awpcp_option('displaypricefield') == 1)
	&&(get_awpcp_option('displaypricefieldreqop') == 1))
	{
		if( !is_numeric($ad_item_price) )
		{
			$error=true;
			$aditempricemsg2="<li class=\"erroralert\">";
			$aditempricemsg2.=__("You have entered an invalid item price. Make sure your price contains numbers only. Please do not include currency symbols.","AWPCP");
			$aditempricemsg2.="</li>";
		}
	}

	// If website field is checked and required make sure website value was entered
	if((get_awpcp_option('displaywebsitefield') == 1)
	&&(get_awpcp_option('displaywebsitefieldreqop') == 1))
	{
		if(!isset($websiteurl) || empty($websiteurl))
		{
			$error=true;
			$websiteurlmsg1="<li class=\"erroralert\">";
			$websiteurlmsg1.=__("You did not enter your website address. Your website address is required.","AWPCP");
			$websiteurlmsg1.="</li>";
		}
	}

	//If they have submitted a website address make sure it is correctly formatted

	if(isset($websiteurl) && !empty($websiteurl) )
	{
		if( !isValidURL($websiteurl) )
		{
			$error=true;
			$websiteurlmsg2="<li class=\"erroralert\">";
			$websiteurlmsg2.=__("Your website address is not properly formatted. Please make sure you have included the http:// part of your website address","AWPCP");
			$websiteurlmsg2.="</li>";
		}
	}

	$thesum=($numval1 +  $numval2);

	if((get_awpcp_option('contactformcheckhuman') == 1) && !is_admin())
	{

		if(!isset($checkhuman) || empty($checkhuman))
		{
			$error=true;
			$checkhumanmsg="<li class=\"erroralert\">";
			$checkhumanmsg.=__("You did not solve the math problem. Please solve the math problem to proceed.","AWPCP");
			$checkhumanmsg.="</li>";
		}
		if($checkhuman != $thesum)
		{
			$error=true;
			$sumwrongmsg="<li class=\"erroralert\">";
			$sumwrongmsg.=__("Your solution to the math problem was incorrect. Please try again","AWPCP");
			$sumwrongmsg.="</li>";
		}
	}

	if($error)
	{
		$ermsg="<p><img src=\"$imagesurl/Warning.png\" border=\"0\" alt=\"Alert\" style=\"float:left;margin-right:10px;\">";
		$ermsg.=__("There has been an error found. Your message has not been sent. Please review the list of problems, correct them then try to send your message again","AWPCP");
		$ermsg.="</p><b>";
		$ermsg.=__("The errors","AWPCP");
		$ermsg.=":</b><br/><ul>";
		$ermsg.=__("$adtitlemsg $adcategorymsg $adcnamemsg $adcemailmsg1 $adcemailmsg2 $adcphonemsg $adcitymsg $adstatemsg $adcountrymsg $addetailsmsg $adpaymethodmsg $adtermidmsg $aditempricemsg1 $aditempricemsg2 $websiteurlmsg1 $websiteurlmsg2 $checkhumanmsg $sumwrongmsg $noadsinparentcatmsg","AWPCP");
		$ermsg.="</ul>";

		load_ad_post_form($adid,$action,$awpcppagename,$adterm_id,$editemail,$adkey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);
	}
	else
	{

		if($adaction == 'delete')
		{
			deletead($adid,$adkey,$editemail);
		}
		elseif($adaction == 'editad')
		{

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
				$message=__("The ad has been edited successfully.");
				$message.="<a href=\"?page=Manage1&offset=$offset&results=$results\">";
				$message.=__("Back to view listings");
				$message.="</a>";

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
						$messagetouser=__("Your changes have been saved");

						echo "<h3>$messagetouser</h3>";

						showad($adid,$omitmenu='');

					}
				}
				else
				{
					$messagetouser=__("Your changes have been saved");
					echo "<h3>$messagetouser</h3>";

					showad($adid,$omitmenu='');

				}
			}

		}
		else
		{

			//Begin processing new ad
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

				if(get_awpcp_option('freepay') == 1)
				{
					processadstep2_paymode($ad_id,$adterm_id,$key,$awpcpuerror='',$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod);
				}
				elseif((get_awpcp_option('freepay') == '0') && (get_awpcp_option('imagesallowdisallow') == 1))
				{
					processadstep2_freemode($ad_id,$adterm_id,$key,$awpcpuerror='',$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod);
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

function processadstep2_paymode($ad_id,$adterm_id,$adkey,$awpcpuerror,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod)
{
	$numimgsallowed='';

		if(get_awpcp_option('imagesallowdisallow') == 1)
		{
			$numimgsallowed=get_numimgsallowed($adterm_id);

			if( $numimgsallowed > 0 )
			{

				echo "<h2>";
				_e("Step 2 Upload Images","AWPCP");
				echo "</h2>";

				$totalimagesuploaded=get_total_imagesuploaded($ad_id);

				if($totalimagesuploaded < $numimgsallowed)
				{
					$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction='',$nextstep='payment',$adpaymethod,$awpcpuperror='');
				}
				else
				{
					$showimageuploadform="";
				}

				$uploadimagesform.="$showimageuploadform";
			}

				$classicontent=$uploadimagesform;
				echo "$classicontent";
		}
		else
		{
			processadstep3($ad_id,$adterm_id,$adkey,$adpaymethod);
		}
}

function processadstep2_freemode($ad_id,$adterm_id,$adkey,$awpcpuerror,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod)
{

	if( (get_awpcp_option('imagesallowdisallow') == 1) && ( get_awpcp_option('imagesallowedfree') > 0))
	{

		$imagesforfree=get_awpcp_option('imagesallowedfree');

		if($totalimagesuploaded < $imagesforfree)
		{
			$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction='',$nextstep='finish',$adpaymethod,$awpcpuperror='');
		}
		else
		{
			$showimageuploadform="";
		}

		$classicontent="$showimageuploadform";

		echo "$classicontent";
	}
	else
	{
		$awpcpadpostedmsg=__("Your ad has been submitted","AWPCP");

		if(get_awpcp_option('adapprove') == 1)
		{
			$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
			$awpcpadpostedmsg.="<p>";
			$awpcpadpostedmsg.=$awaitingapprovalmsg;
			$awpcpadpostedmsg.="</p>";
		}
		if(get_awpcp_option('imagesapprove') == 1)
		{
			$imagesawaitingapprovalmsg=__("If you have uploaded images your images will not show up until an admin has approved them.","AWPCP");
			$awpcpadpostedmsg.="<p>";
			$awpcpadpostedmsg.=$imagesawaitingapprovalmsg;
			$awpcpadpostedmsg.="</p>";
		}

		ad_success_email($ad_id,$txn_id='',$adkey,$awpcpadpostedmsg,$gateway='');
	}

}

function processadstep3($adid,$adterm_id,$key,$adpaymethod)
{
	global $wpdb;
	$table_name2 = $wpdb->prefix . "awpcp_adfees";

	$permastruc=get_option(permalink_structure);
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);

	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$paymentthankyoupagename=sanitize_title(get_awpcp_option('paymentthankyoupagename'), $post_ID='');
	$paymentthankyoupageid=awpcp_get_page_id($paymentthankyoupagename);
	$paymentcancelpagename=sanitize_title(get_awpcp_option('paymentcancelpagename'), $post_ID='');
	$paymentcancelpageid=awpcp_get_page_id($paymentcancelpagename);

	$base=get_option('siteurl');
	$custom="$adid";
	$custom.="_";
	$custom.="$key";

	////////////////////////////////////////////////////////////////////////////
	// Step:3 Create/Display payment page
	////////////////////////////////////////////////////////////////////////////

	$query="SELECT adterm_name,amount,rec_period FROM ".$table_name2." WHERE adterm_id='$adterm_id'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res))
	{
		list($adterm_name,$amount,$recperiod)=$rsrow;
	}


	if($amount <= 0)
	{
		$showpaybutton='';
	}

	else
	{
		$showpaybutton="<h2>";
		$showpaybutton.=__("Step 3 Payment","AWPCP");
		$showpaybutton.="</h2><p>";
		$showpaybutton.=__("Please click the button below to submit payment for your ad listing","AWPCP");
		$showpaybutton.="</p>";

		////////////////////////////////////////////////////////////////////////////
		// Print the paypal button option if paypal is activated
		////////////////////////////////////////////////////////////////////////////
		if($adpaymethod == 'paypal')
		{

			$awpcppaypalpaybutton=awpcp_displaypaymentbutton_paypal($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);

			$showpaybutton.="$awpcppaypalpaybutton";

		} // End if ad payment is paypal

		/////////////////////////////////////////////////////////////////////////////
		// Print the  2Checkout button option if 2Checkout is activated
		/////////////////////////////////////////////////////////////////////////////

		elseif($adpaymethod == '2checkout')
		{

			$awpcptwocheckoutpaybutton=awpcp_displaypaymentbutton_twocheckout($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);
			$showpaybutton.="$awpcptwocheckoutpaybutton";


		} // End if ad payment is 2checkout

	} // End if the fee amount is not a zero value

	// Show page based on if amount is zero or payment needs to be made
	if( $amount <= 0 )
	{
		$finishbutton="<p>";
		$finishbutton.=__("Please click the finish button to complete the process of submitting your listing","AWPCP");
		$finishbutton.="</p>
		<form method=\"post\" id=\"awpcpui_process\">
		<input type=\"hidden\" name=\"a\" value=\"adpostfinish\">
		<input type=\"hidden\" name=\"ad_id\" value=\"$ad_id\" />
		<input type=\"hidden\" name=\"adkey\" value=\"$key\" />
		<input type=\"hidden\" name=\"adterm_id\" value=\"$adterm_id\" />
		<input type=\"Submit\" value=\"";
		$finishbutton.=__("Finish","AWPCP");
		$finishbutton.="\"/></form>";
		$displaypaymentform="$finishbutton";
	}
	else
	{
		$displaypaymentform.="$showpaybutton";
	}

	////////////////////////////////////////////////////////////////////////////
	// Display the content
	////////////////////////////////////////////////////////////////////////////

	$adpostform_content=$displaypaymentform;
	echo "$adpostform_content";

}

function awpcp_displaypaymentbutton_paypal($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base)
{
	global $imagesurl;

	$showpaybuttonpaypal="";

	if( get_awpcp_option('seofriendlyurls') )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$codepaymentthankyou="<input type=\"hidden\" name=\"return\" value=\"$quers/$paymentthankyoupagename/$custom\" />";
			$codepaymentnotifyurl="<input type=\"hidden\" name=\"notify_url\" value=\"$quers/$paymentthankyoupagename\" />";
			$codepaymentcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"$quers/$paymentcancelpagename/$custom\" />";
		}
		else
		{
			$codepaymentthankyou="<input type=\"hidden\" name=\"return\" value=\"$quers/?page_id=$paymentthankyoupageid&i=$custom\" />";
			$codepaymentnotifyurl="<input type=\"hidden\" name=\"notify_url\" value=\"$quers/?page_id=$paymentthankyoupageid\" />";
			$codepaymentcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"$quers/?page_id=$paymentcancelpageid&i=$custom\" />";
		}
	}
	elseif(!( get_awpcp_option('seofriendlyurls') ) )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$codepaymentthankyou="<input type=\"hidden\" name=\"return\" value=\"$quers/$paymentthankyoupagename/$custom\" />";
			$codepaymentnotifyurl="<input type=\"hidden\" name=\"notify_url\" value=\"$quers/$paymentthankyoupagename\" />";
			$codepaymentcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"$quers/$paymentcancelpagename/$custom\" />";
		}
		else
		{
			$codepaymentthankyou="<input type=\"hidden\" name=\"return\" value=\"$quers/?page_id=$paymentthankyoupageid&i=$custom\" />";
			$codepaymentnotifyurl="<input type=\"hidden\" name=\"notify_url\" value=\"$quers/?page_id=$paymentthankyoupageid\" />";
			$codepaymentcancel="<input type=\"hidden\" name=\"cancel_return\" value=\"$quers/?page_id=$paymentcancelpageid&i=$custom\" />";
		}
	}

	if(get_awpcp_option('paylivetestmode') == 1)
	{
		$paypalurl="https://www.sandbox.paypal.com/cgi-bin/webscr";
	}
	else
	{
		$paypalurl="http://www.paypal.com/cgi-bin/webscr";
	}

	$showpaybuttonpaypal.="<form action=\"$paypalurl\" method=\"post\">";

	if(get_awpcp_option('paypalpaymentsrecurring'))
	{
		$paypalcmdvalue="<input type=\"hidden\" name=\"cmd\" value=\"_xclick-subscriptions\" />";
	}
	else
	{
		$paypalcmdvalue="<input type=\"hidden\" name=\"cmd\" value=\"_xclick\" />";
	}

	$showpaybuttonpaypal.="$paypalcmdvalue";

	if(get_awpcp_option('paylivetestmode') == 1)
	{
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"test_ipn\" value=\"1\" />";
	}

	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"business\" value=\"".get_awpcp_option('paypalemail')."\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />";
	$showpaybuttonpaypal.="$codepaymentthankyou";
	$showpaybuttonpaypal.="$codepaymentcancel";
	$showpaybuttonpaypal.="$codepaymentnotifyurl";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"no_note\" value=\"1\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"quantity\" value=\"1\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"no_shipping\" value=\"1\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"rm\" value=\"2\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"item_name\" value=\"$adterm_name\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"item_number\" value=\"$adterm_id\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"amount\" value=\"$amount\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"currency_code\" value=\"".get_awpcp_option('paypalcurrencycode')."\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"custom\" value=\"$custom\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"src\" value=\"1\" />";
	$showpaybuttonpaypal.="<input type=\"hidden\" name=\"sra\" value=\"1\" />";
	if(get_awpcp_option('paypalpaymentsrecurring'))
	{
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"a3\" value=\"$amount\">";
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"p3\" value=\"$recperiod\">";
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"t3\" value=\"D\">";
	}
	//$showpaybuttonpaypal.="<input class=\"button\" type=\"submit\" value=\"";
	//$showpaybuttonpaypal.=__("Pay With PayPal","AWPCP");
	//$showpaybuttonpaypal.="\">";
	$showpaybuttonpaypal.="<input type=\"image\" src=\"$imagesurl/paypalbuynow.gif\" border=\"0\" name=\"submit\" alt=\"";
	$showpaybuttonpaypal.=__("Make payments with PayPal - it's fast, free and secure!","AWPCP");
	$showpaybuttonpaypal.="\">";
	$showpaybuttonpaypal.="</form>";

	return $showpaybuttonpaypal;

}

function awpcp_displaypaymentbutton_twocheckout($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base)
{

	global $imagesurl;
	$showpaybuttontwocheckout="";

	if( get_awpcp_option('seofriendlyurls') )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$x_receipt_link_url="$quers/$paymentthankyoupagename/$custom";
		}
		else
		{
			$x_receipt_link_url="$quers/?page_id=$paymentthankyoupageid&i=$custom";
		}
	}
	elseif(!( get_awpcp_option('seofriendlyurls') ) )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$x_receipt_link_url="$quers/$paymentthankyoupagename/$custom";
		}
		else
		{
			$x_receipt_link_url="$quers/?page_id=$paymentthankyoupageid&i=$custom";
		}
	}

	if(get_awpcp_option('twocheckoutpaymentsrecurring'))
	{
		$x_login_sid="<input type='hidden' name=\"sid\" value=\"".get_awpcp_option('2checkout')."\" >";
	}
	else
	{
		$x_login_sid="<input type=\"hidden\" name=\"x_login\" value=\"".get_awpcp_option('2checkout')."\" />";
	}

	$showpaybuttontwocheckout.="<form action=\"https://www2.2checkout.com/2co/buyer/purchase\" method=\"post\">";
	$showpaybuttontwocheckout.="$x_login_sid";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"id_type\" value=\"1\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"fixed\" value=\"Y\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"pay_method\" value=\"CC\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_Receipt_Link_URL\" value=\"$x_receipt_link_url\">";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_invoice_num\" value=\"1\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_amount\" value=\"$amount\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_prod\" value=\"$adterm_id\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_name\" value=\"$adterm_name\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_description\" value=\"$adterm_name\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_tangible\" value=\"N\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_item_number\" value=\"$adterm_id\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_custom\" value=\"$custom\" />";

	if(get_awpcp_option('twocheckoutpaymentsrecurring'))
	{
		$showpaybuttontwocheckout.="<input type='hidden' name=\"quantity\" value='1' >";
		$showpaybuttontwocheckout.="<input type='hidden' name=\"product_id\" value=\"".get_2co_prodid($adterm_id)."\" />";
		$showpaybuttontwocheckout.="<input type='hidden' name=\"x_twocorec\" value=\"1\" />";
	}

	if(get_awpcp_option('paylivetestmode') == 1)
	{
		$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"demo\" value=\"Y\" />";
	}
	//$showpaybuttontwocheckout.="<input name=\"submit\" class=\"button\" type=\"submit\" value=\"";
	//$showpaybuttontwocheckout.=__("Pay With 2Checkout","AWPCP");
	$showpaybuttontwocheckout.="<input type=\"image\" src=\"$imagesurl/buybow2checkout.gif\" border=\"0\" name=\"submit\" alt=\"";
	$showpaybuttontwocheckout.=__("Pay With 2Checkout","AWPCP");
	$showpaybuttontwocheckout.="\" /></form>";

	return $showpaybuttontwocheckout;
}

function display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuperror)
{

	$awpcp_image_upload_form='';
	$totalimagesuploaded=0;

	$max_image_size=get_awpcp_option('maximagesize');

	if(get_awpcp_option('freepay') == 1)
	{

		$numimgsallowed=get_numimgsallowed($adterm_id);
	}
	else
	{
		$numimgsallowed=get_awpcp_option('imagesallowedfree');
	}

	if(adidexists($ad_id))
	{
		$totalimagesuploaded=get_total_imagesuploaded($ad_id);
	}

	$numimgsleft=($numimgsallowed - $totalimagesuploaded);

	$showimageuploadform="<p>";
	$showimageuploadform.=__("Image slots available","AWPCP");
	$showimageuploadform.="[<b>$numimgsleft</b>]";
	$showimageuploadform.="</p>";

	if(get_awpcp_option('imagesapprove') == 1)
	{
		$showimageuploadform.="<p>";
		$showimageuploadform.=__("Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it","AWPCP");
		$showimageuploadform.="</p>";

	}

	if(isset($awpcpuperror) && !empty($awpcpuperror))
	{
		$showimageuploadform.="<p>";
		foreach($awpcpuperror as $theawpcpuerror)
		{
			$showimageuploadform.=$theawpcpuerror;
		}
		$showimageuploadform.="</p>";
	}

	$showimageuploadform.="<div class=\"theuploaddiv\">
	<form id=\"AWPCPForm1\" name=\"AWPCPForm1\" method=\"post\" ENCTYPE=\"Multipart/form-data\">
	<p id=\"showhideuploadform\">
	<input type=\"hidden\" name=\"ADID\" value=\"$ad_id\" />
	<input type=\"hidden\" name=\"ADTERMID\" value=\"$adterm_id\" />
	<input type=\"hidden\" name=\"nextstep\" value=\"$nextstep\" />
	<input type=\"hidden\" name=\"adpaymethod\" value=\"$adpaymethod\" />
	<input type=\"hidden\" name=\"adaction\" value=\"$adaction\" />
	<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />
	<input type=\"hidden\" name=\"a\" value=\"awpcpuploadfiles\" />";
	$showimageuploadform.=__("If adding images to your ad, select your image from your hard disk","AWPCP");
	$showimageuploadform.=":<br/><br/>";

	for ($i=0;$i<$numimgsleft;$i++)
	{
		$uploadinput="<div class=\"uploadform\"><input type=\"file\" name=\"AWPCPfileToUpload$i\" id=\"AWPCPfileToUpload$i\" size=\"18\" />
		 </div>";
		$showimageuploadform.="$uploadinput";
	}

	$showimageuploadform.="</p><p style=\"clear:both;text-align:center;\"><input type=\"submit\" value=\"";
	$showimageuploadform.=__("Upload Selected Files","AWPCP");
	$showimageuploadform.="\" class=\"button\" id=\"awpcp_buttonForm\" /></p>";
	$showimageuploadform.="</form>";
	$showimageuploadform.="</div>";

	$awpcp_image_upload_form=$showimageuploadform;

	$awpcp_image_upload_form.="<div class=\"fixfloat\"></div>";
	$awpcp_image_upload_form.="<div class=\"finishbutton\"><div class=\"finishbuttonleft\">";

	if($nextstep == 'payment')
	{
		$clicktheword1=__("Go To Next Step");$clicktheword2=__("continue");
	}
	elseif($nextstep == 'finish')
	{
		$clicktheword1=__("Finish");$clicktheword2=__("complete");
	}
	else
	{
		$clicktheword1=__("Finish");$clicktheword2=__("complete");
	}

	$awpcp_image_upload_form.=__("<p>If you prefer not to upload any images please click the $clicktheword1 button to $clicktheword2 this process.</p>","AWPCP");
	$awpcp_image_upload_form.="</div><div class=\"finishbuttonright\">";

			$finishbutton="
				<form method=\"post\" id=\"awpcpui_process\">";
				if($nextstep == 'payment')
				{
					$finishbutton.="<input type=\"hidden\" name=\"a\" value=\"loadpaymentpage\">";
					$finishbutton.="<input type=\"hidden\" name=\"adpaymethod\" value=\"$adpaymethod\">";
				}
				elseif($nextstep == 'finish')
				{
					$finishbutton.="<input type=\"hidden\" name=\"a\" value=\"adpostfinish\">";
				}
				else
				{
					$finishbutton.="<input type=\"hidden\" name=\"a\" value=\"adpostfinish\">";
				}
				$finishbutton.="
				<input type=\"hidden\" name=\"ad_id\" value=\"$ad_id\" />
				<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />
				<input type=\"hidden\" name=\"adaction\" value=\"$adaction\" />
				<input type=\"hidden\" name=\"adterm_id\" value=\"$adterm_id\" />
				<input type=\"Submit\" class=\"button\" value=\"";
				if($nextstep == 'payment')
				{
					$finishbutton.=__("Go To Next Step","AWPCP");
				}
				elseif($nextstep == 'payment')
				{
					$finishbutton.=__("Finish","AWPCP");
				}
				else
				{
					$finishbutton.=__("Finish","AWPCP");
				}
				$finishbutton.="\"/>
				</form>";
				$awpcp_image_upload_form.="$finishbutton";
				$awpcp_image_upload_form.="</div></div>";



	return $awpcp_image_upload_form;

}


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

			$imagecode="<h2>";
			$imagecode.=__("Manage your ad images","AWPCP");
			$imagecode.="</h2>";

			if(!isset($adid) || empty($adid))
			{
				$imagecode.=__("There has been a problem encountered. The system is unable to continue processing the task in progress. Please start over and if you encounter the problem again, please contact a system administrator.","AWPCP");
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

					$imagecode.="<p>";
					$imagecode.=__("Your images are displayed below. The total number of images you are allowed is","AWPCP");
					$imagecode.=": $numimgsallowed</p>";

					if(($numimgsallowed - $totalimagesuploaded) == '0')
					{
						$imagecode.="<p>";
						$imagecode.=__("If you want to change your images you will first need to delete the current images","AWPCP");
						$imagecode.="</p>";
					}

					if(get_awpcp_option('imagesapprove') == 1)
					{
						$imagecode.="<p>";
						$imagecode.=__("Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it","AWPCP");
						$imagecode.="</p>";
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
							$imgstat="<font style=\"font-size:smaller;\">";
							$imgstat.=__("Disabled","AWPCP");
							$imgstat.="</font>";
						}

						$dellink="<a href=\"?a=dp&k=$ikey\">";
						$dellink.=__("Delete","AWPCP");
						$dellink.="</a>";
						$theimage.="<li><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img $transval src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a><br/>$dellink $imgstat</li>";
					}

					$imagecode.=$theimage;
					$imagecode.="</ul></div></div>";
					$imagecode.="<div class=\"fixfloat\"></div>";
				}

				elseif($totalimagesuploaded < 1)
				{
					$imagecode.=__("You do not currently have any images uploaded. Use the upload form below to upload your images. If you do not wish to upload any images simply click the finish button. If uploading images, be careful not to click the finish button until after you've uploaded all your images","AWPCP");
				}


				if($totalimagesuploaded < $numimgsallowed)
				{
					$max_image_size=get_awpcp_option('maximagesize');

					$showimageuploadform=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction='editad',$nextstep='finish',$adpaymethod='',$awpcpuperror);
				}
				else
				{
					$showimageuploadform="";
				}

			}

				$imagecode.=$showimageuploadform;
				$imagecode.="<div class=\"fixfloat\"></div>";
			}

				echo "<div id=\"classiwrapper\">$imagecode</div>";
		}



}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function deletepic($picid,$adid,$adtermid,$adkey,$editemail)
{

	$isadmin=checkifisadmin();
	$savedemail=get_adposteremail($adid);

	if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
	{
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

		if($isadmin == 1 && is_admin())
		{
			$message=__("The image has been deleted","AWPCP");
			return $message;
		}

		else {

			editimages($adtermid,$adid,$adkey,$editemail);
		}

	}
	else
	{
		_e("Problem encountered. Cannot complete request","AWPCP");
	}
echo "</div>";
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: delete ad by specified ad ID
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function deletead($adid,$adkey,$editemail)
{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
		$quers=setup_url_structure($awpcppagename);

		$isadmin=checkifisadmin();


			if(get_awpcp_option('onlyadmincanplaceads') && ($isadmin != '1'))
			{
				$awpcpreturndeletemessage=__("You do not have permission to perform the function you are trying to perform. Access to this page has been denied","AWPCP");
			}
			else
			{

				global $wpdb,$nameofsite;
				$table_name3 = $wpdb->prefix . "awpcp_ads";
				$table_name5 = $wpdb->prefix . "awpcp_adphotos";
				$savedemail=get_adposteremail($adid);


				if((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
				{

					// Delete ad image data from database and delete images from server

					$query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$adid'";
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

						$query="DELETE FROM ".$table_name5." WHERE ad_id='$adid'";
						if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						// Now delete the ad
						$query="DELETE FROM  ".$table_name3." WHERE ad_id='$adid'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						if(($isadmin == 1) && is_admin())
						{
							$message=__("The ad has been deleted","AWPCP");
							return $message;
						}

						else
						{
							$awpcpreturndeletemessage=__("Your ad details and any photos you have uploaded have been deleted from the system","AWPCP");
						}
				}
				else
				{
					$awpcpreturndeletemessage=__("Problem encountered. Cannot complete  request","AWPCP");
				}
		}

					echo "<div id=\"classiwrapper\">";
					awpcp_menu_items();
					echo "<p>";
					echo $awpcpreturndeletemessage;
					echo "</p>";
					echo "</div>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Process PayPal Payment
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function do_paypal($payment_status,$item_name,$item_number,$receiver_email,$quantity,$business,$mcgross,$payment_gross,$txn_id,$fee,$custom)
{

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";
			$table_name2 = $wpdb->prefix . "awpcp_adfees";
			$gateway="Paypal";
			$pbizid=get_awpcp_option('paypalemail');

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
				$message=__("The amount you have paid does not match any of our listing fee amounts. Please contact us to clarify the problem.","AWPCP");
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
					$message=__("There was an error process your transaction. If funds have been deducted from your account they have not been processed to our account. You will need to contact PayPal about the matter.","AWPCP");
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
						$message=__("It appears this transaction has already been processed. If you do not see your ad in the system please contact the site adminstrator for assistance.","AWPCP");
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

						$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled',ad_fee_paid='".addslashes_mq($mcgross)."' WHERE ad_id='$ad_id' AND ad_key='$key'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						if (isset($item_number) && !empty($item_number))
						{

							$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
						}


						$message=__("Payment has been completed","AWPCP");
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

							if(get_awpcp_option('disablependingads') == 0)
							{
								$disabled='1';
							}
							else
							{
								$disabled='0';
							}

							$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled',ad_fee_paid='".addslashes_mq($mcgross)."' WHERE ad_id='$ad_id' AND ad_key='$key'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							if (isset($item_number) && !empty($item_number))
							{

								$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($item_number)."'";
								if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
							}


							$message=__("Payment is pending","AWPCP");
							ad_success_email($ad_id,$txn_id,$key,$message,$gateway);

					}
					else
					{
						$message=__("There appears to be a problem. Please contact customer service if you are viewing this page after having made a payment. If you have not tried to make a payment and you are viewing this page, it means you have arrived at this page in error.","AWPCP");
						abort_payment($message,$ad_id,$txn_id,$gateway);

					}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_2checkout($custom,$x_amount,$x_item_number,$x_trans_id,$x_Login)
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name2 = $wpdb->prefix . "awpcp_adfees";
	$gateway="2checkout";
	$pbizid=get_awpcp_option('2checkout');

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

			$adtermid=$x_item_number;

			////////////////////////////////////////////////////////////////////////////////////
			// Set the value of field: premiumstart
			////////////////////////////////////////////////////////////////////////////////////

			$ad_startdate=mktime();

			////////////////////////////////////////////////////////////////////////////////////
			// Determine when ad term ends based on start time and term length
			////////////////////////////////////////////////////////////////////////////////////

			$days=get_num_days_in_term($adtermid);




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

		if(!(in_array(number_format($x_amount,2),$myamounts)))
		{
			$message=__("The amount you have paid does not match any of our listing fee amounts. Please contact us to clarify the problem","AWPCP");
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

			if (!(strcasecmp($x_Login, $pbizid) == 0))
			{
				$message=__("There was an error process your transaction. If funds have been deducted from your account they have not been processed to our account. You will need to contact 2Checkout about the matter","AWPCP");
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
					$message=__("It appears this transaction has already been processed. If you do not see your ad in the system please contact the site adminstrator for assistance","AWPCP");
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

					if( (get_awpcp_option('adapprove') == 1) || (get_awpcp_option('disablependingads') == 0))
					{
						$disabled='1';
					}
					else
					{
						$disabled='0';
					}

					$query="UPDATE  ".$table_name3." SET adterm_id='".addslashes_mq($x_item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$x_trans_id',payment_status='Completed',payment_gateway='2Checkout',disabled='$disabled',ad_fee_paid='".addslashes_mq($x_amount)."' WHERE ad_id='$ad_id' AND ad_key='$key'";
					if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					if (isset($item_number) && !empty($item_number))
					{
						$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='".addslashes_mq($x_item_number)."'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
					}


					$message=__("Payment Status","AWPCP");
					$message.=":";
					$message.=__("Completed","AWPCP");
					ad_success_email($ad_id,$x_trans_id,$key,$message,$gateway);


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: email adminstrator and ad poster if there was a problem encountered when paypal payment procedure was attempted
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function abort_payment($message,$ad_id,$transactionid,$gateway)
{
	//email the administrator and the user to notify that the payment process was aborted

	global $nameofsite,$siteurl,$thisadminemail;
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option(permalink_structure);
	$quers=setup_url_structure($awpcppagename);

	$modtitle=cleanstring($listingtitle);
	$modtitle=add_dashes($modtitle);

	$url_showad=url_showad($ad_id);
	$adlink="$url_showad";

	$adposteremail=get_adposteremail($ad_id);
	$admostername=get_adpostername($ad_id);
	$listingtitle=get_adtitle($ad_id);
	$awpcpabortemailsubjectuser=get_awpcp_option('paymentabortedsubjectline');

	$subjectadmin=__("Customer attempt to pay for classified ad listing has failed","AWPCP");
	$awpcpabortemailbody=get_awpcp_option('paymentabortedmessagebody');
	$awpcpbreak1="<br/>";
	$awpcpbreak2="<br/><br/>";
	$awpcpabortemailbody.=__("Additional Details","AWPCP");
	$awpcpabortemailbody.="$awpcpbreak2";
	$awpcpabortemailbody.="$message";
	$awpcpabortemailbody.="$awpcpbreak2";
	if(isset($transactionid) && !empty($transactionid))
	{
		$awpcpabortemailbody.=__("Transaction ID","AWPCP");
		$awpcpabortemailbody.=": $transactionid";
		$awpcpabortemailbody.="$awpcpbreak2";
	}

	$awpcpabortemailbody.="$nameofsite";
	$awpcpabortemailbody.="$awpcpbreak1";
	$awpcpabortemailbody.="$siteurl";

	$mailbodyadmin=__("Dear Administrator","AWPCP");
	$mailbodyadmin.="$awpcpbreak2";
	$mailbodyadmin.=__("There was a problem encountered during a customer's attempt to submit payment for a classified ad listing","AWPCP");
	$mailbodyadmin.="$awpcpbreak2";
	$mailbodyadmin.=__("Additional Details","AWPCP");
	$mailbodyadmin.="$awpcpbreak2";
	$mailbodyadmin.=__("Listing Title","AWPCP");
	$mailbodyadmin.=": $listingtitle";
	$mailbodyadmin.="$awpcpbreak2";
	$mailbodyadmin.=__("Listing ID","AWPCP");
	$mailbodyadmin.="$ad_id";
	$mailbodyadmin.="$awpcpbreak2";
	$mailbodyadmin.=__("Listing URL","AWPCP");
	$mailbodyadmin.=": $adlink";
	$mailbodyadmin.="$awpcpbreak2";
	if(isset($transactionid) && !empty($transactionid))
	{
		$mailbodyadmin.=__("Payment transaction ID","AWPCP");
		$mailbodyadmin.=": $transactionid";
		$mailbodyadmin.="$awpcpbreak2";
	}
			$from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

			//email the buyer
			if(send_email($thisadminemail,$adposteremail,$awpcpabortemailsubjectuser,$awpcpabortemailbody,false))
			{
				$awpcpabortemailbody=str_replace("$awpcpbreak1", "\n", $awpcpabortemailbody);
				$awpcpabortemailbody=str_replace("$awpcpbreak2", "\n\n", $awpcpabortemailbody);

				if(!(mail($adposteremail, $awpcpabortemailsubjectuser, $awpcpabortemailbody, $from_header)))
				{
					$awpcp_smtp_host = get_awpcp_option('smtphost');
					$awpcp_smtp_username = get_awpcp_option('smtpusername');
					$awpcp_smtp_password = get_awpcp_option('smtppassword');
					$awpcpabortemailbody=str_replace("$awpcpbreak1", "\n", $awpcpabortemailbody);
					$awpcpabortemailbody=str_replace("$awpcpbreak2", "\n\n", $awpcpabortemailbody);

					$headers = array ('From' => $from_header,
					  'To' => $adposteremail,
					  'Subject' => $subject);
					$smtp = Mail::factory('smtp',
					  array ('host' => $awpcp_smtp_host,
						'auth' => true,
						'username' => $awpcp_smtp_username,
						'password' => $awpcp_smtp_password));

					$mail = $smtp->send($adposteremail, $headers, $awpcpabortemailbody);

				}
			}

			//email the administrator
			if(send_email($thisadminemail,$thisadminemail,$subjectadmin, $mailbodyadmin,false))
			{
				$mailbodyadmin=str_replace("$awpcpbreak1", "\n", $mailbodyadmin);
				$mailbodyadmin=str_replace("$awpcpbreak2", "\n\n", $mailbodyadmin);

				if(!(mail($thisadminemail, $subjectadmin, $mailbodyadmin, $from_header)))
				{
					$awpcp_smtp_host = get_awpcp_option('smtphost');
					$awpcp_smtp_username = get_awpcp_option('smtpusername');
					$awpcp_smtp_password = get_awpcp_option('smtppassword');
					$mailbodyadmin=str_replace("$awpcpbreak1", "\n", $mailbodyadmin);
					$mailbodyadmin=str_replace("$awpcpbreak2", "\n\n", $mailbodyadmin);

					$headers = array ('From' => $from_header,
					  'To' => $thisadminemail,
					  'Subject' => $subjectadmin);
					$smtp = Mail::factory('smtp',
					  array ('host' => $awpcp_smtp_host,
						'auth' => true,
						'username' => $awpcp_smtp_username,
						'password' => $awpcp_smtp_password));

					$mail = $smtp->send($adposteremail, $headers, $mailbodyadmin);

				}
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

function ad_success_email($ad_id,$transactionid,$key,$message,$gateway)
{

	global $nameofsite,$siteurl,$thisadminemail;

	$adposteremail=get_adposteremail($ad_id);
	$adpostername=get_adpostername($ad_id);
	$listingtitle=get_adtitle($ad_id);
	$listingaddedsubject=get_awpcp_option('listingaddedsubject');
	$mailbodyuser=get_awpcp_option('listingaddedbody');

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option(permalink_structure);
	$quers=setup_url_structure($awpcppagename);

	$modtitle=cleanstring($listingtitle);
	$modtitle=add_dashes($modtitle);

	$url_showad=url_showad($ad_id);
	$adlink=$url_showad;

	$subjectadmin=__("New classified ad listing posted Test","AWPCP");
	$linebreak="<br/><br/>";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("Listing Title","AWPCP");
	$mailbodyuser.=": $listingtitle";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("Listing URL","AWPCP");
	$mailbodyuser.=": $adlink";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("Listing ID","AWPCP");
	$mailbodyuser.=": $ad_id";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("Listing Edit Email","AWPCP");
	$mailbodyuser.=": $adposteremail";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("Listing Edit Key","AWPCP");
	$mailbodyuser.=": $key";
	$mailbodyuser.=$linebreak;
	if (strcasecmp ($gateway, "paypal") == 0 || strcasecmp ($gateway, "2checkout") == 0)
	{
		$mailbodyuser.=__("Payment Transaction ID","AWPCP");
		$mailbodyuser.=": $transactionid";
		$mailbodyuser.=$linebreak;
	}
	$mailbodyuser.=__("Additional Details","AWPCP");
	$mailbodyuser.=$linebreak;
	$mailbodyuser.="$message";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("If you have questions about your listing contact","AWPCP");
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=": $thisadminemail";
	$mailbodyuser.=$linebreak;
	$mailbodyuser.=__("Thank you for your business","AWPCP");
	$mailbodyuser.=$linebreak;
	$mailbodyuser.="$siteurl";


	$mailbodyadmin=__("A new classifieds listing has been submitted. A copy of the details sent to the customer can be found below","AWPCP");
	$mailbodyadmin.="$linebreak$mailbodyuser$linebreak";


	$from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

	$messagetouser=__("Your ad has been submitted and an email has been sent to $adposteremail with information you will need to edit your listing.","AWPCP");

	if(get_awpcp_option('adapprove') == 1)
	{
		$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
		$messagetouser.="<p>$awaitingapprovalmsg</p>";
	}

	//email the buyer
	if(send_email($thisadminemail,$adposteremail,$listingaddedsubject,$mailbodyuser,true))
	{
		//email the administrator if the admin has this option set
		if(get_awpcp_option('notifyofadposted'))
		{
			$sentok2=send_email($thisadminemail,$thisadminemail,$subjectadmin,$mailbodyadmin,true);
		}

		$printmessagetouser="$messagetouser";
	}
	// If function send_mail did not work try function mail()
	else
	{
		$mailbodyuser=str_replace("$linebreak", "\n\n", $mailbodyuser);
		$mailbodyadmin=str_replace("$linebreak", "\n\n", $mailbodyadmin);


		if(mail($adposteremail, $listingaddedsubject, $mailbodyuser, $from_header))
		{
			//email the administrator if the admin has this option set using mail()
			if(get_awpcp_option('notifyofadposted'))
			{
				$sentok2=mail($thisadminemail,$subjectadmin,$mailbodyadmin,$from_header);
			}

			$printmessagetouser="$messagetouser";
		}
		else
		{
			// If neither send_email() nor mail() worked try smtp
			$awpcp_smtp_host = get_awpcp_option('smtphost');
			$awpcp_smtp_username = get_awpcp_option('smtpusername');
			$awpcp_smtp_password = get_awpcp_option('smtppassword');
			$mailbodyuser=str_replace("$linebreak", "\n\n", $mailbodyuser);
			$mailbodyadmin=str_replace("$linebreak", "\n\n", $mailbodyadmin);


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

				$mail = $smtp->send($sendtoemail, $headers, $mailbodyuser);

				if (PEAR::isError($mail))
				{
				 $printmessagetouser=__("Your ad has been submitted, but there was a problem encountered while trying to send your ad information to your email address. Please contact the site administrator to obtain your ad key for editing your ad if needed.","AWPCP");
				}
				else
				{
				 $printmessagetouser="$messagetouser";
				}
			}
			else
			{
				 $printmessagetouser=__("Your ad has been submitted, but there was a problem encountered while trying to send your ad information to your email address. Please contact the site administrator to obtain your ad key for editing your ad if needed.","AWPCP");
			}

		}

		$printmessagetouser="$messagetouser";

	}

	echo "<div id=\"classiwrapper\">";
		awpcp_menu_items();
		echo "<p>";
		echo $printmessagetouser;
		echo "</p>";
		echo "<h2>";
		_e("Sample of your ad","AWPCP");
		echo "</h2>";
		showad($ad_id,$omitmenu='1');
		echo "</div>";




}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: If user decides not to go through with paying for ad via paypal and clicks on cancel on the paypal website
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_cancelpayment ()
{
	$base=get_option('siteurl');
	$permastruc=get_option(permalink_structure);
	$quers=setup_url_structure($awpcppagename);
	$pathvaluecancelpayment=get_awpcp_option('pathvaluecancelpayment');

	echo "<div id=\"classiwrapper\">";

	if(isset($_REQUEST['i']) && !empty($_REQUEST['i'])){
	$adinfo=$_REQUEST['i'];}

	list($ad_id,$key) = split('[_]', $adinfo);

	if(!isset($ad_id) || empty($ad_id))
	{
			if(isset($permastruc) && !empty($permastruc))
			{
				$awpcpcancelpayment_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
				$awpcpcancelpayment_requested_url .= $_SERVER['HTTP_HOST'];
				$awpcpcancelpayment_requested_url .= $_SERVER['REQUEST_URI'];

				$awpcpparsedcancelpaymentURL = parse_url ($awpcpcancelpayment_requested_url);
				$awpcpsplitcancelpaymentPath = preg_split ('/\//', $awpcpparsedcancelpaymentURL['path'], 0, PREG_SPLIT_NO_EMPTY);

				$ad_id_key=$awpcpsplitcancelpaymentPath[$pathvaluecancelpayment];

				list($ad_id,$key)=split('[_]',$ad_id_key);

			}


		if(!isset($key) || empty($key))
		{
			if(isset($ad_id) && !empty($ad_id))
			{
				$key=get_adkey($ad_id);
			}
		}
	}

	$adterm_id=get_adterm_id($ad_id);
	$adterm_name=get_adterm_name($adterm_id);
	$amount=get_adfee_amount($adterm_id);
	$recperiod=get_fee_recperiod($adterm_id);
	$base=get_option('siteurl');
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');

	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$placeadpageid=awpcp_get_page_id($placeadpagename);
	$paymentthankyoupagename=sanitize_title(get_awpcp_option('paymentthankyoupagename'), $post_ID='');
	$paymentthankyoupageid=awpcp_get_page_id($paymentthankyoupagename);
	$paymentcancelpagename=sanitize_title(get_awpcp_option('paymentcancelpagename'), $post_ID='');
	$paymentcancelpageid=awpcp_get_page_id($paymentcancelpagename);

	$custom="$ad_id";
	$custom.="_";
	$custom.="$key";

	$showpaybuttonpaypal=awpcp_displaypaymentbutton_paypal($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);
	$showpaybutton2checkout=awpcp_displaypaymentbutton_twocheckout($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);

_e("You have chosen to cancel the payment process. Your ad cannot be activated until you pay the listing fee. You can click the link below to delete your ad information, or you can click the button to make your payment now","AWPCP");


$savedemail=get_adposteremail($ad_id);
$ikey="$ad_id";
$ikey.="_";
$ikey.="$key";
$ikey.="_";
$ikey.="$savedemail";

	if(isset($permastruc) && !empty($permastruc))
	{
		$url_deletead="$quers/$placeadpagename?a=deletead&k=$ikey";
	}
	else
	{
		$url_deletead="$quers/?page_id=$placeadpageid&a=deletead&k=$ikey";
	}

echo "<p><a href=\"$url_deletead\">";
_e("Delete Ad Details","AWPCP");
echo "</a></p>";
if( get_awpcp_option('activatepaypal') && (get_awpcp_option('freepay') == 1))
{
	echo "<p>$showpaybuttonpaypal</p>";
}
if( get_awpcp_option('activate2checkout') && (get_awpcp_option('freepay') == 1))
{
	echo "<p>$showpaybutton2checkout</p></div>";
}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Thank you page to display to user after successfully completing payment via paypal
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function paymentthankyou ()
{

	/*echo "<h1>Get Parameter/s:</h1>";
	echo "<pre>";
	if($_GET)
		print_r($_GET);
	else
		echo "There are no get parameters.";
	echo "</pre>";
	echo "<hr/>";
	echo "<h1>Post Parameter/s:</h1>";
	echo "<pre>";
	if($_POST)
		print_r($_POST);
	else
		echo "There are no post parameters.";
	echo "</pre>";
	die;*/

	$permastruc=get_option(permalink_structure);

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
	$x_item_number = $_POST['x_item_number'];
	$x_custom = $_POST['x_custom'];
	$x_buyer_mail = $_POST['email'];
	$x_twocorec = $_POST['x_twocorec'];
	$x_order_number = $_POST['order_number'];
	$x_sid=$_POST['sid'];

	if($x_response_code == 1)
	{
		$payment_verified=true;
		$awpcpayhandler="twocheckout";
	}

	elseif(isset($x_twocorec) && !empty($x_twocorec) && ($x_twocorec == 1))
	{
		$payment_verified=true;
		$awpcpayhandler="twocheckout";
	}

	if(!$awpcpayhandler == 'twocheckout' )
	{

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		$payment_verified=false;

		foreach ($_POST as $key => $value)
		{
			$value = urlencode(stripslashes_mq($value));
			$req .= "&$key=$value";
		}

		if(get_awpcp_option('paylivetestmode') == 1)
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
					$awpcpayhandler="paypal";
				}
				else
				{
					$pathvaluepaymentthankyou=get_awpcp_option('pathvaluepaymentthankyou');

					if(isset($_REQUEST['i']) && !empty($_REQUEST['i']))
					{
						$adinfo=$_REQUEST['i'];
					}

					list($ad_id,$key) = split('[_]', $adinfo);

					if(!isset($ad_id) || empty($ad_id))
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$awpcppaymentthankyou_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
							$awpcppaymentthankyou_requested_url .= $_SERVER['HTTP_HOST'];
							$awpcppaymentthankyou_requested_url .= $_SERVER['REQUEST_URI'];

							$awpcpparsedpaymentthankyouURL = parse_url ($awpcppaymentthankyou_requested_url);
							$awpcpsplitpaymentthankyouPath = preg_split ('/\//', $awpcpparsedpaymentthankyouURL['path'], 0, PREG_SPLIT_NO_EMPTY);

							$ad_id_key=$awpcpsplitpaymentthankyouPath[$pathvaluepaymentthankyou];

							list($ad_id,$key)=split('[_]',$ad_id_key);
						}

						if(!isset($key) || empty($key))
						{
							if(isset($ad_id) && !empty($ad_id))
							{
								$key=get_adkey($ad_id);
							}
						}
					}

					if(isset($ad_id) && !empty($ad_id))
					{
						$query="UPDATE  ".$table_name3." SET ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,payment_status='Completed' WHERE ad_id='$ad_id' AND ad_key='$key'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

						$adterm_id=get_adtermid($ad_id);

						if (isset($item_number) && !empty($item_number))
						{
							$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='$adterm_id'";
							if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
						}

						echo "<div id=\"classiwrapper\">";
						awpcp_menu_items();
						_e("Your listing had been submitted and your payment has been processed.","AWPCP");
						echo "<p><h2>";
						_e("A sample of your ad","AWPCP");
						echo "</p></h2>";
						showad($ad_id,$omitmenu='');
						echo "</div>";
					}
					else
					{
						echo "<div id=\"classiwrapper\">";
						awpcp_menu_items();
						_e("There was an error encountered completing the processing of payment for your ad. Please contact the site administrator for assistance","AWPCP");
						echo "</div>";
					}
				}
			}
		}


		if (($payment_verified) && ($awpcpayhandler == 'paypal'))
		{
			do_paypal($payment_status,$item_name,$item_number,$receiver_email,$quantity,$business,$mcgross,$payment_gross,$txn_id,$fee,$custom);
		}
		elseif( ($payment_verified) && ($awpcpayhandler == 'twocheckout') && ($x_twocorec != 1))
		{
			do_2checkout($x_custom,$x_amount,$x_item_number,$x_trans_id,$x_Login);
		}
		elseif( ($payment_verified) && ($awpcpayhandler == 'twocheckout') && ($x_twocorec == 1))
		{
			do_2checkout($x_custom,$x_amount,$x_item_number,$x_order_number,$x_sid);
		}
		else
		{
			$pathvaluepaymentthankyou=get_awpcp_option('pathvaluepaymentthankyou');

			if(isset($_REQUEST['i']) && !empty($_REQUEST['i']))
			{
				$adinfo=$_REQUEST['i'];
			}

			list($ad_id,$key) = split('[_]', $adinfo);

			if(!isset($ad_id) || empty($ad_id))
			{
					if(isset($permastruc) && !empty($permastruc))
					{
						$awpcppaymentthankyou_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
						$awpcppaymentthankyou_requested_url .= $_SERVER['HTTP_HOST'];
						$awpcppaymentthankyou_requested_url .= $_SERVER['REQUEST_URI'];

						$awpcpparsedpaymentthankyouURL = parse_url ($awpcppaymentthankyou_requested_url);
						$awpcpsplitpaymentthankyouPath = preg_split ('/\//', $awpcpparsedpaymentthankyouURL['path'], 0, PREG_SPLIT_NO_EMPTY);

						$ad_id_key=$awpcpsplitpaymentthankyouPath[$pathvaluepaymentthankyou];

						list($ad_id,$key)=split('[_]',$ad_id_key);

					}


				if(!isset($key) || empty($key))
				{
					if(isset($ad_id) && !empty($ad_id))
					{
						$key=get_adkey($ad_id);
					}
				}
			}

				if(isset($ad_id) && !empty($ad_id))
				{
					$query="UPDATE  ".$table_name3." SET ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,payment_status='Completed' WHERE ad_id='$ad_id' AND ad_key='$key'";
					if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					$adterm_id=get_adtermid($ad_id);

					if (isset($item_number) && !empty($item_number))
					{
						$query="UPDATE ".$table_name2." SET buys=buys+1 WHERE adterm_id='$adterm_id'";
						if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
					}

				echo "<div id=\"classiwrapper\">";
				awpcp_menu_items();
				_e("Your listing had been submitted and your payment has been processed.","AWPCP");
				echo "<p><h2>";
				_e("A sample of your ad","AWPCP");
				echo "</p></h2>";
				showad($ad_id,$omitmenu='');
				echo "</div>";

				}
				else
				{
					echo "<div id=\"classiwrapper\">";
					awpcp_menu_items();
					_e("There was an error encountered completing the processing of payment for your ad. Please contact the site administrator for assistance","AWPCP");
					echo "</div>";
				}
		}


}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display listing of ad titles when browse ads is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function display_ads($where,$byl,$hidepager)
{
	global $wpdb,$imagesurl,$hasregionsmodule,$awpcp_plugin_path;
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$showadspagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');
	$browseadspagename = sanitize_title(get_awpcp_option('browseadspagename'), $post_ID='');
	$browsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='');
	$awpcp_browsecats_pageid=awpcp_get_page_id($awpcp_browsecats_pagename=(sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='')));
	$permastruc=get_option('permalink_structure');
	$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);
	$browseadspageid=awpcp_get_page_id($browseadspagename);

				if( get_awpcp_option('seofriendlyurls') )
				{
					if(isset($permastruc) && !empty($permastruc))
					{
						$url_browsecat="$quers/$browsecatspagename";
					}
					else
					{
						$url_browsecat="$quers/?page_id=$awpcp_browsecats_pageid";
					}
				}
				elseif(!(get_awpcp_option('seofriendlyurls') ) )
				{
					if(isset($permastruc) && !empty($permastruc))
					{
						$url_browsecat="$quers/$browsecatspagename";
					}
					else
					{
						$url_browsecat="$quers/?page_id=$awpcp_browsecats_pageid";
					}
				}

	if( file_exists("$awpcp_plugin_path/awpcp_display_ads_my_layout.php")  && get_awpcp_option('activatemylayoutdisplayads') )
	{
		include("$awpcp_plugin_path/awpcp_display_ads_my_layout.php");
	}
	else
	{
		echo "<div id=\"classiwrapper\">";

				if($hidepager == 1)
				{
					$uiwelcome=get_awpcp_option('uiwelcome');
					echo "<div class=\"uiwelcome\">$uiwelcome</div>";
				}

				$isadmin=checkifisadmin();
				awpcp_menu_items();

							if($hasregionsmodule ==  1)
							{
								if( isset($_SESSION['theactiveregionid']) )
								{
									$theactiveregionid=$_SESSION['theactiveregionid'];

									$theactiveregionname=get_theawpcpregionname($theactiveregionid);

									echo "<h2>";
									_e("You are currently browsing in","AWPCP");
									echo ": $theactiveregionname</h2><SUP><a href=\"?a=unsetregion\">";
									_e("Clear session for ","AWPCP");
									echo "$theactiveregionname</a></SUP>";
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

		if(get_awpcp_option('disablependingads') == 0)
		{
			if(get_awpcp_option('freepay') == 1)
			{
				$where.=" AND payment_status != 'Pending'";
			}
		}

		if(!ads_exist())
		{
			$showcategories="<p style=\"padding:10px\">";
			$showcategories.=__("There are currently no ads in the system","AWPCP");
			$showcategories.="</p>";
			$pager1='';
			$pager2='';
		}
		else
		{
				$awpcp_image_display_list=array();

					if(isset($permastruc) && !empty($permastruc))
					{
						$tpname="$quers/$browseadspagename";
					}
					else
					{
						$tpname="?page_id=$browseadspageid";
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
				$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details,ad_postdate,ad_enddate,ad_views,ad_fee_paid, IF(ad_fee_paid>0,1,0) as ad_is_paid FROM $from WHERE $where ORDER BY ad_postdate DESC, ad_title ASC LIMIT $offset,$results";
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
					$awpcpadcity=get_adcityvalue($ad_id);
					$awpcpadstate=get_adstatevalue($ad_id);
					$awpcpadcountry=get_adcountryvalue($ad_id);
					$awpcpadcountyvillage=get_adcountyvillagevalue($ad_id);

					$url_showad=url_showad($ad_id);

						if( get_awpcp_option('seofriendlyurls') )
						{

							if(isset($permastruc) && !empty($permastruc))
							{
								$url_browsecats="$quers/$browsecatspagename/$category_id/$modtitle";
							}
							else
							{
								$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&category_id=$category_id";
							}
						}
						elseif(!(get_awpcp_option('seofriendlyurls') ) )
						{
							if(isset($permastruc) && !empty($permastruc))
							{
								$url_browsecats="$quers/$browsecatspagename/?category_id=$category_id";
							}
							else
							{
								$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&category_id=$category_id";
							}
						}

							$ad_title="<a href=\"$url_showad\">".$rsrow[2]."</a>";
							$categorylink="<a href=\"$url_browsecats\">$category_name</a>";


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
									$awpcp_city_display="<td class=\"displayadscelllocation\" width=\"75\" style=\"text-align:center;\">";
									$awpcp_city_display.=__("N/A","AWPCP");
									$awpcp_city_display.="</td>";
								}
							}
						}
						else
						{
							$awpcp_city_display="<td class=\"displayadscelllocation\"  width=\"75\" style=\"text-align:center;\">";
							$awpcp_city_display.=__("N/A","AWPCP");
							$awpcp_city_display.="</td>";
						}

						if(get_awpcp_option('imagesallowdisallow'))
						{

							$awpcp_image_display_head="<td class=\"displayadshead\" width=\"5%\" style=\"text-align:center;\"></td>";
							$awpcp_image_display="<td class=\"displayadscellimg\" width=\"5%\"><a href=\"$url_showad\">";

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
							$awpcp_display_adviews_head="<td class=\"displayadshead\" width=\"5%\" style=\"text-align:center;\">";
							$awpcp_display_adviews_head.=__("VIEWS","AWPCP");
							$awpcp_display_adviews_head.="</td>";
							$awpcp_display_adviews="<td class=\"displayadscellviews\" width=\"5%\" style=\"text-align:center;\">$rsrow[11]</td>";
						}

						$items[]="<tr>$awpcp_image_display<td class=\"displayadscellheadline\" width=\"50%\" valign=\"top\">$ad_title<br>$addetailssummary...</td>$awpcp_city_display<td class=\"displayadscellposted\" width=\"15%\" style=\"text-align:center;\">$rsrow[9]</td>$awpcp_display_adviews</tr>";

						$opentable="<table><tr>$awpcp_image_display_head<td class=\"displayadshead\"  width=\"50%\">";
						$opentable.=__("HEADLINE","AWPCP");
						$opentable.="</td><td class=\"displayadshead\" width=\"25%\" style=\"text-align:center;\">";
						$opentable.=__("LOCATION","AWPCP");
						$opentable.="</td><td class=\"displayadshead\" width=\"15%\" style=\"text-align:center;\">";
						$opentable.=__("POSTED","AWPCP");
						$opentable.="</td>$awpcp_display_adviews_head</tr>";
						$closetable="</table>";

						$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
						$showcategories="$theitems";
				}
				if(!isset($ad_id) || empty($ad_id) || $ad_id == '0')
				{
						$showcategories="<p style=\"padding:20px;\">";
						$showcategories.=__("There were no ads found","AWPCP");
						$showcategories.="</p>";
						$pager1='';
						$pager2='';
				}
		}

		echo "<div class=\"fixfloat\"></div><div class=\"pager\">$pager1</div>";
		echo "<div class=\"changecategoryselect\"><form method=\"post\" action=\"$url_browsecat\"><select name=\"category_id\"><option value=\"-1\">";
		_e("Select Category","AWPCP");
		echo "</a>";
		$allcategories=get_categorynameidall($adcategory='');
		echo "$allcategories";
		echo "</select><input type=\"hidden\" name=\"a\" value=\"browsecat\"><input class=\"button\" type=\"submit\" value=\"";
		_e("Change Category","AWPCP");
		echo "\"></form></div><div class=\"fixfloat\"></div>";
		echo "$showcategories";
		echo "<div class=\"pager\">$pager2</div>";
		if($byl)
		{
			if( field_exists($field='removepoweredbysign') && !(get_awpcp_option('removepoweredbysign')) )
			{
				echo "<p><font style=\"font-size:smaller\">";
				_e("Powered by","AWPCP");
				echo "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
			}
			elseif( field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign')) )
			{

			}
			else
			{
				echo "<p><font style=\"font-size:smaller\">";
				_e("Powered by","AWPCP");
				echo "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
			}
		}
		echo"</div>";

	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: show the ad when at title is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function showad($adid,$omitmenu)
{

	global $wpdb,$awpcp_plugin_path;
	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option(permalink_structure);
	$quers=setup_url_structure($awpcppagename);
	$replytoadpagename=sanitize_title(get_awpcp_option('replytoadpagename'), $post_ID='');
	$replytoadpageid=awpcp_get_page_id($replytoadpagename);
	$showadspagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');
	$pathvalueshowad=get_awpcp_option('pathvalueshowad');

	if(!isset($adid) || empty($adid))
	{
		if(isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$adid=$_REQUEST['adid'];
		}
		elseif(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$adid=$_REQUEST['id'];
		}
		else
		{
			if( get_awpcp_option('seofriendlyurls') )
			{
				if(isset($permastruc) && !empty($permastruc))
				{
					$awpcpshowad_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
					$awpcpshowad_requested_url .= $_SERVER['HTTP_HOST'];
					$awpcpshowad_requested_url .= $_SERVER['REQUEST_URI'];

					$awpcpparsedshowadURL = parse_url ($awpcpshowad_requested_url);
					$awpcpsplitshowadPath = preg_split ('/\//', $awpcpparsedshowadURL['path'], 0, PREG_SPLIT_NO_EMPTY);
					$adid=$awpcpsplitshowadPath[$pathvalueshowad];
				}
			}
		}
	}

	if(isset($adid) && !empty($adid))
	{

		if( file_exists("$awpcp_plugin_path/awpcp_showad_my_layout.php") && get_awpcp_option('activatemylayoutshowad') )
		{
			include("$awpcp_plugin_path/awpcp_showad_my_layout.php");
		}
		else
		{
				echo "<div id=\"classiwrapper\">";

				$isadmin=checkifisadmin();

				if(!$omitmenu)
				{
					awpcp_menu_items();
				}

				if(isset($awpcpadpostedmsg) && !empty($awpcpadpostedmsg))
				{
					echo "$awpcpadpostedmsg";
				}

				//update the ad views
				$query="UPDATE ".$table_name3." SET ad_views=(ad_views + 1) WHERE ad_id='$adid'";
				if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


				if(get_awpcp_option('useadsense') == 1)
				{
					$adsensecode=get_awpcp_option('adsense');
					$showadsense="<p class=\"cl-adsense\">$adsensecode</p>";
				}
				else
				{
					$showadsense='';
				}

						 $query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details,websiteurl from ".$table_name3." WHERE ad_id='$adid'";
						 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							while ($rsrow=mysql_fetch_row($res))
							{
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
									$adcontactphone="<br/>";
									$adcontactphone.=__("Phone","AWPCP");
									$adcontactphone.=": $adcontact_phone";
								}

								if( empty($adcontact_city) && empty($adcontact_state) && empty($adcontact_country) && empty($ad_county_village))
								{
									$location="";
								}
								else
								{
									$location="<br/>";
									$location.=__("Location ","AWPCP");
									$localtion.=": ";

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
										$codecontact="$replytoadpagename/$adid/$modtitle";
									}
									else
									{
										$codecontact="?page_id=$replytoadpageid&i=$adid";
									}
								}
								elseif(!(get_awpcp_option('seofriendlyurls') ) )
								{
									if(isset($permastruc) && !empty($permastruc))
									{
										$codecontact="$replytoadpagename/?i=$adid";
									}
									else
									{
										$codecontact="?page_id=$replytoadpageid&i=$adid";
									}
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
											$aditemprice="<br/><span class=\"itemprice\"><b>";
											$aditemprice.=__("Price","AWPCP");
											$aditemprice.=":</b> <b class=\"price\">$awpcpthecurrencysymbol $itempricereconverted</b></span>";
										}
									}
								}

								if( get_awpcp_option('displayadviews') )
								{
									$awpcpadviews_total=get_numtimesadviewd($adid);
									$awpcpadviews="<br/>";
									$awpcpadviews.=__("Views","AWPCP");
									$awpcpadviews.=":  $awpcpadviews_total";
								}

								if(get_awpcp_option('visitwebsitelinknofollow'))
								{
									$awpcprelnofollow="rel=\"nofollow\" ";
								}
								else
								{
									$awpcprelnofollow="";
								}

								echo "<div id=\"showad\"><div class=\"adtitle\"> $ad_title </div>";
								echo "<div class=\"adbyline\">";
								echo "<a href=\"$quers/$codecontact\">";
								_e("Contact ","AWPCP");
								echo $adcontact_name;
								echo "</a>";
								echo $adcontactphone;
								echo $location;
								if(isset($websiteurl) && !empty($websiteurl))
								{

									echo "<br/><a $awpcprelnofollow href=\"$websiteurl\">";
									_e("Visit Website","AWPCP");
									echo "</a>";
								}
								echo $awpcpadviews;
								echo $aditemprice;
								echo "</div>";
								if( !empty($awpcpadviews) || !empty($aditemprice) )
								{
									echo "<div class=\"fixfloat\"></div>";
								}

								if(get_awpcp_option('adsenseposition') == 1)
								{
									echo "$showadsense";
								}

								if(get_awpcp_option('hyperlinkurlsinadtext')){
									$addetails=preg_replace("/(http:\/\/[^\s]+)/","<a $awpcprelnofollow href=\"\$1\">\$1</a>",$addetails);
								}

								$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "<br /><br />", $addetails);

								echo "<p class=\"addetails\">$addetails</p>";
								if(get_awpcp_option('adsenseposition') == 2)
								{
									echo "$showadsense";
								}
								echo "</div><div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

								$theimage='';

								if(get_awpcp_option('imagesallowdisallow') == 1)
								{

									$totalimagesuploaded=get_total_imagesuploaded($adid);

									if($totalimagesuploaded >=1)
									{

										 $query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$adid' AND disabled='0' ORDER BY image_name ASC";
										 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

										while ($rsrow=mysql_fetch_row($res))
										{
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
	}
	else
	{
		display_ads($where='',$byl='',$hidepager='');
	}

}

function awpcp_append_title($title)
{
		$permastruc=get_option(permalink_structure);
		$awpcpshowadpagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');
		$awpcpbrowsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='');
		$awpcptitleseparator=get_awpcp_option('awpcptitleseparator');
		if(!isset($awpcptitleseparator) || empty($awpcptitleseparator))
		{
			$awpcptitleseparator="|";
		}

		$pathvalueshowad=get_awpcp_option('pathvalueshowad');
		$pathvaluebrowsecats=get_awpcp_option('pathvaluebrowsecats');

	wp_reset_query();

	if(is_page($awpcpshowadpagename) || is_page($awpcpbrowsecatspagename))
	{

		if(isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
		{
			$category_id=$_REQUEST['category_id'];
		}

		if(!isset($adid) || empty($adid))
		{
			if(isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
			{
				$adid=$_REQUEST['adid'];
			}
			elseif(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
			{
				$adid=$_REQUEST['id'];
			}
			else
			{
					if(isset($permastruc) && !empty($permastruc))
					{
						$awpcpshowad_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
						$awpcpshowad_requested_url .= $_SERVER['HTTP_HOST'];
						$awpcpshowad_requested_url .= $_SERVER['REQUEST_URI'];

						$awpcpparsedshowadURL = parse_url ($awpcpshowad_requested_url);
						$awpcpsplitshowadPath = preg_split ('/\//', $awpcpparsedshowadURL['path'], 0, PREG_SPLIT_NO_EMPTY);

						foreach ($awpcpsplitshowadPath as $awpcpsplitshowadPathitem)
						{
							if( $awpcpsplitshowadPathitem == $awpcpbrowsecatspagename )
							{
								$awpcpiscat=1;
								$adcategoryid=$awpcpsplitshowadPath[$pathvaluebrowsecats];
							}
						}

							$adid=$awpcpsplitshowadPath[$pathvalueshowad];
					}
			}
		}


		if( $awpcpiscat == 1 )
		{
			$awpcp_ad_cat_title=get_adcatname($adcategoryid);

			$title.=" $awpcptitleseparator $awpcp_ad_cat_title";
		}
		elseif( isset($category_id) && !empty($category_id) )
		{
			$awpcp_ad_cat_title=get_adcatname($category_id);

			$title.=" $awpcptitleseparator $awpcp_ad_cat_title";
		}
		else
		{
			$awpcp_ad_title=get_adtitle($adid);

			$awpcpadcity=get_adcityvalue($adid);
			$awpcpadstate=get_adstatevalue($adid);
			$awpcpadcountry=get_adcountryvalue($adid);
			$awpcpadcountyvillage=get_adcountyvillagevalue($adid);

			if( get_awpcp_option('showcityinpagetitle') && !empty($awpcpadcity) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adcityvalue($adid);
			}
			if( get_awpcp_option('showstateinpagetitle') && !empty($awpcpadstate) )
			{
					$awpcp_ad_title.=" $awpcptitleseparator ";
					$awpcp_ad_title.=get_adstatevalue($adid);
			}
			if( get_awpcp_option('showcountryinpagetitle') && !empty($awpcpadcountry) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adcountryvalue($adid);
			}
			if( get_awpcp_option('showcountyvillageinpagetitle') && !empty($awpcpadcountyvillage) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adcountyvillagevalue($adid);
			}
			if( get_awpcp_option('showcategoryinpagetitle') )
			{
				$awpcp_ad_category_id=get_adcategory($adid);
				$awpcp_ad_category_name=get_adcatname($awpcp_ad_category_id);

				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=$awpcp_ad_category_name;
			}

			$title.=" $awpcptitleseparator $awpcp_ad_title";
		}

	}

		return $title;
}

add_filter('wp_title','awpcp_append_title');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Uninstall
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcp_uninstall()
{

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

		echo "<div class=\"wrap\"><h2>";
		_e("AWPCP Classifieds Management System Uninstall Plugin","AWPCP");
		echo "</h2>";
		if(isset($message) && !empty($message))
		{
			echo $message;
		}
		echo "<div style=\"padding:20px;\">";
		_e("Thank you for using AWPCP. You have arrived at this page by clicking the Uninstall link. If you are certain you wish to uninstall the plugin, please click the link below to proceed. Please note that all your data related to the plugin, your ads, images and everything else created by the plugin will be destroyed","AWPCP");
		echo "<p><b>";
		_e("Important Information","AWPCP");
		echo "</b></p>";
		echo "<blockquote><p>1.";
		_e("If you plan to use the data created by the plugin please export the data from your mysql database before clicking the uninstall link","AWPCP");
		echo "</p>";
		echo "<p>2.";
		_e("If you want to keep your user uploaded images, please download $dirname to your local drive for later use or rename the folder to something else so the uninstaller can bypass it","AWPCP");
		echo "</p>";
		echo "</blockquote>:";
		echo "<a href=\"?page=Manage3&action=douninstall\">";
		_e("Proceed with Uninstalling Another Wordpress Classifieds Plugin","AWPCP");
		echo "</a></div><div class=\"fixfloat\"></div>";
	}
}

function douninstall()
{

	global $wpdb,$awpcp_plugin_path,$table_prefix;

	//Remove the upload folders with uploaded images

	$dirname=AWPCPUPLOADDIR;

	if (file_exists($dirname))
	{

		require_once $awpcp_plugin_path.'/fileop.class.php';

		$fileop=new fileop();
		$fileop->delete($dirname);

	}

	// Delete the classifieds page(s)

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$awpcppageid=awpcp_get_page_id($awpcppagename);
	$query="DELETE FROM {$table_prefix}posts WHERE ID='$awpcppageid' OR post_parent='$awpcppageid' and post_content LIKE '%AWPCP%'";
	@mysql_query($query);



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
		echo "<div style=\"padding:50px;font-weight:bold;\"><p>";
		_e("Almost done...","AWPCP");
		echo "</p><h1>";
		_e("One More Step","AWPCP");
		echo "</h1><a href=\"plugins.php?deactivate=true\">";
		_e("Please click here to complete the uninstallation process","AWPCP");
		echo "</a></h1></div>";
		die;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>