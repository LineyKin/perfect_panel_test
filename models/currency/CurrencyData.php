<?php

namespace app\models\currency;

use yii\httpclient\Client;
use app\helpers\DebugHelper;
use app\constants\Http;
use yii\redis\Cache;
use Yii;

class CurrencyData {

    const CURRENCY_API = "https://api.coincap.io/v2/rates";

    public function getExchangePair(string $from, $to): array {
        $list = $this->getList();
        if ($list == []) {
            return [];
        }

        $result = [
            'currency_from' => NULL,
            'currency_to' => NULL
        ];

        foreach($list as $val) {
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

        foreach($list as $val) {
            if ($val['symbol'] == $symbol) {
                return $val;
            }
        }

        return [];
    }

    public function getList(): array {
        $cacheKey = 'list';

        // Получаем компонент кэша
        $cache = Yii::$app->cache;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $httpClient = new Client();

        // запрос на список курсов валют из стороннего сервиса
        $response = $httpClient->createRequest()
        ->setMethod(Http::METHOD_GET)
        ->setUrl(self::CURRENCY_API)
        ->send();

        if ($response->isOk) {
            // Сохраняем массив в кэш на 1 час (3600 секунд)
            $cache->set($cacheKey, $response->data['data'], 10);
            return $response->data['data'];
        }

        return [];
    }
}