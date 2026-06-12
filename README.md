# Helpdesk

A multi-tenant, MSP-style service desk built on Laravel — a single platform where
an internal IT/MSP team supports many business units (customer organizations) from
one staff console, while each organization gets its own branded, self-service
customer portal.

It combines ITIL-style ticketing, SLA tracking, change management, project work with
technician time tracking, a knowledge base, two-way email-to-ticket, and optional
AI-assisted triage and replies.

**Adopting this for your business unit?** Start with the
**[Deployment Guide](DEPLOYMENT.md)** — requirements, a one-command local quick start,
production setup (queue worker + scheduler), and per-BU configuration.

```bash
git clone https://github.com/jsernaharris/helpdesk.git
cd helpdesk && composer setup && php artisan db:seed && composer dev
# open http://localhost:8000
```

---

## What it does

### Service desk (ITIL-aligned)
- **Tickets** across four types — incidents, service requests, problems, and changes —
  with auto-generated numbers (`INC-`, `SR-`, `PRB-`, `CHG-`), statuses, priorities,
  and an impact/urgency matrix.
- **Assignment** to technicians or teams, with merge, escalation, internal notes, and a
  full activity history per ticket.
- **Queues** — org-scoped service lines (e.g. Cybersecurity, AI, Datacenter Services).
  Tickets route into a queue from their inbound mailbox or a submission form, and the
  staff list filters by queue.
- **SLAs & business hours** — per-organization SLA plans with response/resolution
  targets, business-hours-aware due dates, breach logging, and escalation rules.
- **Problem & change management** — problem records linked to incidents; a full change
  workflow (draft → submitted → approved → implementing → completed) with per-org
  change policies, categories/templates, a Change Advisory Board (CAB), blackout
  windows, post-implementation reviews, and a change calendar.

### Projects & technician time tracking
- **Projects** are scoped engagements against a customer org (e.g. "patch a BU's
  servers") with a `PRJ-` number, status, dates, a customer contact, and assigned staff.
- **Time tracking** — technicians log manual entries (date, hours, notes, optional
  ticket link); projects show running totals and a filterable **CSV export** for
  billing/reporting.
- **Work ledger** — a combined timeline of auto-recorded events (status changes,
  members, logged time) plus manual work-log notes, with an internal/customer-visible
  distinction.

### Knowledge base
- Markdown articles with categories (hierarchical, per-org or global), draft/published
  workflow, and public / internal / customer-specific visibility.
- Self-service browsing in the customer portal, including an optional AI assistant.

### Email-to-ticket (two-way)
- Inbound mail becomes tickets and threads onto existing ones; staff replies are sent
  back out from the originating mailbox.
- Connects via **IMAP/SMTP** or **Microsoft 365 (Microsoft Graph, app-only)** —
  ideal for shared inboxes. Multiple mailboxes, each with a default queue, type, and
  priority. Sender domains route mail to the right organization.

### AI assistance (optional)
- Ticket triage suggestions, drafted replies, and a knowledge-base chat assistant,
  powered by **Azure AI Foundry**. Entirely optional — the system runs fully without it.

### Multi-tenancy & access
- **Organizations** can own many email domains, so sibling business-unit domains all
  resolve to the same org. Tenant context automatically scopes data; MSP staff can see
  across orgs, customers only see their own.
- **Roles & permissions** via Spatie: `msp_admin`, `msp_technician`, `customer_admin`,
  `customer_user`, plus custom roles. Granular permissions gate every module.
- **Authentication** via local accounts or **Microsoft Entra ID (Azure) SSO**, with
  auto-provisioning.

### Customer portal
- A branded, per-org self-service area: submit and track tickets (with custom form
  templates), follow change requests, view project status and updates, and browse the
  knowledge base — all scoped to the customer's organization, read-only where
  appropriate.

---

## Tech stack

- **Backend:** PHP 8.5, Laravel 13, Eloquent, queues & scheduler
- **Frontend:** Blade + Tailwind CSS, Vite, a touch of Alpine.js
- **Auth/roles:** `spatie/laravel-permission`, `socialiteproviders/microsoft` (Entra SSO)
- **Email:** `webklex/laravel-imap` (IMAP/SMTP) and Microsoft Graph (app-only)
- **Content:** `league/commonmark` (Markdown knowledge base)
- **AI:** Azure AI Foundry (optional)
- **Database:** SQLite for local/dev & tests; MySQL/MariaDB for production

---

## Configuration highlights

Set these in `.env` (see `.env.example` and the [Deployment Guide](DEPLOYMENT.md)):

- `APP_NAME`, `APP_LOGO` — branding (the logo is swappable per deployment)
- `DB_*` — MySQL/MariaDB connection in production
- **Azure SSO:** `MICROSOFT_CLIENT_ID`, `MICROSOFT_CLIENT_SECRET`, `MICROSOFT_TENANT_ID`
- **Azure AI Foundry:** `AZURE_AI_FOUNDRY_ENDPOINT`, `AZURE_AI_FOUNDRY_KEY`,
  `AZURE_AI_FOUNDRY_DEPLOYMENT`
- Mailboxes are configured in-app (Staff → Mailboxes), with credentials stored encrypted.

App defaults (ticket prefixes, auto-close window, SLA warning threshold, default
timezone) live in `config/helpdesk.php`.

---

## Development

```bash
composer setup     # install deps, create .env, key, migrate, build assets
php artisan db:seed   # demo orgs, users, roles, sample data
composer dev       # serve + queue worker + log tail + Vite, all at once
```

Production needs a **queue worker** (outbound email, AI jobs) and the **scheduler**
(mail fetch, SLA checks, auto-close) running — see the Deployment Guide.

### Testing

```bash
composer test      # or: php artisan test
```

Feature tests run against in-memory SQLite and cover email routing, mailbox management,
projects & time tracking, KB category management, roles, SSO provisioning, and more.

---

## Project layout

```
app/
  Http/Controllers/Staff/   # staff console (tickets, changes, projects, KB, admin)
  Http/Controllers/Portal/  # customer self-service portal
  Models/                   # Eloquent models (Ticket, Project, Organization, ...)
  Services/                 # TicketService, EmailProcessingService, AI, mail drivers
database/
  migrations/               # schema
  seeders/                  # roles/permissions + demo data
resources/views/
  layouts/, staff/, portal/ # Blade templates
routes/
  staff.php, portal.php, web.php
```

---

## License

Built on the [Laravel](https://laravel.com) framework, which is open-source software
licensed under the [MIT license](https://opensource.org/licenses/MIT). Application code
in this repository is owned by its maintainers; contact the repository owner for usage
terms.
