RadioChat
========================

A fun chat app working with WebSockets.

Requirements
------------

  * PHP 7.2.24 or higher;
  * PostgreSQL PHP extension (optional)
  * Redis (redis-server)
  * [phpredis][1] library for PHP
  * and the [usual Symfony application requirements][2].

Installation
------------

Clone this repository and run:

```bash
$ composer install --no-interaction

$ npm install
```

Edit doctrine.yaml with your db credentials and run latest migrations:

```bash
$ ./bin/console doctrine:migrations:migrate
```

### phpredis

Run:

```bash
$ sudo apt install php-pear
$ sudo apt -y install php-dev
$ sudo pecl install redis
```

And then add `extension=redis.so` to your php.ini configuration file

Servers
-------

Make sure that both redis-server and WebSocket listener for Redis are running.

You can run redis-server with:

```bash
$ redis-server --protected-mode no
```

You can run WebSocket listener from node_modules folder:

```bash
$ node node_modules/socket-redis/bin/socket-redis.js --redis-host=localhost --socket-ports 8080
```

See [socket-redis][5] for more details about available options.

Usage
-----

There's no need to configure anything to run the application. If you have
installed the [Symfony client][4] binary, run this command to run the built-in
web server and access the application in your browser at <http://localhost:8000>:

```bash
$ cd /<app_dir>/
$ symfony serve
```

If you don't have the Symfony client installed, run `php bin/console server:run`.

You can also start the server with `php bin/console server:start *:8000` and stop it with `php bin/console server:stop`

Alternatively, you can [configure a web server][3] like Nginx or Apache to run
the application.

[1]: https://github.com/phpredis/phpredis
[2]: https://symfony.com/doc/current/reference/requirements.html
[3]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[4]: https://symfony.com/download
[5]: https://github.com/cargomedia/socket-redis#installation--configuration
