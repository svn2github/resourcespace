<?php

#function HookTrack_field_historyViewHOOKNAME()

function HookTrack_field_historyViewDisplay_field_modified_value($field)
{

	global $ref, $track_fields, $baseurl_short;

	if(in_array($field['ref'], $track_fields)) {

		$get_params = '?ref=' . $ref . '&field=' . $field['ref'] . '&field_title=' . $field['title'];

		$field['value'] .= '<a href="' . $baseurl_short . '/plugins/track_field_history/pages/field_history_log.php' . $get_params . '" style="margin-left: 20px;">&gt;&nbsp;History</a>';

	}

	return $field;

}

?>
