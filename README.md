from docker

start encore watch dev srv

$ bin/yarn encore dev --watch


db startup
from docker
$ bin/console d:d:c
$ bin/console d:m:m
$ bin/console d:f:l

bin/console d:d:d --force && bin/console d:d:c && bin/console d:m:m -n && bin/console d:f:l -n

### Базовая авторизация в /редактор-знаний/

Реализовано через .htpasswd

Хэш пароля генерим через [CRYPT_STD_DES](https://www.php.net/manual/ru/function.crypt.php):
```sh
# в docker/
$ docker-compose exec php-fpm php -r 'echo crypt("password", "salt");'
```

## TODO

- [ ] оптимизнуть получение возможных значений признаков
- [ ] может быть когда-нибудь избавиться от дублирования кода в ScalarValues.js, IntValues.js и RealValues.js

## баги

- на http://localhost:8080/feature-possible-values при сабмите формы обратно приходят не совсем актуальные данные
- аналогично в http://localhost:8080/feature-normal-values


## тест-кейсы

### http://localhost:8080/feature-possible-values

- поменять верхнюю границу у №1 на 10.0 невкл
- добавить новое значение для №1: 10.0 вкл, 30.0 вкл
- добавить признаку №3 значение "хз"
- удалить у признака №13 значение "Плавный"
- удалить у признака №14 значение 0, 50
- добавить признаку №14 новое значение: 0, 0

### http://localhost:8080/feature-normal-values

- добавить к №1 30 50 невкл вкл
- чекнуть у №2 значение "прерывистый"
- анчекнуть у №2 значение "отсутствие"
- удалить у №5 единственное значение
- изменить у №11 верхнюю границу 0.02 невкл
- удалить у №14 единственное значение
- добавить к №14 0 0
