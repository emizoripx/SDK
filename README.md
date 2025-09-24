# EMIZOR SDK
Issue invoices according to Bolivian fiscal regulation using EMIZOR services.

## Requirements
- Laravel ^11
- PHP ^8.2
- An account in [EMIZOR](https://emizor.com), with:
    - CLIENT_ID
    - CLIENT_SECRET
    - HOST (`PILOTO` | `PRODUCTION`)

## Installation
Using Composer in a Laravel project:
```sh
composer require emizor/sdk
```


## Test using Docker
```sh
    docker-compose up -d 
    docker-compose exec app composer install
    docker-compose exec app composer test
  ```

----
## Features
### Account Register
- Use to get access to service of EMIZOR SDK ACCOUNT_ID
- Obtain access token
----
## Changelog Conventions
- All new features and hotfixes merged into the [develop] branch should be added under [Unreleased].
- The [main] branch contains only official releases.
- Format:
    - [MAJOR.MINOR.PATCH] - YYYY-MM-DD
        - Added → New implementations (APIs, classes, methods, settings).
        - Changed → Changes in business logic or behavior.
        - Deprecated → Functionalities still work but are scheduled for removal.
        - Removed → Permanently removed functionalities.
        - Fixed → Bug fixes.
        - Security → Security vulnerability fixes.
