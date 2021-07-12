<?php


namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WPF_EVENTTYPE', 'WordPress - WPForms');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use function Sodium\add;

class WpFormsPluginHandler extends BaseContactFormPluginHandler
{
    private function get_form_type_field($formData, $type)
    {
        foreach ($formData as $field) {
            if (array_key_exists('type', $field) && $field['type'] === $type) {
                return $field;
            }
        }
        return null;
    }

    private function convertEntryStringToFormData($entry)
    {
        $formdata = array();
        $entry = json_decode($entry->fields, true);
        foreach ($entry as $field) {
            if (array_key_exists('type', $field)) {
                $formdata[$field["type"]] = $field["value"];
            }
        }
        return $entry;
    }

    public function convertToContactModel($formData)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_WPF_EVENTTYPE);
        $contactModel->setOptIn(false);
        $contactModel->setOptOut(false);
        $contactModel->setOptActionBy(OptActionBy::Owner);

        $emailField = $this->get_form_type_field($formData, 'email');
        if (array_key_exists('value', $emailField)) {
            if (!empty($emailField['value'])) {
                $contactModel->setEmail($emailField['value']);
            }
        }

        $nameField = $this->get_form_type_field($formData, 'name');
        if (array_key_exists('first', $nameField)) {
            if (!empty($nameField['first'])) {
                $contactModel->setFirstName($nameField['first']);
            }
        }
        if (array_key_exists('last', $nameField)) {
            if (!empty($nameField['last'])) {
                $contactModel->setLastName($nameField['last']);
            }
        }

        $phoneField = $this->get_form_type_field($formData, 'phone');
        if (!empty($phoneField)) {
            if (!empty($phoneField['value'])) {
                $contactModel->setPhone($phoneField['value']);
            }
        }

        $dateField = $this->get_form_type_field($formData, 'date-time');
        if (!empty($dateField) && array_key_exists('date', $dateField)) {
            if (!empty($dateField['date']) && in_array(strtolower($dateField['name']), $this->birthdayFields)) {
                $contactModel->setBirthday($dateField['date']);
            }
        }

        $consentField = $this->get_form_type_field($formData, "gdpr-checkbox");
        if (!empty($consentField) && array_key_exists('value', $consentField) && $consentField) {
            //If a gdpr checkbox is present it is required before submitting
            //The value is a string like "I consent to having this website store my information . . . " instead of a bool
            //Will assume people won't alter or change this to be the other way around so having this value == consent
            $contactModel->setOptIn(true);
            $contactModel->setOptActionBy(OptActionBy::Visitor);
        }

        return $contactModel;
    }

    public function ceHandleWpFormsProcessComplete($fields)
    {
        try {
            $this->upsertContact($this->convertToContactModel($fields));
        } catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        // https://wpforms.com/developers/wpforms_process_complete/
        add_action('wpforms_process_complete', array($this, 'ceHandleWpFormsProcessComplete'), 10, 4);
    }

    public function unregisterHooks()
    {
        remove_action('wpforms_process_complete', array($this, 'ceHandleWpFormsProcessComplete'));
    }

    public function get_contacts($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        // Relies on plugin => wpforms paid or pro
        if (in_array('wpforms/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')))
            || in_array('wpforms-lite/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')))
        ) { //this is a guess, have to test first

            //Get form submissions from the wpforms db
            global $wpdb;
            $contactsArray = array();

            //get the form entries
            $entriesQuery = 'SELECT fields FROM wp_wpforms_entries';
            $entryResult = $wpdb->get_results($wpdb->prepare($entriesQuery));

            //Loop through entries and create the contacts
            foreach ($entryResult as $entry) {
                $contactModel = null;
                try {
                    $entryData = $this->convertEntryStringToFormData($entry);
                    $contact = $this->convertToContactModel($entryData);
                    if (!empty($contact->getEmail())) {
                        array_push($contactsArray, $contact);
                    }

                } catch (\Exception $exception) {
                    RaygunManager::get_instance()->exception_handler($exception);
                    continue;
                }

                if (isset($limit) && count($contactsArray) >= $limit) {
                    break;
                }
            }

            if (!empty($contactsArray)) {
                return $contactsArray;
            }
        }

        return null;
    }

    function __construct()
    {
        parent::__construct();
    }
}
