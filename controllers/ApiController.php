<?php

namespace app\controllers;


use yii\web\Controller;
use app\models\currency\CurrencyService;
use app\filters\TokenAuthFilter;
use app\helpers\DebugHelper;
use Yii;

define("PARAM_METHOD_RATES", "rates");
define("PARAM_METHOD_CONVERT", "convert");
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
    private $paramMethod;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config = []);

        $this->httpMethod = Yii::$app->request->method;
        $this->paramMethod = Yii::$app->request->get('method');
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

        /**
         * Возвращаем ошибку, если не указан параметр method
         */
        if(is_null($this->paramMethod)) {
            Yii::$app->response->statusCode = BAD_REQUEST_CODE;
            return Yii::$app->response->data = [
                'status' => STATUS_ERROR,
                'code' => BAD_REQUEST_CODE,
                'message' => "param 'method' not found",
            ];
        }

        /**
         * Возвращаем ошибку, если указан неизвестный параметр method
         */
        if(!in_array($this->paramMethod, [PARAM_METHOD_RATES, PARAM_METHOD_CONVERT])) {
            Yii::$app->response->statusCode = BAD_REQUEST_CODE;
            return Yii::$app->response->data = [
                'status' => STATUS_ERROR,
                'code' => BAD_REQUEST_CODE,
                'message' => sprintf("Unknown method %s", $this->paramMethod),
            ];
        }

        $currencyService = new CurrencyService();

        switch ($this->paramMethod) {
            case PARAM_METHOD_RATES:

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

                $currencyParam = Yii::$app->request->get('currency');

                /**
                 * Возвращаем ответ
                 */
                Yii::$app->response->statusCode = OK_CODE;
                return Yii::$app->response->data = [
                    'status' => STATUS_SUCCESS,
                    'code' => OK_CODE,
                    'data' => $currencyService->rates($currencyParam),
                ];
            case PARAM_METHOD_CONVERT:

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

                /**
                 * Проверяем, что данные переданы
                 */
                if (empty($postParams)) {
                    Yii::$app->response->statusCode = BAD_REQUEST_CODE;
                    return [
                        'status' => STATUS_ERROR,
                        'code' => BAD_REQUEST_CODE,
                        'message' => 'No data in post request body',
                    ];
                }
            
                /**
                 * TODO присвоить ключу data данные метода convert()
                 */
                Yii::$app->response->statusCode = OK_CODE;
                return [
                    'status' => STATUS_SUCCESS,
                    'code' => OK_CODE,
                    'data' => $postParams,
                ];
        }
    }
}