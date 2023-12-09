# Spaify - Laravel SPA Setup Package

Spaify is a Laravel package that scaffolds the project with Vue, Tailwindcss, InertiaJS, Ziggy, and Fontawesome. It automates the process of setting up the project for Single Page Application (SPA) development.

## Installation

Spaify is compatible with Laravel 10 and above.

Install the package using Composer:

```bash
composer require alnaggar/spaify
```

## Usage

Run the `spaify:scaffold` Artisan command to scaffold the Laravel project:

```bash
php artisan spaify:scaffold
```

The command performs the following tasks:

1. Installs npm dependencies.
2. Sets up Inertia middleware in the 'web' middleware group.
3. Configures default files such as `app.blade.php`, `app.css`, `app.js`, etc.

## License

Spaify is open-source software licensed under the [MIT license](LICENSE).
