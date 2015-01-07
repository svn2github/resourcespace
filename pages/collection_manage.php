<?php 
include "../include/db.php";
include "../include/authenticate.php"; if (checkperm("b")){exit("Permission denied");}
#if (!checkperm("s")) {exit ("Permission denied.");}
include "../include/general.php";
include "../include/collections_functions.php";
include "../include/search_functions.php";
include "../include/resource_functions.php";

$offset=getvalescaped("offset",0);
$find=getvalescaped("find",getvalescaped("saved_find",""));setcookie("saved_find",$find, 0, '', '', false, true);
$col_order_by=getvalescaped("col_order_by",getvalescaped("saved_col_order_by","created"));setcookie("saved_col_order_by",$col_order_by, 0, '', '', false, true);
$sort=getvalescaped("sort",getvalescaped("saved_col_sort","ASC"));setcookie("saved_col_sort",$sort, 0, '', '', false, true);
$revsort = ($sort=="ASC") ? "DESC" : "ASC";
# pager
$per_page=getvalescaped("per_page_list",$default_perpage_list,true);setcookie("per_page_list",$per_page, 0, '', '', false, true);

$collection_valid_order_bys=array("fullname","name","ref","count","public");
$modified_collection_valid_order_bys=hook("modifycollectionvalidorderbys");
if ($modified_collection_valid_order_bys){$collection_valid_order_bys=$modified_collection_valid_order_bys;}
if (!in_array($col_order_by,$collection_valid_order_bys)) {$col_order_by="created";} # Check the value is one of the valid values (SQL injection filter)

if (array_key_exists("find",$_POST)) {$offset=0;} # reset page counter when posting

$name=getvalescaped("name","");
if ($name!="" && $collection_allow_creation)
	{
	# Create new collection
	$new=create_collection ($userref,$name);
	set_user_collection($userref,$new);
	refresh_collection_frame();
	
	# Log this
	daily_stat("New collection",$userref);
	
	redirect("pages/collection_edit.php?ref=" . $new);
	}

$delete=getvalescaped("delete","");
if ($delete!="")
	{
	# Delete collection
	delete_collection($delete);

	# Get count of collections
	$c=get_user_collections($userref);
	
	# If the user has just deleted the collection they were using, select a new collection
	if ($usercollection==$delete && count($c)>0)
		{
		# Select the first collection in the dropdown box.
		$usercollection=$c[0]["ref"];
		set_user_collection($userref,$usercollection);
		}

	# User has deleted their last collection? add a new one.
	if (count($c)==0)
		{
		# No collections to select. Create them a new collection.
		$name=get_mycollection_name($userref);
		$usercollection=create_collection ($userref,$name);
		set_user_collection($userref,$usercollection);
		}

	refresh_collection_frame($usercollection);
	}

$removeall=getvalescaped("removeall","");
if ($removeall!=""){
	remove_all_resources_from_collection($removeall);
	refresh_collection_frame($usercollection);
}

$remove=getvalescaped("remove","");
if ($remove!="")
	{
	# Remove someone else's collection from your My Collections
	remove_collection($userref,$remove);
	
	# Get count of collections
	$c=get_user_collections($userref);
	
	# If the user has just removed the collection they were using, select a new collection
	if ($usercollection==$remove && count($c)>0) {
		# Select the first collection in the dropdown box.
		$usercollection=$c[0]["ref"];
		set_user_collection($userref,$usercollection);
	}
	
	refresh_collection_frame();
	}

$add=getvalescaped("add","");
if ($add!="")
	{
	# Add someone else's collection to your My Collections
	add_collection($userref,$add);
	set_user_collection($userref,$add);
	refresh_collection_frame();
	
   	# Log this
	daily_stat("Add public collection",$userref);
	}

$reload=getvalescaped("reload","");
if ($reload!="")
	{
	# Refresh the collection frame (just edited a collection)
	refresh_collection_frame();
	}

$purge=getvalescaped("purge","");
$deleteall=getvalescaped("deleteall","");
if ($purge!="" || $deleteall!="") {
	
	if ($purge!=""){$deletecollection=$purge;}
	if ($deleteall!=""){$deletecollection=$deleteall;}
	
	if (!function_exists("do_search")) {
		include "../include/search_functions.php";
	}
	
	if (!function_exists("delete_resource")) {
		include "../include/resource_functions.php";
	}
	
	# Delete all resources in collection
	if (!checkperm("D")) {
		$resources=do_search("!collection" . $deletecollection);
		for ($n=0;$n<count($resources);$n++) {
			if (checkperm("e" . $resources[$n]["archive"])) {
				delete_resource($resources[$n]["ref"]);	
				collection_log($deletecollection,"D",$resources[$n]["ref"]);
			}
		}
	}
	
	if ($purge!=""){
		# Delete collection
		delete_collection($purge);
		# Get count of collections
		$c=get_user_collections($userref);
		
		# If the user has just deleted the collection they were using, select a new collection
		if ($usercollection==$purge && count($c)>0) {
			# Select the first collection in the dropdown box.
			$usercollection=$c[0]["ref"];
			set_user_collection($userref,$usercollection);
		}
	
		# User has deleted their last collection? add a new one.
		if (count($c)==0) {
			# No collections to select. Create them a new collection.
			$name=get_mycollection_name($userref);
			$usercollection=create_collection ($userref,$name);
			set_user_collection($userref,$usercollection);
		}
	}
	refresh_collection_frame($usercollection);
}

$deleteempty=getvalescaped("deleteempty","");
if ($deleteempty!="") {
		
	$collections=get_user_collections($userref);
	$deleted_usercoll = false;
		
	for ($n = 0; $n < count($collections); $n++) {
		// if count is zero and not My Collection and collection is owned by user:
		if ($collections[$n]['count'] == 0 && $collections[$n]['cant_delete'] != 1 && $collections[$n]['user']==$userref) {
			delete_collection($collections[$n]['ref']);
			if ($collections[$n]['ref'] == $usercollection) {
				$deleted_usercoll = true;
			}
		}
				
	}
		
	# Get count of collections
	$c=get_user_collections($userref);
		
	# If the user has just deleted the collection they were using, select a new collection
	if ($deleted_usercoll && count($c)>0) {
		# Select the first collection in the dropdown box.
		$usercollection=$c[0]["ref"];
		set_user_collection($userref,$usercollection);
	}
	
	# User has deleted their last collection? add a new one.
	if (count($c)==0) {
		# No collections to select. Create them a new collection.
		$name=get_mycollection_name($userref);
		$usercollection=create_collection ($userref,$name);
		set_user_collection($userref,$usercollection);
	}
	
	refresh_collection_frame($usercollection);
}

hook('customcollectionmanage');

$removeall=getvalescaped("removeall","");
if ($removeall!=""){
	remove_all_resources_from_collection($removeall);
	refresh_collection_frame($usercollection);
}


include "../include/header.php";
?>
  <div class="BasicsBox">
    <h1><?php echo $lang["managemycollections"]?></h1>
    <p class="tight"><?php echo text("introtext")?></p><br>
<div class="BasicsBox">
    <form method="post" action="<?php echo $baseurl_short?>pages/collection_manage.php">
		<div class="Question">
			<div class="tickset">
			 <div class="Inline"><input type=text name="find" id="find" value="<?php echo htmlspecialchars(unescape($find)); ?>" maxlength="100" class="shrtwidth" /></div>
			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" /></div>
			 <div class="Inline"><input name="Clear" type="button" onclick="document.getElementById('find').value='';submit();" value="&nbsp;&nbsp;<?php echo $lang["clearbutton"]?>&nbsp;&nbsp;" /></div>
			</div>
			<div class="clearerleft"> </div>
		</div>
	</form>
</div>
<?php

$collections=get_user_collections($userref,$find,$col_order_by,$sort);

$results=count($collections);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$jumpcount=1;

# Create an a-z index
$atoz="<div class=\"InpageNavLeftBlock\">";
if ($find=="") {$atoz.="<span class='Selected'>";}
$atoz.="<a href=\"".$baseurl_short."pages/collection_manage.php?col_order_by=name&find=\" onClick=\"return CentralSpaceLoad(this);\">" . $lang["viewall"] . "</a>";
if ($find=="") {$atoz.="</span>";}
$atoz.="&nbsp;&nbsp;&nbsp;&nbsp;";
for ($n=ord("A");$n<=ord("Z");$n++)
	{
	if ($find==chr($n)) {$atoz.="<span class='Selected'>";}
	$atoz.="<a href=\"".$baseurl_short."pages/collection_manage.php?col_order_by=name&find=" . chr($n) . "\" onClick=\"return CentralSpaceLoad(this);\">&nbsp;" . chr($n) . "&nbsp;</a> ";
	if ($find==chr($n)) {$atoz.="</span>";}
	$atoz.=" ";
	}
$atoz.="</div>";

$url=$baseurl_short."pages/collection_manage.php?paging=true&col_order_by=".urlencode($col_order_by)."&sort=".urlencode($sort)."&find=".urlencode($find)."";

	?><div class="TopInpageNav"><div class="TopInpageNavLeft"><?php echo $atoz?> <div class="InpageNavLeftBlock"><?php echo $lang["resultsdisplay"]?>:
  	<?php 
  	for($n=0;$n<count($list_display_array);$n++){?>
  	<?php if ($per_page==$list_display_array[$n]){?><span class="Selected"><?php echo htmlspecialchars($list_display_array[$n]) ?></span><?php } else { ?><a href="<?php echo $url; ?>&per_page_list=<?php echo urlencode($list_display_array[$n])?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars($list_display_array[$n]) ?></a><?php } ?>&nbsp;|
  	<?php } ?>
  	<?php if ($per_page==99999){?><span class="Selected"><?php echo $lang["all"]?></span><?php } else { ?><a href="<?php echo $url; ?>&per_page_list=99999" onClick="return CentralSpaceLoad(this);"><?php echo $lang["all"]?></a><?php } ?>
  	</div> </div><?php pager(false); ?><div class="clearerleft"></div></div><?php	
?>

<form method=post id="collectionform" action="<?php echo $baseurl_short?>pages/collection_manage.php">
<input type=hidden name="delete" id="collectiondelete" value="">
<input type=hidden name="remove" id="collectionremove" value="">
<input type=hidden name="add" id="collectionadd" value="">

<?php

// count how many collections are owned by the user versus just shared, and show at top
$mycollcount = 0;
$othcollcount = 0;
for($i=0;$i<count($collections);$i++){
	if ($collections[$i]['user'] == $userref){
		$mycollcount++;
	} else {
		$othcollcount++;
	}
}

$collcount = count($collections);
echo $collcount==1 ? $lang["total-collections-1"] : str_replace("%number", $collcount, $lang["total-collections-2"]);
echo " " . ($mycollcount==1 ? $lang["owned_by_you-1"] : str_replace("%mynumber", $mycollcount, $lang["owned_by_you-2"])) . "<br />";
# The number of collections should never be equal to zero.
?>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td class="name"><?php if ($col_order_by=="name") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/collection_manage.php?offset=0&col_order_by=name&sort=<?php echo urlencode($revsort)?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["collectionname"]?></a><?php if ($col_order_by=="name") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td class="fullname"><?php if ($col_order_by=="fullname") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/collection_manage.php?offset=0&col_order_by=fullname&sort=<?php echo urlencode($revsort)?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["owner"]?></a><?php if ($col_order_by=="fullname") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td class="ref"><?php if ($col_order_by=="ref") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/collection_manage.php?offset=0&col_order_by=ref&sort=<?php echo urlencode($revsort)?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["id"]?></a><?php if ($col_order_by=="ref") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td class="created"><?php if ($col_order_by=="created") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/collection_manage.php?offset=0&col_order_by=created&sort=<?php echo urlencode($revsort)?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["created"]?></a><?php if ($col_order_by=="created") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td class="count"><?php if ($col_order_by=="count") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/collection_manage.php?offset=0&col_order_by=count&sort=<?php echo urlencode($revsort)?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["itemstitle"]?></a><?php if ($col_order_by=="count") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<?php if (!$hide_access_column){ ?><td class="access"><?php if ($col_order_by=="public") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/collection_manage.php?offset=0&col_order_by=public&sort=<?php echo urlencode($revsort)?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["access"]?></a><?php if ($col_order_by=="public") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td><?php }?>

<td class="collectionin"><?php echo $lang["showcollectionindropdown"] ?></td>

<?php hook("beforecollectiontoolscolumnheader");?>
<td class="tools"><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>
<form method="get" name="colactions" id="colactions" action="<?php echo $baseurl_short?>pages/collection_manage.php">
<?php

for ($n=$offset;(($n<count($collections)) && ($n<($offset+$per_page)));$n++)
	{
    $colusername=$collections[$n]['fullname'];

	?><tr <?php hook("collectionlistrowstyle");?>>
	<td class="name"><div class="ListTitle">
		<a <?php if ($collections[$n]["public"]==1 && (strlen($collections[$n]["theme"])>0)) { ?>style="font-style:italic;"<?php } ?> href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $collections[$n]["ref"])?>" onClick="return CentralSpaceLoad(this);"><?php echo highlightkeywords(i18n_get_collection_name($collections[$n]),$find) ?></a></div></td>
	<td class="fullname"><?php echo htmlspecialchars(highlightkeywords($colusername,$find)) ?></td>
	<td class="ref"><?php echo htmlspecialchars(highlightkeywords($collection_prefix . $collections[$n]["ref"],$find)) ?></td>
	<td class="created"><?php echo htmlspecialchars(nicedate($collections[$n]["created"],true)) ?></td>
	<td class="count"><?php echo htmlspecialchars($collections[$n]["count"]) ?></td>
<?php if (! $hide_access_column){ ?>	<td class="access"><?php
# Work out the correct access mode to display
if (!hook('collectionaccessmode')) {
	if ($collections[$n]["public"]==0){
		echo $lang["private"];
	}
	else{
		if (strlen($collections[$n]["theme"])>0){
			echo $lang["theme"];
		}
	else{
		echo $lang["public"];
		}
	}
}
?></td><?php
}?>

<td class="collectionin"><input type="checkbox" onClick="UpdateHiddenCollections(this, '<?php echo $collections[$n]['ref'] ?>');" <?php if(!in_array($collections[$n]['ref'],$hidden_collections)){echo "checked";}?>></td>


<?php hook("beforecollectiontoolscolumn");?>
	<td class="tools">	
        <div class="ListTools">
        <?php if ($collections_compact_style){
        
        draw_compact_style_selector($collections[$n]['ref']);
        
        } else {
?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $collections[$n]["ref"])?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a>
<!-- No need to check for permission 'b' - this check is done in the beginning of this file -->
	&nbsp;<a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo urlencode($collections[$n]["ref"]); if ($autoshow_thumbs) {echo "&amp;thumbs=show";}?>" onClick="ChangeCollection(<?php echo $collections[$n]["ref"]?>, '');return false;">&gt;&nbsp;<?php echo $lang["action-select"]?></a>
	<?php if (isset($zipcommand) || $collection_download) { ?>
	&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/terms.php?url=<?php echo urlencode("pages/collection_download.php?collection=" . $collections[$n]["ref"]) ?>"
	>&gt;&nbsp;<?php echo $lang["action-download"]?></a>
	<?php } ?>
	
	<?php if ($contact_sheet==true && $manage_collections_contact_sheet_link) { ?>
    &nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/contactsheet_settings.php?ref=<?php echo urlencode($collections[$n]["ref"]) ?>">&gt;&nbsp;<?php echo $lang["contactsheet"]?></a>
	<?php } ?>

	<?php if ($manage_collections_share_link && $allow_share && (checkperm("v") || checkperm ("g"))) { ?> &nbsp;<a href="<?php echo $baseurl_short?>pages/collection_share.php?ref=<?php echo $collections[$n]["ref"]?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["share"]?></a><?php } ?>

	<?php /*Remove Shared Collection*/if ($manage_collections_remove_link && $username!=$collections[$n]["username"])	{?>&nbsp;<a href="#" onclick="if (confirm('<?php echo $lang["removecollectionareyousure"]?>')) {document.getElementById('collectionremove').value='<?php echo urlencode($collections[$n]["ref"]) ?>';document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang["action-remove"]?></a><?php } ?>

	<?php if ((($username==$collections[$n]["username"]) || checkperm("h")) && ($collections[$n]["cant_delete"]==0)) {?>&nbsp;<a href="#" onclick="if (confirm('<?php echo $lang["collectiondeleteconfirm"]?>')) {document.getElementById('collectiondelete').value='<?php echo urlencode($collections[$n]["ref"]) ?>';CentralSpacePost(document.getElementById('collectionform'),false);} return false;">&gt;&nbsp;<?php echo $lang["action-delete"]?></a><?php } ?>

	<?php if ($collection_purge){ 
		if ($n == 0) {
			?><input type=hidden name="purge" id="collectionpurge" value=""><?php 
		}

		if (checkperm("e0") && $collections[$n]["cant_delete"] == 0) {
			?>&nbsp;<a href="#" onclick="if (confirm('<?php echo $lang["purgecollectionareyousure"]?>')) {document.getElementById('collectionpurge').value='<?php echo urlencode($collections[$n]["ref"]) ?>';document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang["purgeanddelete"]?></a><?php 
		} 
	}
	?>
	<?php hook('additionalcollectiontool') ?>

	<?php if (($username==$collections[$n]["username"]) || (checkperm("h"))) {?>&nbsp;<a href="<?php echo $baseurl_short?>pages/collection_edit.php?ref=<?php echo urlencode($collections[$n]["ref"]) ?>" onClick="return CentralSpaceLoad(this,true);" >&gt;&nbsp;<?php echo $lang["action-edit"]?></a><?php } ?>

    <?php     
    # If this collection is (fully) editable, then display an edit all link
    if ($show_edit_all_link && ($collections[$n]["count"] > 0))
	{
	    if (!$edit_all_checkperms || allow_multi_edit($collections[$n]["ref"])) { ?>&nbsp;<a href="<?php echo $baseurl_short?>pages/edit.php?collection=<?php echo urlencode($collections[$n]["ref"]) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["action-editall"]?></a>&nbsp;<?php } 
	}
	?>

	<?php if (($username==$collections[$n]["username"]) || (checkperm("h"))) {?><a href="<?php echo $baseurl_short?>pages/collection_log.php?ref=<?php echo urlencode($collections[$n]["ref"]) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["log"]?></a><?php } ?>

	<?php hook("addcustomtool"); ?>
	
	</td>
	</tr><?php
	}
}

	?>
	<input type=hidden name="deleteempty" id="collectiondeleteempty" value="">
	
	<?php if ($collections_delete_empty){
        $use_delete_empty=false;
        //check if delete empty is relevant
        foreach ($collections as $collection){
            if ($collection['count']==0){$use_delete_empty=true;}
        }
        if ($use_delete_empty){
        ?>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td></td><?php if (!$hide_access_column){?><td>&nbsp;</td><?php } ?><?php hook("addcollectionmanagespacercolumn");?><td><div class="ListTools">&nbsp;<a href="#" onclick="if (confirm('<?php echo $lang["collectionsdeleteemptyareyousure"]?>')) {document.getElementById('collectiondeleteempty').value='yes';document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang["collectionsdeleteempty"]?></a></div></td></tr>
        <?php }
    }
?>
</table>
</div>

</form>
<div class="BottomInpageNav"><?php pager(false); ?></div>
</div>

<!--Create a collection-->
<?php if ($collection_allow_creation) { ?>
	<div class="BasicsBox">
		<h1><?php echo $lang["createnewcollection"]?></h1>
		<p class="tight"><?php echo text("newcollection")?></p>
		<form method="post" action="<?php echo $baseurl_short?>pages/collection_manage.php">
			<div class="Question">
				<label for="newcollection"><?php echo $lang["collectionname"]?></label>
				<div class="tickset">
				 <div class="Inline"><input type=text name="name" id="newcollection" value="" maxlength="100" class="shrtwidth"></div>
				 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" /></div>
				</div>
			<div class="clearerleft"> </div>
			</div>
		</form>
	</div>
<?php } ?>
 
<!--Find a collection-->
<?php if (!$public_collections_header_only){?>
<?php if($enable_public_collections){?>
<div class="BasicsBox">
    <h1><?php echo $lang["findpubliccollection"]?></h1>
    <p class="tight"><?php echo text("findpublic")?></p>
    <p><a href="<?php echo $baseurl_short?>pages/collection_public.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["findpubliccollection"]?>&nbsp;&gt;</a></p>
</div>
<?php } ?>
<?php } ?>
<div class="BasicsBox">
    <h1><?php echo $lang["view_shared_collections"]?></h1>
    <p><a href="<?php echo $baseurl_short?>pages/view_shares.php" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["view_shared_collections"]?></a></p>
</div>
<?php

include "../include/footer.php";
?>
