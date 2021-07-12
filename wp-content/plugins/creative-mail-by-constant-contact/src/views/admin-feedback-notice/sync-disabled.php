<div id="ce4wp-admin-feedback-notice" class="notice notice-warning" hidden>
    <span class="icon dashicons dashicons-groups"></span>
    <section class="content">
        <p>
            <strong>
                <?= __( 'Should we sync your contacts with', 'ce4wp' ); ?>
                <img class="ce-logo" src="<?= CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/logo.svg'; ?>" />
                ?
            </strong>
        </p>
        <p><?= __( 'Grow your business or blog with the power of email marketing.', 'ce4wp' ); ?></p>
    </section>
    <a href="<?= esc_url( admin_url( 'admin.php?page=creativemail_settings' ) ); ?>">
        <button class="button button-primary"><?= __( 'Sync my contacts', 'ce4wp' ); ?></button>
    </a>
    <span id="close" onclick="hideAdminFeedbackNotice('feedback_notice_sync_disabled')"></span>
</div>
