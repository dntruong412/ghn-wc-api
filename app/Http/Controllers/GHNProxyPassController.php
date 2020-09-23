<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GHNProxyPassController extends Controller
{
    public function callAPI(Request $request) {
        $url = env('GHN_API_ROOT') . $request->input('path');

        $query = $request->all();
        unset($query['path']);
        if (strtoupper($request->method()) == 'GET') {
            $url .= '?' . http_build_query($query);
        } else {
            $query = json_encode($query);
        }

        $token = $request->header('Token');
        if (empty($token)) {
            $token = env('GHN_API_TOKEN');
        }

        $requestHeaders = [
            "Content-Type: application/json",
            "Token: " . $token
        ];
        if (!empty($request->header('ShopId'))) {
            $requestHeaders[] = 'ShopId: ' . $request->header('ShopId');
        }

        $curlParams = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $request->method(),
            CURLOPT_HTTPHEADER     => $requestHeaders,
        ];
        if (strtoupper($request->method()) == 'POST') {
            $curlParams[CURLOPT_POSTFIELDS] = $query;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $curlParams);
        $response = curl_exec($curl);
        curl_close($curl);

        \Log::info('request', [
            'url'            => $url,
            'method'         => $request->method(),
            'query'          => $query,
            'requestHeaders' => $requestHeaders,
            'response'       => $response
        ]);

        return $response;
    }
}
