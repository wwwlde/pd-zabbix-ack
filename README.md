# Acknowledge Zabbix events via PagerDuty

> Этот код написан на коленке, но выполняет свои функции. Если у Вас будут какие-то улучшения, или я чего-то не учел - буду рад получить от Вас сообщение :-)

### Как это работает

Со стороны PagerDuty создается Webhook, который при каждом событии или активности в админке/приложении будет дергать наш PHP-код. Код в свою очередь реагирует только на события "event.acknowledge" и запрашивая детали у PagerDuty API выполняет "acknowledgement" в Zabbix для нужного события.

### Как настроить

В админ панели PagerDuty создайте в  меню `SERVICE DIRECTORY -> ZABBIX -> EXTENSIONS` новый Webhook c типом `Generic V2 Webhook`. В поле `URL` укажите адрес к файлу `zabbix-ack.php` на своем сервере. Также стоит добавить `Custom Headers` с названием X-Auth-Key и каким-то значением, например полученным так:

```sh
$ cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w ${1:-32} | head -n 1
qXqDvsa6WXkW1Ddtoo46lnudE3aw3pzp
```

Это значение нужно также указать в `zabbix-ack.php` в переменной `$pd_webhook_auth`.

Далее идем в `My Profile -> User settings -> Create API User Token` и генерируем токен. Его прописываем в значение переменной `$pd_api_token`.

В файле `zabbix-ack.php` заполняем данные для доступа к zabbix:

```php
$zbxUrl   = 'http://127.0.0.1/';
$zbxUser  = 'admin';
$zbxPass  = '';
```

Вам также понадобится класс [Zabbix PHP API Client (using the JSON-RPC Zabbix API)](https://raw.githubusercontent.com/intellitrend/zabbixapi-php/master/src/ZabbixApi.php). Его нужно положить рядом с `zabbix-ack.php`.

### License

GNU General Public License v3.0

**Free Software, Hell Yeah!**
