<?php

namespace app\models\currency;

use yii\httpclient\Client;

define("CURRENCY_API", "https://api.coincap.io/v2/rates");

class Currency {

    public function getList(): array {
        $httpClient = new Client();

        // запрос на список курсов валют из стороннего сервиса
        $response = $httpClient->createRequest()
        ->setMethod('GET')
        ->setUrl(CURRENCY_API)
        ->send();

        if ($response->isOk) {
            return $response->data;
        }

        return [];
    }
}