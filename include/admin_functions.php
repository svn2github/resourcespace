<?php

function update_resource_type_field_order($neworder)
	{	
	if (!is_array($neworder)) {
		exit ("Error: invalid input to update_resource_type_field_order function.");
	}

	$updatesql= "update resource_type_field set order_by=(case ref ";
	$counter = 10;
	foreach ($neworder as $restype){
		$updatesql.= "when '$restype' then '$counter' ";
		$counter = $counter + 10;
	}
	$updatesql.= "else order_by END)";
	sql_query($updatesql);
	}
	
function update_resource_type_order($neworder)
	{	
	if (!is_array($neworder)) {
		exit ("Error: invalid input to update_resource_type_field_order function.");
	}

	$updatesql= "update resource_type set order_by=(case ref ";
	$counter = 10;
	foreach ($neworder as $restype){
		$updatesql.= "when '$restype' then '$counter' ";
		$counter = $counter + 10;
	}
	$updatesql.= "else order_by END)";
	sql_query($updatesql);
	}
	