<div style="display: flex;">
    <section style="flex: 1;">
        <p style="margin-top: 0;">
            <?= __( 'Thanks for signing up with Creative Mail. Let’s create your first campaign!', 'ce4wp' ); ?>
        </p>
        <button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, '93b1417d-2efb-406d-a9a6-aa8af8f813a3', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)"><?= __( 'Create a campaign', 'ce4wp' ); ?></button>
    </section>
    <img
        src="<?= CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/airplane.svg'; ?>"
        style="margin-top: -11px; margin-right: -12px; height: 8em;"
    />
</div>
