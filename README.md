# CodaStore

A clothing e-commerce store built with Laravel 8: storefront, cart and checkout, customer accounts and an admin dashboard.

## Requirements

| Tool | Version | Notes |
|---|---|---|
| PHP | 8.1 or 8.2 | Laravel 8 does not support newer PHP versions without workarounds |
| Composer | 2.x | |
| MySQL / MariaDB | 5.7+ / 10.4+ | Database name `codastore` by default |
| Node.js | **20** (18-22 work) | See `.nvmrc`. Laravel Mix 6 does **not** run on Node 23+ |

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed      # creates tables, products and the admin account
php artisan storage:link
npm install
npm run dev                     # builds public/js and public/css from resources/
php artisan serve
```

- Storefront: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin/login (seeded account `admin@gmail.com` / `password`, local only)
- Emails are sent in the background. While testing emails, keep a worker running in its own terminal:
  `php artisan queue:work --queue=emails,default`
  (If it isn't running, emails wait in the queue and go out once it starts.)

### Background emails on a server

- Keep the worker running with Supervisor: see `deploy/supervisor/codastore-worker.conf`.
- No Supervisor (shared hosting)? Set `QUEUE_VIA_SCHEDULER=true` and run the scheduler every minute from cron:
  `* * * * * cd /path/to/codastore && php artisan schedule:run >> /dev/null 2>&1`
- After every deploy: `php artisan queue:restart`, so the worker picks up the new code.
- Admin > Failed emails lists emails that couldn't be sent, with Retry; the dashboard warns when emails are stuck.

### Front-end assets

Source files live in `resources/js` and `resources/sass`; `public/js` and `public/css` are **build output**. Always change the source and rebuild:

```bash
nvm use            # or install Node 20
npm run dev        # development build
npm run watch      # rebuild on change
npm run prod       # minified build for deployment
```

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database (see `phpunit.xml`), so they never touch your local data. The PHP `pdo_sqlite` extension must be enabled.

Continuous integration runs the same tests and a production asset build on every push and pull request (`.github/workflows/ci.yml`).

 ## Website link
          https://codastore.herokuapp.com
          - admin side
            https://codastore.herokuapp.com/admin/login
            user : user1@gmail.com 
            password : user@123
            role : employee




    

 
    
 


