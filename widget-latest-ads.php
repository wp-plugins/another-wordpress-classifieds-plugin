<?php

### Function: Init AWPCP Latest Classified Headlines Widget
function init_awpcpsbarwidget() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}

	### Function: AWPCP Latest Classified Headlines Widget
	function widget_awpcplatestads($args) {
		$output = '';
		extract($args);
		$limit=$args[0];
		$title=$args[1];

		$options = get_option('widget_awpcplatestads');
		if(!isset($limit)) {
			$limit = htmlspecialchars(stripslashes($options['hlimit']));
		}

		if(!isset($title)) {
			$title = htmlspecialchars(stripslashes($options['title']));
		}

		if(ads_exist()) {
			$awpcp_sb_widget_beforecontent=get_awpcp_option('sidebarwidgetbeforecontent');
			$awpcp_sb_widget_aftercontent=get_awpcp_option('sidebarwidgetaftercontent');
			$awpcp_sb_widget_beforetitle=get_awpcp_option('sidebarwidgetbeforetitle');
			$awpcp_sb_widget_aftertitle=get_awpcp_option('sidebarwidgetaftertitle');

			if(isset($awpcp_sb_widget_beforecontent) && !empty($awpcp_sb_widget_beforecontent))
			{$awpcp_sb_widget_beforecontent="$awpcp_sb_widget_beforecontent";}
			else{$awpcp_sb_widget_beforecontent="";}

			if(isset($awpcp_sb_widget_aftercontent) && !empty($awpcp_sb_widget_aftercontent))
			{$awpcp_sb_widget_aftercontent="$awpcp_sb_widget_aftercontent";}
			else{$awpcp_sb_widget_aftercontent="";}

			if(isset($awpcp_sb_widget_beforetitle) && !empty($awpcp_sb_widget_beforetitle))
			{$awpcp_sb_widget_beforetitle="$awpcp_sb_widget_beforetitle";}
			else{$awpcp_sb_widget_beforetitle="";}

			if(isset($awpcp_sb_widget_aftertitle) && !empty($awpcp_sb_widget_aftertitle))
			{$awpcp_sb_widget_aftertitle="$awpcp_sb_widget_aftertitle";}
			else{$awpcp_sb_widget_aftertitle="";}

			if(isset($awpcp_sb_widget_beforecontent) && !empty($awpcp_sb_widget_beforecontent))
			{
				$output .= "$awpcp_sb_widget_beforecontent";
			}
			if(isset($awpcp_sb_widget_beforetitle) && !empty($awpcp_sb_widget_beforetitle))
			{
				$output .= "$awpcp_sb_widget_beforetitle";
			}

			$output .= "$title";
			if(isset($awpcp_sb_widget_aftertitle) && !empty($awpcp_sb_widget_aftertitle))
			{
				$output .= "$awpcp_sb_widget_aftertitle";
			}

			if (function_exists('awpcp_sidebar_headlines'))
			{
				$output .= '<ul>'."\n";
				$output .= awpcp_sidebar_headlines($limit, $options['showimages'], $options['showblank']);
				$output .= '</ul>'."\n";
			}

			if(isset($awpcp_sb_widget_aftercontent) && !empty($awpcp_sb_widget_aftercontent))
			{
				$output .= "$awpcp_sb_widget_aftercontent";
			}
		}
		//Echo OK here
		echo $output;
	}

	### Function: AWPCP Latest Classified Headlines Widget Options
	function widget_awpcplatestads_options() {
		$output = '';
		$options = get_option('widget_awpcplatestads');
		if (!is_array($options)) {
			$options = array('hlimit' => '10', 'title' => __('Latest Classifieds', 'AWPCP'), 'showimages' => '1', 'showblank' => '1');
		}
		if ($_POST['awpcplatestads-submit']) {
			$options['hlimit'] = intval($_POST['awpcpwid-limit']);
			$options['title'] = strip_tags($_POST['awpcpwid-title']);
			$options['showimages'] = $_POST['awpcpwid-showimages'] == '1' ? 1 : 0;
			$options['showblank'] = $_POST['awpcpwid-showblank'] == '1' ? 1 : 0;
			//$options['beforewidget'] = $_POST['awpcpwid-beforewidget'];
			//$options['afterwidget'] = $_POST['awpcpwid-afterwidget'];
			//$options['beforetitle'] = $_POST['awpcpwid-beforetitle'];
			//$options['aftertitle'] = $_POST['awpcpwid-aftertitle'];
			update_option('widget_awpcplatestads', $options);
		}
		$output .= '<p><label for="awpcpwid-title">'.__('Widget Title', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-title" size="35" name="awpcpwid-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" />';
		$output .= '<p><label for="awpcpwid-limit">'.__('Number of Items to Show', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" size="5" id="awpcpwid-limit" name="awpcpwid-limit" value="'.htmlspecialchars(stripslashes($options['hlimit'])).'" />';
		$output .= '<p><label for="awpcpwid-showimages">'.__('Show Thumbnails in Widget?', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="awpcpwid-showimages" name="awpcpwid-showimages" value="1" '. ($options['showimages'] == 1 ? 'checked=\"true\"' : '') .' />';
		$output .= '<p><label for="awpcpwid-showblank">'.__('Show \"No Image\" PNG when ad has no picture (improves layout)?', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="checkbox" id="awpcpwid-showblank" name="awpcpwid-showblank" value="1" '. ($options['showblank'] == 1 ? 'checked=\"true\"' : '') .' />';
		//$output .= '<p><label for="awpcpwid-beforewidget">'.__('Before Widget HTML', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-beforewidget" size="35" name="awpcpwid-beforewidget" value="'.htmlspecialchars(stripslashes($options['beforewidget'])).'" />';
		//$output .= '<p><label for="awpcpwid-afterwidget">'.__('After Widget HTML<br>Exclude all quotes<br>(<del>class="XYZ"</del> => class=XYZ)', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-afterwidget" size="35" name="awpcpwid-afterwidget" value="'.htmlspecialchars(stripslashes($options['afterwidget'])).'" />';
		//$output .= '<p><label for="awpcpwid-beforetitle">'.__('Before title HTML', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-beforetitle" size="35" name="awpcpwid-beforetitle" value="'.htmlspecialchars(stripslashes($options['beforetitle'])).'" />';
		//$output .= '<p><label for="awpcpwid-aftertitle">'.__('After title HTML', 'AWPCP').':</label>&nbsp;&nbsp;&nbsp;<input type="text" id="awpcpwid-aftertitle" size="35" name="awpcpwid-aftertitle" value="'.htmlspecialchars(stripslashes($options['aftertitle'])).'" />';
		$output .= '<input type="hidden" id="awpcplatestads-submit" name="awpcplatestads-submit" value="1" />'."\n";
		//Echo ok here:
		echo $output;
	}

	// Register Widgets
	register_sidebar_widget('AWPCP Latest Ads', 'widget_awpcplatestads');
	//wp_register_sidebar_widget('awpcp-latest-ads', 'AWPCP Latest Ads', 'widget_awpcplatestads');

	// register_widget_control('AWPCP Latest Ads', 'widget_awpcplatestads_options', 350, 120);
	$options = array('width' => 350, 'height' => 120);
	wp_register_widget_control('awpcp-latest-ads', 'AWPCP Latest Ads', 
		'widget_awpcplatestads_options', $options);
}

function awpcp_sidebar_headlines($limit, $showimages, $showblank) {
	$output = '';
	global $wpdb,$awpcp_imagesurl;
	$tbl_ads = $wpdb->prefix . "awpcp_ads";

	$awpcppage=get_currentpagename();
	$awpcppagename = sanitize_title($awpcppage, $post_ID='');
	$permastruc=get_option('permalink_structure');
	$quers=setup_url_structure($awpcppagename);
	$displayadthumbwidth=get_awpcp_option('displayadthumbwidth');

	if(!isset($limit) || empty($limit)){
		$limit = 10;
	}

	$query = "SELECT ad_id,ad_title,ad_details FROM ". AWPCP_TABLE_ADS ." ";
	$query.= "WHERE ad_title <> '' AND disabled = '0' ";
	// $query.= "AND (flagged IS NULL OR flagged = 0) ";
	$query.= "ORDER BY ad_postdate DESC, ad_id DESC LIMIT ". $limit . "";
	$res = awpcp_query($query, __LINE__);

	while ($rsrow=mysql_fetch_row($res)) {
		$ad_id=$rsrow[0];
		$modtitle=cleanstring($rsrow[1]);
		$modtitle=add_dashes($modtitle);
		$hasNoImage = true;
		$url_showad=url_showad($ad_id);

		$ad_title="<a href=\"$url_showad\">".stripslashes($rsrow[1])."</a>";
		if (!$showimages) {
			//Old style, list only:
			$output .= "<li>$ad_title</li>";
		} else {
			//New style, with images and layout control:
			$awpcp_image_display="<a class=\"self\" href=\"$url_showad\">";
			if (get_awpcp_option('imagesallowdisallow'))
			{
				$totalimagesuploaded=get_total_imagesuploaded($ad_id);
				if ($totalimagesuploaded >=1)
				{
					$awpcp_image_name=get_a_random_image($ad_id);
					if (isset($awpcp_image_name) && !empty($awpcp_image_name))
					{
						$awpcp_image_name_srccode="<img src=\"".AWPCPTHUMBSUPLOADURL."/$awpcp_image_name\" border=\"0\" width=\"$displayadthumbwidth\" alt=\"$modtitle\"/>";
						$hasNoImage = false;
					}
					else
					{
						$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
					}
				}
				else
				{
					$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
				}
			}
			else
			{
				$awpcp_image_name_srccode="<img src=\"$awpcp_imagesurl/adhasnoimage.gif\" width=\"$displayadthumbwidth\" border=\"0\" alt=\"$modtitle\"/>";
			}
			$ad_teaser = stripslashes(substr($rsrow[2], 0, 50)) . "...";
			$read_more = "<a href=\"$url_showad\">[" . __("Read more", "AWPCP") . "]</a>";
			$awpcp_image_display.="$awpcp_image_name_srccode</a>";
			if (!$showblank && $hasNoImage) {
				//Don't put anything there
				$awpcp_image_display = '';
			}
			$output .= "<li><div class='awpcplatestbox'><div class='awpcplatestthumb'>$awpcp_image_display</div><p><h3>$ad_title</h3></p><p>$ad_teaser<br/>$read_more</p><div class='awpcplatestspacer'></div></div></li>";
		}
	}
	return $output;
}