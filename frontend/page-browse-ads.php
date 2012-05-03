<?php
// TODO: make this a class

// Set Browse Ads Screen
function awpcpui_browseadsscreen() {
	global $browseads_content;
	if (!isset($browseads_content) || empty($browseads_content)){
		$browseads_content=awpcpui_process_browseads();
	}
	return $browseads_content;
}

/**
 * Renders Browse Ads page
 */
function awpcpui_process_browseads() {

	if (isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id'])) {
		$adcategory = $_REQUEST['category_id'];
	} else {
		$adcategory = get_query_var('cid');
	}

	$action='';
	if (isset($_REQUEST['a']) && !empty($_REQUEST['a'])) {
		$action=$_REQUEST['a'];
	}
	if (!isset($action) || empty($action)){
		$action="browsecat";
	}

	if ( ($action == 'browsecat') ) {
		if ($adcategory == -1 || empty($adcategory)) {
			$where="";
		} else {
			$where="(ad_category_id='".$adcategory."' OR ad_category_parent_id='".$adcategory."') AND disabled ='0'";
		}
		$adorcat='cat';
	} else {
		$where="disabled ='0'";
		$adorcat='ad';
	}

	$grouporderby=get_group_orderby();

	if ('dosearch' == $action ) {
		$output = dosearch();	
	} else {
		$output = awpcp_display_ads($where,$byl='',$hidepager='',$grouporderby,$adorcat);
	}

	return $output;
}