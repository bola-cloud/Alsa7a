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
**Body:**
- `name`: string
- `email`: string
- `password`: string
- `password_confirmation`: string
- `onesignal_subscription`: string/object (Optional, Player ID string or subscription object)

### Get Available Clubs
**GET** `/auth/clubs-available`
**Response:** List of clubs available for claiming.
```json
{
    "status": true,
    "data": [
        { "id": 1, "name": "Al Ahli" },
        { "id": 5, "name": "Free Club" }
    ]
}
```

### Login
**POST** `/auth/login`
**Body:**
- `email`: string
- `password`: string
- `onesignal_subscription`: string/object (Optional)
**Response:** Returns token.

### Logout (Protected)
**POST** `/auth/logout`

### Get User (Protected)
**GET** `/user`
**Response:** Current user data.

### Update OneSignal Subscription (Protected)
**POST** `/users/onesignal-subscription`
**Body:**
- `onesignal_subscription`: string/object (Required, Player ID string or object e.g., `{ "id": "..." }`)
**Response:** Success message.

### Get Club Details
**GET** `/clubs/{id}`
**Response:**
```json
{
    "status": true,
    "data": {
        "club": { 
            "id": 1, 
            "name": "Al Ahli", 
            "teams": [
                {
                    "id": 5,
                    "name": "First Team",
                    "sport": { "id": 1, "name": "Football" }
                }
            ],
            ... 
        },
        "roster": {
            "Player": [ { "id": 10, "name": "Player 1", ... } ],
            "Staff": [ { "id": 11, "name": "Coach 1", ... } ],
            "Other": []
        }
    },
    "message": "Club details retrieved successfully"
}
```

### Update Club Leagues (Protected)
**POST** `/clubs/{id}/leagues`
**Body:**
- `leagues`: array of integers (Required, List of League IDs)
**Response:**
```json
{
    "status": true,
    "message": "Club leagues updated successfully",
    "data": { ... }
}
```

### Home (Public)
**GET** `/home`
**Response:**
```json
{
    "status": true,
    "data": {
        "sliders": [...],
        "categories": [...],
        "featured_clubs": [...],
        "leagues": [
            {
                "id": 1,
                "name": "Premier League",
                "clubs": [ { "id": 1, "name": "Club A" } ]
            }
        ]
    },
    "message": "Home data retrieved successfully"
}
```

### Categories
**GET** `/categories`
**Response:**
```json
{
    "parent_categories": [
        {
            "id": 1,
            "name": "Sports",
            "name_en": "Sports",
            "name_ar": "رياضة",
            "image": "...",
            "categories": [
                {
                    "id": 10,
                    "name": "Football",
                    "image": "...",
                    "parent_category_id": 1
                }
            ]
        }
    ]
}
```

### Questions
**GET** `/questions`
**Params:**
- `category_id`: int (Required)

**Response:**
```json
{
    "questions": [
        {
            "id": 55,
            "category_id": 13,
            "type": "multiple_choice",
            "question": "What is your gender?",
            "question_en": "What is your gender?",
            "question_ar": "ما هو جنسك؟",
            "choices": ["Male", "Female"],
            "choices_en": ["Male", "Female"],
            "choices_ar": ["ذكر", "أنثى"],
            "created_at": "...",
            "updated_at": "..."
        },
        {
            "id": 60,
            "type": "multi_select",
            "question": "Select your interests:",
            "question_en": "Select your interests:",
            "question_ar": "حدد اهتماماتك:",
            "choices": ["Sports", "Coding"],
            "choices_en": ["Sports", "Coding"],
            "choices_ar": ["رياضة", "برمجة"]
        }
    ]
}
```

### Submit Answers (Protected)
**POST** `/questions/answers`
**Body:**
- `category_id`: int (Required)
- `club_account_claim_id`: int (Optional, for Club Account categories)
- `answers`: array (Required)
    - `question_id`: int
    - `answer`: string|array

**Example Body:**
```json
{
    "category_id": 13,
    "club_account_claim_id": 5,
    "answers": [
        {
            "question_id": 55,
            "answer": "Male" 
        },
        {
            "question_id": 60,
            "answer": ["Sports", "Coding"] 
        }
    ]
}
```
**Response (201):**
```json
{
    "saved": [ ... ]
}
```

### Search (New)
**GET** `/search`
**Params:**
- `search`: string (Optional) - Search by User Name, Email, Phone, or Title.
- `category_id`: int (Optional) - Filter by User Category.
- `parent_category_id`: int (Optional) - Filter by Parent Category (returns users in any subcategory).
- `filters[question_id]`: value (Optional) - Filter users by their answer to a specific question.
  - Example: `filters[10]=Yes` (Find users who answered "Yes" to Q10).

**Response:**
```json
{
    "users": {
        "current_page": 1,
        "data": [
            { 
                "id": 5, 
                "name": "Coach Ahmed", 
                "category_id": 3,
                ...
            }
        ],
        "total": 5
    },
    "clubs": [
        {
            "id": 1,
            "name": "Al Ahli Club",
            "city": "Amman",
            "logo_url": "http://...",
            "sports": [...]
        }
    ],
    "filterable_questions": [
        {
            "id": 10,
            "question": "Do you offer private sessions?",
            "type": "boolean",
            "choices": null
        }
    ]
}
```
*Note*: `filterable_questions` is only returned when `category_id` is provided, listing non-text questions suitable for building UI filters.

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
- `provider_id`: int (optional) - Filter by specific Provider ID.

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
                    "name": "Coach Ahmed",
                    "profile_photo_url": "http://domain.com/storage/profiles/photo.jpg",
                    "category_id": 3,
                    "category": {
                         "id": 3,
                         "name": "Football Coach",
                         "parent_category": {
                             "id": 1,
                             "name": "Sports"
                         }
                    }
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

### Pay for Service or Event (Thawani)
**POST** `/requests/pay`
**Body:**
- `service_request_id`: int (Optional, for Service Requests)
- `booking_id`: int (Optional, for Event Bookings)
- `booking_id`: int (Optional, for Event Bookings)
*One of them must be present. You can use this endpoint to retry payment for pending bookings.*
*Note: Free events (price = 0) are auto-confirmed and do not require payment.*

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

### Club Requests (Join/Invite)
Manage relationships between users and clubs.

#### List Requests
**GET** `/club-requests`
This endpoint is dynamic and detects your context (Club Owner or individual).

**Params:**
- `view`: (`user` | `club`). Optional. Default: `club` if you own one, otherwise `user`.
- `sent`: (`0` | `1`). Optional. `0` for incoming requests, `1` for requests you sent.
- `status`: (`pending` | `accepted` | `rejected` | `all`). Optional. Default: `pending`.

**Example (As User - My Sent Join Requests):**
`GET /club-requests?view=user&sent=1`

#### Create Request (Join/Invite)
**POST** `/club-requests`
**Body:**
- `type`: string (Required: `join` | `invite`)
- `club_id`: int (Required if `type=join`) - The club you want to join.
- `user_id`: int (Required if `type=invite`) - The user you want to invite to your club (Only for Club Owners).

#### Respond to Request
**POST** `/club-requests/{id}/respond`
**Body:**
- `action`: string (Required: `accept` | `reject`)

*Note*: On `accept`, the user is automatically added as a member of the club.

#### Cancel/Delete Request
**DELETE** `/club-requests/{id}`
Allows the creator of a pending request to cancel it.

---

## 3b. Team Management (Protected)
Manage teams within a club. Access is restricted to the Club Owner.

### List Teams
**GET** `/clubs/{club_id}/teams`
Lists all teams belonging to a specific club.

**Response:**
```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "club_id": 10,
            "sport_id": 1,
            "name": "Under 17s",
            "short_name": "U17",
            "age_group": "2007-2009",
            "image": "http://...",
            "sport": { "id": 1, "name": "Football" }
        }
    ],
    "message": "Teams retrieved successfully"
}
```

### Create Team
**POST** `/clubs/{club_id}/teams`
**Body:**
- `name`: string (Required)
- `sport_id`: int (Required, exists in sports table)
- `age_group`: string (Optional)
- `short_name`: string (Optional)
- `jersey_color`: string (Optional)
- `coach`: string (Optional)
- `founded_year`: int (Optional)
- `image`: file (Optional)
- `active`: boolean (Optional, default true)

**Response (201):**
```json
{
    "status": true,
    "data": { "id": 1, "name": "New Team", ... },
    "message": "Team created successfully"
}
```

### Get Team Details
**GET** `/teams/{id}`
**Response:** Full team object including `club` and `sport` relationships.

### Update Team
**POST** `/teams/{id}`
Uses POST to support image replacement.
**Body:** Same as Create (except `club_id` which is fixed).

### Delete Team
**DELETE** `/teams/{id}`
**Response:**
```json
{
    "status": true,
    "message": "Team deleted successfully"
}
```

---


## 4. Provider Endpoints (Protected)

### Update Service
**POST** `/services/{id}`
**Description:** Update an existing service. Uses POST to support file uploads.
**Body:**
- `title`: string
- `description`: string
- `sport_id`: int
- `price`: number
- `days_available`: array of strings
- `location`: string (optional)
- `gallery[]`: array of images (Add new)
- `deleted_media_ids[]`: array (Remove existing)

**Response:**
```json
{
    "status": true,
    "message": "Service updated successfully"
}
```

### Delete Service
**DELETE** `/services/{id}`
**Response:**
```json
{
    "status": true,
    "message": "Service deleted successfully"
}
```

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
    "message": "Request status updated to accepted",
    "data": { "id": 101, "status": "accepted", ... }
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
        "email": "ahmed@example.com",
        "phone": "962791234567",
        "profile_title": "Pro Coach",
        "bio": "Experienced football coach...",
        "cover_photo": "http://...",
        "category": {
            "id": 3,
            "name": "Football Coach",
            "is_service_provider": true,
            "parent_category_id": 1,
            "parent_category": {
                "id": 1,
                "name": "Sports",
                "image": "..."
            }
        },
        "questions_data": [
            {
                "question_id": 1,
                "question": "Years of Experience?",
                "question_en": "Years of Experience?",
                "question_ar": "سنوات الخبرة؟",
                "type": "text",
                "choices": [],
                "choices_en": [],
                "choices_ar": [],
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
- `answered_question_ids`: `[1, 2, 5]` (IDs of answered questions)
- `questions_complete`: `true` (Boolean status)
- `verification_status`: `pending|approved` (string)
- `is_verified`: `true|false` (boolean)
- `role_in_club`: `'admin'|'member'|null` (Indicates owner vs member status)
- `pending_join_requests`: `[...]` (List of user objects, if Club Admin)
- `pending_club_invites`: `[...]` (List of club objects, if Regular User)
- `club_details`: (For Club Admins/Members)
    - `banner`: string (Banner image URL)
    - `teams`: array
        - `id`, `name`, `age_group`, `image`
        - `members`: array of users (`id`, `name`, `image`, `position`, `number`)

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

### Get User Followers (Paginated)
**GET** `/users/{id}/followers`
**Response:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 10,
                "name": "Fan User",
                "email": "fan@example.com",
                "profile_photo_path": "profiles/photo.jpg",
                "profile_title": "Fan"
            }
        ]
    },
    "my_following_ids": [10, 15],
    "message": "Followers retrieved successfully"
}
```
*Note*: `my_following_ids` allows the frontend to show the correct "Follow" state for each user in the list.

### Get User Following (Paginated)
**GET** `/users/{id}/following`
**Response:** Same structure as `/followers`.

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

### Get Post Likes (Paginated)
**GET** `/posts/{id}/likes`
**Response:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 101,
                "user_id": 5,
                "user": {
                    "id": 5,
                    "name": "Liker Name",
                    "profile_photo_path": "..."
                }
            }
        ]
    }
}
```

### Comments (Protected)

#### List Comments
**GET** `/posts/{id}/comments`
**Response:** Paginated list of comments.

#### Add Comment
**POST** `/posts/{id}/comments`
**Body:** `body` (string)
**Response:**
```json
{
    "status": true,
    "message": "Comment created successfully",
    "data": { "id": 50, "body": "Nice post!", "user": { ... } }
}
```

#### Update Comment
**POST** `/comments/{id}` (Method spoofing for consistency, or PUT)
**Body:** `body` (string)

#### Delete Comment
**DELETE** `/comments/{id}`

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
- `user_id`: int (Optional, filter by specific user)
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

### Interact with Community Post
**Like/Unlike Post:**
- `POST /community/posts/{id}/like`
- Response: `{"status": true, "message": "Liked/Unliked", "is_liked": boolean}`

**Add Comment:**
- `POST /community/posts/{id}/comments`
- Body: `body` (string, required).

**Get Comments:**
- `GET /community/posts/{id}/comments`

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



### My Event Bookings
**GET** `/my-bookings`
**Response:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 10,
                "event_id": 5,
                "status": "confirmed",
                "payment_status": "paid",
                "event": { "id": 5, "title": "Concert", ... }
            }
        ]
    }
}
```

---

## 7. Scenarios & Workflows

### Scenario 1: Service Booking Workflow
This scenario describes how a user books a service, pays for it, and communicates with the provider.

1.  **Request Service**:
    - User calls `POST /services/{id}/request` with `scheduled_at`.
    - Returns `booking_id`.
    - **Status**: `pending`.
    - **Notification**: Provider receives `ServiceRequested`.

2.  **Provider Decision**:
    - Provider calls `POST /provider/requests/{id}/status`.
    - **Status**: `accepted` (or `rejected`).
    - **Notification**: User receives `RequestStatusUpdated`.

3.  **Payment**:
    - User calls `POST /requests/pay` with `service_request_id`.
    - **Logic**: Can only pay if status is `accepted`.
    - **Result**: Returns Thawani session URL. User pays. Webhook updates `payment_status` to `paid`. Chat `Conversation` is created/unlocked.

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

### Scenario 4: Direct Chat (Social)
**Goal**: User A wants to chat with User B (e.g., from their Profile) without any service request.

1.  **View Profile**:
    - User A views User B's profile: `GET /users/{id}/profile`.
    - Profile response includes User B's `id` (e.g., 5).

2.  **Start Chat**:
    - User A calls `POST /chat/conversations` with body `{ "user_id": 5 }`.
    - **Logic**: Backend checks if conversation exists. If yes, returns it. If no, creates new one.
    - **Response**:
      ```json
      {
          "status": true,
          "data": { "id": 105, "user_one_id": 10, "user_two_id": 5, ... }
      }
      ```

3.  **Exchange Messages**:
    - User A calls `POST /chat/conversations/105/messages` to send text.
    - User B receives real-time event.

---

## 8. Real-time Chat & WebSockets

The application uses **Laravel Reverb** for real-time WebSocket communication.
Chat conversations are automatically created when a Service Request is **Accepted**.

### 1. Connection Details
Frontend clients (e.g., Flutter/React) should connect using a WebSocket client (like `laravel-echo` + `pusher-js`).

*   **Host**: `saha.wasl-x.com`
*   **Port**: `443`
*   **Scheme**: `wss` (Secure WebSocket)
*   **App Key**: (From `.env` or API config)
*   **Cluster**: `mt1` (Default)

### 2. Channels & Events
*   **Channel Name**: `private-chat.{conversation_id}`
    *   *Note*: Laravel Echo automatically prefixes `private-` to the name `chat.{id}`.
*   **Event Name**: `App\Events\MessageSent` (Default namespace) OR just `.MessageSent` if using dot notation in Echo.
*   **Payload**:
    ```json
    {
        "message": {
            "id": 12,
            "sender_id": 5,
            "body": "Hello!",
            "created_at": "...",
            "sender": { "id": 5, "name": "..." }
        }
    }
    ```

### 3. Chat API Endpoints

#### List Conversations
**GET** `/chat/conversations`
### Chat (Conversations)
**GET** `/chat/conversations`
Returns a list of unique conversations for the authenticated user (Social-style).

**POST** `/chat/conversations`
Start or get an existing conversation with a user.
**Body:**
- `user_id`: integer (Required)
**Response:** Conversation object.

**Response:**
```json
{
    "status": true,
    "data": [
        {
            "id": 1,
            "other_user": {
                "id": 5,
                "name": "Provider Name",
                "profile_photo_url": "..."
            },
            "last_message": {
                "id": 102,
                "body": "Hello, are you available?",
                "created_at": "..."
            },
            "updated_at": "..."
        }
    ],
    "message": "Conversations retrieved"
}
```

#### Get Messages (History)
**GET** `/chat/conversations/{id}`
**Response:** Paginated list of messages (latest first).
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 102,
                "body": "I am arriving now.",
                "sender_id": 5,
                "created_at": "..."
            }
        ]
    }
}
```

#### Send Message
**POST** `/chat/conversations/{id}/messages`
**Body:**
- `body`: string (Required)

**Response (201):**
```json
{
    "status": true,
    "message": "Message sent",
    "data": { "id": 103, "body": "Ok great!", ... }
}
```
    "data": { "id": 103, "body": "Ok great!", ... }
}
```
**Side Effect**: Triggers `MessageSent` event on channel `private-chat.{id}`.

---

## 8. Notifications API

### Get Notifications (History)
**GET** `/notifications`
**Response:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-string",
                "type": "App\\Notifications\\ServiceRequestNotification",
                "data": {
                    "title": "New Service Request",
                    "body": "User X requested Service Y",
                    "type": "new_request",
                    "request_id": 10,
                    "service_id": 5
                },
                "read_at": null,
                "created_at": "..."
            }
        ],
        "total": 5
    },
    "unread_count": 3,
    "message": "Notifications retrieved successfully"
}
```

### Mark as Read
**POST** `/notifications/{id}/read`
**Response:**
```json
{
    "status": true,
    "message": "Notification marked as read"
}
```

### Mark All as Read
**POST** `/notifications/read-all`
**Response:**
```json
{
    "status": true,
    "message": "All notifications marked as read"
}
```

### Delete Notification
**DELETE** `/notifications/{id}`
**Response:**
```json
{
    "status": true,
    "message": "Notification deleted"
}
```

---

## 9. Realtime / WebSockets (Laravel Reverb)

The application uses **Laravel Reverb** for real-time updates (similar to Pusher).
Mobile developers should use a standard Pusher client (e.g., `pusher-js` or `laravel-echo`) with the following configuration:

### Connection Configuration
- **Host**: `saha.wasl-x.com`
- **Port**: `443` (Default HTTPS/WSS port)
- **Scheme**: `wss` (Secure WebSocket)
- **App Key**: `my_app_key` (As currently configured on server, pending update)
  - *Note: Please check `.env` on server. Recommended key: `t6o995a86az28cff`.*
- **Cluster**: `mt1` (Default)
- **Force TLS**: `true`

### Channels
- **User Notifications**: `App.Models.User.{id}` (Private channel)
  - Events: `Illuminate\Notifications\Events\BroadcastNotificationCreated`
- **Chat**: `chat.conversations.{conversation_id}` (Presence/Private channel - TBD implementation)

### Example (Laravel Echo / JS)
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'my_app_key', // Replace with actual server key
    wsHost: 'saha.wasl-x.com',
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
});
```
