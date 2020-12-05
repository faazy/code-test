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

### Testing API

``
http://127.0.0.1:8000/api/bet [Post method]
``

##### Request Body
```
{
    "player_id": 1,
    "stake_amount": 99.99,
    "selections": [
        {
            "id": 1,
            "odds": 100
        },
        {
            "id": 2,
            "odds": 2
        }
    ]
}
```
##### Error Response

```
{
    "errors": [
        {
            "code": 9,
            "message": "Maximum win amount is 20000"
        },
        {
            "code": 11,
            "message": "Insufficient balance"
        }
    ],
    "selections": [
        {
            "id": 1,
            "errors": [
                {
                    "code": 7,
                    "message": "Maximum odds are 10000"
                }
            ]
        }
    ]
}
```
