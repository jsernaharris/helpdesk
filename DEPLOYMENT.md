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
| PHP       | **8.5+** with `mbstring`, `intl`, `pdo`, `openssl`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`. The PHP `imap` extension is only required for mailboxes using the IMAP driver — Microsoft Graph mailboxes need only `openssl` + outbound HTTPS |
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
APP_LOGO=                # optional: images/logo.svg (under public/) or a full URL; blank = show APP_NAME as text
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

## 5. Production deployment (Ubuntu)

Tested on **Ubuntu 22.04 / 24.04 LTS**. The commands below assume the app lives
at `/var/www/helpdesk` and is served by PHP-FPM as `www-data`. Adjust paths to
taste.

### 5.1 Install system packages

```bash
# PHP 8.5 from the ondrej PPA
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y \
  php8.5-fpm php8.5-cli php8.5-mbstring php8.5-intl php8.5-xml \
  php8.5-bcmath php8.5-curl php8.5-zip php8.5-mysql php8.5-imap php8.5-gd \
  nginx git unzip

# Composer 2
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 (for the Vite asset build)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

> `php8.5-imap` is only needed for IMAP mailboxes — Microsoft Graph mailboxes
> don't require it. The `php8.5-mysql` driver works for both MySQL and MariaDB.

### 5.2 Database server (MySQL or MariaDB)

Install **one** of the following. Both speak the MySQL protocol and use the same
`php8.5-mysql` driver; pick whichever your BU standardizes on.

```bash
# Option A — MySQL 8
sudo apt install -y mysql-server

# Option B — MariaDB 10.6+
sudo apt install -y mariadb-server
```

Either way, harden the install (sets the root auth method, removes anonymous
users and the test DB):

```bash
sudo mysql_secure_installation
```

Create the database and a least-privilege user (this SQL is identical for both
engines):

```bash
sudo mysql <<'SQL'
CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'helpdesk'@'localhost' IDENTIFIED BY 'change-me';
GRANT ALL PRIVILEGES ON helpdesk.* TO 'helpdesk'@'localhost';
FLUSH PRIVILEGES;
SQL
```

Then set the matching connection in `.env`. Laravel ships a dedicated `mariadb`
driver — use it on MariaDB so version detection and grammar are correct:

```dotenv
DB_CONNECTION=mysql      # MySQL 8
# DB_CONNECTION=mariadb  # MariaDB 10.6+
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk
DB_USERNAME=helpdesk
DB_PASSWORD=change-me
```

Both connections default to `utf8mb4` / `utf8mb4_unicode_ci` (full Unicode,
required for emoji in ticket bodies) — see `config/database.php`.

### 5.3 Deploy the app

```bash
sudo git clone https://github.com/jsernaharris/helpdesk.git /var/www/helpdesk
cd /var/www/helpdesk

cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*, MAIL_* (see §4)

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

Give PHP-FPM (`www-data`) ownership of the writable paths:

```bash
sudo chown -R www-data:www-data /var/www/helpdesk/storage /var/www/helpdesk/bootstrap/cache
sudo find /var/www/helpdesk/storage /var/www/helpdesk/bootstrap/cache -type d -exec chmod 775 {} \;
```

### 5.4 Nginx + PHP-FPM

Create `/etc/nginx/sites-available/helpdesk`:

```nginx
server {
    listen 127.0.0.1:8080;          # localhost only — Cloudflare Tunnel fronts it (§5.7)
    server_name helpdesk.your-bu.example.com;
    root /var/www/helpdesk/public;

    index index.php;
    charset utf-8;
    client_max_body_size 25M;       # matches the 25 MB attachment limit

    # Restore the real visitor IP. cloudflared forwards from localhost, so
    # without this every request logs as 127.0.0.1 and Laravel's request->ip()
    # (rate limiting, audit trail) is wrong. Cloudflare sets CF-Connecting-IP.
    set_real_ip_from 127.0.0.1;
    real_ip_header CF-Connecting-IP;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/helpdesk /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

> The `set_real_ip_from` / `real_ip_header` lines pair with Laravel's
> `trustProxies` (§5.7) so the app sees the genuine client address end to end. If
> you front the app with the Cloudflare **proxy** (orange-cloud DNS) instead of a
> tunnel, requests arrive from Cloudflare's edge ranges rather than localhost —
> swap `127.0.0.1` for [Cloudflare's IP ranges](https://www.cloudflare.com/ips/)
> (one `set_real_ip_from` per range).

> Prefer terminating TLS at Nginx instead of a tunnel? Change `listen` to
> `443 ssl`, add your certificate directives, and open the firewall, and drop the
> two `real_ip` lines (no proxy in front). With the Cloudflare Tunnel (§5.7), keep
> Nginx on localhost and leave inbound ports closed.

### 5.5 Queue worker (required, systemd)

Inbound email processing, **outbound ticket replies**, and notifications run on
the **database queue**, so a worker must always be running. Create
`/etc/systemd/system/helpdesk-worker.service`:

```ini
[Unit]
Description=Helpdesk queue worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/helpdesk
ExecStart=/usr/bin/php /var/www/helpdesk/artisan queue:work --tries=3 --timeout=120 --sleep=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now helpdesk-worker
```

After each deploy, signal the worker to restart so it picks up new code:

```bash
php artisan queue:restart
```

### 5.6 Scheduler (required, cron)

The scheduler drives email polling, SLA checks, escalations, and auto-close. Add
**one** cron entry as `www-data` (`sudo crontab -u www-data -e`):

```cron
* * * * * cd /var/www/helpdesk && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs (defined in `routes/console.php`):

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `helpdesk:fetch-emails` | every minute | Pull new messages from active mailboxes |
| `helpdesk:check-sla` | every 5 min | Flag SLA breaches |
| `helpdesk:run-escalations` | every 5 min | Apply escalation rules |
| `helpdesk:auto-close-resolved` | daily | Close tickets resolved past the grace window |

### 5.7 Public access via Cloudflare Tunnel

A [Cloudflare Tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/)
exposes the app over HTTPS **without opening any inbound ports** — the
`cloudflared` daemon makes an outbound connection to Cloudflare and forwards
requests to Nginx on localhost. You need a domain managed by Cloudflare (the free
plan works).

**1. Install `cloudflared`:**

```bash
sudo mkdir -p --mode=0755 /usr/share/keyrings
curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg | \
  sudo tee /usr/share/keyrings/cloudflare-main.gpg >/dev/null
echo "deb [signed-by=/usr/share/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared $(lsb_release -cs) main" | \
  sudo tee /etc/apt/sources.list.d/cloudflared.list
sudo apt update && sudo apt install -y cloudflared
```

**2. Authenticate and create the tunnel:**

```bash
sudo cloudflared tunnel login                 # opens a browser to authorize your domain
sudo cloudflared tunnel create helpdesk       # writes credentials to /root/.cloudflared/<UUID>.json
sudo cloudflared tunnel route dns helpdesk helpdesk.your-bu.example.com
```

**3. Configure ingress** — create `/etc/cloudflared/config.yml`:

```yaml
tunnel: helpdesk
credentials-file: /root/.cloudflared/<UUID>.json

ingress:
  - hostname: helpdesk.your-bu.example.com
    service: http://127.0.0.1:8080      # the Nginx server block from §5.4
  - service: http_status:404
```

**4. Run it as a service:**

```bash
sudo cloudflared service install
sudo systemctl enable --now cloudflared
```

**5. Tell Laravel it is behind a proxy.** Cloudflare terminates TLS, so the app
must trust the proxy to build correct `https://` URLs and avoid redirect loops.
In `bootstrap/app.php`, inside `->withMiddleware(...)`, add:

```php
$middleware->trustProxies(at: '*');
```

Set `APP_URL=https://helpdesk.your-bu.example.com`, then re-run
`php artisan config:cache`. The built-in health endpoint `/up` makes a good
tunnel health check.

> With the tunnel handling ingress, the server needs **no public IP, no open
> ports, and no inbound firewall rules** — only outbound 443 to Cloudflare.

---

## 6. Post-install setup (per BU)

1. **Create your organization and admin user** — either via the staff console
   (logged in as a seeded admin) or by writing a small seeder modeled on
   `MspOrganizationSeeder`.
2. **Configure the shared support inbox(es)** — add each support mailbox under
   **Staff → Mailboxes** so inbound email becomes tickets and staff replies go
   back out. Full walkthrough (both drivers + the Microsoft 365 app
   registration) in **§6b**.
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

## 6b. Shared inbox (email-to-ticket) setup

The helpdesk turns a **shared support mailbox** (e.g. `support@your-bu.example.com`)
into tickets and emails staff replies back to the requester. Mailboxes are
configured **in-app, per organization** — no mailbox credentials go in `.env`.

Each mailbox is added under **Staff → Mailboxes** by a user with the
`settings.manage` permission (the `msp_admin` role has it). Credentials are
stored **encrypted** in the `email_mailboxes` table (keyed by `APP_KEY`), and the
scheduler's `helpdesk:fetch-emails` job (§5.6) polls every **active** mailbox
once a minute. Outbound replies are queued, so the **queue worker (§5.5) must be
running** for both inbound processing and replies.

> **Prerequisite:** the queue worker and scheduler from §5.5–5.6 must be live.
> Without them, mail is never polled and replies never send.

### Add a mailbox

**Staff → Mailboxes → New**, then pick a driver. Common settings for both:

| Field | Notes |
|-------|-------|
| **Name** | Display label, e.g. "ACME Support" |
| **Email address** | The shared mailbox address tickets arrive at |
| **Organization** | Which tenant org these tickets belong to |
| **Active** | Must be on for the mailbox to be polled |
| **Auto-create tickets** | Turn inbound mail into tickets (on by default) |
| **Default priority / type** | Applied to tickets created from this mailbox |

After saving, use **Test Connection** on the mailbox page to validate
credentials before relying on it.

### Driver A — IMAP / SMTP (Basic Auth)

For Gmail/Workspace, generic IMAP providers, or any mailbox that still allows
Basic Auth. Set the IMAP read settings and the SMTP send settings:

- **IMAP:** host, port (default `993`), encryption (`ssl`/`tls`/`none`),
  username, password.
- **SMTP:** host, port (default `587`), encryption (`ssl`/`tls`/`none`),
  username, password.

Use an **app-specific password** where the provider supports it. Inbound mail is
read from the **INBOX** folder and marked as read once fetched.

> **Microsoft 365 has deprecated Basic Auth** — use the Microsoft Graph driver
> below for M365 shared mailboxes.

### Driver B — Microsoft Graph (Microsoft 365 shared mailbox)

The supported way to read and send from an M365 shared mailbox with **no user
password** — the whole loop (poll + reply) runs over Graph with app-only OAuth.

**1. Register an app in Entra ID** (Azure portal → *App registrations* → *New*):

- Under *API permissions*, add **Application** permissions (not delegated)
  `Mail.ReadWrite` and `Mail.Send` on Microsoft Graph, then **grant admin
  consent**.
- Under *Certificates & secrets*, create a **client secret** and copy its value.
- Note the **Application (client) ID** and your **Directory (tenant) ID**.

**2. Scope the app to just the helpdesk mailbox (strongly recommended).** Without
this, the app can read/send as *any* mailbox in the tenant. Restrict it with an
[Application Access Policy](https://learn.microsoft.com/graph/auth-limit-mailbox-access).

**3. Enter the credentials in the mailbox form:** **Tenant ID**, **Client ID**,
**Client Secret**, and the shared **Mailbox** address (UPN). No SMTP/Basic Auth
required.

Endpoint hosts and timeout default to the global cloud and only need overriding
for sovereign clouds (GCC High / 21Vianet) — set the optional `GRAPH_AUTHORITY` /
`GRAPH_BASE_URL` / `GRAPH_TIMEOUT` env vars (see §4) and re-run
`php artisan config:cache`. App-only access tokens are cached in the app's
configured cache store (the database by default — no Redis needed).

### How it flows

1. `helpdesk:fetch-emails` polls each active mailbox every minute and queues a
   job per new message.
2. The worker creates (or appends to) a ticket, tagging it with the originating
   mailbox.
3. When staff reply, the reply is queued and sent **back through that mailbox's
   driver** — so only **email-sourced** tickets send outbound email (web/portal
   tickets don't).

See the **Troubleshooting** table (§8) for the common "emails not turning into
tickets" and "replies not sending" checks.

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
| Redirect loops or `http://` links behind Cloudflare | Add `$middleware->trustProxies(at: '*')` in `bootstrap/app.php`, set `APP_URL` to the `https://` host, then `php artisan config:cache` (see §5.7) |
| Tunnel up but 502/404 | Confirm Nginx is listening on `127.0.0.1:8080` and the `config.yml` `service:` URL matches; check `sudo systemctl status cloudflared` |
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
