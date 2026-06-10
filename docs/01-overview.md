# HelpDesk SaaS — Project Overview

## 1. Project Overview

### Goal

Build a web system for managing clients, machines, support tickets, and work orders.

The system enables operational control of technical service calls, change history, file attachments, and role-based permissions.

This is a portfolio and learning project, but developed with a real SaaS product mindset.

---

## 2. Technical Goals

The project covers and consolidates:

* Laravel (Pragmatic Modular Architecture)
* API-only REST + JWT
* Explicit business rules in Actions
* Eloquent relationships
* Policies and role-based authorization
* File uploads
* Queue and Jobs
* Events and automatic auditing
* Automated testing (PHPUnit)

---

## 3. Scope

| Module | Status |
|--------|--------|
| Authentication (JWT) | ✅ Implemented |
| Users and permissions | ✅ Implemented (create, update) |
| Clients | ✅ Implemented (full CRUD) |
| Machines | ✅ Implemented (full CRUD) |
| Tickets | ✅ Implemented (CRUD + status transitions) |
| Work Orders | ✅ Implemented (full CRUD + status transitions) |
| File uploads | ✅ Implemented (upload, list, download, delete) |
| History and audit | ✅ Implemented (automatic via Observers) |
| Jobs and notifications | 🔜 Planned |

---

## 4. System Roles

The system has three user types.

### Admin

Responsible for full system management.

Permissions:

* Manage users (create, update)
* Manage clients (CRUD)
* Manage machines (CRUD)
* Manage tickets and work orders
* View history

---

### Technician (`tecnico`)

Responsible for executing technical services.

Permissions:

* View clients and machines
* View tickets
* Change technical ticket statuses
* Execute and update work orders
* Attach files
* Record technical information

---

### Attendant (`atendente`)

Responsible for initial intake.

Permissions:

* Create and edit clients
* Create and edit machines
* Create tickets
* View general information
* **Cannot** change critical technical statuses or finalize work orders

---

## 5. Business Rules

The authoritative business rule catalog is in [`02-business-rules.md`](02-business-rules.md) with stable identifiers (`RN-001` to `RN-033`).

Summary by domain:

| Domain | Rules |
|--------|-------|
| Users and roles | RN-001 – RN-004 |
| Clients | RN-005 – RN-008 |
| Machines | RN-009 – RN-012 |
| Tickets | RN-013 – RN-020 |
| Work Orders | RN-021 – RN-025 |
| Files and attachments | RN-026 – RN-029 |
| History and audit | RN-030 – RN-033 |

---

## 6. Development Guidelines

* Clean code, single responsibility
* Explicit business rules in Actions (never in Controllers)
* Form Requests for HTTP validation and authorization
* Policies for role-based permission control
* Transactions for multi-step operations
* Domain-organized commits
* Documentation kept in sync with development
* Full automated test coverage for all implemented flows

---

## 7. Milestones

### Milestone 1 — Foundation ✅ Completed

* Laravel project with PostgreSQL
* JWT Authentication (`/auth/login`, `/auth/me`)
* `make:domain` scaffolding command
* Shared API response layer (`ApiResponse`, `ApiExceptionRenderer`)
* User module (create, update) with roles and policies
* Client module (full CRUD + pagination)
* Machine module (full CRUD + pagination, linked to Client)

---

### Milestone 2 — Tickets ✅ Completed

* Ticket CRUD ✅
* Status and priority ✅
* Policy-gated status transitions ✅
* Business rules RN-013 – RN-020 ✅

---

### Milestone 3 — Work Orders ✅ Completed

* Ticket → work order conversion (transaction) ✅
* Auto sequential numbering (`OS-00001`) ✅
* Lifecycle control (RN-021 – RN-025) ✅

---

### Milestone 4 — Files and History ✅ Completed

* File uploads (image and PDF) ✅
* Physical storage cleanup on delete ✅
* Automatic audit history via Observers (RN-030 – RN-033) ✅

---

### Milestone 5 — Jobs and Notifications

* Queue workers
* Email/push notifications on key events

---

### Milestone 6 — Refinement

* Expanded test coverage
* OpenAPI / Swagger
* Portfolio preparation
