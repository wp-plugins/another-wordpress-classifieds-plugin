<?php 

function awpcp_cron_schedules($schedules) {
	$schedules['monthly'] = array(
		'interval'=> 2592000,
		'display'=>  __('Once Every 30 Days')
	);
	return $schedules;
}

// ensure we get the expiration hooks scheduled properly:
function awpcp_schedule_activation() {
	if (!wp_next_scheduled('doadexpirations_hook')) {
		wp_schedule_event(time(), 'hourly', 'doadexpirations_hook');
	}

	if (!wp_next_scheduled('doadcleanup_hook')) {
		wp_schedule_event(time(), 'monthly', 'doadcleanup_hook');
	}
	
	if (!wp_next_scheduled('awpcp_ad_renewal_email_hook')) {
		wp_schedule_event(time(), 'daily', 'awpcp_ad_renewal_email_hook');
	}

	if (!wp_next_scheduled('awpcp-clean-up-payment-transactions')) {
		wp_schedule_event(time(), 'daily', 'awpcp-clean-up-payment-transactions');
	}

	add_action('doadexpirations_hook', 'doadexpirations');
	add_action('doadcleanup_hook', 'doadcleanup');
	add_action('awpcp_ad_renewal_email_hook', 'awpcp_ad_renewal_email');
	add_action('awpcp-clean-up-payment-transactions', 'awpcp_clean_up_payment_transactions');
	
	// wp_schedule_event(time() + 60, 'hourly', 'doadexpirations_hook');
	// wp_schedule_event(time() + 10, 'monthly', 'doadcleanup_hook');
	// wp_schedule_event(time(), 'daily', 'awpcp_ad_renewal_email_hook');
	// wp_schedule_event(time(), 'daily', 'awpcp-clean-up-payment-transactions');

	// debug('System date is: ' . date('d-m-Y H:i:s'),
	// 	  'Ad Expiration: ' . date('d-m-Y H:i:s', wp_next_scheduled('doadexpirations_hook')),
	// 	  'Ad Cleanup: ' . date('d-m-Y H:i:s', wp_next_scheduled('doadcleanup_hook')),
	// 	  'Ad Renewal Email: ' . date('d-m-Y H:i:s', wp_next_scheduled('awpcp_ad_renewal_email_hook')),
	// 	  'Payment transactions: ' . date('d-m-Y H:i:s', wp_next_scheduled('awpcp-clean-up-payment-transactions')));
}


/*
 * Function to disable ads run hourly
 */
function doadexpirations() {
	global $wpdb, $nameofsite, $siteurl, $thisadminemail;

	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

	$awpcp_from_header = "From: ". $nameofsite . " <" . $thisadminemail . ">\r\n";

	$adexpireafter = get_awpcp_option('addurationfreemode');
	$notify_admin = get_awpcp_option('notifyofadexpired');

	_log("Checking ad expirations");

	// disable the ads or delete the ads?
	$disable_ads = get_awpcp_option('autoexpiredisabledelete');
	// 1 = disable, 0 = delete

	$adstodelete = '';

	$sql = 'select ad_id from ' . AWPCP_TABLE_ADS . ' where ad_enddate <= NOW() and disabled != 1';
	$ads = $wpdb->get_results($sql, ARRAY_A);

	$expiredid = array();

	$subject = get_awpcp_option('adexpiredsubjectline');
	$bodybase = get_awpcp_option('adexpiredbodymessage');

	_log("Expiring ads: " . $adstodelete);

	if ($ads) {
		foreach ($ads as $ad) {

			$expiredid[] = $ad['ad_id'];
			$adid = $ad['ad_id'];

			if( get_awpcp_option('notifyofadexpiring') == 1 && $disable_ads ) {

				_log("Processing Notification for ad: " . $adid);

				$adcontact=get_adpostername($adid);
				_log("Got poster name for ad: " . $adid);

				$awpcpnotifyexpireemail=get_adposteremail($adid);
				_log("Got poster email for ad: " . $adid);

				if ('' == $awpcpnotifyexpireemail) continue; // no email addy, can't send a message without it.

				$adtitle=get_adtitle($adid);
				_log("Got title for ad: " . $adid);

				$adstartdate = date("D M j Y G:i:s", strtotime( get_adstartdate($adid) ) );
				_log("Formatted date for ad: " . $adid);

				$awpcpadexpiredsubject = $subject;
				$awpcpadexpiredbody = $bodybase;
				$awpcpadexpiredbody.="\n\n";
				$awpcpadexpiredbody.=__("Listing Details", "AWPCP");
				$awpcpadexpiredbody.="\n\n";
				$awpcpadexpiredbody.=__("Ad Title:", "AWPCP");
				$awpcpadexpiredbody.=" $adtitle";
				$awpcpadexpiredbody.="\n\n";
				$awpcpadexpiredbody.=__("Posted:", "AWPCP");
				$awpcpadexpiredbody.=" $adstartdate";
				$awpcpadexpiredbody.="\n\n";
				$awpcpadexpiredbody.=__("Renew your ad by visiting:", "AWPCP");
				$awpcpadexpiredbody.=" $siteurl";
				$awpcpadexpiredbody.="\n\n";

				awpcp_process_mail(
					$thisadminemail,
					$awpcpnotifyexpireemail,
					$awpcpadexpiredsubject,
					$awpcpadexpiredbody,
					$nameofsite,
					$thisadminemail
				);

				// SEND THE ADMIN A NOTICE TOO?
				if ( $notify_admin ) {
					awpcp_process_mail(
						$awpcpsenderemail=$thisadminemail,
						$awpcpreceiveremail=$thisadminemail,
						$awpcpemailsubject=$awpcpadexpiredsubject,
						$awpcpemailbody=$awpcpadexpiredbody,
						$awpcpsendername=$nameofsite,
						$awpcpreplytoemail=$thisadminemail
					);
				}

				_log("DONE Processing Notification for ad: " . $adid);
			}

			_log("Processing Notifications complete");
		}

		$adstodelete = join(',' , $expiredid);

	} else {
		_log("No ads expiring now.");
	}


	if ('' != $adstodelete) {
		_log("Now doing expiration query");

		// disable images
		$query = 'update '.$tbl_ad_photos." set disabled=1 WHERE ad_id IN ($adstodelete)";
		_log("Running query: " . $query);

		$res = awpcp_query($query, __LINE__);
		_log("Disabled photos result is " . $res);
	  
		// Disable the ads
		$query="UPDATE ".$tbl_ads." set disabled=1, disabled_date = NOW() WHERE ad_id IN ($adstodelete)";
		_log("Running query: " . $query);

		$res = awpcp_query($query, __LINE__);
		_log("Disabled ads result is " . $res);
	}
}


/*
 * Function run once per month to cleanup disabled / deleted ads.
 */
function doadcleanup() {
	global $wpdb;

	//If they set the 'disable instead of delete' flag, we just return and don't do anything here.
	if (get_awpcp_option('autoexpiredisabledelete') == 1) return;

	$tbl_ads = $wpdb->prefix . "awpcp_ads";
	$tbl_ad_photos = $wpdb->prefix . "awpcp_adphotos";

	// Get the IDs of the ads to be deleted (those that are disabled more than 30 days ago)
	$query="SELECT ad_id FROM ".$tbl_ads." WHERE disabled=1 and (disabled_date + INTERVAL 30 DAY) < CURDATE()";
	$res = awpcp_query($query, __LINE__);

	$expiredid=array();
	if (mysql_num_rows($res)) {
		while ($rsrow=mysql_fetch_row($res)) {
			$expiredid[]=$rsrow[0];
		}
	}

	$adstodelete = join("','", $expiredid);
	$query = "SELECT image_name FROM " . $tbl_ad_photos . " WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);
	$rowcount = mysql_num_rows($res);

	for ($i=0; $i < $rowcount; $i++) {
		$photo=mysql_result($res,$i,0);

		if (file_exists(AWPCPUPLOADDIR.'/'.$photo)) {
			@unlink(AWPCPUPLOADDIR.'/'.$photo);
		}

		if (file_exists(AWPCPTHUMBSUPLOADDIR.'/'.$photo)) {
			@unlink(AWPCPTHUMBSUPLOADDIR.'/'.$photo);
		}
	}

	$query = "DELETE FROM " . $tbl_ad_photos . " WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);

	// Delete the ads
	$query = "DELETE FROM " . $tbl_ads . " WHERE ad_id IN ('$adstodelete')";
	$res = awpcp_query($query, __LINE__);
}


/**
 * Check if any Ad is about to expire and send an email to the poster.
 *
 * This functions runs daily.
 */
function awpcp_ad_renewal_email() {
	global $wpdb, $nameofsite, $thisadminemail;

	if (!(get_awpcp_option('sent-ad-renew-email') == 1)) {
		return;
	}

	$threshold = intval(get_awpcp_option('ad-renew-email-threshold'));

	$query = 'ad_enddate <= ADDDATE(NOW(), INTERVAL %d DAY) AND ';
	$query.= 'disabled != 1 AND renew_email_sent != 1';
	$ads = AWPCP_Ad::find($wpdb->prepare($query, $threshold));

	$subject = get_awpcp_option('renew-ad-email-subject');
	$subject = sprintf($subject, $threshold);

	$panel_url = admin_url('admin.php?page=awpcp-panel');
	$renew_ad_url = get_permalink(awpcp_get_page_id_by_ref('renew-ad-page-name'));

	foreach ($ads as $ad) {
		if (get_awpcp_option('enable-user-panel') == 1) {
			$href = $panel_url;
		} else {			
			$href = add_query_arg(array('ad_id' => $ad->ad_id), $renew_ad_url);
		}		

		// awpcp_process_mail doesn't support HTML
		$body = get_awpcp_option('renew-ad-email-body');
		$body = sprintf($body, $threshold) . "\n\n";
		$body.= __('Listing Details are below:', 'AWPCP') . "\n\n";
		$body.= __('Title', 'AWPCP') . ": " . $ad->ad_title . "\n";
		$body.= __('Posted on', 'AWPCP') . ": " . $ad->get_start_date() . "\n";
		$body.= __('Expires on', 'AWPCP') . ": " . $ad->get_end_date() . "\n\n";
		$body.= __('You can renew your Ad visiting this link:', 'AWPCP') . $href;

		$result = awpcp_process_mail($thisadminemail, $ad->ad_contact_email, $subject, 
						   $body, $nameofsite, $thisadminemail);

		if ($result == 1) {
			$ad->renew_email_sent = true;
			$ad->save();
		}
	}
}


/**
 * Remove incomplete payment transactions
 */
function awpcp_clean_up_payment_transactions() {
	global $wpdb;

	$sql = 'SELECT option_name, option_value FROM ' . $wpdb->options . ' ';
	$sql.= "WHERE option_name LIKE 'awpcp-payment-transaction-%%' ";
	$sql.= 'ORDER BY option_id';

	$results = $wpdb->get_results($wpdb->prepare($sql));

	$threshold = current_time('mysql') - 2592000;
	$threshold = current_time('mysql') - 6*60*60;

	foreach ((array) $results as $row) {
		$name = $row->option_name;
		$attributes = maybe_unserialize($row->option_value);

		$created = strtotime(awpcp_array_data('__created__', false, $attributes));
		$updated = strtotime(awpcp_array_data('__updated__', false, $attributes));
		$completed = strtotime(awpcp_array_data('completed', false, $attributes));

		if ($created && $completed) {
			// debug('completed', date('Y-m-d', $created), date('Y-m-d', $threshold), $name, $attributes);
		}

		if ((!$created || $created < $threshold) && !$completed) {
			// debug('delete', date('Y-m-d', $created), date('Y-m-d', $threshold), $name, $attributes);
			delete_option($name);
		}
	}
}