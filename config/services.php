<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * Microsoft Entra ID (Azure AD) single sign-on.
     *
     * SSO authenticates only; authorization (roles, org scope) is managed
     * in-app from the Users screen. Leave the client id/secret blank to
     * disable SSO entirely (the login button is hidden and the routes 404).
     *
     * `tenant` locks sign-in to a single Entra tenant; defaults to 'common'.
     * `default_org_id` is the organization new SSO users are provisioned into
     * (falls back to the first active non-MSP organization when unset).
     */
    'microsoft' => [
        'client_id' => env('AZURE_SSO_CLIENT_ID'),
        'client_secret' => env('AZURE_SSO_CLIENT_SECRET'),
        'redirect' => env('AZURE_SSO_REDIRECT_URI'),
        'tenant' => env('AZURE_SSO_TENANT_ID', 'common'),
        'default_org_id' => env('AZURE_SSO_DEFAULT_ORG_ID'),
    ],

];
