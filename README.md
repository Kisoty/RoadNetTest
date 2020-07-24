Небольшая дока по API.

###1. Получение списка отзывов
Метод принимает GET-запрос с необязательными параметрами sort (сортировка),
 page (номер страницы) и limit (количество полей на странице).
Page и limit являются целыми числами. Sort предоставляет возможность сортировки
 по дате и рейтингу. Пишется в формате sort=date:asc|desc,rate:asc|desc. asc - 
 сортировка по возрастанию, desc - по убыванию. При указании обоих параметров 
 приоритетная сортировка по рейтингу (ибо по дате смысла нет приоритет делать).
 По дефолту проводится сортировка по убыванию даты (свежие отзывы выше). 
 Page и limit по дефолту 1 и 10 соответственно.
#####Пример запроса
>http://localhost:8080/api/v1/reviews?sort=date:asc,rate:desc&page=2&limit=1
#####Пример успешного ответа
```json
{
    "0": {
        "id": "2",
        "name": "alex2",
        "rating": "3",
        "first_ref": "vk.com"
    },
    "1": {
        "id": "1",
        "name": "alex1",
        "rating": "3",
        "first_ref": "vk.com"
    },
    "success": true
}
```

###2. Получение отзыва по id
Метод принимает GET-запрос с обязательным параметром id и необязательным параметром
fields (дополнительные поля). Fields перечисляются через запятую. 
Доступны 3 дополнительных поля: id, review (полное описание), refs (все ссылки).

#####Пример запроса
>http://localhost:8080/api/v1/reviews?id=1&fields=refs,id,review
#####Пример успешного ответа
```json
{
    "name": "alex1",
    "rating": "3",
    "first_ref": "vk.com",
    "refs": "vk.com&caban.caban&smth.ru",
    "id": "1",
    "review": "aaaaaaaaaa",
    "success": true
}
```
###3. Создание отзыва
Метод принимает PUT-запрос. Тело запроса: json с четырьмя обязательными полями: 
name (имя пользователя), review (текст отзыва), rate (рейтинг),refs (не более 3ех ссылок на картинки).
#####Пример запроса
>http://localhost:8080/api/v1/reviews/
```json
{
    "name": "alex1",
    "review": "aaffaaaaaaaa",
    "refs": "vk.com&caban.ru&smth.ru",
    "rate": 3
}
```
#####Пример успешного ответа
```json
{
    "success": true,
    "id": "3"
}
```
Также все методы возвращают bool поле success - признак успешности вызова метода. При возвращении
 "success": false также возвращает поле 'message', в котором описана ошибка.
 Возможно, все информативные возвращаемые поля стоило выделить отдельное свойство data, но
  тут сложно сказать, как правильно. Как говорит мой опыт, по разному делают.