<?php

include(dirname(__FILE__)."/../../include/db.php");
include(dirname(__FILE__)."/../../include/general.php");
include(dirname(__FILE__)."/../../include/image_processing.php");
include(dirname(__FILE__)."/../../include/resource_functions.php");
include(dirname(__FILE__)."/../../include/collections_functions.php");
include(dirname(__FILE__)."/../../include/search_functions.php");
$api=true;

include(dirname(__FILE__)."/../../include/authenticate.php");

// required: check that this plugin is available to the user
if (!in_array("api_upload",$plugins)){die("no access");}


if (isset($_FILES['userfile'])){
	
	
	
	
 $resource_type=getvalescaped("resource_type",1,true);

 // work out status 
 if (!(checkperm("c") || checkperm("d"))){ die("No upload permissions\n");}
 if (checkperm("XU".$resource_type)){ die("Upload to this Resource Type not allowed.\n");}
 
 $archive=getvalescaped("archive","");
 if ($archive!="" && checkperm('e'.$archive)){
	$status=$archive;
 }
 else if (checkperm("c")) {$status = 0;} # Else, set status to Active - if the user has the required permission.
 else if (checkperm("d")) {$status = -2;} # Else, set status to Pending Submission.  
 
 // check required fields
 $required_fields=sql_array("select ref value from resource_type_field where required=1 and (resource_type='$resource_type' or resource_type='0')");

 $missing_fields=false;
 foreach ($required_fields as $required_field){
	 $value=getvalescaped("field".$required_field,"");
	 if ($value==''){
		 $fieldname=i18n_get_translated(sql_value("select title value from resource_type_field where ref='$required_field'",""));
		 $options=sql_value("select options value from resource_type_field where ref='$required_field'","");
		 $type=sql_value("select type value from resource_type_field where ref='$required_field'","");
		 
		 
		 if ($options!="" && ($type==3 || $type==2)){$optionstring="Allowed Values: ".ltrim(implode("\n",explode(",",$options)),",")."\n";} else {$optionstring="";}
		 echo ("$fieldname is required. Use field$required_field=[string] as a parameter. $optionstring\n");$missing_fields=true;
	 } 
 } 
 if ($missing_fields){die();}
	
 // create resource
 $ref=create_resource(getval("resourcetype",1),$status,$userref);
 
 // set required fields
  foreach ($required_fields as $required_field){
	 $value=getvalescaped("field".$required_field,"");

	 update_field($ref,$required_field,$value);
 } 
 
 
 $path_parts=pathinfo($_FILES['userfile']['name']);
 $extension=strtolower($path_parts['extension']);  
 $filepath=get_resource_path($ref,true,"",true,$extension);
 $collection=getvalescaped('collection',"",true);

 
 $result=move_uploaded_file($_FILES['userfile']['tmp_name'], $filepath);
 $wait=sql_query("update resource set file_extension='$extension',preview_extension='jpg',file_modified=now() ,has_image=0 where ref='$ref'");
 # Store original filename in field, if set
 global $filename_field;
 if (isset($filename_field))
    {
    $wait=update_field($ref,$filename_field,$_FILES['userfile']['name']);	
    }

 // extract metadata
 $wait=extract_exif_comment($ref,$extension);
 $resource=get_resource_data($ref);
 //create previews
 $wait=create_previews($ref,false,$extension);
 // add resource to collection
 if ($collection!=""){
     add_resource_to_collection($ref,$collection);
 }

 $results=do_search("!list$ref","","relevance",$status);        
 
 
 $modified_result=hook("modifyapisearchresult");
 if ($modified_result){
	$results=$modified_result;
 }
   
 // this function in api_core   
 $results=refine_api_resource_results($results);  
        
 // return refs
 header('Content-type: application/json');
 if ($collection!=""){
   $result = array('collection' => $collection, 'resource' => $results);
 } else {
   $result = array('resource' => $results);
 }
        
 echo json_encode($result); // echo json without headers by default

}

 else {echo "no file. Please post via curl with two posts: 'userfile' and 'key' as in <a href=".$baseurl."/plugins/api_upload/readme.txt>ReadMe</a>";}



