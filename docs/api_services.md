# Services & Bookings API Documentation

## Overview
This document outlines the API endpoints for managing Services, Bookings (Requests), and Reviews.
**Base URL**: `{{base_url}}/api/v1`

## Authentication
Protected routes require a Bearer Token in the header.
`Authorization: Bearer <your_token>`

## Enums
### Service Request Status
- `pending`: Initial state.
- `accepted`: Provider accepted.
- `rejected`: Provider rejected.
- `completed`: Service done.
- `canceled`: User canceled.

### Payment Status
- `pending`, `held`, `released`, `refunded`.

### Payment Status
- `pending`, `held`, `released`, `refunded`.

---

## 1. Authentication & Base Data

### Register
**POST** `/auth/register`
**Body:** `name`, `email`, `password`, `password_confirmation`...

### Login
**POST** `/auth/login`
**Body:** `email`, `password`
**Response:** Returns token.

### Logout (Protected)
**POST** `/auth/logout`

### Get User (Protected)
**GET** `/user`
**Response:** Current user data.

### Home (Public)
**GET** `/home`
**Response:** Home screen data.

### Categories
**GET** `/categories`

### Questions
**GET** `/questions`
**POST** `/questions/answers` (Protected)

---

## 2. Public Enpoints (Guest/User)

### List All Services
**GET** `/services`
Accepts optional filters.
**Params:**
- `page`: int (default 1)
- `sport_id`: int (optional)
- `category_id`: int | 'all' (optional) - Filter by Provider's Category. 'all' returns everything.
- `location`: string (optional, search term)

**Response:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "provider_id": 5,
                "club_id": 2,
                "sport_id": 1,
                "title": "Professional Football Training",
                "slug": "professional-football-training",
                "description": "High intensity training for elite players...",
                "location": "Amman International Stadium",
                "days_available": ["Monday", "Wednesday", "Friday"],
                "price": 50.00,
                "currency": "JOD",
                "is_active": true,
                "average_rating": 4.8,
                "provider": {
                    "id": 5,
                    "name": "Coach Ahmed",
                    "profile_photo_url": "http://domain.com/storage/profiles/photo.jpg",
                    "category_id": 3
                },
                "sport": { "id": 1, "name": "Football" },
                "club": { "id": 2, "name": "Amman FC" }
            }
        ],
        "total": 15,
        "per_page": 10
    },
    "message": "Services retrieved successfully"
}
```

### Get Service Details
**GET** `/services/{id}`
**Response:**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "title": "Football Training",
        "description": "...",
        "reviews": [ ... ]
    }
}
```

---

## 2. User Enpoints (Protected)

### Book a Service (Request)
**POST** `/services/{id}/request`
**Body:**
- `scheduled_at`: datetime (Required, Y-m-d H:i:s, must be future)
- `message`: string (Optional)

**Response (201):**
```json
{
    "status": true,
    "message": "Service requested successfully",
    "data": { "id": 101, "status": "pending", ... }
}
```

### My Requests History
**GET** `/my-requests`
Lists all requests made by the authenticated user.
**Response:**
```json
{
    "status": true,
    "data": { "data": [ ... ] }
}
```

### Rate a Service
**POST** `/services/{id}/rate`
**Body:**
- `rating`: int (Required, 1-5)
- `comment`: string (Optional)

**Response (201):**
```json
{
    "status": true,
    "message": "Review submitted successfully"
}
```

### Pay for Service
**POST** `/requests/{id}/pay`
**Response:**
```json
{
    "status": true,
    "message": "Payment successful",
    "data": { "service_request": {...}, "conversation_id": 10 }
}
```

### Chat (Conversations)
**GET** `/chat/conversations`
**Response:** List of conversations.

### Get Messages
**GET** `/chat/conversations/{id}`
**Response:** List of messages.

### Send Message
**POST** `/chat/conversations/{id}/messages`
**Body:** `body` (string)
**Response:** Message object.

---

## 3. Provider Endpoints (Protected)

### List Incoming Requests
**GET** `/provider/requests`
Lists requests where the logged-in user is the **Provider**.

### update Request Status
**POST** `/provider/requests/{id}/status`
**Body:**
- `status`: string (Required: `accepted`, `rejected`, `completed`, `canceled`)

**Response:**
```json
{
    "status": true,
    "message": "Request status updated to accepted"
}
```

---

## 4. Profile API (Public & Protected)

### Get Provider Profile (Public)
**GET** `/users/{id}/profile`
**Response:**
```json
{
    "status": true,
    "data": {
        "id": 5,
        "name": "Ahmed",
        "username": "ahmed@example.com",
        "profile_title": "Pro Coach",
        "bio": "Experienced football coach...",
        "image": "http://...",
        "stats": {
            "posts": 102,
            "followers": 5413,
            "following": 225
        },
        "is_following": true,
        "gallery": [
            { "id": 10, "image": "http://...", "type": "image" }
        ]
    }
}
```

### Update My Profile (Protected)
**POST** `/users/profile`
**Body:**
- `name`: string
- `email`: string
- `phone`: string
- `password`: string (min 8 chars)
- `birth_date`: date (Y-m-d)
- `country`: string
- `bio`: string
- `profile_title`: string
- `image`: file (Profile Photo)
- `cover_photo`: file (Cover Photo)
- `gallery_images[]`: file array (Add images to gallery)
- `gallery_videos[]`: file array (Add videos to gallery)

**Response:**
```json
{
    "status": true,
    "message": "Profile updated successfully"
}
```

### Follow/Unfollow User (Protected)
**POST** `/users/{id}/follow`
**Response:**
```json
{
    "status": true,
    "message": "Followed successfully",
    "data": { "status": "followed" }
}
```

---

## 5. Community API

### Get News (Public)
**GET** `/news`
**Params:** `sport_id` (optional), `page`
**Response:**
```json
{
    "status": true,
    "data": {
        "data": [
            {
                "id": 1,
                "title": "Football Match Result",
                "content": "...",
                "likes_count": 50,
                "comments_count": 10,
                "is_liked": false
            }
        ]
    }
}
```

### Community Feed (Fan Zone)
**GET** `/posts`
**Response:** list of posts with user, likes, comments.

### Create Post (Protected)
**POST** `/posts`
**Body:**
- `content`: string
- `image`: file (optional)
- `video`: file (optional)

### Interact (Protected)
**POST** `/posts/{id}/like` - Toggle like.
**POST** `/posts/{id}/comment` - **Body**: `body` (string).

---

## 6. Event API

### Get Events (Public)
**GET** `/events`
**Params:**
- `type`: `upcoming` (default), `trending`
- `month`: integer (1-12)
- `page`: integer
**Response:** List of events.

### Get Event Details (Public)
**GET** `/events/{id}`
**Response:** Event details including `ticket_types` (JSON).

### Book Event (Protected)
**POST** `/events/{id}/book`
**Body:**
- `ticket_type`: string (Must match one of the event's `ticket_types` names, e.g., "VIP", "Regular")
- `seats`: integer (default 1)
- `name`: string
- `email`: string
- `phone`: string
- `country_code`: string (optional)

**Response:**
```json
{
    "status": true,
    "message": "Booking successful",
    "data": { "id": 10, "status": "pending", ... }
}
```

