# Alsa7a Platform Feature Updates - May 2026

This document provides a detailed technical overview of the new features and API enhancements implemented in May 2026.

---

## 1. Event Creation & Approval System

To maintain content quality, club-created events now undergo a mandatory approval process by the super administrator before becoming public.

### Database Schema Changes
- **Table**: `events`
- **New Field**: `status` (Enum: `pending`, `approved`, `rejected`). Default is `pending`.

### API Endpoints (Club Side)

#### **POST `/api/v1/club/events`**
Allows a club owner to submit a new event for review.

- **Authentication**: Required (Sanctum). User must own a club.
- **Payload (Multipart Form-Data)**:
```json
{
  "title_en": "Summer Football Tournament",
  "title_ar": "بطولة كرة القدم الصيفية",
  "description_en": "Join us for a weekend tournament...",
  "description_ar": "انضم إلينا في بطولة نهاية الأسبوع...",
  "sport_id": 1,
  "start_at": "2026-06-01 18:00:00",
  "end_at": "2026-06-01 22:00:00",
  "venue": "Alsa7a Stadium",
  "price": 5.0,
  "capacity": 100,
  "featured_image": (File/Image)
}
```
- **Response (201 Created)**:
```json
{
  "status": true,
  "message": "Event created and pending approval",
  "data": {
    "id": 12,
    "club_id": 5,
    "status": "pending",
    "title_en": "Summer Football Tournament",
    "slug": "summer-football-tournament-66421abc",
    "featured_image": "https://saha.wasl-x.com/storage/events/abc.jpg"
  }
}
```

- **Error Response (403 Forbidden)**:
If the authenticated user does not own a club.
```json
{
  "status": false,
  "message": "User does not own a club"
}
```

#### **GET `/api/v1/club/events`**
Returns a list of events owned by the authenticated club, including their current status.

---

### API Endpoints (Public)

#### **GET `/api/v1/events`**
Retrieves the list of events.
- **Filter Applied**: Now only returns events with `status: "approved"`.

#### **GET `/api/v1/events/{id}`**
Retrieves details for a single event.
- **Security**: Will return a 404 if the event is not `approved`.

---

## 2. Slider Navigation Links

Sliders can now be linked to internal app pages or external URLs.

### Database Schema Changes
- **Table**: `sliders`
- **New Field**: `link` (String, Nullable, Max 1000).

### API Support
The `link` field is now included in all slider-related API responses (e.g., `GET /api/v1/home`).

**Example Response Fragment**:
```json
{
  "id": 3,
  "title": "New League Starting Soon",
  "image_url": "https://saha.wasl-x.com/storage/sliders/img.png",
  "link": "https://alsa7a.com/leagues/12"
}
```

---

## 3. Player-Club Request Lifecycle

Already implemented system facilitating requests between players and clubs.

### API Endpoints

#### **POST `/api/v1/club-requests`**
Used by a player to request joining a club, or by a club to invite a player.
- **Payload**:
```json
{
  "club_id": 5,
  "user_id": 10,
  "type": "join", // or "invite"
  "message": "I would like to join your academy."
}
```

#### **POST `/api/v1/club-requests/{id}/respond`**
The receiving party (Club or Player) responds to the request.
- **Payload**:
```json
{
  "status": "accepted" // or "rejected"
}
```

---

## 4. Comment Management (Edit & Delete)

Ensuring users have control over their social interactions.

### API Endpoints

#### **POST `/api/v1/comments/{id}`** (Update)
- **Authentication**: Required. Only the author can update.
- **Payload**: `{"comment": "Updated text content"}`

#### **DELETE `/api/v1/comments/{id}`** (Delete)
- **Authorization**:
    1. The author of the comment.
    2. The owner of the post where the comment resides.

---