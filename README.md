# MelaFreelance Time Tracking App

A freelance time-tracking application built with Laravel 12 and Vue.js, featuring client, project, and task management with hourly rates and payment tracking.

## Features

- Client Management
- Project Management
- Task Management
- Hourly Rate Tracking per Project
- Payment Tracking for Projects

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

## Usage

- Access the application in your browser at `http://localhost` (or your configured `APP_URL` in `.env`).
- Navigate to the `/clients` route to manage your clients.
