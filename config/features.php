<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public registration
    |--------------------------------------------------------------------------
    |
    | This app ships as a single-user tool: you provision the one owner account
    | with `php artisan app:create-user` and public registration stays closed.
    | Self-hosters who want to run it for a team can flip this to true (set
    | REGISTRATION_ENABLED=true in .env) to open the /register routes and the
    | "Sign up" link on the login page. The routes are always registered but
    | 404 when this is false, so nothing is exposed until you opt in.
    |
    */

    'registration_enabled' => env('REGISTRATION_ENABLED', false),

];
