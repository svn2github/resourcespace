<?php

# Quick script to check valid HTML
include "../../include/db.php";
include "../../include/authenticate.php";
include "../../include/general.php";
include "../../include/resource_functions.php";

echo "<pre>";

$text=getval("text","");
if($text == ""){exit;}

$html=trim($text);
$result=validate_html($html);
if ($result===true)
    {
    echo "OK\n";
    }
else
    {
    echo "FAIL - $result \n";
    }
echo "</pre>";
?>