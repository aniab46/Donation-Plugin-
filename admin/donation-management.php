<div class="wrap">
    <h1>Donation Management</h1>
    <button id="add-new-form" class="button button-primary">Add New Form</button>

    <div id="new-form-container" style="display:none;">
        <h2>Create a New Donation Form</h2>
        <form method="post" enctype="multipart/form-data">
            <label>Organization Name: <input type="text" name="organization_name" required></label><br>
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Thumbnail Image: <input type="file" name="thumbnail_image" accept="image/*" required></label><br>
            <label>Enable Recurring Pay: <input type="checkbox" name="recurring_pay"></label><br>

            <h3>Form Design:</h3>
            <label>First Name: <input type="checkbox" name="hide_first_name"> Hide</label><br>
            <label>Last Name: <input type="checkbox" name="hide_last_name"> Hide</label><br>
            <label>Phone: <input type="checkbox" name="hide_phone"> Hide</label><br>
            <label>Address: <input type="checkbox" name="hide_address"> Hide</label><br>
            <label>Enable Recurring Option: <input type="checkbox" name="enable_recurring_option"></label><br>
            <label>Payment Options:</label><br>
            <label><input type="checkbox" name="payment_options[]" value="bkash"> Bkash</label><br>
            <label><input type="checkbox" name="payment_options[]" value="nagad"> Nagad</label><br>

            <input type="submit" name="save_donation_form" class="button button-primary" value="Save Form">
        </form>
    </div>

    <h2>Existing Donation Forms</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>Serial</th>
                <th>Thumbnail</th>
                <th>Organization Name</th>
                <th>Email</th>
                <th>Shortcode</th>
                <th>Total Donations</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}donation_forms");
            if ($forms) {
                foreach ($forms as $index => $form) {
                    echo "<tr>
                        <td>". ($index + 1) ."</td>
                        <td><img src='{$form->thumbnail}' width='50' height='50'></td>
                        <td>{$form->organization_name}</td>
                        <td>{$form->email}</td>
                        <td>{$form->shortcode}</td>
                        <td>". get_total_donations($form->id) ."</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No donation forms found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('#add-new-form').on('click', function() {
        $('#new-form-container').toggle();
    });
});
</script>

<?php
if (isset($_POST['save_donation_form'])) {
    global $wpdb;

    // Sanitize and process the form data
    $organization_name = sanitize_text_field($_POST['organization_name']);
    $email = sanitize_email($_POST['email']);
    $recurring_pay = isset($_POST['recurring_pay']) ? 1 : 0;
    $hide_first_name = isset($_POST['hide_first_name']) ? 1 : 0;
    $hide_last_name = isset($_POST['hide_last_name']) ? 1 : 0;
    $hide_phone = isset($_POST['hide_phone']) ? 1 : 0;
    $hide_address = isset($_POST['hide_address']) ? 1 : 0;
    $enable_recurring_option = isset($_POST['enable_recurring_option']) ? 1 : 0;
    $payment_options = isset($_POST['payment_options']) ? implode(',', $_POST['payment_options']) : '';

    // Handle thumbnail upload
    $thumbnail_url = '';
    if (!empty($_FILES['thumbnail_image']['name'])) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploaded_file = wp_handle_upload($_FILES['thumbnail_image'], array('test_form' => false));

        if (isset($uploaded_file['url'])) {
            $thumbnail_url = $uploaded_file['url'];
        } else {
            echo '<div class="error"><p>There was an error uploading the file.</p></div>';
            return;
        }
    }

    // Insert the form data into the database
$result = $wpdb->insert(
    "{$wpdb->prefix}donation_forms",
    array(
        'organization_name' => $organization_name,
        'email' => $email,
        'thumbnail' => $thumbnail_url,
        'recurring_pay' => $recurring_pay,
        'form_options' => json_encode(array(
            'hide_first_name' => $hide_first_name,
            'hide_last_name' => $hide_last_name,
            'hide_phone' => $hide_phone,
            'hide_address' => $hide_address,
            'enable_recurring_option' => $enable_recurring_option,
            'payment_options' => $payment_options,
        )),
    )
);

$form_id = $wpdb->insert_id;

if ($result !== false) {
    // Generate and update the shortcode
    $shortcode = '[donation_form id="' . $form_id . '"]';
    $wpdb->update(
        "{$wpdb->prefix}donation_forms",
        array('shortcode' => $shortcode),
        array('id' => $form_id)
    );
}


    if ($result === false) {
        echo '<div class="error"><p>There was an error saving the donation form.</p></div>';
    } else {
        echo '<div class="updated"><p>Donation form saved successfully.</p></div>';
        // Optionally redirect to avoid form resubmission
        echo "<script>window.location = '".admin_url('admin.php?page=donation-management')."';</script>";
        exit;
    }
}

function get_total_donations($form_id) {
    global $wpdb;
    $total = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount_donated) FROM {$wpdb->prefix}donors WHERE form_id = %d", $form_id));
    return $total ? $total : 0;
}
?>
