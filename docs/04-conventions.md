# Conventions

Naming and organization standards for HelpDesk SaaS. This document complements [`03-architecture.md`](03-architecture.md) (structure and request flow) and [`02-business-rules.md`](02-business-rules.md) (domain behavior). It does not redefine business rules; it defines **how code and APIs are named and arranged** so the codebase stays consistent over time.

---

## Convention Philosophy

Shared conventions improve:

- **Maintainability** — Contributors locate classes and endpoints without guessing folder or naming patterns.
- **Predictability** — The same problem maps to the same artifact types (Action, Request, Policy) across domains.
- **Team onboarding** — New developers read one reference instead of inferring style from scattered files.
- **Code readability** — Class names describe intent (`ResolveTicketAction`, not `TicketHelper::doStuff`).
- **Long-term scalability** — Modules grow without renaming wars or parallel patterns (e.g. both `ClientService` and `CreateClientAction`).

Conventions reduce **ambiguity** (what belongs where) and **architectural drift** (fat controllers, mega-services). When a case is genuinely exceptional, document the exception in a pull request and update this file if the pattern should repeat.

---

## Domain Organization

Domains live under `app/Domains/` using **singular** names that match the business concept, not the database table plural.

```
app/
├── Domains/
│   ├── Auth/
│   ├── User/
│   ├── Client/
│   ├── Machine/
│   ├── Ticket/
│   └── WorkOrder/
```

### Why singular?

- Aligns with **model and class names** (`Client`, `Ticket`), reducing mental mapping between folder and type.
- Reads as a **bounded context** (“the Client module”), not a collection endpoint.
- Avoids inconsistency such as `Clients/` containing class `Client`.

Each domain is **self-contained**: its Actions, HTTP layer, models, and policies live inside the module. Shared cross-domain code belongs in `app/Shared/`, not copied into arbitrary domains.

---

## Domain Module Structure

Every domain uses the same internal layout when the concern exists for that module.

```
Client/
├── Actions/
├── Controllers/
├── DTOs/
├── Exceptions/
├── Models/
├── Policies/
├── Requests/
├── Resources/
└── Services/
```

| Folder | Purpose |
|--------|---------|
| **Actions/** | Business use cases (rules, orchestration, transactions). |
| **Controllers/** | Single-action HTTP entry points; delegate to Actions. |
| **DTOs/** | Optional structured input/output objects when arrays are insufficient. |
| **Exceptions/** | Domain-specific exceptions for this module. |
| **Models/** | Eloquent models and relationship definitions. |
| **Policies/** | Authorization rules per resource/operation. |
| **Requests/** | Form Requests: HTTP validation and authorization hooks. |
| **Resources/** | JSON transformation for API responses. |
| **Services/** | Integrations and technical utilities—not entity-wide business facades. |

Create a subfolder only when it has content. Do not add empty placeholders “for later” across every domain at once.

---

## Controller Convention

### Single Action Controllers

Each HTTP endpoint has its own controller class with a single responsibility:

- **One controller = one endpoint**
- Entry point is **`__invoke()`**
- Controllers stay **thin**: authorize, validate via Request, call one Action, return Resource
- **No business rules** in controllers (no status transitions, no “if cancelled then…” logic)

### Naming

`{Verb}{Entity}Controller` or `{Verb}{Entities}Controller` for collections.

| Controller | Responsibility |
|------------|----------------|
| `CreateClientController` | `POST /api/clients` |
| `UpdateClientController` | `PUT/PATCH /api/clients/{id}` |
| `DeleteClientController` | `DELETE /api/clients/{id}` |
| `ListClientsController` | `GET /api/clients` |

Action-style routes use the verb in the controller name, e.g. `ResolveTicketController`, `CancelTicketController`.

### Why single-action controllers?

- **Discoverability** — File name matches route behavior; no scrolling through multi-method classes.
- **Testing** — One class, one behavior under test.
- **Review** — Pull requests touch only the endpoint being changed.
- **Alignment with Actions** — One controller method invokes one Action.

Register routes explicitly to each invokable controller; avoid fat REST controllers with five unrelated methods.

---

## Action Convention

Actions implement **business use cases**. They are the only place for workflow orchestration and domain decisions (see [`02-business-rules.md`](02-business-rules.md)).

### Naming pattern

**Verb + Entity + Action**

| Action | Use case |
|--------|----------|
| `CreateClientAction` | Register a new client |
| `UpdateClientAction` | Update client data with rule checks |
| `ResolveTicketAction` | Resolve ticket per RN-017, RN-019 |
| `CreateWorkOrderAction` | Create work order from ticket per RN-021 |

Use a single public entry method consistently across the project, e.g. `execute()` or `handle()`—pick one and do not mix.

### Responsibilities

Actions own:

- **Business rules** enforcement
- **Workflow orchestration** (multiple steps, related records)
- **Transactions** when atomicity is required
- **Domain decisions** (allowed or denied operation with clear failure)

### Avoid generic names

| Avoid | Prefer |
|-------|--------|
| `ProcessTicketAction` | `ResolveTicketAction`, `CancelTicketAction` |
| `HandleClientAction` | `CreateClientAction`, `UpdateClientAction` |
| `TicketAction` | One class per use case |

Generic names hide behavior, encourage growing “do everything” classes, and complicate testing and traceability to business rules (`RN-###`).

---

## Request Convention

Form Requests validate and normalize **HTTP input** only.

### Naming

Align with the use case and controller:

- `CreateClientRequest`
- `UpdateClientRequest`
- `LoginRequest`

Pattern: **`{Verb}{Entity}Request`** (same verb as the Action/controller pair).

### Responsibilities

- Field validation (types, formats, required fields)
- Authorization hooks where appropriate (`authorize()`)
- **Not** business rule outcomes (e.g. “ticket already resolved”)—those belong in Actions and domain exceptions

One primary Request per write endpoint; reuse only when validation is truly identical.

---

## Resource Convention

API Resources shape outbound JSON inside the standard envelope (`data`).

### Naming

**`{Entity}Resource`**

| Resource | Transforms |
|----------|------------|
| `ClientResource` | Single client |
| `TicketResource` | Single ticket |
| `WorkOrderResource` | Single work order |

### Collections

Optional dedicated collection wrappers when pagination meta or list-specific formatting is needed:

- `ClientCollection`

Otherwise, `Resource::collection()` is acceptable for simple lists.

Resources must not embed business logic; they map attributes and relationships for the API contract.

---

## Policy Convention

Policies answer: **is this user allowed to attempt the operation?**

### Naming

**`{Entity}Policy`**

Examples: `ClientPolicy`, `TicketPolicy`, `WorkOrderPolicy`.

### Methods

Use Laravel policy method names where they fit REST:

| Method | Typical operation |
|--------|-------------------|
| `view` | Read single resource |
| `viewAny` | List resources |
| `create` | Create resource |
| `update` | Update resource |
| `delete` | Delete resource |

Domain-specific abilities use clear names matching business language:

- `resolve` — Resolve a ticket (`tecnico`, `admin`)
- `cancel` — Cancel a ticket
- `finalize` — Finalize a work order

Custom methods must mirror role rules in [`02-business-rules.md`](02-business-rules.md) (e.g. RN-004, RN-018).

Policies handle **permissions only**, not business validity (e.g. cancelled ticket cannot be resolved—that is an Action/rule check, often `BusinessRuleException`).

---

## Exception Convention

Exceptions express **failure reasons** the API can translate to consistent error responses.

### Naming pattern

**Problem + Exception**

| Exception | Meaning |
|-----------|---------|
| `BusinessRuleException` | Generic business rule violation (with code/message) |
| `ResourceConflictException` | State conflict (409-style scenarios) |
| `TicketAlreadyResolvedException` | Specific ticket state conflict |
| `WorkOrderAlreadyExistsException` | RN-021 violation |

Prefer **specific** domain exceptions in `Domains/{Entity}/Exceptions/` when the case is frequent or needs distinct handling. Use shared base types in `app/Shared/Exceptions/` for hierarchy and HTTP mapping.

Explicit names improve **maintainability** (handler maps one type to one status/message) and **API clarity** (clients can branch on stable error codes in `errors`).

---

## Service Convention

**Services** are not a substitute for Actions.

### Use Services for

- **Reusable utilities** (formatting, slug generation)
- **Integrations** (email provider, external APIs)
- **Infrastructure** (storage abstraction, PDF generation)

Examples:

- `EmailNotificationService`
- `PdfGenerationService`
- `StorageService`

### Do not use entity-wide Services

Avoid:

- `ClientService`
- `TicketService`

These tend to accumulate unrelated methods, duplicate Action responsibilities, and obscure where business rules live. If the operation is a use case, it is an **Action** (`CreateClientAction`). If it is sending email, it is a **Service** invoked from an Action or Job.

---

## DTO Convention

DTOs are **optional**. Introduce them when an Action or Request benefits from a typed structure instead of large associative arrays.

### Naming pattern

**Entity + Purpose + Data**

| DTO | Role |
|-----|------|
| `ClientData` | Normalized client payload for create/update |
| `CreateTicketData` | Input bundle for ticket creation |

Keep DTOs immutable where practical (readonly properties). Mapping from Request → DTO may live in the Request or controller; business validation still runs in the Action.

---

## Model Convention

### Class names

**Singular**, PascalCase, matching the domain entity:

- `Client`
- `Machine`
- `Ticket`
- `WorkOrder`

Aligns with Laravel defaults and Eloquent expectations.

### Table names

**Plural**, `snake_case`:

| Model | Table |
|-------|-------|
| `Client` | `clients` |
| `Machine` | `machines` |
| `Ticket` | `tickets` |
| `WorkOrder` | `work_orders` |

### Columns

- **Foreign keys:** `{related_singular}_id` — `client_id`, `ticket_id`, `machine_id`
- **Timestamps for events:** `{verb}_at` — `resolved_at`, `cancelled_at`, `finalized_at`
- **Booleans:** `is_{adjective}` — `is_active`, `is_closed`

Domain status and priority values stored as enums or strings use **lowercase** identifiers consistent with business rules, e.g. `aberto`, `cancelado`, `alta`, `media`, `baixa`.

---

## API Endpoint Convention

Public HTTP API follows **REST** with predictable paths.

### Rules

- **Prefix:** `/api`
- **Resources:** plural nouns
- **Multi-word resources:** **kebab-case**

| Resource | Path |
|----------|------|
| Clients | `/api/clients` |
| Machines | `/api/machines` |
| Tickets | `/api/tickets` |
| Work orders | `/api/work-orders` |

### Standard verbs

| Method | Path | Intent |
|--------|------|--------|
| `GET` | `/api/clients` | List |
| `POST` | `/api/clients` | Create |
| `GET` | `/api/clients/{id}` | Show |
| `PUT`/`PATCH` | `/api/clients/{id}` | Update |
| `DELETE` | `/api/clients/{id}` | Delete |

Use route model binding or explicit ID validation per project standard; IDs in URLs are numeric or UUID as defined per entity.

### Action endpoints

Non-CRUD operations use **verb segments** on the resource:

```
POST   /api/tickets/{id}/resolve
POST   /api/tickets/{id}/cancel
POST   /api/tickets/{id}/work-order
```

This keeps CRUD routes uniform while making exceptional workflows **explicit** in logs, documentation, and client code—no overloaded `PATCH` with opaque body flags for every transition.

Pair each action route with a dedicated invokable controller and Action.

---

## Database Convention

Database naming aligns with Laravel and the business rule document.

### Tables

Plural, `snake_case`: `clients`, `machines`, `tickets`, `work_orders`, `work_order_attachments`.

### Foreign keys

`{singular_table_entity}_id`:

- `client_id`
- `ticket_id`
- `machine_id`
- `user_id`

### Semantic timestamps

Name columns for **when something happened**, not generic flags:

- `resolved_at`
- `cancelled_at`
- `finalized_at`

Use `created_at` / `updated_at` for row audit; use domain-specific `*_at` for lifecycle events required by business rules.

### Booleans

`is_*` prefix: `is_active`, `is_closed`.

### Enumerated values

Store allowed status/priority/role values in **lowercase** (matching API and [`02-business-rules.md`](02-business-rules.md)):

- Ticket status: `aberto`, `em_andamento`, `resolvido`, `cancelado`
- Priority: `baixa`, `media`, `alta`
- Work order status: `aberta`, `em_execucao`, `finalizada`
- Roles: `admin`, `tecnico`, `atendente`

Prefer database enums or check constraints plus application validation—values must stay in sync with business rules.

### Indexes and constraints

Name foreign keys and unique indexes explicitly in migrations when multiple composites exist, e.g. unique `(client_id, serial_number)` for machines (RN-011).

---

## Convention Summary

HelpDesk SaaS conventions prioritize:

- **Consistency** — Same folder layout and naming patterns in every domain
- **Explicit architecture** — Single-action controllers, named Actions, no entity Services
- **Predictable organization** — Singular domains, plural tables, kebab-case API paths
- **Laravel-native development** — Policies, Form Requests, Eloquent, Resources as intended
- **Maintainability** — Thin HTTP layer, business logic in Actions, rules traceable to `RN-###`

When adding a feature: choose the domain → add Action + Request + Policy + Resource + invokable Controller → expose REST or action route → name tables and columns per this document. If a convention should change, update this file before spreading a new pattern across the codebase.
