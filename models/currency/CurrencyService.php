<?php

namespace app\models\currency;

define("BASIC_CURRENCY", "USD");
define("COMISSION_COEFFICIENT", 1.02); // наша комиисия - 2%

class CurrencyService extends CurrencyData {

    public function getList(): array {
        $data = $this->getOriginalList();

        if($data == []) {
            return [];
        }

        $list = [];

        foreach($data as $val) {
            $rate = $val['symbol'] == BASIC_CURRENCY ? 1 : $val['rateUsd'] / COMISSION_COEFFICIENT;
            $list[$val['symbol']] = $rate;
        }

        asort($list);

        return $list;
    }

    public function getCurrency(string $symbol): array {
        $cur = $this->getBySymbol($symbol);
        if($symbol == BASIC_CURRENCY) {
            return $cur;
        }

        $cur['rateUsd'] /= COMISSION_COEFFICIENT;

        return [
            $symbol => $cur['rateUsd']
        ];
    }

}