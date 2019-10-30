<?php

namespace Shafimsp\SmsNotificationChannel\MobilyWs;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\MobilyWsSmsNotificationException;
use Shafimsp\SmsNotificationChannel\SmsMessage;

class MobilyWsApi
{
    
    /**  @var string mobily.ws endpoint for sending sms */
    protected $endpoint = 'msgSend.php';
    
    /** @var MobilyWsConfig */
    private $config;

    /** @var HttpClient */
    private $http;

    /**
     * Create a new MobilyWs channel instance.
     *
     * @param MobilyWsConfig $config
     * @param HttpClient     $http
     */
    public function __construct(MobilyWsConfig $config, HttpClient $http)
    {
        $this->http = $http;
        $this->config = $config;
    }
    
    /**
     * Send request with string message
     *
     * @param $params
     *
     * @return array
     */
    public function sendString($params)
    {
        $payload = $this->preparePayload($params);
        return $this->send($payload);
    }
    
    /**
     * Send request with MobilyWsMessage instance
     *
     * @param SmsMessage $message
     *
     * @return array
     */
    public function sendMessage(SmsMessage $message)
    {
        $params = [
            'msg' => $message->content,
            'numbers' => implode(",", $message->to),
            'dateSend' => $message->dateSend,
            'timeSend' => $message->timeSend,
            'deleteKey' => $message->deleteKey,
        ];
        
        $payload = $this->preparePayload($params);
        return $this->send($payload);
    }
    
    /**
     * Send request to mobily.ws
     *
     * @param array $payload
     *
     * @return array
     * @throws \Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\MobilyWsSmsNotificationException
     * @internal param array $params
     *
     */
    public function send(array $payload)
    {
        try {
            $response = $this->http->post($this->endpoint, $payload);

            if ($response->getStatusCode() != 200) {
                throw MobilyWsSmsNotificationException::someErrorWhenSendingSms($response);
            }

            $code = (string) $response->getBody();

            if ($code != 1) {
                throw MobilyWsSmsNotificationException::mobilyWsRespondedWithAnError($code, $this->msgSendResponse($code));
            }

            return $code;
        } catch (RequestException $exception) {
            throw MobilyWsSmsNotificationException::couldNotSendRequestToMobilyWs($exception);
        }
    }

    /**
     * Prepare payload for http request.
     *
     * @param $params
     *
     * @return array
     */
    protected function preparePayload($params)
    {
        $form = array_merge([
            'applicationType' => $this->config->applicationType,
            'lang' => $this->config->lang,
            'sender' => $this->config->sms_from,
        ], $params, $this->config->getCredentials());

        return array_merge(
            ['form_params' => $form],
            $this->config->request
        );
    }

    /**
     * Parse the response body from mobily.ws.
     *
     * @param $code
     *
     * @return string
     */
    protected function msgSendResponse($code)
    {
        $arraySendMsg = [];
        $arraySendMsg[0] = 'Not connected to server';
        $arraySendMsg[1] = 'Successfully submitted';
        $arraySendMsg[2] = 'Your balance 0, please recharge so you can send messages';
        $arraySendMsg[3] = 'Your balance is insufficient to complete the submission';
        $arraySendMsg[4] = 'Mobile number (username) is invalid';
        $arraySendMsg[5] = 'Invalid account password';
        $arraySendMsg[6] = 'Web page ineffective, try sending again';
        $arraySendMsg[7] = 'School system ineffective';
        $arraySendMsg[8] = 'Duplicate school code for same user';
        $arraySendMsg[9] = 'End of trial period';
        $arraySendMsg[10] = 'Number of digits is not equal to number of messages';
        $arraySendMsg[11] = 'Your subscription does not allow you to send messages to this school. You must activate the subscription for this school';
        $arraySendMsg[12] = 'Portal version is incorrect';
        $arraySendMsg[13] = 'The number sent is not enabled or there is no BS at the end of the message';
        $arraySendMsg[14] = 'You are not authorized to post using this sender';
        $arraySendMsg[15] = 'Sender numbers are missing or invalid';
        $arraySendMsg[16] = 'Sender name is empty, or invalid';
        $arraySendMsg[17] = 'Message body not available or not properly encrypted';
        $arraySendMsg[18] = 'Transmission stopped from provider';
        $arraySendMsg[19] = 'Application type key not found';

        if (array_key_exists($code, $arraySendMsg)) {
            return $arraySendMsg[$code];
        }
        $message = "The result of the operation is unknown, please try again \n";
        $message .= 'Code sent from the site:';
        $message .= "{$code}";

        return $message;
    }
}
