<?php

namespace CreativeMail\Modules\Api\Models;

use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;

class ApiRequestItem
{

    public $httpMethod;
    public $contentType;
    public $endpoint;
    public $payload;
    public $apiKey;
    public $accountId;

    function __construct($httpMethod, $contentType, $endpoint, $payload)
    {

        $apiKey = OptionsHelper::get_instance_api_key();
        $accountId = OptionsHelper::get_connected_account_id();
        $baseUrl = EnvironmentHelper::get_app_gateway_url('wordpress');

        $this->httpMethod = $httpMethod;
        $this->contentType = $contentType;
        $this->endpoint = $baseUrl.$endpoint;
        $this->payload = $payload;
        $this->apiKey = $apiKey;
        $this->accountId = $accountId;
    }
}
