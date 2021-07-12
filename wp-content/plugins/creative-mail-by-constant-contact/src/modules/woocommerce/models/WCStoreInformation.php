<?php


namespace CreativeMail\Modules\WooCommerce\Models;

use CreativeMail\Helpers\OptionsHelper;
use WC_Countries;

class WCStoreInformation
{
    public $address1;
    public $address2;
    public $city;
    public $postcode;
    public $state;
    public $country;
    public $country_code;
    public $currency;
    public $currency_symbol;
    public $email_from;
    public $email_name;

    function __construct()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $this->address1 = WC()->countries->get_base_address();
            $this->address2 = WC()->countries->get_base_address_2();
            $this->city     = WC()->countries->get_base_city();
            $this->postcode = WC()->countries->get_base_postcode();
            $this->state    = WC()->countries->get_base_state();
            $this->country  = WC()->countries->countries[WC()->countries->get_base_country()];
            $this->country_code  = WC()->countries->get_base_country();
            $this->currency_symbol = get_woocommerce_currency_symbol();
            $this->currency = get_woocommerce_currency();
            $this->email_from = apply_filters('woocommerce_email_from_address', get_option('woocommerce_email_from_address'));
            $this->email_name = apply_filters('woocommerce_email_from_name', get_option('woocommerce_email_from_name'));
        }
    }
}
