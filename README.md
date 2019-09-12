# MobilyWs Driver For Laravel SMS Notification Channel

## Installation
------------

To install the PHP client library using Composer:

```bash
composer require shafimsp/laravel-sms-notification-channel
```

The package will automatically register itself.

## Driver Prerequisites

MobilyWs is API based driver, this require the Guzzle HTTP library, which may be installed via the Composer package manager:

```bash
composer require composer require guzzlehttp/guzzle
```

## MobilyWs Driver

To use the MobilyWs driver, first install Guzzle, then set the driver option in your  config/services.php configuration file to mobilyws. Next, verify that your config/services.php configuration file contains the following options:

```php
    'mobilyws' => [
        // Set yor login credentials to communicate with mobily.ws Api
        'mobile' => env('MOBILYWS_KEY', ''),
        'password' => env('MOBILYWS_KEY', ''),

        // Or use the generated apiKey from your mobily.ws account
        'key' => env('MOBILYWS_KEY', ''),

        'sms_from' => env('MOBILYWS_SMS_FROM', ''),

        // Required by mobily.ws Don't Change.
        'applicationType' => 68,

        // Authentication mode. possible values: api, password, or auto
        'authentication' => 'auto',

        // 3 when using UTF-8. Don't Change
        'lang' => '3',

        /*
        |--------------------------------------------------------------------------
        | Define options for the Http request. (Guzzle http client options)
        |--------------------------------------------------------------------------
        |
        | You do not need to change any of these settings.
        |
        |
        */
        'guzzle' => [
            'client' => [
                // The Base Uri of the Api. Don't Change this Value.
                'base_uri' => 'http://mobily.ws/api/',
            ],

            // Request Options. http://docs.guzzlephp.org/en/stable/request-options.html
            'request' => [
                'http_errors' => true,
                // For debugging
                'debug' => false,
            ],
        ],
    ]
```

## License

MobilyWs SMS Notification Driver is open-sourced software licensed under the [MIT license](LICENSE.md).
