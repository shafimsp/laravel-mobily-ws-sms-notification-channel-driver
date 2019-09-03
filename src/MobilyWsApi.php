<?php

namespace Shafimsp\SmsNotificationChannel\MobilyWs;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\CouldNotSendMobilyWsNotification;
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
     * @param                 $number
     *
     * @return array
     * @internal param $params
     */
    public function sendMessage(SmsMessage $message, $number)
    {
        $params = [
            'msg' => $message->content,
            'numbers' => $number,
            'dateSend' => $message->dateSend(),
            'timeSend' => $message->timeSend(),
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
     * @throws \Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\CouldNotSendMobilyWsNotification
     * @internal param array $params
     *
     */
    public function send(array $payload)
    {
        try {
            $response = $this->http->post($this->endpoint, $payload);

            if ($response->getStatusCode() == 200) {
                return [
                    'code' => $code = (string) $response->getBody(),
                    'message' => $this->msgSendResponse($code),
                ];
            }
            throw CouldNotSendMobilyWsNotification::someErrorWhenSendingSms($response);
        } catch (RequestException $exception) {
            throw CouldNotSendMobilyWsNotification::couldNotSendRequestToMobilyWs($exception);
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
        $arraySendMsg[0] = 'لم يتم الاتصال بالخادم';
        $arraySendMsg[1] = 'تمت عملية الإرسال بنجاح';
        $arraySendMsg[2] = 'رصيدك 0 , الرجاء إعادة التعبئة حتى تتمكن من إرسال الرسائل';
        $arraySendMsg[3] = 'رصيدك غير كافي لإتمام عملية الإرسال';
        $arraySendMsg[4] = 'رقم الجوال (إسم المستخدم) غير صحيح';
        $arraySendMsg[5] = 'كلمة المرور الخاصة بالحساب غير صحيحة';
        $arraySendMsg[6] = 'صفحة الانترنت غير فعالة , حاول الارسال من جديد';
        $arraySendMsg[7] = 'نظام المدارس غير فعال';
        $arraySendMsg[8] = 'تكرار رمز المدرسة لنفس المستخدم';
        $arraySendMsg[9] = 'انتهاء الفترة التجريبية';
        $arraySendMsg[10] = 'عدد الارقام لا يساوي عدد الرسائل';
        $arraySendMsg[11] = 'اشتراكك لا يتيح لك ارسال رسائل لهذه المدرسة. يجب عليك تفعيل الاشتراك لهذه المدرسة';
        $arraySendMsg[12] = 'إصدار البوابة غير صحيح';
        $arraySendMsg[13] = 'الرقم المرسل به غير مفعل أو لا يوجد الرمز BS في نهاية الرسالة';
        $arraySendMsg[14] = 'غير مصرح لك بالإرسال بإستخدام هذا المرسل';
        $arraySendMsg[15] = 'الأرقام المرسل لها غير موجوده أو غير صحيحه';
        $arraySendMsg[16] = 'إسم المرسل فارغ، أو غير صحيح';
        $arraySendMsg[17] = 'نص الرسالة غير متوفر أو غير مشفر بشكل صحيح';
        $arraySendMsg[18] = 'تم ايقاف الارسال من المزود';
        $arraySendMsg[19] = 'لم يتم العثور على مفتاح نوع التطبيق';

        if (array_key_exists($code, $arraySendMsg)) {
            return $arraySendMsg[$code];
        }
        $message = "نتيجة العملية غير معرفه، الرجاء المحاول مجددا\n";
        $message .= 'الكود المرسل من الموقع: ';
        $message .= "{$code}";

        return $message;
    }
}
