<?php @session_start();?>
<?php if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
 Plugin Name: Another Wordpress Classifieds Plugin
 Plugin URI: http://www.awpcp.com
 Description: AWPCP - A plugin that provides the ability to run a free or paid classified ads service on your wordpress blog. !!!IMPORTANT!!! Whether updating a previous installation of Another Wordpress Classifieds Plugin or installing Another Wordpress Classifieds Plugin for the first time, please backup your wordpress database before you install/uninstall/activate/deactivate/upgrade Another Wordpress Classifieds Plugin.
 Version: 1.0.6.13
 Author: A Lewis, D. Rodenbaugh
 Author URI: http://www.skylineconsult.com
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
if ( !defined('WP_CONTENT_DIR') )
define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

if ( !defined('WP_CONTENT_URL') )
define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

//For PHP4 users, even though it's not technically supported:
if (!function_exists('array_walk_recursive'))
{
    function array_walk_recursive(&$input, $funcname, $userdata = "")
    {
        if (!is_callable($funcname)) {
            return false;
        }
        if (!is_array($input)) {
            return false;
        }
       
        foreach ($input AS $key => $value)
        {
            if (is_array($input[$key]))
            {
                array_walk_recursive($input[$key], $funcname, $userdata);
            }
            else
            {
                $saved_value = $value;
                if (!empty($userdata))
                {
                    $funcname($value, $key, $userdata);
                }
                else
                {
                    $funcname($value, $key);
                }
               
                if ($value != $saved_value)
                {
                    $input[$key] = $value;
                }
            }
        }
        return true;
    }
}

$wpcontenturl=WP_CONTENT_URL;
$wpcontentdir=WP_CONTENT_DIR;
$wpinc=WPINC;

//Strip slashes added (e.g. "John\'s Mother\'s Food") out of common variables to avoid garbage in the database:
if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value)
    {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

$awpcp_plugin_path = WP_CONTENT_DIR.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
$awpcp_plugin_url = WP_CONTENT_URL.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

require_once("$awpcp_plugin_path"."dcfunctions.php");
require_once("$awpcp_plugin_path"."functions_awpcp.php");
require_once("$awpcp_plugin_path"."upload_awpcp.php");

//Activate error handler:
set_error_handler("awpcpErrorHandler");

$plugin_dir = basename(dirname(__FILE__));
if (get_awpcp_option('activatelanguages'))
{
	load_plugin_textdomain( 'AWPCP', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
}

$imagespath = WP_CONTENT_DIR.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'images';
$awpcp_imagesurl = WP_CONTENT_URL.'/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'images';


$nameofsite=get_option('blogname');
$siteurl=get_option('siteurl');
$thisadminemail=get_option('admin_email');

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );
$awpcp_plugin_data=get_plugin_data(__FILE__);
$awpcp_db_version = $awpcp_plugin_data['Version'];

if (field_exists($field='uploadfoldername'))
{
	$uploadfoldername=get_awpcp_option('uploadfoldername');
}
else
{
	$uploadfoldername="uploads";
}

define('MAINUPLOADURL', $wpcontenturl .'/' .$uploadfoldername);
define('MAINUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername);
define('AWPCPUPLOADURL', $wpcontenturl .'/' .$uploadfoldername .'/awpcp');
define('AWPCPUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername .'/awpcp/');
define('AWPCPTHUMBSUPLOADURL', $wpcontenturl .'/' .$uploadfoldername .'/awpcp/thumbs');
define('AWPCPTHUMBSUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername .'/awpcp/thumbs/');
define('AWPCPURL', $awpcp_plugin_url );
define('MENUICO', $awpcp_imagesurl .'/menuico.png');

$awpcpthumbsurl=AWPCPTHUMBSUPLOADURL;

if ( file_exists("$awpcp_plugin_path/awpcp_category_icons_module.php") )
{
	require("$awpcp_plugin_path/awpcp_category_icons_module.php");
	$hascaticonsmodule=1;
}

if ( file_exists("$awpcp_plugin_path/awpcp_region_control_module.php") )
{
	require("$awpcp_plugin_path/awpcp_region_control_module.php");
	$hasregionsmodule=1;
}

if ( file_exists("$awpcp_plugin_path/awpcp_remove_powered_by_module.php") )
{
	require("$awpcp_plugin_path/awpcp_remove_powered_by_module.php");
	$haspoweredbyremovalmodule=1;
}

if ( file_exists("$awpcp_plugin_path/awpcp_google_checkout_module.php") )
{
	require("$awpcp_plugin_path/awpcp_google_checkout_module.php");
	$hasgooglecheckoutmodule=1;
}

if ( file_exists("$awpcp_plugin_path/awpcp_extra_fields_module.php") )
{
	require("$awpcp_plugin_path/awpcp_extra_fields_module.php");
	$hasextrafieldsmodule=1;
}

if ( file_exists("$awpcp_plugin_path/awpcp_rss_module.php") )
{
	$hasrssmodule=1;

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$awpcprssaction=$_REQUEST['a'];
	} else { $awpcprssaction='';}

	if ($awpcprssaction == 'rss')
	{
		require("$awpcp_plugin_path/awpcp_rss_module.php");

	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Add css file and jquery codes to header
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcpjs() {
	global $awpcp_plugin_url,$wpdb;
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-form');
	if (checkfortable($wpdb->prefix . "awpcp_adsettings")) {
		if ( !get_awpcp_option('awpcp_thickbox_disabled') )
		{
			wp_enqueue_script('thickbox');
		}
	}
	wp_enqueue_script('jquery-chuch', $awpcp_plugin_url.'js/checkuncheckboxes.js', array('jquery'));
}

function awpcp_insert_thickbox() {
	global $siteurl,$wpinc;
	//	Echo OK here
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
if ( !get_awpcp_option('awpcp_thickbox_disabled') )
{
	add_action('wp_head', 'awpcp_insert_thickbox', 10);
}
add_action( 'doadexpirations_hook', 'doadexpirations' );
if (is_admin()) {
	add_action('admin_menu', 'awpcp_launch');
}
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

if (get_awpcp_option('awpcppagefilterswitch') == 1)
{
	add_filter('wp_list_pages_excludes', 'exclude_awpcp_child_pages');
}

function exclude_awpcp_child_pages($output = '')
{
	$awpcppagename='';
	$cpagename_awpcp=get_currentpagename();

	if (isset($cpagename_awpcp) && !empty($cpagename_awpcp))
	{
		$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');
	}

	$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

	$awpcpchildpages=array();
	global $wpdb,$table_prefix;

	$query="SELECT ID FROM {$table_prefix}posts WHERE post_parent='$awpcpwppostpageid' AND post_content LIKE '%AWPCP%'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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

	$pprefxpageguid=awpcp_get_guid($awpcppageid=awpcp_get_page_id($pprefx));
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
	//$browsecatspageguid=awpcp_get_guid($awpcpbrowsecatspageid=awpcp_get_page_id($browsecatspagename));
	$awpcp_rules = array(
		$pprefx.'/'.$showadspagename.'/(.+?)/(.+?)' => $showadspageguid.'&id='.$wp_rewrite->preg_index(1),
		$pprefx.'/'.$replytoadpagename.'/(.+?)/(.+?)' => $replytoadsadspageguid.'&id='.$wp_rewrite->preg_index(1),
		$pprefx.'/'.$browsecatspagename.'/(.+?)/(.+?)' => $browsecatspageguid.'&a=browsecat&amp;category_id='.$wp_rewrite->preg_index(1),
		$pprefx.'/'.$paymentthankyoupagename.'/(.+?)' => $paymentthankyoupageguid.'&i='.$wp_rewrite->preg_index(1),
		$pprefx.'/'.$paymentcancelpagename.'/(.+?)' => $paymentcancelpageguid.'&i='.$wp_rewrite->preg_index(1),
		$pprefx.'/setregion/(.+?)/(.+?)' => $pprefxpageguid.'&a=setregion&regionid='.$wp_rewrite->preg_index(1),
		$pprefx.'/classifiedsrss' => $awpcppageguid.'&a=rss',
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
	//Echo OK here
	$awpcpstylesheet="awpcpstyle.css";
	$awpcpstylesheetie6="awpcpstyle-ie-6.css";
	echo "\n".'<style type="text/css" media="screen">@import "'.AWPCPURL.'css/'.$awpcpstylesheet.'";</style>
			 <!--[if lte IE 6]><style type="text/css" media="screen">@import "'.AWPCPURL.'css/'.$awpcpstylesheetie6.'";</style><![endif]-->
			 ';
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// PROGRAM FUNCTIONS
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTIONS: Installation | Update
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Create the database tables if they do not not exist
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function do_settings_insert()
{
	global $wpdb;
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";

	$query="INSERT INTO " . $tbl_ad_settings . " (`config_option`, `config_value`, `config_diz`,`config_group_id`, `option_type`) VALUES
		('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: existing page will be overwritten]','10','1'),
		('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('browseadspagename', 'Browse Ads', 'Name browse ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1'),
		('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1'),
		('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1'),
		('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1'),
		('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [ CAUTION: existing page will be overwritten ]','10','1'),
		('editadpagename', 'Edit Ad', 'Name for edit ad page. [ CAUTION: existing page will be overwritten ]','10','1'),
		('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page ]','10','1'),
		('freepay', '0', 'Charge Listing Fee? (Pay Mode)','3','0'),
		('requireuserregistration', '0', 'Require user registration?','7','0'),
		('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php <br/>[ **Only needed if registration is required and your login url is mod-rewritten ] ','7','1'),
		('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1'),
		('main_page_display', '0', 'Show ad listings on main page (checked) or just categories (unchecked)?','1','0'),
		('activatelanguages', '0', 'Turn On Translation File (POT)?','1','0'),		
		('awpcpadminaccesslevel', 'admin', 'Set wordpress role of users who can have admin access to classifieds. Choices [admin,editor][case sensitive]. Currently no other roles will be granted access.','1','1'),				
		('sidebarwidgetaftertitle', '</h2>', 'Code to appear after widget title','1','1'),	
		('sidebarwidgetbeforetitle', '<h2 class=\"widgettitle\">', 'Code to appear before widget title','1','1'),
		('sidebarwidgetaftercontent', '</div>', 'Code to appear after widget content','1','1'),
		('sidebarwidgetbeforecontent', '<div class=\"widget\">', 'Code to appear before widget content','1','1'),
		('usesenderemailinsteadofadmin', '0', 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.','1','0'),
		('awpcpadminemail', '', 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here.','1','1'),		
		('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1'),
		('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0'),
		('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0'),
		('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0'),
		('awpcppagefilterswitch', '1', 'Uncheck this if you need to turn off the AWPCP page filter that prevents AWPCP classifieds children pages from showing up in your wp pages menu [you might need to do this if for example the AWPCP page filter is messing up your page menu. It means you will have to manually exclude the AWPCP children pages from showing in your page list. Some of the pages really should not be visible to your users by default]','1','0'),
		('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0'),
		('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0'),
		('paylivetestmode', '0', 'Put payment gateways in test mode.','3','0'),
		('useadsense', '1', 'Activate AdSense','5','0'),
		('adsense', 'AdSense code', 'Your AdSense code [ Best if 468 by 60 text or banner. ]','5',2),
		('adsenseposition', '2', 'Show AdSense at position: [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1'),
		('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiration].','2','1'),
		('autoexpiredisabledelete', '0', 'Disable expired ads instead of deleting them?','2','0'),
		('imagesallowdisallow', '1', 'Allow images in ads? (affects both free and pay mode)','4','0'),
		('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0'),
		('imagesallowedfree', '4', 'Number of Image Uploads Allowed (Free Mode)','4','1'),
		('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1'),
		('maximagesize', '150000', 'Maximum file size per image user can upload to system.','4','1'),
		('minimagesize', '300', 'Minimum file size per image user can upload to system','4','1'),
		('imgthumbwidth', '125', 'Width for thumbnails created upon upload.','4','1'),
		('maxcharactersallowed', '750', 'Maximum ad length (characters)?','2','1'),
		('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for PayPal payments [if running in pay mode and if PayPal is activated]','3','1'),
		('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your PayPal payments','3','1'),
		('displaycurrencycode', 'USD', 'The display currency for your payment pages','3','1'),
		('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1'),
		('activatepaypal', '1', 'Activate PayPal?','3','0'),
		('activate2checkout', '1', 'Activate 2Checkout?','3','0'),
		('paypalpaymentsrecurring', '0', 'Use recurring payments PayPal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0'),
		('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2Checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0'),
		('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0'),
		('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1'),
		('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2'),
		('notifyofadposted', '1', 'Notify admin of new ad.','2','0'),
		('imagesapprove', '0', 'Hide images until admin approves them','4','0'),
		('adapprove', '0', 'Disable ad until admin approves','2','0'),
		('displayadthumbwidth', '80', 'Width for thumbnails in ad listings view [Only numerical value]','2','1'),
		('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0'),
		('groupbrowseadsby', '1', 'Group ad listings by','2','3'),
		('groupsearchresultsby', '1', 'Group ad listings in search results by','2','3'),
		('showadcount', '1', 'Show how many ads a category contains.','2','0'),
		('adresultsperpage', '10', 'Default number of ads per page','2','1'),
		('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0'),
		('displayadviews', '1', 'Show ad views','2','0'),
		('displayadlayoutcode', '<div id=\"\$awpcpdisplayaditems\"><div style=\"width:\$imgblockwidth;padding:5px;float:left;margin-right:20px;\">\$awpcp_image_name_srccode</div><div style=\"width:50%;padding:5px;float:left;\"><h4>\$ad_title</h4> \$addetailssummary...</div><div style=\"padding:5px;float:left;\"> \$awpcpadpostdate \$awpcp_city_display \$awpcp_state_display \$awpcp_display_adviews \$awpcp_display_price </div><div class=\"fixfloat\"></div></div><div class=\"fixfloat\"></div>', 'Modify as needed to control layout of ad listings page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2'),		
		('awpcpshowtheadlayout', '<div id=\"showad\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"adinfo\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields \$showadsense1<div class=\"adinfo\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><div class=\"fixfloat\"></div>\$awpcpadviews \$showadsense3</div>', 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2'),		
		('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1'),
		('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1'),
		('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1'),
		('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0'),
		('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0'),
		('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1'),
		('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1'),
		('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2'),
		('resendakeyformsubjectline', 'The classified ad ad access key you requested', 'Subject line for email sent out when someone requests their ad access key resent','8', '1'),
		('resendakeyformbodymessage', 'You asked to have your classified ad ad access key resent. Below are all the ad access keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their ad access key resent', '8','2'),
		('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1'),
		('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.','Message body text for email sent out when the payment processing does not complete', '8','2'),
		('adexpiredsubjectline', 'Your classifieds listing at has expired', 'Subject line for email sent out when an ad has auto-expired','8', '1'),
		('adexpiredbodymessage', 'This is an automated notification that your classified ad has expired.','Message body text for email sent out when an ad has auto-expired', '8','2'),
		('seofriendlyurls', '0', 'Turn on Search Engine Friendly URLs? (SEO Mode)', '11','0'),
		('pathvaluecontact', '3', 'If contact page link not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
		('pathvalueshowad', '3', 'If show ad links not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
		('pathvaluebrowsecats', '2', 'If browse categories links not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
		('pathvalueviewcategories', '2', 'If the menu link to view categories layout is not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
		('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in SEO Mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1'),
		('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in SEO Mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1'),
		('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0'),
		('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2'),
		('hyperlinkurlsinadtext', '0', 'Make URLs in ad text clickable', '2','0'),
		('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0'),
		('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2'),
		('displayphonefield', '1', 'Show phone field?','6','0'),
		('displayphonefieldreqop', '0', 'Require phone?','6','0'),
		('displaycityfield', '1', 'Show city field?','6','0'),
		('displaycityfieldreqop', '0', 'Require city?','6','0'),
		('displaystatefield', '1', 'Show state field?','6','0'),
		('displaystatefieldreqop', '0', 'Require state?','6','0'),
		('displaycountryfield', '1', 'Show country field?','6','0'),
		('displaycountryfieldreqop', '0', 'Require country?','6','0'),
		('displaycountyvillagefield', '0', 'Show County/village/other?','6','0'),
		('displaycountyvillagefieldreqop', '0', 'Require county/village/other?','6','0'),
		('displaypricefield', '1', 'Show price field?','6','0'),
		('displaypricefieldreqop', '0', 'Require price?','6','0'),
		('displaywebsitefield', '1', 'Show website field?','6','0'),
		('displaywebsitefieldreqop', '0', 'Require website?','6','0'),
		('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0'),
		('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2'),
		('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0')";
	$wpdb->query($query);
}

function awpcp_install() {
	global $wpdb,$awpcp_db_version,$awpcp_plugin_path;
	_log("Running installation");
	$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$tbl_pagename = $wpdb->prefix . "awpcp_pagename";

	if ($wpdb->get_var("show tables like '$tbl_ad_categories'") != $tbl_ad_categories) {
		_log("Fresh install detected");
			
		$sql = "CREATE TABLE " . $tbl_ad_categories . " (
	  `category_id` int(10) NOT NULL AUTO_INCREMENT,
	  `category_parent_id` int(10) NOT NULL,
	  `category_name` varchar(255) NOT NULL DEFAULT '',
	  `category_order` int(10) NULL DEFAULT '0',
	  PRIMARY KEY (`category_id`)
	) ENGINE=MyISAM;

	INSERT INTO " . $tbl_ad_categories . " (`category_id`, `category_parent_id`, `category_name`, `category_order`) VALUES
	(1, 0, 'General', 0);


	CREATE TABLE " . $tbl_ad_fees . " (
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

	INSERT INTO " . $tbl_ad_fees . " (`adterm_id`, `adterm_name`, `amount`, `recurring`, `rec_period`, `rec_increment`, `buys`, `imagesallowed`) VALUES
	(1, '30 Day Listing', 9.99, 1, 31, 'D', 0, 6);


	CREATE TABLE " . $tbl_ads . " (
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



	CREATE TABLE " . $tbl_ad_settings . " (
	  `config_option` varchar(50) NOT NULL DEFAULT '',
	  `config_value` text NOT NULL,
	  `config_diz` text NOT NULL,
	  `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `option_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`config_option`)
	) ENGINE=MyISAM COMMENT='0-checkbox, 1-text,2-textarea';


	CREATE TABLE " . $tbl_ad_photos . " (
	  `key_id` int(10) NOT NULL AUTO_INCREMENT,
	  `ad_id` int(10) unsigned NOT NULL DEFAULT '0',
	  `image_name` varchar(100) NOT NULL DEFAULT '',
	  `disabled` tinyint(1) NOT NULL,
	  PRIMARY KEY (`key_id`)
	) ENGINE=MyISAM;


	CREATE TABLE " . $tbl_pagename . " (
	  `key_id` int(10) NOT NULL AUTO_INCREMENT,
	  `userpagename` varchar(100) NOT NULL DEFAULT '',
	  PRIMARY KEY (`key_id`)
	) ENGINE=MyISAM;


	";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		add_option("awpcp_db_version", $awpcp_db_version);
		wp_schedule_event( time(), 'hourly', 'doadexpirations_hook' );
	} else {
		_log("Upgrade detected");
		global $wpdb,$awpcp_db_version;

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//	Update the database tables in the event of a new version of plugin
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$installed_ver = get_option( "awpcp_db_version" );

		if ( $installed_ver != $awpcp_db_version ) {
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Update category ordering
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$column="category_order";
			$cat_order_column_exists = mysql_query("SELECT $column FROM $tbl_ad_categories;");

			if (mysql_errno())
			{
				//Add the category order column:
				$wpdb->query("ALTER TABLE " . $tbl_ad_categories . "  ADD `category_order` int(10) NULL DEFAULT '0' AFTER category_name");
				$wpdb->query("UPDATE " . $tbl_ad_categories . " SET category_order=0");
			}
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Fix the shortcode issue if present in installed version
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			$wpdb->query("UPDATE " .$wpdb->prefix . "posts set post_content='[AWPCPCLASSIFIEDSUI]' WHERE post_content='[[AWPCPCLASSIFIEDSUI]]'");


			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// Update ad_settings table to ad field config groud ID if field does not exist in installed version
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$cgid_column_name="config_group_id";
			$cgid_column_name_exists=mysql_query("SELECT $cgid_column_name FROM $tbl_ad_settings;");

			if (mysql_errno())
			{
				$query=("ALTER TABLE " . $tbl_ad_settings . "  ADD `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER config_diz");
				@mysql_query($query);

				$myconfig_group_ops_1=array('showlatestawpcpnews','uiwelcome','main_page_display','contactformcheckhuman', 'contactformcheckhumanhighnumval','awpcptitleseparator','showcityinpagetitle','showstateinpagetitle','showcountryinpagetitle','showcategoryinpagetitle','showcountyvillageinpagetitle','awpcppagefilterswitch','activatelanguages','sidebarwidgetbeforecontent','sidebarwidgetaftercontent','sidebarwidgetbeforetitle','sidebarwidgetaftertitle','usesenderemailinsteadofadmin','awpcpadminaccesslevel','awpcpadminemail');
				$myconfig_group_ops_2=array('addurationfreemode','autoexpiredisabledelete','maxcharactersallowed','notifyofadexpiring', 'notifyofadposted', 'adapprove', 'disablependingads', 'showadcount', 'displayadviews','onlyadmincanplaceads','allowhtmlinadtext', 'hyperlinkurlsinadtext', 'notice_awaiting_approval_ad', 'buildsearchdropdownlists','visitwebsitelinknofollow','groupbrowseadsby','groupsearchresultsby','displayadthumbwidth','adresultsperpage','displayadlayoutcode','awpcpshowtheadlayout');
				$myconfig_group_ops_3=array('freepay','paylivetestmode','paypalemail', 'paypalcurrencycode', 'displaycurrencycode', '2checkout', 'activatepaypal', 'activate2checkout','twocheckoutpaymentsrecurring','paypalpaymentsrecurring');
				$myconfig_group_ops_4=array('imagesallowdisallow', 'awpcp_thickbox_disabled','imagesapprove', 'imagesallowedfree', 'uploadfoldername', 'maximagesize','minimagesize', 'imgthumbwidth');
				$myconfig_group_ops_5=array('useadsense', 'adsense', 'adsenseposition');
				$myconfig_group_ops_6=array('displayphonefield', 'displayphonefieldreqop', 'displaycityfield', 'displaycityfieldreqop', 'displaystatefield','displaystatefieldreqop', 'displaycountryfield', 'displaycountryfieldreqop', 'displaycountyvillagefield', 'displaycountyvillagefieldreqop', 'displaypricefield', 'displaypricefieldreqop', 'displaywebsitefield', 'displaywebsitefieldreqop');
				$myconfig_group_ops_7=array('requireuserregistration', 'postloginformto', 'registrationurl');
				$myconfig_group_ops_8=array('contactformsubjectline','contactformbodymessage','listingaddedsubject','listingaddedbody','resendakeyformsubjectline','resendakeyformbodymessage','paymentabortedsubjectline','paymentabortedbodymessage','adexpiredsubjectline','adexpiredbodymessage');
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

			}
	 	if (get_awpcp_option_group_id('seofriendlyurls') == 1){	$wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_group_id` = '11' WHERE `config_option` = 'seofriendlyurls'"); }
	 	if (get_awpcp_option_type('main_page_display') == 1){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Main page layout [ check for ad listings ] [ Uncheck for categories ]',config_group_id='1' WHERE `config_option` = 'main_page_display'"); }
	 	if (get_awpcp_option_config_diz('paylivetestmode') != "Put payment gateways in test mode"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Put payment gateways in test mode' WHERE `config_option` = 'paylivetestmode'");}
	 	if (get_awpcp_option_config_diz('adresultsperpage') != "Default number of ads per page"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '10', `option_type` = '1', `config_diz` = 'Default number of ads per page' WHERE `config_option` = 'adresultsperpage'");}
	 	if (get_awpcp_option_config_diz('awpcpshowtheadlayout') != "<div id=\"showad\"><div class=\"adtitle\">$ad_title</div><br/><div class=\"adinfo\">$featureimg<label>Contact Information</label><br/><a href=\"$quers/$codecontact\">Contact $adcontact_name</a>$adcontactphone $location $awpcpvisitwebsite</div>$aditemprice $awpcpextrafields <div class=\"fixfloat\"></div> $showadsense1<div class=\"adinfo\"><label>More Information</label><br/>$addetails</div>$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>$awpcpshowadotherimages</ul></div></div><div class=\"fixfloat\"></div>$awpcpadviews $showadsense3</div>"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '2', `option_type` = '2', `config_diz` = 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.', `config_value` = '<div id=\"showad\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"adinfo\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields <div class=\"fixfloat\"></div> \$showadsense1<div class=\"adinfo\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><div class=\"fixfloat\"></div>\$awpcpadviews \$showadsense3</div>' WHERE `config_option` = 'awpcpshowtheadlayout'");}
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Match up the ad settings fields of current versions and upgrading versions
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	if (!field_exists($field='userpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: Make sure page does not already exist]','10','1');");}
	 	if (!field_exists($field='showadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='placeadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='browseadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('browseadspagename', 'Browse Ads', 'Name browse ads apge. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='searchadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='paymentthankyoupagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='paymentcancelpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='replytoadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='browsecatspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='editadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('editadpagename', 'Edit Ad', 'Name for edit ad page. [CAUTION: existing page will be overwritten]','10','1');");}
	 	if (!field_exists($field='categoriesviewpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page]','10','1');");}
	 	if (!field_exists($field='freepay')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('freepay', '0', 'Charge Listing Fee?','3','0');");}
	 	if (!field_exists($field='requireuserregistration')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('requireuserregistration', '0', 'Require user registration?','7','0');");}
	 	if (!field_exists($field='postloginformto')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
	 	if (!field_exists($field='registrationurl')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
	 	if (!field_exists($field='main_page_display')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('main_page_display', '0', 'Main page layout [ check for ad listings | Uncheck for categories ]','1','0');");}
	 	if (!field_exists($field='activatelanguages')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('activatelanguages', '0', 'Activate Language Capability','1','0');");}
	 	if (!field_exists($field='awpcpadminaccesslevel')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcpadminaccesslevel', 'admin', 'Set wordpress role of users who can have admin access to classifieds. Choices [admin,editor]. Currently no other roles will be granted access.','1','1');");}
	 	if (!field_exists($field='sidebarwidgetaftertitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('sidebarwidgetaftertitle', '</h2>', 'Code to appear after widget title','1','1');");}
	 	if (!field_exists($field='sidebarwidgetbeforetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('sidebarwidgetbeforetitle', '<h2 class=\"widgettitle\">', 'Code to appear before widget title','1','1');");}
	 	if (!field_exists($field='sidebarwidgetaftercontent')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('sidebarwidgetaftercontent', '</div>', 'Code to appear after widget content','1','1');");}
	 	if (!field_exists($field='sidebarwidgetbeforecontent')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('sidebarwidgetbeforecontent', '<div class=\"widget\">', 'Code to appear before widget content','1','1');");}
	 	if (!field_exists($field='usesenderemailinsteadofadmin')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('usesenderemailinsteadofadmin', '0', 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.','1','0');");}
	 	if (!field_exists($field='awpcpadminemail')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcpadminemail', '', 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here.','1','1');");}
	 	if (!field_exists($field='awpcptitleseparator')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1');");}
	 	if (!field_exists($field='showcityinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0');");}
	 	if (!field_exists($field='showstateinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0');");}
	 	if (!field_exists($field='showcountryinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0');");}
	 	if (!field_exists($field='showcountyvillageinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES		('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0');");}
	 	if (!field_exists($field='showcategoryinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0');");}
	 	if (!field_exists($field='awpcppagefilterswitch')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcppagefilterswitch', '1', 'Uncheck this if you need to turn off the awpcp page filter that prevents awpcp classifieds children pages from showing up in your wp pages menu [you might need to do this if for example the awpcp page filter is messing up your page menu. It means you will have to manually exclude the awpcp children pages from showing in your page list. Some of the pages really should not be visible to your users by default]','1','0');");}
	 	if (!field_exists($field='paylivetestmode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paylivetestmode', '0', 'Put Paypal and 2Checkout in test mode.','3','0');");}
	 	if (!field_exists($field='useadsense')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('useadsense', '1', 'Activate adsense','5','0');");}
	 	if (!field_exists($field='adsense')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adsense', 'Adsense code', 'Your adsense code [ Best if 468 by 60 text or banner. ]','5','2');");}
	 	if (!field_exists($field='adsenseposition')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adsenseposition', '2', 'Adsense position. [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1');");}
	 	if (!field_exists($field='addurationfreemode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiry].','2','1');");}
	 	if (!field_exists($field='autoexpiredisabledelete')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('autoexpiredisabledelete', '0', 'Disable expired ads instead of deleting them?','2','0');");}
	 	if (!field_exists($field='imagesallowdisallow')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imagesallowdisallow', '1', 'Uncheck to disallow images in ads. [Affects both free and paid]','4','0');");}
	 	if (!field_exists($field='awpcp_thickbox_disabled')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0');");}
	 	if (!field_exists($field='imagesallowedfree')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imagesallowedfree', '4', ' Free mode number of images allowed?','4','1');");}
	 	if (!field_exists($field='uploadfoldername')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1');");}
	 	if (!field_exists($field='maximagesize')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('maximagesize', '150000', 'Maximum size per image user can upload to system.','4','1');");}
	 	if (!field_exists($field='minimagesize')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('minimagesize', '300', 'Minimum size per image user can upload to system','4','1');");}
	 	if (!field_exists($field='imgthumbwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imgthumbwidth', '125', 'Width for thumbnails created upon upload.','4','1');");}
	 	if (!field_exists($field='maxcharactersallowed')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?','2','1');");}
	 	if (!field_exists($field='paypalemail')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]','3','1');");}
	 	if (!field_exists($field='paypalcurrencycode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments','3','1');");}
	 	if (!field_exists($field='displaycurrencycode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycurrencycode', 'USD', 'The currency to show on your payment pages','3','1');");}
	 	if (!field_exists($field='2checkout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1');");}
	 	if (!field_exists($field='activatepaypal')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('activatepaypal', '1', 'Activate PayPal','3','0');");}
	 	if (!field_exists($field='activate2checkout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('activate2checkout', '1', 'Activate 2Checkout ','3','0');");}
	 	if (!field_exists($field='paypalpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paypalpaymentsrecurring', '0', 'Use recurring payments paypal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
	 	if (!field_exists($field='twocheckoutpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
	 	if (!field_exists($field='notifyofadexpiring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0');");}
	 	if (!field_exists($field='notifyofadposted')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('notifyofadposted', '1', 'Notify admin of new ad.','2','0');");}
	 	if (!field_exists($field='listingaddedsubject')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1');");}
	 	if (!field_exists($field='listingaddedbody')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2');");}
	 	if (!field_exists($field='imagesapprove')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('imagesapprove', '0', 'Hide images until admin approves them','4','0');");}
	 	if (!field_exists($field='adapprove')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adapprove', '0', 'Disable ad until admin approves','2','0');");}
	 	if (!field_exists($field='displayadthumbwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayadthumbwidth', '80', 'Width for thumbnails in ad listings view [Only numerical value]','2','1');");}
	 	if (!field_exists($field='disablependingads')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0');");}
	 	if (!field_exists($field='groupbrowseadsby')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('groupbrowseadsby', '1', 'Group ad listings by','2','3');");}
	 	if (!field_exists($field='groupsearchresultsby')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('groupsearchresultsby', '1', 'Group ad listings in search results by','2','3');");}
	 	if (!field_exists($field='showadcount')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showadcount', '1', 'Show how many ads a category contains.','2','0');");}
	 	if (!field_exists($field='adresultsperpage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adresultsperpage', '10', 'Default number of ads per page','2','1');");}
	 	if (!field_exists($field='noadsinparentcat')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0');");}
	 	if (!field_exists($field='displayadviews')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayadviews', '1', 'Show ad views','2','0');");}
	 	if (!field_exists($field='displayadlayoutcode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayadlayoutcode', '<div id=\"\$awpcpdisplayaditems\"><div style=\"width:\$imgblockwidth;padding:5px;float:left;margin-right:20px;\">\$awpcp_image_name_srccode</div><div style=\"width:50%;padding:5px;float:left;\"><h4>\$ad_title</h4> \$addetailssummary...</div><div style=\"padding:5px;float:left;\"> \$awpcpadpostdate \$awpcp_city_display \$awpcp_state_display \$awpcp_display_adviews \$awpcp_display_price </div><div class=\"fixfloat\"></div></div><div class=\"fixfloat\"></div>', 'Modify as needed to control layout of ad listings page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2');");}
	 	if (!field_exists($field='awpcpshowtheadlayout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('awpcpshowtheadlayout', '<div id=\"showad\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"adinfo\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields <div class=\"fixfloat\"></div> \$showadsense1<div class=\"adinfo\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><div class=\"fixfloat\"></div>\$awpcpadviews \$showadsense3</div>', 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2');");}
	 	if (!field_exists($field='smtphost')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1');");}
	 	if (!field_exists($field='smtpusername')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1');");}
	 	if (!field_exists($field='smtppassword')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1');");}
	 	if (!field_exists($field='onlyadmincanplaceads')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0');");}
	 	if (!field_exists($field='contactformcheckhuman')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0');");}
	 	if (!field_exists($field='contactformcheckhumanhighnumval')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1');");}
	 	if (!field_exists($field='contactformsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1');");}
	 	if (!field_exists($field='contactformbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2');");}
	 	if (!field_exists($field='resendakeyformsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('resendakeyformsubjectline', 'The classified ad access key you requested', 'Subject line for email sent out when someone requests their ad access key resent','8', '1');");}
	 	if (!field_exists($field='resendakeyformbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('resendakeyformbodymessage', 'You asked to have your classified ad ad access key resent. Below are all the ad access keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their ad access key resent', '8','2');");}
	 	if (!field_exists($field='paymentabortedsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1');");}
	 	if (!field_exists($field='paymentabortedbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.', 'Message body text for email sent out when the payment processing does not complete','8','2');");}
	 	if (!field_exists($field='adexpiredsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adexpiredsubjectline', 'Your classifieds listing at has expired', 'Subject line for email sent out when an ad has auto-expired','8', '1');");}
	 	if (!field_exists($field='adexpiredbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('adexpiredbodymessage', 'This is an automated notification that your classified ad has expired.','Message body text for email sent out when an ad has auto-expired', '8','2');");}
	 	if (!field_exists($field='seofriendlyurls')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('seofriendlyurls', '0', 'Search Engine Friendly URLs? [ Does not work in some instances ]', '11','0');");}
	 	if (!field_exists($field='pathvaluecontact')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluecontact', '3', 'If contact page link not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	 	if (!field_exists($field='pathvalueshowad')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvalueshowad', '3', 'If show ad links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	 	if (!field_exists($field='pathvaluebrowsecats')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluebrowsecats', '2', 'If browse categories links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	 	if (!field_exists($field='pathvalueviewcategories')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvalueviewcategories', '2', 'If the view categories link is not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
	 	if (!field_exists($field='pathvaluecancelpayment')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
	 	if (!field_exists($field='pathvaluepaymentthankyou')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
	 	if (!field_exists($field='allowhtmlinadtext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0');");}
	 	if (!field_exists($field='htmlstatustext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2');");}
	 	if (!field_exists($field='hyperlinkurlsinadtext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('hyperlinkurlsinadtext', '0', 'Make URLs in ad text clickable', '2','0');");}
	 	if (!field_exists($field='visitwebsitelinknofollow')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0');");}
	 	if (!field_exists($field='notice_awaiting_approval_ad')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2');");}
	 	if (!field_exists($field='displayphonefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayphonefield', '1', 'Show phone field','6','0');");}
	 	if (!field_exists($field='displayphonefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displayphonefieldreqop', '0', 'Require phone','6','0');");}
	 	if (!field_exists($field='displaycityfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycityfield', '1', 'Show city field.','6','0');");}
	 	if (!field_exists($field='displaycityfieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycityfieldreqop', '0', 'Require city','6','0');");}
	 	if (!field_exists($field='displaystatefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaystatefield', '1', 'Show state field.','6','0');");}
	 	if (!field_exists($field='displaystatefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaystatefieldreqop', '0', 'Require state','6','0');");}
	 	if (!field_exists($field='displaycountryfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountryfield', '1', 'Show country field.','6','0');");}
	 	if (!field_exists($field='displaycountryfieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountryfieldreqop', '0', 'Require country','6','0');");}
	 	if (!field_exists($field='displaycountyvillagefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountyvillagefield', '0', 'Show County/village/other.','6','0');");}
	 	if (!field_exists($field='displaycountyvillagefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaycountyvillagefieldreqop', '0', 'Require county/village/other.','6','0');");}
	 	if (!field_exists($field='displaypricefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaypricefield', '1', 'Show price field.','6','0');");}
	 	if (!field_exists($field='displaypricefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaypricefieldreqop', '0', 'Require price.','6','0');");}
	 	if (!field_exists($field='displaywebsitefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaywebsitefield', '1', 'Show website field','6','0');");}
	 	if (!field_exists($field='displaywebsitefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('displaywebsitefieldreqop', '0', 'Require website','6','0');");}
	 	if (!field_exists($field='buildsearchdropdownlists')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0');");}
	 	if (!field_exists($field='uiwelcome')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2');");}
	 	if (!field_exists($field='showlatestawpcpnews')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,	`config_value` , `config_diz` , `config_group_id`, `option_type`	) VALUES('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0');");}

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Create additional classifieds pages if they do not exist
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	$tableexists=checkfortable($tbl_pagename);
	 	if ($tableexists)
	 	{
	 		$cpagename_awpcp=get_currentpagename();
	 		if (isset($cpagename_awpcp) && !empty($cpagename_awpcp))
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

	 			if (!findpage($showadspagename,$shortcode='[AWPCPSHOWAD]'))
	 			{
	 				maketheclassifiedsubpage($showadspagename,$awpcpwppostpageid,$shortcode='[AWPCPSHOWAD]');
	 			}
	 			if (!findpage($placeadpagename,$shortcode='[AWPCPPLACEAD]'))
	 			{
	 				maketheclassifiedsubpage($placeadpagename,$awpcpwppostpageid,$shortcode='[AWPCPPLACEAD]');
	 			}
	 			if (!findpage($browseadspagename,$shortcode='[AWPCPBROWSEADS]'))
	 			{
	 				maketheclassifiedsubpage($browseadspagename,$awpcpwppostpageid,$shortcode='[AWPCPBROWSEADS]');
	 			}
	 			if (!findpage($searchadspagename,$shortcode='[AWPCPSEARCHADS]'))
	 			{
	 				maketheclassifiedsubpage($searchadspagename,$awpcpwppostpageid,$shortcode='[AWPCPSEARCHADS]');
	 			}
	 			if (!findpage($paymentthankyoupagename,$shortcode='[AWPCPPAYMENTTHANKYOU]'))
	 			{
	 				maketheclassifiedsubpage($paymentthankyoupagename,$awpcpwppostpageid,$shortcode='[AWPCPPAYMENTTHANKYOU]');
	 			}
	 			if (!findpage($paymentcancelpagename,$shortcode='[AWPCPCANCELPAYMENT]'))
	 			{
	 				maketheclassifiedsubpage($paymentcancelpagename,$awpcpwppostpageid,$shortcode='[AWPCPCANCELPAYMENT]');
	 			}
	 			if (!findpage($editadpagename,$shortcode='[AWPCPEDITAD]'))
	 			{
	 				maketheclassifiedsubpage($editadpagename,$awpcpwppostpageid,$shortcode='[AWPCPEDITAD]');
	 			}
	 			if (!findpage($replytoadpagename,$shortcode='[AWPCPREPLYTOAD]'))
	 			{
	 				maketheclassifiedsubpage($replytoadpagename,$awpcpwppostpageid,$shortcode='[AWPCPREPLYTOAD]');
	 			}
	 			if (!findpage($browsecatspagename,$shortcode='[AWPCPBROWSECATS]'))
	 			{
	 				maketheclassifiedsubpage($browsecatspagename,$awpcpwppostpageid,$shortcode='[AWPCPBROWSECATS]');
	 			}
	 		}
	 	}

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Add new field websiteurl to awpcp_ads
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	 	$ad_websiteurl_column="websiteurl";

	 	$ad_websiteurl_field=mysql_query("SELECT $ad_websiteurl_column FROM $tbl_ads;");

	 	if (mysql_errno())
	 	{
	 		$wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `websiteurl` VARCHAR( 500 ) NOT NULL AFTER `ad_contact_email`");
	 	}

	 	$wpdb->query("ALTER TABLE " . $tbl_ads . "  DROP INDEX `titdes`");
	 	$wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD FULLTEXT KEY `titdes` (`ad_title`,`ad_details`)");

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Add new field ad_fee_paid for sorting ads by paid listings first
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	 	$ad_fee_paid_column="ad_fee_paid";

	 	$ad_fee_paid_field=mysql_query("SELECT $ad_fee_paid_column FROM $tbl_ads;");

	 	if (mysql_errno())
	 	{
			 $query=("ALTER TABLE " . $tbl_ads . "  ADD `ad_fee_paid` float(7,2) NOT NULL AFTER `adterm_id`");
			 @mysql_query($query);
	 	}

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Increase the length value for the ad_item_price field
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	 	$wpdb->query("ALTER TABLE " . $tbl_ads . " CHANGE `ad_item_price` `ad_item_price` INT( 25 ) NOT NULL");

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Ad new field add_county_village to awpcp_ads
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	 	$ad_county_village_column="ad_county_village";

	 	$ad_county_vilalge_field=mysql_query("SELECT $ad_county_village_column FROM $tbl_ads;");

	 	if (mysql_errno())
	 	{
	 		$wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_county_village` varchar(255) NOT NULL AFTER `ad_country`");
	 	}

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Add field ad_views to table awpcp_ads to track ad views
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	 	$ad_views_column="ad_views";

	 	$ad_views_field=mysql_query("SELECT $ad_views_column FROM $tbl_ads;");

	 	if (mysql_errno())
	 	{
	 		$wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_views` int(10) NOT NULL AFTER `ad_item_price`");
	 	}

	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	// Insert new field ad_item_price into awpcp_ads table
	 	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	 	$ad_itemprice_column="ad_item_price";

	 	$ad_itemprice_field=mysql_query("SELECT $ad_itemprice_column FROM $tbl_ads;");

	 	if (mysql_errno())
	 	{
	 		$wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_item_price` INT( 10 ) NOT NULL AFTER `ad_country`");
	 	}
	 	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	 	update_option( "awpcp_db_version", $awpcp_db_version );
		}
	}
	_log("Installation complete");
}

function awpcp_flush_rewrite_rules()
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
	$myreturn=false;

	global $wpdb, $isclassifiedpage, $table_prefix;

	$query="SELECT * FROM {$table_prefix}posts WHERE post_title='$pagename' AND post_name='$awpcppagename'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
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
	if ( file_exists("$awpcp_plugin_path/awpcp_region_control_module.php") )
	{
		add_submenu_page('awpcp.php', 'Manage Regions', 'Regions', '10', 'Configure4', 'awpcp_opsconfig_regions');
	}
	if ( file_exists("$awpcp_plugin_path/awpcp_extra_fields_module.php") )
	{
		add_submenu_page('awpcp.php', 'Manage Extra Fields', 'Extra Fields', '10', 'Configure5', 'awpcp_add_new_field');
	}
	add_submenu_page('awpcp.php', 'Uninstall AWPCP', 'Uninstall', '10', 'Manage3', 'awpcp_uninstall');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Display the admin home screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcp_home_screen()
{
	$output = '';
	global $message,$user_identity,$wpdb,$awpcp_plugin_path,$awpcp_imagesurl,$awpcp_db_version,$haspoweredbyremovalmodule,$hasregionsmodule,$hascaticonsmodule,$hasgooglecheckoutmodule,$hasextrafieldsmodule,$extrafieldsversioncompatibility;
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";

	$output .= "<div class=\"wrap\"><h2>";
	$output .= __("AWPCP Classifieds Management System","AWPCP");
	$output .= "</h2><p>";
	$output .= __("You are using version","AWPCP");
	$output .= " <b>$awpcp_db_version</b> </p>$message <div style=\"padding:20px;\">";
	$output .= __("Thank you for using Another Wordpress Classifieds Plugin. As a reminder, please use this plugin knowing that is it is a work in progress and is by no means guaranteed to be a bug-free product. Development of this plugin is not a full-time undertaking. Consequently upgrades will be slow in coming; however, please feel free to report bugs and request new features via the ","AWPCP");
	$output .= "<a href='http://forum.awpcp.com'>";
	$output .= __("AWPCP support website", "AWPCP");
	$output .= "</a>";
	$output .= "</div>";

	if ($hasextrafieldsmodule == 1)
	{
		if (!($extrafieldsversioncompatibility == 1))
		{
			$output .= "<div id=\"message\" class=\"updated fade\" style=\"padding:10px;width:92%;\">";
			$output .= __("The version of the extra fields module that you are using is not compatible with this version of Another Wordpress Classifieds Plugin. Please request the updated files for the extra fields module","AWPCP");
			$output .= "<p><a href=\"http://www.awpcp.com/contact\">";
			$output .= __("Request Updated Extra Fields Module files","AWPCP");
			$output .= "</a></p></div>";
		}
	}
	$tableexists=checkfortable($tbl_ad_settings);
	if (!$tableexists)
	{
		$output .= "<b>";
		$output .= __("!!!!ALERT","AWPCP");
		$output .= ":</b>";
		$output .= __("There appears to be a problem with the plugin. The plugin is activated but your database tables are missing. Please de-activate the plugin from your plugins page then try to reactivate it.","AWPCP");
	}
	else
	{
		if (awpcpistableempty($tbl_ad_settings)) {
			do_settings_insert();
		}

		$cpagename_awpcp=get_awpcp_option('userpagename');
		$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

		$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
		if ($isclassifiedpage == false)
		{
			$awpcpsetuptext=display_setup_text();
			$output .= $awpcpsetuptext;

		} else {
			$awpcp_classifieds_page_conflict_check=checkforduplicate($cpagename_awpcp);
			if ( $awpcp_classifieds_page_conflict_check > 1)
			{
				$output .= "<div style=\"border-top:1px solid #dddddd;border-bottom:1px dotted #dddddd;padding:10px;background:#f5f5f5;\"><img src=\"$awpcp_imagesurl/Warning.png\" border=\"0\" alt=\"Alert\" style=\"float:left;margin-right:10px;\">";
				$output .= __("It appears you have a potential problem that could result in the malfunctioning of Another Wordpress Classifieds plugin. A check of your database was performed and duplicate entries were found that share the same post_name value as your classifieds page. If for some reason you uninstall and then reinstall this plugin and the duplicate pages remain in your database, it could break the plugin and prevent it from working. To fix this problem you can manually delete the duplicate pages and leave only the page with the ID of your real classifieds page, or you can use the link below to rebuild your classifieds page. The process will include first deleting all existing pages with a post name value identical to your classifieds page. Note that if you recreate the page, it will be assigned a new page ID so if you are referencing the classifieds page ID anywhere outside of the classifieds program you will need to adjust the old ID to the new ID.","AWPCP");
				$output .= "<br/>";
				$output .= __("Number of duplicate pages","AWPCP");
				$output .= ": [<b>$awpcp_classifieds_page_conflict_check</b>]";
				$output .= "<br/>";
				$output .= __("Duplicated post name","AWPCP");
				$output .= ":[<b>$awpcppagename</b>]";
				$output .= "<p><a href=\"?page=Configure1&action=recreatepage\">";
				$output .= __("Recreate the classifieds page to fix the conflict","AWPCP");
				$output .= "</a></p></div>";
			}

			$output .= "<div style=\"float:left;width:50%;\">";
			$output .= "<div class=\"postbox\">";
			$output .= "<div style=\"background:#eeeeee; padding:10px;color:#444444;\"><strong>";
			$output .= __("Another Wordpress Classifieds Plugin Stats","AWPCP");
			$output .= "</strong></div>";

			$totallistings=countlistings();
			$output .= "<div style=\"padding:10px;\">";
			$output .= __("Number of listings currently in the system","AWPCP");
			$output .= ": [<b>$totallistings</b>]";
			$output .= "</div>";

			if (get_awpcp_option('freepay') == 1)
			{
				if (adtermsset())
				{
					$output .= "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
					$output .= __("You have setup your listing fees. To edit your fees use the 'Manage Listing Fees' option.","AWPCP");
					$output .= "</div>";
				}
				else
				{
					$output .= "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
					$output .= __("You have not configured your Listing fees. Use the 'Manage Listing Fees' option to set up your listing fees. Once that is completed, if you are running in pay mode, the options will automatically appear on the listing form for users to fill out.","AWPCP");
					$output .= "</div>";
				}
			}
			else
			{
				$output .= "<div style=\"padding:10px;\">";
				$output .= __("You currently have your system configured to run in free mode. To change to 'pay' mode go to 'Manage General Options' and Check the box labeled 'Charge listing fee? (Pay Mode)'","AWPCP");
				$output .= "</div>";
			}
			if (categoriesexist())
			{
				$totalcategories=countcategories();
				$totalparentcategories=countcategoriesparents();
				$totalchildrencategories=countcategorieschildren();

				$output .= "<div style=\"padding:10px;border-top:1px solid #dddddd;\"><ul>";
				$output .= "<li style=\"margin-bottom:6px;list-style:none;\">";
				$output .= __("Total number of categories in the system","AWPCP");
				$output .= ": [<b>$totalcategories</b>]</li>";
				$output .= "<li style=\"margin-bottom:6px;list-style:none;\">";
				$output .= __("Number of Top Level parent categories","AWPCP");
				$output .= ": [<b>$totalparentcategories</b>]</li>";
				$output .= "<li style=\"margin-bottom:6px;list-style:none;\">";
				$output .= __("Number of sub level children categories","AWPCP");
				$output .= ": [<b>$totalchildrencategories</b>]</li>";
				$output .= "</ul><p>";
				$output .= __("Use the 'Manage Categories' option to edit/delete current categories or add new categories.","AWPCP");
				$output .= "</p></div>";
			}
			else
			{
				$output .= "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
				$output .= __("You have not setup any categories. Use the 'Manage Categories' option to set up your categories.","AWPCP");
				$output .= "</div>";
			}

			if (get_awpcp_option('freepay') == 1)
			{
				$output .= "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
				$output .= __("You currently have your system configured to run in pay mode. To change to 'free' mode go to 'Manage General Options' and check the box that accompanies the text 'Charge listing fee?'","AWPCP");
				$output .= "</div>";
			}

			$output .= "<div style=\"padding:10px;border-top:1px solid #dddddd;\">";
			$output .= __("Use the buttons on the right to configure your various options","AWPCP");
			$output .= "</div>";
			$output .= "</div>";

			if (get_awpcp_option('showlatestawpcpnews'))
			{
				$output .= "<div class=\"postbox\">";
				$output .= "<div style=\"background:#eeeeee; padding:10px;color:#444444;\"><strong>";
				$output .= __("Latest News About Another Wordpress Classifieds Plugin","AWPCP");
				$output .= "</strong></div>";

				$awpcpwidgets = get_option( 'dashboard_widget_options' );
				@extract( @$awpcpwidgets['dashboard_secondary'], EXTR_SKIP );
				$awpcpfeedurl="http://feeds2.feedburner.com/Awpcp";
				$awpcpgetrss = @fetch_feed( $awpcpfeedurl );
				if ( is_wp_error($awpcpgetrss) ) {
					if ( is_admin() || current_user_can('manage_options') ) {
						$output .= '<div class="rss-widget"><p>';
						printf(__('<strong>RSS Error</strong>: %s'), $awpcpgetrss->get_error_message());
						$output .= '</p></div>';
					}
				} else {
				    // Figure out how many total items there are, but limit it to 5. 
				    $maxitems = $awpcpgetrss->get_item_quantity(5); 
				    // Build an array of all the items, starting with element 0 (first element).
				    $rss_items = $awpcpgetrss->get_items(0, $maxitems); 
					$output .= '<div style="padding:10px;"><ul>';
					if ($maxitems == 0) {
						$output .= '<li>No news right now.</li>';
					} else {
					    // Loop through each feed item and display each item as a hyperlink.
					    foreach ( $rss_items as $item ) {
					    	$title = 'AWPCP News '.$item->get_date('j F Y | g:i a').': '.$item->get_title();
					    	$excerpt = $item->get_description();
					    	$output .= '<li><a href='.$item->get_permalink().' title='.title.'>'.$title.'</a><br/>'.$excerpt.'<br/><br/></li>';
					    }
					}			    
					$output .= '</ul></div>';
				}
				$output .= "</div>";
			}
			$output .= "
</div>
</div>
<div style=\"float:left;width:30%;margin:0 0 0 20px;\">
<ul>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure1\">";$output .= __("Manage General Options","AWPCP"); $output .= "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure2\">";$output .= __("Manage Listing Fees","AWPCP"); $output .= "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Configure3\">";$output .= __("Manage Categories","AWPCP"); $output .= "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage1\">";$output .= __("Manage Listings","AWPCP"); $output .= "</a></li>
<li style=\"background:url(".AWPCPURL."/images/menulist.gif) no-repeat;width:193px;height:40px;text-align:center;padding-top:10px;\"><a style=\"font-size:12px;text-decoration:none;\" href=\"?page=Manage2\">";$output .= __("Manage Images","AWPCP"); $output .= "</a></li>
</ul>";

			if (get_awpcp_option('showlatestawpcpnews'))
			{
				$output .= "<p><a href=\"http://www.awpcp.com/forum\">";
				$output .= __("Plugin Support Site","AWPCP");
				$output .= "</a></p>";
				$output .= "<p><b>";
				$output .= __("Premium Modules","AWPCP"); 
				$output .= "</b></p><em>";
				$output .= __("Installed","AWPCP");
				$output .= "</em><br/><ul>";
				if ( ($hasregionsmodule != 1) && ($hascaticonsmodule != 1) && ($hasgooglecheckoutmodule != 1) && ($hasextrafieldsmodule != 1) )
				{
					$output .= "<li>"; $output .= __("No premium modules installed","AWPCP"); $output .= "</li>";
				}
				else
				{
					if ( ($hasregionsmodule == 1) )
					{
						$output .= "<li>"; $output .= __("Regions Control Module","AWPCP"); $output .= "</li>";
					}
					if ( ($hascaticonsmodule == 1) )
					{
						$output .= "<li>"; $output .= __("Category Icons Module","AWPCP"); $output .= "</li>";
					}
					if ( ($hasgooglecheckoutmodule == 1) )
					{
						$output .= "<li>"; $output .= __("Google Checkout Module","AWPCP"); $output .= "</li>";
					}
					if ( ($hasextrafieldsmodule == 1) )
					{
						$output .= "<li>"; $output .= __("Extra Fields Module","AWPCP"); $output .= "</li>";
					}
				}

				$output .= "</ul><em>"; $output .= __("Uninstalled","AWPCP"); $output .= "</em><ul>";

				if ( ($hasregionsmodule != 1) )
				{
					$output .= "<li><a href=\"http://www.awpcp.com/premium-modules/regions-control-module\">"; $output .= __("Regions Control Module","AWPCP"); $output .= "</a></li>";
				}
				if ( ($hascaticonsmodule != 1) )
				{
					$output .= "<li><a href=\"http://www.awpcp.com/premium-modules/category-icons-module/\">"; $output .= __("Category Icons Module","AWPCP"); $output .= "</a></li>";
				}
				if ( ($hasgooglecheckoutmodule != 1) )
				{
					$output .= "<li><a href=\"http://www.awpcp.com/premium-modules/google-checkout-module/\">"; $output .= __("Google Checkout Module","AWPCP"); $output .= "</a></li>";
				}
				if ( ($hasextrafieldsmodule != 1) )
				{
					$output .= "<li><a href=\"http://www.awpcp.com/premium-modules/extra-fields-module/\">"; $output .= __("Extra Fields Module","AWPCP"); $output .= "</a></li>";
				}
				if ( ($hasregionsmodule == 1) && ($hascaticonsmodule == 1) && ($hasgooglecheckoutmodule == 1) && ($hasextrafieldsmodule == 1) )
				{
					$output .= "<li>"; $output .= __("No uninstalled premium modules","AWPCP"); $output .= "</li>";
				}

				$output .= "</ul><p><b>"; 
				$output .= __("Other Modules","AWPCP"); 
				$output .= "</b></p><em>"; 
				$output .= __("Installed","AWPCP"); 
				$output .= "</em><br/><ul>";

				if ( ($haspoweredbyremovalmodule != 1) )
				{
					$output .= "<li>"; 
					$output .= __("No [Other] modules installed","AWPCP"); 
					$output .= "</li>";
				}
				else
				{
					if ( ($haspoweredbyremovalmodule == 1) )
					{
						$output .= "<li>"; 
						$output .= __("Powered-By Link Removal Module","AWPCP"); 
						$output .= "</li>";
					}
				}

				$output .= "</ul><em>"; $output .= __("Uninstalled","AWPCP"); $output .= "</em><ul>";

				if ( ($haspoweredbyremovalmodule != 1) )
				{
					$output .= "<li><a href=\"http://www.awpcp.com/premium-modules/powered-by-link-removal-module/\">"; 
					$output .= __("Powered-By Link Removal Module","AWPCP"); 
					$output .= "</a></li>";
				}
				else
				{
					$output .= __("No uninstalled [Other] modules","AWPCP");
				}
				$output .= "</ul>";
			}
			$output .= "</div></div>";
		}
	}
	//Echo OK here
	echo $output;
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
	$output = '';
	global $wpdb,$table_prefix;
	global $message;
	if (isset($_REQUEST['mspgs']) && !empty($_REQUEST['mspgs']) )
	{
		$makesubpages=$_REQUEST['mspgs'];
	}

	if (!isset($makesubpages) && empty($makesubpages))
	{
		$makesubpages='';
	}

	if (isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
	{
		if ($_REQUEST['action'] == 'recreatepage')
		{
			$cpagename_awpcp=get_awpcp_option('userpagename');
			$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

			$pageswithawpcpname=array();

			$query="SELECT ID FROM {$table_prefix}posts WHERE post_title='$cpagename_awpcp' AND post_name = '$awpcppagename' AND post_content LIKE '%AWPCP%'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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

			$output .= "<div style=\"padding:50px;font-weight:bold;\"><p>";
			$output .= __("The page has been recreated","AWPCP");
			$output .= "</p><h3><a href=\"?page=awpcp.php\">";
			$output .= __("Back to Control Panel","AWPCP");
			$output .= "</a></h3></div>";
			die;

		}

	}

	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";

	/////////////////////////////////
	// Start the page display
	/////////////////////////////////

	$output .= "<div class=\"wrap\">
	<h2>";
	$output .= __("AWPCP Classifieds Management System Settings Configuration","AWPCP");
	$output .= "</h2>
	$message <p style=\"padding:10px;\">";
	$output .= __("Below you can modify the settings for your classifieds system. With options including turning on/off images in ads, turning on/off HTML in ads, including adsense in ads (will insert 468X60 text ad above ad content and 468X60 image ad below ad content). Also provide your PayPal business email and 2Checkout ID. Google Checkout is also supported via Premium Module.","AWPCP");
	$output .= "</p>";
	$output .= "<div style=\"width:90%;margin:0 auto;display:block;padding:5px;\"><ul>";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=1\">";
	$output .= __("General Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=10\">";
	$output .= __("Classified Pages Setup","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=11\">";
	$output .= __("SEO Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=2\">";
	$output .= __("Ad/Listing Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=3\">";
	$output .= __(" Payment Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=4\">";
	$output .= __(" Image Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=5\">";
	$output .= __(" Adsense Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=6\">";
	$output .= __(" Optional Form Field Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=7\">";
	$output .= __(" Registration Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=8\">";
	$output .= __(" Email Text Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "<li style=\"text-align:center;float:left; list-style:none; padding:10px; margin-right:10px; width:180px; background: #f2f2f2;\"><a style=\"text-decoration:none;text-align:center;\" href=\"?page=Configure1&cgid=9\">";
	$output .= __(" SMTP Settings","AWPCP");
	$output .= "</a></li> ";
	$output .= "</ul></div><div style=\"clear:both;\"></div>";
	$output .= "
	<form method=\"post\" id=\"awpcp_launch\">
	<p><input class=\"button\" name=\"savesettings\" type=\"submit\" value=\"";
	$output .= __("Save Settings","AWPCP");
	$output .= "\" /></p>";

	//////////////////////////////////////
	// Retrieve the currently saved data
	/////////////////////////////////////
	if (!isset($_REQUEST['cgid']) && empty($_REQUEST['cgid'])){ $cgid=10;} else { $cgid=$_REQUEST['cgid']; }

	$query="SELECT config_option,config_value,config_diz,option_type FROM ".$tbl_ad_settings." WHERE config_group_id='$cgid'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

	/////////////////////////////////////////
	// Setup the data items for display
	/////////////////////////////////////////

	$options=array();

	while($rsrow=mysql_fetch_row($res)) {
		list($config_option,$config_value,$config_diz,$option_type)=$rsrow;

		$config_value=str_replace("\"","&quot;",$config_value);

		if ($config_option == 'smtppassword')
		{
			if (get_awpcp_option('smtppassword') )
			{
				$config_diz.="<br><b>**";
				$output .= __("Your password is saved but not shown below. Leave the field blank unless you are changing your SMTP password","AWPCP");
				$output .= "</b>";
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
		}elseif ($option_type==3) {	// radio input
			$field="";
			if ($config_option == 'groupbrowseadsby')
			{
				$orderbyops=array('1','2','3','4','5','6');
				foreach($orderbyops as $orderbyop)
				{
					if ($orderbyop == 1){ $orderbyoptext=__("Most Recent","AWPCP");}
					if ($orderbyop == 2){ $orderbyoptext=__("Title","AWPCP");}
					if ($orderbyop == 3){ $orderbyoptext=__("Paid first then most recent","AWPCP");}
					if ($orderbyop == 4){ $orderbyoptext=__("Paid first then title","AWPCP");}
					if ($orderbyop == 5){ $orderbyoptext=__("Most viewed then title","AWPCP");}
					if ($orderbyop == 6){ $orderbyoptext=__("Most viewed then most recent","AWPCP");}

					if ($config_value == $orderbyop){$checked="checked";} else { $checked="";}
					$field.="<br/><input name=\"$config_option\" type=\"radio\" value=\"$orderbyop\" $checked />$orderbyoptext";
				}
			}
			if ($config_option == 'groupsearchresultsby')
			{
				$orderbyops=array('1','2','3','4','5','6');
				foreach($orderbyops as $orderbyop)
				{
					if ($orderbyop == 1){ $orderbyoptext=__("Most Recent","AWPCP");}
					if ($orderbyop == 2){ $orderbyoptext=__("Title","AWPCP");}
					if ($orderbyop == 3){ $orderbyoptext=__("Paid first then most recent","AWPCP");}
					if ($orderbyop == 4){ $orderbyoptext=__("Paid first then title","AWPCP");}
					if ($orderbyop == 5){ $orderbyoptext=__("Most viewed then title","AWPCP");}
					if ($orderbyop == 6){ $orderbyoptext=__("Most viewed then most recent","AWPCP");}

					if ($config_value == $orderbyop){$checked="checked";} else { $checked="";}

					$field.="<br/><input name=\"$config_option\" type=\"radio\" value=\"$orderbyop\" $checked />$orderbyoptext";
				}
			}
		}

		/////////////////////////////////////////
		// Display the data items
		////////////////////////////////////////

		$output .= "
	<p style=\"display:block;margin-bottom:25px;\">
	<div style=\"padding:5px;width:75%;\">$config_diz $field</div>
	</p>";
	}

	$output .= "
	<input type=\"hidden\" name=\"cgid\" value=\"$cgid\" />
	<input type=\"hidden\" name=\"makesubpages\" value=\"$makesubpages\" />
	<p><input class=\"button\" name=\"savesettings\" type=\"submit\" value=\"";
	$output .= __("Save Settings","AWPCP");
	$output .= "\" /></p></form></div>";
	//Echo OK here
	echo $output;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage general configuration options
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_fees()
{
	$output = '';
	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		$output .= $awpcpsetuptext;

	} else {

		global $wpdb;
		global $message;

		$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";


		/////////////////////////////////
		// Start the page display
		/////////////////////////////////

		$output .= "<div class=\"wrap\">";
		$output .= "<h2>";
		$output .= __("AWPCP Classifieds Management System: Listing Fees Management","AWPCP");
		$output .= "</h2>";
		if (isset($message) && !empty($message))
		{
			$output .= $message;
		}
		$output .= "<p style=\"padding:10px;\">";
	 $output .= __("Below you can add and edit your listing fees. As an example you can add an entry set at $9.99 for a 30 day listing, then another entry set at $17.99 for a 60 day listing. For each entry you can set a specific number of images a user can upload. If you have allow images turned off in your main configuration settings the value you add here will not matter as an upload option will not be included in the ad post form. You can also set a text limit for the ads. The value is in words.","AWPCP");
	 $output .= "</p>";

	 ///////////////////////////////////////
	 // Handle case of adding new settings

	 $rec_increment_op="<option value=\"D\">";
	 $rec_increment_op.=__("Days","AWPCP");
	 $rec_increment_op.="</option>\n";//////////////////////////////////////

	 if (isset($_REQUEST['addnewlistingfeeplan']) && !empty($_REQUEST['addnewlistingfeeplan']))
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

	 	$output .= "<div class=\"postbox\" style=\"padding:20px; width:300px;\">$awpcpfeeform</div>";

	 	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
	 	$message.=__("The new plan has been added!","AWPCP");
	 	$message.="</div>";
	 }

	 else
	 {

			//////////////////////////////////////
			// Retrieve the currently saved data
			/////////////////////////////////////
	 	$output .= "<ul>";

	 	$query="SELECT adterm_id,adterm_name,amount,rec_period,rec_increment,imagesallowed FROM ".$tbl_ad_fees."";
	 	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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
	 			$awpcpfeeform.="<input class=\"button\" type=\"submit\" name=\"savefeesetting\" value=\"";
	 			$awpcpfeeform.=__("Update Plan","AWPCP");
	 			$awpcpfeeform.="\" />";
	 			$awpcpfeeform.="<input type=\"hidden\" name=\"adterm_id\" value=\"$adterm_id\">";
	 			$awpcpfeeform.="<input class=\"button\" type=\"submit\" name=\"deletefeesetting\" value=\"";
	 			$awpcpfeeform.=__("Delete Plan","AWPCP");
	 			$awpcpfeeform.="\" />";
	 			$awpcpfeeform.="</form>";

	 			$output .= "<li class=\"postbox\" style=\"float:left;width:280px;padding:10px; margin-right:20px;\">$awpcpfeeform</li>";
	 		}

	 		$output .= "</ul>";
	 	}


			$output .= "<div style=\"clear:both;\"></div>
			<form method=\"post\" id=\"awpcp_opsconfig_fees\">
			<p style=\"padding:10px;\"><input class=\"button\" type=\"submit\" name=\"addnewlistingfeeplan\" value=\"";
			$output .= __("Add a new listing fee plan","AWPCP");
			$output .= "\" /></p></form>";
		}
		$output .= "</div><br/>";

	}
	//Echo OK here
	echo $output;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage existing listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_opsconfig_categories()
{
	$output = '';
	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');
	$action='';

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		$output .= $awpcpsetuptext;

	} else {

		global $wpdb, $message, $awpcp_imagesurl, $clearform,$hascaticonsmodule;

		$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";
		$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
		$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=10);


		$cat_ID='';
		$category_name='';
		$aeaction='';
		$category_parent_id='';
		$promptmovetocat='';
		$aeaction='';

		///////////////////////////////////////////////////
		// Check for existence of a category ID and action

		if ( isset($_REQUEST['editcat']) && !empty($_REQUEST['editcat']) )
		{
			$cat_ID=$_REQUEST['editcat'];
			$action = "edit";
		}
		elseif ( isset($_REQUEST['delcat']) && !empty($_REQUEST['delcat']) )
		{
			$cat_ID=$_REQUEST['delcat'];
			$action = "delcat";
		}
		elseif ( isset($_REQUEST['managecaticon']) && !empty($_REQUEST['managecaticon']) )
		{
			$cat_ID=$_REQUEST['managecaticon'];
			$action = "managecaticon";
		}
		elseif (isset($_REQUEST['cat_ID']) && !empty($_REQUEST['cat_ID']))
		{
			$cat_ID=$_REQUEST['cat_ID'];
		}


		if ( !isset($action)  || empty($action) )
		{
			if ( isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
			{
				$action=$_REQUEST['action'];
			}

		}
		if ( $action == 'edit' )
		{
			$aeaction='edit';
		}

		if ( $action == 'editcat' )
		{
			$aeaction='edit';
		}

		if ( $action == 'delcat' )
		{
			$aeaction='delete';
		}

		if ( $action == 'managecaticon' )
		{

			$output .= "<div class=\"wrap\"><h2>";
			$output .= __("AWPCP Classifieds Management System Categories Management","AWPCP");
			$output .= "</h2>
			";

			global $awpcp_plugin_path;

			if ($hascaticonsmodule == 1 )
			{
				if ( is_installed_category_icon_module() )
				{
					$output .= load_category_icon_management_page($defaultid=$cat_ID,$offset,$results);
				}
			}

			$output .= "</div>";
			return $output;
			//die;
		}

		if ( $action == 'setcategoryicon' )
		{

			global $awpcp_plugin_path;

			if ($hascaticonsmodule == 1 )
			{
				if ( is_installed_category_icon_module() )
				{


					if ( isset($_REQUEST['cat_ID']) && !empty($_REQUEST['cat_ID']) )
					{
						$thecategory_id=$_REQUEST['cat_ID'];
					}

					if ( isset($_REQUEST['category_icon']) && !empty($_REQUEST['category_icon']) )
					{
						$theiconfile=$_REQUEST['category_icon'];
					}

					if ( isset($_REQUEST['offset']) && !empty($_REQUEST['offset']) )
					{
						$offset=$_REQUEST['offset'];
					}

					if ( isset($_REQUEST['results']) && !empty($_REQUEST['results']) )
					{
						$results=$_REQUEST['results'];
					}

					$message=set_category_icon($thecategory_id,$theiconfile,$offset,$results);
					if ( isset($message) && !empty($message) )
					{
						$clearform=1;
					}
				}
			}
		}

		if ( isset($clearform) && ( $clearform == 1) )
		{
			unset($cat_ID,$action, $aeaction);
		}

		$category_name=get_adcatname($cat_ID);
		$category_order=get_adcatorder($cat_ID);
		$category_order = ($category_order != 0 ? $category_order : 0);
		$cat_parent_ID=get_cat_parent_ID($cat_ID);

		if ($aeaction == 'edit')
		{
			$aeword1=__("You are currently editing the category shown below","AWPCP");
			$aeword2=__("Save Category Changes","AWPCP");
			$aeword3=__("Parent Category","AWPCP");
			$aeword4=__("Category List Order","AWPCP");
			$addnewlink="<a href=\"?page=Configure3\">";
			$addnewlink.=__("Add A New Category","AWPCP");
			$addnewlink.="</a>";
		}
		elseif ($aeaction == 'delete')
		{
			if ( $cat_ID != 1)
			{
				$aeword1=__("If you're sure that you want to delete this category please press the delete button","AWPCP");
				$aeword2=__("Delete Category","AWPCP");
				$aeword3=__("Parent Category","AWPCP");
				$aeword4='';
				$addnewlink="<a href=\"?page=Configure3\">";
				$addnewlink.=__("Add A New Category","AWPCP");
				$addnewlink.="</a>";

				if (ads_exist_cat($cat_ID))
				{
					if ( category_is_child($cat_ID) ) {
						$movetocat=get_cat_parent_ID($cat_ID);
					}
					else
					{
						$movetocat=1;
					}

					$movetoname=get_adcatname($movetocat);
					if ( empty($movetoname) )
					{
						$movetoname=__("Untitled","AWPCP");
					}

					$promptmovetocat="<p>";
					$promptmovetocat.=__("The category contains ads. If you do not select a category to move them to the ads will be moved to:","AWPCP");
					$promptmovetocat.="<b>$movetoname</b></p>";

					$defaultcatname=get_adcatname($catid=1);

					if ( empty($defaultcatname) )
					{
						$defaultcatname=__("Untitled","AWPCP");
					}

					if (category_has_children($cat_ID))
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
				$aeword4='';
				$addnewlink="<a href=\"?page=Configure3\">";
				$addnewlink.=__("Add A New Category","AWPCP");
				$addnewlink.="</a>";
			}
		}
		else
		{
			if ( empty($aeaction) )
			{
				$aeaction="newcategory";
			}

			$aeword1=__("Enter the category name","AWPCP");
			$aeword2=__("Add New Category","AWPCP");
			$aeword3=__("List Category Under","AWPCP");
			$aeword4=__("Category List Order","AWPCP");
			$addnewlink='';
		}
		if ($aeaction == 'delete')
		{
			$orderinput='';
			if ($cat_ID == 1)
			{
				$categorynameinput='';
				$selectinput='';
			}
			else
			{
				$categorynameinput="<p style=\"background:transparent url($awpcp_imagesurl/delete_ico.png) left center no-repeat;padding-left:20px;\">";
				$categorynameinput.=__("Category to Delete","AWPCP");
				$categorynameinput.=": $category_name</p>";
				$selectinput="<p style=\"background:#D54E21;padding:3px;color:#ffffff;\">$thecategoryparentname</p>";
				$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
			}
		}
		elseif ($aeaction == 'edit')
		{
			$categorynameinput="<p style=\"background:transparent url($awpcp_imagesurl/edit_ico.png) left center no-repeat;padding-left:20px;\">";
			$categorynameinput.=__("Category to Edit","AWPCP");
			$categorynameinput.=": $category_name</p><p><input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/></p>";
			$selectinput="<p><select name=\"category_parent_id\"><option value=\"0\">";
			$selectinput.=__("Make This a Top Level Category","AWPCP");
			$selectinput.="</option>";
			$orderinput="<p><input name=\"category_order\" id=\"category_order\" type=\"text\" class=\"inputbox\" value=\"$category_order\" size=\"3\"/></p>";
			$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude='');
			$selectinput.="$categories
						</select></p>";
			$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
		}
		else {
			$categorynameinput="<p style=\"background:transparent url($awpcp_imagesurl/post_ico.png) left center no-repeat;padding-left:20px;\">";
			$categorynameinput.=__("Add a New Category","AWPCP");
			$categorynameinput.="</p><input name=\"category_name\" id=\"cat_name\" type=\"text\" class=\"inputbox\" value=\"$category_name\" size=\"40\"/>";
			$selectinput="<p><select name=\"category_parent_id\"><option value=\"0\">";
			$selectinput.=__("Make This a Top Level Category","AWPCP");
			$selectinput.="</option>";
			$orderinput="<p><input name=\"category_order\" id=\"category_order\" type=\"text\" class=\"inputbox\" value=\"$category_order\" size=\"3\"/></p>";
			$categories=  get_categorynameid($cat_ID,$cat_parent_ID,$exclude='');
			$selectinput.="$categories
					</select></p>";
			$submitbuttoncode="<input type=\"submit\" class=\"button\" name=\"createeditadcategory\" value=\"$aeword2\" />";
		}

		/////////////////////////////////
		// Start the page display
		/////////////////////////////////

		$output .= "<div class=\"wrap\"><h2>";
		$output .= __("AWPCP Classifieds Management System Categories Management","AWPCP");
		$output .= "</h2>";
		if (isset($message) && !empty($message))
		{
			$output .= $message;
		}
		$output .= "<div style=\"padding:10px;\"><p>";
		$output .= __("Below you can add and edit your categories. For more information about managing your categories visit the link below.","AWPCP");
		$output .= "</p><p><a href=\"http://www.awpcp.com/about/categories/\">";
		$output .= __("Useful Information for Classifieds Categories Management","AWPCP");
		$output .= "</a></p><b>";
		$output .= __("Icon Meanings","AWPCP");
		$output .= ":</b> &nbsp;&nbsp;&nbsp;<img src=\"$awpcp_imagesurl/edit_ico.png\" alt=\"";
		$output .= __("Edit Category","AWPCP");
		$output .= "\" border=\"0\">";
		$output .= __("Edit Category","AWPCP");
		$output .= " &nbsp;&nbsp;&nbsp;<img src=\"$awpcp_imagesurl/delete_ico.png\" alt=\"";
		$output .= __("Delete Category","AWPCP");
		$output .= "\" border=\"0\">";
		$output .= __("Delete Category","AWPCP");


		if ($hascaticonsmodule == 1 )
		{
			if ( is_installed_category_icon_module() )
			{
				$output .= " &nbsp;&nbsp;&nbsp;<img src=\"$awpcp_imagesurl/icon_manage_ico.png\" alt=\"";
				$output .= __("Manage Category Icon","AWPCP");
				$output .= "\" border=\"0\">";
				$output .= __("Manage Category icon","AWPCP");
			}
		}


		if ($hascaticonsmodule != 1 )
		{
			$output .= "<div class=\"fixfloat\"><p style=\"padding-top:25px;\">";
			$output .= __("There is a premium module available that allows you to add icons to your categories. If you are interested in adding icons to your categories ","AWPCP");
			$output .= "<a href=\"http://www.awpcp.com/premium-modules/\">";
			$output .= __("Click here to find out about purchasing the Category Icons Module","AWPCP");
			$output .= "</a></p></div>";
		}

		$output .= "
			 </div>
			 <div class=\"postbox\" style=\"width:30%;float:left;padding:10px;\">
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
			<p> $aeword4</p>
			$orderinput
			<p style=\"margin-top:5px;\" class=\"submit\">$submitbuttoncode $addnewlink</p>
			 </form>
			 </div>
			 <div style=\"margin:0;padding:0px 0px 10px 10px;float:left;width:60%\">";

			///////////////////////////////////////////////////////////
			// Show the paginated categories list for management
			//////////////////////////////////////////////////////////

			$from="$tbl_ad_categories";
			$where="category_name <> ''";

			$pager1=create_pager($from,$where,$offset,$results,$tpname='');
			$pager2=create_pager($from,$where,$offset,$results,$tpname='');

			$output .= "$pager1 <form name=\"mycats\" id=\"mycats\" method=\"post\">
			 <p><input type=\"submit\" name=\"deletemultiplecategories\" class=\"button\" value=\"";
			$output .= __("Delete Selected Categories","AWPCP");
			$output .= "\" />
			 <input type=\"submit\" name=\"movemultiplecategories\" class=\"button\" value=\"";
			$output .= __("Move Selected Categories","AWPCP");
			$output .= "\" />
			 <select name=\"moveadstocategory\"><option value=\"0\">";
			$output .= __("Select Move-To category","AWPCP");
			$output .= "</option>";
			$movetocategories=  get_categorynameid($cat_id = 0,$cat_parent_id= 0,$exclude);
			$output .= "$movetocategories</select></p>
			<p>";
			$output .= __("If deleting categories","AWPCP");
			$output .= "<input type=\"radio\" name=\"movedeleteads\" value=\"1\" checked />";
			$output .= __("Move Ads if any","AWPCP");
			$output .= "<input type=\"radio\" name=\"movedeleteads\" value=\"2\" />";
			$output .= __("Delete Ads if any","AWPCP");
			$output .= "</p>";

			$items=array();
			$query="SELECT category_id,category_name,category_parent_id,category_order FROM $from WHERE $where ORDER BY category_order,category_name ASC LIMIT $offset,$results";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$thecategoryicon='';

				if ( function_exists('get_category_icon') )
				{
					$category_icon=get_category_icon($rsrow[0]);
				}

				if ( isset($category_icon) && !empty($category_icon) )
				{
					$caticonsurl="$awpcp_imagesurl/caticons/$category_icon";
					$thecategoryicon="<img style=\"vertical-align:middle;margin-right:5px;\" src=\"$caticonsurl\" alt=\"$rsrow[1]\" border=\"0\">";
				}

				$thecategory_id=$rsrow[0];
				$thecategory_name="$thecategoryicon<a href=\"?page=Manage1&showadsfromcat_id=".$rsrow[0]."\">".$rsrow[1]."</a>";
				$thecategory_parent_id=$rsrow[2];
				$thecategory_order=($rsrow[3] != '' ? $rsrow[3] : '0');
				
				$thecategory_parent_name=get_adparentcatname($thecategory_parent_id);
				$totaladsincat=total_ads_in_cat($thecategory_id);

				if ($hascaticonsmodule == 1 )
				{
					if ( is_installed_category_icon_module() )
					{
						$managecaticon="<a href=\"?page=Configure3&cat_ID=$thecategory_id&action=managecaticon&offset=$offset&results=$results\"><img src=\"$awpcp_imagesurl/icon_manage_ico.png\" alt=\"";
						$managecaticon.=__("Manage Category Icon","AWPCP");
						$managecaticon.="\" border=\"0\"></a>";
					}
				}
				$awpcpeditcategoryword=__("Edit Category","AWPCP");
				$awpcpdeletecategoryword=__("Delete Category","AWPCP");

				$items[]="<tr><td style=\"width:40%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\"><input type=\"checkbox\" name=\"category_to_delete_or_move[]\" value=\"$thecategory_id\" />$thecategory_name ($totaladsincat)</td>
					<td style=\"width:35%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\">$thecategory_parent_name</td>
					<td style=\"width:5%;padding:5px;border-bottom:1px dotted #dddddd;font-weight:normal;\">$thecategory_order</td>
					<td style=\"padding:5px;border-bottom:1px dotted #dddddd;font-size:smaller;font-weight:normal;\"> <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=editcat&offset=$offset&results=$results\"><img src=\"$awpcp_imagesurl/edit_ico.png\" alt=\"$awpcpeditcategoryword\" border=\"0\"></a> <a href=\"?page=Configure3&cat_ID=$thecategory_id&action=delcat&offset=$offset&results=$results\"><img src=\"$awpcp_imagesurl/delete_ico.png\" alt=\"$awpcpdeletecategoryword\" border=\"0\"></a> $managecaticon</td></tr>";
			}

			$opentable="<table class=\"listcatsh\"><tr><td style=\"width:40%;padding:5px;\"><input type=\"checkbox\" onclick=\"CheckAll()\" />";
			$opentable.=__("Category Name (Total Ads)","AWPCP");
			$opentable.="</td><td style=\"width:35%;padding:5px;\">";
			$opentable.=__("Parent","AWPCP");
			$opentable.="</td><td style=\"width:5%;padding:5px;\">";
			$opentable.=__("Order","AWPCP");
			$opentable.="</td><td style=\"width:20%;padding:5px;;\">";
			$opentable.=__("Action","AWPCP");
			$opentable.="</td></tr>";
			$closetable="<tr><td style=\"width:40%;padding:5px;\">";
			$closetable.=__("Category Name (Total Ads)","AWPCP");
			$closetable.="</td><td style=\"width:35%;padding:5px;\">";
			$closetable.=__("Parent","AWPCP");
			$closetable.="</td><td style=\"width:5%;padding:5px;\">";
			$closetable.=__("Order","AWPCP");
			$closetable.="</td><td style=\"width:20%;padding:5px;\">";
			$closetable.=__("Action","AWPCP");
			$closetable.="</td></tr></table>";

			$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
			$showcategories="$theitems";

			$output .= "
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
	//Echo OK here:
	echo $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage view images
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_manage_viewimages()
{
	$output = '';
	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');
	$laction='';

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		$output .= $awpcpsetuptext;

	} else {

		global $message,$wpdb;
		$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
		$where='';

		$output .= "<div class=\"wrap\"><h2>";
		$output .= __("AWPCP Classifieds Management System Manage Images","AWPCP");
		$output .= "</h2>";
		if (isset($message) && !empty($message))
		{
			$output .= $message;
		}
		$output .= "<p style=\"padding:10px;border:1px solid#dddddd;\">";
		$output .= __("Below you can manage the images users have uploaded. Your options are to delete images, and in the event you are operating with image approval turned on you can approve or disable images","AWPCP");
		$output .= "</p>";

		if (isset($_REQUEST['pdel']) && !empty( $_REQUEST['pdel'] ) )
		{
			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">?>";
			$output .= __("The image was deleted successfully","AWPCP");
			$output .= "</div>";
		}


		if (isset($_REQUEST['action']) && !empty($_REQUEST['action']))
		{
			$laction=$_REQUEST['action'];
		}

		if (empty($_REQUEST['action']))
		{
			if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
			{
				$laction=$_REQUEST['a'];
			}
		}

		if (isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$actonid=$_REQUEST['id'];
			$where="ad_id='$actonid'";
		}
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$adid=$_REQUEST['adid'];
		}
		if (isset($_REQUEST['picid']) && !empty($_REQUEST['picid']))
		{
			$picid=$_REQUEST['picid'];
		}
		if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid']))
		{
			$adtermid=$_REQUEST['adtermid'];
		}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
		{
			$adkey=$_REQUEST['adkey'];
		}
		if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail']))
		{
			$editemail=$_REQUEST['editemail'];
		}
		if (isset($_REQUEST['offset']) && !empty($_REQUEST['offset']))
		{
			$offset=$_REQUEST['offset'];
		}
		if (isset($_REQUEST['results']) && !empty($_REQUEST['results']))
		{
			$editemail=$_REQUEST['results'];
		}

		if ($laction == 'approvepic')
		{

			$query="UPDATE  ".$tbl_ad_photos." SET disabled='0' WHERE ad_id='$adid' AND key_id='$picid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			$output .= __("The image has been enabled and can now be viewed","AWPCP");
			$output .= "</div>";

		}
		elseif ($laction == 'rejectpic')
		{

			$query="UPDATE  ".$tbl_ad_photos." SET disabled='1' WHERE ad_id='$adid' AND key_id='$picid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			$output .= __("The image has been disabled and can no longer be viewed","AWPCP");
			$output .= "</div>";


		}
		elseif ($laction == 'deletepic')
		{
			$message=deletepic($picid,$adid,$adtermid,$adkey,$editemail);
			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";
		}

		$output .= viewimages($where);
	}
	//Echo OK here:
	echo $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Manage view images
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Manage view listings
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_manage_viewlistings()
{
	$output = '';
	global $hasextrafieldsmodule;
	$cpagename_awpcp=get_awpcp_option('userpagename');
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');
	$laction='';

	$isclassifiedpage = checkifclassifiedpage($cpagename_awpcp);
	if ($isclassifiedpage == false)
	{
		$awpcpsetuptext=display_setup_text();
		$output .= $awpcpsetuptext;

	} else {

		global $wpdb,$awpcp_imagesurl,$message;

		$output .= "<div class=\"wrap\"><h2>";
		$output .= __("AWPCP Classifieds Management System Manage Ad Listings","AWPCP");
		$output .= "</h2>";
		if (isset($message) && !empty($message))
		{
			$output .= $message;
		}

		$tbl_ads = $wpdb->prefix . "awpcp_ads";
		$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

		if (isset($_REQUEST['action']) && !empty($_REQUEST['action']))
		{
			$laction=$_REQUEST['action'];
		}

		if (empty($_REQUEST['action']))
		{
			if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
			{
				$laction=$_REQUEST['a'];
			}
		}

		if (isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$actonid=$_REQUEST['id'];
		}


		if ($laction == 'deletead')
		{
			$message=deletead($actonid,$adkey='',$editemail='');
			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$message</div>";
		}
		elseif ($laction == 'editad')
		{
			$editemail=get_adposteremail($actonid);
			$adaccesskey=get_adkey($actonid);
			$awpcppage=get_currentpagename();
			$awpcppagename = sanitize_title($awpcppage, $post_ID='');
			$offset=clean_field($_REQUEST['offset']);
			$results=clean_field($_REQUEST['results']);
				
			$output .= load_ad_post_form($actonid,$action='editad',$awpcppagename,$adtermid='',$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
		}
		elseif ($laction == 'dopost1')
		{
			$adid=clean_field($_REQUEST['adid']);
			$adterm_id=clean_field($_REQUEST['adtermid']);
			$adkey=clean_field($_REQUEST['adkey']);
			$editemail=clean_field($_REQUEST['editemail']);
			$adtitle=clean_field($_REQUEST['adtitle']);
			$adtitle=strip_html_tags($adtitle);
			$adcontact_name=clean_field($_REQUEST['adcontact_name']);
			$adcontact_name=strip_html_tags($adcontact_name);
			$adcontact_phone=clean_field($_REQUEST['adcontact_phone']);
			$adcontact_phone=strip_html_tags($adcontact_phone);
			$adcontact_email=clean_field($_REQUEST['adcontact_email']);
			$adcategory=clean_field($_REQUEST['adcategory']);
			$adcontact_city=clean_field($_REQUEST['adcontact_city']);
			$adcontact_city=strip_html_tags($adcontact_city);
			$adcontact_state=clean_field($_REQUEST['adcontact_state']);
			$adcontact_state=strip_html_tags($adcontact_state);
			$adcontact_country=clean_field($_REQUEST['adcontact_country']);
			$adcontact_country=strip_html_tags($adcontact_country);
			$ad_county_village=clean_field($_REQUEST['adcontact_countyvillage']);
			$ad_county_village=strip_html_tags($ad_county_village);
			$ad_item_price=clean_field($_REQUEST['ad_item_price']);
			$ad_item_price=str_replace(",", '', $ad_item_price);
			$addetails=clean_field($_REQUEST['addetails']);
			$websiteurl=clean_field($_REQUEST['websiteurl']);
			$checkhuman=clean_field($_REQUEST['checkhuman']);
			$numval1=clean_field($_REQUEST['numval1']);
			$numval2=clean_field($_REQUEST['numval2']);
				
			if (get_awpcp_option('allowhtmlinadtext') == 0)
			{
				$addetails=strip_html_tags($addetails);
			}
			$adpaymethod=clean_field($_REQUEST['adpaymethod']);
			if (!isset($adpaymethod) || empty($adpaymethod))
			{
				$adpaymethod="paypal";
			}
			if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
				$adaction=clean_field($_REQUEST['adaction']);} else {$adaction='';}
				$awpcppagename=clean_field($_REQUEST['awpcppagename']);
				$offset=clean_field($_REQUEST['offset']);
				$results=clean_field($_REQUEST['results']);
				$output .= processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);
		}
		elseif ($laction == 'approvead')
		{
			$query="UPDATE  ".$tbl_ads." SET disabled='0' WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			$output .= __("The ad has been approved","AWPCP");
			$output .= "</div>";
		}
		elseif ($laction == 'rejectad')
		{
			$query="UPDATE  ".$tbl_ads." SET disabled='1' WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			$output .= __("The ad has been disabled","AWPCP");
			$output .= "</div>";
		}
		elseif ($laction == 'cps')
		{
			if (isset($_REQUEST['changeto']) && !empty($_REQUEST['changeto']))
			{
				$changeto=$_REQUEST['changeto'];
			}

			$query="UPDATE  ".$tbl_ads." SET payment_status='$changeto', disabled='0' WHERE ad_id='$actonid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
			$output .= __("The ad payment status has been changed","AWPCP");
			$output .= "</div>";

		}
		elseif ($laction == 'viewad')
		{
			if (isset($actonid) && !empty($actonid))
			{

				$output .= "<div class=\"postbox\" style=\"padding:20px;width:95%;\">";

				// start insert delete | edit | approve/disable admin links

				$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
				$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=10);

				$deletelink=  "<a href=\"?page=Manage1&action=deletead&id=$actonid&offset=$offset&results=$results\">";
				$deletelink.=__("Delete","AWPCP");
				$deletelink.="</a>";
				$editlink=" |  <a href=\"?page=Manage1&action=editad&id=$actonid&offset=$offset&results=$results\">";
				$editlink.=__("Edit","AWPCP");
				$editlink.="</a>";


				$output .= "<div style=\"padding:10px 0px;; margin-bottom:20px;\"><b>";
				$output .= __("Manage Listing: ","AWPCP");
				$output .= "</b>";
				$output .= "$deletelink $editlink";

				if (get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1)
				{
					$adstatusdisabled=check_if_ad_is_disabled($actonid);

					if ($adstatusdisabled)
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

					$output .= "$approvelink";
				}

				$output .= "</div>";

				// end insert delete | edit | approve/disable admin links
				$output .= showad($actonid,$omitmenu='1');

				$output .= "</div>";
			}
			else
			{
				$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
				$output .= __("No ad ID was supplied","AWPCP");
				$output .= "</div>";

			}

		}
		elseif ($laction == 'viewimages')
		{
			if (isset($_REQUEST['id']) && !empty($_REQUEST['id']))
			{
				$picid=$_REQUEST['id'];
				$where="ad_id='$picid'";
			}
			else
			{
				$where='';
			}

			$output .= viewimages($where);
		}
		elseif ($laction == 'lookupadby')
		{
			if (isset($_REQUEST['lookupadbychoices']) && !empty($_REQUEST['lookupadbychoices']))
			{
				$lookupadbytype=$_REQUEST['lookupadbychoices'];
			}
			else
			{
				$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
				$output .= __("You need to check whether you want to look up the ad by title id or keyword","AWPCP");
				$output .= "</div>";
			}
			if (isset($_REQUEST['lookupadidortitle']) && !empty($_REQUEST['lookupadidortitle']))
			{
				$lookupadbytypevalue=$_REQUEST['lookupadidortitle'];
			}
			else
			{
				$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">You need enter either an ad title or an ad id to look up</div>";
			}
			if ($lookupadbytype == 'adid')
			{
				if (!is_numeric($lookupadbytypevalue))
				{
					$output .= "<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">You indicated you wanted to look up the ad by ID but you entered an invalid ID. Please try again</div>";
				}
				else
				{
					$where="ad_id='$lookupadbytypevalue'";
				}
			}
			elseif ($lookupadbytype == 'adtitle')
			{
				$where="ad_title='$lookupadbytypevalue'";
			}
			elseif ($lookupadbytype == 'titdet')
			{
				$where="MATCH (ad_title,ad_details) AGAINST (\"$lookupadbytypevalue\")";
			}
			elseif ($lookupadbytype == 'location')
			{
				$where="ad_city='$lookupadbytypevalue' OR ad_state='$lookupadbytypevalue' OR ad_country='$lookupadbytypevalue' OR ad_county_village='$lookupadbytypevalue'";
			}
		}

		if (isset($_REQUEST['showadsfromcat_id']) && !empty($_REQUEST['showadsfromcat_id'])){
			$thecat_id=$_REQUEST['showadsfromcat_id'];
			$where="ad_title <> '' AND (ad_category_id='$thecat_id' OR ad_category_parent_id='$thecat_id')";
		}

		$sortby='';
		$lookupadidortitle='';
		$from="$tbl_ads";
		if (!isset($where) || empty($where))
		{
			$where="ad_title <> ''";
		}

		if (!ads_exist())
		{
			$showadstomanage="<p style=\"padding:10px\">";
			$showadstomanage.=__("There are currently no ads in the system","AWPCP");
			$showadstomanage.="</p>";
			$pager1='';
			$pager2='';
		}
		else
		{
			$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
			$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=10);


			if (isset($_REQUEST['sortby']))
			{
				$sortby=$_REQUEST['sortby'];
				if ($sortby == 'titleza')
				{
					$orderby="ad_title DESC";
				}
				elseif ($sortby == 'titleaz')
				{
					$orderby="ad_title ASC";
				}
				elseif ($sortby == 'awaitingapproval')
				{
					$orderby="disabled DESC, ad_key DESC";
				}
				elseif ($sortby == 'paidfirst')
				{
					$orderby="payment_status DESC, ad_key DESC";
				}
				elseif ($sortby == 'mostrecent')
				{
					$orderby="ad_key DESC";
				}
			}

			if (!isset($sortby) || empty($sortby))
			{
				$orderby="ad_key DESC";
			}

			$items=array();
			$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_details,ad_postdate,disabled,payment_status FROM $from WHERE $where ORDER BY $orderby LIMIT $offset,$results";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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

				if (!isset($paymentstatus) || empty($paymentstatus))
				{
					$paymentstatus="N/A";
				}

				$pager1="<p>".create_pager($from,$where,$offset,$results,$tpname='')."</p>";
				$pager2="<p>".create_pager($from,$where,$offset,$results,$tpname='')."</p>";
				$base=get_option('siteurl');
				$awpcppage=get_currentpagename();
				$awpcppagename = sanitize_title($awpcppage, $post_ID='');
				$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

				$ad_title="<input type=\"checkbox\" name=\"awpcp_ad_to_delete[]\" value=\"$ad_id\" /><a href=\"?page=Manage1&action=viewad&id=$ad_id&offset=$offset&results=$results\">".$rsrow[2]."</a>";
				$handlelink="<a href=\"?page=Manage1&action=deletead&id=$ad_id&offset=$offset&results=$results\">";
				$handlelink.=__("Delete","AWPCP");
				$handlelink.="</a> | <a href=\"?page=Manage1&action=editad&id=$ad_id&offset=$offset&results=$results\">";
				$handlelink.=__("Edit","AWPCP");
				$handlelink.="</a>";

				$approvelink='';

				if (get_awpcp_option('adapprove') == 1 || get_awpcp_option('freepay')  == 1)
				{
					if ($disabled == 1)
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


				if (get_awpcp_option('freepay') == 1)
				{
					$paymentstatushead="<th>";
					$paymentstatushead.=__("Payment Status","AWPCP");
					$paymentstatushead.="</th>";

					$changepaystatlink='';

					if ($paymentstatus == 'Pending')
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

				if (get_awpcp_option('imagesallowdisallow') == 1)
				{

					$imagesnotehead="<th>";
					$imagesnotehead.=__("Total Images","AWPCP");
					$imagesnotehead.="</th>";

					$totalimagesuploaded=get_total_imagesuploaded($ad_id);

					if ($totalimagesuploaded >= 1)
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


				$opentable="<table class=\"widefat fixed\"><thead><tr><th><input type=\"checkbox\" onclick=\"CheckAllAds()\" />";
				$opentable.=__("Ad Headline","AWPCP");
				$opentable.="</th><th>";
				$opentable.=__("Manage Ad","AWPCP");
				$opentable.="</th>$paymentstatushead $imagesnotehead</tr></thead>";
				$closetable="</table>";


				$theadlistitems=smart_table($items,intval($results/$results),$opentable,$closetable);
				$showadstomanage="$theadlistitems";
				$showadstomanagedeletemultiplesubmitbutton="<input type=\"submit\" name=\"deletemultipleads\" class=\"button\" value=\"";
				$showadstomanagedeletemultiplesubmitbutton.=__("Delete Checked Ads","AWPCP");
				$showadstomanagedeletemultiplesubmitbutton.="\" /></p>";

			}
			if (!isset($ad_id) || empty($ad_id) || $ad_id == '0' )
			{
				$showadstomanage="<p style=\"padding:20px;\">";
				$showadstomanage.=__("There were no ads found","AWPCP");
				$showadstomanage.="</p>";
				$showadstomanagedeletemultiplesubmitbutton="";
				$pager1='';
				$pager2='';
			}
		}

		$output .= "
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
		$output .= "
			<div id=\"lookupadsby\"><div class=\"lookupadsbytitle\">
			<b>";
		$output .= __("Look Up Ad By","AWPCP");
		$output .= "</b></div>
			<div class=\"lookupadsbyform\">
			<form method=\"post\">
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"adid\"/>Ad ID
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"adtitle\"/>Ad Title
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"titdet\"/>Key Word
			<input type=\"radio\" name=\"lookupadbychoices\" value=\"location\"/>Location
			<input type=\"text\" name=\"lookupadidortitle\" value=\"$lookupadidortitle\"/>
			<input type=\"hidden\" name=\"action\" value=\"lookupadby\" />
			<input type=\"submit\" class=\"button\" value=\"Look Up Ad\" />
			</form>
			</div>
			</div>
			<div style=\"clear:both;\"></div>

			$pager1
			<form name=\"manageads\" id=\"manageads\" method=\"post\">
			<div id=\"listingsops\">
			<div class=\"deletechekedbuttom\">$showadstomanagedeletemultiplesubmitbutton</div>
			<div class=\"sortadsby\">";
			$output .= __("Sort Ads By","AWPCP");
			$output .= ": ";

			if ($sortby == 'mostrecent')
			{
				$output .= "<b>| ";
				$output .= __("Most Recent","AWPCP");
				$output .= " |</b>";
			}
			else
			{
				$output .= "<a href=\"?page=Manage1&sortby=mostrecent\">";
				$output .= __("Most Recent","AWPCP");
				$output .= "</a>";
			}
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			if ($sortby == 'titleza')
			{
				$output .= "<b>| ";
				$output .= __("Title Z-A","AWPCP");
				$output .= " |</b>";
			}
			else
			{
				$output .= "<a href=\"?page=Manage1&sortby=titleza\">";
				$output .= __("Title Z-A","AWPCP");
				$output .= "</a>";
			}
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			if ($sortby == 'titleaz')
			{
				$output .= "<b>| ";
				$output .= __("Title A-Z","AWPCP");
				$output .= " |</b>";
			}
			else
			{
				$output .= "<a href=\"?page=Manage1&sortby=titleaz\">";
				$output .= __("Title A-Z","AWPCP");
				$output .= "</a>";
			}
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			if (get_awpcp_option('adapprove') == 1)
			{
				if ($sortby == 'awaitingapproval')
				{
					$output .= "<b>| ";
					$output .= __("Awaiting Approval","AWPCP");
					$output .= " |</b>";
				}
				else
				{
					$output .= "<a href=\"?page=Manage1&sortby=awaitingapproval\">";
					$output .= __("Awaiting Approval","AWPCP");
					$output .= "</a>";
				}
			}
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			if (get_awpcp_option('freepay') == 1)
			{
				if ($sortby == 'paidfirst')
				{
					$output .= "<b>| ";
					$output .= __("Paid Ads First","AWPCP");
					$output .= " |</b>";
				}
				else
				{
					$output .= "<a href=\"?page=Manage1&sortby=paidfirst\">";
					$output .= __("Paid Ads First","AWPCP");
					$output .= "</a>";
				}

			}
			$output .= "
			</div>
			</div>

			$showadstomanage
		<div id=\"listingsops\">$showadstomanagedeletemultiplesubmitbutton</div>
			</form>
			$pager2";


			$output .= "</div>";
	}
	//Echo OK here:
	echo $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION: Manage view listings
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display images for admin view
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function viewimages($where)
{
	$output = '';
	global $wpdb;
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$thumbnailwidth=get_awpcp_option('imgthumbwidth');
	$thumbnailwidth.="px";

	$from="$tbl_ad_photos";

	if (!isset($where) || empty($where))
	{
		$where="image_name <> ''";
	}
	if (!images_exist())
	{
		$imagesallowedstatus='';

		if (get_awpcp_option('imagesallowdisallow') == 0)
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
		$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
		$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=10);

		$items=array();
		$query="SELECT key_id,ad_id,image_name,disabled FROM $from WHERE $where ORDER BY image_name DESC LIMIT $offset,$results";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		while ($rsrow=mysql_fetch_row($res)) {
			list($ikey,$adid,$image_name,$disabled)=$rsrow;
			$adtermid=get_adterm_id($adid);
			$editemail=get_adposteremail($adid);
			$adkey=get_adkey($adid);


			$dellink="<form method=\"post\" action=\"?page=Manage2\">";
			$dellink.="<input type=\"hidden\" name=\"adid\" value=\"$adid\" />";
			$dellink.="<input type=\"hidden\" name=\"picid\" value=\"$ikey\" />";
			$dellink.="<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\" />";
			$dellink.="<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />";
			$dellink.="<input type=\"hidden\" name=\"editemail\" value=\"$editemail\" />";
			$dellink.="<input type=\"hidden\" name=\"action\" value=\"deletepic\" />";
			$dellink.="<input type=\"submit\" class=\"button\" value=\"";
			$dellink.=__("Delete","AWPCP");
			$dellink.="\" />";
			$dellink.="</form>";
			$transval='';
			if ($disabled == 1){
				$transval="style=\"-moz-opacity:.20; filter:alpha(opacity=20); opacity:.20;\"";
			}

			$approvelink='';

			if ($disabled == 1)
			{
				$approvelink="<form method=\"post\" action=\"?page=Manage2\">";
				$approvelink.="<input type=\"hidden\" name=\"adid\" value=\"$adid\" />";
				$approvelink.="<input type=\"hidden\" name=\"picid\" value=\"$ikey\" />";
				$approvelink.="<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\" />";
				$approvelink.="<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />";
				$approvelink.="<input type=\"hidden\" name=\"editemail\" value=\"$editemail\" />";
				$approvelink.="<input type=\"hidden\" name=\"action\" value=\"approvepic\" />";
				$approvelink.="<input type=\"submit\" class=\"button\" value=\"";
				$approvelink.=__("Approve","AWPCP");
				$approvelink.="\" />";
				$approvelink.="</form>";
			}
			else {
				$approvelink="<form method=\"post\" action=\"?page=Manage2\">";
				$approvelink.="<input type=\"hidden\" name=\"adid\" value=\"$adid\" />";
				$approvelink.="<input type=\"hidden\" name=\"picid\" value=\"$ikey\" />";
				$approvelink.="<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\" />";
				$approvelink.="<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />";
				$approvelink.="<input type=\"hidden\" name=\"editemail\" value=\"$editemail\" />";
				$approvelink.="<input type=\"hidden\" name=\"action\" value=\"rejectpic\" />";
				$approvelink.="<input type=\"submit\" class=\"button\" value=\"";
				$approvelink.=__("Disable","AWPCP");
				$approvelink.="\" />";
				$approvelink.="</form>";
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
		if (!isset($ikey) || empty($ikey) || $ikey == '0')
		{
			$showcategories="<p style=\"padding:20px;\">";
			$showcategories.=__("There were no images found","AWPCP");
			$showcategories.="</p>";
			$pager1='';
			$pager2='';
		}
	}

	$output .= "
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


		$output .= "</div>";
		return $output;
		//die;
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

if (isset($_REQUEST['savesettings']) && !empty($_REQUEST['savesettings']))
{

	global $wpdb;
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";
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

	if (!isset($_REQUEST['cgid']) && empty($_REQUEST['cgid'])){$cgid=10;} else{ $cgid=$_REQUEST['cgid'];}
	if (!isset($_REQUEST['makesubpages']) && empty($_REQUEST['makesubpages'])){$makesubpages='';} else{ $makesubpages=$_REQUEST['makesubpages'];}


	$query="SELECT config_option,option_type FROM ".$tbl_ad_settings." WHERE config_group_id='$cgid'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

	$myoptions=array();

	for ($i=0;$i<mysql_num_rows($res);$i++)
	{
		list($config_option,$option_type)=mysql_fetch_row($res);

		if (isset($_POST[$config_option]))
		{

			$myoptions[$config_option]=clean_field($_POST[$config_option],true);

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

			if ($cgid == 10)
			{
				$newuipagename=$myoptions['userpagename'];

				if ( !empty($myoptions['showadspagename']) )
				{
					$showadspagename=$myoptions['showadspagename'];
				}
				if ( !empty($myoptions['placeadpagename']) )
				{
					$placeadpagename=$myoptions['placeadpagename'];
				}
				if ( !empty($myoptions['browseadspagename']) )
				{
					$browseadspagename=$myoptions['browseadspagename'];
				}
				if ( !empty($myoptions['searchadspagename']) )
				{
					$searchadspagename=$myoptions['searchadspagename'];
				}
				if ( !empty($myoptions['paymentthankyoupagename']) )
				{
					$paymentthankyoupagename=$myoptions['paymentthankyoupagename'];
				}
				if ( !empty($myoptions['paymentcancelpagename']) )
				{
					$paymentcancelpagename=$myoptions['paymentcancelpagename'];
				}
				if ( !empty($myoptions['editadpagename']) )
				{
					$editadpagename=$myoptions['editadpagename'];
				}
				if ( !empty($myoptions['replytoadpagename']) )
				{
					$replytoadpagename=$myoptions['replytoadpagename'];
				}
				if ( !empty($myoptions['browsecatspagename']) )
				{
					$browsecatspagename=$myoptions['browsecatspagename'];
				}
			}

			if ( !empty($myoptions['smtppassword']) )
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
			}elseif ($option_type==3) {
				$myoptions[$config_option]='';
			}
		}
	}

	while (list($k,$v)=each($myoptions))
	{

		if (($cgid == 3))
		{
			$mycurrencycode=$myoptions['paypalcurrencycode'];
			$displaycurrencycode=$myoptions['displaycurrencycode'];
			//PayPal Currencies supported as of 9-June-2010
			$currencycodeslist=array('AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','SGD','SEK','CHF','TWD','THB','USD');


			if (!in_array($mycurrencycode,$currencycodeslist) || !in_array($displaycurrencycode,$currencycodeslist))
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


		if (!$error)
		{
			//Protect option data from having SQL injection attacks:
			$v = add_slashes_recursive($v);
			$query="UPDATE ".$tbl_ad_settings." SET config_value='$v' WHERE config_option='$k'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
		}
	}
	if (($cgid == 10))
	{
		//foo
		// Create the classified user page if it does not exist
		if (empty($currentuipagename))
		{
			maketheclassifiedpage($newuipagename,$makesubpages=1);
		}
		elseif (isset($currentuipagename) && !empty($currentuipagename))
		{

			if (findpage($currentuipagename,$shortcode='[AWPCPCLASSIFIEDSUI]'))
			{
				if ($currentuipagename != '$newuipagename')
				{
					deleteuserpageentry($currentuipagename);
					updatetheclassifiedpagename($currentuipagename,$newuipagename);
				}
			}
			else
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

	add_action('init', 'awpcp_flush_rewrite_rules');
	global $wpdb,$table_prefix,$wp_rewrite;
	$tbl_pagename = $wpdb->prefix . "awpcp_pagename";
	$pdate = date("Y-m-d");

	// First delete any pages already existing with the title and post name of the new page to be created
	$existspageswithawpcpagename=checkfortotalpageswithawpcpname($newuipagename);

	if (!$existspageswithawpcpagename)
	{
		$post_name = sanitize_title($newuipagename, $post_ID='');

		$query="INSERT INTO {$table_prefix}posts SET post_author='1', post_date='$pdate', post_date_gmt='$pdate', post_content='[AWPCPCLASSIFIEDSUI]', post_title='$newuipagename', post_excerpt='', post_status='publish', comment_status='closed', post_name='$post_name', to_ping='', pinged='', post_modified='$pdate', post_modified_gmt='$pdate', post_content_filtered='[AWPCPCLASSIFIEDSUI]', post_parent='0', guid='', post_type='page', menu_order='0'";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
		$awpcpwppostpageid=mysql_insert_id();
		$guid = get_option('home') . "/?page_id=$awpcpwppostpageid";

		$query="UPDATE {$table_prefix}posts set guid='$guid' WHERE post_title='$newuipagename'";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		$query="INSERT INTO ".$tbl_pagename." SET userpagename='$newuipagename'";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		if ($makesubpages)
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

}

function maketheclassifiedsubpage($theawpcppagename,$awpcpwppostpageid,$awpcpshortcodex)
{
	add_action('init', 'awpcp_flush_rewrite_rules');
	global $wpdb,$table_prefix,$wp_rewrite;

	$pdate = date("Y-m-d");

	// First delete any pages already existing with the title and post name of the new page to be created
	//checkfortotalpageswithawpcpname($theawpcppagename);

	$post_name = sanitize_title($theawpcppagename, $post_ID='');

	$query="INSERT INTO {$table_prefix}posts SET post_author='1', post_date='$pdate', post_date_gmt='$pdate', post_content='$awpcpshortcodex', post_title='$theawpcppagename', post_excerpt='', post_status='publish', comment_status='closed', post_name='$post_name', to_ping='', pinged='', post_modified='$pdate', post_modified_gmt='$pdate', post_content_filtered='$awpcpshortcodex', post_parent='$awpcpwppostpageid', guid='', post_type='page', menu_order='0'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
	$newawpcpwppostpageid=mysql_insert_id();
	$guid = get_option('home') . "/?page_id=$newawpcpwppostpageid";

	$query="UPDATE {$table_prefix}posts set guid='$guid' WHERE post_title='$theawpcppagename'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
}

function updatetheclassifiedsubpage($currentsubpagename,$subpagename,$shortcode)
{
	global $wpdb,$table_prefix;

	$post_name = sanitize_title($subpagename, $post_ID='');

	$query="UPDATE {$table_prefix}posts set post_title='$subpagename', post_name='$post_name' WHERE post_title='$currentsubpagename' AND post_content LIKE '%$shortcode%'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

}


function updatetheclassifiedpagename($currentuipagename,$newuipagename)
{
	global $wpdb,$table_prefix, $wp_rewrite;
	$tbl_pagename = $wpdb->prefix . "awpcp_pagename";

	$post_name = sanitize_title($newuipagename, $post_ID='');

	$query="UPDATE {$table_prefix}posts set post_title='$newuipagename', post_name='$post_name' WHERE post_title='$currentuipagename'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

	$query="INSERT INTO ".$tbl_pagename." SET userpagename='$newuipagename'";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start process of updating|deleting|adding new listing fees
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////
// Handle adding a listing fee plan
/////////////////////////////////////////////////

if (isset($_REQUEST['addnewfeesetting']) && !empty($_REQUEST['addnewfeesetting']))
{

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$adterm_name=clean_field($_REQUEST['adterm_name']);
	$amount=clean_field($_REQUEST['amount']);

	$rec_period=clean_field($_REQUEST['rec_period']);
	$rec_increment=clean_field($_REQUEST['rec_increment']);
	$imagesallowed=clean_field($_REQUEST['imagesallowed']);
	$query="INSERT INTO ".$tbl_ad_fees." SET adterm_name='$adterm_name',amount='$amount',recurring=1,rec_period='$rec_period',rec_increment='$rec_increment',imagesallowed='$imagesallowed'";
	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
	$message.=__("The item has been added","AWPCP");
	$message.="!</div>";
	global $message;
}

//////////////////////////////////////////////////
// Handle updating of a listing fee plan
/////////////////////////////////////////////////

if (isset($_REQUEST['savefeesetting']) && !empty($_REQUEST['savefeesetting']))
{

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$adterm_id=clean_field($_REQUEST['adterm_id']);
	$adterm_name=clean_field($_REQUEST['adterm_name']);
	$amount=clean_field($_REQUEST['amount']);
	$rec_period=clean_field($_REQUEST['rec_period']);
	$rec_increment=clean_field($_REQUEST['rec_increment']);
	$imagesallowed=clean_field($_REQUEST['imagesallowed']);
	$query="UPDATE ".$tbl_ad_fees." SET adterm_name='$adterm_name',amount='$amount',recurring=1,rec_period='$rec_period',rec_increment='$rec_increment', imagesallowed='$imagesallowed' WHERE adterm_id='$adterm_id'";
	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
	$message.=__("The item has been updated","AWPCP");
	$message.="!</div>";
	global $message;
}

//////////////////////////////////////////////////
// Handle deleting of a listing fee plan
/////////////////////////////////////////////////

if (isset($_REQUEST['deletefeesetting']) && !empty($_REQUEST['deletefeesetting']))
{

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$awpcpfeeplanoptionitem='';
	$adterm_id='';

	if (isset($_REQUEST['adterm_id']) && !empty($_REQUEST['adterm_id']))
	{
		$adterm_id=clean_field($_REQUEST['adterm_id']);
	}

	if (empty($adterm_id))
	{

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
		$message.=__("No plan ID was provided therefore no action has been taken","AWPCP");
		$message.="!</div>";
	}

	// First make check if there are ads that are saved under this term
	elseif (adtermidinuse($adterm_id))
	{

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
		$message.=__("The plan could not be deleted because there are active ads in the system that are associated with the plan ID. You need to switch the ads to a new plan ID before you can delete the plan.","AWPCP");
		$message.="</div>";

		$awpcpfeechangeadstonewidform="<div style=\"border:5px solid#ff0000;padding:5px;\"><form method=\"post\" id=\"awpcp_launch\">";
		$awpcpfeechangeadstonewidform.="<p>";
		$awpcpfeechangeadstonewidform.=__("Change ads associated with plan ID $adterm_id to this plan ID","AWPCP");
		$awpcpfeechangeadstonewidform.="<br/>";
		$awpcpfeechangeadstonewidform.="<select name=\"awpcpnewplanid\"/>";


		$awpcpfeeplans=$wpdb->get_results("select adterm_id as theadterm_ID, adterm_name as theadterm_name from ".$tbl_ad_fees." WHERE adterm_id != '$adterm_id'");

		foreach($awpcpfeeplans as $awpcpfeeplan)
		{

			$awpcpfeeplanoptionitem .= "<option value='$awpcpfeeplan->theadterm_ID'>$awpcpfeeplan->theadterm_name</option>";
		}

		$awpcpfeechangeadstonewidform.="$awpcpfeeplanoptionitem";

		$awpcpfeechangeadstonewidform.="</select>";
		$awpcpfeechangeadstonewidform.="<input name=\"adterm_id\" type=\"hidden\" value=\"$adterm_id\" /></p>";
		$awpcpfeechangeadstonewidform.="<input class=\"button\" type=\"submit\" name=\"changeadstonewfeesetting\" value=\"";
		$awpcpfeechangeadstonewidform.=__("Submit","AWPCP");
		$awpcpfeechangeadstonewidform.="\" />";
		$awpcpfeechangeadstonewidform.="</form></div>";

		$message.="<p>$awpcpfeechangeadstonewidform</p>";
	}

	else
	{

		$query="DELETE FROM  ".$tbl_ad_fees." WHERE adterm_id='$adterm_id'";
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
		$message.=__("The data has been deleted","AWPCP");
		$message.="!</div>";

	}
}


if (isset($_REQUEST['changeadstonewfeesetting']) && !empty($_REQUEST['changeadstonewfeesetting']))
{

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$adterm_id='';
	$awpcpnewplanid='';

	if (isset($_REQUEST['adterm_id']) && !empty($_REQUEST['adterm_id']))
	{
		$adterm_id=clean_field($_REQUEST['adterm_id']);
	}
	if (isset($_REQUEST['awpcpnewplanid']) && !empty($_REQUEST['awpcpnewplanid']))
	{
		$awpcpnewplanid=clean_field($_REQUEST['awpcpnewplanid']);
	}


	if (empty($adterm_id))
	{

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
		$message.=__("No plan ID was provided therefore no action has been taken","AWPCP");
		$message.="!</div>";
	}
	else
	{
		$query="UPDATE ".$tbl_ads." SET adterm_id='$awpcpnewplanid' WHERE adterm_id='$adterm_id'";
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">";
		$message.=__("All ads with ID $adterm_id have been associated with plan id $awpcpnewplanid. You can now delete plan ID $adterm_id","AWPCP");
		$message.="!</div>";
	}
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Start process of adding | editing ad categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_REQUEST['createeditadcategory']) && !empty($_REQUEST['createeditadcategory']))
{

	global $wpdb;
	$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$category_id=clean_field($_REQUEST['category_id']);


	if (isset($_REQUEST['$movetocat']) && !empty($_REQUEST['$movetocat']))
	{
		$movetocat=clean_field($_REQUEST['movetocat']);
	}
	if (isset($_REQUEST['$deletetheads']) && !empty($_REQUEST['$deletetheads']))
	{
		$deletetheads=$_REQUEST['deletetheads'];
	}

	$aeaction=clean_field($_REQUEST['aeaction']);

	if ($aeaction == 'newcategory')
	{
		$category_name=clean_field($_REQUEST['category_name']);
		$category_parent_id=clean_field($_REQUEST['category_parent_id']);
		$category_order=clean_field($_REQUEST['category_order']);
		//Ensure we have something like a number:
		$category_order = ('' != $category_order ? (is_numeric($category_order) ? $category_order : 0) : 0);
		$query="INSERT INTO ".$tbl_ad_categories." SET category_name='".$category_name."',category_parent_id='".$category_parent_id."'".",category_order=".$category_order;
		@mysql_query($query);
		$themessagetoprint=__("The new category has been successfully added","AWPCP");
	}
	elseif ($aeaction == 'delete')
	{
		if (isset($_REQUEST['category_name']) && !empty($_REQUEST['category_name']))
		{
			$category_name=clean_field($_REQUEST['category_name']);
		}
		if (isset($_REQUEST['category_parent_id']) && !empty($_REQUEST['category_parent_id']))
		{
			$category_parent_id=clean_field($_REQUEST['category_parent_id']);
		}


		// Make sure this is not the default category. If it is the default category alert that the default category can only be renamed not deleted
		if ($category_id == 1)
		{
			$themessagetoprint=__("Sorry but you cannot delete the default category. The default category can only be renamed","AWPCP");
		}

		else
		{
			//Proceed with the delete instructions

			// Move any ads that the category contains if move-to category value is set and does not equal zero

			if ( isset($movetocat) && !empty($movetocat) && ($movetocat != 0) )
			{

				$movetocatparent=get_cat_parent_ID($movetocat);

				$query="UPDATE ".$tbl_ads." SET ad_category_id='$movetocat' ad_category_parent_id='$movetocatparent' WHERE ad_category_id='$category_id'";
				@mysql_query($query);

				// Must also relocate ads where the main category was a child of the category being deleted
				$query="UPDATE ".$tbl_ads." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$category_id'";
				@mysql_query($query);

				// Must also relocate any children categories to the the move-to-cat
				$query="UPDATE ".$tbl_ad_categories." SET category_parent_id='$movetocat' WHERE category_parent_id='$category_id'";
				@mysql_query($query);

			}


			// Else if the move-to value is zero move the ads to the parent category if category is a child or the default category if
			// category is not a child

			elseif ( !isset($movetocat) || empty($movetocat) || ($movetocat == 0) )
			{

				// If the category has a parent move the ads to the parent otherwise move the ads to the default

				if ( category_is_child($category_id) )
				{

					$movetocat=get_cat_parent_ID($category_id);
				}
				else
				{
					$movetocat=1;
				}

				$movetocatparent=get_cat_parent_ID($movetocat);

				// Adjust any ads transferred from the main category
				$query="UPDATE ".$tbl_ads." SET ad_category_id='$movetocat', ad_category_parent_id='$movetocatparent' WHERE ad_category_id='$category_id'";
				@mysql_query($query);

				// Must also relocate any children categories to the the move-to-cat
				$query="UPDATE ".$tbl_ad_categories." SET category_parent_id='$movetocat' WHERE category_parent_id='$category_id'";
				@mysql_query($query);

				// Adjust  any ads transferred from children categories
				$query="UPDATE ".$tbl_ads." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$category_id'";
				if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
			}

			$query="DELETE FROM  ".$tbl_ad_categories." WHERE category_id='$category_id'";
			@mysql_query($query);

			$themessagetoprint=__("The category has been deleted","AWPCP");
		}
	}
	elseif ($aeaction == 'edit')
	{

		if (isset($_REQUEST['category_name']) && !empty($_REQUEST['category_name']))
		{
			$category_name=clean_field($_REQUEST['category_name']);
		}
		if (isset($_REQUEST['category_parent_id']) && !empty($_REQUEST['category_parent_id']))
		{
			$category_parent_id=clean_field($_REQUEST['category_parent_id']);
		}
		$category_order=clean_field($_REQUEST['category_order']);
		//Ensure we have something like a number:
		$category_order = ('' != $category_order ? (is_numeric($category_order) ? $category_order : 0) : 0);
		
		$query="UPDATE ".$tbl_ad_categories." SET category_name='$category_name',category_parent_id='$category_parent_id',category_order='$category_order' WHERE category_id='$category_id'";
		@mysql_query($query);

		$query="UPDATE ".$tbl_ads." SET ad_category_parent_id='$category_parent_id' WHERE ad_category_id='$category_id'";
		@mysql_query($query);

		$themessagetoprint=__("Your category changes have been saved.","AWPCP");
	}
	else
	{
		$themessagetoprint=__("No changes made to categories.","AWPCP");
	}

	$message="<div style=\"background-color: rgb(255, 251, 204);\" id=\"message\" class=\"updated fade\">$themessagetoprint</div>";
	$clearform=1;

}

// Move multiple categories

if ( isset($_REQUEST['movemultiplecategories']) && !empty($_REQUEST['movemultiplecategories']) )
{

	global $wpdb;
	$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	// First get the array of categories to be deleted
	$categoriestomove=clean_field($_REQUEST['category_to_delete_or_move']);

	// Next get the value for where the admin wants to move the ads
	if ( isset($_REQUEST['moveadstocategory']) && !empty($_REQUEST['moveadstocategory'])  && ($_REQUEST['moveadstocategory'] != 0) )
	{
		$moveadstocategory=clean_field($_REQUEST['moveadstocategory']);

		// Next loop through the categories and move them to the new category

		foreach($categoriestomove as $cattomove)
		{

			if ($cattomove != $moveadstocategory)
			{

				// First update all the ads in the category to take on the new parent ID
				$query="UPDATE ".$tbl_ads." SET ad_category_parent_id='$moveadstocategory' WHERE ad_category_id='$cattomove'";
				@mysql_query($query);

				$query="UPDATE ".$tbl_ad_categories." SET category_parent_id='$moveadstocategory' WHERE category_id='$cattomove'";
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
if ( isset($_REQUEST['deletemultiplecategories']) && !empty($_REQUEST['deletemultiplecategories']) )
{

	global $wpdb;
	$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	// First get the array of categories to be deleted
	$categoriestodelete=clean_field($_REQUEST['category_to_delete_or_move']);

	// Next get the value of move/delete ads
	if ( isset($_REQUEST['movedeleteads']) && !empty($_REQUEST['movedeleteads']) )
	{
		$movedeleteads=clean_field($_REQUEST['movedeleteads']);
	}
	else
	{
		$movedeleteads=1;
	}

	// Next get the value for where the admin wants to move the ads
	if ( isset($_REQUEST['moveadstocategory']) && !empty($_REQUEST['moveadstocategory'])  && ($_REQUEST['moveadstocategory'] != 0) )
	{
		$moveadstocategory=clean_field($_REQUEST['moveadstocategory']);
	}
	else
	{
		$moveadstocategory=1;
	}

	// Next make sure there is a default category with an ID of 1 because any ads that exist in the
	// categories will need to be moved to a default category if admin has checked move ads but
	// has not selected a move to category

	if ( ($moveadstocategory == 1) && (!(defaultcatexists($defid='1'))) )
	{
		createdefaultcategory($idtomake='1',$titletocallit='Untitled');
	}

	// Next loop through the categories and move all their ads

	foreach($categoriestodelete as $cattodel)
	{
		// Make sure this is not the default category which cannot be deleted
		if ($cattodel != 1)
		{
			// If admin has instructed moving ads move the ads
			if ($movedeleteads == 1)
			{
				// Now move the ads if any
				$movetocat=$moveadstocategory;
				$movetocatparent=get_cat_parent_ID($movetocat);

				// Move the ads in the category main
				$query="UPDATE ".$tbl_ads." SET ad_category_id='$movetocat',ad_category_parent_id='$movetocatparent' WHERE ad_category_id='$cattodel'";
				@mysql_query($query);

				// Must also relocate ads where the main category was a child of the category being deleted
				$query="UPDATE ".$tbl_ads." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$cattodel'";
				@mysql_query($query);

				// Must also relocate any children categories that do not exist in the categories to delete loop to the the move-to-cat
				$query="UPDATE ".$tbl_ad_categories." SET category_parent_id='$movetocat' WHERE category_parent_id='$cattodel' AND category_id !IN '$categoriestodelete";
				@mysql_query($query);
			}
			elseif ($movedeleteads == 2)
			{

				$movetocat=$moveadstocategory;

				// If the category has children move the ads in the child categories to the default category

				if ( category_has_children($cattodel) )
				{
					//  Relocate the ads ads in any children categories of the category being deleted

					$query="UPDATE ".$tbl_ads." SET ad_category_parent_id='$movetocat' WHERE ad_category_parent_id='$cattodel'";
					@mysql_query($query);

					// Relocate any children categories that exist under the category being deleted
					$query="UPDATE ".$tbl_ad_categories." SET category_parent_id='$movetocat' WHERE category_parent_id='$cattodel'";
					@mysql_query($query);
				}


				// Now delete the ads because the admin has checked Delete ads if any
				massdeleteadsfromcategory($cattodel);
			}

			// Now delete the categories
			$query="DELETE FROM  ".$tbl_ad_categories." WHERE category_id='$cattodel'";
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

if (isset($_REQUEST['deletemultipleads']) && !empty($_REQUEST['deletemultipleads']))
{

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

	if (isset($_REQUEST['awpcp_ad_to_delete']) && !empty($_REQUEST['awpcp_ad_to_delete']))
	{
		$theawpcparrayofadstodelete=$_REQUEST['awpcp_ad_to_delete'];
	}

	if (!isset($theawpcparrayofadstodelete) || empty($theawpcparrayofadstodelete) )
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
		$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id IN ('$listofadstodelete')";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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

		$query="DELETE FROM ".$tbl_ad_photos." WHERE ad_id IN ('$listofadstodelete')";
		@mysql_query($query);


		// Delete the ads
		$query="DELETE FROM ".$tbl_ads." WHERE ad_id IN ('$listofadstodelete')";
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
	if (!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}
	global $classicontent;
	if (!isset($classicontent) || empty($classicontent)){$classicontent=awpcpui_process($awpcppagename);	}
	return $classicontent;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Post Ad Form Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_postformscreen()
{
	global $adpostform_content;
	if (!isset($adpostform_content) || empty($adpostform_content)){$adpostform_content=awpcpui_process_placead();}
	return $adpostform_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Edit Form Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_editformscreen()
{
	global $editpostform_content;
	if (!isset($editpostform_content) || empty($editpostform_content)){$editpostform_content=awpcpui_process_editad();}
	return $editpostform_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Contact Form Screen Configure
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_contactformscreen()
{
	global $contactpostform_content;
	if (!isset($contactpostform_content) || empty($contactpostform_content)){$contactpostform_content=awpcpui_process_contact();}
	return $contactpostform_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Payment Thank you screen Configure
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_paymentthankyouscreen()
{
	global $paymentthankyou_content;
	if (!isset($paymentthankyou_content) || empty($paymentthankyou_content)){$paymentthankyou_content=paymentthankyou();}
	return $paymentthankyou_content;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Browse Ads Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_browseadsscreen()
{
	global $browseads_content;
	if (!isset($browseads_content) || empty($browseads_content)){$browseads_content=awpcpui_process_browseads();}
	return $browseads_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Browse Cats Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_browsecatsscreen()
{
	global $browsecats_content;
	if (!isset($browsecats_content) || empty($browsecats_content)){$browsecats_content=awpcpui_process_browsecats();}
	return $browsecats_content;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Set Search Ads Screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcpui_searchformscreen()
{
	global $searchform_content;
	if (!isset($searchform_content) || empty($searchform_content)){$searchform_content=awpcpui_process_searchads();}
	return $searchform_content;
}

function awpcpui_process_editad()
{
	$output = '';
	global $hasextrafieldsmodule;
	$action='';

	if (!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if ($action == 'editad')
	{
		$output .= load_ad_edit_form($action,$awpcppagename,$usereditemail,$adaccesskey,$message);
	}
	elseif ($action == 'doadedit1')
	{
		$adaccesskey=clean_field($_REQUEST['adaccesskey']);
		$editemail=clean_field($_REQUEST['editemail']);
		$awpcppagename=clean_field($_REQUEST['awpcppagename']);
		$output .= editadstep1($adaccesskey,$editemail,$awpcppagename);
	}
	elseif ($action == 'resendaccesskey')
	{
		$editemail='';
		$awpcppagename='';
		if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail']))
		{
			$editemail=clean_field($_REQUEST['editemail']);
		}
		if (isset($_REQUEST['awpcppagename']) && !empty($_REQUEST['awpcppagename']))
		{
			$awpcppagename=clean_field($_REQUEST['awpcppagename']);
		}
		$output .= resendadaccesskeyform($editemail,$awpcppagename);
	}
	elseif ($action == 'dp')
	{
		if (isset($_REQUEST['k']) && !empty($_REQUEST['k']))
		{
			$keyids=$_REQUEST['k'];
			$keyidelements = explode("_", $keyids);
			$picid=$keyidelements[0];
			$adid=$keyidelements[1];
			$adtermid=$keyidelements[2];
			$adkey=$keyidelements[3];
			$editemail=$keyidelements[4];
		}

		$output .= deletepic($picid,$adid,$adtermid,$adkey,$editemail);
	}
	elseif ($action == 'dopost1')
	{
		$adid='';
		$action='';
		$awpcppagename='';
		$adterm_id='';
		$editemail='';
		$adkey='';
		$adtitle='';
		$adcontact_name='';
		$adcontact_phone='';
		$adcontact_email='';
		$adcategory='';
		$adcontact_city='';
		$adcontact_state='';
		$adcontact_country='';
		$ad_county_village='';
		$ad_item_price='';
		$addetails='';
		$adpaymethod='';
		$offset='';
		$results='';
		$ermsg='';
		$websiteurl='';
		$checkhuman='';
		$numval1='';
		$numval2='';

		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);}
		if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid'])){$adterm_id=clean_field($_REQUEST['adtermid']);}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){$adkey=clean_field($_REQUEST['adkey']);}
		if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail'])){$editemail=clean_field($_REQUEST['editemail']);}
		if (isset($_REQUEST['adtitle']) && !empty($_REQUEST['adtitle'])){$adtitle=clean_field($_REQUEST['adtitle']);}
		$adtitle=strip_html_tags($adtitle);
		if (isset($_REQUEST['adcontact_name']) && !empty($_REQUEST['adcontact_name'])){$adcontact_name=clean_field($_REQUEST['adcontact_name']);}
		$adcontact_name=strip_html_tags($adcontact_name);
		if (isset($_REQUEST['adcontact_phone']) && !empty($_REQUEST['adcontact_phone'])){$adcontact_phone=clean_field($_REQUEST['adcontact_phone']);}
		$adcontact_phone=strip_html_tags($adcontact_phone);
		if (isset($_REQUEST['adcontact_email']) && !empty($_REQUEST['adcontact_email'])){$adcontact_email=clean_field($_REQUEST['adcontact_email']);}
		if (isset($_REQUEST['adcategory']) && !empty($_REQUEST['adcategory'])){$adcategory=clean_field($_REQUEST['adcategory']);}
		if (isset($_REQUEST['adcontact_city']) && !empty($_REQUEST['adcontact_city'])){$adcontact_city=clean_field($_REQUEST['adcontact_city']);}
		$adcontact_city=strip_html_tags($adcontact_city);
		if (isset($_REQUEST['adcontact_state']) && !empty($_REQUEST['adcontact_state'])){$adcontact_state=clean_field($_REQUEST['adcontact_state']);}
		$adcontact_state=strip_html_tags($adcontact_state);
		if (isset($_REQUEST['adcontact_country']) && !empty($_REQUEST['adcontact_country'])){$adcontact_country=clean_field($_REQUEST['adcontact_country']);}
		$adcontact_country=strip_html_tags($adcontact_country);
		if (isset($_REQUEST['adcontact_countyvillage']) && !empty($_REQUEST['adcontact_countyvillage'])){$ad_county_village=clean_field($_REQUEST['adcontact_countyvillage']);}
		$ad_county_village=strip_html_tags($ad_county_village);
		if (isset($_REQUEST['ad_item_price']) && !empty($_REQUEST['ad_item_price'])){$ad_item_price=clean_field($_REQUEST['ad_item_price']);}
		$ad_item_price=str_replace(",", '', $ad_item_price);
		if (isset($_REQUEST['addetails']) && !empty($_REQUEST['addetails'])){$addetails=clean_field($_REQUEST['addetails']);}
		if (get_awpcp_option('allowhtmlinadtext') == 0){
			$addetails=strip_html_tags($addetails);
		}
		if (isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){$adpaymethod=clean_field($_REQUEST['adpaymethod']);}
		if (!isset($adpaymethod) || empty($adpaymethod))
		{
			$adpaymethod="paypal";
		}
		if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
			$adaction=clean_field($_REQUEST['adaction']);} else {$adaction='';}
			if (isset($_REQUEST['awpcppagename']) && !empty($_REQUEST['awpcppagename'])){$awpcppagename=clean_field($_REQUEST['awpcppagename']);}
			if (isset($_REQUEST['offset']) && !empty($_REQUEST['offset'])){$offset=clean_field($_REQUEST['offset']);}
			if (isset($_REQUEST['results']) && !empty($_REQUEST['results'])){$results=clean_field($_REQUEST['results']);}
			if (isset($_REQUEST['websiteurl']) && !empty($_REQUEST['websiteurl'])){$websiteurl=clean_field($_REQUEST['websiteurl']);}
			if (isset($_REQUEST['checkhuman']) && !empty($_REQUEST['checkhuman'])){$checkhuman=clean_field($_REQUEST['checkhuman']);}
			if (isset($_REQUEST['numval1']) && !empty($_REQUEST['numval1'])){$numval1=clean_field($_REQUEST['numval1']);}
			if (isset($_REQUEST['numval2']) && !empty($_REQUEST['numval2'])){$numval2=clean_field($_REQUEST['numval2']);}



		$output .= processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);
	}
	elseif ($action == 'awpcpuploadfiles')
	{
		$adid='';$adtermid='';$adkey='';$adpaymethod='';$nextstep='';$adaction='';
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);}
		if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid'])){$adtermid=clean_field($_REQUEST['adtermid']);}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){$adkey=clean_field($_REQUEST['adkey']);}
		if (isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){$adpaymethod=clean_field($_REQUEST['adpaymethod']);}
		if (isset($_REQUEST['nextstep']) && !empty($_REQUEST['nextstep'])){$nextstep=clean_field($_REQUEST['nextstep']);}
		if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){$adaction=clean_field($_REQUEST['adaction']);}
		$output .= handleimagesupload($adid,$adtermid,$nextstep,$adpaymethod,$adaction,$adkey);
	}
	elseif ($action == 'adpostfinish')
	{
		if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction']))
		{
			$adaction=$_REQUEST['adaction'];
		}
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$theadid=$_REQUEST['adid'];
		}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
		{
			$theadkey=$_REQUEST['adkey'];
		}

		if ($adaction == 'editad')
		{
			$output .= showad($theadid,$omitmenu='');
		}
		else
		{
			$awpcpshowadsample=1;
			$awpcpsubmissionresultmessage ='';
			$message='';
			$awpcpsubmissionresultmessage =ad_success_email($theadid,$txn_id='',$theadkey,$message,$gateway='');
				
			$output .= "<div id=\"classiwrapper\">";
			$output .= awpcp_menu_items();
			$output .= "<p>";
			$output .= $awpcpsubmissionresultmessage;
			$output .= "</p>";
			if ($awpcpshowadsample == 1)
			{
				$output .= "<h2>";
				$output .= __("Sample of your ad","AWPCP");
				$output .= "</h2>";
				$output .= showad($theadid,$omitmenu='1');
			}
			$output .= "</div>";
		}
	}
	elseif ($action == 'deletead')
	{
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$adid=$_REQUEST['adid'];
		}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
		{
			$adkey=$_REQUEST['adkey'];
		}
		if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail']))
		{
			$editemail=$_REQUEST['editemail'];
		}

		$output .= deletead($adid,$adkey,$editemail);
	}
	else
	{
		$output .= load_ad_edit_form($action='editad',$awpcppagename,$editemail='',$adaccesskey='',$message='');
	}
	return $output;
}

function awpcpui_process_contact()
{
	$output ='';
	$action='';
	$permastruc=get_option('permalink_structure');

	$pathvaluecontact=get_awpcp_option('pathvaluecontact');

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if (isset($_REQUEST['i']) && !empty($_REQUEST['i']))
	{
		$adid=$_REQUEST['i'];
	}

	if (!isset($adid) || empty($adid))
	{
		if ( get_awpcp_option('seofriendlyurls') )
		{
			if (isset($permastruc) && !empty($permastruc))
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

	if ($action == 'contact')
	{
		$output .= load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);
	}
	elseif ($action == 'docontact1')
	{
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);} else {$adid='';}
		if (isset($_REQUEST['sendersname']) && !empty($_REQUEST['sendersname'])){$sendersname=clean_field($_REQUEST['sendersname']);} else {$sendersname='';}
		if (isset($_REQUEST['checkhuman']) && !empty($_REQUEST['checkhuman'])){$checkhuman=clean_field($_REQUEST['checkhuman']);} else {$checkhuman='';}
		if (isset($_REQUEST['numval1']) && !empty($_REQUEST['numval1'])){$numval1=clean_field($_REQUEST['numval1']);} else {$numval1='';}
		if (isset($_REQUEST['numval2']) && !empty($_REQUEST['numval2'])){$numval2=clean_field($_REQUEST['numval2']);} else {$numval2='';}
		if (isset($_REQUEST['sendersemail']) && !empty($_REQUEST['sendersemail'])){$sendersemail=clean_field($_REQUEST['sendersemail']);} else {$sendersemail='';}
		if (isset($_REQUEST['contactmessage']) && !empty($_REQUEST['contactmessage'])){$contactmessage=clean_field($_REQUEST['contactmessage']);} else {$contactmessage='';}

		$output .= processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg='');

	}
	else
	{
		$output .= load_ad_contact_form($adid,$sendersname='',$checkhuman='',$numval1='',$numval2='',$sendersemail='',$contactmessage='',$ermsg='');
	}
	return $output;
}

function awpcpui_process_searchads()
{
	$output = '';
	$action='';

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if ($action == 'searchads')
	{
		$output .= load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');
	}
	elseif ($action == 'dosearch')
	{
		$output .= dosearch();
	}
	elseif ( $action == 'cregs' )
	{

		if (isset($_SESSION['regioncountryID']) )
		{
			unset($_SESSION['regioncountryID']);
		}
		if (isset($_SESSION['regionstatownID']) )
		{
			unset($_SESSION['regionstatownID']);
		}
		if (isset($_SESSION['regioncityID']) )
		{
			unset($_SESSION['regioncityID']);
		}
		if ( isset($_SESSION['theactiveregionid']) )
		{
			unset($_SESSION['theactiveregionid']);
		}

		$output .= load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');

	}
	else
	{
		$output .= load_ad_search_form($keywordphrase='',$searchname='',$searchcity='',$searchstate='',$searchcountry='',$searchcountyvillage='',$searchcategory='',$searchpricemin='',$searchpricemax='',$message='');
	}
	return $output;
}

function awpcpui_process_browseads()
{
	$output = '';
	$pathvaluebrowsecats=get_awpcp_option('pathvaluebrowsecats');
	$action='';

	if (isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
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

		if (isset($awpcpsplitbrowsecatsPath[$pathvaluebrowsecats]) && !empty($awpcpsplitbrowsecatsPath[$pathvaluebrowsecats]))
		{
			$adcategory=$awpcpsplitbrowsecatsPath[$pathvaluebrowsecats];
		}

	}

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if ( ($action == 'browsecat') )
	{
		if ($adcategory == -1)
		{
			$where="";
		}
		else
		{
			$where="(ad_category_id='".$adcategory."' OR ad_category_parent_id='".$adcategory."') AND disabled ='0'";
		}
		$adorcat='cat';
	}
	else
	{

		$where="disabled ='0'";
		$adorcat='ad';
	}

	$grouporderby=get_group_orderby();

	$output .= display_ads($where,$byl='',$hidepager='',$grouporderby,$adorcat);
	return $output;
}

function awpcpui_process_browsecats()
{
	$output = '';
	$pathvaluebrowsecats=get_awpcp_option('pathvaluebrowsecats');
	global $hasregionsmodule;
	$action='';

	if (isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
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

		if (isset($awpcpsplitbrowsecatsPath[$pathvaluebrowsecats]) && !empty($awpcpsplitbrowsecatsPath[$pathvaluebrowsecats]))
		{
			$adcategory=$awpcpsplitbrowsecatsPath[$pathvaluebrowsecats];
		}

	}

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}
	if (!isset($action) || empty($action)){$action="browsecat";}

	if ( ($action == 'browsecat') )
	{
		if ($adcategory == -1)
		{
			$where="";
		}
		else
		{
			$where="(ad_category_id='".$adcategory."' OR ad_category_parent_id='".$adcategory."') AND disabled ='0'";
		}
	}
	elseif (!isset($action))
	{
		if (isset($adcategory) )
		{
			if ($adcategory == -1)
			{
				$where="";
			}
			else
			{
				$where="(ad_category_id='".$adcategory."' OR ad_category_parent_id='".$adcategory."') AND disabled ='0'";
			}
		}
		else
		{
			$where="";
		}
	}
	else
	{
		$where="";
	}

	if ($adcategory == -1)
	{
		$output .= "<p><b>";
		$output .= __("No specific category was selected for browsing so you are viewing listings from all categories","AWPCP");
		$output .= "</b></p>";
		return $output;
	}


	$grouporderby=get_group_orderby();

	$output .= display_ads($where,$byl='',$hidepager='',$grouporderby,$adorcat='cat');
	return $output;
}


//Function to replace addslashes_mq, which is causing major grief.  Stripping of undesireable characters now done
// through above stripslashes_gpc.
function clean_field($foo) {
	return $foo;
}

function awpcpui_process_placead()
{
	$output = '';
	global $hasextrafieldsmodule;


	$pathsetregionid=get_awpcp_option('pathsetregionid');
	$pathsetregionbefore=($pathsetregionid - 1);
	$pathsetregionbeforevalue='';
	$action='';

	$awpcpsetregionid_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$awpcpsetregionid_requested_url .= $_SERVER['HTTP_HOST'];
	$awpcpsetregionid_requested_url .= $_SERVER['REQUEST_URI'];

	$awpcpparsedsetregionidURL = parse_url ($awpcpsetregionid_requested_url);
	$awpcpsplitsetregionidPath = preg_split ('/\//', $awpcpparsedsetregionidURL['path'], 0, PREG_SPLIT_NO_EMPTY);

	if (isset($awpcpsplitsetregionidPath[$pathsetregionbefore]) && !empty($awpcpsplitsetregionidPath[$pathsetregionbefore]))
	{
		$pathsetregionbeforevalue=$awpcpsplitsetregionidPath[$pathsetregionbefore];
	}

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}

	if ($action == 'placead')
	{
		$output .= load_ad_post_form($adid='',$action='',$awpcppagename='',$adtermid='',$editemail='',$adaccesskey='',$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset='',$results='',$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
	}
	elseif ($action == 'dopost1')
	{
		$adid='';
		$action='';
		$awpcppagename='';
		$adterm_id='';
		$editemail='';
		$adkey='';
		$adtitle='';
		$adcontact_name='';
		$adcontact_phone='';
		$adcontact_email='';
		$adcategory='';
		$adcontact_city='';
		$adcontact_state='';
		$adcontact_country='';
		$ad_county_village='';
		$ad_item_price='';
		$addetails='';
		$adpaymethod='';
		$offset='';
		$results='';
		$ermsg='';
		$websiteurl='';
		$checkhuman='';
		$numval1='';
		$numval2='';

		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);}
		if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid'])){$adterm_id=clean_field($_REQUEST['adtermid']);}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){$adkey=clean_field($_REQUEST['adkey']);}
		if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail'])){$editemail=clean_field($_REQUEST['editemail']);}
		if (isset($_REQUEST['adtitle']) && !empty($_REQUEST['adtitle'])){$adtitle=clean_field($_REQUEST['adtitle']);}
		$adtitle=strip_html_tags($adtitle);
		if (isset($_REQUEST['adcontact_name']) && !empty($_REQUEST['adcontact_name'])){$adcontact_name=clean_field($_REQUEST['adcontact_name']);}
		$adcontact_name=strip_html_tags($adcontact_name);
		if (isset($_REQUEST['adcontact_phone']) && !empty($_REQUEST['adcontact_phone'])){$adcontact_phone=clean_field($_REQUEST['adcontact_phone']);}
		$adcontact_phone=strip_html_tags($adcontact_phone);
		if (isset($_REQUEST['adcontact_email']) && !empty($_REQUEST['adcontact_email'])){$adcontact_email=clean_field($_REQUEST['adcontact_email']);}
		if (isset($_REQUEST['adcategory']) && !empty($_REQUEST['adcategory'])){$adcategory=clean_field($_REQUEST['adcategory']);}
		if (isset($_REQUEST['adcontact_city']) && !empty($_REQUEST['adcontact_city'])){$adcontact_city=clean_field($_REQUEST['adcontact_city']);}
		$adcontact_city=strip_html_tags($adcontact_city);
		if (isset($_REQUEST['adcontact_state']) && !empty($_REQUEST['adcontact_state'])){$adcontact_state=clean_field($_REQUEST['adcontact_state']);}
		$adcontact_state=strip_html_tags($adcontact_state);
		if (isset($_REQUEST['adcontact_country']) && !empty($_REQUEST['adcontact_country'])){$adcontact_country=clean_field($_REQUEST['adcontact_country']);}
		$adcontact_country=strip_html_tags($adcontact_country);
		if (isset($_REQUEST['adcontact_countyvillage']) && !empty($_REQUEST['adcontact_countyvillage'])){$ad_county_village=clean_field($_REQUEST['adcontact_countyvillage']);}
		$ad_county_village=strip_html_tags($ad_county_village);
		if (isset($_REQUEST['ad_item_price']) && !empty($_REQUEST['ad_item_price'])){$ad_item_price=clean_field($_REQUEST['ad_item_price']);}
		$ad_item_price=str_replace(",", '', $ad_item_price);
		if (isset($_REQUEST['addetails']) && !empty($_REQUEST['addetails'])){$addetails=clean_field($_REQUEST['addetails']);}
		if (get_awpcp_option('allowhtmlinadtext') == 0){
			$addetails=strip_html_tags($addetails);
		}
		if (isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){$adpaymethod=clean_field($_REQUEST['adpaymethod']);}
		if (!isset($adpaymethod) || empty($adpaymethod))
		{
			$adpaymethod="paypal";
		}
		if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
			$adaction=clean_field($_REQUEST['adaction']);} else {$adaction='';}
			if (isset($_REQUEST['awpcppagename']) && !empty($_REQUEST['awpcppagename'])){$awpcppagename=clean_field($_REQUEST['awpcppagename']);}
			if (isset($_REQUEST['offset']) && !empty($_REQUEST['offset'])){$offset=clean_field($_REQUEST['offset']);}
			if (isset($_REQUEST['results']) && !empty($_REQUEST['results'])){$results=clean_field($_REQUEST['results']);}
			if (isset($_REQUEST['websiteurl']) && !empty($_REQUEST['websiteurl'])){$websiteurl=clean_field($_REQUEST['websiteurl']);}
			if (isset($_REQUEST['checkhuman']) && !empty($_REQUEST['checkhuman'])){$checkhuman=clean_field($_REQUEST['checkhuman']);}
			if (isset($_REQUEST['numval1']) && !empty($_REQUEST['numval1'])){$numval1=clean_field($_REQUEST['numval1']);}
			if (isset($_REQUEST['numval2']) && !empty($_REQUEST['numval2'])){$numval2=clean_field($_REQUEST['numval2']);}

				
			$output .= processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);

	}
	elseif ($action == 'awpcpuploadfiles')
	{
			
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);}
		if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid'])){$adtermid=clean_field($_REQUEST['adtermid']);}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){$adkey=clean_field($_REQUEST['adkey']);}
		if (isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){$adpaymethod=clean_field($_REQUEST['adpaymethod']);}
		if (isset($_REQUEST['nextstep']) && !empty($_REQUEST['nextstep'])){$nextstep=clean_field($_REQUEST['nextstep']);}
		if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){$adaction=clean_field($_REQUEST['adaction']);}

		$output .= handleimagesupload($adid,$adtermid,$nextstep,$adpaymethod,$adaction,$adkey);
	}
	elseif ($action == 'loadpaymentpage')
	{
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])){$adid=clean_field($_REQUEST['adid']);} else {$adid='';}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){$key=clean_field($_REQUEST['adkey']);} else {$key='';}
		if (isset($_REQUEST['adtermid']) && !empty($_REQUEST['adtermid'])){$adterm_id=clean_field($_REQUEST['adtermid']);} else { $adterm_id='';}
		if (isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){$adpaymethod=clean_field($_REQUEST['adpaymethod']);} else {$adpaymethod='';}

		$output .= processadstep3($adid,$adterm_id,$key,$adpaymethod);

	}
	elseif ($action == 'dp')
	{
		if (isset($_REQUEST['k']) && !empty($_REQUEST['k']))
		{
			$keyids=$_REQUEST['k'];
			$keyidelements = explode("_", $keyids);
			$picid=$keyidelements[0];
			$adid=$keyidelements[1];
			$adtermid=$keyidelements[2];
			$adkey=$keyidelements[3];
			$editemail=$keyidelements[4];
		}

		$output .= deletepic($picid,$adid,$adtermid,$adkey,$editemail);
	}

	elseif ($action == 'adpostfinish')
	{
		$adaction='';$theadid='';$theadkey='';
		if (isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction']))
		{
			$adaction=$_REQUEST['adaction'];
		}
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$theadid=$_REQUEST['adid'];
		}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
		{
			$theadkey=$_REQUEST['adkey'];
		}

		if ($adaction == 'editad')
		{
			$output .= showad($theadid,$omitmenu='');
		}

		else
		{
			$awpcpshowadsample=1;
			$awpcpsubmissionresultmessage ='';
			$message='';
			$awpcpsubmissionresultmessage =ad_success_email($theadid,$txn_id='',$theadkey,$message,$gateway='');

			$output .= "<div id=\"classiwrapper\">";
			$output .= awpcp_menu_items();
			$output .= "<p>";
			$output .= $awpcpsubmissionresultmessage;
			$output .= "</p>";
			if ($awpcpshowadsample == 1)
			{
				$output .= "<h2>";
				$output .= __("Sample of your ad","AWPCP");
				$output .= "</h2>";
				$output .= showad($theadid,$omitmenu=1);
			}
			$output .= "</div>";
		}
	}
	elseif ($action == 'deletead')
	{
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$adid=$_REQUEST['adid'];
		}
		if (isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey']))
		{
			$adkey=$_REQUEST['adkey'];
		}
		if (isset($_REQUEST['editemail']) && !empty($_REQUEST['editemail']))
		{
			$editemail=$_REQUEST['editemail'];
		}
			
		$output .= deletead($adid,$adkey,$editemail);

	}
	elseif (($action == 'setregion') || ($pathsetregionbeforevalue == 'setregion'))
	{
		if ($hasregionsmodule ==  1)
		{
			if (isset($_REQUEST['regionid']) && !empty($_REQUEST['regionid']))
			{
				$theregionidtoset=$_REQUEST['regionid'];

			}
			else
			{
				$theregionidtoset=$awpcpsplitsetregionidPath[$pathsetregionid];
			}


			if ( isset($_SESSION['theactiveregionid']) )
			{
				unset($_SESSION['theactiveregionid']);
			}

			$_SESSION['theactiveregionid']=$theregionidtoset;

			if (region_is_a_country($theregionidtoset))
			{
				$_SESSION['regioncountryID']=$theregionidtoset;
			}

			if (region_is_a_state($theregionidtoset))
			{
				$thestateparentid=get_theawpcpregionparentid($theregionidtoset);
				$_SESSION['regioncountryID']=$thestateparentid;
				$_SESSION['regionstatownID']=$theregionidtoset;
			}

			if (region_is_a_city($theregionidtoset))
			{
				$thecityparentid=get_theawpcpregionparentid($theregionidtoset);
				$thestateparentid=get_theawpcpregionparentid($thecityparentid);
				$_SESSION['regioncountryID']=$thestateparentid;
				$_SESSION['regionstatownID']=$thecityparentid;
				$_SESSION['regioncityID']=$theregionidtoset;
			}
		}
	}
	elseif ($action == 'unsetregion')
	{
		if ( isset($_SESSION['theactiveregionid']) )
		{
			unset($_SESSION['theactiveregionid']);
		}
		$output .= awpcp_display_the_classifieds_page_body($awpcppagename);

	}
	elseif ( $action == 'setsessionregionid' )
	{
		global $hasregionsmodule;

		if ($hasregionsmodule ==  1)
		{
			if (isset($_REQUEST['sessionregion']) && !empty($_REQUEST['sessionregion']) )
			{
				$sessionregionid=$_REQUEST['sessionregion'];
			}
			if (isset($_REQUEST['sessionregionIDval']) && !empty($_REQUEST['sessionregionIDval']) )
			{
				$sessionregionIDval=$_REQUEST['sessionregionIDval'];
			}

			if ($sessionregionIDval == 1)
			{
				$_SESSION['regioncountryID']=$sessionregionid;
			}

			elseif ($sessionregionIDval == 2)
			{
				$_SESSION['regionstatownID']=$sessionregionid;
			}

			elseif ($sessionregionIDval == 3)
			{
				$_SESSION['regioncityID']=$sessionregionid;
			}
		}


		$output .= load_ad_post_form($adid='',$action,$awpcppagename='',$adtermid='',$editemail='',$adaccesskey='',$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset='',$results='',$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');

	}
	elseif ( $action == 'cregs' )
	{

		if (isset($_SESSION['regioncountryID']) )
		{
			unset($_SESSION['regioncountryID']);
		}
		if (isset($_SESSION['regionstatownID']) )
		{
			unset($_SESSION['regionstatownID']);
		}
		if (isset($_SESSION['regioncityID']) )
		{
			unset($_SESSION['regioncityID']);
		}
		if ( isset($_SESSION['theactiveregionid']) )
		{
			unset($_SESSION['theactiveregionid']);
		}



		$output .= load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail='',$adaccesskey='',$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset='',$results='',$ermsg='',$websieurl='',$checkhuman='',$numval1='',$numval2='');

	}
	else
	{
		$output .= load_ad_post_form($adid='',$action='',$awpcppagename='',$adtermid='',$editemail='',$adaccesskey='',$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset='',$results='',$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
	}
	return $output;
}

function awpcpui_process($awpcppagename)
{
	/*global $wp_rewrite;
	 $therwrules=$wp_rewrite->rewrite_rules();
	 print_r($therwrules);*/
	$output = '';
	$action='';
	$pathvalueviewcategories=get_awpcp_option('pathvalueviewcategories');

	if (!isset($pathvalueviewcategories) || empty($pathvalueviewcategories))
	{
		$pathvalueviewcategories='';
	}


	global $hasrssmodule,$awpcp_plugin_url;
	$awpcppage=get_currentpagename();
	if (!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}


	$pathsetregionid=get_awpcp_option('pathsetregionid');
	$pathsetregionbeforevalue='';
	if (isset($pathsetregionid) && !empty($pathsetregionid))
	{
		$pathsetregionbefore=($pathsetregionid - 1);
	}
	else
	{
		$pathsetregionbefore='';
	}

	$awpcpsetregionid_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$awpcpsetregionid_requested_url .= $_SERVER['HTTP_HOST'];
	$awpcpsetregionid_requested_url .= $_SERVER['REQUEST_URI'];

	$awpcpparsedsetregionidURL = parse_url ($awpcpsetregionid_requested_url);
	$awpcpsplitsetregionidPath = preg_split ('/\//', $awpcpparsedsetregionidURL['path'], 0, PREG_SPLIT_NO_EMPTY);

	if (isset($awpcpsplitsetregionidPath[$pathsetregionbefore]) && !empty($awpcpsplitsetregionidPath[$pathsetregionbefore]))
	{
		$pathsetregionbeforevalue=$awpcpsplitsetregionidPath[$pathsetregionbefore];
	}

	if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
	{
		$action=$_REQUEST['a'];
	}
	global $hasregionsmodule;
	if (($action == 'setregion') || ($pathsetregionbeforevalue == 'setregion'))
	{
		if ($hasregionsmodule ==  1)
		{
			if (isset($_REQUEST['regionid']) && !empty($_REQUEST['regionid']))
			{
				$theregionidtoset=$_REQUEST['regionid'];

			}
			else
			{
				$theregionidtoset=$awpcpsplitsetregionidPath[$pathsetregionid];
			}


			if ( isset($_SESSION['theactiveregionid']) )
			{
				unset($_SESSION['theactiveregionid']);
			}

			$_SESSION['theactiveregionid']=$theregionidtoset;

			if (region_is_a_country($theregionidtoset))
			{
				$_SESSION['regioncountryID']=$theregionidtoset;
			}

			if (region_is_a_state($theregionidtoset))
			{
				$thestateparentid=get_theawpcpregionparentid($theregionidtoset);
				$_SESSION['regioncountryID']=$thestateparentid;
				$_SESSION['regionstatownID']=$theregionidtoset;
			}

			if (region_is_a_city($theregionidtoset))
			{
				$thecityparentid=get_theawpcpregionparentid($theregionidtoset);
				$thestateparentid=get_theawpcpregionparentid($thecityparentid);
				$_SESSION['regioncountryID']=$thestateparentid;
				$_SESSION['regionstatownID']=$thecityparentid;
				$_SESSION['regioncityID']=$theregionidtoset;
			}
		}
	}
	elseif ($action == 'unsetregion')
	{
		if ( isset($_SESSION['theactiveregionid']) )
		{
			unset($_SESSION['theactiveregionid']);
		}

	}


	$categoriesviewpagename=sanitize_title(get_awpcp_option('categoriesviewpagename'), $post_ID='');
	$browsestat='';

	global $awpcp_plugin_url,$hasregionsmodule;

	$awpcpbrowse_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$awpcpbrowse_requested_url .= $_SERVER['HTTP_HOST'];
	$awpcpbrowse_requested_url .= $_SERVER['REQUEST_URI'];

	$awpcpparsedbrowseadsURL = parse_url ($awpcpbrowse_requested_url);

	if (isset($awpcpparsedbrowseadsURL) && !empty($awpcpparsedbrowseadsURL))
	{
		$awpcpsplitbrowseadPath = preg_split ('/\//', $awpcpparsedbrowseadsURL['path'], 0, PREG_SPLIT_NO_EMPTY);
	}


	if (isset($pathvalueviewcategories) && !empty($pathvalueviewcategories))
	{
		if (isset($awpcpsplitbrowseadPath[$pathvalueviewcategories]) && !empty($awpcpsplitbrowseadPath[$pathvalueviewcategories]))
		{
			$browsestat=$awpcpsplitbrowseadPath[$pathvalueviewcategories];
		}
	}
	$awpcp_nothinghereyet=__("You currently have no classifieds","AWPCP");

	$isadmin=checkifisadmin();

	$isclassifiedpage = checkifclassifiedpage($awpcppage);
	if ( ($isclassifiedpage == false) && ($isadmin == 1))
	{
		$output .= __("Hi admin, you need to go to your dashboard and setup your classifieds.","AWPCP");
	}
	elseif (($isclassifiedpage == false) && ($isadmin != 1))
	{
		$output .= $awpcp_nothinghereyet;
	}
	elseif ($browsestat == $categoriesviewpagename)
	{
		$output .= awpcp_display_the_classifieds_page_body($awpcppagename);
	}
	elseif ( isset($_REQUEST['layout']) && ($_REQUEST['layout'] == 2) )
	{
		$output .= awpcp_display_the_classifieds_page_body($awpcppagename);
	}
	else
	{
		$output .= awpcp_load_classifieds($awpcppagename);
	}
	return $output;
}

function awpcp_load_classifieds($awpcppagename)
{
	$output = '';
	if (get_awpcp_option('main_page_display') == 1)
	{
		//Display latest ads on mainpage
		$grouporderby=get_group_orderby();
		$output .= display_ads($where='',$byl='1',$hidepager='',$grouporderby,$adorcat='ad');
	}
	else
	{
		$output .= awpcp_display_the_classifieds_page_body($awpcppagename);
	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End function display the home screen
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: configure the menu place ad edit exisiting ad browse ads search ads
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function awpcp_menu_items()
{
	global $awpcp_imagesurl,$hasrssmodule;

	$action='';
	$output = '';

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

	if ($hasrssmodule == 1)
	{
		if (isset($permastruc) && !empty($permastruc))
		{
			$url_rss_feed="$quers?a=rss";
		}
		else
		{
			$url_rss_feed="$quers?page_id=$awpcp_page_id&a=rss";
		}
		$output .= "<div style=\"float:left;margin-right:10px;\"><a href=\"$url_rss_feed\"><img style=\"border:none;\" src=\"$awpcp_imagesurl/rssicon.png\"/></a></div>";
	}

	if (!isset($action) || empty ($action))
	{
		if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
		{
			$action=$_REQUEST['a'];
		}
	}

	if (isset($permastruc) && !empty($permastruc))
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

	if ($action == 'placead')
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
	if ($action== 'editad')
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
		
	$pathvalueviewcategories=get_awpcp_option('pathvalueviewcategories');
	$catviewpagecheck='';

	$awpcpviewcategories_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
	$awpcpviewcategories_requested_url .= $_SERVER['HTTP_HOST'];
	$awpcpviewcategories_requested_url .= $_SERVER['REQUEST_URI'];

	$awpcpparsedviewcategoriesURL = parse_url ($awpcpviewcategories_requested_url);
	$awpcpsplitviewcategoriesPath = preg_split ('/\//', $awpcpparsedviewcategoriesURL['path'], 0, PREG_SPLIT_NO_EMPTY);


	if (isset($awpcpsplitviewcategoriesPath[$pathvalueviewcategories]) && !empty($awpcpsplitviewcategoriesPath[$pathvalueviewcategories]))
	{
		$catviewpagecheck=$awpcpsplitviewcategoriesPath[$pathvalueviewcategories];
	}


	if (is_page($browseadspagename) )
	{
		$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browsecats\">$categoriesviewpagenameunsani";
		$browseads_browsecats.="</a></li>";
	}
	elseif (is_page($browsecatspagename) || ($catviewpagecheck == $categoriesviewpagename))
	{
		$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browseads\">$browseadspagenameunsani";
		$browseads_browsecats.="</a></li>";
	}
	elseif (( get_awpcp_option('main_page_display') == 1) && ($catviewpagecheck != $categoriesviewpagename))
	{
		if (is_page($awpcppagename) && ($action != 'unsetregion'))
		{
			$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browsecats\">$categoriesviewpagenameunsani";
			$browseads_browsecats.="</a></li>";
		}
		else
		{

			$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browseads\">$browseadspagenameunsani";
			$browseads_browsecats.="</a></li>";
			$browseads_browsecats.="<li class=\"browse\"><a href=\"$url_browsecats\">$categoriesviewpagenameunsani";
			$browseads_browsecats.="</a></li>";
		}
	}
	else
	{
		$browseads_browsecats="<li class=\"browse\"><a href=\"$url_browseads\">$browseadspagenameunsani";
		$browseads_browsecats.="</a></li>";
	}
		
	$output .= "<ul id=\"postsearchads\">";

	$isadmin=checkifisadmin();

	if (!(get_awpcp_option('onlyadmincanplaceads')))
	{
		$output .= "$liplacead";
		$output .= "$lieditad";
		$output .= "$browseads_browsecats";
		$output .= "<li class=\"searchcads\"><a href=\"$url_searchads\">$searchadspagenameunsani";
		$output .= "</a></li>";
	}
	elseif (get_awpcp_option('onlyadmincanplaceads') && ($isadmin == 1))
	{
		$output .= "$liplacead";
		$output .= "$lieditad";
		$output .= "$browseads_browsecats";
		$output .= "<li class=\"searchcads\"><a href=\"$url_searchads\">$searchadspagenameunsani";
		$output .= "</a></li>";
	}
	else
	{
		$output .= "$browseads_browsecats";
		$output .= "<li class=\"searchcads\"><a href=\"$url_searchads\">$searchadspagenameunsani";
		$output .= "</a></li>";
	}
		
	$output .= "</ul><div class=\"fixfloat\"></div>";
	return $output;
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
	$output = '';
	if (!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');

	$output .= "<div id=\"classiwrapper\">";
	$uiwelcome=get_awpcp_option('uiwelcome');
	$output .= "<div class=\"uiwelcome\">$uiwelcome</div>";

	// Place the menu items
	$output .= awpcp_menu_items();

	if ($hasregionsmodule ==  1)
	{
		if ( isset($_SESSION['theactiveregionid']) )
		{
			$theactiveregionid=$_SESSION['theactiveregionid'];
			$theactiveregionname=get_theawpcpregionname($theactiveregionid);
			$output .= "<h2>";
			$output .= __("You are currently browsing in ","AWPCP");
			$output .= "<b>$theactiveregionname</b></h2><SUP><a href=\"$quers/?a=unsetregion\">";
			$output .= __("Clear session for ","AWPCP");
			$output .= "$theactiveregionname</a></SUP>";
		}
	}
	$output .= "
					<div class=\"classifiedcats\">
				";

	//Display the categories
	$output .= awpcp_display_the_classifieds_category($awpcppagename);

	$output .= "</div>";
	$removeLink = get_awpcp_option('removepoweredbysign');
	if ( field_exists($field='removepoweredbysign') && !($removeLink) )
	{
		$output .= "<p><font style=\"font-size:smaller\">";
		$output .= __("Powered by ","AWPCP");
		$output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
	}
	elseif ( field_exists($field='removepoweredbysign') && ($removeLink) )
	{

	}
	else
	{
		$output .= "<p><font style=\"font-size:smaller\">";
		$output .= __("Powered by ","AWPCP");
		$output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
	}
	$output .= "</div>";
	return $output;
}

function awpcp_display_the_classifieds_category($awpcppagename)
{
	global $wpdb,$awpcp_imagesurl,$hasregionsmodule;
	$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";

	$usingsidelist=0;

	if (!isset($awpcppagename) || empty($awpcppagename) )
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
	$query="SELECT category_id,category_name FROM ".$tbl_ad_categories." WHERE category_parent_id='0' AND category_name <> '' ORDER BY category_order,category_name ASC";
	if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

	if (mysql_num_rows($res))
	{
		$i=1;

		//////////////////////////////////////////////////////////////////////
		// For use with regions module if sidelist is enabled
		/////////////////////////////////////////////////////////////////////

		if ($hasregionsmodule ==  1)
		{
			if (get_awpcp_option('showregionssidelist') )
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

		if ($usingsidelist)
		{
			$myreturn.="$awpcpregions_sidepanel<div class=\"awpcpcatlayoutleft\">";
		}

		while ($rsrow=mysql_fetch_row($res))
		{
			$myreturn.="<div id=\"showcategoriesmainlist\"><ul>";

			if (get_awpcp_option('showadcount') == 1)
			{
				$adsincat1=total_ads_in_cat($rsrow[0]);
				$adsincat1="($adsincat1)";
			}
			else
			{
				$adsincat1='';
			}

			$myreturn.="<li>";

			if ( function_exists('get_category_icon') )
			{
				$category_icon=get_category_icon($rsrow[0]);
			}

			if ( isset($category_icon) && !empty($category_icon) )
			{
				$caticonsurl="<img class=\"categoryicon\" src=\"$awpcp_imagesurl/caticons/$category_icon\" alt=\"$rsrow[1]\" border=\"0\">";
			}
			else
			{
				$caticonsurl='';
			}


			$modcatname1=cleanstring($rsrow[1]);
			$modcatname1=add_dashes($modcatname1);

			if (get_awpcp_option('seofriendlyurls'))
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$url_browsecats="$quers/$browsecatspagename/$rsrow[0]/$modcatname1";
				}
				else
				{
					$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&a=browsecat&category_id=$rsrow[0]";
				}
			}
			else
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$url_browsecats="$quers/$browsecatspagename?category_id=$rsrow[0]/$modcatname1";
				}
				else
				{
					$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&a=browsecat&category_id=$rsrow[0]";
				}
			}

			$myreturn.="<p class=\"maincategoryclass\">$caticonsurl<a href=\"$url_browsecats\" class=\"toplevelitem\">$rsrow[1]</a> $adsincat1</p>";

			// Start configuration of sub categories

			$myreturn.="<ul class=\"showcategoriessublist\">";

			$mcid=$rsrow[0];

			$query="SELECT category_id,category_name FROM ".$tbl_ad_categories." WHERE category_parent_id='$mcid' AND category_name <> '' ORDER BY category_order,category_name ASC";
			if (!($res2=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			if (mysql_num_rows($res2))
			{
				while ($rsrow2=mysql_fetch_row($res2))
				{
					if (get_awpcp_option('showadcount') == 1)
					{
						$adsincat2=total_ads_in_cat($rsrow2[0]);
						$adsincat2="($adsincat2)";
					}
					else
					{
						$adsincat2='';
					}

					if ( function_exists('get_category_icon') )
					{
						$sub_category_icon=get_category_icon($rsrow2[0]);
					}

					if ( isset($sub_category_icon) && !empty($sub_category_icon) )
					{
						$subcaticonsurl="<img class=\"categoryicon\" src=\"$awpcp_imagesurl/caticons/$sub_category_icon\" alt=\"$rsrow2[1]\" border=\"0\">";
					}
					else
					{
						$subcaticonsurl='';
					}
					$myreturn.="<li>";

					$modcatname2=cleanstring($rsrow2[1]);
					$modcatname2=add_dashes($modcatname2);

					if (get_awpcp_option('seofriendlyurls'))
					{
						if (isset($permastruc) && !empty($permastruc))
						{
							$url_browsecats2="$quers/$browsecatspagename/$rsrow2[0]/$modcatname2";
						}
						else
						{
							$url_browsecats2="$quers/?page_id=$awpcp_browsecats_pageid&a=browsecat&category_id=$rsrow2[0]";
						}
					}
					else
					{
						if (isset($permastruc) && !empty($permastruc))
						{
							$url_browsecats2="$quers/$browsecatspagename?category_id=$rsrow2[0]/$modcatname2";
						}
						else
						{
							$url_browsecats2="$quers/?page_id=$awpcp_browsecats_pageid&a=browsecat&category_id=$rsrow2[0]";
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

	if ($usingsidelist)
	{
		$myreturn.='</div>'; // To close div class awpcplayoutleft
	}

	$myreturn.='</div>';// Close the container division
	$myreturn.="<div class=\"fixfloat\"></div>";
	
	return $myreturn;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION: show the categories
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	FUNCTION: display the ad post form
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function load_ad_post_form($adid,$action,$awpcppagename,$adtermid,$editemail,$adaccesskey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2)
{
	$output = '';
	global $wpdb,$siteurl,$hasregionsmodule,$hasgooglecheckoutmodule,$hasextrafieldsmodule;

	$isadmin=checkifisadmin();

	if (!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}

	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');

	$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
	$editadpageid=awpcp_get_page_id($editadpagename);
	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$placeadpageid=awpcp_get_page_id($placeadpagename);


	if ( get_awpcp_option('seofriendlyurls') )
	{
		if (isset($permastruc) && !empty($permastruc))
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
	else
	{
		if (isset($permastruc) && !empty($permastruc))
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

	// Handle if only admin can post and non admin user arrives somehow on post ad page
	if (get_awpcp_option('onlyadmincanplaceads') && ($isadmin != 1))
	{
		$output .= "<div id=\"classiwrapper\"><p>";
		$output .= __("You do not have permission to perform the function you are trying to perform. Access to this page has been denied","AWPCP");
		$output .= "</p></div>";
	}
	// Handle if user must be registered
	elseif (get_awpcp_option('requireuserregistration') && !is_user_logged_in())
	{

		$postloginformto=get_awpcp_option('postloginformto');

		if (!isset($postloginformto) || empty($postloginformto))
		{
			$postloginformto="$siteurl/wp-login.php";
		}

		$registrationurl=get_awpcp_option('registrationurl');

		if (!isset($registrationurl) || empty($registrationurl))
		{
			$registrationurl="$siteurl/wp-login.php?action=register";
		}
		$putregisterlink="<a href=\"$registrationurl\" title=\"Register\"><b>";
		$putregisterlink.=__("Register","AWPCP");
		$putregisterlink.="</b></a>";

		$output .= "<div id=\"classiwrapper\"><p>";
		$output .= __("Only registered users can post ads. If you are already registered, please login below in order to post your ad.","AWPCP");
		$output .= "</p><h2>";
		$output .= __("Login","AWPCP");
		$output .= "</h2>";
		$output .= "<form name=\"loginform\" id=\"loginform\" action=\"$postloginformto\" method=\"post\">";
		$output .= "<p>";
		$output .= "<label>";
		$output .= __("Username","AWPCP");
		$output .= "</label>";
		$output .= "<br/>";
		$output .= "<input name=\"log\" id=\"user_login\" value=\"\" class=\"textinput\" size=\"20\" tabindex=\"10\" type=\"text\" />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label>";
		$output .= __("Password","AWPCP");
		$output .= "</label>";
		$output .= "<br/>";
		$output .= "<input name=\"pwd\" id=\"user_pass\" value=\"\" class=\"textinput\" size=\"20\" tabindex=\"20\" type=\"password\" />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<input name=\"rememberme\" id=\"rememberme\" value=\"forever\" tabindex=\"90\" type=\"checkbox\" /><label>";
		$output .= __("Remember Me","AWPCP");
		$output .= "</label>";
		$output .= "</p>";
		$output .= "<p align=\"center\">";
		$output .= "<input name=\"login-submit\" id=\"wp-submit\" value=\"";
		$output .= __("Log In","AWPCP");
		$output .= "\" class=\"submitbutton\" tabindex=\"100\" type=\"submit\" />";
		$output .= "<input name=\"redirect_to\" value=\"$url_placeadpage\" type=\"hidden\" />";
		$output .= "<input name=\"testcookie\" value=\"1\" type=\"hidden\" />";
		$output .= "</p>";
		$output .= "</form>";
		$output .= "<p>$putregisterlink</p>";
		$output .= "</div>";
	}
	// Handle ad post form
	else
	{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// START pre-form configurations
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
		$tbl_ads = $wpdb->prefix . "awpcp_ads";
		$images='';
		$displaydeleteadlink='';

		if ($action == 'editad')
		{
			$savedemail=get_adposteremail($adid);

			if ((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
			{

				if ($hasextrafieldsmodule == 1)
				{
					$x_fields_fetch="";
					$x_fields_list="";

					$x_fields_get_thefields=x_fields_fetch_fields();
					$x_fields_fetch_last=end($x_fields_get_thefields);
					foreach($x_fields_get_thefields as $x_fieldsfield)
					{
						$x_fields_fetch.=$x_fieldsfield;
						if (!($x_fields_fetch_last == $x_fieldsfield))
						{
							$x_fields_fetch.=",";
						}

						$x_fields_list.='$';
						$x_fields_list.=$x_fieldsfield;
						if (!($x_fields_fetch_last == $x_fieldsfield))
						{
							$x_fields_list.=",";
						}
					}

				}
				else
				{
					$x_fields_fetch='';
					$x_fields_list='';
				}

				$query="SELECT ad_title,ad_contact_name,ad_contact_email,ad_category_id,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details,ad_key,websiteurl $x_fields_fetch from ".$tbl_ads." WHERE ad_id='$adid' AND ad_contact_email='$editemail' AND ad_key='$adaccesskey'";
				if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

				while ($rsrow=mysql_fetch_row($res))
				{
					list($adtitle,$adcontact_name,$adcontact_email,$adcategory,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adaccesskey,$websiteurl,$x_fields_list)=$rsrow;
				}

				if (isset($ad_item_price) && !empty($ad_item_price))
				{
					$ad_item_price=($ad_item_price/100);
				}
				else
				{
					$ad_item_price='';
				}

				$displaydeleteadlink="<div class=\"alert\">";
				$displaydeleteadlink.="<form method=\"post\">";
				$displaydeleteadlink.="<input type=\"hidden\" name=\"adid\" value=\"$adid\"/>";
				$displaydeleteadlink.="<input type=\"hidden\" name=\"adkey\" value=\"$adaccesskey\"/>";
				$displaydeleteadlink.="<input type=\"hidden\" name=\"editemail\" value=\"$editemail\"/>";
				$displaydeleteadlink.="<input type=\"hidden\" name=\"a\" value=\"deletead\"/>";
				$displaydeleteadlink.="<input type=\"submit\" name=\"deletead\" class=\"button\" value=\"";
				$displaydeleteadlink.=__("Delete Ad","AWPCP");
				$displaydeleteadlink.="\" />";
				$displaydeleteadlink.="</form>";
				$displaydeleteadlink.="</div>";

			}
			else
			{
				unset($action);
			}
		}
		// End if $action == 'editad'

		////////////////////////////////////////////////////////////////////////////////////////
		// START configuration of payment option settings (ie paypal, 2checkout google checkout
		////////////////////////////////////////////////////////////////////////////////////////

		if (get_awpcp_option('freepay') == 1)
		{
			$paymethod='';

			if ($action == 'editad')
			{
				$paymethod='';
			}

			else
			{
				if (adtermsset() && !is_admin())
				{
					//configure the pay methods

					if ($adpaymethod == 'paypal'){ $ischeckedP="checked"; } else { $ischeckedP=''; }
					if ($adpaymethod == '2checkout'){ $ischecked2co="checked"; } else { $ischecked2co=''; }


					if ($hasgooglecheckoutmodule == 1)
					{
						if ($adpaymethod == 'googlecheckout'){ $ischeckedGC="checked"; } else { $ischeckedGC=''; }
					}

					$paymethod="<div id=\"showhidepaybutton\" style=\"display:none;\"><h2>";
					$paymethod.=__("Payment gateway","AWPCP");
					$paymethod.="</h2>";
					$paymethod.=__("Choose your payment gateway","AWPCP");
					$paymethod.="<p>";

					if (get_awpcp_option('activatepaypal') == 1)
					{
						$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"paypal\" $ischeckedP />PayPal<br/>";
					}

					if (get_awpcp_option('activate2checkout') == 1)
					{
						$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"2checkout\"  $ischecked2co />2Checkout<br/>";
					}

					if ($hasgooglecheckoutmodule == 1)
					{
						if (get_awpcp_option('activategooglecheckout') == 1)
						{
							$paymethod.="<input type=\"radio\" name=\"adpaymethod\" value=\"googlecheckout\"  $ischeckedGC />Google Checkout<br/>";
						}
					}
					$paymethod.="</p>";
					$paymethod.="</div>";
				}
			}
		}
		////////////////////////////////////////////////////////////////////////////////////////
		// END configuration of payment option settings (ie paypal, 2checkout google checkout
		////////////////////////////////////////////////////////////////////////////////////////

		////////////////////////////////////////////////////////////////////////////////////////
		// START configuration of ad term options
		////////////////////////////////////////////////////////////////////////////////////////

		if ($action == 'editad')
		{
			$adtermscode='';
		}
		else
		{
			if (!isset($adterm_id) || empty($adterm_id))
			{
				if (adtermsset() && !is_admin())
				{

					$adtermscode="<h2>";
					$adtermscode.=__("Select Ad Term","AWPCP");
					$adtermscode.="</h2>";

					//////////////////////////////////////////////////
					// Get and configure pay options
					/////////////////////////////////////////////////
					$paytermslistitems=array();

					$query="SELECT * FROM  ".$tbl_ad_fees."";
					if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

					if (mysql_num_rows($res))
					{

						while ($rsrow=mysql_fetch_row($res))
						{
							list($savedadtermid,$adterm_name,$amount,$recurring,$rec_period,$rec_increment)=$rsrow;

							if ($rec_increment == "M"){$termname="Month";}
							if ($rec_increment == "D"){$termname="Day";}
							if ($rec_increment == "W"){$termname="Week";}
							if ($rec_increment == "Y"){$termname="Year";}

							$termname=$termname;

							if ($adtermid == $savedadtermid)
							{
								$ischecked="checked";
							}
							else
							{
								$ischecked='';
							}

							$awpcpthecurrencysymbol=awpcp_get_currency_code();

							$adtermscode.="<input type=\"radio\" name=\"adtermid\"";

							if ($amount > 0)
							{
								$adtermscode.="onclick=\"awpcp_toggle_visibility('showhidepaybutton');\"";
							}

							if ($amount <= 0)
							{
								$adtermscode.="onclick=\"awpcp_toggle_visibility_reverse('showhidepaybutton');\"";
							}

							$awpcpduration=__("Duration","AWPCP");

							$adtermscode.="value=\"$savedadtermid\" $ischecked />$adterm_name ($awpcpthecurrencysymbol$amount $awpcpduration: $rec_period $termname )<br/>";
						}

					}
				}
			}
		}
		////////////////////////////////////////////////////////////////////////////////////////
		// END configuration of ad term options
		////////////////////////////////////////////////////////////////////////////////////////

		/////////////////////////////////////////////////////////////////////
		// Retrieve the categories to populate the select list
		/////////////////////////////////////////////////////////////////////

		$allcategories=get_categorynameidall($adcategory);

		/////////////////////////////////////////////////////////////////////
		// START Setup javascript checkpoints
		/////////////////////////////////////////////////////////////////////

		if ((get_awpcp_option('displayphonefield') == 1) && (get_awpcp_option('displayphonefieldreqop') == 1))
		{
			$phoneerrortxt=__("You did not fill out a phone number for the ad contact person. The information is required","AWPCP");
			$phonecheck="
			if (the.adcontact_phone.value===''){
			alert('$phoneerrortxt');
			the.adcontact_phone.focus();
			return false;
			}";
		} else {$phonecheck='';}

		if ((get_awpcp_option('displaycityfield') == 1) && (get_awpcp_option('displaycityfieldreqop') == 1))
		{
			$cityerrortxt=__("You did not fill out your city. The information is required","AWPCP");
			$citycheck="
			if (the.adcontact_city.value==='') {
			alert('$cityerrortxt');
			the.adcontact_city.focus();
			return false;
			}";
		} else {$citycheck='';}

		if ((get_awpcp_option('displaystatefield') == 1) && (get_awpcp_option('displaystatefieldreqop') == 1))
		{
			$stateerrortxt=__("You did not fill out your state. The information is required","AWPCP");
			$statecheck="
			if (the.adcontact_state.value==='') {
			alert('$stateerrortxt');
			the.adcontact_state.focus();
			return false;
			}";
		} else {$statecheck='';}

		if ((get_awpcp_option('displaycountyvillagefield') == 1) && (get_awpcp_option('displaycountyvillagefieldreqop') == 1))
		{
			$countyvillageerrortxt=__("You did not fill out your county/village/other. The information is required","AWPCP");
			$countyvillagecheck="
			if (the.adcontact_countyvillage.value==='') {
			alert('$countyvillageerrortxt');
			the.adcontact_countyvillage.focus();
			return false;
			}";
		} else {$countyvillagecheck='';}

		if ((get_awpcp_option('displaycountryfield') == 1) && (get_awpcp_option('displaycountryfieldreqop') == 1))
		{
			$countryerrortxt=__("You did not fill out your country. The information is required","AWPCP");
			$countrycheck="
			if (the.adcontact_country.value==='') {
			alert('$countryerrortxt');
			the.adcontact_country.focus();
			return false;
			}";
		} else {$countrycheck='';}

		if ((get_awpcp_option('displaywebsitefield') == 1) && (get_awpcp_option('displaywebsitefieldreqop') == 1))
		{
			$websiteerrortxt=__("You did not fill out your website address. The information is required","AWPCP");
			$websitecheck="
			if (the.websiteurl.value==='') {
			alert('$websiteerrortxt');
			the.websiteurl.focus();
			return false;
			}";
		} else {$websitecheck='';}

		if ((get_awpcp_option('displaypricefield') == 1) && (get_awpcp_option('displaypricefieldreqop') == 1))
		{
			$itempriceerrortxt=__("You did not enter a value for the item price. The information is required","AWPCP");
			$itempricecheck="
			if (the.ad_item_price.value==='') {
			alert('$itempriceerrortxt');
			the.ad_item_price.focus();
			return false;
			}";
		} else {$itempricecheck='';}

		if ( (get_awpcp_option('freepay') == 1) && ($action == 'placead') && !is_admin())
		{
			$paymethoderrortxt=__("You did not select your payment method. The information is required","AWPCP");
			$paymethodcheck="
			if (!checked(the.adpaymethod)) {
			alert('$paymethoderrortxt');
			the.adpaymethod.focus();
			return false;
			}";
		} else {$paymethodcheck='';}

		if ( (get_awpcp_option('freepay') == 1) && ($action == 'placead') && !is_admin() )
		{
			$adtermerrortxt=__("You did not select your ad term choice. The information is required","AWPCP");
			$adtermcheck="
			if (the.adterm_id.value==='') {
			alert('$adtermerrortxt');
			the.adterm_id.focus();
			return false;
			}";
		} else {$adtermcheck='';}

		if ((get_awpcp_option('contactformcheckhuman') == 1) && !is_admin())
		{
			if (isset($numval1) && !empty($numval1)) { $numval1=$numval1;}
			else { $numval1=rand(1,get_awpcp_option('contactformcheckhumanhighnumval'));}
			if (isset($numval2) && !empty($numval2))	{ $numval2=$numval2;	}
			else { $numval2=rand(1,get_awpcp_option('contactformcheckhumanhighnumval'));}

			$thesum=($numval1 +  $numval2);

			$checkhumanerrortxt1=__("You did not solve the math problem. Please solve the math problem to proceed.","AWPCP");
			$checkhumanerrortxt2=__("Your answer to the math problem was not correct. Please try again.","AWPCP");

			$checkhumancheck="
			if (the.checkhuman.value==='') {
			alert('$checkhumanerrortxt1');
			the.checkhuman.focus();
			return false;
			}
			if (the.checkhuman.value != $thesum){
			alert('$checkhumanerrortxt2');
			the.checkhuman.focus();
			return false;
			}";
		}

		$adtitleerrortxt=__("You did not fill out an ad title. The information is required","AWPCP");
		$adcategoryerrortxt=__("You did not select an ad category. The information is required","AWPCP");
		$adcontactemailerrortxt=__("Either you did not enter your email address or the email address you entered is not valid","AWPCP");
		$adcontactnameerrortxt=__("You did not fill in the name of the ad contact person. The information is required","AWPCP");
		$addetailserrortxt=__("You did not fill in any details for your ad. The information is required","AWPCP");

		$checktheform="<script type=\"text/javascript\">
			function checkform() {
			var the=document.adpostform;
   			var checkemj = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;  
			
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
			if (checkemj.test(the.adcontact_email.value) == false) {
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
				 if (e.style.display == 'block')
				 {
				      e.style.display = 'block';
				  }
				 else
				 {
					 e.style.display = 'block';
				}
				}

				 function awpcp_toggle_visibility_reverse(id)
				 {
					var e = document.getElementById(id);
					if (e.style.display == 'block')
					{
						 e.style.display = 'none';
					}
					else
					{
						 e.style.display = 'none';
					}
				}
		</script>";

			/////////////////////////////////////////////////////////////////////
			// END Setup javascript checkpoints
			/////////////////////////////////////////////////////////////////////


			/////////////////////////////////////////////////////////////////////
			// START Setup additional variables
			/////////////////////////////////////////////////////////////////////

			$addetailsmaxlength=get_awpcp_option('maxcharactersallowed');

			$theformbody='';

			$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "\n\n", $addetails);
			$htmlstatus=get_awpcp_option('htmlstatustext');
			$readonlyacname='';
			$readonlyacem='';

			if ( get_awpcp_option('requireuserregistration') && is_user_logged_in() && !is_admin() )
			{
				global $current_user;
				get_currentuserinfo();

				$adcontact_name=$current_user->user_login;
				$adcontact_email=$current_user->user_email;
				$readonlyacname="readonly";
				$readonlyacem="readonly";
			}

			/////////////////////////////////////////////////////////////////////
			// END Setup additional variables
			/////////////////////////////////////////////////////////////////////


			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// START configuration of dropdown lists used with regions module if regions module exists and pre-set regions exist
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if ( $hasregionsmodule ==  1 )
			{
				if ($action == 'editad')
				{
					// Do nothing
				}
				else
				{
					if (isset($_SESSION['regioncountryID']) )
					{
						$thesessionregionidval1=$_SESSION['regioncountryID'];
					}

					if (isset($_SESSION['regionstatownID']) )
					{
						$thesessionregionidval2=$_SESSION['regionstatownID'];
					}

					if (isset($_SESSION['regioncityID']) )
					{
						$thesessionregionidval3=$_SESSION['regioncityID'];
					}


					if ( !isset($thesessionregionidval1) || empty($thesessionregionidval1) )
					{
						if (get_awpcp_option('displaycountryfield') )
						{
							if ( regions_countries_exist() )
							{
								set_session_regionID(1);
								$formdisplayvalue="none";
							}

						}

					}
					elseif ( isset($thesessionregionidval1) && !isset ($thesessionregionidval2) )
					{
						if (get_awpcp_option('displaystatefield') )
						{
							if ( regions_states_exist($thesessionregionidval1) )
							{
								set_session_regionID(2);
								$formdisplayvalue="none";
							}
						}
					}
					elseif ( isset($thesessionregionidval1) && isset($thesessionregionidval2) && !isset ($thesessionregionidval3) )
					{
						if (get_awpcp_option('displaycityfield') )
						{
							if ( regions_cities_exist($thesessionregionidval2) )
							{
								set_session_regionID(3);
								$formdisplayvalue="none";
							}

						}
					}
				}
			}
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// END configuration of dropdown lists used with regions module if regions module exists and pre-set regions exist
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


			if (!isset($formdisplayvalue) || empty($formdisplayvalue) )
			{
				$formdisplayvalue="block";
			}

			if ($action== 'editad' )
			{
				$editorposttext=__("Your ad details have been filled out in the form below. Make any changes needed then resubmit the ad to update it","AWPCP");
			}
			else
			{
				$editorposttext=__("Fill out the form below to post your classified ad. ","AWPCP");
			}

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// END pre-form configurations
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// START form display
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			// Open  div id classiwrapper
			$output .= "<div id=\"classiwrapper\">";

			if (!is_admin())
			{
				$output .= awpcp_menu_items();
			}
			$output .= "<div class=\"fixfloat\"></div>";

			$output .= "<div style=\"display:$formdisplayvalue\">";
			if (!is_admin())
			{
				$theformbody.="$displaydeleteadlink<p>$editorposttext";

				if (! ($action== 'editad' ) )
				{
					if ($hasregionsmodule == 1)
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
			$theformbody.="<input type=\"hidden\" name=\"adid\" value=\"$adid\" />";
			$theformbody.="<input type=\"hidden\" name=\"adaction\" value=\"$action\" />";
			$theformbody.="<input type=\"hidden\" name=\"a\" value=\"dopost1\" />";

			if ($action == 'editad')
			{
				$theformbody.="<input type=\"hidden\" name=\"adtermid\" value=\"$adtermid\" />";
			}

			$theformbody.="<input type=\"hidden\" name=\"adkey\" value=\"$adaccesskey\" />";
			$theformbody.="<input type=\"hidden\" name=\"editemail\" value=\"$editemail\" />";
			$theformbody.="<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\" />";
			$theformbody.="<input type=\"hidden\" name=\"results\" value=\"$results\" />";
			$theformbody.="<input type=\"hidden\" name=\"offset\" value=\"$offset\" />";
			$theformbody.="<input type=\"hidden\" name=\"numval1\" value=\"$numval1\" />";
			$theformbody.="<input type=\"hidden\" name=\"numval2\" value=\"$numval2\" />";
			$theformbody.="<br/>";
			$theformbody.="<h2>";
			$theformbody.=__("Ad Details and Contact Information","AWPCP");
			$theformbody.="</h2><p>";
			$theformbody.=__("Ad Title","AWPCP");
			$theformbody.="<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"adtitle\" value=\"$adtitle\" /></p>";
			$theformbody.="<p>";
			$theformbody.=__("Ad Category","AWPCP");
			$theformbody.="<br/><select name=\"adcategory\"><option value=\"\">";
			$theformbody.=__("Select your ad category","AWPCP");
			$theformbody.="</option>$allcategories</a></select></p>";

			if (get_awpcp_option('displaywebsitefield') == 1)
			{
				$theformbody.="<p>Website URL<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"websiteurl\" value=\"$websiteurl\" /></select></p>";
			}

			$theformbody.="<p>";
			$theformbody.=__("Name of person to contact","AWPCP");
			$theformbody.="<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_name\" value=\"$adcontact_name\" $readonlyacname /></p>";
			$theformbody.="<p>";
			$theformbody.=__("Contact Person's Email [Please enter a valid email. The codes needed to edit your ad will be sent to your email address]","AWPCP");
			$theformbody.="<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_email\" value=\"$adcontact_email\" $readonlyacem /></p>";

			if (get_awpcp_option('displayphonefield') == 1)
			{
				$theformbody.="<p>";
				$theformbody.=__("Contact Person's Phone Number","AWPCP");
				$theformbody.="<br/><input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_phone\" value=\"$adcontact_phone\" /></p>";
			}
			if (get_awpcp_option('displaycountryfield') )
			{
				$theformbody.="<p>";
				$theformbody.=__("Country","AWPCP");
				$theformbody.="<br/>";

				if ($hasregionsmodule ==  1)
				{
					$opsitemregcountrylist=awpcp_region_create_country_list($adcontact_country,$byvalue='');

					if (!isset($opsitemregcountrylist) || empty($opsitemregcountrylist) )
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_country\" value=\"$adcontact_country\" />";
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
					$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_country\" value=\"$adcontact_country\" />";
				}

				$theformbody.="</p>";
			}
			if (get_awpcp_option('displaystatefield') )
			{
				$theformbody.="<p>";
				$theformbody.=__("State/Province","AWPCP");
				$theformbody.="<br/>";

				if ($hasregionsmodule ==  1)
				{
					if (!regions_states_exist($thesessionregionidval1) )
					{
						$opsitemregstatownlist='';
					}
					else
					{
						$opsitemregstatownlist=awpcp_region_create_statown_list($adcontact_state,$byvalue='',$adcontact_country='');
					}

					if (!isset($opsitemregstatownlist) || empty($opsitemregstatownlist) )
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_state\" value=\"$adcontact_state\" />";
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
					$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_state\" value=\"$adcontact_state\" />";
				}

				$theformbody.="</p>";
			}
			if (get_awpcp_option('displaycityfield') )
			{
				$theformbody.="<p>";
				$theformbody.=__("City","AWPCP");
				$theformbody.="<br/>";

				if ($hasregionsmodule ==  1)
				{
					$opsitemregcitylist=awpcp_region_create_city_list($adcontact_city,$byvalue='',$thecitystate='');

					if (!isset($opsitemregcitylist) || empty($opsitemregcitylist) )
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_city\" value=\"$adcontact_city\" />";
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
					$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_city\" value=\"$adcontact_city\" />";
				}

				$theformbody.="</p>";
			}

			if (get_awpcp_option('displaycountyvillagefield') )
			{
				$theformbody.="<p>";
				$theformbody.=__("County/Village/Other","AWPCP");
				$theformbody.="<br/>";

				if ($hasregionsmodule ==  1)
				{
					$opsitemregcountyvillagelist=awpcp_region_create_county_village_list($ad_county_village);

					if (!isset($opsitemregcountyvillagelist) || empty($opsitemregcountyvillagelist) )
					{
						$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_countyvillage\" value=\"$ad_county_village\" />";
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
					$theformbody.="<input size=\"50\" type=\"text\" class=\"inputbox\" name=\"adcontact_countyvillage\" value=\"$ad_county_village\" />";
				}

				$theformbody.="</p>";
			}

			if (get_awpcp_option('displaypricefield') == 1)
			{
				$theformbody.="<p>";
				$theformbody.=__("Item Price","AWPCP");
				$theformbody.="<br/><input size=\"10\" type=\"text\" class=\"inputboxprice\" maxlength=\"10\" name=\"ad_item_price\" value=\"$ad_item_price\" /></p>";
			}
			$theformbody.="<p>";
			$theformbody.=__("Ad Details","AWPCP");
			$theformbody.="<br/><input readonly type=\"text\" name=\"remLen\" size=\"10\" maxlength=\"5\" class=\"inputboxmini\" value=\"$addetailsmaxlength\" />";
			$theformbody.=__("characters left","AWPCP");
			$theformbody.="<br/><br/>$htmlstatus<br/><textarea name=\"addetails\" rows=\"10\" cols=\"50\" class=\"textareainput\" onKeyDown=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\" onKeyUp=\"textCounter(this.form.addetails,this.form.remLen,$addetailsmaxlength);\">$addetails</textarea></p>";
			if (get_awpcp_option('freepay') == '0')
			{
				$output .= "$theformbody";

				if ($hasextrafieldsmodule == 1)
				{
					build_extra_field_form($action,$adid,$ermsg);
				}
			}

			else
			{
				$output .= "$theformbody";

				if ($hasextrafieldsmodule == 1)
				{
					build_extra_field_form($action,$adid,$ermsg);
				}

				$output .= "<br/>";
				$output .= "$adtermscode";
				$output .= "<br/>";
				$output .= "$paymethod";

			}
			if ((get_awpcp_option('contactformcheckhuman') == 1) && !is_admin())
			{
				$output .= "<p>";
				$output .= __("Enter the value of the following sum","AWPCP");
				$output .= ": <b>$numval1 + $numval2</b>";
				$output .= "<br/>";
				$output .= "<input type=\"text\" name=\"checkhuman\" value=\"$checkhuman\" size=\"5\" />";
				$output .= "</p>";
			}

			$continuebuttontxt=__("Continue","AWPCP");
			$output .= "<input type=\"submit\" class=\"scbutton\" value=\"$continuebuttontxt\" />";
			$output .= "</form>";


			$output .= "</div>";
			// Close div style display:$formdisplayvalue

			$output .= "</div>";
			// Close div id classiwrapper

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// END form display
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	}
	// End Handle ad post form
	return $output;
	//End function load_ad_post_form
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display a form to the user when edit existing ad is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_edit_form($action,$awpcppagename,$usereditemail,$adaccesskey,$message)
{
	$output = '';
	$isadmin=checkifisadmin();
	$permastruc=get_option('permalink_structure');
	if (!isset($awpcppagename) || empty($awpcppagename) )
	{
		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	}
	$quers=setup_url_structure($awpcppagename);
	$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
	$editadpageid=awpcp_get_page_id($editadpagename);

	if (isset($permastruc) && !empty($permastruc))
	{
		$url_editpage="$quers/$editadpagename";
		$awpcpquerymark="?";
	}
	else
	{
		$url_editpage="$quers/?page_id=$editadpageid";
		$awpcpquerymark="&";
	}

	if (get_awpcp_option('onlyadmincanplaceads') && ($isadmin != '1'))
	{
		$output .= "<div id=\"classiwrapper\"><p>";
		$output .= __("You do not have permission to perform the function you are trying to perform. Access to this page has been denied","AWPCP");
		$output .= "</p></div>";
	}
	else
	{

		$checktheform="<script type=\"text/javascript\">
				function checkform() {
					var the=document.myform;
			   		var checkemj = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

					if (checkemj.test(the.editemail.value) == false) {					
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

		if (!isset($message) || empty($message))
		{
			$message="<p>";
			$message.=__("Please enter the email address you used when you created your ad in addition to the ad access key that was emailed to you after your ad was submitted","AWPCP");
			$message.="</p>";
		}

		$output .= "<div id=\"classiwrapper\">";
		$output .= awpcp_menu_items();

		if (isset($message) && !empty($message))
		{
			$output .= $message;
		}
		$output .= $checktheform;
		$output .= "<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
		$output .= "<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\" />";
		$output .= "<input type=\"hidden\" name=\"a\" value=\"doadedit1\" />";
		$output .= "<p>";
		$output .= __("Enter your Email address","AWPCP");
		$output .= "<br/>";
		$output .= "<input type=\"text\" name=\"editemail\" value=\"$usereditemail\" class=\"inputbox\" /></p>";
		$output .= "<p>";
		$output .= __("Enter your ad access key","AWPCP");
		$output .= "<br/>";
		$output .= "<input type=\"text\" name=\"adaccesskey\" value=\"$adaccesskey\" class=\"inputbox\" /></p>";
		$output .= "<input type=\"submit\" class=\"scbutton\" value=\"";
		$output .= __("Continue","AWPCP");
		$output .= "\" /> <a href=\"$url_editpage".$awpcpquerymark."a=resendaccesskey\">";
		$output .= __("Resend Ad Access Key","AWPCP");
		$output .= "</a>";
		$output .= "<br/>";
		$output .= "</form>";
		$output .= "</div>";

	}
	return $output;
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
	$adminemailoverride=get_awpcp_option('awpcpadminemail');
	if (isset($adminemailoverride) && !empty($adminemailoverride) && !(strcasecmp($thisadminemail, $adminemailoverride) == 0))
	{
		$thisadminemail=$adminemailoverride;
	}

	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	if (!isset($awpcppagename) || empty($awpcppagename) )
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
			   		var checkemj = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

					if (checkemj.test(the.editemail.value) == false) {						alert('$awpcpresendemailerrortxt');
						the.editemail.focus();
						return false;
					}

					return true;
				}

			</script>";

	if (!isset($message) || empty($message))
	{
		$message="<p>";
		$message.=__("Please enter the email address you used when you created your ad. Your access key will be sent to that email account. The email address you enter must match up with the email address we have on file","AWPCP");
		$message.="</p>";
	}

	if ( isset($editemail) && !empty($editemail) )
	{
		// Get the ad titles and access keys in the database that are associated with the email address
		$query="SELECT ad_title,ad_key,ad_contact_name FROM ".$tbl_ads." WHERE ad_contact_email='$editemail'";
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		$adtitlekeys=array();

		while ($rsrow=mysql_fetch_row($res))
		{
			list($adtitle,$adkey,$adpostername)=$rsrow;

			$adtitlekeys[]="$adtitle: $adkey";

		}

		$totaladsfoundtext=__("Total ads found sharing your email address","AWPCP");
		$totaladsfound=count($adtitlekeys);


		if ($totaladsfound > 0 )
		{
			$resendakeymessage="
			$awpcp_resendakeybody:

			$totaladsfoundtext: [$totaladsfound]
	
	";


			foreach ($adtitlekeys as $theadtitleandkey){
				$resendakeymessage.="
				$theadtitleandkey
		";
			}

			$resendakeymessage.="
			$nameofsite
			$siteurl
	
	";

			$subject="$awpcp_resendakeysubject";

			//email the access key
			if (awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$editemail,$awpcpemailsubject=$subject,$awpcpemailbody=$resendakeymessage,$awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail))
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
		$awpcpresendprocessresponse="$checktheform";
		$awpcpresendprocessresponse="$message";
		$awpcpresendprocessresponse.="<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
		$awpcpresendprocessresponse.="<input type=\"hidden\" name=\"awpcppagename\" value=\"$awpcppagename\" />";
		$awpcpresendprocessresponse.="<input type=\"hidden\" name=\"a\" value=\"resendaccesskey\" />";
		$awpcpresendprocessresponse.="<p>";
		$awpcpresendprocessresponse.=__("Enter your Email address","AWPCP");
		$awpcpresendprocessresponse.="<br/>";
		$awpcpresendprocessresponse.="<input type=\"text\" name=\"editemail\" value=\"$editemail\" class=\"inputbox\" /></p>";
		$awpcpresendprocessresponse.="<input type=\"submit\" class=\"scbutton\" value=\"";
		$awpcpresendprocessresponse.=__("Continue","AWPCP");
		$awpcpresendprocessresponse.="\" /><br/></form>";

	}
	$output = '';
	$output .= "<div id=\"classiwrapper\">";
	$output .= awpcp_menu_items();
	$output .= $awpcpresendprocessresponse;
	$output .= "</div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Display a form to be filled out in order to contact the ad poster
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$message)
{
	$output = '';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');

	$quers=setup_url_structure($awpcppagename);

	$contactformcheckhumanhighnumval=get_awpcp_option('contactformcheckhumanhighnumval');

	$numval1=rand(1,$contactformcheckhumanhighnumval);
	$numval2=rand(1,$contactformcheckhumanhighnumval);

	$thesum=($numval1 + $numval2);

	if (get_awpcp_option('contactformcheckhuman') == 1)
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
		var checkemj = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

		if (the.sendersname.value==='') {
			alert('$awpcpusernamemissing');
			the.sendersname.focus();
			return false;
		}

		if (checkemj.test(the.sendersemail.value) == false) {
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

		$output .= "<div id=\"classiwrapper\">";
		$output .= awpcp_menu_items();
		$isadmin=checkifisadmin();

		$theadtitle=get_adtitle($adid);
		$modtitle=cleanstring($theadtitle);
		$modtitle=add_dashes($modtitle);

		$permastruc=get_option('permalink_structure');
		$showadspagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');

		$url_showad=url_showad($adid);
		$thead="<a href=\"$url_showad\">$theadtitle</a>";


		$output .= "<p>";
		$output .= __("You are responding to ","AWPCP");
		$output .= "$thead</p>";
		if (isset($message) && !empty($message))
		{
			$output .= "$message";
		}
		$output .= $checktheform;
		$output .= "<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
		$output .= "<input type=\"hidden\" name=\"adid\" value=\"$adid\" />";
		$output .= "<input type=\"hidden\" name=\"a\" value=\"docontact1\" />";
		$output .= "<input type=\"hidden\" name=\"numval1\" value=\"$numval1\" />";
		$output .= "<input type=\"hidden\" name=\"numval2\" value=\"$numval2\" />";
		$output .= "<p>";
		$output .= __("Your Name","AWPCP");
		$output .= "<br/>";
		$output .= "<input type=\"text\" name=\"sendersname\" value=\"$sendersname\" class=\"inputbox\" /></p>";
		$output .= "<p>";
		$output .= __("Enter your Email address","AWPCP");
		$output .= "<br/>";
		$output .= "<input type=\"text\" name=\"sendersemail\" value=\"$sendersemail\" class=\"inputbox\" /></p>";
		$output .= "<p>";
		$output .= __("Enter your message below","AWPCP");
		$output .= "<br/>";
		$output .= "<textarea name=\"contactmessage\" rows=\"5\" cols=\"90%\" class=\"textareainput\">$contactmessage</textarea></p>";

		if (get_awpcp_option('contactformcheckhuman') == 1)
		{
			$output .= "<p>";
			$output .= __("Enter the value of the following sum","AWPCP");
			$output .= ": <b>$numval1 + $numval2</b><br>";
			$output .= "<input type=\"text\" name=\"checkhuman\" value=\"$checkhuman\" size=\"5\" /></p>";
		}

		$output .= "<input type=\"submit\" class=\"scbutton\" value=\"";
		$output .= __("Continue","AWPCP");
		$output .= "\" />";
		$output .= "<br/></form></div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Process the request to contact the poster of the ad
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadcontact($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg)
{
	$output = '';
	global $nameofsite,$siteurl,$thisadminemail;
	if (isset($adminemailoverride) && !empty($adminemailoverride) && !(strcasecmp($thisadminemail, $adminemailoverride) == 0))
	{
		$thisadminemail=$adminemailoverride;
	}
	$error=false;
	$adidmsg='';
	$sendersnamemsg='';
	$checkhumanmsg='';
	$sendersemailmsg='';
	$contactmessagemsg='';
	$sumwrongmsg='';
	$sendersemailwrongmsg='';

	$thesum=($numval1 +  $numval2);

	if (!isset($adid) || empty($adid))
	{
		$error=true;
		$adidmsg="<li>";
		$adidmsg.=__("The ad could not be identified due to a missing ad identification number","AWPCP");
		$adidmsg.="</li>";
	}
	if (!isset($sendersname) || empty($sendersname))
	{
		$error=true;
		$sendersnamemsg="<li>";
		$sendersnamemsg.=__("You did not enter your name. You must include a name for this message to be relayed on your behalf","AWPCP");
		$sendersnamemsg.="</li>";
	}

	if (get_awpcp_option('contactformcheckhuman') == 1)
	{

		if (!isset($checkhuman) || empty($checkhuman))
		{
			$error=true;
			$checkhumanmsg="<li>";
			$checkhumanmsg.=__("You did not solve the Math Problem","AWPCP");
			$checkhumanmsg.="</li>";
		}
		if ($checkhuman != $thesum)
		{
			$error=true;
			$sumwrongmsg="<li>";
			$sumwrongmsg.=__("Your solution to the Math problem was incorrect","AWPCP");
			$sumwrongmsg.="</li>";
		}
	}

	if (!isset($contactmessage) || empty($contactmessage))
	{
		$error=true;
		$contactmessagemsg="<li>";
		$contactmessagemsg.=__("There was no text entered for your message","AWPCP");
		$contactmessagemsg.="</li>";
	}

	if (!isset($sendersemail) || empty($sendersemail))
	{
		$error=true;
		$sendersemailmsg="<li>";
		$sendersemailmsg.=__("You did not enter your name. You must include a name for this message to be relayed on your behalf","AWPCP");
		$sendersemailmsg.="</li>";
	}
	if (!isValidEmailAddress($sendersemail))
	{
		$error=true;
		$sendersemailwrongmsg="<li>";
		$sendersemailwrongmsg.=__("The email address you entered was not a valid email address. Please check for errors and try again","AWPCP");
		$sendersemailwrongmsg.="</li>";
	}

	if ($error)
	{
		$ermsg="<p>";
		$ermsg.=__("There has been an error found. Your message has not been sent. Please review the list of problems, correct them then try to send your message again","AWPCP");
		$ermsg.="</p>";
		$ermsg.="<b>";
		$ermsg.=__("The errors","AWPCP");
		$ermsg.=":</b><br/>";
		$ermsg.="<ul>$adidmsg $sendersnamemsg $checkhumanmsg $contactmessagemsg $sumwrongmsg $sendersemailmsg $sendersemailwrongmsg</ul>";

		$output .= load_ad_contact_form($adid,$sendersname,$checkhuman,$numval1,$numval2,$sendersemail,$contactmessage,$ermsg);
	}
	else
	{
		$sendersname=strip_html_tags($sendersname);
		$contactmessage=strip_html_tags($contactmessage);
		$theadtitle=get_adtitle($adid);
		$url_showad=url_showad($adid);
		$adlink="$url_showad";
		$sendtoemail=get_adposteremail($adid);
		$contactformsubjectline=get_awpcp_option('contactformsubjectline');

		if (isset($contactformsubjectline) && !empty($contactformsubjectline) )
		{
			$subject="$contactformsubjectline";
			$subject.=__("Regarding","AWPCP");
			$subject.=": $theadtitle";
		}
		else
		{
			$subject=__("Regarding","AWPCP");
			$subject.=": $theadtitle";
		}

		$contactformbodymessagestart=get_awpcp_option('contactformbodymessage');
		$contactformbodymessage="
		$contactformbodymessagestart
		
	";

		$contactformbodymessage.=

		__("Message","AWPCP");

		$contactformbodymessage.="
		
		$contactmessage
		
	";

		$contactformbodymessage.=

		__("Contacting About:","AWPCP");

		$contactformbodymessage.="
		
		$theadtitle $adlink
		
	";	

		$contactformbodymessage.=

		__("Reply To","AWPCP");

		$contactformbodymessage.="
	";

		$contactformbodymessage.=

		__("Name","AWPCP");
		$contactformbodymessage.=": $sendersname";

		$contactformbodymessage.="
	";

		$contactformbodymessage.=

		__("Email","AWPCP");
		$contactformbodymessage.=": $sendersemail";
		$contactformbodymessage.="
		
		$nameofsite
	";
		$contactformbodymessage.=
		$siteurl;

		if (get_awpcp_option('usesenderemailinsteadofadmin'))
		{
			$awpcpthesendername=$sendersname;
			$awpcpthesenderemail=$sendersemail;
		}
		else
		{
			$awpcpthesendername=$nameofsite;
			$awpcpthesenderemail=$thisadminemail;
		}
		//email the buyer
		if (awpcp_process_mail($awpcpsenderemail=$awpcpthesenderemail,$awpcpreceiveremail=$sendtoemail,$awpcpemailsubject=$subject,$awpcpemailbody=$contactformbodymessage,$awpcpsendername=$awpcpthesendername,$awpcpreplytoemail=$sendersemail))
		{
			$contactformprocessresponse=__("Your message has been sent","AWPCP");
		}
		else
		{
			$contactformprocessresponse=__("There was a problem encountered during the attempt to send your message. Please try again and if the problem persists, please contact the system administrator","AWPCP");
		}
	}

	$contactpostform_content=$contactformprocessresponse;
	$output .= "<div id=\"classiwrapper\">";
	$output .= awpcp_menu_items();
	$output .= $contactformprocessresponse;
	$output .= "</div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display the ad search form
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function load_ad_search_form($keywordphrase,$searchname,$searchcity,$searchstate,$searchcountry,$searchcountyvillage,$searchcategory,$searchpricemin,$searchpricemax,$message){
	$output = '';
	global $hasregionsmodule;

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$searchadspagename = sanitize_title(get_awpcp_option('searchadspagename'), $post_ID='');
	$searchadspageid = awpcp_get_page_id($searchadspagename);
	$clearthesessionlink='';

	$quers=setup_url_structure($awpcppagename);

	if ( get_awpcp_option('seofriendlyurls') )
	{

		if (isset($permastruc) && !empty($permastruc))
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
	else
	{
		if (isset($permastruc) && !empty($permastruc))
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
			if ( (the.searchname.value==='') && (the.searchcity.value==='') && (the.searchstate.value==='') && (the.searchcountry.value==='') && (the.searchcountyvillage.value==='') && (the.searchcategory.value==='') && (the.searchpricemin.value==='') && (the.searchpricemax.value==='') )
			{
				alert('$nosearchkeyworderror');
				the.keywordphrase.focus();
				return false;
			}
		}

		return true;
	}

</script>";

	global $awpcp_plugin_path;
	if ( file_exists("$awpcp_plugin_path/awpcp_region_control_module.php") )
	{
		if ( isset($_SESSION['regioncountryID']) || isset($_SESSION['regionstatownID']) || isset($_SESSION['regioncityID']) )
		{
			$searchinginregion='';

			if (isset($_SESSION['regioncityID']) && !empty($_SESSION['regioncityID']))
			{
				$regioncityname=get_theawpcpregionname($_SESSION['regioncityID']);
				$searchinginregion.="$regioncityname";
			}
			if (isset($_SESSION['regionstatownID']) && !empty($_SESSION['regionstatownID']))
			{
				$regionstatownname=get_theawpcpregionname($_SESSION['regionstatownID']);
				$searchinginregion.=" $regionstatownname";
			}
			if (isset($_SESSION['regioncountryID']) && !empty($_SESSION['regioncountryID']))
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
	}

	if (!isset($message) || empty($message))
	{
		$message="<p>";
		$message.=__("Use the form below to conduct a broad or narrow search. For a broader search enter fewer parameters. For a narrower search enter as many parameters as needed to limit your search to a specific criteria","AWPCP");
		$message.=" $clearthesessionlink</p>";
	}

	$allcategories=get_categorynameidall($searchcategory);

	if (!isset($adcontact_country) || empty($adcontact_country) )
	{
		if ( isset($_SESSION['regioncountryID']) && !empty ($_SESSION['regioncountryID']) )
		{
			$adcontact_country=$_SESSION['regioncountryID'];
		}
	}

	if (!isset($adcontact_state) || empty($adcontact_state) )
	{
		if ( isset($_SESSION['regionstatownID']) && !empty ($_SESSION['regionstatownID']) )
		{
			$adcontact_state=$_SESSION['regionstatownID'];
		}
	}

	if (!isset($adcontact_city) || empty($adcontact_city) )
	{
		if ( isset($_SESSION['regioncityID']) && !empty ($_SESSION['regioncityID']) )
		{
			$adcontact_city=$_SESSION['regioncityID'];
		}
	}

	$output .= "<div id=\"classiwrapper\">";
	$isadmin=checkifisadmin();
	$output .= awpcp_menu_items();
	if (isset($message) && !empty($message))
	{
		$output .= "$message";
	}
	$output .= $checktheform;
	$output .= "<form method=\"post\" name=\"myform\" id=\"awpcpui_process\" onsubmit=\"return(checkform())\">";
	$output .= "<input type=\"hidden\" name=\"a\" value=\"dosearch\" />";
	$output .= "<p>";
	$output .= __("Search for ads containing this word or phrase","AWPCP");
	$output .= ":<br/><input type=\"text\" class=\"inputbox\" size=\"50\" name=\"keywordphrase\" value=\"$keywordphrase\" /></p>";
	$output .= "<p>";
	$output .= __("Search in Category","AWPCP");
	$output .= "<br><select name=\"searchcategory\"><option value=\"\">";
	$output .= __("Select Option","AWPCP");
	$output .= "</option>$allcategories</select></p>";
	$output .= "<p>";
	$output .= __("For Ads Posted By","AWPCP");
	$output .= "<br/><select name=\"searchname\"><option value=\"\">";
	$output .= __("Select Option","AWPCP");
	$output .= "</option>";
	$output .= create_ad_postedby_list($searchname);
	$output .= "</select></p>";


	if (get_awpcp_option('displaypricefield') == 1)
	{
		if ( price_field_has_values() )
		{
			$output .= "<p>";
			$output .= __("Min Price","AWPCP");
			$output .= "<select name=\"searchpricemin\"><option value=\"\">";
			$output .= __("Select","AWPCP");
			$output .= "</option>";
			$output .= create_price_dropdownlist_min($searchpricemin);
			$output .= "</select>";
			$output .= __("Max Price","AWPCP");
			$output .= "<select name=\"searchpricemax\"><option value=\"\">";
			$output .= __("Select","AWPCP");
			$output .= "</option>";
			$output .= create_price_dropdownlist_max($searchpricemax);
			$output .= "</select></p>";
		}
		else
		{
			$output .= "<input type=\"hidden\" name=\"searchpricemin\" value=\"\" />";
			$output .= "<input type=\"hidden\" name=\"searchpricemax\" value=\"\" />";
		}
	}

	if (get_awpcp_option('displaycountryfield') == 1){

		$output .= "<p>";
		$output .= __("Refine to Country","AWPCP");
		$output .= "<br>";

		if ($hasregionsmodule ==  1)
		{
			if ( regions_countries_exist() )
			{

				$output .= "<select name=\"searchcountry\">";
				if (!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
				{
					$output .= "<option value=\"\">";
					$output .= __("Select Option","AWPCP");
					$output .= "</option>";
				}

				$opsitemregcountrylist=awpcp_region_create_country_list($searchcountry,$byvalue='');
				$output .= "$opsitemregcountrylist";
				$output .= "</select>";
			}
			else
			{

				if (!isset($adcontact_country) || empty($adcontact_country) )
				{
					if (!get_awpcp_option('buildsearchdropdownlists'))
					{
						$output .= "
							(separate countries by commas)<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\" />
						";
					}
					else
					{
						if ( adstablehascountries() )
						{

							$output .= "<select name=\"searchcountry\">";
							if (!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
							{
								$output .= "<option value=\"\">";
								$output .= __("Select Option","AWPCP");
								$output .= "</option>";
							}
							$output .= create_dropdown_from_current_countries($searchcountry);
							$output .= "</select>";
						}
						else
						{
							$output .= "(";
							$output .= __("separate countries by commas","AWPCP");
							$output .= ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\" />
							";
						}
					}
				}
				else
				{
					$output .= "(";
					$output .= __("separate countries by commas","AWPCP");
					$output .= ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\" />
						";
				}
			}

		}
		else
		{
			if (!get_awpcp_option('buildsearchdropdownlists'))
			{
				$output .= "(";
				$output .= __("separate countries by commas","AWPCP");
				$output .= ")<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchcountry\" />
				";
			}
			elseif (get_awpcp_option('buildsearchdropdownlists'))
			{
				if ( adstablehascountries() )
				{

					$output .= "<select name=\"searchcountry\">";
					if (!(isset($_SESSION['regioncountryID'])) || empty($_SESSION['regioncountryID']) )
					{
						$output .= "<option value=\"\">";
						$output .= __("Select Option","AWPCP");
						$output .= "</option>";
					}
					$output .= create_dropdown_from_current_countries($searchcountry);
					$output .= "</select>";
				}
				else
				{
					$output .= "(";
					$output .= __("separate countries by commas","AWPCP");
					$output .= ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountry\" value=\"$searchccountry\" />
					";
				}
			}
		}

		$output .= "</p>";
	}

	if (get_awpcp_option('displaystatefield') == 1)
	{

		$output .= "<p>";
		$output .= __("Refine to State","AWPCP");
		$output .= "<br>";

		if ($hasregionsmodule ==  1)
		{
			if (!isset($adcontact_country) || empty($adcontact_country)){$adcontact_country='';}
				
			if ( regions_states_exist($adcontact_country) )
			{

				$output .= "<select name=\"searchstate\">";
				if (!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
				{
					$output .= "<option value=\"\">";
					$output .= __("Select Option","AWPCP");
					$output .= "</option>";
				}
				$opsitemregstatelist=awpcp_region_create_statown_list($searchstate,$byvalue='',$adcontact_country);
				$output .= "$opsitemregstatelist";
				$output .= "</select>";
			}
			else
			{

				if ( !isset($adcontact_country) || empty($adcontact_country) )
				{
					if (!get_awpcp_option('buildsearchdropdownlists'))
					{
						$output .= "(";
						$output .= __("separate states by commas","AWPCP");
						$output .= ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\" />
						";
					}
					else
					{

						if ( adstablehasstates() )
						{

							$output .= "<select name=\"searchstate\">";
							if (!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
							{
								$output .= "<option value=\"\">";
								$output .= __("Select Option","AWPCP");
								$output .= "</option>";
							}
							$output .= create_dropdown_from_current_states($searchstate);
							$output .= "</select>";

						}
						else
						{
							$output .= "(";
							$output .= __("separate states by commas","AWPCP");
							$output .= ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\" />
							";
						}
					}
				}
				else
				{
					$output .= "(";
					$output .= __("separate states by commas","AWPCP");
					$output .= ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\" />
						";
				}
			}

		}
		else
		{
			if (!get_awpcp_option('buildsearchdropdownlists'))
			{
				$output .= "(";
				$output .= __("separate states by commas","AWPCP");
				$output .= ")<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\" />
				";
			}
			else
			{
				if ( adstablehasstates() )
				{

					$output .= "<select name=\"searchstate\">";
					if (!(isset($_SESSION['regionstatownID'])) || empty($_SESSION['regionstatownID']) )
					{
						$output .= "<option value=\"\">";
						$output .= __("Select Option","AWPCP");
						$output .= "</option>";
					}
					$output .= create_dropdown_from_current_states($searchstate);
					$output .= "</select>";

				}
				else
				{
					$output .= "(";
					$output .= __("separate states by commas","AWPCP");
					$output .= ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchstate\" value=\"$searchstate\" />
					";
				}
			}
		}

		$output .= "</p>";

	}

	if (get_awpcp_option('displaycityfield') == 1)
	{
		$output .= "<p>";
		$output .= __("Refine to City","AWPCP");
		$output .= "<br>";

		if (!isset($searchccity) || empty($searchccity)){$searchccity='';}

		if ($hasregionsmodule ==  1)
		{
			if (!isset($adcontact_state) || empty($adcontact_state)){$adcontact_state='';}
				
			if ( regions_cities_exist($adcontact_state) )
			{

				$output .= "<select name=\"searchcity\">";
				if (!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
				{
					$output .= "<option value=\"\">";
					$output .= __("Select Option","AWPCP");
					$output .= "</option>";
				}
				$opsitemregcitylist=awpcp_region_create_city_list($searchcity,$byvalue='',$adcontact_state);
				$output .= "$opsitemregcitylist";
				$output .= "</select>";
			}
			else
			{
				if ( !isset($adcontact_state) || empty($adcontact_state) )
				{
					if (!get_awpcp_option('buildsearchdropdownlists'))
					{
						$output .= "(";
						$output .= __("separate cities by commas","AWPCP");
						$output .= ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\" />
						";
					}
					else
					{

						if ( adstablehascities() )
						{

							$output .= "<select name=\"searchcity\">";
							if (!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
							{
								$output .= "<option value=\"\">";
								$output .= __("Select Option","AWPCP");
								$output .= "</option>";
							}
							$output .= create_dropdown_from_current_cities($searchcity);
							$output .= "</select>";

						}
						else
						{
							$output .= "(";
							$output .= __("separate cities by commas","AWPCP");
							$output .= ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\" />
							";
						}
					}
				}
				else
				{
					$output .= "(";
					$output .= __("separate cities by commas","AWPCP");
					$output .= ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchccity\" />
						";
				}
			}

		}
		else
		{
			if (!get_awpcp_option('buildsearchdropdownlists'))
			{
				$output .= "(";
				$output .= __("separate cities by commas","AWPCP");
				$output .= ")<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchcity\" />
				";
			}
			else
			{
				if ( adstablehascities() )
				{

					$output .= "<select name=\"searchcity\">";
					if (!(isset($_SESSION['regioncityID'])) || empty($_SESSION['regioncityID']) )
					{
						$output .= "<option value=\"\">";
						$output .= __("Select Option","AWPCP");
						$output .= "</option>";
					}
					$output .= create_dropdown_from_current_cities($searchcity);
					$output .= "</select>";

				}
				else
				{
					$output .= "(";
					$output .= __("separate cities by commas","AWPCP");
					$output .= ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcity\" value=\"$searchcity\" />
					";
				}
			}
		}

		$output .= "</p>";
	}


	if (get_awpcp_option('displaycountyvillagefield') == 1)
	{
		$output .= "<p>";
		$output .= __("Refine to County/Village/Other","AWPCP");
		$output .= "<br>";

		if ($hasregionsmodule ==  1)
		{
			if ( regions_counties_exist($adcontact_city) )
			{

				$output .= "<select name=\"searchcountyvillage\"><option value=\"\">";
				$output .= __("Select Option","AWPCP");
				$output .= "</option>";
				$opsitemregcountyvillagelist=awpcp_region_create_county_village_list($searchcountyvillage);
				$output .= "$opsitemregcountyvillagelist";
				$output .= "</select>";
			}
			else
			{

				if ( !isset($adcontact_city) || empty($adcontact_city) )
				{

					if (!get_awpcp_option('buildsearchdropdownlists'))
					{
						$output .= "(";
						$output .= __("separate counties by commas","AWPCP");
						$output .= ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\" />
						";
					}
					else
					{
						if ( adstablehascounties() )
						{

							$output .= "<select name=\"searchcountyvillage\"><option value=\"\">";
							$output .= __("Select Option","AWPCP");
							$output .= "</option>";
							$output .= create_dropdown_from_current_counties($searchcountyvillage);
							$output .= "</select>";
						}
						else
						{
							$output .= "(";
							$output .= __("separate counties by commas","AWPCP");
							$output .= ")<br/>
								<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\" />
							";
						}
					}
				}
				else
				{
					$output .= "(";
					$output .= __("separate counties by commas","AWPCP");
					$output .= ")<br/>
							<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\" />
						";
				}
			}

		}
		else
		{
			if (!get_awpcp_option('buildsearchdropdownlists'))
			{
				$output .= "(";
				$output .= __("separate counties by commas","AWPCP");
				$output .= ")<br/>
				<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\" />
				";
			}
			else
			{
				if ( adstablehascounties() )
				{

					$output .= "<select name=\"searchcountyvillage\"><option value=\"\">";
					$output .= __("Select Option","AWPCP");
					$output .= "</option>";
					$output .= create_dropdown_from_current_counties($searchcountyvillage);
					$output .= "</select>";

				}
				else
				{
					$output .= "(";
					$output .= __("separate counties by commas","AWPCP");
					$output .= ")<br/>
						<input size=\"35\" type=\"text\" class=\"inputbox\" name=\"searchcountyvillage\" value=\"$searchccountyvillage\" />
					";
				}
			}
		}

		$output .= "</p>";
	}

	$output .= "<div align=\"center\"><input type=\"submit\" class=\"scbutton\" value=\"";
	$output .= __("Start Search","AWPCP");
	$output .= "\" /></div></form>";
	$output .= "</div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function dosearch() {
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$keywordphrase=clean_field($_REQUEST['keywordphrase']);
	$searchname=clean_field($_REQUEST['searchname']);
	$searchcity=clean_field($_REQUEST['searchcity']);
	$searchstate=clean_field($_REQUEST['searchstate']);
	$searchcountry=clean_field($_REQUEST['searchcountry']);
	$searchcategory=clean_field($_REQUEST['searchcategory']);
	$searchpricemin=clean_field($_REQUEST['searchpricemin']);
	$searchpricemax=clean_field($_REQUEST['searchpricemax']);
	$searchcountyvillage=clean_field($_REQUEST['searchcountyvillage']);

	$message='';

	$error=false;
	$theerrorslist="<h3>";
	$theerrorslist.=__("Cannot process your request due to the following error","AWPCP");
	$theerrorslist.=":</h3><ul>";
	if (!isset($keywordphrase) && empty($keywordphrase) &&
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

	if ( !empty($searchpricemin) )
	{
		if ( !is_numeric($searchpricemin) )
		{
			$error=true;
			$theerrorslist.="<li>";
			$theerrorslist.=__("You have entered an invalid minimum price. Make sure your price contains numbers only. Please do not include currency symbols","AWPCP");
			$theerrorslist.="</li>";
		}
	}

	if ( !empty($searchpricemax) )
	{
		if (	!is_numeric($searchpricemax) )
		{
			$error=true;
			$theerrorslist.="<li>";
			$theerrorslist.=__("You have entered an invalid maximum price. Make sure your price contains numbers only. Please do not include currency symbols","AWPCP");
			$theerrorslist.="</li>";
		}
	}

	if ( empty($searchpricemin) && !empty($searchpricemax) )
	{
		$searchpricemin=1;
	}

	$theerrorslist.="</ul>";
	$message="<p>$theerrorslist</p>";

	if ($error){
		$output .= load_ad_search_form($keywordphrase,$searchname,$searchcity,$searchstate,$searchcountry,$searchcountyvillage,$searchcategory,$searchpricemin,$searchpricemax,$message);
	}

	else
	{
		$where="disabled ='0'";

		if (isset($keywordphrase) && !empty($keywordphrase))
		{
			$where.=" AND MATCH (ad_title,ad_details) AGAINST (\"$keywordphrase\" IN BOOLEAN MODE)";
		}

		if (isset($searchname) && !empty($searchname))
		{
			$where.=" AND ad_contact_name = '$searchname'";
		}

		if (isset($searchcity) && !empty($searchcity))
		{

			if (is_array( $searchcity ) )
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

		if (isset($searchstate) && !empty($searchstate))
		{
			if (is_array( $searchstate ) )
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

		if (isset($searchcountry) && !empty($searchcountry))
		{
			if (is_array( $searchcountry ) )
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

		if (isset($searchcountyvillage) && !empty($searchcountyvillage)){

			if (is_array( $searchcountyvillage ) )
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

		if (isset($searchcategory) && !empty($searchcategory))
		{
			$where.=" AND (ad_category_id = '$searchcategory' OR ad_category_parent_id = '$searchcategory')";
		}

		if (isset($searchpricemin) && !empty($searchpricemin))
		{
			$searchpricemincents=($searchpricemin * 100);
			$where.=" AND ad_item_price >= '$searchpricemincents'";
		}

		if (isset($searchpricemax) && !empty($searchpricemax))
		{
			$searchpricemaxcents=($searchpricemax * 100);
			$where.=" AND ad_item_price <= '$searchpricemaxcents'";
		}

		$grouporderby=get_group_orderby();

		$output .= display_ads($where,$byl='',$hidepager='',$grouporderby,$adorcat='ad');

	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: process first step of edit ad request
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function editadstep1($adaccesskey,$editemail,$awpcppagename)
{
	$output = '';
	global $wpdb,$hasextrafieldsmodule;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
	$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=10);


	$query="SELECT ad_id,adterm_id FROM ".$tbl_ads." WHERE ad_key='$adaccesskey' AND ad_contact_email='$editemail'";
	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
	while ($rsrow=mysql_fetch_row($res))
	{
		list($adid,$adtermid)=$rsrow;
	}

	if (isset($adid) && !empty($adid))
	{
		$output .= load_ad_post_form($adid,$action='editad',$awpcppagename,$adtermid,$editemail,$adaccesskey,$adtitle='',$adcontact_name='',$adcontact_phone='',$adcontact_email='',$adcategory='',$adcontact_city='',$adcontact_state='',$adcontact_country='',$ad_county_village='',$ad_item_price='',$addetails='',$adpaymethod='',$offset,$results,$ermsg='',$websiteurl='',$checkhuman='',$numval1='',$numval2='');
	}

	else
	{
		$message="<p class=\"messagealert\">";
		$message.=__("The information you have entered does not match the information on file. Please make sure you are using the same email address you used to post your ad and the exact access key that was emailed to you when you posted your ad","AWPCP");
		$message.="</p>";

		$output .= load_ad_edit_form($action='editad',$awpcppagename,$editemail,$adaccesskey,$message);
	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function processadstep1($adid,$adterm_id,$adkey,$editemail,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$adaction,$awpcppagename,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2)
{
	$output = '';
	global $wpdb,$awpcp_imagesurl,$hasextrafieldsmodule;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$permastruc=get_option('permalink_structure');

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
	if (!isset($adtitle) || empty($adtitle))
	{
		$error=true;
		$adtitlemsg="<li class=\"erroralert\">";
		$adtitlemsg.=__("You did not enter a title for your ad","AWPCP");
		$adtitlemsg.="</li>";
	}

	// Check for ad details
	if (!isset($addetails) || empty($addetails))
	{
		$error=true;
		$addetailsmsg="<li class=\"erroralert\">";
		$addetailsmsg.=__("You did not enter any text for your ad. Please enter some text for your ad","AWPCP");
		$addetailsmsg.="</li>";
	}

	// Check for ad category
	if (!isset($adcategory) || empty($adcategory))
	{
		$error=true;
		$adcategorymsg="<li class=\"erroralert\">";
		$adcategorymsg.=__("You did not select a category for your ad. Please select a category for your ad","AWPCP");
		$adcategorymsg.="</li>";
	}

	// Check for ad poster's name
	if (!isset($adcontact_name) || empty($adcontact_name))
	{
		$error=true;
		$adcnamemsg="<li class=\"erroralert\">";
		$adcnamemsg.=__("You did not enter your name. Your name is required","AWPCP");
		$adcnamemsg.="</li>";

	}

	// Check for ad poster's email address
	if (!isset($adcontact_email) || empty($adcontact_email))
	{
		$error=true;
		$adcemailmsg1=="<li class=\"erroralert\">";
		$adcemailmsg1.=__("You did not enter your email. Your email is required","AWPCP");
		$adcemailmsg1.="</li>";
	}

	// Check if email address entered is in a valid email address format
	if (!isValidEmailAddress($adcontact_email))
	{
		$error=true;
		$adcemailmsg2="<li class=\"erroralert\">";
		$adcemailmsg2.=__("The email address you entered was not a valid email address. Please check for errors and try again","AWPCP");
		$adcemailmsg2.="</li>";
	}

	// If phone field is checked and required make sure phone value was entered
	if ((get_awpcp_option('displayphonefield') == 1)
	&&(get_awpcp_option('displayphonefieldreqop') == 1))
	{
		if (!isset($adcontact_phone) || empty($adcontact_phone))
		{
			$error=true;
			$adcphonemsg="<li class=\"erroralert\">";
			$adcphonemsg.=__("You did not enter your phone number. Your phone number is required","AWPCP");
			$adcphonemsg.="</li>";
		}
	}

	// If city field is checked and required make sure city value was entered
	if ((get_awpcp_option('displaycityfield') == 1)
	&&(get_awpcp_option('displaycityfieldreqop') == 1))
	{
		if (!isset($adcontact_city) || empty($adcontact_city))
		{
			$error=true;
			$adcitymsg="<li class=\"erroralert\">";
			$adcitymsg.=__("You did not enter your city. Your city is required","AWPCP");
			$adcitymsg.="</li>";
		}
	}

	// If state field is checked and required make sure state value was entered
	if ((get_awpcp_option('displaystatefield') == 1)
	&&(get_awpcp_option('displaystatefieldreqop') == 1))
	{
		if (!isset($adcontact_state) || empty($adcontact_state))
		{
			$error=true;
			$adstatemsg="<li class=\"erroralert\">";
			$adstatemsg.=__("You did not enter your state. Your state is required","AWPCP");
			$adstatemsg.="</li>";
		}
	}

	// If country field is checked and required make sure country value was entered
	if ((get_awpcp_option('displaycountryfield') == 1)
	&&(get_awpcp_option('displaycountryfieldreqop') == 1))
	{
		if (!isset($adcontact_country) || empty($adcontact_country))
		{
			$error=true;
			$adcountrymsg="<li class=\"erroralert\">";
			$adcountrymsg.=__("You did not enter your country. Your country is required","AWPCP");
			$adcountrymsg.="</li>";
		}
	}

	// If county/village field is checked and required make sure county/village value was entered
	if ((get_awpcp_option('displaycountyvillagefield') == 1)
	&&(get_awpcp_option('displaycountyvillagefieldreqop') == 1))
	{
		if (!isset($ad_county_village) || empty($ad_county_village))
		{
			$error=true;
			$adcountyvillagemsg="<li class=\"erroralert\">";
			$adcountyvillagemsg.=__("You did not enter your county/village. Your county/village is required","AWPCP");
			$adcountyvillagemsg.="</li>";
		}
	}

	if (get_awpcp_option('noadsinparentcat'))
	{

		if (!category_is_child($adcategory))
		{
			$awpcpcatname=get_adcatname($adcategory);
			$error=true;
			$noadsinparentcatmsg="<li class=\"erroralert\">";
			$noadsinparentcatmsg.=__("You can not list your ad in top level categories. You need to select a sub category of $awpcpcatname to list your ad under","AWPCP");
			$noadsinparentcatmsg.="</li>";
		}

	}

	if (($adaction != 'delete') && ($adaction != 'editad'))
	{
		// If running in pay mode make sure a payment method has been checked
		if ((get_awpcp_option('freepay') == 1) && !is_admin())
		{
			if (get_adfee_amount($adterm_id) > 0)
			{
				if (!isset($adpaymethod) || empty($adpaymethod))
				{
					$error=true;
					$adpaymethodmsg="<li class=\"erroralert\">";
					$adpaymethodmsg.=__(">You did not select your payment method. The information is required.","AWPCP");
					$adpaymethodmsg.="</li>";
				}
			}
		}

		// If running in pay mode make sure an ad term has been selected
		if ((get_awpcp_option('freepay') == 1) && !is_admin())
		{
			if (($adaction != 'delete') && ($adaction != 'editad'))
			{
				if (!isset($adterm_id) || empty ($adterm_id))
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
	if ((get_awpcp_option('displaypricefield') == 1)
	&&(get_awpcp_option('displaypricefieldreqop') == 1))
	{
		if (!isset($ad_item_price) || empty($ad_item_price))
		{
			$error=true;
			$aditempricemsg1="<li class=\"erroralert\">";
			$aditempricemsg1.=__("You did not enter the price of your item. The item price is required.","AWPCP");
			$aditempricemsg1.="</li>";
		}
	}

	// Make sure the item price is a numerical value
	if (get_awpcp_option('displaypricefield') == 1)
	{
		if ( isset($ad_item_price) && !empty($ad_item_price) && !is_numeric($ad_item_price) )
		{
			$error=true;
			$aditempricemsg2="<li class=\"erroralert\">";
			$aditempricemsg2.=__("You have entered an invalid item price. Make sure your price contains numbers only. Please do not include currency symbols.","AWPCP");
			$aditempricemsg2.="</li>";
		}
	}

	// If website field is checked and required make sure website value was entered
	if ((get_awpcp_option('displaywebsitefield') == 1)
	&&(get_awpcp_option('displaywebsitefieldreqop') == 1))
	{
		if (!isset($websiteurl) || empty($websiteurl))
		{
			$error=true;
			$websiteurlmsg1="<li class=\"erroralert\">";
			$websiteurlmsg1.=__("You did not enter your website address. Your website address is required.","AWPCP");
			$websiteurlmsg1.="</li>";
		}
	}

	//If they have submitted a website address make sure it is correctly formatted

	if (isset($websiteurl) && !empty($websiteurl) )
	{
		if ( !isValidURL($websiteurl) )
		{
			$error=true;
			$websiteurlmsg2="<li class=\"erroralert\">";
			$websiteurlmsg2.=__("Your website address is not properly formatted. Please make sure you have included the http:// part of your website address","AWPCP");
			$websiteurlmsg2.="</li>";
		}
	}

	$thesum=($numval1 +  $numval2);

	if ((get_awpcp_option('contactformcheckhuman') == 1) && !is_admin())
	{

		if (!isset($checkhuman) || empty($checkhuman))
		{
			$error=true;
			$checkhumanmsg="<li class=\"erroralert\">";
			$checkhumanmsg.=__("You did not solve the math problem. Please solve the math problem to proceed.","AWPCP");
			$checkhumanmsg.="</li>";
		}
		if ($checkhuman != $thesum)
		{
			$error=true;
			$sumwrongmsg="<li class=\"erroralert\">";
			$sumwrongmsg.=__("Your solution to the math problem was incorrect. Please try again","AWPCP");
			$sumwrongmsg.="</li>";
		}
	}

	if ($hasextrafieldsmodule == 1)
	{

		$x_field_errors_msg=validate_x_form();
		if (isset($x_field_errors_msg) && !empty($x_field_errors_msg))
		{
			$error=true;
		}
	}
	else
	{
		$x_field_errors_msg='';
	}

	if ($error)
	{
		$ermsg="<p><img src=\"$awpcp_imagesurl/Warning.png\" border=\"0\" alt=\"Alert\" style=\"float:left;margin-right:10px;\">";
		$ermsg.=__("There has been an error found. Please review the list of problems, correct them then try again","AWPCP");
		$ermsg.="</p><b>";
		$ermsg.=__("The errors","AWPCP");
		$ermsg.=":</b><br/><ul>";
		$ermsg.=__("$adtitlemsg $adcategorymsg $adcnamemsg $adcemailmsg1 $adcemailmsg2 $adcphonemsg $adcitymsg $adstatemsg $adcountrymsg $addetailsmsg $adpaymethodmsg $adtermidmsg $aditempricemsg1 $aditempricemsg2 $websiteurlmsg1 $websiteurlmsg2 $checkhumanmsg $sumwrongmsg $noadsinparentcatmsg $x_field_errors_msg","AWPCP");
		$ermsg.="</ul>";

		$output .= load_ad_post_form($adid,$action=$adaction,$awpcppagename,$adterm_id,$editemail,$adkey,$adtitle,$adcontact_name,$adcontact_phone,$adcontact_email,$adcategory,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$adpaymethod,$offset,$results,$ermsg,$websiteurl,$checkhuman,$numval1,$numval2);
	}
	else
	{

		if ($adaction == 'delete')
		{
			$output .= deletead($adid,$adkey,$editemail);
		}
		else if ($adaction == 'editad')
		{
			$isadmin=checkifisadmin();

			$qdisabled='';

			if (!(is_admin()))
			{
				if (get_awpcp_option('adapprove') == 1)
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
				
			$update_x_fields="";
				
			if ($hasextrafieldsmodule == 1)
			{
				$update_x_fields=do_x_fields_update();
			}
				
			$query="UPDATE ".$tbl_ads." SET ad_category_id='$adcategory',ad_category_parent_id='$adcategory_parent_id',ad_title='$adtitle',
			ad_details='$addetails',websiteurl='$websiteurl',ad_contact_phone='$adcontact_phone',ad_contact_name='$adcontact_name',ad_contact_email='$adcontact_email',ad_city='$adcontact_city',ad_state='$adcontact_state',ad_country='$adcontact_country',ad_county_village='$ad_county_village',ad_item_price='$itempriceincents',
			$qdisabled $update_x_fields ad_last_updated=now() WHERE ad_id='$adid' AND ad_key='$adkey'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}


			if ($isadmin == 1 && is_admin())
			{
				$message=__("The ad has been edited successfully.");
				$message.="<a href=\"?page=Manage1&offset=$offset&results=$results\">";
				$message.=__("Back to view listings");
				$message.="</a>";

				$output .= $message;
			}

			else
			{

				if (get_awpcp_option('imagesallowdisallow'))
				{
					if (get_awpcp_option('freepay') == 1)
					{
						$totalimagesallowed=get_numimgsallowed($adterm_id);
					}
					else if (ad_term_id_set($adid))
					{
						$totalimagesallowed=get_numimgsallowed($adterm_id);
					}
					else
					{
						$totalimagesallowed=get_awpcp_option('imagesallowedfree');
					}


					if ( $totalimagesallowed > 0 )
					{
						$output .= editimages($adterm_id,$adid,$adkey,$editemail);
					}
					else
					{
						$messagetouser=__("Your changes have been saved");

						$output .= "<h3>$messagetouser</h3>";

						$output .= showad($adid,$omitmenu='');

					}
				}
				else
				{
					$messagetouser=__("Your changes have been saved");
					$output .= "<h3>$messagetouser</h3>";

					$output .= showad($adid,$omitmenu='');

				}
			}
		}
		else
		{
			//Begin processing new ad
			$key=time();

			if (isset($adterm_id) && !empty($adterm_id))
			{
				$feeamt=get_adfee_amount($adterm_id);
			}
			else
			{
				$feeamt=0;
			}

			if (get_awpcp_option('adapprove') == 1)
			{
				$disabled='1';
			}
			else
			{
				$disabled='0';
			}

			if ($disabled == 0)
			{

				if (get_awpcp_option('freepay') == 1)
				{

					if ($feeamt <= '0')
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

			if ($adexpireafter == 0)
			{
				$adexpireafter=9125;
			}
			else
			{
				$adexpireafter=$adexpireafter;
			}



			$adcategory_parent_id=get_cat_parent_ID($adcategory);
			$itempriceincents=($ad_item_price * 100);

			$update_x_fields='';
			if ($hasextrafieldsmodule == 1)
			{
				$update_x_fields=do_x_fields_update();
			}

			$query="INSERT INTO ".$tbl_ads." SET ad_category_id='$adcategory',ad_category_parent_id='$adcategory_parent_id',ad_title='$adtitle',ad_details='$addetails',ad_contact_phone='$adcontact_phone',ad_contact_name='$adcontact_name',ad_contact_email='$adcontact_email',ad_city='$adcontact_city',ad_state='$adcontact_state',ad_country='$adcontact_country',ad_county_village='$ad_county_village',ad_item_price='$itempriceincents',websiteurl='$websiteurl',";

			if ( isset($adterm_id) && !empty($adterm_id) )
			{
				$query.="adterm_id='$adterm_id',";
			}
			else
			{
				$query.="adterm_id='0',";
			}

			$query.="ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $adexpireafter DAY,disabled='$disabled',ad_key='$key',ad_transaction_id='',$update_x_fields ad_postdate=now()";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			$ad_id=mysql_insert_id();


			if ( (get_awpcp_option('freepay') == 1) )
			{
				$output .= processadstep2_paymode($ad_id,$adterm_id,$key,$awpcpuerror='',$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod,$adaction);
			}
			elseif ((get_awpcp_option('freepay') == '0') && (get_awpcp_option('imagesallowdisallow') == 1))
			{
				$output .= processadstep2_freemode($ad_id,$adterm_id,$key,$awpcpuerror='',$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod);
			}
			else
			{
				if (isset($_SESSION['regioncountryID']) )
				{
					unset($_SESSION['regioncountryID']);
				}
				if (isset($_SESSION['regionstatownID']) )
				{
					unset($_SESSION['regionstatownID']);
				}
				if (isset($_SESSION['regioncityID']) )
				{
					unset($_SESSION['regioncityID']);
				}

				$awpcpshowadsample=1;
				$message=__("Submission received","AWPCP");
				$awpcpsubmissionresultmessage =ad_success_email($ad_id,$txn_id='',$key,$message,$gateway='');

				$output .= "<div id=\"classiwrapper\">";
				$output .= awpcp_menu_items();
				$output .= "<p>";
				$output .= $awpcpsubmissionresultmessage;
				$output .= "</p>";
				if ($awpcpshowadsample == 1)
				{
					$output .= "<h2>";
					$output .= __("Sample of your ad","AWPCP");
					$output .= "</h2>";
					$output .= showad($ad_id,$omitmenu='1');
				}
				$output .= "</div>";
			}
		}
	}
	return $output;
}

function processadstep2_paymode($ad_id,$adterm_id,$adkey,$awpcpuerror,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod,$adaction)
{
	$output = '';
	if (get_awpcp_option('imagesallowdisallow') == 1)
	{
		$numimgsallowed=get_numimgsallowed($adterm_id);
			
		if ( $numimgsallowed > 0 )
		{
			$output .= "<h2>";
			$output .= __("Step 2 Upload Images","AWPCP");
			$output .= "</h2>";

			$totalimagesuploaded=get_total_imagesuploaded($ad_id);

			if ($totalimagesuploaded < $numimgsallowed)
			{
				$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep='payment',$adpaymethod,$awpcpuperror='');
			}
			else
			{
				$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep='paymentnoform',$adpaymethod,$awpcpuperror='');
			}

		}
		else
		{
			$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep='paymentnoform',$adpaymethod,$awpcpuperror='');
		}

		$classicontent=$showimageuploadform;
		$output .= "$classicontent";
	}
	else
	{
		$output .= processadstep3($ad_id,$adterm_id,$adkey,$adpaymethod);
	}
	return $output;
}

function processadstep2_freemode($ad_id,$adterm_id,$adkey,$awpcpuerror,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$adtitle,$addetails,$adpaymethod)
{
	$output = '';
	$totalimagesuploaded=get_total_imagesuploaded($ad_id);

	if (isset($adaction) && !empty($adaction)){$adaction=$adaction;} else {$adaction='';}

	if (!isset($totalimagesuploaded) || empty($totalimagesuploaded))
	{
		$totalimagesuploaded=0;
	}

	if ( (get_awpcp_option('imagesallowdisallow') == 1) && ( get_awpcp_option('imagesallowedfree') > 0))
	{

		$output .= "<h2>";
		$output .= __("Step 2 Upload Images","AWPCP");
		$output .= "</h2>";

		$imagesforfree=get_awpcp_option('imagesallowedfree');


		if ($totalimagesuploaded < $imagesforfree)
		{
			$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep='finish',$adpaymethod,$awpcpuperror='');
		}
		else
		{
			$showimageuploadform=display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep='finishnoform',$adpaymethod,$awpcpuperror='');
		}

		$classicontent="$showimageuploadform";
		$output .= "$classicontent";
	}
	else
	{
		$awpcpadpostedmsg=__("Your ad has been submitted","AWPCP");

		if (get_awpcp_option('adapprove') == 1)
		{
			$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
			$awpcpadpostedmsg.="<p>";
			$awpcpadpostedmsg.=$awaitingapprovalmsg;
			$awpcpadpostedmsg.="</p>";
		}
		if (get_awpcp_option('imagesapprove') == 1)
		{
			$imagesawaitingapprovalmsg=__("If you have uploaded images your images will not show up until an admin has approved them.","AWPCP");
			$awpcpadpostedmsg.="<p>";
			$awpcpadpostedmsg.=$imagesawaitingapprovalmsg;
			$awpcpadpostedmsg.="</p>";
		}

		$awpcpshowadsample=1;
		$message=$awpcpadpostedmsg;
		$awpcpsubmissionresultmessage =ad_success_email($ad_id,$txn_id='',$adkey,$awpcpadpostedmsg,$gateway='');
			
		$output .= "<div id=\"classiwrapper\">";
		$output .= awpcp_menu_items();
		$output .= "<p>";
		$output .= $awpcpsubmissionresultmessage;
		$output .= "</p>";
		if ($awpcpshowadsample == 1)
		{
			$output .= "<h2>";
			$output .= __("Sample of your ad","AWPCP");
			$output .= "</h2>";
			$output .= showad($ad_id,$omitmenu='1');
		}
		$output .= "</div>";
	}
	return $output;
}

function processadstep3($adid,$adterm_id,$key,$adpaymethod)
{
	$output = '';
	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$permastruc=get_option('permalink_structure');
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$amount=0;

	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$paymentthankyoupagename=sanitize_title(get_awpcp_option('paymentthankyoupagename'), $post_ID='');
	$paymentthankyoupageid=awpcp_get_page_id($paymentthankyoupagename);
	$paymentcancelpagename=sanitize_title(get_awpcp_option('paymentcancelpagename'), $post_ID='');
	$paymentcancelpageid=awpcp_get_page_id($paymentcancelpagename);

	if (isset($adpaymethod) && !empty($adpaymethod))
	{
		if ($adpaymethod == 'paypal')
		{
			$custadpcde="PP";
		}
		elseif ($adpaymethod == '2checkout')
		{
			$custadpcde="2CH";
		}
		elseif ($adpaymethod == 'googlecheckout')
		{
			$custadpcde="GCH";
		}
	}

	$base=get_option('siteurl');
	$custom="$adid";
	$custom.="_";
	$custom.="$key";
	$custom.="_";
	$custom.="$custadpcde";

	////////////////////////////////////////////////////////////////////////////
	// Step:3 Create/Display payment page
	////////////////////////////////////////////////////////////////////////////

	$query="SELECT adterm_name,amount,rec_period FROM ".$tbl_ad_fees." WHERE adterm_id='$adterm_id'";
	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
	while ($rsrow=mysql_fetch_row($res))
	{
		list($adterm_name,$amount,$recperiod)=$rsrow;
	}
	if ($amount <= 0)
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
		if ($adpaymethod == 'paypal')
		{
			$awpcppaypalpaybutton=awpcp_displaypaymentbutton_paypal($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);

			$showpaybutton.="$awpcppaypalpaybutton";

		} // End if ad payment is paypal

		/////////////////////////////////////////////////////////////////////////////
		// Print the  2Checkout button option if 2Checkout is activated
		/////////////////////////////////////////////////////////////////////////////

		elseif ($adpaymethod == '2checkout')
		{
			$awpcptwocheckoutpaybutton=awpcp_displaypaymentbutton_twocheckout($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);
			$showpaybutton.="$awpcptwocheckoutpaybutton";
		} // End if ad payment is 2checkout

		//////////////////////////////////////////////////////////////////////////////////
		// Print the  Google Checkout button option if module exists and GC is activated
		//////////////////////////////////////////////////////////////////////////////////
		elseif ($adpaymethod == 'googlecheckout')
		{
			global $hasgooglecheckoutmodule;
			if ($hasgooglecheckoutmodule == 1)
			{
				$awpcpgooglecheckoutpaybutton=awpcp_displaypaymentbutton_googlecheckout($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);
				$showpaybutton.="$awpcpgooglecheckoutpaybutton";
			}
		}
	} // End if the fee amount is not a zero value

	// Show page based on if amount is zero or payment needs to be made
	if ( $amount <= 0 )
	{
		$finishbutton="<p>";
		$finishbutton.=__("Please click the finish button to complete the process of submitting your listing","AWPCP");
		$finishbutton.="</p>
		<form method=\"post\" id=\"awpcpui_process\">
		<input type=\"hidden\" name=\"a\" value=\"adpostfinish\" />
		<input type=\"hidden\" name=\"adid\" value=\"$adid\" />
		<input type=\"hidden\" name=\"adkey\" value=\"$key\" />
		<input type=\"hidden\" name=\"adtermid\" value=\"$adterm_id\" />
		<input type=\"Submit\" value=\"";
		$finishbutton.=__("Finish","AWPCP");
		$finishbutton.="\"/></form>";
		$displaypaymentform="$finishbutton";
	}
	else
	{
		$displaypaymentform="$showpaybutton";
	}

	////////////////////////////////////////////////////////////////////////////
	// Display the content
	////////////////////////////////////////////////////////////////////////////

	$adpostform_content=$displaypaymentform;
	$output .= "$adpostform_content";
	return $output;
}

function awpcp_displaypaymentbutton_paypal($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base)
{
	global $awpcp_imagesurl;

	$showpaybuttonpaypal="";

	if ( get_awpcp_option('seofriendlyurls') )
	{
		if (isset($permastruc) && !empty($permastruc))
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
	elseif (!( get_awpcp_option('seofriendlyurls') ) )
	{
		if (isset($permastruc) && !empty($permastruc))
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

	if (get_awpcp_option('paylivetestmode') == 1)
	{
		$paypalurl="https://www.sandbox.paypal.com/cgi-bin/webscr";
	}
	else
	{
		$paypalurl="https://www.paypal.com/cgi-bin/webscr";
	}

	$showpaybuttonpaypal.="<form action=\"$paypalurl\" method=\"post\">";

	if (get_awpcp_option('paypalpaymentsrecurring'))
	{
		$paypalcmdvalue="<input type=\"hidden\" name=\"cmd\" value=\"_xclick-subscriptions\" />";
	}
	else
	{
		$paypalcmdvalue="<input type=\"hidden\" name=\"cmd\" value=\"_xclick\" />";
	}

	$showpaybuttonpaypal.="$paypalcmdvalue";

	if (get_awpcp_option('paylivetestmode') == 1)
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
	if (get_awpcp_option('paypalpaymentsrecurring'))
	{
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"a3\" value=\"$amount\" />";
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"p3\" value=\"$recperiod\" />";
		$showpaybuttonpaypal.="<input type=\"hidden\" name=\"t3\" value=\"D\" />";
	}
	//$showpaybuttonpaypal.="<input class=\"button\" type=\"submit\" value=\"";
	//$showpaybuttonpaypal.=__("Pay With PayPal","AWPCP");
	//$showpaybuttonpaypal.="\" />";
	$showpaybuttonpaypal.="<input type=\"image\" src=\"$awpcp_imagesurl/paypalbuynow.gif\" border=\"0\" name=\"submit\" alt=\"";
	$showpaybuttonpaypal.=__("Make payments with PayPal - it's fast, free and secure!","AWPCP");
	$showpaybuttonpaypal.="\" />";
	$showpaybuttonpaypal.="</form>";

	return $showpaybuttonpaypal;

}

function awpcp_displaypaymentbutton_twocheckout($adid,$custom,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base)
{

	global $awpcp_imagesurl;
	$showpaybuttontwocheckout="";

	if ( get_awpcp_option('seofriendlyurls') )
	{
		if (isset($permastruc) && !empty($permastruc))
		{
			$x_receipt_link_url="$quers/$paymentthankyoupagename/$custom";
		}
		else
		{
			$x_receipt_link_url="$quers/?page_id=$paymentthankyoupageid&i=$custom";
		}
	}
	elseif (!( get_awpcp_option('seofriendlyurls') ) )
	{
		if (isset($permastruc) && !empty($permastruc))
		{
			$x_receipt_link_url="$quers/$paymentthankyoupagename/$custom";
		}
		else
		{
			$x_receipt_link_url="$quers/?page_id=$paymentthankyoupageid&i=$custom";
		}
	}

	if (get_awpcp_option('twocheckoutpaymentsrecurring'))
	{
		$x_login_sid="<input type='hidden' name=\"sid\" value=\"".get_awpcp_option('2checkout')."\" />";
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
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_Receipt_Link_URL\" value=\"$x_receipt_link_url\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_invoice_num\" value=\"1\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_amount\" value=\"$amount\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_prod\" value=\"$adterm_id\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_name\" value=\"$adterm_name\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_description\" value=\"$adterm_name\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"c_tangible\" value=\"N\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_item_number\" value=\"$adterm_id\" />";
	$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"x_custom\" value=\"$custom\" />";

	if (get_awpcp_option('twocheckoutpaymentsrecurring'))
	{
		$showpaybuttontwocheckout.="<input type='hidden' name=\"quantity\" value='1' />";
		$showpaybuttontwocheckout.="<input type='hidden' name=\"product_id\" value=\"".get_2co_prodid($adterm_id)."\" />";
		$showpaybuttontwocheckout.="<input type='hidden' name=\"x_twocorec\" value=\"1\" />";
	}

	if (get_awpcp_option('paylivetestmode') == 1)
	{
		$showpaybuttontwocheckout.="<input type=\"hidden\" name=\"demo\" value=\"Y\" />";
	}
	//$showpaybuttontwocheckout.="<input name=\"submit\" class=\"button\" type=\"submit\" value=\"";
	//$showpaybuttontwocheckout.=__("Pay With 2Checkout","AWPCP");
	$showpaybuttontwocheckout.="<input type=\"image\" src=\"$awpcp_imagesurl/buybow2checkout.gif\" border=\"0\" name=\"submit\" alt=\"";
	$showpaybuttontwocheckout.=__("Pay With 2Checkout","AWPCP");
	$showpaybuttontwocheckout.="\" /></form>";

	return $showpaybuttontwocheckout;
}

function display_awpcp_image_upload_form($ad_id,$adterm_id,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuperror)
{
	$awpcp_image_upload_form='';
	$totalimagesuploaded=0;

	$max_image_size=get_awpcp_option('maximagesize');

	if (get_awpcp_option('freepay') == 1)
	{

		$numimgsallowed=get_numimgsallowed($adterm_id);
	}
	else
	{
		$numimgsallowed=get_awpcp_option('imagesallowedfree');
	}

	if (adidexists($ad_id))
	{
		$totalimagesuploaded=get_total_imagesuploaded($ad_id);
	}

	$numimgsleft=($numimgsallowed - $totalimagesuploaded);

	$awpcp_payment_fee=get_adfee_amount($adterm_id);

	if ($awpcp_payment_fee <= 0){$nextstep ="finish";}

	if ($nextstep == 'finishnoform')
	{
		$showimageuploadform='';
	}
	elseif ($nextstep == 'paymentnoform')
	{
		$showimageuploadform='';
	}
	else
	{

		global $awpcp_plugin_path;

		$showimageuploadform="<p>";
		$showimageuploadform.=__("Image slots available","AWPCP");
		$showimageuploadform.="[<b>$numimgsleft</b>]";
		$showimageuploadform.="</p>";

		if (get_awpcp_option('imagesapprove') == 1)
		{
			$showimageuploadform.="<p>";
			$showimageuploadform.=__("Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it","AWPCP");
			$showimageuploadform.="</p>";

		}

		if (isset($awpcpuperror) && !empty($awpcpuperror))
		{
			$showimageuploadform.="<p>";
			foreach($awpcpuperror as $theawpcpuerror)
			{
				$showimageuploadform.=$theawpcpuerror;
			}
			$showimageuploadform.="</p>";
		}

		if (!isset($adaction) || empty($adaction))
		{
			$adaction="notset";
		}
		if (!isset($adterm_id) || empty($adterm_id))
		{
			$adterm_id=-1;
		}
		$showimageuploadform.="<div class=\"theuploaddiv\">
		<form id=\"AWPCPForm1\" name=\"AWPCPForm1\" method=\"post\" ENCTYPE=\"Multipart/form-data\">
		<p id=\"showhideuploadform\">
		<input type=\"hidden\" name=\"adid\" value=\"$ad_id\" />
		<input type=\"hidden\" name=\"adtermid\" value=\"$adterm_id\" />
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
	}


	$awpcp_image_upload_form.=$showimageuploadform;


	$awpcp_image_upload_form.="<div class=\"fixfloat\"></div>";
	$awpcp_image_upload_form.="<div class=\"finishbutton\"><div class=\"finishbuttonleft\">";

	if (($nextstep == 'payment') || ($nextstep == 'paymentnoform'))
	{
		$clicktheword1=__("Go To Next Step");$clicktheword2=__("continue");
	}
	elseif (($nextstep == 'finish') || ($nextstep == 'finishnoform'))
	{
		$clicktheword1=__("Finish");$clicktheword2=__("complete");
	}
	else
	{
		$clicktheword1=__("Finish");$clicktheword2=__("complete");
	}

	if ($nextstep == 'finishnoform')
	{
		$awpcp_image_upload_form.=__("<p>Please click the $clicktheword1 button to $clicktheword2 this process.</p>","AWPCP");
	}
	elseif ($nextstep == 'paymentnoform')
	{
		$awpcp_image_upload_form.=__("<p>Please click the $clicktheword1 button to $clicktheword2 this process.</p>","AWPCP");
	}
	else
	{
		$awpcp_image_upload_form.=__("<p>If you prefer not to upload any images please click the $clicktheword1 button to $clicktheword2 this process.</p>","AWPCP");

	}
	$awpcp_image_upload_form.="</div><div class=\"finishbuttonright\">";

	$finishbutton="
				<form method=\"post\" id=\"awpcpui_process\">";
	if (($nextstep == 'payment') || ($nextstep == 'paymentnoform'))
	{
		$finishbutton.="<input type=\"hidden\" name=\"a\" value=\"loadpaymentpage\" />";
		$finishbutton.="<input type=\"hidden\" name=\"adpaymethod\" value=\"$adpaymethod\" />";
	}
	elseif ($nextstep == 'finish')
	{
		$finishbutton.="<input type=\"hidden\" name=\"a\" value=\"adpostfinish\" />";
	}
	else
	{
		$finishbutton.="<input type=\"hidden\" name=\"a\" value=\"adpostfinish\" />";
	}
	$finishbutton.="
				<input type=\"hidden\" name=\"adid\" value=\"$ad_id\" />
				<input type=\"hidden\" name=\"adkey\" value=\"$adkey\" />
				<input type=\"hidden\" name=\"adaction\" value=\"$adaction\" />
				<input type=\"hidden\" name=\"adtermid\" value=\"$adterm_id\" />
				<input type=\"hidden\" name=\"adpaymethod\" value=\"$adpaymethod\" />				
				<input type=\"Submit\" class=\"button\" value=\"";
	if (($nextstep == 'payment') || ($nextstep == 'paymentnoform'))
	{
		$finishbutton.=__("Go To Next Step","AWPCP");
	}
	elseif ($nextstep == 'payment')
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
	$awpcp_image_upload_form.="</div><div class=\"fixfloat\"></div></div>";



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
	$output = '';
	global $wpdb;
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

	$savedemail=get_adposteremail($adid);
	$transval='';
	$imgstat='';
	$awpcpuperror='';

	if (strcasecmp($editemail, $savedemail) == 0)
	{

		$imagecode="<h2>";
		$imagecode.=__("Manage your ad images","AWPCP");
		$imagecode.="</h2>";

		if (!isset($adid) || empty($adid))
		{
			$imagecode.=__("There has been a problem encountered. The system is unable to continue processing the task in progress. Please start over and if you encounter the problem again, please contact a system administrator.","AWPCP");
		}

		else
		{

			// First make sure images are allowed

			if (get_awpcp_option('imagesallowdisallow') == 1)
			{
				// Next figure out how many images user is allowed to upload

				if ((get_awpcp_option('freepay') == 1) && isset($adtermid) && $adtermid != '0')
				{
					$numimgsallowed=get_numimgsallowed($adtermid);
				}
				elseif ((!get_awpcp_option('freepay')) && (ad_term_id_set($adid)))
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

				if ($totalimagesuploaded >= 1)
				{

					$imagecode.="<p>";
					$imagecode.=__("Your images are displayed below. The total number of images you are allowed is","AWPCP");
					$imagecode.=": $numimgsallowed</p>";

					if (($numimgsallowed - $totalimagesuploaded) == '0')
					{
						$imagecode.="<p>";
						$imagecode.=__("If you want to change your images you will first need to delete the current images","AWPCP");
						$imagecode.="</p>";
					}

					if (get_awpcp_option('imagesapprove') == 1)
					{
						$imagecode.="<p>";
						$imagecode.=__("Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it","AWPCP");
						$imagecode.="</p>";
					}

					// Display the current images

					$imagecode.="<div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>";

					$theimage='';


					$query="SELECT key_id,image_name,disabled FROM ".$tbl_ad_photos." WHERE ad_id='$adid' ORDER BY image_name ASC";
					if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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

						if ($disabled == 1)
						{
							$transval="class=\"imgtransparency\"";
							$imgstat="<font style=\"font-size:smaller;\">";
							$imgstat.=__("Disabled","AWPCP");
							$imgstat.="</font>";
						}

						if (!isset($awpcppagename) || empty($awpcppagename) )
						{
							$awpcppage=get_currentpagename();
							$awpcppagename = sanitize_title($awpcppage, $post_ID='');
						}

						$quers=setup_url_structure($awpcppagename);
						$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
						$editadpageid=awpcp_get_page_id($editadpagename);

						if (isset($permastruc) && !empty($permastruc))
						{
							$url_editpage="$quers/$editadpagename";
							$awpcpquerymark="?";
						}
						else
						{
							$url_editpage="$quers/?page_id=$editadpageid";
							$awpcpquerymark="&";
						}

						$dellink="<a href=\"$url_editpage".$awpcpquerymark."a=dp&k=$ikey\">";
						$dellink.=__("Delete","AWPCP");
						$dellink.="</a>";
						$theimage.="<li><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img $transval src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a><br/>$dellink $imgstat</li>";
					}

					$imagecode.=$theimage;
					$imagecode.="</ul></div></div>";
					$imagecode.="<div class=\"fixfloat\"></div>";
				}

				elseif ($totalimagesuploaded < 1)
				{
					$imagecode.=__("You do not currently have any images uploaded. Use the upload form below to upload your images. If you do not wish to upload any images simply click the finish button. If uploading images, be careful not to click the finish button until after you've uploaded all your images","AWPCP");
				}


				if ($totalimagesuploaded < $numimgsallowed)
				{
					$max_image_size=get_awpcp_option('maximagesize');

					$showimageuploadform=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction='editad',$nextstep='finish',$adpaymethod='',$awpcpuperror);
				}
				else
				{
					$showimageuploadform=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction='editad',$nextstep='finishnoform',$adpaymethod='',$awpcpuperror);

				}

			}

			$imagecode.=$showimageuploadform;
			$imagecode.="<div class=\"fixfloat\"></div>";
		}

		$output .= "<div id=\"classiwrapper\">$imagecode</div>";
	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function deletepic($picid,$adid,$adtermid,$adkey,$editemail)
{
	$output = '';
	$isadmin=checkifisadmin();
	$savedemail=get_adposteremail($adid);

	if ((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
	{
		global $wpdb;
		$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

		$output .= "<div id=\"classiwrapper\">";

		$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE key_id='$picid' AND ad_id='$adid'";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
		$pic=mysql_result($res,0,0);

		$query="DELETE FROM ".$tbl_ad_photos." WHERE key_id='$picid' AND ad_id='$adid' AND image_name='$pic'";
		if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
		if (file_exists(AWPCPUPLOADDIR.'/'.$pic)) {
			@unlink(AWPCPUPLOADDIR.'/'.$pic);
		}
		if (file_exists(AWPCPTHUMBSUPLOADDIR.'/'.$pic)) {
			@unlink(AWPCPTHUMBSUPLOADDIR.'/'.$pic);
		}


		//	$classicontent=$imagecode;
		//	global $classicontent;

		if ($isadmin == 1 && is_admin())
		{
			$message=__("The image has been deleted","AWPCP");
			return $message;
		}

		else {

			$output .= editimages($adtermid,$adid,$adkey,$editemail);
		}

	}
	else
	{
		$output .= __("Unable to delete you image, please contact the administrator.","AWPCP");
	}
	$output .= "</div>";
	return $output;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: delete ad by specified ad ID
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function deletead($adid,$adkey,$editemail)
{
	$output = '';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);

	$isadmin=checkifisadmin();


	if (get_awpcp_option('onlyadmincanplaceads') && ($isadmin != '1'))
	{
		$awpcpreturndeletemessage=__("You do not have permission to perform the function you are trying to perform. Access to this page has been denied","AWPCP");
	}
	else
	{

		global $wpdb,$nameofsite;
		$tbl_ads = $wpdb->prefix . "awpcp_ads";
		$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
		$savedemail=get_adposteremail($adid);
		if ((strcasecmp($editemail, $savedemail) == 0) || ($isadmin == 1 ))
		{
			// Delete ad image data from database and delete images from server

			$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id='$adid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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

			$query="DELETE FROM ".$tbl_ad_photos." WHERE ad_id='$adid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			// Now delete the ad
			$query="DELETE FROM  ".$tbl_ads." WHERE ad_id='$adid'";
			if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			if (($isadmin == 1) && is_admin())
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

	$output .= "<div id=\"classiwrapper\">";
	$output .= awpcp_menu_items();
	$output .= "<p>";
	$output .= $awpcpreturndeletemessage;
	$output .= "</p>";
	$output .= "</div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Process PayPal Payment
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function do_paypal($payment_status,$item_name,$item_number,$receiver_email,$quantity,$mcgross,$payment_gross,$txn_id,$custom,$txn_type)
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$gateway="Paypal";
	$pbizid=get_awpcp_option('paypalemail');

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Configure the data that will be needed for use depending on conditions met
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////////
	// Split the data returned in $custom
	////////////////////////////////////////////////////////////////////////////////////

	$adidkey = $custom;
	$adkeyelements = explode("_", $adidkey);
	$ad_id=$adkeyelements[0];
	$key=$adkeyelements[1];
	$pproc=$adkeyelements[2];


	$ad_id=clean_field($ad_id);
	$key=clean_field($key);

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
	// Bypass amount email dupeid checks if this is a cancellation notification
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$awpcp_ipn_is_cancellation='';
	$awpcp_subscr_cancel="subscr-cancel";
	if (strcasecmp($txn_type, $awpcp_subscr_cancel) == 0)
	{
		// this is a cancellation notification so no need to run validation check on amount transaction id etc
		$awpcp_ipn_is_cancellation=1;
	}
	else
	{

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Make sure the incoming payment amount received matches at least one of the payment ids in the system
		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$myamounts=array();

		$query="SELECT amount FROM ".$tbl_ad_fees."";
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		while ($rsrow=mysql_fetch_row($res))
		{
			$myamounts[]=number_format($rsrow[0],2);
		}


		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// If the incoming payment amount does not match the system amounts
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////


		if (!(in_array(number_format($mcgross,2),$myamounts) || in_array(number_format($payment_gross,2),$myamounts)))
		{
			$message=__("The amount you have paid does not match any of our listing fee amounts. Please contact us to clarify the problem.","AWPCP");
			$awpcpshowadsample=0;
			$awpcppaymentresultmessage=abort_payment($message,$ad_id,$txn_id,$gateway);
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
			$message=__("There was an error processing your transaction. If funds have been deducted from your account they have not been processed to our account. You will need to contact PayPal about the matter.","AWPCP");
			$awpcpshowadsample=0;
			$awpcppaymentresultmessage=abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
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
			$awpcpshowadsample=0;
			$awpcppaymentresultmessage=abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
		}

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// If the transaction ID is not a duplicate proceed with processing the transaction
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Begin updating based on payment status
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if (strcasecmp($payment_status, "Completed") == 0)
	{

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if (get_awpcp_option('adapprove') == 1)
		{
			$disabled='1';
		}
		else
		{
			$disabled='0';
		}

		if ($awpcp_ipn_is_cancellation == 1)
		{
			$query="UPDATE  ".$tbl_ads." SET payment_status='$payment_status' WHERE ad_id='$ad_id' AND ad_key='$key'";
		}
		else
		{
			$query="UPDATE  ".$tbl_ads." SET adterm_id='".clean_field($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled',ad_fee_paid='".clean_field($mcgross)."' WHERE ad_id='$ad_id' AND ad_key='$key'";
		}
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		if (isset($item_number) && !empty($item_number))
		{

			$query="UPDATE ".$tbl_ad_fees." SET buys=buys+1 WHERE adterm_id='".clean_field($item_number)."'";
			if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
		}


		if ($awpcp_ipn_is_cancellation == 1)
		{
			$message=__("Payment status has been changed to cancelled","AWPCP");
			$awpcpshowadsample=0;
			$awpcppaymentresultmessage=ad_paystatus_change_email($ad_id,$txn_id,$key,$message,$gateway);
		}
		else
		{
			$message=__("Payment has been completed","AWPCP");
			$awpcpshowadsample=1;
			$awpcppaymentresultmessage=ad_success_email($ad_id,$txn_id,$key,$message,$gateway);
		}
	}
	elseif (strcasecmp($payment_status, "Refunded") == 0 || strcasecmp($payment_status, "Reversed") == 0 || strcasecmp ($payment_status, "Partially-Refunded") == 0)
	{

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Disable the ad since the payment has been refunded
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		if (get_awpcp_option(freepay) == 1)
		{

			$query="UPDATE  ".$tbl_ads." SET disabled='1',payment_status='$payment_status', WHERE ad_id='$ad_id' AND ad_key='$key'";
			if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			if (isset($item_number) && !empty($item_number))
			{
				$query="UPDATE ".$tbl_ad_fees." SET buys=buys-1 WHERE adterm_id='".clean_field($item_number)."'";
				if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
			}
		}
			
		$message=__("Payment status has been changed to refunded","AWPCP");
		$awpcpshowadsample=0;
		$awpcppaymentresultmessage=ad_paystatus_change_email($ad_id,$txn_id,$key,$message,$gateway);

	}
	elseif (strcasecmp ($payment_status, "Pending") == 0 )
	{

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Set the ad start and end date and save the transaction ID (this will be changed reset upon manual admin approval if ad approval is in effect)
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if (get_awpcp_option('disablependingads') == 0)
		{
			$disabled='1';
		}
		else
		{
			$disabled='0';
		}

		if ($awpcp_ipn_is_cancellation == 1)
		{
			$query="UPDATE  ".$tbl_ads." SET payment_status='$payment_status' WHERE ad_id='$ad_id' AND ad_key='$key'";
		}
		else
		{
			$query="UPDATE  ".$tbl_ads." SET adterm_id='".clean_field($item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$txn_id',payment_status='$payment_status',payment_gateway='Paypal',disabled='$disabled',ad_fee_paid='".clean_field($mcgross)."' WHERE ad_id='$ad_id' AND ad_key='$key'";
		}
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

		if (isset($item_number) && !empty($item_number))
		{

			$query="UPDATE ".$tbl_ad_fees." SET buys=buys+1 WHERE adterm_id='".clean_field($item_number)."'";
			if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
		}


		$message=__("Payment is pending","AWPCP");
		$awpcpshowadsample=1;
		$awpcppaymentresultmessage=ad_success_email($ad_id,$txn_id,$key,$message,$gateway);

	}
	else
	{
		$message=__("There appears to be a problem. Please contact customer service if you are viewing this message after having made a payment. If you have not tried to make a payment and you are viewing this message, it means this message is being shown in error and can be disregarded.","AWPCP");
		$awpcpshowadsample=0;
		$awpcppaymentresultmessage=abort_payment($message,$ad_id,$txn_id,$gateway);

	}
		
	$output .= "<div id=\"classiwrapper\">";
	$output .= awpcp_menu_items();
	$output .= "<p>";
	$output .= $awpcppaymentresultmessage;
	$output .= "</p>";
	if ($awpcpshowadsample == 1)
	{
		$output .= "<h2>";
		$output .= __("Sample of your ad","AWPCP");
		$output .= "</h2>";
		$output .= showad($ad_id,$omitmenu='1');
	}
	$output .= "</div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function do_2checkout($custom,$x_amount,$x_item_number,$x_trans_id,$x_Login)
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$gateway="2checkout";
	$pbizid=get_awpcp_option('2checkout');

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Configure the data that will be needed for use depending on conditions met
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////////
	// Split the data returned in $custom
	////////////////////////////////////////////////////////////////////////////////////

	$adidkey = $custom;
	$adkeyelements = explode("_", $adidkey);
	$ad_id=$adkeyelements[0];
	$key=$adkeyelements[1];
	$pproc=$adkeyelements[2];

		
	$ad_id=clean_field($ad_id);
	$key=clean_field($key);

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

	$query="SELECT amount FROM ".$tbl_ad_fees."";
	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

	while ($rsrow=mysql_fetch_row($res)) {
		$myamounts[]=number_format($rsrow[0],2);
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// If the incoming payment amount does not match the system amounts
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if (!(in_array(number_format($x_amount,2),$myamounts)))
	{
		$message=__("The amount you have paid does not match any of our listing fee amounts. Please contact us to clarify the problem","AWPCP");
		$awpcpshowadsample=0;
		$awpcppaymentresultmessage=abort_payment($message,$ad_id,$x_trans_id,$gateway);
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
		$awpcpshowadsample=0;
		$awpcppaymentresultmessage=abort_payment($message,$ad_id,$x_trans_id,$gateway);
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
		$awpcpshowadsample=0;
		$awpcppaymentresultmessage=abort_payment($message,$ad_id,$x_trans_id,$gateway);
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

	if ( (get_awpcp_option('adapprove') == 1) || (get_awpcp_option('disablependingads') == 0))
	{
		$disabled='1';
	}
	else
	{
		$disabled='0';
	}

	$query="UPDATE  ".$tbl_ads." SET adterm_id='".clean_field($x_item_number)."',ad_startdate=CURDATE(),ad_enddate=CURDATE()+INTERVAL $days DAY,ad_transaction_id='$x_trans_id',payment_status='Completed',payment_gateway='2Checkout',disabled='$disabled',ad_fee_paid='".clean_field($x_amount)."' WHERE ad_id='$ad_id' AND ad_key='$key'";
	if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

	if (isset($item_number) && !empty($item_number))
	{
		$query="UPDATE ".$tbl_ad_fees." SET buys=buys+1 WHERE adterm_id='".clean_field($x_item_number)."'";
		if (!($res=mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
	}


	$message=__("Payment Status","AWPCP");
	$message.=":";
	$message.=__("Completed","AWPCP");
	$awpcpshowadsample=1;
	$awpcppaymentresultmessage=ad_success_email($ad_id,$x_trans_id,$key,$message,$gateway);

	$output .= "<div id=\"classiwrapper\">";
	$output .= awpcp_menu_items();
	$output .= "<p>";
	$output .= $awpcppaymentresultmessage;
	$output .= "</p>";
	if ($awpcpshowadsample == 1)
	{
		$output .= "<h2>";
		$output .= __("Sample of your ad","AWPCP");
		$output .= "</h2>";
		$output .= showad($ad_id,$omitmenu='1');
	}
	$output .= "</div>";
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: email adminstrator and ad poster if there was a problem encountered when paypal payment procedure was attempted
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function abort_payment($message,$ad_id,$transactionid,$gateway)
{
	//email the administrator and the user to notify that the payment process was aborted

	global $nameofsite,$siteurl,$thisadminemail;
	if (isset($adminemailoverride) && !empty($adminemailoverride) && !(strcasecmp($thisadminemail, $adminemailoverride) == 0))
	{
		$thisadminemail=$adminemailoverride;
	}
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option(permalink_structure);
	$quers=setup_url_structure($awpcppagename);
	if (!isset($message) || empty($message)){ $message='';}

	$modtitle=cleanstring($listingtitle);
	$modtitle=add_dashes($modtitle);

	$url_showad=url_showad($ad_id);
	$adlink="$url_showad";

	$adposteremail=get_adposteremail($ad_id);
	$admostername=get_adpostername($ad_id);
	$listingtitle=get_adtitle($ad_id);
	$awpcpabortemailsubjectuser=get_awpcp_option('paymentabortedsubjectline');

	$subjectadmin=__("Customer attempt to pay for classified ad listing has failed","AWPCP");
	$awpcpabortemailbodystart=get_awpcp_option('paymentabortedmessage');
	$awpcpabortemailbodyadditionadets=__("Additional Details","AWPCP");
	$awpcpabortemailbodytransid.=__("Transaction ID","AWPCP");

	$awpcpabortemailbody.="
	$awpcpabortemailbodystart

	$awpcpabortemailbodyadditionadets

	$message

";

	if (isset($transactionid) && !empty($transactionid))
	{

		$awpcpabortemailbody.="$awpcpabortemailbodytransid: $transactionid";
		$awpcpabortemailbody.="

";
	}

	$awpcpabortemailbody.="$nameofsite";
	$awpcpabortemailbody.="
";
	$awpcpabortemailbody.="$siteurl";

	$mailbodyadmindearadmin=__("Dear Administrator","AWPCP");
	$mailbodyadminproblemencountered.=__("There was a problem encountered during a customer's attempt to submit payment for a classified ad listing","AWPCP");

	$mailbodyadmin="
	$mailbodyadmindearadmin

	$mailbodyadminproblemencountered

	$awpcpabortemailbodyadditionadets
";

	$mailbodyadmin.="
";
	$mailbodyadmin.=$message;
	$mailbodyadmin.="
";
	$mailbodyadmin.=__("Listing Title","AWPCP");
	$mailbodyadmin.=": $listingtitle";
	$mailbodyadmin.="
";
	$mailbodyadmin.=__("Listing ID","AWPCP");
	$mailbodyadmin.="$ad_id";
	$mailbodyadmin.="
";
	$mailbodyadmin.=__("Listing URL","AWPCP");
	$mailbodyadmin.=": $adlink";
	$mailbodyadmin.="
";
	if (isset($transactionid) && !empty($transactionid))
	{
		$mailbodyadmin.=__("Payment transaction ID","AWPCP");
		$mailbodyadmin.=": $transactionid";
		$mailbodyadmin.="
	";
	}

	@awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$adposteremail,$awpcpemailsubject=$awpcpabortemailsubjectuser,$awpcpemailbody=$awpcpabortemailbody,$awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail);

	@awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$thisadminemail,$awpcpemailsubject=$subjectadmin, $awpcpemailbody=$mailbodyadmin, $awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail);

	return $message;

}


function abort_payment_no_email($message,$ad_id,$txn_id,$gateway)
{
	return $message;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Send out notifications that listing has been successfully posted
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function ad_paystatus_change_email($ad_id,$transactionid,$key,$message,$gateway)
{

	//email the administrator and the user to notify that the payment process was aborted

	global $nameofsite,$siteurl,$thisadminemail;
	if (isset($adminemailoverride) && !empty($adminemailoverride) && !(strcasecmp($thisadminemail, $adminemailoverride) == 0))
	{
		$thisadminemail=$adminemailoverride;
	}
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option(permalink_structure);
	$quers=setup_url_structure($awpcppagename);
	if (!isset($message) || empty($message)){ $message='';}

	$modtitle=cleanstring($listingtitle);
	$modtitle=add_dashes($modtitle);

	$url_showad=url_showad($ad_id);
	$adlink="$url_showad";

	$adposteremail=get_adposteremail($ad_id);
	$admostername=get_adpostername($ad_id);
	$listingtitle=get_adtitle($ad_id);
	$awpcpabortemailsubjectuser=get_awpcp_option('paymentabortedsubjectline');

	$subjectadmin=__("Listing payment status change notification","AWPCP");
	$awpcpabortemailbodyadditionadets=__("Additional Details","AWPCP");
	$awpcpabortemailbodytransid.=__("Transaction ID","AWPCP");



	$mailbodyadmindearadmin=__("Dear Administrator","AWPCP");
	$mailbodyadminproblemencountered.=__("A listing in the system has been updated with a payment status change","AWPCP");

	$mailbodyadmin="
	$mailbodyadmindearadmin

	$mailbodyadminproblemencountered

	$awpcpabortemailbodyadditionadets
";

	$mailbodyadmin.="
";
	$mailbodyadmin.=$message;
	$mailbodyadmin.="
";
	$mailbodyadmin.=__("Listing Title","AWPCP");
	$mailbodyadmin.=": $listingtitle";
	$mailbodyadmin.="
";
	$mailbodyadmin.=__("Listing ID","AWPCP");
	$mailbodyadmin.="$ad_id";
	$mailbodyadmin.="
";
	$mailbodyadmin.=__("Listing URL","AWPCP");
	$mailbodyadmin.=": $adlink";
	$mailbodyadmin.="
";
	if (isset($transactionid) && !empty($transactionid))
	{
		$mailbodyadmin.=__("Payment transaction ID","AWPCP");
		$mailbodyadmin.=": $transactionid";
		$mailbodyadmin.="
";
	}
	$mailbodyadmin.="
";
	$mailbodyadmin.="
	$nameofsite
	$siteurl
";

	// email admin
	@awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$thisadminemail,$awpcpemailsubject=$subjectadmin, $awpcpemailbody=$mailbodyadmin, $awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail);
	return $message;

}

function ad_success_email($ad_id,$transactionid,$key,$message,$gateway)
{

	global $nameofsite,$siteurl,$thisadminemail;
	if (isset($adminemailoverride) && !empty($adminemailoverride) && !(strcasecmp($thisadminemail, $adminemailoverride) == 0))
	{
		$thisadminemail=$adminemailoverride;
	}

	$adposteremail=get_adposteremail($ad_id);
	$adpostername=get_adpostername($ad_id);
	$listingtitle=get_adtitle($ad_id);
	$listingaddedsubject=get_awpcp_option('listingaddedsubject');
	$mailbodyuser=get_awpcp_option('listingaddedbody');

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option('permalink_structure');
	$quers=setup_url_structure($awpcppagename);

	$modtitle=cleanstring($listingtitle);
	$modtitle=add_dashes($modtitle);

	$url_showad=url_showad($ad_id);
	$adlink=$url_showad;

	$subjectadmin=__("New classified ad listing posted","AWPCP");

	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("Listing Title","AWPCP");
	$mailbodyuser.=": $listingtitle";
	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("Listing URL","AWPCP");
	$mailbodyuser.=": $adlink";
	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("Listing ID","AWPCP");
	$mailbodyuser.=": $ad_id";
	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("Listing Edit Email","AWPCP");
	$mailbodyuser.=": $adposteremail";
	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("Listing Edit Key","AWPCP");
	$mailbodyuser.=": $key";
	$mailbodyuser.="
	
	";
	if (strcasecmp ($gateway, "paypal") == 0 || strcasecmp ($gateway, "2checkout") == 0)
	{
		$mailbodyuser.=__("Payment Transaction ID","AWPCP");
		$mailbodyuser.=": $transactionid";
		$mailbodyuser.="
		
		";
	}
	$mailbodyuseradditionaldets=__("Additional Details","AWPCP");
	$mailbodyuser.="
	$mailbodyuseradditionaldets
	
	$message
	";
	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("If you have questions about your listing contact","AWPCP");
	$mailbodyuser.="
	";
	$mailbodyuser.=": $thisadminemail";
	$mailbodyuser.="
	
	";
	$mailbodyuser.=__("Thank you for your business","AWPCP");
	$mailbodyuser.="
	
	";
	$mailbodyuser.="$siteurl";


	$mailbodyadminstart=__("A new classifieds listing has been submitted. A copy of the details sent to the customer can be found below","AWPCP");
	$mailbodyuser.="
	
	";
	$mailbodyadmin="
	$mailbodyadminstart
	
	$mailbodyuser";

	$mailbodyuser.="
	
	";	

	$messagetouser=__("Your ad has been submitted and an email has been sent to the email address you provided with information you will need to edit your listing.","AWPCP");

	if (get_awpcp_option('adapprove') == 1)
	{
		$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
		$messagetouser.="<p>$awaitingapprovalmsg</p>";
	}


	//email the buyer
	$awpcpdosuccessemail=awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$adposteremail,$awpcpemailsubject=$listingaddedsubject,$awpcpemailbody=$mailbodyuser,$awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail);

	//email the administrator if the admin has this option set
	if (get_awpcp_option('notifyofadposted'))
	{
		@awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$thisadminemail,$awpcpemailsubject=$subjectadmin, $awpcpemailbody=$mailbodyadmin,$awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail);
	}

	if ($awpcpdosuccessemail)
	{
		$printmessagetouser="$messagetouser";
	}
	else
	{
		$printmessagetouser=__("Although your ad has been submitted, there was a problem encountered while attempting to email your ad details to the email address you provided.","AWPCP");
	}

	return $printmessagetouser;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	End process
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: If user decides not to go through with paying for ad via paypal and clicks on cancel on the paypal website
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_cancelpayment()
{
	$output = '';
	$base=get_option('siteurl');
	$permastruc=get_option(permalink_structure);
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$pathvaluecancelpayment=get_awpcp_option('pathvaluecancelpayment');

	$output .= "<div id=\"classiwrapper\">";

	if (isset($_REQUEST['i']) && !empty($_REQUEST['i'])) {
		$adinfo=$_REQUEST['i'];
	}

	$adkeyelements = explode("_", $adinfo);
	$ad_id=$adkeyelements[0];
	$key=$adkeyelements[1];
	$pproc=$adkeyelements[2];


	if (!isset($ad_id) || empty($ad_id))
	{
		if (isset($permastruc) && !empty($permastruc))
		{
			$awpcpcancelpayment_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
			$awpcpcancelpayment_requested_url .= $_SERVER['HTTP_HOST'];
			$awpcpcancelpayment_requested_url .= $_SERVER['REQUEST_URI'];

			$awpcpparsedcancelpaymentURL = parse_url ($awpcpcancelpayment_requested_url);
			$awpcpsplitcancelpaymentPath = preg_split ('/\//', $awpcpparsedcancelpaymentURL['path'], 0, PREG_SPLIT_NO_EMPTY);

			$ad_id_key=$awpcpsplitcancelpaymentPath[$pathvaluecancelpayment];

			$adkeyelements = explode("_", $ad_id_key);
			$ad_id=$adkeyelements[0];
			$key=$adkeyelements[1];
			$pproc=$adkeyelements[2];


		}


		if (!isset($key) || empty($key))
		{
			if (isset($ad_id) && !empty($ad_id))
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


	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$placeadpageid=awpcp_get_page_id($placeadpagename);
	$paymentthankyoupagename=sanitize_title(get_awpcp_option('paymentthankyoupagename'), $post_ID='');
	$paymentthankyoupageid=awpcp_get_page_id($paymentthankyoupagename);
	$paymentcancelpagename=sanitize_title(get_awpcp_option('paymentcancelpagename'), $post_ID='');
	$paymentcancelpageid=awpcp_get_page_id($paymentcancelpagename);


	$custom="$ad_id";
	$custom.="_";
	$custom.="$key";


	$custompp="$custom";
	$custompp.="_PP";
	$custom2ch="$custom";
	$custom2ch.="_2CH";
	$customgch="$custom";
	$customgch.="_GCH";

	$showpaybuttonpaypal=awpcp_displaypaymentbutton_paypal($ad_id,$custompp,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);
	$showpaybutton2checkout=awpcp_displaypaymentbutton_twocheckout($ad_id,$custom2ch,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);

	global $hasgooglecheckoutmodule;
	if ($hasgooglecheckoutmodule == 1)
	{
		$showpaybuttongooglecheckout=awpcp_displaypaymentbutton_googlecheckout($ad_id,$customgch,$adterm_name,$adterm_id,$key,$amount,$recperiod,$permastruc,$quers,$paymentthankyoupageid,$paymentcancelpageid,$paymentthankyoupagename,$paymentcancelpagename,$base);
	}

	$output .= __("You have chosen to cancel the payment process. Your ad cannot be activated until you pay the listing fee. You can click the link below to delete your ad information, or you can click the button to make your payment now","AWPCP");


	$savedemail=get_adposteremail($ad_id);
	$ikey="$ad_id";
	$ikey.="_";
	$ikey.="$key";
	$ikey.="_";
	$ikey.="$savedemail";

	if (isset($permastruc) && !empty($permastruc))
	{
		$url_deletead="$quers/$placeadpagename?a=deletead&k=$ikey";
	}
	else
	{
		$url_deletead="$quers/?page_id=$placeadpageid&a=deletead&k=$ikey";
	}

	$output .= "<p><a href=\"$url_deletead\">";
	$output .= __("Delete Ad Details","AWPCP");
	$output .= "</a></p>";
	if ( get_awpcp_option('activatepaypal') && (get_awpcp_option('freepay') == 1))
	{
		$output .= "<p>";
		$output .= "<h2 class=\"buywith\">";
		$output .= __("Buy With PayPal", "AWPCP");
		$output .= "</h2>";
		$output .= "$showpaybuttonpaypal</p>";
	}
	if ( get_awpcp_option('activate2checkout') && (get_awpcp_option('freepay') == 1))
	{
		$output .= "<p>";
		$output .= "<h2 class=\"buywith\">";
		$output .= __("Buy With 2Checkout", "AWPCP");
		$output .= "</h2>";
		$output .= "$showpaybutton2checkout</p></div>";
	}
	if ( get_awpcp_option('activategooglecheckout') && (get_awpcp_option('freepay') == 1) && ($hasgooglecheckoutmodule == 1))
	{
		$output .= "<p>";
		$output .= "<h2 class=\"buywith\">";
		$output .= __("Buy With Google Checkout", "AWPCP");
		$output .= "</h2>";
		$output .= "$showpaybuttongooglecheckout</p></div>";
	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: Thank you page to display to user after successfully completing payment via paypal
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function paymentthankyou()
{
	$output = '';
	$pathvaluepaymentthankyou=get_awpcp_option('pathvaluepaymentthankyou');
	$permastruc=get_option('permalink_structure');
	if (isset($_REQUEST['i']) && !empty($_REQUEST['i']))
	{
		$adinfo=$_REQUEST['i'];
		$adkeyelements = explode("_", $adinfo);
		$ad_id=$adkeyelements[0];
		$key=$adkeyelements[1];
		$pproc=$adkeyelements[2];

	}

	if (!isset($ad_id) || empty($ad_id))
	{
		if (isset($permastruc) && !empty($permastruc))
		{
			$awpcppaymentthankyou_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
			$awpcppaymentthankyou_requested_url .= $_SERVER['HTTP_HOST'];
			$awpcppaymentthankyou_requested_url .= $_SERVER['REQUEST_URI'];

			$awpcpparsedpaymentthankyouURL = parse_url ($awpcppaymentthankyou_requested_url);
			$awpcpsplitpaymentthankyouPath = preg_split ('/\//', $awpcpparsedpaymentthankyouURL['path'], 0, PREG_SPLIT_NO_EMPTY);

			$ad_id_key=$awpcpsplitpaymentthankyouPath[$pathvaluepaymentthankyou];


			$adkeyelements = explode("_", $ad_id_key);
			$ad_id=$adkeyelements[0];
			if (isset($adkeyelements[1]) && !empty($adkeyelements[1])){$awpcpadkey=$adkeyelements[1];} else {$awpcpadkey='';}
			if (isset($adkeyelements[2]) && !empty($adkeyelements[2])){$pproc=$adkeyelements[2];} else {$pproc='';}
			if (!isset($key) || empty($key)){$key=$awpcpadkey;}

		}
	}

	if ( (isset($_POST['x_response_code']) && !empty($_POST['x_response_code']))  || ( isset($_POST['x_twocorec']) && !empty($_POST['x_twocorec'])) )
	{
		$awpcpayhandler="twocheckout";
	}
	if ( (isset($_POST['custom']) && !empty($_POST['custom']))  && ( isset($_POST['txn_type']) && !empty($_POST['txn_type'])) && ( isset($_POST['txn_id']) && !empty($_POST['txn_id'])) )
	{
		$awpcpayhandler="paypal";
	}

	if ( ($awpcpayhandler != 'paypal') || ($awpcpayhandler != 'twocheckout') )
	{
		if (isset($permastruc) && !empty($permastruc))
		{
			$awpcppaymentthankyou_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
			$awpcppaymentthankyou_requested_url .= $_SERVER['HTTP_HOST'];
			$awpcppaymentthankyou_requested_url .= $_SERVER['REQUEST_URI'];

			$awpcpparsedpaymentthankyouURL = parse_url ($awpcppaymentthankyou_requested_url);
			$awpcpsplitpaymentthankyouPath = preg_split ('/\//', $awpcpparsedpaymentthankyouURL['path'], 0, PREG_SPLIT_NO_EMPTY);

			$ad_id_key=$awpcpsplitpaymentthankyouPath[$pathvaluepaymentthankyou];

			$adkeyelements = explode("_", $ad_id_key);
			$ad_id=$adkeyelements[0];
			if (isset($adkeyelements[1]) && !empty($adkeyelements[1])){$awpcpadkey=$adkeyelements[1];} else {$awpcpadkey='';}
			if (isset($adkeyelements[2]) && !empty($adkeyelements[2])){$pproc=$adkeyelements[2];} else {$pproc='';}
			if (!isset($key) || empty($key)){$key=$awpcpadkey;}

		}
		if (isset($pproc) && !empty($pproc) && ($pproc == 'GCH'))
		{
			$awpcpayhandler="googlecheckout";
		}
		elseif (isset($pproc) && !empty($pproc) && ($pproc == 'PP'))
		{
			$awpcpayhandler="paypal";
		}
		if (isset($pproc) && !empty($pproc) && ($pproc == '2CH'))
		{
			$awpcpayhandler="twocheckout";
		}
	}

	if ($awpcpayhandler == 'paypal')
	{
		//Handle PayPal
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		$payment_verified=false;
		strip_slashes_recursive($_POST);
		foreach ($_POST as $key => $value)
		{
			$value = urlencode($value);
			$req .= "&$key=$value";
		}

		if (get_awpcp_option('paylivetestmode') == 1)
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
		if (isset($_POST['item_name']) && !empty($_POST['item_name'])){$item_name = $_POST['item_name'];} else {$item_name='';};
		if (isset($_POST['item_number']) && !empty($_POST['item_number'])){$item_number = $_POST['item_number'];} else {$item_number='';};
		if (isset($_POST['receiver_email']) && !empty($_POST['receiver_email'])){$receiver_email = $_POST['receiver_email'];} else {$receiver_email='';};
		if (isset($_POST['quantity']) && !empty($_POST['quantity'])){$quantity = $_POST['quantity'];} else {$quantity='';};
		if (isset($_POST['business']) && !empty($_POST['business'])){$business = $_POST['business'];} else {$business='';};
		if (isset($_POST['mc_gross']) && !empty($_POST['mc_gross'])){$mcgross = $_POST['mc_gross'];} else {$mc_gross='';}
		if (isset($_POST['payment_gross']) && !empty($_POST['payment_gross'])){$payment_gross = $_POST['payment_gross'];} else {$payment_gross='';}
		if (isset($_POST['mc_fee']) && !empty($_POST['mc_fee'])){$mc_fee = $_POST['mc_fee'];} else {$tax='';};
		if (isset($_POST['tax']) && !empty($_POST['tax'])){$tax = $_POST['tax'];} else {$payment_currency='';};
		if (isset($_POST['mc_currency']) && !empty($_POST['mc_currency'])){$payment_currency = $_POST['mc_currency'];} else {$payment_currency='';};
		if (isset($_POST['exchange_rate']) && !empty($_POST['exchange_rate'])){$exchange_rate = $_POST['exchange_rate'];} else {$exchange_rate='';};
		if (isset($_POST['payment_status']) && !empty($_POST['payment_status'])){$payment_status = $_POST['payment_status'];} else {$payment_status='';};
		if (isset($_POST['payment_type']) && !empty($_POST['payment_type'])){$payment_type = $_POST['payment_type'];} else {$payment_type='';};
		if (isset($_POST['payment_date']) && !empty($_POST['payment_date'])){$payment_date = $_POST['payment_date'];} else {$payment_date='';};
		if (isset($_POST['txn_id']) && !empty($_POST['txn_id'])){$txn_id = $_POST['txn_id'];} else {$txn_id='';};
		if (isset($_POST['txn_type']) && !empty($_POST['txn_type'])){$txn_type = $_POST['txn_type'];} else {$txn_type='';};
		if (isset($_POST['first_name']) && !empty($_POST['first_name'])){$first_name = $_POST['first_name'];} else {$first_name='';};
		if (isset($_POST['last_name']) && !empty($_POST['last_name'])){$last_name = $_POST['last_name'];} else {$last_name='';};
		if (isset($_POST['payer_email']) && !empty($_POST['payer_email'])){$payer_email = $_POST['payer_email'];} else {$payer_email='';};
		if (isset($_POST['address_street']) && !empty($_POST['address_street'])){$address_street = $_POST['address_street'];} else {$address_street='';};
		if (isset($_POST['address_zip']) && !empty($_POST['address_zip'])){$address_zip = $_POST['address_zip'];} else {$address_zip='';};
		if (isset($_POST['address_city']) && !empty($_POST['address_city'])){$address_city = $_POST['address_city'];} else {$address_city='';};
		if (isset($_POST['address_state']) && !empty($_POST['address_state'])){$address_state = $_POST['address_state'];} else {$address_state='';};
		if (isset($_POST['address_country']) && !empty($_POST['address_country'])){$address_country = $_POST['address_country'];} else {$address_country='';};
		if (isset($_POST['address_country_code']) && !empty($_POST['address_country_code'])){$address_country_code = $_POST['address_country_code'];} else {$address_country_code='';};
		if (isset($_POST['residence_country']) && !empty($_POST['residence_country'])){$residence_country = $_POST['residence_country'];} else {$residence_country='';};
		if (isset($_POST['custom']) && !empty($_POST['custom'])){$custom = $_POST['custom'];} else {$custom='';};

		// Handle the postback and verification
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
		}


		// If payment verified proceed
		if ($payment_verified)
		{
			$output .= do_paypal($payment_status,$item_name,$item_number,$receiver_email,$quantity,$mcgross,$payment_gross,$txn_id,$custom,$txn_type);
		}
		else
		{
			$message=__("There appears to be a problem. Please contact customer service if you are viewing this message after having made a payment via PayPal. If you have not tried to make a payment and you are viewing this message, it means this message is being shown in error and can be disregarded.","AWPCP");
			$output .= abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
		}
	}
	elseif ($awpcpayhandler == 'twocheckout')
	{
		$payment_verified=false;

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

		if ($x_response_code == 1)
		{
			$payment_verified=true;
		}
		elseif (isset($x_twocorec) && !empty($x_twocorec) && ($x_twocorec == 1))
		{
			$payment_verified=true;
		}

		if ($payment_verified)
		{
			$output .= do_2checkout($x_custom,$x_amount,$x_item_number,$x_trans_id,$x_Login);
		}
		else
		{
			$message=__("There appears to be a problem. Please contact customer service if you are viewing this message after having made a payment via 2Checkout. If you have not tried to make a payment and you are viewing this message, it means this message has been sent in error and can be disregarded.","AWPCP");
			$output .= abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
		}

	}
	elseif ($awpcpayhandler == 'googlecheckout')
	{
		//Handle Google Checkout

		$payment_verified=true;
		$output .= do_googlecheckout($ad_id,$key);
	}
	else
	{
		$message=__("There appears to be a problem. Please contact customer service if you are viewing this message after having made a payment. If you have not tried to make a payment and you are viewing this message, it means this message is being shown in error and can be disregarded.","AWPCP");
		$output .= abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: display listing of ad titles when browse ads is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function display_ads($where,$byl,$hidepager,$grouporderby,$adorcat)
{
	$output = '';
	global $wpdb,$awpcp_imagesurl,$hasregionsmodule,$awpcp_plugin_path;
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
	$displayadthumbwidth=get_awpcp_option('displayadthumbwidth');
	$url_browsecats='';

	if ( file_exists("$awpcp_plugin_path/awpcp_display_ads_my_layout.php")  && get_awpcp_option('activatemylayoutdisplayads') )
	{
		include("$awpcp_plugin_path/awpcp_display_ads_my_layout.php");
	}
	else
	{
		$output .= "<div id=\"classiwrapper\">";

		$uiwelcome=get_awpcp_option('uiwelcome');
		$output .= "<div class=\"uiwelcome\">$uiwelcome</div>";


		$isadmin=checkifisadmin();
		$output .= awpcp_menu_items();

		if ($hasregionsmodule ==  1)
		{
			if ( isset($_SESSION['theactiveregionid']) )
			{
				$theactiveregionid=$_SESSION['theactiveregionid'];

				$theactiveregionname=get_theawpcpregionname($theactiveregionid);

				$output .= "<h2>";
				$output .= __("You are currently browsing in ","AWPCP");
				$output .= ": $theactiveregionname</h2><SUP><a href=\"";
				$output .= $quers;
				$output .= "/?a=unsetregion\">";
				$output .= __("Clear session for ","AWPCP");
				$output .= "$theactiveregionname</a></SUP><br/>";
			}
		}

		$tbl_ads = $wpdb->prefix . "awpcp_ads";
		$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

		$from="$tbl_ads";

		if (!isset($where) || empty($where))
		{
			$where="disabled ='0'";
		}
		else
		{
			$where="$where";
		}

		if ($hasregionsmodule == 1)
		{
			if (isset($theactiveregionname) && !empty($theactiveregionname) )
			{
				$where.=" AND (ad_city ='$theactiveregionname' OR ad_state='$theactiveregionname' OR ad_country='$theactiveregionname' OR ad_county_village='$theactiveregionname')";
			}
		}

		if (get_awpcp_option('disablependingads') == 0)
		{
			if (get_awpcp_option('freepay') == 1)
			{
				$where.=" AND payment_status != 'Pending'";
			}
		}

		if (!ads_exist())
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

			if (isset($permastruc) && !empty($permastruc))
			{
				if ($adorcat == 'cat')
				{
					$tpname="$quers/$browsecatspagename";
				}
				else
				{
					$tpname="$quers/$browseadspagename";
				}
			}
			else
			{
				if ($adorcat == 'cat')
				{
					$tpname="?page_id=$awpcp_browsecats_pageid";
				}
				else
				{
					$tpname="?page_id=$browseadspageid";
				}
			}


			$awpcpmyresults=get_awpcp_option('adresultsperpage');
			if (!isset($awpcpmyresults) || empty($awpcpmyresults)){$awpcpmyresults=10;}
			$offset=(isset($_REQUEST['offset'])) ? (clean_field($_REQUEST['offset'])) : ($offset=0);
			$results=(isset($_REQUEST['results']) && !empty($_REQUEST['results'])) ? clean_field($_REQUEST['results']) : ($results=$awpcpmyresults);

			if (!isset($hidepager) || empty($hidepager) )
			{
				$pager1=create_pager($from,$where,$offset,$results,$tpname);
				$pager2=create_pager($from,$where,$offset,$results,$tpname);
			}
			else
			{
				$pager1='';
				$pager2='';
			}

			if (isset($grouporderby) && !empty($grouporderby))
			{
				$grouporder=$grouporderby;
			}
			else
			{
				$grouporder="ORDER BY ad_postdate DESC, ad_title ASC";
			}

			$items=array();
			$query="SELECT ad_id,ad_category_id,ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_details,ad_postdate,ad_enddate,ad_views,ad_fee_paid, IF(ad_fee_paid>0,1,0) as ad_is_paid,ad_item_price FROM $from WHERE $where $grouporder LIMIT $offset,$results";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

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
				$addetailssummary=awpcpLimitText($rsrow[8],10,100,"");
				$awpcpadcity=get_adcityvalue($ad_id);
				$awpcpadstate=get_adstatevalue($ad_id);
				$awpcpadcountry=get_adcountryvalue($ad_id);
				$awpcpadcountyvillage=get_adcountyvillagevalue($ad_id);
				$browsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='');
				$awpcp_browsecats_pageid=awpcp_get_page_id($awpcp_browsecats_pagename=(sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='')));
					

				$url_showad=url_showad($ad_id);

				if ( get_awpcp_option('seofriendlyurls') )
				{

					if (isset($permastruc) && !empty($permastruc))
					{
						$url_browsecats="$quers/$browsecatspagename/$category_id";
					}
					else
					{
						$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&amp;a=browsecat&amp;category_id=$category_id";
					}
				}
				else
				{
					if (isset($permastruc) && !empty($permastruc))
					{
						$url_browsecats="$quers/$browsecatspagename?category_id=$category_id";
					}
					else
					{
						$url_browsecats="$quers/?page_id=$awpcp_browsecats_pageid&amp;a=browsecat&amp;category_id=$category_id";
					}
				}

				$ad_title="<a href=\"$url_showad\">".$rsrow[2]."</a>";
				$categorylink="<a href=\"$url_browsecats\">$category_name</a><br/>";


				$awpcpcity=$rsrow[5];
				$awpcpstate=$rsrow[6];
				$awpcpcountry=$rsrow[7];

				$awpcp_city_display="";
					
				if ( isset($awpcpcity) && !empty($awpcpcity) )
				{
					$awpcp_city_display="$awpcpcity<br/>";
				}
				else
				{
					$awpcp_city_display="";
				}
				if ( isset($awpcpstate) && !empty($awpcpstate) )
				{
					$awpcp_state_display="$awpcpstate<br/>";
				}
				else
				{
					$awpcp_state_display="";
				}
				if ( isset($awpcpcountry) && !empty($awpcpcountry) )
				{
					$awpcp_country_display="$awpcpcountry<br/>";
				}
				else
				{
					$awpcp_country_display='';
				}



				$awpcp_image_display='';
				if (get_awpcp_option('imagesallowdisallow'))
				{

					$awpcp_image_display="<a href=\"$url_showad\">";

					$totalimagesuploaded=get_total_imagesuploaded($ad_id);

					if ($totalimagesuploaded >=1)
					{
						$awpcp_image_name=get_a_random_image($ad_id);
						if (isset($awpcp_image_name) && !empty($awpcp_image_name))
						{
							$awpcp_image_name_srccode="<img src=\"".AWPCPTHUMBSUPLOADURL."/$awpcp_image_name\" border=\"0\" style=\"float:left;margin-right:25px;\" width=\"$displayadthumbwidth\" alt=\"$modtitle\">";
						}
						else
						{
							$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" style=\"float:left;margin-right:25px;\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\">";
						}							}
						else
						{
							$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\">";
						}
				}
				else
				{
					$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\">";
				}

				$awpcp_image_display.="$awpcp_image_name_srccode</a>";

				if ( get_awpcp_option('displayadviews') )
				{
					$awpcp_display_adviews=__("Total views","AWPCP");
					$awpcp_display_adviews.=": $rsrow[11]<br/>";
				} else {$awpcp_display_adviews='';}

				if ( get_awpcp_option('displaypricefield') )
				{
					if (isset($rsrow[14]) && !empty($rsrow[14]))
					{
						$awpcptheprice=$rsrow[14];
						$itempricereconverted=($awpcptheprice/100);
						$itempricereconverted=number_format($itempricereconverted, 2, '.', ',');
						if ($itempricereconverted >=1 )
						{
							$awpcpthecurrencysymbol=awpcp_get_currency_code();
							$awpcp_display_price=__("Price","AWPCP");
							$awpcp_display_price.=": $awpcpthecurrencysymbol $itempricereconverted<br/>";
						}
						else { $awpcp_display_price='';}
					}
					else { $awpcp_display_price='';}

				} else { $awpcp_display_price='';}

				$awpcpadpostdate=date('m/d/Y', strtotime($rsrow[9]))."<br/>";

				$imgblockwidth="$displayadthumbwidth";
				$imgblockwidth.="px";

				$awpcpdisplaylayoutcode=get_awpcp_option('displayadlayoutcode');
				if ( isset($awpcpdisplaylayoutcode) && !empty($awpcpdisplaylayoutcode))
				{
					//$awpcpdisplaylayoutcode=str_replace("\$awpcpdisplayaditems","${awpcpdisplayaditems}",$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$imgblockwidth",$imgblockwidth,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$awpcp_image_name_srccode",$awpcp_image_name_srccode,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$addetailssummary",$addetailssummary,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$ad_title",$ad_title,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$awpcpadpostdate",$awpcpadpostdate,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$awpcp_state_display",$awpcp_state_display,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$awpcp_display_adviews",$awpcp_display_adviews,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$awpcp_city_display",$awpcp_city_display,$awpcpdisplaylayoutcode);
					$awpcpdisplaylayoutcode=str_replace("\$awpcp_display_price",$awpcp_display_price,$awpcpdisplaylayoutcode);

					$items[]="$awpcpdisplaylayoutcode";
				}
				else
				{
					$items[]="
							<div id=\"\$awpcpdisplayaditems\">
							<div style=\"width:$imgblockwidth;padding:5px;float:left;margin-right:20px;\">$awpcp_image_name_srccode</div>
							<div style=\"width:50%;padding:5px;float:left;\"><h4>$ad_title</h4> $addetailssummary...</div>
							<div style=\"padding:5px;float:left;\"> $awpcpadpostdate $awpcp_city_display $awpcp_state_display $awpcp_display_adviews $awpcp_display_price </div>
							<div class=\"fixfloat\"></div>
							</div>	
							<div class=\"fixfloat\"></div>					
							";
				}

				$opentable="";
				$closetable="";

				$theitems=smart_table($items,intval($results/$results),$opentable,$closetable);
				$showcategories="$theitems";
			}
			if (!isset($ad_id) || empty($ad_id) || $ad_id == '0')
			{
				$showcategories="<p style=\"padding:20px;\">";
				$showcategories.=__("There were no ads found","AWPCP");
				$showcategories.="</p>";
				$pager1='';
				$pager2='';
			}
		}

		if (isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
		{
			$show_category_id=$_REQUEST['category_id'];
		}
		else
		{
			$show_category_id='';
		}

		if (!isset($url_browsecatselect) || empty($url_browsecatselect))
		{
			if ( get_awpcp_option('seofriendlyurls') )
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$url_browsecatselect="$quers/$browsecatspagename";
				}
				else
				{
					$url_browsecatselect="$quers/?page_id=$awpcp_browsecats_pageid";
				}
			}
			else
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$url_browsecatselect="$quers/$browsecatspagename";
				}
				else
				{
					$url_browsecatselect="$quers/?page_id=$awpcp_browsecats_pageid";
				}
			}

		}

		if (ads_exist())
		{
			$output .= "<div class=\"fixfloat\"></div><div class=\"pager\">$pager1</div>";
			$output .= "<div class=\"changecategoryselect\"><form method=\"post\" action=\"$url_browsecatselect\"><select name=\"category_id\"><option value=\"-1\">";
			$output .= __("Select Category","AWPCP");
			$output .= "</a>";
			$allcategories=get_categorynameidall($show_category_id='');
			$output .= "$allcategories";
			$output .= "</select><input type=\"hidden\" name=\"a\" value=\"browsecat\" /><input class=\"button\" type=\"submit\" value=\"";
			$output .= __("Change Category","AWPCP");
			$output .= "\" /></form></div><div class=\"fixfloat\"></div>";
		}
		$output .= "$showcategories";
		if (ads_exist())
		{
			$output .= "<div class=\"pager\">$pager2</div>";
		}

		if ($byl)
		{
			if ( field_exists($field='removepoweredbysign') && !(get_awpcp_option('removepoweredbysign')) )
			{
				$output .= "<p><font style=\"font-size:smaller\">";
				$output .= __("Powered by ","AWPCP");
				$output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
			}
			elseif ( field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign')) )
			{

			}
			else
			{
				$output .= "<p><font style=\"font-size:smaller\">";
				$output .= __("Powered by ","AWPCP");
				$output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
			}
		}
		$output .= "</div>";

	}
	return $output;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	START FUNCTION: show the ad when at title is clicked
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function showad($adid,$omitmenu)
{
	$output = '';
	global $wpdb,$awpcp_plugin_path,$hasextrafieldsmodule;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option('permalink_structure');
	$quers=setup_url_structure($awpcppagename);
	$replytoadpagename=sanitize_title(get_awpcp_option('replytoadpagename'), $post_ID='');
	$replytoadpageid=awpcp_get_page_id($replytoadpagename);
	$showadspagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');
	$pathvalueshowad=get_awpcp_option('pathvalueshowad');
	$seoFriendlyUrls = get_awpcp_option('seofriendlyurls');
	
	if (!isset($adid) || empty($adid))
	{
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
		{
			$adid=$_REQUEST['adid'];
		}
		elseif (isset($_REQUEST['id']) && !empty($_REQUEST['id']))
		{
			$adid=$_REQUEST['id'];
		}
		else
		{
			if ( $seoFriendlyUrls )
			{
				if (isset($permastruc) && !empty($permastruc))
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

	if (isset($adid) && !empty($adid))
	{
		if ( file_exists("$awpcp_plugin_path/awpcp_showad_my_layout.php") && get_awpcp_option('activatemylayoutshowad') )
		{
			include("$awpcp_plugin_path/awpcp_showad_my_layout.php");
		}
		else
		{
			$output .= "<div id=\"classiwrapper\">";

			$isadmin=checkifisadmin();

			if (!$omitmenu)
			{
				$output .= awpcp_menu_items();
			}

			if (isset($awpcpadpostedmsg) && !empty($awpcpadpostedmsg))
			{
				$output .= "$awpcpadpostedmsg";
			}

			//update the ad views
			$query="UPDATE ".$tbl_ads." SET ad_views=(ad_views + 1) WHERE ad_id='$adid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}


			if (get_awpcp_option('useadsense') == 1)
			{
				$adsensecode=get_awpcp_option('adsense');
				$showadsense="<div class=\"cl-adsense\">$adsensecode</div>";
			}
			else
			{
				$showadsense='';
			}

			$query="SELECT ad_title,ad_contact_name,ad_contact_phone,ad_city,ad_state,ad_country,ad_county_village,ad_item_price,ad_details,websiteurl from ".$tbl_ads." WHERE ad_id='$adid'";
			if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
				list($ad_title,$adcontact_name,$adcontact_phone,$adcontact_city,$adcontact_state,$adcontact_country,$ad_county_village,$ad_item_price,$addetails,$websiteurl)=$rsrow;
			}

			////////////////////////////////////////////////////////////////////////////////////
			// Step:2 Show a sample of how the ad is going to look
			////////////////////////////////////////////////////////////////////////////////////

			$ad_title=stripslashes($ad_title);
			$addetails=stripslashes($addetails);

			if (!isset($adcontact_name) || empty($adcontact_name)){$adcontact_name="";}
			if (!isset($adcontact_phone) || empty($adcontact_phone))
			{
				$adcontactphone="";
			}
			else
			{
				$adcontactphone="<br/>";
				$adcontactphone.=__("Phone","AWPCP");
				$adcontactphone.=": $adcontact_phone";
			}

			if ( empty($adcontact_city) && empty($adcontact_state) && empty($adcontact_country) && empty($ad_county_village))
			{
				$location="";
			}
			else
			{
				$location="<br/>";
				$location.=__("Location ","AWPCP");
				$location.=": ";
				$first = true;
				if ( isset($adcontact_city) && !empty($adcontact_city) )
				{
					//Add city
					$location.=$adcontact_city;
					$first = false;
				}
				if ( isset($ad_county_village) && !empty($ad_county_village) )
				{
					//Add county/village
					if (!$first) {
						$location.=", ";
					}
					$location.=$ad_county_village;
					$first = false;
				}
				if ( isset($adcontact_state) && !empty($adcontact_state) )
				{
					//Add state
					if (!$first) {
						$location.=", ";
					}
					$location.=$adcontact_state;
					$first = false;
				}
				if ( isset($adcontact_country) && !empty($adcontact_country) )
				{
					//Add country
					if (!$first) {
						$location.=", ";
					}
					$location.=$adcontact_country;
					$first = false;
				}
			}

			$modtitle=cleanstring($ad_title);
			$modtitle=add_dashes($modtitle);

			if ( $seoFriendlyUrls )
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$codecontact="$replytoadpagename/$adid/$modtitle";
				}
				else
				{
					$codecontact="?page_id=$replytoadpageid&i=$adid";
				}
			}
			else
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$codecontact="$replytoadpagename/?i=$adid";
				}
				else
				{
					$codecontact="?page_id=$replytoadpageid&i=$adid";
				}
			}

			$aditemprice='';

			if ( get_awpcp_option('displaypricefield') == 1)
			{
				if ( !empty($ad_item_price) )
				{
					$itempricereconverted=($ad_item_price/100);
					$itempricereconverted=number_format($itempricereconverted, 2, '.', ',');
					if ($itempricereconverted >=1 )
					{
						$awpcpthecurrencysymbol=awpcp_get_currency_code();
						$aditemprice="<div class=\"adinfo\"><label>";
						$aditemprice.=__("Price","AWPCP");
						$aditemprice.="</label><br/>";
						$aditemprice.="<b class=\"price\">$awpcpthecurrencysymbol $itempricereconverted</b></div>";
					}
				}
			}

			$awpcpadviews='';
			if ( get_awpcp_option('displayadviews') )
			{
				$awpcpadviews_total=get_numtimesadviewd($adid);
				$awpcpadviews="<div class=\"adviewed\">";
				$awpcpadviews.=__("This ad has been viewed ","AWPCP");
				$awpcpadviews.="$awpcpadviews_total";
				$awpcpadviews.=__(" times","AWPCP");
				$awpcpadviews.="</div>";
			}
			if (get_awpcp_option('visitwebsitelinknofollow'))
			{
				$awpcprelnofollow="rel=\"nofollow\" ";
			}
			else
			{
				$awpcprelnofollow="";
			}
			$awpcpvisitwebsite='';
			if (isset($websiteurl) && !empty($websiteurl))
			{
				$awpcpvisitwebsite="<br/><a $awpcprelnofollow href=\"$websiteurl\">";
				$awpcpvisitwebsite.=__("Visit Website","AWPCP");
				$awpcpvisitwebsite.="</a>";
			} 
			$featureimg='';
			$allowImages = get_awpcp_option('imagesallowdisallow');
			if ($allowImages == 1)
			{
				$totalimagesuploaded=get_total_imagesuploaded($adid);

				if ($totalimagesuploaded >=1)
				{
					$mainpic=get_a_random_image($adid);
					if (isset($mainpic) && !empty($mainpic)){
						$featureimg="<div style=\"float:right;\"><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$mainpic\"><img class=\"thumbshow\" src=\"".AWPCPTHUMBSUPLOADURL."/$mainpic\"></a></div>";
					}
				}
				$theimage='';
				$awpcpshowadotherimages='';
				$totalimagesuploaded=get_total_imagesuploaded($adid);

				if ($totalimagesuploaded >=1)
				{

					$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id='$adid' AND disabled='0' AND image_name !='$mainpic' ORDER BY image_name ASC";
					if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}

					while ($rsrow=mysql_fetch_row($res))
					{
						list($image_name)=$rsrow;
						$awpcpshowadotherimages.="<li><a class=\"thickbox\" href=\"".AWPCPUPLOADURL."/$image_name\"><img class=\"thumbshow\"  src=\"".AWPCPTHUMBSUPLOADURL."/$image_name\"></a></li>";

					}
				}

			}
			$adsensePosition = get_awpcp_option('adsenseposition');
			$showadsense1="";
			$showadsense2="";
			$showadsense3="";
			if ($adsensePosition == 1)
			{
				$showadsense1="$showadsense";
			} 
			else if ($adsensePosition == 2)
			{
				$showadsense2="$showadsense";
			} 
			else if ($adsensePosition == 3)
			{
				$showadsense3="$showadsense";
			}
			$awpcpextrafields='';
			if ($hasextrafieldsmodule == 1)
			{
				$awpcpextrafields=display_x_fields_data($adid);
			} 
			if (get_awpcp_option('hyperlinkurlsinadtext')){
				$addetails=preg_replace("/(http:\/\/[^\s]+)/","<a $awpcprelnofollow href=\"\$1\">\$1</a>",$addetails);
			}

			$addetails=preg_replace("/(\r\n)+|(\n|\r)+/", "<br /><br />", $addetails);


			$awpcpshowtheadlayout=get_awpcp_option('awpcpshowtheadlayout');
			if (isset($awpcpshowtheadlayout) && !empty($awpcpshowtheadlayout))
			{
				$awpcpshowtheadlayout=str_replace("\$ad_title","$ad_title",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$featureimg","$featureimg",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$quers","$quers",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$codecontact","$codecontact",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$adcontact_name","$adcontact_name",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$adcontactphone","$adcontactphone",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$location","$location",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$aditemprice","$aditemprice",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$awpcpextrafields","$awpcpextrafields",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$awpcpvisitwebsite","$awpcpvisitwebsite",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$showadsense1","$showadsense1",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$addetails","$addetails",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$showadsense2","$showadsense2",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$awpcpshowadotherimages","$awpcpshowadotherimages",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$awpcpadviews","$awpcpadviews",$awpcpshowtheadlayout);
				$awpcpshowtheadlayout=str_replace("\$showadsense3","$showadsense3",$awpcpshowtheadlayout);

				$awpcpshowthead=$awpcpshowtheadlayout;
			}
			else
			{
				$awpcpshowthead="
									<div id=\"showad\">
									<div class=\"adtitle\">$ad_title</div><br/>
									<div class=\"adinfo\">
									$featureimg
									<label>";
									$awpcpshowthead.=__("Contact Information","AWPCP");
									$awpcpshowthead.="</label><br/>
									<a href=\"$quers/$codecontact\">";
									$awpcpshowthead.=__("Contact","AWPCP");
									$awpcpshowthead.="$adcontact_name</a>
									$adcontactphone
									$location
									$awpcpvisitwebsite
									</div>
									$aditemprice
									$awpcpextrafields
									<div class=\"fixfloat\"></div>
									$showadsense1
									<div class=\"adinfo\"><label>";
									$awpcpshowthead.=__("More Information","AWPCP");
									$awpcpshowthead.="</label><br/>$addetails</div>
									$showadsense2
									<div class=\"fixfloat\"></div>
									<div id=\"displayimagethumbswrapper\">
									<div id=\"displayimagethumbs\"><ul>$awpcpshowadotherimages</ul></div>
									</div>
									<div class=\"fixfloat\"></div>
									$awpcpadviews
									$showadsense3
									</div>
									";
			}
			$output .= $awpcpshowthead;
			$output .= "</div><!--close classiwrapper-->";
		}
	}
	else
	{
		$grouporderby=get_group_orderby();
		$output .= display_ads($where='',$byl='',$hidepager='',$grouporderby,$adocat='');
	}
	return $output;
}

function awpcp_append_title($title)
{
	$awpcpiscat='';
	$permastruc=get_option('permalink_structure');
	$awpcpshowadpagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');
	$awpcpbrowsecatspagename=sanitize_title(get_awpcp_option('browsecatspagename'), $post_ID='');
	$awpcptitleseparator=get_awpcp_option('awpcptitleseparator');
	if (!isset($awpcptitleseparator) || empty($awpcptitleseparator))
	{
		$awpcptitleseparator="|";
	}

	$pathvalueshowad=get_awpcp_option('pathvalueshowad');
	$pathvaluebrowsecats=get_awpcp_option('pathvaluebrowsecats');

	wp_reset_query();

	if (is_page($awpcpshowadpagename) || is_page($awpcpbrowsecatspagename))
	{
		if (isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id']))
		{
			$category_id=$_REQUEST['category_id'];
		}

		if (!isset($adid) || empty($adid))
		{
			if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid']))
			{
				$adid=$_REQUEST['adid'];
			}
			elseif (isset($_REQUEST['id']) && !empty($_REQUEST['id']))
			{
				$adid=$_REQUEST['id'];
			}
			else
			{
				if (isset($permastruc) && !empty($permastruc))
				{
					$awpcpshowad_requested_url  = ( !empty($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
					$awpcpshowad_requested_url .= $_SERVER['HTTP_HOST'];
					$awpcpshowad_requested_url .= $_SERVER['REQUEST_URI'];

					$awpcpparsedshowadURL = parse_url ($awpcpshowad_requested_url);
					$awpcpsplitshowadPath = preg_split ('/\//', $awpcpparsedshowadURL['path'], 0, PREG_SPLIT_NO_EMPTY);

					foreach ($awpcpsplitshowadPath as $awpcpsplitshowadPathitem)
					{
						if ( $awpcpsplitshowadPathitem == $awpcpbrowsecatspagename )
						{
							$awpcpiscat=1;
							$adcategoryid=$awpcpsplitshowadPath[$pathvaluebrowsecats];
						}
					}

					$adid=$awpcpsplitshowadPath[$pathvalueshowad];
				}
			}
		}
		if ( $awpcpiscat == 1 )
		{
			$awpcp_ad_cat_title=get_adcatname($adcategoryid);

			$title.=" $awpcptitleseparator $awpcp_ad_cat_title";
		}
		elseif ( isset($category_id) && !empty($category_id) )
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

			if ( get_awpcp_option('showcityinpagetitle') && !empty($awpcpadcity) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adcityvalue($adid);
			}
			if ( get_awpcp_option('showstateinpagetitle') && !empty($awpcpadstate) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adstatevalue($adid);
			}
			if ( get_awpcp_option('showcountryinpagetitle') && !empty($awpcpadcountry) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adcountryvalue($adid);
			}
			if ( get_awpcp_option('showcountyvillageinpagetitle') && !empty($awpcpadcountyvillage) )
			{
				$awpcp_ad_title.=" $awpcptitleseparator ";
				$awpcp_ad_title.=get_adcountyvillagevalue($adid);
			}
			if ( get_awpcp_option('showcategoryinpagetitle') )
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
	$output = '';
	global $message;

	if ( isset($_REQUEST['action']) && !empty($_REQUEST['action']) )
	{
		if ($_REQUEST['action'] == 'douninstall')
		{
			douninstall();
		}
	}

	if ( !isset($_REQUEST['action']) || empty($_REQUEST['action']) )
	{
		$dirname=AWPCPUPLOADDIR;

		$output .= "<div class=\"wrap\"><h2>";
		$output .= __("AWPCP Classifieds Management System Uninstall Plugin","AWPCP");
		$output .= "</h2>";
		if (isset($message) && !empty($message))
		{
			$output .= $message;
		}
		$output .= "<div style=\"padding:20px;\">";
		$output .= __("Thank you for using AWPCP. You have arrived at this page by clicking the Uninstall link. If you are certain you wish to uninstall the plugin, please click the link below to proceed. Please note that all your data related to the plugin, your ads, images and everything else created by the plugin will be destroyed","AWPCP");
		$output .= "<p><b>";
		$output .= __("Important Information","AWPCP");
		$output .= "</b></p>";
		$output .= "<blockquote><p>1.";
		$output .= __("If you plan to use the data created by the plugin please export the data from your mysql database before clicking the uninstall link","AWPCP");
		$output .= "</p>";
		$output .= "<p>2.";
		$output .= __("If you want to keep your user uploaded images, please download $dirname to your local drive for later use or rename the folder to something else so the uninstaller can bypass it","AWPCP");
		$output .= "</p>";
		$output .= "</blockquote>:";
		$output .= "<a href=\"?page=Manage3&action=douninstall\">";
		$output .= __("Proceed with Uninstalling Another Wordpress Classifieds Plugin","AWPCP");
		$output .= "</a></div><div class=\"fixfloat\"></div>";
	}
	//Echo OK here:
	echo $output;
}

function douninstall()
{
	$output = '';
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
	$tbl_ad_categories = $wpdb->prefix . "awpcp_categories";
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$tbl_pagename = $wpdb->prefix . "awpcp_pagename";
	$tbl_regions = $wpdb->prefix . "awpcp_regions";

	$wpdb->query("DROP TABLE " . $tbl_ad_categories);
	$wpdb->query("DROP TABLE " . $tbl_ad_fees);
	$wpdb->query("DROP TABLE " . $tbl_ads);
	$wpdb->query("DROP TABLE " . $tbl_ad_settings);
	$wpdb->query("DROP TABLE " . $tbl_ad_photos);
	$wpdb->query("DROP TABLE " . $tbl_pagename);

	$tblRegionsExists=checkfortable($tbl_regions);

	if ($tblRegionsExists)
	{
		$wpdb->query("DROP TABLE " . $tbl_regions);
	}
	// Remove the version number from the options table
	$query="DELETE FROM {$table_prefix}options WHERE option_name='awpcp_db_version'";
	@mysql_query($query);

	//Remove widget entries from options table
	$query="DELETE FROM {$table_prefix}options WHERE option_name='widget_awpcplatestads'";
	@mysql_query($query);

	unregister_sidebar_widget('AWPCP Latest Ads', 'widget_awpcplatestads');
	unregister_widget_control('AWPCP Latest Ads', 'widget_awpcplatestads_options', 350, 120);

	// Clear the ad expiration schedule
	wp_clear_scheduled_hook('doadexpirations_hook');

	$thepluginfile="another-wordpress-classifieds-plugin/awpcp.php";
	$current = get_option('active_plugins');
	array_splice($current, array_search( $thepluginfile, $current), 1 );
	update_option('active_plugins', $current);
	do_action('deactivate_' . $thepluginfile );
	$output .= "<div style=\"padding:50px;font-weight:bold;\"><p>";
	$output .= __("Almost done...","AWPCP");
	$output .= "</p><h1>";
	$output .= __("One More Step","AWPCP");
	$output .= "</h1><a href=\"plugins.php?deactivate=true\">";
	$output .= __("Please click here to complete the uninstallation process","AWPCP");
	$output .= "</a></h1></div>";
	//Echo ok here:
	echo $output;
	die;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>