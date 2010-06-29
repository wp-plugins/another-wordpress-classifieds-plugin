<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

//Start functions from dan caragea
//---------------------------------------------------------------------------//
// author: Dan Caragea <dan@rdsct.ro>
// info:   general purpose functions
//---------------------------------------------------------------------------//
// copyright license
//
// permission is granted to anyone to use the functions listed in dcfunctions.php
// for any purpose,
// including commercial applications and to redistribute it
// freely, subject to the following restrictions:
//
// 1. the origin of this software must not be misrepresented;
//    you must not claim that you wrote the original software.
//    if you use this software in a product, an acknowledgment
//    in the product documentation is required.
//
// 2. you must not alter the source code without prior consent from the author.
//
// 3. mail about the fact of using this class in production
//    would be very appreciated.
//
// 4. this notice may not be removed or altered from any source distribution.
//
//---------------------------------------------------------------------------//

function smart_table($array,$table_cols=1,$opentable,$closetable) {
	$usingtable='';
	$myreturn="$opentable\n";
	$row=0;
	$total_vals=count($array);
	$i=1;
	$awpcpdisplayaditemclass='';


	if( (isset($opentable) && !empty($opentable)) && (isset($closetable) && !empty($closetable)) )
	{
		$usingtable=1;
	}

	foreach ($array as $v) {
			
		if ($i % 2 == 0) { $awpcpdisplayaditemclass = "displayaditemsodd"; } else { $awpcpdisplayaditemclass = "displayaditemseven"; }


		$v=str_replace("\$awpcpdisplayaditems",$awpcpdisplayaditemclass,$v);

		if ((($i-1)%$table_cols)==0)
		{
				
			if($usingtable)
			{
				$myreturn.="<tr>\n";
			}

			$row++;
		}

		if($usingtable)
		{
			$myreturn.="\t<td valign=\"top\">";
		}
			
		$myreturn.="$v";
			
		if($usingtable)
		{
			$myreturn.="</td>\n";
		}

		if ($i%$table_cols==0)
		{
			if($usingtable)
			{
				$myreturn.="</tr>\n";
			}
		}
			
		$i++;
	}
	$rest=($i-1)%$table_cols;
	if ($rest!=0) {
		$colspan=$table_cols-$rest;
			
		$myreturn.="\t<td".(($colspan==1) ? '' : " colspan=\"$colspan\"")."></td>\n</tr>\n";
			
	}
	//}
	$myreturn.="$closetable\n";
	return $myreturn;
}



function create_awpcp_random_seed() {
	list($usec, $sec) = explode(' ', microtime());
	return (int)$sec+(int)($usec*100000);
}



function vector2table($vector) {
	$afis="<table>\n";
	$i=1;
	$afis.="<tr>\n\t<td class=title colspan=2>Table</td>\n</tr>\n";
	while (list($k,$v) = each($vector)) {
		$afis.="<tr class=".(($i%2) ? "trpar" : "trimpar").">\n\t<td>".htmlentities($k)."</td>\n\t<td>".htmlentities($v)."</td>\n</tr>\n";
		$i++;
	}
	$afis.="</table>\n";
	return $afis;
}


function vector2biditable($myarray,$rows,$cols) {
	$myreturn="<table>\n";
	for ($r=0;$r<$rows;$r++) {
		$myreturn.="<tr>\n";
		for ($c=0;$c<$cols;$c++) {
			$myreturn.="\t<td>".$myarray[$r*$cols+$c]."</td>\n";
		}
		$myreturn.="</tr>\n";
	}
	$myreturn.="</table>\n";
	return $myreturn;
}


function vector2options($show_vector,$selected_map_val,$exclusion_vector=array()) {
	$myreturn='';
	while (list($k,$v)=each($show_vector)) {
		if (!in_array($k,$exclusion_vector)) {
			$myreturn.="<option value=\"".$k."\"";
			if ($k==$selected_map_val) {
				$myreturn.=" selected";
			}
			$myreturn.=">".$v."</option>\n";
		}
	}
	return $myreturn;
}


function vector2checkboxes($show_vector,$excluded_keys_vector,$checkname,$binvalue,$table_cols=1,$showlabel=true) {
	$myreturn='<table>';
	$i=0;
	$row=0;
	$myvector=array_flip(array_diff(array_flip($show_vector),$excluded_keys_vector));
	$total_vals=count($myvector);
	$i=1;
	while (list($k,$v)=each($myvector)) {
		if (($i%$table_cols)==1) {$myreturn.="<tr>\n";}
		$myreturn.="\t<td>\n";
		$myreturn.="\t\t<input type=\"checkbox\" name=\"".$checkname."[$k]\"";
		if (isset($binvalue) && ($binvalue>0) && (($binvalue>>$k)%2)) {
			//print "binvalue=$binvalue k=$k<br>";
			$myreturn.=" checked";
		}
		$myreturn.=">";
		if ($showlabel) {
			$myreturn.=$v;
		}
		$myreturn.="\n";
		$myreturn.="\t</td>\n";
		if ($i%$table_cols==0) {$myreturn.="</tr>\n";}
		$i++;
	}
	$rest=($i-1)%$table_cols;
	if ($rest!=0) {
		$colspan=$table_cols-$rest;
		$myreturn.="\t<td".(($colspan==1) ? ("") : (" colspan=\"$colspan\""))."></td>\n</tr>\n";
	}
	$myreturn.="</table>\n";
	return $myreturn;
}

function vector2binvalues($myarray) {
	$myreturn=0;
	while (list($k,$v)=each($myarray)) {
		$myreturn+=(1<<$k);
	}
	return $myreturn;
}


function binvalue2index($binvalue) {
	$myarray=array();
	$i=0;
	while ($binvalue>0) {
		if ($binvalue & 1) {
			$myarray[]=$i;
		}
		$binvalue>>=1;
		$i++;
	}
	return $myarray;
}


function array2string($myarray,$binvalue) {
	$myreturn='';
	while (list($k,$v)=each($myarray)) {
		if (isset($binvalue) && ($binvalue>0) && (($binvalue>>$k)%2)) {
			$myreturn.=$v.', ';
		}
	}
	$myreturn=substr($myreturn,0,-2);
	return $myreturn;
}


function del_keys($myarray,$keys) {
	$myreturn=array();
	while (list($k,$v)=each($myarray)) {
		if (!in_array($k,$keys)) {
			$myreturn[$k]=$v;
		}
	}
	return $myreturn;
}


function del_empty_vals($myarray) {
	$myreturn=array();
	while (list($k,$v)=each($myarray)) {
		if (!empty($v)) {
			$myreturn[$k]=$v;
		}
	}
	return $myreturn;
}

if (!function_exists('stripslashes_mq')) {
	function stripslashes_mq($value) {
		if (is_array($value)) {
			$myreturn=array();
			while (list($k,$v)=each($value)) {
				$myreturn[stripslashes_mq($k)]=stripslashes_mq($v);
			}
		} else {
			if(get_magic_quotes_gpc()==0) {
				$myreturn=$value;
			} else {
				$myreturn=stripslashes($value);
			}
		}
		return $myreturn;
	}
}

if (!function_exists('addslashes_mq')) {
	function addslashes_mq($value) {
		if (is_array($value)) {
			$myreturn=array();
			while (list($k,$v)=each($value)) {
				$myreturn[addslashes_mq($k)]=addslashes_mq($v);
			}
		} else {
			if(get_magic_quotes_gpc() == 0) {
				$myreturn=addslashes($value);
			} else {
				$myreturn=$value;
			}
		}
		return $myreturn;
	}
}

if (!function_exists('file_put_contents')) {

	function file_put_contents($myfilename,&$mydata) {
		$myreturn=false;
		if ($this->op_mode=='disk') {
			if (is_file($myfilename) && !is_writable($myfilename)) {
				@chmod($myfilename,0644);
				if (!is_writable($myfilename)) {
					@chmod($myfilename,0666);
				}
			}
			if ((is_file($myfilename) && is_readable($myfilename) && is_writable($myfilename)) || !is_file($myfilename)) {
				if ($handle=@fopen($myfilename,'wb')) {
					if (@fwrite($handle,$mydata)) {
						$myreturn=true;
					}
					@fclose($handle);
				}
			}
		} elseif ($this->op_mode=='ftp') {
			$myfilename=str_replace(_BASEPATH_.'/',_FTPPATH_,$myfilename);
			$tmpfname=tempnam(_BASEPATH_.'/tmp','ftp');
			$temp=fopen($tmpfname,'wb+');
			fwrite($temp,$mydata);
			rewind($temp);
			$old_de=ini_get('display_errors');
			ini_set('display_errors',0);
			$myreturn=ftp_fput($this->ftp_id,$myfilename,$temp,FTP_BINARY);
			fclose($temp);
			@unlink($tmpfname);
			ini_set('display_errors',$old_de);
		}
		return $myreturn;
	}
}

if (!function_exists('file_get_contents')) {

	function file_get_contents($file) {
		$myreturn='';
		if (function_exists('file_get_contents')) {
			$myreturn=file_get_contents($file);
		} else {
			$myreturn=fread($fp=fopen($file,'rb'),filesize($file));
			fclose($fp);
		}
		return $myreturn;
	}
}


function array2qs($myarray) {
	$myreturn="";
	while (list($k,$v)=each($myarray)) {
		$myreturn.="$k=$v&";
	}
	$myreturn=substr($myreturn,0,-1);
	return $myreturn;
}


function create_pager($from,$where,$offset,$results,$tpname)
{

	$permastruc=get_option('permalink_structure');
	if(isset($permastruc) && !empty($permastruc))
	{
		$awpcpoffset_set="?offset=";
	}
	else
	{
		if(is_admin())
		{
			$awpcpoffset_set="?offset=";
		}
		else
		{
			$awpcpoffset_set="&offset=";
		}
	}

	mt_srand(create_awpcp_random_seed());
	$radius=5;
	global $PHP_SELF;
	global $accepted_results_per_page;

	$accepted_results_per_page=array("5"=>5,"10"=>10,"20"=>20,"30"=>30,"40"=>40,"50"=>50,"60"=>60,"70"=>70,"80"=>80,"90"=>90,"100"=>100);

	if(!isset($tpname) || empty($tpname))
	{
		$tpname="$PHP_SELF";
	}


	$params=array();
	$params=array_merge($_GET,$_POST);
	unset($params['offset'],$params['results'],$params['PHPSESSID'],$params['aeaction'],$params['category_id'],$params['cat_ID'],$params['action'],$params['aeaction'],$params['category_name'],$params['category_parent_id'],$params['createeditadcategory'],$params['deletemultiplecategories'],$params['movedeleteads'],$params['moveadstocategory'],$params['category_to_delete'],$params['tpname'],$params['category_icon'],$params['sortby'],$params['adid'],$params['picid'],$params['adkey'],$params['editemail'],$params['adtermid']);

	$cid='';
	if( isset($_REQUEST['a']) && !empty($_REQUEST['a']) && ($_REQUEST['a'] == 'browsecat') )
	{
		$cid=$_REQUEST['category_id'];
		$params['category_id']=$cid;

		if( !get_awpcp_option('seofriendlyurls') )
		{

			$awpcppage=get_currentpagename();
			$awpcppagename = sanitize_title($awpcppage, $post_ID='');
			$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);
			if( !get_awpcp_option('seofriendlyurls') )
			{
				$params['page_id']="$awpcpwppostpageid";
			}
		}

	}

	if( isset($_REQUEST['a']) && !empty($_REQUEST['a']) && ($_REQUEST['a'] == 'browseads') )
	{

		$awpcppage=get_currentpagename();
		$awpcppagename = sanitize_title($awpcppage, $post_ID='');
		$awpcpwppostpageid=awpcp_get_page_id($awpcppagename);

		if( !get_awpcp_option('seofriendlyurls') )
		{
			$params['page_id']="$awpcpwppostpageid";
		}
	}

	$myrand=mt_rand(1000,2000);
	$myreturn="<form id=\"pagerform$myrand\" name=\"pagerform$myrand\" action=\"\" method=\"get\">\n";
	$myreturn.="<table>\n";
	$myreturn.="<tr>\n";
	$myreturn.="\t<td>\n";
	$query="SELECT count(*) FROM $from WHERE $where";
	if (!($res=@mysql_query($query))) {die(mysql_error().' on line: '.__LINE__);}
	$totalrows=mysql_result($res,0,0);
	$total_pages=ceil($totalrows/$results);
	$myreturn.="\t\t<a  href=\"$tpname".$awpcpoffset_set."0&results=$results&".array2qs($params)."\">&laquo;</a>&nbsp;";
	$dotsbefore=false;
	$dotsafter=false;
	for ($i=1;$i<=$total_pages;$i++) {
		if (((($i-1)*$results)<=$offset) && ($offset<$i*$results)) {
			$myreturn.="$i&nbsp;";
		} elseif (($i-1+$radius)*$results<$offset) {
			if (!$dotsbefore) {
				$myreturn.="...";
				$dotsbefore=true;
			}
		} elseif (($i-1-$radius)*$results>$offset) {
			if (!$dotsafter) {
				$myreturn.="...";
				$dotsafter=true;
			}
		} else {
			$myreturn.="<a href=\"$tpname$awpcpoffset_set".(($i-1)*$results)."&results=$results&".array2qs($params)."\">$i</a>&nbsp;";
		}
	}
	$myreturn.="<a href=\"$tpname$awpcpoffset_set".(($total_pages-1)*$results)."&results=$results&".array2qs($params)."\">&raquo;</a>&nbsp;\n";
	$myreturn.="\t</td>\n";
	$myreturn.="\t<td>\n";
	$myreturn.="\t\t<input type=\"hidden\" name=\"offset\" value=\"$offset\" />\n";
	while (list($k,$v)=each($params)) {
		$myreturn.="\t\t<input type=\"hidden\" name=\"$k\" value=\"$v\" />\n";
	}
	$myreturn.="\t\t<select name=\"results\" onchange=\"document.pagerform$myrand.submit()\">\n";
	$myreturn.=vector2options($accepted_results_per_page,$results);
	$myreturn.="\t\t</select>\n";
	$myreturn.="\t</td>\n";
	$myreturn.="</tr>\n";
	$myreturn.="</table>\n";
	$myreturn.="</form>\n";
	return $myreturn;
}

function _gdinfo() {
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


function unix2dos($mystring) {
	$mystring=preg_replace("/\r/m",'',$mystring);
	$mystring=preg_replace("/\n/m","\r\n",$mystring);
	return $mystring;
}


function send_email($from,$to,$subject,$message,$html=false,$attachments=array(),$bcc='') {
	$separator='Next.Part.331925654896717'.mktime();
	$att_separator='NextPart.is_a_file9817298743'.mktime();
	$headers="From: $from\n";
	$headers.="MIME-Version: 1.0\n";
	if (!empty($bcc)) {
		$headers.="Bcc: $bcc\n";
	}
	$text_header="Content-Type: text/plain; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 8bit\n\n";
	$html_header="Content-Type: text/html; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 8bit\n\n";
	$html_message=$message;
	$text_message=$message;
	$text_message=str_replace('&nbsp;',' ',$text_message);
	$text_message=trim(strip_tags(stripslashes($text_message)));
	// Bring down number of empty lines to 2 max
	$text_message=preg_replace("/\n[\s]+\n/","\n",$text_message);
	$text_message=preg_replace("/[\n]{3,}/", "\n\n",$text_message);
	$text_message=wordwrap($text_message,72);
	$message="\n\n--$separator\n".$text_header.$text_message;
	if ($html) {
		$message.="\n\n--$separator\n".$html_header.$html_message;
	}
	$message.="\n\n--$separator--\n";

	if (!empty($attachments)) {
		$headers.="Content-Type: multipart/mixed; boundary=\"$att_separator\";\n";
		$message="\n\n--$att_separator\nContent-Type: multipart/alternative; boundary=\"$separator\";\n".$message;
		while (list(,$file)=each($attachments)) {
			$message.="\n\n--$att_separator\n";
			$message.="Content-Type: application/octet-stream; name=\"".basename($file)."\"\n";
			$message.="Content-Transfer-Encoding: base64\n";
			$message.='Content-Disposition: attachment; filename="'.basename($file)."\"\n\n";
			$message.=wordwrap(base64_encode(fread(fopen($file,'rb'),filesize($file))),72,"\n",1);
		}
		$message.="\n\n--$att_separator--\n";
	} else {
		$headers.="Content-Type: multipart/alternative;\n\tboundary=\"$separator\";\n";
	}
	$message='This is a multi-part message in MIME format.'.$message;
	if (isset($_SERVER['WINDIR']) || isset($_SERVER['windir']) || isset($_ENV['WINDIR']) || isset($_ENV['windir'])) {
		$message=unix2dos($message);
	}
	//	$headers=unix2dos($headers);
	$sentok=@mail($to,$subject,$message,$headers,"-f$from");
	return $sentok;
}



//End Functions from Dan Caragea

?>