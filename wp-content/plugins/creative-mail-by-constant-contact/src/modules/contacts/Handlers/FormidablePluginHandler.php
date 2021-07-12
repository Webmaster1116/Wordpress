<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_FRM_EVENTTYPE', 'WordPress - Formidable');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class FormidablePluginHandler extends BaseContactFormPluginHandler
{
    function __construct()
    {
        parent::__construct();
    }

    private function FindEntryValues($entry, $formidableContact)
    {
        if ($this->isNullOrEmpty($entry)) {
            return null;
        }
        foreach ($entry as $field) {
            $fieldName = strtolower($field->fieldName);
            if ($field->fieldType == 'phone' || in_array($fieldName, $this->phoneFields)) {
                $formidableContact->phone = $field->entryValue;
            } elseif ($field->fieldType == 'text' && in_array($fieldName, $this->firstnameFields)) {
                $formidableContact->firstName = $field->entryValue;
            } elseif ($field->fieldType == 'text' && in_array($fieldName, $this->lastnameFields)) {
                $formidableContact->lastName = $field->entryValue;
            } elseif ($field->fieldType == 'date' && in_array($fieldName, $this->birthdayFields)) {
                $formidableContact->birthday = $field->entryValue;
            } elseif ($field->fieldType == 'email') {
                $formidableContact->email = $field->entryValue;
            }
        }
    }

    public function convertToContactModel($contact)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_FRM_EVENTTYPE);

        if (isset($contact->isSync) && $contact->isSync) {
            $contactModel->setOptIn(true);
            $contactModel->setOptOut(false);
            $contactModel->setOptActionBy(OptActionBy::Owner);
        }

        //Formidable doesn't seem to have a consent checkbox

        if (!empty($contact->email)) {
            $contactModel->setEmail($contact->email);
        }

        if (!empty($contact->firstName)) {
            $contactModel->setFirstName($contact->firstName);
        }

        if (!empty($contact->lastName)) {
            $contactModel->setLastName($contact->lastName);
        }

        if (!empty($contact->phone)) {
            $contactModel->setPhone($contact->phone);
        }
        if (!empty($contact->birthday)) {
            $contactModel->setBirthday($contact->birthday);
        }
        return $contactModel;
    }

    public function ceHandleFormidableFormSubmission($entry_id, $form_id)
    {
        try {
            //map entry values to the field meta
            $entry = \FrmField::get_all_for_form($form_id);
            $entryFieldData = $_POST["item_meta"];
            foreach ($entry as $field) {
                if (!empty($entryFieldData[$field->id])) {
                    $field->entryValue = $entryFieldData[$field->id];
                    $field->fieldType = $field->type;
                    $field->fieldName = $field->name;
                }
            }
            $formidableContact = new \stdClass();

            //Convert to contactModel
            $this->FindEntryValues($entry, $formidableContact);

            if (empty($formidableContact->email)) {
                return;
            }
            $this->upsertContact($this->convertToContactModel($formidableContact));
        } catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        add_action('frm_after_create_entry', array($this, 'ceHandleFormidableFormSubmission'), 30, 2);
    }

    public function unregisterHooks()
    {
        remove_action('frm_after_create_entry', array($this, 'ceHandleFormidableFormSubmission'));
    }

    public function get_contacts($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        if (in_array('formidable/formidable.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            global $wpdb;
            $contactsArray = array();

            $forms = \FrmForm::getAll();
            foreach ($forms as $form) {
                $entriesQuery = "SELECT e.item_id AS entryId, e.meta_value AS entryValue, f.name AS fieldName, f.description AS fieldDescription, f.type AS fieldType
                            FROM wp_frm_item_metas e
                            INNER JOIN wp_frm_fields f ON f.id = e.field_id WHERE f.form_id = {$form->id} ORDER BY e.item_id";

                $entryResults = $wpdb->get_results($wpdb->prepare($entriesQuery));
                if (empty($entryResults)) {
                    continue;
                }

                $mappedEntries = $this->CombineEntryData($entryResults);

                //Get the contact data for each entry
                foreach ($mappedEntries as $entry) {
                    $formidableContact = new \stdClass();

                    //Convert to contactModel
                    $this->FindEntryValues($entry, $formidableContact);

                    if (empty($formidableContact->email)) {
                        continue;
                    }
                    $formidableContact->isSync = true;
                    try {
                        $contactModel = null;
                        $contactModel = $this->convertToContactModel($formidableContact);
                    } catch (\Exception $exception) {
                        RaygunManager::get_instance()->exception_handler($exception);
                        continue;
                    }
                    if (!empty($contactModel->email)) {
                        array_push($contactsArray, $contactModel);
                    }
                    if (isset($limit) && count($contactsArray) >= $limit) {
                        break;
                    }
                }
            }
            if (!empty($contactsArray)) {
                return $contactsArray;
            }
            return null;
        }
    }

    private function CombineEntryData(array $entryResults)
    {
        $entries = array();
        foreach ($entryResults as $entryRow) {
            if (isset($entryRow->entryId)) {
                $entries[$entryRow->entryId][] = $entryRow;
            } elseif (isset($entryRow->id)) {
                $entries[$entryRow->id][] = $entryRow;
            }
        }
        return $entries;
    }
}
