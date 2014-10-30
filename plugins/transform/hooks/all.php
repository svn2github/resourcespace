<?php 

function HookTransformAllAdditionalheaderjs(){
global $baseurl,$baseurl_short;?>
<link rel="stylesheet" href="<?php echo $baseurl_short?>plugins/transform/lib/jcrop/css/jquery.Jcrop.min.css" type="text/css" />
<script type="text/javascript" src="<?php echo $baseurl?>/plugins/transform/lib/jcrop/js/jquery.Jcrop.min.js" language="javascript"></script>
<?php } 

function HookTransformAllCollectiontoolcompact1($collection, $count_result,$cinfo,$colresult,$col_editable){
	global $cropper_enable_batch,$lang;
	global $baseurl_short;

	if ($cropper_enable_batch && $count_result>0 && $col_editable){
	?>
	<option value="<?php echo $collection?>|0|0|<?php echo $baseurl_short?>plugins/transform/pages/collection_transform.php?collection=<?php echo $collection?>|main|false">&gt;&nbsp;<?php echo $lang["transform"]?>...</option>
	<?php
	}
    
}
function HookTransformAllAdditional_title_pages_array(){
        return array("crop","collection_transform");
}
function HookTransformAllAdditional_title_pages(){
        global $pagename,$lang,$applicationname;
        switch($pagename){
			case "crop":
				global $original;
				if($original){
					$pagetitle=$lang['transform_original'];
				}
				else{
					$pagetitle=$lang['transformimage'];
                }
                break;
            case "collection_transform":
				$pagetitle=$lang['batchtransform'];
				break;
		}
        if(isset($pagetitle)){
                echo "<script language='javascript'>\n";
                echo "document.title = \"$applicationname - $pagetitle\";\n";
                echo "</script>";
        }
}
