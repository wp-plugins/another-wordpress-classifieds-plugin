=== Another Wordpress Classifieds Plugin ===
Contributors: awpcp.com
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4459898
Tags: classifieds,ads, classified ads,classified,adsense,picture,paypal
Requires at least: 2.7
Tested up to: 2.8.4.
Stable tag: trunk

Another Wordpress Classifieds Plugin provides the ability for you to add classified ads to your wordpress blog. 

== Description ==

This plugin is designed to allow you to add a classifieds ad section to your blog. You can use it to add pretty much any type of classified ads service that can run under a simplified structure taking some basic information from the user. 

Features:

* You can make it free to post an ad or you can charge a fee. 
* Paypal and 2checkout built-in (You can use one or the other or both)
* Users can upload pictures or if you prefer you can turn off this option so users can't upload pictures
* If running in pay mode you can have multiple payment fee scales 
* If running in pay mode you can set a different number of pictures users can upload based on a specific fee scale
* If running in free mode and users can upload pictures you can set a specific value for the number of pictures they can upload
* You can control the size of image files users can upload
* You can turn on image approval so images are hidden until you approve them
* You can turn on ad approval so ads are hidden until you approve them
* The standard form takes an ad title, at text, contact name, email, city, state, country, phone. If you want you can disable city, state, country and phone so only title, contact name, email and ad text fields are shown in the submission form
* You can turn on or off notification when new ads are posted
* Ads will autoexpire based on free or paid mode. If free mode, you can set a value for how long an ad can stay in the system. If running in pay mode ad term length depends on the value you configure and ads will automatically expire when that term ends.
* You can turn on or off the option to display adsense in ads
* If displaying adsense in ads you can configure the position based on 3 position options
* You can turn on or off search engine friendly URls
* You can allow or disallow HTML in ad text 
* You can set a limit on the size of the ad text
* You can turn on or off notification to ad poster when ad expires
* Users can edit their ads
* Users can delete their ad
* Admin can edit user ads
* Viewers can contact the ad poster. Ad poster's email is never shown
* Ads can be searched by keyword, city, state, country, name of poster.
* Classifieds can be setup so only admin can post ads
* Classifieds can be setup so only registered users can post ad. 
* Lost access Key retrieval for users needing to edit their ad who can't remember their access key
* Display how many times an ad has been viewed (admin can turn off or on)
* For sites where HTML will not be allowed in ad text admin has the option turn on or off hyperlinking of any URLs found in text

These are just some of the features of Another Wordpress Classifieds Plugin

Note: This plugin has been developed by a hobbyist programmer who does not pretend to have the skill of a PHP expert, a MYSQL expert, or an expert wordpress developer.
Use this plugin knowing it comes with no guarantee that the methods of coding used are up to PHP, MYSQL or wordpress plugin development expert standards. 

For more information and support please visit http://awpcp.com


== Credits ==

Copyright 2009 A. Lewis awpcp.com

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

Pre-installation note: If you have previously tried other classified plugins and you plan to give the classifieds page created by Another Wordpress Classifieds Plugin the same name as previously used with other classifieds plugins you've installed, please make sure your database has been cleared of any entries in the wp_posts table where the post_name value and the post_title value are the same as the name you plan to give your classifieds page.
For example, if your classifieds page will be named "Classifieds", you might have an wp_posts database entry post_title value of "Classifieds" and post_name value of "classifieds" still left over from previous installations of other classifieds plugins. 
You need to check your database to make sure data from your previous classifieds plugins does not still exist. Specifically, you need to delete all entries from your wp_posts table where the post_title and post_name values are the same as what you plan to name your classifieds page.

1. 	Upload the folder another-wordpress-classifieds-plugin with its contents to wp-content/plugins/

2.   	Activate the plugin

3.	Click on the "Click here to setup your site options" link and fill out all your various options to configure how you want your classifieds to run. 
	A Classifieds page will automatically be created with the name you provide in the options form. The page will contain an important tag. If this tag is not found in the page the classifieds content will not display.
	The code that will be written to the file: [[AWPCPCLASSIFIEDSUI]]
	It is best that the page created for your classifieds only have [[AWPCPCLASSIFIEDSUI]] as the content.

4. 	After you have setup your options you should create some categories and sub categories for your classifieds

5. 	After you have created your categories you should setup your payment fees if you are going to be running in pay mode.


Your classifieds should now be ready and working. If you are having problems please visit http://www.awpcp.com for help


== Screenshots ==

For screen shots of the application please visit http://www.awpcp.com/about

== Frequently Asked Questions ==

Question: Is this product guaranteed to be free of bugs
Answer: This product is not guaranteed to be free of bugs. This is an ongoing development project. If you encounter problems using the plugin please considering helping to improve the product by reporting the problems you encounter. 

Question: Will this plugin work with versions of wordpress earlier than 2.7?
Answer: The plugin has only been tested with version 2.7 and above. If you wish to test it on earlier versions you are free to do so. If you find that it works on an earlier version please consider sharing this information for the benefit of others. At some point in the near future time will be set aside to test the plugin on earlier versions of wordpress.

Question: Does your plugin have any particular requirements for PHP or MySQL versions?
Answer: Yes, you need PHP 5+ and Mysql 5+


For more Frequently asked questions about this plugin visit http://www.awpcp.com/category/questions-answers/



