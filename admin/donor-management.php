<div class="wrap">
    <h1>Donor Management</h1>
    <button id="add-new-donor" class="button button-primary">Add New Donor</button>

    <div id="new-donor-container" style="display:none;">
        <h2>Add a New Donor</h2>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="save_donor">
            <label>Name: <input type="text" name="name" required></label><br>
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Phone: <input type="text" name="phone" required></label><br>
            <label>One-time/Recurring: <input type="checkbox" name="recurring"></label><br>
            <label>Payment Method:</label>
            <select name="payment_method">
                <option value="bkash">Bkash</option>
                <option value="nagad">Nagad</option>
            </select><br>
            <input type="submit" class="button button-primary" value="Save Donor">
        </form>
    </div>

    <h2>Existing Donors</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>Serial</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Amount Donated</th>
                <th>Edit/Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $donors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}donors");
            if ($donors) {
                foreach ($donors as $index => $donor) {
                    echo "<tr>
                        <td>". ($index + 1) ."</td>
                        <td>{$donor->name}</td>
                        <td>{$donor->email}</td>
                        <td>{$donor->phone}</td>
                        <td>{$donor->donation_date}</td>
                        <td>{$donor->amount_donated}</td>
                        <td>
                            <a href='#' class='edit-donor'>Edit</a> |
                            <a href='#' class='delete-donor'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No donors found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('#add-new-donor').on('click', function() {
        $('#new-donor-container').toggle();
    });
});
</script>

<?php
// Handle the donor form submission
function save_donor() {
    global $wpdb;

    // Sanitize and process the form data
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $recurring = isset($_POST['recurring']) ? 1 : 0;
    $payment_method = sanitize_text_field($_POST['payment_method']);

    // Insert the donor data into the database
    $wpdb->insert(
        "{$wpdb->prefix}donors",
        array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'recurring' => $recurring,
            'payment_method' => $payment_method,
            'donation_date' => current_time('mysql'),
            'amount_donated' => 0,
            'form_id' => 0 // Placeholder, to be linked to a specific form
        )
    );

    wp_redirect(admin_url('admin.php?page=donor-management'));
    exit;
}
add_action('admin_post_save_donor', 'save_donor');
