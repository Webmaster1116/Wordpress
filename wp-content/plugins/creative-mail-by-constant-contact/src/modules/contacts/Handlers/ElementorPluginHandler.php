<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_EL_EVENTTYPE', 'WordPress - Elementor');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class ElementorPluginHandler extends BaseContactFormPluginHandler
{
    function __construct()
    {
        parent::__construct();
    }

    private function GetNameFromForm($fields)
    {
        foreach ($fields as $field) {
            //Try to find a name value based on the default Elementor form with name field
            if (array_key_exists('type', $field) && ($field['type'] === "text" && $field['id'] === "name")) {
                return $field["value"];
            }
        }
        return null;
    }

    private function FindFormData($elemContact, $fields)
    {
        foreach ($fields as $field) {
            if (array_key_exists('type', $field) && in_array($field['type'], $this->emailFields)) {
                $elemContact->email = $field["value"];
            } elseif (array_key_exists('type', $field) && in_array($field['type'], $this->phoneFields)) {
                $elemContact->phone = $field["value"];
            } elseif (array_key_exists('type', $field) && $field['type'] === 'date' && in_array(strtolower($field['title']), $this->birthdayFields)) {
                $elemContact->birthday = $field["value"];
            }
        }
    }

    public function convertToContactModel($contact)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_EL_EVENTTYPE);

        $contactModel->setOptIn(false);
        $contactModel->setOptOut(false);
        $contactModel->setOptActionBy(OptActionBy::Visitor);

        if (!empty($contact->email)) {
            $contactModel->setEmail($contact->email);
        }

        $values = explode(' ', $contact->name);
        $firstName = array_shift($values);
        $lastName = implode(' ', $values);

        if (!empty($firstName)) {
            $contactModel->setFirstName($firstName);
        }
        if (!empty($lastName)) {
            $contactModel->setLastName($lastName);
        }
        if (!empty($contact->phone)) {
            $contactModel->setPhone($contact->phone);
        }
        if (!empty($contact->birthday)) {
            $contactModel->setBirthday($contact->birthday);
        }
        return $contactModel;
    }

    public function ceHandleElementorFormSubmission($settings, $record)
    {
        try {
            $fields = $record->get("fields");
            $elemContact = new \stdClass();
            $elemContact->name = $this->GetNameFromForm($fields);

            //attempt to get additional data
            $this->FindFormData($elemContact, $fields);
            if (empty($elemContact->email)) {
                return;
            }

            $this->upsertContact($this->convertToContactModel($elemContact));
        } catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        add_action('elementor_pro/forms/mail_sent', array($this, 'ceHandleElementorFormSubmission'), 10, 2);
    }

    public function unregisterHooks()
    {
        remove_action('elementor_pro/forms/mail_sent', array($this, 'ceHandleElementorFormSubmission'));
    }

    public function get_contacts($limit = null)
    {
        //Elementor seems to not store form submissions locally
        return null;
    }
}
