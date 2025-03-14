<?php

namespace app\models\currency;

use yii\httpclient\Client;
use app\helpers\DebugHelper;

define("CURRENCY_API", "https://api.coincap.io/v2/rates");

class Currency {

    public function getExchangePair(string $from, $to): array {
        $list = $this->getList();
        if ($list == []) {
            return [];
        }

        $result = [
            'currency_from' => NULL,
            'currency_to' => NULL
        ];

        foreach($list['data'] as $val) {
            if ($val['symbol'] == $from) {
                $result['currency_from'] = $val;
            }

            if ($val['symbol'] == $to) {
                $result['currency_to'] = $val;
            }

            if(!is_null($result['currency_to']) && !is_null($result['currency_from'])) {
                return $result;
            }
        }

        return [];
    }

    public function getBySymbol(string $symbol): array {
        $list = $this->getList();
        if ($list == []) {
            return [];
        }

        foreach($list['data'] as $val) {
            if ($val['symbol'] == $symbol) {
                return $val;
            }
        }

        return [];
    }

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