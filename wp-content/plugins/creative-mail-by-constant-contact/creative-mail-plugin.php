<?php
/**
 * Creative Mail by Constant Contact
 *
 * @package CreativeMail
*/
/**
 * Plugin Name: Creative Mail by Constant Contact
 * Plugin URI: https://wordpress.org/plugins/creative-mail-by-constant-contact/
 * Description: Free email marketing designed specifically for WordPress, Jetpack and WooCommerce. Send newsletters, promotions, updates and transactional e-commerce emails. Simple and easy, powered by Constant Contact’s rock solid reliability.
 * Author: Constant Contact
 * Version: 1.3.8
 * Author URI: https://www.constantcontact.com
 * WC requires at least: 3.0.0
 * WC tested up to: 5.1.0
*/
use CreativeMail\CreativeMail;
function _load_ce4wp_plugin()
{
    global $creativemail;

    if($creativemail != null) {
        return true;
    }

    define('CE4WP_PLUGIN_DIR', __DIR__ . '/');
    define('CE4WP_PLUGIN_URL', plugin_dir_url(__FILE__) . '/');
    define('CE4WP_PLUGIN_FILE', __FILE__);
    define('CE4WP_PLUGIN_VERSION', '1.3.8');
    define('CE4WP_INSTANCE_UUID_KEY', 'ce4wp_instance_uuid');
    define('CE4WP_INSTANCE_HANDSHAKE_TOKEN', 'ce4wp_handshake_token');
    define('CE4WP_INSTANCE_HANDSHAKE_EXPIRATION', 'ce4wp_handshake_expiration');
    define('CE4WP_INSTANCE_ID_KEY', 'ce4wp_instance_id');
    define('CE4WP_INSTANCE_API_KEY_KEY', 'ce4wp_instance_api_key');
    define('CE4WP_ENCRYPTION_KEY_KEY', 'ce4wp_encryption_key');
    define('CE4WP_CONNECTED_ACCOUNT_ID', 'ce4wp_connected_account_id');
    define('CE4WP_ACTIVATED_PLUGINS', 'ce4wp_activated_plugins');
    define('CE4WP_MANAGED_EMAIL_NOTIFICATIONS', 'ce4wp_managed_email_notifications');
    define('CE4WP_ACCEPTED_CONSENT', 'ce4wp_accepted_consent');
    define('CE4WP_SYNCHRONIZE_ACTION', 'ce4wp_synchronize_contacts');
    define('CE4WP_CHECKOUT_CHECKBOX_TEXT', 'ce4wp_checkout_checkbox_text');
    define('CE4WP_CHECKOUT_CHECKBOX_ENABLED', 'ce4wp_checkout_checkbox_enabled');
    define('CE4WP_APP_GATEWAY_URL', 'https://app-gateway.creativemail.com/');
    define('CE4WP_APP_URL', 'https://app.creativemail.com/');
    define('CE4WP_ENVIRONMENT', 'PRODUCTION');
    define('CE4WP_BUILD_NUMBER', '1138');
    define('CE4WP_RAYGUN_PHP_KEY', 'Z85xL3mkgnW13Ri9DajGUg');
    define('CE4WP_BATCH_SIZE', 500);
    define('CE4WP_WC_API_KEY_ID', 'ce4wp_woocommerce_api_key_id');
    define('CE4WP_WC_API_CONSUMER_KEY', 'ce4wp_woocommerce_consumer_key');
    define('CE4WP_REFERRED_BY', 'ce4wp_referred_by');
    define('CE4WP_HIDE_BANNER', 'ce4wp_hide_banner');

    // Load all the required files
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        include_once __DIR__ . '/vendor/autoload.php';
    }

    $creativemail = CreativeMail::get_instance();
    $creativemail->add_hooks();

    return true;
}

function ce4wp_deactivate()
{
    delete_option('ce4wp_activated');
    delete_option('ce4wp_install_date');
}

function ce4wp_activate()
{
    add_option('ce4wp_activated', true);
    add_option('ce4wp_install_date', date('Y-m-d G:i:s'), '', 'yes');
    if (( isset($_REQUEST['action']) && 'activate-selected' === $_REQUEST['action'] )
        && ( isset($_POST['checked']) && count($_POST['checked']) > 1 )
    ) {
        return;
    }
    add_option('ce4wp_activation_redirect', wp_get_current_user()->ID);
}

add_action('plugins_loaded', '_load_ce4wp_plugin', 10);
register_activation_hook(__FILE__, 'ce4wp_activate');
register_deactivation_hook(__FILE__, 'ce4wp_deactivate');
