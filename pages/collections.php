<?php
include_once dirname(__FILE__)."/../include/db.php";
include_once dirname(__FILE__)."/../include/general.php";
include_once dirname(__FILE__)."/../include/collections_functions.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(getvalescaped("collection","",true),$k))) {include_once dirname(__FILE__)."/../include/authenticate.php";}
if (checkperm("b")){exit($lang["error-permissiondenied"]);}
include_once dirname(__FILE__)."/../include/research_functions.php";
include_once dirname(__FILE__)."/../include/resource_functions.php";
include_once dirname(__FILE__)."/../include/search_functions.php";


// copied from collection_manage to support compact style collection adds (without redirecting to collection_manage)
$addcollection=getvalescaped("addcollection","");
if ($addcollection!="")
	{
	# Add someone else's collection to your My Collections
	add_collection($userref,$addcollection);
	set_user_collection($userref,$addcollection);
	refresh_collection_frame();
	
   	# Log this
	daily_stat("Add public collection",$userref);
	}
/////

#Remove all from collection
	$ref = getvalescaped("ref","",true);
if($ref!='' && getvalescaped("submitted","")=='removeall' && getval("removeall","")!="") 
	{
	remove_all_resources_from_collection($ref);
	}
# Disable info box for external access.
if ($k!="") {$infobox=false;} 
# Disable checkboxes for external users.
if ($k!="") {$use_checkboxes_for_selection=false;}

if(!isset($thumbs))
    {
    $thumbs=getval("thumbs","unset");
    if($thumbs == "unset")
        {
        $thumbs = $thumbs_default;
        rs_setcookie("thumbs", $thumbs, 1000,"","",false,false);
        }
    }

# Basket mode? - this is for the e-commerce user request modes.
if ($userrequestmode==2 || $userrequestmode==3)
	{
	# Enable basket
	$basket=true;	
	}
else
	{
	$basket=false;
	}

$collection=getvalescaped("collection","",true);
$entername=getvalescaped("entername","");

# ------------ Change the collection, if a collection ID has been provided ----------------
if ($collection!="")
	{
	hook("prechangecollection");
	#change current collection
	
	if ($k=="" && $collection==-1)
		{
		# Create new collection
		if ($entername!=""){ $name=$entername;} 
		else { $name=get_mycollection_name($userref);}
		$new=create_collection ($userref,$name);
		set_user_collection($userref,$new);
		
		# Log this
		daily_stat("New collection",$userref);
		}
	else
		{
		# Switch the existing collection
		if ($k=="") {set_user_collection($userref,$collection);}
		$usercollection=$collection;
		}

	hook("postchangecollection");
	}

	
# Load collection info.
$cinfo=get_collection($usercollection);
	
# Check to see if the user can edit this collection.
$allow_reorder=false;
if (($k=="") && (($userref==$cinfo["user"]) || ($cinfo["allow_changes"]==1) || (checkperm("h"))))
	{
	$allow_reorder=true;
	}	
	
# Reordering capability
if ($allow_reorder)
	{
	# Also check for the parameter and reorder as necessary.
	$reorder=getvalescaped("reorder",false);
	if ($reorder)
		{
		$neworder=json_decode(getvalescaped("order",false));
		update_collection_order($neworder,$usercollection);
		exit("SUCCESS");
		}
	}


# Include function for reordering
if ($allow_reorder)
	{
	?>
	<script type="text/javascript">
	function ReorderResourcesInCollection(idsInOrder)
		{
		var newOrder = [];
		jQuery.each(idsInOrder, function() {
			newOrder.push(this.substring(13));
			}); 
		
		jQuery.ajax({
		  type: 'POST',
		  url: '<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo urlencode($usercollection) ?>&reorder=true',
		  data: {order:JSON.stringify(newOrder)},
		  success: function() {
		    var results = new RegExp('[\\?&amp;]' + 'search' + '=([^&amp;#]*)').exec(window.location.href);
		    var ref = new RegExp('[\\?&amp;]' + 'ref' + '=([^&amp;#]*)').exec(window.location.href);
		    if ((ref==null)&&(results!== null)&&('<?php echo urlencode("!collection" . $usercollection); ?>' === results[1])) CentralSpaceLoad('<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>',true);
		  }
		});		
		}
		
		jQuery(document).ready(function() {
			jQuery('#CollectionSpace').sortable({
				helper:"clone",
				items: ".CollectionPanelShell",

				start: function (event, ui)
					{
					InfoBoxEnabled=false;
					if (jQuery('#InfoBoxCollection')) {jQuery('#InfoBoxCollection').hide();}
					},

				stop: function(event, ui)
					{
					InfoBoxEnabled=true;
					var idsInOrder = jQuery('#CollectionSpace').sortable("toArray");
					ReorderResourcesInCollection(idsInOrder);
					}
			});
			jQuery('.CollectionPanelShell').disableSelection();
			
		});	
		
		
	</script>
<?php } 
else { ?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
			jQuery('.ui-sortable').sortable('disable');
			jQuery('.CollectionPanelShell').enableSelection();			
		});	
	</script>
	<?php } 
	hook("responsivethumbsloaded");

?>
	<style>
	#CollectionMenuExp
		{
		height:<?php echo $collection_frame_height-15?>px;
		<?php if ($remove_collections_vertical_line){?>border-right: 0px;<?php }?>
		}
	</style>

	<?php hook("headblock");?>

	</head>

	<body class="CollectBack" id="collectbody"<?php if ($infobox) { ?> OnMouseMove="InfoBoxMM(event);"<?php } ?>>
<div style="display:none;" id="currentusercollection"><?php echo $usercollection?></div>

<script>usercollection='<?php echo htmlspecialchars($collection) ?>';</script>
<?php 

$add=getvalescaped("add","");
if ($add!="")
	{
	if(strpos($add,",")>0)
		{
		$addarray=explode(",",$add);
		}
	else
		{
		$addarray[0]=$add;
		unset($add);
		}	
	foreach ($addarray as $add)
		{
		hook("preaddtocollection");
		#add to current collection
		if (add_resource_to_collection($add,$usercollection,false,getvalescaped("size",""))==false)
			{ ?>
			<script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
			}
		else
			{
			# Log this	
			daily_stat("Add resource to collection",$add);
		
			# Update resource/keyword kit count
			$search=getvalescaped("search","");
			if ((strpos($search,"!")===false) && ($search!="")) {update_resource_keyword_hitcount($add,$search);}
			hook("postaddtocollection");
			}
		}	
	# Show warning?
	if (isset($collection_share_warning) && $collection_share_warning)
		{
		?><script language="Javascript">alert("<?php echo $lang["sharedcollectionaddwarning"]?>");</script><?php
		}
	}

$remove=getvalescaped("remove","");
if ($remove!="")
	{
	if(strpos($remove,",")>0)
		{
		$removearray=explode(",",$remove);
		}
	else
		{
		$removearray[0]=$remove;
		unset($remove);
		}	
	foreach ($removearray as $remove)
		{
		hook("preremovefromcollection");
		#remove from current collection
		if (remove_resource_from_collection($remove,$usercollection)==false)
			{
			?><script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
			}
		else
			{
			# Log this	
			daily_stat("Removed resource from collection",$remove);		
			hook("postremovefromcollection");
			}
		}
	}
	
$addsearch=getvalescaped("addsearch",-1);
if ($addsearch!=-1)
	{
    if (!collection_writeable($usercollection))
        { ?>
        <script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
        }
    else
        {
        hook("preaddsearch");
        if (getval("mode","")=="")
            {
            #add saved search
            add_saved_search($usercollection);

            # Log this
            daily_stat("Add saved search to collection",0);
            }
        else
            {
            #add saved search (the items themselves rather than just the query)
            $resourcesnotadded=add_saved_search_items($usercollection);
            if (!empty($resourcesnotadded))
                {
                ?><script language="Javascript">alert("<?php echo $lang["notapprovedresources"] . implode(", ",$resourcesnotadded);?>");</script><?php
                }
            # Log this
            daily_stat("Add saved search items to collection",0);
            }
        hook("postaddsearch");
        }
	}

$removesearch=getvalescaped("removesearch","");
if ($removesearch!="")
	{
    if (!collection_writeable($usercollection))
        { ?>
        <script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
        }
    else
        {
        hook("preremovesearch");
        #remove saved search
        remove_saved_search($usercollection,$removesearch);
        hook("postremovesearch");
        }
	}
	
$addsmartcollection=getvalescaped("addsmartcollection",-1);
if ($addsmartcollection!=-1)
	{
	
	# add collection which autopopulates with a saved search 
	add_smart_collection();
		
	# Log this
	daily_stat("Added smart collection",0);	
	}
	
$research=getvalescaped("research","");
if ($research!="")
	{
	hook("preresearch");
	$col=get_research_request_collection($research);
	if ($col==false)
		{
		$rr=get_research_request($research);
		$name="Research: " . $rr["name"];  # Do not translate this string, the collection name is translated when displayed!
		$new=create_collection ($rr["user"],$name,1);
		set_user_collection($userref,$new);
		set_research_collection($research,$new);
		}
	else
		{
		set_user_collection($userref,$col);
		}
	hook("postresearch");
	}
	
hook("processusercommand");
?>


<?php 
$searches=get_saved_searches($usercollection);

// Note that the full search is done initially. The time saved is due to content drawing and transfer.
$result=do_search("!collection" . $usercollection,"","relevance",0);
$count_result=count($result);


$hook_count=hook("countresult","",array($usercollection,$count_result));if (is_numeric($hook_count)) {$count_result=$hook_count;} # Allow count display to be overridden by a plugin (e.g. that adds it's own resources from elsewhere e.g. ResourceConnect).
$feedback=$cinfo["request_feedback"];



# E-commerce functionality. Work out total price, if $basket_stores_size is enabled so that they've already selected a suitable size.
$totalprice=0;
if (($userrequestmode==2 || $userrequestmode==3) && $basket_stores_size)
	{
	foreach ($result as $resource)
		{
		# For each resource in the collection, fetch the price (set in config.php, or config override for group specific pricing)
		$id=$resource["purchase_size"];
		if ($id=="") {$id="hpr";} # Treat original size as "hpr".
		if (array_key_exists($id,$pricing))
			{
			$price=$pricing[$id];
			
			# Pricing adjustment hook (for discounts or other price adjustments plugin).
			$priceadjust=hook("adjust_item_price","",array($price,$resource["ref"],$resource["purchase_size"]));
			if ($priceadjust!==false)
				{
				$price=$priceadjust;
				}
			
			$totalprice+=$price;
			}
		else
			{
			$totalprice+=999; # Error.
			}
		}
	}

if(!hook("clearmaincheckboxesfromcollectionframe")){
	if ($use_checkboxes_for_selection ){?>
	
	<script type="text/javascript">
	var checkboxes=jQuery('input.checkselect');
	//clear all
	checkboxes.each(function(box){
		jQuery(checkboxes[box]).attr('checked',false);
		jQuery(checkboxes[box]).change();
	});
	</script>
<?php }
} // end hook clearmaincheckboxesfromcollectionframe

if(!hook("updatemaincheckboxesfromcollectionframe")){
		
	if ($use_checkboxes_for_selection){?>
	<script type="text/javascript"><?php
	# update checkboxes in main window
	for ($n=0;$n<count($result);$n++)			
		{
		$ref=$result[$n]["ref"];
		?>
		if (jQuery('#check<?php echo htmlspecialchars($ref) ?>')){
		jQuery('#check<?php echo htmlspecialchars($ref) ?>').attr('checked',true);
		}
			
	<?php }
	} ?></script><?php
}# end hook updatemaincheckboxesfromcollectionframe

?><div><?php

if (true) { // draw both

?><div id="CollectionMaxDiv" style="display:<?php if ($thumbs=="show") { ?>block<?php } else { ?>none<?php } ?>"><?php 
# ---------------------------- Maximised view -------------------------------------------------------------------------
if (hook("replacecollectionsmax", "", array($k!="")))
	{
	# ------------------------ Hook defined view ----------------------------------
	}
else if ($basket)
	{
	# ------------------------ Basket Mode ----------------------------------------
	?>
	<div id="CollectionMenu">
	<h2><?php echo $lang["yourbasket"] ?></h2>
	<form action="<?php echo $baseurl_short?>pages/purchase.php">

	<?php if ($count_result==0) { ?>
	<p><br /><?php echo $lang["yourbasketisempty"] ?></p><br /><br /><br />
	<?php } else { ?>
	<p><br /><?php if ($count_result==1) {echo $lang["yourbasketcontains-1"];} else {echo str_replace("%qty",$count_result,$lang["yourbasketcontains-2"]);} ?>

	<?php if ($basket_stores_size) {
	# If they have already selected the size, we can show a total price here.
	?><br/><?php echo $lang["totalprice"] ?>: <?php echo $currency_symbol . " " . number_format($totalprice,2) ?><?php } ?>
	
	</p>

	<p style="padding-bottom:10px;"><input type="submit" name="buy" value="&nbsp;&nbsp;&nbsp;<?php echo $lang["buynow"] ?>&nbsp;&nbsp;&nbsp;" /></p>
	<?php } ?>
	<?php if (!$disable_collection_toggle) { ?>
    <a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;">&gt; <?php echo $lang["hidethumbnails"]?></a>
  <?php } ?>
	<a href="<?php echo $baseurl_short?>pages/purchases.php" onclick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["viewpurchases"]?></a>


	</form>
	</div>
	<?php	
	}
elseif ($k!="")
	{
	# ------------- Anonymous access, slightly different display ------------------
	$tempcol=$cinfo;
	?>
<div id="CollectionMenu">
  <h2><?php echo i18n_get_collection_name($tempcol)?></h2>
	<br />
	<div class="CollectionStatsAnon">
	<?php echo $lang["created"] . " " . nicedate($tempcol["created"])?><br />
  	<?php echo $count_result . " " . $lang["youfoundresources"]?><br />
  	</div>
    <?php if ((isset($zipcommand) || $collection_download) && $count_result>0) { ?>
	<a href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k) ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k)?>" onclick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["action-download"]?></a>
	<?php }
     if ($feedback) {?><br /><br /><a onclick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>">&gt;&nbsp;<?php echo $lang["sendfeedback"]?></a><?php } ?>
    <?php if ($count_result>0 && checkperm("q"))
    	{ 
		# Ability to request a whole collection (only if user has restricted access to any of these resources)
		$min_access=collection_min_access($result);
		if ($min_access!=0)
			{
		    ?>
		    <br/><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_request.php?ref=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>">&gt; <?php echo $lang["requestall"]?></a>
		    <?php
		    }
	    }
	?>
	<?php if (!$disable_collection_toggle) { ?>
    <br/><a  id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;">&gt; <?php echo $lang["hidethumbnails"]?></a>
  <?php } ?>
</div>
<?php 
} else { 
# -------------------------- Standard display --------------------------------------------
?>
<?php if ($collection_dropdown_user_access_mode){?>
<div id="CollectionMenuExp">
<?php } else { ?>
<div id="CollectionMenu">
<?php } ?>

<?php if (!hook("thumbsmenu")) { ?>
  <?php if (!hook("replacecollectiontitle") && !hook("replacecollectiontitlemax")) { ?><h2 id="CollectionsPanelHeader"><?php if ($collections_compact_style){?><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_manage.php"><?php } ?><?php echo $lang["mycollections"]?><?php if ($collections_compact_style){?></a><?php } ?></h2><?php } ?>
  <form method="get" id="colselect" onsubmit="newcolname=encodeURIComponent(jQuery('#entername').val());CollectionDivLoad('<?php echo $baseurl_short?>pages/collections.php?collection=-1&k=<?php echo urlencode($k) ?>&entername='+newcolname);return false;">
		<div class="SearchItem" style="padding:0;margin:0;"><?php echo $lang["currentcollection"]?>&nbsp;(<strong><?php echo $count_result?></strong>&nbsp;<?php if ($count_result==1){echo $lang["item"];} else {echo $lang["items"];}?>): 
		<select name="collection" id="collection" onchange="if(document.getElementById('collection').value==-1){document.getElementById('entername').style.display='block';document.getElementById('entername').focus();return false;} <?php if (!checkperm("b")){ ?>ChangeCollection(jQuery(this).val(),'<?php echo urlencode($k)  ?>');<?php } else { ?>document.getElementById('colselect').submit();<?php } ?>" <?php if ($collection_dropdown_user_access_mode){?>class="SearchWidthExp"<?php } else { ?> class="SearchWidth"<?php } ?>>
		<?php
		$list=get_user_collections($userref);
		$found=false;
		for ($n=0;$n<count($list);$n++)
			{
			if(in_array($list[$n]['ref'],$hidden_collections)){continue;}

            if ($collection_dropdown_user_access_mode){    
                $colusername=$list[$n]['fullname'];
                
                # Work out the correct access mode to display
                if (!hook('collectionaccessmode')) {
                    if ($list[$n]["public"]==0){
                        $accessmode= $lang["private"];
                    }
                    else{
                        if (strlen($list[$n]["theme"])>0){
                            $accessmode= $lang["theme"];
                        }
                    else{
                            $accessmode= $lang["public"];
                        }
                    }
                }
            }
                

			#show only active collections if a start date is set for $active_collections 
			if (strtotime($list[$n]['created']) > ((isset($active_collections))?strtotime($active_collections):1))
					{ ?>
			<option value="<?php echo $list[$n]["ref"]?>" <?php if ($usercollection==$list[$n]["ref"]) {?> 	selected<?php $found=true;} ?>><?php echo i18n_get_collection_name($list[$n]) ?> <?php if ($collection_dropdown_user_access_mode){echo htmlspecialchars("(". $colusername."/".$accessmode.")"); } ?></option>
			<?php }
			}
		if ($found==false)
			{
			# Add this one at the end, it can't be found
			$notfound=$cinfo;
			if ($notfound!==false)
				{
				?>
				<option selected><?php echo i18n_get_collection_name($notfound) ?></option>
				<?php
				}
			}
		
		if ($collection_allow_creation) { ?>
			<option value="-1">(<?php echo $lang["createnewcollection"]?>)</option>
		<?php } ?>

		</select>
		<input type=text id="entername" name="entername" style="display:none;" placeholder="<?php echo $lang['entercollectionname']?>" <?php if ($collection_dropdown_user_access_mode){?>class="SearchWidthExp"<?php } else { ?> class="SearchWidth"<?php } ?>>
		</div>			
  </form>

  
  <?php if ($collections_compact_style){
	 hook("beforecollectiontoolscolumn");?>
	 <?php if (!hook("modifycompacttoolslabel")){ echo "<div style='height:5px;'></div>".$lang['tools'].":";}
    draw_compact_style_selector($cinfo['ref']);?>	

     <?php hook("aftercollectionscompacttools");?>

		 
     <div class="collectionscompactstylespacer"></div>
     
     <a id="toggleThumbsLink" onClick="ToggleThumbs();return false;" href="#">&gt;&nbsp;<?php echo $lang["hidethumbnails"]?></a><?php 
    }
    else { ?><ul>
  	<?php if ((!collection_is_research_request($usercollection)) || (!checkperm("r"))) { ?>
    <?php if (checkperm("s")) { ?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_manage.php">&gt; <?php echo $lang["managemycollections"];?></a></li>
	<?php if ($contact_sheet==true) { ?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/contactsheet_settings.php?ref=<?php echo urlencode($usercollection) ?>">&gt;&nbsp;<?php echo $lang["contactsheet"]?></a></li><?php } ?>
    <?php if ($allow_share) { ?>
    	<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_share.php?ref=<?php echo urlencode($usercollection) ?>">&gt; <?php echo $lang["share"]?></a></li><?php 
    	hook('aftershareinbottomtoolbar', '', array($usercollection));
    } ?>
    <?php if (($userref==$cinfo["user"]) || (checkperm("h"))) {?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_edit.php?ref=<?php echo urlencode($usercollection) ?>">&gt;&nbsp;<?php echo $allow_share?$lang["action-edit"]:$lang["editcollection"]?></a></li><?php } ?>
	<?php if ($preview_all){?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/preview_all.php?ref=<?php echo urlencode($usercollection) ?>">&gt;&nbsp;<?php echo $lang["preview_all"]?></a></li><?php } ?>
	<?php hook("collectiontool2");?>
    <?php if ($feedback) {?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>">&gt;&nbsp;<?php echo $lang["sendfeedback"]?></a></li><?php } ?>
    
    <?php } ?>
    <?php } else {
	if (!hook("replacecollectionsresearchlinks")){	
    $research=sql_value("select ref value from research_request where collection='$usercollection'",0);	
	?>
    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_research.php">&gt; <?php echo $lang["manageresearchrequests"]?></a></li>    
    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_research_edit.php?ref=<?php echo urlencode($research) ?>">&gt; <?php echo $lang["editresearchrequests"]?></a></li>    
    <?php } /* end hook replacecollectionsresearchlinks */ ?>
	<?php } ?>
    
    <?php 
    # If this collection is (fully) editable, then display an extra edit all link
    if ((count($result)>0) && $show_edit_all_link && (!$edit_all_checkperms || allow_multi_edit($result))) 
    	{ ?>
	    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection)?>">&gt; <?php echo $lang["viewall"]?></a></li>
	    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?collection=<?php echo urlencode($usercollection) ?>">&gt; <?php echo $lang["action-editall"]?></a></li>
	    <?php 
	    
	    } 
	else { ?>
    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection)?>">&gt; <?php echo $lang["viewall"]?></a></li>
    <?php }
    echo (isset($emptycollection) && collection_writeable($usercollection)) ? '<li><a href="'.$baseurl_short.'pages/collections.php?ref='.urlencode($usercollection).'&removeall=true&submitted=removeall&ajax=true" onclick="if(!confirm(\''.$lang['emptycollectionareyousure'].'\')){return false;}return CollectionDivLoad(this);"> &gt; '.$lang["emptycollection"].'</a></li>' : "";
     if ($count_result>0)
    	{ 
		# Ability to request a whole collection (only if user has restricted access to any of these resources)
		$min_access=collection_min_access($result);
		if ($min_access!=0)
			{
		    ?>
		    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_request.php?ref=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>">&gt; <?php echo $lang["requestall"]?></a></li>
		    <?php
		    }
	    }
	?>
    
    <?php if ((isset($zipcommand) || $collection_download) && $count_result>0) { ?>
    <li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k) ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k)?>">&gt; <?php echo $lang["action-download"]?></a></li>
	<?php } ?>
	<?php hook("collectiontool");?>
	<?php if (!$disable_collection_toggle) { ?>
    <li><a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;">&gt; <?php echo $lang["hidethumbnails"]?></a></li>
  <?php } ?>
</ul><?php } /* end compact collections */?>
</div>
<?php } ?>


<?php } ?>

<!--Resource panels-->
<?php if ($collection_dropdown_user_access_mode){?>
<div id="CollectionSpace" class="CollectionSpaceExp">
<?php } else { ?>
<div id="CollectionSpace" class="CollectionSpace">
<?php } ?>

<?php 
# Loop through saved searches
if (isset($cinfo['savedsearch'])&&$cinfo['savedsearch']==null  && $k=='')
	{ // don't include saved search item in result if this is a smart collection  

	# Setting the save search icon
	$folderurl=$baseurl."/gfx/images/";
	$iconurl=$folderurl."save-search"."_".$language.".gif";
	if (!file_exists($iconurl))
		{
		# A language specific icon is not found, use the default icon
		$iconurl = $folderurl . "save-search.gif";
		}

	for ($n=0;$n<count($searches);$n++)			
		{
		$ref=$searches[$n]["ref"];
		$url=$baseurl_short."pages/search.php?search=" . urlencode($searches[$n]["search"]) . "&restypes=" . urlencode($searches[$n]["restypes"]) . "&archive=" . urlencode($searches[$n]["archive"]);
		?>
		<!--Resource Panel-->
		<div class="CollectionPanelShell">
		<table border="0" class="CollectionResourceAlign"><tr><td>
		<a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $url?>"><img border=0 width=56 height=75 src="<?php echo $iconurl?>"/></a></td>
		</tr></table>
		<?php if(!hook('replacesavedsearchtitle')){?>
		<div class="CollectionPanelInfo"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $url?>"><?php echo tidy_trim($lang["savedsearch"],(13-strlen($n+1)))?> <?php echo $n+1?></a>&nbsp;</div><?php } ?>
		<?php if(!hook('replaceremovelink_savedsearch')){?>
		<div class="CollectionPanelInfo"><a onclick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?removesearch=<?php echo urlencode($ref) ?>&nc=<?php echo time()?>">x <?php echo $lang["action-remove"]?>
		</a></div>	<?php } ?>			
		</div>
		<?php		
		}
}		

# Loop through thumbnails
if ($count_result>0) 
	{
	# loop and display the results
	for ($n=0;$n<count($result);$n++)					
		{
		$ref=$result[$n]["ref"];
		?>
<?php if (!hook("resourceview")) { ?>
		<!--Resource Panel-->
		<div class="CollectionPanelShell" id="ResourceShell<?php echo urlencode($ref) ?>">
		<?php if (!hook("rendercollectionthumb")){?>
		<?php $access=get_resource_access($result[$n]);
		$use_watermark=check_use_watermark();?>
		<table border="0" class="CollectionResourceAlign"><tr><td>
		<a style="position:relative;" onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode("!collection" . $usercollection)?>&k=<?php echo $k?>&curpos=<?php echo $n ?>"><?php if ($result[$n]["has_image"]==1) { 
		
		$colimgpath=get_resource_path($ref,false,"col",false,$result[$n]["preview_extension"],-1,1,$use_watermark,$result[$n]["file_modified"])
		?>
		<img border=0 src="<?php echo $colimgpath?>" class="CollectImageBorder" <?php if (!$infobox) { ?>title="<?php echo htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))?>" alt="<?php echo htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))?>"<?php } ?> 
		<?php if ($infobox) { ?>onMouseOver="InfoBoxSetResource(<?php echo $ref?>);" onMouseOut="InfoBoxSetResource(0);"<?php } ?>
		/>
			<?php
		
		} else { ?><img border=0 src="<?php echo $baseurl_short?>gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true) ?>"
		<?php if ($infobox) { ?>onMouseOver="InfoBoxSetResource(<?php echo $ref?>);" onMouseOut="InfoBoxSetResource(0);"<?php } ?>
		/><?php } ?><?php hook("aftersearchimg","",array($result[$n]))?></a></td>
		</tr></table>
		<?php } /* end hook rendercollectionthumb */?>
		
		<?php 

		$title=$result[$n]["field".$view_title_field];	
		$title_field=$view_title_field;
		if (isset($metadata_template_title_field) && isset($metadata_template_resource_type))
			{
			if ($result[$n]['resource_type']==$metadata_template_resource_type)
				{
				$title=$result[$n]["field".$metadata_template_title_field];
				$title_field=$metadata_template_title_field;
				}	
			}	
		$field_type=sql_value("select type value from resource_type_field where ref=$title_field","");
		if($field_type==8){
			$title=strip_tags($title);
			$title=str_replace("&nbsp;"," ",$title);
		}
		?>	
		<?php if (!hook("replacecolresourcetitle")){?>
		<div class="CollectionPanelInfo"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode("!collection" . $usercollection)?>&k=<?php echo urlencode($k) ?>" <?php if (!$infobox) { ?>title="<?php echo htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))?>"<?php } ?> ><?php echo htmlspecialchars(tidy_trim(i18n_get_translated($title),14));?></a>&nbsp;</div>
		<?php } ?>
		
		<?php if ($k!="" && $feedback) { # Allow feedback for external access key users
		?>
		<div class="CollectionPanelInfo">
		<span class="IconComment <?php if ($result[$n]["commentset"]>0) { ?>IconCommentAnim<?php } ?>"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_comment.php?ref=<?php echo urlencode($ref) ?>&collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><img src="../gfx/interface/sp.gif" alt="" width="14" height="12" /></a></span>		
		</div>
		<?php } ?>
	
		<?php if ($k=="") { ?><div class="CollectionPanelInfo">
		<?php if (($feedback) || (($collection_reorder_caption || $collection_commenting))) { ?>
		<span class="IconComment <?php if ($result[$n]["commentset"]>0) { ?>IconCommentAnim<?php } ?>"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_comment.php?ref=<?php echo urlencode($ref) ?>&collection=<?php echo urlencode($usercollection) ?>"><img src="../gfx/interface/sp.gif" alt="" width="14" height="12" /></a></span>		
		<?php } ?>

		<?php if (!isset($cinfo['savedsearch'])||(isset($cinfo['savedsearch'])&&$cinfo['savedsearch']==null)){ // add 'remove' link only if this is not a smart collection 
			?>
		<?php if (!hook("replaceremovelink")){?>
		<a class="CollectionResourceRemove" onclick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?remove=<?php echo urlencode($ref) ?>&nc=<?php echo time()?>">x <?php echo $lang["action-remove"]?></a>
		<?php
				} //end hook replaceremovelink 
			} # End of remove link condition 
		?></div><?php 
		} # End of k="" condition 
		 ?>
		</div>
		<?php
		} # End of ResourceView hook
	  } # End of loop through resources
	} # End of results condition

	
if ($count_result>$max_collection_thumbs){
	?><div class="CollectionPanelShell"><table border="0" class="CollectionResourceAlign"><tr><td><img/></td>
		</tr></table>
		<div class="CollectionPanelInfo"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $usercollection?>&k=<?php echo urlencode($k) ?>"><?php echo $lang['viewall']?>...</a></div>
		
		<?php
	}

?></div><?php		
# Plugin for additional collection listings	(deprecated)
if (file_exists("plugins/collection_listing.php")) {include "plugins/collection_listing.php";}

hook("thumblistextra");
?>
</div>  
<?php 

# Add the infobox.
?>
	<div id="InfoBoxCollection"><div id="InfoBoxCollectionInner"> </div></div></div>
	<?php


}


	?><div id="CollectionMinDiv" style="display:<?php if ($thumbs=="hide") { ?>block<?php } else { ?>none<?php } ?>"><?php 
	if (true)
	{
	# ------------------------- Minimised view
	?>
	<!--Title-->	
	<?php if (!hook("nothumbs")) {

	if (hook("replacecollectionsmin", "", array($k!="")))
		{
		# ------------------------ Hook defined view ----------------------------------
		}
	else if ($basket)
		{
		# ------------------------ Basket Mode ----------------------------------------
		?>
		<div id="CollectionMinTitle"><h2><?php echo $lang["yourbasket"] ?></h2></div>
		<div id="CollectionMinRightNav">
		<form action="<?php echo $baseurl_short?>pages/purchase.php">
		<ul>
		
		<?php if ($count_result==0) { ?>
		<li><?php echo $lang["yourbasketisempty"] ?></li>
		<?php } else { ?>

		<?php if ($basket_stores_size) {
		# If they have already selected the size, we can show a total price here.
		?><li><?php echo $lang["totalprice"] ?>: <?php echo $currency_symbol . " " . number_format($totalprice,2) ?><?php } ?></li>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection)?>"><?php echo $lang["viewall"]?></a></li>
		<li><input type="submit" name="buy" value="&nbsp;&nbsp;&nbsp;<?php echo $lang["buynow"] ?>&nbsp;&nbsp;&nbsp;" /></li>
		<?php } ?>
	  <?php if (!$disable_collection_toggle) { ?>
		<?php /*if ($count_result<=$max_collection_thumbs) { */?><li><a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang["showthumbnails"]?></a></li><?php /*}*/ ?>
	  <?php } ?>
		<li><a href="<?php echo $baseurl_short?>pages/purchases.php" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang["viewpurchases"]?></a></li>
		</ul>
		</form>

		</div>
		<?php	
		}
	elseif ($k!="")
		{
		# Anonymous access, slightly different display
		$tempcol=$cinfo;
		?>
	<div id="CollectionMinTitle" class="ExternalShare"><h2><?php echo i18n_get_collection_name($tempcol)?></h2></div>
	<div id="CollectionMinRightNav" class="ExternalShare">
		<?php if(!hook("replaceanoncollectiontools")){ ?>
		<?php if ((isset($zipcommand) || $collection_download) && $count_result>0) { ?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k) ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k)?>"><?php echo $lang["action-download"]?></a></li>
		<?php } ?>
		<?php if ($feedback) {?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><?php echo $lang["sendfeedback"]?></a></li><?php } ?>
		<?php if ($count_result>0)
			{ 
			# Ability to request a whole collection (only if user has restricted access to any of these resources)
			$min_access=collection_min_access($result);
			if ($min_access!=0)
				{
				?>
				<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_request.php?ref=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><?php echo 	$lang["requestall"]?></a></li>
				<?php
				}
			}
		?>
	  <?php if (!$disable_collection_toggle) { ?>
		<li><a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang["showthumbnails"]?></a></li>
	  <?php } ?>
	  <?php } # end hook("replaceanoncollectiontools") ?>
	</div>
	<?php 
	} else { 
	?>

	<div id="CollectionMinTitle" class="ExternalShare"><?php if (!hook("replacecollectiontitle") && !hook("replacecollectiontitlemin")) { ?><h2><?php if ($collections_compact_style){?><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_manage.php"><?php } ?><?php echo $lang["mycollections"]?><?php if ($collections_compact_style){?></a><?php }?></h2><?php } ?></div>

	<!--Menu-->	
	<div id="CollectionMinRightNav">
	<?php hook("addcompacttoolslabelmin");?>
	<div id="MinSearchItem">
	  <?php if ($collections_compact_style){
	    hook("beforetogglethumbs");
	  	 if (/*($count_result<=$max_collection_thumbs) && */!$disable_collection_toggle) { ?>&nbsp;&nbsp;<a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;">&gt;&nbsp;<?php echo $lang["showthumbnails"]?></a><?php } 
		}
		else { ?>
		<ul>
		<?php if ((!collection_is_research_request($usercollection)) || (!checkperm("r"))) { ?>
		<?php if (checkperm("s")) { ?><?php if (!$collections_compact_style){?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_manage.php"><?php echo $lang["managemycollections"]?></a></li><?php } ?>
		<?php if ($contact_sheet==true) { ?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/contactsheet_settings.php?ref=<?php echo urlencode($usercollection) ?>">&nbsp;<?php echo $lang["contactsheet"]?></a></li>
		<?php } ?>
		<?php if ($allow_share) { ?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_share.php?ref=<?php echo urlencode($usercollection) ?>"><?php echo $lang["share"]?></a></li><?php } ?>
		
		<?php if (($userref==$cinfo["user"]) || (checkperm("h"))) {?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_edit.php?ref=<?php echo urlencode($usercollection) ?>">&nbsp;<?php echo $allow_share?$lang["action-edit"]:$lang["editcollection"]?></a></li><?php } ?>

		<?php if ($preview_all){?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/preview_all.php?ref=<?php echo urlencode($usercollection) ?>"><?php echo $lang["preview_all"]?></a></li><?php } ?>
		<?php hook('collectiontool2min');?>
		<?php if ($feedback) {?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection)?>&k=<?php echo urlencode($k) ?>">&nbsp;<?php echo $lang["sendfeedback"]?></a></li><?php } ?>
		
		<?php } ?>
		<?php } else {
		if (!hook("replacecollectionsresearchlinks")){	
		$research=sql_value("select ref value from research_request where collection='$usercollection'",0);	
		?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_research.php"><?php echo $lang["manageresearchrequests"]?></a></li>   
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_research_edit.php?ref=<?php echo urlencode($research) ?>"><?php echo $lang["editresearchrequests"]?></a></li>         
		<?php } /* end hook replacecollectionsresearchlinks */ ?>	
		<?php } ?>
		<?php 
		# If this collection is (fully) editable, then display an extra edit all link
		if ((count($result)>0) && checkperm("e" . $result[0]["archive"]) && allow_multi_edit($result)) { ?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection)?>"><?php echo $lang["viewall"]?></a></li>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?collection=<?php echo $usercollection?>"><?php echo $lang["action-editall"]?></a></li>    
		<?php 
		 } else { ?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection)?>"><?php echo $lang["viewall"]?></a></li>
		<?php } 
		echo (isset($emptycollection) && collection_writeable($usercollection)) ? '<li><a href="'.$baseurl_short.'pages/collections.php?ref='.urlencode($usercollection).'&removeall=true&submitted=removeall&ajax=true" onclick="if(!confirm(\''.$lang['emptycollectionareyousure'].'\')){return false;}return CollectionDivLoad(this);">'.$lang["emptycollection"].'</a></li>' : "";
		if ((isset($zipcommand) || $collection_download) && $count_result>0) { ?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo $k?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k)?>"><?php echo $lang["action-download"]?></a></li>
		<?php } ?>
		<?php if ($count_result>0 && $k=="" && checkperm("q"))
			{ 
			# Ability to request a whole collection (only if user has restricted access to any of these resources)
			$min_access=collection_min_access($result);
			if ($min_access!=0)
				{
				?>
				<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_request.php?ref=<?php echo urlencode($usercollection) ?>"><?php echo $lang["action-request"]?></a></li>
				<?php
				}
			}
		?>
		<?php hook("collectiontoolmin");?>
		<?php if (/*($count_result<=$max_collection_thumbs) && */!$disable_collection_toggle) { ?><li><a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang["showthumbnails"]?></a></li><?php } ?>
		
	  </ul>
	  <?php } ?>
	</div>
	</div>
	<!--Collection Dropdown-->
	<?php if(!hook("replace_collectionmindroptitle")){?>
	<div id="CollectionMinDropTitle"><?php echo $lang["currentcollection"]?>:&nbsp;</div>
    <?php } # end hook replace_collectionmindroptitle ?>				
	<div id="CollectionMinDrop">
	 <form method="get" id="colselect2" onsubmit="newcolname=encodeURIComponent(jQuery('#entername2').val());CollectionDivLoad('<?php echo $baseurl_short?>pages/collections.php?thumbs=<?php echo urlencode($thumbs) ?>&collection=-1&k=<?php echo urlencode($k) ?>&entername='+newcolname);return false;">
			<div class="MinSearchItem" id="MinColDrop">
			
			<input type=text id="entername2" name="entername" placeholder="<?php echo $lang['entercollectionname']?>" style="display:inline;display:none;" class="SearchWidthExp">
			</div><script>jQuery('#collection').clone().attr('id','collection2').attr('onChange',"if(document.getElementById('collection2').value==-1){document.getElementById('entername2').style.display='inline';document.getElementById('entername2').focus();return false;}<?php if (!checkperm("b")){ ?>ChangeCollection(jQuery(this).val(),'<?php echo urlencode($k) ?>');<?php } else { ?>document.getElementById('colselect2').submit();<?php } ?>").prependTo('#MinColDrop');</script>		
	  </form>
	</div>
	<?php } ?>
	<?php } ?>
	<!--Collection Count-->	
	<?php if(!hook("replace_collectionminitems")){?>
	<div id="CollectionMinitems"><strong><?php echo $count_result?></strong>&nbsp;<?php if ($count_result==1){echo $lang["item"];} else {echo $lang["items"];}?></div>
	<?php } # end hook replace_collectionminitems ?>
	</div>
	<?php } ?>

<?php draw_performance_footer();?>

	</div>
	</body>
	</html>
<?php 
