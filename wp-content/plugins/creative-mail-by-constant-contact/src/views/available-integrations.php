<?php

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;

$supported_integrations = CreativeMail::get_instance()->get_integration_manager()->get_supported_integrations(true);
$active_plugin_count = count(CreativeMail::get_instance()->get_integration_manager()->get_active_plugins());
$title = $active_plugin_count == 0 ? 'We support contact synchronization from the following plugins:' : 'Plugins we also support';
$title_class = $active_plugin_count == 0 ? 'ce4wp-body2' : 'ce4wp-plugin-support-title';

?>
<script type="application/javascript">
    function showPluginModal(name, url) {
        if (url === null && url === undefined)
            return
        // Check if url is relative
        if (url[0] === '/') {
            document.getElementById('plugin-store-title').textContent = name;
            document.getElementById('plugin-store-iframe').src = url;
            document.getElementById('plugin-store-iframe').title = name;
            document.getElementById('plugin-store-modal').style.display = "block";
        } else {
            var win = window.open(url, '_blank');
            win.focus();
        }
    }

    function closePluginModal() {
        document.getElementById('plugin-store-modal').style.display = "none";
    }
</script>
<span class="ce4wp-typography-root <?php echo $title_class ?>" style="margin: 40px 0 20px 0; display: block">
    <?= __($title, 'ce4wp') ?>
</span>
<div style="color: rgba(0, 0, 0, 0.6);" class="ce4wp-grid">
    <?php
    foreach ($supported_integrations as $supported_integration) {
        if ($supported_integration->is_hidden_from_suggestions()) {
            continue;
        }
        $path = '/assets/p/universal/wordpress-plugin/' . $supported_integration->get_slug() . '.png';
        $plugin_image = EnvironmentHelper::get_app_url() . $path;
        echo '<div class="ce4wp-grid-item">
        <div class="ce4wp-settings-card" onclick="showPluginModal(&quot;' . esc_html($supported_integration->get_name()) . '&quot;,&quot;' . esc_attr($supported_integration->get_url()) . '&quot;)">
            <div class="ce4wp-grid">
                <div class="ce4wp-grid-item ce4wp-grid-xs-2">
                    <div class="ce4wp-settings-card-image" style="background-image: url(' . esc_attr($plugin_image) . ')" title="' . esc_attr($supported_integration->get_slug()) . '"></div>
                </div>
                <div class="ce4wp-grid-item ce4wp-grid-xs-8">
                   <span class="ce4wp-settings-card-title">' . esc_html($supported_integration->get_name()) . '</span>
                </div>
                <div class="ce4wp-grid-item ce4wp-grid-xs-2">
                    <div class="ce4wp-settings-card-link ce4wp-settings-card-image" title="' . esc_attr($supported_integration->get_slug()) . '"></div>
                </div>
            </div>
        </div>
    </div>
';
    }
    ?>
</div>

<!-- plugin store modal -->
<div id="plugin-store-modal" role="presentation" class="ce4wp-dialog-root" height="auto" variant="default"
     style="display: none;">
    <div class="ce4wp-backdrop-root" aria-hidden="true" style="opacity: 1; "></div>

    <div class="ce4wp-dialog-container" role="none presentation" tabindex="-1"
         style="opacity: 1; ">

        <div class="ce4wp-dialog-wrapper" style="max-width: 1200px" role="dialog">
            <div width="100%" class="ce4wp-dialog-header">
                <div class="ce4wp-dialog-header-title">
                    <div class="ce4wp-dialog-header-title-wrapper">
                        <div class="ce4wp-dialog-header-title-wrapper-content">
                            <h3 class="ce4wp-typography-root ce4wp-typography-h3" id="plugin-store-title"></h3>
                        </div>
                    </div>
                </div>
                <div class="ce4wp-dialog-header-close">
                    <div class="ce4wp-dialog-header-close-wrapper" onclick="closePluginModal()">
                        <div class="ce4wp-dialog-header-close-wrapper-button">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="black"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div height="auto" class="ce4wp-dialog-content">
                <iframe frameborder="0"
                        hspace="0"
                        allowtransparency="true"
                        src=""
                        id="plugin-store-iframe"
                        name="store-iframe"
                        style="width: 100%; height: calc(100vh - 400px)"
                        title="">This feature requires inline frames. You have iframes disabled or your browser does not
                    support them.
                </iframe>
            </div>
        </div>
    </div>
</div>

