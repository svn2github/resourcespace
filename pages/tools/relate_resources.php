<?php
require dirname(__FILE__)."/../../include/db.php";
require dirname(__FILE__)."/../../include/general.php";
require dirname(__FILE__)."/../../include/resource_functions.php";

set_time_limit(0);


if (defined('STDIN')) {
  if (empty($argv[1])) {exit("Resource Refs missing");}
	$rlist= explode(",",$argv[1]);
} else { 
 	if (getval("resource_refs","")==="") {exit("Resource Refs missing");}
	$rlist=getval("resource_refs","");
}
#Cleanse Input
foreach($rlist as $k => $v) {
	if(!is_numeric($v)){unset($rlist[$k]);continue;}
	if(sql_value("SELECT count(ref) as value FROM resource WHERE ref='".$v."'",0)!=1){unset($rlist[$k]);continue;}
}
for ($n=0;$n<count($rlist);$n++)
	{ 
	for ($m=0;$m<count($rlist);$m++)
		{
		if ($rlist[$n]!=$rlist[$m]) # Don't relate a resource to itself
			{ 
			if(sql_value("SELECT count(ref) as value FROM resource WHERE ref='".$v."'",0)!=1)
				{
				sql_query("INSERT INTO resource_related (resource,related) VALUES ('" . $rlist[$n] . "','" . $rlist[$m] . "')");
				}
			}
		}
	}