<?php
namespace LibDB;
require_once('MySQLServer.php');
class DB {
	public $conn;
	public $RecSet;
	public $Debug;
	public $RowCount;
	public $ColCount;
	private $NoResult;
	private function do_connect()
	{
		//$this->Debug=1;
		$this->conn=mysql_connect(HOST_Name,MySQL_User,MySQL_Pass);
		if(!$this->conn)
		{
			die('Could not Connect: '.mysql_error()."<br><br>");
		}
		mysql_select_db(MySQL_DB) or die('Cannot select database (database.php): '.mysql_error()."<br><br>");
		$this->NoResult=1;
	}
	public function SqlSafe($StrValue)
	{
		$this->do_connect();
		return mysql_real_escape_string(htmlspecialchars($StrValue));
	}
	public function do_ins_query($querystr)
	{
		$this->do_connect();
		$this->RecSet = mysql_query($querystr,$this->conn);
		if (!$this->RecSet)
		{
			$message = 'Error(database): ' . mysql_error();
			//$message .= 'Whole query: '. $querystr."<br>";
			if($this->Debug)
				echo $message;
			$this->RowCount=0;
			return 0;
		}
		$this->NoResult=1;
		$this->RowCount=mysql_affected_rows($this->conn);
		return $this->RowCount;
	}

	public function do_sel_query($querystr)
	{
		$this->do_connect();
		$this->RecSet = mysql_query($querystr,$this->conn);
		if (mysql_errno($this->conn))
		{
			if($this->Debug)
				echo mysql_error($this->conn);
			$this->NoResult=1;
			$this->RowCount=0;
			return 0;
		}
		$this->NoResult=0;
		$this->RowCount=mysql_num_rows($this->RecSet);
		$this->ColCount=mysql_num_fields($this->RecSet);
		return $this->RowCount;
	}

	public function get_row()
	{
		if(!$this->NoResult)
			return mysql_fetch_assoc($this->RecSet);
	}

	public function get_n_row()
	{
		if (!$this->NoResult)
			return mysql_fetch_row($this->RecSet);
	}
	public function GetFieldName($ColPos)
	{
		if(mysql_errno())
			return "ERROR!";
		else if($this->ColCount>$ColPos)
			return mysql_field_name($this->RecSet,$ColPos);
		else
			return "Offset Error!";
	}

	public function GetTableName($ColPos)
	{
		if(mysql_errno())
			return "ERROR!";
		else if($this->ColCount>$ColPos)
			return mysql_field_table($this->RecSet,$ColPos);
		else
			return "Offset Error!";
	}
	public function show_sel($val,$txt,$query,$sel_val="-- Choose --")
	{
		$this->do_sel_query($query);
		$opt=$this->RowCount;
		if(($sel_val==="-- Choose --") ||($sel_val===""))
			echo "<option value=''>-- Choose --</option>";
		for($i=0;$i<$opt;$i++)
		{
			$row=$this->get_row();
			if($row[$val]==$sel_val)
				$sel="selected";
			else
				$sel="";
			echo '<option value="'.htmlspecialchars($row[$val])
			.'"'.$sel.'>'.htmlspecialchars($row[$txt]).'</option>';
		}
	}

	public function do_max_query($Query)
	{
		$this->do_sel_query($Query);
		$row= $this->get_n_row();
		//echo "Whole Row: ".$row[0].$row[1];
		if ($row[0]==null)
			return 0;
		else
			return htmlspecialchars($row[0]);
	}

	public function ShowTable($QueryString)
	{
		// Performing SQL query
		$this->do_sel_query($QueryString);
		// Printing results in HTML
		echo '<table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1">';
		$i=0;
		while ($i<mysql_num_fields($this->RecSet))
		{
			echo '<th>'.htmlspecialchars(mysql_field_name($this->RecSet,$i)).'</th>';
			$i++;
		}
		$i=0;
		while ($line = mysql_fetch_array($this->RecSet, MYSQL_ASSOC))
		{
			echo "\t<tr>\n";
			foreach ($line as $col_value)
				echo "\t\t<td>".$col_value."</td>\n";
			//$strdt=date("F j, Y, g:i:s a",$ntime);
			//echo "\t\t<td>$strdt</td>\n";
			echo "\t</tr>\n";
			$i++;
		}
		echo "</table>\n";
		//$this->do_close();
		return ($i);
	}
	public function ShowTableKiosk($QueryString)
	{
		// Connecting, selecting database
		$this->do_sel_query($QueryString);
		// Printing results in HTML
		echo '<table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1" border="1">';
		echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;border: 1px solid black;"></td></tr>';
		$i=0;
		while ($line = mysql_fetch_array($this->RecSet, MYSQL_ASSOC))
		{
			$i=0;
			foreach ($line as $col_value)
			{
				echo "\t<tr>\n";
				echo '<th  style="background-color:#FFDA91;font-weight:bold;text-align:left;border: 1px solid black;">'.htmlspecialchars(mysql_field_name($this->RecSet,$i)).'</th>';
				echo "\t\t".'<td style="border: 1px solid black;">'.$col_value."</td>\n";
				//$strdt=date("F j, Y, g:i:s a",$ntime);
				//echo "\t\t<td>$strdt</td>\n";
				echo "\t</tr>\n";
				$i++;
			}
			echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;border: 1px solid black;"></td></tr>';
		}
		echo "</table>\n";
		$this->do_close();
		return ($i);
	}
	public function do_close()
	{
		// Free resultset
		if(!$this->NoResult)
			mysql_free_result($this->RecSet);
		// Closing connection
		mysql_close($this->conn);
		//echo "<br />LastQuery: ".$LastQuery;
	}
	function EditTableV2($QueryString)
	{
		$this->do_sel_query($QueryString);
		echo "Total Records: ".mysql_num_rows($this->RecSet)."\n<br />";
		// Printing results in HTML
		echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
		.'"><table rules="all" frame="box" cellpadding="5" cellspacing="1">';
		//Update Table Data
		$col=1;
		if(isset($_REQUEST['Delete']))
		{
			$Data=new DB();
			$Query="Delete from ".mysql_field_table($this->RecSet,0)
			." Where ".mysql_field_name($this->RecSet,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
			//echo $Query;
			$Data->do_ins_query($Query);
			$this->do_sel_query($QueryString);
			//echo 'Query failed: ' . mysql_error();
		}
		if(isset($_POST[mysql_field_name($this->RecSet,$col)]))
		{
			$Data=new DB();
			while ($col<mysql_num_fields($this->RecSet))
			{
				$row=0;
				//echo $r."--".mysql_field_name($this->RecSet,$col)."--".mysql_field_table($this->RecSet,$col)
				//	.$_POST[mysql_field_name($this->RecSet,$col)][$row];
				while($row<count($_POST[mysql_field_name($this->RecSet,$col)]))
				{
					$ColName=mysql_field_name($this->RecSet,$col);
					if (empty($_POST[mysql_field_name($this->RecSet,$col)][$row]))
						$POSTVal="NULL";
					elseif((substr($ColName,0,4)=="Date") && $POSTVal!="NULL")
					{
						$POSTVal="'".date("Y-m-d",strtotime($_POST[mysql_field_name($this->RecSet,$col)][$row]))."' ";
					}
					else
						$POSTVal="'".mysql_real_escape_string($_POST[mysql_field_name($this->RecSet,$col)][$row])."' ";
					$Query="Update ".mysql_field_table($this->RecSet,$col)
					." Set ".$ColName."=".$POSTVal." "
					." Where ".mysql_field_name($this->RecSet,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->RecSet,0)][$row])." LIMIT 1;";
					//echo $Query."<br />";
					$Data->do_ins_query($Query);
					$row++;
				}
				$col++;
			}
			$this->do_sel_query($QueryString);
			//echo $Query."<br />";
		}
		//Print Collumn Names
		$i=0;
		//Print Rows
		$odd="";
		$RecCount=0;
		while ($line = mysql_fetch_array($this->RecSet, MYSQL_ASSOC))
		{
			$RecCount++;
			$odd=$odd==""?"odd":"";
			//echo '<tr class="'.$odd.'">';
			$i=0;
			foreach ($line as $col_value)
			{
				$ColName=mysql_field_name($this->RecSet,$i);
				echo '<tr><td><b>('.($i+1).') '.$ColName.'</b></td><td>';
				if($i==0)
				{
					$allow='readonly';
					echo '<a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
					.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a>&nbsp;&nbsp;';
				}
				else
					$allow='';

				$ColVal=htmlspecialchars($col_value);
				//echo "Value: ".$ColVal."<br/>";
				if((substr($ColName,0,4)=="Date") && $ColVal!="")
				{
					//echo "Value: ".strtotime($ColVal)."<br/>";
					$ColVal=date("Y-m-d",strtotime($ColVal));
					//print_r(date_get_last_errors());
				}

				echo '('.($i+1).') <input '.$allow.' type="text" size="'.(mysql_field_len($this->RecSet,$i))
				.'" name="'.mysql_field_name($this->RecSet,$i).'[]" value="'.$ColVal.'" /> </td></tr>';
				$i++;
			}
			echo '<tr><td colspan="2" style="background-color:#F4A460;"></td></tr>';
		}
		echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
		echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>';
	}
	function EditTableV3($QueryString)
	{
		$R=$this->do_sel_query($QueryString);
		echo "<h3>Total Records: ".$R."</h3>";
		if($R)
		{

			// Printing results in HTML
			echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
			.'"><table rules="all" frame="box" cellpadding="5" cellspacing="1">';
			//Update Table Data
			$col=1;
			if(isset($_REQUEST['Delete']))
			{
				$Data=new DB();
				$Query="Delete from ".mysql_field_table($this->RecSet,0)
				." Where ".mysql_field_name($this->RecSet,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
				//echo $Query;
				$Data->do_ins_query($Query);
				$this->RecSet = mysql_query($QueryString,$this->conn);
				//echo 'Query failed: ' . mysql_error();
			}
			$FieldName=mysql_field_name($this->RecSet,$col);
			if(isset($_POST[$FieldName]))
			{
				$Data=new DB();
				while ($col<mysql_num_fields($this->RecSet))
				{
					$row=0;
					//echo $r."--".mysql_field_name($this->RecSet,$col)."--".mysql_field_table($this->RecSet,$col)
					//	.$_POST[mysql_field_name($this->RecSet,$col)][$row];
					$ColName=mysql_field_name($this->RecSet,$col);
					while($row<count($_POST[mysql_field_name($this->RecSet,$col)]))
					{
						//$Loop=0;
						if (empty($_POST[mysql_field_name($this->RecSet,$col)][$row]))
						{
							$POSTVal="NULL";

						}
						elseif(substr($ColName,0,4)=="Date")
						{
							$POSTVal="STR_TO_DATE('".$_POST[mysql_field_name($this->RecSet,$col)][$row]."','%e/%c/%y')";

						}
						else
							$POSTVal="'".mysql_real_escape_string($_POST[mysql_field_name($this->RecSet,$col)][$row])."' ";
							
						//echo "Field: ".$Loop."-".$ColName.":".$POSTVal."<br/>";

						$Query="Update ".mysql_field_table($this->RecSet,$col)
						." Set `".$ColName."`=".$POSTVal." "
						." Where ".mysql_field_name($this->RecSet,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->RecSet,0)][$row])." LIMIT 1;";
						//echo $Query."<br />";
						$Data->do_ins_query($Query);
						$row++;
					}
					$col++;
					//echo "Col:".$col." ".$FieldName."<br />";
				}
				$this->do_sel_query($QueryString);
				//echo $Query."<br />";
			}
			$i=0;
			//Print Rows
			$odd="";
			$RecCount=0;
			echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;"></td></tr>';
			while ($line = mysql_fetch_array($this->RecSet, MYSQL_ASSOC))
			{
				$RecCount++;
				$odd=$odd==""?"odd":"";
				//echo '<tr class="'.$odd.'">';
				$i=0;
				foreach ($line as $col_value)
				{
					$ColName=mysql_field_name($this->RecSet,$i);
					$ColVal=htmlspecialchars($col_value);
					//echo "Value: ".$ColVal."<br/>";
					$DateFormat="";
					$DateValue="";
					if((substr($ColName,0,4)=="Date"))
					{
						if($ColVal!="")
						{
							$DateValue=date("jS F Y l",strtotime($ColVal));
							$ColVal=date("d/m/y",strtotime($ColVal));
						}
						$DateFormat=" (d/m/yy)";
						//print_r(date_get_last_errors());
					}
					echo '<tr><th style="background-color:#FFDA91;font-weight:bold;">('.($i+1).') '.$ColName.'</th><td>';
					if($i==0)
					{
						$allow='readonly';
						echo '<a href="?Delete='.htmlspecialchars($col_value).'"><img border="0" height="16" width="16" '
						.'title="Delete" alt="Delete" src="./Images/b_drop.png"/></a>&nbsp;&nbsp;';
					}
					else
						$allow='';
					echo $DateFormat.'<input '.$allow.' type="text" maxlength="'.(mysql_field_len($this->RecSet,$i)).'" size="'.((mysql_field_len($this->RecSet,$i)>40)?40:mysql_field_len($this->RecSet,$i))
					.'" name="'.mysql_field_name($this->RecSet,$i).'[]" value="'.$ColVal.'" /> '.$DateValue.' </td></tr>';
					$i++;
				}
				echo '<tr><td colspan="2" style="background-color:#F4A460;height:3px;"></td></tr>';
			}
			echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
			echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>';
		}
	}
	function EditTable($QueryString)
	{
		$this->RecSet = mysql_query($QueryString,$this->conn);
		echo "Total Records: ".mysql_num_rows($this->RecSet)."\n<br />";
		// Printing results in HTML
		echo '<form name="frmData" method="post" action="'.htmlspecialchars($_SERVER['PHP_SELF'])
		.'"><table rules="all" frame="box" width="100%" cellpadding="5" cellspacing="1">';
		//Update Table Data
		$col=1;
		if(isset($_REQUEST['Delete']))
		{
			$Data=new DB();

			$Query="Delete from ".mysql_field_table($this->RecSet,0)
			." Where ".mysql_field_name($this->RecSet,0)."=".intval($_REQUEST['Delete'])." LIMIT 1;";
			//echo $Query;
			$Data->do_ins_query($Query);
			$this->RecSet = mysql_query($QueryString,$this->conn);
			//echo 'Query failed: ' . mysql_error();
		}
		if(isset($_POST[mysql_field_name($this->RecSet,$col)]))
		{
			$Data=new DB();
			while ($col<mysql_num_fields($this->RecSet))
			{
				$row=0;
				//echo $r."--".mysql_field_name($this->RecSet,$col)."--".mysql_field_table($this->RecSet,$col)
				//	.$_POST[mysql_field_name($this->RecSet,$col)][$row];
				while($row<count($_POST[mysql_field_name($this->RecSet,$col)]))
				{
					$Query="Update ".mysql_field_table($this->RecSet,$col)
					." Set ".mysql_field_name($this->RecSet,$col)."='".mysql_real_escape_string($_POST[mysql_field_name($this->RecSet,$col)][$row])."'"
					." Where ".mysql_field_name($this->RecSet,0)."=".mysql_real_escape_string($_POST[mysql_field_name($this->RecSet,0)][$row])." LIMIT 1;";
					//echo $Query."<br />";
					$Data->do_ins_query($Query);
					$row++;
				}
				$col++;
			}
			$this->do_sel_query($QueryString);
			echo $Query."<br />";
		}
		//Print Collumn Names
		$i=0;
		echo '<tr><td colspan="4" style="background-color:#F4A460;"></td></tr><tr>';
		while ($i<mysql_num_fields($this->RecSet))
		{
			echo '<th>('.($i+1).') '.mysql_field_name($this->RecSet,$i).'</th>';
			$i++;
			if (($i%4)==0 && $i>1)
				echo '</tr><tr>';
		}
		echo '</tr><tr><td colspan="4" style="background-color:#F4A460;"></td></tr>';
		//Print Rows
		$odd="";
		$RecCount=0;
		while ($line = mysql_fetch_array($this->RecSet, MYSQL_ASSOC))
		{
			$RecCount++;
			$odd=$odd==""?"odd":"";
			echo '<tr class="'.$odd.'">';
			$i=0;
			foreach ($line as $col_value)
			{
				if (($i%4)==0 && $i>1)
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
				echo '('.($i+1).') <input '.$allow.' type="text" size="'.((mysql_field_len($this->RecSet,$i)>40)?40:mysql_field_len($this->RecSet,$i))
				.'" name="'.mysql_field_name($this->RecSet,$i).'[]" value="'.htmlspecialchars($col_value).'" /> </td>';
				$i++;
			}
			echo '</tr><tr><td colspan="4" style="background-color:#F4A460;"></td></tr>';
		}
		echo '<tr><td colspan="'.$i.'" style="text-align:right;"><input type="hidden" name="RecFrom" value="'.intval($_REQUEST['RecFrom']).'"/>';
		echo '&nbsp;&nbsp;&nbsp;<input style="width:80px;" type="submit" value="Save" /></td></tr></table></form>';
	}
	public function __sleep()
	{
		$this->do_close();
		return array('conn','result','Debug');
	}
	public function __wakeup()
	{
		$this->do_connect();
	}

}
?>
