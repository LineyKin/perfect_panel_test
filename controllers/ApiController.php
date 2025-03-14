<?php

namespace app\controllers;


use yii\web\Controller;
use app\models\currency\CurrencyService;
use app\filters\TokenAuthFilter;
use Yii;

define("METHOD_RATES", "rates");
define("METHOD_CONVERT", "convert");

define("STATUS_SUCCESS", "success");
define("STATUS_ERROR", "error");

define("BAD_REQUEST_CODE", 400);
define("OK_CODE", 200);

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
        // Устанавливаем формат ответа JSON
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        /**
         * Возвращаем ошибку, если не указан параметр method
         */
        if(!isset($_GET['method'])) {
            Yii::$app->response->statusCode = BAD_REQUEST_CODE;
            return Yii::$app->response->data = [
                'status' => STATUS_ERROR,
                'code' => BAD_REQUEST_CODE,
                'message' => 'Method param not found',
            ];
        }

        /**
         * Возвращаем ошибку, если указан неизвестный параметр method
         */
        if(!in_array($_GET['method'], [METHOD_RATES, METHOD_CONVERT])) {
            Yii::$app->response->statusCode = BAD_REQUEST_CODE;
            return Yii::$app->response->data = [
                'status' => STATUS_ERROR,
                'code' => BAD_REQUEST_CODE,
                'message' => 'Unknown method ' . $_GET['method'],
            ];
        }

        $currencyService = new CurrencyService();

        if ($_GET['method'] == METHOD_RATES) {
            if(isset($_GET['currency'])) {
                $data = $currencyService->getCurrency($_GET['currency']);
            } else {
                $data = $currencyService->getList();
            }

            Yii::$app->response->statusCode = OK_CODE;
            return Yii::$app->response->data = [
                'status' => STATUS_SUCCESS,
                'code' => OK_CODE,
                'data' => $data,
            ];
        }

        // TODO доделать метод convert
    }
}