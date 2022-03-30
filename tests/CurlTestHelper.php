<?php

namespace Tests;

class CurlTestHelper
{

    public function get($url, $headers, $params): array
    {
        return $this->request($url, $params, $headers);
    }

    public function post($url, $headers, $params): array
    {
        return $this->request($url, $params, $headers, 'POST');
    }

    public function put($url, $headers, $params): array
    {
        return $this->request($url, $params, $headers, 'PUT');
    }

    public function patch($url, $headers, $params): array
    {
        return $this->request($url, $params, $headers, 'PATCH');
    }

    public function delete($url, $headers, $params): array
    {
        return $this->request($url, $params, $headers, 'DELETE');
    }

    #[ArrayShape(['code' => "mixed", 'body' => "mixed", 'error' => "string"])]
    private function request($url, $params, $headers, $type = 'GET'): array
    {
        array_push($headers, 'Content-Type: application/json');

        $curl = curl_init();


        if ($type == 'GET') {
            if (count($params) > 0) {
                $url = $url . "?" . http_build_query($params);
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_ENCODING => '',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $type,
                CURLOPT_HTTPHEADER => $headers,
            ));
        } else {
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_ENCODING => '',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $type,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => $headers,
            ));
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return [
            'code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'body' => json_decode($response, true),
            'error' => @curl_error($curl)
        ];
    }

}