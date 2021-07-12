<?php

namespace CreativeMail\Helpers;

use stdClass;

/**
 * Class CE4WP_OptionsHelper
 * Exposes a wrapper around all the options that we register within the plugin.
 *
 * @package CreativeMail\Helpers
 * @access  private
 */
class OptionsHelper
{
    /**
     * Gets the generated unique id for this WP instance, or will generate a new unique id if none is present.
     *
     * @return string
     */
    public static function get_instance_uuid()
    {

        // Do we already have a UUID?
        $instanceUuid = get_option(CE4WP_INSTANCE_UUID_KEY, null);
        if ($instanceUuid === null) {

            // Just generate one and store it
            $instanceUuid = uniqid();
            add_option(CE4WP_INSTANCE_UUID_KEY, $instanceUuid);
        }

        return $instanceUuid;
    }

    /**
     * Gets the generated handshake token that should be used during setup.
     *
     * @return string
     */
    public static function get_handshake_token()
    {

        // Do we already have a UUID?
        $token = get_option(CE4WP_INSTANCE_HANDSHAKE_TOKEN, null);
        $expiration = self::get_handshake_expiration();
        if ($token === null || $expiration === null || $expiration < time()) {

            // No token is known or it expired, generate a new one
            $token = GuidHelper::generate_guid();
            update_option(CE4WP_INSTANCE_HANDSHAKE_TOKEN, $token);
            update_option(CE4WP_INSTANCE_HANDSHAKE_EXPIRATION, time() + 3600);
        }

        return $token;
    }

    /**
     * Gets the expiration time associated with the generated handshake token.
     *
     * @return int|null
     */
    public static function get_handshake_expiration()
    {
        return get_option(CE4WP_INSTANCE_HANDSHAKE_EXPIRATION, null);
    }

    /**
     * Gets the consumer API key that can be used to interact with the Creative Mail platform.
     *
     * @return string|null
     */
    public static function get_wc_consumer_key()
    {
        return EncryptionHelper::get_option(CE4WP_WC_API_CONSUMER_KEY, null);
    }

    /**
     * Sets the consumer key that can be used to interact with the Creative Mail platform.
     *
     * @param $value string
     *
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function set_wc_consumer_key($value)
    {
        EncryptionHelper::add_option(CE4WP_WC_API_CONSUMER_KEY, $value);
    }

    /**
     * Deletes the consumer key.
     *
     * @return int|null
     */
    public static function delete_wc_consumer_key()
    {
        return delete_option(CE4WP_WC_API_CONSUMER_KEY);
    }

    /**
     * Gets the assigned api key id.
     *
     * @return int|null
     */
    public static function get_wc_api_key_id()
    {
        return get_option(CE4WP_WC_API_KEY_ID, null);
    }

    /**
     * Sets the assigned api key id that is generated when connecting this WP instance to the Creative Mail account.
     *
     * @param $value int
     */
    public static function set_wc_api_key_id($value)
    {
        add_option(CE4WP_WC_API_KEY_ID, $value);
    }

    /**
     * Deletes the api key id.
     *
     * @return int|null
     */
    public static function delete_wc_api_key_id()
    {
        return delete_option(CE4WP_WC_API_KEY_ID);
    }

    /**
     * Gets the assigned instance id.
     *
     * @return int|null
     */
    public static function get_instance_id()
    {
        return get_option(CE4WP_INSTANCE_ID_KEY, null);
    }

    /**
     * Sets the assigned instance id that is generated when connecting this WP instance to the Creative Mail account.
     *
     * @param $value int
     */
    public static function set_instance_id($value)
    {
        add_option(CE4WP_INSTANCE_ID_KEY, $value);
    }

    /**
     * Gets the assigned checkbox text.
     *
     * @return string
     */
    public static function get_checkout_checkbox_text()
    {
        return get_option(CE4WP_CHECKOUT_CHECKBOX_TEXT, "Yes, I'm ok with you sending me additional newsletter and email content");
    }

    /**
     * Sets the assigned checkout checkbox text.
     *
     * @param $value string
     */
    public static function set_checkout_checkbox_text($value)
    {
        update_option(CE4WP_CHECKOUT_CHECKBOX_TEXT, $value);
    }

    /**
     * Sets the assigned checkout checkbox enabled.
     *
     * @param $value bool
     */
    public static function set_checkout_checkbox_enabled($value)
    {
        update_option(CE4WP_CHECKOUT_CHECKBOX_ENABLED, $value);
    }

    /**
     * Gets the  assigned checkout checkbox enabled value
     *
     * @return int|bool
     */
    public static function get_checkout_checkbox_enabled()
    {
        return get_option(CE4WP_CHECKOUT_CHECKBOX_ENABLED, '1');
    }

    /**
     * Gets the id of the account that is connected to the combination of this WP unique id and Creative Mail account id.
     *
     * @return int|null
     */
    public static function get_connected_account_id()
    {
        return get_option(CE4WP_CONNECTED_ACCOUNT_ID, null);
    }

    /**
     * Sets the id of the account that is connected to the combination of this WP unique id and Creative Mail account id.
     *
     * @param $value int
     */
    public static function set_connected_account_id($value)
    {
        add_option(CE4WP_CONNECTED_ACCOUNT_ID, $value);
    }

    /**
     * Gets the API key that can be used to interact with the Creative Mail platform.
     *
     * @return string|null
     */
    public static function get_instance_api_key()
    {
        return EncryptionHelper::get_option(CE4WP_INSTANCE_API_KEY_KEY, null);
    }

    /**
     * Sets the API key that can be used to interact with the Creative Mail platform.
     *
     * @param $value string
     *
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function set_instance_api_key($value)
    {
        EncryptionHelper::add_option(CE4WP_INSTANCE_API_KEY_KEY, $value);
    }

    /**
     * Gets a string representing all the plugins that were activated for synchronization during the setup process.
     *
     * @return string|array
     */
    public static function get_activated_plugins()
    {
        return get_option(CE4WP_ACTIVATED_PLUGINS, array());
    }

    /**
     * Sets a string representing all the plugins that were activated for synchronization during the setup process.
     *
     * @param $plugins
     */
    public static function set_activated_plugins($plugins)
    {
        update_option(CE4WP_ACTIVATED_PLUGINS, $plugins);
    }

    /**
     * Get managed email notification array or string
     *
     * @return string|array
     */
    public static function get_managed_email_notifications()
    {
        global $wpdb;
        $rows   = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM $wpdb->options WHERE option_name like %s", CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '%'));
        $result = array();
        foreach ( $rows as $row ) {
            $name = $row->option_name;
            if ($name === CE4WP_MANAGED_EMAIL_NOTIFICATIONS ) {
                //convert old to new format
                return self::convert_managed_email_notifications($row->option_value);
            }

            $item = new stdClass();
            $item->name = str_replace(CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '_', '', $name);
            $item->active =$row->option_value == 'true';
            array_push($result, $item);
        }

        return $result;
    }

    /**
     * One time converts the email notifications to the new format
     *
     * @return array
     */
    private static function convert_managed_email_notifications($items)
    {
        $items = maybe_unserialize($items);
        if (empty($items) || $items == null ) {
            return array();
        }

        $result = array();
        foreach ( $items as $item ) {
            if (property_exists($item, 'name') ) {
                OptionsHelper::set_managed_email_notification($item->name, $item->active == true ? 'true' : 'false');
                array_push($result, $item);
            }
        }

        delete_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS);
        return $result;
    }

    /**
     * Deletes all the email notifications options
     */
    private static function delete_managed_email_notifications()
    {
        $managed_notifications = self::get_managed_email_notifications();
        foreach ( $managed_notifications as $item ) {
            if (property_exists($item, 'name') ) {
                delete_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '_' . $item->name);
            }
        }
    }

    /**
     * Set managed email notification by name
     *
     * @param $data
     * @param $active
     */
    public static function set_managed_email_notification($name, $active)
    {
        update_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '_' . $name, $active);
    }

    /**
     * Gets an int value representing when the user did accept the terms on our consent screen.
     *
     * @return int|null
     */
    public static function get_consent_accept_date()
    {
        return get_option(CE4WP_ACCEPTED_CONSENT, null);
    }

    /**
     * Sets the current time value indicated the user accepted the terms on the consent screen.
     */
    public static function set_did_accept_consent()
    {
        update_option(CE4WP_ACCEPTED_CONSENT, time());
    }

    /**
     * Gets an string value representing who referred this customer
     *
     * @return string|null
     */
    public static function get_referred_by()
    {
        return get_option(CE4WP_REFERRED_BY, null);
    }

    /**
     * Gets the hide banner option for the given banner.
     *
     * @param $banner string
     *
     * @return bool
     */
    public static function get_hide_banner($banner)
    {
        return get_option(CE4WP_HIDE_BANNER . ':' . $banner, false);
    }

    /**
     * Sets the hide banner option for the given banner.
     *
     * @param $banner string
     * @param $hide bool
     */
    public static function set_hide_banner($banner, $hide = true)
    {
        update_option(CE4WP_HIDE_BANNER . ':' . $banner, $hide);
    }

    /**
     * Will clear all the registered options for this plugin.
     * Only the Unique Id won't be cleared so that we can restore the link when the plugin is reactivated.
     *
     * @param $clear_all bool When set to 'true' the instance UUID will be re-generated, this will cause the link between the plugin and the user account to break.
     */
    public static function clear_options($clear_all)
    {
        delete_option(CE4WP_INSTANCE_ID_KEY);
        delete_option(CE4WP_INSTANCE_API_KEY_KEY);
        delete_option(CE4WP_CONNECTED_ACCOUNT_ID);
        delete_option(CE4WP_ACTIVATED_PLUGINS);
        delete_option(CE4WP_ACCEPTED_CONSENT);
        delete_option(CE4WP_WC_API_KEY_ID);
        delete_option(CE4WP_WC_API_CONSUMER_KEY);
        delete_option(CE4WP_INSTANCE_HANDSHAKE_TOKEN);
        delete_option(CE4WP_INSTANCE_HANDSHAKE_EXPIRATION);
        delete_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS);
        delete_option(CE4WP_CHECKOUT_CHECKBOX_TEXT);
        self::delete_managed_email_notifications();

        if($clear_all === true) {
            delete_option(CE4WP_INSTANCE_UUID_KEY);
            delete_option(CE4WP_ENCRYPTION_KEY_KEY);
        }
    }
}
