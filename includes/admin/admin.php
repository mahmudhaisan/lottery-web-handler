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


// Function to update selected_time based on user meta and lottery settings
function update_user_selected_time_based_on_lottery_settings()
{
    // Get predefined lottery times from options
    $lottery_time_1 = get_option('lottery_time_1', false);
    $lottery_time_2 = get_option('lottery_time_2', false);
    $lottery_time_3 = get_option('lottery_time_3', false);

    // If none of the lottery times are set, exit early
    if (!$lottery_time_1 && !$lottery_time_2 && !$lottery_time_3) {
        return;
    }

    // Query users who have 'selected_time_key' meta with one of the values 'lottery_time_1', 'lottery_time_2', or 'lottery_time_3'
    $args = [
        'meta_key'   => 'selected_time_key', // We are interested in users who have 'selected_time_key' meta
        'meta_compare' => 'IN', // Match if the value is one of the keys
        'fields'     => 'ID'    // Only fetch user IDs
    ];


    // Get all users who have 'selected_time_key' matching 'lottery_time_1', 'lottery_time_2', or 'lottery_time_3'
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();

    // Loop through each user and update their selected_time if needed
    if (!empty($users)) {
        foreach ($users as $user_id) {


            // Get the current selected_time_key for the user
            $selected_time_key = get_user_meta($user_id, 'selected_time_key', true);


            // Check and update selected_time based on available lottery times
            if ($selected_time_key == 'lottery_time_1' && $lottery_time_1) {
                update_user_meta($user_id, 'selected_time', $lottery_time_1);
            } elseif ($selected_time_key == 'lottery_time_2' && $lottery_time_2) {
                update_user_meta($user_id, 'selected_time', $lottery_time_2);
            } elseif ($selected_time_key == 'lottery_time_3' && $lottery_time_3) {
                update_user_meta($user_id, 'selected_time', $lottery_time_3);
            }
        }
    }
}

// Hook the function to run when settings are saved or updated
add_action('updated_option', 'update_user_selected_time_based_on_lottery_settings', 10, 3);





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
                    <th>Method</th>
                    <td><?php echo esc_html($request->method); ?></td>
                </tr>
                <tr>
                    <th>Withdraw Nunber</th>
                    <td><?php echo esc_html($request->withdraw_number); ?></td>
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


// Render deposit page
function render_deposit_management_page()
{
    $deposit_table = new Deposit_Requests_List_Table();
    $deposit_table->prepare_items();
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Deposit Management</h1>
        <form method="post">
            <?php $deposit_table->display(); ?>
        </form>
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













// Callback for Lottery Participants Page with Separate Pagination per Group in Tabs
function render_lottery_participants_page()
{
    global $wpdb;

    // Define how many users per page
    $users_per_page = 20;

    // Get users grouped by their selected lottery time
    $grouped_users = get_users_grouped_by_selected_time();

    echo '<div class="wrap" style="max-width: 1000px; margin: auto;">';
    echo '<h1 style="text-align: center; margin-bottom: 20px;">Lottery Participants</h1>';
    echo '<p style="text-align: center; font-size: 16px; color: #555;">Here you can view all participants, their selected lottery time, and current taka.</p>';

    // Ensure $grouped_users is not empty
    if (!empty($grouped_users)) {
    
        // Get the first tab key (earliest lottery time)
        $first_key = key($grouped_users);
    
        // Determine the active tab (default to the first tab if not set)
        $active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $grouped_users) ? $_GET['tab'] : $first_key;
    
        // Tab navigation
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($grouped_users as $selected_time => $participants) {
            
            $group_key = sanitize_key($selected_time); // Ensure safe URL parameter
            $active_class = ($active_tab == $group_key) ? 'nav-tab-active' : '';
            
            echo '<a href="' . esc_url(add_query_arg('tab', $group_key, admin_url('admin.php?page=lottery-participants'))) . '" class="nav-tab ' . $active_class . '">'
                . esc_html($selected_time) . ' Minutes Lottery</a>';
        }
        echo '</h2>';
    }
     

    // Determine active tab
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : key($grouped_users);

    if (isset($grouped_users[$active_tab])) {
        $participants = $grouped_users[$active_tab];
        $group_key = sanitize_key($active_tab);

        // Get the current page number for this group
        $current_page = isset($_GET["paged_$group_key"]) ? max(1, intval($_GET["paged_$group_key"])) : 1;
        $offset = ($current_page - 1) * $users_per_page;

        // Paginate participants for the current lottery time group
        $paginated_participants = array_slice($participants, $offset, $users_per_page);

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

        if (!empty($paginated_participants)) {
            foreach ($paginated_participants as $participant_id) {
                $user = get_userdata($participant_id);
                $user_login = $user ? $user->user_login : 'N/A';
                $selected_time_value = get_user_meta($participant_id, 'selected_time', true);
                $points = get_user_meta($participant_id, 'total_user_points', true) ?: 0;

                echo '<tr style="border-bottom: 1px solid #ddd;">';
                echo '<td style="padding: 12px; font-size: 14px; color: #333;">' . esc_html($participant_id) . '</td>';
                echo '<td style="padding: 12px; font-size: 14px; color: #333;">' . esc_html($user_login) . '</td>';
                echo '<td style="padding: 12px; font-size: 14px; color: #333;">' . esc_html($selected_time_value) . '</td>';
                echo '<td style="padding: 12px; font-size: 14px; color: #007cba;">' . esc_html($points) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4" style="padding: 12px; text-align: center; color: #555;">No participants found.</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';

        // Pagination Links for this group
        $total_pages = ceil(count($participants) / $users_per_page);
        if ($total_pages > 1) {
            echo '<div style="margin-top: 20px; text-align: center;">';

            echo paginate_links([
                'base'      => add_query_arg("paged_$group_key", '%#%'),
                'format'    => '',
                'current'   => $current_page,
                'total'     => $total_pages,
                'prev_text' => __('&laquo; Prev'),
                'next_text' => __('Next &raquo;'),
                'add_args'  => [
                    'tab' => $group_key
                ]
            ]);

            echo '</div>';
        }
    }

    echo '</div>';
}


function create_payment_instruction_cpt() {
    $labels = array(
        'name' => 'Payment Instructions',
        'singular_name' => 'Payment Instruction',
        'menu_name' => 'Payment Instructions',
        'name_admin_bar' => 'Payment Instruction',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Payment Instruction',
        'new_item' => 'New Payment Instruction',
        'edit_item' => 'Edit Payment Instruction',
        'view_item' => 'View Payment Instruction',
        'all_items' => 'All Payment Instructions',
        'search_items' => 'Search Payment Instructions',
        'not_found' => 'No payment instructions found.',
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-money',
        'supports' => array('title', 'editor'),
        'capability_type' => 'post',
        'has_archive' => false,
    );

    register_post_type('payment_instruction', $args);
}
add_action('init', 'create_payment_instruction_cpt');
