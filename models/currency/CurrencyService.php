<?php declare(strict_types=1);

namespace app\models\currency;

use app\models\currency\CurrencyData;
use yii\base\Model;

class CurrencyService extends Model {

    public $currency;
    public $currency_from;
    public $currency_to;
    public $value;
    public $method;

    const BASIC_CURRENCY = "USD";
    const COMISSION_COEFFICIENT = 1.02; // наша комиисия - 2%

    const SCENARIO_RATES = 1;
    const SCENARIO_CONVERT = 2;

    const METHOD_RATES = 'rates';
    const METHOD_CONVERT = 'convert';

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['method'],
            self::SCENARIO_RATES => ['currency'],
            self::SCENARIO_CONVERT => ['currency_from', 'currency_to', 'value'],
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

            // когда вызываем метод convert
            [['currency_from', 'currency_to', 'value'], 'required', 'on' => self::SCENARIO_CONVERT],
        ];
    }

    public function rates(): array {
        if (is_null($this->currency)) {
            return $this->getList();
        }

        return $this->getCurrency($this->currency);
    }

    public function convert(): array {
        return [];
    }

    /**
     * Возвращает список валют, курс которых пересчитан с учётом комиссии
     */
    private function getList(): array {
        $currencyData = new CurrencyData();
        $data = $currencyData->getOriginalList();
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
        return $rate / self::COMISSION_COEFFICIENT;
    }

}