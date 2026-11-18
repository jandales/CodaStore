# Plan: Sales reports

Status: **for review, nothing built yet.**

A **Reports** page in the admin that answers everyday questions such as:
- How much did we sell last month?
- What sold best?
- Which sizes run out?
- How much COD cash is still with couriers?

Every figure can be filtered by date range and exported to CSV.

---

## 1. Where things stand today

| Area | Today | What has to change |
|---|---|---|
| Dashboard | Income per month for this year, order counts by status, recent orders, low stock | Stays as the quick overview, with a "View reports" link |
| Detailed reporting | None. The only option is exporting orders to CSV and working in a spreadsheet | New Reports page with date ranges, comparisons, breakdowns and exports |
| The data | Orders have totals, shipping, discount, COD fee, status and dates; order lines have product, variant, price and quantity; payments have method and status; returns have reasons and refunds | Enough for everything below. Indexes are added on order dates and status so reports stay fast |
| Days and months | The app runs in UTC; the store timezone setting isn't used | Reports group by the **store's timezone** (Asia/Manila by default), so an order at 7 am counts on the right day |

## 2. Decisions needed (suggested defaults in brackets)

1. **What counts as a sale:** **[orders that weren't cancelled]**. Returned orders and COD parcels that
   weren't collected are shown separately as **returns / refunds** and subtracted to give **net sales**.
2. **Which date a sale belongs to:** **[the order date]**, not the delivery or payment date. COD cash
   still with couriers is shown as its own figure.
3. **Who can see reports:** **[a new "Reports" staff permission]**. Revenue is sensitive, so it's
   separate from Orders. Administrators always have it.
4. **Store timezone:** **[Asia/Manila]**. The existing timezone in Settings → General is used for
   reports, and set to Asia/Manila if empty.
5. **Charts:** **[drawn on the server]**, like the dashboard's bars, so no extra JavaScript library or
   build step is needed.

---

## 3. Phase 1: Reports overview

**Date range** at the top:
- presets: Today, Yesterday, Last 7 days, Last 30 days, This month, Last month, This year
- a custom From / To range
- **"Compare to the previous period"** on by default

**Summary cards**, each showing the % change against the previous period:

| Card | Meaning |
|---|---|
| Net sales | Order totals minus returns and refunds |
| Orders | Number of orders |
| Average order value | Net sales ÷ orders |
| Items sold | Units across all order lines |
| Returns and refunds | Amount and number of returned or not-collected orders |
| COD awaiting collection | Cash still due on shipped COD orders (as of today) |

**Sales over time chart:**
- bars per day, week or month, chosen automatically from the range length
- hovering a bar shows its sales and orders

**Breakdown of net sales:** items, shipping fees, COD fees, discounts and coupons, refunds.

**Size:** M.

## 4. Phase 2: Breakdowns

Each is a table on the Reports page (with tabs or sections), using the same date range and with a
CSV download:

- **Products:** units sold, net sales, returns, and current stock, sorted by sales. Click a product to
  see its **variants** (e.g. which sizes sell, and which run out).
- **Categories:** units, net sales and share of sales.
- **Payment methods:** PayPal vs cash on delivery (plus manual payment methods once manual orders
  exist), with order count, net sales and average order value; COD collected vs not collected.
- **Sales channels:** website, Facebook, Instagram, phone… (once Manual orders adds channels; until
  then everything is "Website").
- **Customers:**
  - new vs returning customers in the period
  - top customers by spend
  - repeat purchase rate
- **Discounts:** each coupon's uses, discount given and the sales it was used on.
- **Returns:** returns by reason (wrong size, damaged…), by product, and refund amounts.

**Size:** M.

## 5. Phase 3: Exports, permission and dashboard link

- **CSV export** for the summary and every breakdown, named with the range, e.g.
  `coda-sales-products-2026-11-01-to-2026-11-30.csv`. Formula-like cells are made safe, as in the
  existing exports.
- New **Reports** permission (decision 3), plus a sidebar link under Orders.
- **Dashboard:** "View reports" link; the income chart uses the store timezone too.
- **Performance:** database indexes on order date and status, and aggregate queries instead of
  loading every order. Checked with a year of generated sample orders.

**Size:** S.

---

## 6. Order of work and effort

| Order | Phase | Depends on | Size |
|---|---|---|---|
| 1 | Reports overview | decisions 1 to 5 | M |
| 2 | Breakdowns | Phase 1 | M |
| 3 | Exports, permission, dashboard | Phase 2 | S |

Independent of Manual orders and can be built first. When Manual orders lands, its channels and
payment methods show up in the Channels and Payment methods breakdowns automatically.

## 7. Testing

- **Figures:** each one checked against a known set of orders. The set includes:
  - cancelled, returned and COD not-collected orders
  - discounts, coupons and COD fees
  - variants with their own prices
- **Date boundaries:** an order at 00:30 Manila time counts on that day, not the day before; month and
  year edges.
- **Comparison:** the previous period has the same length, and the % change is right, including when
  the previous period had no sales.
- **Breakdowns:** product, variant and category totals add up to the overview's item sales.
- **CSV:** each export matches the table on screen.
- **Permissions:** staff without Reports get the "no access" page.
- **Browser check:** each range preset, the chart, and each breakdown.

## 8. Risks

| Risk | How the plan handles it |
|---|---|
| Numbers that don't match what staff expect | Clear definitions on the page ("Net sales = …"), and the breakdowns add up to the totals |
| Wrong day because of timezones | Everything grouped in the store timezone, with tests at day edges |
| Slow page as orders grow | Indexes and aggregate queries, tested with a year of sample orders |
| Revenue seen by staff who shouldn't | Separate Reports permission |
