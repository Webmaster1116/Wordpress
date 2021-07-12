<?php
/**
 * Adapter class to provide compatibility to WooCommerce version < 3.5.0, when
 * the version 3 of WooCommerce REST API was introduced.
 *
 * @package Kliken Marketing for Google
 */

namespace Kliken\WcPlugin;

defined( 'ABSPATH' ) || exit;

if ( class_exists( '\WC_REST_Orders_V2_Controller' ) ) {
	/**
	 * WooCommerce at v3. We still try to extend from the v2 of the class.
	 */
	class WC_REST_Orders_Controller_Compat extends \WC_REST_Orders_V2_Controller { }
} else {
	/**
	 * WooCommerce at v2.
	 */
	class WC_REST_Orders_Controller_Compat extends \WC_REST_Orders_Controller { }
}
