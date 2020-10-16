<?php

namespace WHMCS\Module\Registrar\SWHosting;

/**
 * Sample Registrar Module Simple API Client.
 *
 * A simple API Client for communicating with an external API endpoint.
 */
class RestApiClient
{
    const TEST_API_URL = 'https://ote-api.swhosting.com/v1/';
    const API_URL = 'https://api.swhosting.com/v1/';

    protected $url;
    protected $token;

    public function __construct($token, $isTest = false)
    {
        $this->url = ($isTest == 'on') ? self::TEST_API_URL : self::API_URL;
        $this->token = $token;
    }

    /**
     * Make external API call to registrar API.
     *
     * @param string $action
     * @param array $postfields
     *
     * @throws \Exception Connection error
     * @throws \Exception Bad API response
     *
     * @return array
     */
    public function call($endpoint, $method = 'GET', $data = [])
    {
        $url = $this->url . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$this->token
        ));
        if ($method != 'HEAD' && $method != 'GET') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$this->token,
                'Content-Type: application/json',
            ));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);

        $responseAPI = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($responseAPI);

        $this->log('SWHostingRegistrar','RestApiClient/call',[
            'url' => $url,
            'method' => $method,
            'http_code' => $http_code,
            'body' => $data,
        ],$responseAPI,$response,[]);

        if ($http_code < 200 || $http_code >= 300) {
            $message = (isset($response->message)) ? $response->message : '';
            if (is_object($message)) {
                $message = implode(" ",(array)$message);
            }

            throw new \Exception('Response Error: ' . $http_code . ' - ' . $message);
        }

        curl_close($ch);

        return $response;
    }

    private function log($module, $action, $requestString, $responeData, $processedData, $replaceVars)
    {
        if (function_exists('logModuleCall')) {
            logModuleCall($module,$action,$requestString,$responeData,$processedData,$replaceVars);
        }
    }

}