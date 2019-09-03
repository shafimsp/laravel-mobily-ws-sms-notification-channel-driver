<?php
namespace Shafimsp\SmsNotificationChannel\MobilyWs;

use Shafimsp\SmsNotificationChannel\Drivers\Driver;
use Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\CouldNotSendMobilyWsNotification;
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
    public function send()
    {
        $response = $this->dispatchRequest($this->message, $this->recipient);

        if ($response['code'] == 1) {
            return $response['message'];
        }

        throw CouldNotSendMobilyWsNotification::mobilyWsRespondedWithAnError($response['code'], $response['message']);
    }

    /**
     * @param $message
     * @param $number
     *
     * @return array
     *
     * @throws CouldNotSendMobilyWsNotification
     */
    private function dispatchRequest($message, $number)
    {
        if (is_string($message)) {
            $response = $this->client->sendString([
                'msg' => $message,
                'numbers' => $number,
            ]);
        } elseif ($message instanceof SmsMessage) {
            $response = $this->client->sendMessage($message, $number);
        } else {
            $errorMessage = sprintf('toMobilyWs must return a string or instance of %s. Instance of %s returned',
                SmsMessage::class,
                gettype($message)
            );

            throw CouldNotSendMobilyWsNotification::withErrorMessage($errorMessage);
        }

        return $response;
    }
}