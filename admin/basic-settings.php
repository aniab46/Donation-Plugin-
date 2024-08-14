<div class="wrap">
    <h1>Basic Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('wp_donation_plugin_settings'); ?>
        <?php do_settings_sections('wp_donation_plugin_settings'); ?>
        <h3>Payment Options:</h3>
        <label><input type="checkbox" name="wp_donation_plugin[bkash_enabled]" value="1" <?php checked(1, get_option('wp_donation_plugin')['bkash_enabled'], true); ?> /> Enable Bkash</label><br>
        <label><input type="checkbox" name="wp_donation_plugin[nagad_enabled]" value="1" <?php checked(1, get_option('wp_donation_plugin')['nagad_enabled'], true); ?> /> Enable Nagad</label><br>

        <h3>Payment Setup:</h3>
        <label>Bkash API Key: <input type="text" name="wp_donation_plugin[bkash_api_key]" value="<?php echo esc_attr(get_option('wp_donation_plugin')['bkash_api_key']); ?>"></label><br>
        <label>Nagad API Key: <input type="text" name="wp_donation_plugin[nagad_api_key]" value="<?php echo esc_attr(get_option('wp_donation_plugin')['nagad_api_key']); ?>"></label><br>

        <input type="submit" class="button button-primary" value="Save Settings">
    </form>
</div>

<?php
// Register the settings
function wp_donation_plugin_settings_init() {
    register_setting('wp_donation_plugin_settings', 'wp_donation_plugin');
}
add_action('admin_init', 'wp_donation_plugin_settings_init');
