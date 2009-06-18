<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Source of upload functions: http://www.finalwebsites.com/forums/topic/php-ajax-upload-example
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$root = dirname(dirname(dirname(dirname(__FILE__))));
 if (file_exists($root.'/wp-load.php')) {
    require_once($root.'/wp-load.php');
} else {
    require_once($root.'/wp-config.php');
}


include(dirname(__FILE__).'/classes/upload/foto_upload_script.php');


if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down


$wpcontentdir=WP_CONTENT_DIR;

if(field_exists($field='uploadfoldername'))
{
	$theuploadfoldername=get_awpcp_option('uploadfoldername');
}
else
{
	$theuploadfoldername="uploads";
}


$uploaddir=$wpcontentdir.'/' .$theuploadfoldername .'/';

if(isset($_POST['THEPLUGINPATH']) && !empty($_POST['THEPLUGINPATH'])){
$thepluginpath=$_POST['THEPLUGINPATH'];}else { $thepluginpath='';}


		//Set permission on main upload directory

		require_once $thepluginpath.'classes/fileop.class.php';

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



$foto_upload = new Foto_upload;


if(isset($_POST['MAX_FILE_SIZE']) && !empty($_POST['MAX_FILE_SIZE'])){
$json['size'] = $_POST['MAX_FILE_SIZE'];}
else { $json['size'] = get_awpcp_option('MAX_FILE_SIZE');}
if(isset($_POST['ADID']) && !empty($_POST['ADID'])){
$adid=$_POST['ADID'];}else { $adid='';}
if(isset($_POST['ADTERMID']) && !empty($_POST['ADTERMID'])){
$adtermid=$_POST['ADTERMID'];}else { $adtermid='';}




$json['img'] = '';
$json['ustatmsg'] = '';
$json['showhideuploadform'] = '';

$foto_upload->upload_dir = $uploaddir;
$foto_upload->foto_folder = $themainawpcpuploaddir;
$foto_upload->thumb_folder = $themainawpcpuploadthumbsdir;
$foto_upload->extensions = array(".jpg", ".gif", ".png");
$foto_upload->language = "en";
$foto_upload->x_max_size = 640;
$foto_upload->y_max_size = 480;
$foto_upload->x_max_thumb_size = 125;
$foto_upload->y_max_thumb_size = 125;

$twidth=$foto_upload->x_max_thumb_size;


if(isset($_FILES['AWPCPfileToUpload']) && !empty($_FILES['AWPCPfileToUpload'])){
$foto_upload->the_temp_file = $_FILES['AWPCPfileToUpload']['tmp_name'];
$foto_upload->the_file = $_FILES['AWPCPfileToUpload']['name'];
$foto_upload->http_error = $_FILES['AWPCPfileToUpload']['error'];
}

$foto_upload->rename_file = true;


if ($foto_upload->upload()) {

	$foto_upload->process_image(false, true, true, 80);
	$json['img'] = $foto_upload->file_copy;
	$imagename=$foto_upload->file_copy;


	global $wpdb;
		$table_name5 = $wpdb->prefix . "awpcp_adphotos";

$ctiu=get_total_imagesuploaded($adid);
if(get_awpcp_option('freepay') == '1'){
$nia=get_numimgsallowed($adtermid);}
else {
$nia=get_awpcp_option('imagesallowedfree');
}

if(get_awpcp_option('imagesapprove') == 1){
$disabled='1';}
else {$disabled='0';}

if($ctiu < $nia){
	if(isset($imagename) && !empty($imagename)){
		$query="INSERT INTO ".$table_name5." SET image_name='$imagename',ad_id='$adid',disabled='$disabled'";
		if (!($res=@mysql_query($query))) {trigger_error(mysql_error(),E_USER_ERROR);}
	}
}

$numimgsallowed='';

if(get_awpcp_option('imagesallowdisallow') == '1')
{

	if((get_awpcp_option('freepay') == 1) && isset($adtermid) && $adtermid != '0')
	{
		$numimgsallowed=get_numimgsallowed($adtermid);
	}
	elseif((!get_awpcp_option('freepay')) && (ad_term_id_set($adid)))
	{
		$numimgsallowed=get_numimgsallowed($adtermid);
	}
	else
	{
		$numimgsallowed=get_awpcp_option('imagesallowedfree');
	}
}

$totalimagesuploaded=get_total_imagesuploaded($adid);
$numimgsleft=($numimgsallowed - $totalimagesuploaded);

$ustatmsg="You currently have [ $totalimagesuploaded ] out of [ $numimgsallowed ] images uploaded. You can upload an additional [ $numimgsleft ] images.\n";


if($totalimagesuploaded >= $numimgsallowed){
$showhideuploadform=1;}else {$showhideuploadform='';}

$json['ustatmsg'] = $ustatmsg;
$json['showhideuploadform'] = $showhideuploadform;

}



$json['error'] = strip_tags($foto_upload->show_error_string());




echo json_encode($json);



?>