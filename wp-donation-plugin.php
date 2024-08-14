<?php
/**
 * Plugin Name: WP Donation Plugin
 * Description: A donation plugin with multiple features like form management, donor management, and payment integration.
 * Version: 1.0
 * Author: Your Name
 */

// Security check to prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the admin menu
add_action('admin_menu', 'wp_donation_plugin_menu');

function wp_donation_plugin_menu() {
    add_menu_page('Donation Management', 'Donations', 'manage_options', 'donation-management', 'donation_management_page');
    add_submenu_page('donation-management', 'Donor Management', 'Donors', 'manage_options', 'donor-management', 'donor_management_page');
    add_submenu_page('donation-management', 'Basic Settings', 'Settings', 'manage_options', 'basic-settings', 'basic_settings_page');
    add_submenu_page('donation-management', 'After Submission', 'After Submission', 'manage_options', 'after-submission', 'after_submission_page');
}

// Include admin pages
function donation_management_page() {
    include_once plugin_dir_path(__FILE__) . 'admin/donation-management.php';
}
function donor_management_page() {
    include_once plugin_dir_path(__FILE__) . 'admin/donor-management.php';
}
function basic_settings_page() {
    include_once plugin_dir_path(__FILE__) . 'admin/basic-settings.php';
}
function after_submission_page() {
    include_once plugin_dir_path(__FILE__) . 'admin/after-submission.php';
}


// Create custom database tables on plugin activation
function wp_donation_plugin_install() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $donation_forms_table = $wpdb->prefix . 'donation_forms';
    $sql = "CREATE TABLE $donation_forms_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        organization_name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        thumbnail varchar(255) NOT NULL,
        recurring_pay tinyint(1) DEFAULT 0,
        form_options text NOT NULL,
        shortcode varchar(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $donors_table = $wpdb->prefix . 'donors';
    $sql .= "CREATE TABLE $donors_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(15) NOT NULL,
        recurring tinyint(1) DEFAULT 0,
        payment_method varchar(50) NOT NULL,
        donation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        amount_donated float NOT NULL,
        form_id mediumint(9) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wp_donation_plugin_install');



function wp_donation_plugin_settings_init() {
    register_setting('wp_donation_plugin_settings', 'wp_donation_plugin');
}
add_action('admin_init', 'wp_donation_plugin_settings_init');


//process donation
function process_donation() {
    if (!isset($_POST['payment_method'])) {
        wp_die('No payment method selected.');
    }

    $payment_method = sanitize_text_field($_POST['payment_method']);
    $amount = sanitize_text_field($_POST['amount']); // Ensure you have an amount field in the form

    if ($payment_method === 'bkash') {
        // Handle Bkash payment processing
        $bkash_api_key = get_option('wp_donation_plugin')['bkash_api_key'];
        // Code to process payment using Bkash API
        // Use $bkash_api_key and other necessary data to make the API call
    } elseif ($payment_method === 'nagad') {
        // Handle Nagad payment processing
        $nagad_api_key = get_option('wp_donation_plugin')['nagad_api_key'];
        // Code to process payment using Nagad API
        // Use $nagad_api_key and other necessary data to make the API call
    } else {
        wp_die('Invalid payment method.');
    }

    // After processing, you might want to redirect or display a success message
    wp_redirect(home_url('/thank-you')); // Example redirect after payment
    exit;
}
add_action('admin_post_nopriv_process_donation', 'process_donation');
add_action('admin_post_process_donation', 'process_donation');


// Short code add
function render_donation_form($atts) {
    $options = get_option('wp_donation_plugin');
    global $wpdb;
    $atts = shortcode_atts(array('id' => 0), $atts, 'donation_form');
    $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}donation_forms WHERE id = %d", $atts['id']));
    if (!$form) return "Donation form not found.";

    $form_options = json_decode($form->form_options, true);
    ob_start();
    ?>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="process_donation">
        <input type="hidden" name="form_id" value="<?php echo esc_attr($form->id); ?>">
        <label>Name: <input type="text" name="name" required></label><br>
        <label>Email: <input type="email" name="email" required></label><br>
        <?php if (!$form_options['hide_first_name']) : ?>
            <label>First Name: <input type="text" name="first_name"></label><br>
        <?php endif; ?>
        <?php if (!$form_options['hide_last_name']) : ?>
            <label>Last Name: <input type="text" name="last_name"></label><br>
        <?php endif; ?>
        <?php if (!$form_options['hide_phone']) : ?>
            <label>Phone: <input type="text" name="phone"></label><br>
        <?php endif; ?>
        <?php if (!$form_options['hide_address']) : ?>
            <label>Address: <input type="text" name="address"></label><br>
        <?php endif; ?>
        <?php if ($form_options['enable_recurring_option']) : ?>
            <label>Recurring: <input type="checkbox" name="recurring"></label><br>
        <?php endif; ?>
        <label>Payment Method:</label><br>
        <?php if ($options['bkash_enabled']) : ?>
            <label><input type="radio" name="payment_method" value="bkash"> Bkash</label><br>
        <?php endif; ?>
        <?php if ($options['nagad_enabled']) : ?>
            <label><input type="radio" name="payment_method" value="nagad"> Nagad</label><br>
        <?php endif; ?>
        <input type="submit" value="Donate">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('donation_form', 'render_donation_form');

