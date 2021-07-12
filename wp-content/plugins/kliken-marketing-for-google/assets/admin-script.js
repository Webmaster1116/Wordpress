jQuery(document).ready(function() {
    // Only care about those notice messages with "data-message-name" attribute, and "is-dimissible" class
    jQuery(".wrap").on("click", "div[data-message-name].is-dismissible button.notice-dismiss", function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (confirm(dimissibleNotice.confirmMessage)) {
            jQuery.post( ajaxurl, {
                action: dimissibleNotice.action,
                message_name: jQuery(this).parent().attr('data-message-name'),
                _ajax_nonce: dimissibleNotice.nonce
            })
        }
    });
});
