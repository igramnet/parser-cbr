# Parser of exchange rates from the site cbr.ru

## This code allows you to get exchange rates from the Central Bank of the Russian Federation

## How to use
Для ежедневного парсинга необходимо запустить планировщик Laravel (можно добавить конечно в планировщике Ubuntu, если разворачивать сразу в Docker).

Для этого запускаем в консоли команду **php artisan schedule:work**

Нам также необходимо запустить в консоли запустить команду **php artisan queue:listen --queue=cbr**, чтобы слушать и обрабатывать очередь (режимы бывают разные: work, listen).

Для первого запуска (чтобы спарсить данные за 180 дней) перейдите по ссылке http://127.0.0.1:8000/parse, которая запустит парсинг данных.

## Video manual

https://youtu.be/ceK2VGbpOwU
