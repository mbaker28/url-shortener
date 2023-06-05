## URL Shortener

A simple URL shortener app inspired by [bit.ly](https://bit.ly).

It will take any valid URL and generate a random string of characters that redirects to the given URL.

### Installation

Requirements:

- `PHP` >= 8.1
- `composer`
- `npm`
- A database connection.

### Run

To run this in a local environment, clone it and install dependencies:

```
git clone https://github.com/mbaker28/url-shortener.git
cd url-shortener/
composer install
npm run dev
```

You can then run the web application using docker:

`docker compose up -d --build`

The web app is running at [http://localhost](http://localhost)