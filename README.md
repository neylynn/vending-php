# PHP Vending Machine

## Quick start
1. Create MySQL DB `vending` and run `schema.sql`.
2. Edit `app/Config.php` for DB credentials.
3. Install deps: `composer install`.
4. Serve: `php -S localhost:8000 -t public public/index.php` or put under Apache `htdocs` with `.htaccess`.
5. Visit `/login`.

## API
- POST `/api/auth/login` (form-data: email, password) -> `{token}`
- GET `/api/products`
- GET `/api/products/{id}`
- POST `/api/products` (Admin, Bearer token)
- POST `/api/products/{id}/purchase` (Bearer token)

## Login Credential for admin
email : admin@example.com
password : Admin@123

## Login Credential for user
email : user@example.com
password : User@123

## Unit Testing
- create 'vending_testing' database
- run sql/schema.sql 
- update config in phpunit.xml

## How to test
- run this command via git bash
- cd vending-app
- vendor/bin/phpunit tests/ProductsControllerTest.php --testdox