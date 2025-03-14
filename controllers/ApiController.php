<?php

namespace app\controllers;


use yii\web\Controller;
use app\models\currency\Currency;

class ApiController extends Controller {

    public function actionV1()
    {
        $cur = new Currency();
        return json_encode($cur->getList());
    }
}