<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# Thunder Expense Tracker

## Project Overview
Thunder Expense Tracker is a Laravel 11 + Vue 3 (Vite) single-page application for managing retirement account expenses. It features a custom login/register UI and is designed for easy local development and future extensibility.

## Prerequisites
- PHP 8.2+
- Composer
- Node.js (v18+ recommended)
- npm
- MySQL (or compatible database)

## Setup Instructions

### 1. Clone the Repository
```
git clone <repo-url>
cd <project-folder>
```

### 2. Install PHP Dependencies
```
composer install
```

### 3. Install Node Dependencies
```
npm install
```

### 4. Environment Configuration
- Copy `.env.example` to `.env` and update as needed:
  - Set `APP_URL` to `http://thunderexpensetracker.com:8002`
  - Set your database credentials

### 5. Hosts File Setup (Windows)
- Open `C:\Windows\System32\drivers\etc\hosts` as Administrator
- Add this line:
  ```
  127.0.0.1   thunderexpensetracker.com
  ```

### 6. Database Setup
- Create a database (e.g., `thunder_expense_tracker`)
- Run migrations:
```
php artisan migrate
```
- (Optional) Seed the database as needed

### 7. Start Development Servers
- **Laravel Backend:**
  ```
  php artisan serve --host=thunderexpensetracker.com --port=8002
  ```
- **Vite Dev Server:**
  ```
  npm run dev
  ```

### 8. Access the App
- Visit: [http://thunderexpensetracker.com:8002/login](http://thunderexpensetracker.com:8002/login)

## Notes
- For production, update your environment and web server configs accordingly.
- If you change the local domain, update both `.env` and `vite.config.js`.
- If you encounter CORS or asset issues, ensure both servers are running and the hosts file is correct.

## Contact
For help, email: support@example.ph

---

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
