<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API\Orders;

defined( 'ABSPATH' ) or exit;

/**
 * Orders API order handler.
 *
 * @since 2.1.0
 */
class Order {


	/** @var string API state meaning Facebook is still processing the order and no action is possible */
	const STATUS_PROCESSING = 'FB_PROCESSING';

	/** @var string API state meaning Facebook has processed the orders and the seller needs to acknowledge it */
	const STATUS_CREATED = 'CREATED';

	/** @var string API state meaning the order was acknowledged and is now being processed in WC */
	const STATUS_IN_PROGRESS = 'IN_PROGRESS';

	/** @var string API state meaning all items in the order are shipped and/or cancelled */
	const STATUS_COMPLETED = 'COMPLETED';


	/** @var array order data */
	protected $data;


	/**
	 * Orders API order handler constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param array $response_data response data from the API
	 */
	public function __construct( $response_data ) {

		$this->data = $response_data;
	}


	/**
	 * Gets the order’s ID.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_id() {

		return ! empty( $this->data['id'] ) ? $this->data['id'] : '';
	}


	/**
	 * Gets the order’s status.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_status() {

		return ! empty( $this->data['order_status'] ) && ! empty( $this->data['order_status']['state'] ) ? $this->data['order_status']['state'] : '';
	}


	/**
	 * Gets the items' data.
	 *
	 * @see https://developers.facebook.com/docs/commerce-platform/order-management/order-api#item
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_items() {

		return ! empty( $this->data['items']['data'] ) ? $this->data['items']['data'] : array();
	}


	/**
	 * Gets the channel name.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_channel() {

		return ! empty( $this->data['channel'] ) ? $this->data['channel'] : '';
	}


	/**
	 * Gets the shipping details.
	 *
	 * @see https://developers.facebook.com/docs/commerce-platform/order-management/order-api#selected_shipping_option
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_selected_shipping_option() {

		return isset( $this->data['selected_shipping_option'] ) ? $this->data['selected_shipping_option'] : array();
	}


	/**
	 * Gets the shipping address.
	 *
	 * @see https://developers.facebook.com/docs/commerce-platform/order-management/order-api#shipping_address
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_shipping_address() {

		return isset( $this->data['shipping_address'] ) ? $this->data['shipping_address'] : array();
	}


	/**
	 * Gets the payment details.
	 *
	 * @see https://developers.facebook.com/docs/commerce-platform/order-management/order-api#estimated_payment_details
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_estimated_payment_details() {

		return isset( $this->data['estimated_payment_details'] ) ? $this->data['estimated_payment_details'] : array();
	}


	/**
	 * Gets the buyer details.
	 *
	 * @see https://developers.facebook.com/docs/commerce-platform/order-management/order-api#buyer_details
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_buyer_details() {

		return isset( $this->data['buyer_details'] ) ? $this->data['buyer_details'] : array();
	}


}
