TEST TASK FOR TIFIA.COM
-------------------
[Test task](https://bitbucket.org/alexgutnik/test-task/src/master/)

[Source Repository](https://github.com/mikelozben/tifia.com)

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources

REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.6.0.


INSTALLATION
------------
~~~
composer install
./yii migrate/up
./yii referral/rebuild-partner-net
~~~

CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

ЗАМЕЧАНИЯ ПО БАЗЕ
-------------

в текущей архитектуре о целостности данных речи не идёт, сначала нужно выправить схемы таблиц, потом по необходимости добавлять 
внешние ключи.
    
### users
##### 1. client_uid не уникален, не понятно обусловлено это или нет. сейчас есть дубли (со статусом 0 и 1, вроде как один пользователь неактивен)
~~~
с client_uid 35950944:

"id","client_uid","email","gender","fullname","country","region","city","address","partner_id","reg_date","status"
7724,35950944,marilie.pouros@schultz.com,mrs.,Jessica Lebsack,MU,Kansas,South Estherside,"38534 Greenholt Greens Suite 855",81256769,2018-08-13 20:12:23,0
13379,35950944,maida.walsh@hotmail.com,mr.,Nathanael Langworth,IE,South Carolina,Heidiport,"577 Patricia Way",81256769,2018-08-31 02:33:12,1
~~~
~~~
с client_uid 70833669:

"id","client_uid","email","gender","fullname","country","region","city","address","partner_id","reg_date","status"
5995,70833669,wanda.hills@yahoo.com,mrs.,Sunny Hoppe,KP,South Carolina,South Fred,"583 Wintheiser Locks",28323359,2018-08-19 06:52:49,1
17164,70833669,barbara00@hotmail.com,mrs.,Leilani Zulauf,SV,Wisconsin,North Leilanimouth,"147 Zora Place Apt. 804",28323359,2018-08-18 21:15:27,0
~~~

если client_uid это именно uid, то надо добавить уникальность и использовать вместо id.
если же для разных пользователей может быть один клиент то это довольно странно смотрится, я бы проанализировал бизнес логику.
           
##### 2. сейчас возможен случай, когда один клиент входит в разные реферальные сети. не уверен что это укладывается в бизнес-логику.
более того, сейчас возможны циклы.

##### 3. gender и набор (country, region, city) я бы вынес в справочные таблицы а в users оставил ссылки на них. 

### accounts
##### 1. поля нужно сделать целочисленными


CONSOLE
-------------
### Partner Network
#### Rebuilds all network
~~~
referral/rebuild-partner-net
~~~

#### Rebuilds network with given client uid
~~~
referral/rebuild-partner-net-for-client 82824897
~~~

### Network visualization
#### All network without relations table
~~~
referral/full-net-without-relation-table
~~~
#### All network with relations table
~~~
referral/full-net-with-relation-table
~~~
#### Network for given client uid without relations table
~~~
referral/full-net-for-client-without-relation-table 82824897
~~~
#### Network for given client uid with relations table
~~~
referral/full-net-for-client-with-relation-table 82824897
~~~

### Test queries
#### Total volume calculation
~~~
referral/get-total-volume-for-referral-net-for-client-uid 82824897 '2010-01-01 00:00:00' '2030-01-01 00:00:00'
~~~
#### Total profit calculation
~~~
referral/get-total-profit-for-referral-net-for-client-uid 82824897 '2010-01-01 00:00:00' '2030-01-01 00:00:00'
~~~

#### Direct referrals number calculation
~~~
referral/get-direct-referrals-number-for-client-uid 82824897
~~~
#### All referrals number calculation
~~~
referral/get-all-referrals-number-for-client-uid 82824897
~~~
