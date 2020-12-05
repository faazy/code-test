# Laravel 

## Deployment Instructions

```
// update the code to latest master branch
git pull origin master

// install with composer
php composer install --no-dev
```

## Deployment Server Initial Setup Instructions

```
// copy the .env file
cp .env.example .env

// generate app key
php artisan key:generate

// edit the .env file with the server settings

// link the storage folder
php artisan storage:link
```

### Development Instructions

Run Basic Migration
```
php artisan migrate
```

Run PHP Development Sever
```
php artisan serve
```
