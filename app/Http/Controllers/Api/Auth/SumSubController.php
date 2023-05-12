<?php

namespace App\Http\Controllers\Api\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class SumsubController extends Controller
{
    private $sumsubSecretKey;
    private $sumsubAppToken;
    private $sumsubTestBaseUrl;

    public function __construct()
    {
        // Set your Sumsub credentials here
        $this->sumsubSecretKey = env('SUMSUB_SECRET_KEY');
        $this->sumsubAppToken = env('SUMSUB_APP_TOKEN');
        $this->sumsubTestBaseUrl = 'https://api.sumsub.com';
    }

    public function createApplicant(Request $request)
    {
        $externalUserId = uniqid();
        $levelName = 'basic-kyc-level';

        $client = new Client();
        $ts = time();

        $url = '/resources/applicants?levelName=' . $levelName;
        $requestBody = [
            'externalUserId' => $externalUserId
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'X-App-Token' => $this->sumsubAppToken,
            'X-App-Access-Sig' => $this->createSignature($ts, 'POST', $url, json_encode($requestBody)),
            'X-App-Access-Ts' => $ts,
        ];

        try {
            $response = $client->request('POST', $this->sumsubTestBaseUrl . $url, [
                'headers' => $headers,
                'json' => $requestBody
            ]);

            $responseBody = json_decode($response->getBody());
            $applicantId = $responseBody->id;

            return response()->json(['applicantId' => $applicantId]);
        } catch (\Exception $e) {
            // Handle exceptions here
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAccessToken(Request $request)
    {
        $externalUserId = uniqid();
        $levelName = 'basic-kyc-level';
        $requestBody = [
            'externalUserId' => $externalUserId
        ];
        $client = new Client();
        $ts = time();
        $url = "/resources/accessTokens?userId=" . $externalUserId . "&levelName=" . $levelName;
        $request = new GuzzleRequest('POST', $this->sumsubTestBaseUrl . $url);
        
        $headers = [
            'Content-Type' => 'application/json',
            'X-App-Token' => $this->sumsubAppToken,
            'X-App-Access-Sig' => $this->createSignature($ts, 'POST', $url, json_encode($requestBody)),
            'X-App-Access-Ts' => $ts,
        ];

        try {
            $response = $client->request('POST', $this->sumsubTestBaseUrl . $url, [
                'headers' => $headers,
                'json' => $requestBody
            ]);
            $responseBody = $response->getBody();
            return json_decode($responseBody);
            return response()->json(['accessToken' => $responseBody]);
        } catch (\Exception $e) {
            // Handle exceptions here
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function createSignature($ts, $httpMethod, $url, $httpBody)
    {
        return hash_hmac('sha256', $ts . strtoupper($httpMethod) . $url . $httpBody, $this->sumsubSecretKey);
    }
}