<?php

function handleimagesupload()
{

	global $wpdb, $wpcontentdir,$awpcp_plugin_path;
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";

	if(field_exists($field='uploadfoldername'))
	{
		$theuploadfoldername=get_awpcp_option('uploadfoldername');
	}
	else
	{
		$theuploadfoldername="uploads";
	}


	$uploaddir=$wpcontentdir.'/' .$theuploadfoldername .'/';

		//Set permission on main upload directory

		require_once $awpcp_plugin_path.'fileop.class.php';

		$fileop=new fileop();

		$fileop->set_permission($uploaddir,0777);

		$themainawpcpuploaddir=$uploaddir . 'awpcp/';
		$themainawpcpuploadthumbsdir=$uploaddir . 'awpcp/thumbs/';


		//Create the plugin upload directories if they do not exist

		if ( !is_dir($themainawpcpuploaddir) )
		{
			umask(0);
			mkdir($themainawpcpuploaddir, 0777);
		}

		if ( !is_dir($themainawpcpuploadthumbsdir) )
		{
			umask(0);
			mkdir($themainawpcpuploadthumbsdir, 0777);
		}

		$fileop->set_permission($themainawpcpuploaddir,0777);
		$fileop->set_permission($themainawpcpuploadthumbsdir,0777);



		$imgmaxsize = get_awpcp_option('maximagesize');
		$imgminsize = get_awpcp_option('minimagesize');

		if(isset($_REQUEST['ADID']) && !empty($_REQUEST['ADID'])){
		$adid=$_REQUEST['ADID'];}else { $adid='';}
		if(isset($_REQUEST['ADTERMID']) && !empty($_REQUEST['ADTERMID'])){
		$adtermid=$_REQUEST['ADTERMID'];}else { $adtermid='';}
		if(isset($_REQUEST['nextstep']) && !empty($_REQUEST['nextstep'])){
		$nextstep=$_REQUEST['nextstep'];}
		if(isset($_REQUEST['adpaymethod']) && !empty($_REQUEST['adpaymethod'])){
		$adpaymethod=$_REQUEST['adpaymethod'];}
		if(isset($_REQUEST['adaction']) && !empty($_REQUEST['adaction'])){
		$adaction=$_REQUEST['adaction'];}
		if(isset($_REQUEST['adkey']) && !empty($_REQUEST['adkey'])){
		$adkey=$_REQUEST['adkey'];}

		$awpcp_main_folder = $themainawpcpuploaddir;
		$awpcp_thumb_folder = $themainawpcpuploadthumbsdir;
		$awpcp_allowedextensions = array(".jpg", ".gif", ".png");
		$twidth=get_awpcp_option('imgthumbwidth');



		if(get_awpcp_option('freepay') == 1)
		{

			$numimgsallowed=get_numimgsallowed($adtermid);
		}
		else
		{
			$numimgsallowed=get_awpcp_option('imagesallowedfree');
		}

		if(adidexists($adid))
		{
			$totalimagesuploaded=get_total_imagesuploaded($adid);
		}

		$numimgsleft=($numimgsallowed - $totalimagesuploaded);

		$errornofiles=true;
		$awpcpuerror=array();


		for ($i=0;$i<$numimgsleft;$i++)
		{

			$theuploadedfilename = $_FILES['AWPCPfileToUpload'. $i]['name'];

			if(!empty($theuploadedfilename))
			{
				$errornofiles=false;
			}
		}




		if ($errornofiles)
		{
			$awpcpuerror[]="<p class=\"uploaderror\">";
			$awpcpuerror[].=__("No file was selected","AWPCP");
			$awpcpuerror[].="</p>";
			$awpcpuploadformshow=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuerror);
			echo $awpcpuploadformshow;
		}

		else
		{
			awpcpuploadimages($adid,$adtermid,$adkey,$imgmaxsize,$imgminsize,$twidth,$nextstep,$adpaymethod,$adaction,$awpcp_main_folder,'AWPCPfileToUpload');
		}

}


function awpcpuploadimages($adid,$adtermid,$adkey,$imgmaxsize,$imgminsize,$twidth,$nextstep,$adpaymethod,$adaction,$destdir,$actual_field_name,$required=false)
{
	global $wpdb;
	$table_name5 = $wpdb->prefix . "awpcp_adphotos";
	$awpcpupdatinserted=false;
	$awpcpuploaderror=false;
	$awpcpfilesuploaded=true;
	$awpcpuerror=array();


	if(adidexists($adid))
	{
		$totalimagesuploaded=get_total_imagesuploaded($adid);
	}

		if(get_awpcp_option('freepay') == 1)
		{

			$numimgsallowed=get_numimgsallowed($adtermid);
		}
		else
		{
			$numimgsallowed=get_awpcp_option('imagesallowedfree');
		}

		$numimgsleft=($numimgsallowed - $totalimagesuploaded);


		for($i=0;$i<$numimgsleft;$i++)
		{


			$filename=addslashes($_FILES[$actual_field_name.$i]['name']);
			$ext=strtolower(substr(strrchr($_FILES[$actual_field_name.$i]['name'],"."),1));
			$ext_array=array('gif','jpg','jpeg','png');


			if (isset($_FILES[$actual_field_name.$i]['tmp_name']) && is_uploaded_file($_FILES[$actual_field_name.$i]['tmp_name']))
			{
				$imginfo = getimagesize($_FILES[$actual_field_name.$i]['tmp_name']);
				$imgfilesizeval=filesize($_FILES[$actual_field_name.$i]['tmp_name']);

			$desired_filename=mktime();
			$desired_filename.="_$i";



				if(isset($filename) && !empty($filename))
				{
					if (!(in_array($ext, $ext_array)))
					{
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">[$filename]";
					$awpcpuerror[].=__("had an invalid file extension and was not uploaded","AWPCP");
					$awpcpuerror[].="</p>";
					}
					elseif(filesize($_FILES[$actual_field_name.$i]['tmp_name']) <= $imgminsize)
					{
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">";
					$awpcpuerror[].=__("The size of $filename was too small. The file was not uploaded. File size must be greater than $imgminsize bytes","AWPCP");
					$awpcpuerror[].="</p>";
					}
					elseif($imginfo[0]< $twidth)
					{
					// width is too short
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">[$filename]";
					$awpcpuerror[].=__("did not meet the minimum width of [$twidth] pixels. The file was not uploaded","AWPCP");
					$awpcpuerror[].="</p>";
					}
					elseif ($imginfo[1]< $twidth)
					{
					// height is too short
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">[$filename]";
					$awpcpuerror[].=__("did not meet the minimum height of [$twidth] pixels. The file was not uploaded","AWPCP");
					$awpcpuerror[].="</p>";
					}
					elseif(!isset($imginfo[0]) && !isset($imginfo[1]))
					{
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">[$filename]";
					$awpcpuerror[].=__("does not appear to be a valid image file","AWPCP");
					$awpcpuerror[].="</p>";
					}
					elseif( $imgfilesizeval > $imgmaxsize )
					{
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">[$filename]";
					$awpcpuerror[].=__("was larger than the maximum allowed file size of [$imgmaxsize] bytes. The file was not uploaded");
					$awpcpuerror[].="</p>";
					}
					elseif(!empty($desired_filename))
					{
						$filename="$desired_filename.$ext";

						if (!move_uploaded_file($_FILES[$actual_field_name.$i]['tmp_name'],$destdir.'/'.$filename))
						{
							$filename='';
							$awpcpuploaderror=true;
							$awpcpuerror[].="<p class=\"uploaderror\">[$filename]";
							$awpcpuerror[].=__("could not be moved to the destination directory","AWPCP");
							$awpcpuerror[].="</p>";
						}
						else
						{
							if(!awpcpcreatethumb($filename,$destdir,$twidth))
							{
								$awpcpuploaderror=true;
								$awpcpuerror[].="<p class=\"uploaderror\">";
								$awpcpuerror[].=__("Could not create thumbnail image of [ $filename ]","AWPCP");
								$awpcpuerror[].="</p>";
							}

								@chmod($destdir.'/'.$filename,0644);

								$ctiu=get_total_imagesuploaded($adid);

								if(get_awpcp_option('freepay') == '1')
								{
									$nia=get_numimgsallowed($adtermid);
								}
								else
								{
									$nia=get_awpcp_option('imagesallowedfree');
								}

								if(get_awpcp_option('imagesapprove') == 1)
								{
									$disabled='1';
								}
								else
								{
									$disabled='0';
								}

								if($ctiu < $nia)
								{
									$query="INSERT INTO ".$table_name5." SET image_name='$filename',ad_id='$adid',disabled='$disabled'";
									if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
								}

								$awpcpupdatinserted=true;

								if(!($awpcpupdatinserted))
								{
									$awpcpuploaderror=true;
									$awpcpuerror[].="<p class=\"uploaderror\">";
									$awpcpuerror[].=__("Could not save the information to the database for [ $filename ]","AWPCP");
									$awpcpuerror[].="</p>";
								}
						}
					}
				}
				else
				{
					$awpcpuploaderror=true;
					$awpcpuerror[].="<p class=\"uploaderror\">";
					$awpcpuerror[].=__("Unknown error encountered uploading image","AWPCP");
					$awpcpuerror[].="</p>";
				}
			}

		} // Close for $i...

			if ($awpcpuploaderror)
			{
				$awpcpuploadformshow=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuerror);
				echo $awpcpuploadformshow;
			}
			elseif(!($awpcpfilesuploaded))
			{
				$awpcpuerror[]="<p class=\"uploaderror\">";
				$awpcpuerror[].=__("One or more images failed to be uploaded","AWPCP");
				$awpcpuerror[].="</p>";
				$awpcpuploadformshow=display_awpcp_image_upload_form($adid,$adtermid,$adkey,$adaction,$nextstep,$adpaymethod,$awpcpuerror);
				echo $awpcpuploadformshow;
			}
			else
			{
				if(($nextstep == 'finish') && ($adaction == 'editad'))
				{
					$awpcpadpostedmsg=__("Your ad has been submitted","AWPCP");

					if(get_awpcp_option('adapprove') == 1)
					{
						$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
						$awpcpadpostedmsg.="<p>";
						$awpcpadpostedmsg.=$awaitingapprovalmsg;
						$awpcpadpostedmsg.="</p>";
					}
					if(get_awpcp_option('imagesapprove') == 1)
					{
						$imagesawaitingapprovalmsg=__("If you have uploaded images your images will not show up until an admin has approved them.","AWPCP");
						$awpcpadpostedmsg.="<p>";
						$awpcpadpostedmsg.=$imagesawaitingapprovalmsg;
						$awpcpadpostedmsg.="</p>";
					}

					ad_success_email($adid,$txn_id='',$adkey,$awpcpadpostedmsg,$gateway='');
				}

				elseif($nextstep == 'payment')
				{
					// Move to next step in process
					processadstep3($adid,$adtermid,$adkey,$adpaymethod);
				}
				else
				{
					$awpcpadpostedmsg=__("Your ad has been submitted","AWPCP");

					if(get_awpcp_option('adapprove') == 1)
					{
						$awaitingapprovalmsg=get_awpcp_option('notice_awaiting_approval_ad');
						$awpcpadpostedmsg.="<p>";
						$awpcpadpostedmsg.=$awaitingapprovalmsg;
						$awpcpadpostedmsg.="</p>";
					}
					if(get_awpcp_option('imagesapprove') == 1)
					{
						$imagesawaitingapprovalmsg=__("If you have uploaded images your images will not show up until an admin has approved them.","AWPCP");
						$awpcpadpostedmsg.="<p>";
						$awpcpadpostedmsg.=$imagesawaitingapprovalmsg;
						$awpcpadpostedmsg.="</p>";
					}

					ad_success_email($adid,$txn_id='',$adkey,$awpcpadpostedmsg,$gateway='');
				}
			}

}

function awpcpcreatethumb($filename,$destdir,$twidth)
{
		$show_all=true;
		$photothumbs_width=$twidth;
		$mynewimg='';
		if (extension_loaded('gd')) {
			if ($imginfo=getimagesize($destdir."/$filename")) {
				$width=$imginfo[0];
				$height=$imginfo[1];
				if ($width>$photothumbs_width) {
					$newwidth=$photothumbs_width;
					$newheight=$height*($photothumbs_width/$width);
					if ($imginfo[2]==1) {		//gif
					} elseif ($imginfo[2]==2) {		//jpg
						if (function_exists('imagecreatefromjpeg')) {
							$myimg=@imagecreatefromjpeg($destdir."/$filename");
						}
					} elseif ($imginfo[2]==3) {	//png
						$myimg=@imagecreatefrompng($destdir."/$filename");
					}
					if (isset($myimg) && !empty($myimg)) {
						$gdinfo=awpcp_GD();
						if (stristr($gdinfo['GD Version'], '2.')) {	// if we have GD v2 installed
							$mynewimg=@imagecreatetruecolor($newwidth,$newheight);
							if (imagecopyresampled($mynewimg,$myimg,0,0,0,0,$newwidth,$newheight,$width,$height)) {
								$show_all=false;
							}
						} else {	// GD 1.x here
							$mynewimg=@imagecreate($newwidth,$newheight);
							if (@imagecopyresized($mynewimg,$myimg,0,0,0,0,$newwidth,$newheight,$width,$height)) {
								$show_all=false;
							}
						}
					}
				}
			}
		}
		if (!is_writable($destdir.'/thumbs')) {
			@chmod($destdir.'/thumbs',0755);
			if (!is_writable($destdir.'/thumbs')) {
				@chmod($destdir.'/thumbs',0777);
			}
		}
		if ($show_all) {
			$myreturn=@copy($destdir."/$filename",$destdir."/thumbs/$filename");
		} else {
			$myreturn=@imagejpeg($mynewimg,$destdir."/thumbs/$filename",100);
		}
		@chmod($destdir.'/thumbs'."/$filename",0644);
	return $myreturn;
}



		function awpcp_GD() {
			$myreturn=array();
			if (function_exists('gd_info')) {
				$myreturn=gd_info();
			} else {
				$myreturn=array('GD Version'=>'');
				ob_start();
				phpinfo(8);
				$info=ob_get_contents();
				ob_end_clean();
				foreach (explode("\n",$info) as $line) {
					if (strpos($line,'GD Version')!==false) {
						$myreturn['GD Version']=trim(str_replace('GD Version', '', strip_tags($line)));
					}
				}
			}
			return $myreturn;
			}


?>