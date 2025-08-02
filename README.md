# Freelance Time Tracking App

A freelance time-tracking application built with Laravel 12 and Vue.js, featuring client, project, and task management with hourly rates and payment tracking.

## Features

- Client Management (CRUD)
- Project Management (Planned)
- Task Management (Planned)
- Hourly Rate Tracking per Project (Planned)
- Payment Tracking for Projects (Planned)

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

## Usage

- Access the application in your browser at `http://localhost` (or your configured `APP_URL` in `.env`).
- Navigate to the `/clients` route to manage your clients.
