<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Another Wordpress Classifieds Plugin: This file: functions_awpcp.php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: retrieve individual options from settings table
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_awpcp_option($option) {
	global $wpdb;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";
	$myreturn=0;
	$tableexists=checkfortable($table_name4);

	if($tableexists)
	{
		$query="SELECT config_value FROM  ".$table_name4." WHERE config_option='$option'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		if (mysql_num_rows($res))
		{
			$myreturn=mysql_result($res,0,0);
		}
	}
	return $myreturn;
}

function get_awpcp_option_group_id($option) {
	global $wpdb;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";
	$myreturn=0;
	$tableexists=checkfortable($table_name4);

	if($tableexists)
	{
		$query="SELECT config_group_id FROM  ".$table_name4." WHERE config_option='$option'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		if (mysql_num_rows($res))
		{
			$myreturn=mysql_result($res,0,0);
		}
	}
	return $myreturn;
}

function get_awpcp_option_type($option) {
	global $wpdb;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";
	$myreturn=0;
	$tableexists=checkfortable($table_name4);

	if($tableexists)
	{
		$query="SELECT option_type FROM  ".$table_name4." WHERE config_option='$option'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		if (mysql_num_rows($res))
		{
			$myreturn=mysql_result($res,0,0);
		}
	}
	return $myreturn;
}

function get_awpcp_option_config_diz($option) {
	global $wpdb;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";
	$myreturn=0;
	$tableexists=checkfortable($table_name4);

	if($tableexists)
	{
		$query="SELECT config_diz FROM  ".$table_name4." WHERE config_option='$option'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
		if (mysql_num_rows($res))
		{
			$myreturn=mysql_result($res,0,0);
		}
	}
	return $myreturn;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_is_classifieds()
{
	global $post,$table_prefix;
	$awpcppageid=$post->ID;


	$classifiedspagecontent="[AWPCPCLASSIFIEDSUI]";


	$query="SELECT post_content FROM {$table_prefix}posts WHERE ID='$awpcppageid' AND post_type='page' AND post_status='publish'";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

	while ($rsrow=mysql_fetch_row($res))
	{
	 	list($thepostcontentvalue)=$rsrow;
	}

	if(strcasecmp($thepostcontentvalue, $classifiedspagecontent) == 0)
	{
		$istheclassifiedspage=true;
	}
	else
	{
		$istheclassifiedspage=false;
	}

	return $istheclassifiedspage;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the user is an admin
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function checkifisadmin() {

	global $current_user;
	get_currentuserinfo();

$thisuserlevel=$current_user->user_level;

if(!isset($thisuserlevel) || empty($thisuserlevel)) {

if(is_admin()) {

	// This is assumptive and here because some users
	// run plugins that alter the wordpress user_level
	// structure thereby rendering $current_user->user_level
	// null and void

	$thisuserlevel = "10";

	}
}

	if($thisuserlevel == '10'){
		$isadmin=1;
	} else { $isadmin = 0; }

return $isadmin;

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the admin has setup any listing fee options
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function adtermsset(){

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name2."";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_2co_prodid($adterm_id) {

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

$twoco_pid='';

$query="SELECT twoco_pid from ".$table_name2." WHERE adterm_id='$adterm_id'";
if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res))
	{
 		list($twoco_pid)=$rsrow;
	}

	return $twoco_pid;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the admin has setup some categories
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function categoriesexist(){

global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";

	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name1."";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function adtermidinuse($adterm_id)
{

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name3." WHERE adterm_id='$adterm_id'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Count the total number of ads in the  system
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function countlistings(){

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

	$totallistings='';

	$query="SELECT count(*) FROM ".$table_name3."";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res)) {
	 	list($totallistings)=$rsrow;
	}
	return $totallistings;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Count the total number of categories
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function countcategories(){

global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";

	$totalcategories='';

	$query="SELECT count(*) FROM ".$table_name1."";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res)) {
	 	list($totalcategories)=$rsrow;
	}
	return $totalcategories;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Count parent categories
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function countcategoriesparents(){

global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";

	$totalparentcategories='';
	$query="SELECT count(*) FROM ".$table_name1." WHERE category_parent_id='0'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res)) {
	 	list($totalparentcategories)=$rsrow;
	}
	return $totalparentcategories;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Count children categories
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function countcategorieschildren(){

global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";

	$totalchildrencategories='';
	$query="SELECT count(*) FROM ".$table_name1." WHERE category_parent_id!='0'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res)) {
	 	list($totalchildrencategories)=$rsrow;
	}
	return $totalchildrencategories;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get number of images allowed per ad term id
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_numimgsallowed($adtermid){
global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";
$imagesallowed='';
$query="SELECT imagesallowed FROM ".$table_name2." WHERE adterm_id='$adtermid'";
if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res)) {
	list($imagesallowed)=$rsrow;
	}
	return $imagesallowed;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION check if ad has entry in adterm ID field in the event admin switched back to free mode after previously running in paid mode
// this way user continues to be allowed number of images allowed per the ad term ID
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function ad_term_id_set($adid)
{
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$myreturn=false;

			 $query="SELECT adterm_id from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adterm_id)=$rsrow;
				}
				if($adterm_id > 0)
				{
					$myreturn=true;
				}

	return $myreturn;;


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION check if user has paid for ad in event admin switched back to free mode after previously running in paid mode
// this way if user paid for ad user continues to be allowed number of images paid for
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check to see how many images an ad is currently using
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_total_imagesuploaded($ad_id) {

global $wpdb;
$table_name5 = $wpdb->prefix . "awpcp_adphotos";

$totalimagesuploaded='';

	$query="SELECT count(*) FROM ".$table_name5." WHERE ad_id='$ad_id'";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	while ($rsrow=mysql_fetch_row($res)) {
	 	list($totalimagesuploaded)=$rsrow;
	}
	return $totalimagesuploaded;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the total number of days an ad term last based on term ID value
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_num_days_in_term($adtermid) {
$numdaysinterm='';
global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

			 $query="SELECT rec_period from ".$table_name2." WHERE adterm_id='$adtermid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($numdaysinterm)=$rsrow;
				}
	return $numdaysinterm;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the id for the ad term based on having the ad ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adterm_id($adid) {

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$adterm_id='';

			 $query="SELECT adterm_id from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adterm_id)=$rsrow;
				}
	return $adterm_id;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad term name for the ad term based on having the ad term ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adterm_name($adterm_id) {

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

$adterm_name='';

			 $query="SELECT adterm_name from ".$table_name2." WHERE adterm_id='$adterm_id'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adterm_name)=$rsrow;
				}
	return $adterm_name;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad recperiod based on having the ad term ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_fee_recperiod($adterm_id) {

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

$recperiod='';

			 $query="SELECT rec_period from ".$table_name2." WHERE adterm_id='$adterm_id'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($recperiod)=$rsrow;
				}
	return $recperiod;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad posters name based on having the ad ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adpostername($adid) {

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$adpostername='';
			 $query="SELECT ad_contact_name from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adpostername)=$rsrow;
				}

	return $adpostername;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad posters access key based on given ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adkey($adid) {

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$adkey='';

			 $query="SELECT ad_key from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adkey)=$rsrow;
				}
	return $adkey;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad title based on having the ad email
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adcontactbyem($email) {
$adcontact='';
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

			 $query="SELECT ad_contact_name from ".$table_name3." WHERE ad_contact_email='$email'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adcontact)=$rsrow;
				}
	return $adcontact;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad posters name based on having the ad email
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adtitlebyem($email) {
$adtitle='';
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

			 $query="SELECT ad_title from ".$table_name3." WHERE ad_contact_email='$email'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adtitle)=$rsrow;
				}
	return $adtitle;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad posters email based on having the ad ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adposteremail($adid) {

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$adposteremail='';

			 $query="SELECT ad_contact_email from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adposteremail)=$rsrow;
				}
	return $adposteremail;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the number of times an ad has been viewed
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_numtimesadviewd($adid)
{

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$numtimesadviewed='';

			 $query="SELECT ad_views from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($numtimesadviewed)=$rsrow;
				}
	return $numtimesadviewed;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Get the number of times an ad has been viewed
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad title based on having the ad ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adtitle($adid) {

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

$adtitle='';

			 $query="SELECT ad_title from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adtitle)=$rsrow;
				}
	return $adtitle;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the ad term fee amount for the ad term based on having the ad term ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_adfee_amount($adterm_id) {

global $wpdb;
$table_name2 = $wpdb->prefix . "awpcp_adfees";

$adterm_amount='';

			 $query="SELECT amount from ".$table_name2." WHERE adterm_id='$adterm_id'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($adterm_amount)=$rsrow;
				}
	return $adterm_amount;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get ad term fee amount based on ad term ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Create list of top level categories for admin category management
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	function get_categorynameid($cat_id = 0,$cat_parent_id= 0,$exclude)
	{

		global $wpdb;
		$table_name1 = $wpdb->prefix . "awpcp_categories";

		if(isset($exclude) && !empty($exclude))
		{
			$excludequery="AND category_id !='$exclude'";
		}

	 	$catnid=$wpdb->get_results("select category_id as cat_ID, category_parent_id as cat_parent_ID, category_name as cat_name from ".$table_name1." WHERE category_parent_id='0' AND category_name <> '' $excludequery");

	 	foreach($catnid as $categories)
	 	{

	  	if($categories->cat_ID == $cat_parent_id)
	   	{
	   		$optionitem .= "<option selected value='$categories->cat_ID'>$categories->cat_name</option>";
	   	}
	  	else
	   	{
	   		$optionitem .= "<option value='$categories->cat_ID'>$categories->cat_name</option>";
	   	}

	 	}

	 	return $optionitem;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create list of top level categories for admin category management
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Create the list with both parent and child categories selection for ad post form
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_categorynameidall($cat_id = 0)
		{

			global $wpdb;
			$table_name1 = $wpdb->prefix . "awpcp_categories";
			$optionitem='';

			// Start with the main categories

			$query="SELECT category_id,category_name FROM ".$table_name1." WHERE category_parent_id='0' and category_name <> '' ORDER BY category_name ASC";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

					while ($rsrow=mysql_fetch_row($res)) {

						$cat_ID=$rsrow[0];
						$cat_name=$rsrow[1];

						$opstyle="style=\"background-color:#eeeeee;margin-bottom:3px;\"";

						if($cat_ID == $cat_id)
						{
							$maincatoptionitem = "<option $opstyle selected value='$cat_ID'>$cat_name</option>";
						}
						else {
							$maincatoptionitem = "<option $opstyle value='$cat_ID'>$cat_name</option>";
						}

						$optionitem.="$maincatoptionitem";

						// While still looping through main categories get any sub categories of the main category

						$maincatid=$cat_ID;

		   					$query="SELECT category_id,category_name FROM ".$table_name1." WHERE category_parent_id='$maincatid' ORDER BY category_name ASC";
							if (!($res2=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

							while ($rsrow2=mysql_fetch_row($res2)) {


								$subcat_ID=$rsrow2[0];
								$subcat_name=$rsrow2[1];

								if($subcat_ID == $cat_id)
								{
									$subcatoptionitem = "<option selected value='$subcat_ID'>$subcat_name</option>";
								}
								else {
									$subcatoptionitem = "<option  value='$subcat_ID'>$subcat_name</option>";
								}

								$optionitem.="$subcatoptionitem";
							}
		   			}

		 	return $optionitem;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create drop down list of categories for ad post form
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the category name
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_adcatname($cat_ID){

			global $wpdb;
			$table_name1 = $wpdb->prefix . "awpcp_categories";

			if(isset($cat_ID) && (!empty($cat_ID))){
			 $query="SELECT category_name from ".$table_name1." WHERE category_id='$cat_ID'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($cname)=$rsrow;
				}
			}
			return $cname;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get the category name
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the country associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_adcountryvalue($adid){

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";

			$theadcountry='';

			if(isset($adid) && (!empty($adid))){
			 $query="SELECT ad_country from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($theadcountry)=$rsrow;
				}
			}
			return $theadcountry;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Retrieve the country associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the state associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_adstatevalue($adid){

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";

			$theadstate='';

			if(isset($adid) && (!empty($adid))){
			 $query="SELECT ad_state from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($theadstate)=$rsrow;
				}
			}
			return $theadstate;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Retrieve the state associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the city associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_adcityvalue($adid){

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";

			$theadcity='';

			if(isset($adid) && (!empty($adid))){
			 $query="SELECT ad_city from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($theadcity)=$rsrow;
				}
			}
			return $theadcity;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Retrieve the city associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the city associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_adcountyvillagevalue($adid){

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";

			$theadcountyvillage='';

			if(isset($adid) && (!empty($adid))){
			 $query="SELECT ad_county_village from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($theadcountyvillage)=$rsrow;
				}
			}
			return $theadcountyvillage;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Retrieve the city associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the category associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_adcategory($adid){

			global $wpdb;
			$table_name3 = $wpdb->prefix . "awpcp_ads";

			$theadcategoryid='';

			if(isset($adid) && (!empty($adid))){
			 $query="SELECT ad_category_id from ".$table_name3." WHERE ad_id='$adid'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($theadcategoryid)=$rsrow;
				}
			}
			return $theadcategoryid;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Retrieve the category associated with a specific ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the parent category name
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		function get_adparentcatname($cat_ID){

			global $wpdb;
			$table_name1 = $wpdb->prefix . "awpcp_categories";
			$cname='';

			if($cat_ID == '0')
			{
				$cname="Top Level Category";
			}

			else
			{

				if(isset($cat_ID) && (!empty($cat_ID)))
				{
			 		$query="SELECT category_name from ".$table_name1." WHERE category_id='$cat_ID'";
			 		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

 						while ($rsrow=mysql_fetch_row($res))
 						{
 							list($cname)=$rsrow;
						}
				}
			}
			return $cname;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get the name of the category parent
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Retrieve the parent category ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function get_cat_parent_ID($cat_ID){

			global $wpdb;
			$table_name1 = $wpdb->prefix . "awpcp_categories";

			if(isset($cat_ID) && (!empty($cat_ID))){
			 $query="SELECT category_parent_id from ".$table_name1." WHERE category_id='$cat_ID'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res)) {
 					list($cpID)=$rsrow;
				}
			}
			return $cpID;
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get the ID or the category parent
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the transaction ID coming back from paypal or 2checkout is a duplicate
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function isdupetransid($transid){

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name3." WHERE ad_transaction_id='$transid'";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if a transaction ID from paypal or 2checkout is already in the system
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if there are any ads in the system
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function ads_exist() {
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name3."";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if any ads exist in the system
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if there are any ads in a specified category
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function ads_exist_cat($catid) {
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name3." WHERE ad_category_id='$catid' OR ad_category_parent_id='$catid'";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if a category has ads
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the category has children
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function category_has_children($catid) {
global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";
	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name1." WHERE category_parent_id='$catid'";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if a category has children
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if the category is a child
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function category_is_child($catid) {
global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";
	$myreturn=false;

	$query="SELECT category_parent_id FROM ".$table_name1." WHERE category_id='$catid'";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
		while ($rsrow=mysql_fetch_row($res)) {
 			list($cparentid)=$rsrow;
 			if( $cparentid != '0' )
 			{
 				$myreturn=true;
 			}
		}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if a category is a child
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check how many ads a category contains
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function total_ads_in_cat($catid) {
global $wpdb,$hasregionsmodule;
$table_name3 = $wpdb->prefix . "awpcp_ads";
	$totaladsincat='';


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

	$query="SELECT count(*) FROM ".$table_name3." WHERE (ad_category_id='$catid' OR ad_category_parent_id='$catid') AND disabled = '0' $filter";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
 	while ($rsrow=mysql_fetch_row($res)) {
 		list($totaladsincat)=$rsrow;
	}
	return $totaladsincat;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check how many ads are in a category
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if there are any ads in the system
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function images_exist() {
global $wpdb;
$table_name5 = $wpdb->prefix . "awpcp_ads";
	$myreturn=false;
	$query="SELECT count(*) FROM ".$table_name5."";
	if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
	if (mysql_num_rows($res) && mysql_result($res,0,0)) {
		$myreturn=true;
	}
	return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Check if images exist in system
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Eemove unwanted characters from string and setup for use with search engine friendly urls
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: remove unwanted characters from string to be used in URL for search engine friendliness
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: replace underscores with dashes for search engine friendly urls
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function add_dashes($text) {
$text=str_replace("_","-",$text);
return $text;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: replace underscores with dashes for search engine friendly urls
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get the page ID when the page name is known
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Get the id of a page by its name
function awpcp_get_page_id($awpcppagename){
	global $wpdb;
	$awpcpwppostpageid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$awpcppagename'");
	return $awpcpwppostpageid;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Get the ID from wordpress posts table where the post_name is known
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the page guid
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function awpcp_get_guid($awpcpshowadspageid){
	global $wpdb;
	$awpcppageguid = $wpdb->get_var("SELECT guid FROM $wpdb->posts WHERE ID ='$awpcpshowadspageid'");
	return $awpcppageguid;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Get the page guid
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Get the order by setting for ad listings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Get the orderby setting for ad listings
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: setup the structure of the URLs based on if permalinks are on and SEO urls are turned on
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: setup structure of URLs based on if permalinks are on and SEO urls are turned on
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

					if( get_awpcp_option('seofriendlyurls') )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_showad="$quers/$showadspagename/$ad_id/$modtitle";
						}
						else
						{
							$awpcp_showad_pageid=awpcp_get_page_id($showadspagename);
							$url_showad="$quers/?page_id=$awpcp_showad_pageid&id=$ad_id";
						}
					}
					elseif(!(get_awpcp_option('seofriendlyurls') ) )
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

					if( get_awpcp_option('seofriendlyurls') )
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
							$url_placead="$quers/$placeadpagename";
						}
						else
						{
							$url_placead="$quers/?page_id=$awpcp_placead_pageid";
						}
					}
					elseif(!(get_awpcp_option('seofriendlyurls') ) )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_placead="$quers/$placeadpagename";
						}
						else
						{
							$url_placead="$quers/?page_id=$awpcp_placead_pageid";
						}
					}

		return $url_placead;
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
							$url_searchads="$quers/$searchadspagename";
						}
						else
						{
							$url_searchads="$quers/?page_id=$awpcp_searchads_pageid";
						}
					}
					elseif(!(get_awpcp_option('seofriendlyurls') ) )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_searchads="$quers/$searchadspagename";
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
							$url_editad="$quers/$editadpagename";
						}
						else
						{
							$url_editad="$quers/?page_id=$awpcp_editad_pageid";
						}
					}
					elseif(!(get_awpcp_option('seofriendlyurls') ) )
					{
						if(isset($permastruc) && !empty($permastruc))
						{
							$url_editad="$quers/$editpagename";
						}
						else
						{
							$url_editad="$quers/?page_id=$awpcp_editad_pageid";
						}
					}

		return $url_editad;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get the parent_id of the post
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_page_parent_id($awpcpwppostpageid){
	global $wpdb;
	$awpcppageparentid = $wpdb->get_var("SELECT post_parent FROM $wpdb->posts WHERE ID = '$awpcpwppostpageid'");
	return $awpcppageparentid;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get the parent id of a wordpress post where the post ID is known
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get the name of a wordpress entry from table posts where the parent id is present
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_awpcp_parent_page_name($awpcppageparentid) {

	global $wpdb;
	$awpcpparentpagename = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID = '$awpcppageparentid'");
	return $awpcpparentpagename;


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get the name of a wordpress wp_post entry where the ID of the post parent is present
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: check if a specific database table exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function checkfortable($table) {

		$tableexists=false;
		$query="SELECT count(*) FROM ".$table."";
		if (($res=mysql_query($query))) {
			$tableexists=true;
		}

		return $tableexists;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if a specific database table exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: add field config_group_id to table adsettings v 1.0.5.6 update specific
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function add_config_group_id($cvalue,$coption)
{
	global $wpdb;
	$table_name4 = $wpdb->prefix . "awpcp_adsettings";

	$query="UPDATE ".$table_name4." SET config_group_id='$cvalue' WHERE config_option='$coption'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: add field config_group_id to table adsettings v 1.0.5.6 update specific
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: check if a specific ad id already exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function adidexists($adid) {
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";
		$adidexists=false;
		$query="SELECT count(*) FROM ".$table_name3." WHERE ad_id='$adid'";
		if (($res=mysql_query($query))) {
			$adidexists=true;
		}

		return $adidexists;
}

function categoryidexists($adcategoryid) {
global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_adcategories";
		$categoryidexists=false;
		$query="SELECT count(*) FROM ".$table_name1." WHERE categoryid='$adcategoryid'";
		if (($res=mysql_query($query))) {
			$categoryidexists=true;
		}

		return $categoryidexists;
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if a specific ad id already exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get the current name of the classfieds page
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
$table_name6 = $wpdb->prefix . "awpcp_pagename";

$tableexists=checkfortable($table_name6);
$currentpagename='';

		if(!$tableexists){
			$currentpagename='';
		}

		else {

			 $query="SELECT userpagename from ".$table_name6."";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
 				while ($rsrow=mysql_fetch_row($res))
 				{
 					list($currentpagename)=$rsrow;
				}
		}

			return $currentpagename;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: query awpcp_pagename for the name being used for the classifieds site
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: delete the classfied page name from database as needed
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function deleteuserpageentry() {

global $wpdb;
$table_name6 = $wpdb->prefix . "awpcp_pagename";

			 $query="TRUNCATE ".$table_name6."";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
			 mysql_query($query);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: delete the user page entry from awpcp_pagename table
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: check if the classifieds page exists in the wp posts table
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function findpage($pagename,$shortcode) {

global $wpdb,$table_prefix;
$myreturn=false;

			 $query="SELECT post_title FROM {$table_prefix}posts WHERE post_title='$pagename' AND post_content='$shortcode'";
			 if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
				if (mysql_num_rows($res) && mysql_result($res,0,0)) {
					$myreturn=true;
			}
			return $myreturn;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if classifieds page exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function printmessage($message){

	if(isset($message) && !empty($message)){
		echo "$message";
		die;
	}
	else {
		echo "There is a small problem. You have just completed a task but the system is confused about how to respond as it has received no message to relay to you.Please check to make sure you successfully completed what you were trying to do";
		die;
	}

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: check ad_settings to see if a particular function exists to prevent duplicate entery when updating plugin
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function field_exists($field){
global $wpdb;
$table_name4 = $wpdb->prefix . "awpcp_adsettings";

$tableexists=checkfortable($table_name4);

		if($tableexists)
		{
			 $query="SELECT config_value FROM  ".$table_name4." WHERE config_option='$field'";
			 	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if ad_settings field exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: a general functin to shorten text to summary or excerpt
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: limit text
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function isValidURL($url)
{
 return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: function to handle automatic ad expirations
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function doadexpirations(){

	global $wpdb,$nameofsite,$siteurl,$thisadminemail;
	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";
	$headers="From: $thisadminemail";

	// Get the IDs of the ads to be deleted
	$query="SELECT ad_id FROM ".$table_name3." WHERE ad_enddate < CURDATE()";
	if (!($res=mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	$expiredid=array();

	if (mysql_num_rows($res)) {

	 	while ($rsrow=mysql_fetch_row($res)) {
 			$expiredid[]=$rsrow[0];

		}
		$totalusers=count($expiredid);
	}

				$adstodelete=join("','",$expiredid);
				// Delete the ad images

					$query="SELECT image_name FROM ".$table_name5." WHERE ad_id IN ('$adstodelete')";
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

					$query="DELETE FROM ".$table_name5." WHERE ad_id IN ('$adstodelete')";
					if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}


					// Send out an email notifying users that their ad has been deleted if notify is on

					if(get_awpcp_option('notifyofadexpiring') == '1'){

							foreach ($expiredid as $adid) {

								$adcontact=get_adpostername($adid);
								$email=get_adposteremail($adid);
								$adtitle=get_adtitle($adid);

								$subject="Your classifieds listing at $nameofsite has expired";
								$body="Dear $adcontact<br><br>";
								$body.="This is an automated notification that your ad titled <b>$adtitle</b> posted at $nameofsite has expired.";
								$body.="$siteurl";

							//mail($email,$subject,$body,$headers);
							send_email($thisadminemail,$email,$subject,$body,true);

							}



					}

					// Delete the ads

					$query="DELETE FROM ".$table_name3." WHERE ad_id IN ('$adstodelete')";
					@mysql_query($query);

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: process auto ad expiration
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Function to check for the existence of a default category with a category ID of 1 (used with mass category deletion)
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function defaultcatexists($defid) {



global $wpdb;
$table_name1 = $wpdb->prefix . "awpcp_categories";

	$myreturn=false;
		$query="SELECT * FROM ".$table_name1." WHERE category_id='$defid'";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}
		if (mysql_num_rows($res)) {
			$myreturn=true;
		}

	return $myreturn;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if default category exists
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: function to create a default category with an ID of  1 in the event a default category with ID 1 does not exist
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function createdefaultcategory($idtomake,$titletocallit) {

		global $wpdb;
		$table_name1 = $wpdb->prefix . "awpcp_categories";

		$query="INSERT INTO ".$table_name1." SET category_name='$titletocallit',category_parent_id='0'";
	 	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	 	$newdefid=mysql_insert_id();

	 	$query="UPDATE ".$table_name1." SET category_id='1' WHERE category_id='$newdefid'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create default category
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: function to delete multiple ads at once used when admin deletes a category that contains ads but does not move the ads to a new category
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function massdeleteadsfromcategory($catid){

	global $wpdb,$nameofsite,$siteurl,$thisadminemail;
	$table_name3 = $wpdb->prefix . "awpcp_ads";
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	// Get the IDs of the ads to be deleted
	$query="SELECT ad_id FROM ".$table_name3." WHERE ad_category_id='$catid'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	$fordeletionid=array();

	if (mysql_num_rows($res)) {

	 	while ($rsrow=mysql_fetch_row($res)) {
 			$fordeletionid[]=$rsrow[0];

		}
		$totalusers=count($fordeletionid);
	}

				$adstodelete=join("','",$fordeletionid);
				// Delete the ad images

					$query="SELECT image_name FROM ".$table_name5." WHERE ad_id IN ('$adstodelete')";
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

					$query="DELETE FROM ".$table_name5." WHERE ad_id IN ('$adstodelete')";
					@mysql_query($query);


					// Delete the ads

					$query="DELETE FROM ".$table_name3." WHERE ad_id IN ('$adstodelete')";
					@mysql_query($query);


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: mass delete ads
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: The sidebar widget to show latest sidebar ads
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
### Function: Init AWPCP Latest Classified Headlines Widget
function init_awpcpsbarwidget() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}

	### Function: AWPCP Latest Classified Headlines Widget
	function widget_awpcplatestads($args) {
		extract($args);
		$limit=$args[0];
		$title=$args[1];
		if(!isset($before_widget))
		{
			$before_widget=$args[2];
		}
		if(!isset($after_widget))
		{
			$after_widget=$args[3];
		}
		if(!isset($before_title))
		{
			$before_title=$args[4];
		}
		if(!isset($after_title))
		{
			$after_title=$args[5];
		}

		if(!isset($limit) && !isset($title))
		{
			$options = get_option('widget_awpcplatestads');
			$title = htmlspecialchars(stripslashes($options['title']));
			$limit = htmlspecialchars(stripslashes($options['hlimit']));
			if(!isset($before_widget))
			{
				$before_widget=$options['beforewidget'];
			}
			if(!isset($after_widget))
			{
				$after_widget=$options['afterwidget'];
			}
			if(!isset($before_title))
			{
				$before_title=$options['beforetitle'];
			}
			if(!isset($after_title))
			{
				$after_title=$options['aftertitle'];
			}
		}
		if(ads_exist())
		{
			if(isset($before_widget) && !empty($before_widget))
			{
				echo "$before_widget";
			}
			if(isset($before_title) && !empty($before_title))
			{
				echo "$before_title";
			}
			echo "$title";

			if(isset($after_title) && !empty($after_title))
			{
				echo "$after_title";
			}
			if (function_exists('awpcp_sidebar_headlines'))
			{
				echo '<ul>'."\n";
				awpcp_sidebar_headlines($limit);
				echo '</ul>'."\n";
			}

			if(isset($after_widget) && !empty($after_widget))
			{
				echo "$after_widget";
			}
		}
	}

	### Function: AWPCP Latest Classified Headlines Widget Options
	function widget_awpcplatestads_options() {
		$options = get_option('widget_awpcplatestads');
		if (!is_array($options)) {
			$options = array('hlimit' => '10', 'title' => __('Latest Classifieds', 'wp-awpcplatestads'), 'beforewidget' => '', 'afterwidget' => '', 'beforetitle' => '', 'aftertitle' => '');
		}
		if ($_POST['awpcplatestads-submit']) {
			$options['hlimit'] = intval($_POST['awpcpwid-limit']);
			$options['title'] = strip_tags($_POST['awpcpwid-title']);
			$options['beforewidget'] = $_POST['awpcpwid-beforewidget'];
			$options['afterwidget'] = $_POST['awpcpwid-afterwidget'];
			$options['beforetitle'] = $_POST['awpcpwid-beforetitle'];
			$options['aftertitle'] = $_POST['awpcpwid-aftertitle'];
			update_option('widget_awpcplatestads', $options);
		}
		echo '<p><label for="awpcpwid-title">'.__('Widget Title', 'wp-awpcplatestads').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-title" size="35" name="awpcpwid-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" />';
		echo '<p><label for="awpcpwid-limit">'.__('Number of headlines to Show', 'wp-awpcplatestads').':</label>&nbsp;&nbsp;&nbsp;<input type="text" size="5" id="awpcpwid-limit" name="awpcpwid-limit" value="'.htmlspecialchars(stripslashes($options['hlimit'])).'" />';
		echo '<p><label for="awpcpwid-beforewidget">'.__('Before Widget HTML', 'wp-awpcplatestads').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-beforewidget" size="35" name="awpcpwid-beforewidget" value="'.htmlspecialchars(stripslashes($options['beforewidget'])).'" />';
		echo '<p><label for="awpcpwid-afterwidget">'.__('After Widget HTML<br>Exclude all quotes<br>(<del>class="XYZ"</del> => class=XYZ)', 'wp-awpcplatestads').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-afterwidget" size="35" name="awpcpwid-afterwidget" value="'.htmlspecialchars(stripslashes($options['afterwidget'])).'" />';
		echo '<p><label for="awpcpwid-beforetitle">'.__('Before title HTML', 'wp-awpcplatestads').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-beforetitle" size="35" name="awpcpwid-beforetitle" value="'.htmlspecialchars(stripslashes($options['beforetitle'])).'" />';
		echo '<p><label for="awpcpwid-aftertitle">'.__('After title HTML', 'wp-awpcplatestads').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-aftertitle" size="35" name="awpcpwid-aftertitle" value="'.htmlspecialchars(stripslashes($options['aftertitle'])).'" />';



		echo '<input type="hidden" id="awpcplatestads-submit" name="awpcplatestads-submit" value="1" />'."\n";
	}

	// Register Widgets
	register_sidebar_widget('AWPCPClassifieds', 'widget_awpcplatestads');
	register_widget_control('AWPCPClassifieds', 'widget_awpcplatestads_options', 350, 120);

}

function awpcp_sidebar_headlines($limit) {

global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";


$awpcppage=get_currentpagename();
$awpcppagename = sanitize_title($awpcppage, $post_ID='');
$permastruc=get_option('permalink_structure');
$quers=setup_url_structure($awpcppagename);

if(!isset($limit) || empty ($limit)){
$limit=10;}

			$query="SELECT ad_id,ad_title FROM ".$table_name3." WHERE ad_title <> '' AND disabled = '0' ORDER BY ad_postdate DESC LIMIT ".$limit."";
			if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

			while ($rsrow=mysql_fetch_row($res)) {
				$ad_id=$rsrow[0];
				$modtitle=cleanstring($rsrow[1]);
				$modtitle=add_dashes($modtitle);

						$url_showad=url_showad($ad_id);

						$ad_title="<a href=\"$url_showad\">".$rsrow[1]."</a>";


			echo "<li>$ad_title</li>";

			}

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: sidebar widget
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: make sure there's not more than one page with the name of the classifieds page
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function checkforduplicate($cpagename_awpcp)
{

	$awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');

	$pageswithawpcpname=array();
	global $wpdb,$table_prefix;

	$query="SELECT ID FROM {$table_prefix}posts WHERE post_name = '$awpcppagename' AND post_type='post'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

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

	$pageswithawpcpname=array();
	global $wpdb,$table_prefix;

	$query="SELECT ID FROM {$table_prefix}posts WHERE post_title='$awpcppage' AND post_name = '$awpcppagename' AND post_content LIKE '%AWPCP%' AND post_type='post'";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

	if (mysql_num_rows($res))
	{
	 	while ($rsrow=mysql_fetch_row($res))
	 	{
	 		$pageswithawpcpname[]=$rsrow[0];
	 	}
	 	$totalpageswithawpcpname=count($pageswithawpcpname);
	}

	if( $totalpageswithawpcpname >= 1 )
	{

		foreach ( $pageswithawpcpname as $pagewithawpcpname )
		{

			//Delete the pages
			$query="DELETE FROM {$table_prefix}posts WHERE ID = '$pagewithawpcpname' AND post_type='post' OR (post_parent='$pagewithawpcpname' AND post_content LIKE '%AWPCP%')";
			@mysql_query($query);

			//$query="DELETE FROM {$table_prefix}postmeta WHERE post_id = '$pagewithawpcpname'";
			//@mysql_query($query);

			//$query="DELETE FROM {$table_prefix}comments WHERE comment_post_ID = '$pagewithawpcpname'";
			//@mysql_query($query);

		}

			deleteuserpageentry($awpcppage);

	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: make sure there's not more than one page with the name of the classifieds page
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: create a drop down list containing names of ad posters
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create_ad_postedby_list()
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";


		$query="SELECT DISTINCT ad_contact_name FROM ".$table_name3."";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
		 		echo "<option value=\"$rsrow[0]\">$rsrow[0]</option>";
			}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create a drop down list containing names of ad posters
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: create a drop down list containing price option
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function create_price_dropdownlist_min($searchpricemin)
{

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
				echo "<option value=\"$pricerangevalue\"";

				if($pricerangevalue == $theawpcphighvalue)
				{
					echo "selected ";
				}
					echo ">$pricerangevalue</option>";
			}
}

function create_price_dropdownlist_max($searchpricemax)
{

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

				echo "<option value=\"$pricerangevalue\"";

				if($pricerangevalue == $theawpcphighvalue)
				{
					echo "selected ";
				}
					echo ">$pricerangevalue</option>";
		 	}
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

		$query="SELECT MAX(ad_item_price) as endval FROM ".$table_name3."";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

			while ($rsrow=mysql_fetch_row($res))
			{


				$maxadprice=$rsrow[0];

			}

			return $maxadprice;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create a drop down list containing price option
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: create a drop down list containing cities options from saved cities in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create_dropdown_from_current_cities()
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";


	$listofsavedcities=array();

		$query="SELECT DISTINCT ad_city FROM ".$table_name3." WHERE ad_city <> ''  ORDER by ad_city ASC";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$listofsavedcities[]=$rsrow[0];

				$savedcitieslist=array_unique($listofsavedcities);
			}

			foreach ($savedcitieslist as $savedcity)
			{
				echo "<option value=\"$savedcity\">$savedcity</option>";
		 	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create a drop down list containing cities options from saved cities in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: create a drop down list containing state options from saved states in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create_dropdown_from_current_states()
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

	$listofsavedstates=array();

		$query="SELECT DISTINCT ad_state FROM ".$table_name3." WHERE ad_state <> ''  ORDER by ad_state ASC";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$listofsavedstates[]=$rsrow[0];
				$savedstateslist=array_unique($listofsavedstates);
			}

					foreach ($savedstateslist as $savedstate)
					{
		 				echo "<option value=\"$savedstate\">$savedstate</option>";
		 			}


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create a drop down list containing states options from saved states in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: create a drop down list containing county/village options from saved states in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create_dropdown_from_current_counties()
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

	$listofsavedcounties=array();

		$query="SELECT DISTINCT ad_county_village FROM ".$table_name3." WHERE ad_county_village <> ''  ORDER by ad_county_village ASC";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$listofsavedcounties[]=$rsrow[0];
				$savedcountieslist=array_unique($listofsavedcounties);

			}
					foreach ($savedcountieslist as $savedcounty)
					{
		 				echo "<option value=\"$savedcounty\">$savedcounty</option>";
		 			}


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create a drop down list containing county/village options from saved states in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: create a drop down list containing country options from saved countries in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function create_dropdown_from_current_countries()
{

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

	$listofsavedcountries=array();

		$query="SELECT DISTINCT ad_country FROM ".$table_name3." WHERE ad_country <> '' ORDER by ad_country ASC";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

			while ($rsrow=mysql_fetch_row($res))
			{
				$listofsavedcountries[]=$rsrow[0];
				$savedcountrieslist=array_unique($listofsavedcountries);

			}
					foreach ($savedcountrieslist as $savedcountry)
					{
		 				echo "<option value=\"$savedcountry\">$savedcountry</option>";
		 			}


}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: create a drop down list containing country options from saved states in database
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if ads table contains city data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function adstablehascities()
{

	$myreturn=false;

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_city FROM ".$table_name3." WHERE ad_city <> ''";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

		if (mysql_num_rows($res))
  		{
  			$myreturn=true;
  		}

  		return $myreturn;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Check if ads table contains city data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if ads table contains state data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function adstablehasstates()
{

	$myreturn=false;

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_state FROM ".$table_name3." WHERE ad_state <> ''";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

		if (mysql_num_rows($res))
  		{
  			$myreturn=true;
  		}

  		return $myreturn;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Check if ads table contains state data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if ads table contains country data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function adstablehascountries()
{

	$myreturn=false;

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_country FROM ".$table_name3." WHERE ad_country <> ''";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

		if (mysql_num_rows($res))
  		{
  			$myreturn=true;
  		}

  		return $myreturn;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Check if ads table contains country data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Check if ads table contains county data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function adstablehascounties()
{

	$myreturn=false;

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_county_village FROM ".$table_name3." WHERE ad_county_village <> ''";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

		if (mysql_num_rows($res))
  		{
  			$myreturn=true;
  		}

  		return $myreturn;

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: Check if ads table contains county data
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: check if there are any values entered into the price field for any ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function price_field_has_values()
{
	$myreturn=false;

	global $wpdb;
	$table_name3 = $wpdb->prefix . "awpcp_ads";

		$query="SELECT ad_item_price FROM ".$table_name3." WHERE ad_item_price > '0'";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

		if (mysql_num_rows($res))
  		{
  			$myreturn=true;
  		}

  		return $myreturn;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check if there are any values entered into the price field for any ad
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get an image name associated with a specified ad ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function get_a_random_image($ad_id)
{

global $wpdb;
$table_name5 = $wpdb->prefix . "awpcp_adphotos";
$awpcp_image_name='';

	$query="SELECT image_name FROM ".$table_name5." WHERE ad_id='$ad_id' AND disabled='0' LIMIT 1";
	if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}

  	if (mysql_num_rows($res))
  	{
		list($awpcp_image_name)=mysql_fetch_row($res);
	}

	return $awpcp_image_name;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get an image name associated with a specified ad ID
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: check a specific ad to see if it is disabled or enabled
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function check_if_ad_is_disabled($adid) {
global $wpdb;
$table_name3 = $wpdb->prefix . "awpcp_ads";

	$myreturn=false;
		$query="SELECT disabled FROM ".$table_name3." WHERE ad_id='$adid'";
		if (!($res=mysql_query($query))) {error(mysql_error(),__LINE__,__FILE__);}

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: check a specific ad to see if it is disabled or enabled
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: get the currency code for price fields
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION: get the currency code for price fields
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// START FUNCTION: Clear HTML tags
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function strip_html_tags( $text )
{
    $text = preg_replace(
        array(
          // Remove invisible content
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// END FUNCTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>
