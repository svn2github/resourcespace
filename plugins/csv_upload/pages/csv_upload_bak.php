<?php
/**
 * CSV upload * 
 * @package ResourceSpace
 */

include dirname(__FILE__)."/../../../include/db.php";
include dirname(__FILE__)."/../../../include/authenticate.php";
include dirname(__FILE__)."/../../../include/general.php";
include dirname(__FILE__)."/../../../include/header.php";

if (!checkperm("c"))
	{	
	echo $lang['csv_upload_error_no_permission'];	
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
	}
?><h1><?php echo $lang['csv_upload_nav_link']; ?></h1>
<?php
	
$fd="user_{$userref}_uploaded_meta";

# ----- we do not have a successfully submitted csv, so show the upload form an exit -----

if (!isset($_FILES[$fd]) || $_FILES[$fd]['error']>0)
	{	
?><form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data">	
	<input type="file" name="<?php echo $fd; ?>"><br /><br />
	<input type="submit" value="Next">
</form><?php
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
	}

# ----- we have an uploaded csv at this point so make a csv array -----

$csv=array();
$file=fopen($_FILES[$fd]['tmp_name'], 'r');
while (($line = fgetcsv($file))!==false) 
{
	array_push($csv,$line);
}
fclose($file);
unset($file);
	
# ----- create an associative array containing the database column names and possible options -----

$sql=<<<EOT
select x.table_name, x.resource_type, x.column_name, x.options from
(select "resource" as table_name, 0 as resource_type, column_name as column_name, null as options from information_schema.columns where table_name="resource" and table_schema="{$mysql_db}"
union 
select "resource_type_field" as table_name, resource_type, name as column_name, options from resource_type_field where name is not null) x
EOT;
$results=sql_query($sql);
unset ($sql);

$schema=array();
foreach ($results as $result)
{
	if (!isset($schema[$result['table_name']])) $schema[$result['table_name']]=array();	
	if (!isset($schema[$result['table_name']][$result['resource_type']])) $schema[$result['table_name']][$result['resource_type']]=array();
	$schema[$result['table_name']][$result['resource_type']][$result['column_name']]=empty($result['options']) ? null : explode(",",$result['options']);
}
unset ($results);	

# ----- include the checks to be called by the check() function -----

include dirname(__FILE__)."/csv_check_functions.php";

# ----- function used to run checks, dynamically calls underlying function which should return array of errors.  Displays output as table row -----

function check($name,&$csv,&$schema)
{
	global $lang;
	$errors=$name($csv,$schema);
	$ok=count($errors)==0;
?><tr>
<td><?php echo $lang["check_{$name}"]; ?></td>
<td><?php echo $ok ? "<b>OK</b>" : "<b>FAIL</b><br />" . implode("<br />",$errors); ?></td>
</tr>
<?php	
	return $ok;
}

# ----- we are now ready to run some tests, display output in a table -----

?><form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
	<input type="submit" value="Back">
</form><br />
<b><?php echo $_FILES[$fd]['name']; ?></b><br /><br />
<table class="InfoTable">
<?php
	
$ok=
	check("line_count",$csv,$schema)
	&&
	check("header_names",$csv,$schema)
?>
</table><br />
<?php

# ----- if all of the tests passed then display button to run import sql script -----

if ($ok)
{	
	?><input type="button" value="Run import" onclick="todo" />
<?php		
}

include dirname(__FILE__)."/../../../include/footer.php";

