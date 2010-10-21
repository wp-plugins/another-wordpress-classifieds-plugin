<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
///
///
// Another Wordpress Classifieds Plugin: This file: functions_awpcp.php
///
//Debugger helper:
if(!function_exists('_log')){
	function _log( $message ) {
		if( WP_DEBUG === true ){
			if( is_array( $message ) || is_object( $message ) ){
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}


// Error handler functions
function awpcpErrorHandler($errno, $errstr, $errfile, $errline){
	$output = '';
	switch ($errno) {
		case E_USER_ERROR:
			if ($errstr == "(SQL)"){
				// handling an sql error
				$output .= "<b>AWPCP SQL Error</b> Errno: [$errno] SQLError:" . SQLMESSAGE . "<br />\n";
				$output .= "Query : " . SQLQUERY . "<br />\n";
				$output .= "Called by line " . SQLERRORLINE . " in file " . SQLERRORFILE . ", error in ".__FILE__." at line ".__LINE__;
				$output .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				$output .= "Aborting...<br />\n";
			} else {
				$output .= "<b>AWPCP PHP Error</b> [$errno] $errstr<br />\n";
				$output .= "  Fatal error called by line $errline in file $errfile, error in ".__FILE__." at line ".__LINE__;
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

function sqlerrorhandler($ERROR, $QUERY, $PHPFILE, $LINE){
	define("SQLQUERY", $QUERY);
	define("SQLMESSAGE", $ERROR);
	define("SQLERRORLINE", $LINE);
	define("SQLERRORFILE", $PHPFILE);
	trigger_error("(SQL)", E_USER_ERROR);
}

function awpcp_query($query, $LINE) {
	//Query, and if failure happens, emit an appropriate error
	$res = array();
	if (!($res=mysql_query($query))) {
		sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], $LINE);
	}
	return $res;
}
//Error handler installed in main awpcp.php file, after this file is included.
function add_slashes_recursive( $variable )
{
	if ( is_string( $variable ) )
	return addslashes( $variable ) ;

	elseif ( is_array( $variable ) )
	foreach( $variable as $i => $value )
	$variable[ $i ] = add_slashes_recursive( $value ) ;

	return $variable ;
}

function strip_slashes_recursive( $variable )
{
	if ( is_string( $variable ) )
	return stripslashes( $variable ) ;
	if ( is_array( $variable ) )
	foreach( $variable as $i => $value )
	$variable[ $i ] = strip_slashes_recursive( $value ) ;

	return $variable ;
}

function awpcp_submit_spam($ad_id) {
	if (function_exists('akismet_init')) {
		$wpcom_api_key = get_option('wordpress_api_key');

		if (!empty($wpcom_api_key)) {
			require_once(ABSPATH . WPINC . '/pluggable.php');
			_log("Now submitting ad " . $ad_id . " as spam");
			global $wpdb, $akismet_api_host, $akismet_api_port, $current_user, $current_site;
			$ad_id = (int) $ad_id;
			$tbl_ads = $wpdb->prefix . "awpcp_ads";
			$query = "SELECT * FROM " . $tbl_ads . " WHERE ad_id = ".$ad_id;
			$res = awpcp_query($query, __LINE__);
			if ($ad_record=mysql_fetch_array($res)) {
				if ( $ad_record['disabled'] == '1' ) {
					_log("Ad " . $ad_id . " already marked as spam");
					return;
				}
				$content = array();
				_log("Ad " . $ad_id . " constructing Akismet call");
				//Construct an Akismet-like query:
				$content['comment_author'] = $ad_record['ad_contact_name'];
				$content['comment_author_email'] = $ad_record['ad_contact_email'];
				$content['comment_author_url'] = $ad_record['websiteurl'];
				$content['comment_content'] = $ad_record['ad_details'];
				$content['blog'] = get_option('home');
				$content['blog_lang'] = get_locale();
				$content['blog_charset'] = get_option('blog_charset');
				$content['permalink'] = '';
				get_currentuserinfo();
				if ( is_object($current_user) ) {
				    $content['reporter'] = $current_user->user_login;
				}
				if ( is_object($current_site) ) {
					$content['site_domain'] = $current_site->domain;
				}
				$content['user_role'] = 'Subscriber';	//always
				$query_string = '';
				foreach ( $content as $key => $data ) {
					$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
				}
				_log("Ad " . $ad_id . " query: " . $query_string);
				$response = akismet_http_post($query_string, $akismet_api_host, "/1.1/submit-spam", $akismet_api_port);
				_log("Ad " . $ad_id . " spammed, Akismet said: ");
				foreach ($response as $key => $value) {
					_log($key." - ".$value."");
				}
			} else {
				_log("Ad " . $ad_id . " not found, cannot mark as spam");
			}
		} else {
			global $message;
			$message="<div style=\"background-color: #FF99CC;\" id=\"message\" class=\"updated fade\">";
			$message.=__("Please disable spam control on your AWPCP settings because you do not have Akismet properly configured (missing API key)","AWPCP");
			$message.="</div>";
		}
	} else {
		global $message;
		$message="<div style=\"background-color: #FF99CC;\" id=\"message\" class=\"updated fade\">";
		$message.=__("Please disable spam control on your AWPCP settings because you do not have Akismet installed","AWPCP");
		$message.="</div>";
	}
}

//Function to detect spammy posts.  Requires Akismet to be installed.
function awpcp_check_spam($name, $website, $email, $details) {
	$content = array();
	//Construct an Akismet-like query:
	$content['comment_author'] = $name;
	$content['comment_author_email'] = $email;
	$content['comment_author_url'] = $website;
	$content['comment_content'] = $details;
	
	// innocent until proven guilty
	$isSpam = FALSE;

	if (function_exists('akismet_init')) {

		$wpcom_api_key = get_option('wordpress_api_key');

		if (!empty($wpcom_api_key)) {

			global $akismet_api_host, $akismet_api_port;

			// set remaining required values for akismet api
			$content['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$content['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$content['referrer'] = $_SERVER['HTTP_REFERER'];
			$content['blog'] = get_option('home');

			if (empty($content['referrer'])) {
				$content['referrer'] = get_permalink();
			}

			$queryString = '';

			foreach ($content as $key => $data) {
				if (!empty($data)) {
					$queryString .= $key . '=' . urlencode(stripslashes($data)) . '&';
				}
			}

			$response = akismet_http_post($queryString, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
			_log("Spam check, Akismet said: ");
			foreach ($response as $key => $value) {
				_log($key." - ".$value."");
			}
						
			if ($response[1] == 'true') {
				//update_option('akismet_spam_count', get_option('akismet_spam_count') + 1);
				$isSpam = TRUE;
			}

		} else {
			global $message;
			$message="<div style=\"background-color: #FF99CC;\" id=\"message\" class=\"updated fade\">";
			$message.=__("Please disable spam control on your AWPCP settings because you do not have Akismet properly configured (missing API key)","AWPCP");
			$message.="</div>";
		}
	} else {
		global $message;
		$message="<div style=\"background-color: #FF99CC;\" id=\"message\" class=\"updated fade\">";
		$message.=__("Please disable spam control on your AWPCP settings because you do not have Akismet installed","AWPCP");
		$message.="</div>";
	}
	_log("Ad spam check: " . $isSpam);
	return $isSpam;

}

///
// START FUNCTION: retrieve individual options from settings table
///

function get_awpcp_setting($column, $option) {
	global $wpdb;
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";
	$myreturn=0;
	$tableexists=checkfortable($tbl_ad_settings);

	if($tableexists)
	{
		$query="SELECT ".$column." FROM  ".$tbl_ad_settings." WHERE config_option='$option'";
		$res = $wpdb->get_var($query);
		$myreturn = strip_slashes_recursive($res);
	}
	return $myreturn;
}

function get_awpcp_option($option) {
	return get_awpcp_setting('config_value', $option);
}

function get_awpcp_option_group_id($option) {
	return get_awpcp_setting('config_group_id', $option);
}

function get_awpcp_option_type($option) {
	return get_awpcp_setting('option_type', $option);
}

function get_awpcp_option_config_diz($option) {
	return get_awpcp_setting('config_diz', $option);
}

///
// END FUNCTION
///

function awpcp_is_classifieds()
{
	global $post,$table_prefix;
	$awpcppageid=$post->ID;
	$classifiedspagecontent="[AWPCPCLASSIFIEDSUI]";

	$query="SELECT post_content FROM {$table_prefix}posts WHERE ID='$awpcppageid' AND post_type='page' AND post_status='publish'";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		list($thepostcontentvalue)=$rsrow;
	}

	$istheclassifiedspage= (strcasecmp($thepostcontentvalue, $classifiedspagecontent) == 0);
	return $istheclassifiedspage;

}


// START FUNCTION: Check if the user is an admin

function checkifisadmin() {

	global $current_user;
	get_currentuserinfo();

	if(get_awpcp_option('awpcpadminaccesslevel') == 'admin')
	{
		if(current_user_can('install_plugins'))
		{
			$isadmin=1;
		}
		else
		{
			$isadmin=0;
		}
	}
	if(get_awpcp_option('awpcpadminaccesslevel') == 'editor')
	{
		if(current_user_can('edit_pages'))
		{
			$isadmin=1;
		}
		else
		{
			$isadmin=0;
		}
	}
	else
	{
		if(current_user_can('install_plugins'))
		{
			$isadmin=1;
		}
		else
		{
			$isadmin=0;
		}
	}

	return $isadmin;

}

///
// END FUNCTION
///

function awpcpistableempty($table){
	global $wpdb;

	$myreturn=true;
	$query="SELECT count(*) FROM ".$table."";
	$res = awpcp_query($query, __LINE__);
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=false;
	}
	return $myreturn;
}

function awpcpisqueryempty($table, $where){
	global $wpdb;

	$myreturn=true;
	$query="SELECT count(*) FROM ".$table." ".$where;
	$res = awpcp_query($query, __LINE__);
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=false;
	}
	return $myreturn;
}

// START FUNCTION: Check if the admin has setup any listing fee options


function adtermsset(){

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$myreturn=!awpcpistableempty($tbl_ad_fees);
	return $myreturn;
}


// END FUNCTION


function get_2co_prodid($adterm_id) {

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$twoco_pid='';

	$query="SELECT twoco_pid from ".$tbl_ad_fees." WHERE adterm_id='$adterm_id'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res))
	{
		list($twoco_pid)=$rsrow;
	}

	return $twoco_pid;
}


// START FUNCTION: Check if the admin has setup some categories


function categoriesexist(){

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	$myreturn=!awpcpistableempty($tbl_categories);
	return $myreturn;
}


// END FUNCTION



function adtermidinuse($adterm_id)
{
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$myreturn=!awpcpisqueryempty($tbl_ads, " WHERE adterm_id='$adterm_id'");
	return $myreturn;
}


// START FUNCTION: Count the total number of ads in the  system


function countlistings(){

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$totallistings='';

	$query="SELECT count(*) FROM ".$tbl_ads."";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($totallistings)=$rsrow;
	}
	return $totallistings;
}


// END FUNCTION



// START FUNCTION: Count the total number of categories


function countcategories(){

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	$totalcategories='';

	$query="SELECT count(*) FROM ".$tbl_categories."";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($totalcategories)=$rsrow;
	}
	return $totalcategories;
}


// END FUNCTION



// START FUNCTION: Count parent categories


function countcategoriesparents(){

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	$totalparentcategories='';
	$query="SELECT count(*) FROM ".$tbl_categories." WHERE category_parent_id='0'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($totalparentcategories)=$rsrow;
	}
	return $totalparentcategories;
}


// END FUNCTION



// START FUNCTION: Count children categories


function countcategorieschildren(){

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	$totalchildrencategories='';
	$query="SELECT count(*) FROM ".$tbl_categories." WHERE category_parent_id!='0'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($totalchildrencategories)=$rsrow;
	}
	return $totalchildrencategories;
}


// END FUNCTION



// START FUNCTION: get number of images allowed per ad term id


function get_numimgsallowed($adtermid){
	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";
	$imagesallowed='';
	$query="SELECT imagesallowed FROM ".$tbl_ad_fees." WHERE adterm_id='$adtermid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($imagesallowed)=$rsrow;
	}
	return $imagesallowed;
}


// END FUNCTION


/////////
// START FUNCTION check if ad has entry in adterm ID field in the event admin switched back to free mode after previously running in paid mode
// this way user continues to be allowed number of images allowed per the ad term ID
/////////

function ad_term_id_set($adid)
{
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$myreturn=false;

	$query="SELECT adterm_id from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adterm_id)=$rsrow;
	}
	if($adterm_id > 0)
	{
		$myreturn=true;
	}

	return $myreturn;;


}


// END FUNCTION check if user has paid for ad in event admin switched back to free mode after previously running in paid mode
// this way if user paid for ad user continues to be allowed number of images paid for




// START FUNCTION: Check to see how many images an ad is currently using


function get_total_imagesuploaded($ad_id) {

	global $wpdb;
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

	$totalimagesuploaded='';

	$query="SELECT count(*) FROM ".$tbl_ad_photos." WHERE ad_id='$ad_id'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($totalimagesuploaded)=$rsrow;
	}
	return $totalimagesuploaded;

}


// END FUNCTION




// START FUNCTION: Get the total number of days an ad term last based on term ID value


function get_num_days_in_term($adtermid) {
	$numdaysinterm='';
	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$query="SELECT rec_period from ".$tbl_ad_fees." WHERE adterm_id='$adtermid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($numdaysinterm)=$rsrow;
	}
	return $numdaysinterm;
}


// END FUNCTION



// START FUNCTION: Get the id for the ad term based on having the ad ID


function get_adterm_id($adid) {

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$adterm_id='';

	$query="SELECT adterm_id from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adterm_id)=$rsrow;
	}
	return $adterm_id;
}


// END FUNCTION



// START FUNCTION: Get the ad term name for the ad term based on having the ad term ID


function get_adterm_name($adterm_id) {

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$adterm_name='';

	$query="SELECT adterm_name from ".$tbl_ad_fees." WHERE adterm_id='$adterm_id'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adterm_name)=$rsrow;
	}
	return $adterm_name;
}


// END FUNCTION



// START FUNCTION: Get the ad recperiod based on having the ad term ID


function get_fee_recperiod($adterm_id) {

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$recperiod='';

	$query="SELECT rec_period from ".$tbl_ad_fees." WHERE adterm_id='$adterm_id'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($recperiod)=$rsrow;
	}
	return $recperiod;
}


// END FUNCTION




// START FUNCTION: Get the ad posters name based on having the ad ID


function get_adpostername($adid) {

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$adpostername='';
	$query="SELECT ad_contact_name from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adpostername)=$rsrow;
	}

	return $adpostername;
}


// END FUNCTION



// START FUNCTION: Get the ad posters access key based on given ID


function get_adkey($adid) {

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$adkey='';

	$query="SELECT ad_key from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adkey)=$rsrow;
	}
	return $adkey;
}


// END FUNCTION



// START FUNCTION: Get the ad title based on having the ad email


function get_adcontactbyem($email) {
	$adcontact='';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_contact_name from ".$tbl_ads." WHERE ad_contact_email='$email'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adcontact)=$rsrow;
	}
	return $adcontact;

}


// END FUNCTION



// START FUNCTION: Get the ad posters name based on having the ad email


function get_adtitlebyem($email) {
	$adtitle='';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_title from ".$tbl_ads." WHERE ad_contact_email='$email'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adtitle)=$rsrow;
	}
	return strip_slashes_recursive($adtitle);

}


// END FUNCTION



// START FUNCTION: Get the ad posters email based on having the ad ID


function get_adposteremail($adid) {

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$adposteremail='';

	$query="SELECT ad_contact_email from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adposteremail)=$rsrow;
	}
	return $adposteremail;
}

function get_adstartdate($adid) {

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$adstartdate='';

	$query="SELECT ad_start_date from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adstartdate)=$rsrow;
	}
	return $adstartdate;
}


// END FUNCTION



// START FUNCTION: Get the number of times an ad has been viewed


function get_numtimesadviewd($adid)
{

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$numtimesadviewed='';

	$query="SELECT ad_views from ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($numtimesadviewed)=$rsrow;
	}
	return $numtimesadviewed;

}


// END FUNCTION: Get the number of times an ad has been viewed




// START FUNCTION: Get the ad title based on having the ad ID


function get_adtitle($adid) {

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$adtitle='';

	$query="SELECT ad_title from ".$tbl_ads." WHERE ad_id='$adid'";
	$adtitle = $wpdb->get_var($query);
	//$res = awpcp_query($query, __LINE__);
	//while ($rsrow=mysql_fetch_row($res)) {
	//	list($adtitle)=$rsrow;
	//}

	return strip_slashes_recursive($adtitle);
}


// END FUNCTION




// START FUNCTION: Get the ad term fee amount for the ad term based on having the ad term ID


function get_adfee_amount($adterm_id) {

	global $wpdb;
	$tbl_ad_fees = $wpdb->prefix . "awpcp_adfees";

	$adterm_amount='';

	$query="SELECT amount from ".$tbl_ad_fees." WHERE adterm_id='$adterm_id'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($adterm_amount)=$rsrow;
	}
	return $adterm_amount;
}


// END FUNCTION: get ad term fee amount based on ad term ID



// START FUNCTION: Create list of top level categories for admin category management



function get_categorynameid($cat_id = 0,$cat_parent_id= 0,$exclude)
{

	global $wpdb;
	$optionitem='';
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	if(isset($exclude) && !empty($exclude))
	{
		$excludequery="AND category_id !='$exclude'";
	}else{$excludequery='';}

	$catnid=$wpdb->get_results("select category_id as cat_ID, category_parent_id as cat_parent_ID, category_name as cat_name from ".$tbl_categories." WHERE category_parent_id='0' AND category_name <> '' $excludequery");

	foreach($catnid as $categories)
	{

		if($categories->cat_ID == $cat_parent_id)
		{
			$optionitem .= "<option selected='selected' value='$categories->cat_ID'>$categories->cat_name</option>";
		}
		else
		{
			$optionitem .= "<option value='$categories->cat_ID'>$categories->cat_name</option>";
		}

	}

	return $optionitem;
}


// END FUNCTION: create list of top level categories for admin category management



// START FUNCTION: Create the list with both parent and child categories selection for ad post form


function get_categorynameidall($cat_id = 0)
{

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";
	$optionitem='';

	// Start with the main categories

	$query="SELECT category_id,category_name FROM ".$tbl_categories." WHERE category_parent_id='0' and category_name <> '' ORDER BY category_order, category_name ASC";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res)) {

		$cat_ID=$rsrow[0];
		$cat_name=$rsrow[1];

		$opstyle="class=\"dropdownparentcategory\"";

		if($cat_ID == $cat_id)
		{
			$maincatoptionitem = "<option $opstyle selected='selected' value='$cat_ID'>$cat_name</option>";
		}
		else {
			$maincatoptionitem = "<option $opstyle value='$cat_ID'>$cat_name</option>";
		}

		$optionitem.="$maincatoptionitem";

		// While still looping through main categories get any sub categories of the main category

		$maincatid=$cat_ID;

		$query="SELECT category_id,category_name FROM ".$tbl_categories." WHERE category_parent_id='$maincatid' ORDER BY category_order, category_name ASC";
		$res2 = awpcp_query($query, __LINE__);

		while ($rsrow2=mysql_fetch_row($res2)) {
			$subcat_ID=$rsrow2[0];
			$subcat_name=$rsrow2[1];

			if($subcat_ID == $cat_id)
			{
				$subcatoptionitem = "<option selected='selected' value='$subcat_ID'>- $subcat_name</option>";
			}
			else {
				$subcatoptionitem = "<option  value='$subcat_ID'>- $subcat_name</option>";
			}

			$optionitem.="$subcatoptionitem";
		}
	}

	return $optionitem;
}


// END FUNCTION: create drop down list of categories for ad post form



// START FUNCTION: Retrieve the category name


function get_adcatname($cat_ID){

	global $wpdb;
	$cname='';
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	if(isset($cat_ID) && (!empty($cat_ID))){
		$query="SELECT category_name from ".$tbl_categories." WHERE category_id='$cat_ID'";
		$cname = $wpdb->get_results($query, ARRAY_A);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res)) {
		//	list($cname)=$rsrow;
		//}
		foreach($cname as $cn) { 
			$cname = $cn['category_name'];
		}
	}
	return strip_slashes_recursive($cname);
}

function get_adcatorder($cat_ID){

	global $wpdb;
	$cname='';
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	if(isset($cat_ID) && (!empty($cat_ID))){
		$query="SELECT category_order from ".$tbl_categories." WHERE category_id='$cat_ID'";
		$res = awpcp_query($query, __LINE__);
		while ($rsrow=mysql_fetch_row($res)) {
			list($cname)=$rsrow;
		}
	}
	return $cname;
}


// END FUNCTION: get the category name



// START FUNCTION: Retrieve the country associated with a specific ad


function get_adcountryvalue($adid){

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$theadcountry='';

	if(isset($adid) && (!empty($adid))){
		$query="SELECT ad_country from ".$tbl_ads." WHERE ad_id='$adid'";
		$theadcountry = $wpdb->get_var($query);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res)) {
		//	list($theadcountry)=$rsrow;
		//}
	}
	return $theadcountry;
}


// END FUNCTION: Retrieve the country associated with a specific ad




// START FUNCTION: Retrieve the state associated with a specific ad


function get_adstatevalue($adid){

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$theadstate='';

	if(isset($adid) && (!empty($adid))){
		$query="SELECT ad_state from ".$tbl_ads." WHERE ad_id='$adid'";
		$theadstate = $wpdb->get_var($query);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res)) {
		//	list($theadstate)=$rsrow;
		//}
	}
	return $theadstate;
}


// END FUNCTION: Retrieve the state associated with a specific ad




// START FUNCTION: Retrieve the city associated with a specific ad


function get_adcityvalue($adid){

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$theadcity='';

	if(isset($adid) && (!empty($adid))){
		$query="SELECT ad_city from ".$tbl_ads." WHERE ad_id='$adid'";
		$adcity = $wpdb->get_var($query);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res)) {
		//	list($theadcity)=$rsrow;
		//}
	}
	return $theadcity;
}


// END FUNCTION: Retrieve the city associated with a specific ad



// START FUNCTION: Retrieve the city associated with a specific ad


function get_adcountyvillagevalue($adid){

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$theadcountyvillage='';

	if(isset($adid) && (!empty($adid))){
		$query="SELECT ad_county_village from ".$tbl_ads." WHERE ad_id='$adid'";
		$theadcountyvillage = $wpdb->get_var($query);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res)) {
		//	list($theadcountyvillage)=$rsrow;
		//}
	}
	return $theadcountyvillage;
}


// END FUNCTION: Retrieve the city associated with a specific ad




// START FUNCTION: Retrieve the category associated with a specific ad


function get_adcategory($adid){

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$theadcategoryid='';

	if(isset($adid) && (!empty($adid))){
		$query="SELECT ad_category_id from ".$tbl_ads." WHERE ad_id='$adid'";
		$theadcategoryid = $wpdb->get_var($query);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res)) {
		//	list($theadcategoryid)=$rsrow;
		//}
	}
	return $theadcategoryid;
}


// END FUNCTION: Retrieve the category associated with a specific ad




// START FUNCTION: Retrieve the parent category name



function get_adparentcatname($cat_ID){

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";
	$cname='';

	if($cat_ID == '0')
	{
		$cname="Top Level Category";
	}

	else
	{

		if(isset($cat_ID) && (!empty($cat_ID)))
		{
			$query="SELECT category_name from ".$tbl_categories." WHERE category_id='$cat_ID'";
			$res = awpcp_query($query, __LINE__);

			while ($rsrow=mysql_fetch_row($res))
			{
				list($cname)=$rsrow;
			}
		}
	}
	return $cname;
}


// END FUNCTION: get the name of the category parent



// START FUNCTION: Retrieve the parent category ID


function get_cat_parent_ID($cat_ID){

	global $wpdb;
	$cpID='';
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	if(isset($cat_ID) && (!empty($cat_ID))){
		$query="SELECT category_parent_id from ".$tbl_categories." WHERE category_id='$cat_ID'";
		$res = awpcp_query($query, __LINE__);
		while ($rsrow=mysql_fetch_row($res)) {
			list($cpID)=$rsrow;
		}
	}
	return $cpID;
}


// END FUNCTION: get the ID or the category parent



// START FUNCTION: Check if the transaction ID coming back from paypal or 2checkout is a duplicate


function isdupetransid($transid){
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$myreturn=!awpcpisqueryempty($tbl_ads, " WHERE ad_transaction_id='$transid'");
	return $myreturn;
}


// END FUNCTION: check if a transaction ID from paypal or 2checkout is already in the system



// START FUNCTION: Check if there are any ads in the system



function ads_exist() {
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$myreturn=!awpcpistableempty($tbl_ads);
	return $myreturn;
}


// END FUNCTION: check if any ads exist in the system



// START FUNCTION: Check if there are any ads in a specified category



function ads_exist_cat($catid) {
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$myreturn=!awpcpisqueryempty($tbl_ads, " WHERE ad_category_id='$catid' OR ad_category_parent_id='$catid'");
	return $myreturn;
}


// END FUNCTION: check if a category has ads



// START FUNCTION: Check if the category has children



function category_has_children($catid) {
	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";
	$myreturn=!awpcpisqueryempty($tbl_categories, " WHERE category_parent_id='$catid'");
	return $myreturn;
}


// END FUNCTION: check if a category has children



// START FUNCTION: Check if the category is a child



function category_is_child($catid) {
	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";
	$myreturn=false;

	$query="SELECT category_parent_id FROM ".$tbl_categories." WHERE category_id='$catid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($cparentid)=$rsrow;
		if( $cparentid != '0' )
		{
			$myreturn=true;
		}
	}
	return $myreturn;
}


// END FUNCTION: check if a category is a child




// START FUNCTION: Check how many ads a category contains



function total_ads_in_cat($catid) {
	global $wpdb,$hasregionsmodule;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$totaladsincat='';
	$filter='';


	if((get_awpcp_option('disablependingads') == 1)  && (get_awpcp_option('freepay') == 1)){
		$filter=" AND payment_status != 'Pending'";
	}

	if($hasregionsmodule == 1)
	{
		if( isset($_SESSION['theactiveregionid']) )
		{
			$theactiveregionid=$_SESSION['theactiveregionid'];
			$theactiveregionname=get_theawpcpregionname($theactiveregionid);

			$filter.="AND (ad_city='$theactiveregionname' OR ad_state='$theactiveregionname' OR ad_country='$theactiveregionname' OR ad_county_village='$theactiveregionname')";
		}
	}

	$query="SELECT count(*) FROM ".$tbl_ads." WHERE (ad_category_id='$catid' OR ad_category_parent_id='$catid') AND disabled = '0' $filter";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res)) {
		list($totaladsincat)=$rsrow;
	}
	return $totaladsincat;
}


// END FUNCTION: check how many ads are in a category



// START FUNCTION: Check if there are any ads in the system



function images_exist() {
	global $wpdb;
	$tbl_ad_photos = $wpdb->prefix . "awpcp_ads";
	$myreturn=!awpcpistableempty($tbl_ad_photos);
	return $myreturn;
}


// END FUNCTION: Check if images exist in system




// START FUNCTION: Eemove unwanted characters from string and setup for use with search engine friendly urls


function cleanstring($text)
{

	$code_entities_match = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
	$code_entities_replace = array('_','_','','','','','','','','','','','','','','','','','','','','','','','');
	$text = str_replace($code_entities_match, $code_entities_replace, $text);
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		$text="".(filter_var($text, FILTER_SANITIZE_URL))."";
	}
	return $text;
}


// END FUNCTION: remove unwanted characters from string to be used in URL for search engine friendliness



// START FUNCTION: replace underscores with dashes for search engine friendly urls


function add_dashes($text) {
	$text=str_replace("_","-",$text);
	return $text;
}


// END FUNCTION: replace underscores with dashes for search engine friendly urls




// START FUNCTION: get the page ID when the page name is known


// Get the id of a page by its name
function awpcp_get_page_id($awpcppagename){
	global $wpdb;
	$awpcpwppostpageid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$awpcppagename'");
	return $awpcpwppostpageid;
}


// END FUNCTION: Get the ID from wordpress posts table where the post_name is known



// START FUNCTION: Get the page guid


function awpcp_get_guid($awpcpshowadspageid){
	global $wpdb;
	$awpcppageguid = $wpdb->get_var("SELECT guid FROM $wpdb->posts WHERE ID ='$awpcpshowadspageid'");
	return $awpcppageguid;
}

// END FUNCTION: Get the page guid



// START FUNCTION: Get the order by setting for ad listings


function get_group_orderby()
{

	$getgrouporderby=get_awpcp_option('groupbrowseadsby');

	if(!isset($getgrouporderby) || empty($getgrouporderby))
	{
		$grouporderby='';
	}
	else
	{
		if(isset($getgrouporderby) && !empty($getgrouporderby))
		{
			if($getgrouporderby == 1)
			{
				$grouporderby="ORDER BY ad_key DESC";
			}
			elseif($getgrouporderby == 2)
			{
				$grouporderby="ORDER BY ad_title DESC";
			}
			elseif($getgrouporderby == 3)
			{
				$grouporderby="ORDER BY ad_is_paid DESC, ad_postdate DESC, ad_title ASC";
			}
			elseif($getgrouporderby == 4)
			{
				$grouporderby="ORDER BY ad_is_paid DESC, ad_title ASC";
			}
			elseif($getgrouporderby == 5)
			{
				$grouporderby="ORDER BY ad_views DESC, ad_title ASC";
			}
			elseif($getgrouporderby == 6)
			{
				$grouporderby="ORDER BY ad_views DESC, ad_key DESC";
			}
		}
	}

	return $grouporderby;
}


// END FUNCTION: Get the orderby setting for ad listings




// START FUNCTION: setup the structure of the URLs based on if permalinks are on and SEO urls are turned on


function setup_url_structure($awpcpthepagename)
{
	$quers='';
	$theblogurl=get_bloginfo('url');

	$permastruc=get_option('permalink_structure');

	if( strstr($permastruc,'index.php') )
	{
		$theblogurl.="/index.php";
	}

	if(isset($permastruc) && !empty($permastruc))
	{
		$quers="$theblogurl/$awpcpthepagename";
	}
	else
	{
		$quers="$theblogurl";
	}


	return $quers;
}


// END FUNCTION: setup structure of URLs based on if permalinks are on and SEO urls are turned on


function url_showad($ad_id)
{
	$url_showad='';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');
	$showadspagename=sanitize_title(get_awpcp_option('showadspagename'), $post_ID='');
	$awpcp_showad_pageid=awpcp_get_page_id($showadspagename);
	$awpcpadcity=get_adcityvalue($ad_id);
	$awpcpadstate=get_adstatevalue($ad_id);
	$awpcpadcountry=get_adcountryvalue($ad_id);
	$awpcpadcountyvillage=get_adcountyvillagevalue($ad_id);

	$awpcpadtitle=get_adtitle($ad_id);
	$modtitle=cleanstring($awpcpadtitle);
	$modtitle=add_dashes($modtitle);
	$seoFriendlyUrls = get_awpcp_option('seofriendlyurls');
	if( $seoFriendlyUrls )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_showad="$quers/$showadspagename/$ad_id/$modtitle/";
		}
		else
		{
			$awpcp_showad_pageid=awpcp_get_page_id($showadspagename);
			$url_showad="$quers/?page_id=$awpcp_showad_pageid&id=$ad_id";
		}
	}
	else
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_showad="$quers/$showadspagename/?id=$ad_id";
		}
		else
		{
			$awpcp_showad_pageid=awpcp_get_page_id($awpcp_showad_pagename=(sanitize_title(get_awpcp_option('showadspagename'), $post_ID='')));
			$url_showad="$quers/?page_id=$awpcp_showad_pageid&id=$ad_id";
		}
	}

	if( $seoFriendlyUrls )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			if( get_awpcp_option('showcityinpagetitle') && !empty($awpcpadcity) )
			{
				$url_showad.="/";
				$url_showad.=cleanstring(add_dashes(get_adcityvalue($ad_id)));
			}
			if( get_awpcp_option('showstateinpagetitle') && !empty($awpcpadstate) )
			{
				$url_showad.="/";
				$url_showad.=cleanstring(add_dashes(get_adstatevalue($ad_id)));
			}
			if( get_awpcp_option('showcountryinpagetitle') && !empty($awpcpadcountry) )
			{
				$url_showad.="/";
				$url_showad.=cleanstring(add_dashes(get_adcountryvalue($ad_id)));
			}
			if( get_awpcp_option('showcountyvillageinpagetitle') && !empty($awpcpadcountyvillage) )
			{
				$url_showad.="/";
				$url_showad.=cleanstring(add_dashes(get_adcountyvillagevalue($ad_id)));
			}
			if( get_awpcp_option('showcategoryinpagetitle') )
			{
				$awpcp_ad_category_id=get_adcategory($ad_id);
				$awpcp_ad_category_name=cleanstring(add_dashes(get_adcatname($awpcp_ad_category_id)));

				$url_showad.="/";
				$url_showad.=$awpcp_ad_category_name;
			}
			//Always append a slash (RSS module issue)
			$url_showad.="/";
		}
	}
	return $url_showad;
}

function url_placead()
{
	$url_placead='';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');
	$placeadpagename=sanitize_title(get_awpcp_option('placeadpagename'), $post_ID='');
	$awpcp_placead_pageid=awpcp_get_page_id($placeadpagename);
	if( get_awpcp_option('seofriendlyurls') )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_placead="$quers/$placeadpagename/";
		}
		else
		{
			$url_placead="$quers/?page_id=$awpcp_placead_pageid";
		}
	}
	else
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_placead="$quers/$placeadpagename/";
		}
		else
		{
			$url_placead="$quers/?page_id=$awpcp_placead_pageid";
		}
	}

	return $url_placead;
}

function url_classifiedspage()
{
	$url_classifiedspage='';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');
	$awpcp_pageid=awpcp_get_page_id($awpcppagename);
	if( get_awpcp_option('seofriendlyurls') )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_classifiedspage="$quers/";
		}
		else
		{
			$url_classifiedspage="$quers/?page_id=$awpcp_pageid";
		}
	}
	else
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_classifiedspage="$quers/";
		}
		else
		{
			$url_classifiedspage="$quers/?page_id=$awpcp_pageid";
		}
	}

	return $url_classifiedspage;
}

function url_searchads()
{
	$url_searchad='';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');
	$searchadspagename=sanitize_title(get_awpcp_option('searchadspagename'), $post_ID='');
	$awpcp_searchads_pageid=awpcp_get_page_id($searchadspagename);

	if( get_awpcp_option('seofriendlyurls') )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_searchads="$quers/$searchadspagename/";
		}
		else
		{
			$url_searchads="$quers/?page_id=$awpcp_searchads_pageid";
		}
	}
	else
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_searchads="$quers/$searchadspagename/";
		}
		else
		{
			$url_searchads="$quers/?page_id=$awpcp_searchads_pageid";
		}
	}

	return $url_searchads;
}

function url_editad()
{
	$url_placead='';
	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$quers=setup_url_structure($awpcppagename);
	$permastruc=get_option('permalink_structure');
	$editadpagename=sanitize_title(get_awpcp_option('editadpagename'), $post_ID='');
	$awpcp_editad_pageid=awpcp_get_page_id($editadpagename);
	if( get_awpcp_option('seofriendlyurls') )
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_editad="$quers/$editadpagename/";
		}
		else
		{
			$url_editad="$quers/?page_id=$awpcp_editad_pageid";
		}
	}
	else
	{
		if(isset($permastruc) && !empty($permastruc))
		{
			$url_editad="$quers/$editpagename/";
		}
		else
		{
			$url_editad="$quers/?page_id=$awpcp_editad_pageid";
		}
	}

	return $url_editad;
}

// START FUNCTION: get the parent_id of the post


function get_page_parent_id($awpcpwppostpageid){
	global $wpdb;
	$awpcppageparentid = $wpdb->get_var("SELECT post_parent FROM $wpdb->posts WHERE ID = '$awpcpwppostpageid'");
	return $awpcppageparentid;
}


// END FUNCTION: get the parent id of a wordpress post where the post ID is known



// START FUNCTION: get the name of a wordpress entry from table posts where the parent id is present


function get_awpcp_parent_page_name($awpcppageparentid) {

	global $wpdb;
	$awpcpparentpagename = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID = '$awpcppageparentid'");
	return $awpcpparentpagename;
}


// END FUNCTION: get the name of a wordpress wp_post entry where the ID of the post parent is present




// START FUNCTION: check if a specific database table exists


function checkfortable($table) {
	global $wpdb;
	$tableexists = $wpdb->get_var("show tables like '$table'") == $table;
	return $tableexists;
}


// END FUNCTION: check if a specific database table exists




// START FUNCTION: add field config_group_id to table adsettings v 1.0.5.6 update specific



function add_config_group_id($cvalue,$coption)
{
	global $wpdb;
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";
	//Escape quotes:
	$cvalue = add_slashes_recursive($cvalue);
	$query="UPDATE ".$tbl_ad_settings." SET config_group_id='$cvalue' WHERE config_option='$coption'";
	$res = awpcp_query($query, __LINE__);
}


// END FUNCTION: add field config_group_id to table adsettings v 1.0.5.6 update specific



// START FUNCTION: check if a specific ad id already exists


function adidexists($adid) {
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$adidexists=false;
	$query="SELECT count(*) FROM ".$tbl_ads." WHERE ad_id='$adid'";
	if (($res=mysql_query($query))) {
		$adidexists=true;
	}

	return $adidexists;
}

function categoryidexists($adcategoryid) {
	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_adcategories";
	$categoryidexists=false;
	$query="SELECT count(*) FROM ".$tbl_categories." WHERE categoryid='$adcategoryid'";
	if (($res=mysql_query($query))) {
		$categoryidexists=true;
	}

	return $categoryidexists;
}



// END FUNCTION: check if a specific ad id already exists




// START FUNCTION: get the current name of the classfieds page


function display_setup_text()
{
	$awpcpsetuptext="<h2>";
	$awpcpsetuptext.=__("Setup Process","AWPCP");
	$awpcpsetuptext.="</h2>";
	$awpcpsetuptext.="<p>";
	$awpcpsetuptext.=__("It looks like you have not yet told the system how you want your classifieds to operate.","AWPCP");
	$awpcpsetuptext.="</p>";
	$awpcpsetuptext.="<p>";
	$awpcpsetuptext.=__("Please begin by setting up the options for your site. The system needs to know a number of things about how you want to run your classifieds.","AWPCP");
	$awpcpsetuptext.="</p><a href=\"?page=Configure1&mspgs=1\">";
	$awpcpsetuptext.=__("Click here to setup your site options","AWPCP");
	$awpcpsetuptext.="</a></p>";

	return $awpcpsetuptext;
}

function get_currentpagename() {
	global $wpdb;
	$tbl_pagename = $wpdb->prefix . "awpcp_pagename";

	$tableexists=checkfortable($tbl_pagename);
	$currentpagename='';

	if(!$tableexists){
		$currentpagename='';
	}

	else {

		$query="SELECT userpagename from ".$tbl_pagename."";
		$res = $wpdb->get_results($query, ARRAY_A);
		//$res = awpcp_query($query, __LINE__);
		//while ($rsrow=mysql_fetch_row($res))
		foreach($res as $rsrow)
		{
			$currentpagename = $rsrow['userpagename'];
			//list($currentpagename)=$rsrow;
		}
	}

	return $currentpagename;

}


// END FUNCTION: query awpcp_pagename for the name being used for the classifieds site




// START FUNCTION: delete the classfied page name from database as needed


function deleteuserpageentry() {

	global $wpdb;
	$tbl_pagename = $wpdb->prefix . "awpcp_pagename";

	$query="TRUNCATE ".$tbl_pagename."";
	$res = awpcp_query($query, __LINE__);
	mysql_query($query);
}


// END FUNCTION: delete the user page entry from awpcp_pagename table




// START FUNCTION: check if the classifieds page exists in the wp posts table


function findpage($pagename,$shortcode) {

	global $wpdb,$table_prefix;
	$myreturn=false;

	$query="SELECT post_title FROM {$table_prefix}posts WHERE post_title='$pagename' AND post_content LIKE '%$shortcode%'";
	$res = awpcp_query($query, __LINE__);
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;

}


// START FUNCTION: check ad_settings to see if a particular function exists to prevent duplicate entery when updating plugin


function field_exists($field){
	global $wpdb;
	$tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";

	$tableexists=checkfortable($tbl_ad_settings);

	if($tableexists)
	{
		$query="SELECT config_value FROM  ".$tbl_ad_settings." WHERE config_option='$field'";
		$res = awpcp_query($query, __LINE__);
		if (mysql_num_rows($res))
		{
			$myreturn=true;
		}
		else
		{
			$myreturn=false;
		}

		return $myreturn;
	}
}


// END FUNCTION: check if ad_settings field exists



// START FUNCTION: a general functin to shorten text to summary or excerpt


function awpcpLimitText($Text,$Min,$Max,$MinAddChar) {
	if (strlen($Text) < $Min) {
		$Limit = $Min-strlen($Text);
		$Text .= $MinAddChar;
	}
	elseif (strlen($Text) >= $Max) {
		$words = explode(" ", $Text);
		$check=1;
		while (strlen($Text) >= $Max) {
			$c=count($words)-$check;
			$Text=substr($Text,0,(strlen($words[$c])+1)*(-1));
			$check++;
		}
	}

	return $Text;
}


// END FUNCTION: limit text


function isValidURL($url)
{
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}


function isValidEmailAddress($email) {
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		return false;
	}

	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++) {
		if
		(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
?'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
		$local_array[$i])) {
			return false;
		}
	}

	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) {
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) {
			if
			(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
?([A-Za-z0-9]+))$",
			$domain_array[$i])) {
				return false;
			}
		}
	}
	return true;
}



// START FUNCTION: function to handle automatic ad expirations


/*
 * Function run once per month to cleanup disabled ads.
 */
function doadcleanup() {
	global $wpdb;
	//If they set the 'disable instead of delete' flag, we just ignore this call altogether:
	if (get_awpcp_option('autoexpiredisabledelete') == 1) { return; }
	
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	
	// Get the IDs of the ads to be deleted (those that are disabled now)
	$query="SELECT ad_id FROM ".$tbl_ads." WHERE disabled='1'";
	$res = awpcp_query($query, __LINE__);

	$expiredid=array();
	if (mysql_num_rows($res))
	{
		while ($rsrow=mysql_fetch_row($res))
		{
			$expiredid[]=$rsrow[0];
		}
	}

	$adstodelete=join("','",$expiredid);
	$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);
	$rowcount=mysql_num_rows($res);
	for ($i=0;$i<$rowcount;$i++)
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

	$query="DELETE FROM ".$tbl_ad_photos." WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);

	// Delete the ads
	$query="DELETE FROM ".$tbl_ads." WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);
}

/*
 * Function to disable ads run hourly
 */
function doadexpirations() {

	global $wpdb,$nameofsite,$siteurl,$thisadminemail;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$awpcp_from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";
	$adexpireafter=get_awpcp_option('addurationfreemode');
	_log("Checking ad expirations");
	if ($adexpireafter == 0)
	{
		//Randomly far into the future...
		$adexpireafter=9125;
	}
	
	// Get the IDs of the ads to be deleted.  In order to account for the dynamic nature of the user selection of
	// expiration times, we need to look at the start date of the ad, ignore the end, and calculate the interval based
	// on the current setting.  This has the unfortunate effect of expiring ads if they turn it down (to say 30 days) and
	// then upping it to 90, the ads that would have been enabled will then be disabled (or deleted)
	$query="SELECT ad_id FROM ".$tbl_ads." WHERE (ad_startdate + INTERVAL $adexpireafter DAY) < CURDATE() AND disabled='0'";
	$res = awpcp_query($query, __LINE__);

	$expiredid=array();
	if (mysql_num_rows($res))
	{
		while ($rsrow=mysql_fetch_row($res))
		{
			$expiredid[]=$rsrow[0];
		}
		$adstodelete=join("','",$expiredid);
		_log("Expiring ads: " . $adstodelete);
		
		foreach ($expiredid as $adid)
		{
			$adcontact=get_adpostername($adid);
			$awpcpnotifyexpireemail=get_adposteremail($adid);
			$adtitle=get_adtitle($adid);
			$adstartdate=date("D M j Y G:i:s", get_adstartdate($adid));
			
			$awpcpadexpiredsubject=get_awpcp_option('adexpiredsubjectline');
			$awpcpadexpiredbody=get_awpcp_option('adexpiredbodymessage');
			$awpcpadexpiredbody.="<br/><br/>";
			$awpcpadexpiredbody.=__("Listing Details", "AWPCP");
			$awpcpadexpiredbody.="<br/><br/>";
			$awpcpadexpiredbody.=__("Ad Title:", "AWPCP");
			$awpcpadexpiredbody.=" $listingtitle";
			$awpcpadexpiredbody.="<br/>";
			$awpcpadexpiredbody.=__("Posted:", "AWPCP");
			$awpcpadexpiredbody.=" $adstartdate";
			$awpcpadexpiredbody.="<br/><br/>";
			$awpcpadexpiredbody.=__("Renew your ad by visiting:", "AWPCP");
			$awpcpadexpiredbody.=" $siteurl";
			$awpcpadexpiredbody.="<br/><br/>";
			
			if(get_awpcp_option('notifyofadexpiring') == '1')
			{
				//@awpcp_process_mail($awpcpsenderemail=$thisadminemail,$awpcpreceiveremail=$awpcpnotifyexpireemail,$awpcpemailsubject=$awpcpadexpiredsubject,$awpcpemailbody=$awpcpadexpiredbody,$awpcpsendername=$nameofsite,$awpcpreplytoemail=$thisadminemail);
			}
		}
		// FOR NOW and because of the bug in 1.0.6.18, we disable ads by default.
		//Disable the images
		$query="UPDATE ".$tbl_ad_photos." set disabled='1' WHERE ad_id IN ('$adstodelete')";
		$res = awpcp_query($query, __LINE__);
	
		// Disable the ads
		$query="UPDATE ".$tbl_ads." set disabled='1' WHERE ad_id IN ('$adstodelete')";
		$res = awpcp_query($query, __LINE__);
	}
}

function renewsubscription($adid)
{
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$myreturn=false;
	$query="SELECT payment_status FROM ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);
	while ($rsrow=mysql_fetch_row($res))
	{
		list($paymentstatus)=$rsrow;
	}
	if($paymentstatus != 'Cancelled')
	{
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: process auto ad expiration



// START FUNCTION: Function to check for the existence of a default category with a category ID of 1 (used with mass category deletion)


function defaultcatexists($defid) {
	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	$myreturn=false;
	$query="SELECT * FROM ".$tbl_categories." WHERE category_id='$defid'";
	$res = awpcp_query($query, __LINE__);
	if (mysql_num_rows($res)) {
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: check if default category exists



// START FUNCTION: function to create a default category with an ID of  1 in the event a default category with ID 1 does not exist


function createdefaultcategory($idtomake,$titletocallit) {

	global $wpdb;
	$tbl_categories = $wpdb->prefix . "awpcp_categories";

	$query="INSERT INTO ".$tbl_categories." SET category_name='$titletocallit',category_parent_id='0'";
	$res = awpcp_query($query, __LINE__);
	$newdefid=mysql_insert_id();

	$query="UPDATE ".$tbl_categories." SET category_id='1' WHERE category_id='$newdefid'";
	$res = awpcp_query($query, __LINE__);
}


// END FUNCTION: create default category


//////////////////////
// START FUNCTION: function to delete multiple ads at once used when admin deletes a category that contains ads but does not move the ads to a new category
//////////////////////

function massdeleteadsfromcategory($catid){

	global $wpdb,$nameofsite,$siteurl,$thisadminemail;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

	// Get the IDs of the ads to be deleted
	$query="SELECT ad_id FROM ".$tbl_ads." WHERE ad_category_id='$catid'";
	$res = awpcp_query($query, __LINE__);

	$fordeletionid=array();

	if (mysql_num_rows($res)) {

		while ($rsrow=mysql_fetch_row($res)) {
			$fordeletionid[]=$rsrow[0];

		}
		$totalusers=count($fordeletionid);
	}

	$adstodelete=join("','",$fordeletionid);
	// Delete the ad images

	$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);

	for ($i=0;$i<mysql_num_rows($res);$i++) {
		$photo=mysql_result($res,$i,0);

		if (file_exists(AWPCPUPLOADDIR.'/'.$photo)) {
			@unlink(AWPCPUPLOADDIR.'/'.$photo);
		}
		if (file_exists(AWPCPTHUMBSUPLOADDIR.'/'.$photo)) {
			@unlink(AWPCPTHUMBSUPLOADDIR.'/'.$photo);
		}
	}

	$query="DELETE FROM ".$tbl_ad_photos." WHERE ad_id IN ('$adstodelete')";
	@mysql_query($query);


	// Delete the ads

	$query="DELETE FROM ".$tbl_ads." WHERE ad_id IN ('$adstodelete')";
	@mysql_query($query);


}


// END FUNCTION: mass delete ads




// START FUNCTION: The sidebar widget to show latest sidebar ads

### Function: Init AWPCP Latest Classified Headlines Widget
function init_awpcpsbarwidget() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}

	### Function: AWPCP Latest Classified Headlines Widget
	function widget_awpcplatestads($args) {
		$output = '';
		extract($args);
		$limit=$args[0];
		$title=$args[1];

		$options = get_option('widget_awpcplatestads');
		if(!isset($limit))
		{
			$limit = htmlspecialchars(stripslashes($options['hlimit']));
		}
		if(!isset($title))
		{
			$title = htmlspecialchars(stripslashes($options['title']));
		}
		if(ads_exist())
		{
			$awpcp_sb_widget_beforecontent=get_awpcp_option('sidebarwidgetbeforecontent');
			$awpcp_sb_widget_aftercontent=get_awpcp_option('sidebarwidgetaftercontent');
			$awpcp_sb_widget_beforetitle=get_awpcp_option('sidebarwidgetbeforetitle');
			$awpcp_sb_widget_aftertitle=get_awpcp_option('sidebarwidgetaftertitle');

			if(isset($awpcp_sb_widget_beforecontent) && !empty($awpcp_sb_widget_beforecontent))
			{$awpcp_sb_widget_beforecontent="$awpcp_sb_widget_beforecontent";}
			else{$awpcp_sb_widget_beforecontent="";}

			if(isset($awpcp_sb_widget_aftercontent) && !empty($awpcp_sb_widget_aftercontent))
			{$awpcp_sb_widget_aftercontent="$awpcp_sb_widget_aftercontent";}
			else{$awpcp_sb_widget_aftercontent="";}

			if(isset($awpcp_sb_widget_beforetitle) && !empty($awpcp_sb_widget_beforetitle))
			{$awpcp_sb_widget_beforetitle="$awpcp_sb_widget_beforetitle";}
			else{$awpcp_sb_widget_beforetitle="";}

			if(isset($awpcp_sb_widget_aftertitle) && !empty($awpcp_sb_widget_aftertitle))
			{$awpcp_sb_widget_aftertitle="$awpcp_sb_widget_aftertitle";}
			else{$awpcp_sb_widget_aftertitle="";}

			if(isset($awpcp_sb_widget_beforecontent) && !empty($awpcp_sb_widget_beforecontent))
			{
				$output .= "$awpcp_sb_widget_beforecontent";
			}
			if(isset($awpcp_sb_widget_beforetitle) && !empty($awpcp_sb_widget_beforetitle))
			{
				$output .= "$awpcp_sb_widget_beforetitle";
			}

			$output .= "$title";
			if(isset($awpcp_sb_widget_aftertitle) && !empty($awpcp_sb_widget_aftertitle))
			{
				$output .= "$awpcp_sb_widget_aftertitle";
			}

			if (function_exists('awpcp_sidebar_headlines'))
			{
				$output .= '<ul>'."\n";
				$output .= awpcp_sidebar_headlines($limit, $options['showimages'], $options['showblank']);
				$output .= '</ul>'."\n";
			}

			if(isset($awpcp_sb_widget_aftercontent) && !empty($awpcp_sb_widget_aftercontent))
			{
				$output .= "$awpcp_sb_widget_aftercontent";
			}
		}
		//Echo OK here
		echo $output;
	}

	### Function: AWPCP Latest Classified Headlines Widget Options
	function widget_awpcplatestads_options() {
		$output = '';
		$options = get_option('widget_awpcplatestads');
		if (!is_array($options)) {
			$options = array('hlimit' => '10', 'title' => __('Latest Classifieds', 'AWPCP'), 'showimages' => '1', 'showblank' => '1');
		}
		if ($_POST['awpcplatestads-submit']) {
			$options['hlimit'] = intval($_POST['awpcpwid-limit']);
			$options['title'] = strip_tags($_POST['awpcpwid-title']);
			$options['showimages'] = $_POST['awpcpwid-showimages'] == '1' ? 1 : 0;
			$options['showblank'] = $_POST['awpcpwid-showblank'] == '1' ? 1 : 0;
			//$options['beforewidget'] = $_POST['awpcpwid-beforewidget'];
			//$options['afterwidget'] = $_POST['awpcpwid-afterwidget'];
			//$options['beforetitle'] = $_POST['awpcpwid-beforetitle'];
			//$options['aftertitle'] = $_POST['awpcpwid-aftertitle'];
			update_option('widget_awpcplatestads', $options);
		}
		$output .= '<p><label for="awpcpwid-title">'.__('Widget Title', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-title" size="35" name="awpcpwid-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" />';
		$output .= '<p><label for="awpcpwid-limit">'.__('Number of Items to Show', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" size="5" id="awpcpwid-limit" name="awpcpwid-limit" value="'.htmlspecialchars(stripslashes($options['hlimit'])).'" />';
		$output .= '<p><label for="awpcpwid-showimages">'.__('Show Thumbnails in Widget?', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="awpcpwid-showimages" name="awpcpwid-showimages" value="1" '. ($options['showimages'] == 1 ? 'checked=\"true\"' : '') .' />';
		$output .= '<p><label for="awpcpwid-showblank">'.__('Show \"No Image\" PNG when ad has no picture (improves layout)?', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="awpcpwid-showblank" name="awpcpwid-showblank" value="1" '. ($options['showblank'] == 1 ? 'checked=\"true\"' : '') .' />';
		//$output .= '<p><label for="awpcpwid-beforewidget">'.__('Before Widget HTML', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-beforewidget" size="35" name="awpcpwid-beforewidget" value="'.htmlspecialchars(stripslashes($options['beforewidget'])).'" />';
		//$output .= '<p><label for="awpcpwid-afterwidget">'.__('After Widget HTML<br>Exclude all quotes<br>(<del>class="XYZ"</del> => class=XYZ)', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-afterwidget" size="35" name="awpcpwid-afterwidget" value="'.htmlspecialchars(stripslashes($options['afterwidget'])).'" />';
		//$output .= '<p><label for="awpcpwid-beforetitle">'.__('Before title HTML', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-beforetitle" size="35" name="awpcpwid-beforetitle" value="'.htmlspecialchars(stripslashes($options['beforetitle'])).'" />';
		//$output .= '<p><label for="awpcpwid-aftertitle">'.__('After title HTML', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-aftertitle" size="35" name="awpcpwid-aftertitle" value="'.htmlspecialchars(stripslashes($options['aftertitle'])).'" />';
		$output .= '<input type="hidden" id="awpcplatestads-submit" name="awpcplatestads-submit" value="1" />'."\n";
		//Echo ok here:
		echo $output;
	}
	// Register Widgets
	register_sidebar_widget('AWPCP Latest Ads', 'widget_awpcplatestads');
	register_widget_control('AWPCP Latest Ads', 'widget_awpcplatestads_options', 350, 120);

}
add_action('widgets_init', 'widget_awpcp_search_init');
function widget_awpcp_search_init() {
	register_widget('AWPCP_Search_Widget');
}

function awpcp_sidebar_headlines($limit, $showimages, $showblank) {
	$output = '';
	global $wpdb,$awpcp_imagesurl;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option('permalink_structure');
	$quers=setup_url_structure($awpcppagename);
	$displayadthumbwidth=get_awpcp_option('displayadthumbwidth');
	
	if(!isset($limit) || empty ($limit)){
		$limit=10;
	}

	$query="SELECT ad_id,ad_title,ad_details FROM ".$tbl_ads." WHERE ad_title <> '' AND disabled = '0' ORDER BY ad_postdate DESC LIMIT ".$limit."";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res)) {
		$ad_id=$rsrow[0];
		$modtitle=cleanstring($rsrow[1]);
		$modtitle=add_dashes($modtitle);
		$hasNoImage = true;
		$url_showad=url_showad($ad_id);
		
		$ad_title="<a href=\"$url_showad\">".stripslashes($rsrow[1])."</a>";
		if (!$showimages) {
			//Old style, list only:
			$output .= "<li>$ad_title</li>";
		} else {
			//New style, with images and layout control:
			$awpcp_image_display="<a href=\"$url_showad\">";
			if (get_awpcp_option('imagesallowdisallow'))
			{
				$totalimagesuploaded=get_total_imagesuploaded($ad_id);
				if ($totalimagesuploaded >=1)
				{
					$awpcp_image_name=get_a_random_image($ad_id);
					if (isset($awpcp_image_name) && !empty($awpcp_image_name))
					{
						$awpcp_image_name_srccode="<img src=\"".AWPCPTHUMBSUPLOADURL."/$awpcp_image_name\" border=\"0\" width=\"$displayadthumbwidth\" alt=\"$modtitle\"/>";
						$hasNoImage = false;
					}
					else
					{
						$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
					}							
				}
				else
				{
					$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
				}
			}
			else
			{
				$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
			}
			$ad_teaser = stripslashes(substr($rsrow[2], 0, 50)) . "...";
			$read_more = "<a href=\"$url_showad\">[" . __("Read more", "AWPCP") . "]</a>";
			$awpcp_image_display.="$awpcp_image_name_srccode</a>";
			if (!$showblank && $hasNoImage) {
				//Don't put anything there
				$awpcp_image_display = '';
			}
			$output .= "<li><div class='awpcplatestbox'><div class='awpcplatestthumb'>$awpcp_image_display</div><p><h3>$ad_title</h3></p><p>$ad_teaser<br/>$read_more</p><div class='awpcplatestspacer'></div></div></li>";
		}
	}
	return $output;
}


// END FUNCTION: sidebar widget



// START FUNCTION: make sure there's not more than one page with the name of the classifieds page


function checkforduplicate($cpagename_awpcp)
{
	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$pageswithawpcpname=array();
	global $wpdb,$table_prefix;
	$totalpageswithawpcpname='';

	$query="SELECT ID FROM {$table_prefix}posts WHERE post_name = '$awpcppagename' AND post_type='post'";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		while ($rsrow=mysql_fetch_row($res))
		{
			$pageswithawpcpname[]=$rsrow[0];
		}
		$totalpageswithawpcpname=count($pageswithawpcpname);
	}

	return $totalpageswithawpcpname;
}

function checkfortotalpageswithawpcpname($awpcppage) {

	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$totalpageswithawpcpname='';

	$allpageswithawpcppagename=array();
	$pageswithawpcpname=array();
	$childpageswithawpcpname=array();

	global $wpdb,$table_prefix;
	$awpcppage = add_slashes_recursive($awpcppage);
	$query="SELECT ID FROM {$table_prefix}posts WHERE post_title='$awpcppage' AND post_name = '$awpcppagename' AND post_content LIKE '%AWPCP%' AND post_type='page'";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		while ($rsrow=mysql_fetch_row($res))
		{
			$pageswithawpcpname[]=$rsrow[0];
		}
	}

	if(!empty($pageswithawpcpname))
	{
		foreach ( $pageswithawpcpname as $pagewithawpcpname )
		{
			// Get child pages if any
			$query="SELECT ID FROM {$table_prefix}posts WHERE post_parent='$pagewithawpcpname' AND post_content LIKE '%AWPCP%'";
			$res = awpcp_query($query, __LINE__);

			if (mysql_num_rows($res))
			{
				while ($rsrow=mysql_fetch_row($res))
				{
					$childpageswithawpcpname[]=$rsrow[0];
				}
			}
		}

		if(!empty($childpageswithawpcpname))
		{
			$allpageswithawpcppagename=array_merge($pageswithawpcpname,$childpageswithawpcpname);
		}
		else
		{
			$allpageswithawpcppagename=$pageswithawpcpname;
		}
			
		$totalpageswithawpcpname=count($allpageswithawpcppagename);

		if( $totalpageswithawpcpname >= 1 )
		{
			foreach ( $allpageswithawpcppagename as $thispagewithawpcpname )
			{
				//Delete the pages
				wp_delete_post( $thispagewithawpcpname, $force_delete = true );
			}
			deleteuserpageentry($awpcppage);
		}
	}
	else
	{
		return 0;
	}

}


// END FUNCTION: make sure there's not more than one page with the name of the classifieds page



// START FUNCTION: create a drop down list containing names of ad posters


function create_ad_postedby_list()
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT DISTINCT ad_contact_name FROM ".$tbl_ads." WHERE disabled='0'";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		$output .= "<option value=\"$rsrow[0]\">$rsrow[0]</option>";
	}
	return $output;
}


// END FUNCTION: create a drop down list containing names of ad posters



// START FUNCTION: create a drop down list containing price option



function create_price_dropdownlist_min($searchpricemin)
{
	$output = '';
	$pricerangevalues=array('0','25','50','100','500','1000','2500','5000','7500','10000','25000','50000','100000','250000','500000','1000000');

	if( isset($searchpricemin) && !empty($searchpricemin) )
	{
		$theawpcplowvalue=$searchpricemin;
	}
	else
	{
		$theawpcplowvalue='';
	}
	foreach ($pricerangevalues as $pricerangevalue)
	{
		$output .= "<option value=\"$pricerangevalue\"";

		if($pricerangevalue == $theawpcplowvalue)
		{
			$output .= "selected='selected' ";
		}
		$output .= ">$pricerangevalue</option>";
	}
	return $output;
}

function create_price_dropdownlist_max($searchpricemax)
{
	$output = '';
	$pricerangevalues=array('0','25','50','100','500','1000','2500','5000','7500','10000','25000','50000','100000','250000','500000','1000000');

	if( isset($searchpricemax) && !empty($searchpricemax) )
	{
		$theawpcphighvalue=$searchpricemax;
	}
	else
	{
		$theawpcphighvalue='';
	}

	foreach ($pricerangevalues as $pricerangevalue)
	{
		$output .= "<option value=\"$pricerangevalue\"";

		if($pricerangevalue == $theawpcphighvalue)
		{
			$output .= "selected='selected' ";
		}
		$output .= ">$pricerangevalue</option>";
	}
	return $output;
}


function awpcp_array_range($from, $to, $step){

	$array = array();
	for ($x=$from; $x <= $to; $x += $step){
		$array[] = $x;
	}
	return $array;

}


function awpcp_get_max_ad_price()
{
	$query="SELECT MAX(ad_item_price) as endval FROM ".$tbl_ads."";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		$maxadprice=$rsrow[0];
	}
	return $maxadprice;
}


// END FUNCTION: create a drop down list containing price option



// START FUNCTION: create a drop down list containing cities options from saved cities in database


function create_dropdown_from_current_cities()
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$listofsavedcities=array();

	$query="SELECT DISTINCT ad_city FROM ".$tbl_ads." WHERE ad_city <> '' AND disabled = '0' ORDER by ad_city ASC";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		$listofsavedcities[]=$rsrow[0];
		$savedcitieslist=array_unique($listofsavedcities);
	}

	foreach ($savedcitieslist as $savedcity)
	{
		$output .= "<option value=\"$savedcity\">$savedcity</option>";
	}
	return $output;
}


// END FUNCTION: create a drop down list containing cities options from saved cities in database




// START FUNCTION: create a drop down list containing state options from saved states in database


function create_dropdown_from_current_states()
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$listofsavedstates=array();

	$query="SELECT DISTINCT ad_state FROM ".$tbl_ads." WHERE ad_state <> '' AND disabled = '0' ORDER by ad_state ASC";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		$listofsavedstates[]=$rsrow[0];
		$savedstateslist=array_unique($listofsavedstates);
	}

	foreach ($savedstateslist as $savedstate)
	{
		$output .= "<option value=\"$savedstate\">$savedstate</option>";
	}
	return $output;
}


// END FUNCTION: create a drop down list containing states options from saved states in database



// START FUNCTION: create a drop down list containing county/village options from saved states in database


function create_dropdown_from_current_counties()
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$listofsavedcounties=array();

	$query="SELECT DISTINCT ad_county_village FROM ".$tbl_ads." WHERE ad_county_village <> '' AND disabled = '0' ORDER by ad_county_village ASC";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		$listofsavedcounties[]=$rsrow[0];
		$savedcountieslist=array_unique($listofsavedcounties);

	}
	foreach ($savedcountieslist as $savedcounty)
	{
		$output .= "<option value=\"$savedcounty\">$savedcounty</option>";
	}
	return $output;
}


// END FUNCTION: create a drop down list containing county/village options from saved states in database



// START FUNCTION: create a drop down list containing country options from saved countries in database


function create_dropdown_from_current_countries()
{
	$output = '';
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$listofsavedcountries=array();

	$query="SELECT DISTINCT ad_country FROM ".$tbl_ads." WHERE ad_country <> '' AND disabled = '0' ORDER by ad_country ASC";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		$listofsavedcountries[]=$rsrow[0];
		$savedcountrieslist=array_unique($listofsavedcountries);

	}
	foreach ($savedcountrieslist as $savedcountry)
	{
		$output .= "<option value=\"$savedcountry\">$savedcountry</option>";
	}
	return $output;
}


// END FUNCTION: create a drop down list containing country options from saved states in database




// START FUNCTION: Check if ads table contains city data


function adstablehascities()
{

	$myreturn=false;

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_city FROM ".$tbl_ads." WHERE ad_city <> ''";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: Check if ads table contains city data



// START FUNCTION: Check if ads table contains state data


function adstablehasstates()
{

	$myreturn=false;

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_state FROM ".$tbl_ads." WHERE ad_state <> ''";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: Check if ads table contains state data



// START FUNCTION: Check if ads table contains country data


function adstablehascountries()
{

	$myreturn=false;

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_country FROM ".$tbl_ads." WHERE ad_country <> ''";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: Check if ads table contains country data



// START FUNCTION: Check if ads table contains county data


function adstablehascounties()
{

	$myreturn=false;

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_county_village FROM ".$tbl_ads." WHERE ad_county_village <> ''";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: Check if ads table contains county data





// START FUNCTION: check if there are any values entered into the price field for any ad


function price_field_has_values()
{
	$myreturn=false;

	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$query="SELECT ad_item_price FROM ".$tbl_ads." WHERE ad_item_price > '0'";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		$myreturn=true;
	}

	return $myreturn;
}


// END FUNCTION: check if there are any values entered into the price field for any ad




// START FUNCTION: get an image name associated with a specified ad ID


function get_a_random_image($ad_id)
{

	global $wpdb;
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	$awpcp_image_name='';

	$query="SELECT image_name FROM ".$tbl_ad_photos." WHERE ad_id='$ad_id' AND disabled='0' LIMIT 1";
	$res = awpcp_query($query, __LINE__);

	if (mysql_num_rows($res))
	{
		list($awpcp_image_name)=mysql_fetch_row($res);
	}

	return $awpcp_image_name;
}


// END FUNCTION: get an image name associated with a specified ad ID





// START FUNCTION: check a specific ad to see if it is disabled or enabled


function check_if_ad_is_disabled($adid) {
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$myreturn=false;
	$query="SELECT disabled FROM ".$tbl_ads." WHERE ad_id='$adid'";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res))
	{
		list($adstatusdisabled)=$rsrow;
	}
	if ($adstatusdisabled == 1)
	{
		$myreturn=true;
	}

	return $myreturn;

}


// END FUNCTION: check a specific ad to see if it is disabled or enabled


function check_ad_fee_paid($adid) {
	global $wpdb;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$adfeeispaid=false;
	$query="SELECT ad_fee_paid FROM ".$tbl_ads." WHERE ad_id='$adid'";
	while ($rsrow=mysql_fetch_row($res))
	{
		list($ad_fee_paid)=$rsrow;
	}
	if($ad_fee_paid > 0){$adfeeispaid=true;}

	return $adfeeispaid;
}


// START FUNCTION: get the currency code for price fields


function awpcp_get_currency_code()
{

	$amtcurrencycode=get_awpcp_option('displaycurrencycode');

	if(
	($amtcurrencycode == 'CAD') ||
	($amtcurrencycode == 'AUD') ||
	($amtcurrencycode == 'NZD') ||
	($amtcurrencycode == 'SGD') ||
	($amtcurrencycode == 'HKD') ||
	($amtcurrencycode == 'USD') )
	{
		$thecurrencysymbol="$";
	}

	if( ($amtcurrencycode == 'JPY') )
	{
		$thecurrencysymbol="&yen;";
	}

	if( ($amtcurrencycode == 'EUR') )
	{
		$thecurrencysymbol="&euro;";
	}

	if( ($amtcurrencycode == 'GBP') )
	{
		$thecurrencysymbol="&pound;";
	}



	if(empty($thecurrencysymbol)) {
		$thecurrencysymbol="$amtcurrencycode";
	}

	return $thecurrencysymbol;
}


// END FUNCTION: get the currency code for price fields




// START FUNCTION: Clear HTML tags

function strip_html_tags( $text )
{
	// Remove invisible content
	$text = preg_replace(
	array(
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
	// Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
	),
	array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
	),
	$text );
	return strip_tags( $text );
}


// END FUNCTION


function awpcp_process_mail($awpcpsenderemail,$awpcpreceiveremail,$awpcpemailsubject,$awpcpemailbody,$awpcpsendername,$awpcpreplytoemail)
{
	$headers =	"MIME-Version: 1.0\n" .
	"From: $awpcpsendername <$awpcpsenderemail>\n" .
	"Reply-To: $awpcpreplytoemail\n" .
	"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

	$subject = $awpcpemailsubject;

	$time = date_i18n( __('l F j, Y \a\t g:i a', "AWPCP"), current_time( 'timestamp' ) );

	$message = "

	$awpcpemailbody

	".__('Time:', 'AWPCP')." $time

	";
	_log("Processing email");
	if(wp_mail( $awpcpreceiveremail, $subject, $message, $headers ))
	{
		_log("Sent via WP");
		return 1;
	}
	elseif( awpcp_send_email($awpcpsenderemail,$awpcpreceiveremail,$awpcpemailsubject,$awpcpemailbody,true) )
	{
		_log("Sent via send_email");
		return 1;
	}
	elseif( @mail($awpcpreceiveremail, $awpcpemailsubject, $awpcpemailbody, $headers) )
	{
		_log("Sent via mail");
		return 1;
	}
	else
	{
		_log("Attempting by SMTP, all others failed");
		// None of the other email methods have worked so try the SMTP
		$awpcp_smtp_host = get_awpcp_option('smtphost');
		$awpcp_smtp_username = get_awpcp_option('smtpusername');
		$awpcp_smtp_password = get_awpcp_option('smtppassword');
			
		if( isset($awpcp_smtp_username) && !empty($awpcp_smtp_username) 
			&& isset($awpcp_smtp_password) && !empty($awpcp_smtp_password) 
			&& isset($awpcp_smtp_hostname) && !empty($awpcp_smtp_hostname))
		{
			include("Mail.php");
			$recipients = $awpcpreceiveremail;
			$mailmsg = $awpcpemailbody;
			$smtpinfo["host"] = $awpcp_smtp_host;
			$smtpinfo["port"] = "25";
			$smtpinfo["auth"] = true;
			$smtpinfo["username"] = $awpcp_smtp_username;
			$smtpinfo["password"] = $awpcp_smtp_password;
			$mail_object =& Mail::factory("smtp", $smtpinfo);

			if($mail_object->send($recipients, $headers, $mailmsg))
			{
				_log("SMTP succeeded");
				return 1;
			}
			else
			{
				_log("SMTP failed during send");
				return 0;
			}
		}
		else
		{
			_log("SMTP not configured properly, all attempts failed");
			return 0;
		}
	}

}

function is_at_least_awpcp_version($version)
{
	global $awpcp_plugin_data;
	if (version_compare($awpcp_plugin_data['Version'], $version, ">=")) {
		// you're on a later or equal version, this is good
		$ok = true;
	} else {
		// earlier version, this is bad
		$ok = false;
	}
	return $ok;
}

function awpcp_admin_sidebar($float) {
$apath = get_option('siteurl').'/wp-admin/images';
if ('' == $float) $float = 'float:right !important';
$out = <<< AWPCP
<style>
.li_link { margin-left: 10px }
.inside { padding: 5px 10px !important; }
.apostboxes { 
	background-color:#FFFFFF;
	border-color:#DFDFDF;
	-moz-border-radius:6px 6px 6px 6px;
	border-style:solid;
	border-width:1px;
	line-height:1;
	margin-bottom:20px;
	min-width:255px;
	position:relative;
	width:99.5%;
}
.apostboxes h3 { 
	background:url("$apath/gray-grad.png") repeat-x scroll left top #DFDFDF;
	text-shadow:0 1px 0 #FFFFFF;
}
</style>
<div class="postbox-container1" style="padding-right: 0.5%; $float; width: 20%; ">
    <div class="metabox-holder">	
	<div class="meta-box-sortables">

	    <div class="apostboxes">
		    <h3 class="hndle1"><span>Like this plugin?</span></h3>
		    <div class="inside">
		    <p>Why not do any or all of the following:</p>
			    <ul>
			    <li class="li_link"><a href="http://wordpress.org/extend/plugins/another-wordpress-classifieds-plugin/">Give it a good rating on WordPress.org.</a></li>
			    <li class="li_link"><a href="http://wordpress.org/extend/plugins/another-wordpress-classifieds-plugin/">Let other people know that it works with your WordPress setup.</a></li>
			    <li class="li_link"><a href="http://www.awpcp.com/premium-modules/?ref=panel">Buy a Premium Module</a></li>
			    </ul>
		    </div>
	    </div>

	    <div class="apostboxes" style="border-color:#26E600; border-width:3px;">
		    <h3 class="hndle1" style="color:#145200;"><span class="red"><strong>Get a Premium Module!</strong></span></h3>
		    <div class="inside" style="background-color:#FFFFCF">
			<ul>
			<li  class="li_link"><a style="color:#145200;" href="http://www.awpcp.com/premium-modules/extra-fields-module/?ref=panel" target="_blank">Extra 
Fields Module</a></li>
			<li  class="li_link"><a style="color:#145200;" href="http://www.awpcp.com/premium-modules/category-icons-module/?ref=panel" 
target="_blank">Category Icons Premium Module</a></li>
			<li  class="li_link"><a style="color:#145200;" href="http://www.awpcp.com/premium-modules/regions-control-module/?ref=panel" target="_blank">Regions 
Control Module</a></li>
			<li  class="li_link"><a style="color:#145200;" href="http://www.awpcp.com/premium-modules/google-checkout-module/?ref=panel" target="_blank">Google 
Checkout Payment Module</a></li>
			<li  class="li_link"><a style="color:#145200;" href="http://www.awpcp.com/premium-modules/rss-module/?ref=panel" target="_blank">RSS 
Module</a></li>
			<li  class="li_link"><a style="color:#145200;" href="http://www.awpcp.com/premium-modules/powered-by-link-removal-module/?ref=panel" 
target="_blank">Powered By Link Removal Module</a></li>
			</ul>
		    </div>
	    </div>

	    <div class="apostboxes">
		    <h3 class="hndle1"><span>Found a bug? &nbsp; Need Support?</span></h3>
		    <div class="inside">
			    <p>If you've found a bug or need support <a href="http://forum.awpcp.com/" target="_blank">visit the forums!</a></p>				
		    </div>
	    </div>

	</div>
    </div>
</div>
AWPCP;
return $out;
}

?>
