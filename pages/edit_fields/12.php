<?php
/*******************************************/
/**************RADIO BUTTONS****************/
/*******************************************/

$options = trim_array(explode(",",$field["options"]));
$set = trim($value);

$l=average_length($options);

$cols=10;
if($l>5)  {$cols=6;}
if($l>10) {$cols=4;}
if($l>15) {$cols=3;}
if($l>25) {$cols=2;}

$rows=ceil(count($options)/$cols);

// Autoupdate is set only on search forms, otherwise it should be false
if(!isset($autoupdate)) {
	$autoupdate = false;
}

if ($edit_autosave) { ?>
	<script type="text/javascript">
		// Function to allow radio buttons to save automatically when $edit_autosave from config is set: 
		function radio_allow_save() {
			preventautosave=false;
			
			setTimeout(function () {
		        preventautosave=true;
		    }, 1000);
		}
	</script>
<?php } ?>

<div class="radioblock">                    
		<div class="radiooptions">
			<?php 
			$row = 1;
			$col = 1;
			foreach ($options as $key => $value) {
				if($col > $cols) {
					$col = 1;
					$row++; ?>
					</tr>
					<tr>
				<?php }
				$col++;
				?>
		
			<div class="radiooption"><span class="radio">
				<input type="radio" id="field_<?php echo $field["ref"] . '_' . $value; ?>" name="field_<?php echo $field["ref"]; ?>" value="<?php echo $value; ?>" <?php if($value == $set) {?>checked<?php } ?> <?php if($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>/>
			</span>
			<span class="radiotext">
				<label class="customFieldLabel" for="field_<?php echo $field["ref"] . '_' . $value; ?>" <?php if($edit_autosave) { ?>onmousedown="radio_allow_save();" <?php } ?>><?php echo $value; ?></label>
			</span>
			</div>
			<?php } ?>
		</div>
</div>