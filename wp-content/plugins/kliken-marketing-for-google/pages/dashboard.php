<?php
/**
 * Dashboard page.
 *
 * @package Kliken Marketing for Google
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wrap kk-wrapper">
	<h2><?php esc_html_e( 'Google Ads & Marketing by Kliken', 'kliken-marketing-for-google' ); ?></h2>

	<p><?php esc_html_e( 'Launch Google Shopping ads and get your products found online easily.', 'kliken-marketing-for-google' ); ?></p>

	<div class="kk-box">
		<div class="kk-box-header">
			<div class="kk-img-container">
				<img src="https://msacdn.s3.amazonaws.com/images/logos/woocommerce/GoogleHeader-Logo.svg" alt="Google Logo" height="40" class="kk-google-img">
				<img src="https://msacdn.s3.amazonaws.com/images/logos/woocommerce/PoweredByKliken.png" alt="Powered by Kliken" class="kk-poweredby-img">
			</div>
		</div>

		<div class="kk-box-content">
			<h1><?php esc_html_e( 'Your store is connected.', 'kliken-marketing-for-google' ); ?></h1>

			<p class="subhdr"><?php esc_html_e( 'Your WooCommerce store is connected to your Kliken account.', 'kliken-marketing-for-google' ); ?></p>

			<hr>

			<div class="kk-link">
				<a class="sub-heading" href="<?php echo esc_url( KK_WC_WOOKLIKEN_BASE_URL . 'smb/shopping/dashboard' ); ?>">
					<?php esc_html_e( 'Campaign Dashboard', 'kliken-marketing-for-google' ); ?>
				</a>
				<p class="sub-note"><?php esc_html_e( 'Open your dashboard to review your campaign\'s performance', 'kliken-marketing-for-google' ); ?></p>
			</div>

			<div class="kk-link">
				<a class="sub-heading" href="<?php echo esc_url( KK_WC_WOOKLIKEN_BASE_URL . 'smb/shopping/create' ); ?>">
					<?php esc_html_e( 'Create a New Google Shopping Campaign', 'kliken-marketing-for-google' ); ?>
				</a>
				<p class="sub-note"><?php esc_html_e( 'Build a campaign in a few minutes, and sell to customers as they search for your products on Google.', 'kliken-marketing-for-google' ); ?></p>
			</div>

			<div class="kk-link">
				<a class="sub-heading" href="<?php echo esc_url( KK_WC_WOOKLIKEN_BASE_URL . 'smb/shopping/manage' ); ?>">
					<?php esc_html_e( 'Manage Campaigns', 'kliken-marketing-for-google' ); ?>
				</a>
				<p class="sub-note"><?php esc_html_e( 'Make changes to your active campaigns, purchase one you built, or reinstate your cancelled campaigns.', 'kliken-marketing-for-google' ); ?></p>
			</div>

			<hr>

			<details class="primer advanced-settings">
				<summary><?php esc_html_e( 'Advanced Options', 'kliken-marketing-for-google' ); ?></summary>

				<div>
					<input type="hidden" name="section" value="<?php echo esc_attr( $this->id ); ?>" />
				</div>
				<table class="form-table">
					<?php
						// NOTE: these methods are only available if this file is included within a WC_Integration extended class.
						$this->generate_settings_html( $this->get_form_fields() ); // WPCS: XSS ok.
					?>
				</table>
				<button type="button" class="button button-default" id="enable-settings-edit"><?php esc_html_e( 'Enable Edit', 'kliken-marketing-for-google' ); ?></button>
				<button type="submit" class="button-primary woocommerce-save-button" name="save" id="submit" value="Save changes"><?php esc_html_e( 'Save Changes', 'kliken-marketing-for-google' ); ?></button>

				<hr>
				<a href="<?php echo esc_url( \Kliken\WcPlugin\Helper::build_authorization_url( $this->account_id, $this->app_token ) ); ?>">
					<button type="button" class="button button-primary" id="authorize-api-access"><?php esc_html_e( 'Authorize API Access', 'kliken-marketing-for-google' ); ?></button>
				</a>
			</details>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function() {
		// Disable setting inputs, and hide submit button
		jQuery(".advanced-settings input[type=text]").prop("disabled", true);
		jQuery("#submit").prop("disabled", true).hide();

		jQuery("#enable-settings-edit").click(function() {
			jQuery(".advanced-settings input[type=text]").prop("disabled", false);
			jQuery("#submit").prop("disabled", false).show();

			// Hide the button itself
			jQuery(this).hide();
		});
	});
</script>
