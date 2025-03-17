## Решение задания №1

```rb
SELECT
    u.id AS `ID`,
    CONCAT(u.first_name, ' ', u.last_name) AS `Name`,
    GROUP_CONCAT(DISTINCT b.author) AS `Author`,
    GROUP_CONCAT(b.name SEPARATOR ', ') AS `Book`
FROM users AS u
    INNER JOIN user_books AS ub ON u.id = ub.user_id
    INNER JOIN books AS b ON b.id = ub.book_id
WHERE TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 7 AND 17
    AND DATEDIFF(ub.return_date, ub.get_date) <= 14
GROUP BY u.id
    HAVING COUNT(DISTINCT b.author) = 1 AND COUNT(b.name) = 2;
```

## Решение задания №2

Клонируем код из репозитория
```rb
git clone git@github.com:LineyKin/perfect_panel_test.git
```

Переходим в корневую папку приложения
```rb
cd perfect_panel_test/
```

Собираем папку vendor на основе имеющегося файла composer.json
```rb
composer install
```

Дадим разрешение записывать в папку runtime кеш, логи и т.д.
```rb
chmod 777 runtime
```

Создаём env-файл. Токен авторизации будем хранить в переменной окружения
```rb
touch .env
```
Откроем этот env-файл в редакторе
```rb
nano .env
```

В редакторе пишем API_TOKEN="". В двойных кавычках указываем наш токен.
Согласно ТЗ это строка из 64 символов, состоящая из букв латинского алфавита любого регистра, цифр, а так же символов '_' и ')'. 
Из редактора можно выйти нажав ctrl+X.
***
Запускаем приложение
```rb
docker compose up -d
```

## Проверка задания №2

API для получения списка валют
```rb
http://localhost:8000/api/v1?method=rates
```

API для получения курса одной конкретной валюты
```rb
http://localhost:8000/api/v1?method=rates&currency=BTC
```

API для конвертации
```rb
http://localhost:8000/api/v1?method=convert
```

Тестировать удобно в postman. Последний API это POST-запрос. Параметры передаются в теле. Для заполнения тела перейдём во вкладку Body. Выберем в списке слева form-data или  x-www-form-urlencoded и заполняем ключи и значения.
