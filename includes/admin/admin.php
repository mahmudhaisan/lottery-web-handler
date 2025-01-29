<?php


// Add main menu and submenus
add_action('admin_menu', function () {
    // Main menu: Lottery Dashboard
    add_menu_page(
        'Lottery Dashboard', // Page title
        'Lottery Dashboard', // Menu title
        'manage_options',    // Capability
        'lottery-dashboard', // Menu slug
        'render_lottery_dashboard_page', // Callback function
        'dashicons-tickets-alt', // Icon
        25 // Position
    );

    // Submenu: Withdrawal Requests
    add_submenu_page(
        'lottery-dashboard', // Parent slug
        'Withdrawal Requests', // Page title
        'Withdrawal Requests', // Menu title
        'manage_options', // Capability
        'withdrawal-requests', // Menu slug
        'render_withdrawal_admin_page' // Callback function
    );

    // Submenu: Settings
    add_submenu_page(
        'lottery-dashboard', // Parent slug
        'Lottery Settings', // Page title
        'Settings', // Menu title
        'manage_options', // Capability
        'lottery-settings', // Menu slug
        'render_lottery_settings_page' // Callback function
    );


    add_submenu_page(
        'lottery-dashboard',  // Parent slug (Lottery Dashboard)
        'Lottery Participants', // Page title
        'Participants',  // Menu title
        'manage_options', // Capability
        'lottery-participants',  // Menu slug
        'render_lottery_participants_page'  // Callback function
    );


    // Add Deposit Management submenu
    add_submenu_page(
        'lottery-dashboard',  // Parent slug
        'Deposit Management', // Page title
        'Manage Deposits',  // Menu title
        'manage_options', // Capability
        'deposit-management',  // Menu slug
        'render_deposit_management_page'  // Callback function
    );
});



// Register settings
function register_lottery_settings()
{
    register_setting('lottery_settings_group', 'lottery_time_1');
    register_setting('lottery_settings_group', 'lottery_time_2');
    register_setting('lottery_settings_group', 'lottery_time_3');
    register_setting('lottery_settings_group', 'lottery_points_1');
    register_setting('lottery_settings_group', 'lottery_points_2');
    register_setting('lottery_settings_group', 'lottery_points_3');
}
add_action('admin_init', 'register_lottery_settings');


// অ্যাডমিন সেটিংস পেজ
function render_lottery_settings_page()
{
?>
    <div class="wrap">
        <h2>লটারি সেটিংস</h2>
        <form method="post" action="options.php">
            <?php settings_fields('lottery_settings_group'); ?>
            <?php do_settings_sections('lottery_settings_group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">প্রথম লটারি সময় (মিনিট)</th>
                    <td><input type="number" name="lottery_time_1" value="<?php echo esc_attr(get_option('lottery_time_1', '5')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">দ্বিতীয় লটারি সময় (মিনিট)</th>
                    <td><input type="number" name="lottery_time_2" value="<?php echo esc_attr(get_option('lottery_time_2', '10')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">তৃতীয় লটারি সময় (মিনিট)</th>
                    <td><input type="number" name="lottery_time_3" value="<?php echo esc_attr(get_option('lottery_time_3', '15')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">প্রথম লটারি পুরস্কার (৳)</th>
                    <td><input type="number" name="lottery_points_1" value="<?php echo esc_attr(get_option('lottery_points_1', '10')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">দ্বিতীয় লটারি পুরস্কার (৳)</th>
                    <td><input type="number" name="lottery_points_2" value="<?php echo esc_attr(get_option('lottery_points_2', '20')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">তৃতীয় লটারি পুরস্কার (৳)</th>
                    <td><input type="number" name="lottery_points_3" value="<?php echo esc_attr(get_option('lottery_points_3', '30')); ?>" /></td>
                </tr>
            </table>

            <?php submit_button('সংরক্ষণ করুন'); ?>
        </form>
    </div>
<?php
}



















// Callback for Lottery Dashboard
function render_lottery_dashboard_page()
{
    // Retrieve the lottery reward settings from the options, or set to default (10) if not set
    $lottery_5_min_rewards = get_option('lottery_5_min_rewards', 10);  // Default value is 10
    $lottery_10_min_rewards = get_option('lottery_10_min_rewards', 10);  // Default value is 10
    $lottery_15_min_rewards = get_option('lottery_15_min_rewards', 10);  // Default value is 10

    echo '<div class="wrap" style="max-width: 800px; margin: auto;">';
    echo '<h1 style="text-align: center; margin-bottom: 20px;">Lottery Dashboard</h1>';
    echo '<p style="text-align: center; font-size: 16px; color: #555;">Welcome to the Lottery Dashboard! Here you can view and manage all lottery-related data and features.</p>';

    // Display lottery data in a styled table
    echo '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">';
    echo '<thead>';
    echo '<tr style="background: #007cba; color: #fff; text-align: left;">';
    echo '<th style="padding: 12px; font-size: 16px;">Lottery Interval</th>';
    echo '<th style="padding: 12px; font-size: 16px;">Reward Amount</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr style="border-bottom: 1px solid #ddd;">';
    echo '<td style="padding: 12px; font-size: 14px; color: #333;">5-Minute Lottery</td>';
    echo '<td style="padding: 12px; font-size: 14px; color: #007cba;">' . esc_html($lottery_5_min_rewards) . '</td>';
    echo '</tr>';
    echo '<tr style="border-bottom: 1px solid #ddd;">';
    echo '<td style="padding: 12px; font-size: 14px; color: #333;">10-Minute Lottery</td>';
    echo '<td style="padding: 12px; font-size: 14px; color: #007cba;">' . esc_html($lottery_10_min_rewards) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="padding: 12px; font-size: 14px; color: #333;">15-Minute Lottery</td>';
    echo '<td style="padding: 12px; font-size: 14px; color: #007cba;">' . esc_html($lottery_15_min_rewards) . '</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';

    echo '<div style="text-align: center; margin-top: 30px;">';
    echo '<a href="' . admin_url('admin.php?page=lottery-settings') . '" class="button button-primary" style="padding: 10px 20px; font-size: 16px; text-decoration: none; border-radius: 5px;">Manage Settings</a>';
    echo '</div>';
    echo '</div>';
}









function render_edit_withdrawal_page()
{
    if (!isset($_GET['request_id']) || !is_numeric($_GET['request_id'])) {
        wp_die('Invalid request ID.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'withdrawal_requests';
    $request_id = intval($_GET['request_id']);
    $request = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $request_id));




    if (!$request) {
        wp_die('Request not found.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_status = sanitize_text_field($_POST['status']);
        $completed_time = current_time('mysql');

        // Update status and completed time if status is completed
        $wpdb->update(
            $table_name,
            [
                'status'         => $new_status,
                'completed_time' => $new_status === 'completed' ? $completed_time : null,
            ],
            ['id' => $request_id]
        );

        // If the request status is completed, deduct points from the user
        if ($new_status === 'completed') {
            $user_id = $request->user_id;
            $amount = $request->amount;

            // Get current total points from user meta
            $user_total_points = intval(get_user_meta($user_id, 'total_user_points', true));

            // If user has enough points, deduct the amount
            if ($user_total_points >= $amount) {
                $new_total_points = $user_total_points - $amount;
                update_user_meta($user_id, 'total_user_points', $new_total_points);
            } else {
                wp_die('Insufficient points to complete the withdrawal.');
            }
        }

        wp_redirect(admin_url('admin.php?page=withdrawal-requests'));
        exit;
    }

?>
    <div class="wrap">
        <h1>Edit Withdrawal Request</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th>User Name</th>
                    <td><?php echo esc_html(get_user_by('id', $request->user_id)->display_name); ?></td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td><?php echo esc_html($request->amount); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <select name="status">
                            <option value="pending" <?php selected($request->status, 'pending'); ?>>Pending</option>
                            <option value="completed" <?php selected($request->status, 'completed'); ?>>Completed</option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
            </p>
        </form>
    </div>
<?php
}




// Render Deposit Management Page
function render_deposit_management_page()
{
    global $wpdb;

    // Handle deposit status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $deposit_index = isset($_POST['deposit_index']) ? intval($_POST['deposit_index']) : -1;
        $action = sanitize_text_field($_POST['action']); // approve or delete

        if ($user_id > 0 && $deposit_index >= 0) {
            // Get user meta for deposit requests
            $deposit_requests = get_user_meta($user_id, 'deposit_requests', true);

            if ($deposit_requests && isset($deposit_requests[$deposit_index])) {
                if ($action === 'approve') {
                    // Approve the request
                    $deposit_requests[$deposit_index]['status'] = 'approved';
                    $deposit_requests[$deposit_index]['completed_time'] = current_time('mysql');

                    // Update total points
                    $current_points = get_user_meta($user_id, 'total_user_points', true);
                    $current_points = $current_points ? intval($current_points) : 0;
                    $current_points += intval($deposit_requests[$deposit_index]['amount']);
                    update_user_meta($user_id, 'total_user_points', $current_points);

                    echo '<div class="notice notice-success is-dismissible"><p>Deposit approved and points added successfully.</p></div>';
                } elseif ($action === 'delete') {
                    // Delete the request
                    unset($deposit_requests[$deposit_index]);
                    $deposit_requests = array_values($deposit_requests); // Re-index the array
                    echo '<div class="notice notice-success is-dismissible"><p>Deposit request deleted successfully.</p></div>';
                }

                // Save updated requests
                update_user_meta($user_id, 'deposit_requests', $deposit_requests);
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Invalid deposit request.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Invalid form data.</p></div>';
        }
    }

    // Fetch all users with deposit requests
    $users = get_users(['meta_key' => 'deposit_requests']);
?>
    <div class="wrap">
        <h1>Deposit Management</h1>
        <p>Below is the list of deposit requests. Approve or delete them as needed.</p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Deposit Amount</th>
                    <th>Sender Number</th>
                    <th>Status</th>
                    <th>Request Time</th>
                    <th>Screenshot</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $deposit_requests = get_user_meta($user->ID, 'deposit_requests', true);
                    if (!$deposit_requests) continue;

                    foreach ($deposit_requests as $index => $request): ?>
                        <tr>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td>৳<?php echo esc_html($request['amount']); ?> BDT</td>
                            <td><?php echo esc_html($request['sender_number']); ?></td>
                            <td><?php echo ucfirst(esc_html($request['status'])); ?></td>
                            <td><?php echo esc_html($request['request_time']); ?></td>
                            <td>
                                <?php if (!empty($request['screenshot_url'])): ?>
                                    <a href="<?php echo esc_url($request['screenshot_url']); ?>" target="_blank">View Screenshot</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
                                        <input type="hidden" name="deposit_index" value="<?php echo esc_attr($index); ?>">
                                        <button type="submit" name="action" value="approve" class="button button-primary">Approve</button>
                                        <button type="submit" name="action" value="delete" class="button button-secondary">Delete</button>
                                    </form>
                                <?php elseif ($request['status'] === 'approved'): ?>
                                    <button class="button button-disabled" disabled>Completed</button>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}











add_action('admin_menu', function () {
    add_submenu_page(
        null,
        'Edit Withdrawal Request',
        'Edit Withdrawal',
        'manage_options',
        'edit-withdrawal',
        'render_edit_withdrawal_page'
    );
});
// Callback for Lottery Participants Page
function render_lottery_participants_page()
{
    // Get users grouped by their selected lottery time
    $grouped_users = get_users_grouped_by_selected_time();


    echo '<div class="wrap" style="max-width: 1000px; margin: auto;">';
    echo '<h1 style="text-align: center; margin-bottom: 20px;">Lottery Participants</h1>';
    echo '<p style="text-align: center; font-size: 16px; color: #555;">Here you can view all participants, their selected lottery time, and current points.</p>';

    // Loop through each lottery time group and display participants
    foreach ($grouped_users as $selected_time => $participants) {
        // Start the table for each lottery time
        echo '<h2 style="margin-top: 40px; font-size: 24px; color: #007cba;">' . esc_html($selected_time) . ' Minitues Lottery</h2>';
        echo '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">';
        echo '<thead>';
        echo '<tr style="background: #007cba; color: #fff; text-align: left;">';
        echo '<th style="padding: 12px; font-size: 16px;">User ID</th>';
        echo '<th style="padding: 12px; font-size: 16px;">Username</th>';
        echo '<th style="padding: 12px; font-size: 16px;">Lottery Time</th>';
        echo '<th style="padding: 12px; font-size: 16px;">Current Points</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop through participants in the current selected time group
        foreach ($participants as $participant_id) {
            // Get user data from ID
            $user = get_userdata($participant_id);

            // Retrieve user login, selected time, and points (assuming 'points' is a user meta field)
            $user_login = $user ? $user->user_login : 'N/A'; // Fallback if user not found
            // $user_id = $user ? $user->ID : 'N/A'; // Fallback if user not found
            $selected_time = get_user_meta($participant_id, 'selected_time', true); // Assuming 'selected_time' is stored in user meta
            $points = get_user_meta($participant_id, 'total_user_points', true) ?: 0; // Assuming 'points' is stored in user meta, default to 0 if not set

            // Display the user data in the table
            echo '<tr style="border-bottom: 1px solid #ddd;">';
            echo '<td style="padding: 12px; font-size: 14px; color: #333;">' . esc_html($participant_id) . '</td>';
            echo '<td style="padding: 12px; font-size: 14px; color: #333;">' . esc_html($user_login) . '</td>';
            echo '<td style="padding: 12px; font-size: 14px; color: #333;">' . esc_html($selected_time) . '</td>';
            echo '<td style="padding: 12px; font-size: 14px; color: #007cba;">' . esc_html($points) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }

    echo '</div>';
}
