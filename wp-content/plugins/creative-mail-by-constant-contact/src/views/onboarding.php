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
                            <h1 class="ce4wp-typography-root ce4wp-typography-h1">
                                <?=  __( 'Intelligent email marketing for', 'ce4wp') ?><br /><?=  __( 'WordPress and WooCommerce', 'ce4wp') ?>
                            </h1>
                            <h6 class="ce4wp-typography-root ce4wp-typography-h6 ce4wp-mt-4 ce4wp-mb-2">
                                <?=  __('With Creative Mail your blog posts, store promotions, announcements, event updates and more can be delivered straight into the inbox of your customers and prospects.', 'ce4wp') ?>
                            </h6>
                            <h6 class="ce4wp-typography-root ce4wp-typography-h6 ce4wp-mt-4 ce4wp-mb-2">
                                <?=  __('The fine print:', 'ce4wp') ?>
                            </h6>
                            <ul class="ce4wp-list-root pb-4 ce4wp-list-padding">
                                <li class="ce4wp-list-item-root" style="max-width: 550px;">
                                    <div class="ce4wp-list-item-icon-root ce4wp-pr-3 ce4wp-mt-2">
                                        <svg class="ce4wp-svg-icon-root ce4wp-svg-icon-color" focusable="false" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4.75 8.12891L12.6953 0.183594L13.75 1.23828L4.75 10.2383L0.566406 6.05469L1.62109 5L4.75 8.12891Z" fill="#7A5CBD"/>
                                        </svg>
                                    </div>
                                    <div class="ce4wp-list-item-text-root ce4wp-d-flex ce4wp-flex-column ce4wp-m-0">
                                        <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
                                            <?=  __('By using Creative Mail you’ll share basic information about your site (including your site name and URL) with Constant Contact so that we can retrieve your blog posts, media files and store products for use in your emails;', 'ce4wp') ?>
                                        </p>
                                    </div>
                                </li>
                                <li class="ce4wp-list-item-root" style="max-width: 550px;">
                                    <div class="ce4wp-list-item-icon-root ce4wp-pr-3 ce4wp-mt-2">
                                        <svg class="ce4wp-svg-icon-root ce4wp-svg-icon-color" focusable="false" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4.75 8.12891L12.6953 0.183594L13.75 1.23828L4.75 10.2383L0.566406 6.05469L1.62109 5L4.75 8.12891Z" fill="#7A5CBD"/>
                                        </svg>
                                    </div>
                                    <div class="ce4wp-list-item-text-root ce4wp-d-flex ce4wp-flex-column ce4wp-m-0">
                                        <p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
                                             <?=  __('Creative Mail uses tools, including cookies, to improve the performance and experience of the product. For more information you can read our', 'ce4wp') ?> <a href="https://www.endurance.com/privacy/privacy" target="_blank"><?=  __('privacy notice', 'ce4wp') ?></a>.
                                        </p>
                                    </div>
                                </li>
                            </ul>
                            <a id='ce4wp-go-button'
                               target="_blank"
                               class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary ce4wp-mb-4 ce4wp-mt-2"
                               tabindex="0"
                               type="button"
                               data-element-type="button"
                               onclick="ce4wpNavigateToDashboard(this, undefined, { source: 'onboarding' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
                                <span class="ce4wp-button-label" style="width: 100%;">
                                    <?=  __('I Agree and let\'s get started!', 'ce4wp') ?><span class="ce4wp-button-endIcon">
                                    <svg class="ce4wp-Svgicon-root" focusable="false" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"></path>
                                    </svg>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">
    let blurred = false;
    window.onblur = function() {
        blurred = true;
        document.getElementById('ce4wp-go-button').style.display = "none";
    };
    window.onfocus = function() { blurred && (location.reload()); };
</script>
