## About YouTube parser

YouTube parser is a console application.

    Инструкция к запуску:

- Должен быть создан json файл для инициализации по api google в директории "app/Console/Storages"
- В корневой директории ввести "docker-compose up -d" для создания приложения
- Зайти внутрь контейнера "docker-compose exec php bash"
- Внутри контейнера установить зависимости composer "composer install"
- В корне проекта создать .env файл, можно просто скопировать .env-example
- Внутри контейнера установить artisan key "php artisan key:generate"
- Внутри контейнера запустить скрипт "php artisan parser:start"
- Запрашиваемые значение rangeId и spreadsheetId по-умолчанию установлены на таблице из задания

## Contacts

Send an e-mail to Kirill Zorin via [zosya43nax@gmail.com](mailto:zosya43nax@gmail.com) or search me on telegram: @zorink