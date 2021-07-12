<?php


namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use stdClass;
use WC_Coupon;
use WC_Order;

/**
 * Class CheckoutManager
 *
 * @package CreativeMail\Managers
 */
class CheckoutManager
{
    /**
     * Current checkout UUID.
     *
     * @var   string
     * @since 1.3.0
     */
    protected $checkout_uuid = '';

    const UPDATE_CHECKOUT_DATA = 'update_checkout_data';
    const META_CHECKOUT_UUID = 'ce4wp_checkout_uuid';
    const META_CHECKOUT_RECOVERED = 'ce4wp_checkout_recovered';
    const CHECKOUT_UUID = 'checkout_uuid';
    const NONCE = 'nonce';
    const EMAIL = 'email';
    const CHECKED = 'checked';
    const DOMAIN = 'ce4wp';
    const BILLING_EMAIL = 'billing_email';
    const BILLING_EMAIL_NOTICE = 'billing_email_notice';
    const BILLING_EMAIL_NO_CONSENT = 'billing_email_no_consent';
    const CHECKOUT_UUID_PARAM = 'checkout_uuid = %s';
    const COUPONS = 'coupons';
    const PRODUCT_ID = 'product_id';
    const VARIATION_ID = 'variation_id';
    const QUANTITY = 'quantity';
    const VARIATION = 'variation';
    const USER_EMAIL = 'user_email';
    const PRODUCTS = 'products';
    const CUSTOMER = 'customer';
    const DATETIME_ZERO = "0000-00-00 00:00:00";

    /**
     * Add hooks
     *
     * @since 1.3.0
     */
    public function add_hooks()
    {
        // check if woocommerce is active
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('woocommerce_before_checkout_form', array($this, 'enqueue_scripts'));
            // add checkout notice field
            add_filter('woocommerce_form_field_ce4wp_notice', array($this, 'add_email_usage_notice_field'), 10, 4);
            add_filter('woocommerce_checkout_fields', array($this, 'ce4wp_filter_checkout_fields'));

            add_action('woocommerce_after_template_part', array($this, 'save_or_clear_checkout_data'), 10, 1);
            add_action('woocommerce_add_to_cart', array($this, self::UPDATE_CHECKOUT_DATA));
            add_action('woocommerce_cart_item_removed', array($this, self::UPDATE_CHECKOUT_DATA), 30, 0);
            add_action('woocommerce_cart_item_restored', array($this, self::UPDATE_CHECKOUT_DATA), 30, 0);
            add_action('woocommerce_cart_item_set_quantity', array($this, self::UPDATE_CHECKOUT_DATA), 20, 0);

            add_action('wp_ajax_ce4wp_abandoned_checkouts_capture_guest_checkout', array($this, 'maybe_capture_guest_checkout'));
            add_action('wp_ajax_nopriv_ce4wp_abandoned_checkouts_capture_guest_checkout', array($this, 'maybe_capture_guest_checkout'));

            add_action('wp_ajax_ce4wp_abandoned_checkouts_no_consent_checkout', array($this, 'no_consent_checkout'));
            add_action('wp_ajax_nopriv_ce4wp_abandoned_checkouts_no_consent_checkout', array($this, 'no_consent_checkout'));

            add_action('woocommerce_checkout_create_order', array($this, 'clear_purchased_data'), 10, 1);
            add_action('woocommerce_checkout_order_processed', array($this, 'order_processed'), 10, 1);
            add_action('woocommerce_order_status_completed', array($this, 'order_completed'), 10, 1);

            // Sanitize checkout UUID.
            $this->checkout_uuid = filter_input(INPUT_GET, 'ce4wp-recover', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

            if (empty($this->checkout_uuid)) {
                return;
            }

            add_action('wp_loaded', array($this, 'recover_checkout'));
        }
    }

    /**
     * Add custom field under billing_email
     *
     * @since 1.3.0
     */
    public function ce4wp_filter_checkout_fields($fields) {
        $fields['billing'][self::BILLING_EMAIL_NOTICE] = array(
            'type' => 'ce4wp_notice',
            'required'  => false,
            'class'      => array( 'form-row-wide' ),
            'clear'     => true,
            'priority' => $fields['billing']['billing_email']['priority'] + 0.5
        );
        return $fields;
    }

    /**
     * Add logic for ce4wp_notice field type
     *
     * @since 1.3.0
     */
    public function add_email_usage_notice_field( $field, $key, $args, $value )
    {
        $field_html = '<label style="font-weight:400;">' . __( 'Your email and cart are saved so we can send you email reminders about this order.', self::DOMAIN ) .' <a href="#" id="ce4wp_no_consent">'. __( 'No thanks', self::DOMAIN ).'</a></label>';

        $container_class = esc_attr( implode( ' ', $args['class'] ) );
        $container_id = esc_attr( $args['id'] ) . '_field';

        $after = ! empty( $args['clear'] ) ? '<div class="clear"></div>' : '';

        $field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';

        return sprintf( $field_container, $container_class, $container_id, $field_html ) . $after;
    }

    /**
     * Order has been completed
     *
     * @param int $order_id    The order id.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function order_completed($order_id)
    {
        $this->update_checkout($order_id, '/v1.0/checkout/order_completed');
        $this->cleanup_old_checkouts($order_id);
    }

    /**
     * Order has been created and is processed
     *
     * @param int $order_id    Newly created order id.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function order_processed( $order_id) {
        $this->update_checkout($order_id, '/v1.0/checkout/order_created');
    }

    /**
     * Cleanup previous checkouts in case old is still marked as abandoned
     *
     * @param int $order_id    Woocommerce order id.
     *
     * @since 1.3.3
     *
     * @return void
     */
    private function cleanup_old_checkouts( $order_id )
    {
        $order = wc_get_order($order_id);
        if ( empty( $order ) ) {
            return;
        }
        try
        {
            $data = $this->get_checkout_uuid_by_email($order->get_billing_email());
            foreach ($data as $checkout_data)
            {
                $endpoint = EnvironmentHelper::get_app_gateway_url('wordpress') . '/v1.0/checkout/'. $checkout_data->checkout_uuid;
                $this->ce4wp_remote_delete($endpoint);
                CreativeMail::get_instance()->get_database_manager()->remove_checkout_data($checkout_data->checkout_uuid);
            }
        }
        catch (\Exception $e)
        {
            RaygunManager::get_instance()->exception_handler($e);
        }
    }

    /**
     * Update of checkout data in the external service
     *
     * @param int $order_id    Newly created order id.
     * @param string $endpoint Endpoint to call
     *
     * @since 1.3.0
     */
    private function update_checkout($order_id, $endpoint) {
        $order = wc_get_order($order_id);
        if ( empty( $order ) ) {
            return;
        }
        // check if order had checkout uuid
        $uuid = $order->get_meta( self::META_CHECKOUT_UUID, true);
        // check if order is created with checkout meta
        if (empty($uuid)) {
            return;
        }
        // try find recovery date from order meta data
        $recovery_date = $order->get_meta( self::META_CHECKOUT_RECOVERED, true);
        // Remote post to ce4wp marking checkout as completed/created
        $requestItem = new stdClass();
        $requestItem->uuid = $uuid;
        $requestItem->order_id = $order->get_id();
        $requestItem->order_total = $order->get_total();
        $requestItem->order_currency = $order->get_currency();
        $requestItem->recovery_date = (empty($recovery_date) || $recovery_date === self::DATETIME_ZERO) ? null : $recovery_date;
        $endpoint = EnvironmentHelper::get_app_gateway_url('wordpress') . $endpoint;
        // call remote endpoint to update
        $this->ce4wp_remote_post($requestItem, $endpoint);
    }

    /**
     * Enqueue abandoned cart javascript files
     *
     * @since 1.3.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 'ce4wp-consent-checkout', CE4WP_PLUGIN_URL . 'assets/js/consent_checkout.js', [ 'wp-util' ], CE4WP_PLUGIN_VERSION, false );

        if ( is_user_logged_in() ) {
            return;
        }

        wp_enqueue_script( 'ce4wp-guest-checkout', CE4WP_PLUGIN_URL . 'assets/js/guest_checkout.js', [ 'wp-util' ], CE4WP_PLUGIN_VERSION, false );
    }

    /**
     * AJAX handler for attempting to capture guest checkouts.
     *
     * @since 1.3.0
     */
    public function maybe_capture_guest_checkout() {
        $data = filter_input_array( INPUT_POST, [
            self::NONCE => FILTER_SANITIZE_STRING,
            self::EMAIL => FILTER_SANITIZE_EMAIL
        ] );

        if ( empty( $data[self::NONCE] ) || ! wp_verify_nonce( $data[self::NONCE], 'woocommerce-process_checkout' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce.', self::DOMAIN ) );
        }

        $email = filter_var( $data[self::EMAIL], FILTER_VALIDATE_EMAIL );

        if ( ! $email ) {
            wp_send_json_error( esc_html__( 'Invalid email.', self::DOMAIN ) );
        }

        WC()->session->set( self::BILLING_EMAIL, $email );
        $this->save_checkout_data( $email, true);

        wp_send_json_success();
    }

    /**
     * AJAX handler for opt out on abandoned cart.
     *
     * @since 1.3.0
     */
    public function no_consent_checkout()
    {
        $data = filter_input_array(INPUT_POST, [
            self::NONCE => FILTER_SANITIZE_STRING
        ]);

        if (empty($data[self::NONCE]) || !wp_verify_nonce($data[self::NONCE], 'woocommerce-process_checkout')) {
            wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
        }
        // save no consent on session
        WC()->session->set( self::BILLING_EMAIL_NO_CONSENT, true);

        $checkout_id = WC()->session->get( self::CHECKOUT_UUID );
        if (empty($checkout_id)) {
            wp_send_json_success();
        }

        $endpoint = EnvironmentHelper::get_app_gateway_url('wordpress') . '/v1.0/checkout/'. $checkout_id;
        $this->ce4wp_remote_delete($endpoint);
        CreativeMail::get_instance()->get_database_manager()->change_checkout_consent($checkout_id, false);

        wp_send_json_success();
    }

    /**
     * Either call an update of checkout data which will be saved or remove checkout data based on what template we arrive at.
     *
     * @param string $template_name Current template file name.
     *
     * @since 1.3.0
     */
    public function save_or_clear_checkout_data( $template_name) {

        // If checkout page displayed, save checkout data.
        if ( 'checkout/form-checkout.php' === $template_name ) {
            $this->save_checkout_data();
        }
    }

    /**
     * Helper function to update current checkout session data in db.
     *
     * Used to strip unneeded params from callbacks.
     *
     * @since 1.3.0
     */
    public function update_checkout_data() {
        $this->save_checkout_data();
    }

    /**
     * Helper function to retrieve checkout contents based on checkout UUID.
     *
     * @param string $uuid Checkout UUID.
     *
     * @since 1.3.0
     *
     * @return array                 Checkout contents.
     */
    private function get_checkout_contents( $uuid ) {
        $checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( 'checkout_contents', self::CHECKOUT_UUID_PARAM, [ $uuid ] );

        if ( empty( $checkout ) ) {
            return [];
        }

        return maybe_unserialize( array_shift( $checkout )->checkout_contents );
    }

    /**
     * Helper function to retrieve checkout recovery date based on checkout UUID.
     *
     * @param string $uuid Checkout UUID.
     *
     * @since 1.3.0
     *
     * @return string|null Checkout recovery date if exists, else null.
     */
    private function get_checkout_recovery_date( $uuid ) {
        $checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( 'checkout_recovered', self::CHECKOUT_UUID_PARAM, [ $uuid ] );

        return ( empty( $checkout ) ? null : array_shift( $checkout )->checkout_recovered );
    }

    /**
     * Helper function to retrieve checkout UUID for current user.
     *
     * @since 1.3.0
     *
     * @return string Checkout UUID if exists, else empty string.
     */
    private function get_checkout_uuid_by_user() {
        $checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( self::CHECKOUT_UUID, 'user_id = %d', [ get_current_user_id() ] );

        return ( empty( $checkout ) ? '' : array_shift( $checkout )->checkout_uuid );
    }

    /**
     * Helper function to retrieve checkout UUID for email address.
     *
     * @since 1.3.3
     *
     * @return array List of checkout UUIDs if exists, else empty string.
     */
    private function get_checkout_uuid_by_email($email_address) {
        return CreativeMail::get_instance()->get_database_manager()->get_checkout_data( self::CHECKOUT_UUID, 'user_email = %s', [ $email_address ] );
    }

    /**
     * Save current checkout data to db.
     *
     * @param string  $billing_email Manually set customer billing email if provided.
     * @param boolean $is_checkout   Manually mark current page as checkout if necessary (e.g., coming from ajax callback).
     * @param boolean|null $consent_checkout   Manually mark consent value.
     *
     * @since 1.3.0
     *
     * @return void
     */
    protected function save_checkout_data( string $billing_email = '', bool $is_checkout = false) {
        // Get current user email.
        $session_customer      = WC()->session->get( self::CUSTOMER );
        $session_billing_email = is_array( $session_customer ) && key_exists( self::EMAIL, $session_customer ) ? $session_customer[self::EMAIL] : '';

        if (empty($billing_email)) {
            $billing_email = $session_billing_email;
            if (empty($billing_email)) {
                $billing_email = WC()->checkout->get_value( self::BILLING_EMAIL );
                if (empty($billing_email)) {
                    $billing_email = WC()->session->get( self::BILLING_EMAIL );
                }
            }
        }
        $is_checkout           = $is_checkout ?: is_checkout();
        $uuid         = WC()->session->get( self::CHECKOUT_UUID );

        if ( empty( $billing_email ) ) {
            return;
        }

        $has_no_consent = WC()->session->get( self::BILLING_EMAIL_NO_CONSENT);
        if($has_no_consent === true)
        {
            return;
        }

        // Check for existing checkout session.
        if ( ! $uuid ) {

            // Only create session if cart is not empty.
            // This is to avoid re-creating checkout UUID during checkout process.
            if ( $is_checkout && empty( WC()->cart->get_cart() ) ) {
                return;
            }

            // Retrieve existing checkout UUID for registered users only.
            if ( is_user_logged_in() ) {
                $existing_uuid = $this->get_checkout_uuid_by_user();
            }

            // Only create session if currently on checkout page or if current user has an existing session saved.
            if ( ! $is_checkout && empty( $existing_uuid ) ) {
                return;
            }

            $uuid = isset( $existing_uuid ) && ! empty( $existing_uuid ) ? $existing_uuid : wp_generate_uuid4();

            WC()->session->set( 'checkout_uuid', $uuid );
        }

        $current_time = current_time( 'mysql', 1 );
        $user_id = get_current_user_id();

        $cart_products = WC()->cart->get_cart();
        $cart_coupons = WC()->cart->get_applied_coupons();

        $checkout_content = [
            self::PRODUCTS        => array_values($cart_products),
            self::COUPONS         => $cart_coupons,
        ];

        CreativeMail::get_instance()->get_database_manager()->upsert_checkout($uuid, $user_id, $billing_email, $checkout_content, $current_time);

        // Remote post to ce4wp create or update cart if email is provided
        $requestItem = new stdClass();
        $requestItem->data = wp_json_encode($this->get_cart_data_for_endpoint($cart_products, $cart_coupons));
        $requestItem->uuid = $uuid;
        $requestItem->user_id = $user_id;
        $requestItem->billing_email = $billing_email;
        $requestItem->timestamp = strtotime($current_time);
        $endpoint = EnvironmentHelper::get_app_gateway_url('wordpress') . '/v1.0/checkout/upsert';

        $consent = CreativeMail::get_instance()->get_database_manager()->has_checkout_consent($uuid);
        if ($consent) {
            $this->ce4wp_remote_post($requestItem, $endpoint);
        }
    }

    /**
     * Get cart object with data for each product and coupon
     *
     * @since 1.3.0
     */
    private function get_cart_data_for_endpoint($cart_products, $cart_coupons) {
        $data = new stdClass();
        $data->products = array();
        $data->coupons = array();
        $data->user = new stdClass();

        try
        {
            // Get user first and last name of available
            $current_user = wp_get_current_user();
            if ($current_user->exists() ) {
                $data->user->id = $current_user->ID;
                $data->user->username = $current_user->user_login;
                $data->user->display_name = $current_user->display_name;
                $data->user->first_name = $current_user->user_firstname;
                $data->user->last_name = $current_user->user_lastname;
                $data->user->email = $current_user->user_email;
            }

            $dp = 2; // decimal point

            foreach ($cart_products as $value)
            {
                $product = array_key_exists('data', $value) ? $value['data'] : wc_get_product($value[self::PRODUCT_ID]);
                $product_id = $product->get_id();
                $product_data = array(
                    'images' => array()
                );
                $attachment_ids = $product->get_gallery_image_ids();
                foreach ($attachment_ids as $attachment_id) {
                    $product_data['images'][] = wp_get_attachment_url($attachment_id);
                }

                $product_data["on_sale"] = $product->is_on_sale();
                $product_data["sale_price"] = $product->get_sale_price();
                $product_data["regular_price"] = $product->get_regular_price();
                $src = wc_placeholder_img_src();
                if ($image_id = $product->get_image_id()) {
                    list($src) = wp_get_attachment_image_src($image_id, 'full');
                }

                $line_subtotal = empty($value['line_subtotal']) ? 0: $value['line_subtotal'];
                $line_subtotal_tax =empty($value['line_subtotal_tax']) ? 0: $value['line_subtotal_tax'];
                $line_total = empty($value['line_total']) ? 0: $value['line_total'];
                $line_tax = empty($value['line_tax']) ? 0: $value['line_tax'];

                $data->products[] = array(
                    'name' => $product->get_name(),
                    'product_id' => $product_id,
                    'product_image' => $src,
                    'product_data' => $product_data,
                    'sku' => is_object($product) ? $product->get_sku() : null,
                    'product_url' => get_the_permalink($product_id),
                    'variation_id' => $value[self::VARIATION_ID],
                    'subtotal' => wc_format_decimal($line_subtotal, $dp),
                    'subtotal_tax' => wc_format_decimal($line_subtotal_tax, $dp),
                    'total' => wc_format_decimal($line_total, $dp),
                    'total_tax' => wc_format_decimal($line_tax, $dp),
                    'price' => wc_format_decimal($line_subtotal, $dp),
                    'quantity' => $value[self::QUANTITY]
                );
            }

            foreach ($cart_coupons as $coupon_code)
            {
                $coupon_id = wc_get_coupon_id_by_code($coupon_code);
                if ($coupon_id)
                {
                    $coupon = new WC_Coupon($coupon_id);

                    $data->coupons[] = array(
                        'code' => $coupon->get_code(),
                        'amount' => $coupon->get_amount(),
                        'discount_type' => $coupon->get_discount_type(),
                        'description' => $coupon->get_description(),
                        'free_shipping' => $coupon->get_free_shipping()
                    );
                }
            }
        }
        catch (\Exception $e)
        {
            RaygunManager::get_instance()->exception_handler($e);
        }

        return $data;
    }

    /**
     * Remove current checkout session data from db upon successful order submission.
     *
     * @param WC_Order $order    Newly created order object.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function clear_purchased_data( $order) {
        $checkout_id = WC()->session->get( self::CHECKOUT_UUID );
        if (empty($checkout_id)) {
            return;
        }

        $order->update_meta_data( self::META_CHECKOUT_UUID, $checkout_id );

        // get the recovery date if recovered
        $recovery_date = $this->get_checkout_recovery_date($checkout_id);
        if (!empty($recovery_date) && $recovery_date !== self::DATETIME_ZERO)
        {
            $order->update_meta_data(self::META_CHECKOUT_RECOVERED, $recovery_date);
        }
        CreativeMail::get_instance()->get_database_manager()->remove_checkout_data($checkout_id);
        WC()->session->__unset( self::CHECKOUT_UUID );
    }

    /**
     * Recovery saved checkout from UUID.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function recover_checkout() {

        // Set checkout session UUID.
        WC()->session->set( self::CHECKOUT_UUID, $this->checkout_uuid );

        // Clear current checkout contents.
        WC()->cart->empty_cart();

        // Get saved checkout contents.
        $checkout_contents = $this->get_checkout_contents( $this->checkout_uuid );

        if ( empty($checkout_contents) ) {
            return;
        }

        // Mark checkout as recovered
        CreativeMail::get_instance()->get_database_manager()->mark_checkout_recovered($this->checkout_uuid);

        // Recover saved products.
        $this->recover_products( $checkout_contents[self::PRODUCTS] );

        // Apply coupons.
        foreach ( $checkout_contents[self::COUPONS] as $coupon ) {
            WC()->cart->apply_coupon( $coupon );
        }

        // Maybe recover checkout email.
        $this->maybe_recover_checkout_email();

        // Update totals.
        WC()->cart->calculate_totals();

        // Redirect to checkout page.
        wp_safe_redirect( wc_get_page_permalink( 'cart' ) );

        exit();
    }


    /**
     * Recover checkout email address if guest user and no email is set.
     *
     * @since 1.3.0
     *
     * @return void
     */
    protected function maybe_recover_checkout_email() : void {
        $checkout_email = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( self::USER_EMAIL, self::CHECKOUT_UUID_PARAM, [ $this->checkout_uuid ] );
        $checkout_email = empty( $checkout_email ) ? '' : array_shift( $checkout_email )->user_email;

        if ( is_user_logged_in() || ! empty( WC()->session->get( self::BILLING_EMAIL ) ) || empty( $checkout_email ) ) {
            return;
        }

        WC()->session->set( self::BILLING_EMAIL, $checkout_email );
        WC()->customer->set_billing_email( $checkout_email );
    }

    /**
     * Recover products from saved checkout data.
     *
     * @param array $products Array of product data.
     *
     * @since 1.3.0
     */
    protected function recover_products( $products ) {
        if (empty($products)) {
            return;
        }
        // Programmatically add each product to cart.
        $products_added = [];
        foreach ( $products as $product ) {
            $added = WC()->cart->add_to_cart(
                $product[self::PRODUCT_ID],
                $product[self::QUANTITY],
                empty( $product[self::VARIATION_ID] ) ? 0 : $product[self::VARIATION_ID],
                empty( $product[self::VARIATION] ) ? array() : $product[self::VARIATION]
            );
            if ( false !== $added ) {
                $products_added[ ( empty( $product[self::VARIATION_ID] ) ? $product[self::PRODUCT_ID] : $product[self::VARIATION_ID] ) ] = $product[self::QUANTITY];
            }
        }

        // Add product notices.
        if ( 0 < count( $products_added ) ) {
            wc_add_to_cart_message( $products_added );
        }
        if ( count( $products ) > count( $products_added ) ) {
            wc_add_notice(
                sprintf(
                /* translators: %d item count */
                    _n(
                        '%d item from your previous order is currently unavailable and could not be added to your cart.',
                        '%d items from your previous order are currently unavailable and could not be added to your cart.',
                        ( count( $products ) - count( $products_added ) ),
                        self::DOMAIN
                    ),
                    ( count( $products ) - count( $products_added ) )
                ),
                'error'
            );
        }
    }

    private function ce4wp_remote_post($requestItem, $endpoint) {
        try
        {
            // check if abandoned cart email is managed by creative mail
            $enabled = CreativeMail::get_instance()->get_email_manager()->is_email_managed('cart_abandoned_ce4wp');
            if($enabled) {
                wp_remote_post(
                    $endpoint, array(
                        'method' => 'POST',
                        'timeout' => 10,
                        'headers' => array(
                            'x-account-id' => OptionsHelper::get_connected_account_id(),
                            'x-api-key' => OptionsHelper::get_instance_api_key(),
                            'content-type' => 'application/json'
                        ),
                        'body' => wp_json_encode($requestItem)
                    )
                );
            }
        } catch (\Exception $e) {
            RaygunManager::get_instance()->exception_handler($e);
        }
    }

    private function ce4wp_remote_delete($endpoint) {
        try {
            wp_remote_request($endpoint,
                array(
                    'method' => 'DELETE',
                    'headers' => array(
                        'x-account-id' => OptionsHelper::get_connected_account_id(),
                        'x-api-key' => OptionsHelper::get_instance_api_key()
                    )
                )
            );
        } catch (\Exception $e) {
            // silent
        }
    }
}
