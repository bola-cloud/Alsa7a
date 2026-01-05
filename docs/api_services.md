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

### Global Settings
**GET** `/settings`
**Response:**
```json
{
    "status": true,
    "data": {
        "site_name": "Alsa7a",
        "site_logo": "http://...",
        "site_icon": "http://...",
        "service_commission": "10",
        "currency": "EGP"
    }
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

### Pay for Service (Thawani)
**POST** `/requests/pay`
**Body:**
- `service_request_id`: int (Required)

**Response:**
```json
{
    "status": true,
    "message": "Payment session created",
    "data": {
        "session_id": "sess_12345",
        "redirect_url": "https://uatcheckout.thawani.al/pay/sess_12345?key=..."
    }
}
```
**Flow:**
1. Call API -> Get `redirect_url`.
2. Open URL in WebView.
3. User pays.
4. Thawani redirects to `success_url` (or `cancel_url`).
5. Webhook updates status in background.

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

### Send Message
**POST** `/chat/conversations/{id}/messages`
**Body:** `body` (string)
**Response:** Message object.

---

## 3. Club & Profiles

### List Clubs
**GET** `/clubs`

### Club Details (Roster & Staff)
**GET** `/clubs/{id}`
**Response:**
```json
{
    "status": true,
    "data": {
        "club": { "id": 1, "name": "Amman FC", "logo_url": "..." },
        "roster": {
            "Player": [
                { "id": 5, "name": "Rami", "position": "GK", "image": "..." }
            ],
            "Coach": [
                { "id": 8, "name": "Coach Ali", "image": "..." }
            ]
        }
    }
}
```

### User Profile (Dual View)
**GET** `/users/{id}/profile`
**Response:**
Returns both social `stats` (posts, followers) and `professional` details (club, position, number).

---

## 4. Provider Endpoints (Protected)

### Create Service
**POST** `/services`
**Body:**
- `title`: string
- `description`: string
- `sport_id`: int
- `price`: number
- `days_available`: array of strings (e.g., `["MON", "WED"]`, valid: SUN..SAT)
- `location`: string (optional)
- `gallery[]`: array of images

**Response:**
```json
{
    "status": true,
    "message": "Service created successfully",
    "data": {
        "id": 15,
        "title": "My New Service",
        "media": [ ... ]
    }
}
```

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
        "questions_data": [
            {
                "question_id": 1,
                "question": "Years of Experience?",
                "type": "text",
                "answer": "5 Years"
            }
        ],
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

### Get My Profile (Protected)
**GET** `/my-profile`
**Response:** Same structure as `/users/{id}/profile` but for the authenticated user, including:
- `questions_data` (Questions answered by the user)
- `professional` details
- `gallery` (Posts)

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

### Get User Posts (Pagination)
**GET** `/users/{id}/posts`
**Response:** Standard pagination (data property contains array of posts).
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            { "id": 1, "content": "Hello", "type": "text", "is_liked": false }
        ]
    }
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

### Upload Verification Documents (Protected)
**POST** `/users/verification/upload`
**Body:**
- `documents[]`: file (Required, PDF/Images)
**Response:**
```json
{
    "status": true,
    "message": "Documents uploaded. Please wait for admin approval.",
    "data": { "verification_status": "pending" }
}
```

### Check Verification Status (Protected)
**GET** `/users/verification/status`
**Response:**
```json
{
    "status": true,
    "data": {
        "verification_status": "pending",
        "is_approved": false,
        "rejection_reason": null
    }
}
```

---

## 5. Community API
### Profile Posts (Instagram-like)
The interactions below apply to the `Post` entity (Profile/Media posts).

### Community Feed (Fan Zone)
**GET** `/posts`
**Response:** list of posts with user, likes, comments.

### Create Post (Protected)
**POST** `/posts`
**Body:**
- `content`: string (optional if media provided)
- `image`: file (optional)
- `video`: file (optional)

**Response:**
```json
{
    "status": true,
    "message": "Post created successfully",
    "data": { "id": 10, "type": "image", ... }
}
```

### Update Post (Protected)
**POST** `/posts/{id}` (Method spoofing for files)
**Body:**
- `content`: string (optional)
- `image`: file (optional, replaces old)
- `video`: file (optional, replaces old)

**Response:**
```json
{
    "status": true,
    "message": "Post updated successfully"
}
```

### Delete Post (Protected)
**DELETE** `/posts/{id}`
**Response:**
```json
{
    "status": true,
    "message": "Post deleted successfully"
}
```

### Interact (Protected)
**POST** `/posts/{id}/like` - Toggle like.
**POST** `/posts/{id}/comment` - **Body**: `body` (string).

---

### Community Blogs (Facebook-like)
Categorized discussions and articles.

### Get Categories
**GET** `/community/categories`
**Response:**
```json
{
    "status": true,
    "data": [
        { "id": 1, "name": "General", "image": "..." }
    ]
}
```

### Get Community Posts
**GET** `/community/posts`
**Params:**
- `category_id`: int (Optional filter)
- `page`: int

### Create Community Post
**POST** `/community/posts`
**Body:**
- `community_category_id`: int
- `content`: string
- `image`: file (Optional)

### Update Community Post
**POST** `/community/posts/{id}` (Method spoofing)
**Body:** `content`, `image`, `community_category_id`.

### Delete Community Post
**DELETE** `/community/posts/{id}`

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


---

## 7. Scenarios & Workflows

### Scenario 1: Service Booking Workflow
This scenario describes how a user books a service, pays for it, and communicates with the provider.

1.  **Request Service**:
    - User calls `POST /services/{id}/request` with `scheduled_at`.
    - **Status**: `pending`.
    - **Notification**: Provider receives `ServiceRequested`.

2.  **Provider Decision**:
    - Provider calls `POST /provider/requests/{id}/status`.
    - **Status**: `accepted` (or `rejected`).
    - **Notification**: User receives `RequestStatusUpdated`.

3.  **Payment**:
    - User calls `POST /requests/{id}/pay` (Mock payment).
    - **Logic**: Can only pay if status is `accepted`.
    - **Result**: `payment_status` becomes `paid`. Chat `Conversation` is created/unlocked.

4.  **Chat**:
    - User/Provider calls `GET /chat/conversations` to find the chat.
    - They exchange messages via `POST /chat/conversations/{id}/messages`.
    - Real-time updates via `MessageSent` event (Laravel Reverb).

### Scenario 2: Club & Player Dual Profiles
This scenario details the structure of Club Rosters and Player Profiles.

1.  **Club Profile**:
    - `GET /clubs/{id}` returns the club's details (Logo, City, etc.).
    - **Roster**:The API automatically groups all users belonging to this club by their `Category`.
    - **Example Response**:
      ```json
      "roster": {
          "Player": [ ... ],
          "Coach": [ ... ],
          "Physiotherapist": [ ... ]
      }
      ```

2.  **Player (Dual) Profile**:
    - Players are `Users` with a specific `category_id`.
    - `GET /users/{id}/profile` returns a response split into two sections:
        - **Social**: "Instagram-like" data (Bio, Followers, Gallery).
        - **Professional**: "Player Card" data (Club, Position, Number, Stats).
    - This allows usage in different contexts (Social Feed vs Match Lineup).

### Scenario 3: User Verification & Approval
This scenario describes the strict user onboarding process.

1.  **Registration (Pending Approval)**:
    - User registers via `POST /auth/register`.
    - Response includes `"requires_approval": true`.
    - User **cannot login** yet (returns 403).

2.  **Document Verification (If Verified Category)**:
    - If user selected a constrained Category (e.g., "Coach"):
    - User calls `POST /users/verification/upload` with ID/Certificate files.
    - Status updates to `pending` in Admin Panel.

3.  **Admin Approval**:
    - Admin reviews documents in Dashboard.
    - Admin clicks "Approve". `is_approved` becomes `true`.

4.  **Access Granted**:
    - User can now successfully `POST /auth/login`.
