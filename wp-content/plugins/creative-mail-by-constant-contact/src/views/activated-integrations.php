<?php

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;

$available_integrations = CreativeMail::get_instance()->get_integration_manager()->get_active_plugins();
$activated_integrations = CreativeMail::get_instance()->get_integration_manager()->get_activated_integrations();

?>

<script type="application/javascript">
  function showConsentModal () {
    var form = document.getElementById("activated_plugins_form");
    var checkboxes = form.querySelectorAll("input[type='checkbox']:checked");
    if (checkboxes.length > 0) {
        document.getElementById('consent-modal').style.display = "block";
    } else {
        submitForm();
    }
  }

  function closeConsentModal () {
    document.getElementById('consent-modal').style.display = "none";
  }

  function submitForm() {
    document.getElementById('consent-modal-activated-loader').classList.remove("ce4wp-hidden");
    document.getElementById('consent-modal-activated-content').style.display = "none";
    document.getElementById('activated_plugins_form').submit()
  }

  function onChecked(slug){
      var card = document.getElementById('activated-plugins-' + slug);
      if(card !== undefined && card !== null) {
          card.classList.toggle("ce4wp-selected")
      }
  }
</script>

<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
  <?= __( 'Select one or more plugins to enable the synchronization of its contacts with Creative Mail.', 'ce4wp') ?>
</p>
<br />
<form id="activated_plugins_form" name="plugins" action="" method="post">
  <input type="hidden" name="action" value="change_activated_plugins" />
  <div style="color: rgba(0, 0, 0, 0.6);" class="ce4wp-grid">
        <?php
        foreach ($available_integrations as $available_integration) {
            $active = in_array($available_integration, $activated_integrations);
            $checked = $active === true ? 'checked' : '';
            $path = '/assets/p/universal/wordpress-plugin/'.$available_integration->get_slug() .'.png';
            $plugin_image = EnvironmentHelper::get_app_url().$path;

            echo '<div class="ce4wp-grid-item">
        <div id="activated-plugins-' . esc_attr($available_integration->get_slug()) .'" class="ce4wp-settings-card" >
            <label for="activated-plugins-check-' . esc_attr($available_integration->get_slug()) .'">
                <div class="ce4wp-grid">
                    <div class="ce4wp-grid-item ce4wp-grid-xs-2">
                        <div class="ce4wp-settings-card-image" style="background-image: url(' . esc_attr($plugin_image) . ')" title="' . esc_attr($available_integration->get_slug()) . '"></div>
                    </div>
                    <div class="ce4wp-grid-item ce4wp-grid-xs-8">
                            <span class="ce4wp-settings-card-title">' . esc_html($available_integration->get_name()) . '</span>
                    </div>
                    <div class="ce4wp-grid-item ce4wp-grid-xs-2"  style="line-height: 48px;">
                    <label class="ce4wp-checkbox">
                        <input onclick="onChecked(&quot;' . esc_attr($available_integration->get_slug()) .'&quot;)" type="checkbox" name="activated_plugins[]" id="activated-plugins-check-' . esc_attr($available_integration->get_slug()) .'" value="' . esc_attr($available_integration->get_slug()) . '" ' . esc_attr($checked) . ' />
                        <span></span>
                    </label>
                    </div>
                </div>
            </label>
        </div>
    </div>';
        }
        ?>
    </div>
    <div class="ce-kvp">
        <br />
    <input name="save_button" type="submit" class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary ce4wp-mt-2" id="save-activated-plugins" value="Save" onclick="showConsentModal(); return false;" />
    <!--  -->
  </div>

  <!-- Consent modal -->
  <div id="consent-modal" role="presentation" class="ce4wp-dialog-root" height="auto" variant="default" style="display: none;">
    <div class="ce4wp-backdrop-root" aria-hidden="true" style="opacity: 1; "></div>

    <div class="ce4wp-dialog-container" role="none presentation" tabindex="-1"
      style="opacity: 1; ">

      <div class="ce4wp-dialog-wrapper" role="dialog">
        <div width="100%" class="ce4wp-dialog-header">
          <div class="ce4wp-dialog-header-title">
            <div class="ce4wp-dialog-header-title-wrapper">
              <div class="ce4wp-dialog-header-title-wrapper-content">
                <h3 class="ce4wp-typography-root ce4wp-typography-h3"><?= __( 'Yes, these contacts expect to hear from me', 'ce4wp') ?></h3>
              </div>
            </div>
          </div>
          <div class="ce4wp-dialog-header-close">
            <div class="ce4wp-dialog-header-close-wrapper" onclick="closeConsentModal()">
              <div class="ce4wp-dialog-header-close-wrapper-button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
                  <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
        <div  id='consent-modal-activated-loader' height="auto" class="ce4wp-dialog-content  ce4wp-hidden">
          <div class="ce4wp-loader" role="progressbar" style="width: 40px; height: 40px;">
              <svg class="core-test-MuiCircularProgress-svg" viewBox="22 22 44 44">
                  <circle class="core-test-MuiCircularProgress-circle core-test-MuiCircularProgress-circleIndeterminate" cx="44" cy="44" r="20.2" fill="none" stroke-width="3.6">
                  </circle>
              </svg>
          </div>
        </div>
        <div id='consent-modal-activated-content'>
          <div height="auto" class="ce4wp-dialog-content">
              <div>
                  <div class="ce4wp-pb-3">
                      <span><?= __( 'Each time you add contacts, they must meet the following conditions.', 'ce4wp') ?></span>
                  </div>
                  <div class="ce4wp-consent">
                      <div class="ce4wp-pb-3">
                      <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __('I have the consent of each contact on my list', 'ce4wp') ?></h4>
                      <span><?= __( 'You must have the prior consent of each contact added to your Constant Contact account. Your account cannot contain purchased, rented, third party or appended lists. In addition, you may not add auto-response addresses, transactional addresses, or user group addresses.', 'ce4wp') ?></span>
                      </div>
                      <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __('I am not adding role addresses or distribution lists', 'ce4wp') ?></h4>
                      <span><?= __( 'Role addresses, such as sales@ or marketing@, and distribution lists often mail to more than one person and result in higher than normal spam complaints. You must remove these from your list prior to upload.', 'ce4wp') ?></span>
                  </div>
                  <div class="ce4wp-pb-3">
                      <span><?= __('Getting your email delivered is important to us. We may contact you to review your list before we send your email, if you add contacts that are likely to cause higher than normal bounces or for other reasons that we know may cause spam complaints. Thanks for helping to eliminate spam.', 'ce4wp') ?></span>
                  </div>
              </div>
          </div>
          <div class="ce4wp-dialog-footer">
            <div class="ce4wp-dialog-footer-close">
              <div class="ce4wp-dialog-footer-close-wrapper">
                <button class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary" type="button" onclick="submitForm()" >
                  <span class="MuiButton-label"><?= __( 'Got it!', 'ce4wp') ?></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

