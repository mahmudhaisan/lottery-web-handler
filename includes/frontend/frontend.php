<?php



// Add the Anonymous Browsing button functionality to the footer
function add_anonymous_browsing_script()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Create the "Anonymous Browsing" button with custom styles
            var anonymousButton = $(
                '<p><a href="#" class="button anonymous-browsing">গেস্ট  লগিন</a></p>'
            );

            // Append the button before the "Sign Up" button in the registration form
            //$('.xoo-el-register-btn').before(anonymousButton);

            // Also append the button to the login form above the "Remember me" section
            $('.xoo-el-login-btm-fields').before(anonymousButton.clone());

            // Style the button using jQuery CSS
            $('.anonymous-browsing').css({
                display: 'inline-block',
                padding: '10px 20px',
                margin: '15px 0',
                color: '#fff',
                backgroundColor: '#134cb3',
                borderRadius: '5px',
                textDecoration: 'none',
                textAlign: 'center',
                fontSize: '14px',
                fontWeight: 'bold',
                cursor: 'pointer',
            });

            // Add hover effect
            $('.anonymous-browsing').hover(
                function() {
                    $(this).css({
                        backgroundColor: '#005177',
                    });
                },
                function() {
                    $(this).css({
                        backgroundColor: '#0073aa',
                    });
                }
            );

            // Handle the button click for Anonymous Browsing
            $('.anonymous-browsing').on('click', function(e) {
                e.preventDefault(); // Prevent default behavior

                // Redirect to the /lottery page with the anonymous browsing flag
                window.location.href = '/lottery??lottery-page=dashboard';
            });

            // Optionally, prevent form submission if the anonymous browsing button is clicked
            $('.xoo-el-form-login, .xoo-el-form-register').on('submit', function(e) {
                if (window.location.href.indexOf('anonymous_browsing=true') > -1) {
                    e.preventDefault(); // Prevent form submission
                }
            });
        });
    </script>



<?php
}
add_action('wp_footer', 'add_anonymous_browsing_script', 100);





add_action('wp_enqueue_scripts', 'ldhp_custom_enqueue_assets');

// Enqueue CSS and JavaScript
function ldhp_custom_enqueue_assets()
{

    wp_enqueue_style('bootstrap-min', LDH_PLUGINS_DIR_URL . 'assets/css/bootstrap.min.css');
    wp_enqueue_style('fontawesome-css-min', LDH_PLUGINS_DIR_URL . 'assets/css/fontawesome.min.css');
    wp_enqueue_style('sb-admin-2', LDH_PLUGINS_DIR_URL . 'assets/css/sb-admin-2.css');
    wp_enqueue_style('style-css', LDH_PLUGINS_DIR_URL . 'assets/css/style.css');

    wp_enqueue_script('bootstrap-min', LDH_PLUGINS_DIR_URL . 'assets/js/bootstrap.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('jquery-easing-min-js', LDH_PLUGINS_DIR_URL . 'assets/js/jquery.easing.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('sb-admin-2', LDH_PLUGINS_DIR_URL . 'assets/js/sb-admin-2.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('script-js', LDH_PLUGINS_DIR_URL . 'assets/js/script.js', array('jquery'), '1.0.0', true);
    wp_localize_script(
        'script-js',
        'carpet_checkout',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),

        )
    );
}







function render_sidebar()
{
    $current_user_id = get_current_user_id();
    $selected_time = get_user_meta($current_user_id, 'selected_time', true);

    // Check the remaining time to select the lottery type again
    $selected_time_exceeds = time_left_until_exceeded($current_user_id, 7);
    $show_time_selection_bool = ($selected_time_exceeds['show_time_selection']);

    // Get current lottery times from the options
    $lottery_time_1 = get_option('lottery_time_1', 5);
    $lottery_time_2 = get_option('lottery_time_2', 10);
    $lottery_time_3 = get_option('lottery_time_3', 15);

    $current_page = isset($_GET['lottery-page']) ? $_GET['lottery-page'] : 'dashboard'; // Get the current lottery page


    ob_start();
?>
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo esc_url(home_url()); ?>">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-laugh-wink"></i>
            </div>
            <div class="sidebar-brand-text mx-3">লটারি</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?php echo esc_url(add_query_arg('lottery-page', 'dashboard', get_permalink())); ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>ড্যাশবোর্ড</span>
            </a>
        </li>

        <li class="nav-item <?php echo ($current_page === 'results') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?php echo esc_url(add_query_arg('lottery-page', 'results', get_permalink())); ?>">
                <i class="fas fa-fw fa-trophy"></i>
                <span>লটারি ফলাফল</span>
            </a>
        </li>

        <li class="nav-item <?php echo ($current_page === 'deposit') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?php echo esc_url(add_query_arg('lottery-page', 'deposit', get_permalink())); ?>">
                <i class="fas fa-fw fa-dollar-sign"></i>
                <span>ডিপোজিট</span>
            </a>
        </li>

        <?php if (is_user_logged_in()): ?>
            <hr class="sidebar-divider">
            <li class="nav-item">
                <div class="p-3">
                    <?php
                    if ($selected_time) {
                        // Display the selected time if it's set
                    ?>
                        <p class="text-white"> লটারির ধরন - <strong><?php echo esc_html($selected_time); ?> মিনিট লটারি</strong></p>
                        <p class="text-white">
                            <?php
                            // Show the time remaining before they can select again
                            if (!$show_time_selection_bool) {
                                $time_left = "নতুন লটারির ধরন নির্বাচন করতে পারবেন ";
                                if ($selected_time_exceeds['days'] > 0) {
                                    $time_left .= $selected_time_exceeds['days'] . " দিন ";
                                }

                                $time_left .= $selected_time_exceeds['hours'] . " ঘন্টা ";
                                $time_left .= $selected_time_exceeds['minutes'] . " মিনিট ";
                                $time_left .= $selected_time_exceeds['seconds'] . " সেকেন্ড পর ";

                                echo esc_html($time_left);
                            }
                            ?>
                        </p>
                    <?php
                    }
                    ?>
                </div>
            </li>
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Show the time selection form if the user is allowed to select or if no time is selected -->
            <li class="nav-item">
                <div class="p-3">
                    <?php
                    if (!$selected_time || $show_time_selection_bool) {
                        // Show the form if no time is selected or the user can select a new time
                    ?>
                        <form method="post" action="">
                            <label for="select-time" class="text-white">লটারির ধরন:</label>
                            <select id="select-time" name="selected_time" class="form-control mb-2">
                                <option value="<?php echo esc_attr($lottery_time_1); ?>" <?php selected($selected_time, $lottery_time_1); ?>><?php echo esc_html($lottery_time_1); ?> মিনিট</option>
                                <option value="<?php echo esc_attr($lottery_time_2); ?>" <?php selected($selected_time, $lottery_time_2); ?>><?php echo esc_html($lottery_time_2); ?> মিনিট</option>
                                <option value="<?php echo esc_attr($lottery_time_3); ?>" <?php selected($selected_time, $lottery_time_3); ?>><?php echo esc_html($lottery_time_3); ?> মিনিট</option>
                            </select>
                            <button type="submit" class="btn btn-success btn-block">সাবমিট</button>
                        </form>
                    <?php
                    }
                    ?>
                </div>
            </li>
        <?php endif; ?>
    </ul>
<?php
    return ob_get_clean();
}



// Get predefined lottery times from options
$lottery_time_1 = get_option('lottery_time_1', 5);
$lottery_time_2 = get_option('lottery_time_2', 10);
$lottery_time_3 = get_option('lottery_time_3', 15);

// Handle form submission to store the selected time and current timestamp in user meta
function handle_time_selection()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_time'])) {
        $current_user_id = get_current_user_id();
        $selected_time = sanitize_text_field($_POST['selected_time']);

        // Check which lottery time matches the selected time
        $lottery_time_key = '';

        if ($selected_time == get_option('lottery_time_1', 5)) {
            $lottery_time_key = 'lottery_time_1';
        } elseif ($selected_time == get_option('lottery_time_2', 10)) {
            $lottery_time_key = 'lottery_time_2';
        } elseif ($selected_time == get_option('lottery_time_3', 15)) {
            $lottery_time_key = 'lottery_time_3';
        }

        // Save the selected time and its corresponding lottery time key
        if ($lottery_time_key) {
            update_user_meta($current_user_id, 'selected_time', $selected_time);
            update_user_meta($current_user_id, 'selected_time_key', $lottery_time_key);
            update_user_meta($current_user_id, 'selected_time_updated_at', current_time('timestamp'));
        }

        // Redirect to avoid resubmission
        wp_redirect(add_query_arg());
        exit;
    }
}
add_action('init', 'handle_time_selection');




/**
 * Renders the topbar content of the dashboard, including the search bar and WooCommerce user info dropdown.
 *
 * @return string The HTML content of the topbar.
 */
function render_topbar()
{
    ob_start();

    // Get current WooCommerce user
    $current_user = wp_get_current_user();
    $user_avatar = get_avatar($current_user->ID, 32); // Get user avatar with size 32px
    $user_name = $current_user->display_name;

    $current_user_id = get_current_user_id();


?>
    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow navbar-custom-position">
        <!-- Sidebar Toggle (Topbar) -->
        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
            <i class="fa fa-bars"></i>
        </button>

        <?php if (is_user_logged_in()) : ?>
            <!-- User Info Dropdown (Aligned to the right) -->
            <div class="ml-auto dropdown user-dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-lg-inline text-gray-600 small"><?php echo esc_html($user_name); ?></span>

                    <div class="ml-2 user-avatar rounded-circle">
                        <?php echo $user_avatar; ?>
                    </div>
                </button>

                <!-- Logged-in User Dropdown Menu -->
                <ul class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="userDropdown">

                    <li>
                        <a class="dropdown-item" href="<?php echo esc_url(wp_logout_url(get_permalink(get_option('woocommerce_myaccount_page_id')))); ?>">Logout</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!is_user_logged_in()) : ?>
            <!-- Dropdown for Non-Logged-In Users -->
            <div class="dropdown text-end">
                <button class="btn btn-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Welcome, Guest
                </button>
                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                    <!-- Home Link -->
                    <li><a class="dropdown-item" href="<?php echo esc_url(home_url()); ?>">Home</a></li>

                    <!-- Login Link -->
                    <li><a class="dropdown-item" href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>">Login</a></li>

                    <!-- Register Link -->
                    <li><a class="dropdown-item" href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>">Register</a></li>
                </ul>
            </div>
        <?php endif; ?>

    </nav>
    <!-- End of Topbar -->

    <?php echo render_offcanvas_menu(); ?>


    <style>
        .user-avatar img {
            border-radius: 50%;
            width: 32px;
            height: 32px;
        }

        .user-dropdown .dropdown-menu {
            min-width: 200px;
        }

        .user-dropdown .dropdown-menu a {
            color: #5a5c69;
            text-decoration: none;
        }

        .user-dropdown .dropdown-menu a:hover {
            background-color: #f8f9fc;
        }

        .user-dropdown .dropdown-divider {
            margin: 0.5rem 0;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            $('#userDropdown').on('click', function(e) {
                e.preventDefault();
                $(this).next('.dropdown-menu').toggle();
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#userDropdown').length) {
                    $('.user-dropdown .dropdown-menu').hide();
                }
            });
        });
    </script>
<?php
    return ob_get_clean();
}


function render_offcanvas_menu()
{
    $current_user_id = get_current_user_id();
    $selected_time = get_user_meta($current_user_id, 'selected_time', true);

    // Get current lottery times from the options
    $lottery_time_1 = get_option('lottery_time_1', 5);
    $lottery_time_2 = get_option('lottery_time_2', 10);
    $lottery_time_3 = get_option('lottery_time_3', 15);
    $current_page = isset($_GET['lottery-page']) ? $_GET['lottery-page'] : 'dashboard'; // Get the current lottery page


    ob_start(); // Start output buffering
?>
    <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
        <!-- Offcanvas Header -->
        <div class="offcanvas-header bg-primary text-white">
            <a class="sidebar-brand d-flex align-items-center text-white text-decoration-none" href="<?php echo esc_url(home_url()); ?>">
                <div class="sidebar-brand-text mx-3 fs-5">লটারি</div>
            </a>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <!-- Offcanvas Body -->
        <div class="offcanvas-body p-3">
            <ul class="nav flex-column bg-light p-3 rounded shadow-sm" id="sidebarMenu">
                <!-- Sidebar Divider -->
                <hr class="sidebar-divider my-3">

                <!-- Dashboard Item -->
                <li class="nav-item mb-2">
                    <a class="nav-link d-flex align-items-center text-dark py-2 px-3 rounded hover-highlight <?php echo ($current_page === 'dashboard') ? 'active bg-primary' : ''; ?>" href="<?php echo esc_url(add_query_arg('lottery-page', 'dashboard', get_permalink())); ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        <span class="fw-bold">ড্যাশবোর্ড</span>
                    </a>
                </li>

                <!-- Lottery Results Item -->
                <li class="nav-item mb-2">
                    <a class="nav-link d-flex align-items-center text-dark py-2 px-3 rounded hover-highlight <?php echo ($current_page === 'results') ? 'active bg-primary' : ''; ?>" href="<?php echo esc_url(add_query_arg('lottery-page', 'results', get_permalink())); ?>">
                        <i class="fas fa-trophy me-2"></i>
                        <span class="fw-bold">লটারি ফলাফল</span>
                    </a>
                </li>

                <!-- Deposit Item -->
                <li class="nav-item mb-2">
                    <a class="nav-link d-flex align-items-center text-dark py-2 px-3 rounded hover-highlight <?php echo ($current_page === 'deposit') ? 'active bg-primary' : ''; ?>" href="<?php echo esc_url(add_query_arg('lottery-page', 'deposit', get_permalink())); ?>">
                        <i class="fas fa-fw fa-dollar-sign me-2"></i>
                        <span class="fw-bold">ডিপোজিট</span>
                    </a>
                </li>


                <!-- Additional Section for Logged-In Users -->
                <?php if (is_user_logged_in()) : ?>
                    <hr class="sidebar-divider">
                    <li class="nav-item">
                        <div class="bg-primary text-white p-3 rounded">
                            <?php if ($selected_time): ?>
                                <p class="mb-2">লটারির ধরন - <strong><?php echo esc_html($selected_time) . ' '; ?> মিনিট লটারি</strong></p>
                            <?php else : ?>
                                <form method="post" action="">
                                    <label for="select-time" class="text-white">লটারির ধরন:</label>
                                    <select id="select-time" name="selected_time" class="form-control mb-2">
                                        <option value="<?php echo esc_attr($lottery_time_1); ?>" <?php selected($selected_time, $lottery_time_1); ?>><?php echo esc_html($lottery_time_1); ?> মিনিট</option>
                                        <option value="<?php echo esc_attr($lottery_time_2); ?>" <?php selected($selected_time, $lottery_time_2); ?>><?php echo esc_html($lottery_time_2); ?> মিনিট</option>
                                        <option value="<?php echo esc_attr($lottery_time_3); ?>" <?php selected($selected_time, $lottery_time_3); ?>><?php echo esc_html($lottery_time_3); ?> মিনিট</option>
                                    </select>
                                    <button type="submit" class="btn btn-success btn-block">সাবমিট</button>
                                </form>
                            <?php endif; ?>


                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered output as a string
}







// Handle point redemption and withdrawal request
function render_dashboard_content()
{
    ob_start();

    if (!is_user_logged_in()) { ?>


        <!-- Begin Page Content -->
        <div class="container-fluid" id="dashboard">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">ড্যাশবোর্ড</h1>
            </div>


            <!-- User Points and Redeem Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">স্বাগতম!!</h6>
                </div>
                <div class="card-body">লটারি ড্যাশবোর্ডে স্বাগতম। লটারি অংশগ্রহণ করতে লগইন করুন</div>
            </div>

        </div>

    <?php


    } else {




        // Get current user ID and points
        $user_id = get_current_user_id();
        $user_points = get_user_meta($user_id, 'total_user_points', true);
        if ($user_points === '') {
            $user_points = 0; // Default points for demo
            update_user_meta($user_id, 'total_user_points', $user_points);
        }

        // Handle point redemption
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_points'], $_POST['withdraw_method'], $_POST['withdraw_number'])) {
            $redeem_points = intval($_POST['redeem_points']);
            $withdraw_method = sanitize_text_field($_POST['withdraw_method']);
            $withdraw_number = sanitize_text_field($_POST['withdraw_number']);

            if ($redeem_points > 0 && $redeem_points <= $user_points && !empty($withdraw_method) && !empty($withdraw_number)) {
                // Deduct the points and update user points
                // $user_points -= $redeem_points;
                // update_user_meta($user_id, 'total_user_points', $user_points);

                // Save redemption request in the database
                global $wpdb;
                $table_name = $wpdb->prefix . 'withdrawal_requests';
                $wpdb->insert($table_name, [
                    'user_id'        => $user_id,
                    'amount'         => $redeem_points,
                    'method'         => $withdraw_method,
                    'withdraw_number' => $withdraw_number,
                    'status'         => 'pending',
                    'request_time'   => current_time('mysql'),
                ]);

                // Display a success message
                echo '<div class="alert alert-success">Your withdrawal request is now pending.</div>';
            } else {
                echo '<div class="alert alert-danger">Invalid request. Please check your inputs.</div>';
            }
        }







    ?>



        <!-- Begin Page Content -->
        <div class="container-fluid" id="dashboard">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">ড্যাশবোর্ড</h1>
            </div>


            <!-- User Points and Redeem Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">বর্তমান ব্যালান্স</h6>
                </div>
                <div class="card-body">



                    <h4> <strong><?php echo '৳ ' . esc_html($user_points) . ' '; ?></strong>টাকা </h4>
                    <!-- Redeem Points Form -->
                    <?php if ($user_points > 0): ?>
                        <form method="POST" action="">
                            <div class="form-group mt-3">
                                <label for="redeem_points">টাকার পরিমানঃ</label>
                                <input type="number" name="redeem_points" id="redeem_points" class="form-control" min="1" max="<?php echo esc_attr($user_points); ?>" required>
                            </div>

                            <div class="form-group mt-3">
                                <label for="withdraw_method">উইথড্র মেথডঃ</label>
                                <select name="withdraw_method" id="withdraw_method" class="form-control" required>
                                    <option value="">-- মেথড নির্বাচন করুন --</option>
                                    <option value="bkash">বিকাশ</option>
                                    <option value="nagad">নগদ</option>
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <label for="withdraw_number"> বিকাশ/নগদ নাম্বারঃ</label>
                                <input type="text" name="withdraw_number" id="withdraw_number" class="form-control" pattern="[0-9]{11}" placeholder="01XXXXXXXXX" required>
                            </div>

                            <button type="submit" class="btn btn-primary">টাকা উইথড্র</button>
                        </form>

                    <?php else: ?>
                        <p class="text-danger">বর্তমানে আপনার ব্যালান্স শুন্য</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Withdrawal History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">টাকা উইথড্র এর ইতিহাস</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>টাকার পরিমান</th>
                                <th>উইথড্র মেথড</th>
                                <th>মোবাইল নাম্বার</th>
                                <th>বর্তমান অবস্থা</th>
                                <th>রিকুয়েস্ট সময়</th>
                                <th>টাকা প্রদানের সময়</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch user's withdrawal history
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'withdrawal_requests';
                            $requests = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id ORDER BY request_time DESC");
                            foreach ($requests as $request):
                            ?>
                                <tr>
                                    <td>৳<?php echo ' ' . esc_html($request->amount); ?> টাকা</td>
                                    <td><?php echo esc_html(ucfirst($request->method)); ?></td>
                                    <td><?php echo esc_html($request->withdraw_number); ?></td>
                                    <td><?php echo esc_html($request->status); ?></td>
                                    <td><?php echo esc_html($request->request_time); ?></td>
                                    <td><?php echo $request->status == 'completed' ? esc_html($request->completed_time) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>


    <?php         } ?>



    <!-- /.container-fluid -->
<?php
    return ob_get_clean();
}






















function render_history_content()
{
?>


    <div class="container-fluid">
        History
    </div>

<?php }




function render_results_content()
{
    global $wpdb;

    // Define default results per page
    $default_results_per_page = 5;

    // Get results per page from query parameter or use default
    $results_per_page = isset($_GET['results_per_page']) ? intval($_GET['results_per_page']) : $default_results_per_page;

    // Get current page number
    $paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;

    // Calculate the offset
    $offset = ($paged - 1) * $results_per_page;

    // Fetch results from the lottery table
    $table_name = $wpdb->prefix . 'lottery_data';
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY issued_time DESC LIMIT %d OFFSET %d",
            $results_per_page,
            $offset
        )
    );

    // Count the total number of results for pagination
    $total_results = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Calculate total pages
    $total_pages = ceil($total_results / $results_per_page);

    ob_start();
?>
    <div class="container-fluid" id="results">
        <!-- Results Filter -->
        <div class="row mb-3 bg-light p-2">
            <div class="col-md-12 d-flex justify-content-start">
                <form method="GET" action="" class="d-flex align-items-center">
                    <!-- Preserve existing query parameters -->
                    <?php foreach ($_GET as $key => $value): ?>
                        <?php if ($key !== 'results_per_page'): ?>
                            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Filter Layout -->
                    <div class="d-flex align-items-center me-3">
                        <label for="results_per_page" class="me-2 mb-0 fw-bold">Show:</label>
                        <select name="results_per_page" id="results_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="5" <?php selected($results_per_page, 5); ?>>5</option>
                            <option value="20" <?php selected($results_per_page, 20); ?>>20</option>
                            <option value="50" <?php selected($results_per_page, 50); ?>>50</option>
                            <option value="100" <?php selected($results_per_page, 100); ?>>100</option>
                        </select>
                    </div>

                    <!-- Reset Button -->
                    <button type="submit" name="results_per_page" value="5" class="btn btn-outline-secondary btn-sm ms-2">রিসেট </button>
                </form>
            </div>
        </div>

        <!-- Results Cards -->
        <div class="row">
            <?php if ($results): ?>
                <?php foreach ($results as $result): ?>
                    <?php echo render_result_card($result); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No results found.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php echo render_pagination($total_pages); ?>
    </div>
<?php
    return ob_get_clean();
}






/**
 * Renders a single result card.
 *
 * @param object $result The result object from the database.
 * @return string The HTML content of a single result card.
 */
function render_result_card($result)
{
    // print_r($result);
    // Get winner info (user details)
    $winner = get_user_by('ID', $result->winner_id);

    // Get total points of the winner
    $total_user_points = get_user_meta($winner->ID, 'total_user_points', true);


    ob_start();
?>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="card mb-4" style="width: 100%;">
                <div class="card-header bg-primary text-white text-center">
                    <strong>বিজয়ীর লটারী নাম্বার: <?php echo esc_html($winner->ID); ?></strong>
                </div>
                <div class="card-body">
                    <p class="card-text"><strong>পুরস্কার এর টাকা: </strong> <?php echo ' ৳ ' . esc_html($result->points_awarded); ?></p>
                    <p class="card-text"><strong>(বাংলাদেশ) তারিখ:</strong>
                        <?php
                        // Create a DateTime object from the issued_time
                        $datetime = new DateTime($result->issued_time);

                        // Set the locale to Bengali (Bangladesh)
                        setlocale(LC_TIME, 'bn_BD.UTF-8');

                        // Format the date and time without seconds, in AM/PM format
                        $formatted_date = $datetime->format('j F Y, g:i A');

                        // Convert AM/PM to Bengali
                        $formatted_date_bengali = str_replace(
                            ['AM', 'PM'],
                            ['AM', 'PM'], // You can customize the AM/PM text here if needed in Bengali.
                            $formatted_date
                        );

                        echo esc_html($formatted_date_bengali);
                        ?>
                    </p>

                    <p class="card-text"><strong>লটারির ধরন:</strong> <?php echo esc_html($result->group_time); ?> মিনিট লটারি</p>
                    <!-- Partially Hidden Username -->
                    <p class="card-text"><strong>পুরষ্কার বিজয়ীর নাম:</strong>
                        <?php
                        $username = get_user_by('ID', $result->winner_id)->user_login;
                        echo strlen($username) > 4 ? substr($username, 0, 2) . '****' . substr($username, -2) : $username;
                        ?>
                    </p>

                    <!-- Partially Hidden Phone Number -->
                    <p class="card-text"><strong>মোবাইল নাম্বার:</strong>
                        <?php
                        $phone = get_user_meta($result->winner_id, 'phone_number', true);
                        echo $phone ? substr($phone, 0, 4) . '****' . substr($phone, -4) : 'No phone number available';
                        ?>
                    </p>


                </div>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}









/**
 * Renders pagination with preserved query parameters, Bootstrap styling, Bangla texts, and links to First and Last pages.
 *
 * @param int $total_pages The total number of pages.
 * @return string The HTML content of pagination links.
 */
function render_pagination($total_pages)
{
    // Get the current page or default to 1.
    $paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;

    // Calculate the range of pages to display (only 10 pages at a time).
    $start_page = floor(($paged - 1) / 10) * 10 + 1;
    $end_page = min($start_page + 9, $total_pages);

    // Preserve the `lottery-page` parameter.
    $base_url = add_query_arg('lottery-page', 'results');

    ob_start();
?>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <!-- Go to First Page -->
            <?php if ($paged > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', 1, $base_url)); ?>" aria-label="First">
                        প্রথম
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">প্রথম</span>
                </li>
            <?php endif; ?>

            <?php if ($paged > 1): ?>
                <!-- Previous button -->
                <li class="page-item">
                    <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $paged - 1, $base_url)); ?>" aria-label="Previous">
                        পূর্ববর্তী
                    </a>
                </li>
            <?php else: ?>
                <!-- Disabled Previous button -->
                <li class="page-item disabled">
                    <span class="page-link">পূর্ববর্তী</span>
                </li>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i == $paged): ?>
                    <!-- Active page -->
                    <li class="page-item active" aria-current="page">
                        <span class="page-link"><?php echo $i; ?></span>
                    </li>
                <?php else: ?>
                    <!-- Regular page link -->
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $i, $base_url)); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($paged < $total_pages): ?>
                <!-- Next button -->
                <li class="page-item">
                    <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $paged + 1, $base_url)); ?>" aria-label="Next">
                        পরবর্তী
                    </a>
                </li>
            <?php else: ?>
                <!-- Disabled Next button -->
                <li class="page-item disabled">
                    <span class="page-link">পরবর্তী</span>
                </li>
            <?php endif; ?>

            <!-- Go to Last Page -->
            <?php if ($paged < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $total_pages, $base_url)); ?>" aria-label="Last">
                        শেষ
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">শেষ</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

<?php
    return ob_get_clean();
}
















/**
 * Renders the footer content of the dashboard.
 *
 * @return string The HTML content of the footer.
 */
function render_footer()
{
    ob_start();
?>
    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>&copy; Velki 2025</span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->
<?php
    return ob_get_clean();
}









/**
 * Main function to render the entire custom dashboard layout using shortcodes.
 * This function combines all the modularized functions to build the full dashboard page.
 *
 * @return string The HTML content of the entire dashboard page.
 */
function render_custom_template_shortcode()
{


    ob_start();


    // Get the lottery page parameter from the URL query string
    $page = isset($_GET['lottery-page']) ? sanitize_text_field($_GET['lottery-page']) : 'dashboard';

?>
    <div class="mw-100">

        <!-- Page Wrapper -->
        <div id="wrapper">

            <?php



            ?>
            <!-- Sidebar -->
            <?php echo render_sidebar(); ?>

            <!-- Content Wrapper -->
            <div id="content-wrapper" class="d-flex flex-column">

                <!-- Main Content -->
                <div id="content">


                    <!-- Topbar -->
                    <?php

                    // Check if the 'anonymous_browsing' query parameter is present in the URL



                    echo render_topbar();

                    ?>

                    <!-- Dashboard Content -->
                    <?php



                    // Check if the 'anonymous_browsing' query parameter is present in the URL
                    if (isset($_GET['anonymous_browsing'])) {
                        // Check if the 'lottery-page' query parameter is set to 'results'
                        if (isset($_GET['lottery-page']) && $_GET['lottery-page'] === 'results') {
                            // Show the results content
                            echo render_results_content();
                        }
                    } else {
                        if ($page === 'dashboard') {
                            echo render_dashboard_content();
                        } elseif ($page === 'results') {
                            echo render_results_content();
                        } elseif ($page === 'history') {
                            echo render_history_content();
                        } elseif ($page == 'deposit') {

                            echo render_deposit_content();
                        } else {
                            echo render_dashboard_content(); // Default to Dashboard
                        }
                    }





                    ?>

                </div>
                <!-- End of Main Content -->

                <!-- Footer -->
                <?php echo render_footer(); ?>

            </div>
            <!-- End of Content Wrapper -->

        </div>
        <!-- End of Page Wrapper -->

    </div>

    <?php
    return ob_get_clean();
}



// Register the shortcode for the dashboard
add_shortcode('lottery_dashboard', 'render_custom_template_shortcode');





function custom_login_text_translations($translated_text, $text, $domain)
{
    // Check for specific strings and replace them with your translations
    switch ($text) {
        case 'Forgot Password?':
            $translated_text = __('পাসওয়ার্ড  ভূলে গেছেন?', 'textdomain'); // Change this to your desired text
            break;
        case 'Remember me':
            $translated_text = __(' লগিন  মনে রাখুন', 'textdomain'); // Change this to your desired text
            break;
    }
    return $translated_text;
}
add_filter('gettext', 'custom_login_text_translations', 20, 3);






















function render_deposit_content()
{
    ob_start();

    if (!is_user_logged_in()) { ?>
        <div class="container-fluid" id="dashboard">
            <div class="alert alert-warning">Please log in to access your deposit options.</div>
        </div>
    <?php } else {
        $user_id = get_current_user_id();

        // Handle deposit request form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit_amount']) && is_numeric($_POST['deposit_amount'])) {
            $deposit_amount = intval($_POST['deposit_amount']);
            $sender_number = sanitize_text_field($_POST['sender_number']);
            $screenshot_url = handle_deposit_file_upload();

            // Fetch existing deposit requests from usermeta
            $deposit_requests = get_user_meta($user_id, 'deposit_requests', true);
            if (!$deposit_requests) {
                $deposit_requests = [];
            }

            // Create a new deposit request entry
            $new_deposit_request = [
                'amount' => $deposit_amount,
                'sender_number' => $sender_number,
                'status' => 'pending',
                'request_time' => current_time('mysql'),
                'screenshot_url' => $screenshot_url,
            ];

            // Add the new deposit request to the array
            $deposit_requests[] = $new_deposit_request;

            // Update the usermeta with the new deposit request array
            update_user_meta($user_id, 'deposit_requests', $deposit_requests);

            echo '<div class="alert alert-success">Your deposit request has been submitted and is pending review.</div>';
        }

        // Fetch user's deposit requests
        $deposit_requests = get_user_meta($user_id, 'deposit_requests', true);
    ?>

        <!-- Deposit Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">ডিপোজিট রিকুয়েস্ট</h6>
            </div>
            <div class="card-body">

                <?php echo display_payment_instructions(); ?>

                <!-- Deposit Form -->
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="deposit_amount">ডিপোজিটের পরিমাণ:</label>
                        <input type="number" name="deposit_amount" id="deposit_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="sender_number">পাঠানো নাম্বার:</label>
                        <input type="text" name="sender_number" id="sender_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="screenshot">লেনদেনের স্ক্রিনশট আপলোড করুন:</label>
                        <input type="file" name="screenshot" id="screenshot" class="form-control-file" required>
                    </div>
                    <button type="submit" class="btn btn-primary">ডিপোজিট রিকুয়েস্ট পাঠান</button>
                </form>
            </div>
        </div>


        <!-- Deposit History -->
        <?php if ($deposit_requests): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">ডিপোজিট এর ইতিহাস</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ডিপোজিট পরিমান</th>
                                <th>পাঠানো নাম্বার</th>
                                <th>বর্তমান অবস্থা</th>
                                <th>রিকুয়েস্ট সময়</th>
                                <th>স্ক্রিনশট</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deposit_requests as $request): ?>
                                <tr>
                                    <td>৳<?php echo esc_html($request['amount']); ?> টাকা</td>
                                    <td><?php echo esc_html($request['sender_number']); ?></td>
                                    <td><?php echo esc_html($request['status']); ?></td>
                                    <td><?php echo esc_html($request['request_time']); ?></td>
                                    <td>
                                        <?php if (!empty($request['screenshot_url'])): ?>
                                            <a href="<?php echo esc_url($request['screenshot_url']); ?>" target="_blank">স্ক্রিনশট দেখুন</a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="container-fluid mb-4">
                <p class="text-danger">কোনো ডিপোজিট ইতিহাস পাওয়া যায়নি।</p>
            </div>
<?php endif;
    }

    return ob_get_clean();
}

// Handle file upload for deposit screenshot
function handle_deposit_file_upload()
{
    if (isset($_FILES['deposit_screenshot']) && $_FILES['deposit_screenshot']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['deposit_screenshot'];
        $upload_dir = wp_upload_dir(); // Get the WordPress upload directory
        $target_dir = $upload_dir['path'] . '/'; // Define the target directory

        // Generate a unique filename
        $filename = wp_unique_filename($upload_dir['path'], $file['name']);
        $target_file = $target_dir . $filename;

        // Move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return $upload_dir['url'] . '/' . $filename; // Return the file URL
        }
    }

    return ''; // Return empty string if upload fails
}
