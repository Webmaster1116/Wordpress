<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API\FBE\Installation\Read;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\API;

/**
 * FBE Installation API read response object.
 *
 * @since 2.0.0
 */
class Response extends API\Response {


	/**
	 * Gets the pixel ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_pixel_id() {

		return ! empty( $this->get_data()->pixel_id ) ? $this->get_data()->pixel_id : '';
	}


	/**
	 * Gets the business manager ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_business_manager_id() {

		return ! empty( $this->get_data()->business_manager_id ) ? $this->get_data()->business_manager_id : '';
	}


	/**
	 * Gets the ad account ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_ad_account_id() {

		return ! empty( $this->get_data()->ad_account_id ) ? $this->get_data()->ad_account_id : '';
	}


	/**
	 * Gets the catalog ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_catalog_id() {

		return ! empty( $this->get_data()->catalog_id ) ? $this->get_data()->catalog_id : '';
	}


	/**
	 * Gets the page ID.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_page_id() {

		$pages = $this->get_data()->pages;

		return ! empty( $pages ) && is_array( $pages ) ? current( $pages ) : '';
	}


	/**
	 * Gets Instagram Business ID.
	 *
	 * @since 2.1.5
	 *
	 * @return string
	 */
	public function get_instagram_business_id() {

		$instagram_profiles = ! empty( $this->get_data()->instagram_profiles ) ? $this->get_data()->instagram_profiles : '';

		if ( empty( $instagram_profiles ) ) {
			return '';
		}

		return is_array( $instagram_profiles ) ? current( $instagram_profiles ) : $instagram_profiles;
	}


	/**
	 * Gets the commerce merchant settings ID.
	 *
	 * @since 2.1.5
	 *
	 * @return string
	 */
	public function get_commerce_merchant_settings_id() {

		return ! empty( $this->get_data()->commerce_merchant_settings_id ) ? $this->get_data()->commerce_merchant_settings_id : '';
	}


	/**
	 * Gets the profiles.
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public function get_profiles() {

		return ! empty( $this->get_data()->profiles ) ? $this->get_data()->profiles : array();
	}


	/**
	 * Gets the response data.
	 *
	 * @since 2.0.0
	 *
	 * @return \stdClass
	 */
	public function get_data() {

		$data = ! empty( $this->response_data->data ) && is_array( $this->response_data->data ) ? $this->response_data->data : array();

		return is_object( $data[0] ) ? $data[0] : new \stdClass();
	}


}
