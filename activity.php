<?php 
use LibDB as FN;
require_once('functions.php');
FN\session_auth();
FN\HtmlHeader("Activity");
?>
<style type="text/css" media="all" title="CSS By Abu Salam Parvez Alam" >
<!--
@import url("css/Style.css");
-->
</style>
</head>
<body>
<div class="TopPanel">
  <div class="LeftPanelSide"></div>
  <div class="RightPanelSide"></div>
  <h1><?php echo AppTitle; ?></h1>
</div>
<div class="Header">
</div>
<?php 
require_once("topmenu.php");
?>
<div class="content" style="margin-left:5px;margin-right:5px;">
<h2>Activity Logs</h2><!--CONCAT(DATE_FORMAT(max(vtime)+(9*3600)+(30*60),'%W %d %M %Y %r'),' IST')-->
<?php
//echo "Session Time: ".$_SESSION['LifeTime']." :> ".time();
	$Data=new FN\DB();
if($_SESSION['UserLevel']=='1')
	{
		
?>
<form name="frm_activity" method="post" action="<?php $_SERVER['PHP_SELF']?>">
    <label for="Officer">Officer:</label>
    <select name="Officer" onChange="javascript:document.frm_activity.submit();">
      <?php 
		if($_POST['Officer']=="")
			$Choice="-- Choose --";
		else
			$Choice=$_POST['Officer'];
		$Query="SELECT `OfficerID`, concat(`Designation`,' [',`DeptName`,']') as Designation FROM lms_officers o,lms_departments d Where d.DeptID=o.DeptID AND User=1  order by Designation";
		  $Data->show_sel("OfficerID","Designation",$Query,$Choice);
	  ?>
	</select>
</form>
<?php
	$Query="SELECT l.LogID,l.`ip` as `IP Address`,l.`AccessTime` , l.`Action`, l.`SessionID`, l.`Method`  FROM ".MySQL_Pre."logs l Where l.`UserMapID`=".$_POST['Officer']." ORDER BY l.`LogID` desc limit 50;";
	}	
else
	$Query="SELECT LogID,`ip` as `IP Address`,AccessTime , Action, SessionID, Method  FROM ".MySQL_Pre."logs Where UserMapID=".$_SESSION['UserMapID']." ORDER BY LogID desc limit 50;";
	FN\ShowData($Query);
	echo "<br />".$Query;
?>
</div>
<div class="pageinfo">
  <?php FN\pageinfo(); ?>
</div>
<div class="footer">
  <?php FN\footerinfo();?>
</div>
</body>
</html>
 