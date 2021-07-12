<?php

namespace CreativeMail\Modules\Contacts\Handlers;

use CreativeMail\Modules\Contacts\Services\ContactsSyncService;
use Exception;

abstract class BaseContactFormPluginHandler
{
    private $contactSyncService;

    public abstract function convertToContactModel($contactForm);
    public abstract function registerHooks();
    public abstract function unregisterHooks();
    public abstract function get_contacts($limit = null);

    protected $birthdayFields = array('birthday', 'date-of-birth', 'date_of_birth', 'birth_date', 'birth-date', 'birth date', 'birth day', 'date of birth');
    protected $phoneFields = array('phone', 'phone_number', 'telephone', 'tel', 'tel-number', 'tel_number', 'mobile_number', 'mobile number', 'phone number');
    protected $emailFields = array('your-email', 'email', 'e-mail', 'emailaddress', 'email_address', 'email address', 'email-address', 'e-mail address');
    protected $firstnameFields = array('firstname', 'first_name', 'name', 'your-name', 'first name', 'first-name', 'first');
    protected $lastnameFields = array('lastname', 'last_name', 'last name', 'last-name', 'last');

    public function upsertContact($model)
    {
        if (!isset($model)) {
            throw new Exception('No model provided');
        }

        $contactModel = null;
        if (!is_a($model, 'CreativeMail\Modules\Contacts\Models\ContactModel')) {
            $contactModel = $this->convertToContactModel($model);
        }
        else {
            $contactModel = $model;
        }
        $this->contactSyncService->upsertContact($contactModel);
    }

    public function batchUpsertContacts($models)
    {
        if (!isset($models)) {
            throw new Exception('No models provided');
        }

        $this->contactSyncService->upsertContacts($models);
    }

    protected function isNullOrEmpty($value)
    {
        return !isset($value) && empty($value);
    }

    function __construct()
    {
        $this->contactSyncService = new ContactsSyncService();
        $this->registerHooks();
    }

    function __destruct()
    {
        $this->unregisterHooks();
    }
}
