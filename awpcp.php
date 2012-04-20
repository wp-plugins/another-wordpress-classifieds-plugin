<?php
/*
 Plugin Name: Another Wordpress Classifieds Plugin (AWPCP)
 Plugin URI: http://www.awpcp.com
 Description: AWPCP - A plugin that provides the ability to run a free or paid classified ads service on your wordpress blog. <strong>!!!IMPORTANT!!!</strong> Whether updating a previous installation of Another Wordpress Classifieds Plugin or installing Another Wordpress Classifieds Plugin for the first time, please backup your wordpress database before you install/uninstall/activate/deactivate/upgrade Another Wordpress Classifieds Plugin.
 Version: 2.0.4
 Author: D. Rodenbaugh
 License: GPLv2 or any later version
 Author URI: http://www.skylineconsult.com
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * dcfunctions.php and filop.class.php used with permission of Dan Caragea, http://datemill.com
 * AWPCP Classifieds icon set courtesy of http://www.famfamfam.com/lab/icons/silk/
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
} 
// Conditionally start session if not already active
if(!isset($_SESSION)) {
	@session_start();
}


// // I don't think this are needed, should always be defined.. don't they?
// if (!defined('WP_CONTENT_DIR')) {
// 	// no trailing slash, full paths only - WP_CONTENT_URL is defined further down
// 	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); 
// }
// if (!defined('WP_CONTENT_URL')) {
// 	// no trailing slash, full paths only - WP_CONTENT_URL is defined further down
// 	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); 
// }

define('AWPCP_BASENAME', str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));
define('AWPCP_DIR', WP_CONTENT_DIR. '/plugins/' . AWPCP_BASENAME);
define('AWPCP_URL', WP_CONTENT_URL. '/plugins/' . AWPCP_BASENAME);


// TODO: Why do we need a custom error handler?
// Set custom error handler functions
function AWPCPErrorHandler($errno, $errstr, $errfile, $errline){
	$output = '';
	switch ($errno) {
		case E_USER_ERROR:
			if ($errstr == "(SQL)"){
				// handling an sql error
				$output .= "<b>AWPCP SQL Error</b> Errno: [$errno] SQLError:" . SQLMESSAGE . "<br />\n";
				$output .= "Query : " . SQLQUERY . "<br />\n";
				$output .= "Called by line " . SQLERRORLINE . " in file " . SQLERRORFILE . ", error in ".$errfile." at line ".$errline;
				$output .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				$output .= "Aborting...<br />\n";
			} else {
				$output .= "<b>AWPCP PHP Error</b> [$errno] $errstr<br />\n";
				$output .= "  Fatal error called by line $errline in file $errfile, error in ".$errfile." at line ".$errline;
				$output .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				$output .= "Aborting...<br />\n";
			}
			//Echo OK here:
			echo $output;
			exit(1);
			break;

		case E_USER_WARNING:
		case E_USER_NOTICE:
	}
	/* true=Don't execute PHP internal error handler */
	return true;
}

if (file_exists(AWPCP_DIR . 'DEBUG')) {
	// let's see some errors
} else {
	set_error_handler("AWPCPErrorHandler");
}


global $wpdb; // XXX: do we need $wpdb this here? --@wvega
global $awpcp_plugin_data;
global $awpcp_db_version;

global $pcontenturl;
global $wpcontentdir;
global $awpcp_plugin_path;
global $awpcp_plugin_url;
global $wpinc;
global $imagespath;
global $awpcp_imagesurl;

global $nameofsite;
global $siteurl;
global $thisadminemail;


require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
// get_plugin_data accounts for about 2% of the cost of 
// each request, defining the version manually is a less
// expensive way.
$awpcp_plugin_data = get_plugin_data(__FILE__);
$awpcp_db_version = $awpcp_plugin_data['Version'];

$wpcontenturl = WP_CONTENT_URL;
$wpcontentdir = WP_CONTENT_DIR;
$awpcp_plugin_path = AWPCP_DIR;
$awpcp_plugin_url = AWPCP_URL;
$wpinc = WPINC;
$imagespath = $awpcp_plugin_path . 'images';
$awpcp_imagesurl = $awpcp_plugin_url .'images';

$nameofsite = get_option('blogname');
$siteurl = get_option('siteurl');
$thisadminemail = get_option('admin_email');



// Common
require_once(AWPCP_DIR . "debug.php");
require_once(AWPCP_DIR . "functions.php");
require_once(AWPCP_DIR . "cron.php");

// API & Classes
require_once(AWPCP_DIR . "classes/models/ad.php");
require_once(AWPCP_DIR . "classes/models/payment-transaction.php");
require_once(AWPCP_DIR . "classes/helpers/list-table.php");
require_once(AWPCP_DIR . "classes/settings-api.php");
require_once(AWPCP_DIR . "widget-latest-ads.php");

// installation functions
require_once(AWPCP_DIR . "install.php");

// admin functions
require_once(AWPCP_DIR . "admin/admin-panel.php");
require_once(AWPCP_DIR . "admin/user-panel.php");

// frontend functions
require_once(AWPCP_DIR . "frontend/payment-functions.php");
require_once(AWPCP_DIR . "frontend/ad-functions.php");
require_once(AWPCP_DIR . "frontend/shortcode.php");

// other resources
require_once(AWPCP_DIR . "dcfunctions.php");
require_once(AWPCP_DIR . "awpcp_search_widget.php");
require_once(AWPCP_DIR . "functions_awpcp.php");
require_once(AWPCP_DIR . "upload_awpcp.php");

// modules (in development)



class AWPCP {
	
	// Admin section
	public $admin = null;
	// User Ad Management panel
	public $panel = null;
	// Frontend pages
	public $pages = null;
	// Settings API -- not the one from WP
	public $settings = null;

	// TODO: I want to register all plugin scripts here, enqueue on demand in each page.
	// is that a good idea? -@wvega

	public function AWPCP() {
		// we need to instatiate this here, because some options are
		// consulted before plugins_loaded...
		$this->settings = new AWPCP_Settings_API();
		$this->installer = AWPCP_Installer::instance();

        register_activation_hook(__FILE__, array($this->installer, 'install'));
        add_action('plugins_loaded', array($this, 'setup'), 10);
	}

	/**
	 * Check if AWPCP DB version corresponds to current AWPCP plugin version
	 */
	public function updated() {
		global $awpcp_db_version;
		$installed = get_option('awpcp_db_version', '');
		// if installed version is greater than plugin version
		// not sure what to do. Downgrade is not currently supported.
		return version_compare($installed, $awpcp_db_version) === 0;
	}

	/**
	 * Single entry point for AWPCP plugin.
	 * 
	 * This is functional but still a work in progress...
	 */
	public function setup() {
		if (!$this->updated()) {
			$this->installer->install();
		}

		if ($this->updated()) {
			$this->settings->setup();
			$this->admin = new AWPCP_Admin();
			$this->panel = new AWPCP_User_Panel();
			$this->pages = new AWPCP_Pages();
			
			add_action('init', array($this, 'register_scripts'));
			add_action('init', array($this, 'create_pages'));

			// actions and filters from functions_awpcp.php
			add_action('widgets_init', 'widget_awpcp_search_init');
			add_action('phpmailer_init','awpcp_phpmailer_init_smtp');
			add_filter('awpcp_single_ad_layout', 'awpcp_insert_tweet_button', 1, 3);
			add_filter('awpcp_single_ad_layout', 'awpcp_insert_share_button', 2, 3);

			// actions and filters from awpcp.php
			add_action('wp_print_scripts', 'awpcpjs',1);
			add_action('wp_head', 'awpcp_addcss');

			if (!get_awpcp_option('awpcp_thickbox_disabled')) {
				add_action('wp_head', 'awpcp_insert_thickbox', 10);
			}

			add_action('wp', 'awpcp_schedule_activation');
			add_filter('cron_schedules', 'awpcp_cron_schedules');

			add_action("init", "init_awpcpsbarwidget");
			add_action('init', 'maybe_redirect_new_ad', 1);

			if (get_awpcp_option('awpcppagefilterswitch') == 1) {
				add_filter('wp_list_pages_excludes', 'exclude_awpcp_child_pages');
			}

			add_action('wp_loaded', 'awpcp_rules');
			add_action('generate_rewrite_rules', 'awpcp_rewrite_rules', 1, 1);
			add_filter('query_vars', 'awpcp_query_vars');
			add_action('wp_head', 'awpcp_insert_facebook_meta');

			remove_action('wp_head', 'rel_canonical');
			add_action('wp_head', 'awpcp_rel_canonical');
		}
	}

	public function register_scripts() {
		// had to use false as the version number because otherwise the resulting URLs would 
		// throw 404 errors. Not sure why :S -@wvega

		wp_register_script('awpcp-page-place-ad',
					AWPCP_URL . 'js/page-place-ad.js', array('jquery'), false, true);

		wp_register_style('awpcp-jquery-ui-datepicker',
					AWPCP_URL . 'js/datepicker/cupertino/jquery-ui-1.8.16.custom.css',
					array(), false);
		wp_register_script('awpcp-jquery-ui-datepicker',
					AWPCP_URL . 'js/datepicker/jquery-ui-1.8.16.datepicker.min.js',
					array('jquery'), false, true);
	}

	/**
	 * Create pages after the plugin has been activated
	 */
	public function create_pages() {		
		$installation_complete = get_option('awpcp_installationcomplete', 0);
		$version = get_option('awpcp_db_version');

		if (!$installation_complete) {
			update_option('awpcp_installationcomplete', 1);
			awpcp_create_pages(__('AWPCP', 'AWPCP'));
			
		} else if (version_compare($version, '1.8.9.4.54') > 0) {
			// global $wpdb;
			// $query = 'SELECT pages.page, pages.id, posts.ID post ';
			// $query.= 'FROM ' . AWPCP_TABLE_PAGES . ' AS pages ';
			// $query.= 'LEFT JOIN ' . $wpdb->posts . ' AS posts ON (posts.ID = pages.id) ';
			// $query.= 'WHERE posts.ID IS NULL';

			// $orphan = $wpdb->get_results($wpdb->prepare($query));
			// $excluded = array('view-categories-page-name');

			// // if a page is registered in the code but there is no reference
			// // of it in the database, create it.
			// $shortcodes = awpcp_pages();
			// $refnames = $wpdb->get_col('SELECT page FROM ' . AWPCP_TABLE_PAGES);
			// $missing = array_diff(array_keys($shortcodes), $refnames);

			// foreach ($missing as $page) {
			// 	$item = new stdClass();
			// 	$item->page = $page;
			// 	$item->id = -1;
			// 	$item->post = null;
			// 	array_push($orphan, $item);
			// }

			// foreach($orphan as $page) {
			// 	$refname = $page->page;

			// 	if (in_array($refname, $excluded)) { continue; }

			// 	$name = get_awpcp_option($refname);
			// 	if (strcmp($refname, 'main-page-name') == 0) {
			// 		awpcp_create_pages($name, $subpages=false);
			// 	} else {
			// 		awpcp_create_subpage($refname, $name, $shortcodes[$refname][1]);
			// 	}
			// }
		}
	}
}

global $awpcp;
$awpcp = new AWPCP();



$plugin_dir = basename(dirname(__FILE__));
if (get_awpcp_option('activatelanguages')) {
	load_plugin_textdomain( 'AWPCP', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
}


$uploadfoldername = get_awpcp_option('uploadfoldername', "uploads");

define('MAINUPLOADURL', $wpcontenturl .'/' .$uploadfoldername);
define('MAINUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername);
define('AWPCPUPLOADURL', $wpcontenturl .'/' .$uploadfoldername .'/awpcp');
define('AWPCPUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername .'/awpcp/');
define('AWPCPTHUMBSUPLOADURL', $wpcontenturl .'/' .$uploadfoldername .'/awpcp/thumbs');
define('AWPCPTHUMBSUPLOADDIR', $wpcontentdir .'/' .$uploadfoldername .'/awpcp/thumbs/');
define('MENUICO', $awpcp_imagesurl .'/menuico.png');

$awpcpthumbsurl=AWPCPTHUMBSUPLOADURL;
$hascaticonsmodule = 0;
$hasregionsmodule = 0;
$haspoweredbyremovalmodule = 0;
$hasgooglecheckoutmodule = 0;
$hasextrafieldsmodule = 0;
$hasrssmodule = 0;
$hasfeaturedadsmodule = 0;



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
	require("$awpcp_plugin_path/awpcp_rss_module.php");
	$hasrssmodule=1;
}
if (file_exists(WP_CONTENT_DIR . "/plugins/awpcp-featured-ads/awpcp_featured_ads.php")) 
{
	$hasfeaturedadsmodule=1;
}

// Add css file and jquery codes to header
function awpcpjs() {
	global $awpcp_plugin_url;
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-form');

	if (!get_awpcp_option('awpcp_thickbox_disabled')) {
		wp_enqueue_script('thickbox');
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


/**
 * Redirect to the ad page when a new ad is posted. This prevents posting duplicates when someone clicks reload.
 * This also allows admins to post without going through the checkout process.
 *
 * This is no longer used after the Place Ad workflow changes.
 */
function maybe_redirect_new_ad() { 
	global $wp_query;

	$a = awpcp_post_param('a', '');
	$adid = awpcp_post_param('adid', '');

    if (( isset($wp_query->query_vars) && 'adpostfinish' == get_query_var('a') && '' != get_query_var('adid') ) ||
	 	( 'adpostfinish' == $a && '' != $adid))
    {
		// if ( get_awpcp_option('seofriendlyurls') ) {
		// 	wp_redirect( url_showad( intval( $_POST['adid'] ) ).'?adstatus=preview');
		// } else {
		// 	wp_redirect( url_showad( intval( $_POST['adid'] ) ).'&adstatus=preview');
		// }
    	$url = add_query_arg(array('adstatus' => 'preview'), url_showad(intval($_POST['adid'])));
    	wp_redirect($url);
		die;
	}
}



// if (get_awpcp_option('awpcppagefilterswitch') == 1) {
// 	add_filter('wp_list_pages_excludes', 'exclude_awpcp_child_pages');
// }

/**
 * Returns the IDs of the pages used by the AWPCP plugin.
 */
function exclude_awpcp_child_pages($excluded=array()) {
	// global $wpdb;

	// $pages = array_keys(awpcp_subpages());
	// if (empty($pages)) {
	// 	return array();
	// }

	// $query = 'SELECT id FROM ' . AWPCP_TABLE_PAGES . ' ';
	// $query.= "WHERE page in ('" . implode("','", $pages) . "')";

	// return  array_filter($wpdb->get_col($query));


	global $wpdb,$table_prefix;

	$awpcp_page_id = awpcp_get_page_id_by_ref('main-page-name');

	$query = "SELECT ID FROM {$table_prefix}posts WHERE post_parent='$awpcp_page_id' AND post_content LIKE '%AWPCP%'";
	$res = awpcp_query($query, __LINE__);

	$awpcpchildpages = array();
	while ($rsrow=mysql_fetch_row($res)) {
		$awpcpchildpages[] = $rsrow[0];
	}

	// foreach ($awpcpchildpages as $awpcppageidstoexclude) {
	// 	array_push($output, $awpcppageidstoexclude);
	// }

	// return $output;

	return array_merge($awpcpchildpages, $excluded);
}


function awpcp_rules() {
	global $wp_rewrite;	
	$categories_view = sanitize_title(get_awpcp_option('view-categories-page-name'));
	$permalink = get_permalink(awpcp_get_page_id_by_ref('main-page-name'));
	$pattern = trim(str_replace(home_url(), '', $permalink), '/');
	$pattern = '('.$pattern.')/('.$categories_view.')';
	$rules = $wp_rewrite->wp_rewrite_rules();
	if (!isset($rules[$pattern])) {
		flush_rewrite_rules();
	}
}


// add_action('generate_rewrite_rules', 'awpcp_rewrite_rules', 1, 1);
function awpcp_rewrite_rules($wp_rewrite) {
	$pages = array('main-page-name', 
				   'show-ads-page-name', 
				   'reply-to-ad-page-name', 
				   'browse-categories-page-name', 
				   'payment-thankyou-page-name', 
				   'payment-cancel-page-name');

	$home_url = home_url(); $patterns = array();
	foreach ($pages as $page) {
		$id = awpcp_get_page_id_by_ref($page);
		$permalink = get_permalink($id);
		$pattern = trim(str_replace($home_url, '', $permalink), '/');
		if (!empty($pattern)) {
			$patterns[$page] = $pattern;
		}
	}

	$prefix = '(' . $pattern['main-page-name'] . ')';
	$categories_view = sanitize_title(get_awpcp_option('view-categories-page-name'));

	$rules = array(
		'('.$patterns['show-ads-page-name'].')/(.+?)/(.+?)' 
			=> 'index.php?pagename=$matches[1]/&id=$matches[2]',
		'('.$patterns['reply-to-ad-page-name'].')/(.+?)/(.+?)' 
			=> 'index.php?pagename=$matches[1]/&id=$matches[2]',
		'('.$patterns['browse-categories-page-name'].')/(.+?)/(.+?)' 
			=> 'index.php?pagename=$matches[1]/&a=browsecat&cid='.$wp_rewrite->preg_index(2),
		'('.$patterns['payment-thankyou-page-name'].')/([a-zA-Z0-9]+)' 
			=> 'index.php?pagename=$matches[1]/&awpcp-txn='.$wp_rewrite->preg_index(2),
		'('.$patterns['payment-cancel-page-name'].')/([a-zA-Z0-9]+)' 
			=> 'index.php?pagename=$matches[1]/&awpcp-txn='.$wp_rewrite->preg_index(2),
		'('.$patterns['main-page-name'].')/(setregion)/(.+?)/(.+?)' 
			=> 'index.php?pagename='.$patterns['main-page-name'].'/&a=setregion&regionid='.$wp_rewrite->preg_index(2),
		'('.$patterns['main-page-name'].')/(classifiedsrss)' 
			=> 'index.php?pagename='.$patterns['main-page-name'].'/&awpcp-action=rss',
		'('.$patterns['main-page-name'].')/('.$categories_view.')' 
			=> 'index.php?pagename='.$patterns['main-page-name'].'/&layout=2&cid='.$categories_view);

	$wp_rewrite->rules = $rules + $wp_rewrite->rules;


	// global $siteurl;
	// $awpcppage = get_currentpagename();
	// $pprefx = sanitize_title($awpcppage, $post_ID='');

	// $pprefxpageguid=awpcp_get_guid($awpcppageid=awpcp_get_page_id($pprefx));
	// $showadspagename=sanitize_title(get_awpcp_option('show-ads-page-name'),$post_ID='');
	// $replytoadpagename=sanitize_title(get_awpcp_option('reply-to-ad-page-name'),$post_ID='');
	// $showadspageguid=awpcp_get_guid($awpcpshowadspageid=awpcp_get_page_id($showadspagename));
	// $replytoadsadspageguid=awpcp_get_guid($awpcpreplytoadspageid=awpcp_get_page_id($replytoadpagename));
	// $awpcppageguid=awpcp_get_guid($awpcppageid=awpcp_get_page_id($pprefx));
	// $browsecatspagename=sanitize_title(get_awpcp_option('browse-categories-page-name'),$post_ID='');
	// $browsecatspageguid=awpcp_get_guid($awpcpbrowsecatspageid=awpcp_get_page_id($browsecatspagename));
	// $paymentcancelpagename=sanitize_title(get_awpcp_option('payment-cancel-page-name'),$post_ID='');
	// $paymentcancelpageguid=awpcp_get_guid($awpcppaymentcancelpageid=awpcp_get_page_id($paymentcancelpagename));
	// $paymentthankyoupagename=sanitize_title(get_awpcp_option('payment-thankyou-page-name'),$post_ID='');
	// $paymentthankyoupageguid=awpcp_get_guid($awpcppaymentcancelpageid=awpcp_get_page_id($paymentthankyoupagename));
	// $categoriesviewpagename=sanitize_title(get_awpcp_option('view-categories-page-name'),$post_ID='');
	// //$browsecatspageguid=awpcp_get_guid($awpcpbrowsecatspageid=awpcp_get_page_id($browsecatspagename));

	// $awpcppre = $pprefx; // save a copy without parenthesis for use in rules below
	// $pprefx = '(' . sanitize_title($awpcppage, $post_ID='') . ')';

	// $awpcp_rules = array(
	// 	$pprefx.'/('.$showadspagename.')/(.+?)/(.+?)' => 'index.php?pagename=$matches[1]/$matches[2]/&id=$matches[3]',
	// 	$pprefx.'/('.$replytoadpagename.')/(.+?)/(.+?)' => 'index.php?pagename=$matches[1]/$matches[2]/&id=$matches[3]',
	// 	$pprefx.'/('.$browsecatspagename.')/(.+?)/(.+?)' => 'index.php?pagename=$matches[1]/$matches[2]/&a=browsecat&cid='.$wp_rewrite->preg_index(3),
	// 	$pprefx.'/('.$paymentthankyoupagename.')/(.+?)' => 'index.php?pagename=$matches[1]/$matches[2]/&i='.$wp_rewrite->preg_index(3),
	// 	$pprefx.'/('.$paymentcancelpagename.')/(.+?)' => 'index.php?pagename=$matches[1]/$matches[2]/&i='.$wp_rewrite->preg_index(3),
	// 	$pprefx.'/(setregion)/(.+?)/(.+?)' => 'index.php?pagename='.$awpcppre.'/&a=setregion&regionid='.$wp_rewrite->preg_index(3),
	// 	$pprefx.'/(classifiedsrss)' => 'index.php?pagename='.$awpcppre.'/&a=rss',
	// 	$pprefx.'/('.$categoriesviewpagename .')' => 'index.php?pagename='.$awpcppre.'/&layout=2'
	// );

	// // $wp_rewrite->rules = $awpcp_rules + $wp_rewrite->rules;
}

// add_filter('query_vars', 'awpcp_query_vars');
function awpcp_query_vars($query_vars) {
	$query_vars[] = "cid";
	$query_vars[] = "i";
	$query_vars[] = "id";
	$query_vars[] = "layout";
	$query_vars[] = "regionid";
	$query_vars[] = 'awpcp-action';
	return $query_vars;
}



// The function to add the reference to the plugin css style sheet to the header of the index page
function awpcp_addcss()
{
	//Echo OK here
	$awpcpstylesheet="awpcpstyle.css";
	$awpcpstylesheetie6="awpcpstyle-ie-6.css";
	echo "\n".'<link rel="stylesheet" type="text/css" media="screen" href="'.AWPCP_URL.'css/'.$awpcpstylesheet.'" /> 
			 <!--[if lte IE 6]><style type="text/css" media="screen">@import "'.AWPCP_URL.'css/'.$awpcpstylesheetie6.'";</style><![endif]-->
			 ';
	// load custom stylesheet if one exists in the wp-content/plugins directory: 
	if (file_exists(WP_PLUGIN_DIR.'/awpcp_custom_stylesheet.css')) { 
	    echo "\n".'<link rel="stylesheet" type="text/css" media="screen" href="'.WP_PLUGIN_URL.'/awpcp_custom_stylesheet.css" />';
	}

}
// PROGRAM FUNCTIONS

// START FUNCTIONS: Installation | Update




//	End process










// Add actions and filters etc
// add_action('wp_head', 'awpcp_insert_facebook_meta');

// The function to add the page meta and Facebook meta to the header of the index page
// https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2F108.166.84.26%2F%25253Fpage_id%25253D5%252526id%25253D3&t=Ad+in+Rackspace+1.8.9.4+(2)
function awpcp_insert_facebook_meta() {
	$output = '';


	if ((isset($_REQUEST['id']) && $_REQUEST['id'] != '') || get_query_var('id') != '' ) 
	{
		$ad_id = $_REQUEST['id'];
		if ( $ad_id == '' ) {
			$ad_id = get_query_var('id');
		}
		global $wpdb;

		$tbl_ads = $wpdb->prefix . "awpcp_ads";
		
		$ads = $wpdb->get_row('SELECT * FROM ' . $tbl_ads . ' WHERE ad_id = ' . $ad_id, ARRAY_A);

		if ( !empty($ads) ) {

			$charset = get_bloginfo('charset');

			$ads_url = url_showad($ads['ad_id']);
			$ads_title = stripslashes($ads['ad_title']);
			$ads_description = strip_tags(stripslashes($ads['ad_details']));
			$ads_description = str_replace("\n", " ", $ads_description);
			if ( strlen($ads_description) > 300 ) {
				$ads_description = substr($ads_description, 0, 300) . '...';
			}
			$output .= '<title>' . $ads_title . '</title>' . PHP_EOL;
			$output .= '<meta name="title" content="' . $ads_title . '" />' . PHP_EOL;
			$output .= '<meta name="description" content="' . htmlspecialchars($ads_description, ENT_QUOTES, $charset) . '" />' . PHP_EOL;
			$output .= '<meta property="og:type" content="article" />' . PHP_EOL;
			$output .= '<meta property="og:url" content="' . $ads_url . '" />' . PHP_EOL;
			$output .= '<meta property="og:title" content="' . $ads_title . '" />' . PHP_EOL;
			$output .= '<meta property="og:description" content="' . htmlspecialchars($ads_description, ENT_QUOTES, $charset) . '" />' . PHP_EOL;

			//$adpic = get_a_random_image($ads['ad_id']);
			$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

			$img_query = "SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id='$ad_id' AND disabled='0'";
			$ad_images = $wpdb->get_results($img_query, ARRAY_A);

			if (!empty($ad_images)) {

				if ( field_exists( $field = 'uploadfoldername' ) ) {
					$uploadfoldername = get_awpcp_option('uploadfoldername');
				} else {
					$uploadfoldername = 'uploads';
				}
				$blogurl = '';
				if ( is_multisite() ) {
					$blog = get_blog_details(1);
					$blogurl = $blog->siteurl;
				} else {
					$blogurl = get_site_url();
				}
				foreach ($ad_images as $ad_image) {
					$image_url = $blogurl . '/wp-content/' . $uploadfoldername . '/awpcp/' . $ad_image['image_name'];
					$output .=  '<meta property="og:image" content="' . $image_url . '" />' . PHP_EOL;
					$output .=  '<link rel="image_src" href="' . $image_url . '" />' . PHP_EOL;
				}
			} else {
				$output .= '<meta property="og:image" content="' . AWPCP_URL . 'images/adhasnoimage.gif" />' . PHP_EOL;
				// http://108.166.84.26/wp-content/plugins/another-wordpress-classifieds-plugin/images/adhasnoimage.gif
			}
		}
	}

	echo $output;
}

function awpcp_rel_canonical() {
	if (!is_singular())
		return;

	global $wp_the_query;
	if (!$page = $wp_the_query->get_queried_object_id()) {
		return;
	}

	if ($page != awpcp_get_page_id_by_ref('show-ads-page-name')) {
		return rel_canonical();
	}

	$ad = intval(awpcp_request_param('id', ''));
	$ad = empty($ad) ? intval(get_query_var('id')) : $ad;

	if (empty($ad)) {
		$link = get_permalink($page);
	} else {
		$link = url_showad($ad);
	}
	
	echo "<link rel='canonical' href='$link' />\n";
}
