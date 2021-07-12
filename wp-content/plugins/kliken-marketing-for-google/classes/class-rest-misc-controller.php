<?php
/**
 * REST controller for miscellaneous endpoints.
 *
 * @package Kliken Marketing for Google
 */

namespace Kliken\WcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * REST API Misc controller class.
 *
 * @extends WC_REST_CRUD_Controller
 */
class REST_Misc_Controller extends \WC_REST_CRUD_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-kliken/v1';

	/**
	 * Register the routes we need.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/google-token', [
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_google_token' ],
					'permission_callback' => function() {
						return current_user_can( 'manage_options' );
					},
				],
			]
		);

		register_rest_route(
			$this->namespace, '/shipping/wc-services', [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_wc_services_shipping_methods' ],
					'permission_callback' => [ $this, 'get_shipping_methods_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Save Google Verification Token to database so we can later on display it as a header's meta.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save_google_token( $request ) {
		// If we know more about Google's token specifications, we can have more strict rules
		// For now, just sanitize the token as a text string (no tags, no simple injections).
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		if ( ! $token ) {
			return new \WP_Error( 'rest_bad_request', __( 'Invalid Data', 'kliken-marketing-for-google' ), [ 'status' => 400 ] );
		}

		$saved_settings                 = Helper::get_plugin_options();
		$saved_settings['google_token'] = $token;

		// Save the token to database.
		update_option( Helper::get_option_key(), $saved_settings );

		// But WP_REST_Response is only available since 4.4?
		return new \WP_REST_Response( null, 201 );
	}

	/**
	 * Get shipping services associated with a WooCommerce Services Carrier shipping method.
	 * Because for some reason, WooCommerce does not return that with their API endpoints.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_wc_services_shipping_methods( $request ) {
		$id           = (int) $request['id'];
		$carrier_name = $request['carrier'];

		// Get the option from database.
		$data = get_option( "woocommerce_wc_services_{$carrier_name}_{$id}_form_settings" );

		if ( ! $data || empty( $data ) || empty( $data->services ) ) {
			return new \WP_Error( 'no_wc_services', __( 'Invalid Data', 'kliken-marketing-for-google' ), [ 'status' => 404 ] );
		}

		$carrier_services = [];

		foreach ( $data->services as $key => $service ) {
			if ( true !== $service['enabled'] ) {
				continue;
			}

			array_push(
				$carrier_services, [
					'service_name'    => $service['id'],
					'adjustment'      => $service['adjustment'],
					'adjustment_type' => $service['adjustment_type'],
				]
			);
		}

		$response = rest_ensure_response(
			[
				'carrier_name'     => $data->title,
				'carrier_services' => $carrier_services,
			]
		);

		return $response;
	}

	/**
	 * Check whether a given request has permission to view shipping methods.
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|boolean
	 */
	public function get_shipping_methods_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'shipping_methods', 'read' ) ) {
			return new \WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'kliken-marketing-for-google' ), [ 'status' => rest_authorization_required_code() ] );
		}
		return true;
	}
}
