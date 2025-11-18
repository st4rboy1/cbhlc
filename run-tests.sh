#!/bin/bash
php -r "require __DIR__.'/vendor/autoload.php'; (Dotenv\Dotenv::createImmutable(__DIR__))->load();"
vendor/bin/sail artisan test