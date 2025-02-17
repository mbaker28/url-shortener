## URL Shortener

A simple URL shortener app inspired by [bit.ly](https://bit.ly).

It will take any valid URL and generate a random string of characters that redirects to the given URL.

Requirements:

- `Docker`

### Run

To run this in a local environment, clone it and run the app using docker:

`docker compose up -d --build`

Make sure you run database migrations the first time you run the app.

`docker compose run --remove-orphans php php bin/console doctrine:migrations:migrate`

The web app is running at [http://localhost:3000](http://localhost:3000)
