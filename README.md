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
