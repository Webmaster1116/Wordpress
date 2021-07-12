<?php

namespace CreativeMail\Modules\Contacts\Managers;

use CreativeMail\Modules\Contacts\Processors\ContactsSyncBackgroundProcessor;

class ContactsSyncManager
{
    private $contacts_sync_background_processor;

    public function __construct()
    {
        $this->contacts_sync_background_processor = new ContactsSyncBackgroundProcessor();

        add_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'publish_contact_sync_request'));
    }

    public function __destruct()
    {
        remove_action(CE4WP_SYNCHRONIZE_ACTION, array($this, 'publish_contact_sync_request'));
    }

    public function publish_contact_sync_request()
    {
        $this->contacts_sync_background_processor->push_to_queue(null);

        // Start the queue.
        $this->contacts_sync_background_processor->save()->dispatch();
    }
}