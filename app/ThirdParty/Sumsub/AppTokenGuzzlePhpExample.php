<?php

namespace App\ThirdParty\Sumsub;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class AppTokenGuzzlePhpExample
{
    private string $SUMSUB_SECRET_KEY;
    private string $SUMSUB_APP_TOKEN;
    private string $SUMSUB_TEST_BASE_URL;

    public function __construct()
    {
        $this->SUMSUB_SECRET_KEY = config('sumsub.secret');
        $this->SUMSUB_APP_TOKEN = config('sumsub.app_token');
        $this->SUMSUB_TEST_BASE_URL = config('sumsub.base_url');
    }

    public function createApplicant($externalUserId, $levelName)
        // https://developers.sumsub.com/api-reference/#creating-an-applicant
    {
        $requestBody = [
            'externalUserId' => $externalUserId
        ];

        $url = '/resources/applicants?levelName=' . $levelName;
        $request = new Request('POST', $this->SUMSUB_TEST_BASE_URL . $url);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody(GuzzleHttp\Psr7\Stream(json_encode($requestBody)));

        $responseBody = $this->sendHttpRequest($request, $url)->getBody();
        return json_decode($responseBody)->{'id'};
    }

    public function sendHttpRequest($request, $url)
    {
        $client = new Client();
        $ts = time();

        $request = $request->withHeader('X-App-Token', $this->SUMSUB_APP_TOKEN);
        $request = $request->withHeader(
            'X-App-Access-Sig',
            $this->createSignature($ts, $request->getMethod(), $url, $request->getBody())
        );
        $request = $request->withHeader('X-App-Access-Ts', $ts);

        // Reset stream offset to read body in `send` method from the start
        $request->getBody()->rewind();

        try {
            $response = $client->send($request);

            //echo $response->getStatusCode() . '\n';

            if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201) {
                // https://developers.sumsub.com/api-reference/#errors
                // If an unsuccessful answer is received, please log the value of the "correlationId" parameter.
                // Then perhaps you should throw the exception. (depends on the logic of your code)
            }
        } catch (GuzzleException $e) {
          Log::error($e->getMessage());
        }

        return $response;
    }

    private function createSignature($ts, $httpMethod, $url, $httpBody)
    {
        return hash_hmac('sha256', $ts . strtoupper($httpMethod) . $url . $httpBody, $this->SUMSUB_SECRET_KEY);
    }

//    public function addDocument($applicantId)
//        // https://developers.sumsub.com/api-reference/#adding-an-id-document
//    {
//        $metadata = ['idDocType' => 'PASSPORT', 'country' => 'GBR'];
//        $file = __DIR__ . '/resources/images/sumsub-logo.png';
//
//        $multipart = new MultipartStream([
//            [
//                "name" => "metadata",
//                "contents" => json_encode($metadata)
//            ],
//            [
//                'name' => 'content',
//                'contents' => fopen($file, 'r')
//            ],
//        ]);
//
//        $url = "/resources/applicants/" . $applicantId . "/info/idDoc";
//        $request = new GuzzleHttp\Psr7\Request('POST', self::SUMSUB_TEST_BASE_URL . $url);
//        $request = $request->withBody($multipart);
//
//        return $this->sendHttpRequest($request, $url)->getHeader("X-Image-Id")[0];
//    }
//
//    public function getApplicantStatus($applicantId)
//        // https://developers.sumsub.com/api-reference/#getting-applicant-status-api
//    {
//        $url = "/resources/applicants/" . $applicantId . "/requiredIdDocsStatus";
//        $request = new GuzzleHttp\Psr7\Request('GET', self::SUMSUB_TEST_BASE_URL . $url);
//
//        return $responseBody = $this->sendHttpRequest($request, $url)->getBody();
//        return json_decode($responseBody);
//    }

//    public function getApplicantsData()
//    {
//        $url = "/resources/applicants/62999273f2c75d00013bbc61/one";
//        $request = new GuzzleHttp\Psr7\Request("GET", self::SUMSUB_TEST_BASE_URL . $url);
//        return $this->sendHttpRequest($request, $url)->getBody();
//    }

    public function getAccessToken($externalUserId, $levelName)
        // https://developers.sumsub.com/api-reference/#access-tokens-for-sdks
    {
        $url = "/resources/accessTokens?userId=" . $externalUserId . "&levelName=" . $levelName;
        $request = new Request('POST', $this->SUMSUB_TEST_BASE_URL . $url);

        return $this->sendHttpRequest($request, $url)->getBody();
    }
}
