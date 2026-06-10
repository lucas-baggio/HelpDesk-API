# API Reference

Complete reference for all implemented HTTP endpoints in HelpDesk SaaS.

**Base URL:** `http://localhost:8000/api` (local)

**Authentication:** All protected endpoints require a `Bearer` token in the `Authorization` header, obtained via `POST /auth/login`.

**Response envelope:** Every response follows the standard format described in [`03-architecture.md`](03-architecture.md).

---

## Authentication

### `POST /api/auth/login`

Authenticate and receive a JWT token.

**Auth required:** No

**Request body**

```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Success `200`**

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1...",
    "token_type": "bearer",
    "expires_in": 10080
  }
}
```

**Errors**

| Status | Code | Reason |
|--------|------|--------|
| `401` | — | Invalid credentials |
| `403` | — | Account is inactive |
| `422` | — | Validation failed (missing fields) |

---

### `GET /api/auth/me`

Return the authenticated user's profile.

**Auth required:** Bearer token

**Success `200`**

```json
{
  "success": true,
  "message": "Authenticated user retrieved.",
  "data": {
    "id": "019ead...",
    "name": "Admin Test",
    "email": "admin@example.com",
    "role": "admin",
    "is_active": true,
    "created_at": "2026-06-09T18:00:00.000000Z",
    "updated_at": "2026-06-09T18:00:00.000000Z"
  }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `401` | Token missing, expired, or invalid |

---

## Users

### `POST /api/users`

Create a new user. Restricted to `admin`.

**Auth required:** Bearer — `admin` only

**Request body**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | string | ✅ | Max 255 |
| `email` | string | ✅ | Unique, valid email |
| `password` | string | ✅ | Min 8, confirmed |
| `password_confirmation` | string | ✅ | Must match `password` |
| `role` | string | — | `admin`, `tecnico`, `atendente` (default: `atendente`) |

**Success `201`**

```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": "019ead...",
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "atendente",
    "is_active": true,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `401` | Unauthenticated |
| `403` | Authenticated but not `admin` |
| `422` | Validation failed |

---

### `PUT /api/users/{id}`

Update an existing user. Restricted to `admin`.

**Auth required:** Bearer — `admin` only

**URL parameter:** `id` — UUID of the user

**Request body** (all fields optional)

| Field | Type | Notes |
|-------|------|-------|
| `name` | string | Max 255 |
| `email` | string | Unique (excluding current user) |
| `password` | string | Min 8, confirmed |
| `password_confirmation` | string | Required when `password` is sent |
| `role` | string | `admin`, `tecnico`, `atendente` |
| `is_active` | boolean | Enable / disable account |

**Success `200`**

```json
{
  "success": true,
  "message": "User updated successfully",
  "data": { ... }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `401` | Unauthenticated |
| `403` | Not `admin` |
| `404` | User not found |
| `422` | Validation failed |

---

## Clients

### `GET /api/clients`

List clients (paginated). All authenticated roles.

**Auth required:** Bearer (any role)

**Query parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `is_active` | `0` or `1` | Filter by active status |

**Success `200`**

```json
{
  "success": true,
  "message": "Clients retrieved successfully.",
  "data": [
    {
      "id": "019ead...",
      "name": "Acme Corp",
      "email": "contato@acme.com",
      "cpf_cnpj": "12.345.678/0001-99",
      "phone": "(11) 99999-9999",
      "address": {
        "street": "Main Street",
        "number": "123",
        "complement": null,
        "district": "Downtown",
        "city": "New York",
        "state": "NY",
        "zip_code": "10001"
      },
      "is_active": true,
      "created_at": "...",
      "updated_at": "..."
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

### `POST /api/clients`

Create a new client. Restricted to `admin` and `atendente`.

**Auth required:** Bearer — `admin`, `atendente`

**Request body**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | string | ✅ | Max 255 |
| `email` | string | ✅ | Unique, valid email |
| `cpf_cnpj` | string | ✅ | Unique, max 18 chars |
| `phone` | string | ✅ | Max 20 |
| `street` | string | ✅ | Max 255 |
| `number` | string | ✅ | Max 20 |
| `state` | string | ✅ | 2-char UF code |
| `district` | string | ✅ | Max 255 |
| `city` | string | ✅ | Max 255 |
| `zip_code` | string | ✅ | Max 10 |
| `complement` | string | — | Nullable, max 255 |

**Success `201`**

```json
{
  "success": true,
  "message": "Client created successfully.",
  "data": { ... }
}
```

**Business rule errors**

| Code | Message |
|------|---------|
| `CLIENT_CPF_CNPJ_ALREADY_EXISTS` | A client with this CPF/CNPJ already exists. |

---

### `GET /api/clients/{id}`

Show a single client.

**Auth required:** Bearer (any role)

**Success `200`** — Returns full client object.

**Errors**

| Status | Reason |
|--------|--------|
| `404` | Client not found |

---

### `PUT /api/clients/{id}`

Update client data. Restricted to `admin` and `atendente`.

**Auth required:** Bearer — `admin`, `atendente`

All fields are optional. Only sent fields are updated. `email` and `cpf_cnpj` uniqueness validation ignores the current record.

**Success `200`**

```json
{
  "success": true,
  "message": "Client updated successfully.",
  "data": { ... }
}
```

---

### `DELETE /api/clients/{id}`

Deactivate a client (`is_active → false`). Restricted to `admin`.

Per **RN-007**, deactivation will be blocked when machines or tickets are linked (enforced once those modules are implemented).

**Auth required:** Bearer — `admin` only

**Success `200`**

```json
{
  "success": true,
  "message": "Client deactivated successfully.",
  "data": {
    "id": "019ead...",
    "is_active": false,
    ...
  }
}
```

---

## Machines

### `GET /api/machines`

List machines (paginated). All authenticated roles.

**Auth required:** Bearer (any role)

**Query parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `client_id` | UUID | Filter by client |
| `is_active` | `0` or `1` | Filter by active status |

**Success `200`**

```json
{
  "success": true,
  "message": "Machines retrieved successfully.",
  "data": [
    {
      "id": "019ead...",
      "client_id": "019ead...",
      "name": "Notebook Dell",
      "model": "Latitude 5420",
      "serial_number": "SN-00001",
      "is_active": true,
      "created_at": "...",
      "updated_at": "..."
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 1, "last_page": 1 }
}
```

---

### `POST /api/machines`

Create a new machine linked to a client. Restricted to `admin` and `atendente`.

**Auth required:** Bearer — `admin`, `atendente`

**Request body**

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `client_id` | UUID | ✅ | Must reference an existing client |
| `name` | string | ✅ | Max 255 |
| `model` | string | — | Nullable, max 255 |
| `serial_number` | string | — | Nullable, unique per client (RN-011) |

**Success `201`**

```json
{
  "success": true,
  "message": "Machine created successfully.",
  "data": { ... }
}
```

**Business rule errors**

| Code | Message |
|------|---------|
| `MACHINE_SERIAL_NUMBER_ALREADY_EXISTS` | A machine with this serial number already exists for this client. |

---

### `GET /api/machines/{id}`

Show a single machine.

**Auth required:** Bearer (any role)

**Success `200`** — Returns full machine object.

**Errors**

| Status | Reason |
|--------|--------|
| `404` | Machine not found |

---

### `PUT /api/machines/{id}`

Update machine data. Restricted to `admin` and `atendente`.

**Auth required:** Bearer — `admin`, `atendente`

All fields optional. `serial_number` uniqueness validation scoped to the same client, ignoring the current record.

| Field | Type | Notes |
|-------|------|-------|
| `name` | string | Max 255 |
| `model` | string | Nullable |
| `serial_number` | string | Nullable, unique per client |
| `is_active` | boolean | Enable / disable machine |

**Success `200`**

```json
{
  "success": true,
  "message": "Machine updated successfully.",
  "data": { ... }
}
```

---

### `DELETE /api/machines/{id}`

Deactivate a machine (`is_active → false`). Restricted to `admin`.

**Auth required:** Bearer — `admin` only

**Success `200`**

```json
{
  "success": true,
  "message": "Machine deactivated successfully.",
  "data": {
    "id": "019ead...",
    "is_active": false,
    ...
  }
}
```

---

---

## Tickets

### `GET /api/tickets`

List tickets with optional filters. Returns paginated results.

**Auth required:** Bearer (any role)

**Query parameters**

| Param | Type | Description |
|-------|------|-------------|
| `client_id` | uuid | Filter by client |
| `status` | string | Filter by status: `aberto`, `em_andamento`, `resolvido`, `cancelado` |
| `priority` | string | Filter by priority: `baixa`, `media`, `alta` |

**Success `200`**

```json
{
  "success": true,
  "message": "Tickets retrieved successfully.",
  "data": [
    {
      "id": "019ead...",
      "client_id": "019ead...",
      "machine_id": null,
      "created_by": "019ead...",
      "resolved_by": null,
      "title": "HP P1102 printer not responding",
      "description": "Stopped working after driver update.",
      "priority": "alta",
      "status": "aberto",
      "resolved_at": null,
      "created_at": "2026-06-09T19:00:50.000000Z",
      "updated_at": "2026-06-09T19:00:50.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

### `POST /api/tickets`

Create a new ticket. Restricted to `admin` and `atendente`.

**Auth required:** Bearer — `admin`, `atendente`

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `client_id` | uuid | Yes | Must exist in `clients` |
| `machine_id` | uuid | No | When provided, must belong to `client_id` |
| `title` | string | Yes | Max 255 |
| `description` | string | Yes | |
| `priority` | string | Yes | `baixa`, `media`, `alta` |

**Success `201`**

```json
{
  "success": true,
  "message": "Ticket created successfully.",
  "data": {
    "id": "019ead...",
    "status": "aberto",
    "priority": "alta",
    ...
  }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `403` | Insufficient role (`tecnico` cannot create) |
| `422` | Validation failed or machine belongs to a different client |

---

### `GET /api/tickets/{id}`

Retrieve a single ticket.

**Auth required:** Bearer (any role)

**Success `200`** — Returns full ticket object.

**Errors**

| Status | Reason |
|--------|--------|
| `404` | Ticket not found |

---

### `PUT /api/tickets/{id}`

Update ticket fields (title, description, priority, machine_id). Closed tickets (`resolvido` or `cancelado`) cannot be updated.

**Auth required:** Bearer — `admin`, `atendente`

All fields optional.

| Field | Type | Notes |
|-------|------|-------|
| `title` | string | Max 255 |
| `description` | string | |
| `priority` | string | `baixa`, `media`, `alta` |
| `machine_id` | uuid | Nullable; must belong to same client |

**Success `200`**

```json
{
  "success": true,
  "message": "Ticket updated successfully.",
  "data": { ... }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `422` | `TICKET_ALREADY_CLOSED` — ticket is resolved or cancelled |
| `422` | `TICKET_MACHINE_CLIENT_MISMATCH` — machine from different client |

---

### `POST /api/tickets/{id}/start`

Move ticket from `aberto` to `em_andamento`. Restricted to `admin` and `tecnico` (RN-018).

**Auth required:** Bearer — `admin`, `tecnico`

**Success `200`**

```json
{
  "success": true,
  "message": "Ticket moved to in progress.",
  "data": { "status": "em_andamento", ... }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `403` | Insufficient role |
| `422` | `TICKET_CANNOT_START` — ticket is not in `aberto` status |

---

### `POST /api/tickets/{id}/resolve`

Resolve the ticket. Records `resolved_by` and `resolved_at` (RN-019). Cannot resolve a cancelled ticket (RN-017). Restricted to `admin` and `tecnico`.

**Auth required:** Bearer — `admin`, `tecnico`

**Success `200`**

```json
{
  "success": true,
  "message": "Ticket resolved successfully.",
  "data": {
    "status": "resolvido",
    "resolved_by": "019ead...",
    "resolved_at": "2026-06-09T19:01:04.000000Z",
    ...
  }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `403` | Insufficient role |
| `422` | `TICKET_CANCELLED_CANNOT_RESOLVE` — RN-017 |
| `422` | `TICKET_ALREADY_RESOLVED` — ticket is already resolved |

---

### `POST /api/tickets/{id}/cancel`

Cancel the ticket. Already-closed tickets cannot be cancelled. Restricted to `admin` and `tecnico`.

**Auth required:** Bearer — `admin`, `tecnico`

**Success `200`**

```json
{
  "success": true,
  "message": "Ticket cancelled successfully.",
  "data": { "status": "cancelado", ... }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `403` | Insufficient role |
| `422` | `TICKET_ALREADY_CLOSED` — ticket is already resolved or cancelled |

---

## Work Orders

### `GET /api/work-orders`

List work orders with optional filters. Returns paginated results.

**Auth required:** Bearer (any role)

**Query parameters**

| Param | Type | Description |
|-------|------|-------------|
| `ticket_id` | uuid | Filter by ticket |
| `status` | string | Filter by status: `aberta`, `em_execucao`, `finalizada` |

**Success `200`**

```json
{
  "success": true,
  "message": "Work orders retrieved successfully.",
  "data": [
    {
      "id": "019ead...",
      "ticket_id": "019ead...",
      "number": "OS-00001",
      "description": "Hard drive replaced and RAID reconfigured.",
      "service_value": "1200.00",
      "status": "aberta",
      "created_at": "2026-06-10T16:36:24.000000Z",
      "updated_at": "2026-06-10T16:36:24.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

### `POST /api/work-orders`

Create a work order linked to a ticket. Restricted to `admin` and `tecnico`. Only one work order per ticket is allowed (RN-021). Cannot be created for a cancelled ticket (RN-022).

**Auth required:** Bearer — `admin`, `tecnico`

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `ticket_id` | uuid | Yes | Must exist and not be cancelled |
| `description` | string | Yes | |
| `service_value` | decimal | No | Monetary value |

**Success `201`**

```json
{
  "success": true,
  "message": "Work order created successfully.",
  "data": {
    "id": "019ead...",
    "number": "OS-00001",
    "status": "aberta",
    ...
  }
}
```

**Errors**

| Status | Code | Reason |
|--------|------|--------|
| `403` | — | Insufficient role |
| `409` | `WORK_ORDER_ALREADY_EXISTS` | Ticket already has a work order (RN-021) |
| `422` | `WORK_ORDER_TICKET_CANCELLED` | Ticket is cancelled (RN-022) |

---

### `GET /api/work-orders/{id}`

Retrieve a single work order.

**Auth required:** Bearer (any role)

**Success `200`** — Returns full work order object.

---

### `PUT /api/work-orders/{id}`

Update work order fields. Finalized work orders cannot be edited (RN-024).

**Auth required:** Bearer — `admin`, `tecnico`

| Field | Type | Notes |
|-------|------|-------|
| `description` | string | |
| `service_value` | decimal | Nullable |

**Errors**

| Status | Code | Reason |
|--------|------|--------|
| `422` | `WORK_ORDER_ALREADY_FINALIZED` | Cannot edit a finalized work order |

---

### `POST /api/work-orders/{id}/start`

Move work order from `aberta` to `em_execucao` (RN-023).

**Auth required:** Bearer — `admin`, `tecnico`

**Success `200`**

```json
{
  "success": true,
  "message": "Work order moved to in execution.",
  "data": { "status": "em_execucao", ... }
}
```

**Errors**

| Status | Code | Reason |
|--------|------|--------|
| `403` | — | Insufficient role |
| `422` | `WORK_ORDER_CANNOT_START` | Work order is not in `aberta` status |

---

### `POST /api/work-orders/{id}/finalize`

Finalize the work order. Must be in `em_execucao` first. Once finalized, the status cannot be reversed (RN-024).

**Auth required:** Bearer — `admin`, `tecnico`

**Success `200`**

```json
{
  "success": true,
  "message": "Work order finalized successfully.",
  "data": { "status": "finalizada", ... }
}
```

**Errors**

| Status | Code | Reason |
|--------|------|--------|
| `403` | — | Insufficient role |
| `422` | `WORK_ORDER_NOT_IN_EXECUTION` | Must be `em_execucao` first |
| `422` | `WORK_ORDER_ALREADY_FINALIZED` | Already finalized (RN-024) |

---

## Work Order Files

Files are nested under work orders. All endpoints require authentication.

### `GET /api/work-orders/{id}/files`

List all files attached to a work order.

**Auth required:** Bearer (any role)

**Success `200`**

```json
{
  "success": true,
  "message": "Files retrieved successfully.",
  "data": [
    {
      "id": "019ead...",
      "work_order_id": "019ead...",
      "uploaded_by": "019ead...",
      "file_name": "relatorio.pdf",
      "mime_type": "application/pdf",
      "file_size": 512000,
      "url": "/api/work-orders/{id}/files/{file}/download",
      "created_at": "2026-06-10T17:00:00.000000Z"
    }
  ]
}
```

---

### `POST /api/work-orders/{id}/files`

Upload a file to the work order. Restricted to `admin` and `tecnico` (RN-028).

**Auth required:** Bearer — `admin`, `tecnico`

**Content-Type:** `multipart/form-data`

| Field | Type | Notes |
|-------|------|-------|
| `file` | file | Required. Allowed: JPEG, PNG, GIF, WebP, PDF. Max 10 MB (RN-026, RN-029). |

**Success `201`**

```json
{
  "success": true,
  "message": "File uploaded successfully.",
  "data": {
    "id": "019ead...",
    "file_name": "relatorio.pdf",
    "mime_type": "application/pdf",
    "file_size": 512000,
    "url": "/api/work-orders/{id}/files/{file}/download",
    "created_at": "2026-06-10T17:00:00.000000Z"
  }
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `403` | Insufficient role (`atendente` cannot upload) |
| `422` | Invalid file type (only JPEG, PNG, GIF, WebP, PDF allowed) |
| `422` | File exceeds 10 MB limit |

---

### `GET /api/work-orders/{id}/files/{file}/download`

Download a file. Returns the binary file as an attachment.

**Auth required:** Bearer (any role)

**Success `200`** — Binary file download with `Content-Disposition: attachment`.

**Errors**

| Status | Reason |
|--------|--------|
| `404` | File not found |

---

### `DELETE /api/work-orders/{id}/files/{file}`

Delete a file. Also removes the physical file from storage (RN-027). Restricted to `admin` or the original uploader.

**Auth required:** Bearer — `admin` or original uploader

**Success `200`**

```json
{
  "success": true,
  "message": "File deleted successfully.",
  "data": null
}
```

**Errors**

| Status | Reason |
|--------|--------|
| `403` | Not the uploader and not admin |
| `404` | File not found |

---

## Error Reference

### Standard error envelope

```json
{
  "success": false,
  "message": "Human-readable summary.",
  "errors": {}
}
```

### HTTP status codes used

| Code | Meaning |
|------|---------|
| `200` | OK |
| `201` | Created |
| `401` | Unauthenticated (missing, expired, or invalid token) |
| `403` | Forbidden (authenticated but insufficient permission) |
| `404` | Resource not found |
| `409` | Conflict (resource state prevents the operation) |
| `422` | Validation failed — `errors` contains field-level messages |
| `500` | Unexpected server error (never exposes stack trace in production) |

### Validation response example

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "cpf_cnpj": ["The cpf cnpj field is required."]
  }
}
```

### Business rule error example

```json
{
  "success": false,
  "message": "A client with this CPF/CNPJ already exists.",
  "errors": {
    "code": "CLIENT_CPF_CNPJ_ALREADY_EXISTS"
  }
}
```

---

## History

The History domain provides a **read-only** audit trail of meaningful system events. Entries are created automatically by Eloquent Observers (RN-030 – RN-033) — they cannot be created or deleted via the API.

### History object

```json
{
  "id": "uuid",
  "user_id": "uuid",
  "entity_type": "ticket | work_order | work_order_file",
  "entity_id": "uuid",
  "action": "ticket.created | ticket.status_changed | work_order.created | work_order.status_changed | work_order.service_value_updated | file.uploaded | file.deleted",
  "description": "Human-readable description of the change.",
  "created_at": "2026-06-10T20:00:00.000000Z"
}
```

### Recorded actions

| Action | Trigger |
|---|---|
| `ticket.created` | Ticket created via `POST /tickets` |
| `ticket.status_changed` | `POST /tickets/{id}/start`, `/resolve`, `/cancel` |
| `work_order.created` | Work order created via `POST /work-orders` |
| `work_order.status_changed` | `POST /work-orders/{id}/start`, `/finalize` |
| `work_order.service_value_updated` | `PUT /work-orders/{id}` when `service_value` changes (RN-033) |
| `file.uploaded` | File uploaded via `POST /work-orders/{id}/files` |
| `file.deleted` | File deleted via `DELETE /work-orders/{id}/files/{fileId}` |

---

### `GET /api/histories`

List all history entries, ordered by most recent. Supports filtering by entity.

**Auth required:** Bearer — all roles

**Query parameters**

| Parameter | Type | Description |
|---|---|---|
| `entity_type` | string | Filter by entity type (`ticket`, `work_order`, `work_order_file`) |
| `entity_id` | uuid | Filter by specific entity UUID |
| `user_id` | uuid | Filter by the user who performed the action |

**Success `200`**

```json
{
  "success": true,
  "message": "History retrieved successfully.",
  "data": [
    {
      "id": "...",
      "user_id": "...",
      "entity_type": "ticket",
      "entity_id": "...",
      "action": "ticket.status_changed",
      "description": "Ticket status changed from \"aberto\" to \"em_andamento\".",
      "created_at": "2026-06-10T21:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

---

### `GET /api/histories/{id}`

Retrieve a single history entry.

**Auth required:** Bearer — all roles

**Success `200`**

```json
{
  "success": true,
  "message": "History entry retrieved successfully.",
  "data": {
    "id": "...",
    "user_id": "...",
    "entity_type": "work_order",
    "entity_id": "...",
    "action": "work_order.service_value_updated",
    "description": "Work order OS-00001 service value changed from \"100\" to \"999.99\".",
    "created_at": "2026-06-10T22:00:00.000000Z"
  }
}
```

**Errors**

| Status | Description |
|--------|-------------|
| `401` | Unauthenticated |
| `403` | Insufficient permissions |
| `404` | History entry not found |
