<?php

function show_table_headers($showprice)
	{
	global $lang;
	if(!hook("replacedownloadspacetableheaders")){
	?><tr><td><?php echo $lang["fileinformation"]?></td>
	<td><?php echo $lang["filetype"]?></td>
	<?php if ($showprice) { ?><td><?php echo $lang["price"] ?></td><?php } ?>
	<td class="textcenter"><?php echo $lang["options"]?></td>
	</tr>
	<?php
	} # end hook("replacedownloadspacetableheaders")
	}

function HookFormat_chooserViewReplacedownloadoptions()
	{
	global $resource, $ref, $counter, $headline, $lang, $download_multisize, $showprice, $save_as,
			$direct_link_previews, $hide_restricted_download_sizes, $format_chooser_output_formats,
			$baseurl_short, $search, $offset, $k, $order_by, $sort, $archive, $direct_download;

	$inputFormat = $resource['file_extension'];

	if ($resource["has_image"] != 1 || !$download_multisize || $save_as
			|| !supportsInputFormat($inputFormat))
		return false;

	$defaultFormat = getDefaultOutputFormat($inputFormat);
	$tableHeadersDrawn = false;

	?><table cellpadding="0" cellspacing="0"><?php
	hook("formatchooserbeforedownloads");
	$sizes = get_image_sizes($ref, false, $resource['file_extension'], false);
	$downloadCount = 0;
	$originalSize = -1;

	# Show original file download
	for ($n = 0; $n < count($sizes); $n++)
		{
		$downloadthissize = resource_download_allowed($ref, $sizes[$n]["id"], $resource["resource_type"]);
		$counter++;

		if ($sizes[$n]['id'] != '') {
			if ($downloadthissize)
				$downloadCount++;
			continue;
		}

		# Is this the original file? Set that the user can download the original file
		# so the request box does not appear.
		$fulldownload = false;
		if ($sizes[$n]["id"] == "")
			$fulldownload = true;

		$originalSize = $sizes[$n];

		$headline = $lang['collection_download_original'];
		if ($direct_link_previews && $downloadthissize)
			$headline = make_download_preview_link($ref, $sizes[$n]);
		if ($hide_restricted_download_sizes && !$downloadthissize && !checkperm("q"))
			continue;

		if (!$tableHeadersDrawn)
			{
			show_table_headers($showprice);
			$tableHeadersDrawn = true;
			}

		?><tr class="DownloadDBlend" id="DownloadBox<?php echo $n?>">
		<td class="DownloadFileName"><h2><?php echo $headline?></h2><p><?php
		echo $sizes[$n]["filesize"];
		if (is_numeric($sizes[$n]["width"]))
			echo preg_replace('/^<p>/', ', ', get_size_info($sizes[$n]), 1);

		?></p><td class="DownloadFileFormat"><?php echo str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["field-fileextension"]) ?></td><?php

		if ($showprice)
			{
			?><td><?php echo get_display_price($ref, $sizes[$n]) ?></td><?php
			}
		add_download_column($ref, $sizes[$n], $downloadthissize);
		}

	# Add drop down for all other sizes
	$closestSize = 0;
	if ($downloadCount > 0)
		{
		if (!$tableHeadersDrawn)
			show_table_headers($showprice);

		?><tr class="DownloadDBlend">
		<td class="DownloadFileSizePicker"><select id="size"><?php

		$sizes = get_all_image_sizes();

		# Filter out all sizes that are larger than our image size, but not the closest one
		for ($n = 0; $n < count($sizes); $n++)
			{
			if (intval($sizes[$n]['width']) >= intval($originalSize['width'])
					&& intval($sizes[$n]['height']) >= intval($originalSize['height'])
					&& ($closestSize == 0 || $closestSize > (int)$sizes[$n]['width']))
				$closestSize = (int)$sizes[$n]['width'];
			}
		for ($n = 0; $n < count($sizes); $n++)
			{
			if (intval($sizes[$n]['width']) != $closestSize
					&& intval($sizes[$n]['width']) > intval($originalSize['width'])
					&& intval($sizes[$n]['height']) > intval($originalSize['height']))
				unset($sizes[$n]);
			}
		foreach ($sizes as $n => $size)
			{
			# Only add choice if allowed
			$downloadthissize = resource_download_allowed($ref, $size["id"], $resource["resource_type"]);
			if (!$downloadthissize)
				continue;

			$name = $size['name'];
			if ($size['width'] == $closestSize)
				$name = $lang['format_chooser_original_size'];
			?><option value="<?php echo $n ?>"><?php echo $name ?></option><?php
			}
		?></select><p id="sizeInfo"></p></td><?php
		if ($showprice)
			{
			?><td>-</td><?php
			}
		?><td class="DownloadFileFormatPicker" style="vertical-align: top;"><select id="format"><?php

		foreach ($format_chooser_output_formats as $format)
			{
			?><option value="<?php echo $format ?>" <?php if ($format == $defaultFormat) {
				?>selected="selected"<?php } ?>><?php echo str_replace_formatted_placeholder("%extension", $format, $lang["field-fileextension"]) ?></option><?php
			}

		?></select><?php showProfileChooser(); ?></td>
		<td class="DownloadButton"><a id="convertDownload" onClick="return CentralSpaceLoad(this,true);"><?php
			echo $lang['action-download'] ?></a></td>
		</tr><?php
		}
	?></table><?php
	hook("formatchooseraftertable");
	if ($downloadCount > 0)
		{
	?><script type="text/javascript">
		// Store size info in JavaScript array
		var sizeInfo = {
			<?php
			foreach ($sizes as $n => $size)
				{
				if ($size['width'] == $closestSize)
					$size = $originalSize;
			?>
			<?php echo $n ?>: {
				'info': '<?php echo get_size_info($size, $originalSize) ?>',
				'id': '<?php echo $size['id'] ?>',
			},
			<?php } ?>
		};
		function updateSizeInfo() {
			var selected = jQuery('select#size').find(":selected").val();
			jQuery('#sizeInfo').html(sizeInfo[selected]['info']);
		}
		function updateDownloadLink() {
			var index = jQuery('select#size').find(":selected").val();
			var selectedFormat = jQuery('select#format').find(":selected").val();
			var profile = jQuery('select#profile').find(":selected").val();
			if (profile)
				profile = "&profile=" + profile;
			else
				profile = '';

			basePage = 'pages/download_progress.php?ref=<?php echo $ref ?>&ext='
					+ selectedFormat.toLowerCase() + profile + '&size=' + sizeInfo[index]['id']
					+ '&search=<?php echo urlencode($search) ?>&offset=<?php echo $offset ?>'
					+ '&k=<?php echo $k ?>&archive=<?php echo $archive ?>&sort='
					+ '<?php echo $sort?>&order_by=<?php echo $order_by ?>';

			jQuery('a#convertDownload').attr('href', '<?php echo $baseurl_short;
						if (!$direct_download)
							{
							echo 'pages/terms.php?ref=' . $ref . '&search=' . $search . '&k='
									. $k . '&url=';
							}
					?>' + <?php echo $direct_download ? 'basePage' : 'encodeURIComponent(basePage)' ?>
					);
		}
		jQuery(document).ready(function() {
			updateSizeInfo();
			updateDownloadLink();
		});
		jQuery('select#size').change(function() {
			updateSizeInfo();
			updateDownloadLink();
		});
		jQuery('select#format').change(function() {
			updateDownloadLink();
		});
		jQuery('select#profile').change(function() {
			updateDownloadLink();
		});
	</script><?php
		}
	return true;
	}

?>
