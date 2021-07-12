<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_NL_EVENTTYPE', 'WordPress - NewsLetter');

use CreativeMail\Managers\RaygunManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;

class NewsLetterContactFormPluginHandler extends BaseContactFormPluginHandler
{
    public function convertToContactModel($user)
    {
        $contactModel = new ContactModel();

        $contactModel->setEventType(CE4WP_NL_EVENTTYPE);
        $contactModel->setOptIn(true);
        $contactModel->setOptActionBy(OptActionBy::Visitor);

        $email = $user->email;
        if (!empty($email)) {
            $contactModel->setEmail($email);
        }

        $name = $user->name;
        if (!empty($name)) {
            $contactModel->setFirstName($name);
        }

        $surname = $user->surname;
        if (!empty($surname)) {
            $contactModel->setLastName($surname);
        }

        return $contactModel;
    }

    public function ceHandleContactNewsletterSubmit($user)
    {
        try {
            $this->upsertContact($this->convertToContactModel($user));
        }
        catch (\Exception $exception) {
            RaygunManager::get_instance()->exception_handler($exception);
        }
    }

    public function registerHooks()
    {
        add_action('newsletter_user_confirmed', array($this, 'ceHandleContactNewsletterSubmit'));
    }

    public function unregisterHooks()
    {
        remove_action('newsletter_user_confirmed', array($this, 'ceHandleContactNewsletterSubmit'));
    }

    public function get_contacts($limit = null)
    {
        if (!is_int($limit) || $limit <= 0) {
            $limit = null;
        }

        global $wpdb;

        $query = 'select * from wp_newsletter order by id desc';

        if ($limit != null) {
            $query .= " LIMIT %d";
            $query = $wpdb->prepare($query, $limit);
        } else {
            $query = $wpdb->prepare($query);
        }

        $result = $wpdb->get_results($query);

        $backfillArray = array();

        if (isset($result) && !empty($result)) {
            foreach ($result as $contact) {
                $contactModel = new ContactModel();
                try {
                    $contactModel->setEventType(CE4WP_NL_EVENTTYPE);
                    $contactModel->setOptIn($contact->status !== "U");
                    $contactModel->setOptOut($contact->status === "U");
                    $contactModel->setOptActionBy(OptActionBy::Visitor);

                    $email = $contact->email;
                    if (!empty($email)) {
                        $contactModel->setEmail($email);
                    }

                    $name = $contact->name;
                    if (!empty($name)) {
                        $contactModel->setFirstName($name);
                    }

                    $surname = $contact->surname;
                    if (!empty($surname)) {
                        $contactModel->setLastName($surname);
                    }
                } catch (\Exception $exception) {
                    RaygunManager::get_instance()->exception_handler($exception);
                    continue;
                }

                if (!empty($contactModel->getEmail())) {
                    array_push($backfillArray, $contactModel);
                }
            }
        }

        if (!empty($backfillArray)) {
            return $backfillArray;
        }

        return null;
    }

    function __construct()
    {
        parent::__construct();
    }
}
