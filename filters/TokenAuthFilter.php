<?php

namespace app\filters;

use app\helpers\DebugHelper;
use yii\base\ActionFilter;
use Yii;
use app\constants\Http;

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
        if (is_null($token) || $token !== $this->token) {
            // Устанавливаем формат ответа на JSON
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            Yii::$app->response->statusCode = Http::CODE_FORBIDDEN;

            // Возвращаем JSON-ответ
            Yii::$app->response->data = [
                'status' => 'error',
                'code' => Http::CODE_FORBIDDEN,
                'message' => 'Invalid token',
            ];

            // Прерываем выполнение дальнейших действий
            return false;
        }

        return parent::beforeAction($action);
    }
}