# MelaFreelance Time Tracking App

A freelance time-tracking application built with Laravel 12 and Vue.js, featuring client, project, and task management with hourly rates and payment tracking.

## Features

- Client Management
- Project Management
- Task Management
- Hourly Rate Tracking per Project
- Payment Tracking for Projects
- Multilingual UI (English & Italian) with a configurable currency

## Setup

1.  Clone the repository:
    ```bash
    git clone melasistema/clients-melasistema
    cd clients-melasistema
    ```
2.  Start Laravel Sail:
    ```bash
    ./vendor/bin/sail up -d
    ```
3.  Install PHP dependencies (inside Sail container):
    ```bash
    ./vendor/bin/sail composer install
    ```
4.  Install JavaScript dependencies (inside Sail container):
    ```bash
    ./vendor/bin/sail npm install
    ```
5.  Copy the `.env.example` file and configure your environment variables:
    ```bash
    cp .env.example .env
    ./vendor/bin/sail artisan key:generate
    ```
6.  Configure your database in `.env` and run migrations (inside Sail container):
    ```bash
    ./vendor/bin/sail artisan migrate
    ```
7.  Run the development server (inside Sail container):
    ```bash
    ./vendor/bin/sail npm run dev
    ```

## Accounts & registration

MelaFreelance ships as a **single-user** app: public sign-up is closed, and you
create the one owner account yourself. On a fresh install, provision it from the
CLI:

```bash
php artisan app:create-user
# or headless / scripted:
php artisan app:create-user --name="Jane Doe" --email=jane@example.com --password='a-strong-password'
```

The account is created already email-verified, so you can log in right away
without configuring a mail server.

### Running it for a team (multi-user)

If you want to self-host for several people and let them sign up, set the
following in your `.env` and re-cache config:

```dotenv
REGISTRATION_ENABLED=true
```

That opens the `/register` routes and shows a "Sign up" link on the login page.
Leave it unset (or `false`) to keep the app single-user.

## Language & currency

The entire interface is translated and driven by config, so a self-hoster
switches language and currency in `.env` without touching any Vue or PHP code.

### Language

Set the active language with the standard Laravel locale knob:

```dotenv
APP_LOCALE=it          # 'en' (default) or 'it'
APP_FALLBACK_LOCALE=en # used for any string missing in the active locale
```

**English** and **Italian** ship complete. Every user-facing string — the
clients/projects/tasks pages, the app sidebar and menus, all settings pages,
and the full authentication flow (login, registration, password reset, email
verification) — reads through the translation layer; there are no hardcoded
English strings in the app UI.

How it works:

- Copy lives in Laravel PHP lang files under `lang/{locale}/`, split by area
  (`common.php`, `clients.php`, `projects.php`, `tasks.php`, `trash.php`,
  `settings.php`, `auth.php`).
- The active locale's messages are shared to the frontend on every request and
  **deep-merged over the fallback locale**, so a key missing in Italian falls
  back to the English text — never a raw key.
- Vue components read them via a `useTranslations()` composable that mirrors
  Laravel's `__()`: `__('clients.title')`, with `:placeholder` substitution.

**Adding a language:** create a `lang/{code}/` directory (e.g. `lang/fr/`),
copy the files from `lang/en/`, translate the values, then set
`APP_LOCALE={code}`. Any untranslated key automatically falls back to English.

### Currency & number formatting

Currency and number/locale formatting are independent of the UI language:

```dotenv
MONEY_CURRENCY=EUR   # ISO 4217 currency code (default: EUR)
MONEY_LOCALE=it-IT   # Intl locale for number/currency formatting (default: it-IT)
```

These feed a shared money config that the frontend formatter reads, so amounts
and tracked time are rendered consistently across every page.

After changing any of these values, re-cache config:

```bash
php artisan config:clear   # or: php artisan config:cache
```

## Usage

- Access the application in your browser at `http://localhost` (or your configured `APP_URL` in `.env`).
- Navigate to the `/clients` route to manage your clients.
