<?php
/**
 * Main plugin class to bootstrap everything else
 *
 * @package Kliken Marketing for Google
 */

namespace Kliken\WcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin class
 */
class Plugin {

	const ALREADY_BOOTSTRAPED      = 1;
	const DEPENDENCIES_UNSATISFIED = 2;
	const DISMISS_POSTFIX          = '_dismissed';

	/**
	 * Flag to indicate the plugin has been boostrapped.
	 *
	 * @var bool
	 */
	private $_bootstrapped = false;

	/**
	 * Try to register important hooks, with main stuff inside "plugins_loaded"
	 * so we can have checks for requirements/dependencies.
	 */
	public function maybe_run() {
		register_activation_hook( KK_WC_PLUGIN_FILE, [ $this, 'activate' ] );

		add_action( 'plugins_loaded', [ $this, 'bootstrap' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( KK_WC_PLUGIN_FILE ), [ $this, 'plugin_action_links' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		if ( is_admin() ) {
			add_action( 'wp_ajax_' . KK_WC_ACTION_DISMISS_NOTICE, [ $this, 'ajax_dismiss_notice' ] );
		}
	}

	/**
	 * Bootstrap the execution of the plugin.
	 *
	 * @throws \Exception Throw an exception if the plugin has been called before.
	 */
	public function bootstrap() {
		try {
			load_plugin_textdomain( 'kliken-marketing-for-google', false, KK_WC_PLUGIN_REL_PATH . '/languages' );

			if ( $this->_bootstrapped ) {
				throw new \Exception( __( 'Google Ads & Marketing by Kliken plugin can only be called once', 'kliken-marketing-for-google' ), self::ALREADY_BOOTSTRAPED );
			}

			$this->check_dependencies();
			$this->run();

			$this->_bootstrapped = true;
		} catch ( \Exception $e ) {
			if ( in_array( $e->getCode(), [ self::ALREADY_BOOTSTRAPED, self::DEPENDENCIES_UNSATISFIED ] ) ) {
				set_site_transient( KK_WC_BOOTSTRAP_MESSAGE, $e->getMessage(), MONTH_IN_SECONDS );
			}

			add_action( 'admin_notices', [ $this, 'show_bootstrap_message' ] );
		}
	}

	/**
	 * Run when the plugin is activated.
	 */
	public function activate() {
		$saved_settings = Helper::get_plugin_options();
		if ( empty( $saved_settings ) ) {
			update_option( Helper::get_option_key(), [
				'account_id'   => 0,
				'app_token'    => '',
				'google_token' => '',
			] );

			$has_account_id = false;
		} else {
			$has_account_id = Helper::is_valid_account_id( $saved_settings['account_id'] );
		}

		if ( ! $has_account_id ) {
			// Try to find account id baked into the plugin zip.
			$config_path = KK_WC_PLUGIN_DIR . 'config.php';
			if ( file_exists( $config_path ) ) {
				include_once $config_path;

				// $kk_account_id and $kk_app_token are defined in the config file.
				$authorization_url = Helper::save_account_info( $kk_account_id, $kk_app_token );

				// Remove the file.
				unlink( $config_path );  // phpcs:ignore WordPress.VIP.FileSystemWritesDisallow.file_ops_unlink

				// Here we need to know if after activation we need to redirect to the WooCommerce Auth page or not.
				if ( ! empty( $authorization_url ) ) {
					set_site_transient( KK_WC_TRANSIENT_AUTH_REDIRECT, $authorization_url, MINUTE_IN_SECONDS );
				}
			} else {
				// Seems to not have account info, we will try to show onboarding message, for a month, at top.
				set_site_transient( KK_WC_WELCOME_MESSAGE, true, MONTH_IN_SECONDS );
			}
		}
	}

	/**
	 * Add more links to plugin's "row" in the WordPress' plugin list,
	 * for more actions that user can do.
	 *
	 * @param array $links A list of links already registered.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = [];

		if ( function_exists( 'wc' ) ) {
			$plugin_links[] = '<a href="' . esc_url( Helper::get_plugin_page() ) . '">' . __( 'Dashboard', 'kliken-marketing-for-google' ) . '</a>';
		}

		$plugin_links[] = '<a href="https://kliken.com/contact/">' . __( 'Support', 'kliken-marketing-for-google' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Show bootstrap (warning) message if needed.
	 */
	public function show_bootstrap_message() {
		$message = get_site_transient( KK_WC_BOOTSTRAP_MESSAGE );

		if ( ! empty( $message )
			&& false === get_option( KK_WC_BOOTSTRAP_MESSAGE . self::DISMISS_POSTFIX, false )
			&& current_user_can( 'manage_options' ) ) {
			Message::show_warning( $message, 'bsmessage' );
		}
	}

	/**
	 * Show onboarding (info) message if needed.
	 */
	public function show_onboarding_message() {
		$settings = Helper::get_plugin_options();

		if ( false !== get_site_transient( KK_WC_WELCOME_MESSAGE, false )
			&& false === get_option( KK_WC_WELCOME_MESSAGE . self::DISMISS_POSTFIX, false )
			&& current_user_can( 'manage_options' ) && ! Helper::is_plugin_page()
			&& ! Helper::is_valid_account_id( $settings['account_id'] ) ) {
			Message::show_info( Helper::get_onboarding_message(), 'welcome' );
		}
	}

	/**
	 * Add a new integration to WooCommerce.
	 *
	 * @param array $integrations List of integrations.
	 * @return array
	 */
	public function woocommerce_integrations( $integrations ) {
		$integrations[] = '\\Kliken\\WcPlugin\\WC_Integration';

		return $integrations;
	}

	/**
	 * Add Tracking Script and Google Verification Token to site's header if available.
	 */
	public function wp_head() {
		// Inject Google verification token.
		Helper::add_google_verification_token();

		// Inject tracking script.
		Helper::add_tracking_script();
	}

	/**
	 * Register custom REST API endpoints for WooCommerce.
	 */
	public function rest_api_init() {
		( new REST_Misc_Controller() )->register_routes();
		( new REST_Products_Controller() )->register_routes();
		( new REST_Orders_Controller() )->register_routes();
	}

	/**
	 * Stuff to do during admin initialization.
	 */
	public function admin_init() {
		Helper::check_redirect_for_wc_auth();
	}

	/**
	 * Enqueue script(s) needed for admin pages.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'kk-admin-script', KK_WC_PLUGIN_URL . 'assets/admin-script.js', [ 'jquery' ] );

		wp_localize_script(
			'kk-admin-script',
			'dimissibleNotice',
			[
				'action'         => KK_WC_ACTION_DISMISS_NOTICE,
				'nonce'          => wp_create_nonce( KK_WC_ACTION_DISMISS_NOTICE ),
				'confirmMessage' => esc_html__( 'This will dismiss the message permanently. Are you sure?', 'kliken-marketing-for-google' ),
			]
		);
	}

	/**
	 * Action to save account information after signing up.
	 */
	public function save_account() {
		if ( ! isset( $_GET['t'] ) || ! wp_verify_nonce( sanitize_key( $_GET['t'] ), KK_WC_ACTION_SAVE_ACCOUNT ) ) { // WPCS: input var ok.
			wp_nonce_ays( KK_WC_ACTION_SAVE_ACCOUNT );
		}

		// Default to redirect back to dashboard page.
		$url = Helper::get_plugin_page();

		if ( isset( $_GET['maid'], $_GET['appt'] ) ) { // WPCS: input var ok.
			// Once account info is saved, we will have to do WooCommerce API Authorization again.
			$authorization_url = Helper::save_account_info(
				sanitize_text_field( wp_unslash( $_GET['maid'] ) ), // WPCS: input var ok.
				sanitize_text_field( wp_unslash( $_GET['appt'] ) ) // WPCS: input var ok.
			);

			if ( $authorization_url ) {
				$url = $authorization_url;
			}
		}

		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Action to handle AJAX request to permanently dismiss a notice.
	 */
	public function ajax_dismiss_notice() {
		check_ajax_referer( KK_WC_ACTION_DISMISS_NOTICE );

		if ( isset( $_POST['message_name'] ) ) { // WPCS: input var ok.
			$message_name = sanitize_key( $_POST['message_name'] );  // WPCS: input var ok.

			switch ( $message_name ) {
				case 'welcome':
					update_option( KK_WC_WELCOME_MESSAGE . self::DISMISS_POSTFIX, true );
					break;
				case 'bsmessage':
					update_option( KK_WC_BOOTSTRAP_MESSAGE . self::DISMISS_POSTFIX, true );
					delete_site_transient( KK_WC_BOOTSTRAP_MESSAGE );
					break;
			}
		}

		wp_die();
	}

	/**
	 * Check if the dependencies of the plugin are satisfied.
	 * Here we need WooCommerce 3.0 or above, to be installed, and active.
	 *
	 * @throws \Exception Throw an exception if the dependency is not satisfied.
	 */
	private function check_dependencies() {
		if ( ! function_exists( 'wc' ) ) {
			throw new \Exception( __( 'Google Ads & Marketing by Kliken requires WooCommerce to be activated', 'kliken-marketing-for-google' ), self::DEPENDENCIES_UNSATISFIED );
		}

		$required_woo_version = '3.0';
		if ( version_compare( wc()->version, $required_woo_version, '<' ) ) {
			/* translators: %s: Version number of WooCommerce required to run plugin. Do not translate. */
			throw new \Exception( sprintf( __( 'Google Ads & Marketing by Kliken requires WooCommerce version %s or greater', 'kliken-marketing-for-google' ), $required_woo_version ), self::DEPENDENCIES_UNSATISFIED );
		}
	}

	/**
	 * Main running point of the plugins. Responsible for hooking into different parts of WordPress and WooCommerce.
	 */
	private function run() {
		add_filter( 'woocommerce_integrations', [ $this, 'woocommerce_integrations' ] );
		add_action( 'wp_head', [ $this, 'wp_head' ] );
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_notices', [ $this, 'show_onboarding_message' ] );

		// Additional admin actions registered for handling requests.
		if ( is_admin() ) {
			// Handle request to save account info coming back from Kliken sign up page.
			add_action( 'admin_action_' . KK_WC_ACTION_SAVE_ACCOUNT, [ $this, 'save_account' ] );
		}
	}
}
