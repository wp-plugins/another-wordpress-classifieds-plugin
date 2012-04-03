<?php 

class AWPCP_Search_Ads_Page {
	// true if the shortcode handler was executed
	public $active = false;
	
	public function AWPCP_Search_Ads_Page() {
		// used to get list of relevant Regions in Search Ads page
		// to update dropdowns
		// TODO: this actions probably belong to the RegionControl module
		// but I rather not touch it right now.
		add_action('wp_ajax_awpcp-search-ads-get-regions', array($this, 'regions_list'));
		add_action('wp_ajax_nopriv_awpcp-search-ads-get-regions', array($this, 'regions_list'));

		add_action('wp_footer', array($this, 'print_scripts'));
		add_action('admin_footer', array($this, 'print_scripts'));

		$src = AWPCP_URL . 'js/region-control.js';
		wp_register_script('awpcp-region-control', $src, array('jquery'), '1.1', true);
	}

	public function print_scripts() {
		if (!$this->active) {
			return;
		}
		wp_print_scripts('awpcp-region-control');
		wp_print_scripts('awpcp-extra-fields');
	}

	public function dispatch() {
		$action = awpcp_request_param('a');

		switch ($action) {
			case 'dosearch':
				$html = dosearch();
				break;

			case 'cregs':
				unset($_SESSION['regioncountryID']);
				unset($_SESSION['regionstatownID']);
				unset($_SESSION['regioncityID']);
				unset($_SESSION['theactiveregionid']);

			case 'searchads':
			default:
				$html = $this->render($keywordphrase='', $searchname='', $searchcity='',
											$searchstate='', $searchcountry='', $searchcountyvillage='',
											$searchcategory='', $searchpricemin='', $searchpricemax='',
											$message='');
				break;
		}

		return $html;
	}

	public function render($keywordphrase='', $searchname='', $searchcity='',
				$searchstate='', $searchcountry='', $searchcountyvillage='', 
				$searchcategory='', $searchpricemin='', $searchpricemax='', 
				$message='')
	{

		global $hasregionsmodule, $hasextrafieldsmodule;

		$searchadspageid = awpcp_get_page_id_by_ref('search-ads-page-name');

		$url = get_permalink($searchadspageid);
		
		$region = '';
		if ($hasregionsmodule == 1) {
			if (isset($_SESSION['regioncityID']) && !empty($_SESSION['regioncityID'])) {
				$searchcity = get_theawpcpregionname($_SESSION['regioncityID']);
				$region .= $searchcity;
			}
			if (isset($_SESSION['regionstatownID']) && !empty($_SESSION['regionstatownID'])) {
				$searchstate = get_theawpcpregionname($_SESSION['regionstatownID']);
				$region .= " " . $searchstate;
			}
			if (isset($_SESSION['regioncountryID']) && !empty($_SESSION['regioncountryID'])) {
				$searchcountry = get_theawpcpregionname($_SESSION['regioncountryID']);
				$region .= " " . $searchcountry;
			}
		}

		if (!isset($message) || empty($message)) {
			$message = __("Use the form below to conduct a broad or narrow search. For a broader search enter fewer parameters. For a narrower search enter as many parameters as needed to limit your search to a specific criteria","AWPCP");
		}

		$allcategories = get_categorynameidall($searchcategory);

		$query = array('country' => $searchcountry, 'state' => $searchstate,
					   'city' => $searchcity, 'countyvillage' => $searchcountyvillage);
		$translations = array('country' => 'searchcountry', 'state' => 'searchstate',
						  	  'city' => 'searchcity', 'county' => 'searchcountyvillage');
		if ($hasregionsmodule) {
			$region_fields = awpcp_region_control_form_fields($query, $translations);
		} else {
			$region_fields = awpcp_region_form_fields($query, $translations);
		}

		$isadmin = checkifisadmin(); // XXX: no needed?

		ob_start();
			include(AWPCP_DIR . 'frontend/templates/page-search-ads.tpl.php');
			$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function regions_list() {
		$field = awpcp_request_param('field', '', $_GET);
		$filter = awpcp_request_param('filterby', '', $_GET);
		$value = awpcp_request_param('value', '', $_GET);

		switch ($field) {
			case 'State':
			case 'City':
			case 'County':
				$entries = awpcp_region_control_get_entries($field, $value, $filter);
				break;
			case 'Country':
			default:
				$entries = array();
		}

		$html = awpcp_region_control_render_options($entries);
		if (count($entries) > 1) {
			$html = '<option value="">' . __('Select Option', 'AWPCP') . '</option>' . $html;
		}

		$response = array('status' => 'ok',
						  'entries' => $entries,
						  'html' => $html);

		header( "Content-Type: application/json" );
    	echo json_encode($response);
    	die();
	}
}


function load_ad_search_form($keywordphrase='', $searchname='', $searchcity='',
				$searchstate='', $searchcountry='', $searchcountyvillage='', 
				$searchcategory='', $searchpricemin='', $searchpricemax='', 
				$message='') 
{
	global $awpcp;
	$page = $awpcp->pages->search_ads;

	return $page->render($keywordphrase, $searchname, $searchcity,
						 $searchstate, $searchcountry, $searchcountyvillage, 
						 $searchcategory, $searchpricemin, $searchpricemax, 
						 $message);
}