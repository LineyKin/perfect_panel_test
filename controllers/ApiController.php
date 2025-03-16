<?php

namespace app\controllers;


use yii\web\Controller;
use app\models\currency\CurrencyService;
use app\filters\TokenAuthFilter;
use app\helpers\DebugHelper;
use Yii;

define("HTTP_METHOD_GET", "GET");
define("HTTP_METHOD_POST", "POST");

define("STATUS_SUCCESS", "success");
define("STATUS_ERROR", "error");

define("BAD_REQUEST_CODE", 400);
define("OK_CODE", 200);

class ApiController extends Controller {

    /**
     * Отключаем CSRF-валидацию
     */
    public $enableCsrfValidation = false;

    private $httpMethod;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config = []);

        $this->httpMethod = Yii::$app->request->method;
    }

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
            $errors = $model->errors;
            Yii::$app->response->statusCode = BAD_REQUEST_CODE;
            return Yii::$app->response->data = [
                'status' => STATUS_ERROR,
                'code' => BAD_REQUEST_CODE,
                'message' => $errors,
            ];
        }

        switch ($model->method) {
            case $model::METHOD_RATES:

                /**
                 * Проверяем, что это GET-запрос
                 */
                if($this->httpMethod != HTTP_METHOD_GET) {
                    Yii::$app->response->statusCode = BAD_REQUEST_CODE;
                    return Yii::$app->response->data = [
                        'status' => STATUS_ERROR,
                        'code' => BAD_REQUEST_CODE,
                        'message' => sprintf("Unaleble method %s. Need method %s",$this->httpMethod, HTTP_METHOD_GET),
                    ];
                }



                $model->scenario = $model::SCENARIO_RATES;
                $model->currency = Yii::$app->request->get('currency');

                /**
                 * Возвращаем ответ
                 */
                Yii::$app->response->statusCode = OK_CODE;
                return Yii::$app->response->data = [
                    'status' => STATUS_SUCCESS,
                    'code' => OK_CODE,
                    'data' => $model->rates(),
                ];
            case $model::METHOD_CONVERT:

                /**
                 * Проверяем, что это POST-запрос
                 */
                if($this->httpMethod != HTTP_METHOD_POST) {
                    Yii::$app->response->statusCode = BAD_REQUEST_CODE;
                    return Yii::$app->response->data = [
                        'status' => STATUS_ERROR,
                        'code' => BAD_REQUEST_CODE,
                        'message' => sprintf("Unaleble method %s. Need method %s",$this->httpMethod, HTTP_METHOD_POST),
                    ];
                }

                /**
                 * Получаем данные из тела POST-запроса
                 */
                $postParams = Yii::$app->request->post();

                $model->scenario = $model::SCENARIO_CONVERT;
                $model->attributes = $postParams;

                if ($model->validate()) {
                     /**
                     * TODO присвоить ключу data данные метода convert()
                     */
                    Yii::$app->response->statusCode = OK_CODE;
                    return [
                        'status' => STATUS_SUCCESS,
                        'code' => OK_CODE,
                        'data' => $postParams,
                    ];
                } else {
                    // проверка не удалась:  $errors - это массив содержащий сообщения об ошибках
                    $errors = $model->errors;

                    Yii::$app->response->statusCode = BAD_REQUEST_CODE;
                    return Yii::$app->response->data = [
                        'status' => STATUS_ERROR,
                        'code' => BAD_REQUEST_CODE,
                        'message' => $errors,
                    ];
                }
        }
    }
}