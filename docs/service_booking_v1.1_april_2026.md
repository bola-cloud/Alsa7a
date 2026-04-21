# 📋 API Documentation — New Service Types & Free Booking Logic

**Version:** 1.1  
**Date:** 2026-04-19  
**Base URL:** `{{base_url}}/api/v1`  
**Auth:** Bearer Token (Sanctum)

---

## 🆕 What's New

1. **New service type added:** `loan_request` (طلب إعارة)
2. **Free-twice logic:** First 2 requests for special service types are free and recorded as paid
3. **New field `is_free`** in the Book Service API for club-invited free sessions
4. **`is_free` field returned** in all service request responses
5. **Chat Meta:** New `meta` (JSON) field added to messages for custom data (included in POST and GET)

---

## 📦 Service Types

The `type` field on a service can be one of:

| Value | Description |
|-------|-------------|
| `default` | خدمة عادية — charged normally |
| `performance_experience` | تجربة الأداء — first 2 requests free |
| `loan_request` | طلب إعارة *(NEW)* — first 2 requests free |

> **Note:** Services of type `performance_experience` and `loan_request` are automatically created by the system for every club. The mobile app does not need to create them manually.

---

## 📌 Book a Service

### `POST /services/{id}/request`

**Auth Required:** ✅ Yes

**Request Body:**

```json
{
  "scheduled_at": "2026-05-01 10:00:00",
  "message": "رسالة اختيارية للمزود",
  "is_free": false
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `scheduled_at` | `datetime` | ✅ | Must be a future date/time |
| `message` | `string` | ❌ | Optional message to the provider (max 500 chars) |
| `is_free` | `boolean` | ❌ | Default: `false`. See details below ⬇️ |

---

### 💡 Pricing Logic (important)

#### Case 1: `is_free = true` (Club invites player for free)
Used when the **club** wants to invite a player for free, regardless of how many times they've used the service before.

- **Result:** `price = 0`, `payment_status = "paid"`, `is_free = true`
- No payment needed, session is considered fully paid.

```json
{
  "scheduled_at": "2026-05-01 10:00:00",
  "is_free": true
}
```

#### Case 2: `is_free = false` (or not sent) — Special Service Types
For services of type `performance_experience` or `loan_request`:

| Request Count (same type, same provider) | Price | payment_status |
|------------------------------------------|-------|----------------|
| 1st request | `0` OMR | `paid` ✅ |
| 2nd request | `0` OMR | `paid` ✅ |
| 3rd request and beyond | From settings (~1 OMR) | `pending` 💳 |

> The count is per **requester ↔ provider ↔ service type** combination.  
> Canceled or rejected requests do **not** count toward the 2 free uses.

#### Case 3: `is_free = false` — Regular Service (`default` type)
Normal pricing: `price = service.price`, `payment_status = "pending"`.

---

### ✅ Success Response (`201 Created`)

```json
{
  "status": true,
  "message": "Service requested successfully",
  "data": {
    "id": 42,
    "service_id": 10,
    "requester_id": 5,
    "provider_id": 3,
    "status": "pending",
    "scheduled_at": "2026-05-01T10:00:00.000000Z",
    "message": null,
    "price": "0.00",
    "payment_status": "paid",
    "is_free": true,
    "created_at": "2026-04-19T13:30:00.000000Z",
    "service": { "..." : "..." },
    "provider": { "..." : "..." },
    "requester": { "..." : "..." },
    "provider_profile": { "..." : "..." },
    "requester_profile": { "..." : "..." }
  }
}
```

#### Key fields in response:

| Field | Type | Description |
|-------|------|-------------|
| `price` | `string` | Final price applied (may be `"0.00"` for free sessions) |
| `payment_status` | `string` | `"paid"` = no payment needed / `"pending"` = payment required |
| `is_free` | `boolean` | `true` if the session was free (either club-invited or within the 2-free quota) |
| `status` | `string` | Booking status: `pending`, `accepted`, `completed`, `canceled`, `rejected` |

---

### ❌ Error Responses

**422 Validation Error:**
```json
{
  "status": false,
  "errors": {
    "scheduled_at": ["The scheduled at field is required."]
  }
}
```

**400 — Booking own service:**
```json
{
  "status": false,
  "message": "You cannot book your own service"
}
```

**404 — Service not found:**
```json
{
  "status": false,
  "message": "Service not found"
}
```

---

## 📋 List My Requests

### `GET /my-requests`

**Auth Required:** ✅ Yes

No changes to this endpoint. The `is_free` field is now included in each request object in the response.

---

## 🔍 Filter Services by Type

### `GET /services?type=loan_request`

Use the `type` query param to filter services:

```
GET /services?type=performance_experience
GET /services?type=loan_request
GET /services?type=default
```

---

## 🏟️ Club Default Services (Background Info)

When a club is assigned an owner (user_id), the system **automatically** creates two services linked to that club:

| Service | Type | Default Price |
|---------|------|---------------|
| تجربة الأداء | `performance_experience` | 1 OMR |
| طلب إعارة | `loan_request` | 1 OMR |

> The default price can be changed from the admin settings panel.  
> These services appear in the `/services` endpoint like any other service, filtered by `club_id` or `provider_id`.

---

## 🔄 Suggested Mobile Flow for Special Services

```
User taps "Book Performance Experience" or "Book Loan Request"
        ↓
POST /services/{id}/request
  with { scheduled_at: "...", is_free: false }
        ↓
Check response:
  - payment_status == "paid"  → Show "Booked Successfully! Free session 🎉"
  - payment_status == "pending" → Proceed to payment screen
```

For club-admin booking a player for free:
```
Club admin selects player
        ↓
POST /services/{id}/request
  with { scheduled_at: "...", is_free: true }
        ↓
payment_status == "paid" → "Player invited successfully!"
```

---

---

## 💬 Chat Message Meta

### `POST /chat/conversations/{id}/messages`

**Auth Required:** ✅ Yes

Now accepts an optional `meta` field for storing additional message details. This field is also **returned in all GET requests** (conversation messages list).

**Request Body (POST):**

```json
{
  "body": "مرحبا بك",
  "meta": {
    "type": "custom_type",
    "details": "any extra data here"
  }
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `body` | `string` | ✅ | The message text |
| `meta` | `object` | ❌ | JSON object for any extra metadata |

**Sample Response (GET /chat/conversations/{id}):**

```json
{
  "status": true,
  "data": {
    "data": [
      {
        "id": 1,
        "body": "مرحبا بك",
        "meta": { "type": "image", "url": "..." },
        "sender_id": 5,
        "created_at": "..."
      }
    ]
  }
}
```

---

## 📈 Public Services Activity

### `GET /services-activity`

Returns a chronological feed of recent service bookings across the platform. This is intended for the public "Activity" or "Home" screen.

**Auth Required:** ❌ No (Optional for follow status)

**Sample Response:**
```json
{
  "status": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 105,
        "service_id": 12,
        "requester_id": 45,
        "provider_id": 3,
        "status": "pending",
        "price": "1.00",
        "is_free": false,
        "created_at": "2026-04-21T10:00:00Z",
        "service": {
          "id": 12,
          "name": "Personal Training",
          "featured_image": "..."
        },
        "provider_profile": { "id": 3, "name": "Coach Ahmed", "image": "..." },
        "requester_profile": { "id": 45, "name": "User Name", "image": "..." }
      }
    ]
  }
}
```

---

## 📱 Feed & Profile Enhancements

### 1. Follow Status in Feed
In `GET /posts` (Community Feed), each user object now includes:
- `is_following`: `boolean` — Is the current user following this post's author?
- `is_follower`: `boolean` — Is this author following the current user?

### 2. Question Sorting System
Questions are now ordered by a custom `sort_order` defined in the admin panel.

#### `GET /questions` & `GET /search` (filterable_questions)
- Returned questions are now sorted by `sort_order` (ascending).
- Questions with `sort_order = 0` (unordered) appear at the end.

#### `GET /users/{id}/profile`
The `questions_data` array now includes:
- `sort_order`: `integer`
- The entire array is sorted according to this order.

---

*For questions, contact the backend team.*

