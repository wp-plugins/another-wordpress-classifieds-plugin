=== Another Wordpress Classifieds Plugin ===
Contributors: awpcp.com
Donate link: http://awpcp.com/donate
Tags: classifieds,ads,classified ads,classified,awpcp
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: tags/1.0.6.15

Another Wordpress Classifieds Plugin provides the ability for you to add classified ads to your Wordpress blog. 

== Description ==
Add a classified ads section to your Wordpress site, quickly and easily.  
Lots of features, very configurable.  Easy to setup. 
We're the #1 Classifieds Plugin for WordPress: over one year old and 36,000+ downloads!

Features:

 = Ad Features =
    1. Search ads by city, state, country, username, or keyword
    2. Allow AdSense ads to be displayed
    3. Control over HTML use in ads
    4. Limit the text size of ad postings
    5. Standard form fields can be enabled or disabled, 
       depending on your needs (title, website, name, country, etc)
    6. Allow or disallow image posting with ads
    7. Automatic expiration of ads (free or paid)
    8. Configurable ad display for AdSense placement
 = Notification Features =
    1. Allow posters to be notified of expiring ads
    2. Allow admin to be notified when new ads are posted
    3. Classified ad users can contact the ad poster without exposing 
       the poster's email (blind email system, similar to Craigslist)
 = Payment Features =
    1. Allows for paid or free ads
    2. Supports PayPal and 2Checkout for paid ads
    3. Google Checkout support for paid ads also through a Premium Module
    4. Allow for image posting limits higher than free ads
    5. Multiple fee scales for posting ads (allows you to have longer ad 
       postings with higher fees)
    6. Variable ad expiration lengths connected to fee scale 
       (e.g. allow ads	to run for 30 days for $10, 60 days for $15, etc)
 = Ad Posting Controls =
    1. Allow admin to approve ads prior to posting
    2. Allow admin to approve images prior to posting
    3. Users can edit or delete their own ads
    4. Admin can edit or delete all ads in the system
    5. Secure system gives users a unique editing key to administer 
       their own ads
    6. Allows for limiting image posting (by image size, height, 
       width and total count if desired)
 = Image Features =
    1. Allow image posting or shut it off completely
    2. Control the number, filesize, height and width of images posted
    3. Allow for different numbers of images to be posted with 
       paid ads vs. free ads
    4. Allow for image approval prior to posting
 = Premium Modules (Add on modules, not included with free plugin) =
    1. Category Icons (adds small icons to give visual cues to 
       your user for each category in AWPCP)
    2. Extra Fields (allows you to add any custom fields for display.  
       For example, if you have a pet-related board, you might have 
       "Sex", "Breed", and "Dam/Sire" as additional fields you require for display)
    3. Regions Control (allows you to restrict viewing of ads based on 
       locale, so users don't see items they aren't physically near them, 
       also allows customization of the region types, such as 
       village, borough, etc)
    4. Google Checkout (allows you to support Google Checkout as a 
       payment method for paid ad posting)
    5. RSS Feeds (allows you syndicate your classifieds for your users to
       download in their favorite RSS reader and come back to your site
       more often!)
    6. "Powered By" Link Removal (allows you to get rid of the 
       link at the bottom of all AWPCP pages)

= Premium Module Links: =

[Category Icons](http://awpcp.com/premium-modules/category-icons-module)

[Extra Fields](http://awpcp.com/premium-modules/extra-fields-module)

[Region Control](http://awpcp.com/premium-modules/regions-control-module)

[Google Checkout](http://awpcp.com/premium-modules/google-checkout-module)

[RSS Feeds](http://awpcp.com/premium-modules/rss-module)

["Powered By" Link Removal](http://awpcp.com/donate)

These are just some of the features of Another Wordpress Classifieds Plugin

Note: This plugin has been developed by a hobbyist programmer who does not pretend to have the skill of a PHP expert, a MYSQL expert, or an expert wordpress developer.
Use this plugin knowing it comes with no guarantee that the methods of coding used are up to PHP, MYSQL or wordpress plugin development expert standards. 

For more information and support please visit [http://awpcp.com](http://awpcp.com)
For plugin support please visit [http://forum.awpcp.com](http://forum.awpcp.com)

If you love the plugin, [please consider purchasing a Premium Module](http://awpcp.com/premium-modules/)!

== Credits ==

Copyright 2009-10 awpcp.com

AWPCP Classifieds icons courtesy of http://www.famfamfam.com/lab/icons/silk/
dcfunctions.php courtesy of Dan Caragea http://www.datemill.com
fileop.class.php courtesy of Dan Caragea http://www.datemill.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA



== Installation ==

 = IMPORTANT = 

Please backup your wordpress database before you install/uninstall/activate/deactivate/upgrade Another Wordpress Classifieds Plugin.

Pre-installation note: If you have previously tried other classified plugins and you plan to give the classifieds page created by Another Wordpress Classifieds Plugin the same name as previously used with other classifieds plugins you've installed, please make sure your database has been cleared of any entries in the wp_posts table where the post_name value and the post_title value are the same as the name you plan to give your classifieds page.
For example, if your classifieds page will be named "Classifieds", you might have a wp_posts database entry post_title value of "Classifieds" and post_name value of "classifieds" still left over from previous installations of other classifieds plugins. 
You need to check your database to make sure data from your previous classifieds plugins does not still exist. Specifically, you need to delete all entries from your wp_posts table where the post_title and post_name values are the same as what you plan to name your classifieds page.

1. 	Upload the folder another-wordpress-classifieds-plugin with its contents to wp-content/plugins/

2.  Activate the plugin

3.	Click on the "Click here to setup your site options" link and fill out all your various options to configure how you want your classifieds to run. 

4. 	After you have setup your options you should create some categories and sub categories for your classifieds

5. 	After you have created your categories you should setup your payment fees if you are going to be running in pay mode.

Your classifieds should now be ready and working. If you are having problems please visit the [support forum](http://forum.awpcp.com) for technical questions, documentation and FAQs.


== Screenshots ==

[Visit the main site for screenshots](http://www.awpcp.com/features)

== Frequently Asked Questions ==

Question: Will this plugin work with versions of WordPress earlier than 2.7?
Answer: The plugin has only been tested with version 2.7 and above. If you wish 
	to test it on earlier versions you are free to do so. If you find that it 
	works on an earlier version please consider sharing this information for 
	the benefit of others.

Question: Does your plugin have any particular requirements for PHP or MySQL versions?
Answer: Yes, you need PHP 5+ and MySQL 5+.  Earlier versions may work, 
	but are not officially supported.

Visit the support forum for [support, FAQs and docs](http://forum.awpcp.com)

Find the [faqs here](http://www.awpcp.com/category/questions-answers/)

[Plugin documentation](http://www.awpcp.com/about/)

== Changelog ==
 
 = 1.0.6.16 =
 NEW: Added options to Latest Ads widget for image display
 Bug: Fixed bug regarding category ordering in the drop-down list under Browse Ads
 Bug: Fixed admin email override failure
 Bug: Fixed issue with ad layout HTML codes and backslashing
 
 = 1.0.6.15 =
 NEW: Image thumbnails now display in Latest Ads widget
 Bug: Fixed bug where thumbnails in ad lists did not have link to ad
 Bug: Set default search for listing in Admin panel to Keyword
 Bug: Fixed ad blocking problems by changing class 'adinfo' to 'cladinfo'
 Bug: Fixed editor role to allow for access to AWPCP Admin menu
 Bug: Fixed search to send back error when search keyword is < 4 letters
 Bug: Fixed pagination problem with Browse Categories
 
 = 1.0.6.14 =
 NEW: RSS Module Support
 Bug: Fixed display problems with Extra Fields Module
 Bug: Fixed bug where users without POSIX installed got image upload errors
 Bug: Updated string handling for internationalization
 
 = 1.0.6.13 =
 Bug: Fixed PayPal output bugs
 Bug: Fixed slash escaping for all fields
 Bug: Added array_walk_recursive function for PHP4 users
 Bug: Fixed upload directory creation ownership issue
 
 = 1.0.6.12 =
 Bug: Fixed category insert/delete bug
 Bug: Fixed admin panel edit for ads
 Bug: Fixed slash escaping for ads
 
 = 1.0.6.11 =
 Bug: Fixed admin widget display
 
 = 1.0.6.10 =
 NEW: Support for WordPress 3.0
 NEW: Support for category ordering
 NEW: Compatibility fix for Simple Facebook Connect
 Bug: Fixed image upload problem where directory not found
 
 = 1.0.6.9 =
 Bug: Fixed $awpcpvisitwebsite missing from display codes
 