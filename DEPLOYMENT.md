# Deployment Guide

This guide is for a business unit (BU) adopting this helpdesk for its own use. It
covers what the application is, what it needs to run, and how to stand it up for
local evaluation, staging, and production.

---

## 1. What this is

A multi-tenant MSP-style helpdesk built on Laravel. It provides:

- **Customer portal** (`/portal`) — ticket submission, status tracking, and a
  knowledge base for end users.
- **Staff console** (`/staff`) — ticket queue, assignment, SLA tracking, change
  management (ITIL), knowledge base authoring, per-organization form templates,
  and user management.
- **Email-to-ticket (inbound & outbound)** — polls per-organization mailboxes,
  turns messages into tickets, and emails staff replies back to the requester.
  Each mailbox uses either **IMAP/SMTP** or **Microsoft Graph** (app-only OAuth
  for Microsoft 365 shared mailboxes, no Basic Auth).
- **Optional AI assist** — reply suggestions, triage, and KB chat via Azure AI
  Foundry. Disabled by default; leaving the keys blank turns the features off.

Tenancy is per **organization**: each customer org has its own users, tickets,
SLA plan, business hours, and form templates.

---

## 2. Requirements

| Component | Version / notes |
|-----------|-----------------|
| PHP       | **8.3+** (8.4 tested) with `mbstring`, `intl`, `pdo`, `openssl`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`. The PHP `imap` extension is only required for mailboxes using the IMAP driver — Microsoft Graph mailboxes need only `openssl` + outbound HTTPS |
| Composer  | 2.x |
| Node.js   | **20+** (for the Vite 8 / Tailwind 4 asset build) |
| Database  | SQLite (default, good for eval) · MySQL 8 / MariaDB 10.6+ · PostgreSQL 13+ |
| Web server| Nginx or Apache fronting PHP-FPM (production); `php artisan serve` for local |
| Process supervisor | systemd or Supervisor (for the queue worker and scheduler) |

Mailboxes are configured **in-app per organization** under **Staff → Mailboxes**;
their credentials (IMAP/SMTP passwords or Graph client secrets) are stored
encrypted in the database, so no mailbox keys belong in `.env`. A global
`MAIL_*` SMTP config is still used for system notifications (see §4); per-ticket
email replies go out through the originating mailbox's driver.

---

## 3. Quick start (local evaluation)

```bash
git clone https://github.com/jsernaharris/helpdesk.git
cd helpdesk

# One-shot bootstrap: installs deps, copies .env, generates APP_KEY,
# runs migrations, installs npm packages, builds assets.
composer setup

# Load roles, an MSP org, SLA plan, business hours, and demo data.
php artisan db:seed

# Run everything (server + queue + logs + vite) in one command:
composer dev
```

Then open <http://localhost:8000>.

**Demo logins** (all seeded with password `password` — change or disable
seeding before any non-local environment):

| Role | Email |
|------|-------|
| MSP staff (admin) | `admin@msphelpdesk.com` |
| MSP staff (technician) | `tech@msphelpdesk.com` |
| Customer admin | `admin@acme.com` |
| Customer user | `jane@acme.com` |

> The demo data lives in `DemoDataSeeder` / `MspOrganizationSeeder`. For a clean
> tenant with no demo content, run only the foundational seeders:
> `php artisan db:seed --class=RolesAndPermissionsSeeder` (then create your own
> org and admin).

---

## 4. Configuration (`.env`)

`.env.example` is the source of truth — copy it and fill in the values that
matter for your environment. Key sections:

```dotenv
APP_NAME="Your BU Helpdesk"
APP_ENV=production
APP_KEY=                 # set via `php artisan key:generate`
APP_DEBUG=false          # MUST be false in production
APP_URL=https://helpdesk.your-bu.example.com

# Database — switch off sqlite for production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk
DB_USERNAME=helpdesk
DB_PASSWORD=

# Queue & cache use the database by default (no Redis required)
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

# Outbound mail (notifications)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="helpdesk@your-bu.example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Optional: Azure AI Foundry — leave blank to disable all AI features
AZURE_AI_FOUNDRY_ENDPOINT=
AZURE_AI_FOUNDRY_KEY=
AZURE_AI_FOUNDRY_DEPLOYMENT=
AZURE_AI_FOUNDRY_API_VERSION=2024-10-21

# Optional: Microsoft Graph mailbox endpoints — only override for sovereign
# clouds (e.g. GCC High / 21Vianet). NOT secrets; per-mailbox Graph credentials
# are entered in the app, not here. Defaults target the global cloud.
# GRAPH_AUTHORITY=https://login.microsoftonline.com
# GRAPH_BASE_URL=https://graph.microsoft.com/v1.0
# GRAPH_TIMEOUT=30
```

> **Secrets:** `.env` is git-ignored and must never be committed. Provision it
> through your platform's secret manager or config management, not the repo.

---

## 5. Production deployment

### 5.1 Build and migrate

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate          # first deploy only
php artisan migrate --force
npm ci && npm run build           # compiles assets into public/build

# Cache framework config/routes/views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link          # public disk symlink
```

Point your web server's document root at `public/`. Ensure `storage/` and
`bootstrap/cache/` are writable by the web/PHP-FPM user.

### 5.2 Queue worker (required)

Inbound email processing and notifications run on the **database queue**, so a
worker must be running:

```bash
php artisan queue:work --tries=3 --timeout=120
```

Run it under Supervisor or a systemd service so it restarts on failure and on
deploy. After each deploy, restart workers so they pick up new code:

```bash
php artisan queue:restart
```

### 5.3 Scheduler (required)

The app relies on Laravel's scheduler for inbound email polling, SLA checks,
escalations, and auto-close. Add **one** cron entry that runs the scheduler
every minute:

```cron
* * * * * cd /path/to/helpdesk && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs (defined in `routes/console.php`):

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `helpdesk:fetch-emails` | every minute | Pull new messages from active mailboxes |
| `helpdesk:check-sla` | every 5 min | Flag SLA breaches |
| `helpdesk:run-escalations` | every 5 min | Apply escalation rules |
| `helpdesk:auto-close-resolved` | daily | Close tickets resolved past the grace window |

---

## 6. Post-install setup (per BU)

1. **Create your organization and admin user** — either via the staff console
   (logged in as a seeded admin) or by writing a small seeder modeled on
   `MspOrganizationSeeder`.
2. **Configure inbound mailboxes** — add each support mailbox under
   **Staff → Mailboxes** (requires `settings.manage`). Two drivers are available;
   credentials for both are stored encrypted in the `email_mailboxes` table and
   `helpdesk:fetch-emails` polls all active ones. Use the **Test Connection**
   button after saving.
   - **IMAP / SMTP (Basic Auth)** — set IMAP host/port/encryption/username/
     password and the SMTP equivalents. Use an app-specific password where the
     provider supports it. Note Microsoft 365 has deprecated Basic Auth.
   - **Microsoft Graph (Microsoft 365 / shared mailbox)** — the supported way to
     read and send from an M365 shared mailbox with no user password. Register an
     Azure AD application, grant it the **Application** permissions
     `Mail.ReadWrite` and `Mail.Send`, and **grant admin consent**. Strongly
     recommended: scope the app to only the helpdesk mailbox with an
     [Application Access Policy](https://learn.microsoft.com/graph/auth-limit-mailbox-access).
     Then enter the **Tenant ID**, **Client ID**, **Client Secret**, and the
     shared **Mailbox** address in the mailbox form. The whole loop (poll + reply)
     runs over Graph — no SMTP Basic Auth required. Endpoint hosts/timeout can be
     overridden via the optional `GRAPH_AUTHORITY` / `GRAPH_BASE_URL` /
     `GRAPH_TIMEOUT` env vars (defaults target the global cloud).
3. **Set SLA plans and business hours** — defaults are seeded by
   `DefaultSlaPlanSeeder` / `DefaultBusinessHoursSeeder`; adjust to the BU's
   commitments.
4. **Define form templates** — set up the custom intake fields your BU needs.
5. **(Optional) Enable AI** — fill in the `AZURE_AI_FOUNDRY_*` values and rerun
   `php artisan config:cache`.

---

## 6a. Single sign-on with Microsoft Entra ID (optional)

SSO **authenticates** users; **authorization stays in the app** — roles and a
technician's site/organization scope are assigned from the staff **Users**
screen, and custom roles are managed under **Roles**. Leaving the client
id/secret blank disables SSO entirely (the button is hidden and the
`/auth/azure/*` routes return 404), so password login keeps working.

**1. Register an app in Entra ID** (Azure portal → *App registrations* → *New*):

- Redirect URI (Web): `https://<your-app-host>/auth/azure/callback`
- Under *Certificates & secrets*, create a client secret.
- Note the **Application (client) ID**, the **secret value**, and your
  **Directory (tenant) ID**.

**2. Configure `.env`:**

```dotenv
AZURE_SSO_CLIENT_ID=<application-client-id>
AZURE_SSO_CLIENT_SECRET=<client-secret-value>
AZURE_SSO_REDIRECT_URI="${APP_URL}/auth/azure/callback"
AZURE_SSO_TENANT_ID=<directory-tenant-id>   # locks sign-in to your tenant
AZURE_SSO_DEFAULT_ORG_ID=                    # org new SSO users join (see below)
```

Then `php artisan config:cache`.

**3. How provisioning works:**

- Any user in your tenant can sign in with Microsoft. On **first** login they
  are auto-provisioned as a **`customer_user`** in the default organization
  (`AZURE_SSO_DEFAULT_ORG_ID`, or the first active non-MSP org if unset) so they
  can immediately file and track tickets.
- Provisioning happens **once**. To make someone a technician or admin, edit
  them in the staff **Users** screen: assign the role(s) and, for a technician,
  set their organization to the MSP org and scope them to specific sites. Later
  Entra group changes do **not** alter app roles — the app is the source of
  truth after first login.
- Existing accounts are linked by email on first SSO login, preserving their
  current roles.

> Set `AZURE_SSO_DEFAULT_ORG_ID` to a real customer organization before enabling
> SSO; if no non-MSP org exists, new-user provisioning is refused with an error.

---

## 7. Upgrading

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
```

---

## 8. Troubleshooting

| Symptom | Check |
|---------|-------|
| Emails not turning into tickets | Is the scheduler cron running? Is a `queue:work` process up? Is the mailbox marked active? Use **Staff → Mailboxes → Test Connection** to validate credentials |
| Graph mailbox fails to connect | Confirm the Azure app has the **Application** permissions `Mail.ReadWrite` + `Mail.Send` with **admin consent**, the client secret hasn't expired, and any Application Access Policy includes the mailbox |
| Staff replies not emailed to requester | Only **email-sourced** tickets send replies (web/portal tickets don't); confirm the ticket's mailbox is active and a `queue:work` process is running |
| Assets missing / unstyled | Run `npm run build`; confirm `public/build` exists and `storage:link` was run |
| 500 with no detail | `APP_DEBUG=false` hides errors — check `storage/logs/laravel.log` |
| Config changes not taking effect | Re-run `php artisan config:cache` (cached config ignores live `.env` edits) |
| AI features absent | Expected when `AZURE_AI_FOUNDRY_*` are blank — they're opt-in |

---

## 9. Security notes

- Set `APP_DEBUG=false` and a strong unique `APP_KEY` in every non-local
  environment.
- Disable demo seeding (`DemoDataSeeder`) outside of evaluation, and rotate or
  remove the default `password` accounts.
- Serve only `public/` over HTTPS; everything above it should be inaccessible.
- Restrict mailbox credentials to least privilege. For Microsoft Graph, scope the
  app registration to only the helpdesk mailbox with an Application Access Policy
  and rotate the client secret periodically. Mailbox credentials are stored
  encrypted in the database (keyed by `APP_KEY`) — protect and back up `APP_KEY`
  accordingly, since rotating it invalidates stored secrets.
