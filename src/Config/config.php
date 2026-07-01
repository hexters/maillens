<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailer name — the single switch
    |--------------------------------------------------------------------------
    |
    | MailLens turns on automatically whenever your app's MAIL_MAILER matches
    | the name below. Set MAIL_MAILER=lens and outgoing mail is captured and
    | the /mail inbox becomes available — no separate enable flag needed.
    | Point MAIL_MAILER at anything else and MailLens stays completely out of
    | the way (routes and table aren't even loaded).
    |
    */

    'mailer' => 'lens',

    /*
    |--------------------------------------------------------------------------
    | Web UI
    |--------------------------------------------------------------------------
    |
    | Where the inbox lives and what middleware guards it.
    |
    */

    'route_prefix' => env('MAILLENS_ROUTE_PREFIX', 'mail'),

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Keep at most this many captured messages; older ones are pruned as new
    | mail arrives. Set to null to keep everything.
    |
    */

    'limit' => env('MAILLENS_LIMIT', 200),

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    |
    | Captured mail is stored here using the app's default database connection.
    |
    */

    'table' => 'maillens_messages',

];
