<?php
namespace CreativeMail\Modules\Woocommerce\Emails;

use WC_Email;
use WC_Order;

class AbandonedCartEmail extends WC_Email {
    /**
     * Constructor.
     *
     * @param WC_Email[] $email_classes All existing instances of WooCommerce emails.
     */
    public function __construct( $email_classes = array() ) {
        $this->id             = 'cart_abandoned_ce4wp';
        $this->title          = __( 'Abandoned cart', 'ce4wp' );
        $this->description    = __( 'Send customers a reminder after they abandoned their shopping cart', 'ce4wp' );
        $this->customer_email = true;
        $this->enabled = false;

        // We want all the parent's methods, with none of its properties, so call its parent's constructor, rather than my parent constructor.
        parent::__construct();
    }

    /**
     * Triggers the email.
     *
     * @param WC_Order $order The order.
     */
    public function trigger( $order ) {
        return;
    }
}
