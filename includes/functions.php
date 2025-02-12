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
        AND (
          capabilities.meta_value LIKE '%\"subscriber\"%' 
          OR capabilities.meta_value LIKE '%\"administrator\"%'
      )
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




function display_payment_instructions() {
    $args = array(
        'post_type'      => 'payment_instruction',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No payment instructions available.</p>';
    }

    $output = '<h5 class="mb-4">পেমেন্ট নির্দেশনা</h5>';
    $output .= '<div class="row">';

    while ($query->have_posts()) {
        $query->the_post();
        $title = get_the_title();
        $content = get_the_content();

        $output .= '
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6 class="font-weight-bold">' . esc_html($title) . '</h6>
                    <p>' . wp_kses_post($content) . '</p>
                </div>
            </div>';
    }

    wp_reset_postdata();
    $output .= '</div>';

    return $output;
}