<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Find - Laravel Project

Find is a Laravel-based web application for managing users, reports (signalements), messages, and more. This project leverages Laravel's modular structure for scalability and maintainability.

## Prerequisites

- PHP >= 8.1
- Composer
- SQLite/MySQL/PostgreSQL (default: SQLite)
- Node.js & npm (for frontend assets)

## Installation

Clone the repository:

```bash
git clone <repository-url>
cd Find
```

Install PHP dependencies:

```bash
composer install
```

Install Node.js dependencies:

```bash
npm install
```

Copy the example environment file and configure your environment variables:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

## Database Setup

By default, the project uses SQLite. You can change the database connection in `.env`.

- To use SQLite, create the database file:

```bash
touch database/database.sqlite
```

- To use MySQL/PostgreSQL, update the DB settings in `.env`.

Run migrations to create the database tables:

```bash
php artisan migrate
```

(Optional) Seed the database with test data:

```bash
php artisan db:seed
```

## Running the Application

Start the local development server:

```bash
php artisan serve
```

Compile frontend assets:

```bash
npm run dev
```

## Project Structure

- `app/Modules/` - Modular structure for features (User, Message, Signalement, etc.)
- `database/migrations/` - Database migration files
- `routes/` - API and web routes
- `resources/views/` - Blade templates
- `public/` - Publicly accessible files

## Useful Artisan Commands

- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed the database
- `php artisan make:model ModelName -m` - Create a model and migration
- `php artisan make:controller ControllerName` - Create a controller
- `php artisan make:migration create_table_name` - Create a migration file

## Testing

Run the test suite:

```bash
php artisan test
```

## Troubleshooting

- Clear cache:
  ```bash
  php artisan config:cache
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  ```
- If you encounter permission issues, ensure the `storage/` and `bootstrap/cache/` directories are writable.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
