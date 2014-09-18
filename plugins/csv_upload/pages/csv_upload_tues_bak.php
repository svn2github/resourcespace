<?php
/**
 * CSV upload * 
 * @package ResourceSpace
 */

include dirname(__FILE__)."/../../../include/db.php";
include dirname(__FILE__)."/../../../include/authenticate.php";
include dirname(__FILE__)."/../../../include/general.php";
include dirname(__FILE__)."/../../../include/header.php";

?><div class="BasicsBox"> 
<?php

if (!checkperm("c"))
	{	
	echo $lang['csv_upload_error_no_permission'];	
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
	}
?><h1><?php echo $lang['csv_upload_nav_link']; ?></h1>
<?php
	
# contants
	
$fd="user_{$userref}_uploaded_meta";			// file descriptor for uploaded file					// TODO: push these to a config file?
$override_fields=array("status","access");		// user can set if empty or override these fields

# ----- we do not have a successfully submitted csv, so show the upload form an exit -----

if (!isset($_FILES[$fd]) || $_FILES[$fd]['error']>0)
{	
?><form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data">		
	<div class="Question">
		<label for="<?php echo $fd; ?>">File</label>
		<input type="file" id="<?php echo $fd; ?>" name="<?php echo $fd; ?>" onchange="if(this.value==null || this.value=='') { jQuery('.file_selected').hide(); } else { jQuery('.file_selected').show(); } ">	
	</div>
	<?php foreach ($override_fields as $s)
	{	
	?><div class="file_selected Question" style="display: none;">
		<label for="<?php echo $s; ?>"><?php echo $lang[$s]?></label>				
		<select name="<?php echo $s; ?>" id="<?php echo $s; ?>" onchange="if (this.options[this.selectedIndex].value=='default') { jQuery('#<?php echo $s; ?>_action').hide(); } else { jQuery('#<?php echo $s; ?>_action').show(); }" class="stdwidth">
			<option value="default">Default</option><?php	// TODO localise this
$i=0;	
while (isset($lang[$s . $i]))
{
	?><option value="<?php echo $i; ?>"><?php echo $lang[$s . $i]; ?></option>
<?php
	$i++;
}
?>				</select>
		<select id="<?php echo $s; ?>_action" name="<?php echo $s; ?>_action" style="display: none;" class="stdwidth" >					
			<option value="1">When unspecified</option>		<?php // TODO localise this ?>
			<option value="2">Override</option>				<?php // TODO localise this ?>
		</select>			
		<div class="clearerleft"></div>		
	</div>
	<?php
	}
?>		
	<label for="submit" class="file_selected" style="display: none;"></label>
	<input type="submit" id="submit" value="Next" class="file_selected" style="display: none;">  <?php // TODO localise this ?>
</form><?php
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
}

?><form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
	<input type="submit" value="Back">
</form><br />

<?php  // TODO remove
	echo "<pre>debug:";
	print_r ($_POST);
	echo "</pre>";	
?>

<b><?php echo $_FILES[$fd]['name']; ?></b><br /><br />
<table class="InfoTable">
<?php

# ----- Start of populating system arrays for verification of data -----

$resource_table_columns=array();				// list of the columns in the resource_table
foreach (sql_query("select column_name from information_schema.columns where table_name='resource' and table_schema='{$mysql_db}'") as $s) $resource_table_columns[$s['column_name']]=null;

$resource_type_field_table_columns=array();		





// all of the names in resource_table_field (as key) and array of their respective options (null if free format) as value





$resource_type_columns=array();					// associative array where key is the resource_type, and value is array of field names.
$resource_type_columns_required=array();		// associative array where key is the resource_type, and value is array of field names that are required.

$resource_types=array();						// a list of all of the resource_types in the system, i.e. 0 through to n.




echo "<pre>";
print_r ($results);
exit;



$results=sql_query("select name,ref from resource_type");
foreach ($results as $result) $resource_types[$result['ref']]=$result['name'];

$results=sql_query("select name, resource_type, options, required from resource_type_field where name is not null and name <> ''");
foreach ($results as $result) 
{
	foreach (array_keys($resource_types) as $resource_type)
	{
		if ($result['resource_type']==$resource_type || $result['resource_type']==0)
		{
			if (!isset($resource_type_columns[$resource_type])) 
			{
				$resource_type_columns[$resource_type]=array();
				$resource_type_columns_required[$resource_type]=array();				
			}
			array_push ($resource_type_columns[$resource_type],$result['name']);
			if ($result['required'])
			{
				array_push ($resource_type_columns_required[$resource_type],$result['name']);
			}
		}
	}
	$resource_type_field_table_columns[$result['name']]=$result['required'] ? explode(",",$result['options']) : null;
}

# ----- end of array population -----

$file=fopen($_FILES[$fd]['tmp_name'], 'r');
$line_count=0;

if (($header = fgetcsv($file))==false)
{
	// todo nicely exit if there isn't a header	
	exit;
}

# ----- start of header row checks -----

$missing_resource_type_columns_required=array();
foreach (array_keys($resource_type_columns_required) as $resource_type)
{
	foreach ($resource_type_columns_required[$resource_type] as $resource_name)
	{
		$found=array_search($resource_name, $header);
		if ($found===false)
		{			
			if (!isset($missing_resource_type_columns_required[$resource_type])) $missing_resource_type_columns_required[$resource_type]=array();
			array_push ($missing_resource_type_columns_required[$resource_type],$resource_name);
		}
	}
}

?><tr><td>Required field headers</td><td>
<?php
if (count($missing_resource_type_columns_required)>0)
{
?><b>WARINING required field headers missing for these resource types:</b><br /><br />
<?php
		foreach (array_keys($missing_resource_type_columns_required) as $resource_type)
		{
			echo "<b>{$resource_type}:{$resource_types[$resource_type]}</b> (";	
			echo implode(",",$missing_resource_type_columns_required[$resource_type]);
			echo ")<br />";					
		}
?><br />If the CSV file contains these resource types then the import will be unsuccessful.
<?php
}
else
{
?>OK
<?php
}
?></td></tr>
<?php

$fields_not_found=array();
$ignore_field_indecies=array();
$i=0;

foreach ($header as $field)
{		
	if (!array_key_exists($field, $resource_table_columns) && !array_key_exists($field, $resource_type_field_table_columns)) 
	{
		array_push($fields_not_found,$field);
		array_push($ignore_field_indecies,$i);
	}
	$i++;
}

?><tr><td>Non-mapped fields</td><td>
<?php 
if (count($fields_not_found)>0)
{
?><b>WARNING extra fields found:</b><br /><br />
<?php
	echo implode(",",$fields_not_found);	
?><br /><br />These fields will be ignored.
<?php
}
else
{
?>OK, none found.
<?php
}
?>
<tr>
<td>resource_type field check</td>
<td><?php
if (($resource_type_index=array_search("resource_type",$header))===false)
{
?><b>ERROR not found</b></td>
</tr>
</table>
<?php
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
}
else
{
?>OK, found (column <?php echo $resource_type_index+1; ?>)</td>
</tr>
<tr>
	<td>Data validation</td>
	<td><?php
}

// TODO: check for dup column names, if so bomb out


# ----- end of header row checks, process each of the rows -----

$valid = true;

$line_count=0;


$resource_table_data=array();
$resource_data_table_data=array();
$errors=array();

while (($line=fgetcsv($file))!==false && count($errors<100))	// TODO: change this to a config
{
	$line_count++;
	$file_line_count=$line_count+1;
	
	$resource_table_data[$line_count]=array();
	$resource_data_table_data[$line_count]=array();	
	
	if (count($line)!=count($header))	// check that the current row has the correct number of columns
	{
		array_push ($errors,"Incorrect number of columns(" . count($line) . ") found on line " . $file_line_count . " (should be " . count($header) . ")");
		continue;		
	}	
	
	$resource_type=$line[$resource_type_index];
	
	if (!array_key_exists($resource_type,$resource_types))		// check to see if resource_type is valid
	{
		array_push ($errors,"resource_type(" . $resource_type . ") not found (allowable values are " . implode (",",array_keys($resource_types)) .") found on line " . $file_line_count);
		continue;
	}
	
	
	
	for ($i=0; $i<count($line); $i++)
	{
		if (array_search($i, $ignore_field_indecies)!==false)		// skip cell if in ignore column list
		{
			continue;
		}
		$cell_name=$header[$i];	// get column name for current cell
		$cell_value=$line[$i];	// get value for current cell
		
		// check that allowable values are ok
		
		
		
		
		
		
		
		
		// check that mandatory columns set, and lookups ok
		
		
		
		

		//echo $cell_name . "=" . $cell_value . "<br />";		
	}
	
	
	
}

if ($line_count==0)		// add an error if there are no lines of data to process
{
	array_push($errors,"No lines of data found in file");		
}

if (count($errors)>0)
{
?>
<b><?php echo count($errors); ?> ERROR<?php if (count($errors)>1) {?>S<?php } ?> found:</b><br />
<pre>
<?php
foreach ($errors as $errors)
{
	echo $errors . PHP_EOL;
}
?>
</pre><?php
}



fclose($file);


?>
	</td>
</tr>
</table>

</div> <!-- end of BasicsBox -->
<?php


	

include dirname(__FILE__)."/../../../include/footer.php";

