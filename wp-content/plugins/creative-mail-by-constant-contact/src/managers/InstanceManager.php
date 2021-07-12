<?php


namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\OptionsHelper;
use WP_Error;

/**
 * Class InstanceManager
 *
 * @package CreativeMail\Managers
 */
class InstanceManager
{
    public function __construct()
    {

    }

    public function add_hooks()
    {

    }

    /**
     * Handles the callback from the WordPress API and will store all the instance details.
     *
     * @param $request
     *
     * @return bool|WP_Error
     */
    public function handle_callback($request)
    {
        $account_information = json_decode($request->get_body());
        if ($account_information === null) {
            return new WP_Error('rest_bad_request', 'Invalid account details', array('status' => 400));
        }

        // Store the account information in the settings
        OptionsHelper::set_instance_id($account_information->site_id);
        OptionsHelper::set_instance_api_key($account_information->api_key);
        OptionsHelper::set_connected_account_id($account_information->account_id);

        // refresh woo commerce api key
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            CreativeMail::get_instance()->get_api_manager()->refresh_key();
        }

        return true;
    }
}
