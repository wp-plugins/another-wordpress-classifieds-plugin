<?php 

require_once(AWPCP_DIR . 'import.php');

class AWPCP_Admin_CSV_Importer {

	public function dispatch() {
		global $awpcp_plugin_path;
		global $start_date;
		global $end_date;
		global $import_date_format;
		global $date_sep;
		global $time_sep;
		global $auto_cat;
		global $assign_user;
		global $assigned_user;
		global $test_import;

		global $import_count;
		global $reject_count;
		global $pic_import_count;

		global $import_errors;

		$import_type = awpcp_post_param('import_type', '');
		$test_import = strcmp($import_type, "Test Import") === 0;

		$start_date = awpcp_post_param("startDate", '');
		$end_date = awpcp_post_param("endDate", '');
		$import_date_format = awpcp_post_param("date_fmt", 'us_date');
		$date_sep = awpcp_post_param("sep_date", '/');
		$time_sep = awpcp_post_param("sep_time", ':');
		$auto_cat = awpcp_post_param("auto_cat", 1);
		$assign_user = awpcp_post_param('assign_user', 0);
		$assigned_user = intval(awpcp_post_param('user', 0));

		// Original implementation used a global var to pass errors.
		// That is still used until I got a change to refactor the
		// existing functions to use an errors array passed by reference.
		// The messages array is only used to report when a new user
		// is created.
		$errors = array();
		$messages = array();
		$form_errors = array();
		
		if (!empty($import_type)) {
			$csv_file_name = $_FILES['import']['name'];
			$zip_file_name = $_FILES['import_zip']['name'];

			if (empty($csv_file_name)) {
				$form_errors['import'] = __('Please select the CSV file to import.', 'AWPCP');
			} else {
				$ext = trim(strtolower(substr(strrchr($csv_file_name, "."), 1)));
				if ($ext != "csv") {
					$form_errors['import'] = __('Please upload a valid csv file.', 'AWPCP');
				}
				
				if (!empty($zip_file_name)) {
					$ext = trim(strtolower(substr(strrchr($zip_file_name, "."), 1)));
					if ($ext != "zip") {
						$form_errors['import_zip'] = __('Please upload a valid zip file.', 'AWPCP');
						$continue_import = false;
					}
				}
			}
			
			if (!empty($start_date)) {
				$date_arr = explode("/", $start_date);
				if (!is_valid_date($date_arr[0], $date_arr[1], $date_arr[2])) {
					$form_errors['startDate'] = __('Invalid Start Date.', 'AWPCP');
				} else if (strlen($date_arr[2]) != 4) {
					$form_errors['startDate'] = __('Invalid Start Date -- Year Must be of Four Digit.', 'AWPCP');
				}
			}
			
			if (!empty($end_date)) {
				$date_arr = explode("/", $end_date);
				if (!is_valid_date($date_arr[0], $date_arr[1], $date_arr[2])) {
					$form_errors['endDate'] = __('Invalid End Date.', 'AWPCP');
				} else if (strlen($date_arr[2]) != 4) {
					$form_errors['endDate'] = __('Invalid End Date -- Year Must be of Four Digit.', 'AWPCP');
				}
			}
				
			if (empty($form_errors)) {
				import_ad($_FILES['import']['tmp_name'], $_FILES['import_zip']['tmp_name'], $errors, $messages);
			}
		}

		ob_start();
			include(AWPCP_DIR . '/admin/templates/admin-panel-csv-importer.tpl.php');
			$html = ob_get_contents();
		ob_end_clean();

		echo $html;
	}
}


class AWPCP_CSV_Importer {
}