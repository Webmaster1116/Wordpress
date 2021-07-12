<?php
/**
 * Contains JS script to register a transaction up on order received page.
 *
 * @package Kliken Marketing for Google
 */

defined( 'ABSPATH' ) || exit;

?>

<script type="text/javascript">
	var swPreRegister = function() {
		sw.gawCurrency = "<?php echo esc_attr( $trans['currency'] ); ?>";

		var trans = sw.create_transaction(
			"<?php echo esc_attr( $trans['order_id'] ); ?>",
			"<?php echo esc_attr( $trans['affiliate'] ); ?>",
			"<?php echo esc_attr( $trans['sub_total'] ); ?>",
			"<?php echo esc_attr( $trans['tax'] ); ?>",
			"<?php echo esc_attr( $trans['city'] ); ?>",
			"<?php echo esc_attr( $trans['state'] ); ?>",
			"<?php echo esc_attr( $trans['country'] ); ?>",
			"<?php echo esc_attr( $trans['total'] ); ?>",
		);

		<?php foreach ( $trans['items'] as $index => $item ) : ?>
			trans.add_item(
				"<?php echo esc_attr( $item['id'] ); ?>",
				"<?php echo esc_attr( $item['name'] ); ?>",
				"<?php echo esc_attr( $item['category'] ); ?>",
				"<?php echo esc_attr( $item['price'] ); ?>",
				"<?php echo esc_attr( $item['quantity'] ); ?>",
			);
		<?php endforeach; ?>

		sw.hit.set_page("ORDER_CONFIRMATION");
	};
</script>
