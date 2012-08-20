<?php

// for PHP4 users, even though it's not technically supported:
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


if (!function_exists('wp_strip_all_tags')) {
	/**
	 * Properly strip all HTML tags including script and style
	 *
	 * @since 2.9.0
	 *
	 * @param string $string String containing HTML tags
	 * @param bool $remove_breaks optional Whether to remove left over line breaks and white space chars
	 * @return string The processed string.
	 */
	function wp_strip_all_tags($string, $remove_breaks = false) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags($string);

		if ( $remove_breaks )
			$string = preg_replace('/[\r\n\t ]+/', ' ', $string);

		return trim($string);
	}
}


if (!function_exists('wp_trim_words')) {
	/**
	 * Trims text to a certain number of words.
	 *
	 * @since 3.3.0
	 *
	 * @param string $text Text to trim.
	 * @param int $num_words Number of words. Default 55.
	 * @param string $more What to append if $text needs to be trimmed. Default '&hellip;'.
	 * @return string Trimmed text.
	 */
	function wp_trim_words( $text, $num_words = 55, $more = null ) {
		if ( null === $more )
			$more = __( '&hellip;' );
		$original_text = $text;
		$text = wp_strip_all_tags( $text );
		$words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
		if ( count( $words_array ) > $num_words ) {
			array_pop( $words_array );
			$text = implode( ' ', $words_array );
			$text = $text . $more;
		} else {
			$text = implode( ' ', $words_array );
		}
		return apply_filters( 'wp_trim_words', $text, $num_words, $more, $original_text );
	}
}


function awpcp_esc_attr($text) {
	// WP adds slashes to all request variables
	$text = stripslashes($text);
	// AWPCP adds more slashes
	$text = stripslashes($text);
	$text = esc_attr($text);
	return $text;
}

function awpcp_esc_textarea($text) {
	$text = stripslashes($text);
	$text = stripslashes($text);
	$text = esc_textarea($text);
	return $text;
}

/**
 * Returns the given date as MySQL date string, Unix timestamp or
 * using a custom format.
 *
 * @since 2.0.7
 * @param $format 	'mysql', 'timestamp', or first arguemnt for date() function.
 */
function awpcp_time($date, $format='mysql') {
	if (is_null($date) || empty($date)) {
		$date = current_time('timestamp');
	} else if (is_string($date)) {
		$date = strtotime($date);
	} // else, we asume a timestamp

	if ($format === 'mysql' || $format === 'timestamp')
		return $format === 'mysql' ? date('Y-m-d H:i:s', $date) : $date;
	return date($format, $date);
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
		$users[$k]->phone = awpcp_array_data('phone', '', $profile);
		$users[$k]->city = awpcp_array_data('city', '', $profile);
		$users[$k]->state = awpcp_array_data('state', '', $profile);
		$users[$k]->user_url = awpcp_get_property($data, 'user_url', '');
	}

	return $users;
}


/**
 * Returns a WP capability required to be considered an AWPCP admin.
 *
 * http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table
 *
 * @since 2.0.7
 */
function awpcp_admin_capability() {
	$roles = explode(',', get_awpcp_option('awpcpadminaccesslevel'));
	if (in_array('editor', $roles))
		return 'edit_pages';
	// default to: only WP administrator users are AWPCP admins
	return 'install_plugins';
}


/**
 * Check if current user is an Administrator according to
 * AWPCP settings.
 */
function awpcp_current_user_is_admin() {
	$capability = awpcp_admin_capability();
	return current_user_can($capability);
}


function awpcp_user_is_admin($id) {
	$capability = awpcp_admin_capability();
	return user_can($id, $capability);
}


function awpcp_get_grid_item_css_class($classes, $pos, $columns, $rows) {
	if ($pos < $columns)
		$classes[] = 'first-row';
	if ($pos >= (($rows - 1) * $columns))
		$classes[] = 'last-row';
	if ($pos == 0 || $pos % $columns == 0)
		$classes[] = 'first-column';
	if (($pos + 1) % $columns == 0)
		$classes[] = 'last-column';
	return $classes;
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
 */
function awpcp_get_ad_images($ad_id) {
	global $wpdb;

	$query = "SELECT * FROM " . AWPCP_TABLE_ADPHOTOS . " ";
	$query.= "WHERE ad_id=%d ORDER BY image_name ASC";

	return $wpdb->get_results($wpdb->prepare($query, $ad_id));
}

/**
 *
 */
function awpcp_get_image_url($image, $suffix='') {
	static $uploads = array();

	if (empty($uploads))
		$uploads = array_shift(awpcp_setup_uploads_dir());

	$images = trailingslashit(AWPCPUPLOADURL);
	$thumbnails = trailingslashit(AWPCPTHUMBSUPLOADURL);

	if (is_object($image))
		$basename = $image->image_name;
	if (is_string($image))
		$basename = $image;

	$original = $images . $basename;
	$thumbnail = $thumbnails . $basename;
	$part = empty($suffix) ? '.' : "-$suffix.";

	$info = pathinfo($original);

	if ($suffix == 'original') {
		$alternatives = array($original);
	} else if ($suffix == 'large') {
		$alternatives = array(
			str_replace(".{$info['extension']}", "$part{$info['extension']}", $original),
			$original
		);
	} else {
		$alternatives = array(
			str_replace(".{$info['extension']}", "$part{$info['extension']}", $thumbnail),
			$thumbnail,
			$original
		);
	}

	foreach ($alternatives as $imagepath) {
		if (file_exists(str_replace(AWPCPUPLOADURL, $uploads, $imagepath))) {
			return $imagepath;
		}
	}

	return false;
}


/**
 *
 */
function awpcp_set_ad_primary_image($ad_id, $image_id) {
	global $wpdb;

	$query = 'UPDATE ' . AWPCP_TABLE_ADPHOTOS . ' ';
	$query.= "SET is_primary = 0 WHERE ad_id = %d";

	if ($wpdb->query($wpdb->prepare($query, $ad_id)) === false)
		return false;

	$query = 'UPDATE ' . AWPCP_TABLE_ADPHOTOS . ' ';
	$query.= 'SET is_primary = 1 WHERE ad_id = %d AND key_id = %d';
	$query = $wpdb->prepare($query, $ad_id, $image_id);

	return $wpdb->query($query) !== false;
}


/**
 *
 */
function awpcp_get_ad_primary_image($ad_id) {
	global $wpdb;

	$query = 'SELECT * FROM ' . AWPCP_TABLE_ADPHOTOS . ' ';
	$query.= 'WHERE ad_id = %d AND is_primary = 1';

	$results = $wpdb->get_results($wpdb->prepare($query, $ad_id));

	if (!empty($results)) return $results[0];

	$query = 'SELECT * FROM ' . AWPCP_TABLE_ADPHOTOS . ' ';
	$query.= 'WHERE ad_id = %d ORDER BY key_id LIMIT 0,1';

	$results = $wpdb->get_results($wpdb->prepare($query, $ad_id));

	return empty($results) ? null : $results[0];
}


/**
 * Inserts a menu item after one of the existing items.
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


/**
 * Check if the page identified by $refname exists.
 */
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


/**
 * Always return the full URL, even if AWPCP main page
 * is also the home page.
 */
function awpcp_get_main_page_url() {
	$permalinks = get_option('permalink_structure');
	$id = awpcp_get_page_id_by_ref('main-page-name');

	if ($permalinks) {
		$url = home_url(get_page_uri($id));
	} else {
		$url = add_query_arg('page_id', $id, home_url());
	}

	return user_trailingslashit($url);
}


/**
 * Returns a link to an AWPCP page identified by $pagename.
 *
 * @since 2.0.7
 */
function awpcp_get_page_url($pagename) {
	return get_permalink(awpcp_get_page_id_by_ref($pagename));
}


/**
 * Returns a link that can be used to initiate the Ad Renewal process.
 *
 * @since 2.0.7
 */
function awpcp_get_renew_ad_url($ad_id) {
	if (get_awpcp_option('enable-user-panel') == 1) {
		$url = awpcp_get_user_panel_url();
		return add_query_arg(array('id' => $ad_id, 'action' => 'renew-ad'), $url);
	} else {
		$url = awpcp_get_page_url('renew-ad-page-name');
		return add_query_arg(array('ad_id' => $ad_id), $url);
	}
}

/**
 * Returns a link to Ad Management (a.k.a User Panel).
 *
 * @since 2.0.7
 */
function awpcp_get_user_panel_url() {
	return admin_url('admin.php?page=awpcp-panel');
}


function awpcp_current_url() {
	return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Returns the domain used in the current request, optionally stripping
 * the www part of the domain.
 *
 * @since 2.0.6
 * @param $www 	boolean		true to include the 'www' part,
 *							false to attempt to strip it.
 */
function awpcp_get_current_domain($www=true, $prefix='') {
	$domain = awpcp_array_data('HTTP_HOST', '', $_SERVER);
	if (empty($domain)) {
		$domain = awpcp_array_data('SERVER_NAME', '', $_SERVER);
	}

	if (!$www && substr($domain, 0, 4) === 'www.') {
		$domain = $prefix . substr($domain, 4);
	}

	return $domain;
}

/**
 * Bulds WordPress ajax URL using the same domain used in the current request.
 *
 * @since 2.0.6
 */
function awpcp_ajaxurl() {
	static $ajaxurl = false;

	if ($ajaxurl === false) {
		$url = admin_url('admin-ajax.php');
		$url = 'http://unitypost.com/wp-admin/admin-ajax.php';
		$parts = parse_url($url);
		$ajaxurl = str_replace($parts['host'], awpcp_get_current_domain(), $url);
	}

	return $ajaxurl;
}


function awpcp_post_param($name, $default='') {
	return awpcp_array_data($name, $default, $_POST);
}


function awpcp_request_param($name, $default='', $from=null) {
	return awpcp_array_data($name, $default, is_null($from) ? $_REQUEST : $from);
}


function awpcp_array_data($name, $default, $from=array()) {
	$value = isset($from[$name]) ? $from[$name] : null;

	if (is_array($value) && count($value) > 0) {
		return $value;
	} else if (!empty($value)) {
		return $value;
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


function awpcp_flash($message) {
	$messages = get_option('awpcp-messages', array());
	$messages[] = $message;
	update_option('awpcp-messages', $messages);
}


function awpcp_print_message($message, $class=array('updated')) {
	return '<div class="' . join(' ', $class) . '"><p>' . $message . '</p></div>';
}


function awpcp_print_messages() {
	$messages = get_option('awpcp-messages', array());

	$html = '';
	foreach ($messages as $message) {
		$html .= awpcp_print_message($message);
	}

	update_option('awpcp-messages', array());

	echo $html;
}
add_action('admin_notices', 'awpcp_print_messages');


/**
 * @since 2.0.7
 */
function awpcp_table_exists($table) {
    global $wpdb;
    $result = $wpdb->get_var("SHOW TABLES LIKE '" . $table . "'");
    return strcasecmp($result, $table) === 0;
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
