<?php
include "../include/db.php";
include "../include/general.php";
include "../include/resource_functions.php"; //for checking scr access
include "../include/search_functions.php";
include "../include/collections_functions.php";

# External access support (authenticate only if no key provided, or if invalid access key provided)
$s=explode(" ",getvalescaped("search",""));
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(str_replace("!collection","",$s[0]),$k))) {include "../include/authenticate.php";}

 # Disable info box for external users.
if ($k!="") {$infobox=false;}
else {
       #note current user collection for add/remove links
       $user=get_user($userref);$usercollection=$user['current_collection'];
}
# Disable checkboxes for external users.
if ($k!="") {$use_checkboxes_for_selection=false;}

$search=getvalescaped("search","");

hook("moresearchcriteria");

# create a display_fields array with information needed for detailed field highlighting
$df=array();


$all_field_info=get_fields_for_search_display(array_unique(array_merge($sort_fields,$thumbs_display_fields,$list_display_fields,$xl_thumbs_display_fields,$small_thumbs_display_fields)));

# get display and normalize display specific variables
$display=getvalescaped("display",$default_display);setcookie("display",$display, 0, '', '', false, true);

if ($display=="thumbs"){ 
	$display_fields	= $thumbs_display_fields;  
	if (isset($search_result_title_height)) { $result_title_height = $search_result_title_height; }
	$results_title_trim = $search_results_title_trim;
	$results_title_wordwrap	= $search_results_title_wordwrap;
	}
	
if ($display=="list"){ 
	$display_fields	= $list_display_fields; 
	$results_title_trim = $list_search_results_title_trim;
	}
	
if ($display=="smallthumbs"){ 
	$display_fields	= $small_thumbs_display_fields; 
	if (isset($small_search_result_title_height)) { $result_title_height = $small_search_result_title_height; }
	$results_title_trim = $small_search_results_title_trim;
	$results_title_wordwrap = $small_search_results_title_wordwrap;
	}
if ($display=="xlthumbs"){ 
	$display_fields = $xl_thumbs_display_fields;
	if (isset($xl_search_result_title_height)) { $result_title_height = $xl_search_result_title_height; }
	$results_title_trim = $xl_search_results_title_trim;
	$results_title_wordwrap = $xl_search_results_title_wordwrap;
	}

$n=0;
foreach ($display_fields as $display_field)
	{
	# Find field in selected list
	for ($m=0;$m<count($all_field_info);$m++)
		{
		if ($all_field_info[$m]["ref"]==$display_field)
			{
			$field_info=$all_field_info[$m];
			$df[$n]['ref']=$display_field;
			$df[$n]['type']=$field_info['type'];
			$df[$n]['indexed']=$field_info['keywords_index'];
			$df[$n]['partial_index']=$field_info['partial_index'];
			$df[$n]['name']=$field_info['name'];
			$df[$n]['title']=$field_info['title'];
			$df[$n]['value_filter']=$field_info['value_filter'];
			$n++;
			}
		}
	}
$n=0;	
$df_add=hook("displayfieldsadd");
# create a sort_fields array with information for sort fields
$n=0;
$sf=array();
foreach ($sort_fields as $sort_field)
	{
	# Find field in selected list
	for ($m=0;$m<count($all_field_info);$m++)
		{
		if ($all_field_info[$m]["ref"]==$sort_field)
			{ 
			$field_info=$all_field_info[$m];
			$sf[$n]['ref']=$sort_field;
			$sf[$n]['title']=$field_info['title'];
			$n++;
			}
		}
	}
$n=0;	

# Append extra search parameters from the quick search.
if (!$config_search_for_number || !is_numeric($search)) # Don't do this when the search query is numeric, as users typically expect numeric searches to return the resource with that ID and ignore country/date filters.
	{
	// For the simple search fields, collect from the GET and POST requests and assemble into the search string.
	reset ($_POST);reset($_GET);

	foreach (array_merge($_GET, $_POST) as $key=>$value)
		{
		if (is_string($value))
		  {
		  $value=trim($value);
		  }
		if ($value!="" && substr($key,0,6)=="field_")
			{
			if ((strpos($key,"_year")!==false)||(strpos($key,"_month")!==false)||(strpos($key,"_day")!==false))
				{
				# Date field
				
				# Construct the date from the supplied dropdown values
				$key_part=substr($key,0, strrpos($key, "_"));
				$field=substr($key_part,6);
                $value="";
				if (strpos($search, $field.":")===false) 
				    {
                $key_year=$key_part."_year";
				$value_year=getvalescaped($key_year,"");
				if ($value_year!="") $value=$value_year;
				else $value="nnnn";
				
				$key_month=$key_part."_month";
				$value_month=getvalescaped($key_month,"");
				if ($value_month=="") $value_month.="nn";
				
				$key_day=$key_part."_day";
				$value_day=getvalescaped($key_day,"");
				if ($value_day!="") $value.="|" . $value_month . "|" . $value_day;
				elseif ($value_month!="nn") $value.="|" . $value_month;
    				
    
    				$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . $field . ":" . $value;

				    }
	            				
				}
			elseif (strpos($key,"_drop_")!==false)
				{
				# Dropdown field
				# Add keyword exactly as it is as the full value is indexed as a single keyword for dropdown boxes.
				$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . substr($key,11) . ":" . $value;
				}		
			elseif (strpos($key,"_cat_")!==false)
				{
				# Category tree field
				# Add keyword exactly as it is as the full value is indexed as a single keyword for dropdown boxes.
				$value=str_replace(",",";",$value);
				if (substr($value,0,1)==";") {$value=substr($value,1);}
				
				$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . substr($key,10) . ":" . $value;
				}		

			else
				{
				# Standard field
				$values=explode(" ",$value);
				foreach ($values as $value)
					{
					# Standard field
					$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . substr($key,6) . ":" . $value;
					}
				}
			}
		}

	$year=getvalescaped("year","");
	if ($year!="") {$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . "year:" . $year;}
	$month=getvalescaped("month","");
	if ($month!="") {$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . "month:" . $month;}
	$day=getvalescaped("day","");
	if ($day!="") {$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . "day:" . $day;}
	}

$searchresourceid = "";
if (is_numeric(trim(getvalescaped("searchresourceid","")))){
	$searchresourceid = trim(getvalescaped("searchresourceid",""));
	$search = "!resource$searchresourceid";
}
	
hook("searchstringprocessing");


# Fetch and set the values
//setcookie("search",$search); # store the search in a cookie if not a special search
$offset=getvalescaped("offset",0);if (strpos($search,"!")===false) {setcookie("saved_offset",$offset, 0, '', '', false, true);}
if ((!is_numeric($offset)) || ($offset<0)) {$offset=0;}
$order_by=getvalescaped("order_by","");if (strpos($search,"!")===false) {setcookie("saved_order_by",$order_by, 0, '', '', false, true);}
if ($order_by=="")
	{
	if (substr($search,0,11)=="!collection") // We want the default collection order to be applied
		{$order_by="relevance";}
	else
		{$order_by=$default_sort;}
	}
$per_page=getvalescaped("per_page",$default_perpage);setcookie("per_page",$per_page, 0, '', '', false, true);
$archive=getvalescaped("archive",0);if (strpos($search,"!")===false) {setcookie("saved_archive",$archive, 0, '', '', false, true);}
$jumpcount=0;

if (getvalescaped("recentdaylimit","")!="") //set for recent search, don't set cookie
	{
	$daylimit=getvalescaped("recentdaylimit","");
	}
else if($recent_search_period_select==true && strpos($search,"!")===false) //set cookie for paging
	{
	$daylimit=getvalescaped("daylimit",""); 
	setcookie("daylimit",$daylimit, 0, '', '', false, true); 
	}
else {$daylimit="";} // clear cookie for new search

# Most sorts such as popularity, date, and ID should be descending by default,
# but it seems custom display fields like title or country should be the opposite.
$default_sort_order="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_order="ASC";}
$sort=getvalescaped("sort",$default_sort_order);setcookie("saved_sort",$sort, 0, '', '', false, true);
$revsort = ($sort=="ASC") ? "DESC" : "ASC";

## If displaying a collection
# Enable/disable the reordering feature. Just for collections for now.
$allow_reorder=false;

# get current collection resources to pre-fill checkboxes
if ($use_checkboxes_for_selection){
$collectionresources=get_collection_resources($usercollection);
}
    $hiddenfields=getvalescaped("hiddenfields","");

# fetch resource types from query string and generate a resource types cookie
if (getvalescaped("resetrestypes","")=="")
	{
	$restypes=getvalescaped("restypes","");
	}
else
	{ 
	$restypes="";
	reset($_POST);reset($_GET);foreach (array_merge($_GET, $_POST) as $key=>$value)

		{
		
	    $hiddenfields=Array();
		//$hiddenfields=explode(",",$hiddenfields);
		if ($key=="rttickall" && $value=="on"){$restypes="";break;}	
		if ((substr($key,0,8)=="resource")&&!in_array($key, $hiddenfields)) {if ($restypes!="") {$restypes.=",";} $restypes.=substr($key,8);}
		}

	setcookie("restypes",$restypes, 0, '', '', false, true);

	# This is a new search, log this activity
	if ($archive==2) {daily_stat("Archive search",0);} else {daily_stat("Search",0);}
	}


# if search is not a special search (ie. !recent), use starsearchvalue.
if (getvalescaped("search","")!="" && strpos(getvalescaped("search",""),"!")!==false)
	{
	$starsearch="";
	}
else
	{
	$starsearch=getvalescaped("starsearch","");	
	setcookie("starsearch",$starsearch, 0, '', '', false, true);
}

# If returning to an old search, restore the page/order by
if (!array_key_exists("search",$_GET) && !array_key_exists("search",$_POST))
	{
	$offset=getvalescaped("saved_offset",0,true);setcookie("saved_offset",$offset, 0, '', '', false, true);
	$order_by=getvalescaped("saved_order_by","relevance");setcookie("saved_order_by",$order_by, 0, '', '', false, true);
	$sort=getvalescaped("saved_sort","");setcookie("saved_sort",$sort, 0, '', '', false, true);
	$archive=getvalescaped("saved_archive",0);setcookie("saved_archive",$archive, 0, '', '', false, true);
	}
	
hook("searchparameterhandler");	
	
# If requested, refresh the collection frame (for redirects from saves)
if (getvalescaped("refreshcollectionframe","")!="")
	{
	refresh_collection_frame();
	}

# Initialise the results references array (used later for search suggestions)
$refs=array();

# Special query? Ignore restypes
if (strpos($search,"!")!==false) {$restypes="";}

# Do the search!
$search=refine_searchstring($search);
if (strpos($search,"!")===false) {setcookie("search",$search, 0, '', '', false, true);}
hook('searchaftersearchcookie');
$result=do_search($search,$restypes,$order_by,$archive,$per_page+$offset,$sort,false,$starsearch,false,false,$daylimit, getvalescaped("go",""));
if($k=="" && strpos($search,"!")===false && $archive==0){$collections=do_collections_search($search,$restypes);} // don't do this for external shares

# Allow results to be processed by a plugin
$hook_result=hook("process_search_results","search",array("result"=>$result,"search"=>$search));
if ($hook_result!==false) {$result=$hook_result;}

if (substr($search,0,11)=="!collection")
	{
	$collection=substr($search,11);
	$collection=explode(",",$collection);
	$collection=$collection[0];
	$collectiondata=get_collection($collection);
	
	if ($k!="") {$usercollection=$collection;} # External access - set current collection.
	
	if (!$collectiondata){?>
		<script>alert('<?php echo $lang["error-collectionnotfound"];?>');document.location='<?php echo $baseurl."/pages/" . $default_home_page;?>'</script>
	<?php } 
	# Check to see if this user can edit (and therefore reorder) this resource
	if (($userref==$collectiondata["user"]) || ($collectiondata["allow_changes"]==1) || (checkperm("h")))
		{
		$allow_reorder=true;
		}
	}

# Include function for reordering
if ($allow_reorder && $display!="list")
	{
	# Also check for the parameter and reorder as necessary.
	$reorder=getvalescaped("reorder",false);
	if ($reorder)
		{
		$neworder=json_decode(getvalescaped("order",false));
		update_collection_order($neworder,$collection,$offset);
		exit("SUCCESS");
		}
	}

include ("../include/search_title_processing.php");

    
# Special case: numeric searches (resource ID) and one result: redirect immediately to the resource view.
if ((($config_search_for_number && is_numeric($search)) || $searchresourceid > 0) && is_array($result) && count($result)==1)
	{
	redirect($baseurl_short."pages/view.php?ref=" . $result[0]["ref"] . "&search=" . urlencode($search) . "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&offset=" . urlencode($offset) . "&archive=" . urlencode($archive) . "&k=" . urlencode($k));
	}
	

# Include the page header to and render the search results
include "../include/header.php";
if($k=="")
	{
	 ?>
	<script type="text/javascript">
	var dontReloadSearchBar=<?php echo getval('noreload', null)!=null ? 'true' : 'false' ?>;
	if (dontReloadSearchBar !== true)
		ReloadSearchBar();
	ReloadLinks();
	</script>
 	<?php
	}
if ($display_user_rating_stars && $k=="")
	{
	if (!hook("replace_user_rating_searchviewjs")){?>
	<script src="<?php echo $baseurl ?>/lib/js/user_rating_searchview.js?1" type="text/javascript"></script>
	<?php
	}
	}

	if ($allow_reorder && $display!="list") {
?>
	<script type="text/javascript">
	
	function ReorderResources(idsInOrder)
		{
		var newOrder = [];
		jQuery.each(idsInOrder, function() {
			newOrder.push(this.substring(13));
			});
		jQuery.ajax({
		  type: 'POST',
		  url: 'search.php?search=!collection<?php echo urlencode($collection) ?>&reorder=true',
		  data: {order:JSON.stringify(newOrder)},
		  success: function(){
		  <?php if (isset($usercollection) && ($usercollection==$collection)) { ?>
			 UpdateCollectionDisplay('<?php echo isset($k)?htmlspecialchars($k):"" ?>');
		  <?php } ?>
			} 
		});
		}		
            jQuery('.ui-sortable').sortable('enable');
			jQuery('#CentralSpace').sortable({
				helper:"clone",
				items: ".ResourcePanelShell, .ResourcePanelShellLarge, .ResourcePanelShellSmall",

				start: function (event, ui)
					{
					InfoBoxEnabled=false;
					if (jQuery('#InfoBox')) {jQuery('#InfoBox').hide();}
					if (jQuery('#InfoBoxCollection')) {jQuery('#InfoBoxCollection').hide();}
					},

				update: function(event, ui)
					{
					InfoBoxEnabled=true;
					var idsInOrder = jQuery('#CentralSpace').sortable("toArray");
					ReorderResources(idsInOrder);
					}
			});
			jQuery('.ResourcePanelShell').disableSelection();
			jQuery('.ResourcePanelShellLarge').disableSelection();
			jQuery('.ResourcePanelShellSmall').disableSelection();			
	
	</script>
<?php }
	elseif (!hook("noreorderjs")) { ?>
	<script type="text/javascript">
        
			jQuery('.ui-sortable').sortable('disable');
			jQuery('.ResourcePanelShell').enableSelection();
			jQuery('.ResourcePanelShellLarge').enableSelection();
			jQuery('.ResourcePanelShellSmall').enableSelection();
			
	
	</script>
	<?php }

# Hook to replace all search results (used by ResourceConnect plugin, allows search mechanism to be entirely replaced)
if (!hook("replacesearchresults")) { 

# Extra CSS to support more height for titles on thumbnails.
if (isset($result_title_height))
	{
	?>
	<style>
	.ResourcePanelInfo .extended
		{
		white-space:normal;
		height: <?php echo $result_title_height ?>px;
		}
	</style>
	<?php
	}


# Extra CSS if using Image Infoboxes ($infobox_image_mode)
if ($infobox_image_mode)
	{
	?>
	<style>
	#InfoBox
		{
		width:400px;height:450px;
		}
	#InfoBoxInner
		{
		height:350px;
		}
	</style>
	<?php
	
	}

#if (is_array($result)||(isset($collections)&&(count($collections)>0)))
if (true) # Always show search header now.
	{
	$url=$baseurl_short."pages/search.php?search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=".urlencode($sort)."&amp;offset=" . urlencode($offset) . "&amp;archive=" . urlencode($archive)."&amp;sort=".urlencode($sort) . "&amp;restypes=" . urlencode($restypes);
	$resources_count=is_array($result)?count($result):0;
    if (isset($collections)) 
        {
        $results_count=count($collections)+$resources_count;
    	}
	?>
	<div class="TopInpageNav">
	<div class="TopInpageNavLeft">
	<?php hook("responsiveresultoptions"); ?>
	<div id="SearchResultFound" class="InpageNavLeftBlock"><?php echo $lang["youfound"]?>:<br /><span class="Selected">
	<?php
	if (isset($collections)) 
	    {
        echo number_format($results_count)?> </span><?php echo ($results_count==1) ? $lang["youfoundresult"] : $lang["youfoundresults"];
	    } 
	else
	    {
	    echo number_format($resources_count)?> </span><?php echo ($resources_count==1)? $lang["youfoundresource"] : $lang["youfoundresources"];
	    }
	 ?></div>
	<div class="InpageNavLeftBlock <?php if($iconthumbs) {echo 'icondisplay';} ?>"><?php echo $lang["display"]?>:<br />


	<?php if ($display_selector_dropdowns){?>
	<select class="medcomplementwidth ListDropdown" style="width:auto" id="displaysize" name="displaysize" onchange="CentralSpaceLoad(this.value,true);">
	<?php if ($xlthumbs==true) { ?><option <?php if ($display=="xlthumbs"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=xlthumbs&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["xlthumbs"]?></option><?php } ?>
	<option <?php if ($display=="thumbs"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=thumbs&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["largethumbs"]?></option>
	<?php if ($smallthumbs==true) { ?><option <?php if ($display=="smallthumbs"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=smallthumbs&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["smallthumbs"]?></option><?php } ?>
	<option <?php if ($display=="list"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=list&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["list"]?></option>
	</select>&nbsp;
	<?php } elseif($iconthumbs) { ?>

	<?php if ($xlthumbs==true) { ?> <?php if ($display=="xlthumbs") { ?><span class="xlthumbsiconactive">&nbsp;</span><?php } else { ?><a href="<?php echo $url?>&amp;display=xlthumbs&amp;k=<?php echo urlencode($k) ?>" title='<?php echo $lang["xlthumbstitle"] ?>' onClick="return CentralSpaceLoad(this);"><span class="xlthumbsicon">&nbsp;</span></a><?php } ?>&nbsp;<?php } ?>
	<?php if ($display=="thumbs") { ?> <span class="largethumbsiconactive">&nbsp;</span><?php } else { ?><a href="<?php echo $url?>&amp;display=thumbs&amp;k=<?php echo urlencode($k) ?>" title='<?php echo $lang["largethumbstitle"] ?>' onClick="return CentralSpaceLoad(this);"><span class="largethumbsicon">&nbsp;</span></a><?php } ?>
	<?php if ($smallthumbs==true) { ?> <?php if ($display=="smallthumbs") { ?><span class="smallthumbsiconactive">&nbsp;</span><?php } else { ?><a href="<?php echo $url?>&amp;display=smallthumbs&amp;k=<?php echo urlencode($k)?>" title='<?php echo $lang["smallthumbstitle"] ?>' onClick="return CentralSpaceLoad(this);"><span class="smallthumbsicon">&nbsp;</span></a><?php } } ?>
	<?php if ($display=="list") { ?> <span class="smalllisticonactive">&nbsp;</span><?php } else { ?><a href="<?php echo $url?>&amp;display=list&amp;k=<?php echo urlencode($k) ?>" title='<?php echo $lang["listtitle"] ?>' onClick="return CentralSpaceLoad(this);"><span class="smalllisticon">&nbsp;</span></a><?php } ?> <?php hook("adddisplaymode"); ?> 

<?php } else { ?>
	
	<?php if ($xlthumbs==true) { ?> <?php if ($display=="xlthumbs") { ?><span class="Selected"><?php echo $lang["xlthumbs"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=xlthumbs&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["xlthumbs"]?></a><?php } ?>&nbsp; |&nbsp;<?php } ?>
	<?php if ($display=="thumbs") { ?> <span class="Selected"><?php echo $lang["largethumbs"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=thumbs&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["largethumbs"]?></a><?php } ?>&nbsp; |&nbsp; 
	<?php if ($smallthumbs==true) { ?> <?php if ($display=="smallthumbs") { ?><span class="Selected"><?php echo $lang["smallthumbs"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=smallthumbs&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["smallthumbs"]?></a><?php } ?>&nbsp; |&nbsp;<?php } ?>
	<?php if ($display=="list") { ?> <span class="Selected"><?php echo $lang["list"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=list&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["list"]?></a><?php } ?> <?php hook("adddisplaymode"); ?> 
	

	<?php } ?>
	</div>
	
	<?php if ($display_selector_dropdowns || $perpage_dropdown){?>
	<div class="InpageNavLeftBlock"><?php echo ucfirst($lang["perpage"]);?>:<br />
		<select class="medcomplementwidth ListDropdown" style="width:auto" id="resultsdisplay" name="resultsdisplay" onchange="CentralSpaceLoad(this.value,true);">
		<?php for($n=0;$n<count($results_display_array);$n++){
			if ($display_selector_dropdowns || $perpage_dropdown){?>
				<option <?php if ($per_page==$results_display_array[$n]){?>selected="selected"<?php } ?> value="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>&amp;per_page=<?php echo urlencode($results_display_array[$n])?>&amp;sort=<?php echo urlencode($sort)?>"><?php echo urlencode($results_display_array[$n])?></option>
			<?php } ?>
		<?php } ?>	
		</select>
	</div>
	<?php } 
	
	if ($display_selector_dropdowns && $recent_search_period_select && strpos($search,"!")===false && getvalescaped("recentdaylimit","")==""){?>
	<div class="InpageNavLeftBlock"><?php echo $lang["period"]?>:<br />
		<select class="medcomplementwidth ListDropdown" style="width:auto" id="resultsdisplay" name="resultsdisplay" onchange="CentralSpaceLoad(this.value,true);">
		<?php for($n=0;$n<count($recent_search_period_array);$n++){
			if ($display_selector_dropdowns){?>
				<option <?php if ($daylimit==$recent_search_period_array[$n]){?>selected="selected"<?php } ?> value="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>"><?php echo urlencode($results_display_array[$n])?>&amp;daylimit=<?php echo urlencode(str_replace("?",$recent_search_period_array[$n],$lang["lastndays"]))?></option>
			<?php } ?>
		<?php } ?>
		<option <?php if ($daylimit==""){?>selected="selected"<?php } ?> value="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>"><?php echo urlencode($results_display_array[$n])?>&amp;daylimit=<?php echo $lang["anyday"] ?></option>
		</select>
	</div>
	<?php } 
	
	# order by
	#if (strpos($search,"!")===false)
	if ($search!="!duplicates" && $search!="!unused") # Ordering enabled for collections/themes too now at the request of N Ward / Oxfam
		{
		$rel=$lang["relevance"];
		if(!hook("replaceasadded"))
			{
			if (isset($collection)){$rel=$lang["collection_order_description"];}
			elseif (strpos($search,"!")!==false) {$rel=$lang["asadded"];}
			}

		function display_sort_order($name, $label)
			{
			global $order_by;
			if (isset($GLOBALS['display_fields_added']))
				echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
			else
				$GLOBALS['display_fields_added'] = true;
			$fixedOrder = $name=='relevance';
			$selected = $order_by==$name;
			if ($selected && $fixedOrder)
				{
				?><span class="Selected"><?php echo $label?></span><?php
				}
			else
				{
				global $baseurl_short, $revsort, $search, $archive, $restypes, $k, $sort;
				if ($selected)
					{
					?><span class="Selected"><?php
					}
				?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php
					echo urlencode($search) . '&amp;order_by=' . $name . '&amp;archive='
							. urlencode($archive) . '&amp;k=' . urlencode($k) . '&amp;restypes='
							. urlencode($restypes);
					if ($selected)
						{
						echo '&amp;sort=' . urlencode($revsort);
						}
					?>" onClick="return CentralSpaceLoad(this);"><?php echo $label ?></a><?php
					if (!$fixedOrder && $selected)
						{
						?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php
						}
					if ($selected)
						{
						?></span><?php
						}
				}
			}

		$orderFields = array('relevance' => $rel);
		if ($random_sort)
			$orderFields['random'] = $lang['random'];
		if ($popularity_sort)
			$orderFields['popularity'] = $lang['popularity'];
		if ($orderbyrating)
			$orderFields['rating'] = $lang['rating'];
		if ($date_column)
			$orderFields['date'] = $lang['date'];
		if ($colour_sort)
			$orderFields['colour'] = $lang['colour'];
		if ($order_by_resource_id)
			$orderFields['resourceid'] = $lang['resourceid'];
		if ($order_by_resource_type)
			$orderFields['resourcetype'] = $lang['type'];

		# Add thumbs_display_fields to sort order links for thumbs views
		for ($x=0;$x<count($sf);$x++)
			{
			if (!isset($metadata_template_title_field)){$metadata_template_title_field=false;}
			if ($sf[$x]['ref']!=$metadata_template_title_field)
				{
				$orderFields['field' . $sf[$x]['ref']] = htmlspecialchars($sf[$x]['title']);
				}
			}

		$modifiedFields = hook('modifyorderfields', '', array($orderFields));
		if ($modifiedFields)
			$orderFields = $modifiedFields;
		?>
		<div class="InpageNavLeftBlock ">
		<?php
		echo $lang["sortorder"] . ':<br />';

		foreach ($orderFields as $order => $label)
			{
			display_sort_order($order, $label);
			}
		hook("sortorder");?>
		</div>
		<?php
		} 
		if (!$display_selector_dropdowns && !$perpage_dropdown){?>
		<div class="InpageNavLeftBlock"><?php echo ucfirst($lang["perpage"]);?>:<br />
		<?php 
		for($n=0;$n<count($results_display_array);$n++){?>
		<?php if ($per_page==$results_display_array[$n]){?><span class="Selected"><?php echo urlencode($results_display_array[$n])?></span><?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;per_page=<?php echo urlencode($results_display_array[$n])?>&amp;sort=<?php echo urlencode($sort)?>" onClick="return CentralSpaceLoad(this);"><?php echo urlencode($results_display_array[$n])?></a><?php } ?><?php if ($n>-1&&$n<count($results_display_array)-1){?>&nbsp;|<?php } ?>
		<?php } ?>
		</div>
		<?php } 
	
		if (!$display_selector_dropdowns && $recent_search_period_select && strpos($search,"!")===false && getvalescaped("recentdaylimit","")==""){?>
		<div class="InpageNavLeftBlock"><?php echo $lang["period"]?>:<br />
		<?php 
		for($n=0;$n<count($recent_search_period_array);$n++){
			if ($daylimit==$recent_search_period_array[$n]){?><span class="Selected"><?php echo htmlspecialchars(str_replace("?",$recent_search_period_array[$n],$lang["lastndays"]))?> </span>&nbsp;|&nbsp;<?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>&amp;daylimit=<?php echo urlencode($recent_search_period_array[$n])?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars(str_replace("?",$recent_search_period_array[$n],$lang["lastndays"]))?></a>&nbsp;|&nbsp;<?php } 
			}
		if ($daylimit==""){?><span class="Selected"><?php echo $lang["all"] ?></span><?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>&amp;daylimit=" onClick="return CentralSpaceLoad(this);"><?php echo $lang["all"]?></a><?php } 
		?>				
		</div>
		<?php } ?>		
		
	<?php

		
	$results=count($result);
	$totalpages=ceil($results/$per_page);
	if ($offset>$results) {$offset=0;}
	$curpage=floor($offset/$per_page)+1;
	$url=$baseurl_short."pages/search.php?search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=" . urlencode($sort) . "&amp;archive=" . urlencode($archive) . "&amp;k=" . urlencode($k) . "&amp;restypes=" . urlencode($restypes);	
	?>
	</div>
	<?php hook("stickysearchresults"); ?> <!--the div TopInpageNavRight was added in after this hook so it may need to be adjusted -->
	<div class="TopInpageNavRight">
	<?php
	    pager();
		$draw_pager=true;
	?>
	</div>
	<div class="clearerleft"></div>
	</div>
        <?php 
		hook("stickysearchresults");

	if ($display_search_titles)
		{
		hook("beforesearchtitle");
		if (!$collections_compact_style)
			{
	        echo $search_title;
	        hook("aftersearchtitle");
	        }
	    else 
	    	{
	    	echo $search_title;hook("aftersearchtitle");
	    	if (substr($search,0,11)=="!collection" && $k=="")
		    	{
		        $cinfo=get_collection(substr($search,11));
		        $feedback=$cinfo["request_feedback"];
		        $count_result=count($result);
		        $collections_compact_style_titleview=true;
		        hook("beforecollectiontoolscolumn");
		        ?><div class="SearchResultsCollectionCompactTools"><?php
		        echo $lang["tools"].": ";
		        draw_compact_style_selector($cinfo["ref"]);
		        ?></div><?php
		        ?><br /><br /><div class="clearerleft"></div><?php
		        $collection_compact_style_titleview=false;
		        } /*end if a collection search and compact_style - action selector*/   
    		}
    	}	
	hook("beforesearchresults");
	
	# Archive link
	if (($archive==0) && (strpos($search,"!")===false) && $archive_search) 
		{ 
		$arcresults=do_search($search,$restypes,$order_by,2,0);
		if (is_array($arcresults)) {$arcresults=count($arcresults);} else {$arcresults=0;}
		if ($arcresults>0) 
			{
			?>
			<div class="SearchOptionNav"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;archive=2" onClick="return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang["view"]?> <span class="Selected"><?php echo number_format($arcresults)?></span> <?php echo ($arcresults==1)?$lang["match"]:$lang["matches"]?> <?php echo $lang["inthearchive"]?></a></div>
			<?php 
			}
		else
			{
			?>
			<div class="InpageNavLeftBlock">&gt;&nbsp;<?php echo $lang["nomatchesinthearchive"]?></div>
			<?php 
			}
		}
	if (!$collections_compact_style){echo $search_title_links;}
	hook("beforesearchresults2");
	hook("beforesearchresultsexpandspace");
	?>
	<div class="clearerleft"></div>
	<?php

		
	if (!is_array($result) && empty($collections))
		{
		// No matches found
		?>
		<div class="BasicsBox"> 
		  <div class="NoFind">
			<p><?php echo $lang["searchnomatches"]?></p>
			<?php if ($result!="")
			{
			?>
			<p><?php echo $lang["try"]?>: <a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode(strip_tags($result))?>"><?php echo stripslashes($result)?></a></p>
			<?php $result=array();
			}
			else
			{
			?>
			<p><?php if (strpos($search,"country:")!==false) { ?><p><?php echo $lang["tryselectingallcountries"]?> <?php } 
			elseif (strpos($search,"year:")!==false) { ?><p><?php echo $lang["tryselectinganyyear"]?> <?php } 
			elseif (strpos($search,"month:")!==false) { ?><p><?php echo $lang["tryselectinganymonth"]?> <?php } 
			else 		{?><?php echo $lang["trybeinglessspecific"]?><?php } ?> <?php echo $lang["enteringfewerkeywords"]?></p>
			<?php
			}
		  ?>
		  </div>
		</div>
		<?php
		}

    $list_displayed = false;
    # Listview - Display title row if listview and if any result.
    if ($display=="list" && ((is_array($result) && count($result)>0) || (isset($collections) && is_array($collections) && count($collections)>0)))
        {
        $list_displayed = true;
		?>
		<div class="Listview">
		<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">

		<?php if(!hook("replacelistviewtitlerow")){?>	
		<tr class="ListviewTitleStyle">
		<?php if (!hook("listcheckboxesheader")){?>
		<?php if ($use_checkboxes_for_selection){?><td><?php echo $lang['addremove'];?></td><?php } ?>
		<?php } # end hook listcheckboxesheader 

		for ($x=0;$x<count($df);$x++)
			{?>
			<?php if ($order_by=="field".$df[$x]['ref']) {?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=field<?php echo $df[$x]['ref']?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars($df[$x]['title'])?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=field<?php echo $df[$x]['ref']?>&amp;sort=<?php echo urlencode($revsort)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars($df[$x]['title'])?></a></td><?php } ?>
			<?php }
		
		if ($display_user_rating_stars && $k==""){?><td><?php if ($order_by=="popularity") {?><span class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=popularity&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;sort=<?php echo urlencode($revsort)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["popularity"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></span><?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=popularity&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["popularity"]?></a><?php } ?></td><?php } 
		if (isset($rating_field)){?><td>&nbsp;</td><!-- contains admin ratings --><?php }
		if ($id_column){?><?php if ($order_by=="resourceid"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourceid&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["id"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourceid&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["id"]?></a></td><?php } ?><?php } ?>
		<?php if ($resource_type_column){?><?php if ($order_by=="resourcetype"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourcetype&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["type"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourcetype&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["type"]?></a></td><?php } ?><?php } ?>
		<?php if ($list_view_status_column){?><?php if ($order_by=="status"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=status&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["status"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=status&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["status"]?></a></td><?php } ?><?php } ?>
		<?php if ($date_column){?><?php if ($order_by=="date"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=date&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["date"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=date&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["date"]?></a></td><?php } ?><?php } ?>
		<?php hook("addlistviewtitlecolumn");?>
		<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
		</tr>
		<?php } ?> <!--end hook replace listviewtitlerow-->
		<?php
		}
		# Include public collections and themes in the main search, if configured.		
		if ($offset==0 && isset($collections)&& strpos($search,"!")===false && $archive==0)
			{
			include "../include/search_public.php";
			}

	
	# work out common keywords among the results
	if ((count($result)>$suggest_threshold) && (strpos($search,"!")===false) && ($suggest_threshold!=-1))
		{
		for ($n=0;$n<count($result);$n++)
			{
			if ($result[$n]["ref"]) {$refs[]=$result[$n]["ref"];} # add this to a list of results, for query refining later
			}
		$suggest=suggest_refinement($refs,$search);
		if (count($suggest)>0)
			{
			?><p><?php echo $lang["torefineyourresults"]?>: <?php
			for ($n=0;$n<count($suggest);$n++)
				{
				if ($n>0) {echo ", ";}
				?><a  href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo  urlencode(strip_tags($suggest[$n])) ?>" onClick="return CentralSpaceLoad(this);"><?php echo stripslashes($suggest[$n])?></a><?php
				}
			?></p><?php
			}
		}
		
	$rtypes=array();
	if (!isset($types)){$types=get_resource_types();}
	for ($n=0;$n<count($types);$n++) {$rtypes[$types[$n]["ref"]]=$types[$n]["name"];}
    if (is_array($result) && count($result)>0)
        {
        $showkeypreview = false;
        $showkeycollect = false;
        $showkeycollectout = false;
        $showkeyemail = false;
        $showkeystar = false;
        $showkeycomment = false;

        # loop and display the results
        for ($n=$offset;(($n<count($result)) && ($n<($offset+$per_page)));$n++)
            {
	    # Allow alternative configuration settings for this resource type.
	    resource_type_config_override($result[$n]["resource_type"]);
		
		if ($order_by=="resourcetype" && $display!="list")
			{
			if ($n==0 || ((isset($result[$n-1])) && $result[$n]["resource_type"]!=$result[$n-1]["resource_type"]))
				{
				echo "<h1 class=\"SearchResultsDivider\" style=\"clear:left;\">" . htmlspecialchars($rtypes[$result[$n]["resource_type"]]) .  "</h1>";
				}
			}
			
            $ref = $result[$n]["ref"];
            $GLOBALS['get_resource_data_cache'][$ref] = $result[$n];
            $url = $baseurl_short."pages/view.php?ref=" . $ref . "&amp;search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=". urlencode($sort) . "&amp;offset=" . urlencode($offset) . "&amp;archive=" . urlencode($archive) . "&amp;k=" . urlencode($k) . "&amp;curpos=" . urlencode($n);

            if (isset($result[$n]["url"])) {$url = $result[$n]["url"];} # Option to override URL in results, e.g. by plugin using process_Search_results hook above
 
            $rating = "";
            if (isset($rating_field)){$rating = "field".$rating_field;}
			hook("beforesearchviewcalls");
            if ($display=="thumbs")
                {
                #  ---------------------------- Thumbnails view ----------------------------
                include "search_views/thumbs.php";
                } 

            if ($display=="xlthumbs")
                {
                #  ---------------------------- X-Large Thumbnails view ----------------------------
                include "search_views/xlthumbs.php";
                }

            if ($display=="smallthumbs")
                {
                # ---------------- Small Thumbs view ---------------------
                include "search_views/smallthumbs.php";
                }

            if ($display=="list")
                {
                # ----------------  List view -------------------
                include "search_views/list.php";
                }

            hook("customdisplaymode");

            }
        }
    # Listview - Add closing tag if a list is displayed.
    if ($list_displayed==true)
        {
        ?>
        </table>
        </div>
        <?php
        }
    else
        {
        # Display keys (only keys used in the current search view).
        if (!hook("replacesearchkey"))
            {
            if (is_array($result) && count($result)>0)
                { ?>
                <div class="BottomInpageKey"><?php
                    echo $lang["key"] . " ";
                    if ($showkeystar) { ?><div class="KeyStar"><?php echo $lang["verybestresources"]?></div><?php }
                    if ($showkeycomment) { ?><div class="KeyComment"><?php echo $lang["addorviewcomments"]?></div><?php }
                    if ($showkeyemail) { ?><div class="KeyEmail"><?php echo $lang["emailresource"]?></div><?php }
                    if ($showkeycollectout) { ?><div class="KeyCollectOut"><?php echo $lang["removefromcurrentcollection"]?></div><?php }
                    if ($showkeycollect) { ?><div class="KeyCollect"><?php echo $lang["addtocurrentcollection"]?></div><?php }
                    if ($showkeypreview) { ?><div class="KeyPreview"><?php echo $lang["fullscreenpreview"]?></div><?php }
                    hook("searchkey"); ?>
                </div><?php
                }
            } /* end hook replacesearchkey */
        }        
    }
?>
<!--Bottom Navigation - Archive, Saved Search plus Collection-->
<div class="BottomInpageNav">
	<div class="BottomInpageNavLeft">
	<?php if(!hook("replacesearchbottomnav")){ ?>
	<?php if (!checkperm("b") && $k=="") { ?>
	<?php if($allow_save_search) { ?><div class="InpageNavLeftBlock"><a onClick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?addsearch=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;daylimit=<?php echo urlencode($daylimit) ?>">&gt;&nbsp;<?php echo $lang["savethissearchtocollection"]?></a></div><?php } ?>
	<?php if($allow_smart_collections && substr($search,0,11)!="!collection") { ?><div class="InpageNavLeftBlock"><a onClick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?addsmartcollection=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;starsearch=<?php echo urlencode($starsearch) ?>">&gt;&nbsp;<?php echo $lang["savesearchassmartcollection"]?></a></div><?php } ?>
	<?php global $smartsearch; if($allow_smart_collections && substr($search,0,11)=="!collection" && (is_array($smartsearch[0]) && !empty($smartsearch[0]))) { $smartsearch=$smartsearch[0];?><div class="InpageNavLeftBlock"><a onClick="return CentralSpaceLoad(this,true);" href="search.php?search=<?php echo urlencode($smartsearch['search'])?>&amp;restypes=<?php echo urlencode($smartsearch['restypes'])?>&amp;archive=<?php echo urlencode($smartsearch['archive']) ?>&amp;starsearch=<?php echo urlencode($smartsearch['starsearch']) ?>&amp;daylimit=<?php echo urlencode($daylimit) ?>">&gt;&nbsp;<?php echo $lang["dosavedsearch"]?></a></div><?php } ?>
	
	<?php if ($resources_count!=0){?>
	<div class="InpageNavLeftBlock"><a onClick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?addsearch=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;mode=resources&amp;daylimit=<?php echo urlencode($daylimit) ?>">&gt;&nbsp;<?php echo $lang["savesearchitemstocollection"]?></a></div>
	<?php if($show_searchitemsdiskusage) {?>
	<div class="InpageNavLeftBlock"><a onClick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short?>pages/search_disk_usage.php?search=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;daylimit=<?php echo urlencode($daylimit) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>">&gt;&nbsp;<?php echo $lang["searchitemsdiskusage"]?></a></div>
	<?php } ?>
	<?php } ?>

	<?php } ?>
	
	<?php hook("resultsbottomtoolbar"); ?>
	<?php } ?>  <!--End of hook("replacesearchbottomnav")-->	
	<?php 
	$url=$baseurl_short."pages/search.php?search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=" . urlencode($sort) . "&amp;archive=" . urlencode($archive) . "&amp;daylimit=" . urlencode($daylimit) . "&amp;k=" . urlencode($k) . "&amp;restypes=" . urlencode($restypes);	
	?>
	</div>
	<div class="BottomInpageNavRight">	
	<?php 
	if (isset($draw_pager)) {pager(false);} 
	?>
	</div>
	<div class="clearerleft"></div>
</div>
	<?php
} # End of replace all results hook conditional

hook("endofsearchpage");?>
<?php	


# Add the infobox.
?>
<div id="InfoBox"><div id="InfoBoxInner"> </div></div>
<?php
include "../include/footer.php";
?>
