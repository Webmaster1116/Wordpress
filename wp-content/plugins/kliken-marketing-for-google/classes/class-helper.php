<?php
/**
 * Helper class to provide some common functionalities
 *
 * @package Kliken Marketing for Google
 */

namespace Kliken\WcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class
 */
class Helper {
	/**
	 * Name of the option that WooCommerce will save the plugin settings into.
	 *
	 * @var string
	 */
	private static $_option_key = null;

	/**
	 * A wrapper around WC_Logger log method.
	 *
	 * @param string  $level Log level.
	 * @param string  $message Message to log.
	 * @param boolean $force Force logging without the need of WP_DEBUG mode.
	 */
	public static function wc_log( $level, $message, $force = false ) {
		if ( ( WP_DEBUG || $force )
			&& function_exists( 'wc_get_logger' )
			&& $message
			&& in_array( $level, [ 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ], true ) ) {
			wc_get_logger()->log( $level, $message, [ 'source' => 'kliken-marketing-for-google' ] );
		}
	}

	/**
	 * Get plugin option key, because of WooCommerce Integration Settings API.
	 *
	 * @return string
	 */
	public static function get_option_key() {
		if ( null === self::$_option_key ) {
			if ( function_exists( 'wc' ) && class_exists( '\WC_Integration' ) ) {
				self::$_option_key = ( new WC_Integration( false ) )->get_option_key();
			} else {
				// In the weird case when WooCommerce is not available.
				self::$_option_key = 'woocommerce_kk_wcintegration_settings';
			}
		}

		return self::$_option_key;
	}

	/**
	 * Get plugin options, being an integration with WooCommerce Settings API.
	 *
	 * @return array
	 */
	public static function get_plugin_options() {
		return get_option( self::get_option_key(), [] );
	}

	/**
	 * Check if provided id can be considered a valid account id.
	 * Should be an integer.
	 *
	 * @param mixed $id Account Id.
	 * @return boolean
	 */
	public static function is_valid_account_id( $id ) {
		return ! empty( $id ) && ctype_digit( strval( $id ) );
	}

	/**
	 * Check if the provided token can be considered a valid application token.
	 * Should be not empty, for now.
	 *
	 * @param string $token Application token.
	 * @return boolean
	 */
	public static function is_valid_app_token( $token ) {
		$token = sanitize_text_field( $token );
		return ! empty( $token );
	}

	/**
	 * Check if current page is a page of the plugin, matching the provided page slug.
	 *
	 * @param string $page_slug Page slug.
	 * @return boolean
	 */
	public static function is_plugin_page( $page_slug = null ) {
		global $pagenow;

		if ( 'admin.php' !== $pagenow ) {
			return false;
		}

		if ( null !== $page_slug ) {
			return ( isset( $_GET['page'] ) && $page_slug === $_GET['page'] ); // WPCS: CSRF ok, input var ok.
		} else {
			return (
				isset( $_GET['page'] ) && 'wc-settings' === $_GET['page']
				&& isset( $_GET['tab'] ) && 'integration' === $_GET['tab']
				&& isset( $_GET['section'] ) && KK_WC_INTEGRATION_PAGE_ID === $_GET['section']
			); // WPCS: CSRF ok, input var ok.
		}
	}

	/**
	 * Get plugin page URL.
	 */
	public static function get_plugin_page() {
		return 'admin.php?page=wc-settings&tab=integration&section=' . KK_WC_INTEGRATION_PAGE_ID;
	}

	/**
	 * Get base64 encoded data url for the logo image.
	 *
	 * @return string
	 */
	public static function get_base64_icon() {
		// phpcs:ignore
		return 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( KK_WC_PLUGIN_DIR . 'assets/logo-small.svg' ) );
	}

	/**
	 * Get a list of supported languages (WP style) with a map to .NET style
	 *
	 * @return array
	 */
	public static function get_wl_supported_languages() {
		return [
			'en_US' => 'en',
			'es_ES' => 'es',
			'de_DE' => 'de',
			'de_CH' => 'de-CH',
			'fr_FR' => 'fr',
			'nl_NL' => 'nl',
		];
	}

	/**
	 * Get locale of WordPress site/user, in .NET style.
	 * Default to English if not supported by the plugin.
	 *
	 * @return string
	 */
	public static function get_wl_locale() {
		if ( function_exists( 'get_user_locale' ) ) {
			$wp_locale = get_user_locale();
		} else {
			$wp_locale = get_locale();
		}

		$supported_langs = self::get_wl_supported_languages();

		if ( array_key_exists( $wp_locale, $supported_langs ) ) {
			return $supported_langs[ $wp_locale ];
		}

		return 'en';
	}

	/**
	 * Get locale of WordPress site/user, with a postfix.
	 *
	 * @param string $postfix Postfix to be used. Defaults to "-".
	 * @return string
	 */
	public static function get_locale_postfix( $postfix = '-' ) {
		if ( function_exists( 'get_user_locale' ) ) {
			$wp_locale = get_user_locale();
		} else {
			$wp_locale = get_locale();
		}

		$supported_langs = self::get_wl_supported_languages();

		if ( array_key_exists( $wp_locale, $supported_langs ) ) {
			if ( 'en_US' === $wp_locale ) {
				return '';
			} else {
				return $postfix . $wp_locale;
			}
		}

		return '';
	}

	/**
	 * Add SiteWit tracking script to page.
	 */
	public static function add_tracking_script() {
		global $wp;

		$saved_settings = self::get_plugin_options();

		if ( ! self::is_valid_account_id( $saved_settings['account_id'] ) ) {
			return;
		}

		// Check if is cart/checkout page.
		if ( is_cart() || is_checkout() ) {
			$cart = self::build_cart_data();

			if ( null !== $cart ) {
				include_once KK_WC_PLUGIN_DIR . 'pages/cartscript.php';
			}
		}

		// Check if is product page.
		if ( is_product() ) {
			$product = self::build_product_data();

			if ( null !== $product ) {
				include_once KK_WC_PLUGIN_DIR . 'pages/productscript.php';
			}
		}

		// Check if is order received page.
		if ( is_order_received_page() ) {
			$order_id = isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : 0;
			$trans    = self::build_transaction_data( $order_id );

			if ( null !== $trans ) {
				include_once KK_WC_PLUGIN_DIR . 'pages/transactionscript.php';
			}
		}

		$account_id = $saved_settings['account_id'];
		include_once KK_WC_PLUGIN_DIR . 'pages/trackingscript.php';
	}

	/**
	 * Add Google Verification Token to page meta.
	 */
	public static function add_google_verification_token() {
		$saved_settings = self::get_plugin_options();

		// Sanitize the saved string again just in case.
		$token = sanitize_text_field( $saved_settings['google_token'] );

		if ( $token ) {
			printf( '<!-- Kliken Google Site Verification Token Tag -->' );
			printf( '<meta name="google-site-verification" content="%s" />', esc_attr( $token ) );
		}
	}

	/**
	 * Build transaction/order data preparing to be recorded by our tracking script
	 *
	 * @param int $order_id WooCommerce Order Id.
	 * @return array|null
	 */
	public static function build_transaction_data( $order_id ) {
		// Get the order detail.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return null;
		}

		// We don't care about these statuses.
		$status = $order->get_status();
		if ( 'cancelled' === $status || 'refunded' === $status || 'failed' === $status ) {
			return null;
		}

		$transaction = [
			'order_id'  => $order_id,
			'currency'  => $order->get_currency(),
			'affiliate' => null,
			'sub_total' => $order->get_subtotal(),
			'tax'       => $order->get_total_tax(),
			'city'      => $order->get_billing_city(),
			'state'     => $order->get_billing_state(),
			'country'   => $order->get_billing_country(),
			'total'     => $order->get_total(),
			'items'     => [],
		];

		$order_items = $order->get_items();

		// Cache category info, because in the order, there might be multiple items under same category.
		$category_cache = [];

		foreach ( $order_items as $index => $item ) {
			$product = $item->get_product();

			if ( ! $product ) continue;

			$product_categories = $product->get_category_ids();
			$category_name      = '';

			foreach ( $product_categories as $index => $id ) {
				if ( array_key_exists( $id, $category_cache ) ) {
					$category_name = $category_cache[ $id ];
				} else {
					$term = get_term_by( 'id', $id, 'product_cat' );
					if ( $term ) {
						$category_name         = $term->name;
						$category_cache[ $id ] = $category_name;
					}
				}
			}

			array_push(
				$transaction['items'], [
					'id'      => $product->get_id(),
					'name'     => $product->get_name(),
					'category' => $category_name,
					'price'    => $product->get_price(),
					'quantity' => $item->get_quantity(),
				]
			);
		}

		return $transaction;
	}

	/**
	 * Build product data preparing to be recorded by our tracking script
	 *
	 * @return array|null
	 */
	public static function build_product_data() {
		$product = wc_get_product();

		if ( ! $product ) {
			return null;
		}

		$product_info = [
			'id'      => $product->get_id(),
			'name'     => $product->get_name(),
			'price'    => $product->get_price(),
			'category' => $product->get_category_ids()
		];

		return $product_info;
	}

	/**
	 * Build current cart data preparing to be recorded by our tracking script
	 *
	 * @return array|null
	 */
	public static function build_cart_data() {
		$cart = WC()->cart;

		if ( null === $cart || $cart->is_empty() ) {
			return null;
		}

		return $cart->get_cart();
	}

	/**
	 * Build the WooCommerce authorization URL
	 * Doc: https://woocommerce.github.io/woocommerce-rest-api-docs/#rest-api-keys
	 *
	 * @param int    $account_id Account Id.
	 * @param string $application_token Application Token.
	 * @return string|null
	 */
	public static function build_authorization_url( $account_id, $application_token ) {
		if ( empty( $account_id ) || empty( $application_token ) ) {
			return null;
		}

		$authorization_url = get_site_url() . '/wc-auth/v1/authorize'
			. '?app_name=' . rawurlencode( KK_WC_AUTH_APP_NAME )
			. '&scope=read_write'
			. '&user_id=' . base64_encode( $account_id . ':' . $application_token )
			. '&return_url=' . rawurlencode( 'bit.ly/2OweS8h' ) // This links back to woo.kliken.com. We just need to do this to shorten the link because some WordPress hostings seem to dislike long links.
			. '&callback_url=' . rawurlencode( KK_WC_AUTH_CALLBACK_URL );

		return $authorization_url;
	}

	/**
	 * Build sign up link.
	 */
	public static function build_signup_link() {
		$current_user = wp_get_current_user();

		return sprintf(
			KK_WC_WOOKLIKEN_BASE_URL . 'auth/woo/?u=%s&n=%s&e=%s&t=%s&return=%s',
			rawurlencode( get_site_url() ),
			rawurlencode( $current_user->display_name ),
			rawurlencode( $current_user->user_email ),
			wp_create_nonce( KK_WC_ACTION_SAVE_ACCOUNT ),
			get_admin_url() . 'admin.php?action=' . KK_WC_ACTION_SAVE_ACCOUNT
		);
	}

	/**
	 * Save account information.
	 *
	 * @param int    $account_id Account Id.
	 * @param string $application_token Application Token.
	 * @return string|null WooCommerce authorization URL to redirect to after saving account info.
	 */
	public static function save_account_info( $account_id, $application_token ) {
		if ( self::is_valid_account_id( $account_id )
			&& self::is_valid_app_token( $application_token )
		) {
			$saved_settings = self::get_plugin_options();

			$google_token = '';
			if ( ! empty( $saved_settings['google_token'] ) ) {
				$google_token = $saved_settings['google_token'];
			}

			update_option(
				self::get_option_key(), [
					'account_id'   => intval( $account_id ),
					'app_token'    => sanitize_text_field( $application_token ),
					'google_token' => $google_token,
				]
			);

			return self::build_authorization_url( $account_id, $application_token );
		}

		return null;
	}

	/**
	 * Redirect to WooCommerce Authorization page if needed.
	 */
	public static function check_redirect_for_wc_auth() {
		// Bail if activating from network, or bulk.
		if ( is_network_admin() ) {
			return;
		}

		// If WooCommerce is not available/active, don't bother.
		if ( ! function_exists( 'wc' ) ) {
			return;
		}

		$authorization_url = get_site_transient( KK_WC_TRANSIENT_AUTH_REDIRECT );

		if ( ! empty( $authorization_url ) ) {
			delete_site_transient( KK_WC_TRANSIENT_AUTH_REDIRECT );

			if ( wp_safe_redirect( $authorization_url ) ) {
				exit;
			}
		}
	}

	/**
	 * Get the formatted, clean, safe onboarding message.
	 */
	public static function get_onboarding_message() {
		return sprintf(
			wp_kses(
				/* translators: %s: A hyperlink */
				__( '<strong>Google Ads & Marketing by Kliken plugin is almost ready.</strong> <a href="%s">Click here</a> to get started.', 'kliken-marketing-for-google' ),
				[
					'strong' => [],
					'a'      => [ 'href' => [] ],
				]
			), esc_url( self::get_plugin_page() )
		);
	}

	/**
	 * Get image link.
	 *
	 * @param string $image_base_name Base name of the image, without the extension.
	 * @param string $image_extension Extension of the image, including the ".".
	 */
	public static function get_image_link( $image_base_name, $image_extension ) {
		return KK_WC_PLUGIN_URL . 'assets/' . $image_base_name . self::get_locale_postfix() . $image_extension;
	}
}
