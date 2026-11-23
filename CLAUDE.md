# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

CodaStore is a clothing e-commerce store: storefront, cart and checkout, customer accounts and an admin dashboard. Laravel 13 on PHP 8.4+ (the app still uses the Laravel 8 layout: `app/Http/Kernel.php`, `app/Console/Kernel.php`, `RouteServiceProvider`).

## Commands

```bash
php artisan test                                   # all tests (in-memory SQLite, see phpunit.xml)
php artisan test --filter=ManualOrdersTest         # one test class
php artisan test --filter=test_method_name         # one test method
vendor/bin/phpunit                                 # what CI runs

npm run dev | npm run watch | npm run prod         # Laravel Mix build (Node 20, see .nvmrc; Mix 6 breaks on Node 23+)

php artisan queue:work --queue=emails,default      # needed locally for emails to actually send
php artisan migrate --seed                         # seeded admin: admin@gmail.com / password
```

Tests need the PHP `pdo_sqlite` extension. CI (`.github/workflows/ci.yml`) runs PHPUnit on PHP 8.4 and a production asset build.

## Front-end assets

`resources/js` and `resources/sass` are the source; `public/js` and `public/css` are **build output** (committed). Change the source, then rebuild with `npm run dev` / `npm run prod`, and commit both. Mix entry points are in `webpack.mix.js`: `app.js`/`app.scss` for the storefront, `main.js`/`main.scss` for the admin. `docs/REDESIGN.md` is the UI design spec (tokens, components).

## Architecture

**Routing.** Almost everything is in `routes/web.php`: storefront routes, then `/admin` routes under `auth:admin`. `routes/api.php` is a token API. `routes/admin.php` is dead code (not loaded by `RouteServiceProvider`).

**Two auth guards.** `web` (customers, `App\Models\User`, checkout requires `verified`) and `admin` (staff, `App\Models\Admin`). Admin areas are gated with the `permission:<key>` route middleware (`AdminPermission`), backed by `Admin::PERMISSIONS` and `Admin::hasPermission()`; administrators pass every check, `staff` is administrator-only.

**Controllers** are grouped by area: `Store/` (storefront), `Admin/`, `Auth/` (with `Auth/User/` for customers), `Api/`. Controllers stay thin; logic lives in `app/Services` (older flat `*Services.php` classes, newer ones grouped into `Orders/`, `Products/`, `Payments/`, `Reports/`, `Emails/`).

**Orders.** `Services/Orders/OrderBuilder::create()` is the single path that creates an order, used by both website checkout (`PlaceOrderServices`) and admin manual orders (`ManualOrders`). It runs in a transaction, locks and takes stock per variant, and throws `OutOfStockException`. Cancel / return / refund are separate services in `Services/Orders/`.

**Stock.** Two levels of quantity: `Stock` (product total) and `ProductSku.qty` (per variant); both must change together. Damaged stock is kept apart from sellable stock. Every stock change must be recorded in the history via `Services/Products/StockMovements` (`record()` for a known change, `snapshot()` + `recordDiff()` around arbitrary edits). `StockLedger::restore()` puts stock back for cancels/returns. Observers on `Order`, `Product`, `Stock` and `ProductSku` are registered in `AppServiceProvider`.

**Payments.** `App\Contracts\PaymentGateway` is bound to `Services/Payments/PayPalGateway` in `AppServiceProvider`; cash on delivery is `Services/Payments/CashOnDelivery`. Tests swap in `tests/Fakes/FakePaymentGateway` with `$this->app->instance(PaymentGateway::class, new FakePaymentGateway)`.

**Emails.** Every store mailable uses `Mail/Concerns/SendsInBackground`: queued on the `emails` queue, 3 tries with backoff, and dispatched only after the DB transaction commits (`after_commit` in `config/queue.php`). Failed emails are surfaced in Admin > Failed emails (`Services/Emails/EmailQueue`). On hosting without Supervisor, `QUEUE_VIA_SCHEDULER=true` makes the scheduler run the worker every minute. The scheduler also runs `store:low-stock-digest` (daily) and `store:cart-reminders` (hourly).

**Helpers.** `app/Helpers/upload.php` and `app/Helpers/query.php` are autoloaded global functions.

## Tests

Feature tests (`tests/Feature`) cover most behaviour. `tests/TestCase.php` has shared builders: `seedStoreBasics()` (categories, size attribute, settings the layouts need) and `createProduct()` (published product with stock and optional S/M/L/XL variants); use them instead of building fixtures by hand. phpunit.xml sets `QUEUE_CONNECTION=sync` and `MAIL_MAILER=array`.

## Plan documents

The `docs/*_PLAN.md` files (`FEATURES_PLAN.md`, `PAYMENTS_PLAN.md`, `MANUAL_ORDERS_PLAN.md`, etc.) record the design and reasoning behind each feature. Read the relevant one before changing that area. `docs/FEATURES_PLAN.md` sets the working rules: database changes only through reversible migrations that are safe on a live database, every change ships with tests, and one commit per item.
