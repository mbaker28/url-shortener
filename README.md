## URL Shortener

A simple URL shortener app written in [Symfony](https://symfony.com) using [Stimulus](https://stimulus.hotwired.dev/) and [Turbo](https://turbo.hotwired.dev).

## Docker

Build and start the Symfony Docker environment:

```sh
docker compose build --pull
docker compose up --wait
docker compose exec php php bin/console doctrine:migrations:migrate
```

The app is served at `https://localhost`.
