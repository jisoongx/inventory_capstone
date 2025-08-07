<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        // Change the default guard to one of your existing guards
        // Or, if you always explicitly specify guards, you can remove this env() call
        'guard' => env('AUTH_GUARD', 'super_admin'), // Changed from 'web' to 'super_admin'
        // Change the default password broker to one of your existing ones,
        // or remove it if you don't use a generic password reset
        'passwords' => env('AUTH_PASSWORD_BROKER', 'super_admins'), // Changed from 'users' to 'super_admins'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        // If you don't use a generic 'web' guard, you can remove or comment this out.
        // If you need a 'web' guard, you MUST configure it to use one of your existing providers.
        // For example, if 'super_admin' is your primary, you could do:
        // 'web' => [
        //     'driver' => 'session',
        //     'provider' => 'super_admins',
        // ],
        // For now, we'll assume you don't need a generic 'web' guard that isn't one of your specific roles.
        'super_admin' => [
            'driver' => 'session',
            'provider' => 'super_admins',
        ],
        'owner' => [
            'driver' => 'session',
            'provider' => 'owners',
        ],
        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        // Removed the default 'users' provider as you don't use a 'users' table or App\Models\User model.
        'super_admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\SuperAdmin::class,
        ],
        'owners' => [
            'driver' => 'eloquent',
            'model' => App\Models\Owner::class,
        ],
        'staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        // If you need password reset for SuperAdmins, Owners, or Staff,
        // you should define separate password brokers for each,
        // pointing to their respective providers.
        // For example:
        'super_admins' => [
            'provider' => 'super_admins',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'), // You'll need this table
            'expire' => 60,
            'throttle' => 60,
        ],
        // You can add 'owners' and 'staff' password brokers similarly if needed.
        // Removed the default 'users' password broker as it's not used.
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];