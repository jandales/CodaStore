# Plan: Cash on Delivery

Status: **built** on the `cod-payments` branch. Adds **cash on delivery (COD)** as a second payment method next to
PayPal. No other payment providers are planned.

---

## 1. Where things stand today

| Area | Today | What has to change |
|---|---|---|
| Checkout payment step | One button, "Pay with PayPal", posts to `checkout.paypal.start` | Shopper picks PayPal or Cash on delivery; the button and next step follow the choice |
| Order creation | `PlaceOrderServices::storeOrder()` runs when the shopper returns from PayPal, with a payment marked `paid` | Also runs straight away for COD, with a payment marked `pending` (cash due on delivery). The bag, stock checks, stock reservation and coupon logic are reused as they are |
| Order status flow | To ship → Shipped → Delivered, or Cancelled / Returned | Same statuses. For COD, "Delivered" also means the cash was collected, and a shipped COD order can be marked "not collected" |
| Refunds | `RefundPayment` refunds through PayPal and skips any other provider | COD payments are skipped automatically; COD returns get a manual "Refund sent" step |
| Text that names PayPal | Payment page, emails (cancelled, return approved), customer order page, admin messages, invoice | Use the order's actual method |
| Home page | Already says "Cards, PayPal and cash on delivery" | Change to "PayPal and cash on delivery" (PayPal also covers cards) |

## 2. Decisions needed (suggested defaults in brackets)

1. **COD fee** added to the order: [₱0]
2. **Highest order total** allowed for COD: [₱5,000]
3. **Where COD is offered:** all shipping methods and regions, or only some: [all]
4. **Unpaid COD orders per customer:** how many can be open at once: [2]
5. **When COD money counts as income:** count it on the dashboard when the order is placed (as today), but
   show it as "awaiting collection" on the Payments page until the order is delivered: [yes]
6. **Refunds for returned COD orders:** the customer gives bank or GCash details, you send the money, then
   an admin clicks "Refund sent" to record it: [yes]

All of 1–4 become admin settings, so they can be changed later without code changes.

---

## 3. Phase 1: Choosing a payment method

- **Payment step:** radio buttons for "PayPal" and "Cash on delivery". COD shows its fee (if any). If the bag
  doesn't qualify for COD, it shows why, e.g. "Cash on delivery is available for orders up to ₱5,000".
  One button whose label follows the choice: "Pay with PayPal" or "Place order". Works without JavaScript.
- **One checkout endpoint** that sends PayPal choices to the existing PayPal flow (unchanged) and COD
  choices to the new COD flow.
- **Admin settings** (Settings permission, under Settings): switch COD on or off, plus the fee, maximum
  order total, allowed shipping methods and the per-customer limit.
- PayPal behaves exactly as today, and the existing PayPal tests must keep passing.

**Size:** S.

## 4. Phase 2: Cash on delivery orders

**Customer flow**
1. Picks "Cash on delivery" and clicks **Place order**.
2. The server re-checks the bag, stock and COD rules, then creates the order straight away (no redirect):
   - stock reserved and coupon use recorded, as for PayPal orders
   - status "To ship"
   - payment: provider `cod`, status `pending`, for the full amount
   - the COD fee as its own line on the order, if set
3. Confirmation page and email: "Please have ₱X ready when your order arrives."
4. The customer can still cancel until it ships. Nothing was paid, so no refund step is needed.

**Admin flow**
- Orders list and order page: a "Cash on delivery" label and the cash due.
- **Marked as delivered** → payment becomes `paid`, with the collection time recorded.
- New action on shipped COD orders: **"Not collected / returned to us"** (parcel refused or undeliverable)
  → order becomes "Returned", stock goes back, payment becomes `failed`. Recorded in the activity log.
- **Returns after delivery:** approving a COD return asks the admin to send the money manually, then
  **"Refund sent"** records it (decision 6). The customer email explains how the refund will reach them.
- Who can do what follows the existing permissions: Orders for delivery and "not collected", Refunds for
  approving returns and recording refunds.

**Guards against misuse**
- A verified email is already required at checkout.
- Limit on unpaid COD orders per customer (decision 4).
- Rate limit on placing orders.
- COD rules are always checked on the server, never trusted from the form.

**Size:** M.

## 5. Phase 3: Wording, documents and reports

- Payment page, confirmation email, customer order page, invoice, admin order page and admin messages name
  the order's actual method: "Paid with PayPal", "Cash on delivery: ₱X due" or "Cash on delivery: paid".
- Emails for cancelled and returned orders say the right thing for COD (nothing charged / refund sent manually).
- Payments page: filter by method and status, with an "awaiting collection" total.
- Orders CSV export: payment method and payment status columns.
- Home page and footer: list PayPal and cash on delivery (only what is switched on).
- README: the COD settings.

**Size:** S.

---

## 6. Order of work and effort

| Order | Phase | Depends on | Size |
|---|---|---|---|
| 1 | Choosing a payment method | none | S |
| 2 | Cash on delivery orders | Phase 1, decisions 1–6 | M |
| 3 | Wording, documents and reports | Phase 2 | S |

No outside accounts or keys are needed, and the defaults are reasonable, so work can start any time.

## 7. Testing

- **Placing COD orders:**
  - an order is created with a pending payment, reserved stock and the fee line
  - the confirmation email says how much cash to have ready
- **COD rules:**
  - over the maximum total, a disallowed shipping method, or too many unpaid COD orders is refused
  - each refusal shows a clear message
  - when COD is switched off, the option is hidden and any posted COD choice is refused
- **Cancelling before shipping:** stock goes back and no refund is attempted.
- **Delivery:** marking delivered collects the payment. "Not collected" returns the stock and fails the
  payment.
- **Returns:** a COD return records a manual refund. PayPal returns still refund through PayPal.
- **Permissions:** staff without the needed permission can't mark collections or record refunds.
- **PayPal:** still works exactly as before.
- **Manual check:** a browser run-through of a COD order from bag to delivery before merging.

## 8. Risks

| Risk | How the plan handles it |
|---|---|
| Parcels refused at the door (cash never collected) | Per-customer limit on unpaid COD orders; "not collected" restores stock; activity log shows repeat cases |
| Fake or bulk COD orders | Verified email required, rate limit, per-customer limit, optional maximum order total |
| Income looks higher than cash received | Payments page separates "awaiting collection" from collected |
| Shoppers bypassing the rules by editing the form | All COD rules are checked on the server when the order is placed |
| Changing the payment step breaks PayPal | PayPal flow is reused unchanged and its tests must stay green |
