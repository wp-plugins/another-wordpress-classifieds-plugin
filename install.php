<?php 
/**
 * Installation and Upgrade functions
 */

global $wpdb;

define('AWPCP_TABLE_ADFEES', $wpdb->prefix . "awpcp_adfees");
define('AWPCP_TABLE_ADS', $wpdb->prefix . "awpcp_ads");
define('AWPCP_TABLE_ADSETTINGS', $wpdb->prefix . "awpcp_adsettings");
define('AWPCP_TABLE_ADPHOTOS', $wpdb->prefix . "awpcp_adphotos");
define('AWPCP_TABLE_CATEGORIES', $wpdb->prefix . "awpcp_categories");
define('AWPCP_TABLE_PAGES', $wpdb->prefix . "awpcp_pages");
define('AWPCP_TABLE_PAGENAME', $wpdb->prefix . "awpcp_pagename");


class AWPCP_Installer {

    private static $instance = null;
    
    private function AWPCP_Installer() {
        // pass
    }

    public static function instance() {
        if (is_null(AWPCP_Installer::$instance)) {
            AWPCP_Installer::$instance = new AWPCP_Installer();
        }
        return AWPCP_Installer::$instance;
    }

    public function column_exists($table, $column) {
        global $wpdb;
        $wpdb->hide_errors();
        $result = $wpdb->query("SELECT `$column` FROM $table");
        $wpdb->show_errors();
        return $result !== false;
    }

    // public function activate() {
    //     $this->install();
    //     // not needed anymore, but some plugins may check for it
    //     update_option('awpcp_installationcomplete', 0);
    // }

    /**
     * Creates AWPCP tables.
     *
     * If is not a fresh install it calls $this->upgrade().
     */
    public function install() {
        global $wpdb, $awpcp_db_version;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $version = get_option('awpcp_db_version');

        // if table exists, this is an upgrade
        $table = $wpdb->get_var("SHOW TABLES LIKE '" . AWPCP_TABLE_CATEGORIES . "'");
        if (strcmp($table, AWPCP_TABLE_CATEGORIES) == 0) {
            return $this->upgrade($version, $awpcp_db_version);
        }
            
        // // Ooops - page already exists - abort this function and queue an admin warning message
        // if (findpagebyname('AWPCP')) {
        //  update_option('awpcp_pagename_warning', 1);
        //      return;
        // } else { 
        //  delete_option('awpcp_pagename_warning');
        // }


        // create Categories table
        $sql = "CREATE TABLE " . AWPCP_TABLE_CATEGORIES . " (
          `category_id` int(10) NOT NULL AUTO_INCREMENT,
          `category_parent_id` int(10) NOT NULL,
          `category_name` varchar(255) NOT NULL DEFAULT '',
          `category_order` int(10) NULL DEFAULT '0',
          PRIMARY KEY  (`category_id`)
        ) ENGINE=MyISAM;";
        dbDelta($sql);


        // create Ad Fees table
        $sql = "CREATE TABLE " . AWPCP_TABLE_ADFEES . " (
          `adterm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `adterm_name` varchar(100) NOT NULL DEFAULT '',
          `amount` float(6,2) unsigned NOT NULL DEFAULT '0.00',
          `recurring` tinyint(1) unsigned NOT NULL DEFAULT '0',
          `rec_period` int(5) unsigned NOT NULL DEFAULT '0',
          `rec_increment` varchar(5) NOT NULL DEFAULT '',
          `buys` int(10) unsigned NOT NULL DEFAULT '0',
          `imagesallowed` int(5) unsigned NOT NULL DEFAULT '0',
          `is_featured_ad_pricing` tinyint(1) DEFAULT NULL,
          `categories` TEXT DEFAULT NULL,
          PRIMARY KEY  (`adterm_id`)
        ) ENGINE=MyISAM;";
        dbDelta($sql);


        // create Ads table
        $sql = "CREATE TABLE " . AWPCP_TABLE_ADS . " (
          `ad_id` int(10) NOT NULL AUTO_INCREMENT,
          `adterm_id` int(10) NOT NULL DEFAULT '0',
          `ad_fee_paid` float(7,2) NOT NULL,
          `ad_category_id` int(10) NOT NULL,
          `ad_category_parent_id` int(10) NOT NULL,
          `ad_title` varchar(255) NOT NULL DEFAULT '',
          `ad_details` text NOT NULL,
          `ad_contact_name` varchar(255) NOT NULL DEFAULT '',
          `ad_contact_phone` varchar(255) NOT NULL DEFAULT '',
          `ad_contact_email` varchar(255) NOT NULL DEFAULT '',
          `websiteurl` varchar( 375 ) NOT NULL,
          `ad_city` varchar(255) NOT NULL DEFAULT '',
          `ad_state` varchar(255) NOT NULL DEFAULT '',
          `ad_country` varchar(255) NOT NULL DEFAULT '',
          `ad_county_village` varchar(255) NOT NULL DEFAULT '',
          `ad_item_price` int(25) NOT NULL,
          `ad_views` int(10) NOT NULL DEFAULT 0,
          `ad_postdate` date NOT NULL DEFAULT '0000-00-00',
          `ad_last_updated` date NOT NULL,
          `ad_startdate` datetime NOT NULL,
          `ad_enddate` datetime NOT NULL,
          `disabled` tinyint(1) NOT NULL DEFAULT '0',
          `disabled_date` datetime,
          `ad_key` varchar(255) NOT NULL DEFAULT '',
          `ad_transaction_id` varchar(255) NOT NULL DEFAULT '',
          `payment_gateway` varchar(255) NOT NULL DEFAULT '',
          `payment_status` varchar(255) NOT NULL DEFAULT '',
          `is_featured_ad` tinyint(1) DEFAULT NULL,
          `posterip` varchar(15) NOT NULL DEFAULT '',
          `flagged` tinyint(1) NOT NULL DEFAULT 0,
          `user_id` INT(10) DEFAULT NULL,
          `renew_email_sent` TINYINT(1) NOT NULL DEFAULT 0,
          FULLTEXT KEY `titdes` (`ad_title`,`ad_details`),
          PRIMARY KEY  (`ad_id`)
        ) ENGINE=MyISAM;";
        dbDelta($sql);


        // Create Ad Settings table
        // $sql = "CREATE TABLE " . AWPCP_TABLE_ADSETTINGS . " (
        //   `config_option` varchar(50) NOT NULL DEFAULT '',
        //   `config_value` text NOT NULL,
        //   `config_diz` text NOT NULL,
        //   `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1',
        //   `option_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
        //   PRIMARY KEY  (`config_option`)
        // ) ENGINE=MyISAM COMMENT='0-checkbox, 1-text,2-textarea';";
        // dbDelta($sql);


        // create Ad Photos table
        $sql = "CREATE TABLE " . AWPCP_TABLE_ADPHOTOS . " (
          `key_id` int(10) NOT NULL AUTO_INCREMENT,
          `ad_id` int(10) unsigned NOT NULL DEFAULT '0',
          `image_name` varchar(100) NOT NULL DEFAULT '',
          `disabled` tinyint(1) NOT NULL,
          PRIMARY KEY  (`key_id`)
        ) ENGINE=MyISAM;";
        dbDelta($sql);


        // create Pagename table
        // TODO: not sure if this table is needed at all, we could use an option...
        $sql = "CREATE TABLE " . AWPCP_TABLE_PAGENAME . " (
          `key_id` int(10) NOT NULL AUTO_INCREMENT,
          `userpagename` varchar(100) NOT NULL DEFAULT '',
          PRIMARY KEY  (`key_id`)
        ) ENGINE=MyISAM;";
        dbDelta($sql);


        // create Pages table
        $sql = 'CREATE TABLE ' . AWPCP_TABLE_PAGES . " (
          `page` VARCHAR(100) NOT NULL,
          `id` INT(10) NOT NULL,
          PRIMARY KEY  (`page`)
        ) ENGINE=MyISAM;";
        dbDelta($sql);


        // insert deafult category
        $data = array('category_id' => 1, 
                      'category_parent_id' => 0, 
                      'category_name' => __('General', 'AWPCP'), 
                      'category_order' => 0);
        $wpdb->insert(AWPCP_TABLE_CATEGORIES, $data);
        
        // insert default Fee
        $data = array('adterm_id' => 1, 
                      'adterm_name' => __('30 Day Listing', 'AWPCP'), 
                      'amount' => 9.99, 
                      'recurring' => 1, 
                      'rec_period' => 31, 
                      'rec_increment' => 'D', 
                      'buys' => 0, 
                      'imagesallowed' => 6);
        $wpdb->insert(AWPCP_TABLE_ADFEES, $data);


        do_action('awpcp_install');

        return update_option("awpcp_db_version", $awpcp_db_version);
    }

    public function uninstall() {
        global $wpdb, $awpcp_plugin_path, $table_prefix, $awpcp;

        // Remove the upload folders with uploaded images
        $dirname = AWPCPUPLOADDIR;
        if (file_exists($dirname)) {
            require_once $awpcp_plugin_path.'/fileop.class.php';
            $fileop = new fileop();
            $fileop->delete($dirname);
        }

        // Delete the classifieds page(s)
        $awpcppageid = awpcp_get_page_id_by_ref('main-page-name');

        // TODO: use wp_delete_post and query_posts
        $query="DELETE FROM {$table_prefix}posts WHERE ID='$awpcppageid' OR post_parent='$awpcppageid' and post_content LIKE '%AWPCP%'";
        awpcp_query($query, __LINE__);

        // Drop the tables
        $tbl_regions = $wpdb->prefix . "awpcp_regions";

        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_CATEGORIES);
        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_ADFEES);
        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_ADS);
        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_ADSETTINGS);
        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_ADPHOTOS);
        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_PAGENAME);
        $wpdb->query("DROP TABLE IF EXISTS " . AWPCP_TABLE_PAGES);

        $tblRegionsExists = checkfortable($tbl_regions);
        if ($tblRegionsExists) {
            $wpdb->query("DROP TABLE " . $tbl_regions);
        }

        // remove AWPCP options from options table
        delete_option('awpcp_installationcomplete');
        delete_option('awpcp_pagename_warning');
        delete_option('widget_awpcplatestads');
        delete_option('awpcp_db_version');
        delete_option($awpcp->settings->option);

        unregister_sidebar_widget('AWPCP Latest Ads', 'widget_awpcplatestads');
        unregister_widget_control('AWPCP Latest Ads', 'widget_awpcplatestads_options', 350, 120);

        // Clear the ad expiration schedule
        wp_clear_scheduled_hook('doadexpirations_hook');
        wp_clear_scheduled_hook('doadcleanup_hook');
        wp_clear_scheduled_hook('awpcp_ad_renewal_email_hook');
        wp_clear_scheduled_hook('awpcp-clean-up-payment-transactions');
        
        // TODO: use deactivate_plugins function
        // http://core.trac.wordpress.org/browser/branches/3.2/wp-admin/includes/plugin.php#L548
        $thepluginfile = "another-wordpress-classifieds-plugin/awpcp.php";
        $current = get_option('active_plugins');
        array_splice($current, array_search( $thepluginfile, $current), 1 );
        update_option('active_plugins', $current);
        do_action('deactivate_' . $thepluginfile );

        do_action('awpcp_uninstall');
    }

    // TODO: remove settings table after another major release
    // TODO: remove pagename table after another major release
    public function upgrade($oldversion, $newversion) {
        global $wpdb;

        if (version_compare($oldversion, '1.8.9.4') < 0) {
            $this->upgrade_to_1_8_9_4($oldversion);
        }
        if (version_compare($oldversion, '1.9.9') < 0) {
            $this->upgrade_to_1_9_9($oldversion);
        }
        if (version_compare($oldversion, '2.0.0') < 0) {
            $this->upgrade_to_2_0_0($oldversion);
        }

        do_action('awpcp_upgrade', $oldversion, $newversion);
        
        return update_option("awpcp_db_version", $newversion);
    }

    private function upgrade_to_1_8_9_4($version) {
        global $wpdb;

        // Try to enable the expired ads, bug in 1.0.6.17:
        if ($version == '1.0.6.17') {
            $query = "UPDATE ". AWPCP_TABLE_ADS ." SET DISABLED='0' WHERE ad_enddate >= NOW()";
            $wpdb->query($query);
        }

        if (!is_at_least_awpcp_version('1.8.7.1')) {
            // Fix the problem with disabled_date not being nullable from 1.8.7
            $query = "ALTER TABLE ". AWPCP_TABLE_ADS ." MODIFY disabled_date datetime";
            $wpdb->query($query);
        }

        // Upgrade featured ad columns for module
        if (!$this->column_exists(AWPCP_TABLE_ADS, 'is_featured_ad')) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `is_featured_ad` tinyint(1) DEFAULT NULL");
        }

        // Upgrade for tracking poster's IP address
        if (!$this->column_exists(AWPCP_TABLE_ADS, 'posterip')) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `posterip` varchar(15) DEFAULT NULL");
        }

        if (!$this->column_exists(AWPCP_TABLE_ADS, 'flagged')) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `flagged` tinyint(1) DEFAULT NULL");
        }

        // Upgrade for deleting ads that are marked as disabled or deleted
        if (!$this->column_exists(AWPCP_TABLE_ADS, 'disabled_date')) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `disabled_date` datetime DEFAULT NULL");
        }


        if (!$this->column_exists(AWPCP_TABLE_ADFEES, 'is_featured_ad_pricing')) {
            $wpdb->query("ALTER TABLE " . $tbl_ad_fees . "  ADD `is_featured_ad_pricing` tinyint(1) DEFAULT NULL");
        }

        if (!$this->column_exists(AWPCP_TABLE_ADFEES, 'categories')) {
            $wpdb->query("ALTER TABLE " . $tbl_ad_fees . "  ADD `categories` text DEFAULT NULL");
        }


        if (!$this->column_exists(AWPCP_TABLE_CATEGORIES, 'category_order')) {
            $wpdb->query("ALTER TABLE " . $tbl_ad_categories . "  ADD `category_order` int(10) NULL DEFAULT '0' AFTER category_name");
            $wpdb->query("UPDATE " . $tbl_ad_categories . " SET category_order=0");
        }


        if (!$this->column_exists(AWPCP_TABLE_ADFEES, 'categories')) {
            $wpdb->query("ALTER TABLE " . $tbl_ad_fees . "  ADD `categories` text DEFAULT NULL");
        }


        // Fix the shortcode issue if present in installed version
        $sql = "UPDATE " . $wpdb->posts . " SET post_content='[AWPCPCLASSIFIEDSUI]' ";
        $sql.= "WHERE post_content='[[AWPCPCLASSIFIEDSUI]]'";
        $wpdb->query($sql);


        if (!field_exists('tos')) {
            // add terms of service field
            $sql = 'INSERT INTO '. $tbl_ad_settings .'(`config_option`,`config_value`,`config_diz`,`config_group_id`,`option_type`) 
                VALUES ("tos","Terms of service go here...","Terms of Service for posting an ad - modify this to fit your needs:","1","0")';
            $wpdb->query($sql);

            $sql = 'INSERT INTO '. $tbl_ad_settings .'(`config_option`,`config_value`,`config_diz`,`config_group_id`,`option_type`) 
                VALUES ("requiredtos", "Display and require Terms of Service","Display and require Terms of Service","1","0")';
            $wpdb->query($sql);
        }

        if (!field_exists('notifyofadexpired')) {
            //add notify of an expired ad field
            $sql = 'insert into '.$tbl_ad_settings.'(`config_option`,`config_value`,`config_diz`,`config_group_id`,`option_type`) 
                values ("notifyofadexpired","Notify admin of expired ads.","Notify admin of expired ads.","1","0")';

            $wpdb->query($sql);
        }

        //Fix bug from 1.8.6.4:
        $wpdb->query("UPDATE $tbl_ad_settings SET option_type ='0' where config_option='notifyofadexpired'");



        // Update ad_settings table to ad field config groud ID if field does not exist in installed version

        $cgid_column_name="config_group_id";
        $cgid_column_name_exists=mysql_query("SELECT $cgid_column_name FROM $tbl_ad_settings");
        if (mysql_errno() || !$cgid_column_name_exists) {
            $query=("ALTER TABLE " . $tbl_ad_settings . "  ADD `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER config_diz");
            awpcp_query($query, __LINE__);

            $myconfig_group_ops_1=array('showlatestawpcpnews','uiwelcome','main_page_display','useakismet','contactformcheckhuman', 'contactformcheckhumanhighnumval','awpcptitleseparator','showcityinpagetitle','showstateinpagetitle','showcountryinpagetitle','showcategoryinpagetitle','showcountyvillageinpagetitle','awpcppagefilterswitch','activatelanguages','sidebarwidgetbeforecontent','sidebarwidgetaftercontent','sidebarwidgetbeforetitle','sidebarwidgetaftertitle','usesenderemailinsteadofadmin','awpcpadminaccesslevel','awpcpadminemail','useakismet');
            $myconfig_group_ops_2=array('addurationfreemode','autoexpiredisabledelete','maxcharactersallowed','notifyofadexpiring', 'notifyofadposted', 'adapprove', 'disablependingads', 'showadcount', 'displayadviews','onlyadmincanplaceads','allowhtmlinadtext', 'hyperlinkurlsinadtext', 'notice_awaiting_approval_ad', 'buildsearchdropdownlists','visitwebsitelinknofollow','groupbrowseadsby','groupsearchresultsby','displayadthumbwidth','adresultsperpage','displayadlayoutcode','awpcpshowtheadlayout');
            $myconfig_group_ops_3=array('freepay','paylivetestmode','paypalemail', 'paypalcurrencycode', 'displaycurrencycode', '2checkout', 'activatepaypal', 'activate2checkout','twocheckoutpaymentsrecurring','paypalpaymentsrecurring');
            $myconfig_group_ops_4=array('imagesallowdisallow', 'awpcp_thickbox_disabled','imagesapprove', 'imagesallowedfree', 'uploadfoldername', 'maximagesize','minimagesize', 'imgthumbwidth', 'imgmaxheight', 'imgmaxwidth');
            $myconfig_group_ops_5=array('useadsense', 'adsense', 'adsenseposition');
            $myconfig_group_ops_6=array('displayphonefield', 'displayphonefieldreqop', 'displaycityfield', 'displaycityfieldreqop', 'displaystatefield','displaystatefieldreqop', 'displaycountryfield', 'displaycountryfieldreqop', 'displaycountyvillagefield', 'displaycountyvillagefieldreqop', 'displaypricefield', 'displaypricefieldreqop', 'displaywebsitefield', 'displaywebsitefieldreqop', 'displaypostedbyfield');
            $myconfig_group_ops_7=array('requireuserregistration', 'postloginformto', 'registrationurl');
            $myconfig_group_ops_8=array('contactformsubjectline','contactformbodymessage','listingaddedsubject','listingaddedbody','resendakeyformsubjectline','resendakeyformbodymessage','paymentabortedsubjectline','paymentabortedbodymessage','adexpiredsubjectline','adexpiredbodymessage');
            $myconfig_group_ops_9=array('usesmtp','smtphost','smtpport','smtpusername','smtppassword');
            $myconfig_group_ops_10=array('userpagename','showadspagename','placeadpagename','page-name-renew-ad','browseadspagename','browsecatspagename','editadpagename','paymentthankyoupagename','paymentcancelpagename','replytoadpagename','searchadspagename','categoriesviewpagename');
            $myconfig_group_ops_11=array('seofriendlyurls','pathvaluecontact','pathvalueshowad','pathvaluebrowsecategory','pathvalueviewcategories','pathvaluecancelpayment','pathvaluepaymentthankyou');

            // assign a group value to each setting
            foreach($myconfig_group_ops_1 as $myconfig_group_op_1){add_config_group_id($cvalue='1',$myconfig_group_op_1);}
            foreach($myconfig_group_ops_2 as $myconfig_group_op_2){add_config_group_id($cvalue='2',$myconfig_group_op_2);}
            foreach($myconfig_group_ops_3 as $myconfig_group_op_3){add_config_group_id($cvalue='3',$myconfig_group_op_3);}
            foreach($myconfig_group_ops_4 as $myconfig_group_op_4){add_config_group_id($cvalue='4',$myconfig_group_op_4);}
            foreach($myconfig_group_ops_5 as $myconfig_group_op_5){add_config_group_id($cvalue='5',$myconfig_group_op_5);}
            foreach($myconfig_group_ops_6 as $myconfig_group_op_6){add_config_group_id($cvalue='6',$myconfig_group_op_6);}
            foreach($myconfig_group_ops_7 as $myconfig_group_op_7){add_config_group_id($cvalue='7',$myconfig_group_op_7);}
            foreach($myconfig_group_ops_8 as $myconfig_group_op_8){add_config_group_id($cvalue='8',$myconfig_group_op_8);}
            foreach($myconfig_group_ops_9 as $myconfig_group_op_9){add_config_group_id($cvalue='9',$myconfig_group_op_9);}
            foreach($myconfig_group_ops_10 as $myconfig_group_op_10){add_config_group_id($cvalue='10',$myconfig_group_op_10);}
            foreach($myconfig_group_ops_11 as $myconfig_group_op_11){add_config_group_id($cvalue='11',$myconfig_group_op_11);}
        }

        if (get_awpcp_option_group_id('seofriendlyurls') == 1){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_group_id` = '11' WHERE `config_option` = 'seofriendlyurls'"); }
        if (get_awpcp_option_type('main_page_display') == 1){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Main page layout [ check for ad listings ] [ Uncheck for categories ]',config_group_id='1' WHERE `config_option` = 'main_page_display'"); }
        if (get_awpcp_option_config_diz('paylivetestmode') != "Put payment gateways in test mode"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Put payment gateways in test mode' WHERE `config_option` = 'paylivetestmode'");}
        if (get_awpcp_option_config_diz('adresultsperpage') != "Default number of ads per page"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '10', `option_type` = '1', `config_diz` = 'Default number of ads per page' WHERE `config_option` = 'adresultsperpage'");}
        if (get_awpcp_option_config_diz('awpcpshowtheadlayout') != "<div id=\"showawpcpadpage\"><div class=\"adtitle\">$ad_title</div><br/><div class=\"showawpcpadpage\">$featureimg<label>Contact Information</label><br/><a href=\"$quers/$codecontact\">Contact $adcontact_name</a>$adcontactphone $location $awpcpvisitwebsite</div>$aditemprice $awpcpextrafields <div class=\"fixfloat\"></div> $showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>$addetails</div>$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">$tweetbtn $sharebtn $flagad</span>$awpcpadviews $showadsense3</div>"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '2', `option_type` = '2', `config_diz` = 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.', `config_value` = '<div id=\"showawpcpadpage\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"showawpcpadpage\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields <div class=\"fixfloat\"></div> \$showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">\$tweetbtn \$sharebtn \$flagad</span>\$awpcpadviews \$showadsense3</div>' WHERE `config_option` = 'awpcpshowtheadlayout'");}

        ////
        // Match up the ad settings fields of current versions and upgrading versions
        ////

        if (!field_exists($field='userpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: Make sure page does not already exist]','10','1');");}
        if (!field_exists($field='showadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='placeadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='browseadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('browseadspagename', 'Browse Ads', 'Name browse ads apge. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='searchadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES        ('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='paymentthankyoupagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='paymentcancelpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='replytoadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='browsecatspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='editadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('editadpagename', 'Edit Ad', 'Name for edit ad page. [CAUTION: existing page will be overwritten]','10','1');");}
        if (!field_exists($field='categoriesviewpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES        ('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page]','10','1');");}
        if (!field_exists($field='freepay')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('freepay', '0', 'Charge Listing Fee?','3','0');");}
        if (!field_exists($field='requireuserregistration')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('requireuserregistration', '0', 'Require user registration?','7','0');");}
        if (!field_exists($field='postloginformto')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
        if (!field_exists($field='registrationurl')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
        if (!field_exists($field='main_page_display')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('main_page_display', '0', 'Main page layout [ check for ad listings | Uncheck for categories ]','1','0');");}
        if (!field_exists($field='activatelanguages')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('activatelanguages', '0', 'Activate Language Capability','1','0');");}
        if (!field_exists($field='awpcpadminaccesslevel')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcpadminaccesslevel', 'admin', 'Set wordpress role of users who can have admin access to classifieds. Choices [admin,editor]. Currently no other roles will be granted access.','1','1');");}
        if (!field_exists($field='sidebarwidgetaftertitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetaftertitle', '</h3>', 'Code to appear after widget title','1','1');");}
        if (!field_exists($field='sidebarwidgetbeforetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetbeforetitle', '<h3 class=\"widgettitle\">', 'Code to appear before widget title','1','1');");}
        if (!field_exists($field='sidebarwidgetaftercontent')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetaftercontent', '</div>', 'Code to appear after widget content','1','1');");}
        if (!field_exists($field='sidebarwidgetbeforecontent')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetbeforecontent', '<div class=\"widget\">', 'Code to appear before widget content','1','1');");}
        if (!field_exists($field='usesenderemailinsteadofadmin')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('usesenderemailinsteadofadmin', '0', 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.','1','0');");}
        if (!field_exists($field='awpcpadminemail')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcpadminemail', '', 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here.','1','1');");}
        if (!field_exists($field='awpcptitleseparator')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1');");}
        if (!field_exists($field='showcityinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0');");}
        if (!field_exists($field='showstateinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0');");}
        if (!field_exists($field='showcountryinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0');");}
        if (!field_exists($field='showcountyvillageinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES        ('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0');");}
        if (!field_exists($field='showcategoryinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0');");}
        if (!field_exists($field='awpcppagefilterswitch')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcppagefilterswitch', '1', 'Uncheck this if you need to turn off the awpcp page filter that prevents awpcp classifieds children pages from showing up in your wp pages menu [you might need to do this if for example the awpcp page filter is messing up your page menu. It means you will have to manually exclude the awpcp children pages from showing in your page list. Some of the pages really should not be visible to your users by default]','1','0');");}
        if (!field_exists($field='paylivetestmode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paylivetestmode', '0', 'Put Paypal and 2Checkout in test mode.','3','0');");}
        if (!field_exists($field='useadsense')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('useadsense', '1', 'Activate adsense','5','0');");}
        if (!field_exists($field='adsense')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adsense', 'Adsense code', 'Your adsense code [ Best if 468 by 60 text or banner. ]','5','2');");}
        if (!field_exists($field='adsenseposition')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adsenseposition', '2', 'Adsense position. [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1');");}
        if (!field_exists($field='addurationfreemode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiry].','2','1');");}
        if (!field_exists($field='autoexpiredisabledelete')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('autoexpiredisabledelete', '0', 'Disable expired ads instead of deleting them?','2','0');");}
        if (!field_exists($field='imagesallowdisallow')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imagesallowdisallow', '1', 'Allow images in ads? [Affects both free and paid]','4','0');");}
        if (!field_exists($field='awpcp_thickbox_disabled')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0');");}
        if (!field_exists($field='imagesallowedfree')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imagesallowedfree', '4', ' Free mode number of images allowed?','4','1');");}
        if (!field_exists($field='uploadfoldername')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1');");}
        if (!field_exists($field='maximagesize')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('maximagesize', '150000', 'Maximum size per image user can upload to system.','4','1');");}
        if (!field_exists($field='minimagesize')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('minimagesize', '300', 'Minimum size per image user can upload to system','4','1');");}
        if (!field_exists($field='imgthumbwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imgthumbwidth', '125', 'Minimum height/width for uploaded images (used for both).','4','1');");}
        if (!field_exists($field='maxcharactersallowed')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?','2','1');");}
        if (!field_exists($field='imgmaxheight')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,`config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imgmaxheight', '480', 'Max image height. Images taller than this are automatically resized upon upload.','4','1');");}
        if (!field_exists($field='imgmaxwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imgmaxwidth', '640', 'Max image width. Images wider than this are automatically resized upon upload.','4','1');");}
        if (!field_exists($field='paypalemail')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]','3','1');");}
        if (!field_exists($field='paypalcurrencycode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments','3','1');");}
        if (!field_exists($field='displaycurrencycode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycurrencycode', 'USD', 'The currency to show on your payment pages','3','1');");}
        if (!field_exists($field='2checkout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1');");}
        if (!field_exists($field='activatepaypal')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('activatepaypal', '1', 'Activate PayPal','3','0');");}
        if (!field_exists($field='activate2checkout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('activate2checkout', '1', 'Activate 2Checkout ','3','0');");}
        if (!field_exists($field='paypalpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paypalpaymentsrecurring', '0', 'Use recurring payments paypal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
        if (!field_exists($field='twocheckoutpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
        if (!field_exists($field='notifyofadexpiring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0');");}
        if (!field_exists($field='notifyofadposted')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('notifyofadposted', '1', 'Notify admin of new ad.','2','0');");}
        if (!field_exists($field='listingaddedsubject')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1');");}
        if (!field_exists($field='listingaddedbody')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2');");}
        if (!field_exists($field='imagesapprove')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imagesapprove', '0', 'Hide images until admin approves them','4','0');");}
        if (!field_exists($field='adapprove')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adapprove', '0', 'Disable ad until admin approves','2','0');");}
        if (!field_exists($field='displayadthumbwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayadthumbwidth', '80', 'Width for thumbnails in ad listings view [Only numerical value]','2','1');");}
        if (!field_exists($field='disablependingads')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0');");}
        if (!field_exists($field='groupbrowseadsby')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('groupbrowseadsby', '1', 'Group ad listings by','2','3');");}
        if (!field_exists($field='groupsearchresultsby')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('groupsearchresultsby', '1', 'Group ad listings in search results by','2','3');");}
        if (!field_exists($field='showadcount')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showadcount', '1', 'Show how many ads a category contains.','2','0');");}
        if (!field_exists($field='adresultsperpage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adresultsperpage', '10', 'Default number of ads per page','2','1');");}
        if (!field_exists($field='noadsinparentcat')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0');");}
        if (!field_exists($field='displayadviews')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayadviews', '1', 'Show ad views','2','0');");}
        if (!field_exists($field='displayadlayoutcode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayadlayoutcode', '<div class=\"\$awpcpdisplayaditems\"><div style=\"width:\$imgblockwidth;padding:5px;float:left;margin-right:20px;\">\$awpcp_image_name_srccode</div><div style=\"width:50%;padding:5px;float:left;\"><h4>\$ad_title</h4> \$addetailssummary...</div><div style=\"padding:5px;float:left;\"> \$awpcpadpostdate \$awpcp_city_display \$awpcp_state_display \$awpcp_display_adviews \$awpcp_display_price </div><div class=\"fixfloat\"></div></div><div class=\"fixfloat\"></div>', 'Modify as needed to control layout of ad listings page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2');");}
        if (!field_exists($field='awpcpshowtheadlayout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcpshowtheadlayout', '<div id=\"showawpcpadpage\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"showawpcpadpage\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields <div class=\"fixfloat\"></div> \$showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">\$tweetbtn \$sharebtn \$flagad</span>\$awpcpadviews \$showadsense3</div>', 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2');");}
        if (!field_exists($field='usesmtp')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('usesmtp', '0', 'Enable external SMTP server [ if emails not processing normally]', 9 ,'0');");}
        if (!field_exists($field='smtphost')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1');");}
        if (!field_exists($field='smtpport')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtpport', '25', 'SMTP port [ if emails not processing normally]', 9 ,'1');");}
        if (!field_exists($field='smtpusername')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1');");}
        if (!field_exists($field='smtppassword')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1');");}
        if (!field_exists($field='onlyadmincanplaceads')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0');");}
        if (!field_exists($field='contactformcheckhuman')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0');");}
        if (!field_exists($field='useakismet')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('useakismet', '0', 'Use Akismet for Posting Ads/Contact Responses (strong anti-spam)', '1','0');");}
        if (!field_exists($field='contactformcheckhumanhighnumval')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1');");}
        if (!field_exists($field='contactformsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1');");}
        if (!field_exists($field='contactformbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2');");}
        if (!field_exists($field='resendakeyformsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('resendakeyformsubjectline', 'The classified ad access key you requested', 'Subject line for email sent out when someone requests their ad access key resent','8', '1');");}
        if (!field_exists($field='resendakeyformbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('resendakeyformbodymessage', 'You asked to have your classified ad ad access key resent. Below are all the ad access keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their ad access key resent', '8','2');");}
        if (!field_exists($field='paymentabortedsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1');");}
        if (!field_exists($field='paymentabortedbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.', 'Message body text for email sent out when the payment processing does not complete','8','2');");}
        if (!field_exists($field='adexpiredsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adexpiredsubjectline', 'Your classifieds listing ad has expired', 'Subject line for email sent out when an ad has auto-expired','8', '1');");}
        if (!field_exists($field='adexpiredbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adexpiredbodymessage', 'This is an automated notification that your classified ad has expired.','Message body text for email sent out when an ad has auto-expired', '8','2');");}
        if (!field_exists($field='seofriendlyurls')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('seofriendlyurls', '0', 'Search Engine Friendly URLs? [ Does not work in some instances ]', '11','0');");}
        if (!field_exists($field='pathvaluecontact')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluecontact', '3', 'If contact page link not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
        if (!field_exists($field='pathvalueshowad')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvalueshowad', '3', 'If show ad links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
        if (!field_exists($field='pathvaluebrowsecats')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluebrowsecats', '2', 'If browse categories links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
        if (!field_exists($field='pathvalueviewcategories')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvalueviewcategories', '2', 'If the view categories link is not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
        if (!field_exists($field='pathvaluecancelpayment')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
        if (!field_exists($field='pathvaluepaymentthankyou')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
        if (!field_exists($field='allowhtmlinadtext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0');");}
        if (!field_exists($field='htmlstatustext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2');");}
        if (!field_exists($field='hyperlinkurlsinadtext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('hyperlinkurlsinadtext', '0', 'Make URLs in ad text clickable', '2','0');");}
        if (!field_exists($field='visitwebsitelinknofollow')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0');");}
        if (!field_exists($field='notice_awaiting_approval_ad')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2');");}
        if (!field_exists($field='displayphonefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayphonefield', '1', 'Show phone field','6','0');");}
        if (!field_exists($field='displayphonefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayphonefieldreqop', '0', 'Require phone','6','0');");}
        if (!field_exists($field='displaycityfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycityfield', '1', 'Show city field.','6','0');");}
        if (!field_exists($field='displaycityfieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycityfieldreqop', '0', 'Require city','6','0');");}
        if (!field_exists($field='displaystatefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaystatefield', '1', 'Show state field.','6','0');");}
        if (!field_exists($field='displaystatefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaystatefieldreqop', '0', 'Require state','6','0');");}
        if (!field_exists($field='displaycountryfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountryfield', '1', 'Show country field.','6','0');");}
        if (!field_exists($field='displaycountryfieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountryfieldreqop', '0', 'Require country','6','0');");}
        if (!field_exists($field='displaycountyvillagefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountyvillagefield', '0', 'Show County/village/other.','6','0');");}
        if (!field_exists($field='displaycountyvillagefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountyvillagefieldreqop', '0', 'Require county/village/other.','6','0');");}
        if (!field_exists($field='displaypricefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaypricefield', '1', 'Show price field.','6','0');");}
        if (!field_exists($field='displaypricefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaypricefieldreqop', '0', 'Require price.','6','0');");}
        if (!field_exists($field='displaywebsitefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaywebsitefield', '1', 'Show website field','6','0');");}
        if (!field_exists($field='displaywebsitefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaywebsitefieldreqop', '0', 'Require website','6','0');");}
        if (!field_exists($field='displaypostedbyfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaypostedbyfield', '1', 'Show Posted By field?','6','0');");}
        if (!field_exists($field='buildsearchdropdownlists')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0');");}
        if (!field_exists($field='uiwelcome')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2');");}
        if (!field_exists($field='showlatestawpcpnews')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0');");}


        // create or restore AWPCP pages
        awpcp_create_pages();
        

        // Add new field websiteurl to awpcp_ads
        if (!$this->column_exists('websiteurl', AWPCP_TABLE_ADS)) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `websiteurl` VARCHAR( 500 ) NOT NULL AFTER `ad_contact_email`");
        }

        $wpdb->query("ALTER TABLE " . $tbl_ads . "  DROP INDEX `titdes`");
        $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD FULLTEXT KEY `titdes` (`ad_title`,`ad_details`)");


        // Add new field ad_fee_paid for sorting ads by paid listings first
        if (!$this->column_exists('ad_fee_paid', AWPCP_TABLE_ADS)) {
             $query=("ALTER TABLE " . $tbl_ads . "  ADD `ad_fee_paid` float(7,2) NOT NULL AFTER `adterm_id`");
             awpcp_query($query, __LINE__);
        }

        // Increase the length value for the ad_item_price field
        $wpdb->query("ALTER TABLE " . $tbl_ads . " CHANGE `ad_item_price` `ad_item_price` INT( 25 ) NOT NULL");

        // Ad new field add_county_village to awpcp_ads
        if (!$this->column_exists('ad_county_village', AWPCP_TABLE_ADS)) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_county_village` varchar(255) NOT NULL AFTER `ad_country`");
        }

        // Add field ad_views to table awpcp_ads to track ad views
        if (!$this->column_exists('ad_views', AWPCP_TABLE_ADS)) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_views` int(10) NOT NULL DEFAULT 0 AFTER `ad_item_price`");
        }

        // Insert new field ad_item_price into awpcp_ads table
        if (!$this->column_exists('ad_item_price', AWPCP_TABLE_ADS)) {
            $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_item_price` INT( 10 ) NOT NULL AFTER `ad_country`");
        }
    }

    private function upgrade_to_1_9_9($version) {
        global $wpdb, $awpcp;

        // Add an user_id column to the Ads table
        if (version_compare($version, '1.8.9.4.46') < 0 &&
            !$this->column_exists(AWPCP_TABLE_ADS, 'user_id')) {
            $wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS . "  ADD `user_id` INT(10) DEFAULT NULL");

            // attempt to populate user_id column
            $users_emails = $wpdb->get_results("SELECT ID, user_email FROM " . $wpdb->users);
            $query = "UPDATE " . AWPCP_TABLE_ADS . " SET user_id = %d WHERE LOWER(ad_contact_email) = %s";
            foreach ($users_emails as $user) {
                $wpdb->query($wpdb->prepare($query, $user->ID, strtolower($user->user_email)));
            }
            $wpdb->show_errors();
        }


        // Add a renew_email_sent column to Ads table
        if (version_compare($version, '1.8.9.4.46') < 0 &&
            !$this->column_exists(AWPCP_TABLE_ADS, 'renew_email_sent')) {
            $wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS . "  ADD `renew_email_sent` TINYINT(1) NOT NULL DEFAULT 0");
        }


        // Map old settings to the new Settings API system
        $table = $wpdb->get_var("SHOW TABLES LIKE '" . AWPCP_TABLE_ADSETTINGS . "'");
        if (version_compare($version, '1.8.9.4.46') < 0 &&
            strcmp($table, AWPCP_TABLE_ADSETTINGS) == 0) 
        {
            $settings = $wpdb->get_results('SELECT * FROM ' . AWPCP_TABLE_ADSETTINGS);
            foreach ($settings as $setting) {
                switch (intval($setting->option_type)) {
                    case 0:
                        $value = intval($setting->config_value);
                        break;
                    case 1:
                    case 2:
                    case 3:
                        $value = $setting->config_value;
                        break;
                }
                $awpcp->settings->update_option($setting->config_option, $value, $force=true);
            }
        }


        $translations = array(
            'userpagename' => 'main-page-name', 
            'showadspagename' => 'show-ads-page-name',
            'placeadpagename' => 'place-ad-page-name',
            'editadpagename' => 'edit-ad-page-name', 
            'page-name-renew-ad' => 'renew-ad-page-name',
            'replytoadpagename' => 'reply-to-ad-page-name',
            'browseadspagename' => 'browse-ads-page-name', 
            'searchadspagename' => 'search-ads-page-name', 
            'browsecatspagename' => 'browse-categories-page-name', 
            'categoriesviewpagename' => 'view-categories-page-name', 
            'paymentthankyoupagename' => 'payment-thankyou-page-name', 
            'paymentcancelpagename' => 'payment-cancel-page-name');

        // rename page name settings
        if (version_compare($version, '1.8.9.4.46') < 0 &&
            $awpcp->settings->get_option('main-page-name', null) === null) {
            foreach ($translations as $original => $translation) {
                $value = $awpcp->settings->get_option($original, null);
                // only translate settings that already exists, the others will
                // be defined when the settings are registered
                if ($value !== null) {
                    $awpcp->settings->update_option($translation, $value, $force=true);
                }
            }
        }


        // create Pages table and map pagename to WP Pages IDs
        $table = $wpdb->get_var("SHOW TABLES LIKE '" . AWPCP_TABLE_PAGES . "'");
        if (strcmp($table, AWPCP_TABLE_PAGES) != 0) {
            $sql = 'CREATE TABLE ' . AWPCP_TABLE_PAGES . " (
              `page` VARCHAR(100) NOT NULL,
              `id` INT(10) NOT NULL,
              PRIMARY KEY  (`page`)
            ) ENGINE=MyISAM;";
            dbDelta($sql);

            $table = AWPCP_TABLE_PAGES;
        }

        // if this table has records then we already did the migration, skip.
        $count = count($wpdb->get_results('SELECT * FROM ' . AWPCP_TABLE_PAGES ));
        if ($count <= 0) {
            // map pagenames to ids
            $pages = array_values($translations);
            foreach ($pages as $page) {
                $name = $awpcp->settings->get_option($page, null);

                if ($name == null) { continue; }

                $name = sanitize_title($name);
                $sql = "SELECT ID FROM $wpdb->posts WHERE post_name = '$name' AND post_type = 'page'";
                $id = intval($wpdb->get_var($sql));
                $id = $id > 0 ? $id : -1;

                $params = array('page' => $page, 'id' => $id);
                $result = $wpdb->insert(AWPCP_TABLE_PAGES, $params);
            }
        }
    }

    private function upgrade_to_2_0_0($version) {
        global $awpcp;
        // Change Expred Ad subject line setting
        if (version_compare($version, '1.9.9.4 beta') <= 0) {
            $awpcp->settings->update_option('adexpiredsubjectline', 
                'Your classifieds listing at %s has expired', $force=true);
        }
    }
}



/**
 * Check if all required pages are in place and creates
 * the ones that are missing.
 */
// function awpcp_restore_pages() {
//     $tableexists = checkfortable(AWPCP_TABLE_PAGENAME);

//     if ($tableexists) {
//         $cpagename_awpcp = get_currentpagename();

//         if (isset($cpagename_awpcp) && !empty($cpagename_awpcp)) {
//             $awpcppagename = sanitize_title($cpagename_awpcp, $post_ID='');
//             $awpcpwppostpageid = awpcp_get_page_id($awpcppagename);

//             awpcp_create_subpages($awpcpwppostpageid);
//         }
//     }
// }



/**
 * Checks if a given settings exists in the Settings table and
 * inserts it it doesn't exists.
 */
function awpcp_insert_setting($field, $value, $description, $group, $type) {
    global $wpdb;

    if (!field_exists($field)) {
        $data = array('config_option' => $field, 'config_value' => $value, 
                      'config_diz' => $description, 'config_group_id' => $group, 
                      'option_type' => $type);
        $wpdb->insert(AWPCP_TABLE_ADSETTINGS, $data);
    }
}

/**
 * Insert settings in to Settings table.
 */
// function awpcp_insert_settings() {
//     global $wpdb;

//     $tbl_ad_settings = AWPCP_TABLE_ADSETTINGS;

//     $query = "INSERT INTO " . $tbl_ad_settings;
//     $query.= " (`config_option`, `config_value`, `config_diz`,`config_group_id`, `option_type`) VALUES ";

//     // General Settings
//     $query .= "
//         ('main_page_display', '0', 'Show ad listings on main page (checked) or just categories (unchecked)?','1','0'),
//         ('activatelanguages', '0', 'Turn On Translation File (POT)?','1','0'),      
//         ('awpcpadminaccesslevel', 'admin', 'Set wordpress role of users who can have admin access to classifieds. Choices [admin,editor][case sensitive]. Currently no other roles will be granted access.','1','1'),               
//         ('sidebarwidgetaftertitle', '</h3>', 'Code to appear after widget title','1','1'),  
//         ('sidebarwidgetbeforetitle', '<h3 class=\"widgettitle\">', 'Code to appear before widget title','1','1'),
//         ('sidebarwidgetaftercontent', '</div>', 'Code to appear after widget content','1','1'),
//         ('sidebarwidgetbeforecontent', '<div class=\"widget\">', 'Code to appear before widget content','1','1'),
//         ('usesenderemailinsteadofadmin', '0', 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.','1','0'),
//         ('awpcpadminemail', '', 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here.','1','1'),       
//         ('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1'),
//         ('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0'),
//         ('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0'),
//         ('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0'),
//         ('awpcppagefilterswitch', '1', 'Uncheck this if you need to turn off the AWPCP page filter that prevents AWPCP classifieds children pages from showing up in your wp pages menu [you might need to do this if for example the AWPCP page filter is messing up your page menu. It means you will have to manually exclude the AWPCP children pages from showing in your page list. Some of the pages really should not be visible to your users by default]','1','0'),
//         ('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0'),
//         ('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0'),
//         ('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0'),
//         ('useakismet', '1', 'Use Akismet for Posting Ads/Contact Responses (strong anti-spam)', '1','0'),
//         ('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1'),
//         ('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2'),
//         ('tos', 'Terms of service go here...','Terms of Service to post an ad - modify this to fit your needs:','1','2'),
//         ('requiredtos', 'Display and require Terms of Service','Display and require Terms of Service','1','0'),
//         ('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0'),
//         ('enable-user-panel', '0', 'Activate User Ad Management Panel', 1, 0),";

//     // Ad/Listings Settings
//     $query .= "
//         ('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiration].','2','1'),
//         ('autoexpiredisabledelete', '0', 'Disable expired ads instead of deleting them?','2','0'),
//         ('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0'),
//         ('notifyofadposted', '1', 'Notify admin of new ad.','2','0'),
//         ('notifyofadexpired', '1', 'Notify admin of expired ads.','2','0'),
//         ('sent-ad-renew-email', '1', 'Ad Renewal Email', '2', '0'),
//         ('ad-renew-email-threshold', '5', 'Ad Renewal Email Threshold', '2', '0'),
//         ('adapprove', '0', 'Disable ad until admin approves','2','0'),
//         ('displayadthumbwidth', '80', 'Width for thumbnails in ad listings view [Only numerical value]','2','1'),
//         ('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0'),
//         ('groupbrowseadsby', '1', 'Group ad listings by','2','3'),
//         ('groupsearchresultsby', '1', 'Group ad listings in search results by','2','3'),
//         ('showadcount', '1', 'Show how many ads a category contains.','2','0'),
//         ('adresultsperpage', '10', 'Default number of ads per page','2','1'),
//         ('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0'),
//         ('displayadviews', '1', 'Show ad views','2','0'),
//         ('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0'),
//         ('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0'),
//         ('maxcharactersallowed', '750', 'Maximum ad length (characters)?','2','1'),
//         ('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2'),
//         ('hyperlinkurlsinadtext', '0', 'Make URLs in ad text clickable', '2','0'),
//         ('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0'),
//         ('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0'),
//         ('displayadlayoutcode', '<div class=\"\$awpcpdisplayaditems\"><div style=\"width:\$imgblockwidth;padding:5px;float:left;margin-right:20px;\">\$awpcp_image_name_srccode</div><div style=\"width:50%;padding:5px;float:left;\"><h4>\$ad_title</h4> \$addetailssummary...</div><div style=\"padding:5px;float:left;\"> \$awpcpadpostdate \$awpcp_city_display \$awpcp_state_display \$awpcp_display_adviews \$awpcp_display_price </div><div class=\"fixfloat\"></div></div><div class=\"fixfloat\"></div>', 'Modify as needed to control layout of ad listings page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2'),
//         ('awpcpshowtheadlayout', '<div id=\"showawpcpadpage\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"showawpcpadpage\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields \$showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">\$tweetbtn \$sharebtn \$flagad</span>\$awpcpadviews \$showadsense3</div>', 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2'),
//         ('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2'),
//         ('show-menu-item-place-ad', 1, 'Show Place Ad menu item', 2, 0),
//         ('show-menu-item-edit-ad', 1, 'Show Edit Ad menu item', 2, 0),
//         ('show-menu-item-browse-ads', 1, 'Show Browse Ads menu item', 2, 0),
//         ('show-menu-item-search-ads', 1, 'Show Search Ads menu item', 2, 0),";

//     // Payment settings
//     $query .= "
//         ('paylivetestmode', '0', 'Put payment gateways in test mode.','3','0'),
//         ('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for PayPal payments [if running in pay mode and if PayPal is activated]','3','1'),
//         ('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your PayPal payments','3','1'),
//         ('displaycurrencycode', 'USD', 'The display currency for your payment pages','3','1'),
//         ('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1'),
//         ('activatepaypal', '1', 'Activate PayPal?','3','0'),
//         ('activate2checkout', '1', 'Activate 2Checkout?','3','0'),
//         ('paypalpaymentsrecurring', '0', 'Use recurring payments PayPal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0'),
//         ('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2Checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0'),
//         ('freepay', '0', 'Charge Listing Fee? (Pay Mode)','3','0'),";

//     // Image settings
//     $query .= "
//         ('imagesallowdisallow', '1', 'Allow images in ads? (affects both free and pay mode)','4','0'),
//         ('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0'),
//         ('imagesallowedfree', '4', 'Number of Image Uploads Allowed (Free Mode)','4','1'),
//         ('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1'),
//         ('maximagesize', '150000', 'Maximum file size per image user can upload to system.','4','1'),
//         ('minimagesize', '300', 'Minimum file size per image user can upload to system','4','1'),
//         ('imgthumbwidth', '125', 'Minimum width/height for uploaded images (used for both).','4','1'),
//         ('imgmaxwidth', '640', 'Max width for images. Images wider than this are automatically resized upon upload.','4','1'),
//         ('imgmaxheight', '480', 'Max height for images. Images taller than this are automatically resized upon upload.','4','1'),
//         ('imagesapprove', '0', 'Hide images until admin approves them','4','0'),";

//     // AdSense settings
//     $query .= "
//         ('useadsense', '1', 'Activate AdSense','5','0'),
//         ('adsense', 'AdSense code', 'Your AdSense code [ Best if 468 by 60 text or banner. ]','5',2),
//         ('adsenseposition', '2', 'Show AdSense at position: [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1'),";

//     // Optional Form Field settings
//     $query .= "
//         ('displayphonefield', '1', 'Show phone field?','6','0'),
//         ('displayphonefieldreqop', '0', 'Require phone?','6','0'),
//         ('displaycityfield', '1', 'Show city field?','6','0'),
//         ('displaycityfieldreqop', '0', 'Require city?','6','0'),
//         ('displaystatefield', '1', 'Show state field?','6','0'),
//         ('displaystatefieldreqop', '0', 'Require state?','6','0'),
//         ('displaycountryfield', '1', 'Show country field?','6','0'),
//         ('displaycountryfieldreqop', '0', 'Require country?','6','0'),
//         ('displaycountyvillagefield', '0', 'Show County/village/other?','6','0'),
//         ('displaycountyvillagefieldreqop', '0', 'Require county/village/other?','6','0'),
//         ('displaypricefield', '1', 'Show price field?','6','0'),
//         ('displaypricefieldreqop', '0', 'Require price?','6','0'),
//         ('displaywebsitefield', '1', 'Show website field?','6','0'),
//         ('displaywebsitefieldreqop', '0', 'Require website?','6','0'),
//         ('displaypostedbyfield', '1', 'Show Posted By field?','6','0'),";

//     // Registration settings
//     $query .= "
//         ('requireuserregistration', '0', 'Require user registration?','7','0'),
//         ('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php <br/>[ **Only needed if registration is required and your login url is mod-rewritten ] ','7','1'),
//         ('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1'),";

//     // Email Text settings
//     $query .= "
//         ('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1'),
//         ('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2'),
//         ('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1'),
//         ('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2'),
//         ('resendakeyformsubjectline', 'The classified ad ad access key you requested', 'Subject line for email sent out when someone requests their ad access key resent','8', '1'),
//         ('resendakeyformbodymessage', 'You asked to have your classified ad ad access key resent. Below are all the ad access keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their ad access key resent', '8','2'),
//         ('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1'),
//         ('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.','Message body text for email sent out when the payment processing does not complete', '8','2'),
//         ('adexpiredsubjectline', 'Your classifieds listing ad has expired', 'Subject line for email sent out when an ad has auto-expired','8', '1'),
//         ('adexpiredbodymessage', 'This is an automated notification that your classified ad has expired.','Message body text for email sent out when an ad has auto-expired', '8','2'),
//         ('renew-ad-email-subject', 'Your classifieds listing Ad will expire in %d days.', 'Subject line for email sent out when an Ad is about to expire.','8', '1'),
//         ('renew-ad-email-body', 'This is an automated notification that your classified Ad will expire in %d days.','Message body text for email sent out when an Ad is about to expire. Use %d as placeholder for the number of days before the Ad expires.', '8','2'),";

//     // SMTP settings
//     $query .= "
//         ('usesmtp', '0', 'Enabled external SMTP server [ if emails not processing normally]', 9 ,'0'),
//         ('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1'),
//         ('smtpport', '25', 'SMTP port [ if emails not processing normally]', 9 ,'1'),
//         ('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1'),
//         ('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1'),";

//     // Classified Pages settings
//     $query .= "
//         ('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('page-name-renew-ad', 'Renew Ad', 'Name for Renew Ad page. [CAUTION: existing page will be overwritten]', '10', '1'),
//         ('browseadspagename', 'Browse Ads', 'Name browse ads page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1'),
//         ('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [ CAUTION: existing page will be overwritten ]','10','1'),
//         ('editadpagename', 'Edit Ad', 'Name for edit ad page. [ CAUTION: existing page will be overwritten ]','10','1'),
//         ('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page ]','10','1'),";

//     // SEO settings
//     $query .= "
//         ('seofriendlyurls', '0', 'Turn on Search Engine Friendly URLs? (SEO Mode)', '11','0'),
//         ('pathvaluecontact', '3', 'If contact page link not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
//         ('pathvalueshowad', '3', 'If show ad links not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
//         ('pathvaluebrowsecats', '2', 'If browse categories links not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
//         ('pathvalueviewcategories', '2', 'If the menu link to view categories layout is not working in SEO Mode change value until correct path is found. Start at 1', '11','1'),
//         ('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in SEO Mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1'),
//         ('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in SEO Mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1')";

//     return $wpdb->query($query);
// }


/**
 * Creates table structure for new installations or
 * updates existing tables to match current version db.
 */
 // TODO: test dbDelta
// function awpcp_install() {
//     //debug();
//     global $wpdb, $awpcp_db_version, $awpcp_plugin_path;

//     // XXX: this shouldn't be in this function - wvega
//     wp_enqueue_script('jquery');

//     //_log("Running installation");
//     $tbl_ad_categories = AWPCP_TABLE_CATEGORIES;
//     $tbl_ad_fees = AWPCP_TABLE_ADFEES;
//     $tbl_ads = AWPCP_TABLE_ADS;
//     $tbl_ad_settings = AWPCP_TABLE_ADSETTINGS;
//     $tbl_ad_photos = AWPCP_TABLE_ADPHOTOS;
//     $tbl_pagename = AWPCP_TABLE_PAGENAME;

//     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

//     // if tables don't exist then this is a clean install
//     if ($wpdb->get_var("show tables like '$tbl_ad_categories'") != $tbl_ad_categories) {
//         _log("Fresh install detected");
            
//         // Ooops - page already exists - abort this function and queue an admin warning message
//         if (findpagebyname('AWPCP')) {
//             update_option('awpcp_pagename_warning', 1);
//             return;
//         } else { 
//             delete_option('awpcp_pagename_warning');
//         }

//         // create Categories table

//         $sql = "CREATE TABLE " . $tbl_ad_categories . " (
//           `category_id` int(10) NOT NULL AUTO_INCREMENT,
//           `category_parent_id` int(10) NOT NULL,
//           `category_name` varchar(255) NOT NULL DEFAULT '',
//           `category_order` int(10) NULL DEFAULT '0',
//           PRIMARY KEY (`category_id`)
//         ) ENGINE=MyISAM;";
//         dbDelta($sql);

//         // Insret deafult category
//         $sql = "INSERT INTO " . $tbl_ad_categories . " (`category_id`, `category_parent_id`, `category_name`, `category_order`) VALUES
//         (1, 0, 'General', 0);";
//         dbDelta($sql);


//         // create Ad Fees table

//         $sql = "CREATE TABLE " . $tbl_ad_fees . " (
//           `adterm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//           `adterm_name` varchar(100) NOT NULL DEFAULT '',
//           `amount` float(6,2) unsigned NOT NULL DEFAULT '0.00',
//           `recurring` tinyint(1) unsigned NOT NULL DEFAULT '0',
//           `rec_period` int(5) unsigned NOT NULL DEFAULT '0',
//           `rec_increment` varchar(5) NOT NULL DEFAULT '',
//           `buys` int(10) unsigned NOT NULL DEFAULT '0',
//           `imagesallowed` int(5) unsigned NOT NULL DEFAULT '0',
//           `is_featured_ad_pricing` tinyint(1) DEFAULT NULL,
//           PRIMARY KEY (`adterm_id`)
//         ) ENGINE=MyISAM;";
//         dbDelta($sql);

//         $sql = "INSERT INTO " . $tbl_ad_fees . " (`adterm_id`, `adterm_name`, `amount`, `recurring`, `rec_period`, `rec_increment`, `buys`, `imagesallowed`) VALUES
//         (1, '30 Day Listing', 9.99, 1, 31, 'D', 0, 6);";
//         dbDelta($sql);

//         $sql = "ALTER TABLE " . $tbl_ad_fees . "  ADD `categories` text DEFAULT NULL";
//         $wpdb->query($sql);


//         $sql = "CREATE TABLE " . $tbl_ads . " (
//           `ad_id` int(10) NOT NULL AUTO_INCREMENT,
//           `adterm_id` int(10) NOT NULL DEFAULT '0',
//           `ad_fee_paid` float(7,2) NOT NULL,
//           `ad_category_id` int(10) NOT NULL,
//           `ad_category_parent_id` int(10) NOT NULL,
//           `ad_title` varchar(255) NOT NULL DEFAULT '',
//           `ad_details` text NOT NULL,
//           `ad_contact_name` varchar(255) NOT NULL DEFAULT '',
//           `ad_contact_phone` varchar(255) NOT NULL DEFAULT '',
//           `ad_contact_email` varchar(255) NOT NULL DEFAULT '',
//           `websiteurl` varchar( 375 ) NOT NULL,
//           `ad_city` varchar(255) NOT NULL DEFAULT '',
//           `ad_state` varchar(255) NOT NULL DEFAULT '',
//           `ad_country` varchar(255) NOT NULL DEFAULT '',
//           `ad_county_village` varchar(255) NOT NULL DEFAULT '',
//           `ad_item_price` int(25) NOT NULL,
//           `ad_views` int(10) NOT NULL DEFAULT 0,
//           `ad_postdate` date NOT NULL DEFAULT '0000-00-00',
//           `ad_last_updated` date NOT NULL,
//           `ad_startdate` datetime NOT NULL,
//           `ad_enddate` datetime NOT NULL,
//           `disabled` tinyint(1) NOT NULL DEFAULT '0',
//           `disabled_date` datetime,
//           `ad_key` varchar(255) NOT NULL DEFAULT '',
//           `ad_transaction_id` varchar(255) NOT NULL DEFAULT '',
//           `payment_gateway` varchar(255) NOT NULL DEFAULT '',
//           `payment_status` varchar(255) NOT NULL DEFAULT '',
//           `is_featured_ad` tinyint(1) DEFAULT NULL,
//           `posterip` varchar(15) NOT NULL DEFAULT '',
//           `flagged` tinyint(1) NOT NULL DEFAULT 0,
//           `user_id` INT(10) DEFAULT NULL,
//           `renew_email_sent` TINYINT(1) NOT NULL DEFAULT 0,
//           FULLTEXT KEY `titdes` (`ad_title`,`ad_details`),
//           PRIMARY KEY (`ad_id`)
//         ) ENGINE=MyISAM;";
//         dbDelta($sql);


//         $sql = "CREATE TABLE " . $tbl_ad_settings . " (
//           `config_option` varchar(50) NOT NULL DEFAULT '',
//           `config_value` text NOT NULL,
//           `config_diz` text NOT NULL,
//           `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1',
//           `option_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
//           PRIMARY KEY (`config_option`)
//         ) ENGINE=MyISAM COMMENT='0-checkbox, 1-text,2-textarea';";
//         dbDelta($sql);


//         $sql = "CREATE TABLE " . $tbl_ad_photos . " (
//           `key_id` int(10) NOT NULL AUTO_INCREMENT,
//           `ad_id` int(10) unsigned NOT NULL DEFAULT '0',
//           `image_name` varchar(100) NOT NULL DEFAULT '',
//           `disabled` tinyint(1) NOT NULL,
//           PRIMARY KEY (`key_id`)
//         ) ENGINE=MyISAM;";
//         dbDelta($sql);


//         $sql = "CREATE TABLE " . $tbl_pagename . " (
//           `key_id` int(10) NOT NULL AUTO_INCREMENT,
//           `userpagename` varchar(100) NOT NULL DEFAULT '',
//           PRIMARY KEY (`key_id`)
//         ) ENGINE=MyISAM;";
//         dbDelta($sql);

//         update_option("awpcp_db_version", $awpcp_db_version);

//     } else {
//         delete_option('awpcp_pagename_warning');

//         //  Update the database tables in the event of a new version of plugin
//         $installed_ver = get_option( "awpcp_db_version" );

//         if ( $installed_ver != $awpcp_db_version ) {
//             _log("UPGRADE detected");

//             if ($installed_ver == '1.0.6.17') {
//                 //Try to enable the expired ads, bug in 1.0.6.17:
//                 $query="UPDATE ".$tbl_ads." SET DISABLED='0' WHERE ad_enddate >= NOW()";
//                 $wpdb->query($query);
//             }
//             if (!is_at_least_awpcp_version('1.8.7.1')) {
//                 //Fix the problem with disabled_date not being nullable from 1.8.7
//                 $query="ALTER TABLE ".$tbl_ads." MODIFY disabled_date datetime";
//                 $wpdb->query($query);
//             }

//             //Upgrade featured ad columns for module
//             $column="is_featured_ad";
//             $column_exists = mysql_query("SELECT $column FROM $tbl_ads");
//             if (mysql_errno() || !$column_exists)
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `is_featured_ad` tinyint(1) DEFAULT NULL");
//             }

//             //Upgrade for tracking poster's IP address
//             $column="posterip";
//             $column_exists = mysql_query("SELECT $column FROM $tbl_ads");
//             if (mysql_errno() || !$column_exists)
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `posterip` varchar(15) DEFAULT NULL");
//             }

//             //Upgrade for tracking poster's IP address
//             $column="flagged";
//             $column_exists = mysql_query("SELECT $column FROM $tbl_ads");
//             if (mysql_errno() || !$column_exists)
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `flagged` tinyint(1) DEFAULT NULL");
//             }

//             //Upgrade for deleting ads that are marked as disabled or deleted
//             $column="disabled_date";
//             $column_exists = mysql_query("SELECT $column FROM $tbl_ads");
//             if (mysql_errno() || !$column_exists)
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `disabled_date` datetime DEFAULT NULL");
//             }

//             $column="is_featured_ad_pricing";

//             $column_exists = mysql_query("SELECT $column FROM $tbl_ad_fees");
//             if (mysql_errno() || !$column_exists)
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ad_fees . "  ADD `is_featured_ad_pricing` tinyint(1) DEFAULT NULL");
//             }
//             $column="categories";
//             $column_exists = mysql_query("SELECT $column FROM $tbl_ad_fees");
//             if (mysql_errno() || !$column_exists) {
//                 $wpdb->query("ALTER TABLE " . $tbl_ad_fees . "  ADD `categories` text DEFAULT NULL");
//             }

//             // Add an user_id column to the Ads table
//             $wpdb->hide_errors();
//             $column_exists = $wpdb->query("SELECT user_id FROM " . AWPCP_TABLE_ADS);
//             if ($column_exists === false) {
//                 $wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS . "  ADD `user_id` INT(10) DEFAULT NULL");
//             }

//             $users_emails = $wpdb->get_results("SELECT ID, user_email FROM " . $wpdb->users);
//             $query = "UPDATE " . AWPCP_TABLE_ADS . " SET user_id = %d WHERE LOWER(ad_contact_email) = %s";
//             foreach ($users_emails as $user) {
//                 $wpdb->query($wpdb->prepare($query, $user->ID, strtolower($user->user_email)));
//             }
//             $wpdb->show_errors();

//             // Add a renew_email_sent column to Ads table
//             $wpdb->hide_errors();
//             $column_exists = $wpdb->query("SELECT renew_email_sent FROM " . AWPCP_TABLE_ADS);
//             if ($column_exists === false) {
//                 $wpdb->query("ALTER TABLE " . AWPCP_TABLE_ADS . "  ADD `renew_email_sent` TINYINT(1) NOT NULL DEFAULT 0");
//             }
//             $wpdb->show_errors();


            
//             ////
//             // Update category ordering
//             ////
//             $column="category_order";
//             $cat_order_column_exists = mysql_query("SELECT $column FROM $tbl_ad_categories");

//             if (mysql_errno() || !$cat_order_column_exists)
//             {
//                 //Add the category order column:
//                 $wpdb->query("ALTER TABLE " . $tbl_ad_categories . "  ADD `category_order` int(10) NULL DEFAULT '0' AFTER category_name");
//                 $wpdb->query("UPDATE " . $tbl_ad_categories . " SET category_order=0");
//             }
            
//             ////
//             // Fix the shortcode issue if present in installed version
//             ////
//             $wpdb->query("UPDATE " .$wpdb->prefix . "posts set post_content='[AWPCPCLASSIFIEDSUI]' WHERE post_content='[[AWPCPCLASSIFIEDSUI]]'");

//             $tos_column_name="tos";
//             $tos_column_name_exists = mysql_query("SELECT config_option from $tbl_ad_settings where config_option='$tos_column_name'");

//             if (mysql_errno() || !$tos_column_name_exists)
//             {
//                 // add terms of service field
//                 $sql = 'insert into '.$tbl_ad_settings.'(`config_option`,`config_value`,`config_diz`,`config_group_id`,`option_type`) 
//                     values ("tos","Terms of service go here...","Terms of Service for posting an ad - modify this to fit your needs:","1","0")';
    
//                 $wpdb->query($sql);
    
//                 $sql = 'insert into '.$tbl_ad_settings.'(`config_option`,`config_value`,`config_diz`,`config_group_id`,`option_type`) 
//                     values ("requiredtos", "Display and require Terms of Service","Display and require Terms of Service","1","0")';
    
//                 $wpdb->query($sql);
//             }

//             $ads_column_name="notifyofadexpired";
//             $ads_column_name_exists = mysql_query("SELECT config_option from $tbl_ad_settings where config_option='$ads_column_name'");
            
//             if (mysql_errno() || !$ads_column_name_exists)
//             {
//                 //add notify of an expired ad field
//                 $sql = 'insert into '.$tbl_ad_settings.'(`config_option`,`config_value`,`config_diz`,`config_group_id`,`option_type`) 
//                     values ("notifyofadexpired","Notify admin of expired ads.","Notify admin of expired ads.","1","0")';

//                 $wpdb->query($sql);

//             }
//             //Fix bug from 1.8.6.4:
//             $wpdb->query("UPDATE $tbl_ad_settings SET option_type ='0' where config_option='notifyofadexpired'");


//             ////
//             // Update ad_settings table to ad field config groud ID if field does not exist in installed version
//             ////

//             $cgid_column_name="config_group_id";
//             $cgid_column_name_exists=mysql_query("SELECT $cgid_column_name FROM $tbl_ad_settings");
//             if (mysql_errno() || !$cgid_column_name_exists)
//             {
//                 $query=("ALTER TABLE " . $tbl_ad_settings . "  ADD `config_group_id` tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER config_diz");
//                 awpcp_query($query, __LINE__);

//                 $myconfig_group_ops_1=array('showlatestawpcpnews','uiwelcome','main_page_display','useakismet','contactformcheckhuman', 'contactformcheckhumanhighnumval','awpcptitleseparator','showcityinpagetitle','showstateinpagetitle','showcountryinpagetitle','showcategoryinpagetitle','showcountyvillageinpagetitle','awpcppagefilterswitch','activatelanguages','sidebarwidgetbeforecontent','sidebarwidgetaftercontent','sidebarwidgetbeforetitle','sidebarwidgetaftertitle','usesenderemailinsteadofadmin','awpcpadminaccesslevel','awpcpadminemail','useakismet');
//                 $myconfig_group_ops_2=array('addurationfreemode','autoexpiredisabledelete','maxcharactersallowed','notifyofadexpiring', 'notifyofadposted', 'adapprove', 'disablependingads', 'showadcount', 'displayadviews','onlyadmincanplaceads','allowhtmlinadtext', 'hyperlinkurlsinadtext', 'notice_awaiting_approval_ad', 'buildsearchdropdownlists','visitwebsitelinknofollow','groupbrowseadsby','groupsearchresultsby','displayadthumbwidth','adresultsperpage','displayadlayoutcode','awpcpshowtheadlayout');
//                 $myconfig_group_ops_3=array('freepay','paylivetestmode','paypalemail', 'paypalcurrencycode', 'displaycurrencycode', '2checkout', 'activatepaypal', 'activate2checkout','twocheckoutpaymentsrecurring','paypalpaymentsrecurring');
//                 $myconfig_group_ops_4=array('imagesallowdisallow', 'awpcp_thickbox_disabled','imagesapprove', 'imagesallowedfree', 'uploadfoldername', 'maximagesize','minimagesize', 'imgthumbwidth', 'imgmaxheight', 'imgmaxwidth');
//                 $myconfig_group_ops_5=array('useadsense', 'adsense', 'adsenseposition');
//                 $myconfig_group_ops_6=array('displayphonefield', 'displayphonefieldreqop', 'displaycityfield', 'displaycityfieldreqop', 'displaystatefield','displaystatefieldreqop', 'displaycountryfield', 'displaycountryfieldreqop', 'displaycountyvillagefield', 'displaycountyvillagefieldreqop', 'displaypricefield', 'displaypricefieldreqop', 'displaywebsitefield', 'displaywebsitefieldreqop', 'displaypostedbyfield');
//                 $myconfig_group_ops_7=array('requireuserregistration', 'postloginformto', 'registrationurl');
//                 $myconfig_group_ops_8=array('contactformsubjectline','contactformbodymessage','listingaddedsubject','listingaddedbody','resendakeyformsubjectline','resendakeyformbodymessage','paymentabortedsubjectline','paymentabortedbodymessage','adexpiredsubjectline','adexpiredbodymessage');
//                 $myconfig_group_ops_9=array('usesmtp','smtphost','smtpport','smtpusername','smtppassword');
//                 $myconfig_group_ops_10=array('userpagename','showadspagename','placeadpagename','page-name-renew-ad','browseadspagename','browsecatspagename','editadpagename','paymentthankyoupagename','paymentcancelpagename','replytoadpagename','searchadspagename','categoriesviewpagename');
//                 $myconfig_group_ops_11=array('seofriendlyurls','pathvaluecontact','pathvalueshowad','pathvaluebrowsecategory','pathvalueviewcategories','pathvaluecancelpayment','pathvaluepaymentthankyou');

//                 // assign a group value to each setting
//                 foreach($myconfig_group_ops_1 as $myconfig_group_op_1){add_config_group_id($cvalue='1',$myconfig_group_op_1);}
//                 foreach($myconfig_group_ops_2 as $myconfig_group_op_2){add_config_group_id($cvalue='2',$myconfig_group_op_2);}
//                 foreach($myconfig_group_ops_3 as $myconfig_group_op_3){add_config_group_id($cvalue='3',$myconfig_group_op_3);}
//                 foreach($myconfig_group_ops_4 as $myconfig_group_op_4){add_config_group_id($cvalue='4',$myconfig_group_op_4);}
//                 foreach($myconfig_group_ops_5 as $myconfig_group_op_5){add_config_group_id($cvalue='5',$myconfig_group_op_5);}
//                 foreach($myconfig_group_ops_6 as $myconfig_group_op_6){add_config_group_id($cvalue='6',$myconfig_group_op_6);}
//                 foreach($myconfig_group_ops_7 as $myconfig_group_op_7){add_config_group_id($cvalue='7',$myconfig_group_op_7);}
//                 foreach($myconfig_group_ops_8 as $myconfig_group_op_8){add_config_group_id($cvalue='8',$myconfig_group_op_8);}
//                 foreach($myconfig_group_ops_9 as $myconfig_group_op_9){add_config_group_id($cvalue='9',$myconfig_group_op_9);}
//                 foreach($myconfig_group_ops_10 as $myconfig_group_op_10){add_config_group_id($cvalue='10',$myconfig_group_op_10);}
//                 foreach($myconfig_group_ops_11 as $myconfig_group_op_11){add_config_group_id($cvalue='11',$myconfig_group_op_11);}

//             }

//             if (get_awpcp_option_group_id('seofriendlyurls') == 1){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_group_id` = '11' WHERE `config_option` = 'seofriendlyurls'"); }
//             if (get_awpcp_option_type('main_page_display') == 1){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Main page layout [ check for ad listings ] [ Uncheck for categories ]',config_group_id='1' WHERE `config_option` = 'main_page_display'"); }
//             if (get_awpcp_option_config_diz('paylivetestmode') != "Put payment gateways in test mode"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '0', `option_type` = '0', `config_diz` = 'Put payment gateways in test mode' WHERE `config_option` = 'paylivetestmode'");}
//             if (get_awpcp_option_config_diz('adresultsperpage') != "Default number of ads per page"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '10', `option_type` = '1', `config_diz` = 'Default number of ads per page' WHERE `config_option` = 'adresultsperpage'");}
//             if (get_awpcp_option_config_diz('awpcpshowtheadlayout') != "<div id=\"showawpcpadpage\"><div class=\"adtitle\">$ad_title</div><br/><div class=\"showawpcpadpage\">$featureimg<label>Contact Information</label><br/><a href=\"$quers/$codecontact\">Contact $adcontact_name</a>$adcontactphone $location $awpcpvisitwebsite</div>$aditemprice $awpcpextrafields <div class=\"fixfloat\"></div> $showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>$addetails</div>$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">$tweetbtn $sharebtn $flagad</span>$awpcpadviews $showadsense3</div>"){ $wpdb->query("UPDATE " . $tbl_ad_settings . " SET `config_value` = '2', `option_type` = '2', `config_diz` = 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.', `config_value` = '<div id=\"showawpcpadpage\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"showawpcpadpage\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields <div class=\"fixfloat\"></div> \$showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">\$tweetbtn \$sharebtn \$flagad</span>\$awpcpadviews \$showadsense3</div>' WHERE `config_option` = 'awpcpshowtheadlayout'");}


//             ////
//             // Match up the ad settings fields of current versions and upgrading versions
//             ////

//             if (!field_exists($field='userpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('userpagename', 'AWPCP', 'Name for classifieds page. [CAUTION: Make sure page does not already exist]','10','1');");}
//             if (!field_exists($field='showadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showadspagename', 'Show Ad', 'Name for show ads page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='placeadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('placeadpagename', 'Place Ad', 'Name for place ads page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='browseadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('browseadspagename', 'Browse Ads', 'Name browse ads apge. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='searchadspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES        ('searchadspagename', 'Search Ads', 'Name for search ads page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='paymentthankyoupagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentthankyoupagename', 'Payment Thank You', 'Name for payment thank you page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='paymentcancelpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentcancelpagename', 'Cancel Payment', 'Name for payment cancel page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='replytoadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('replytoadpagename', 'Reply To Ad', 'Name for reply to ad page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='browsecatspagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('browsecatspagename', 'Browse Categories', 'Name for browse categories page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='editadpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('editadpagename', 'Edit Ad', 'Name for edit ad page. [CAUTION: existing page will be overwritten]','10','1');");}
//             if (!field_exists($field='categoriesviewpagename')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES        ('categoriesviewpagename', 'View Categories', 'Name for categories view page. [ Dynamic Page]','10','1');");}
//             if (!field_exists($field='freepay')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('freepay', '0', 'Charge Listing Fee?','3','0');");}
//             if (!field_exists($field='requireuserregistration')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('requireuserregistration', '0', 'Require user registration?','7','0');");}
//             if (!field_exists($field='postloginformto')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('postloginformto', '', 'Post login form to [Value should be the full URL to the wordpress login script. Example http://www.awpcp.com/wp-login.php **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
//             if (!field_exists($field='registrationurl')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('registrationurl', '', 'Location of registraiton page [Value should be the full URL to the wordpress registration page. Example http://www.awpcp.com/wp-login.php?action=register **Only needed if registration is required and your login url is mod-rewritten ] ','7','1');");}
//             if (!field_exists($field='main_page_display')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('main_page_display', '0', 'Main page layout [ check for ad listings | Uncheck for categories ]','1','0');");}
//             if (!field_exists($field='activatelanguages')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('activatelanguages', '0', 'Activate Language Capability','1','0');");}
//             if (!field_exists($field='awpcpadminaccesslevel')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcpadminaccesslevel', 'admin', 'Set wordpress role of users who can have admin access to classifieds. Choices [admin,editor]. Currently no other roles will be granted access.','1','1');");}
//             if (!field_exists($field='sidebarwidgetaftertitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetaftertitle', '</h3>', 'Code to appear after widget title','1','1');");}
//             if (!field_exists($field='sidebarwidgetbeforetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetbeforetitle', '<h3 class=\"widgettitle\">', 'Code to appear before widget title','1','1');");}
//             if (!field_exists($field='sidebarwidgetaftercontent')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetaftercontent', '</div>', 'Code to appear after widget content','1','1');");}
//             if (!field_exists($field='sidebarwidgetbeforecontent')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('sidebarwidgetbeforecontent', '<div class=\"widget\">', 'Code to appear before widget content','1','1');");}
//             if (!field_exists($field='usesenderemailinsteadofadmin')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('usesenderemailinsteadofadmin', '0', 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.','1','0');");}
//             if (!field_exists($field='awpcpadminemail')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcpadminemail', '', 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here.','1','1');");}
//             if (!field_exists($field='awpcptitleseparator')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcptitleseparator', '-', 'The character to use to separate ad details used in browser page title [Example: | / - ]','1','1');");}
//             if (!field_exists($field='showcityinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showcityinpagetitle', '1', 'Show city in browser page title when viewing individual ad','1','0');");}
//             if (!field_exists($field='showstateinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showstateinpagetitle', '1', 'Show state in browser page title when viewing individual ad','1','0');");}
//             if (!field_exists($field='showcountryinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showcountryinpagetitle', '1', 'Show country in browser page title when viewing individual ad','1','0');");}
//             if (!field_exists($field='showcountyvillageinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES        ('showcountyvillageinpagetitle', '1', 'Show county/village/other setting in browser page title when viewing individual ad','1','0');");}
//             if (!field_exists($field='showcategoryinpagetitle')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showcategoryinpagetitle', '1', 'Show category in browser page title when viewing individual ad','1','0');");}
//             if (!field_exists($field='awpcppagefilterswitch')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcppagefilterswitch', '1', 'Uncheck this if you need to turn off the awpcp page filter that prevents awpcp classifieds children pages from showing up in your wp pages menu [you might need to do this if for example the awpcp page filter is messing up your page menu. It means you will have to manually exclude the awpcp children pages from showing in your page list. Some of the pages really should not be visible to your users by default]','1','0');");}
//             if (!field_exists($field='paylivetestmode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paylivetestmode', '0', 'Put Paypal and 2Checkout in test mode.','3','0');");}
//             if (!field_exists($field='useadsense')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('useadsense', '1', 'Activate adsense','5','0');");}
//             if (!field_exists($field='adsense')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adsense', 'Adsense code', 'Your adsense code [ Best if 468 by 60 text or banner. ]','5','2');");}
//             if (!field_exists($field='adsenseposition')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adsenseposition', '2', 'Adsense position. [ 1 - above ad text body ] [ 2 - under ad text body ] [ 3 - below ad images. ]','5','1');");}
//             if (!field_exists($field='addurationfreemode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('addurationfreemode', '0', 'Expire free ads after how many days? [0 for no expiry].','2','1');");}
//             if (!field_exists($field='autoexpiredisabledelete')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('autoexpiredisabledelete', '0', 'Disable expired ads instead of deleting them?','2','0');");}
//             if (!field_exists($field='imagesallowdisallow')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imagesallowdisallow', '1', 'Allow images in ads? [Affects both free and paid]','4','0');");}
//             if (!field_exists($field='awpcp_thickbox_disabled')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcp_thickbox_disabled', '0', 'Turn off the thickbox/lightbox if it conflicts with other elements of your site','4','0');");}
//             if (!field_exists($field='imagesallowedfree')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imagesallowedfree', '4', ' Free mode number of images allowed?','4','1');");}
//             if (!field_exists($field='uploadfoldername')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('uploadfoldername', 'uploads', 'Upload folder name. [ Folder must exist and be located in your wp-content directory ]','4','1');");}
//             if (!field_exists($field='maximagesize')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('maximagesize', '150000', 'Maximum size per image user can upload to system.','4','1');");}
//             if (!field_exists($field='minimagesize')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('minimagesize', '300', 'Minimum size per image user can upload to system','4','1');");}
//             if (!field_exists($field='imgthumbwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imgthumbwidth', '125', 'Minimum height/width for uploaded images (used for both).','4','1');");}
//             if (!field_exists($field='maxcharactersallowed')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('maxcharactersallowed', '750', 'What is the maximum number of characters the text of an ad can contain?','2','1');");}
//             if (!field_exists($field='imgmaxheight')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,`config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imgmaxheight', '480', 'Max image height. Images taller than this are automatically resized upon upload.','4','1');");}
//             if (!field_exists($field='imgmaxwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imgmaxwidth', '640', 'Max image width. Images wider than this are automatically resized upon upload.','4','1');");}
//             if (!field_exists($field='paypalemail')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paypalemail', 'xxx@xxxxxx.xxx', 'Email address for paypal payments [if running in paymode and if paypal is activated]','3','1');");}
//             if (!field_exists($field='paypalcurrencycode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paypalcurrencycode', 'USD', 'The currency in which you would like to receive your paypal payments','3','1');");}
//             if (!field_exists($field='displaycurrencycode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycurrencycode', 'USD', 'The currency to show on your payment pages','3','1');");}
//             if (!field_exists($field='2checkout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('2checkout', 'xxxxxxx', 'Account for 2Checkout payments [if running in pay mode and if 2Checkout is activated]','3','1');");}
//             if (!field_exists($field='activatepaypal')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('activatepaypal', '1', 'Activate PayPal','3','0');");}
//             if (!field_exists($field='activate2checkout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('activate2checkout', '1', 'Activate 2Checkout ','3','0');");}
//             if (!field_exists($field='paypalpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paypalpaymentsrecurring', '0', 'Use recurring payments paypal [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
//             if (!field_exists($field='twocheckoutpaymentsrecurring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('twocheckoutpaymentsrecurring', '0', 'Use recurring payments 2checkout [ this feature is not fully automated or fully integrated. For more reliable results do not use recurring ','3','0');");}
//             if (!field_exists($field='notifyofadexpiring')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('notifyofadexpiring', '1', 'Notify ad poster that their ad has expired?','2','0');");}
//             if (!field_exists($field='notifyofadposted')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('notifyofadposted', '1', 'Notify admin of new ad.','2','0');");}
//             if (!field_exists($field='listingaddedsubject')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('listingaddedsubject', 'Your classified ad listing has been submitted', 'Subject line for email sent out when someone posts an ad','8','1');");}
//             if (!field_exists($field='listingaddedbody')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('listingaddedbody', 'Thank you for submitting your classified ad. The details of your ad are shown below.', 'Message body text for email sent out when someone posts an ad','8','2');");}
//             if (!field_exists($field='imagesapprove')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('imagesapprove', '0', 'Hide images until admin approves them','4','0');");}
//             if (!field_exists($field='adapprove')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adapprove', '0', 'Disable ad until admin approves','2','0');");}
//             if (!field_exists($field='displayadthumbwidth')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayadthumbwidth', '80', 'Width for thumbnails in ad listings view [Only numerical value]','2','1');");}
//             if (!field_exists($field='disablependingads')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('disablependingads', '1', 'Enable paid ads that are pending payment.','2','0');");}
//             if (!field_exists($field='groupbrowseadsby')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('groupbrowseadsby', '1', 'Group ad listings by','2','3');");}
//             if (!field_exists($field='groupsearchresultsby')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('groupsearchresultsby', '1', 'Group ad listings in search results by','2','3');");}
//             if (!field_exists($field='showadcount')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showadcount', '1', 'Show how many ads a category contains.','2','0');");}
//             if (!field_exists($field='adresultsperpage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adresultsperpage', '10', 'Default number of ads per page','2','1');");}
//             if (!field_exists($field='noadsinparentcat')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('noadsinparentcat', '0', 'Prevent ads from being posted to top level categories?.','2','0');");}
//             if (!field_exists($field='displayadviews')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayadviews', '1', 'Show ad views','2','0');");}
//             if (!field_exists($field='displayadlayoutcode')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayadlayoutcode', '<div class=\"\$awpcpdisplayaditems\"><div style=\"width:\$imgblockwidth;padding:5px;float:left;margin-right:20px;\">\$awpcp_image_name_srccode</div><div style=\"width:50%;padding:5px;float:left;\"><h4>\$ad_title</h4> \$addetailssummary...</div><div style=\"padding:5px;float:left;\"> \$awpcpadpostdate \$awpcp_city_display \$awpcp_state_display \$awpcp_display_adviews \$awpcp_display_price </div><div class=\"fixfloat\"></div></div><div class=\"fixfloat\"></div>', 'Modify as needed to control layout of ad listings page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2');");}
//             if (!field_exists($field='awpcpshowtheadlayout')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('awpcpshowtheadlayout', '<div id=\"showawpcpadpage\"><div class=\"adtitle\">\$ad_title</div><br/><div class=\"showawpcpadpage\">\$featureimg<label>Contact Information</label><br/><a href=\"\$quers/\$codecontact\">Contact \$adcontact_name</a>\$adcontactphone \$location \$awpcpvisitwebsite</div>\$aditemprice \$awpcpextrafields <div class=\"fixfloat\"></div> \$showadsense1<div class=\"showawpcpadpage\"><label>More Information</label><br/>\$addetails</div>\$showadsense2 <div class=\"fixfloat\"></div><div id=\"displayimagethumbswrapper\"><div id=\"displayimagethumbs\"><ul>\$awpcpshowadotherimages</ul></div></div><span class=\"fixfloat\">\$tweetbtn \$sharebtn \$flagad</span>\$awpcpadviews \$showadsense3</div>', 'Modify as needed to control layout of single ad view page. Maintain code formatted as \$somecodetitle. Changing the code keys will prevent the elements they represent from displaying.','2','2');");}
//             if (!field_exists($field='usesmtp')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('usesmtp', '0', 'Enable external SMTP server [ if emails not processing normally]', 9 ,'0');");}
//             if (!field_exists($field='smtphost')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtphost', 'mail.example.com', 'SMTP host [ if emails not processing normally]', 9 ,'1');");}
//             if (!field_exists($field='smtpport')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtpport', '25', 'SMTP port [ if emails not processing normally]', 9 ,'1');");}
//             if (!field_exists($field='smtpusername')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtpusername', 'smtp_username', 'SMTP username [ if emails not processing normally]', 9,'1');");}
//             if (!field_exists($field='smtppassword')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('smtppassword', '', 'SMTP password [ if emails not processing normally]', 9,'1');");}
//             if (!field_exists($field='onlyadmincanplaceads')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('onlyadmincanplaceads', '0', 'Only admin can post ads', '2','0');");}
//             if (!field_exists($field='contactformcheckhuman')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformcheckhuman', '1', 'Activate Math ad post and contact form validation', '1','0');");}
//             if (!field_exists($field='useakismet')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('useakismet', '0', 'Use Akismet for Posting Ads/Contact Responses (strong anti-spam)', '1','0');");}
//             if (!field_exists($field='contactformcheckhumanhighnumval')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformcheckhumanhighnumval', '10', 'Math validation highest number', '1','1');");}
//             if (!field_exists($field='contactformsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformsubjectline', 'Response to your AWPCP Demo Ad', 'Subject line for email sent out when someone replies to ad','8', '1');");}
//             if (!field_exists($field='contactformbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('contactformbodymessage', 'Someone has responded to your AWPCP Demo Ad', 'Message body text for email sent out when someone replies to ad', '8','2');");}
//             if (!field_exists($field='resendakeyformsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('resendakeyformsubjectline', 'The classified ad access key you requested', 'Subject line for email sent out when someone requests their ad access key resent','8', '1');");}
//             if (!field_exists($field='resendakeyformbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('resendakeyformbodymessage', 'You asked to have your classified ad ad access key resent. Below are all the ad access keys in the system that are tied to the email address you provided', 'Message body text for email sent out when someone requests their ad access key resent', '8','2');");}
//             if (!field_exists($field='paymentabortedsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentabortedsubjectline', 'There was a problem processing your classified ads listing payment', 'Subject line for email sent out when the payment processing does not complete','8', '1');");}
//             if (!field_exists($field='paymentabortedbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('paymentabortedbodymessage', 'There was a problem encountered during your attempt to submit payment for your classified ad listing. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.', 'Message body text for email sent out when the payment processing does not complete','8','2');");}
//             if (!field_exists($field='adexpiredsubjectline')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adexpiredsubjectline', 'Your classifieds listing ad has expired', 'Subject line for email sent out when an ad has auto-expired','8', '1');");}
//             if (!field_exists($field='adexpiredbodymessage')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('adexpiredbodymessage', 'This is an automated notification that your classified ad has expired.','Message body text for email sent out when an ad has auto-expired', '8','2');");}
//             if (!field_exists($field='seofriendlyurls')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('seofriendlyurls', '0', 'Search Engine Friendly URLs? [ Does not work in some instances ]', '11','0');");}
//             if (!field_exists($field='pathvaluecontact')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluecontact', '3', 'If contact page link not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
//             if (!field_exists($field='pathvalueshowad')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvalueshowad', '3', 'If show ad links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
//             if (!field_exists($field='pathvaluebrowsecats')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluebrowsecats', '2', 'If browse categories links not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
//             if (!field_exists($field='pathvalueviewcategories')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvalueviewcategories', '2', 'If the view categories link is not working in seo mode change value until correct path is found. Start at 1', '11','1');");}
//             if (!field_exists($field='pathvaluecancelpayment')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluecancelpayment', '2', 'If the cancel payment buttons are not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
//             if (!field_exists($field='pathvaluepaymentthankyou')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('pathvaluepaymentthankyou', '2', 'If the payment thank you page is not working in seo mode it means the path the plugin is using is not correct. Change the until the correct path is found. Start at 1', '11','1');");}
//             if (!field_exists($field='allowhtmlinadtext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('allowhtmlinadtext', '0', 'Allow HTML in ad text [ Not recommended ]', '2','0');");}
//             if (!field_exists($field='htmlstatustext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('htmlstatustext', 'No HTML Allowed', 'Display this text above ad detail text input box on ad post page', '2','2');");}
//             if (!field_exists($field='hyperlinkurlsinadtext')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('hyperlinkurlsinadtext', '0', 'Make URLs in ad text clickable', '2','0');");}
//             if (!field_exists($field='visitwebsitelinknofollow')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('visitwebsitelinknofollow', '1', 'Add no follow to links in ads', '2','0');");}
//             if (!field_exists($field='notice_awaiting_approval_ad')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('notice_awaiting_approval_ad', 'All ads must first be approved by the administrator before they are activated in the system. As soon as an admin has approved your ad it will become visible in the system. Thank you for your business.','Text for message to notify user that ad is awaiting approval','2','2');");}
//             if (!field_exists($field='displayphonefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayphonefield', '1', 'Show phone field','6','0');");}
//             if (!field_exists($field='displayphonefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displayphonefieldreqop', '0', 'Require phone','6','0');");}
//             if (!field_exists($field='displaycityfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycityfield', '1', 'Show city field.','6','0');");}
//             if (!field_exists($field='displaycityfieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycityfieldreqop', '0', 'Require city','6','0');");}
//             if (!field_exists($field='displaystatefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaystatefield', '1', 'Show state field.','6','0');");}
//             if (!field_exists($field='displaystatefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaystatefieldreqop', '0', 'Require state','6','0');");}
//             if (!field_exists($field='displaycountryfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountryfield', '1', 'Show country field.','6','0');");}
//             if (!field_exists($field='displaycountryfieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountryfieldreqop', '0', 'Require country','6','0');");}
//             if (!field_exists($field='displaycountyvillagefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountyvillagefield', '0', 'Show County/village/other.','6','0');");}
//             if (!field_exists($field='displaycountyvillagefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaycountyvillagefieldreqop', '0', 'Require county/village/other.','6','0');");}
//             if (!field_exists($field='displaypricefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaypricefield', '1', 'Show price field.','6','0');");}
//             if (!field_exists($field='displaypricefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,  `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaypricefieldreqop', '0', 'Require price.','6','0');");}
//             if (!field_exists($field='displaywebsitefield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaywebsitefield', '1', 'Show website field','6','0');");}
//             if (!field_exists($field='displaywebsitefieldreqop')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaywebsitefieldreqop', '0', 'Require website','6','0');");}
//             if (!field_exists($field='displaypostedbyfield')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('displaypostedbyfield', '1', 'Show Posted By field?','6','0');");}
//             if (!field_exists($field='buildsearchdropdownlists')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,    `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('buildsearchdropdownlists', '0', 'The search form can attempt to build drop down country, state, city and county lists if data is available in the system. Limits search to available locations. Note that with the regions module installed the value for this option is overridden.','2','0');");}
//             if (!field_exists($field='uiwelcome')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` ,   `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('uiwelcome', 'Looking for a job? Trying to find a date? Looking for an apartment? Browse our classifieds. Have a job to advertise? An apartment to rent? Post a classified ad.', 'The welcome text for your classified page on the user side','1','2');");}
//             if (!field_exists($field='showlatestawpcpnews')){$wpdb->query("INSERT  INTO " . $tbl_ad_settings . " (`config_option` , `config_value` , `config_diz` , `config_group_id`, `option_type`    ) VALUES('showlatestawpcpnews', '1', 'Allow AWPCP RSS.','1','0');");}

//             // added on Nov 21, 2011
//             awpcp_insert_setting('enable-user-panel', 0, 
//                                  'Activate User Ad Management Panel', 1, 0);
//             // added on Dec 14, 2011
//             awpcp_insert_setting('page-name-renew-ad', 'Renew Ad', 
//                                  'Name for Renew Ad page. [CAUTION: existing page will be overwritten]', 
//                                  10, 1);
//             awpcp_insert_setting('sent-ad-renew-email', 1, 'Ad Renewal Email', 2, 0);
//             awpcp_insert_setting('ad-renew-email-threshold', 5, 
//                                  'Ad Renewal Email Threshold (in days)', 2, 1);
//             // added on Dec 20, 2011
//             awpcp_insert_setting('renew-ad-email-subject', 
//                                  'Your classifieds listing Ad will expire in %d days.', 
//                                  'Subject line for email sent out when an Ad is about to expire.', 
//                                  8, 1);
//             awpcp_insert_setting('renew-ad-email-body', 
//                                  'This is an automated notification that your classified Ad will expire in %d days.',
//                                  'Message body text for email sent out when an Ad is about to expire. Use %d as placeholder for the number of days before the Ad expires.', 
//                                  8, 2);
//             // added on Dec 21, 2011
//             awpcp_insert_setting('show-menu-item-place-ad', 1, 'Show Place Ad menu item', 2, 0);
//             awpcp_insert_setting('show-menu-item-edit-ad', 1, 'Show Edit Ad menu item', 2, 0);
//             awpcp_insert_setting('show-menu-item-browse-ads', 1, 'Show Browse Ads menu item', 2, 0);
//             awpcp_insert_setting('show-menu-item-search-ads', 1, 'Show Search Ads menu item', 2, 0);

//             awpcp_restore_pages();


//             ////
//             // Add new field websiteurl to awpcp_ads
//             ////

//             $ad_websiteurl_column="websiteurl";

//             $ad_websiteurl_field=mysql_query("SELECT $ad_websiteurl_column FROM $tbl_ads;");

//             if (mysql_errno())
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `websiteurl` VARCHAR( 500 ) NOT NULL AFTER `ad_contact_email`");
//             }

//             $wpdb->query("ALTER TABLE " . $tbl_ads . "  DROP INDEX `titdes`");
//             $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD FULLTEXT KEY `titdes` (`ad_title`,`ad_details`)");


//             ////
//             // Add new field ad_fee_paid for sorting ads by paid listings first
//             ////

//             $ad_fee_paid_column="ad_fee_paid";

//             $ad_fee_paid_field=mysql_query("SELECT $ad_fee_paid_column FROM $tbl_ads;");

//             if (mysql_errno())
//             {
//                  $query=("ALTER TABLE " . $tbl_ads . "  ADD `ad_fee_paid` float(7,2) NOT NULL AFTER `adterm_id`");
//                  awpcp_query($query, __LINE__);
//             }

//             ////
//             // Increase the length value for the ad_item_price field
//             ////

//             $wpdb->query("ALTER TABLE " . $tbl_ads . " CHANGE `ad_item_price` `ad_item_price` INT( 25 ) NOT NULL");

//             ////
//             // Ad new field add_county_village to awpcp_ads
//             ////

//             $ad_county_village_column="ad_county_village";

//             $ad_county_vilalge_field=mysql_query("SELECT $ad_county_village_column FROM $tbl_ads;");

//             if (mysql_errno())
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_county_village` varchar(255) NOT NULL AFTER `ad_country`");
//             }

//             ////
//             // Add field ad_views to table awpcp_ads to track ad views
//             ////

//             $ad_views_column="ad_views";

//             $ad_views_field=mysql_query("SELECT $ad_views_column FROM $tbl_ads;");

//             if (mysql_errno())
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_views` int(10) NOT NULL DEFAULT 0 AFTER `ad_item_price`");
//             }

//             ////
//             // Insert new field ad_item_price into awpcp_ads table
//             ////
//             $ad_itemprice_column="ad_item_price";

//             $ad_itemprice_field=mysql_query("SELECT $ad_itemprice_column FROM $tbl_ads;");

//             if (mysql_errno())
//             {
//                 $wpdb->query("ALTER TABLE " . $tbl_ads . "  ADD `ad_item_price` INT( 10 ) NOT NULL AFTER `ad_country`");
//             }

//             update_option( "awpcp_db_version", $awpcp_db_version );
//         }
//     }
//     _log("Installation complete");
// }
//add_action('init', 'awpcp_install', 1);