<?php

namespace CreativeMail\Clients;

use CreativeMail\Exceptions\CreativeMailException;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use stdClass;

class CreativeMailClient {

    private $instance_api_key;
    private $connected_account_id;

    public function __construct()
    {
        $this->instance_api_key = OptionsHelper::get_instance_api_key();
        $this->connected_account_id = OptionsHelper::get_connected_account_id();
    }

    public function get_account_status()
    {
        $response = wp_remote_get(
            EnvironmentHelper::get_app_gateway_url() . 'wordpress/v1.0/account/status',
            $this->get_default_headers()
        );

        if ( is_wp_error( $response ) ) {
            throw new CreativeMailException( 'Could not get account status' );
        }

        if ($response['response']['code'] === 401) {
            return null;
        }

        return json_decode( $response['body'], true );
    }

    public function get_most_recent_campaigns()
    {
        $response = wp_remote_get(
            EnvironmentHelper::get_app_gateway_url() . 'wordpress/v1.0/campaign-statistics/most-recent',
            $this->get_default_headers()
        );

        if ( is_wp_error( $response ) ) {
            throw new CreativeMailException( 'Could not get most recent campaigns' );
        }

        $campaigns_data = json_decode( $response['body'], true );
        return $this->parse_most_recent_campaigns( $campaigns_data );
    }

    private function parse_most_recent_campaigns( $campaigns_data )
    {
        $most_recent_campaigns = [];

        foreach ( $campaigns_data as $campaign_data ) {
            $campaign = new stdClass();
            $campaign->id = $campaign_data['external_id'];
            $campaign->name = $campaign_data['name'];

            if ( empty( $campaign_data['scheduled_on'] ) ) {
                $campaign->status = __( 'Draft', 'ce4wp' );
                $campaign->is_draft = true;
            } else if ( empty( $campaign_data['activity_summaries'] ) ) {
                $scheduled_on = date( "m/d/Y", strtotime( $campaign_data['scheduled_on'] ) );
                $campaign->status = sprintf( __( 'Scheduled on %s', 'ce4wp' ), $scheduled_on );
                $campaign->is_draft = false;
            } else {
                $sent_on = date( "m/d/Y", strtotime( $campaign_data['scheduled_on'] ) );
                $campaign->status = sprintf( __( 'Sent on %s', 'ce4wp' ), $sent_on );
                $campaign->is_draft = false;

                $activity_summary = $campaign_data['activity_summaries'][0];
                $number_of_opens = $activity_summary['stats']['em_opens'];
                $number_of_sends = $activity_summary['stats']['em_sends'];
                $campaign->open_rate = floor( ( $number_of_opens / $number_of_sends ) * 100 );
            }

            $most_recent_campaigns[] = $campaign;
        }

        return $most_recent_campaigns;
    }

    private function get_default_headers()
    {
        return [
            'headers' => [
                'x-api-key' => $this->instance_api_key,
                'x-account-id' => $this->connected_account_id
            ],
        ];
    }

}
