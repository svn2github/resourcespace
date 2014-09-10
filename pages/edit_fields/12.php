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

if(!isset($help_js)) {
    $help_js = '';
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

<table id="" class="radioOptionTable" cellpadding="3" cellspacing="3">                    
        <tbody>
                <tr>
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
                
                        <td width="10" valign="middle">
                                <input type="radio" id="field_<?php echo $field["ref"] . '_' . sha1($value); ?>" name="field_<?php echo $field["ref"]; ?>" value="<?php echo $value; ?>" <?php if($value == $set) {?>checked<?php } ?> <?php if($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } echo $help_js; ?>/>
                        </td>
                        <td align="left" valign="middle">
                                 <label class="customFieldLabel" for="field_<?php echo $field["ref"] . '_' . sha1($value); ?>" <?php if($edit_autosave) { ?>onmousedown="radio_allow_save();" <?php } ?>><?php echo $value; ?></label>
                        </td>

                        <?php } ?>
                </tr>
        </tbody>
</table>