<?php 
use LibDB as FN;
require_once('functions.php');
FN\session_auth();
FN\HtmlHeader("Change Password");
FN\IncludeCSS();
FN\IncludeJS("js/php.js");
?>
<script type="text/javascript">
	function ChkPwd(Token)
	{
			if(document.getElementById('CNewPassWD').value.length > 6 
							&& /\d/.test(document.getElementById('CNewPassWD').value) 
							&& /[A-Z]/.test(document.getElementById('CNewPassWD').value) 
							&& /[-*!@#$%^&+=]/.test(document.getElementById('CNewPassWD').value)) { 
					document.getElementById('OldPassWD').value=MD5(MD5(document.getElementById('OldPassWD').value)+Token); 
					document.getElementById('NewPassWD').value=MD5(MD5(document.getElementById('NewPassWD').value)+Token); 
					document.getElementById('CNewPassWD').value=MD5(document.getElementById('CNewPassWD').value);
					document.getElementById('frmChgPWD').submit();
			} else {
					alert('Your password is very weak!\n\n'
									+'Password Should be atleast 6 characters long '
									+'and must contain lowercase and uppercase letters, '
									+'numbers, and non alpha-numeric characters atleast one each.');
			}
	}
</script>
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
$action=-1;
$Data=new FN\DB();
	if(FN\GetVal($_POST,'LoginToken')!==$_SESSION['Token'])
	{
		$action=2;
	}
	else
	{
		;
		if(FN\GetVal($_POST,'NewPassWD')!==md5(FN\GetVal($_POST,'CNewPassWD').md5($_SESSION['Token'])))
		{
			$action=3;
		}
		else
		{
			$Qry="Update `".MySQL_Pre."Users` set UserPass='" . $Data->SqlSafe(FN\GetVal($_POST,'CNewPassWD'))
			. "' where UserMapID=" . $_SESSION['UserMapID'] . " AND md5(concat(`UserPass`,md5('" . FN\GetVal($_POST,'LoginToken') . "')))='"
			. FN\GetVal($_POST,'OldPassWD') . "'";
			$rows=$Data->do_ins_query($Qry);
			if($rows>0)
			{
				$action=1;
			}
			else
			{
				$action=2;
			}
		}
		$_SESSION['Token']=md5($_SERVER['REMOTE_ADDR'].session_id().time());
	}
require_once("topmenu.php");
?>
<div class="content">
  <?php 

//echo $action;
//echo $_POST['OldPassWD']."---".$_POST['NewPassWD']."---".$_POST['CNewPassWD']."<br />".$Qry;
switch($action)
{
	case 1:
		echo "<h2>Your password changed Successfully!</h2>";
	break;
	case 2:
		echo "<h2>Sorry! Invalid Old Password!</h2>";
	break;
	case 3:
		echo "<h2>New Passwords do not match.</h2>";
	break;
	default:
		echo "<h2>Change Password</h2>";
		break;
}
if(($action==2) || ($action==-1) || ($action==3))
{
?>
  <form name="frmChgPWD" id="frmChgPWD" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
    <label for="OldPassWD">Old Password:</label><br />
    <input type="password" name="OldPassWD" id="OldPassWD" />
    <br />
    <label for="NewPassWD">New Password:</label> <br />
    <input type="password" name="NewPassWD" id="NewPassWD" />
    <br />
    <label for="CNewPassWD">Confirm New Password:</label> <br />
    <input type="password" name="CNewPassWD" id="CNewPassWD" />
    <input type="hidden" name="LoginToken" value="<?php echo $_SESSION['Token'];?>" />
    <br />
    <input type="button" value="Change" onClick="ChkPwd('<?php echo md5($_SESSION['Token']);?>');" />
  </form>
      <?php 
}
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
