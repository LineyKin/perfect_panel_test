<?php

namespace app\controllers;


use yii\web\Controller;
use app\models\currency\Currency;
use app\filters\TokenAuthFilter;
use Yii;

class ApiController extends Controller {

    public function behaviors()
    {
        return [
            'tokenAuth' => [
                'class' => TokenAuthFilter::class,
                'token' => Yii::$app->params['fixedToken'],
            ],
        ];
    }

    public function actionV1()
    {
        $cur = new Currency();
        return json_encode($cur->getExchangePair("RUB", "USD"));
    }
}