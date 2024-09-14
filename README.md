# Blog

In this project I 
* installed symphony 7 
* created the Post entity 
* configured work with JWT 
* created 2 API controllers for authorization and operations with Post 
* connected the documentation of Swager (Nelmio) `/api/doc/` using attribute notations from the entity and controllers 
* added an API error interceptor [ApiExceptionSubscriber.php](src%2FEventSubscriber%2FApiExceptionSubscriber.php)
* modified the classic forms and a full test for them, then I realized that this was not required [PostControllerTest.php](tests%2FController%2FPostControllerTest.php)
* configured a limiter for requests
* created a unit test for the method of forming search conditions [PostRepositoryTest.php](tests%2FPostRepositoryTest.php)

## Start
1. Check requirements: **PHP 8.2**, module **SQLite** 
2. Install dependencies
```bash
coposer install
```
3. Create database: 
```bash
bin/console doctrine:database:create
```
4. Run migrate
```bash
bin/console doctrine:migrations:migrate
```
5. Generate JWT public and private keys
```bash
php bin/console lexik:jwt:generate-keypair
```
5. Start server
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