<?php 


// Fetch users grouped by their selected time, limited to subscribers
function get_users_grouped_by_selected_time()
{
    global $wpdb;

    $query = "
        SELECT DISTINCT um.meta_value AS selected_time, um.user_id
        FROM {$wpdb->usermeta} AS um
        INNER JOIN {$wpdb->usermeta} AS capabilities
            ON um.user_id = capabilities.user_id
        WHERE um.meta_key = 'selected_time'
          AND capabilities.meta_key = '{$wpdb->prefix}capabilities'
          AND capabilities.meta_value LIKE '%\"subscriber\"%'
    ";
    $results = $wpdb->get_results($query, ARRAY_A);

    $grouped_users = [];
    foreach ($results as $result) {
        $time_key = $result['selected_time'];
        if (!isset($grouped_users[$time_key])) {
            $grouped_users[$time_key] = [];
        }
        $grouped_users[$time_key][] = $result['user_id'];
    }



    return $grouped_users;
}
