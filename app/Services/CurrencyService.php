<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CurrencyService
{
    public function getCurrencies()
    {
        $app_url = env('EXCHANGE_API_URL');
        $app_id = env('EXCHANGE_API_ID');
        $fullUrl = $app_url . $app_id;
        $client = new Client();
        try {
            //code...
            $response = $client->request('GET', $fullUrl);
            $data = $response->getBody()->getContents();
            return $data;
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }
}
