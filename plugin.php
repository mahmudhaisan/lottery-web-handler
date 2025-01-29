<?php

/**
 * Plugin Name: Lottery Dashboard Handler
 * Plugin URI: https://github.com/mahmudhaisan/
 * Description: Lottery Dashboard Handler
 * Author: Mahmud haisan
 * Author URI: https://github.com/mahmudhaisan
 * Developer: Mahmud Haisan
 * Developer URI: https://github.com/mahmudhaisan
 * Text Domain: ldh
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


if (!defined('ABSPATH')) {
    die('are you cheating');
}

define("LDH_PLUGINS_PATH", plugin_dir_path(__FILE__));
define("LDH_PLUGINS_DIR_URL", plugin_dir_url(__FILE__));



// Create a custom table for storing lottery data on plugin activation
function create_lottery_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'lottery_data';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL to create the lottery data table
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        issued_time DATETIME NOT NULL,
        winner_id BIGINT(20) UNSIGNED NOT NULL,
        points_awarded INT NOT NULL,
        group_time VARCHAR(10) NOT NULL, -- Store the time group (e.g., '5', '10', '15')
        PRIMARY KEY (id),
        FOREIGN KEY (winner_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
    ) $charset_collate;";

    // Load WordPress upgrade library to use dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_lottery_table');



// Create the withdrawal_requests table on plugin activation
function create_withdrawal_requests_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'withdrawal_requests';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        amount INT(11) NOT NULL,
        method VARCHAR(20) NOT NULL, 
        withdraw_number VARCHAR(20) NOT NULL, 
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        request_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_time DATETIME NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_withdrawal_requests_table');












// register_deactivation_hook(__FILE__, 'clear_lottery_events_on_deactivation');

// Ensure some users exist for testing the lottery system
function ensure_lottery_users()
{
    $users_to_create = [
        ['user_login' => 'user1', 'email' => 'user1@example.com'],
        ['user_login' => 'user2', 'email' => 'user2@example.com'],
        ['user_login' => 'user3', 'email' => 'user3@example.com'],
    ];

    foreach ($users_to_create as $user_data) {
        if (!username_exists($user_data['user_login']) && !email_exists($user_data['email'])) {
            wp_create_user($user_data['user_login'], 'password123', $user_data['email']);
        }
    }
}
// add_action('init', 'ensure_lottery_users');















/**
 * Lottery System: Dynamic Winner Selection Based on User-Selected Time Intervals
 */





// Handle form submission to store the selected time and current timestamp in user meta
function handle_time_selection()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_time'])) {
        $current_user_id = get_current_user_id();
        $selected_time = sanitize_text_field($_POST['selected_time']);

        // Save user-selected time and timestamp
        update_user_meta($current_user_id, 'selected_time', $selected_time);
        update_user_meta($current_user_id, 'selected_time_updated_at', current_time('timestamp'));

        // Redirect to avoid resubmission
        wp_redirect(add_query_arg());
        exit;
    }
}
add_action('init', 'handle_time_selection');







// Add custom intervals for scheduling
function add_custom_cron_intervals($schedules)
{
    $schedules['lottery_every_5_minutes'] = [
        'interval' => 5 * 60, // 5 minutes
        'display'  => __('Every 5 Minutes'),
    ];
    $schedules['lottery_every_10_minutes'] = [
        'interval' => 10 * 60, // 10 minutes
        'display'  => __('Every 10 Minutes'),
    ];
    $schedules['lottery_every_15_minutes'] = [
        'interval' => 15 * 60, // 15 minutes
        'display'  => __('Every 15 Minutes'),
    ];
    return $schedules;
}
add_filter('cron_schedules', 'add_custom_cron_intervals');

// Unified function to schedule events
function schedule_lottery_events()
{
    $events = [
        'run_lottery_every_5_minutes'  => 'lottery_every_5_minutes',
        'run_lottery_every_10_minutes' => 'lottery_every_10_minutes',
        'run_lottery_every_15_minutes' => 'lottery_every_15_minutes',
    ];

    foreach ($events as $hook => $interval) {
        // Schedule only if no event is already scheduled for the given hook
        if (!wp_next_scheduled($hook)) {
            wp_schedule_event(time(), $interval, $hook);
        }
    }
}
add_action('wp', 'schedule_lottery_events');

// Process lottery event for a specific group with execution time tracking
function process_lottery_event($time_key)
{
    global $wpdb;

    // Option key to store the last processed time for this group
    $option_key = "lottery_last_processed_group_{$time_key}";

    // Get the last processed time
    $last_processed = get_option($option_key);

    // Calculate the next valid processing time
    $interval_in_seconds = $time_key * 60; // Convert group time to seconds
    $next_valid_time = $last_processed ? $last_processed + $interval_in_seconds : 0;

    // Check if enough time has passed
    if (!$last_processed || time() >= $next_valid_time) {


        $grouped_users = get_users_grouped_by_selected_time();

        if (isset($grouped_users[$time_key]) && !empty($grouped_users[$time_key])) {
            $users = $grouped_users[$time_key];
            $random_user_id = $users[array_rand($users)];

            // Update user points
            $current_points = get_user_meta($random_user_id, 'total_user_points', true);
            $new_points = $current_points ? $current_points + 10 : 10;
            update_user_meta($random_user_id, 'total_user_points', $new_points);

            // Log the winner
            $user = get_userdata($random_user_id);
            error_log("Winner (Group {$time_key} Minutes): {$user->user_login}, New Points: {$new_points}");

            // Save lottery data to the custom table
            global $wpdb;
            $table_name = $wpdb->prefix . 'lottery_data';
            $wpdb->insert($table_name, [
                'issued_time'   => current_time('mysql'),
                'winner_id'     => $random_user_id,
                'points_awarded' => 10,
                'group_time'    => $time_key,
            ]);


  
        } else {
            error_log("No users found for group: {$time_key} minutes.");
        }


        update_option($option_key, time());

        // Log for debugging
        error_log("Lottery processed for group {$time_key} at " . current_time('mysql'));


    } else {
        // Log that the event is skipped
        error_log("Lottery skipped for group {$time_key} at " . current_time('mysql') . " (Next run time: " . date('Y-m-d H:i:s', $next_valid_time) . ")");
    }
}

// Hook for each lottery event
add_action('run_lottery_every_5_minutes', function () {
    process_lottery_event(5);
});
add_action('run_lottery_every_10_minutes', function () {
    process_lottery_event(10);
});
add_action('run_lottery_every_15_minutes', function () {
    process_lottery_event(15);
});

// Clear scheduled events on plugin deactivation
function clear_lottery_cron_events_on_deactivation()
{
    $events = [
        'run_lottery_every_5_minutes',
        'run_lottery_every_10_minutes',
        'run_lottery_every_15_minutes',
    ];

    foreach ($events as $hook) {
        wp_clear_scheduled_hook($hook);
    }

    // Remove options storing last processed times
    delete_option('lottery_last_processed_group_5');
    delete_option('lottery_last_processed_group_10');
    delete_option('lottery_last_processed_group_15');
}
register_deactivation_hook(__FILE__, 'clear_lottery_cron_events_on_deactivation');
























// // Add custom intervals for scheduling
// function add_custom_cron_intervals($schedules)
// {
//     $schedules['lottery_every_5_minutes'] = [
//         'interval' => 1 * 60,
//         'display'  => __('Every 1 Minutes'),
//     ];
//     $schedules['lottery_every_10_minutes'] = [
//         'interval' => 2 * 60,
//         'display'  => __('Every 2 Minutes'),
//     ];
//     $schedules['lottery_every_15_minutes'] = [
//         'interval' => 3 * 60,
//         'display'  => __('Every 3 Minutes'),
//     ];
//     return $schedules;
// }
// add_filter('cron_schedules', 'add_custom_cron_intervals');






// // Schedule the events if not already scheduled
// function schedule_individual_events()
// {
//     // Print the next scheduled time for the 5-minute lottery event
//     $next_5_minute_time = wp_next_scheduled('run_lottery_every_5_minutes');
//     if ($next_5_minute_time) {
//         echo 'Next scheduled time for 5-minute lottery: ' . date('Y-m-d H:i:s', $next_5_minute_time) . "<br>";
//     } else {
//         echo 'The 5-minute lottery event is not scheduled.' . "<br>";

//         // Schedule the 5-minute lottery event
//         wp_schedule_event(time(), 'lottery_every_5_minutes', 'run_lottery_every_5_minutes');
//     }

//     // Print the next scheduled time for the 10-minute lottery event
//     $next_10_minute_time = wp_next_scheduled('run_lottery_every_10_minutes');
//     if ($next_10_minute_time) {
//         echo 'Next scheduled time for 10-minute lottery: ' . date('Y-m-d H:i:s', $next_10_minute_time) . "<br>";
//     } else {
//         echo 'The 10-minute lottery event is not scheduled.' . "<br>";

//         // Schedule the 10-minute lottery event
//         wp_schedule_event(time(), 'lottery_every_10_minutes', 'run_lottery_every_10_minutes');
//     }

//     // Print the next scheduled time for the 15-minute lottery event
//     $next_15_minute_time = wp_next_scheduled('run_lottery_every_15_minutes');
//     if ($next_15_minute_time) {
//         echo 'Next scheduled time for 15-minute lottery: ' . date('Y-m-d H:i:s', $next_15_minute_time) . "<br>";
//     } else {
//         echo 'The 15-minute lottery event is not scheduled.' . "<br>";

//         // Schedule the 15-minute lottery event
//         wp_schedule_event(time(), 'lottery_every_15_minutes', 'run_lottery_every_15_minutes');
//     }
// }
// add_action('wp', 'schedule_individual_events');



// // Add dummy actions for each event
// add_action('run_lottery_every_5_minutes', function () {
//     error_log('5-minute lottery triggered at ' . date('Y-m-d H:i:s'));
// });
// add_action('run_lottery_every_10_minutes', function () {
//     error_log('10-minute lottery triggered at ' . date('Y-m-d H:i:s'));
// });
// add_action('run_lottery_every_15_minutes', function () {
//     error_log('15-minute lottery triggered at ' . date('Y-m-d H:i:s'));
// });




// // Process lottery event for a specific group
// function process_lottery_event($time_key)
// {


//     global $wpdb;
//     $table_name = $wpdb->prefix . 'lottery_data';
//     $wpdb->insert($table_name, [
//         'issued_time'   => current_time('mysql'),
//         'winner_id'     => 1,
//         'points_awarded' => 10,
//         'group_time'    => $time_key,
//     ]);


//     // $grouped_users = get_users_grouped_by_selected_time();

//     // if (isset($grouped_users[$time_key]) && !empty($grouped_users[$time_key])) {
//     //     $users = $grouped_users[$time_key];
//     //     $random_user_id = $users[array_rand($users)];

//     //     // Update user points
//     //     $current_points = get_user_meta($random_user_id, 'total_user_points', true);
//     //     $new_points = $current_points ? $current_points + 10 : 10;
//     //     update_user_meta($random_user_id, 'total_user_points', $new_points);

//     //     // Log the winner
//     //     $user = get_userdata($random_user_id);
//     //     error_log("Winner (Group {$time_key} Minutes): {$user->user_login}, New Points: {$new_points}");

//     //     // Save lottery data to the custom table
//     //     global $wpdb;
//     //     $table_name = $wpdb->prefix . 'lottery_data';
//     //     $wpdb->insert($table_name, [
//     //         'issued_time'   => current_time('mysql'),
//     //         'winner_id'     => $random_user_id,
//     //         'points_awarded' => 10,
//     //         'group_time'    => $time_key,
//     //     ]);
//     // } else {
//     //     error_log("No users found for group: {$time_key} minutes.");
//     // }
// }

// // Hook for 5-minute event
// add_action('run_lottery_every_5_minutes', 'process_5_minute_lottery_event');
// function process_5_minute_lottery_event()
// {
//     process_lottery_event(5);
// }

// // Hook for 10-minute event
// add_action('run_lottery_every_10_minutes', 'process_10_minute_lottery_event');
// function process_10_minute_lottery_event()
// {
//     process_lottery_event(10);
// }

// // Hook for 15-minute event
// add_action('run_lottery_every_15_minutes', 'process_15_minute_lottery_event');
// function process_15_minute_lottery_event()
// {
//     process_lottery_event(15);
// }

// // Clear scheduled events on plugin deactivation
// function clear_custom_cron_events_on_deactivation()
// {
//     wp_clear_scheduled_hook('run_lottery_every_5_minutes');
//     wp_clear_scheduled_hook('run_lottery_every_10_minutes');
//     wp_clear_scheduled_hook('run_lottery_every_15_minutes');
// }
// register_deactivation_hook(__FILE__, 'clear_custom_cron_events_on_deactivation');


















function remove_all_other_cron_jobs()
{
    // Get all scheduled cron events
    $cron_events = _get_cron_array();

    // Define your custom cron hooks
    $my_cron_hooks = [
        'run_lottery_every_5_minutes',
        'run_lottery_every_10_minutes',
        'run_lottery_every_15_minutes',
    ];

    if ($cron_events) {
        foreach ($cron_events as $timestamp => $events) {
            foreach ($events as $hook => $details) {
                // If the hook is not one of your custom hooks, remove it
                if (!in_array($hook, $my_cron_hooks)) {
                    wp_clear_scheduled_hook($hook);
                }
            }
        }
    }
}
add_action('init', 'remove_all_other_cron_jobs');



// // Dynamically schedule events for each group
// function schedule_lottery_events_by_group()
// {
//     $grouped_users = get_users_grouped_by_selected_time();

//     foreach ($grouped_users as $time_key => $users) {
//         if (!empty($users)) {
//             $hook_name = "run_lottery_event_{$time_key}";
//             $interval_name = "every_{$time_key}_minutes";

//             // Schedule the event if not already scheduled
//             if (!wp_next_scheduled($hook_name)) {
//                 wp_schedule_event(time(), $interval_name, $hook_name);
//             }
//         }
//     }
// }
// add_action('wp', 'schedule_lottery_events_by_group');

// // Process lottery event for a specific group
// function process_lottery_event($time_key)
// {
//     $grouped_users = get_users_grouped_by_selected_time();

//     if (isset($grouped_users[$time_key]) && !empty($grouped_users[$time_key])) {
//         $users = $grouped_users[$time_key];
//         $random_user_id = $users[array_rand($users)];

//         // Update user points
//         $current_points = get_user_meta($random_user_id, 'total_user_points', true);
//         $new_points = $current_points ? $current_points + 10 : 10;
//         update_user_meta($random_user_id, 'total_user_points', $new_points);

//         // Log the winner
//         $user = get_userdata($random_user_id);
//         error_log("Winner (Group {$time_key} Minutes): {$user->user_login}, New Points: {$new_points}");

//         // Save lottery data to the custom table
//         global $wpdb;
//         $table_name = $wpdb->prefix . 'lottery_data';
//         $wpdb->insert($table_name, [
//             'issued_time'   => current_time('mysql'),
//             'winner_id'     => $random_user_id,
//             'points_awarded' => 10,
//             'group_time'    => $time_key,
//         ]);
//     } else {
//         error_log("No users found for group: {$time_key} minutes.");
//     }
// }



// // Clear all scheduled events on plugin deactivation
// function clear_lottery_events_on_deactivation()
// {
//     $grouped_users = get_users_grouped_by_selected_time();

//     foreach ($grouped_users as $time_key => $users) {
//         $hook_name = "run_lottery_event_{$time_key}";
//         wp_clear_scheduled_hook($hook_name);
//     }
// }
// register_deactivation_hook(__FILE__, 'clear_lottery_events_on_deactivation');









































































































































function time_left_until_exceeded($user_id, $days)
{
    // Retrieve the stored timestamp from user meta
    $stored_timestamp = get_user_meta($user_id, 'selected_time_updated_at', true);

    // Check if the timestamp exists
    if (!$stored_timestamp) {
        return [
            'days' => $days,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'show_time_selection' => true

        ]; // Default to full days if no timestamp
    }

    // Get the current timestamp
    $current_timestamp = current_time('timestamp');

    // Calculate the total seconds for the given days
    $days_in_seconds = $days * 24 * 60 * 60;

    // Calculate the remaining seconds
    $remaining_seconds = ($stored_timestamp + $days_in_seconds) - $current_timestamp;;

    // If time has already passed, return 0 for all values
    if ($remaining_seconds <= 0) {
        return [
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
            'show_time_selection' => true
        ];
    }

    // Calculate remaining days
    $remaining_days = floor($remaining_seconds / (24 * 60 * 60));

    // Calculate remaining hours
    $remaining_hours = floor(($remaining_seconds % (24 * 60 * 60)) / (60 * 60));

    // Calculate remaining minutes
    $remaining_minutes = floor(($remaining_seconds % (60 * 60)) / 60);

    // Calculate remaining seconds
    $remaining_seconds = $remaining_seconds % 60;

    return [
        'days' => $remaining_days,
        'hours' => $remaining_hours,
        'minutes' => $remaining_minutes,
        'seconds' => $remaining_seconds,
        'show_time_selection' => false
    ];
}



add_action('template_redirect', function() {
    // Get the current URL path
    $current_url = untrailingslashit($_SERVER['REQUEST_URI']);
    
    // Define the paths
    $dashboard_path = '/lottery-dashboard';
    $lottery_path = '/lottery';

    // Check if the user is logged in
    if (is_user_logged_in()) {
        // Redirect from the dashboard to the lottery page
        if ($current_url === $dashboard_path) {
            wp_redirect(home_url($lottery_path));
            exit;
        }
    } else {
        // Check if a non-logged-in user is on the lottery page
        if ($current_url === $lottery_path) {
            wp_redirect(add_query_arg(
                array('lottery-page' => 'dashboard'),
                home_url($lottery_path)
            ));
            exit;
        }
    }


    if (is_account_page()) {
        
        // Check if the user is logged in
        if (is_user_logged_in()) {
            // Redirect logged-in users to the Lottery page
            wp_redirect(home_url('/lottery'));
            exit;
        }
    }
});






// Add custom phone number field to Xoo Registration form
add_action('xoo_custom_phone_field', function ($args) {
    if ($args === 'register') { // Only add to the registration form
?>

        <div class=" xoo-aff-group xoo-aff-cont-email one xoo-aff-cont-required xoo_el_reg_phone_cont">
        <label for="xoo_el_reg_username" class="xoo-aff-label"> মোবাইল নাম্বার</label>
        <div class=" xoo-aff-input-group custom-phone-reg">
                <span class="xoo-aff-input-icon fas fa-phone"></span>
                <input type="text" class="xoo-aff-required xoo-aff-phone"
                    name="xoo_el_reg_phone"
                    placeholder="<?php esc_attr_e(' মোবাইল নাম্বার', 'xoo-el'); ?>"
                    value="<?php echo isset($_POST['xoo_phone']) ? esc_attr($_POST['xoo_phone']) : ''; ?>"
                    required autocomplete="tel">
            </div>
        </div>

    <?php
    }
});






















//snippets code 

function custom_admin_user_styles() {
    echo '<style>
        .user-action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            display: inline-block;
            margin-right: 5px;
        }
        .user-action-btn.activate {
            background-color: #28a745; /* Green */
        }
        .user-action-btn.deny {
            background-color: #dc3545; /* Red */
        }
        .user-action-btn.activated {
            background-color: #6c757d; /* Grey */
            cursor: default;
            text-decoration: none;
        }
    </style>';
}
add_action('admin_head', 'custom_admin_user_styles');





add_action('wp_head', function() {
    if (!is_user_logged_in()) {
		
        echo '<style>
            .my-account-class {
                display: none !important;
            }
        </style>';
    }
});

add_filter('woocommerce_account_content', function($content) {
    // Add a wrapper with a new class
    return '<div class="custom-my-account-class">' . $content . '</div>';
});


// Step 1: Add "Pending" role
function add_pending_user_role() {
    add_role('pending', 'Pending', [
        'read' => false, // Restrict access
    ]);
}
add_action('init', 'add_pending_user_role');

// Step 2: Set new users to "Pending" role
function set_user_as_pending($user_id) {
    $user = new WP_User($user_id);
    $user->set_role('pending');

    // Send confirmation email
    $activation_code = md5($user_id . time());
    update_user_meta($user_id, 'activation_code', $activation_code);

    $activation_url = add_query_arg([
        'action' => 'activate_user',
        'code'   => $activation_code,
        'user'   => $user_id,
    ], home_url());

    wp_mail($user->user_email, 'Activate Your Account', 'Click here to activate your account: ' . $activation_url);
}
add_action('user_register', 'set_user_as_pending');

// Step 3: Handle account activation
function handle_user_activation() {
    if (isset($_GET['action']) && $_GET['action'] === 'activate_user' && !empty($_GET['code']) && !empty($_GET['user'])) {
        $user_id = intval($_GET['user']);
        $activation_code = sanitize_text_field($_GET['code']);

        $stored_code = get_user_meta($user_id, 'activation_code', true);
        if ($activation_code === $stored_code) {
            $user = new WP_User($user_id);
            $user->set_role('subscriber'); // Set to "Subscriber" or any other role you prefer

            delete_user_meta($user_id, 'activation_code'); // Clean up
            wp_redirect(home_url('/activation-success')); // Redirect to a success page
            exit;
        } else {
            wp_die('Invalid activation code.');
        }
    }
}
add_action('init', 'handle_user_activation');

// Step 4: Prevent "Pending" users from logging in
function restrict_pending_users($user, $username, $password) {
    if (in_array('pending', (array) $user->roles)) {
        return new WP_Error('pending_account', 'Your account is pending approval. Admin will check and let you know.');
    }
    return $user;
}
add_filter('authenticate', 'restrict_pending_users', 30, 3);

// Step 1: Add a custom column to the Users table
function add_activate_user_column($columns) {
    $columns['activate_user'] = 'Activate User'; // Add a new column
    return $columns;
}
add_filter('manage_users_columns', 'add_activate_user_column');


function display_activate_user_column($value, $column_name, $user_id) {
    if ($column_name === 'activate_user') {
        $user = get_userdata($user_id);

        if (in_array('pending', (array) $user->roles)) {
            // Activation URL
            $activation_url = add_query_arg([
                'action' => 'admin_activate_user',
                'user_id' => $user_id,
                '_wpnonce' => wp_create_nonce('admin_activate_user_' . $user_id),
            ], admin_url('users.php'));

            // Deny URL
            $deny_url = add_query_arg([
                'action' => 'admin_deny_user',
                'user_id' => $user_id,
                '_wpnonce' => wp_create_nonce('admin_deny_user_' . $user_id),
            ], admin_url('users.php'));

            // Render Activate and Deny buttons
            return sprintf(
                '<a href="%s" class="user-action-btn activate">Activate</a>
                <a href="%s" class="user-action-btn deny">Deny</a>',
                esc_url($activation_url),
                esc_url($deny_url)
            );
        } else {
            // User already activated
            return '<span class="user-action-btn activated">Activated</span>';
        }
    }
    return $value;
}
add_filter('manage_users_custom_column', 'display_activate_user_column', 10, 3);





add_filter('manage_users_custom_column', 'display_activate_user_column', 10, 3);

// Step 3: Handle the activation logic when the link is clicked
function handle_admin_user_activation() {
    if (isset($_GET['action']) && $_GET['action'] === 'admin_activate_user' && !empty($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);

        // Verify nonce for security
        if (!wp_verify_nonce($_GET['_wpnonce'], 'admin_activate_user_' . $user_id)) {
            wp_die('Invalid nonce.');
        }

        $user = get_userdata($user_id);
        if (in_array('pending', (array) $user->roles)) {
            // Change the user role from "Pending" to "Subscriber"
            $user = new WP_User($user_id);
            $user->set_role('subscriber'); // Change to desired role

            // Redirect with a success message
            wp_redirect(add_query_arg('user_activated', 'true', admin_url('users.php')));
            exit;
        } else {
            wp_die('User is already activated or invalid role.');
        }
    }
}
add_action('admin_init', 'handle_admin_user_activation');

// Step 4: Add a success message after activation
function display_user_activation_notice() {
    if (isset($_GET['user_activated']) && $_GET['user_activated'] === 'true') {
        echo '<div class="updated notice is-dismissible"><p>User successfully activated.</p></div>';
    }
}
add_action('admin_notices', 'display_user_activation_notice');


function handle_admin_user_denial() {
    if (isset($_GET['action']) && $_GET['action'] === 'admin_deny_user' && !empty($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);

        // Verify nonce for security
        if (!wp_verify_nonce($_GET['_wpnonce'], 'admin_deny_user_' . $user_id)) {
            wp_die('Invalid nonce.');
        }

        $user = get_userdata($user_id);
        if ($user && in_array('pending', (array) $user->roles)) {
            // Delete the user
            wp_delete_user($user_id);

            // Redirect with a success message
            wp_redirect(add_query_arg('user_denied', 'true', admin_url('users.php')));
            exit;
        } else {
            wp_die('Invalid user or role for denial.');
        }
    }
}
add_action('admin_init', 'handle_admin_user_denial');


function display_user_denial_notice() {
    if (isset($_GET['user_denied']) && $_GET['user_denied'] === 'true') {
        echo '<div class="updated notice is-dismissible"><p>User successfully denied and deleted.</p></div>';
    }
}
add_action('admin_notices', 'display_user_denial_notice');





include_once LDH_PLUGINS_PATH . '/includes/admin/admin.php';
include_once LDH_PLUGINS_PATH . '/includes/frontend/frontend.php';
include_once LDH_PLUGINS_PATH . '/includes/withdrawal.php';
include_once LDH_PLUGINS_PATH . '/includes/functions.php';

if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
    include_once LDH_PLUGINS_PATH . '/ajax.php';
}


