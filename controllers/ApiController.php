<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\currency\CurrencyService;
use app\filters\TokenAuthFilter;
use app\helpers\DebugHelper;
use Yii;
use app\constants\Http;

class ApiController extends Controller {

    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";

    /**
     * Отключаем CSRF-валидацию
     */
    public $enableCsrfValidation = false;

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
        /**
         * Устанавливаем формат ответа JSON
         */
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new CurrencyService();
        $model->method = Yii::$app->request->get('method');

        if(!$model->validate()) {
            Yii::$app->response->statusCode = Http::CODE_BAD_REQUEST;
            return Yii::$app->response->data = [
                'status' => self::STATUS_ERROR,
                'code' => Http::CODE_BAD_REQUEST,
                'message' => $model->errors,
            ];
        }

        $model->http_method = Yii::$app->request->method;

        switch ($model->method) {
            case $model::METHOD_RATES:

                $model->scenario = $model::SCENARIO_RATES;

                if(!$model->validate()) {
                    Yii::$app->response->statusCode = Http::CODE_BAD_REQUEST;
                    return Yii::$app->response->data = [
                        'status' => self::STATUS_ERROR,
                        'code' => Http::CODE_BAD_REQUEST,
                        'message' => $model->errors,
                    ];
                }

                $model->currency = Yii::$app->request->get('currency');

                /**
                 * Возвращаем ответ
                 */
                Yii::$app->response->statusCode = Http::CODE_OK;
                return Yii::$app->response->data = [
                    'status' => self::STATUS_SUCCESS,
                    'code' => Http::CODE_OK,
                    'data' => $model->rates(),
                ];
            case $model::METHOD_CONVERT:

                $model->scenario = $model::SCENARIO_CONVERT;
                $model->attributes = Yii::$app->request->post();

                if ($model->validate()) {
                    Yii::$app->response->statusCode = Http::CODE_OK;
                    return [
                        'status' => self::STATUS_SUCCESS,
                        'code' => Http::CODE_OK,
                        'data' => $model->convert(),
                    ];
                }

                Yii::$app->response->statusCode = Http::CODE_BAD_REQUEST;
                return Yii::$app->response->data = [
                    'status' => self::STATUS_ERROR,
                    'code' => Http::CODE_BAD_REQUEST,
                    'message' => $model->errors,
                ];
        }
    }
}