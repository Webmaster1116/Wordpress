<?php
/**
 * Created by PhpStorm.
 * User: Martijn
 * Date: 2020-02-10
 * Time: 13:42
 */

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WC_EVENTTYPE', 'WordPress - WooCommerce');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactAddressModel;
use CreativeMail\Modules\Contacts\Models\ContactModel;

class WooCommercePluginHandler extends BaseContactFormPluginHandler
{
    const CHECKOUT_CONSENT_CHECKBOX_ID = 'ce4wp_checkout_consent_checkbox';
    const CHECKOUT_CONSENT_CHECKBOX_VALUE = 'ce4wp_checkout_consent';
    const CHECKOUT_CONSENT_CHECKBOX_VALUE_OLD = 'ce_checkout_consent';

    public function convertToContactModel($orderId)
    {
        $contactModel = new ContactModel();
        $products_detail = get_post_meta($orderId);

        if (isset($products_detail)) {
            if (!empty($products_detail["_billing_email"]) && isset($products_detail["_billing_email"][0]) && !empty($products_detail["_billing_email"][0])) {
                $contactModel->setEmail($products_detail["_billing_email"][0]);
            } else {
                return $contactModel;
            }

            if (!empty($products_detail["_billing_first_name"])) {
                $contactModel->setFirstName($products_detail["_billing_first_name"][0]);
            }
            if (!empty($products_detail["_billing_last_name"])) {
                $contactModel->setLastName($products_detail["_billing_last_name"][0]);
            }

            $contactAddress = $this->getContactAddressFromOrder($products_detail);

            if (!empty($contactAddress)) {
                $contactModel->setContactAddress($contactAddress);
            }

            if (!empty($contactModel->getEmail())) {
                $contactModel->setEventType(CE4WP_WC_EVENTTYPE);
                $contactModel->setOptActionBy(2);
                $contactModel->setOptIn(false);
                $contactModel->setOptOut(false);
            }

            if (!empty($products_detail["_billing_phone"])) {
                $contactModel->setPhone($products_detail["_billing_phone"][0]);
            }

            $checkbox_value = null;

            if (!empty($_POST[self::CHECKOUT_CONSENT_CHECKBOX_ID])) {
                $checkbox_value = esc_attr($_POST[self::CHECKOUT_CONSENT_CHECKBOX_ID]);
            } else if (!empty($products_detail[self::CHECKOUT_CONSENT_CHECKBOX_ID])) {
                $checkbox_value = $products_detail[self::CHECKOUT_CONSENT_CHECKBOX_ID];
            } else if (!empty($_POST[self::CHECKOUT_CONSENT_CHECKBOX_VALUE])) {
                $checkbox_value = esc_attr($_POST[self::CHECKOUT_CONSENT_CHECKBOX_VALUE]);
            } else if (!empty($products_detail[self::CHECKOUT_CONSENT_CHECKBOX_VALUE])) {
                $checkbox_value = $products_detail[self::CHECKOUT_CONSENT_CHECKBOX_VALUE][0]; //this value appears to be in array;
            } else if (!empty($products_detail[self::CHECKOUT_CONSENT_CHECKBOX_VALUE_OLD])) {
                $checkbox_value = $products_detail[self::CHECKOUT_CONSENT_CHECKBOX_VALUE_OLD][0]; //this value appears to be in array;
            }

            if (!is_null($checkbox_value)) {
                $contactModel->setOptActionBy(1);
                $contactModel->setOptIn((bool)$checkbox_value);
                $contactModel->setOptOut(!(bool)$checkbox_value);
            }

        }
        return $contactModel;
    }

    function getContactAddressFromOrder($products_detail)
    {
        $contactAddress = new ContactAddressModel();

        if (isset($products_detail)) {
            if (!empty($products_detail["_billing_address_1"])) {
                $contactAddress->setAddress($products_detail["_billing_address_1"][0]);
            }
            if (!empty($products_detail["_billing_address_2"])) {
                $contactAddress->setAddress2($products_detail["_billing_address_2"][0]);
            }
            if (!empty($products_detail["_billing_city"])) {
                $contactAddress->setCity($products_detail["_billing_city"][0]);
            }
            if (!empty($products_detail["_billing_country"])) {
                $contactAddress->setCountryCode($products_detail["_billing_country"][0]);
            }
            if (!empty($products_detail["_billing_postcode"])) {
                $contactAddress->setPostalCode($products_detail["_billing_postcode"][0]);
            }
            if (!empty($products_detail["_billing_state"])) {
                $contactAddress->setStateCode($products_detail["_billing_state"][0]);
            }
        }
        return $contactAddress;
    }

    public function ceHandlerWooCommerceNewOrder($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $this->upsertContact($this->convertToContactModel($order->ID));
        } catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        add_action('woocommerce_checkout_order_created', array($this, 'ceHandlerWooCommerceNewOrder'), 10, 1);
    }

    public function unregisterHooks()
    {
        remove_action('woocommerce_checkout_order_created', array($this, 'ceHandlerWooCommerceOrder'));
    }

    public function get_contacts($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        $backfillArray = array();

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses())
        );

        if ($limit != null) {
            $args['posts_per_page'] = $limit;
        }

        $products_orders = get_posts($args);

        foreach ($products_orders as $products_order) {

            $contactModel = null;
            try {
                $contactModel = $this->convertToContactModel($products_order->ID);
            } catch (\Exception $exception) {
                // silent exception
                continue;
            }

            if (!empty($contactModel->getEmail())) {
                array_push($backfillArray, $contactModel);
            }
        }

        if (!empty($backfillArray)) {
            return $backfillArray;
        }

        return null;
    }

    function __construct()
    {
        parent::__construct();
    }
}
