<?php declare(strict_types=1);

namespace app\models\currency;

use app\models\currency\CurrencyData;
use yii\base\Model;
use app\constants\Http;

class CurrencyService extends Model {

    public $currency;
    public $currency_from;
    public $currency_to;
    public $value;
    public $method;
    public $http_method;

    const BASIC_CURRENCY = "USD";
    const COMISSION = 0.02; // наша комиисия - 2%
    const MIN_CONVERT_VALUE = 0.01; // минимальное количество валюты для обмена

    const SCENARIO_RATES = 1;
    const SCENARIO_CONVERT = 2;

    const METHOD_RATES = 'rates';
    const METHOD_CONVERT = 'convert';

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['method'],
            self::SCENARIO_RATES => ['currency', 'http_method'],
            self::SCENARIO_CONVERT => ['currency_from', 'currency_to', 'value', 'http_method'],
        ];
    }

    public function rules()
    {
        return [
            // общие правила
            [['method'], 'required', 'on' => self::SCENARIO_DEFAULT],
            ['method', 'in', 'range' => [self::METHOD_CONVERT, self::METHOD_RATES], 'on' => self::SCENARIO_DEFAULT],

            // когда вызываем метод rates
            [['currency'], 'safe', 'on' => self::SCENARIO_RATES],
            ['http_method', 'in', 'range' => [Http::METHOD_GET], 'on' => self::SCENARIO_RATES],

            // когда вызываем метод convert
            [['currency_from', 'currency_to', 'value'], 'required', 'on' => self::SCENARIO_CONVERT],
            ['http_method', 'in', 'range' => [Http::METHOD_POST], 'on' => self::SCENARIO_CONVERT],
            ['value', 'number', 'min' => self::MIN_CONVERT_VALUE, 'on' => self::SCENARIO_CONVERT],

             // Проверка, что currency_from не равен currency_to
            ['currency_to', 'compare', 'compareAttribute' => 'currency_from', 'operator' => '!=', 'on' => self::SCENARIO_CONVERT],
        ];
    }

    public function rates(): array {
        if (is_null($this->currency)) {
            return $this->getList();
        }

        return $this->getCurrency($this->currency);
    }

    public function convert(): array {
        $currencyData = new CurrencyData();
        $data = $currencyData->getExchangePair($this->currency_from, $this->currency_to);
        unset($currencyData);

        // расчёт обменного курса
        $rateFrom = $data['currency_from']['rateUsd'];
        $rateTo = $data['currency_to']['rateUsd'];
        $exchangeRate = $this->recalcRate($rateFrom / $rateTo);

        // расчёт количества возвращаемой валюты, которую мы обменяли
        $convertedValue = $exchangeRate * $this->value;
        $convertedValue = $convertedValue >= 1 ? round($convertedValue, 2) : round($convertedValue, 10);

        return [
            'currency_from' => $this->currency_from,
            'currency_to' => $this->currency_to,
            'value' => $this->value,
            'converted_value' => $convertedValue,
            'rate' => $exchangeRate,
            //'data' => $data
        ];
    }

    /**
     * Возвращает список валют, курс которых пересчитан с учётом комиссии
     */
    private function getList(): array {
        $currencyData = new CurrencyData();
        $data = $currencyData->getList();
        unset($currencyData);

        if($data == []) {
            return [];
        }

        $list = [];

        foreach($data as $val) {
            $symbol = $val['symbol'];
            $rateUsd = (float) $val['rateUsd'];
            $rate = $symbol == self::BASIC_CURRENCY ? $rateUsd : $this->recalcRate($rateUsd);
            $list[$symbol] = $rate;
        }

        unset($data);

        asort($list);

        return $list;
    }

    /**
     * Возвращает данные одной конкретной валюты с учётом комиссии
     */
    private function getCurrency(string $symbol): array {
        $currencyData = new CurrencyData();
        $cur = $currencyData->getBySymbol($symbol);
        unset($currencyData);

        if($symbol == self::BASIC_CURRENCY) {
            return $cur;
        }

        return [
            $symbol => $this->recalcRate((float) $cur['rateUsd']),
        ];
    }

    /**
     * Возвращает пересчитанный курс с учётом комиссии
     */
    private function recalcRate(float $rate): float {
        return $rate * (1 - self::COMISSION);
    }

}