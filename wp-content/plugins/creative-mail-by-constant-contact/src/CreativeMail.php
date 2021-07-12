<?php


namespace CreativeMail;

use CreativeMail\Managers\AdminManager;
use CreativeMail\Managers\ApiManager;
use CreativeMail\Managers\CheckoutManager;
use CreativeMail\Managers\DatabaseManager;
use CreativeMail\Managers\EmailManager;
use CreativeMail\Managers\InstanceManager;
use CreativeMail\Managers\IntegrationManager;
use CreativeMail\Modules\Contacts\Managers\ContactsSyncManager;

class CreativeMail
{
    private static $instance;

    private $admin_manager;
    private $api_manager;
    private $instance_manager;
    private $integration_manager;
    private $email_manager;
    private $database_manager;
    private $checkout_manager;
    private $contacts_sync_manager;

    public function __construct()
    {
        if (current_user_can('administrator')) {
            $this->admin_manager = new AdminManager();
        }

        $this->database_manager = new DatabaseManager();
        $this->instance_manager = new InstanceManager();
        $this->api_manager = new ApiManager();
        $this->integration_manager = new IntegrationManager();
        $this->email_manager = new EmailManager();
        $this->checkout_manager = new CheckoutManager();
        $this->contacts_sync_manager = new ContactsSyncManager();
    }

    public function add_hooks()
    {
        if (!$this->is_active()) {
            return;
        }

        if ($this->admin_manager !== null) {
            $this->admin_manager->add_hooks();
        }
        if ($this->database_manager !== null) {
            $this->database_manager->add_hooks();
        }

        $this->api_manager->add_hooks();
        $this->integration_manager->add_hooks();
        $this->instance_manager->add_hooks();
        $this->email_manager->add_hooks();

        // check if abandoned cart email is managed by creative mail
        $enabled = $this->email_manager->is_email_managed('cart_abandoned_ce4wp');
        if ($enabled)
        {
            $this->checkout_manager->add_hooks();
        }
    }

    public function get_database_manager()
    {
        return $this->database_manager;
    }

    public function get_integration_manager()
    {
        return $this->integration_manager;
    }

    public function get_instance_manager()
    {
        return $this->instance_manager;
    }

    public function get_api_manager()
    {
        return $this->api_manager;
    }

    public function get_email_manager()
    {
        return $this->email_manager;
    }

    public function get_admin_manager()
    {
        return $this->admin_manager;
    }

    public function get_contacts_sync_manager()
    {
        return $this->contacts_sync_manager;
    }

    public function is_active()
    {
        return in_array(plugin_basename(CE4WP_PLUGIN_FILE), apply_filters('active_plugins', get_option('active_plugins')));
    }

    public static function get_instance()
    {

        if (self::$instance === null) {
            self::$instance = new CreativeMail();
        }

        return self::$instance;
    }
}
