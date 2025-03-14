<?php

namespace app\filters;

use app\helpers\DebugHelper;
use yii\base\ActionFilter;
use Yii;

define("INVALID_TOKEN_STATUS_CODE", 403);

class TokenAuthFilter extends ActionFilter
{
    public $token; // Фиксированный токен

    public function beforeAction($action)
    {
        $token = null;

        // Получаем токен из заголовка
        $authHeader = Yii::$app->request->headers->get('Authorization');
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Проверяем токен
        if ($token === null || $token !== $this->token) {
            // Устанавливаем формат ответа на JSON
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            Yii::$app->response->statusCode = INVALID_TOKEN_STATUS_CODE;

            // Возвращаем JSON-ответ
            Yii::$app->response->data = [
                'status' => 'error',
                'code' => INVALID_TOKEN_STATUS_CODE,
                'message' => 'Invalid token',
            ];

            // Прерываем выполнение дальнейших действий
            return false;
        }

        return parent::beforeAction($action);
    }
}