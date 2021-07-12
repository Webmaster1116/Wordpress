<?php
/**
 * REST controller for getting products, extending of what WooCommerce
 * does not provide at the moment.
 *
 * @package Kliken Marketing for Google
 */

namespace Kliken\WcPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * REST API Products controller class.
 *
 * @extends WC_REST_Products_Controller_Compat
 */
class REST_Products_Controller extends WC_REST_Products_Controller_Compat {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-kliken/v1';

	/**
	 * Register the routes for products.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/products/modified', [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace, '/products/deleted', [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_deleted_products' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Get deleted/trashed products.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_deleted_products( $request ) {
		$page     = intval( $request['page'] );
		$per_page = intval( $request['per_page'] );

		$query_args = [
			'fields'              => 'ids', // We only need post ids for this.
			'post_type'           => $this->post_type,
			'post_status'         => 'trash',
			'ignore_sticky_posts' => true,
			'paged'               => 0 === $page ? 1 : $page,
			'posts_per_page'      => 0 === $per_page ? 100 : $per_page,
			'orderby'             => 'modified',
			'order'               => 'ASC',
		];

		$query_args['date_query'] = [];
		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$query_args['date_query'][0]['column'] = 'post_modified';
			$query_args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$query_args['date_query'][0]['column'] = 'post_modified';
			$query_args['date_query'][0]['after']  = $request['after'];
		}

		$query  = new \WP_Query();
		$result = $query->query( $query_args );

		$total_posts = $query->found_posts;
		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );
			$count_query = new \WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$response = rest_ensure_response( $result );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) ceil( $total_posts / (int) $query_args['posts_per_page'] ) );

		return $response;
	}

	/**
	 * Prepare objects query to get modified products.
	 *
	 * @since  3.0.0
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		// To fix some notice messages in header of REST response
		if ( empty( $request['page'] ) ) {
			$request['page'] = 1;
		}

		if ( empty( $request['orderby'] ) ) {
			$request['orderby'] = 'modified';
		}

		$args = parent::prepare_objects_query( $request );

		// Reset the date query to look up post_modified column instead.
		if ( ! empty( $args['date_query'] ) ) {
			$args['date_query'][0]['column'] = 'post_modified';
		}

		return $args;
	}
}
