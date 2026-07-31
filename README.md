# MelaFreelance

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**Track your time, know what you're owed, and get paid.** MelaFreelance is a
self-hosted time-tracking app for freelancers. You keep a tidy list of clients,
the projects you do for them, and the tasks you work on — the app times your
work, computes what each project earns, and tracks who has paid you.

It runs as your own private app (single-user by default), built on Laravel 12 +
Vue 3. There's no account to sign up for and no data leaving your server — you
host it, you own it.

---

## What you can do

### Organise your work: Clients → Projects → Tasks

Everything hangs off a simple hierarchy that matches how freelance work actually
flows:

- **Clients** — the people or companies you work for.
- **Projects** — a piece of work for a client. Each project sets *how* it bills
  (see below).
- **Tasks** — the individual things you do on a project. Tasks are what you put
  the timer on and where your hours accumulate.

### Three ways a project earns — pick per project

You don't set a "project type"; the app figures out how a project bills from
what you fill in:

| You set… | Billing | What you're owed |
| --- | --- | --- |
| An **agreed fee** | **Fixed-price** | The fee, flat. Your tracked hours are shown for reference (and an effective hourly rate), but never change what's owed. |
| An **hourly rate** (no fee) | **Hourly** | Your tracked time × the rate. |
| Neither (rate left at 0) | **Non-billable** | Nothing. For personal work you still want to time — the money UI drops away and hours become the headline. |

Because it's derived, you can start a project hourly and later agree a fixed fee
just by filling the fee in — no migration, no re-typing.

### The always-on timer

A running timer bar lives in the app header on **every page**, not buried in one
screen — so you can start a task, navigate anywhere, and still see the clock
ticking.

- **One timer at a time.** Starting a task automatically stops whatever else was
  running and banks its time. As a freelancer you work one thing at a time, and
  this guarantees the same minute is never billed to two tasks.
- **It remembers what you were on.** When you stop, the bar stays put showing the
  last task you worked — with a **Resume** button and a link straight to that
  task — until you dismiss it. Change pages all you like; you won't lose your
  place.

### Payments: a ledger, not a checkbox

"Paid" isn't a yes/no flag. You record **payments** against a project as they
arrive — amount, date, and an optional note — and the app derives the rest:

- **Paid so far** — the sum of recorded payments.
- **Outstanding** — what's owed minus what's been paid.
- **Fully paid** — reached automatically once payments cover what's owed.

This handles deposits, staged/milestone payments, and part-payments naturally:
one project can have many payment rows.

### Marking work complete

Projects and tasks can be marked **complete** independently of payment — done is
done, whether or not the money's in yet. Two sensible rules are built in:

- Completing a task that's still timing **stops the timer first** (your seconds
  are banked, nothing is lost).
- A **fully-paid project can't be re-opened** — once it's delivered and paid,
  it's closed for good.

### Your dashboard

The landing page is your at-a-glance overview:

- **Outstanding** — total money still owed to you across all projects.
- **Received this month** vs. **all-time** — because payments carry a date, the
  money side can be windowed.
- **Hours tracked** — all-time.
- **Awaiting payment** — projects you've completed but haven't been fully paid
  for.
- **Recent payments** — your latest income, at a glance.

### Trash & recovery

Deleting a client, project, or task doesn't destroy it — it moves to **Trash**,
and its whole subtree goes with it (a trashed client hides its projects and
tasks and drops out of your totals). Restore it and everything comes back
exactly as it was. Only a permanent delete from Trash wipes it for good.

---

## Getting it running

MelaFreelance is meant to be cloned and run on your own machine or server. Two
ways to run it — pick one.

### Option A — Laravel Sail (Docker; matches production)

Best if you have Docker and want the same MySQL setup used in production.

```bash
git clone melasistema/clients-melasistema
cd clients-melasistema
cp .env.example .env

./vendor/bin/sail up -d                 # start the containers
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

The app runs at `http://localhost`.

### Option B — Local PHP + Node (SQLite, zero services)

Best for trying it out quickly — the default `.env` uses a file-based SQLite
database, so there's nothing else to install.

```bash
git clone melasistema/clients-melasistema
cd clients-melasistema
cp .env.example .env

composer install
npm install
php artisan key:generate
php artisan migrate

composer dev        # serves the app and the frontend together
```

> Requires PHP 8.4 and Node 22+.

---

## Create your account

MelaFreelance ships **single-user**: public sign-up is closed and you create the
one owner account yourself from the command line.

```bash
php artisan app:create-user
# …or scripted / non-interactive:
php artisan app:create-user --name="Jane Doe" --email=jane@example.com --password='a-strong-password'
```

The account is created **already verified**, so you can log in immediately
without setting up a mail server. Then open the app and go to **Clients** to
start.

### Running it for a small team (optional)

Want to self-host for several people and let them register? Set this in `.env`:

```dotenv
REGISTRATION_ENABLED=true
```

That opens the sign-up page and its "Sign up" link. Everyone's data stays
private to their own account. Leave it unset to keep the app single-user.

---

## Language & currency

The whole interface and all money/number formatting are driven by config — a
self-hoster switches language and currency in `.env`, without touching any code.

### Language

```dotenv
APP_LOCALE=it          # 'en' (default) or 'it'
APP_FALLBACK_LOCALE=en # used for any string missing in the active language
```

**English** and **Italian** ship complete — every screen, menu, and the full
login flow. A string missing in one language automatically falls back to the
other, so you never see a broken label.

**Adding your own language:** copy `lang/en/` to `lang/{code}/` (e.g.
`lang/fr/`), translate the values, and set `APP_LOCALE={code}`. Anything you
leave untranslated falls back to English.

### Currency & number formatting

Independent of the interface language:

```dotenv
MONEY_CURRENCY=EUR   # ISO 4217 currency code (default: EUR)
MONEY_LOCALE=it-IT   # locale used to format amounts & numbers (default: it-IT)
```

After changing any of these, refresh the cached config:

```bash
php artisan config:clear
```

---

## A note on your data

There is no cloud, no telemetry, and no shared database — MelaFreelance runs
entirely on the server you put it on, and your clients, hours, and earnings
never leave it. Back up your database the way you back up anything else you care
about.

---

## License

MelaFreelance is open source under the **MIT License** — free to use, modify,
and self-host. See [LICENSE](LICENSE) for the full text.

© 2026 Luca Visciola (Melasistema)

