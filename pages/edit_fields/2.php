<?php /* -------- Check box list ------------------ */ 

if(!hook("customchkboxes")):

# Translate all options
$options=trim_array(explode(",",$field["options"]));

$modified_options=hook("modify_field_options","",array($field));
if($modified_options!=""){$options=$modified_options;}

$option_trans=array();
$option_trans_simple=array();
for ($m=0;$m<count($options);$m++)
	{
	$trans=i18n_get_translated($options[$m]);
	$option_trans[$options[$m]]=$trans;
	$option_trans_simple[]=$trans;
	}

if ($auto_order_checkbox) {natsort($option_trans);}
$options=array_keys($option_trans); # Set the options array to the keys, so it is now effectively sorted by translated string	
	
$set=trim_array(explode(",",$value));

if ($edit_autosave) { ?>
	<script type="text/javascript">
		// Function to allow checkboxes to save automatically when $edit_autosave from config is set: 
		function checkbox_allow_save() {
			preventautosave=false;
			
			setTimeout(function () {
		        preventautosave=true;
		    }, 500);
		}
	</script>
<?php }

global $checkbox_ordered_vertically;
if ($checkbox_ordered_vertically)
	{
	$wrap=0;
	$l=average_length($option_trans_simple);
	$cols=10;
	if ($l>5)  {$cols=6;}
	if ($l>10) {$cols=4;}
	if ($l>15) {$cols=3;}
	if ($l>25) {$cols=2;}

	$height=ceil(count($options)/$cols);
	if(!hook('rendereditchkboxes')):
	# ---------------- Vertical Ordering (only if configured) -----------
	?>
	<fieldset class="customFieldset" name="<?php echo $field['title']; ?>">
		<legend class="accessibility-hidden"><?php echo $field['title']; ?></legend>
		<div class="verticaleditcheckboxes"><?php
	for ($y=0;$y<$height;$y++)
		{
		for ($x=0;$x<$cols;$x++)
			{
			# Work out which option to fetch.
			$o=($x*$height)+$y;
			if ($o<count($options))
				{
				$option=$options[$o];
				$trans=$option_trans[$option];

				$name=$field["ref"] . "_" . md5($option);
				if ($option!="")
					{
					/*if(!hook("replace_checkbox_vertical_rendering","",array($name,$option,$ref=$field["ref"],$set))){*/
						?>
						<div class="checkoption"><span class="checkbox"><input type="checkbox" id="<?php echo $name; ?>" name="<?php echo $name?>" value="yes" <?php if (in_array($option,$set)) {?>checked<?php } ?> 
						<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');" onmousedown="checkbox_allow_save();"<?php } ?>
						/></span><span class="checkboxtext"><label class="customFieldLabel" for="<?php echo $name; ?>" <?php if($edit_autosave) { ?>onmousedown="checkbox_allow_save();" <?php } ?>><?php echo htmlspecialchars($trans)?></label></span></div>
						<?php
						/*} # end hook("replace_checkbox_vertical_rendering")*/
					}
				}
			}?><br /><?php
		}
	?></div></fieldset><?php
	endif;
	}
else
	{				
	# ---------------- Horizontal Ordering (Standard) ---------------------				
	?>
	<div class="editcheckboxes">
	<?php

	foreach ($option_trans as $option=>$trans)
		{
		$name=$field["ref"] . "_" . md5($option);
		?>
		<div class="checkoption"><span class="checkbox"><input type="checkbox" name="<?php echo $name?>" value="yes" <?php if (in_array($option,$set)) {?>checked<?php } ?>
		<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } ?>
		 /></span><span class="checkboxtext"><?php echo htmlspecialchars($trans)?>&nbsp;</span></div>
		<?php
		}
	?></div><?php
	}
	
endif;
