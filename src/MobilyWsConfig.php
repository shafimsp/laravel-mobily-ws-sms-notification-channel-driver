<?php

namespace Shafimsp\SmsNotificationChannel\MobilyWs;

use Shafimsp\SmsNotificationChannel\MobilyWs\Exceptions\CouldNotSendMobilyWsNotification;

class MobilyWsConfig
{
    /**
     * @var array Supported authentication methods
     */
    protected $authenticationMethods = [
        'key', 'password', 'auto',
    ];

    /**
     * @var string The authentication method
     */
    private $authMethod;

    /**
     * @var array
     */
    private $config;

    /**
     * MobilyWsConfig constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->setAuthenticationMethod($config);
        $this->validateCredentials();
    }

    public function getCredentials()
    {
        switch ($this->authMethod) {
            case 'password':
                return [
                  'mobile' => $this->mobile,
                  'password' => $this->password,
                ];
            case 'key':
                return [
                  'apiKey' => $this->key,
                ];
            case 'auto':
                return $this->getAutoCredentials();
        }
    }

    public function getAuthenticationMethod()
    {
        return $this->authMethod;
    }

    public function __get($name)
    {
        if ($name == 'request') {
            return $this->config['guzzle']['request'];
        }

        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }

    protected function setAuthenticationMethod($config)
    {
        if (isset($config['authentication'])) {
            if (in_array($config['authentication'], $this->authenticationMethods)) {
                return $this->authMethod = $config['authentication'];
            }

            throw CouldNotSendMobilyWsNotification::withErrorMessage(
                sprintf('Method %s is not supported. Please choose from: (key, password, auto)',
                    $config['authentication']
                )
            );
        }

        throw CouldNotSendMobilyWsNotification::withErrorMessage('Please set the authentication method in the mobilyws config file');
    }

    protected function getAutoCredentials()
    {
        if ($this->key) {
            return [
              'apiKey' => $this->key,
            ];
        }

        return [
            'mobile' => $this->mobile,
            'password' => $this->password,
        ];
    }

    protected function validateCredentials()
    {
        if (!isset($this->config['key']) && !isset($this->config['mobile'], $this->config['password'])) {
            throw CouldNotSendMobilyWsNotification::withErrorMessage('No credentials were provided. Please set your (mobile/password) or key in the config file');
        }
    }
}
