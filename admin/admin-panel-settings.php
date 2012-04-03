<?php

class AWPCP_Admin_Settings {
	
	public function AWPCP_Admin_Settings() {
		global $awpcp;
		$page = strtolower($awpcp->admin->title) . '_page_' . 'awpcp-admin-settings';
		add_action('admin_print_styles_' . $page, array($this, 'scripts'));
	}

	public function scripts() {
	}

	public function dispatch() {
		global $awpcp;

		$group = $awpcp->settings->groups[awpcp_request_param('g', 'pages-settings')];
		$groups = $awpcp->settings->groups;

		ob_start();
			include(AWPCP_DIR . 'admin/templates/admin-panel-settings.tpl.php');
			$content = ob_get_contents();
		ob_end_clean();

		echo $content;
	}
}