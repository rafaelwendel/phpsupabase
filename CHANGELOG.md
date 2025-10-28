# Changes in PHPSupabase #

## 0.0.11 - 2025-10-28

- Add error handling for PostgREST responses in Database and QueryBuilder
    - Credit to @Snowbaha for contribution

## 0.0.10 - 2025-06-29

- Fix Prevent error if the uriBase (Service class) is not a valid URL (instead to have : "Warning: Undefined array key "scheme")
    - Credit to @Snowbaha for contribution

## 0.0.9 - 2025-05-28

- Add port to URI Base on `Service` to allow connect to local Supabase
    - Credit to @jpoto-dev for contribution
- Fix deprecation warnings in PHP 8.4
    - Credit to @carlobeltrame for contribution

## 0.0.8 - 2024-08-09

### Fixed

- Fix param tags on `Auth`, `Database`, `QueryBuilder` and `Service` classes
    - Credit to @Bartel-C8 for contribution
- Fix `getError` return type on `Auth`, `Database`, `QueryBuilder` and `Service` classes
    - Credit to @kevineduardo for contribution

## 0.0.7 - 2023-07-25

### Added

- Add `getHeader` method on Service class

### Changed

- Change the `executeDml` method on Database class to verify if `Prefer` header is defined (Or define the default value).
    - Suggested by @nkt-dk
- Change the `where` method on QueryBuilder class (Use `urlencode` function on `$value` variable)
    - Credit to @fred-derf for contribution

## 0.0.6 - 2023-06-15

### Added

- Create `limit` method on QueryBuilder class
    - Credit to @streeboga for contribution
- Create `limit` option on `createCustomQuery` method (Database class)
    - Credit to @streeboga for contribution

## 0.0.5 - 2023-05-17

### Changed

- Change the Service class constructor to accept the URI base without suffix. It is now possible to use a single service instance to create an auth object or database/querybuilder objects.
- Change the `getUriBase` method.
- Change on Auth, Database and QueryBuilder the methods that called `getUriBase`, setting now the suffix (`auth/v1` or `rest/v1`)

### Added

- Create `suffix` attribute in Auth, Database and QueryBuilder classes to set the respective suffix of URI (`auth/v1` or `rest/v1`)


## 0.0.4 - 2022-12-15

### Fixed

- fix: returns an array instead of an object in Database class

## 0.0.3 - 2022-10-10

### Added

- Create `getService` method in Database and QueryBuilder classes
- Create `response` attribute (with `getResponse` method) in Service class to set the Response of requests

## 0.0.2 - 2022-07-22

### Added

- Create `order` method in QueryBuilder class