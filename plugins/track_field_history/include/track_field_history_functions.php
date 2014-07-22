<?php
if (!function_exists('track_field_history_get_field_log')) {
    
    function track_field_history_get_field_log($resource_id, $field_id) {
    
        $query = sprintf('
                   SELECT resource_log.date AS date,
                          IFNULL(user.fullname, user.username) AS user,
                          resource_log.previous_value AS value
                     FROM resource_log
                LEFT JOIN user ON user.ref = resource_log.user
                    WHERE type = "e"
                      AND resource = %d
                      AND resource_type_field = %d
                 ORDER BY resource_log.date DESC;
            ',
            $resource_id,
            $field_id
        );
        $log_results = sql_query($query);

        if(empty($log_results)) { return $log_results; }

        // Create an array with all the previous values and remove the last element as it will always be empty:
        $log_values = array();
        foreach ($log_results as $result) {
            $log_values[] = $result['value'];
        }
        array_pop($log_values);

        $query = sprintf('
                SELECT value
                  FROM resource_data
                 WHERE resource = %d
                   AND resource_type_field = %d;
            ',
            $resource_id,
            $field_id
        );
        $last_log_value = sql_value($query, '');
        
        for($i = 0; $i < count($log_results); $i++) {

            // Current value is recorded in a different place:
            if($i == 0) {
                $log_results[$i]['value'] = $last_log_value;
                continue;
            } 

            // Make sure each edit record has the next previous value:
            $log_results[$i]['value'] = $log_values[$i - 1];

        }

        return $log_results;
    
    }

}
?>