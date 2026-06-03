# HelpDesk SaaS

A Laravel **API-only** help desk and work order management system built as a portfolio and learning project. It models how a technical support or maintenance company might run day-to-day operations: clients, equipment, support tickets, work orders, permissions, file attachments, audit history, and background jobs. Consumers integrate via JSON endpoints; there is no server-rendered frontend in this repository.

The focus is not a tutorial CRUD app, but a product-shaped codebase with explicit business rules, authorization boundaries, and incremental delivery through milestones.

---

## Features

| Area | Status |
|------|--------|
| Project foundation (Laravel, migrations scaffold, queue tables) | Implemented |
| Product documentation (`docs/`) | In progress |
| Authentication | Planned |
| Users and roles (Admin, Technician, Attendant) | Planned |
| Clients (CRUD, validation, uniqueness rules) | Planned |
| Machines / equipment (per-client linkage) | Planned |
| Support tickets (status, priority, policies) | Planned |
| Work orders (ticket conversion, transactions, numbering) | Planned |
| File uploads (images, PDF, storage cleanup) | Planned |
| Change history / audit trail | Planned |
| Notifications and queue jobs | Planned |
| REST API (resources, versioning, consistent responses) | Planned |
| Automated tests (PHPUnit; Pest optional) | Planned |
| API documentation and refinement | Planned |

---

## Project Goals

**Learning**

- Practice Laravel in a realistic domain (support + field service), not isolated CRUD exercises.
- Work with Eloquent relationships, Form Requests, Policies, observers/events, queues, and file storage.
- Build confidence with automated tests against business rules.

**Engineering**

- Keep controllers thin and push rules into the domain layer where they belong.
- Enforce authorization at the policy/gate level on every API action.
- Record audit history automatically instead of relying on manual logging.
- Ship incrementally via milestones so each phase stays reviewable and deployable in spirit.

**Product mindset**

- Simulate operational workflows (open ticket → assign → work order → resolve).
- Design with future SaaS concerns in mind (roles, audit, async work) without over-building on day one.

---

## Architecture and Development Philosophy

The application follows Laravel’s MVC structure with conventions chosen for clarity and maintainability:

- **Thin controllers** — HTTP layer delegates validation, authorization, and heavy logic elsewhere.
- **Form Requests** — Input validation and authorization hooks stay explicit and reusable.
- **Policies and Gates** — Role-based access (e.g. who may change technical ticket status) is centralized.
- **Explicit business rules** — Documented requirements (see `docs/01-visao-geral.md`) drive implementation; magic behavior in controllers is avoided.
- **Transactions** — Critical flows (e.g. creating a work order from a ticket) run inside database transactions.
- **Observers / events** — Side effects such as audit entries and notifications stay decoupled from controllers.
- **Documentation-first** — Scope, roles, and business rules are defined before or alongside code.
- **Milestone-driven delivery** — Each phase delivers a coherent slice (foundation → tickets → OS → files → queue/tests → polish).

---

## Modules

### Authentication

API authentication for internal users (e.g. token or Sanctum). Foundation for role-aware requests and policy checks.

### Users and Roles

Three roles drive permissions:

- **Admin** — Full system management.
- **Technician** — Execute work orders, update technical ticket status, attach files.
- **Attendant** — Create tickets, manage clients; restricted from critical technical status changes.

### Clients

Legal entities (individual or company) served by the company. Linked to machines and tickets; deletion guarded when dependencies exist.

### Machines

Equipment owned by a client (serial number, linkage). Every machine belongs to exactly one client.

### Support Tickets

Operational requests with priority (`low`, `medium`, `high`) and status (`open`, `in_progress`, `resolved`, `cancelled`). Optional machine association; resolution metadata when closed.

### Work Orders

Formal service orders generated from tickets. Single work order per ticket; lifecycle distinct from ticket status.

### File Uploads

Attachments on relevant records (images and PDF). Physical removal from storage when records are deleted.

### History / Audit

Automatic log of meaningful changes (status, amounts, assignee, etc.) with user and timestamp.

### Notifications / Queue

Async processing for notifications and other background work via Laravel queues and jobs.

### Tests

Feature and unit tests covering authorization, state transitions, and core business rules.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Framework | [Laravel](https://laravel.com) 13.x |
| Language | PHP 8.3+ |
| Database | PostgreSQL (local instance via Docker — **planned**, not set up yet) |
| API | REST / JSON |
| Auth & authorization | Laravel Auth, Sanctum (or equivalent), Policies, Gates |
| Storage | Laravel filesystem (local / configurable) |
| Background work | Queue, Jobs (database driver by default) |
| Testing | PHPUnit 12 |
| Code style | Laravel Pint |
| Local infrastructure | Docker (PostgreSQL container — to be added) |

---

## Business Rules

A subset of enforced or planned rules (full list in `docs/01-visao-geral.md`):

| ID | Rule |
|----|------|
| RN-001 | A client may have multiple machines. |
| RN-003 | A client cannot be deleted while machines or tickets are linked. |
| RN-010 | A cancelled ticket cannot be resolved. |
| RN-011 | Only **Technician** or **Admin** may change ticket status. |
| RN-013 | A ticket may generate **at most one** work order. |
| RN-014 | Work order creation runs inside a **database transaction**. |
| RN-016 | A finalized work order cannot return to `open`. |
| RN-019 | Relevant changes must produce an **automatic** audit history entry (user, date, change). |

Additional rules cover unique tax IDs per client, serial numbers per client, allowed upload types, and resolution metadata on tickets.

---

## Roadmap / Milestones

### Milestone 1 — Foundation

- Laravel project setup
- Authentication
- Users and roles
- Clients and machines

### Milestone 2 — Tickets

- Ticket CRUD
- Status and priority workflows
- Policies and business rule enforcement

### Milestone 3 — Work Orders

- Ticket → work order conversion
- Transactions and numbering

### Milestone 4 — Files and History

- Uploads and storage
- Observers / events
- Audit trail

### Milestone 5 — Queue and Tests

- Queue workers and notifications
- Feature and unit test coverage

### Milestone 6 — Refinement

- API consistency and documentation
- Refactoring and portfolio readiness

---

## Installation

### Requirements

- PHP 8.3+
- Composer 2.x
- Docker and Docker Compose (for local PostgreSQL — **coming soon**)

### Setup

```bash
git clone <repository-url> helpdesk-saas
cd helpdesk-saas

composer install

cp .env.example .env
php artisan key:generate
```

**Database (planned)** — Local development will use **PostgreSQL** running in a **Docker** container. `docker-compose` and the matching `.env` settings are not in the repository yet; until then, the default Laravel `.env.example` may still point at SQLite for bootstrapping only.

When Docker is added, configuration will look like this:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=helpdesk_saas
DB_USERNAME=helpdesk
DB_PASSWORD=secret
```

```bash
# After docker-compose is available:
docker compose up -d

php artisan migrate
```

### Run locally

```bash
php artisan serve
```

For development with queue and logs together:

```bash
composer dev
```

Or use the one-shot setup script (once the database stack is in place):

```bash
composer setup
```

### Tests

```bash
composer test
# or
php artisan test
```

### Queue worker

When notifications and jobs are enabled:

```bash
php artisan queue:work
```

---

## Documentation

Detailed product scope, roles, business rules, and milestones (Portuguese):

- [`docs/01-visao-geral.md`](docs/01-visao-geral.md)

---

## Future Improvements

Directions aligned with a SaaS product, not committed to the current scope:

- **Docker Compose for PostgreSQL** — Reproducible local database and documented connection defaults.
- **Dashboard endpoints** — KPIs: open tickets, SLA-style metrics, technician workload.
- **Multi-tenancy** — Isolate data per company (tenant_id or dedicated databases).
- **Email notifications** — Ticket updates, assignment, work order completion.
- **API versioning and OpenAPI** — Stable contracts and generated documentation for integrators.
- **OAuth / API clients** — Third-party integrations beyond first-party tokens.
- **Billing and plans** — Subscription tiers per tenant.
- **Reporting** — Export and scheduled reports for operations management.

---

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
