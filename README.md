# Project for a course Software Arquiteture to FADERGS


##Prerequisites
- PHP >= 7.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- NodeJs
- Composer - Package manager for PHP
- NPM - Node package manager

## Installation
On the command prompt run the following commands:
```
 $ git clone https://github.com/leo-moliveira/fadergs-system-b.git
 $ cd fadergs-system-b
 $ composer install
 $ cp .env.example .env (edit it with your database information)
 $ php artisan jwt:secret
 $ php artisan migrate
 $ php -S localhost:8000 -t public/
```

## Official Documentation


## Contributing

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
