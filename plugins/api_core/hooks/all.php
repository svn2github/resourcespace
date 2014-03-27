<?php


function refine_api_resource_results($results){
	
	global $api_search_exclude_fields,$lang;
	
	// Prettify field titles
	if (getval("prettyfieldnames","")!=""){
		$fields=sql_array("select ref value from resource_type_field");
		$fields=sql_query("select ref, title from resource_type_field where  ref in ('" . join("','",$fields) . "') order by order_by");

		$field_name=array();

		foreach ($fields as $field){
			$field_name[$field['ref']]=i18n_get_translated($field['title']);

		}
		for ($n=0;$n<count($results);$n++){
			foreach ($results[$n] as $key=>$value){
				if (substr($key,0,5)=="field"){
					$field=str_replace("field","",$key);

					$results[$n][$field_name[$field]]=$results[$n][$key];
					unset ($results[$n][$key]);
					}
			}
		}
	}

	if (getval("contributedby","true")!="false"){
		$users=get_users();
		$n=0;
		$users_array=array();
		foreach($users as $user){
			$users_array[$user['ref']]=$user['fullname'];
		}
		
		for ($n=0;$n<count($results);$n++){
			if ($results[$n]['created_by']>0 && isset($users_array[$results[$n]['created_by']])){
			$results[$n][$lang['contributedby']]=$users_array[$results[$n]['created_by']];
			}
		}
		
	}

	// Exclude fields (clean up the output)
	if ($api_search_exclude_fields!=""){
		$newresult=array();
		$api_search_exclude_fields=explode(",",$api_search_exclude_fields);
		$api_search_exclude_fields=trim_array($api_search_exclude_fields);
		$x=0;
		for ($n=0;$n<count($results);$n++){
			foreach ($results[$n] as $key=>$value){
				if (!in_array($key,$api_search_exclude_fields)){
					$newresult[$x][$key]=$value;
				}
			}
			$x++;	
		}
		$results=$newresult;
	}
return $results;
}
