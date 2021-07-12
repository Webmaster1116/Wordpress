<?php


namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Modules\Api\Processes\ApiBackgroundProcess;
use CreativeMail\Modules\Blog\Models\BlogAttachment;
use CreativeMail\Modules\Blog\Models\BlogInformation;
use CreativeMail\Modules\Blog\Models\BlogPost;
use CreativeMail\Modules\WooCommerce\Models\WCInformationModel;
use CreativeMail\Modules\WooCommerce\Models\WCProductModel;
use WP_Error;
use WP_REST_Response;

/**
 * Class ApiManager
 *
 * @package CreativeMail\Managers
 */
class ApiManager
{
    const API_NAMESPACE = "creativemail/v1";
    const ROUTE_METHODS = 'methods';
    const ROUTE_PATH = 'path';
    const ROUTE_CALLBACK = 'callback';
    const ROUTE_PERMISSION_CALLBACK = 'permission_callback';
    const ROUTE_REQUIRES_WP_ADMIN = [
        '/sso'
    ];
    const HTTP_STATUS = 'status';
    private $api_background_process;

    function __construct()
    {
        $this->api_background_process = new ApiBackgroundProcess();
    }

    public function get_api_background_process()
    {
        return $this->api_background_process;
    }

    /**
     * Will add all the hooks that are required to setup our plugin API.
     */
    public function add_hooks()
    {

        add_action('rest_api_init', array($this, 'add_rest_endpoints'));
    }

    public function validate_wp_admin()
    {
        if(!current_user_can('administrator')) {
            return new WP_Error('rest_forbidden', __('Sorry, you are not allowed to do that.','ce4wp'), array( self::HTTP_STATUS => 401 ));
        }

        return true;
    }

    public function validate_api_key()
    {
        //never cache our rest endpoints
        nocache_headers();

        if (! array_key_exists("HTTP_X_API_KEY", $_SERVER) ) {
            return new WP_Error('rest_forbidden', __('Sorry, you are not allowed to do that.','ce4wp'), array( self::HTTP_STATUS => 401 ));
        }

        $key    = OptionsHelper::get_instance_api_key();
        $apiKey = $_SERVER["HTTP_X_API_KEY"];
        // verify that api key is valid
        if ($apiKey === $key ) {
            return true;
        }

        return new WP_Error('rest_forbidden', __('Sorry, you are not allowed to do that.','ce4wp'), array( self::HTTP_STATUS => 401 ));
    }

    public function validate_callback()
    {
        //never cache our rest endpoints
        nocache_headers();

        if (! array_key_exists("HTTP_X_API_KEY", $_SERVER) ) {
            return new WP_Error('rest_forbidden', __('Sorry, you are not allowed to do that.','ce4wp'), array( self::HTTP_STATUS => 401 ));
        }

        $apiKey = $_SERVER["HTTP_X_API_KEY"];
        // Verify handshake expiration
        $expiration = OptionsHelper::get_handshake_expiration();
        if ($expiration === null || $expiration < time() ) {
            return new WP_Error('rest_unauthorized', 'Unauthorized', array( self::HTTP_STATUS => 401 ));
        }

        // Verify handshake
        if ($apiKey === OptionsHelper::get_handshake_token() ) {
            return true;
        }

        return new WP_Error('rest_unauthorized', 'Unauthorized', array( self::HTTP_STATUS => 401 ));
    }

    public function add_rest_endpoints()
    {
        // Add the endpoint to handle the callback
        $routes = array (
            array (
                self::ROUTE_PATH              => '/callback',
                self::ROUTE_METHODS           => 'POST',
                self::ROUTE_CALLBACK          => array(CreativeMail::get_instance()->get_instance_manager(), 'handle_callback'),
                self::ROUTE_PERMISSION_CALLBACK => function () {
                    return $this->validate_callback();
                }
            ),
            array (
                self::ROUTE_PATH              => '/available_plugins',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    return $this->modify_response($this->get_plugin_info(true));
                }
            ),
            array (
                self::ROUTE_PATH              => '/available_plugins',
                self::ROUTE_METHODS           => 'POST',
                self::ROUTE_CALLBACK          => function ($request) {
                    CreativeMail::get_instance()->get_integration_manager()->set_activated_plugins(json_decode($request->get_body()));
                }
            ),
            array (
                self::ROUTE_PATH              => '/plugins',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    return $this->modify_response($this->get_plugin_info(false));
                }
            ),
            array (
                self::ROUTE_PATH              => '/managed_email_notifications',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    $result = CreativeMail::get_instance()->get_email_manager()->get_managed_email_notifications();
                    return $this->modify_response(new WP_REST_Response($result, 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/managed_email_notifications',
                self::ROUTE_METHODS           => 'POST',
                self::ROUTE_CALLBACK          => function ($request) {
                    if(!CreativeMail::get_instance()->get_integration_manager()->get_permalinks_enabled()) {
                        return $this->modify_response(new WP_REST_Response(array( 'message' => 'Please enable pretty permalinks in the WordPress settings.'), 400));
                    }

                    $result = CreativeMail::get_instance()->get_email_manager()->set_managed_email_notifications(json_decode($request->get_body()));
                    return $this->modify_response(new WP_REST_Response($result, 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/abandoned_checkout',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function ($request) {
                    return $this->modify_response($this->get_ce_checkout($request->get_param('uuid')));
                }
            ),
            array (
                self::ROUTE_PATH              => '/wc_key',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    return $this->modify_response($this->get_wc_keys());
                }
            ),
            array (
                self::ROUTE_PATH              => '/synchronize',
                self::ROUTE_METHODS           => 'POST',
                self::ROUTE_CALLBACK          => function () {
                    do_action(CE4WP_SYNCHRONIZE_ACTION);
                    return $this->modify_response(new WP_REST_Response(null, 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/wc_information',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    return $this->modify_response(new WP_REST_Response(new WCInformationModel(), 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/wc_products',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function ($request) {
                    $productData = array();
                    $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                    if (in_array('woocommerce/woocommerce.php', $active_plugins)) {
                        $page = 1;
                        if (property_exists($request,'page')) {
                            $page = (int)$request['page'];
                        }

                        $types = array_merge( array_keys( wc_get_product_types() ) );

                        if (in_array('woocommerce-bookings/woocommerce-bookings.php', $active_plugins)) {
                            array_push($types, 'booking');
                        }

                        // Get 25 most recent products
                        $products = wc_get_products(
                            array(
                            'limit' => 25,
                            'paged' => $page,
                            'type' => $types
                            )
                        );
                        foreach ($products as $product) {
                            array_push($productData, new WCProductModel($product->get_data()));
                        }
                    }
                    return $this->modify_response(new WP_REST_Response($productData, 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/blog_information',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    return $this->modify_response(new WP_REST_Response(new BlogInformation(), 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/wp_posts',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function ($request) {

                    $page = 1;
                    if (property_exists($request,'page')) {
                        $page = (int)$request['page'];
                    }

                    $posts = get_posts(
                        array(
                        'posts_per_page' => 10,
                        'paged' => $page,
                        'post_type' => 'post'
                        )
                    );

                    $postData = array();
                    foreach ($posts as $post)
                    {
                        array_push($postData, new BlogPost($post));
                    }

                    return $this->modify_response(new WP_REST_Response($postData, 200));
                }
            ),
            array (
                self::ROUTE_PATH              => '/images',
                self::ROUTE_METHODS           => 'GET',
                self::ROUTE_CALLBACK          => function () {
                    $attachmentData = array();
                    $attachments = get_posts(
                        array(
                        'post_type' => 'attachment',
                        'post_mime_type' => 'image',
                        'post_status' => 'inherit',
                        'posts_per_page' => -1
                        )
                    );

                    foreach ($attachments as $attachment)
                    {
                        array_push($attachmentData, new BlogAttachment($attachment));
                    }

                    return $this->modify_response(new WP_REST_Response($attachmentData, 200));
                }
            ),
            array (
                self::ROUTE_PATH                => '/hide_banner',
                self::ROUTE_METHODS             => 'POST',
                self::ROUTE_CALLBACK            => function ($request) {
                    $banner = $request->get_param('banner');
                    if (empty($banner)) {
                        return $this->modify_response(new WP_REST_Response('Missing banner param', 400));
                    }

                    OptionsHelper::set_hide_banner($banner, true);
                    return $this->modify_response(new WP_REST_Response(null, 204));
                },
                self::ROUTE_PERMISSION_CALLBACK => function () {
                    return true;
                }
            ),
        );

        foreach ($routes as $route) {
            $this->register_route($route);
        }
    }

    private function get_plugin_info($onlyActivePlugins)
    {
        $result        = array();
        $activePlugins = CreativeMail::get_instance()->get_integration_manager()->get_active_plugins();
        if ($onlyActivePlugins === true ) {
            foreach ( $activePlugins as $activePlugin ) {
                array_push(
                    $result, array(
                    'name' => $activePlugin->get_name(),
                    'slug' => $activePlugin->get_slug()
                    )
                );
            }
        } else {
            $allPlugins       = CreativeMail::get_instance()->get_integration_manager()->get_supported_integrations();
            $activatedPlugins = CreativeMail::get_instance()->get_integration_manager()->get_activated_plugins();
            foreach ( $allPlugins as $plugin ) {
                if ($plugin->is_hidden_from_suggestions()) {
                    continue;
                }
                array_push(
                    $result, array(
                    'name'      => $plugin->get_name(),
                    'slug'      => $plugin->get_slug(),
                    'installed' => in_array($plugin, $activePlugins, true) !== false,
                    'activated' => array_search($plugin->get_slug(), $activatedPlugins, true) !== false,
                    )
                );
            }
        }

        return new WP_REST_Response($result, 200);
    }

    /**
     * Modifies the response to disable caching
     *
     * @param WP_REST_Response $response The endpoint its response
     *
     * @return WP_REST_Response
     */
    private function modify_response($response)
    {
        return $response;
    }

    private function get_ce_checkout($checkout_uuid) {
        if (empty($checkout_uuid)) {
            return new WP_REST_Response("No uuid provided", 400);
        }

        $checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( 'user_id,user_email,checkout_contents,checkout_updated,checkout_created,checkout_recovered,checkout_uuid',
            'checkout_uuid = %s', [ $checkout_uuid ] );
        if (empty($checkout)) {
            return new WP_REST_Response($checkout, 404);
        }

        return new WP_REST_Response($checkout, 200);
    }

    private function get_wc_keys()
    {
        $wcKey  = CreativeMail::get_instance()->get_api_manager()->get_or_generate_key();
        $key    = sha1(OptionsHelper::get_instance_api_key() . OptionsHelper::get_instance_uuid());
        $salt   = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $salted = '';
        $dx     = '';
        while ( strlen($salted) < 48 ) {
            $dx     = md5($dx . $key . $salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, 16);
        $cs  = openssl_encrypt($wcKey->consumer_secret, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $ck  = openssl_encrypt($wcKey->consumer_key, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        $result          = new \stdClass();
        $result->salt    = bin2hex($salt);
        $result->secret  = base64_encode($cs);
        $result->key     = base64_encode($ck);
        $result->version = '1';

        return new WP_REST_Response($result, 200);
    }

    /**
     * Registers a route to the WP Rest endpoints for this plugin.
     *
     * @param array $route
     */
    private function register_route(array $route)
    {

        // Make sure the route is valid
        $path = $route[self::ROUTE_PATH];
        $methods = $route[self::ROUTE_METHODS];
        $callback = $route[self::ROUTE_CALLBACK];

        if (array_key_exists(self::ROUTE_PERMISSION_CALLBACK, $route)) {
            $permission_callback = $route[self::ROUTE_PERMISSION_CALLBACK];
        }
        else if(in_array($path, self::ROUTE_REQUIRES_WP_ADMIN)) {
            $permission_callback = array( $this, 'validate_wp_admin' );
        }
        else {
            $permission_callback = array( $this, 'validate_api_key' );
        }

        // Make sure we at least have a path
        if (empty($path)) { return;
        }

        // If we don't have a method, assume it is GET
        if(empty($methods)) {
            $methods = 'GET';
        }

        $arguments = array(
            self::ROUTE_METHODS               => $methods,
            self::ROUTE_CALLBACK              => $callback,
            self::ROUTE_PERMISSION_CALLBACK   => $permission_callback
        );

        register_rest_route(self::API_NAMESPACE, $path, $arguments);
    }

    /**
     * Refreshes the WC REST API key.
     *
     * @param int $user_id WordPress user ID
     *
     * @return object|bool
     *
     * @throws Exception
     *
     * @since 1.1.0
     */
    public function refresh_key( $user_id = null )
    {

        $this->revoke_key();

        return $this->create_key($user_id);
    }


    /**
     * Generates a WC REST API key for Jilt to use.
     *
     * @param int $user_id WordPress user ID
     *
     * @return object
     *
     * @throws Exception
     *
     * @since 1.1.0
     */
    public function create_key( $user_id = null )
    {
        global $wpdb;

        // if no user is specified, try the current user or find an eligible admin
        if (! $user_id ) {

            $user_id = get_current_user_id();

            // if the current user can't manage WC, try and get the first admin
            if (! user_can($user_id, 'manage_woocommerce') ) {

                $user_id = null;

                $administrator_ids = get_users(
                    array(
                    'role'   => 'administrator',
                    'fields' => 'ID',
                    )
                );

                foreach ( $administrator_ids as $administrator_id ) {

                    if (user_can($administrator_id, 'manage_woocommerce') ) {

                        $user_id = $administrator_id;
                        break;
                    }
                }

                if (! $user_id ) {
                    throw new Exception('No eligible users could be found');
                }
            }

            // otherwise, check the user that's specified
        } elseif (! user_can($user_id, 'manage_woocommerce') ) {

            throw new Exception("User {$user_id} does not have permission");
        }

        $user = get_userdata($user_id);

        if (! $user ) {
            throw new Exception('Invalid user');
        }

        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $result = $wpdb->insert(
            $wpdb->prefix . 'woocommerce_api_keys',
            array(
                'user_id'         => $user->ID,
                'description'     => 'CreativeMail',
                'permissions'     => 'read_write',
                'consumer_key'    => wc_api_hash($consumer_key),
                'consumer_secret' => $consumer_secret,
                'truncated_key'   => substr($consumer_key, -7),
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );

        if (! $result ) {
            throw new Exception('The key could not be saved');
        }

        $key = new \stdClass();

        $key->key_id          = $wpdb->insert_id;
        $key->user_id         = $user->ID;
        $key->consumer_key    = $consumer_key;
        $key->consumer_secret = $consumer_secret;

        // store the new key ID
        $this->set_key_id($key->key_id);
        $this->set_consumer_key($consumer_key);

        return $key;
    }


    /**
     * Revokes the configured WC REST API key.
     *
     * @since 1.1.0
     */
    public function revoke_key()
    {
        global $wpdb;

        if ($key_id = $this->get_key_id() ) {
            $wpdb->delete($wpdb->prefix . 'woocommerce_api_keys', array( 'key_id' => $key_id ), array( '%d' ));
        }

        OptionsHelper::delete_wc_api_key_id();
        OptionsHelper::delete_wc_consumer_key();
    }


    /**
     * Gets the configured WC REST API key.
     *
     * @since 1.1.0
     *
     * @return object|null
     */
    public function get_key()
    {
        global $wpdb;

        $key = null;

        if ($id = $this->get_key_id() ) {
            $key = $wpdb->get_row(
                $wpdb->prepare(
                    "
				SELECT key_id, user_id, permissions, consumer_secret
				FROM {$wpdb->prefix}woocommerce_api_keys
				WHERE key_id = %d
			", $id
                )
            );

            if (isset($key) ) {
                $key->consumer_key = $this->get_consumer_key();
            }
        }

        return $key;
    }

    /**
     * Gets or generate the configured WC REST API key.
     *
     * @since 1.1.0
     *
     * @return object|null
     */
    public function get_or_generate_key()
    {
        $key = $this->get_key();

        if ($key == null) {
            $key = $this->refresh_key();
        }

        return $key;
    }


    /**
     * Gets the configured WC REST API key ID.
     *
     * @since 1.1.0
     *
     * @return int
     */
    public function get_key_id()
    {

        return OptionsHelper::get_wc_api_key_id();
    }

    public function get_consumer_key()
    {

        return OptionsHelper::get_wc_consumer_key();
    }

    /**
     * Sets a WC REST API key ID.
     *
     * @param int $id key ID
     *
     * @since 1.1.0
     */
    public function set_key_id( $id )
    {
        OptionsHelper::set_wc_api_key_id($id);
    }

    public function set_consumer_key( $key )
    {
        OptionsHelper::set_wc_consumer_key($key);
    }
}
