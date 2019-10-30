<?php
namespace Shafimsp\SmsNotificationChannel\MobilyWs;

use Shafimsp\SmsNotificationChannel\Drivers\Driver;
use Shafimsp\SmsNotificationChannel\SmsMessage;

class MobilyWsDriver extends Driver
{

    /**
     * The Nexmo client.
     *
     * @var MobilyWsApi
     */
    protected $client;


    /**
     * Create a new Nexmo driver instance.
     *
     * @param  MobilyWsApi  $client
     */
    public function __construct( $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send(SmsMessage $message)
    {
        return $this->client->sendMessage($message);
    }

}