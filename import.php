<?php 
	// require_once("../../wp-config.php");

	global $ads_columns;
			   
	global $ads_columns_type;
			   
	global $auto_columns;
			   
	global $auto_columns_types;		   

	global $extra_fields;

	global $required_columns;
	
	$ads_columns = array (
		"title" => "ad_title",
		"details" => "ad_details",
		"contact_name" => "ad_contact_name",
		"contact_email" => "ad_contact_email",
		"category_name" => "ad_category_id",
		"contact_phone" => "ad_contact_phone",
		"website_url" => "websiteurl",
		"city" => "ad_city",
		"country" => "ad_country",
		"county_village" => "ad_county_village",
		"item_price" => "ad_item_price",
		"start_date" => "ad_startdate",
		"end_date" => "ad_enddate",
		'username' => 'user_id',
		// "images" => ""
	);
			   
	$ads_columns_type = array (
		"title" => "varchar",
		"details" => "varchar",
		"contact_name" => "varchar",
		"contact_email" => "varchar",
		"category_name" => "varchar",
		"contact_phone" => "varchar",
		"website_url" => "varchar",
		"city" => "varchar",
		"country" => "varchar",
		"county_village" => "varchar",
		"item_price" => "",
		"start_date" => "date",
		"end_date" => "date",
		'username' => '',
		"images" => "varchar"
	);

	$extra_fields = array();
			   
	 // is_featured_ad -> 0
	 // disabled -> 0
	 // terms of service -> 1
	 // ad_postdate -> same as start_date
	 // disabled_date -> null
	 // ad_views -> 0
	 // ad_last_updated -> current time
	 // ad_key -> (default)
			   
	$auto_columns = array (
					"is_featured_ad" => "0",
					"disabled" => "0",
					"adterm_id" => "0",
					"ad_postdate" => "?",
					"disabled_date" => "",
					"ad_views" => "0",
					"ad_last_updated" => "?",
					"ad_key" => ""
			   );
			   
	$auto_columns_types = array (
					"is_featured_ad" => "",
					"disabled" => "",
					"adterm_id" => "",
					"ad_postdate" => "?",
					"disabled_date" => "date",
					"ad_views" => "",
					"ad_last_updated" => "?",
					"ad_key" => "varchar"
			   );
			   
	// title, details, contact_name, contact_email, category_name,
	// contact_phone, website_url, city, country, county_village, item_price, start_date, end_date,images
	$required_columns = array ("title", "details", "contact_name", "contact_email", "category_name", 'username');
	
	$import_count = 0;
	$reject_count = 0;
	$pic_import_count = 0;
	$curr_row = 0;
	$import_errors = array();
	$import_error_row_flag = array();
		
	// import_ad("sample4.csv", "");
	
	function import_ad($csv_file_name, $img_arch_file_name, &$errors=array(), &$messages=array()) {
		global $import_errors;
		global $import_error_row_flag;
		global $reject_count;
		global $assign_user;
		global $assigned_user;
		global $required_columns;

		$no_zip_file = false;
		if (!empty($img_arch_file_name)) {
			extract_images($img_arch_file_name);
			$no_zip_file = true;
		}
		
		$data = getCsvData($csv_file_name);
		
		if (empty($data)) {
			echo "<br/>Invalid CSV file.";
			return;
		}
		
		$row_no = array();
		
		$query_data = array();
		$counter = 0;
		
		$header = $data[0];
		if (in_array("images", $header) && !$no_zip_file) {
			echo "<br/>Image file names were found but no ZIP was provided.";
			return;
		}
		
		$header_count = count($header);
		$rows_count = count($data);

		if ($assign_user && !in_array('username', $header)) {
			array_push($header, 'username');
		} else if (!$assign_user) {
			$required_columns = array_diff($required_columns, array('username'));
		}

		// per row column count can be handled here
		for ($i = 1; $i < $rows_count; $i++) {
			$data_columns = $data[$i];
			$column_count = count($data_columns);
			if ($column_count != $header_count) {
				// error message
				$import_errors[] = "Row number $i: input length mismatch";
				$import_error_row_flag[$i] = true;
				$reject_count++;
				continue;
			}
			for ($j = 0; $j < $column_count; $j++) {
				// echo "Data: " . $header[$j] . "" . $data_columns[$j];
				$key = trim($header[$j], "\n\r");
				$query_data[$counter][$key] = $data_columns[$j];
			}
			$row_no[$counter] = $i;
			$query_data[$counter]["row_no"] = $i;
			$counter++;
		}
		
		// var_dump($query_data);
		
		// echo "<br/>Zip files<br/>";
		
		validate_csv_data($header, $query_data);
		prepare_query($query_data, $header, $no_zip_file, $errors, $messages);
	}
	
	function validate_csv_data($header, $query_data) {
		global $wpdb;
		global $ads_columns, $ads_columns_type;
		global $extra_fields, $required_columns;
		global $import_errors;

		// the importer should be a class and this should be part of 
		// the constructor or an init action handler. @wvega
		if (defined('AWPCPEXTRAFIELDSMOD')) {
			global $tbl_ad_extra_fields;

			// find extra fields
			$fields = awpcp_get_extra_fields();

			// add extra fields to $ads_columns and $ads_columns_type arrays
			foreach ($fields as $field) {
				$extra_fields[$field->field_name] = $field;
			}
		}

		foreach ($required_columns as $required) {
			if (!in_array($required, $header)) {
				$import_errors[] = "One of the required column is missing. Import can't continue.";
				// echo "One of the required column is missing. Import can't continue.";
				die("One of the required column is missing. Import can't continue: $required");
			}
		}

		// accepted columns are standard Ads columns + extra fields columns
		$keys = array_merge(array_keys($ads_columns_type), array_keys($extra_fields));
		foreach ($header as $head_col) {
			if (!in_array($head_col, $keys)) {			
				// echo "Unknown column specified, import can't continue";
				$import_errors[] = "Unknown column specified, import can't continue";
				die("Unknown column specified. Import can't continue: $head_col");
			}
		}
	}
	
	function category_id($cat_name, $row_num) {
		global $wpdb;
		global $import_errors;
		global $import_error_row_flag;
		global $auto_cat;
		global $test_import;
		$create_category = true;
		$sql = $wpdb->prepare("SELECT category_id FROM {$wpdb->base_prefix}awpcp_categories WHERE category_name = '%s'", $cat_name);
		$cat_id = $wpdb->get_var($sql);
		if (!$cat_id && $auto_cat == "1" && !$test_import) {
			// $query = "INSERT INTO {$wpdb->base_prefix}awpcp_categories (category_parent_id, category_name, category_order) VALUES (0, '$cat_name', 0)";
			$query = $wpdb->prepare("INSERT INTO {$wpdb->base_prefix}awpcp_categories (category_parent_id, category_name, category_order) VALUES (0, '%s', 0)", $cat_name);
			// echo "<br/>$query";
			$wpdb->query($query);
			$cat_id = $wpdb->insert_id; // 5; //$wpdb->insert_id;
			return $cat_id;
		} else if ($cat_id) {
			return $cat_id;
		} else if ($auto_cat == "1" && $test_import) {
			return 5; // dummy value returned for test import.
		} 
		return 0; // TODO false
	}
	
	function prepare_query($query_data, $header, $no_zip_file, &$errors=array(), &$messages=array()) {
		global $wpdb;
		global $ads_columns;
		global $ads_columns_type;
		global $extra_fields;
		global $required_columns;
		global $import_errors;
		global $import_error_row_flag;
		global $auto_columns;
		global $auto_columns_types;
		global $import_count;
		global $pic_import_count;
		global $reject_count;
		global $test_import;
		// global $errors;
		
		foreach ($query_data as $data) {
			$row_no = $data["row_no"];
			$cat_id = category_id($data["category_name"], $row_no);
			$email = awpcp_array_data('contact_email', '', $data);

			if ($cat_id == 0) {
				$import_errors[] = "Category name not found at row number $row_no";
				$import_error_row_flag[$row_no] = true;
				$reject_count++;
				continue;
			}

			$query = "INSERT INTO {$wpdb->base_prefix}awpcp_ads ";
			$query_cols = "";
			$query_val = "";
			$val_arr = array();

			$skip = false;
			
			foreach ($ads_columns as $key => $column) {
				if (in_array($key, $header)) {
					$val = $data[$key];	

					if ($key == 'username') {
						$val = awpcp_csv_importer_get_user_id($val, $email, $row_no, $errors, $messages);
						if ($val === false) {
							$import_errors[] = $errors[count($errors) - 1];
							$import_error_row_flag[$row_no] = true;
							break;
						}

					} else if ($key == "category_name") {
						$val = $cat_id;

					} else { //if ($key == "start_date" || $key == "start_date") {
						$val = get_value($val, $key, $row_no);
					}

					if (empty($val) && in_array($key, $required_columns)) {
						$import_errors[] = "Required value missing at row number $row_no";
						$import_error_row_flag[$row_no] = true;
						break;
					}

					if (empty($query_cols)) {
						$query_cols = $column;
						if (empty($ads_columns_type[$key])) {
							// $query_val = "$val";
							$query_val = "%d";
							$val_arr[] = $val;
						} else {
							// $query_val = "'$val'";
							$query_val = "%s";
							$val_arr[] = $val;
						}
					} else {
						$query_cols = $query_cols . ", " . $column;
						if (empty($ads_columns_type[$key])) {
							// $query_val = $query_val . ", " . $val;
							$query_val = $query_val . ", %d";
							$val_arr[] = $val;
						} else {
							// $query_val = $query_val . ", '" . $val . "'";
							$query_val = $query_val . ", '%s'";
							$val_arr[] = $val;
						}
					} 				
				}
			}
			
			// $auto_columns_types
			foreach($auto_columns as $key => $value) {
				$query_cols = $query_cols . ", " . $key;
				if ($value == "?") {
					$value = get_value($value, $key, $row_no);
				}
				if (empty($auto_columns_types[$key])) {
					$query_val = $query_val . ", %d";
					$val_arr[] = 0;
				} else {
					$query_val = $query_val . ", '%s'";
					$val_arr[] = $value;
				}
			}

			// get an validate extra fields columns
			foreach ($extra_fields as $field) {
				$name = $field->field_name;

				// validate only extra fields present in the CSV file
				if (!isset($data[$name])) {
					continue;
				}

				$validate = $field->field_validation;
				$type = $field->field_input_type;
				$options = $field->field_options;
				$category = $field->field_category;

				$enforce = strcmp($category, 'root') == 0 || $category == $cat_id;

				$value = awpcp_validate_extra_field($name, $data[$name], $row_no, $validate, $type, $options, $enforce);

				if (is_array($value)) {
					// we found an error, let's skip this row
					$import_errors = array_merge((array) $import_errors, (array) $value);
					$import_error_row_flag[$row_no] = true;
					break;
				}

				switch ($field->field_mysql_data_type) {
					case 'VARCHAR':
					case 'TEXT':
						$query_val .= ', %s';
						break;
					case 'INT':
						$query_val .= ', %d';
						break;
					case 'FLOAT':
						$query_val .= ', %f';
						break;
				}

				$query_cols .= ', ' . $name;
				$val_arr[] = $value;
			}
			
			// Call for test to collect the errors
			if ($no_zip_file) {
				$images = $data["images"];
				$images = explode(";", $images);
				$pic_import_count += import_images($images, 5 /* Dummy */, $file_name, $destdir, $adtermid, $adkey, $row_no, $imgmaxsize = 100, $imgminsize = 100, $twidth = 125);
			}
			


			// if there was an error, skip this row and try to import the next one
			if ($import_error_row_flag[$row_no] == true) {
				$reject_count++;
				continue;
			}
			
			$query = $wpdb->prepare($query . " (" . $query_cols . ") VALUES (" . $query_val . ")", $val_arr);
			
			if ($test_import) {
				$inserted_id = 5; // dummy value
			} else {
				$wpdb->query($query);
				$inserted_id = $wpdb->insert_id; // 5; //$wpdb->insert_id;
			}
			// echo "<br/>" . $query;
			
			if ($no_zip_file && !$test_import) {
				$images = $data["images"];
				$images = explode(";", $images);
				// $pic_import_count += 
				import_images($images, $inserted_id, $file_name, $destdir, $adtermid, $adkey, $row_no, $imgmaxsize = 100, $imgminsize = 100, $twidth = 125, $test_import);
			}
			
			$import_count++;
			
			// var_dump($images);
			// foreach ($images as $image) {
			// 	import_images($images, $inserted_id, $file_name, $destdir, $adtermid, $adkey, $imgmaxsize = 100, $imgminsize = 100, $twidth = 125);
			// 	echo "<br/>" . $image;
			// }
		}

		array_splice($errors, count($errors), 0, $import_errors);
	}
	
	function get_value($val, $key, $row_num) {
		global $start_date, $end_date, $import_date_format, $date_sep, $time_sep;
		global $import_errors, $import_error_row_flag;

		if ($key == "item_price") {
			// numeric validation
			if (is_numeric($val)) {
				// AWPCP stores Ad prices using an INT column (WTF!!) so we need to 
				// store 99.95 as 9995 and 99 as 9900.
				return $val * 100;
			} else {
				$import_errors[] = "Item price non numeric at row number $row_num";
				$import_error_row_flag[$row_num] = true;
			}
		} else if ($key == "start_date") {
			// TODO: validation
			if (!empty($val)) {
				$val = parse_date($val, $import_date_format, $date_sep, $time_sep);
				if (empty($val) || $val == null) {
					$import_errors[] = "Invalid Start date at row number: $row_num";
					$import_error_row_flag[$row_num] = true;
				}
				return $val;
			}
			if (empty($start_date)) {
				// $date = new DateTime();
				// $val = $date->format( 'Y-m-d' );
				$import_errors[] = "Start date missing (alternately you can specify the default start date) at row number $row_num";
				$import_error_row_flag[$row_num] = true;
			} else {
				// TODO: validation
				$val = parse_date($start_date, "", $date_sep, $time_sep); // $start_date;
			}
			return $val;
		} else if ($key == "end_date") {
			// TODO: validation
			if (!empty($val)) {
				$val = parse_date($val, $import_date_format, $date_sep, $time_sep);
				if (empty($val) || $val == null) {
					$import_errors[] = "Invalid End date at row number: $row_num";
					$import_error_row_flag[$row_num] = true;
				}
				return $val;
			}
			if (empty($end_date)) {
				// $date = new DateTime();
				// $val = $date->format( 'Y-m-d' );
				$import_errors[] = "End date missing (alternately you can specify the default end date) at row number $row_num";
				$import_error_row_flag[$row_num] = true;
			} else {
				// TODO: validation
				$val = parse_date($end_date, "", $date_sep, $time_sep); // $end_date;
			}
			return $val;
		} else if ($key == "ad_postdate") {
			if (empty($start_date)) {
				$date = new DateTime();
				$val = $date->format( 'Y-m-d' );
			} else {
				// TODO: validation
				$val = parse_date($start_date, "", $date_sep, $time_sep, "Y-m-d"); // $start_date;
			}
			return $val;
		} else if ($key == "ad_last_updated") {
			$date = new DateTime();
			// $date->setTimezone( $timezone );
			$val = $date->format( 'Y-m-d' ); 
			return $val;
		} else if (!empty($val)) {
			return $val;
		}
		return false;
	}
	
	function parse_date($val, $import_date_format, $date_separator, $time_separator, $format = "Y-m-d H:i:s") {
		$datetime = new DateTime();
		try {
			if ($import_date_format == "us_date") {
				$date = explode($date_separator, $val);
				// var_dump($date);
				if (!is_valid_date($date[0], $date[1], $date[2])) return null;
				$datetime->setDate($date[2], $date[0], $date[1]);
		    	// $datetime->setTime($time[0], $time[1], $time[2]);
			} else if ($import_date_format == "uk_date") {
				$date = explode($date_separator, $val);
				// var_dump($date);
				if (!is_valid_date($date[1], $date[0], $date[2])) return null;
				$datetime->setDate($date[2], $date[1], $date[0]);
		    	// $datetime->setTime($time[0], $time[1], $time[2]);
			} else if ($import_date_format == "us_date_time") {
				$date_time = explode(" ", $val);
				$date = $date_time[0];
				// var_dump($date);
				$time = $date_time[1];
				// var_dump($time);
				$date = explode($date_separator, $date);
				$time = explode($time_separator, $time);
				if (!is_valid_date($date[0], $date[1], $date[2])) return null;
				$datetime->setDate($date[2], $date[0], $date[1]);
		    	$datetime->setTime($time[0], $time[1], $time[2]);
			} else if ($import_date_format == "uk_date_time") {
				$date_time = explode(" ", $val);
				$date = $date_time[0];
				// var_dump($date);
				$time = $date_time[1];
				// var_dump($time);
				$date = explode($date_separator, $date);
				$time = explode($time_separator, $time);
				if (!is_valid_date($date[1], $date[0], $date[2])) return null;
				$datetime->setDate($date[2], $date[1], $date[0]);
		    	$datetime->setTime($time[0], $time[1], $time[2]);
			} else {
				$date = explode($date_separator, $val);
				$datetime->setDate($date[2], $date[0], $date[1]);
			}
		} catch (Exception $ex) {
			echo "Exception: " . $ex->getMessage();
		}
		 
		// $date = "2011/03/20";
		// $date = explode("/", $date);

		// $time = "07:16:17";
		// $time = explode(":", $time);

		// $tz_string = "America/Los_Angeles"; // Use one from list of TZ names http://php.net/manual/en/timezones.php
		// $tz_object = new DateTimeZone($tz_string);


		// $datetime->setTimezone($tz_object);
		// $datetime->setDate($date[0], $date[1], $date[2]);
		// $datetime->setTime($time[0], $time[1], $time[2]);
	   
	    return $datetime->format($format); // Prints "2011/03/20 07:16:17" 
	}
	
// 	function post_image($file_name) {
// 		$kbm_ZipPost_ZipFile = zip_open ( "csv.zip" ) ;
// 		while ( $kbm_ZipPost_EntryFile = zip_read ( $kbm_ZipPost_ZipFile ) ){
// //			var_dump($kbm_ZipPost_EntryFile);
// //			echo "FileName: " . zip_entry_name($kbm_ZipPost_EntryFile);
// 			if ( zip_entry_filesize($kbm_ZipPost_EntryFile) > 0 ) {
// 				$kbm_ZipPost_Entry = zip_entry_read($kbm_ZipPost_EntryFile, zip_entry_filesize($kbm_ZipPost_EntryFile));
// //				var_dump($kbm_ZipPost_Entry);
// //				KBM_ZipPoster_Debug ('new entryfile read');
// //				KBM_ZipPoster_Debug ('entry is: '.$kbm_ZipPost_Entry);
// 				zip_entry_close($kbm_ZipPost_EntryFile);
// //				KBM_ZipPoster_Debug ('entryfile has been closed');
// //				KBM_ZipPoster_Create_Post ( $kbm_ZipPost_Configuration, $kbm_ZipPost_Entry );
// //				KBM_ZipPoster_Debug ('new post created');
// //				$kbm_ZipPoster_PostCount = $kbm_ZipPoster_PostCount + 1 ;
// //				KBM_ZipPoster_Debug ('kbm_ZipPoster_PostCount increased to: '.$kbm_ZipPoster_PostCount);
// 			} else{
// //				KBM_ZipPoster_Debug ('file size of entry is 0; skipping');
// 				zip_entry_close($kbm_ZipPost_EntryFile);
// //				KBM_ZipPoster_Debug ('non-file entryfile has been closed');
// 			}
		
// 		}
// 	}
	
	function import_images($images, $adid, $file_name, $destdir, $adtermid, $adkey, $row_no, $imgmaxsize = 100, $imgminsize = 100, $twidth = 125, $test_import = true) {
		$output = '';
		global $wpdb;
		global $import_errors, $import_error_row_flag;
		// global $test_import;
		$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
		$awpcpupdatinserted = false;
		$awpcpuploaderror = false;
		$awpcpfilesuploaded = true;
		$awpcpuerror = array();
		$found_error = false;
		$file_import_count = 0;
		
		// $file_name = "";
		
		if(adidexists($adid)) {
			$totalimagesuploaded = get_total_imagesuploaded($adid);
		}
	
		// if(get_awpcp_option('freepay') == 1) {
		// 	$numimgsallowed = get_numimgsallowed($adtermid);
		// } else {
		// 	$numimgsallowed = get_awpcp_option('imagesallowedfree');
		// }
		
		$numimgsallowed = get_awpcp_option('imagesallowedfree');
	
		$numimgsleft = ($numimgsallowed - $totalimagesuploaded);
		
		global $wpcontentdir, $current_user;
		// $destdir = $wpcontentdir . "\\uploads\\awpcp";
		// $file_dir = $wpcontentdir . "\\uploads\\awpcp\\import\\" . $current_user->ID . "\\"; //scan2.jpg";
		
		$destdir = $wpcontentdir . "/uploads/awpcp";
		$file_dir = $wpcontentdir . "/uploads/awpcp/import/" . $current_user->ID . "/"; //scan2.jpg";
		
		// for($i = 0; $i < $numimgsleft; $i++) {
		// for($i = 0; $i < 1; $i++) {
		$i = 0;
		foreach ($images as $image) {
			$file_name = $file_dir . $image;
			$filename = addslashes($file_name); //$_FILES[$actual_field_name.$i]['name']);
			$destdir = addslashes($destdir);
			// echo "<br/>FileName: " . $filename;
			// echo "<br/>DestDir: " . $destdir;
			$ext = strtolower(substr(strrchr($filename, "."), 1));
			$ext_array = array('gif', 'jpg', 'jpeg', 'png');
			
			$found_error = false;
	
			// if (isset($_FILES[$actual_field_name.$i]['tmp_name']) && is_uploaded_file($_FILES[$actual_field_name.$i]['tmp_name'])) {
			if (isset($filename)) {
				$imginfo = getimagesize($filename);
				$imgfilesizeval = filesize($filename);
				// echo "<br/>FileSize: " . $imgfilesizeval;
	
				$desired_filename = mktime();
				$desired_filename .= "_$i";
				if(isset($filename) && !empty($filename)) {
					if (!(in_array($ext, $ext_array))) {
						$awpcpuploaderror = true;
						$awpcpuerror[] .= __(" had an invalid file extension and was not uploaded","AWPCP");
						$import_errors[] = "Row no $row_no: Had an invalid file extension and was not uploaded";
						$import_error_row_flag[$row_no] = true;
						$found_error = true;
					} 
					// elseif($filename <= $imgminsize) {
					// 	$awpcpuploaderror = true;
					// 	$awpcpuerror[] .= sprintf(__("The size of %1$s was too small. The file was not uploaded. File size must be greater than %2$d bytes", "AWPCP"), $filename, $imgminsize);
					// } elseif($imginfo[0] < $twidth) {
					// 	$awpcpuploaderror = true;
					// 	$awpcpuerror[] .= sprintf(__(" did not meet the minimum width of [%s] pixels. The file was not uploaded", "AWPCP"), $twidth);
					// } elseif ($imginfo[1] < $twidth) {
					// 	$awpcpuploaderror = true;
					// 	$awpcpuerror[] .= sprintf(__(" did not meet the minimum height of [%s] pixels. The file was not uploaded", "AWPCP"), $twidth);
					// } elseif(!isset($imginfo[0]) && !isset($imginfo[1])) {
					// 	$awpcpuploaderror = true;
					// 	$awpcpuerror[] .= __(" does not appear to be a valid image file","AWPCP");
					// } elseif( $imgfilesizeval > $imgmaxsize ) {
					// 	$awpcpuploaderror = true;
					// 	$awpcpuerror[] .= sprintf(__(" was larger than the maximum allowed file size of [%s] bytes. The file was not uploaded", "AWPCP"), $imgmaxsize);
					// } else
					if(!empty($desired_filename)) {
						$desired_filename = "$desired_filename.$ext";
						
						// echo "<br/>DestDir: " . $destdir . '/' . $desired_filename;
						// echo "<br/>OrgFile: " . $filename;
	
						if (!copy($filename, $destdir . '/' . $desired_filename)) {
						// if (!move_uploaded_file($filename, $destdir . '' . $desired_filename)) {
							$orfilename = $desired_filename;
							$desired_filename = '';
							$awpcpuploaderror = true;
							$awpcpuerror[] .= __(" could not be moved to the destination directory","AWPCP");
							$import_errors[] = "Row no $row_no: could not be moved to the destination directory";
							$import_error_row_flag[$row_no] = true;
							$found_error = true;
						} else {
							awpcp_resizer($desired_filename, $destdir); 
							if(!awpcpcreatethumb($desired_filename, $destdir, $twidth)) {
								$awpcpuploaderror = true;
								$awpcpuerror[].=sprintf(__("Could not create thumbnail image of [ %s ]", "AWPCP"), $desired_filename);
								$import_errors[] = "Row no $row_no: Could not create thumbnail image of [ $desired_filename ]";
								$import_error_row_flag[$row_no] = true;
								$found_error = true;
							}
							
							@chmod($destdir . '/' . $desired_filename, 0644);
							$ctiu = get_total_imagesuploaded($adid);
	
							// if(get_awpcp_option('freepay') == '1') {
							// 	$nia = get_numimgsallowed($adtermid);
							// } else {
							// 	$nia = get_awpcp_option('imagesallowedfree');
							// }
							
							$nia = get_awpcp_option('imagesallowedfree');
	
							if(get_awpcp_option('imagesapprove') == 1) {
								$disabled='1';
							} else {
								$disabled='0';
							}
	
							if($ctiu < $nia && !$found_error) {
								// $query = "INSERT INTO " . $tbl_ad_photos . " SET image_name='$desired_filename',ad_id='$adid',disabled='$disabled'";
								$query = $wpdb->prepare("INSERT INTO " . $tbl_ad_photos . " SET image_name='%s', ad_id='%d', disabled='%d'", $desired_filename, $adid, $disabled);
								if (!$test_import) {
									$wpdb->query($query);
									// if (!($res=@mysql_query($query))) {sqlerrorhandler("(".mysql_errno().") ".mysql_error(), $query, $_SERVER['PHP_SELF'], __LINE__);}
								}
								$file_import_count++;
								// echo "<br/>" . $query;
							}
	
							// $awpcpupdatinserted = true;
	
							// if(!($awpcpupdatinserted)) {
							// 	$awpcpuploaderror = true;
							// 	$awpcpuerror[].=sprintf(__("Could not save the information to the database for [ %s ]", "AWPCP"), $filename);
							// }
						}
					}
				} else {
					$awpcpuploaderror = true;
					$awpcpuerror[].=__("Unknown error encountered uploading image","AWPCP");
				}
			}
			$i++;
	
		} // Close for $i...
		// echo $output;
		// var_dump($awpcpuerror);
		return $file_import_count; //$found_error;
	}
	
	function extract_images($file_name) { //($adid, $adtermid, $nextstep, $adpaymethod, $adaction, $adkey) {
		$output = '';
		global $wpdb, $wpcontentdir, $awpcp_plugin_path;
	
		// $tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";
	
		if(field_exists($field = 'uploadfoldername'))	{
			$theuploadfoldername = get_awpcp_option('uploadfoldername');
		} else {
			$theuploadfoldername = "uploads";
		}
		
		$uploaddir = $wpcontentdir.'/' . $theuploadfoldername .'/';
	
		//Set permission on main upload directory
		require_once $awpcp_plugin_path.'fileop.class.php';
	
		$fileop = new fileop();
		$filedata = fileowner($wpcontentdir);
		$owner = $filedata['name'];
		//Create the upload dir, if necessary:
		if ( !is_dir($uploaddir) ) {
			umask(0);
			mkdir($uploaddir, 0777);
			chown($uploadir, $owner);
		}
		$fileop->set_permission($uploaddir,0777);
		
		$themainawpcpuploaddir = $uploaddir . 'awpcp/';
		$thumbsuploaddir = $uploaddir . 'awpcp/thumbs/';
		$themainawpcpuploadthumbsdir = $uploaddir . 'awpcp/import/';
		//Create the plugin upload directories if they do not exist
		if ( !is_dir($themainawpcpuploaddir) ) {
			umask(0);
			mkdir($themainawpcpuploaddir, 0777);
			chown($themainawpcpuploaddir, $owner);
		}
		
		if ( !is_dir($thumbsuploaddir) ) {
			umask(0);
			mkdir($thumbsuploaddir, 0777);
			chown($thumbsuploaddir, $owner);
		}
	
		if ( !is_dir($themainawpcpuploadthumbsdir) ) {
			umask(0);
			mkdir($themainawpcpuploadthumbsdir, 0777);
			chown($themainawpcpuploadthumbsdir, $owner);
		}
		
		global $current_user;
		$themainawpcpuploadthumbsdir = $uploaddir . 'awpcp/import/' . $current_user->ID;
		
		if ( !is_dir($themainawpcpuploadthumbsdir) ) {
			umask(0);
			mkdir($themainawpcpuploadthumbsdir, 0777);
			chown($themainawpcpuploadthumbsdir, $owner);
		}
	
		$fileop->set_permission($themainawpcpuploaddir, 0777);
		$fileop->set_permission($themainawpcpuploadthumbsdir, 0777);
	
		$awpcp_main_folder = $themainawpcpuploaddir;
		$awpcp_thumb_folder = $themainawpcpuploadthumbsdir;
		
		// $kbm_ZipPost_ZipFile = zip_open ( $file_name ) ;
		// while ( $kbm_ZipPost_EntryFile = zip_read ( $kbm_ZipPost_ZipFile ) ) {
		// 	// var_dump($kbm_ZipPost_EntryFile);
		// 	// echo "FileName: " . zip_entry_name($kbm_ZipPost_EntryFile);
		// 	$file_name = zip_entry_name($kbm_ZipPost_EntryFile);
		// 	if ( zip_entry_filesize($kbm_ZipPost_EntryFile) > 0 ) {
		// 		$kbm_ZipPost_Entry = zip_entry_read($kbm_ZipPost_EntryFile, zip_entry_filesize($kbm_ZipPost_EntryFile));
				
		// 		$fh = fopen($awpcp_thumb_folder . "/" . $file_name, 'w'); // or die("can't open file");
		// 		fwrite($fh, $kbm_ZipPost_Entry);
		// 		fclose($fh);
				
		// 		// var_dump($kbm_ZipPost_Entry);
		// 		zip_entry_close($kbm_ZipPost_EntryFile);
		// 		// KBM_ZipPoster_Create_Post ( $kbm_ZipPost_Configuration, $kbm_ZipPost_Entry );
		// 	} else{
		// 		zip_entry_close($kbm_ZipPost_EntryFile);
		// 	}
		// }

		global $awpcp_plugin_path;
		include("$awpcp_plugin_path/zip.class.php");
		
		// require "zip.class.php"; // Get the zipfile class
		$zipfile = new zipfile; // Create an object
		$zipfile->read_zip($file_name); // Read the zip file
		
		// Now, $zipfile->files is an array containing information about the files
		// Here is an example of it's use
		
		// foreach($zipfile->files as $filea)
		// {
		// 	echo "The contents of {$filea['name']}:\n{$file['data']}\n\n";
		// }
		
		// $kbm_ZipPost_ZipFile = zip_open ( $file_name ) ;
		// while ( $kbm_ZipPost_EntryFile = zip_read ( $kbm_ZipPost_ZipFile ) ) {
		foreach($zipfile->files as $filea) {
			// var_dump($kbm_ZipPost_EntryFile);
			// echo "FileName: " . zip_entry_name($kbm_ZipPost_EntryFile);
			$file_name = $filea['name']; //zip_entry_name($kbm_ZipPost_EntryFile);
			// if ( zip_entry_filesize($kbm_ZipPost_EntryFile) > 0 ) {
				$kbm_ZipPost_Entry = $filea['data']; //zip_entry_read($kbm_ZipPost_EntryFile, zip_entry_filesize($kbm_ZipPost_EntryFile));
				
				$fh = fopen($awpcp_thumb_folder . "/" . $file_name, 'w'); // or die("can't open file");
				fwrite($fh, $kbm_ZipPost_Entry);
				fclose($fh);
				
			// 	var_dump($kbm_ZipPost_Entry);
			// 	zip_entry_close($kbm_ZipPost_EntryFile);
			// 	KBM_ZipPoster_Create_Post ( $kbm_ZipPost_Configuration, $kbm_ZipPost_Entry );
			// } else{
			// 	zip_entry_close($kbm_ZipPost_EntryFile);
			// }
		}
		
		if ($errornofiles) {
			$awpcpuerror[]="<p class=\"uploaderror\">";
			$awpcpuerror[].=__("No file was selected","AWPCP");
			$awpcpuerror[].="</p>";
			// $awpcpuploadformshow=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuerror);
			$output .= $awpcpuploadformshow;
		} else {
			// $output .= awpcpuploadimages($adid,$adtermid,$adkey,$imgmaxsize,$imgminsize,$twidth,$nextstep,$adpaymethod,$adaction,$awpcp_main_folder,'AWPCPfileToUpload');
		}
		// echo $output;
	}
	
	function getCsvData($fileName, $maxLimit=1000) {
	  $csvData = file_get_contents($fileName);
	  $order   = array("\r\n", "\n", "\r");
	  $csvData = str_replace($order, "\n", $csvData);
	  $rows = split ("[\n]", $csvData);
	  $i=0;
	  for($i=0; $i<count($rows) && $i<$maxLimit; $i++) {
	    
	    $row = $rows[$i] . "\n";
	    $len = strlen($row);
	    $cell = '';
	    $col=0;
	    $escaped = false;
	    for($j=0; $j<$len; $j++) {
	      if($row[$j] == '"') {
	        if($j+1<$len && $row[$j+1] == '"') {
	          $cell .= $row[$j];
	          $j++;
	          continue;
	        }
	        $escaped = !$escaped;
	        continue;
	      }
	      if(!$escaped) {
	        // if($row[$j] == ',' || $row[$j] == ';' || $row[$j] == '\r' || $row[$j] == '\n' || $j==$len-1) {
	        if($row[$j] == ',' || $row[$j] == '\r' || $row[$j] == '\n' || $j==$len-1) {
	          if($j==$len-1) {
	            $cell .= $row[$j];
	          }
	          $data[$i][$col] = trim($cell, "\r\n");
	          $cell = '';
	          $col++;
	          $escaped = false;
	          continue;
	        }
	      }
	      $cell .= $row[$j];
	    }
	    
	    // $cols = split ("[,|;]", $rows[$i]);
	    // for($j=0; $j<count($cols); $j++) {
	    //   $data[$i][$j] = $cols[$j];
	    // }
	  }
	  return $data;
	}


	function is_valid_date($month, $day, $year) {
		if (strlen($year) != 4) return false;
		return checkdate($month, $day, $year);
	}

/**
 * Validate extra field values and return value.
 *
 * @param name        field name
 * @param value       field value in CSV file
 * @param row         row number in CSV file
 * @param validate    type of validation
 * @param type        type of input field (Input Box, Textarea Input, Checkbox,
 *                                         SelectMultiple, Select, Radio Button)
 * @param options     list of options for fields that accept multiple values
 * @param enforce     true if the Ad that's being imported belongs to the same category
 *                    that the extra field was assigned to, or if the extra field was
 *                    not assigned to any category.
 *                    required fields may be empty if enforce is false.
 */
function awpcp_validate_extra_field($name, $value, $row, $validate, $type, $options, $enforce) {
	$errors = array();
	$list = null;

	switch ($type) {
		case 'Input Box':
		case 'Textarea Input':
			// nothing special here, proceed with validation
			break;

		case 'Checkbox':
		case 'Select Multiple':
			// value can be any combination of items from options list
			$msg = __("Extra Field $name's value is not allowed in row $row. Allowed values are: %s", 'AWPCP');
			$list = split(';', $value);

		case 'Select':
		case 'Radio Button':
			$list = is_array($list) ? $list : array($value);

			if (!isset($msg)) {
				$msg = __("Extra Field $name's value is not allowed in row $row. Allowed value is one of: %s", 'AWPCP');
			}

			// only attempt to validate if the field is required (has validation)
			if (!empty($validate)) {
				foreach ($list as $item) {
					if (empty($item)) {
						continue;
					}
					if (!in_array($item, $options)) {
						$msg = sprintf($msg, join(', ', $options));
						$errors[] = $msg;
					}
				}
			}

			// extra fields multiple values are stored serialized
			$value = maybe_serialize($list);
			
			break;

		default:
			break;
	}

	if (!empty($errors)) {
		return $errors;
	}

	$list = is_array($list) ? $list : array($value);

	foreach ($list as $k => $item) {
		if (!$enforce && empty($item)) {
			continue;
		}

		switch ($validate) {
			case 'missing':
				if (empty($value)) {
					$errors[] = "Extra Field $name is required in row $row.";
				}
				break;

			case 'url':
				if (!isValidURL($item)) {
					$errors[] = "Extra Field $name must be a valid URL in row $row.";
				}
				break;

			case 'email':
				$regex = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";
				if (!eregi($regex, $item)) {
					$errors[] = "Extra Field $name must be a valid email address in row $row.";
				}
				break;

			case 'numericdeci':
				if (!is_numeric($item)) {
					$errors[] = "Extra Field $name must be a number in row $row.";
				}
				break;

			case 'numericnodeci':
				if (!ctype_digit($item)) {
					$errors[$name] = "Extra Field $name must be an integer number in row $row.";
				}
				break;

			default:
				break;
		}
	}

	if (!empty($errors)) {
		return $errors;
	}
	
	return $value;
}


/**
 * Attempts to find a user by its username or email. If a user can't be
 * found one will be created.
 *
 * @param $username string 	User's username
 * @param $email 	string 	User's email address
 * @param $row 		int 	The index of the row being processed
 * @param $errors 	array 	Used to pass errors back to the caller.
 * @param $messages array 	Used to pass messages back to the caller
 *
 * @return User ID or false on error
 */
function awpcp_csv_importer_get_user_id($username, $email, $row, &$errors=array(), &$messages=array()) {
	global $test_import;
	global $assign_user;
	global $assigned_user;

	static $users = array();

	if (!$assign_user) {
		return '';
	}

	if (isset($users[$username])) {
		return $users[$username];
	}

	$user = empty($username) ? false : get_user_by('login', $username);
	if ($user === false) {
		$user = empty($email) ? false : get_user_by('email', $email);
	} else {
		$users[$user->user_login] = $user->ID;
		return $user->ID;
	}
	if (is_object($user)) {
		$users[$user->user_login] = $user->ID;
		return $user->ID;
	}

	// a default user was selected, do not attempt to create a new one
	if ($assigned_user > 0) {
		return $assigned_user;
	}

	if (empty($username)) {
		$errors[] = sprintf(__("Username is required in row %s."), $row);
		return false;
	} else if (empty($email)) {
		$errors[] = sprintf(__("Contact email is required in row %s."), $row);
		return false;
	}

	$password = wp_generate_password(8, false, false);

	if ($test_import) {
		$result = 1; // fake it!
	} else {
		$result = wp_create_user($username, $password, $email);
	}

	if (is_wp_error($result)) {
		$errors[] = $result->get_error_message();
		return false;
	}
	$users[$username] = $result;

	$message = __("A new user '%s' with email address '%s' and password '%s' was created for row %d.");
	$messages[] = sprintf($message, $username, $email, $password, $row);

	return $result;
}