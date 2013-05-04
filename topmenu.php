<?php use LibDB as FN; ?>
<div class="MenuBar">
	<ul>
		<li
			class="<?php echo ($_SERVER['SCRIPT_NAME']==BaseDIR.'index.php')?'SelMenuitems':'Menuitems';?>">
			<a href="<?php echo FN\GetAbsoluteURLFolder(); ?>index.php">Home</a>
		</li>
		<li
			class="<?php echo ($_SERVER['SCRIPT_NAME']==BaseDIR.'AppForm.php')?'SelMenuitems':'Menuitems';?>">
			<a href="<?php echo FN\GetAbsoluteURLFolder(); ?>AppForm.php">Data Entry</a>
		</li>
		<li
			class="<?php echo ($_SERVER['SCRIPT_NAME']==BaseDIR.'Report.php')?'SelMenuitems':'Menuitems';?>">
			<a href="<?php echo FN\GetAbsoluteURLFolder(); ?>Report.php">Report</a>
		</li>
		<li
			class="<?php echo ($_SERVER['SCRIPT_NAME']==BaseDIR.'activity.php')?'SelMenuitems':'Menuitems';?>">
			<a href="<?php echo FN\GetAbsoluteURLFolder(); ?>activity.php">Activity Report</a>
		</li>
		<li
			class="<?php echo ($_SERVER['SCRIPT_NAME']==BaseDIR.'changepwd.php')?'SelMenuitems':'Menuitems';?>">
			<a href="<?php echo FN\GetAbsoluteURLFolder(); ?>changepwd.php">Change Password</a>
		</li>
		<li
			class="<?php echo ($_SERVER['SCRIPT_NAME']==BaseDIR.'?LogOut=1')?'SelMenuitems':'Menuitems';?>">
			<a href="<?php echo FN\GetAbsoluteURLFolder(); ?>?LogOut=1">Logout</a>
		</li>
	</ul>
</div>
