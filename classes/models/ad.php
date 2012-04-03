<?php 

class AWPCP_Ad {
	
	static function from_object($object) {
		$ad = new AWPCP_Ad;

		$ad->ad_id = $object->ad_id;
		$ad->adterm_id = $object->adterm_id; // fee plan id
		$ad->ad_fee_paid = $object->ad_fee_paid;
		$ad->ad_category_id = $object->ad_category_id;
		$ad->ad_category_parent = $object->ad_category_parent;
		$ad->ad_category_parent_id = $object->ad_category_parent_id;
		$ad->ad_title = $object->ad_title;
		$ad->ad_details = $object->ad_details;
		$ad->ad_contact_name = $object->ad_contact_name;
		$ad->ad_contact_phone = $object->ad_contact_phone;
		$ad->ad_contact_email = $object->ad_contact_email;
		$ad->ad_city = $object->ad_city;
		$ad->ad_state = $object->ad_state;
		$ad->ad_country = $object->ad_country;
		$ad->ad_county_village = $object->ad_county_village;
		$ad->ad_item_price = $object->ad_item_price;
		$ad->ad_views = $object->ad_views;
		$ad->ad_postdate = $object->ad_postdate;
		$ad->ad_last_updated = $object->ad_last_updated;
		$ad->ad_startdate = $object->ad_startdate;
		$ad->ad_enddate = $object->ad_enddate;
		$ad->ad_key = $object->ad_key;
		$ad->ad_transaction_id = $object->ad_transaction_id;
		$ad->user_id = $object->user_id;

		$ad->payment_gateway = $object->payment_gateway;
		$ad->payment_status = $object->payment_status;

		$ad->is_featured_ad = $object->is_featured_ad;
		$ad->flagged = $object->flagged;
		$ad->disabled = $object->disabled;
		$ad->disabled_date = $object->disabled_date;
		$ad->renew_email_sent = $object->renew_email_sent;

		$ad->websiteurl = $object->websiteurl;
		$ad->posterip = $object->posterip;

		return $ad;
	}

	public static function find_by_user_id($id) {
		return AWPCP_Ad::find_by("user_id = " . intval($id));
	}

	public static function find_by_id($id) {
		return AWPCP_Ad::find_by("ad_id = " . intval($id));
	}

	static function find_by($where) {
		$results = AWPCP_Ad::find($where);
		if (!empty($results)) {
			return $results[0];
		}
		return null;
	}

	/**
	 * 
	 */
	static function find($where='1 = 1', $order='id', $offset=0, $results=10) {
		global $wpdb;

		switch ($order) {
			case 'titleza':
				$order = "ad_title DESC";
				break;
			case 'titleaz':
				$order = "ad_title ASC";
				break;
			case 'awaitingapproval':
				$order = "disabled DESC, ad_key DESC";
				break;
			case 'paidfirst':
				$order = "payment_status DESC, ad_key DESC";
				break;
			case 'mostrecent':
				$order = "ad_startdate DESC";
				break;
			case 'oldest':
				$order = "ad_startdate ASC";
				break;
			case 'featured':
				$order = "is_featured_ad DESC, ad_startdate DESC";
				break;
			case 'flagged':
				$order = "ad_startdate DESC";
				$where .= ' AND flagged = 1 ';
				break;
			default:
				$order = 'ad_id DESC';
				break;
		}

		$query = "SELECT * FROM " . AWPCP_TABLE_ADS . " WHERE $where ";
		$query.= "ORDER BY $order LIMIT $offset,$results";

		$items = $wpdb->get_results($query);
		$results = array();

		foreach($items as $item) {
			$results[] = AWPCP_Ad::from_object($item);
		}

		return $results;
	}

	static function count($where='1=1') {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM " . AWPCP_TABLE_ADS . " WHERE $where";
		$n = $wpdb->get_var($query);

		return $n !== FALSE ? $n : 0;
	}

	/**
	 * Finds out if the Ad identified by $id belongs to the user
	 * whose information is stored in $user.
	 * 
	 * @param $id int Ad id
	 * @param $user array See get_currentuserinfo()
	 */
	static function belongs_to_user($id, $user_id) {
		global $wpdb;
		
		if (empty($id) && empty($user_id)) {
			return false;
		}

		$where = $wpdb->prepare("ad_id = %d AND user_id = %d", $id, $user_id);
		$ad = AWPCP_Ad::count($where);
		
		return $ad > 0;
	}

	function save() {
		global $wpdb;

		$data = array('ad_id' => $this->ad_id,
					'adterm_id' => $this->adterm_id,
					'ad_fee_paid' => $this->ad_fee_paid,
					'ad_category_id' => $this->ad_category_id,
					'ad_category_parent_id' => $this->ad_category_parent_id,
					'ad_title' => $this->ad_title,
					'ad_details' => $this->ad_details,
					'ad_contact_name' => $this->ad_contact_name,
					'ad_contact_phone' => $this->ad_contact_phone,
					'ad_contact_email' => $this->ad_contact_email,
					'ad_city' => $this->ad_city,
					'ad_state' => $this->ad_state,
					'ad_country' => $this->ad_country,
					'ad_county_village' => $this->ad_county_village,
					'ad_item_price' => $this->ad_item_price,
					'ad_views' => $this->ad_views,
					'ad_postdate' => $this->ad_postdate,
					'ad_last_updated' => $this->ad_last_updated,
					'ad_startdate' => $this->ad_startdate,
					'ad_enddate' => $this->ad_enddate,
					'ad_key' => $this->ad_key,
					'ad_transaction_id' => $this->ad_transaction_id,
					'user_id' => $this->user_id,

					'payment_gateway' => $this->payment_gateway,
					'payment_status' => $this->payment_status,

					'is_featured_ad' => $this->is_featured_ad,
					'flagged' => $this->flagged,
					'disabled' => $this->disabled,
					'disabled_date' => $this->disabled_date,
					'renew_email_sent' => $this->renew_email_sent,

					'websiteurl' => $this->websiteurl,
					'posterip' => $this->posterip);

		if (empty($this->ad_id)) {
			$result = $wpdb->insert(AWPCP_TABLE_ADS, $data);
			$this->ad_id = $wpdb->insert_id;
		} else {
			$result = $wpdb->update(AWPCP_TABLE_ADS, $data, array('ad_id' => $this->ad_id));
		}

		return $result;
	}

	function get_category_name() {
		if (!isset($this->category_name))
			$this->category_name = get_adcatname($object->category_id);
		return $this->category_name;
	}

	function get_fee_plan_name() {
		return awpcp_get_fee_plan_name($this->ad_id, $this->adterm_id);
	}

	function get_start_date() {
		if (!empty($this->ad_startdate))
			return date('M d Y', strtotime($this->ad_startdate));
		return '';
	}

	function get_end_date() {
		if (!empty($this->ad_enddate))
			return date('M d Y', strtotime($this->ad_enddate));
		return '';
	}

	function has_expired($date=null) {
		$end_date = strtotime(date('Y-m-d', strtotime($this->ad_enddate)));
		$date = is_null($date) ? strtotime(date('Y-m-d', time())) : $date;		
		return $end_date < $date;
	}

	function is_about_to_expire() {
		$threshold = get_awpcp_option('ad-renew-email-threshold');
		$date = strtotime(date('Y-m-d', strtotime(sprintf('today + %d days', $threshold))));
		return $this->has_expired($date);
	}

	function get_payment_status() {
		if (!empty($this->payment_status))
			return $this->payment_status;
		return 'N/A';
	}

	function get_total_images_uploaded() {
		return get_total_imagesuploaded($this->ad_id);
	}
}

function awpcp_get_fee_plan_name($id, $ad_term_id) {
	global $wpdb;
	if (!empty($ad_term_id)) {
		$query = 'SELECT adterm_name FROM ' . AWPCP_TABLE_ADFEES . ' ';
		$query.= 'WHERE adterm_id = ' . $ad_term_id;
		return $wpdb->get_var($query);
	} else {
		return apply_filters('awpcp-ad-payment-term-name', '', $id);
	}
}