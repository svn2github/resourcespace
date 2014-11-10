<?php
/**
 * Edit content strings page (part of Team Center)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include "../../include/authenticate.php"; if (!checkperm("o")) {exit ("Permission denied.");}
include "../../include/general.php";
include "../../include/research_functions.php";

$offset=getvalescaped("offset",0);
$page=getvalescaped("page","");
$name=getvalescaped("name","");
$findpage=getvalescaped("findpage","");
$findname=getvalescaped("findname","");
$findtext=getvalescaped("findtext","");
$newhelp=getvalescaped("newhelp","");
$editlanguage=getvalescaped("editlanguage",isset($defaultlanguage) ? $defaultlanguage : $language);
$editgroup=getvalescaped("editgroup","");

# get custom value from database, unless it has been newly passed from team_content.php
if (getval("custom","")==1){ $custom=1; $newcustom=true; } else {$custom=check_site_text_custom($page,$name); $newcustom=false;}

if ((getval("save","")!="") && (getval("langswitch","")==""))
	{
	# Save data
	save_site_text($page,$name,$editlanguage,$editgroup);
	if ($newhelp!=""){
		if (getval("returntolist","")==""){
			redirect($baseurl_short."pages/team/team_content_edit.php?page=help&name=".$newhelp."&offset=".$offset."&findpage=".$findpage."&findname=".$findname."&findtext=".$findtext);
		}
		}
	if (getval("custom","")==1){
		if (getval("returntolist","")==""){
			redirect($baseurl_short."pages/team/team_content_edit.php?page=$page&name=$name&offset=".$offset."&findpage=".$findpage."&findname=".$findname."&findtext=".$findtext);
		}
		}	
	if (getval("returntolist","")!=""){
		redirect ($baseurl_short."pages/team/team_content.php?nc=" . time()."&findpage=".$findpage."&findname=".$findname."&findtext=".$findtext."&offset=".$offset);
	}
	}
	
# Fetch user data
$text=get_site_text($page,$name,$editlanguage,$editgroup);

include "../../include/header.php";
?>
<p><a href="<?php echo $baseurl_short?>pages/team/team_content.php?nc=<?php echo time()?>&findpage=<?php echo $findpage?>&findname=<?php echo $findname?>&findtext=<?php echo $findtext?>&offset=<?php echo $offset?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtomanagecontent"]?></a></p>
<div class="BasicsBox">
<h1><?php echo $lang["editcontent"]?></h1>

<form method= post id="mainform" action="<?php echo $baseurl_short?>pages/team/team_content_edit.php">
<input type=hidden name=page value="<?php echo $page?>">
<input type=hidden name=name value="<?php echo $name?>">
<input type=hidden name=langswitch id=langswitch value="">
<input type=hidden name=groupswitch id=groupswitch value="">

<div class="Question"><label><?php echo $lang["page"]?></label><div class="Fixed"><?php echo $page?></div><div class="clearerleft"> </div></div>
<?php if ($page=="help"){?>
<div class="Question"><label for="name"><?php echo $lang["name"]?></label><input type=text name="name" class="stdwidth" value="<?php echo htmlspecialchars($name)?>">
<?php } else { ?>
<div class="Question"><label><?php echo $lang["name"]?></label><div class="Fixed"><?php echo $name?></div><div class="clearerleft"> </div></div>
<?php } ?>

<div class="Question">
<label for="editlanguage"><?php echo $lang["language"]?></label>
<select class="stdwidth" name="editlanguage" onchange="document.getElementById('langswitch').value='yes';document.getElementById('mainform').submit();">
<?php foreach ($languages as $key=>$value) { ?>
<option value="<?php echo $key?>" <?php if ($editlanguage==$key) { ?>selected<?php } ?>><?php echo $value?></option>
<?php } ?>
</select>
<div class="clearerleft"> </div>
</div>

<?php if(!hook("managecontenteditgroupselector")){ ?>
<div class="Question">
<label for="editgroup"><?php echo $lang["group"]?></label>
<select class="stdwidth" name="editgroup" onchange="document.getElementById('groupswitch').value='yes';document.getElementById('mainform').submit();">
<option value=""></option>
<?php 
$groups=get_usergroups();
for ($n=0;$n<count($groups);$n++) { ?>
<option value="<?php echo $groups[$n]["ref"]?>" <?php if ($editgroup==$groups[$n]["ref"]) { ?>selected<?php } ?>><?php echo $groups[$n]["name"]?></option>
<?php } ?>
</select>
<div class="clearerleft"> </div>
</div>
<?php } /* End managecontenteditgroupselector */?>

<div class="Question">
<?php
if ($site_text_use_ckeditor)
	{?>
	<p><label for="text"><?php echo $lang["text"]?></label></p><br>
	<textarea name="text" class="stdwidth" rows=15 cols=50 id="<?php echo $lang["text"]?>" ><?php echo htmlspecialchars($text)?></textarea>
	<script type="text/javascript">
	<?php if(!hook("ckeditorinit")){ ?>
		var editor = CKEDITOR.instances['<?php echo $lang["text"]?>'];
		if (editor) { editor.destroy(true); }
		CKEDITOR.replace('<?php echo $lang["text"] ?>',
			{
			toolbar : [ <?php global $ckeditor_content_toolbars;echo $ckeditor_content_toolbars; ?> ],
			height: "600"	
			});
		var editor = CKEDITOR.instances['<?php echo $lang["text"]?>'];
		<?php } ?>
	<?php hook("ckeditoroptions"); ?>
	</script>
	<?php }
else
	{?>
		<label for="text"><?php echo $lang["text"]?></label><textarea name="text" class="stdwidth" rows=15 cols=50><?php echo htmlspecialchars($text)?></textarea>
	<?php } ?>

<div class="clearerleft"> </div>
</div>


<!-- disabled next two as they are in system setup, and making these available on the team centre could lead to accidental deletes and copies.
<div class="Question"><label>Tick to delete this item</label><input name="deleteme" type="checkbox" value="yes"><div class="clearerleft"> </div></div>

<div class="Question"><label>Tick to save as a copy</label><input name="copyme" type="checkbox" value="yes"><div class="clearerleft"> </div></div>
-->

<?php # add special ability to create and remove help pages
if ($page=="help") { ?>
<?php if ($name!="introtext"){ ?>
	<div class="Question"><label for="deleteme"><?php echo $lang["ticktodeletehelp"]?></label><input class="deleteBox" name="deleteme" type="checkbox" value="yes"><div class="clearerleft"> </div></div>
<?php } ?><br><br>
<label for="newhelp"><?php echo $lang["createnewhelp"]?></label><input name="newhelp" type=text value=""><div class="clearerleft"> </div>
<?php } ?>

<?php # add ability to delete custom page/name entries
 if ($custom==1 && $page!="help"){ ?>
	<div class="Question"><label for="deletecustom"><?php echo $lang["ticktodeletehelp"]?></label><input class="deleteBox" name="deletecustom" type="checkbox" value="yes"><div class="clearerleft"> </div></div>
<?php } ?>

<input type=hidden id="returntolist" name="returntolist" value=''/>
<div id="submissionResponse"></div>
<div class="QuestionSubmit">
<label for="save"> </label>			
<input class="saveText" name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
<input class="saveText return" name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang['saveandreturntolist']?>&nbsp;&nbsp;" />
</div>
</form>
</div>
<script>
var validsubmission = false;
jQuery(".saveText").click(function(){
	if(jQuery(".deleteBox:checked").length > 0) {
		jQuery('#returntolist').val(true);
		return true;
	}
	if(validsubmission){return true;}
	CentralSpaceShowLoading();
	if(jQuery(this).hasClass("return")) {
		jQuery('#returntolist').val(true);
	}else {
		jQuery('#returntolist').val("");
	}
	var ckeditor = '<?php echo $site_text_use_ckeditor ?>';
	if(ckeditor==true){var checktext = jQuery(textarea.editor).val();}
	else{var checktext = jQuery("textarea").val();}
	jQuery.post("../tools/check_html.php",{"text":checktext},function(response, status, xhr){
			CentralSpaceHideLoading();
            if(response !=="<pre>OK\n</pre>"){
				jQuery("#submissionResponse").html(response);
				return false;
            }else {
            	validsubmission=true;
            	jQuery(".saveText").click();
            }
    	}
    );
    return false;
});
</script>
<?php		
include "../../include/footer.php";
?>
