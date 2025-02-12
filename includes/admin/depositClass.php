<?php


// Register the WP_List_Table class
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Deposit_Requests_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'Deposit Request',
            'plural'   => 'Deposit Requests',
            'ajax'     => false,
        ]);
    }

    // Define table columns
    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox" />',  // Checkbox column
            'username'      => 'Username',
            'amount'        => 'Deposit Amount',
            'sender_number' => 'Sender Number',
            'status'        => 'Status',
            'request_time'  => 'Request Time',
            'screenshot'    => 'Screenshot',
        ];
    }

    // Define sortable columns
    public function get_sortable_columns()
    {
        return [
            'username'      => ['username', false],
            'amount'        => ['amount', false],
            'status'        => ['status', false],
            'request_time'  => ['request_time', false],
        ];
    }

    // Bulk actions
    public function get_bulk_actions()
    {
        return [
            'bulk_approve' => 'Approve',
            'bulk_delete'  => 'Delete',
        ];
    }

    // Prepare items (data population)
    public function prepare_items()
    {
        global $wpdb;

        $items_per_page = 10;
        $current_page   = $this->get_pagenum();

        // Fetch users who have deposit requests
        $users = get_users(['meta_key' => 'deposit_requests']);
        $data = [];

        foreach ($users as $user) {
            $deposit_requests = get_user_meta($user->ID, 'deposit_requests', true);

            if (is_array($deposit_requests) && !empty($deposit_requests)) {
                foreach ($deposit_requests as $index => $request) {
                    $data[] = [
                        'id'            => $user->ID . '-' . $index,
                        'username'      => $user->display_name,
                        'amount'        => '৳' . esc_html($request['amount']) . ' BDT',
                        'sender_number' => esc_html($request['sender_number']),
                        'status'        => ucfirst(esc_html($request['status'])),
                        'request_time'  => esc_html($request['request_time']),
                        'screenshot'    => !empty($request['screenshot_url']) ? '<a href="' . esc_url($request['screenshot_url']) . '" target="_blank">View</a>' : 'N/A',
                        'user_id'       => $user->ID,
                        'deposit_index' => $index,
                    ];
                }
            }
        }

        $this->items = $data;

        // Pagination args
        $this->set_pagination_args([
            'total_items' => count($data),
            'per_page'    => $items_per_page,
            'total_pages' => ceil(count($data) / $items_per_page),
        ]);

        // Column headers setup
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    // Define the callback function for the checkbox column
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="deposit_id[]" value="%s" />', $item['id']);
    }

    // Render column content
    public function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    // Custom column for actions
    public function column_action($item)
    {
        if ($item['status'] === 'Pending') {
            return '
                <form method="POST">
                    ' . wp_nonce_field('deposit_action', '_wpnonce', true, false) . '
                    <input type="hidden" name="user_id" value="' . esc_attr($item['user_id']) . '">
                    <input type="hidden" name="deposit_index" value="' . esc_attr($item['deposit_index']) . '">
                    <button type="submit" name="action" value="approve" class="button button-primary">Approve</button>
                    <button type="submit" name="action" value="delete" class="button button-secondary">Delete</button>
                </form>
            ';
        } elseif ($item['status'] === 'Approved') {
            return '<button class="button button-disabled" disabled>Completed</button>';
        }
        return '-';
    }

    // Process bulk actions
    public function process_bulk_action()
    {
        if ('bulk_approve' === $this->current_action()) {
            // Logic for bulk approving requests
            $ids = isset($_REQUEST['deposit_id']) ? array_map('intval', $_REQUEST['deposit_id']) : [];
            foreach ($ids as $id) {
                // Approve each request
                // You can add your logic to approve the requests here
            }
        } elseif ('bulk_delete' === $this->current_action()) {
            // Logic for bulk deleting requests
            $ids = isset($_REQUEST['deposit_id']) ? array_map('intval', $_REQUEST['deposit_id']) : [];
            foreach ($ids as $id) {
                // Delete each request
                // You can add your logic to delete the requests here
            }
        }
    }

    // Display the table
    public function display()
    {
        ?>
        <form method="post">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
            <?php
            parent::display();
            ?>
        </form>
        <?php
    }
}
