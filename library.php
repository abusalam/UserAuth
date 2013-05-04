<?php
namespace LibDB;
require_once('database.php');
function initpage()
{
	session_start();
	$sess_id=md5(microtime());

	//$_SESSION['Debug']=$_SESSION['Debug']."InInitPage(".$_SESSION['Client_SID']."=".$_COOKIE['Client_SID'].")";
	setcookie("Client_SID",$sess_id,(time()+(LifeTime*60)));
	$_SESSION['Client_SID']=$sess_id;
	$_SESSION['LifeTime']=time();
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >';
	$t=(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"");
	$reg=new DB();
	$reg->do_ins_query("INSERT INTO ".MySQL_Pre."logs(IP,URL,UserAgent,Referrer,SessionID) values"
			."('".$_SERVER['REMOTE_ADDR']."','".$_SERVER['PHP_SELF']."','".$_SERVER['HTTP_USER_AGENT']."','<".$t.">','".$_SESSION['Client_SID']."');");
	if(isset($_REQUEST['show_src']))
	{
		if($_REQUEST['show_src']=="me")
			show_source(substr($_SERVER['PHP_SELF'],1,strlen($_SERVER['PHP_SELF'])));
	}
	return;
}

function pageinfo()
{
	$strfile=strtok($_SERVER['PHP_SELF'],"/");
	//echo $_SERVER['PHP_SELF'].' | '.$strfile;
	$str=strtok("/");
	//echo ' | '.$str;
	while( $str)
	{
		$strfile=$str;
		//echo ' | '.$strfile;
		$str=strtok("/");
	}
	$reg=new DB();
 	$visitor_num=$reg->do_max_query("select VisitCount from ".MySQL_Pre."Visits where PageURL='".$_SERVER['PHP_SELF']."'");
	$LastVisit=$reg->do_max_query("select timestamp(LastVisit) from ".MySQL_Pre."Visits where PageURL like '".$_SERVER['PHP_SELF']."'");
	if($visitor_num>0)
		$reg->do_ins_query("update ".MySQL_Pre."Visits set `VisitCount`=`VisitCount`+1, VisitorIP='".$_SERVER['REMOTE_ADDR']."' where PageURL='".$_SERVER['PHP_SELF']."'");
	else
		$reg->do_ins_query("Insert into ".MySQL_Pre."Visits(PageURL,VisitorIP) values('".$_SERVER['PHP_SELF']."','".$_SERVER['REMOTE_ADDR']."')");
	$_SESSION['LifeTime']=time();
	echo "<strong > Last Updated On:</strong> &nbsp;&nbsp;".date("l d F Y g:i:s A ",filemtime($strfile))
		." IST &nbsp;&nbsp;&nbsp;<b>Your IP: </b>".$_SERVER['REMOTE_ADDR']
		."&nbsp;&nbsp;&nbsp;<b>Visits:</b>&nbsp;&nbsp;".$visitor_num
		." <b>Last Visit:</b> ".date(" g:i:s A ",strtotime($LastVisit))
		."";
	$reg->do_close();
	return;
}
function footerinfo()
{
	echo 'Designed and Developed By <strong>National Informatics Centre</strong>, Paschim Medinipur District Centre<br/>'
	.'L. A. Building (2nd floor), Collectorate Compound, Midnapore<br/>'
	.'West Bengal - 721101 , India Phone : 91-3222-263506, Email: wbmdp(a)nic.in<br/>';
	//."DB_SID: ".$_SESSION['ID']." ORG: ".session_id()." Cookie:".$_COOKIE['LMS_SID']." VALID=".$_SESSION['Validity']." | ".LifeTime.$_SESSION['LMS_AUTH'];
}
function ToDate($AppDate)
{
	if($AppDate!="")
		return date("d-m-Y",strtotime($AppDate));
	else
		return date("d-m-Y",time());
}
function ToDBDate($AppDate)
{
	if ($AppDate=="")
		return date("Y-m-d",time());
	else
		return date("Y-m-d",strtotime($AppDate));
}
function RandStr($length) {
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$size = strlen($chars);
	$str="";
	for($i=0;$i<$length;$i++){
		$Chr= $chars[ rand( 0, $size - 1 ) ];
		$str .=$Chr;
		$chars=str_replace($Chr, "", $chars);
		$size = strlen($chars);
	}
	return $str;
}
?>
