<?php


/**
 * For PHP4 users, even though it's not technically supported:
 */
if (!function_exists('array_walk_recursive')) {
    function array_walk_recursive(&$input, $funcname, $userdata = "") {
        if (!is_callable($funcname)) {
            return false;
        }
        if (!is_array($input)) {
            return false;
        }
       
        foreach ($input AS $key => $value) {
            if (is_array($input[$key])) {
                array_walk_recursive($input[$key], $funcname, $userdata);
            } else {
                $saved_value = $value;
                if (!empty($userdata)) {
                    $funcname($value, $key, $userdata);
                } else {
                    $funcname($value, $key);
                }               
                if ($value != $saved_value) {
                    $input[$key] = $value;
                }
            }
        }        
        return true;
    }
}


/**
 * Get a WP User. See awpcp_get_users for details.
 *
 * @param $id int 	User ID
 */
function awpcp_get_user($id) {
	$users = awpcp_get_users('WHERE ID = ' . intval($id));
	if (!empty($users)) {
		return array_shift($users);
	}
	return null;
}

/**
 * Get list of WP registered users, adding special attributes to 
 * each User object, as needed by AWPCP.
 *
 * Attributes added are:
 * - username
 * - address
 * - city
 * - state
 *
 * @param $where string 	SQL Where clause to filter users
 */
function awpcp_get_users($where='') {
	global $wpdb;

	$users = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users $where");

	foreach ($users as $k => $user) {
		$data = get_userdata($user->ID);
		// extracts AWPCP profile data
		$profile = get_user_meta($user->ID, 'awpcp-profile', true);

		$users[$k] = new stdClass();
		$users[$k]->ID = $user->ID;
		$users[$k]->user_email = empty($profile['email']) ? $data->user_email : $profile['email'];
		$users[$k]->user_login = awpcp_get_property($data, 'user_login', '');
		$users[$k]->display_name = awpcp_get_property($data, 'display_name', '');
		$users[$k]->first_name = awpcp_get_property($data, 'first_name', '');
		$users[$k]->last_name = awpcp_get_property($data, 'last_name', '');
		$users[$k]->username = awpcp_array_data('username', '', $profile);
		$users[$k]->address = awpcp_array_data('address', '', $profile);
		$users[$k]->city = awpcp_array_data('city', '', $profile);
		$users[$k]->state = awpcp_array_data('state', '', $profile);
		$users[$k]->user_url = awpcp_get_property($data, 'user_url', '');
	}

	return $users;
}

/**
 * Check if current user is an Administrator according to
 * AWPCP settings.
 */
function awpcp_current_user_is_admin() {
	return checkifisadmin() == 1;
}


function awpcp_get_categories() {
	global $wpdb;

	$sql = 'SELECT * FROM ' . AWPCP_TABLE_CATEGORIES;
	$results = $wpdb->get_results($sql);

	return $results;
}


/**
 * Returns an array of Region fields. Only those enabled
 * in the settings will be returned.
 *
 * @param $translations array 	Allow developers to change the name 
 * 								attribute of the form field associated
 *								to this Region Field.
 */
function awpcp_region_fields($translations) {
	$fields = array();

	if (get_awpcp_option('displaycountryfield')) {
		$fields['country'] = array(
			'class' => 'country-field',
			'name' => $translations['country'],
			'label' => __('Country', 'AWPCP'),
			'help' => __('separate countries by commas', 'AWPCP'));
	}
	if (get_awpcp_option('displaystatefield')) {
		$fields['state'] = array(
			'class' => 'state-field',
			'name' => $translations['state'],
			'label' => __('State/Province', 'AWPCP'),
			'help' => __('separate states by commas', 'AWPCP'));
	}
	if (get_awpcp_option('displaycityfield')) {
		$fields['city'] = array(
			'class' => 'city-field',
			'name' => $translations['city'],
			'label' => __('City', 'AWPCP'),
			'help' => __('separate cities by commas', 'AWPCP'));
	}
	if (get_awpcp_option('displaycountyvillagefield')) {
		$fields['county'] = array(
			'class' => 'county-field',
			'name' => $translations['county'],
			'label' => __('County/Village/Other', 'AWPCP'),
			'help' => __('separate counties by commas', 'AWPCP'));
	}

	return $fields;
}

/**
 * Generates HTML for Region fields. Only those enabled
 * in the settings will be returned.
 *
 * @param $query array 			Default or selected values for form fields.
 * @param $translations array 	Allow developers to change the name 
 * 								attribute of the form field associated
 *								to this Region Field.
 */
function awpcp_region_form_fields($query, $translations) {
	if (is_null($translations)) {
		$translations = array('country', 'state', 'city', 'county');
		$translations = array_combine($translations, $translations);
	}

	$fields = array();
	foreach (awpcp_region_fields($translations) as $key => $field) {
		$fields[$key] = array_merge($field, array('value' => $query[$key],
												  'entries' => array(),
												  'options' => ''));
	}

	ob_start();
		include(AWPCP_DIR . 'frontend/templates/region-control-form-fields.tpl.php');
		$html = ob_get_contents();
	ob_end_clean();

	return $html;
}

/**
 * AWPCP misc functions
 *
 * TODO: merge content from functions_awpcp.php, 
 * fileop.class.php, dcfunctions.php, upload_awpcp.php
 * as needed.
 */

/**
 * Return number of allowed images for an Ad, according to its
 * Ad ID or Fee Term ID.
 * 
 * @param $ad_id 		int 	Ad ID.
 * @param $ad_term_id 	int 	Ad Term ID.
 */
function awpcp_get_ad_number_allowed_images($ad_id) {
	$ad = AWPCP_Ad::find_by_id($ad_id);

	if (is_null($ad)) {
		return 0;
	}

	$ad_term_id = $ad->adterm_id;
	if (!empty($ad_term_id)) {
		$allowed = get_numimgsallowed($ad_term_id);
	} else {
		$allowed = get_awpcp_option('imagesallowedfree');
	}

	return apply_filters('awpcp_number_images_allowed', $allowed, $ad_id);
}



/**
 * Inserts an menu item after one of the existing items.
 *
 * This function should be used by plugins when handling
 * the awpcp_menu_items filter.
 *
 * @param $items 	array 	Existing items
 * @param $after 	string 	key of item we want to place the new
 * 							item after
 * @param $key 		string 	New item's key
 * @param $item 	array 	New item's description
 */
function awpcp_insert_menu_item($items, $after, $key, $item) {
	$all = array_merge($items, array($key => $item));
	$keys = array_keys($items);
	$p = array_search($after, $keys);

	if ($p !== FALSE) {
		array_splice($keys, $p+1, 0, $key);
		$items = array();

		// the code below was the only way I find to insert an
		// item in an arbitrary position of an array preserving
		// keys. array_splice dropped the key of the inserted
		// value.

		// create items array in proper order.
		foreach($keys as $key) {
			$items[$key] = $all[$key];
		}
	}

	return $items;
}


function awpcp_find_page($refname) {
	global $wpdb;

	$query = 'SELECT posts.ID, page FROM ' . $wpdb->posts . ' AS posts ';
	$query.= 'LEFT JOIN ' . AWPCP_TABLE_PAGES . ' AS pages ';
	$query.= 'ON (posts.ID = pages.id) WHERE pages.page = %s';

	$query = $wpdb->prepare($query, $refname);
	$pages = $wpdb->get_results($query);

	return $pages !== false && !empty($pages);
}

/**
 * Return name of current AWPCP page.
 *
 * This is part of an effor to put all AWPCP functions under
 * the same namespace.
 */ 
function awpcp_get_main_page_name() {
	return get_awpcp_option('main-page-name');
}

function awpcp_get_page_url($pagename) {
	return get_permalink(awpcp_get_page_get_id(sanitize_title($pagename)));
}


function awpcp_current_url() {
	return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}


function awpcp_post_param($name, $default='') {
	return awpcp_array_data($name, $default, $_POST);
}

function awpcp_request_param($name, $default='', $from=null) {
	return awpcp_array_data($name, $default, is_null($from) ? $_REQUEST : $from);
}

function awpcp_array_data($name, $default, $from=array()) {
	if (isset($from[$name]) && !empty($from[$name])) {
		return $from[$name];
	}
	return $default;
}


function awpcp_get_property($object, $property, $default='') {
    if (is_object($object) && (isset($object->$property) || 
    	array_key_exists($property, get_object_vars($object)))) {
        return $object->$property;
    }
    return $default;
}

function awpcp_get_properties($objects, $property, $default='') {
	$results = array();
	foreach ($objects as $object) {
		$results[] = awpcp_get_property($object, $property, $default);
	}
	return $results;
}



/** Table Helper related functions 
 ---------------------------------------------------------------------------- */

function awpcp_register_column_headers($screen, $columns, $sortable=array()) {
	$wp_list_table = new AWPCP_List_Table($screen, $columns, $sortable);
}

function awpcp_print_column_headers($screen, $id = true, $sortable=array()) {
	$wp_list_table = new AWPCP_List_Table($screen, array(), $sortable);
	$wp_list_table->print_column_headers($id);
}


function awpcp_user_login_form($redirect_to='', $message='') {
	$post_url = get_awpcp_option('postloginformto');
	if (empty($post_url)) {
		$post_url = "$siteurl/wp-login.php";
	}

	$registration_url = get_awpcp_option('registrationurl');
	if (empty($registration_url)) {
		$registration_url="$siteurl/wp-login.php?action=register";
	}

	if (empty($message)) {
		$message = __("Only registered users can post ads. If you are already registered, please login below in order to post your ad.", "AWPCP");
	}

	ob_start();
		include(AWPCP_DIR . 'frontend/templates/user-login-form.tpl.php');
		$html = ob_get_contents();
	ob_end_clean();

	return $html;
}