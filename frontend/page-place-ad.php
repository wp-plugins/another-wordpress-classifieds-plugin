<?php 

class AWPCP_Place_Ad_Page {

	public $error = false;
	public $active = false;

	public function AWPCP_Place_Ad_Page() {
		add_action('wp_footer', array($this, 'print_scripts'));
		add_action('admin_footer', array($this, 'print_scripts'));

		$src = AWPCP_URL . 'js/extra-fields.js';
		wp_register_script('awpcp-extra-fields', $src, array('jquery'), '1.0', true);
	}

	public function print_scripts() {
		if (!$this->active) {
			return;
		}
		wp_print_scripts('awpcp-extra-fields');
	}

	public function dispatch() {
		return awpcpui_process_placead();
	}

	// public function _dispatch($action='place-ad') {
	// 	$action = awpcp_post_param('action', $action);

	// 	switch ($action) {
	// 		case 'place-ad':
	// 			$content = $this->place();
	// 			break;
	// 		case 'edit-ad':
	// 			$content = $this->edit();
	// 			break;
	// 		case 'save-ad':
	// 		case 'update-ad':
	// 			$content = $this->save();
	// 			break;
	// 		case 'upload-images':
	// 			break;
	// 		case 'payment':
	// 			break;
	// 	}

	// 	if ($this->error) {
	// 		ob_start();
	// 			include(AWPCP_DIR . 'frontend/templates/page-error.tpl.php');
	// 			$content = ob_get_contents();
	// 		ob_end_clean();
	// 	}

	// 	return $content;
	// }

	// /**
	//  * Shows Ad details form for creation tasks.
	//  */
	// public function place() {

	// 	// TODO: get request parameters

	// 	$is_admin = awpcp_current_user_is_admin();
	// 	$placeadpagename = sanitize_title(get_awpcp_option('place-ad-page-name'));
	// 	$placeadpageid = awpcp_get_page_id($placeadpagename);
	// 	$url_placeadpage = get_permalink($placeadpageid);

	// 	if (get_awpcp_option('onlyadmincanplaceads') && $is_admin) {
	// 		$this->error = __('You do not have permission to perform the function you are trying to perform. Access to this page has been denied', 'AWPCP');
	// 		return;
	// 	}
		
	// 	if (get_awpcp_option('requireuserregistration') &&!is_user_logged_in()) {
	// 		$message = __('Hi, You need to be a registered user to post Ads in this website. Please use the form below to login or register.', 'AWPCP');
	// 		return awpcp_user_login_form($url_placeadpage, $message);
	// 	}

	// 	// Allow plugins to decide whether the current user should be asked
	// 	// to pay something for posting the Ad.
	// 	// XXX: replaced is_admin() with $isadmin since the first one only checks
	// 	// if we are showing an admin page, not if current user is an administrator.
	// 	$request_payment = adtermsset() && !$isadmin;
	// 	$request_payment = apply_filters('awpcp-should-request-payment', $request_payment);

	// 	// TODO: get userinfo (profile info)
	// 	// TODO: get Categories
	// 	// TODO: load Ad Terms and prepare to display
	// 	// TODO: load Payment Methods and preapre to display
	// 	// TODO: extract JavaScript checks to an external file
	// 	// TODO: prepare Region Control selector
	// 	// TODO: prepare Region Control form fields
	// 	// TODO: create template
	// 	// TODO: prepare Extra Fields form fields
	// }
}