<?php
/**
 * Contains JS script to register a cart page.
 *
 * @package Kliken Marketing for Google
 */

defined( 'ABSPATH' ) || exit;

?>

<script type="text/javascript">
	var swPreRegister = function() {
		let itemsForGTAG = [];
		let swCart = [];
		<?php foreach ($cart as $key => $value) : ?>
			<?php $product = $value['data']; ?>
			sw.track("AddToCart",
				{
					content_type: "product",
					content_ids: "<?php echo esc_attr( $product->get_id() ); ?>",
					content_name: "<?php echo esc_attr( $product->get_name() ); ?>",
					value: "<?php echo esc_attr( $product->get_price() ); ?>",
					currency: sw.config.currency
				}
			);

			itemsForGTAG.push(
				{
					"id": "<?php echo esc_attr( $product->get_id() ); ?>",
					"name": "<?php echo esc_attr( $product->get_name() ); ?>",
					"price": "<?php echo esc_attr( $product->get_price() ); ?>",
					"quantity": "<?php echo esc_attr( $value['quantity'] ); ?>",
					"google_business_vertical": "retail"
				}
			);

			swCart.push(
				{
					"id": "<?php echo esc_attr( $product->get_id() ); ?>",
					"name": "<?php echo esc_attr( $product->get_name() ); ?>",
					"price": "<?php echo esc_attr( $product->get_price() ); ?>",
					"quantity": "<?php echo esc_attr( $value['quantity'] ); ?>",
					"currency": sw.config.currency
				}
			);
		<?php endforeach ?>

		sw.gEvent("add_to_cart",
			{
				"items": itemsForGTAG
			}
		);

		sw.register_shopcart(
			{
				"items": swCart
			}
		);
	};
</script>
