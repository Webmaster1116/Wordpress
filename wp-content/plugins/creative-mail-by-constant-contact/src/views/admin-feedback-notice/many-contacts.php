<div id="ce4wp-admin-feedback-notice" class="notice notice-warning" hidden>
    <img class="icon" src="<?= CE4WP_PLUGIN_URL . 'assets/images/airplane-purple.svg'; ?>" />
    <section class="content">
        <p>
            <strong><?= __( 'Awesome... your audience is growing!', 'ce4wp' ); ?></strong>
        </p>
        <p>
            <?= __( 'Your', 'ce4wp' ); ?>
            <strong><?= $ce_number_of_contacts; ?></strong>
            <?= __( 'contacts are ready for a Creative Mail email campaign. Send one now!', 'ce4wp' ); ?>
        </p>
    </section>
    <button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, 'd25f690a-217a-4d68-9c58-8693965d4673', { source: 'feedback_notice' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)"><?= __( 'Get started', 'ce4wp' ); ?></button>
    <span id="close" onclick="hideAdminFeedbackNotice('feedback_notice_many_contacts')"></span>
</div>
