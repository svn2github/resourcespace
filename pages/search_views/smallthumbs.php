<?php if (!hook("renderresultsmallthumb")) { ?>

<!--Resource Panel-->
<div class="ResourcePanelShellSmall" <?php if ($display_user_rating_stars && $k==""){?> <?php } ?>id="ResourceShell<?php echo htmlspecialchars($ref)?>">
	<div class="ResourcePanelSmall">
		<?php  if ($resource_type_icons) { ?>
		<div class="ResourceTypeIcon IconResourceType<?php echo $result[$n]["resource_type"];  ?>"></div>
		<?php }  ?>
		<?php if (!hook("renderimagesmallthumb")) {
		$access=get_resource_access($result[$n]);
		$use_watermark=check_use_watermark();

		# Work out the preview image path
		$col_url=get_resource_path($ref,false,"col",false,$result[$n]["preview_extension"],-1,1,$use_watermark,$result[$n]["file_modified"]);
		if (isset($result[$n]["col_url"])) {$col_url=$result[$n]["col_url"];} # If col_url set in data, use instead, e.g. by manipulation of data via process_search_results hook

		?>
		<table border="0" class="ResourceAlignSmall<?php hook('searchdecorateresourcetableclass'); ?>">
		<?php hook("resourcetop")?>
		<tr><td>
		<a style="position:relative" href="<?php echo $url?>"  onClick="return CentralSpaceLoad(this,true);" <?php if (!$infobox) { ?>title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field])))?>"<?php } ?>><?php if ($result[$n]["has_image"]==1) { ?><img  src="<?php echo $col_url ?>" class="ImageBorder"  alt="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))); ?>"
		<?php if ($infobox) { ?>onmouseover="InfoBoxSetResource(<?php echo htmlspecialchars($ref)?>);" onmouseout="InfoBoxSetResource(0);"<?php } ?>
		 /><?php } else { ?><img border=0 src="<?php echo $baseurl_short?>gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true) ?>" 
		<?php if ($infobox) { ?>onmouseover="InfoBoxSetResource(<?php echo htmlspecialchars($ref)?>);" onmouseout="InfoBoxSetResource(0);"<?php } ?>
		/><?php } ?><?php hook("aftersearchimg","",array($result[$n]))?></a>
		</td>
		</tr></table>				
		<?php } /* end hook renderimagesmallthumb */?>


        <?php if ($display_user_rating_stars && $k==""){ 
				if (!hook("replacesearchstars")){?>
		<?php if ($result[$n]['user_rating']=="") {$result[$n]['user_rating']=0;}
		$modified_user_rating=hook("modifyuserrating");
		if ($modified_user_rating){$result[$n]['user_rating']=$modified_user_rating;}?>
		
		<div  class="RatingStars" onMouseOut="UserRatingDisplay(<?php echo $result[$n]['ref']?>,<?php echo $result[$n]['user_rating']?>,'StarCurrent');">&nbsp;<?php
	    for ($z=1;$z<=5;$z++)
			{
			?><a href="#" onMouseOver="UserRatingDisplay(<?php echo $result[$n]['ref']?>,<?php echo $z?>,'StarSelect');" onClick="UserRatingSet(<?php echo $userref?>,<?php echo $result[$n]['ref']?>,<?php echo $z?>);return false;" id="RatingStarLink<?php echo $result[$n]['ref'].'-'.$z?>"><span id="RatingStar<?php echo $result[$n]['ref'].'-'.$z?>" class="Star<?php echo ($z<=$result[$n]['user_rating']?"Current":"Empty")?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></a><?php
			}
		?>
		</div>
		<?php } // end hook replacesearchstars
		} ?>
		<?php if (!hook("replaceicons")) { ?>
		<?php hook("icons");?>
		<?php } //end hook replaceicons ?>

		<?php
		$df_alt=hook("displayfieldsalt");
		$df_normal=$df;
		if ($df_alt) $df=$df_alt;
		# smallthumbs_display_fields
		for ($x=0;$x<count($df);$x++)
			{
			if(!in_array($df[$x]['ref'],$small_thumbs_display_fields)){continue;}
			#value filter plugin -tbd	
			$value=$result[$n]['field'.$df[$x]['ref']];
			$plugin="../plugins/value_filter_" . $df[$x]['name'] . ".php";
			if ($df[$x]['value_filter']!=""){
				eval($df[$x]['value_filter']);
			}
			else if (file_exists($plugin)) {include $plugin;}
			# swap title fields if necessary
			if (isset($metadata_template_resource_type) && isset ($metadata_template_title_field)){
				if (($df[$x]['ref']==$view_title_field) && ($result[$n]['resource_type']==$metadata_template_resource_type)){
					$value=$result[$n]['field'.$metadata_template_title_field];
					}
				}
			?>		
			<?php 
			// extended css behavior 
			if ( in_array($df[$x]['ref'],$small_thumbs_display_extended_fields) &&
			( (isset($metadata_template_title_field) && $df[$x]['ref']!=$metadata_template_title_field) || !isset($metadata_template_title_field) ) ){ ?>
			<?php if (!hook("replaceresourcepanelinfosmall")){?>
			<div class="ResourcePanelInfo"><div class="extended">
			<?php if ($x==0){ // add link if necessary ?><a href="<?php echo $url?>"  onClick="return CentralSpaceLoad(this,true);" <?php if (!$infobox) { ?>title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($value)))?>"<?php } //end if infobox ?>><?php } //end link
			echo format_display_field($value);			
			if ($show_extension_in_search) { ?><?php echo " " . str_replace_formatted_placeholder("%extension", $result[$n]["file_extension"], $lang["fileextension-inside-brackets"])?><?php } ?><?php if ($x==0){ // add link if necessary ?></a><?php } //end link?>&nbsp;</div></div>
			<?php } /* end hook replaceresourcepanelinfosmall */?>
			<?php 

			// normal behavior
			} else if  ( (isset($metadata_template_title_field)&&$df[$x]['ref']!=$metadata_template_title_field) || !isset($metadata_template_title_field) ) {?> 
			<?php if (!hook("replaceresourcepanelinfosmallnormal")){?>
			<div class="ResourcePanelInfo"><?php if ($x==0){ // add link if necessary ?><a href="<?php echo $url?>"  onClick="return CentralSpaceLoad(this,true);" <?php if (!$infobox) { ?>title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($value)))?>"<?php } //end if infobox ?>><?php } //end link?><?php echo highlightkeywords(tidy_trim(TidyList(i18n_get_translated($value)),28),$search,$df[$x]['partial_index'],$df[$x]['name'],$df[$x]['indexed'])?><?php if ($x==0){ // add link if necessary ?></a><?php } //end link?>&nbsp;</div><div class="clearer"></div>
			<?php } ?>
			<?php } /* end hook replaceresourcepanelinfosmallnormal */?>
			<?php
			}
			$df=$df_normal;
		?>
		
		
		<?php hook("smallsearchfreeicon");?>
		<?php if (!hook("replaceresourceplaneliconssmall")){?>
		<div class="ResourcePanelIcons"><?php if ($display_resource_id_in_thumbnail && $ref>0) { echo htmlspecialchars($ref); } else { ?><?php } ?>	
		<?php } /* end hook replaceresourcepaneliconssmall */ ?>
		<?php hook("smallsearchicon");?>
		<?php if (!hook("replaceresourcetoolssmall")){?>

		<!-- Preview icon -->
		<?php if (!hook("replacefullscreenpreviewicon")){?>
		<?php if ($result[$n]["has_image"]==1){?>
		<span class="IconPreview">
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/preview.php?from=search&amp;ref=<?php echo urlencode($ref)?>&amp;ext=<?php echo $result[$n]["preview_extension"]?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&amp;k=<?php echo urlencode($k)?>"  title="<?php echo $lang["fullscreenpreview"]?>"><img src="<?php echo $baseurl_short?>gfx/interface/sp.gif" alt="<?php echo $lang["fullscreenpreview"]?>" width="22" height="12" /></a></span>
		<?php $showkeypreview = true; ?>
		<?php } ?>
		<?php } /* end hook replacefullscreenpreviewicon */?>

		<!-- Add to collection icon -->
		<?php if (!checkperm("b") && $k=="") { ?>
		<span class="IconCollect"><?php echo add_to_collection_link($ref,$search)?><img src="<?php echo $baseurl_short?>gfx/interface/sp.gif" alt="" width="22" height="12" /></a></span>
		<?php $showkeycollect = true; ?>
		<?php } ?>

		<!-- Remove from collection icon -->
		<?php if (!checkperm("b") && substr($search,0,11)=="!collection" && $k=="") { ?>
		<?php if ($search=="!collection".$usercollection){?>
		<span class="IconCollectOut"><?php echo remove_from_collection_link($ref,$search)?><img src="<?php echo $baseurl_short?>gfx/interface/sp.gif" alt="" width="22" height="12" /></a></span>
		<?php $showkeycollectout = true; ?>
		<?php } ?>
		<?php } ?>

		<?php } // end hook replaceresourcetoolssmall ?>
		</div>
<?php hook("smallthumbicon"); ?>
<div class="clearer"></div></div>	
<div class="PanelShadow"></div></div>

<?php } # end hook renderresultsmallthumb
