<style>
.ce4wp-woocommerce {
    margin: 0 -12px -12px;
}

.ce4wp-woocommerce__item {
    display: flex;
    justify-content: space-between;
    background-color: #fafafa;
    padding: 0 12px;
    border-top: 1px solid #ddd;
}

.ce4wp-woocommerce__item p {
    margin: 0.5em 0;
}
</style>

<h3><?= __( 'Transactional WooCommerce email', 'ce4wp' ); ?></h3>
<section class="ce4wp-woocommerce">
    <div class="ce4wp-woocommerce__item">
        <p><?= __( 'Active', 'ce4wp' ); ?>:</p>
        <p>
            <strong><?= $number_of_active_notifications; ?></strong>
        </p>
    </div>
    <div class="ce4wp-woocommerce__item">
        <p><?= __( 'Inactive', 'ce4wp' ); ?>:</p>
        <p>
            <strong><?= $number_of_possible_notifications - $number_of_active_notifications; ?></strong>
        </p>
    </div>
</section>
