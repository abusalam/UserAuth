<?php
use LibDB as FN;
require_once('functions.php');
session_start();
$Data = new FN\DB();
$ID = FN\GetVal($_SESSION, 'ID');
$_SESSION['ID'] = session_id();
if (!isset($_SESSION['LifeTime']))
	$_SESSION['LifeTime'] = time();
$action = FN\CheckAuth();
if ($action == "LogOut") {
	$Data->do_ins_query("INSERT INTO `".MySQL_Pre."logs` (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserMapID`,`URL`,`Action`,`Method`,`URI`) values"
		. "('" . $_SESSION['ID'] . "','" . $_SERVER['REMOTE_ADDR'] . "','" 
		. $Data->SqlSafe($_SERVER["HTTP_REFERER"]) . "','" . $_SERVER['HTTP_USER_AGENT'] 
		. "','" . FN\GetVal($_SESSION, 'UserMapID') . "','" . $Data->SqlSafe($_SERVER['PHP_SELF']) . "','" 
		. $action . ": (" . $_SERVER['SCRIPT_NAME'] .")','" . $Data->SqlSafe($_SERVER['REQUEST_METHOD']) 
		. "','" . $Data->SqlSafe($_SERVER['REQUEST_URI']) . "');");
	session_unset();
	session_destroy();
	session_start();
	$_SESSION = array();
	$_SESSION['Debug'] = $_SESSION['Debug'] . $action . "TOKEN-!Valid";
	header("Location: index.php");
	exit;
}
if ($action != "Valid") {
	FN\initSess();
}
if ((FN\GetVal($_POST, 'UserID') !== NULL) && (FN\GetVal($_POST, 'UserPass') !==NULL)) {
	$QueryLogin ="Select UserMapID,UserName from `".MySQL_Pre."Users` U "
			. "where `UserID`='" . $_POST['UserID'] . "' AND MD5(concat(`UserPass`,MD5('" 
			. $_POST['LoginToken'] . "')))='" . $_POST['UserPass'] . "' AND Activated";
	$rows = $Data->do_sel_query($QueryLogin);
	if ($rows > 0) {
		session_regenerate_id();
		$Row = $Data->get_row();
		$_SESSION['UserName'] = $Row['UserName'];
		$_SESSION['UserMapID'] = $Row['UserMapID'];
		$_SESSION['ID'] = session_id();
		$_SESSION['FingerPrint'] = md5($_SERVER['REMOTE_ADDR'] . $_SERVER[
			'HTTP_USER_AGENT'] . "KeyLeft");
		$_SESSION['REFERER1'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER[
			'REQUEST_URI'];
		$action = "JustLoggedIn";
		$Data->do_ins_query(
			"Update ".MySQL_Pre."Users Set LoginCount=LoginCount+1 where `UserID`='" .
			$_POST['UserID'] . "' AND MD5(concat(`UserPass`,MD5('" . $_POST[
			'LoginToken'] . "')))='" . $_POST['UserPass'] . "'");
		$Data->do_ins_query(
			"INSERT INTO ".MySQL_Pre."logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserMapID`,`URL`,`Action`,`Method`,`URI`) values"
			. "('" . $_SESSION['ID'] . "','" . $_SERVER['REMOTE_ADDR'] . "','" .
			$Data->SqlSafe($_SERVER['HTTP_REFERER']) . "','" .
			$_SERVER['HTTP_USER_AGENT'] . "','" . $_SESSION['UserMapID'] . "','"
			. $Data->SqlSafe($_SERVER['PHP_SELF']) .
			"','Login: Success','" . $Data->SqlSafe($_SERVER[
			'REQUEST_METHOD']) . "','" . $Data->SqlSafe($_SERVER[
			'REQUEST_URI'] . $_SERVER['QUERY_STRING']) . "');");
	} else {
		$action = "NoAccess";
		$Data->do_ins_query(
			"INSERT INTO ".MySQL_Pre."logs (`SessionID`,`IP`,`Referrer`,`UserAgent`,`UserMapID`,`URL`,`Action`,`Method`,`URI`) values"
			. "('" . $_SESSION['ID'] . "','" . $_SERVER['REMOTE_ADDR'] . "','" .
			$Data->SqlSafe($_SERVER['HTTP_REFERER']) . "','" .
			$_SERVER['HTTP_USER_AGENT'] . "','" . $_POST['UserID'] . "','" .
			$Data->SqlSafe($_SERVER['PHP_SELF']) .
			"','Login: Failed[".$_POST['UserID']."]','" . $Data->SqlSafe($_SERVER[
			'REQUEST_METHOD']) . "','" . $Data->SqlSafe($_SERVER[
			'REQUEST_URI'] . $_SERVER['QUERY_STRING']) . "');");
	}
}
$_SESSION['Token'] = md5($_SERVER['REMOTE_ADDR'] . $ID . time());

FN\HtmlHeader("Login");
FN\IncludeCSS();
FN\jQueryInclude();
FN\IncludeJS("js/php.js");
?>
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
if (FN\GetVal($_SESSION, 'UserName') !== NULL) {
	require_once('topmenu.php');
} else {
?>
<div class="MenuBar"></div>
  	<?php
}
?>
<div class="content">
<?php
//echo $action."Captcha: ".$_SESSION['Captcha'];
switch ($action) {
	case "LogOut":
		echo
			"<h2 align=\"center\">Thank You! You Have Successfully Logged Out!</h2>";
		break;
	case "JustLoggedIn":
		echo "<h2 align=\"center\">Welcome " . $_SESSION['UserName'] .
			" You Have Successfully Logged In!</h2>";
		break;
	case "Valid":
		echo "<h2 align=\"center\">You are already Logged In as {$_SESSION['UserName']}!</h2>";
		break;
	case "NoAccess":
		echo "<h2 align=\"center\">Sorry! Access Denied!</h2>";
		break;
	default:
		echo "<h2>Login - " . AppTitle . "</h2>";
		break;
}
if (($action != "JustLoggedIn") && ($action != "Valid")) {
?> 
<form name="frmLogin" method="post" action="<?php $_SERVER['PHP_SELF']?>">
	<?php //echo "USERID: ".$_POST['UserID']."<br/>".$_POST['UserPass']."<br />".$UserPass.$QueryLogin.$action; ?>
    <label for="UserID">User ID:</label><br />
	<input type="text" id="UserID" name="UserID" value="admin" autocomplete="off"/>
<br />
<label for="UserPass">Password:</label><br />
<input type="password" id="UserPass" name="UserPass" value="test@123" autocomplete="off"/><br />
<input type="hidden" name="LoginToken" value="<?php echo $_SESSION['Token'];?>" />
<input style="width:80px;" type="submit" value="Login" onClick="document.getElementById('UserPass').value=MD5(MD5(document.getElementById('UserPass').value)+'<?php echo md5($_SESSION['Token']);?>');"/>
</form>
<p><b>Register: </b><a href="Register.php">Click here</a> to register yourself</p>
<?php
	//echo $_SESSION['Debug'];
}
?>
</div>
<div class="pageinfo">
  <?php FN\pageinfo(); ?>
</div>
<div class="footer">
  <?php FN\footerinfo(); ?>
</div>
<?php
//print_r($_SESSION);
?>
</body>
</html>
