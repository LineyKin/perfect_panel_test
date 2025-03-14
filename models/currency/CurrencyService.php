<?php

namespace app\models\currency;

define("BASIC_CURRENCY", "USD");
define("COMISSION_COEFFICIENT", 1.02); // наша комиисия - 2%

class CurrencyService extends CurrencyData {

    /**
     * Возвращает список валют, курс которых пересчитан с учётом комиссии
     */
    public function getList(): array {
        $data = $this->getOriginalList();

        if($data == []) {
            return [];
        }

        $list = [];

        foreach($data as $val) {
            $symbol = $val['symbol'];
            $rateUsd = $val['rateUsd'];
            $rate = $symbol == BASIC_CURRENCY ? $rateUsd : $this->recalcRate($rateUsd);
            $list[$symbol] = $rate;
        }

        unset($data);

        asort($list);

        return $list;
    }

    /**
     * Возвращает одной конкретной валюты с учётом комиссии
     */
    public function getCurrency(string $symbol): array {
        $cur = $this->getBySymbol($symbol);
        if($symbol == BASIC_CURRENCY) {
            return $cur;
        }

        return [
            $symbol => $this->recalcRate($cur['rateUsd']),
        ];
    }

    /**
     * Возвращает пересчитанный курс с учётом комиссии
     */
    private function recalcRate(float $rate): float {
        return $rate / COMISSION_COEFFICIENT;
    }

}