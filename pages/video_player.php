<?php
# Video player - plays the preview file created to preview video resources.

global $alternative;

# First we look for a preview video with the expected extension.
$flashfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative);
if (file_exists($flashfile))
	{
	$flashpath=get_resource_path($ref,false,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative,false);
	}
elseif ($ffmpeg_preview_extension!="flv")
	{
	# Still no file. For legacy systems that are not using MP4 previews, next we look for an FLV preview.
	$flashfile=get_resource_path($ref,true,"",false,"flv",-1,1,false,"",$alternative);
	$flashpath=get_resource_path($ref,false,"",false,"flv",-1,1,false,"",$alternative,false);
	}
if (!file_exists($flashfile))
        {
	# Back out to playing the source file direct (not a preview). For direct FLV upload support - the file itself is an FLV. Or, with the preview functionality disabled, we simply allow playback of uploaded video files.
	$flashfile=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative);
	$flashpath=get_resource_path($ref,false,"",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative,false);
	}

$flashpath_raw=$flashpath;     
$flashpath=urlencode($flashpath);

$thumb=get_resource_path($ref,false,"pre",false,"jpg",-1,1,false,"",$alternative); 
$thumb_raw=$thumb;
$thumb=urlencode($thumb);

# Choose a colour based on the theme.
$theme=(isset($userfixedtheme) && $userfixedtheme!="")?$userfixedtheme:getval("colourcss","greyblu");
$color="505050";$bgcolor1="666666";$bgcolor2="111111";$buttoncolor="999999";
if ($theme=="greyblu") {$color="446693";$bgcolor1="6883a8";$bgcolor2="203b5e";$buttoncolor="adb4bb";}	
if ($theme=="whitegry") {$color="ffffff";$bgcolor1="ffffff";$bgcolor2="dadada";$buttoncolor="666666";}	
if ($theme=="black") {$bgcolor1="666666";$bgcolor2="111111";$buttoncolor="999999";}	

$width=$ffmpeg_preview_max_width;
$height=$ffmpeg_preview_max_height;
if ($pagename=="search"){$width="355";$height=355/$ffmpeg_preview_max_width*$ffmpeg_preview_max_height;}
?>
<?php if(!hook("swfplayer")){ ?>

<?php if (!$videojs) { ?>
<object type="application/x-shockwave-flash" data="<?php echo $baseurl_short?>lib/flashplayer/player_flv_maxi.swf?t=<?php echo time() ?>" width="<?php echo $width?>" height="<?php echo $height?>" class="Picture">
     <param name="allowFullScreen" value="true" />
     <param name="movie" value="<?php echo $baseurl_short?>lib/flashplayer/player_flv_maxi.swf" />
     <param name="FlashVars" value="flv=<?php echo $flashpath?>&amp;width=<?php echo $width?>&amp;height=<?php echo $height?>&amp;margin=0&amp;showvolume=1&amp;volume=200&amp;showtime=2&amp;autoload=1&amp;<?php if ($pagename!=="search"){?>showfullscreen=1<?php } ?>&amp;showstop=1&amp;buttoncolor=<?php echo $buttoncolor?>&playercolor=<?php echo $color?>&bgcolor=<?php echo $color?>&bgcolor1=<?php echo $bgcolor1?>&bgcolor2=<?php echo $bgcolor2?>&startimage=<?php echo $thumb?>&playeralpha=75&autoload=1&buffermessage=&buffershowbg=0" />
</object>
<?php } else { ?>

<!-- START VIDEOJS -->
<link href="<?php echo $baseurl_short?>lib/videojs/video-js.css" rel="stylesheet">
<script src="<?php echo $baseurl_short?>lib/videojs/video.js"></script>
<video id="introvideo" controls width="<?php echo $width?>" height="<?php echo $height?>" data-setup="" class="video-js vjs-default-skin vjs-big-play-centered" poster="<?php echo $thumb_raw?>" preload="auto" >
     <source src="<?php echo $flashpath_raw?>" type="video/flv" />
     <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
</video>
<!-- END VIDEOJS -->
<?php } ?>

<?php } ?>

