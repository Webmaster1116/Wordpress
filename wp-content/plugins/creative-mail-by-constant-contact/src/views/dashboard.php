<div class="ce4wp-admin-wrapper">
    <header class="ce4wp-swoosh-header"></header>

    <div class="ce4wp-swoosh-container">
    <div style="margin-top: 0px;">
      <div class="ce4wp-backdrop">
        <div class="ce4wp-backdrop-container">
          <div class="ce4wp-backdrop-header">
            <div class="ce4wp-logo-poppins"></div>
            <div>
              <img src="<?php echo CE4WP_PLUGIN_URL . 'assets/images/airplane.svg'; ?>" class="ce4wp-airplane" alt="Paper airplane decoration">
            </div>
          </div>

          <div class="ce4wp-card">
            <div class="ce4wp-px-4 ce4wp-pt-4">
              <h1 class="ce4wp-typography-root ce4wp-typography-h1 ce4wp-inline-block ce4wp-mb-3">
                <?= __( 'Intelligent email marketing for', 'ce4wp'); ?><br><?= __( 'WordPress and WooCommerce', 'ce4wp'); ?>
              </h1>
              <p class="ce4wp-typography-root ce4wp-subtitle ce4wp-mt-4 ce4wp-mb-4">
                <?= __( 'You’re all set! Creative Mail and WordPress have been linked.', 'ce4wp'); ?>
              </p>
              <div id="ce4wploaded">
                <a id='ce4wp-go-button' onclick="ce4wpNavigateToDashboard(this, 'd25f690a-217a-4d68-9c58-8693965d4673', { source: 'ce4wp_dashboard' }, ce4wpDashboardStartCallback, ce4wpDashboardFinishCallback)" class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary ce4wp-mt-2" tabindex="0" type="button" data-element-type="button">
                  <span class="ce4wp-button-label ce4wp-hide-on-mobile" style="width: 100%;"><?= __( 'Open your Creative Mail dashboard', 'ce4wp'); ?><span class="ce4wp-button-endIcon">
                    <svg class="ce4wp-Svgicon-root" focusable="false" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"></path>
                    </svg>
                    </span>
                  </span>
                    <span class="ce4wp-button-label ce4wp-show-on-mobile" style="width: 100%;"><?= __( 'Open Creative Mail dashboard', 'ce4wp'); ?>
                  </span>
                </a>
                <h6 id='ce4wp-sub-apps-title' class="ce4wp-typography-root ce4wp-typography-h6 ce4wp-mt-4 ce4wp-mb-3">
                <?= __( 'Or jump straight into:', 'ce4wp'); ?>
                </h6>
                <div id='ce4wp-sub-apps-container' class="ce4wp-grid ce4wp-mt-3">
                  <div class="ce4wp-grid-item" onclick="ce4wpNavigateToDashboard(this, '1fabdbe2-95ed-4e1e-a2f3-ba0278f5096f', { source: 'ce4wp_dashboard' }, ce4wpDashboardStartCallback, ce4wpDashboardFinishCallback)">
                    <div class="ce4wp-grid-item-card ce4wp-mb-4">
                      <div class="ce4wp-grid-item-card-media" title="WooCommerce emails" style="background-image: url(<?php echo CE4WP_PLUGIN_URL . 'assets/images/tile-img-woocommerce.svg'; ?>);"></div>
                      <div class="ce4wp-grid-item-card-content-root">
                        <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __( 'WooCommerce emails', 'ce4wp'); ?></h4>
                        <p class="ce4wp-pt-2 ce4wp-typography-root ce4wp-body2"><?= __( 'Spice up your transactional WooCommerce store emails.', 'ce4wp'); ?></p>
                      </div>
                    </div>
                  </div>
                  <div class="ce4wp-grid-item" onclick="ce4wpNavigateToDashboard(this, 'd5baea05-c603-4cca-852e-f8e82414f6b0', { source: 'ce4wp_dashboard' }, ce4wpDashboardStartCallback, ce4wpDashboardFinishCallback)">
                    <div class="ce4wp-grid-item-card ce4wp-mb-4">
                      <div class="ce4wp-grid-item-card-media" title="Email Automations" style="background-image: url(<?php echo CE4WP_PLUGIN_URL . 'assets/images/tile-img-automations.svg'; ?>);"></div>
                      <div class="ce4wp-grid-item-card-content-root">
                        <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __( 'Email Automations', 'ce4wp'); ?></h4>
                        <p class="ce4wp-pt-2 ce4wp-typography-root ce4wp-body2"><?= __( 'Engage your audience without lifting a finger.', 'ce4wp'); ?></p>
                      </div>
                    </div>
                  </div>
                  <div class="ce4wp-grid-item" onclick="ce4wpNavigateToDashboard(this, '836b20fc-9ff1-41b2-912b-a8646caf05a4', { source: 'ce4wp_dashboard' }, ce4wpDashboardStartCallback, ce4wpDashboardFinishCallback)">
                    <div class="ce4wp-grid-item-card ce4wp-mb-4">
                      <div class="ce4wp-grid-item-card-media" title="Contact Management" style="background-image: url(<?php echo CE4WP_PLUGIN_URL . 'assets/images/tile-img-contactmanagement.svg'; ?>);"></div>
                      <div class="ce4wp-grid-item-card-content-root">
                        <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __( 'Contact Management', 'ce4wp'); ?></h4>
                        <p class="ce4wp-pt-2 ce4wp-typography-root ce4wp-body2"><?= __( 'Manage your contacts and email lists, all in one place.', 'ce4wp'); ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div id="ce4wpskeleton" style="display: none;">
                <div class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-mt-2 skeleton-pulse" style="width: 300px; color: #8C8C8C;">
                  <span class="ce4wp-button-label" style="width: 100%;"><?= __( 'Loading your account...', 'ce4wp'); ?></span>
                </div>
                <div class="ce4wp-typography-root ce4wp-typography-h6 ce4wp-mt-4 ce4wp-mb-3 skeleton-pulse ce4wp-subapps-skeleton"></div>
                <div class="ce4wp-grid ce4wp-mt-3">
                  <div class="ce4wp-grid-item">
                    <div class="ce4wp-grid-item-card ce4wp-mb-4">
                      <div class="ce4wp-grid-item-card-media skeleton-pulse ce4wp-grid-item-card-media-skeleton"></div>
                      <div class="ce4wp-grid-item-card-content-root skeleton-pulse">
                        <div class="ce4wp-grid-item-card-content-skeleton-title"></div>
                        <div class="ce4wp-grid-item-card-content-skeleton-description"></div>
                      </div>
                    </div>
                  </div>
                  <div class="ce4wp-grid-item">
                    <div class="ce4wp-grid-item-card ce4wp-mb-4">
                      <div class="ce4wp-grid-item-card-media skeleton-pulse ce4wp-grid-item-card-media-skeleton"></div>
                      <div class="ce4wp-grid-item-card-content-root skeleton-pulse">
                        <div class="ce4wp-grid-item-card-content-skeleton-title"></div>
                        <div class="ce4wp-grid-item-card-content-skeleton-description"></div>
                      </div>
                    </div>
                  </div>
                  <div class="ce4wp-grid-item">
                    <div class="ce4wp-grid-item-card ce4wp-mb-4">
                      <div class="ce4wp-grid-item-card-media skeleton-pulse ce4wp-grid-item-card-media-skeleton"></div>
                      <div class="ce4wp-grid-item-card-content-root skeleton-pulse">
                        <div class="ce4wp-grid-item-card-content-skeleton-title"></div>
                        <div class="ce4wp-grid-item-card-content-skeleton-description"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
