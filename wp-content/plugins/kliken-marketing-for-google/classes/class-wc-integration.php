<?php
/**
 * Integration class to hook plugin settings into WooCommerce's Settings Integration tab
 *
 * @package Kliken Marketing for Google
 */

namespace Kliken\WcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Integration class
 */
class WC_Integration extends \WC_Integration {
	/**
	 * Constructor. Default to also init.
	 *
	 * @param boolean $init If class initialization is also needed.
	 */
	public function __construct( $init = true ) {
		global $woocommerce;

		$this->id           = KK_WC_INTEGRATION_PAGE_ID;
		$this->method_title = __( 'Google Ads & Marketing by Kliken', 'kliken-marketing-for-google' );  // This is for the sub-section text.

		if ( true === $init ) {
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			$this->account_id = $this->get_option( 'account_id' );
			$this->app_token  = $this->get_option( 'app_token' );

			// Action to save the options.
			add_action( 'woocommerce_update_options_integration_' . $this->id, [ $this, 'process_admin_options' ] );
			add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, [ $this, 'sanitize_settings' ] );
		}
	}

	/**
	 * Initialize integration settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'account_id'   => [
				'title'   => __( 'Account Id', 'kliken-marketing-for-google' ),
				'type'    => 'text',
				'default' => '',
			],
			'app_token'    => [
				'title'   => __( 'Application Token', 'kliken-marketing-for-google' ),
				'type'    => 'text',
				'default' => '',
			],
			'google_token' => [
				'title'   => __( 'Google Verification Token', 'kliken-marketing-for-google' ),
				'type'    => 'text',
				'default' => '',
			],
			'guide_link'   => [
				'type'        => 'desc',
				'description' => '<a href="https://support.sitewit.com/hc/en-us/articles/360016107933-WooCommerce-Advanced-Options" target="_blank" rel="noopener noreferrer">' . __( 'What are these options?', 'kliken-marketing-for-google' ) . '</a>',
				'desc_tip'    => '',
			],
		];
	}

	/**
	 * Display "options" area.
	 */
	public function admin_options() {
		Helper::check_redirect_for_wc_auth();

		// So this is the way WordPress hide the save button *facepalming*.
		$GLOBALS['hide_save_button'] = true;

		include_once KK_WC_PLUGIN_DIR . 'pages/admin-style.php';

		if ( Helper::is_valid_account_id( $this->settings['account_id'] )
			&& Helper::is_valid_app_token( $this->settings['app_token'] )
		) {
			// Already have account info. Show dashboard.
			include_once KK_WC_PLUGIN_DIR . 'pages/dashboard.php';
		} else {
			// Show sign up form.
			include_once KK_WC_PLUGIN_DIR . 'pages/getstarted.php';
		}
	}

	/**
	 * Generate HTML code for "desc" field.
	 * This is a custom field for display a descriptive info line alongside other fields.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 */
	public function generate_desc_html( $key, $data ) {
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc" style="padding: 0;">
			</th>
			<td class="forminp" style="padding: 0 0 0 10px;">
				<fieldset>
					<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Sanitize setting values before saving to the database
	 *
	 * @param array $settings Array of fields setting values being submitted.
	 * @return array
	 */
	public function sanitize_settings( $settings ) {
		// Validate account id as an integer.
		$settings['account_id'] = intval( $settings['account_id'] );

		// Sanitize token.
		$settings['app_token'] = sanitize_text_field( $settings['app_token'] );

		// Sanitize google verification token.
		$settings['google_token'] = sanitize_text_field( $settings['google_token'] );

		if ( 0 === $settings['account_id'] || '' === $settings['app_token'] ) {
			// No (valid) values were entered, we should show onboarding/welcome message.
			set_site_transient( KK_WC_WELCOME_MESSAGE, true, MONTH_IN_SECONDS );
		} elseif ( intval( $this->account_id ) !== $settings['account_id']
			|| $settings['app_token'] !== $this->app_token
		) {
			// If the values changed, we want to redirect them to the WooCommerce Authorization page immediately after save.
			$authorization_url = Helper::build_authorization_url( $settings['account_id'], $settings['app_token'] );
			if ( ! empty( $authorization_url ) ) {
				set_site_transient( KK_WC_TRANSIENT_AUTH_REDIRECT, $authorization_url, MINUTE_IN_SECONDS );
			}
		}

		return $settings;
	}
}
