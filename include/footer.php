<?php 
# Do not display header / footer when dynamically loading CentralSpace contents.
if (getval("ajax","")=="" && !hook("replace_footer")) { 

hook("beforefooter");

# Include theme bar?
if ($use_theme_bar && !in_array($pagename,array("search_advanced","login","preview","admin_header","user_password","user_request")) && ($loginterms==false))
	{
	?></td></tr></table><?php
	}
?>
<div class="clearer"></div>

<!-- Use aria-live assertive for high priority changes in the content: -->
<span role="status" aria-live="assertive" class="ui-helper-hidden-accessible"></span>

</div><!--End div-CentralSpace-->
<?php if (($pagename!="login") && ($pagename!="user_password") && ($pagename!="preview_all") && ($pagename!="user_request")) { ?></div><?php } ?><!--End div-CentralSpaceContainer-->

<div class="clearer"></div>

<?php hook("footertop"); ?>

<?php if (($pagename!="login") && ($pagename!="user_request") && ($pagename!="user_password") && ($pagename!="preview_all")&& ($pagename!="done") && ($pagename!="preview") && ($pagename!="change_language") && ($loginterms==false)) { ?>
<!--Global Footer-->
<div id="Footer">

<?php if (!hook("replaceswapcss")){?>
<script type="text/javascript">
function SwapCSS(css){
	if (css.substr(-5)=="space"){
	document.getElementById('colourcss').href='<?php echo $baseurl?>/plugins/'+css+'/css/Col-' + css + '.css?css_reload_key=<?php echo $css_reload_key?>';	

	} else { 
	document.getElementById('colourcss').href='<?php echo $baseurl?>/css/Col-' + css + '.css?css_reload_key=<?php echo $css_reload_key?>';
	}

	SetCookie("colourcss",css,1000);  

	jQuery.ajax({
			url:"<?php echo $baseurl?>/pages/ajax/get_plugin_css.php?theme="+css,
			success: function(response) {
				jQuery('head').append(response); // add new css
				jQuery('.plugincss0').remove(); // then remove old
				jQuery('.plugincss').attr('class', 'plugincss0'); // set up new css for later removal
				}
			});
}

</script>
<?php } ?>

<?php if (getval("k","")=="") { ?>
<div id="FooterNavLeft" class=""><span id="FooterThemes" aria-hidden="true" role="presentation"><?php if (isset($userfixedtheme) && $userfixedtheme=="") { ?><?php echo $lang["interface"]?>:&nbsp;&nbsp;
<?php if (!hook("replacecustomthemechips"))	{
	// enable custom theme chips 
	if (count($available_themes!=0)){
		foreach ($available_themes as $available_theme){
		if (!in_array($available_theme, $available_themes_by_default)){?>
		&nbsp;<a href="#" onClick="SwapCSS('<?php echo $available_theme?>');return false;" name="choosetheme"><label class="ui-helper-hidden-accessible" for="choosetheme"><?php echo $lang['changethemeto'] . ' ' . ucfirst($available_theme); ?></label><img src="<?php echo $baseurl?>/plugins/<?php echo $available_theme?>/gfx/interface/<?php echo ucfirst($available_theme)?>Chip.gif" alt="<?php echo ucfirst($available_theme); ?> Theme Chip" width="11" height="11" /></a>
		<?php } else {?>
		&nbsp;<a href="#" onClick="SwapCSS('<?php echo $available_theme?>');return false;" name="choosetheme"><label class="ui-helper-hidden-accessible" for="choosetheme"><?php echo $lang['changethemeto'] . ' ' . ucfirst($available_theme); ?></label><img src="<?php echo $baseurl?>/gfx/interface/<?php echo ucfirst($available_theme)?>Chip.gif" alt="<?php echo ucfirst($available_theme); ?> Theme Chip" width="11" height="11" /></a>
		<?php } ?>
	<?php }
	}
	}/*End hook("replacecustomthemechips")*/
?>	</span><span id="FooterLanguages">
<?php } ?>
<?php if ($disable_languages==false && $show_language_chooser){?>
<?php echo $lang["language"]?>: <a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl?>/pages/change_language.php"><?php echo $languages[$language]?></a>
<?php } ?>
</span></div>


<?php if (!hook("replacefooternavright")){?>
<?php if ($about_link || $contact_link) { ?>
<div id="FooterNavRight" class="HorizontalNav HorizontalWhiteNav">
		<ul>
<?php if (!hook("replacefooterlinks")){?>
		<?php if (!$use_theme_as_home && !$use_recent_as_home) { ?><li><a href="<?php echo $baseurl?>/pages/<?php echo $default_home_page?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["home"]?></a></li><?php } ?>
		<?php if ($about_link) { ?><li><a href="<?php echo $baseurl?>/pages/about.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["aboutus"]?></a></li><?php } ?>
		<?php if ($contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php } ?>
<?php } /* end hook replacefooterlinks */ ?>
		</ul>
</div>
<?php } ?>
<?php } /* end hook replacefooternavright */ ?>

<?php } ?>

<div id="FooterNavRightBottom" class="OxColourPale"><?php echo text("footer")?></div>

<div class="clearer"></div>
</div>
<?php } ?>

<br />

<?php echo $extrafooterhtml; ?>

<?php } // end ajax ?>

<?php /* always include the below as they are perpage */?>

<?php hook("footerbottom"); ?>

<?php draw_performance_footer();?>

<?php
//titlebar modifications

if ($show_resource_title_in_titlebar){
$general_title_pages=array("team_content","team_archive","team_resource","team_user","team_request","team_research","team_plugins","team_mail","team_export","team_stats","team_report","team_user_log","research_request","requests","edit","themes","collection_public","collection_manage","team_home","help","home","tag","upload_java_popup","upload_java","contact","geo_search","search_advanced","about","contribute","user_preferences","view_shares","check","index");
$search_title_pages=array("contactsheet_settings","search","preview_all","collection_edit","edit","collection_download","collection_share","collection_request");
$resource_title_pages=array("view","delete","log","alternative_file","alternative_files","resource_email","edit","preview");
$additional_title_pages=array(hook("additional_title_pages_array"));

    // clear resource or search title for pages that don't apply:
    if (!in_array($pagename,array_merge($general_title_pages,$search_title_pages,$resource_title_pages)) && (empty($additional_title_pages) || !in_array($pagename,$additional_title_pages))){
		echo "<script language='javascript'>\n";
		echo "document.title = \"$applicationname\";\n";
		echo "</script>";
    }
    // place resource titles
    else if (in_array($pagename,$resource_title_pages) && !isset($_GET['collection']) && !isset($_GET['java'])) /* for edit page */{
        $title =  str_replace('"',"''",i18n_get_translated(get_data_by_field($ref,$view_title_field)));
        echo "<script type=\"text/javascript\" language='javascript'>\n";
        
        if ($pagename=="edit"){$title=$lang['action-edit']." - ".$title;}
        
        echo "document.title = \"$applicationname - $title\";\n";

        if($pagename=='edit' && $distinguish_uploads_from_edits) {

			$js = sprintf("
				jQuery(document).ready(function() {
					var h1 = jQuery(\"h1\").text();

					if(h1 == \"%s\") {
						document.title = \"%s - \" + h1;\n
					}
				});
			",
				$lang["addresourcebatchbrowser"],
				$applicationname);

			echo $js;

        }
        
        echo "</script>";
    }

    // place collection titles
    else if (in_array($pagename,$search_title_pages)){
        if (isset($search_title)){
            $title=str_replace('"',"''",$lang["searchresults"]." - ".html_entity_decode(strip_tags($search_title)));
        }
        else if (($pagename=="collection_download") || $pagename=="edit" && getval("collection","")!=""){
            $collectiondata=get_collection($collection);
            $title = strip_tags(str_replace('"',"''",i18n_get_collection_name($collectiondata)));
            }  
        else {
            $collection=getval("ref","");
            $collectiondata=get_collection($collection);
            $title = strip_tags(str_replace('"',"''",i18n_get_collection_name($collectiondata)));
            }
        // add a hyphen if title exists  
        if (strlen($title)!=0){$title="- $title";}    
        if ($pagename=="edit"){$title=" - ".$lang['action-editall']." ".$title;}
        if ($pagename=="collection_share"){$title=" - ".$lang['share']." ".$title;}
        if ($pagename=="collection_edit"){$title=" - ".$lang['action-edit']." ".$title;}
        if ($pagename=="preview_all"){$title=" - ".$lang['preview_all']." ".$title;}
        if ($pagename=="collection_download"){$title=" - ".$lang['download']." ".$title;}
        echo "<script language='javascript'>\n";
        echo "document.title = \"$applicationname $title\";\n";
        echo "</script>";
    }
    
      // place page titles
    else if (in_array($pagename,$general_title_pages)){ 
		
		if ($pagename=="themes"){
			$pagetitle=$lang['themes'];
			for ($n=0;$n<$theme_category_levels;$n++){
				if (getval("theme".$n,"")!=""){
					$pagetitle.=" / ".getval("theme".$n,"");
				}
			}
		}
		else if (isset($lang[$pagename])){
			$pagetitle=$lang[$pagename];
		} 
		else if (isset($lang['action-'.$pagename])){
			$pagetitle=$lang["action-".$pagename];
			if (getval("java","")!=""){$pagetitle=$lang['upload']." ".$pagetitle;}
		}
		else if (isset($lang[str_replace("_","",$pagename)])){
			$pagetitle=$lang[str_replace("_","",$pagename)];
		}
		else if ($pagename=="team_content"){
			$pagetitle=$lang['managecontent'];
		}
		else if ($pagename=="collection_public"){
			$pagetitle=$lang["publiccollections"];
		}
		else if ($pagename=="collection_manage"){
			$pagetitle=$lang["mycollections"];
		}
		else if ($pagename=="team_home"){
			$pagetitle=$lang["teamcentre"];
		}
		else if ($pagename=="help"){
			$pagetitle=$lang["helpandadvice"];
		}
		else if ($pagename=="tag"){
			$pagetitle=$lang["tagging"];
		}
		else if (strpos($pagename,"upload")!==false){
			$pagetitle=$lang["upload"];
		}
		else if ($pagename=="contact"){
			$pagetitle=$lang["contactus"];
		}
		else if ($pagename=="geo_search"){
			$pagetitle=$lang["geographicsearch"];
		}
		else if ($pagename=="search_advanced"){
			$pagetitle=$lang["advancedsearch"];
			if (getval("archive","")==2){$pagetitle.=" - ".$lang['archiveonlysearch'];}
		}	
		else if ($pagename=="about"){
			$pagetitle=$lang["aboutus"];
		}	
		else if ($pagename=="contribute"){
			$pagetitle=$lang["mycontributions"];
		}	
		else if ($pagename=="user_preferences"){
			$pagetitle=$lang["user-preferences"];
		}	
		else if ($pagename=="requests"){
			$pagetitle=$lang["myrequests"];
		}	
		else if ($pagename=="team_resource"){
			$pagetitle=$lang["manageresources"];
		}	
		else if ($pagename=="team_archive"){
			$pagetitle=$lang["managearchiveresources"];
		}	
		else if($pagename=="view_shares"){
			$pagetitle=$lang["shared_collections"];
		}	
		else if($pagename=="team_user"){
			$pagetitle=$lang["manageusers"];
		}
		else if($pagename=="team_request"){
			$pagetitle=$lang["managerequestsorders"];
		}
		else if($pagename=="team_research"){
			$pagetitle=$lang["manageresearchrequests"];
		}
		else if($pagename=="team_plugins"){
			$pagetitle=$lang["pluginmanager"];
		}
		else if($pagename=="team_mail"){
			$pagetitle=$lang["sendbulkmail"];
		}
		else if($pagename=="team_export"){
			$pagetitle=$lang["exportdata"];
		}
		else if($pagename=="team_export"){
			$pagetitle=$lang["exportdata"];
		}
		else if($pagename=="team_stats"){
			$pagetitle=$lang["viewstatistics"];
		}
		else if($pagename=="team_report"){
			$pagetitle=$lang["viewreports"];
		}
		else if($pagename=="check"){
			$pagetitle=$lang["installationcheck"];
		}
		else if($pagename=="index"){
			$pagetitle=$lang["systemsetup"];
		}
		else if($pagename=="team_user_log"){
			global $userdata;
			$pagetitle=$lang["userlog"] . ": " . $userdata["fullname"];
		}
		else {
			$pagetitle="";
		}
		if (strlen($pagetitle)!=0){$pagetitle="- $pagetitle";} 
        echo "<script language='javascript'>\n";
        echo "document.title = \"$applicationname $pagetitle\";\n";
        echo "</script>";
    }
    hook("additional_title_pages");
}
   
?><script src="<?php echo $baseurl?>/lib/js/Placeholders.min.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script><?php


if (getval("ajax","")=="") { 
	// don't show closing tags if we're in ajax mode
	?>


<!--CollectionDiv-->
<?php 
$omit_collectiondiv_load_pages=array("login","user_request","user_password","index","preview_all");
?></div>

<?php # Work out the current collection (if any) from the search string if external access
if (isset($k) && $k!="" && isset($search) && !isset($usercollection))
    {
    if (substr($search,0,11)=="!collection") {
		$usercollection = substr($search,11);
    }
}
?><script>
<?php if (!isset($usercollection)){?>
	usercollection='';
<?php } else { ?>
	usercollection='<?php echo htmlspecialchars($usercollection) ?>';
	var collections_popout = <?php echo $collection_bar_popout? "true": "false"; ?>;
<?php } ?>
</script><?php 

if ($collections_footer && !in_array($pagename,$omit_collectiondiv_load_pages) && !checkperm("b") && isset($usercollection)) 
	{?>
	<div id="CollectionDiv" class="CollectBack AjaxCollect ui-layout-south"></div>
	<script type="text/javascript">
		var collection_frame_height=<?php echo $collection_frame_height?>;
		var thumbs="<?php echo htmlspecialchars($thumbs); ?>";
		function ShowThumbs() {
			myLayout.sizePane("south", collection_frame_height);
			jQuery('.ui-layout-south').animate({scrollTop:0}, 'fast');
			jQuery('#CollectionMinDiv').hide();
			jQuery('#CollectionMaxDiv').show();
			SetCookie('thumbs',"show",1000);
		}
		function HideThumbs() {
			myLayout.sizePane("south", 40);
			jQuery('.ui-layout-south').animate({scrollTop:0}, 'fast');			
			jQuery('#CollectionMinDiv').show();
			jQuery('#CollectionMaxDiv').hide();
			SetCookie('thumbs',"hide",1000);
		}
		function ToggleThumbs() {
			thumbs = getCookie("thumbs");
			if (thumbs=="show"){
				HideThumbs();
			} else { 
				ShowThumbs();
			}
		}
		function InitThumbs() {
			if(thumbs!="hide") {
				ShowThumbs();
			} else if(thumbs=="hide") {
				HideThumbs();
			}
		}
		myLayout=jQuery('body').layout({
			//closable:false,
			resizable:true,
			livePaneResizing:true,
			triggerEventsDuringLiveResize: false,
			minSize:40,
			spacing_open:6,
			spacing_closed:6,
			togglerLength_open:"200",
			togglerTip_open: '<?php echo $lang["toggle"]?>',
			resizerTip: '<?php echo $lang["resize"]?>',
			south__onclose_start: function(pane){
				if (pane=="south"){
					if(jQuery('.ui-layout-south').height()>40 && thumbs!="hide"){
						HideThumbs();
					} else if(jQuery('.ui-layout-south').height()<=40 && thumbs=="hide"){
						ShowThumbs();
					}
					return false;
				}
			},
			south__onresize: function(pane){
				if (pane=="south"){
					thumbs = getCookie("thumbs");
					if(jQuery('.ui-layout-south').height() < collection_frame_height && thumbs!="hide"){
						HideThumbs();
					} else if(jQuery('.ui-layout-south').height()> 40 && thumbs=="hide"){
						ShowThumbs();
					}
				}
			}
		});
		window.onload = function() {
		    CollectionDivLoad('<?php echo $baseurl_short?>pages/collections.php?thumbs=<?php echo urlencode($thumbs); ?>&collection='+usercollection+'<?php echo (isset($k) ? "&k=".urlencode($k) : ""); ?>');
		    InitThumbs();
		}
</script>
<?php } // end omit_collectiondiv_load_pages 
else {?><div class="ui-layout-south" ></div><script>myLayout=jQuery('body').layout({south__initHidden: true });	</script><?php } ?>


<?php hook("afteruilayout");?>
<?php hook("responsivescripts"); ?>

</body>
</html>
<?php } // end if !ajax ?>
