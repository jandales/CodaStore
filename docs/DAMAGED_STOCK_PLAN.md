# Plan: Damaged stock

Status: **for review, nothing built yet.**

Keep damaged units **separate from sellable stock**. Example:

| | Units |
|---|---|
| On hand | 100 |
| Damaged | 5 |
| **Available (can be sold)** | **95** |

Damaged units stay on record until they are **repaired** (back to Available) or **written off** (gone
for good). Every step shows in Stock details.

---

## 1. Where things stand today

| Area | Today | What has to change |
|---|---|---|
| Stock numbers | One number per variant (or per product without variants): the sellable stock | Add a **Damaged** number next to it; the existing number becomes **Available** |
| Inventory page | Stock In / Stock Out dropdown, quantity and Save on every row | One **Adjust stock** button per row (Stock In, Stock Out, Damage), a Damaged column and a filter |
| Stock details | History of every change, with stock after each change | Damaged moves in the history, plus Damaged and On hand figures |
| Returns | Approve with "Put the items back into stock" (yes / no) | No change (returns are out of scope) |
| Selling | Shop, checkout, manual orders and low-stock warnings use the one stock number | **No change**: they keep using Available only, so damaged units can never be sold |

The only way to record damage today is Stock Out, which loses the units and the reason.

## 2. Decisions needed (suggested defaults in brackets)

1. **What the shop and low-stock warnings count:** **[Available only]**. 95 in the example.
2. **Where damage is recorded:** **[the Adjust stock panel on Inventory]** (Stock In, Stock Out,
   Damage), with an optional note ("torn seam, supplier box 3").
3. **What happens to damaged units:** **[Repaired → back to Available; Written off → removed]**. No
   selling damaged items at a discount in the first version (that would need its own "seconds"
   product).
4. **Returns:** **[no change]**. Out of scope for now.
5. **Money value of damage:** **[not in the first version]**. Units only; a value needs a cost price
   on products, which the store doesn't have yet.

---

## 3. Phase 1: Damaged quantity and Inventory actions

**Data**
- `damaged_qty` (default 0) on variants (`product_skus`) and on products without variants (`stocks`).
  For a product with variants, the product's damaged total is the sum of its variants, like the
  stock total today.
- Stock history (`stock_movements`) gets `damaged_change` and `damaged_balance`. A move records both
  numbers: Mark as damaged 5 = Available −5, Damaged +5.

**Inventory page: columns**

| Photo | Product | SKU | Category | **Available** | **Damaged** | |
|---|---|---|---|---|---|---|
| | laborum `M` | 561-0127 | jacket | 95 | 5 | **Adjust stock** |

- **Available**: the current Stock column, renamed. It's the sellable stock.
- **Damaged** (new): red when above 0.
- The per-row Stock In / Stock Out dropdown, the +/− box and Save are replaced by one **Adjust stock**
  button.

**Inventory page: the Adjust stock panel**

Opens for one row and has three actions:

| Action | What it does | Available | Damaged | Allowed up to |
|---|---|---|---|---|
| **Stock In** | New delivery, found units, count correction | + | | any |
| **Stock Out** | Lost / missing, count correction | − | | Available |
| **Damage** | Mark as damaged | − | + | Available |
| | *Damaged units:* Repaired, back to stock | + | − | Damaged |
| | *Damaged units:* Write off | | − | Damaged |

- **Damage** has the three choices above. Repaired and Write off are the only ways damaged units
  leave the Damaged column.
- **Quantity**, plus an optional **note** ("Torn seam, supplier box 3") kept in the history.
- **Live preview** before saving, e.g. "Available 95 → 92 · Damaged 5 → 8".
- A clear message when the quantity is too high, e.g. "Only 5 damaged to write off".
- Filter: **Has damaged stock**.
- The panel runs from a small standalone script (`public/js/admin-inventory.js`), because the
  compiled admin script can't be rebuilt on this machine's Node version.

**Stock history reasons:** Stock added · Stock removed · Marked as damaged · Repaired, back to
stock · Damaged, written off. Each shows the note.

**Size:** M.

## 4. Phase 2: Stock details, product page and export

**Stock details**
- Cards: **Available now**, **Damaged now**, **On hand** (both together), and **Damaged · last 30 days**.
- Variant tabs show both numbers when there is damage, e.g. "M 28 · 2 damaged".
- The history shows each move's effect on Available and on Damaged, e.g. "−5 → 95 available ·
  +5 → 5 damaged".

**Returns:** no change. Approving a return keeps today's choice (put back into stock or not); damaged
returns can be marked with **Damage** on Inventory afterwards.

**Product page:** the stock fields stay the sellable (Available) numbers; a note under them shows
"2 damaged · manage on Inventory" when there is damage. Editing a product never changes damaged
counts.

**Exports and reports**
- The products CSV export gets a Damaged column.
- Sales reports are unaffected, since damage isn't a sale.

**Size:** S–M.

---

## 5. Order of work and effort

| Order | Phase | Depends on | Size |
|---|---|---|---|
| 1 | Damaged quantity and Inventory actions | decisions 1–3 | M |
| 2 | Stock details, product page, export | Phase 1 | S–M |

Builds on Stock details (branch `stock-history`).

## 6. Testing

- **Moves:**
  - Mark as damaged, Repaired and Write off change the right numbers.
  - History records both changes and both balances.
  - Quantities above what's available or damaged are refused with a clear message.
- **Selling never touches damaged units:**
  - With 95 available and 5 damaged, the shop shows 95.
  - Checkout and manual orders can't take a 96th unit.
  - Low-stock warnings use 95.
- **Variants:** the product's damaged total is the sum of its variants; the product page, Inventory and
  Stock details agree.
- **Product edits and CSV import:** changing stock on the product page or by import leaves damaged
  counts alone.
- **Permissions:** the damaged actions need the Products permission, like the rest of Inventory.
- **Browser check:**
  - mark 5 as damaged, repair 2 and write off 3 on one variant
  - check the Inventory columns, the Stock details cards and the history

## 7. Risks

| Risk | How the plan handles it |
|---|---|
| Damaged units sold by mistake | Selling only reads Available; tests try to oversell |
| Numbers that don't add up between pages | One rule everywhere: On hand = Available + Damaged; product totals are sums of variants |
| Damaged units forgotten forever | "Has damaged stock" filter on Inventory and the Damaged card on Stock details |
| Staff using Stock Out for damage out of habit | The action list puts "Mark as damaged" right next to it; the note field explains what happened |
