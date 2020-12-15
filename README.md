Yii2 SBM Client
===============
Обертка для libsmbclient

* Обеспечивает подключение к общей папке на Windows машине с Linux php приложения.
* Для работы требуется установить smbclient и php расширение libsmbclient
* https://github.com/eduardok/libsmbclient-php

Installation
------------

Предпочитаемый способ установки - [composer](http://getcomposer.org/download/).

Запустите:

```
php composer.phar require --prefer-dist dekado/yii2-smbclient "dev-master"
```

либо добавьте

```
"dekado/yii2-smbclient": "dev-master"
```

в секцию require вашего `composer.json` файла.


Usage
-----

После установки, настроить компонент:

```php
'smbclient' => [
    'class' => 'dekado\smbclient\SMBClient',
    'dirRoot' => 'smb://folder/folder/',
    'user' => 'userLogin',
    'password' => 'userPass',
],
```

Затем доступ к классу:
```
<?php Yii::$app->smbclient ?>
```
