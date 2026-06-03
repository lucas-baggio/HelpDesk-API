# Business Rules

This document defines the **functional and behavioral rules** of HelpDesk SaaS. It describes what the system must enforce from a domain perspective—who may do what, which states are valid, and which operations are allowed or blocked.

It is **not** an implementation guide: it does not prescribe controllers, models, database design, or application architecture.

Related product context: [`01-visao-geral.md`](01-visao-geral.md).

---

## Business Rules Overview

Business rules are the authoritative contract for system behavior. They underpin:

- **Use-case decisions** — Whether an operation is valid in the current context
- **Input validation** — Which values and combinations are acceptable at the boundary
- **Automated tests** — Expected outcomes for allowed and denied scenarios
- **Product and support clarity** — Shared language between stakeholders and development

Every feature, API behavior, and regression test must **respect** the rules in this document. When behavior is ambiguous, this file takes precedence until an explicit change is agreed and documented.

Rules use stable identifiers (`RN-###`) for traceability in specs, tests, and change requests.

---

## Users and Roles

The system distinguishes three roles. Role assignment determines which operations a user may perform.

| Role | Identifier | Scope |
|------|------------|--------|
| Administrator | `admin` | Full operational and configuration access within the product scope |
| Technician | `tecnico` | Technical execution: statuses, work orders, field service data |
| Attendant | `atendente` | Front-office: clients, ticket intake; limited technical authority |

### Rules

**RN-001** — **Admin has full system access.**

The `admin` role may perform all operations defined for the product, including user management, client and machine maintenance, ticket and work order management, and access to history and attachments, subject only to the business rules in this document (not role restrictions).

---

**RN-002** — **Tecnico can manage technical workflows and update service-related statuses.**

The `tecnico` role may execute and update work orders, change ticket statuses that represent technical progress, attach files to work orders, and record technical descriptions and service values. This role is intended for field and support staff executing service.

---

**RN-003** — **Atendente can manage clients and create support tickets but cannot perform restricted technical operations.**

The `atendente` role may register and edit clients, manage client-linked data allowed for attendants, and create support tickets. This role must **not** perform operations reserved for `tecnico` or `admin`, such as changing critical technical ticket statuses or finalizing work orders, unless explicitly allowed elsewhere.

---

**RN-004** — **Protected operations must respect role permissions.**

Any operation that affects technical status, work order lifecycle, or other restricted capabilities must verify the authenticated user’s role before proceeding. Denied access is a hard rejection: the system must not partially apply the operation.

---

## Clients

Clients represent individuals or organizations receiving support. A client is the anchor for machines and support tickets.

### Rules

**RN-005** — **A client may have multiple machines.**

There is no fixed upper limit on machines per client within product scope. Each machine remains a separate record linked to exactly one client.

---

**RN-006** — **CPF/CNPJ must be unique.**

The tax identifier (CPF for individuals, CNPJ for legal entities) must be unique across all clients. The system must reject creation or update that would duplicate an existing identifier.

---

**RN-007** — **Client deletion must be blocked if related machines or tickets exist.**

A client cannot be removed while any machine or support ticket still references that client. Removal is only permitted when no such dependencies remain (or after a defined archival process, if introduced later).

---

**RN-008** — **Client information must remain editable.**

Core client data (identity, contact, tax identifier where applicable) may be updated while the client exists, subject to uniqueness and validation rules. Edits must not silently break referential integrity with machines or tickets.

---

## Machines

Machines (equipment) are assets owned by a client and may be referenced by support tickets.

### Rules

**RN-009** — **Every machine must belong to a client.**

A machine record cannot exist without a parent client. Orphan machines are not allowed.

---

**RN-010** — **Machine association with client is mandatory.**

On create and update, the client association is required and must reference a valid, existing client. Changing ownership between clients, if supported, must preserve consistency with tickets and history.

---

**RN-011** — **Serial number must be unique per client.**

Within the same client, serial numbers must not duplicate. The same serial may exist for **different** clients. Uniqueness is evaluated in the scope of `(client, serial_number)`.

---

**RN-012** — **Machines may exist without active tickets.**

A machine does not require an open or historical ticket to remain in the catalog. Equipment can be registered before any incident is reported.

---

## Support Tickets

Support tickets represent service requests. They track priority, status, and optional linkage to equipment.

### Allowed priorities

| Value | Meaning |
|-------|---------|
| `baixa` | Low urgency |
| `media` | Medium urgency |
| `alta` | High urgency |

### Allowed statuses

| Value | Meaning |
|-------|---------|
| `aberto` | Open; not yet in active technical handling |
| `em_andamento` | In progress |
| `resolvido` | Resolved; work completed from a ticket perspective |
| `cancelado` | Cancelled; no further resolution path |

### Rules

**RN-013** — **Every ticket must belong to a client.**

A ticket cannot be created without a valid client. The client context is mandatory for reporting and SLA-style operations.

---

**RN-014** — **Ticket may optionally be linked to a machine.**

When equipment is relevant, the ticket may reference one of the client’s machines. When not relevant, the ticket may exist with client only and no machine.

---

**RN-015** — **Priority must respect allowed values.**

Priority must be exactly one of: `baixa`, `media`, `alta`. Any other value is invalid.

---

**RN-016** — **Status must respect allowed values.**

Status must be exactly one of: `aberto`, `em_andamento`, `resolvido`, `cancelado`. Transitions must follow the lifecycle rules below; arbitrary status values are not permitted.

---

**RN-017** — **Cancelled ticket cannot be resolved.**

A ticket in `cancelado` cannot be moved to `resolvido` or treated as successfully closed for service completion. Reopening or correcting a cancelled ticket, if allowed in the future, must be a distinct, documented rule change.

---

**RN-018** — **Only authorized roles may change technical statuses.**

Changes to ticket status—especially transitions into or within `em_andamento` and `resolvido`—are restricted to `tecnico` and `admin` as defined in RN-002 and RN-004. `atendente` may create tickets and perform allowed non-technical updates but must not bypass this restriction.

---

**RN-019** — **Resolving a ticket must register resolution timestamp and responsible user.**

When status becomes `resolvido`, the system must record:

- **Resolution timestamp** — When the ticket was marked resolved
- **Responsible user** — Which authenticated user performed the resolution

These fields are mandatory for a valid resolution; they support accountability and reporting.

---

**RN-020** — **Tickets must preserve operational history.**

Lifecycle changes (status, priority, assignment, and other defined critical fields) must remain auditable through the history mechanism described in [History and Audit](#history-and-audit). Ticket data alone is insufficient without a trace of how it changed over time.

---

## Work Orders

Work orders formalize execution of service linked to a support ticket. They have their own status lifecycle distinct from the ticket, though the two remain related.

### Allowed statuses

| Value | Meaning |
|-------|---------|
| `aberta` | Open; not yet in execution |
| `em_execucao` | Work in progress |
| `finalizada` | Completed |

### Rules

**RN-021** — **A ticket may generate only one work order.**

At most one work order may exist per support ticket. Attempting to create a second work order for the same ticket must fail. This prevents duplicate billing, execution, and conflicting service records.

---

**RN-022** — **Work order creation depends on an existing ticket.**

A work order cannot be created in isolation; it must reference a valid ticket that satisfies any additional product rules for conversion (e.g. ticket not cancelled, if such a rule applies at creation time).

---

**RN-023** — **Work order must have lifecycle control.**

Status changes must follow allowed values and valid transitions for `aberta`, `em_execucao`, and `finalizada`. The system must reject transitions that skip required states or violate RN-024.

---

**RN-024** — **Finalized work order cannot return to open state.**

Once `finalizada`, a work order cannot be set back to `aberta`. Corrections after completion, if ever supported, require a separate business process and must not reuse this rule silently.

---

**RN-025** — **Work orders may contain technical descriptions and service value.**

A work order may store narrative technical detail and a monetary or numeric service value where the product defines them. These fields support execution records, billing context, and audit; changes to value or critical descriptions should be traceable per history rules.

---

## Files and Attachments

Files provide evidence and documentation for service execution, primarily tied to work orders.

### Rules

**RN-026** — **Only supported file types may be uploaded.**

Permitted types:

- **Images** (e.g. JPEG, PNG—exact MIME/extension list defined at product configuration time)
- **PDF**

All other types must be rejected at upload time.

---

**RN-027** — **File removal must delete physical storage.**

When an attachment is removed from the system, the stored file must be deleted from storage as well. Logical deletion without removing the binary is not sufficient.

---

**RN-028** — **Attachments belong to work orders.**

Uploads are associated with work orders, not floating globally. Linking ensures context for technicians and audit (which service execution the file supports).

---

**RN-029** — **Upload limits and validation must be enforced.**

Maximum file size, count per work order (if capped), and type validation must be enforced before the attachment is accepted. Invalid uploads must not persist metadata or storage.

---

## History and Audit

History provides a chronological record of meaningful changes for accountability and troubleshooting.

### Rules

**RN-030** — **Relevant system changes must generate history automatically.**

The system must record history without relying on manual logging by users. Examples include status changes, service value updates, responsibility changes, and other fields designated as auditable.

---

**RN-031** — **History must record user, timestamp, and performed action.**

Each history entry must include:

- **User** — Who triggered the change (authenticated identity)
- **Timestamp** — When the change occurred
- **Performed action** — What changed (e.g. field, old/new values, or a structured action code)

Incomplete records do not satisfy the audit requirement.

---

**RN-032** — **Status changes must be traceable.**

Transitions for support tickets and work orders must appear in history so operators can answer when and by whom a status changed.

---

**RN-033** — **Critical operational changes must preserve auditability.**

Changes that affect compliance, billing, or dispute resolution—including resolution of tickets, finalization of work orders, and material edits to service value—must be recoverable from history. Traceability supports internal quality, customer disputes, and portfolio-grade operational transparency.

---

## Business Rule Summary

This document defines the **functional contract** of HelpDesk SaaS across users, clients, machines, tickets, work orders, files, and audit history.

| Domain | Rule IDs |
|--------|----------|
| Users and roles | RN-001 – RN-004 |
| Clients | RN-005 – RN-008 |
| Machines | RN-009 – RN-012 |
| Support tickets | RN-013 – RN-020 |
| Work orders | RN-021 – RN-025 |
| Files and attachments | RN-026 – RN-029 |
| History and audit | RN-030 – RN-033 |

Implementations, API contracts, and test suites should reference these identifiers. Any change to behavior must update this document first, then align delivery and tests accordingly.
