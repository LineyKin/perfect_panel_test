<?php

namespace app\filters;

use app\helpers\DebugHelper;
use yii\base\ActionFilter;
use Yii;
use app\constants\Http;

class TokenAuthFilter extends ActionFilter
{
    public $token; // токен из .env
    const TOKEN_PATTERN = '/^Bearer\s+([\w)]{64})$/';

    public function beforeAction($action)
    {
        $headerToken = null; // токен из заголовка

        // Получаем и валидируем токен из заголовка
        $authHeader = Yii::$app->request->headers->get('Authorization');
        if ($authHeader !== null && preg_match(self::TOKEN_PATTERN, $authHeader, $matches)) {
            $headerToken = $matches[1];
        }

        // Сверяем с токеном из .env
        if ($headerToken !== $this->token) {
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