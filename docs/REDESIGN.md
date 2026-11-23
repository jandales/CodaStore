# CodaStore UI Redesign Spec

Goal: a **simple, elegant, modern** clothing storefront. Quiet luxury: lots of whitespace, a warm neutral palette, one serif for headlines, photography doing the talking.

Scope: storefront first (home, shop, product, cart, checkout, account), then admin restyle to share tokens.

---

## 1. Design principles

1. **Photography leads.** UI chrome stays quiet so clothing is the focus.
2. **One accent, used sparingly.** Black is the primary action color; no coral/blue/green mix.
3. **Generous space.** Section padding 64–96px desktop, 40px mobile.
4. **Few components, used consistently.** One button style set, one card, one form field.
5. **Accessible by default.** Visible focus, AA contrast, 44px touch targets, alt text.

---

## 2. Design tokens

Define once in `resources/sass/_tokens.scss` as CSS variables and import from both `app.scss` and `main.scss`. Replace hard-coded `#fff`, `#eee`, `#555`, `#ff523b`, etc.

```css
:root {
  /* Color */
  --bg:           #FAF8F5;  /* page, warm off-white */
  --surface:      #FFFFFF;  /* cards, inputs */
  --surface-alt:  #F1EDE6;  /* image placeholders, panels */
  --ink:          #1A1A1A;  /* text, primary button */
  --ink-muted:    #6B6760;  /* secondary text (AA on --bg) */
  --line:         #E6E1DA;  /* hairlines */
  --accent:       #8C7A68;  /* eyebrows, small highlights only */
  --success:      #3F6B4F;
  --danger:       #A63D2F;
  --focus:        #1A1A1A;

  /* Type */
  --font-display: 'Cormorant Garamond', Georgia, serif;
  --font-body:    'Inter', system-ui, sans-serif;

  /* Scale (rem) */
  --fs-xs: .75rem;  --fs-sm: .8125rem; --fs-base: .9375rem;
  --fs-lg: 1.125rem; --fs-h3: 1.75rem; --fs-h2: 2.25rem; --fs-h1: clamp(2.5rem, 5vw, 3.75rem);

  /* Space (8px base) */
  --s1: .5rem; --s2: 1rem; --s3: 1.5rem; --s4: 2rem; --s6: 3rem; --s8: 5rem;

  /* Shape */
  --radius: 0;            /* square = editorial; use 2px max on inputs */
  --container: 1200px;
  --shadow-pop: 0 8px 24px rgba(0,0,0,.08);
  --ease: cubic-bezier(.2,.7,.2,1);
}
```

**Typography rules**

| Use | Font | Size | Notes |
|---|---|---|---|
| Hero / H1 | Display 400 | `--fs-h1` | line-height 1.05 |
| Section titles | Display 500 | `--fs-h2` | sentence case |
| Body | Body 400 | `--fs-base` | line-height 1.6 |
| Nav, buttons, labels | Body 500 | `--fs-xs/sm` | uppercase + .1em tracking for buttons and eyebrows only |
| Price | Body 400 | `--fs-base` | never lighter than `--ink-muted` |

Load fonts with `font-display: swap`; self-host in `public/fonts` rather than the Google CDN.

---

## 3. Layout and components

### Global
- Container `max-width: var(--container)`, side padding 24px (16px under 640px).
- Breakpoints (one set everywhere): `sm 640`, `md 768`, `lg 1024`, `xl 1280`.
- Sticky header, 72px desktop / 56px mobile, `--bg` with 0.5px bottom `--line`.

### Announcement bar
Black strip, 11px uppercase text, 8px padding. One message (shipping threshold). Dismissible.

### Header
- Desktop: category links left, centered wordmark, icons right (search, account, bag with count).
- Mobile: hamburger left, wordmark center, bag right. Off-canvas menu, full-height, focus-trapped.
- Icons get `aria-label`; the bag count is a small black dot, not a coral square.
- Search opens an inline overlay with autosuggest.

### Buttons
| Variant | Style |
|---|---|
| Primary | `--ink` bg, `--bg` text, uppercase 12px, .1em tracking, 14px 28px padding |
| Secondary | transparent, 1px `--ink` border |
| Text link | underline 0.5px, offset 4px |

Hover: primary lightens to `#333`; secondary fills `--ink`. 160ms `--ease`. One primary button per view. Min height 44px.

### Hero
Two columns on desktop (text left, image right, 4:5), stacked on mobile with image first. Eyebrow (season) + serif H1 (max 2 lines) + one sentence + two CTAs ("Shop new in", "Lookbook"). Use `object-fit: cover` and `aspect-ratio` to prevent the stretched-image problem.

### Category tiles
3-up grid, 4:5 images with label overlaid bottom-left in white; subtle zoom (1.03) on hover.

### Product card
- Image 3:4, `object-fit: cover`, second image on hover.
- Name (14px), price (muted), color swatches (max 4 and "+n").
- Badge top-left: "New", "Low stock", "Sale" (small caps on `--bg`, no color fill).
- Quick add on hover (desktop); always-visible "+" on mobile.
- Whole card is one link; wishlist heart top-right.

### Shop / listing page
- Title + count, filter bar (Category, Size, Color, Price) and sort on one line; filters open a right drawer on mobile.
- Grid 4 → 3 → 2 columns. 24px gap. Pagination replaced by "Load more" with count.
- Empty state: headline, one sentence, "Clear filters".

### Product page
- Left: vertical thumbs + large image (zoom on hover), swipe gallery on mobile.
- Right (sticky): name, price, rating, short description, **color swatches**, **size chips** (disabled = struck through), quantity stepper, primary "Add to bag", secondary "Add to wishlist".
- Below: accordion (Details, Fabric and care, Shipping and returns), reviews, "You may also like".
- Show stock hint ("Only 3 left") and a size guide link.

### Cart (drawer and page)
- Drawer: line items with thumb, name, variant, qty stepper, remove; subtotal; "Checkout" primary; free-shipping progress bar.
- Cart page mirrors this with a summary column. Fix the zero-subtotal bug seen at checkout.

### Checkout
- Single-column form, order summary on the right (collapses to a top accordion on mobile).
- 3 steps: Information, Shipping, Payment, shown as plain text breadcrumbs, not icon circles.
- Fields: floating-free labels above inputs, 48px height, 1px `--line` border, `--ink` border on focus.
- Fix typos: "Country", "Philippines", "calculated at next step".

### Forms
Label above field (13px, 500). Helper text muted. Error: `--danger` text + 1px border + icon, announced via `aria-live`.

### Footer
Black, 4 columns: Shop, Help (shipping, returns, size guide), Company, Newsletter signup. Social icons, payment icons, copyright.

### Feedback
Replace the modal "OK" popup with toasts (bottom-center, auto-dismiss 4s, `role="status"`).

---

## 4. Motion
- Durations 160–240ms, `--ease`. Fade/translate only.
- Page sections fade up 8px once on scroll, disabled for `prefers-reduced-motion`.
- No carousels autoplaying; arrows plus swipe only.

---

## 5. Accessibility checklist
- [ ] Remove blanket `outline: none`; add `:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; }`
- [ ] Contrast AA for all text (muted text `#6B6760` on `#FAF8F5` ≈ 5:1)
- [ ] Every `<img>` has meaningful `alt` (decorative = `alt=""`)
- [ ] Touch targets ≥ 44×44px (size chips, qty stepper, icons)
- [ ] Unique `<title>` per page: "Page name | Coda"
- [ ] Skip link, landmark roles, labelled icon buttons
- [ ] Respect `prefers-reduced-motion`

---

## 6. Admin (phase 2)
Keep the dark sidebar layout but adopt tokens: sidebar `--ink`, content `--bg`, cards `--surface` with 0.5px `--line` borders, status badges use semantic colors with tinted backgrounds. Fix "Dashbaord". Add breadcrumbs and consistent table styles (sticky header, row hover, mobile card fallback).

---

## 7. Implementation plan

| Phase | Work | Files |
|---|---|---|
| 0 | Quick fixes: titles, typos, alt text, focus styles | `layout/*.blade.php`, views |
| 1 | Tokens + fonts + base reset, shared by both Sass entry points | `resources/sass/_tokens.scss`, `app.scss`, `main.scss` |
| 2 | Header, footer, buttons, forms, toasts | `sass/app/_header`, `_footer`, `_buttons`, `_modal` |
| 3 | Home: hero, categories, product cards | `home.blade.php`, `_product`, `_colletion` |
| 4 | Shop listing and product page | `shop/*`, `_product` |
| 5 | Cart drawer, cart page, checkout | `_cart`, `checkout*.blade.php` |
| 6 | Account pages | `_account`, `account/*` |
| 7 | Admin restyle | `sass/main/_style`, `layout/admin.blade.php` |
| 8 | Responsive pass (375 / 768 / 1280) and a11y audit | all |

Cleanup along the way: remove duplicate `hero.jpg`, stop committing compiled `public/css` and `resources/css`, split the 3,500-line `_style.scss` into partials.

---

## 8. Definition of done
- All pages use tokens only, with no raw hex outside `_tokens.scss`.
- One breakpoint set; no horizontal scroll at 375px.
- Lighthouse accessibility ≥ 95 on home, shop, product, checkout.
- Visual QA against the approved homepage sample.

---

## 9. Status (updated 2026-11-17)

| Phase | State | Notes |
|---|---|---|
| 0 Quick fixes | Done | Titles, typos, alt text, focus styles |
| 1 Tokens + base | Done | `sass/_tokens.scss`, `sass/app/_base.scss` |
| 2 Header, footer, buttons, forms | Done | `sass/app/_chrome.scss` |
| 3 Home | Done | `sass/app/_home.scss`, shared `components/product-card.blade.php` |
| 4 Shop + product page | Done | `sass/app/_shop.scss` |
| 5 Bag drawer, cart, checkout | Done | `sass/app/_checkout.scss` |
| 6 Account, sign in, register | Done | `sass/app/_account-shell.scss` |
| 7 Admin | Done | `sass/main/_admin-theme.scss` |
| 8 Responsive + a11y pass | Done | `sass/app/_pages.scss` (about, contact, search, 404) |

**Responsive and accessibility check:** an in-browser sweep of 12 storefront pages at 375, 768 and 1280px found no horizontal scrolling, no images without `alt`, no unlabeled fields, no unnamed links or buttons, exactly one `h1` per page and a unique `<title>` on each. Lighthouse was not run.

**Open items**
- JavaScript was not changed in any phase because Laravel Mix fails on Node 26. Build with Node 18 or 20 (`npm run dev`) to pick up future JS work. Things that need it:
  - Keyboard support (Enter/Space) for size chips, quantity buttons and gallery thumbnails.
  - Toast notifications to replace the "OK" popup, and its copy ("Successfully Added to your").
  - Live subtotal update on the cart page and the shipping-step summary.
- Until Mix works, CSS is compiled with the `sass` CLI; the font and hero image `url()` paths are rewritten after compiling (see session notes).
- Fonts load from Google Fonts; self-host them in `public/fonts`.
- The admin pages not visually checked: coupons, reviews, customers, users, inventory, categories, attributes, settings.
- `resources/views/admin/inbox/index.blade copy.php` looks like a leftover file.
