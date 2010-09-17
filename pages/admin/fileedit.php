<?php
include "../../include/db.php";?>
<?php include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}?>
<?php
$folder=getval("folder","default");

$file=getval("file","");

$error="";
# Chars to translate on save/load
$from=array("&","<",">");
$to=array("&AMP;","&LT;","&GT;");

# Save value?
if (getval("submit","")!="")
	{
	$value=getval("value","");
	$value=str_replace($to,$from,$value);
	$f=fopen($file,"w");fwrite($f,$value);fclose($f);
	}

if (getval("delete","")!="")
	{
	unlink($file);
	?>
	<script language="Javascript">
	top.main.left.EmptyNode(<?php echo getval("parent","")?>);
	top.main.left.ReloadNode(<?php echo getval("parent","")?>);
	</script>
	<?php
	exit("File deleted.");
	}

# Fetch value

$value=join("",file($file));
$value=str_replace($from,$to,$value);

include "include/header.php";
?>
<body style="background-position:0px -85px;margin:0;padding:10px;">

<style type="text/css">

.CodeMirror-line-numbers {font-size:9pt; }

</style>

<?php if ($error!="") { ?>
<div class=propbox style="font-weight:bold;color:red;"><?php echo $error?></div><br><br>
<?php } ?>

<div class="proptitle">File: <?php echo str_replace("../","",$file); if (basename($file)=="config.default.php"){echo " (copy and paste options from here)";}?></div>
<div class="propbox">

<form method=post>
<textarea style="height:100%;" id="code" name="value"><?php echo $value?></textarea>
<input type=hidden name="file" value="<?php echo $file?>">

<table width="100%">
<tr>
<?php 
$filename=basename($file); 
if ($filename!="config.php" && $filename!="config.default.php"){
	?>
	<td align=left><input type="submit" name="delete" value="delete" style="width:100px;" onclick="return confirm('Are you sure?');"></td>
<?php } ?>

<?php if ($filename!="config.default.php"){
	echo $filename;?>
<td align=right><input type="submit" name="submit" value="Save" style="width:150px;" onclick="this.value='please wait';"></td></tr>
<?php } ?>
</table>
</form>

</div>

<script type="text/javascript">
    var editor = CodeMirror.fromTextArea('code', {
    height: "80%",
    parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js",
                     "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js",
                     "../contrib/php/js/parsephphtmlmixed.js"],
	stylesheet: ["../../lib/CodeMirror/css/xmlcolors.css", "../../lib/CodeMirror/css/jscolors.css", "../../lib/CodeMirror/css/csscolors.css", "../../lib/CodeMirror/contrib/php/css/phpcolors.css"],
    path: "../../lib/CodeMirror/js/",
    continuousScanning: 500,
		
	lineNumbers: true

   });
</script>


</body>
</html>
