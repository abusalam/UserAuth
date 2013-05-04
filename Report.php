<?php 
use LibDB as FN;
require_once('functions.php');
FN\session_auth();
FN\HtmlHeader("Report");
FN\IncludeCSS();
FN\jQueryInclude();
?>
<script type="text/javascript">
$(function() {
	$( ".datepick" ).datepicker({minDate: "-43Y", 
								maxDate: "-18Y",
								dateFormat: 'yy-mm-dd',
								showOtherMonths: true,
								selectOtherMonths: true,
								showButtonPanel: true,
								changeMonth: true,
							    changeYear: true,
								showAnim: "slideDown"
								});
});
</script>
</head>
<body>
	<div class="TopPanel">
		<div class="LeftPanelSide"></div>
		<div class="RightPanelSide"></div>
		<h1>
			<?php echo AppTitle;?>
		</h1>
	</div>
	<div class="Header"></div>
	<?php 
	require_once("topmenu.php");
	?>
	<div class="content">
		<h2>Report</h2>
		
		<p>Content goes here</p>
		
	</div>
	<div class="pageinfo">
		<?php FN\pageinfo(); ?>
	</div>
	<div class="footer">
		<?php FN\footerinfo();?>
	</div>
</body>
</html>
