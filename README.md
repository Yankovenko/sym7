# Blog

## Start
1. Check requirements: **PHP 8.2**, module **SQLite** 
2. Create database: 
```bash
bin/console doctrine:database:create
```
3. Run migrate
```bash
bin/console doctrine:migrations:migrate
```
4. Start server
```bash
php -S localhost:8000 -t public
```

## Usage

[http://localhost:8000/post](http://localhost:8000/post) - Classic web form 

[http://localhost:8000/api/doc](http://localhost:8000/api/doc) - Swagger documentation


## Run Tests
```bash
bin/phpunit
```