<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_CAL_EVENTTYPE', 'WordPress - Caldera Forms');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class CalderaPluginHandler extends BaseContactFormPluginHandler
{
    private function GetCalderaPhoneFields()
    {
        return array_merge($this->phoneFields, ['phone_better']);
    }

    private function GetNameFromForm($entry)
    {
        if ($this->isNullOrEmpty($entry)) {
            return null;
        }

        $name = null;
        foreach ($entry as $field) {
            if ($field->slug === "first_name") {
                $name["firstname"] = $field->value;
                continue;
            }
            if ($field->slug === "last_name") {
                $name["lastname"] = $field->value;
                return $name;
            }
        }
        return $name;
    }

    private function FindFormValues($entry, $calderaContact)
    {
        if ($this->isNullOrEmpty($entry)) {
            return null;
        }
        $calderaPhoneFields = $this->GetCalderaPhoneFields();
        foreach ($entry as $field) {
            $slug = strtolower($field->slug);
            if (in_array($slug, $calderaPhoneFields)) {
                $calderaContact->phone = $field->value;
            } elseif (in_array($slug, $this->birthdayFields)) {
                $calderaContact->birthday = $field->value;
            } elseif ($slug == "email_address") {
                $calderaContact->email = $field->value;
            } elseif ($slug == "consent") {
                $calderaContact->consent = $field->value;
            }
        }
    }

    public function convertToContactModel($contact)
    {
        $email = $contact->email;
        if ($this->isNullOrEmpty($email)) {
            return null;
        }

        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_CAL_EVENTTYPE);
        $contactModel->setOptOut(false);

        //if its a form submission we take the opt_in value and set action to visitor
        if ($contact->isFormSubmission) {
            $contactModel->setOptIn(boolval($contact->consent));
            $contactModel->setOptActionBy(OptActionBy::Visitor);
        } else {
            $contactModel->setOptIn(true);
            $contactModel->setOptActionBy(OptActionBy::Owner);
        }

        $contactModel->setEmail($email);

        if (!empty($contact->firstname)) {
            $contactModel->setFirstName($contact->firstname);
        }
        if (!empty($contact->lastname)) {
            $contactModel->setLastName($contact->lastname);
        }
        if (!empty($contact->phone)) {
            $contactModel->setPhone($contact->phone);
        }
        if (!empty($contact->birthday)) {
            $contactModel->setBirthday($contact->birthday);
        }

        return $contactModel;
    }

    public function ceHandleCalderaFormSubmission($form, $referrer, $process_id, $entryid)
    {
        try {
            global $wpdb;
            $calderaContact = new \stdClass();
            $calderaContact->isFormSubmission = true;

            $entryData = $wpdb->get_results($wpdb->prepare("SELECT slug, `value` FROM wp_cf_form_entry_values WHERE entry_id = {$entryid}"));
            $nameValues = $this->GetNameFromForm($entryData);
            $calderaContact->firstname = array_key_exists("firstname", $nameValues) ? $nameValues["firstname"] : null;
            $calderaContact->lastname = array_key_exists("lastname", $nameValues) ? $nameValues["lastname"] : null;

            //get additional form values and add to the contact
            $this->FindFormValues($entryData, $calderaContact);

            if (empty($calderaContact->email)) {
                return;
            }
            $this->upsertContact($this->convertToContactModel($calderaContact));
        } catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        add_action('caldera_forms_submit_complete', array($this, 'ceHandleCalderaFormSubmission'), 60, 4); //make sure the prio is set as to run after caldera itself otherwise data is not present in db
    }

    public function unregisterHooks()
    {
        remove_action('caldera_forms_submit_complete', array($this, 'ceHandleCalderaFormSubmission'));
    }

    public function get_contacts($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        // Relies on plugin => GravityForms
        if (in_array('caldera-forms/caldera-core.php', apply_filters('active_plugins', get_option('active_plugins'))) && defined('CFCORE_VER')) {
            global $wpdb;

            $contactsArray = array();
            $entryIds = $wpdb->get_results($wpdb->prepare("SELECT id FROM wp_cf_form_entries WHERE status = 'active'"));

            //loop through the entries and extract necessary data
            foreach ($entryIds as $entry) {
                $contact = new \stdClass();
                $contact->isFormSubmission = false;
                $entryData = $wpdb->get_results($wpdb->prepare("SELECT slug, `value` FROM wp_cf_form_entry_values WHERE entry_id = {$entry->id}"));

                //get form values
                $this->FindFormValues($entryData, $contact);

                if (empty($contact->email)) {
                    continue;
                }

                $nameValues = $this->GetNameFromForm($entryData);
                $contact->firstname = array_key_exists("firstname", $nameValues) ? $nameValues["firstname"] : null;
                $contact->lastname = array_key_exists("lastname", $nameValues) ? $nameValues["lastname"] : null;

                //contact opt in is true on sync
                $contact->consent = true;

                //Convert to contactModel
                $contactModel = null;
                try {
                    $contactModel = $this->convertToContactModel($contact);
                } catch (\Exception $exception) {
                    RaygunManager::get_instance()->exception_handler($exception);
                    continue;
                }

                if (!empty($contactModel)) {
                    array_push($contactsArray, $contactModel);
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
}
