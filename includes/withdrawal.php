<?php


// Register the WP_List_Table class
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Withdrawal_Requests_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'withdrawal_request',
            'plural'   => 'withdrawal_requests',
            'ajax'     => false,
        ]);
    }

    // Define columns
    public function get_columns()
    {
        return [
            'cb'             => '<input type="checkbox" />', // Checkbox for bulk actions
            'user_id'        => 'User Name',
            'amount'         => 'Amount',
            'amount'         => 'Amount',
            'method'         => 'method',
            'status'         => 'Status',
            'withdraw_number'   => 'withdraw_number',
            'completed_time' => 'Completed Time',
        ];
    }

    // Define sortable columns
    public function get_sortable_columns()
    {
        return [
            'user_id'      => ['user_id', true],
            'amount'       => ['amount', false],
            'status'       => ['status', false],
            'request_time' => ['request_time', false],
            'method' => ['method', false],
            'withdraw_number' => ['method', false],
        ];
    }

    // Add checkbox column
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="request_id[]" value="%s" />',
            esc_attr($item->id)
        );
    }

    // Display data for columns
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'user_id':
                // Fetch username from user ID
                $user = get_user_by('id', $item->user_id);
                $user_name = $user ? $user->display_name : 'Unknown User';
                return sprintf(
                    '<a href="%s">%s</a>',
                    admin_url('admin.php?page=edit-withdrawal&request_id=' . $item->id),
                    esc_html($user_name)
                );
            case 'amount':
                return esc_html($item->amount);
            case 'status':
                return esc_html($item->status);
            case 'request_time':
                return esc_html($item->request_time);
            case 'completed_time':
                return $item->status === 'completed' ? esc_html($item->completed_time) : 'N/A';
            case 'method':
                return esc_html($item->method);
            case 'withdraw_number':
                return esc_html($item->method);
            default:
                return ''; // For undefined columns
        }
    }

    // Prepare items, search, and pagination
    public function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'withdrawal_requests';

        // Handle search query
        $search = isset($_REQUEST['s']) ? trim($_REQUEST['s']) : '';
        $where  = $search ? $wpdb->prepare("WHERE user_id IN (SELECT ID FROM {$wpdb->users} WHERE display_name LIKE %s)", '%' . $wpdb->esc_like($search) . '%') : '';

        // Pagination setup
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Fetch total items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");

        // Fetch paginated results
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY request_time DESC LIMIT %d OFFSET %d", $per_page, $offset)
        );

        // Set items and pagination args
        $this->items = $results;
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    // Add bulk actions
    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
        ];
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'withdrawal_requests';

            $ids = isset($_REQUEST['request_id']) ? array_map('intval', $_REQUEST['request_id']) : [];
            if (!empty($ids)) {
                $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($ids_placeholder)", $ids));
            }
        }
    }
}


// Render admin page
function render_withdrawal_admin_page()
{
    $withdrawal_table = new Withdrawal_Requests_List_Table();

    // Process bulk actions
    $withdrawal_table->process_bulk_action();

    // Prepare table items
    $withdrawal_table->prepare_items();
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Withdrawal Requests</h1>

        <!-- Search Form -->
        <form method="get">
            <input type="hidden" name="page" value="withdrawal-requests" />
            <?php $withdrawal_table->search_box('Search User', 'search_user'); ?>
        </form>

        <!-- Display Table -->
        <form method="post">
            <?php
            $withdrawal_table->display();
            ?>
        </form>
    </div>
<?php
}
