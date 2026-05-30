#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi

    php bin/console -V

    if grep -q '^DATABASE_URL=' .env || [ -n "$DATABASE_URL" ]; then
        echo 'Waiting for database to be ready...'
        attempts_left=60

        until [ "$attempts_left" -eq 0 ] || database_error=$(php bin/console dbal:run-sql -q 'SELECT 1' 2>&1); do
            if [ "$?" -eq 255 ]; then
                attempts_left=0
                break
            fi

            sleep 1
            attempts_left=$((attempts_left - 1))
            echo "Still waiting for database to be ready... $attempts_left attempts left."
        done

        if [ "$attempts_left" -eq 0 ]; then
            echo 'The database is not up or not reachable:'
            echo "$database_error"
            exit 1
        fi

        echo 'The database is now ready and reachable.'

        if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
            php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
        fi
    fi

    echo 'PHP app ready.'
fi

exec docker-php-entrypoint "$@"
