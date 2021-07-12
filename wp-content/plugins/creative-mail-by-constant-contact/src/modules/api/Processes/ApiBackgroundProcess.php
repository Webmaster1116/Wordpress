<?php

namespace CreativeMail\Modules\Api\Processes;

use CreativeMail\Modules\Api\Models\ApiRequestItem;
use WP_Background_Process;

class ApiBackgroundProcess extends WP_Background_Process
{

    protected $action = 'ce_api_background_process';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param ApiRequestItem $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $item )
    {
        if (!isset($item->httpMethod) || empty($item->httpMethod)) {
            return false;
        }

        if (!isset($item->endpoint) || empty($item->endpoint)) {
            return false;
        }

        $httpMethod = strtoupper($item->httpMethod);

        if ($httpMethod === 'POST') {
            wp_remote_post(
                $item->endpoint, array(
                'method' => $httpMethod,
                'headers' => array(
                    'x-account-id' => $item->accountId,
                    'x-api-key' => $item->apiKey,
                    'content-type' => $item->contentType
                ),
                'body' => $item->payload
                )
            );
            return false;
        }

        wp_remote_get(
            $item->endpoint, array(
            'method' => $httpMethod,
            'headers' => array(
                'x-account-id' => $item->accountId,
                'x-api-key' => $item->apiKey,
                'content-type' => $item->contentType
            )
            )
        );
        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        parent::complete();
    }
}
