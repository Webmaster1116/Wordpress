<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_GF_EVENTTYPE', 'WordPress - GravityForms');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class GravityFormsPluginHandler extends BaseContactFormPluginHandler
{
    private $textFormFields = array('text', 'textarea');

    public function convertToContactModel($user)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_GF_EVENTTYPE);

        if ($user->isSync) { //If its a DB sync we set optin on true, action by owner
            $contactModel->setOptIn(true);
            $contactModel->setOptOut(false);
            $contactModel->setOptActionBy(OptActionBy::Owner);
        } else {
            //Get contact data from submission
            $contactModel->setOptIn(boolval($user->consent));
            $contactModel->setOptActionBy(OptActionBy::Visitor);
        }

        $email = $user->email;
        if (!empty($email)) {
            $contactModel->setEmail($email);
        }

        $firstName = isset($user->name['firstName']) ? $user->name['firstName'] : null;
        $insertion = isset($user->name['insertion']) ? $user->name['insertion'] : null;
        $lastName = isset($user->name['lastName']) ? $user->name['lastName'] : null;

        if (!empty($firstName)) {
            $contactModel->setFirstName($firstName);
        }

        if (!empty($lastName)) {
            if (!empty($insertion)) {
                $lastName = implode(' ', [$insertion, $lastName]);
            }
            $contactModel->setLastName($lastName);
        }
        if (!empty($user->phone)) {
            $contactModel->setPhone($user->phone);
        }
        if (!empty($user->birthday)) {
            $contactModel->setBirthday($user->birthday);
        }

        return $contactModel;
    }

    /**
     * Gets the first name, optional insertion and last name from the contactform
     *
     * @param $entry (The form submission)
     * @param $form (The form used)
     *
     * @return string (concatenated firstname, insertion and lastname) Returns the concatenated name
     */
    private function GetNameValuesFromForm($entry, $form)
    {
        $nameValues = array();
        foreach ($form['fields'] as $field) {
            if ($field["type"] == "name") {
                $values = $field["inputs"];
                $nameValues["firstName"] = rgar($entry, $values[1]["id"]); //first name
                $nameValues["insertion"] = rgar($entry, $values[2]["id"]); //insertion
                $nameValues["lastName"] = rgar($entry, $values[3]["id"]); //last name
            }
        }
        return $nameValues;
    }

    /**
     * Attempts to get the email from the email field if present,
     * otherwise searches text fields for email labels and values
     * Returns the value of the email field or the first valid email found in an "email" labelled text field, or NULL
     *
     * @param $entry (The form submission)
     * @param $form (The form used)
     *
     * @return string (either a validated email or NULL)
     */
    private function GetEmailFromForm($entry, $form)
    {
        $email = null;
        //Check for email type in form
        foreach ($form['fields'] as $field) {
            if ($field["type"] == "email") {
                $email = rgar($entry, $field["id"]);
                //Check if the values is a valid email
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $email;
                }
            }
        }
        //Else check if we can find an email value in text fields
        foreach ($form['fields'] as $field) {
            if (in_array(strtolower($field["type"]), $this->textFormFields) && in_array(strtolower($field["label"]), $this->emailFields)) {
                $possibleEmail = rgar($entry, $field["id"]);
                if (filter_var($possibleEmail, FILTER_VALIDATE_EMAIL)) {
                    return $possibleEmail;
                }
            }
        }
        return $email;
    }

    private function FindFormData($contact, $entry, $form)
    {
        foreach ($form['fields'] as $field) {
            if (strtolower($field["type"]) === 'phone') {
                $contact->phone = rgar($entry, $field["id"]);
            } elseif (strtolower($field["type"]) === 'date' && in_array(strtolower($field["label"]), $this->birthdayFields)) {
                $contact->birthday = rgar($entry, $field["id"]);
            } elseif (strtolower($field["type"]) === 'consent' && strtolower($field["label"]) === 'consent') {
                $contact->consent = rgar($entry, (string)($field["id"] + .1)); //consent values in the entry are weird
            }
        }
    }

    public function ceHandleGravityFormSubmission($entry, $form)
    {
        try {
            $contact = new \stdClass();
            $contact->name = $this->GetNameValuesFromForm($entry, $form);
            $contact->email = $this->GetEmailFromForm($entry, $form);
            $this->FindFormData($contact, $entry, $form);
            if (empty($contact->email)) {
                return;
            }

            $contact->isSync = false;
            $this->upsertContact($this->convertToContactModel($contact));
        } catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        add_action('gform_after_submission', array($this, 'ceHandleGravityFormSubmission'), 10, 2);
    }

    public function unregisterHooks()
    {
        remove_action('gform_after_submission', array($this, 'ceHandleGravityFormSubmission'));
    }

    public function get_contacts($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        // Relies on plugin => GravityForms
        if (in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            global $wpdb;

            $contactsArray = array();

            //get the forms and their fields
            $formsQuery = 'SELECT form_id, display_meta FROM wp_gf_form_meta';
            $formsResult = $wpdb->get_results($wpdb->prepare($formsQuery));

            //loop through the forms and get their respective entries
            foreach ($formsResult as $form) {
                //get the entries and their meta (i think only meta is needed)
                $entriesQuery = 'SELECT entry_id, meta_key, meta_value FROM wp_gf_entry_meta';
                $entriesQuery .= " WHERE form_id = $form->form_id";
                $entryResults = $wpdb->get_results($entriesQuery);
                if (empty($entryResults)) {
                    continue;
                }

                //combine all entry meta into their respective entries
                $entries = array();
                foreach ($entryResults as $entry) {
                    $entries[$entry->entry_id][$entry->meta_key] = $entry->meta_value;
                }

                //Get the contact data for each entry
                foreach ($entries as $entry) {
                    $contact = new \stdClass();

                    //Get the formArray from the display_meta
                    $formArray = json_decode($form->display_meta, true);

                    $contact->email = $this->GetEmailFromForm($entry, $formArray);
                    if (empty($contact->email)) {
                        continue;
                    }
                    $contact->name = $this->GetNameValuesFromForm($entry, $formArray);
                    $this->FindFormData($contact, $entry, $formArray);
                    $contact->isSync = true;

                    //Convert to contactModel
                    $contactModel = null;
                    try {
                        $contactModel = $this->convertToContactModel($contact);
                    } catch (\Exception $exception) {
                        RaygunManager::get_instance()->exception_handler($exception);
                        continue;
                    }

                    array_push($contactsArray, $contactModel);

                    if (isset($limit) && count($contactsArray) >= $limit) {
                        break;
                    }
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

    function __construct()
    {
        parent::__construct();
    }
}
