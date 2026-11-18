# Plan: Background emails

Status: **built** (branch `background-emails`).

Every email is sent **while the page waits**. That's why saving an order, placing an order or changing a
status can take several seconds and feel frozen. This plan sends emails **in the background** instead:
- The page saves and moves on straight away.
- A separate worker sends the email a moment later.
- If the mail server is down, the worker tries again later.

---

## 1. Where things stand today

The order emails are already written as "queued" emails (`Mail::to(...)->queue(...)`), but
`QUEUE_CONNECTION=sync` in `.env` sends them immediately anyway.

| Email | How it's sent today | Where it slows things down |
|---|---|---|
| Order confirmed | Queued, but sync | Checkout (PayPal and COD), Create order |
| Order shipped | Queued, but sync | Status update to Shipped |
| Order cancelled / refunded | Queued, but sync | Cancelling an order |
| Return requested, approved or declined | Queued, but sync | Return request, approve or decline |
| Newsletter welcome | Queued, but sync | Newsletter sign-up |
| Forgot password (customer and admin) | Job, but sync | Forgot-password form |
| Contact form message | Job, but sync | Contact page |
| Verify email address | Sent directly | Customer registration |
| Cart reminders, low-stock digest | Sent directly from scheduled commands | Nobody waits; the scheduled task just runs longer |

**What's already in place:**
- The `jobs` and `failed_jobs` tables exist.
- The README already mentions `php artisan queue:work`.
- Tests use `sync` with fake mail and stay that way.

**What could go wrong once emails really run later:**
- **Too early:** an email can be picked up before the order it's about has finished saving, if it's
  queued inside a database transaction (shipping an order is). It must wait until the save is
  committed.
- **Worker not running:** emails would quietly pile up and nobody would notice.
- **Failures:** a failed email ends up in `failed_jobs`, and today nobody can see that table.

## 2. Decisions needed (suggested defaults in brackets)

1. **Where emails wait:** **[the database]** (the existing `jobs` table). Nothing new to install. Redis
   is faster but needs another service, which a store of this size doesn't need.
2. **How the worker runs on the live server:** **[Supervisor]**, which keeps
   `php artisan queue:work` running and restarts it if it stops. **Fallback for shared hosting
   without Supervisor:** the existing every-minute scheduler starts a short worker run that stops
   when the queue is empty (turned on with a setting).
3. **Retries:** **[3 tries, after 1, 5 and 15 minutes]**, then the email counts as failed.
4. **Failed emails:** **[a "Failed emails" page in Settings]** with the reason and Retry / Delete
   buttons, plus a warning on the dashboard.
5. **Locally (XAMPP):** **[run the worker in its own terminal]** while testing emails. If it isn't
   running, emails simply wait and send once it starts; nothing is lost.

---

## 3. Phase 1: Send emails in the background

- `.env` / `.env.example`: `QUEUE_CONNECTION=database`. Tests keep `sync`.
- **Send only after the save is committed** (`after_commit`), so an email never goes out for an
  order whose save was rolled back.
- **One queue for emails** (`emails`), so a slow email never holds up other background work.
- **Retries and timeouts** on every email: 3 tries with the delays above, and a 30-second time limit
  per attempt.
- **Emails for deleted records**: if an order or customer was deleted before the email went out, the
  email fails straight away (Laravel 8 can't skip it quietly) and the Failed emails page says "The
  order or customer was deleted before the email was sent", with Delete.
- **Also send in the background:** verify email, forgot password (customer and admin), contact form,
  cart reminders and low-stock digest.
- **The emails don't change:** they're rendered at send time with the order as it is then. If an
  order changes within those few seconds, the email shows the newer details.
- README: how to run the worker locally and on a server.

**Size:** S.

## 4. Phase 2: Keep the worker running, and notice when it isn't

- **Supervisor config** in `deploy/supervisor/codastore-worker.conf`:
  - restarts automatically
  - writes to its own log
  - restarts every hour or after 500 jobs, to pick up code changes and keep memory low
- **Shared-hosting fallback:** with `QUEUE_VIA_SCHEDULER=true`, the scheduler (already called every
  minute by cron) runs `queue:work --stop-when-empty --max-time=50` every minute, never two at once.
- **After each deploy:** `php artisan queue:restart` (added to the README's deploy steps), so the
  worker uses the new code.
- **Dashboard warning**, shown to staff with the Settings permission:
  - "Emails are waiting to send. Is the worker running?" when an email has waited more than 10 minutes
  - "3 emails failed. Review" with a link to the page below

**Size:** S.

## 5. Phase 3: Failed emails page

**Admin → Settings → Failed emails** (Settings permission). It lists each failed email with:
- the email type (e.g. "Order shipped"), recipient, order number and when it failed
- the reason in plain words, e.g. "Mail server refused the login", "Couldn't connect to the mail
  server" or "Address rejected", with the technical detail in a fold-out

Actions:
- **Retry**, **Retry all** and **Delete**, each recorded in the activity log.
- After the mail settings are fixed, **Retry all** sends everything that was waiting.

**Size:** S–M.

---

## 6. Order of work and effort

| Order | Phase | Depends on | Size |
|---|---|---|---|
| 1 | Send emails in the background | decisions 1, 3, 5 | S |
| 2 | Keep the worker running + dashboard warning | Phase 1, decision 2 | S |
| 3 | Failed emails page | Phase 1, decision 4 | S–M |

Phase 1 alone fixes the slowness, as long as a worker is running. Phases 2 and 3 make sure emails
aren't lost when a worker isn't running or the mail server fails.

## 7. Testing

- **Speed:**
  - Checkout, Create order, Shipped, Cancel and the return actions finish without waiting for the
    mail server.
  - Checked with a mail server that is deliberately slow (a 5-second delay), with the page time
    measured before and after.
- **Queued, not sent:** each action puts the right email on the `emails` queue for the right person,
  and nothing is sent until the worker runs.
- **After commit:** an email queued inside a transaction that is rolled back is never sent.
- **Retries:** a mail server that fails twice and then works sends the email on the third try; one
  that always fails ends up on the Failed emails page.
- **Deleted records:** an email for an order deleted before sending fails once and says why.
- **Warnings:** the dashboard warning appears when an email has waited more than 10 minutes, and when
  something failed.
- **Failed emails page:** Retry sends the email and removes it from the list; Delete removes it.
  Staff without the Settings permission get the "no access" page.
- **Browser check with Mailtrap:**
  - with the worker running, emails arrive within seconds
  - with the worker stopped, they wait and then all arrive once it starts

## 8. Risks

| Risk | How the plan handles it |
|---|---|
| Worker not running, so emails never go out | Dashboard warning after 10 minutes; Supervisor restarts it; scheduler fallback |
| An email sent twice (the mail server accepted it but timed out before answering) | Rare; a 30-second timeout and only 3 tries keep it unlikely. Customers might see a duplicate, never a missing email |
| Email sent for an order that was rolled back | Emails wait for the save to commit |
| Worker running old code after a deploy | `queue:restart` in the deploy steps; hourly restarts |
| Mailtrap / SMTP rate limits when many emails send at once | One worker sends them one by one; retries with growing delays |
| Forgetting the worker locally, so "emails don't arrive" | README note, plus the dashboard warning after 10 minutes |
