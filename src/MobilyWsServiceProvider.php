<?php

namespace Shafimsp\SmsNotificationChannel\MobilyWs;

use GuzzleHttp\Client;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Shafimsp\SmsNotificationChannel\Facades\Sms;
use Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\CouldNotSendMobilyWsNotification;
use Shafimsp\SmsNotificationChannel\SmsManager;

class MobilyWsServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Sms::resolved(function (SmsManager $service) {
            $service->extend('mobilyws', function ($app) {
                $mobilyWsConfig = $this->app['config']['services.mobilyws'];
                if (is_null($mobilyWsConfig)) {
                    throw CouldNotSendMobilyWsNotification::withErrorMessage('Config file was not found. Please publish the config file');
                }

                return new MobilyWsDriver(
                    new MobilyWsApi(
                        new MobilyWsConfig($mobilyWsConfig),
                        new Client(
                            $mobilyWsConfig['guzzle']['client']
                        )
                    )
                );
            });
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

}
