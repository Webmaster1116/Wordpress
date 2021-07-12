<?php
use CreativeMail\Helpers\EnvironmentHelper;
?>

<div class="ce4wp-card">
    <div class="ce4wp-px-4 ce4wp-py-4">
        <h2 class="ce4wp-typography-root ce4wp-typography-h2 ce4wp-mb-2"><?= __( 'Technical details', 'ce4wp' ); ?></h2>

        <div class="ce4wp-kvp">
            <h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2"><?= __( 'Instance UUID', 'ce4wp' ); ?></h4>
            <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);"><?php echo esc_html($this->instance_uuid) ?></p>
        </div>

        <div class="ce4wp-kvp">
            <h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2"><?= __( 'Instance Id', 'ce4wp' ); ?></h4>
            <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);"><?php echo esc_html($this->instance_id) ?></p>
        </div>

        <div class="ce4wp-kvp">
            <h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2"><?= __( 'Environment', 'ce4wp' ); ?></h4>
            <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);"><?php echo esc_html(EnvironmentHelper::get_environment()) ?></p>
        </div>

        <div class="ce4wp-kvp">
            <h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2"><?= __( 'Plugin version', 'ce4wp' ); ?></h4>
            <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);"><?php echo esc_html(CE4WP_PLUGIN_VERSION) . '.' . esc_html(CE4WP_BUILD_NUMBER) ?></p>
        </div>

        <div class="ce4wp-kvp">
            <h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2"><?= __( 'App', 'ce4wp' ); ?></h4>
            <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);"><?php echo esc_js(EnvironmentHelper::get_app_url()) ?></p>
        </div>

        <div class="ce4wp-kvp">
            <h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2"><?= __( 'App Gateway', 'ce4wp' ); ?></h4>
            <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);"><?php echo esc_js(EnvironmentHelper::get_app_gateway_url()) ?></p>
        </div>
    </div>
</div>
