# PHPSupabase

PHPSupabase is a library written in php language, which allows you to use the resources of a project created in Supabase ([supabase.io](https://supabase.io)), through integration with its Rest API.

## About Supabase

Supabase is "The Open Source Firebase Alternative". Through it, is possible to create a backend in less than 2 minutes. Start your project with a Postgres Database, Authentication, instant APIs, realtime subscriptions and Storage.

## PHPSupabase Features

- Create and manage users of a Supabase project
- Manage user authentication (with email/password, magic links, among others)
- Insert, Update, Delete and Fetch data in Postgres Database (by Supabase project Rest API)
- A QueryBuilder class to filter project data in uncomplicated way

## Instalation & loading

PHPSupabase is available on [Packagist](https://packagist.org/packages/rafaelwendel/phpsupabase), and instalation via [Composer](https://getcomposer.org) is the recommended way to install it. Add the follow line to your `composer.json` file:

```json
"rafaelwendel/phpsupabase" : "1.0"
```

or run

```sh
composer require rafaelwendel/phpsupabase
```