# CodaStore: feature implementation plan

Status: **draft for review. Nothing here is implemented yet.**
Based on the code audit (routes, models, services, checkout flow) done on 2026-11-17.

---

## 0. Ground rules

- **Branching:** merge the open `ui-redesign` branch into `main` first. Then one branch per phase (`phase-a-payments`, `phase-b-emails`, ...), each ending in a PR.
- **Every item ships with tests** (Feature tests with the existing model factories; see Phase F for the test setup, which should be done *first*).
- **Database changes are migrations only**, reversible, and safe to run on a live database. Anything destructive (dropping card columns) is a separate migration with a documented backup step.
- **One commit per item** with a message that says what and why.
- **JavaScript:** until Node 18/20 is set up (Phase F-1), JS edits are made in both `resources/js` and the compiled `public/js/app.js`. After F-1, rebuild from source and delete that duplication.

## 1. Findings that drive the plan

| # | Finding | Evidence |
|---|---|---|
| F1 | Card numbers and CVCs are stored as plain text | `user_payment_options`: `card_number`, `card_cvc`, `card_expire_date` are `string` columns, no encryption cast |
| F2 | The card number is also copied into every order payment | `PlaceOrderServices::storeOrderPayment` sets `reference_number = request('card_number')` |
| F3 | No payment is taken; payment is recorded as `paid` unconditionally | `storeOrderPayment` hardcodes `status => 'paid'`, provider `'card'` |
| F4 | PayPal is half-built | `srmklive/paypal` installed, `config/paypal.php` exists, `PaymentController` is a demo with a hardcoded `100.00` and no link from checkout |
| F5 | Customer cancel writes `canceled`; everything else uses `cancelled`; stock is not restored | `OrderServices::cancel` |
| F6 | Stock is subtracted at order time without checking it | `PlaceOrderServices::storeOrderItem` -> `Stock::decrement` |
| F7 | The order error path returns HTTP 200 | `PlaceOrderController::store` puts `500` inside the JSON array instead of the status argument |
| F8 | Stock is per product, not per size/colour | `stocks.product_id`, `qty`; variants are names only |
| F9 | `sale_price` is saved but never used | no reads outside `ProductServices` |
| F10 | `regular_price` drives all money maths | `Cart`, `CartItem`, `CartServices`, `PlaceOrderServices`, `ShopServices` and API controllers |
| F11 | Wishlist is half-built | `Product::wishlist()` references a `Wishlist` model and table that don't exist; only `account/wishlist.blade.php` exists |
| F12 | No order emails | Only password-reset and contact mails exist; `QUEUE_CONNECTION=sync` |
| F13 | No real tests | `tests/` contains only Laravel's examples; `phpunit.xml` DB lines are commented out |

---

## Phase A: Money, stock and order correctness (critical)

**Goal:** an order can only be created when it is paid and in stock; cancelling puts stock back; no card data is stored anywhere.

### A1. Fix cancellation (small)
- Add status constants to `Order` (`STATUS_CONFIRMED`, `SHIPPED`, `COMPLETED`, `CANCELLED`, `RETURNED`) and use them in `OrderServices`, `AdminOrderServices`, `DashboardController`, `Order::statusColor`.
- `OrderServices::cancel`: only allowed while `confirmed`; in a transaction set `cancelled` + `cancelled_at`, **increment stock for each item**, reverse coupon usage, and (after A3) trigger a refund.
- Migration: `UPDATE orders SET status='cancelled' WHERE status='canceled'`.
- **Tests:** cancel restores stock; cannot cancel a shipped order; legacy `canceled` rows are normalised.

### A2. Check stock when placing the order (small-medium)
- In `PlaceOrderServices::storeOrder`, inside the existing transaction: `Stock::lockForUpdate()` the rows for all cart products, verify `qty >= requested`, otherwise throw a `OutOfStockException` carrying product name and available qty.
- Controller returns a proper `422` with a readable message; checkout JS shows it and links back to the cart. Fix F7 (status code).
- Cart page: show "Only N left" next to lines whose qty exceeds stock.
- **Tests:** two buyers, one unit (second fails and nothing is created); partial stock message; successful order decrements once.

### A3. Real payments with PayPal (medium-large)
Provider choice is an open decision (see section 4). Plan assumes **PayPal Checkout** because the package and config are already in the project; the structure also fits Stripe or PayMongo with a different gateway class.

- New `App\Services\Payments\PayPalGateway` (create order from the **server-computed** grand total in PHP; never trust a browser amount; capture; refund). Remove the demo `/paypal` page and `PaymentController` stub.
- Checkout payment step: replace the card form with **"Pay with PayPal"** (and optionally Cash on delivery, decision below).
- Flow:
  1. Customer clicks Pay with PayPal: server re-validates cart + stock (A2), creates the PayPal order for the exact total, redirects to approval.
  2. Return URL: server captures; only on `COMPLETED` does it call `PlaceOrderServices::storeOrder` with provider `paypal`, `reference_number = capture id`, payment `status = paid`.
  3. If capture succeeds but the stock check now fails, **refund automatically** and show a clear message.
  4. Cancel URL returns to checkout with the cart intact.
- Idempotency: remember the PayPal order id in the session/DB so refreshing the return URL cannot create a second order.
- Config: sandbox keys in `.env`, documented in `.env.example`. Currency `PHP`.
- **Tests:** gateway is mocked; success creates exactly one order; failed capture creates none; double-return creates one; amount sent equals server total (including tax, shipping, coupon).

### A4. Remove stored cards (small-medium)
- Remove the "Payment methods" account pages, routes, controller/service, the checkout card component, `card.js`/`payment.js` card handling, and the `[cleave.js] Please check the element` console error that comes with them.
- Migration 1 (safe): **scrub** existing data: `UPDATE order_payments SET reference_number = NULL WHERE provider = 'card'` (or keep the last 4 digits only); `TRUNCATE`/null the card columns.
- Migration 2 (after a backup is confirmed): drop `card_number`, `card_cvc`, `card_expire_date`, `card_name` (or drop `user_payment_options`).
- Rollout note for the owner: if real card data was ever stored, treat it as exposed (notify per local rules, restrict DB backups).
- **Tests:** no route or view references card fields; order creation stores no card data.

### A5. Pricing source of truth (prepares Phase C1)
- Add `Product::effectivePrice()` (sale price when valid, otherwise regular) and switch the 10 files in F10 to use it **now, with identical behaviour** (sale price still ignored). This isolates the risky refactor and lets Phase C turn sales on safely.
- Store the price actually charged on `order_products.price` (already the case) so past orders never change.

**Phase A exit criteria:** all A tests green; manual run: add to cart -> PayPal sandbox -> order created once -> cancel -> stock restored; DB contains no card numbers.

---

## Phase B: Order emails and tracking

### B1. Mail infrastructure
- Set `MAIL_FROM_ADDRESS/NAME`; dev uses `log`/Mailtrap. Switch to `QUEUE_CONNECTION=database`, add the jobs table (already migrated) and a documented `php artisan queue:work` command.

### B2. Mailables (Markdown, branded to match the new look)
- `OrderConfirmed` (items, totals, shipping address, link to order), `OrderShipped` (courier + tracking link), `OrderCancelled` (refund note).
- Sent **after commit** so a failed transaction never emails. Billing email from `OrderBilling.email`.

### B3. Tracking data
- Migration: `orders.courier`, `orders.tracking_number`.
- Admin "Ship order" action gets two fields; customer order detail page shows them.
- **Tests:** `Mail::fake()` asserts each mail is queued once with the right recipient and content.

---

## Phase C: Storefront features

### C1. Sale prices (medium)
- Turn on `effectivePrice()` sale logic (optional sale start/end dates later).
- UI: strikethrough regular price + "Sale" badge on product card, product page, cart drawer, cart and checkout summary; "On sale" filter.
- Coupon rules: define whether coupons stack with sale prices (decision).
- **Tests:** cart total and order item price use the sale price; ending a sale doesn't change past orders.

### C2. Shop filters (medium)
- Query params: `size`, `color` (via `product_variants`/`attributes`), `price_min`, `price_max`, `in_stock`, `sale`, combined with the existing sort and category.
- `ShopServices` builds the query; pagination keeps the filters; mobile drawer UI; active-filter chips and "Clear all".

### C3. Wishlist (medium)
- Migration + `Wishlist` model (`user_id`, `product_id`, unique pair); routes `wishlist.index/store/destroy`; heart button on cards and product page (AJAX with a login prompt for guests); finish `account/wishlist.blade.php` in the new design.

### C4. Sold-out and low stock (small, per-product)
- Product page: "Sold out" state, disabled Add to bag, optional "Notify me" email capture (table + a job when restocked).
- **Per-size stock is not possible with the current schema (F8).** It needs a `variant_stocks` table and changes to cart, checkout, inventory admin and product forms. Treated as an optional **C4b**, only if you sell sizes that run out independently.

### C5. Size guide (small)
- Modal/link on product page with a table per category; content stored in admin settings. **Needs real measurements from you.**

### C6. Guest checkout (medium-large, optional)
- Allow checkout with email only; order linked by email; account creation offered afterwards. Touches cart ownership (`Cart::ByUser`), checkout session and order lookup; do after A and B.

---

## Phase D: Admin and operations

- **D1. Low-stock alerts:** threshold setting; dashboard card + daily email digest.
- **D2. Exports and documents:** CSV export for orders/customers/products; printable invoice and packing slip (Blade + print CSS).
- **D3. Returns and refunds:** customer "Request return" -> admin approve -> refund through the gateway -> stock restored (optional) -> `returned` status. Depends on A3.
- **D4. Order notes and activity log:** internal notes on orders; log who changed status/price/stock.
- **D5. Roles and permissions:** replace the single `role` flag with named permissions (products, orders, refunds, settings).
- **D6. Bulk product import:** CSV import with a dry-run report.

---

## Phase E: Growth and trust

- **E1. SEO:** per-page meta description, Open Graph/Twitter tags, canonical URLs, `sitemap.xml` (products + categories), `robots.txt`.
- **E2. Legal and help pages:** Privacy, Terms, Shipping & Returns, FAQ (content from you).
- **E3. Email verification** (the model already implements `MustVerifyEmail`; enable routes and views).
- **E4. Newsletter signup** + abandoned-cart reminder email (scheduled command).
- **E5. Related products** on the product page.

---

## Phase F: Quality and maintenance (**start here, in parallel with A**)

- **F-1. Tooling:** add `.nvmrc` (Node 20), a README "Getting started", get `npm run dev/prod` working, rebuild `public/js/app.js` and `public/css/*.css` from source and stop hand-editing compiled files.
- **F-2. Test setup:** enable a test DB in `phpunit.xml` (sqlite in-memory or a dedicated MySQL schema), add missing factories (`Order`, `Cart`, `Review`, `Coupon` states), a base `TestCase` helper to log in as customer/admin.
- **F-3. CI:** GitHub Actions workflow running `composer install`, migrations and `phpunit` on every PR.
- **F-4. Cleanup:** delete `resources/views/admin/inbox/index.blade copy.php`; set `reviews.block` default to `0` so the admin "unblocked" filter works.
- **F-5. Framework upgrade (separate project):** Laravel 8 is end-of-life. Upgrade 8 -> 9 -> 10 once F-2/F-3 give a safety net. Not part of this plan's schedule.

---

## 2. Suggested order and size

| Order | Item | Size | Depends on |
|---|---|---|---|
| 1 | F-1, F-2, F-3 (tooling, tests, CI) | M | none |
| 2 | A1, A2, A5 | M | F-2 |
| 3 | A3 PayPal, A4 remove cards | L | A2, A5 |
| 4 | B1-B3 emails + tracking | M | A3 (so payment mail has real data) |
| 5 | C1 sale prices, C2 filters, C3 wishlist, C4 sold-out | L | A5 |
| 6 | D1-D3 | L | A3, B |
| 7 | E1-E5, C5, C6, D4-D6 | L | as needed |

Rough effort for 1-4 (the part that makes the store safe to run): **about 2 to 3 weeks of focused work**; items 5-7 are independent and can be picked off one at a time.

## 3. Risks and how the plan handles them

| Risk | Mitigation |
|---|---|
| Payment code is hard to test end to end | Gateway behind an interface, mocked in tests; manual sandbox script in the PR |
| Customer pays but stock ran out in between | Re-check stock at capture; automatic refund + message |
| Price refactor (A5) changes totals by accident | Done as a behaviour-preserving step first, with before/after cart total tests |
| Dropping card columns loses data someone needs | Separate migration after an explicit backup step; scrub first |
| Existing orders with `canceled` | Normalising migration |
| Compiled JS drifting from source | F-1 first; until then edits mirrored in both places |
| Laravel 8 / PHP 8.5 friction on this machine | Tests run in CI on a supported PHP (8.1/8.2) |

## 4. Decisions needed from you before starting

1. ~~**Payment provider**~~ **Decided: PayPal** (already installed). **Cash on delivery: not included** (PayPal only; an order is created only after payment is confirmed). Can be added later as its own item.
2. ~~**Saved cards**~~ **Decided: remove completely** (default taken, no preference given). Existing card data is scrubbed, columns dropped after a backup.
3. **Per-size stock (C4b):** do sizes sell out independently (e.g. M sold out, L available)? If yes it moves up in priority and adds schema work.
4. **Sale + coupon stacking:** can a coupon apply on top of a sale price?
5. **Guest checkout (C6):** wanted now, later or never?
6. **Content you will provide:** size charts, legal pages, shipping/returns text, store contact details.
7. **Hosting:** where will production run (PHP version, queue worker availability, HTTPS)? This affects mail/queue setup and CI.
8. ~~**Branching**~~ **Decided: merge `ui-redesign` into `main` first**, then branch each phase from the updated `main` (default taken, no preference given).
