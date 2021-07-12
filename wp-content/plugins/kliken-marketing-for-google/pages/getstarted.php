<?php
/**
 * Get started page.
 *
 * @package Kliken Marketing for Google
 */

defined( 'ABSPATH' ) || exit;

?>

<style>
	#getstarted_btn {
		background-color: #ff6600;
		color: #fff;
		padding: 10px 20px;
		font-size: 14px;
		text-decoration: none;
		font-family: "Lato",sans-serif;
		line-height: 35px;
		font-weight: 700;
		letter-spacing: 2px !important;
		text-transform: uppercase;
	}
</style>

<div class="wrap kk-wrapper">
	<h2><?php esc_html_e( 'Google Ads & Marketing by Kliken', 'kliken-marketing-for-google' ); ?></h2>

	<p><?php esc_html_e( 'Launch Google Shopping Ads and get your products found online easily.', 'kliken-marketing-for-google' ); ?></p>

	<div class="kk-box">
		<div class="kk-box-header">
			<div class="kk-img-container">
				<img src="https://msacdn.s3.amazonaws.com/images/logos/woocommerce/GoogleHeader-Logo.svg" alt="Google Logo" height="40" class="kk-google-img">
				<img src="https://msacdn.s3.amazonaws.com/images/logos/woocommerce/PoweredByKliken.png" alt="Powered by Kliken" class="kk-poweredby-img">
			</div>
		</div>

		<div class="kk-box-content">
			<h1><?php esc_html_e( 'Increase sales and revenue with Google Shopping Ads', 'kliken-marketing-for-google' ); ?></h1>

			<p class="subhdr"><?php esc_html_e( 'Use this WooCommerce and Google Ads integration to:', 'kliken-marketing-for-google' ); ?></h5>
			<ul>
				<li><?php esc_html_e( 'Find more customers on Google', 'kliken-marketing-for-google' ); ?></li>
				<li><?php esc_html_e( 'Automate bidding to maximize sales for your marketing budget', 'kliken-marketing-for-google' ); ?></li>
				<li><?php esc_html_e( 'Keep your marketing and store in sync', 'kliken-marketing-for-google' ); ?></li>
				<li><?php esc_html_e( 'Create perfect shopping campaigns in minutes', 'kliken-marketing-for-google' ); ?></li>
			</ul>

			<a id="getstarted_btn" href="<?php echo esc_url( \Kliken\WcPlugin\Helper::build_signup_link() ); ?>"><?php esc_html_e( 'Get Started', 'kliken-marketing-for-google' ); ?></a>
		</div>
	</div>
</div>
