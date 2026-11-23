# Plan: Manual orders

Status: **for review, nothing built yet.**

Staff can create an order in the admin for a sale that didn't happen on the website: Facebook or
Instagram messages, phone orders, walk-in customers. The order then works exactly like a website
order: stock is taken, it shows in Orders, can be shipped, delivered, cancelled, returned and
invoiced, and counts in reports.

---

## 1. Where things stand today

| Area | Today | What has to change |
|---|---|---|
| Creating orders | Only checkout can create orders. `PlaceOrderServices` reads the customer's bag from the browser cookie and the address and shipping method from the checkout session | Separate the core (check and lock stock per variant, create the order, its lines, payment, coupon use) so checkout and the admin both call it with an explicit list of items |
| Customer on an order | `orders.user_id` is required, and customer accounts need a unique email | A manual order needs a customer: an existing one, a new one, or (decision 1) a guest order without an account |
| Payment | `order_payments.provider` is `paypal` or `cod` | Add manual payment records: cash, bank transfer, GCash / Maya, other, with an optional reference, plus "awaiting payment" with a later "Mark as paid" |
| Where the order came from | Not recorded | New `orders.channel`: website (default), Facebook, Instagram, TikTok, phone, walk-in, other. Used by reports |
| Order screens and emails | Assume a website order | Show the channel and the staff member who created it; the confirmation email is optional |

## 2. Decisions needed (suggested defaults in brackets)

1. **Customers without an email** (common for walk-in and social media buyers):
   - a. **Allow guest orders**: the order keeps the name, phone and address but no customer account.
     `orders.user_id` becomes optional, and the screens that assume an account are made to cope.
     **[recommended]**
   - b. Always create a customer account: an email becomes required for every manual order.
2. **Changing prices per line:** **[no]**. Lines use the current price (per variant, with any sale).
   For deals, use an **order discount** (amount or %), which shows as its own line on the invoice.
3. **Backdating:** allow an **order date** in the past, for orders taken earlier but entered later.
   **[yes, up to 30 days back]**
4. **Confirmation email:** a checkbox on the form, ticked by default when the customer has an email.
   **[yes]**
5. **Coupons on manual orders:** **[not in the first version]**. The order discount covers it.

---

## 3. Phase 1: Shared order-creation core

- New `OrderBuilder` service that takes: customer (or guest details), items (product, variant, qty),
  shipping method and fee, discount, payment details, channel and order date.
- It does what checkout does today, in one transaction:
  - check and lock stock per variant
  - create the order, its lines (with the variant and its price at that moment) and the shipping and
    billing details
  - record the payment and update the totals
- Checkout is changed to read its bag and session, then call `OrderBuilder`. **No change in behaviour**:
  the existing checkout, COD and variant tests must stay green.
- Migration:
  - `orders.channel` (default `website`) and `orders.created_by_admin_id` (nullable)
  - `orders.discount_amount` (default 0)
  - `order_payments.method` (e.g. `cash`, `bank_transfer`, `gcash`)
  - for decision 1a: `orders.user_id` becomes nullable

**Size:** M.

## 4. Phase 2: Create order page (Orders → "Create order")

**Customer**
- Search existing customers by name, email or phone, or **"New customer"**: name, phone, email
  (optional with decision 1a).
- Shipping address: filled from the customer's saved address, or typed in.

**Items**
- Search products by name or SKU, choose a variant (Size / Colour), and set the quantity.
- Each line shows the unit price, the available stock for that variant, and the line total. A variant
  with no stock can't be added, and a quantity can't exceed its stock.

**Delivery and payment**
- Shipping method from the store's methods (fee filled in, editable, e.g. ₱0 for pickup).
- Order discount: amount or %.
- Payment:
  - **Cash on delivery** (cash due on delivery, same as website COD orders)
  - **Paid**: cash, bank transfer, GCash / Maya, or other, with an optional reference (e.g. the GCash
    reference number)
  - **Awaiting payment**: for example waiting for a bank transfer; a **"Mark as paid"** button
    appears on the order page later
- Channel (Facebook, Instagram, TikTok, phone, walk-in, other), order date (decision 3), internal
  note, and "Email the customer a confirmation".

**Summary and save**
- A live summary of subtotal, shipping, discount and total. Totals are always recalculated on the
  server.
- **Create order** saves and opens the new order's page.

**Size:** M.

## 5. Phase 3: Fitting manual orders into the rest of the admin

- **Orders list:** a channel label (e.g. "Facebook"), a filter by channel, and an "Awaiting payment" tab.
- **Order page:** shows the channel, "Created by Mia Reyes", payment method and reference, and "Mark as
  paid" when awaiting payment. Ship, deliver, COD collect, cancel, returns, notes and invoice all work
  as for website orders.
- **Guest orders** (decision 1a): the orders list, order page, invoice, CSV export and dashboard use the
  name and phone from the address when there is no account. Returns and "My orders" only apply when
  there is an account.
- **Cancelling:** stock goes back to the exact variant. Refunds for manual payments are recorded by
  hand, like COD.
- **Activity log:** "Created manual order 2026… (Facebook, ₱1,250.00)".
- **Permissions:** creating manual orders needs the Orders permission; "Mark as paid" also needs Orders.
- **CSV export:** Channel, Payment method and Created by columns.

**Size:** M.

---

## 6. Order of work and effort

| Order | Phase | Depends on | Size |
|---|---|---|---|
| 1 | Shared order-creation core | decisions 1 to 3 | M |
| 2 | Create order page | Phase 1 | M |
| 3 | Fitting into the admin | Phase 2 | M |

About the same size as the variants work. No outside accounts are needed.

## 7. Testing

- **Checkout:** unchanged after Phase 1. All current checkout, COD, PayPal and variant tests pass.
- **Creating manual orders:**
  - for an existing customer, a new customer, and a guest (1a)
  - stock is taken from the right variant
  - a variant can't be oversold, and two staff can't sell the last unit at once
- **Totals:** shipping, discount and the COD fee are always worked out on the server, never trusted
  from the form.
- **Payments:** COD, paid (each method with a reference), and awaiting payment then "Mark as paid".
- **Backdating:** within the limit, and refused beyond it.
- **After the order:** cancelling restores stock, the invoice shows the channel and payment, and the
  CSV has the new columns.
- **Permissions:** staff without Orders can't create orders or mark them paid.
- **Browser run-through:** a Facebook COD order and a walk-in paid order from start to delivery.

## 8. Risks

| Risk | How the plan handles it |
|---|---|
| Changing order creation breaks checkout | Phase 1 is a pure refactor, guarded by the existing checkout tests, before any new screen |
| Overselling between website and manual orders | Same locked per-variant stock check for both |
| Guest orders break screens that expect an account | Phase 3 makes each of those screens cope, with a test for each |
| Wrong totals typed in by staff | Prices come from the catalogue; totals are recalculated on the server |
| Staff creating fake or test orders | Created-by is recorded and shown, and logged in the activity log |
