<?php /* -------- Drop down list ------------------ */ 

# Translate all options
$options=trim_array(explode(",",$field["options"]));
$modified_options=hook("modify_field_options","",array($field));
if($modified_options!=""){$options=$modified_options;}
$adjusted_dropdownoptions=hook("adjustdropdownoptions");
if ($adjusted_dropdownoptions){$options=$adjusted_dropdownoptions;}

$option_trans=array();
for ($m=0;$m<count($options);$m++)
	{
	$option_trans[$options[$m]]=i18n_get_translated($options[$m]);
	}
if ($auto_order_checkbox) {asort($option_trans);}	

$adjusted_dropdownoptiontrans=hook("adjustdropdownoptiontrans","edit",array($field,$option_trans));
if ($adjusted_dropdownoptiontrans){$option_trans=$adjusted_dropdownoptiontrans;}

if (substr($value,0,1) == ',') { $value = substr($value,1); }	// strip the leading comma if it exists
?><select class="stdwidth" name="<?php echo $name?>" id="<?php echo $name?>" <?php echo $help_js; hook("additionaldropdownattributes","",array($field)); ?>
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } ?>
>
<?php if (!hook("replacedropdowndefault","",array($field)))
	{ 
	if($value==="") {
		$value = $options[0]==="" ? "" : $options[0];
	}

	// If all fields should be blank, overwrite value to be blank:
	if(isset($blank_edit_template) && $blank_edit_template) {
		$value = '';
	}
	?><option value=""></option><?php
	} ?>
<?php
foreach ($option_trans as $option=>$trans)
	{
	if (trim($option)!="")
		{
		?>
		<option value="<?php echo htmlspecialchars(trim($option))?>" <?php if (trim($option)==trim($value)) {?>selected<?php } ?>><?php echo htmlspecialchars(trim($trans))?></option>
		<?php
		}
	}
?></select><?php

