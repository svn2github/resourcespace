<?php
include ("../../include/db.php");
include ("../../include/authenticate.php");
include ("../../include/general.php");
include ("../../include/header.php");
?>

<div class="BasicsBox"> 
  
  <p><a href="<?php echo $baseurl . "/pages/team/team_home.php" ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["teamcentre"]?></a></p>
  
  <h1><?php echo $lang["systemsetup"]?></h1>
  <p><?php echo text("introtext")?></p>

  <div class="VerticalNav">
	<ul>
		<li><a href="admin_group_management.php" onclick="return CentralSpaceLoad(this,true);" ><?php echo $lang['page-title_user_group_management']; ?></a></li>
		<li><a href="admin_resource_types.php" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang["treenode-resource_types_and_fields"] ?></a></li>
		<li><a href="admin_resource_type_fields.php" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang["admin_resource_type_fields"] ?></a></li>
		<li><a href="admin_report_management.php" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang['page-title_report_management']; ?></a></li>
		<li><a href="admin_size_management.php" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang["page-title_size_management"] ?></a></li>
		
		<?php if ($use_plugins_manager == true){ ?>
		<li><a href="<?php echo $baseurl?>/pages/team/team_plugins.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["pluginssetup"]?></a></li>
		<?php } ?>
		
<?php
if ($web_config_edit)
	{
?>		<li><a href="fileedit.php?file=../../include/config.php" target="_blank"><?php echo $lang["action-edit"]; ?> config.php</a></li>
		<li><a href="fileedit.php?file=../../include/config.default.php" target="_blank"><?php echo $lang["action-edit"]; ?> config.default.php</a></li>
<?php
	}	

hook("customadminfunction");
?>

	</ul>
	</div>
</div> <!-- End of BasicsBox -->


<?php


include("../../include/footer.php");
