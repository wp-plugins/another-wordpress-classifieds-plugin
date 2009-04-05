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
$uploaddir=$wpcontentdir.'/uploads/';


$foto_upload = new Foto_upload;


if(isset($_POST['MAX_FILE_SIZE']) && !empty($_POST['MAX_FILE_SIZE'])){
$json['size'] = $_POST['MAX_FILE_SIZE'];}
else { $json['size'] = get_awpcp_option('maxfilesize');}
if(isset($_POST['ADID']) && !empty($_POST['ADID'])){
$adid=$_POST['ADID'];}else { $adid='';}
if(isset($_POST['ADTERMID']) && !empty($_POST['ADTERMID'])){
$adtermid=$_POST['ADTERMID'];}else { $adtermid='';}




$json['img'] = '';
$json['ustatmsg'] = '';
$json['showhideuploadform'] = '';

$foto_upload->upload_dir = $uploaddir;
$foto_upload->foto_folder = $uploaddir . 'awpcp/';
$foto_upload->thumb_folder = $uploaddir . 'awpcp/thumbs/';
$foto_upload->extensions = array(".jpg", ".gif", ".png");
$foto_upload->language = "en";
$foto_upload->x_max_size = 640;
$foto_upload->y_max_size = 480;
$foto_upload->x_max_thumb_size = 125;
$foto_upload->y_max_thumb_size = 125;

$twidth=$foto_upload->x_max_thumb_size;

$hiderv=true;

if(isset($_FILES['fileToUpload']) && !empty($_FILES['fileToUpload'])){
$foto_upload->the_temp_file = $_FILES['fileToUpload']['tmp_name'];
$foto_upload->the_file = $_FILES['fileToUpload']['name'];
$foto_upload->http_error = $_FILES['fileToUpload']['error'];
$hiderv=false;
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
if(get_awpcp_option('imagesallowdisallow') == '1'){
	if(get_awpcp_option('freepay') == '1'){
	$numimgsallowed=get_numimgsallowed($adtermid);}
	else {$numimgsallowed=get_awpcp_option('imagesallowedfree');}
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



if(!($hiderv)){
echo json_encode($json);
}


?>