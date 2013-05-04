<?php
namespace LibDB;

require_once('library.php');

function GetVal($Array,$Index,$IsCombo=FALSE){
	$Data=new DB();
	if(!isset($Array[$Index])){
		if ($IsCombo)
			return "-- Choose --";
		else
			return NULL;
	}
	else{
		if ($IsCombo && ($Array[$Index]===NULL))
			return "-- Choose --";
		else
			return $Data->SqlSafe($Array[$Index]);
	}
	
}

function GetAbsoluteURLFolder()
{
	$scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
	$scriptFolder .= $_SERVER['HTTP_HOST'].BaseDIR;
	return $scriptFolder;
}

function InpSanitize($PostData){
	//$Fields="";
	$Data=new DB();
	foreach ($PostData as $FieldName => &$Value){
		$Value=$Data->SqlSafe($Value);
		//$Fields=$Fields."<br />".$FieldName;
		if($Value==""){
			$_SESSION['Msg']='<b>Message:</b> Field '.GetColHead($FieldName).' left unfilled.';
		}
	}
	unset($Value);
	//$PostData['Fields']=$Fields;
	//echo "Total Fields:".count($PostData);
	return $PostData;
}
/*
 * Shows the content of $_SESSION['Msg']
 */
function ShowMsg(){
	if(GetVal($_SESSION,"Msg")!=""){
		echo '<span class="Message">'.$_SESSION['Msg'].'</span><br/>';
		$_SESSION['Msg']="";
	}
}

function initSess()
{
	$sess_id=md5(microtime());
	
	$_SESSION['Debug']=GetVal($_SESSION,'Debug')."InInitPage(".GetVal($_SESSION,'SESSION_TOKEN')."=".GetVal($_COOKIE,'SESSION_TOKEN').")";
	setcookie("SESSION_TOKEN",$sess_id,(time()+(LifeTime*60)));
	$_SESSION['SESSION_TOKEN']=$sess_id;
	$_SESSION['LifeTime']=time();
	$t=(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"");
	$reg=new DB();				
	$reg->do_ins_query("INSERT INTO ".MySQL_Pre."visitors(ip,vpage,uagent,referrer) values"		
			."('".$_SERVER['REMOTE_ADDR']."','".htmlspecialchars($_SERVER['PHP_SELF'])."','".$_SERVER['HTTP_USER_AGENT']
			."','<".$t.">');");
	if(isset($_REQUEST['show_src']))
	{
		if($_REQUEST['show_src']=="me")
		show_source(substr($_SERVER['PHP_SELF'],1,strlen($_SERVER['PHP_SELF'])));
	}	
	return;
}

function CheckAuth()
{
	$_SESSION['Debug']=GetVal($_SESSION,'Debug')."CheckAuth";
    if((!isset($_SESSION['UserName'])) && (!isset($_SESSION['UserMapID'])))
	{
		return "Browsing";
	}
	if(isset($_REQUEST['LogOut']))
    {
        return "LogOut";
    }
    else if($_SESSION['LifeTime']<(time()-(LifeTime*60)))
    {
        return "TimeOut(".$_SESSION['LifeTime']."-".(time()-(LifeTime*60)).")";
    }
    else if($_SESSION['SESSION_TOKEN']!=$_COOKIE['SESSION_TOKEN'])
    {
        $_SESSION['Debug']="(".$_SESSION['SESSION_TOKEN']."=".$_COOKIE['SESSION_TOKEN'].")";
		return "INVALID SESSION (".$_SESSION['SESSION_TOKEN']."=".$_COOKIE['SESSION_TOKEN'].")";
    }
    elseif ($_SESSION['ID']!==session_id()){
    	$_SESSION['Debug']="(".$_SESSION['ID']."=".session_id().")";
    	return "INVALID SESSION (".$_SESSION['ID']."=".session_id().")";
    }
    else
    {                                        
		return "Valid";
    }
}

function session_auth()
{
	if (! isset($_SESSION))
		session_start();
	$_SESSION['Debug']=GetVal($_SESSION,'Debug')."InSession_AUTH";
	$SessRet=CheckAuth();
	$reg=new DB();
	//$reg->do_max_query("Select 1");
	if(GetVal($_REQUEST,'NoAuth'))
		initSess();
	else
	{
		if($SessRet!="Valid")
		{
			$reg->do_ins_query("INSERT INTO ".MySQL_Pre."logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"
						."('".GetVal($_SESSION,'ID')."','".$_SERVER['REMOTE_ADDR']."','".$reg->SqlSafe($_SERVER['HTTP_REFERER'])."','".$reg->SqlSafe($_SERVER['HTTP_USER_AGENT'])
						."','".GetVal($_SESSION,'UserName')."','".$reg->SqlSafe($_SERVER['PHP_SELF'])."','".$SessRet.": ("
						.$_SERVER['SCRIPT_NAME'].")','".$reg->SqlSafe($_SERVER['REQUEST_METHOD'])."','".$reg->SqlSafe($_SERVER['REQUEST_URI'])."');");    
			session_unset();
			session_destroy();
			session_start();
			$_SESSION=array();
			$_SESSION['Debug']=GetVal($_SESSION,'Debug').$SessRet."SESSION_TOKEN-!Valid";
			header("Location: ".BaseDIR."index.php");
			exit;
		}
		else
		{
			$_SESSION['Debug']=GetVal($_SESSION,'Debug')."SESSION_TOKEN-IsValid";
			$sess_id=md5(microtime());
			setcookie("SESSION_TOKEN",$sess_id,(time()+(LifeTime*60)));
			$_SESSION['SESSION_TOKEN']=$sess_id;
			$_SESSION['LifeTime']=time();
			$t=(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:"");  
			$reg->do_ins_query("INSERT INTO ".MySQL_Pre."Visitors(ip,vpage,uagent,referrer) values"		
				."('".$_SERVER['REMOTE_ADDR']."','".htmlspecialchars($_SERVER['PHP_SELF'])."','".$_SERVER['HTTP_USER_AGENT']
				."','<".$t.">');");
			$LogQuery="INSERT INTO ".MySQL_Pre."logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserID`,`URL`,`Action`,`Method`,`URI`) values"		
				."('".GetVal($_SESSION,'ID')."','".$_SERVER['REMOTE_ADDR']."','".$reg->SqlSafe($t)."','".$_SERVER['HTTP_USER_AGENT']
				."','".GetVal($_SESSION,'UserName')."','".$reg->SqlSafe($_SERVER['PHP_SELF'])."','Process (".$_SERVER['SCRIPT_NAME'].")','"
				.$reg->SqlSafe($_SERVER['REQUEST_METHOD'])."','".$reg->SqlSafe($_SERVER['REQUEST_URI'])."');";
			$reg->do_ins_query($LogQuery);
		}
	}
	if(GetVal($_REQUEST,'show_src')!==NULL)
	{
		echo $LogQuery;
		if($_REQUEST['show_src']=="me")
		show_source(substr($_SERVER['PHP_SELF'],1,strlen($_SERVER['PHP_SELF'])));
	}	
	return;	
}

function EditForm($QueryString)
{ 
	$RowBreak=8;
	$Data=new DB();
	$TotalRows=$Data->do_sel_query($QueryString);
	// Printing results in HTML 
	echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
		.'"><table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1">';
	//Update Table Data
	$col=1;
	$TotalCols=$Data->ColCount;
	if($_POST['AddNew']=="New Rows")
	{
		$i=0;
		$AddNewDB=new DB();
		$MaxSlNo=$AddNewDB->do_max_query("Select max(SlNo)+1 from ".$_SESSION['TableName']." Where PartID=".$_SESSION['PartID']);
		if($MaxSlNo==0)
			$MaxSlNo=1;
		while($i<intval($_POST['txtInsRows']))
		{
			$Query="Insert Into ".$_SESSION['TableName']."(`SlNo`,`PartID`) values({$MaxSlNo},{$_SESSION['PartID']});";
			$AddNewDB->do_ins_query($Query);
			$i++;
			$MaxSlNo++;
			//echo $Query."<br />";
		}
		$AddNewDB->do_close();
		unset($AddNewDB);
	}
	elseif($_POST['Delete']=="Delete")
	{
		$DelDB=new DB();
		$DelDB->do_max_query("Select 1");
		for($i=0;$i<count($_POST['RowSelected']);$i++)
		{
			$Query="Delete from {$_SESSION['TableName']} Where PartID={$_SESSION['PartID']} AND SlNo=".$_POST['RowSelected'][$i];
			$DelDB->do_ins_query($Query);
		}
		$DelDB->do_close();
		unset($DelDB);
	}
	else
	{
		if(isset($_POST[$Data->GetFieldName($col)]))
		{
			$DBUpdt=new DB();
			while ($col<$TotalCols)
			{
				$row=0;
				//echo $row.",".$col."--".$Data->GetFieldName($col)."--".$Data->GetTableName($col)
				//	.$_POST[$Data->GetFieldName($col)][$row];
				while($row<count($_POST[$Data->GetFieldName($col)]))
				{
					$Query="Update ".$Data->GetTableName($col)
						." Set ".$Data->GetFieldName($col)."='".$DBUpdt->SqlSafe($_POST[$Data->GetFieldName($col)][$row])."'"
						." Where ".$Data->GetFieldName(0)."=".$DBUpdt->SqlSafe($_POST[$Data->GetFieldName(0)][$row])." AND PartID=".$_SESSION['PartID']." LIMIT 1;";
					//echo $Query."<br />";
					$DBUpdt->do_ins_query($Query);
					$row++;
				}
				$col++;
			}
			//echo $Query."<br />";
			$DBUpdt->do_close();
			unset($DBUpdt);
		}
	}
	$EditRows=$TotalRows-10;		
	if(intval($_SESSION['PartID'])>0)
		$EditRows=(intval($_POST['SlFrom'])>0)?(intval($_POST['SlFrom'])-1):$EditRows;
	$QueryString=$QueryString." LIMIT ".(($EditRows>0)?$EditRows:0).",10";
	$Data->do_sel_query($QueryString);
	//Print Collumn Names
	$i=0;
	echo "Total Records: {$TotalRows}";
	echo '<tr><td colspan="'.$TotalCols.'" style="background-color:#F4A460;"></td></tr><tr>';
	
	while ($i<$TotalCols)
	{
		echo '<th>'.GetColHead($Data->GetFieldName($i)).'</th>';
		$i++;
		if (($i%$RowBreak)==0 && $i>1)
				echo '</tr><tr>';
	}
	echo '</tr><tr><td colspan="'.$TotalCols.'" style="background-color:#F4A460;"></td></tr>';
	//Print Rows
	$odd="";
	$RecCount=0;
	while ($line = $Data->get_row()) 
	{   
		$RecCount++;
		$odd=$odd==""?"odd":"";
		echo '<tr class="'.$odd.'">';
		$i=0;
		foreach ($line as $col_value)
		{  
			if (($i%$RowBreak)==0 && $i>1)
				echo '</tr><tr>';
			echo '<td>';
			if($i==0)
			{
				$allow='readonly';
				echo '<input type="checkbox" name="RowSelected[]" value="'.htmlspecialchars($col_value).'"/>&nbsp;&nbsp;'
					.'<!--a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
					.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a-->&nbsp;&nbsp;';
			}
			else
				$allow='';
			echo '<input '.$allow.' type="text"';
				//size="'.((mysql_field_len($Data->result,$i)>40)?40:mysql_field_len($Data->result,$i)).'"
			echo ' name="'.$Data->GetFieldName($i).'[]" value="'.htmlspecialchars($col_value).'" /> </td>';     
			$i++;
		}   
		echo '</tr><tr><td colspan="'.$TotalCols.'" style="background-color:#F4A460;"></td></tr>'; 
	} 
	echo '<tr><td colspan="'.$TotalCols.'" style="text-align:right;">'
		.'<label for="txtInsRows">Insert:</label>'
		.'<input type="text" name="txtInsRows" size="3" value="'.(isset($_POST['txtInsRows'])?htmlspecialchars($_POST['txtInsRows']):"1").'"/>'
		.'<input type="hidden" name="ShowBlank" value="'.$_POST['ShowBlank'].'" />'
		.'<input type="submit" name="AddNew" value="New Rows" /><input style="width:80px;" type="submit" name="Delete" value="Delete" />';
	echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>'; 
}

function ShowData($QueryString)
{ 
	// Connecting, selecting database 
	$Data=new DB();
	$TotalRows=$Data->do_sel_query($QueryString);  
	$TotalCols=$Data->ColCount;
	// Printing results in HTML 
	echo '<table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1">'; 
	$i=0;
	
	echo "Total Records: {$TotalRows}<br />";
	while ($i<$TotalCols)
	{
		echo '<th style="text-align:center;">'.GetColHead($Data->GetFieldName($i)).'</th>';
		$i++;
	}
	$j=0;
	while ($line = $Data->get_row()) 
	{   
		echo "\t<tr>\n";   
		foreach ($line as $col_value)
			echo "\t\t<td>".$col_value."</td>\n";
		//$strdt=date("F j, Y, g:i:s a",$ntime); 
		//echo "\t\t<td>$strdt</td>\n";   
		echo "\t</tr>\n"; 
		$j++;
	} 
	echo "</table>\n"; 
	unset($Data);
	return ($j);
}

function GetOfficeName()
{
	if(intval($_SESSION['PartID'])>0)
	{
		$Fields=new DB();
		$PartName=$Fields->do_max_query("Select CONCAT(PartNo,'-',PartName) as PartName from ".MySQL_Pre."PartMap where PartID=".$_SESSION['PartID']);
		$Fields->do_close();
		unset($Fields);
	}
	return ($PartName);
}

function GetColHead($ColName)
{
	$Fields=new DB();
	$ColHead=$Fields->do_max_query("Select Caption from ".MySQL_Pre."Fields where FieldName='{$ColName}'");
	$Fields->do_close();
	unset($Fields);
	return (!$ColHead?$ColName:$ColHead);
}

function HtmlHeader($PageTitle="Paschim Medinipur"){
	$AppTitle=AppTitle;
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml">';
	echo '<head>';
	echo "<title>{$PageTitle} - {$AppTitle}</title>";
	echo '<meta name="robots" content="noarchive,noodp">';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
}
function jQueryInclude(){
	echo  '<link type="text/css" href="css/ui-lightness/jquery-ui-1.10.2.custom.min.css" rel="Stylesheet" />' 
		. '<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>'
		. '<script type="text/javascript" src="js/jquery-ui-1.10.2.custom.min.js"></script>';
}
function IncludeJS($JavaScript){
	echo '<script type="text/javascript" src="'.$JavaScript.'"></script>';
}

function IncludeCSS($CSS="css/Style.css"){
	echo  '<link type="text/css" href="'.$CSS.'" rel="Stylesheet" />';
}

function HelplineReply($AppName, $TxtQry, $ReplyTxt) {
	$AppName=AppTitle; //Quirk
	$Body = '<h2>' . $AppName . '</h2><div>' . '<b>Your Query:</b><br/>' . str_replace("\r\n", "<br />", $TxtQry) . '<br/><br/>' . '<b>Reply:</b>' . '<p><i>' . str_replace("\r\n", "<br />", $ReplyTxt) . '</i></p>' . '</div>';
	return $Body;
}
?>