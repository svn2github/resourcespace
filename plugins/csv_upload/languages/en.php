<?php
# English
# Language File for the csv_upload Plugin
# -------
# Note: when translating to a new language, preserve the original case if possible.

$lang["csv_upload_nav_link"]="CSV upload";
$lang["csv_upload_intro"]="<p>This plugin allows you to create resources by uploading a CSV file. The format of the CSV is important and must follow a defined format.</p>";

$lang["csv_upload_condition1"]="<li>The CSV must have a header row</li>";
$lang["csv_upload_condition2"]="<li>To create resources of different resource types there must be a column named 'resource_type'</li>";
$lang["csv_upload_condition3"]="<li>To assign different archive states to the resources there must be a column named 'status'</li>";
$lang["csv_upload_condition4"]="<li>To assign different access levels (open,restricted, confidential) states to the resources there must be a column named 'access'</li>";
$lang["csv_upload_condition5"]="<li>To be able to upload resource files later using batch replace functionality, each resource there should be a column named 'Original filename' and each file should have a unique filename</li>";
$lang["csv_upload_condition6"]="<li>All other column headers must correspond to the full name of a resource metadata field</li>";
$lang["csv_upload_condition7"]="<li>All mandatory fields for the created resource types must be present</li>";

$lang["csv_upload_error_no_permission"]="You do not have the correct permissions to upload a CSV file";


$lang["check_line_count"]="At least two rows found in CSV file";
$lang["check_header_names"]="Header names match those in the meta fields";

