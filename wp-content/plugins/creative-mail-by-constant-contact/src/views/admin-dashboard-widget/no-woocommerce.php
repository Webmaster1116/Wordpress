<div style="display: flex;">
    <section style="flex: 1;">
        <p style="margin-top: 0;">
            <?= __( 'Easily manage and brand all of your important transactional WooCommerce store emails.', 'ce4wp' ); ?>
        </p>
        <button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, '1fabdbe2-95ed-4e1e-a2f3-ba0278f5096f', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
            <?= __( "Let's go!", 'ce4wp' ); ?>
        </button>
    </section>
    <img
        src="<?= CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/no-woocommerce.svg'; ?>"
        style="height: 8em; margin-left: 1em;"
    />
</div>
