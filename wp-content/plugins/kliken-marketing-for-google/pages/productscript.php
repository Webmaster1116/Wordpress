<?php
/**
 * Contains JS script to register a product page.
 *
 * @package Kliken Marketing for Google
 */

defined( 'ABSPATH' ) || exit;

?>

<script type="text/javascript">
	var swPreRegister = function() {
		sw.track('ViewContent',
			{
				content_type: 'product',
				content_ids: "<?php echo esc_attr( $product['id'] ); ?>",
				content_name: "<?php echo esc_attr( $product['name'] ); ?>",
				content_category: "<?php echo esc_attr( implode( ',', $product['category'] ) ); ?>"
			}
		);

		sw.gEvent('view_item',
			{
				items: [
					{
						"id": "<?php echo esc_attr( $product['id'] ); ?>",
						"name": "<?php echo esc_attr( $product['name'] ); ?>",
						"category": "<?php echo esc_attr( implode( ',', $product['category'] ) ); ?>",
						"google_business_vertical": "retail"
					}
				]
			}
		);

		sw.gEvent('page_view',
			{
				"ecomm_prodid": "<?php echo esc_attr( $product['id'] ); ?>"
			}
		);

		sw.register_product_view(
			{
				"id": "<?php echo esc_attr( $product['id'] ); ?>",
				"category": "<?php echo esc_attr( implode( ',', $product['category'] ) ); ?>"
			}
		);
	};
</script>
