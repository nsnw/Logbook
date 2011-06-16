<?php

include('config.inc.php');
include('logbook.inc.php');
include('logui.inc.php');

$db = db_create();
generateHeader();

$convert_table = array(
	'COL_MODE' => 'Mode',
	'COL_CALL' => 'Callsign',
	'COL_NAME' => 'Name',
	'COL_GRIDSQUARE' => 'Locator',
	'COL_COMMENT' => 'Remark',
);

// Get variables from GET string, and sanitise them
$start = preg_replace("/[^0-9]/", "", $_GET['start']);
$show = preg_replace("/[^0-9]/", "", $_GET['show']);

if($_GET['orderby'])
{
	$order_by = preg_replace("/[^\w]/", "", $_GET['orderby']);
	if($order_by == "clear")
	{
		setcookie("orderby");
		$order_by = "COL_TIME_ON";
	}
	else
	{
		setcookie("orderby", $order_by);
	}
}
elseif($_COOKIE['orderby'])
{
	$order_by = preg_replace("/[^\w]/", "", $_COOKIE['orderby']);
}
else
{
	$order_by = "COL_TIME_ON";
}

if($_GET['orderdir'])
{
	$order_dir = preg_replace("/[^\w]/", "", $_GET['orderdir']);
	if($order_dir == "clear")
	{
		setcookie("orderdir");
		$order_dir = "DESC";
	}
	else
	{
		setcookie("orderdir", $order_dir);
	}
}
elseif($_COOKIE['orderdir'])
{
	$order_dir = preg_replace("/[^\w]/", "", $_COOKIE['orderdir']);
}
else
{
	$order_dir = "DESC";
}

if($_GET['filterby'])
{
	$filter_by = preg_replace("/[^\w]/", "", $_GET['filterby']);
	if($filter_by == "clear")
	{
		setcookie("filterby");
		$filter_by = "";
	}
	else
	{
		setcookie("filterby", $filter_by);
	}
}
elseif($_COOKIE['filterby'])
{
	$filter_by = preg_replace("/[^\w]/", "", $_COOKIE['filterby']);
}

if($_GET['filteron'])
{
	$filter_on = preg_replace("/[^\w]/", "", $_GET['filteron']);
	if($filter_on == "clear")
	{
		setcookie("filteron");
		$filter_on = "";
	}
	else
	{
		setcookie("filteron", $filter_on);
	}
}
elseif($_COOKIE['filteron'])
{
	$filter_on = preg_replace("/[^\w]/", "", $_COOKIE['filteron']);
}

$search = array();

if($_GET['searchfor'])
{
	$searchfor = preg_replace("/[^\w]/", "", $_GET['searchfor']);
	$searchby = preg_replace("/[^\w]/", "", $_GET['searchby']);

	if($searchfor == "clear")
	{
		setcookie("searchfor");
		setcookie("searchby");
		$searchfor = "";
		$searchby = "";
	}
	else
	{
		setcookie("searchfor", $searchfor);
		setcookie("searchby", $searchby);
		array_push($search, array($searchby => $searchfor));
	}
}
elseif($_COOKIE['searchfor'])
{
	$searchfor = preg_replace("/[^\w]/", "", $_COOKIE['searchfor']);
	$searchby = preg_replace("/[^\w]/", "", $_COOKIE['searchby']);
	array_push($search, array($searchby => $searchfor));
}

if($filter_on)
{
	array_push($search, array($filter_on => $filter_by));

}

if(!$start)
{
	$start = "0";
}

if(!$show)
{
	$show = "20";
}

$end = $start + $show;

$current = "start=".$start."&show=".$show;

$q = getQSOList($search, $order_by, $order_dir, $start.",".$show);
$qsoCount = countQSO();

if($show >= $q['count'])
{
	$display = $q['count'];
}
else
{
	$display = $show;
}

?>
<div id="toplinks">
<div class="infobox">
<b>Logbook</b>
</div>
<div class="searchbox">
<form method="GET" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<input type="hidden" name="start" value="<?php echo $start; ?>">
<input type="hidden" name="show" value="<?php echo $start; ?>">
Search
<input type="text" name="searchfor" size="25">
<select name="searchby">
	<option value="COL_CALL">Callsign</option>
	<option value="COL_GRIDSQUARE">Locator</option>
	<option value="COL_NAME">Name</option>
	<option value="COL_COMMENT">Remark</option>
	<option value="COL_MODE">Mode</option>
</select>
<input type="submit" value="Go">
</form>
</div>
<div class="buttonbox">
<a href="<?php echo $_SERVER['SCRIPT_URI']."?searchfor=clear&searchby=clear&filterby=clear&filteron=clear&orderby=clear&orderdir=clear"; ?>">Show all</a> 
<?php

if(($start-$show) < 0)
{
	$start = 0;
}
else
{
	?>
	<a class="buttons" href="<?php echo $_SERVER['SCRIPT_URI']."?start=0&show=".$show; ?>">&#x21E4;</a>
	<a class="buttons" href="<?php echo $_SERVER['SCRIPT_URI']."?start=".($start-$show)."&show=".$show; ?>">&#x2190;</a> 
	<?php
}

if($start < ($q['count'] - $show))
{
	?>
	<a class="buttons" href="<?php echo $_SERVER['SCRIPT_URI']."?start=".($start+$show)."&show=".$show; ?>">&#x2192;</a>
	<a class="buttons" href="<?php echo $_SERVER['SCRIPT_URI']."?start=".($q['count']-$show)."&show=".$show; ?>">&#x21E5;</a>
	<?php
}
	
?>
&nbsp;
Starting at <b><?php echo $start+1; ?></b>, showing <b><?php echo $display; ?></b> records, <b><?php echo $q['count']; ?></b> total.
<?php
if($searchby)
{
	?>
	Searching on <b><?php echo $convert_table[$searchby]; ?></b> for <b><?php echo $searchfor; ?></b> <a class="cancel" href="<?php echo $_SERVER['SCRIPT_URI']."?searchby=clear&searchfor=clear"; ?>">&#x20E0;</a>.
	<?php
}

if($filter_on)
{
	?>
	Filtering where <b><?php echo $convert_table[$filter_on]; ?></b> is <b><?php echo $filter_by; ?></b> <a class="cancel" href="<?php echo $_SERVER['SCRIPT_URI']."?filterby=clear&filteron=clear"; ?>">&#x20E0;</a>.
	<?php
}
?>
</div>
</div>

<table id="qsotable" width="99%" cellspacing="0">
<tr>
	<th class="time">Date / Time&nbsp;
		<?
		if($order_by == "COL_TIME_ON" && $order_dir == "ASC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_TIME_ON&orderdir=ASC"; ?>">&#x21E7;</a>
		<?
		if($order_by == "COL_TIME_ON" && $order_dir == "DESC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_TIME_ON&orderdir=DESC"; ?>">&#x21E9;</a>
	</th>
	<th class="callsign">Callsign&nbsp;
		<?
		if($order_by == "COL_CALL" && $order_dir == "ASC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_CALL&orderdir=ASC"; ?>">&#x21E7;</a>
		<?
		if($order_by == "COL_CALL" && $order_dir == "DESC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_CALL&orderdir=DESC"; ?>">&#x21E9;</a>
	</th>
	<th class="name">Name&nbsp;
		<?
		if($order_by == "COL_NAME" && $order_dir == "ASC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_NAME&orderdir=ASC"; ?>">&#x21E7;</a>
		<?
		if($order_by == "COL_NAME" && $order_dir == "DESC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_NAME&orderdir=DESC"; ?>">&#x21E9;</a>
	</th>
	<th class="locator">Locator&nbsp;
		<?
		if($order_by == "COL_GRIDSQUARE" && $order_dir == "ASC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_GRIDSQUARE&orderdir=ASC"; ?>">&#x21E7;</a>
		<?
		if($order_by == "COL_GRIDSQUARE" && $order_dir == "DESC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_GRIDSQUARE&orderdir=DESC"; ?>">&#x21E9;</a>
	</th>
	<th class="frequency">Frequency&nbsp;
		<?
		if($order_by == "COL_FREQ" && $order_dir == "ASC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_FREQ&orderdir=ASC"; ?>">&#x21E7;</a>
		<?
		if($order_by == "COL_FREQ" && $order_dir == "DESC") { $class = "selected"; } else { $class = "not-selected"; }
		?>
		<a class="<?php echo $class; ?>" href="<?php echo $_SERVER['SCRIPT_URI']."?".$current."&orderby=COL_FREQ&orderdir=DESC"; ?>">&#x21E9;</a>
	</th>
	<th class="mode">Mode</th>
	<th class="remark">Remarks</th>
</tr>
<?php

$i = 0;

foreach ($q['qso'] as $qso)
{
	if($i % 2)
	{
		$rowclass = "odd";
	}
	else
	{
		$rowclass = "even";
	}
	?>
		<tr class="<?php echo $rowclass; ?>">
			<td class="time"><? echo $qso['COL_TIME_ON']; ?></td>
			<td class="callsign"><a target="_top" class="callsign" href="http://qrz.com/db/<? echo $qso['COL_CALL']; ?>"><? echo str_replace("0", "&Oslash;", $qso['COL_CALL']); ?></a> <a class="select" href="<?php echo $_SERVER['SCRIPT_URI']."?searchby=COL_CALL&searchfor=".$qso['COL_CALL']; ?>">&#x21B5;</a><br>
			<span class="small"><? echo $qso['COL_COUNTRY']; ?></span>
			</td>
			<td class="name"><b><? echo $qso['COL_NAME']; ?></b><br>
			<span class="small"><? echo $qso['COL_QTH']; ?></span>
			</td>
			<td class="locator"><? echo $qso['COL_GRIDSQUARE']; ?></td>
			<td class="frequency"><? printf("%f", $qso['COL_FREQ']/1000000); ?> <span class="small">(<? echo $qso['COL_BAND']; ?>)</span></td>
			<td class="mode"><a class="mode" href="<?php echo $_SERVER['SCRIPT_URI']."?"."filteron=COL_MODE&filterby=".$qso['COL_MODE']; ?>"><?php echo $qso['COL_MODE']; ?></a></td>
			<td class="remark"><? echo $qso['COL_COMMENT']; ?></td>
		</tr>
	<?php
	$i++;
}
?>
</table>
<div id="footer">Logbook v1.0 - &copy; 2011 Andy Smith <a href="http://m0vkg.org.uk/">M&Oslash;VKG</a>.</div>
