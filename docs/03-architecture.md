# Architecture

HelpDesk SaaS is an **API-only** Laravel application. This document describes how the codebase is organized, how requests flow through the system, and why **Pragmatic Modular Laravel Architecture** was adopted as the structural baseline.

Related reading: product scope and business rules in [`01-visao-geral.md`](01-visao-geral.md).

---

## Architecture Overview

The project uses **Pragmatic Modular Laravel Architecture**: domain-oriented modules inside `app/Domains/`, shared cross-cutting code in `app/Shared/`, and Laravel’s native building blocks (routing, Eloquent, Form Requests, Policies, queues) instead of a custom framework layer.

### Why not “Laravel only” (flat MVC)?

A single `app/Http/Controllers` tree with models and logic spread across controllers, helpers, and ad hoc service classes works for small apps. As domains grow (tickets, work orders, audit, files), responsibilities blur: controllers accumulate rules, duplication appears, and authorization becomes inconsistent. The modular layout keeps each bounded context in one place without abandoning Laravel conventions.

### Why not full Clean Architecture?

Full Clean Architecture (entities, use cases, interface adapters, frameworks as outer rings) enforces strict dependency inversion and many layers. That pays off in large teams and long-lived products with heavy domain complexity, but it also adds:

- More classes and indirection for every operation
- Mapping overhead between layers (DTOs, repositories, presenters)
- Slower iteration for a portfolio-sized API with well-understood CRUD-plus-workflow domains

This project deliberately **does not** adopt that full model. Business rules still live outside controllers—in **Actions**—but persistence stays Eloquent-first, and HTTP stays Laravel-first.

### Tradeoffs

| Choice | Benefit | Cost |
|--------|---------|------|
| Domain folders under `app/Domains/` | Clear ownership per module | Requires discipline when code is shared across domains |
| Actions as use cases | Testable workflows, explicit rules | More classes than “everything in the controller” |
| Eloquent in Actions | Productivity, familiar Laravel DX | Domain logic can leak into models if not guarded |
| Shared layer for HTTP/errors | Consistent API surface | Must avoid turning `Shared/` into a junk drawer |

The architecture favors **pragmatic separation of responsibilities**: enough structure to scale and review, not enough ceremony to fight the framework.

---

## Core Principles

**Domain-oriented organization** — Code is grouped by business area (Client, Ticket, WorkOrder), not only by technical type (all controllers in one folder).

**Thin controllers** — Controllers parse HTTP, authorize, delegate to Actions, and return Resources. They do not own business workflows.

**Explicit business rules** — Rules documented in product specs are implemented in Actions (and validated at the edge with Requests/Policies), not implied in Blade or scattered helpers.

**Laravel-native development** — Form Requests, Policies, Eloquent, Jobs, Events, and Sanctum (or equivalent) are first-class; no parallel container or custom ORM.

**Consistent API responses** — Success and error payloads follow a single envelope so clients and tests can rely on shape.

**Centralized exception handling** — Domain and application exceptions map to HTTP status codes and messages in one place (`Shared/Exceptions` + handler).

**Separation of concerns** — Validation (Request), permission (Policy), business decision (Action), persistence (Model), serialization (Resource) each have a single primary role.

---

## Project Structure

The application root under `app/` is split into **Domains** (feature modules) and **Shared** (cross-domain infrastructure).

```
app/
├── Domains/
│   ├── Auth/
│   ├── User/
│   ├── Client/
│   ├── Machine/
│   ├── Ticket/
│   └── WorkOrder/
│
├── Shared/
│   ├── Exceptions/
│   ├── Http/
│   ├── Traits/
│   ├── Helpers/
│   └── Support/
```

**Domains** — Each directory is a self-contained module for one bounded context: its own models, actions, HTTP layer, and policies. Cross-domain calls should go through explicit Actions or small application services, not through foreign controllers.

**Shared** — Code used by multiple domains: base API responses, exception hierarchy, generic HTTP middleware helpers, and utilities that are not business use cases (e.g. formatting, shared enums).

Domains may depend on `Shared`. Domains should **avoid** depending on each other’s internal folders; prefer orchestration at the Action level or documented integration points.

---

## Domain Module Structure

Each domain follows the same internal layout so navigation and code review stay predictable.

```
app/Domains/Client/
├── Models/
├── Actions/
├── Requests/
├── Resources/
├── Controllers/
├── Policies/
├── DTOs/
└── Exceptions/
```

| Folder | Responsibility |
|--------|----------------|
| **Models/** | Eloquent models, relationships, scopes, and persistence-oriented attributes. No HTTP knowledge. |
| **Actions/** | Use cases: business rules, orchestration, transactions, and calls to models or external services. |
| **Requests/** | Form Requests: input validation, authorization hooks, and normalized input for Actions. |
| **Resources/** | API Resources: transform models/collections into the standard JSON envelope (`data`). |
| **Controllers/** | HTTP entry points: route parameters, call Request → Policy → Action → Resource, return response. |
| **Policies/** | Authorization: whether the authenticated user may perform the operation on a given resource. |
| **DTOs/** | Optional immutable structures for passing data between layers when arrays are insufficient. |
| **Exceptions/** | Domain-specific exceptions (e.g. client cannot be deleted while tickets exist). |

Not every domain needs every folder on day one; empty folders are not created until needed. **DTOs** and **Exceptions** are added when complexity justifies them.

---

## Functional Request Flow

A typical mutating API request follows this path:

```
Route
  → Controller
  → Request
  → Policy
  → Action
  → Model
  → Resource
  → Response
```

| Step | Purpose |
|------|---------|
| **Route** | Maps verb and URI to a controller method; applies middleware (auth, throttle). |
| **Controller** | Resolves route models, type-hints the Form Request, invokes policy check, runs one Action, wraps result in a Resource. |
| **Request** | Validates and sanitizes input; fails fast with 422 and field errors if invalid. |
| **Policy** | Answers authorization before side effects; fails with 403 when the user lacks permission. |
| **Action** | Executes the use case: business rules, state transitions, `DB::transaction` when needed. |
| **Model** | Loads and persists data via Eloquent; relationships stay on the model layer. |
| **Resource** | Shapes the outbound JSON (`data` node inside the global envelope). |
| **Response** | Returns consistent success payload (status code + `success`, `message`, `data`). |

Controllers remain **thin**: no multi-step workflows, no embedded “if cancelled then cannot resolve” logic. That belongs in **Actions** so the same rule can be reused from jobs or future entry points without duplicating HTTP code.

---

## Actions and Business Rules

**Actions** are the primary home for business logic in this architecture. They correspond to **use cases** in a pragmatic Laravel style: one class per meaningful operation, invokable via a single public method (e.g. `execute()` or `handle()`).

### Actions are responsible for

- Enforcing **business rules** (e.g. a cancelled ticket cannot be resolved)
- **Workflow orchestration** (e.g. resolve ticket → record resolver and timestamp)
- **Database transactions** when multiple writes must succeed or fail together
- **Domain decisions** (e.g. only one work order per ticket)
- **Coordinating persistence** (create/update related models, dispatch events/jobs)

### Examples

| Action | Typical responsibility |
|--------|------------------------|
| `CreateTicketAction` | Validate domain preconditions, create ticket, optional machine link, initial status |
| `ResolveTicketAction` | Ensure status allows resolution, set `resolved_at` and resolver, trigger audit side effects |
| `CreateWorkOrderAction` | Ensure ticket has no existing work order, create OS inside a transaction, assign number |

### Separation of questions

| Layer | Question |
|-------|----------|
| **Request** | Are the incoming data valid and well-formed? |
| **Policy** | Is the user allowed to perform this operation? |
| **Action** | Does this operation make sense according to business rules, and what state changes apply? |

Policies gate **permission**. Actions gate **business validity**. Both can reject an operation; they must not duplicate each other’s concerns.

Actions may call **Services** for integrations (mail, PDF) but should not delegate core domain decisions to generic “God services.”

---

## Services vs Actions

| | **Actions** | **Services** |
|---|-------------|--------------|
| **Purpose** | Application use cases and business operations | Reusable utilities and external integrations |
| **Scope** | One clear operation per class | Shared technical capability |
| **Examples** | `CreateClientAction`, `ResolveTicketAction` | `EmailNotificationService`, `PdfGenerationService` |
| **Business rules** | Yes | No (or minimal); callers pass already-validated intent |

**Avoid** large `SomethingService` classes with dozens of unrelated methods. If a method represents a business story (“create work order from ticket”), it belongs in an **Action**. If it sends an email template or talks to a third-party API, it belongs in a **Service**.

Jobs and listeners can invoke Actions to keep background behavior aligned with HTTP behavior.

---

## API Response Standard

All JSON API responses use a **single envelope** so clients, mobile apps, and tests handle outcomes uniformly.

### Success

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

- `success` — Always `true` for successful operations.
- `message` — Human-readable summary; suitable for UI toasts or logs.
- `data` — Resource output (object, array, or paginated wrapper). Empty object when no body is needed.

Use appropriate HTTP status codes (`200`, `201`, `204` with a minimal body if required by convention).

### Error

```json
{
  "success": false,
  "message": "Operation failed",
  "errors": {}
}
```

- `success` — Always `false`.
- `message` — Primary error description (not a stack trace in production).
- `errors` — Optional detail: validation field map (`422`), error codes, or nested context.

Validation failures typically populate `errors` with field keys; business rule failures may use a stable `code` inside `errors` for client branching.

### Why consistency matters

- One client adapter can parse all endpoints.
- Feature tests assert on `success` and structure, not ad hoc keys per controller.
- Exception handler maps domain exceptions to this shape automatically.

Implementation lives in `Shared/Http` (traits, base controller helpers, or dedicated response builder).

---

## Exception Strategy

Business and authorization failures are expressed as **exceptions**, not as mixed return arrays inside Actions. A centralized handler (Laravel’s `bootstrap/app.php` exception configuration or `App\Exceptions\Handler`) translates them to the error envelope and HTTP status.

### Shared exception layer

`app/Shared/Exceptions/` defines the base hierarchy; domains may extend or define specific types under `Domains/*/Exceptions/`.

### Examples

| Exception | Typical HTTP | When |
|-----------|--------------|------|
| `BusinessRuleException` | `422` or `409` | Rule violated (e.g. cancelled ticket cannot be resolved) |
| `UnauthorizedActionException` | `403` | Authenticated but not permitted for this action (if not using Policy abort only) |
| `ResourceConflictException` | `409` | Conflict with current state (e.g. work order already exists for ticket) |

Domain exceptions carry a **safe message** for `message` and optional **machine-readable codes** in `errors`.

### Benefits

- Actions stay readable (`throw` instead of nested conditionals returning arrays).
- HTTP mapping is defined once; controllers do not switch on internal codes.
- Logs and API responses stay aligned; debugging does not depend on remembering per-controller error formats.

---

## Architectural Decision Summary

HelpDesk SaaS prioritizes:

- **Maintainability** — Predictable module layout and thin HTTP layer.
- **Clear business logic** — Actions own use cases; Requests and Policies own edges.
- **Developer experience** — Standard Laravel patterns, minimal custom infrastructure.
- **Real-world Laravel architecture** — Suitable for portfolio review and production-style APIs without Clean Architecture overhead.
- **Long-term scalability** — New domains add a folder and repeat the same structure; Shared cross-cutting concerns do not sprawl into domains.

This document is the reference for implementing new endpoints: add or extend a domain module, wire the request flow, enforce rules in Actions, and return responses through the shared API contract.
