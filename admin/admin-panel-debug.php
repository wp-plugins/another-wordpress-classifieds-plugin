<?php

class AWPCP_Admin_Debug {
	
	public function AWPCP_Admin_Debug() {
		// global $awpcp;
		// $page = strtolower($awpcp->admin->title) . '_page_' . 'awpcp-admin-settings';
		// add_action('admin_print_styles_' . $page, array($this, 'scripts'));
	}

	public function scripts() {
	}

	public function dispatch() {
		global $awpcp, $wpdb, $wp_rewrite;

		$options = $awpcp->settings->options;

        $options['awpcp_installationcomplete'] = get_option('awpcp_installationcomplete');
        $options['awpcp_pagename_warning'] = get_option('awpcp_pagename_warning');
        $options['widget_awpcplatestads'] = get_option('widget_awpcplatestads');
        $options['awpcp_db_version'] = get_option('awpcp_db_version');

		$sql = 'SELECT posts.ID post, posts.post_title title, pages.page ref, pages.id FROM ' . AWPCP_TABLE_PAGES . ' AS pages ';
		$sql.= 'LEFT JOIN ' . $wpdb->posts . ' AS posts ';
		$sql.= 'ON (posts.ID = pages.id)';

		$pages = $wpdb->get_results($wpdb->prepare($sql));

		$rules = (array) $wp_rewrite->wp_rewrite_rules();

		ob_start();
			include(AWPCP_DIR . 'admin/templates/admin-panel-debug.tpl.php');
			$content = ob_get_contents();
		ob_end_clean();

		echo $content;
	}
}