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
        "street": "Rua das Flores",
        "number": "123",
        "complement": null,
        "district": "Centro",
        "city": "São Paulo",
        "state": "SP",
        "zip_code": "01310-100"
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
      "title": "Impressora HP P1102 com erro",
      "description": "Parou após atualização do driver.",
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
