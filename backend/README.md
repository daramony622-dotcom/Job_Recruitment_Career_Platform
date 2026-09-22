<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Telegram Login

Telegram login uses a one-time deep link. The user clicks Telegram Login, opens the bot, presses Start, confirms Yes, and the browser polls the token until the account is created or approved.

For the official Telegram Login Widget, configure `TELEGRAM_BOT_USERNAME` with the bot username without `@`. Register the exact public HTTPS frontend hostname with BotFather using `/setdomain` (for example, `jobs.example.com`). Set that same URL in `FRONTEND_URL`, set the API URL in the frontend `VITE_API_URL`, and add the frontend origin to `CORS_ALLOWED_ORIGINS`. Telegram cannot use `localhost` as the production widget domain.

For local development, run the Telegram update consumer in a second backend terminal:

```bash
php artisan telegram:poll
```

For production, configure Telegram to call `POST /api/telegram/webhook` and set `TELEGRAM_WEBHOOK_SECRET`. The webhook secret is enforced whenever it is configured. Do not run polling and a webhook for the same bot at the same time.

After deploying with the production `APP_URL`, configure the webhook from the backend directory:

```bash
php artisan telegram:set-webhook
```

To inspect the current webhook, call Telegram's `getWebhookInfo` API. To switch back to local polling, remove the webhook first:

```bash
php artisan telegram:set-webhook --remove
php artisan telegram:poll
```

### Windows SSL certificate setup

If polling reports `cURL error 60`, configure PHP with a trusted CA bundle. Find the active PHP configuration with:

```bash
php --ini
```

In the loaded `php.ini`, set both values to the same CA bundle path:

```ini
curl.cainfo = "C:\\php\\extras\\ssl\\cacert.pem"
openssl.cafile = "C:\\php\\extras\\ssl\\cacert.pem"
```

Alternatively, set the project-specific path in `.env`:

```env
TELEGRAM_CA_BUNDLE=C:\\php\\extras\\ssl\\cacert.pem
```

Restart the terminal after changing PHP configuration, then run `php artisan config:clear` and `php artisan telegram:poll`. Do not disable SSL verification.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
