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

## 5. Reels Video & HLS System (FFmpeg Integration)

To optimize storage space on our 50GB VPS and provide a smooth, buffering-free streaming experience like TikTok or Instagram, Alsa7a implements a server-side video compression and **HLS (HTTP Live Streaming)** adaptive chunking pipeline.

### Architectural Workflow
1. **Upload**: User uploads a video (`.mp4`, `.mov`, etc.) via the standard post endpoint. The post is temporarily marked as `processing_status = "pending"`.
2. **Background Processing**: A background queue job `ProcessReelVideo` processes the raw video asynchronously using **FFmpeg**:
   - Compresses the video into three adaptive bitrates: Low (500 Kbps), Medium (1500 Kbps), and High (3000 Kbps).
   - Segments the video into small `.ts` chunks.
   - Generates a master `.m3u8` HLS playlist referencing these segments.
3. **Storage Preservation**: Once chunking is complete, the original heavy raw `.mp4` file is safely deleted from the server disk to save space.
4. **Publish**: The post is marked as `processing_status = "completed"`, immediately making it visible in feeds.

### Database Schema Changes
- **Table**: `posts` & `community_posts`
  - `hls_url` (String, Nullable): URL to the `.m3u8` playlist.
  - `processing_status` (Enum: `completed`, `pending`, `processing`, `failed`): Tracks FFmpeg queue state. Defaults to `completed`.
- **Table**: `posts` only
  - `views_count` (Unsigned Integer): Tracks views. Defaults to `0`.

---

### API Endpoints

#### **POST `/api/v1/posts`** (Upload Reel Video)
Submits a new standard video post to the processing pipeline.
- **Authentication**: Required (Sanctum).
- **Payload (Multipart Form-Data)**:
  - `content` (String, Optional): Post description.
  - `video` (File, Required): High-resolution MP4/MOV video up to 50MB.
  - `video_thumbnail` (File, Optional): Preview thumbnail image.
- **Response (201 Created)**:
```json
{
  "status": true,
  "message": "Post created successfully",
  "data": {
    "id": 105,
    "user_id": 12,
    "content": "فيديو ريلز تجريبي⚽️",
    "image": "posts/videos/temp_name.mp4",
    "video_thumbnail": "storage/posts/thumbnails/preview.jpg",
    "type": "video",
    "processing_status": "pending",
    "views_count": 0,
    "hls_url": null
  }
}
```

#### **GET `/api/v1/reels`** (Reels Feed)
Retrieves a paginated list of fully-processed profile videos.
- **Filter Applied**: Automatically excludes posts with `processing_status != "completed"` or `type != "video"`.
- **Authentication**: Optional (providing Sanctum Bearer token populates `is_liked` & `is_following` statuses).
- **Response (200 OK)**:
```json
{
  "status": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 105,
        "user_id": 12,
        "content": "فيديو ريلز تجريبي⚽️",
        "video_thumbnail": "https://alsaha.tech/storage/posts/thumbnails/preview.jpg",
        "type": "video",
        "hls_url": "https://alsaha.tech/storage/reels/105_xYZaBc12/playlist.m3u8",
        "processing_status": "completed",
        "views_count": 421,
        "likes_count": 15,
        "comments_count": 3,
        "is_liked": false,
        "user": {
          "id": 12,
          "name": "Alex",
          "is_following": true
        }
      }
    ]
  },
  "message": "Reels retrieved successfully"
}
```

#### **POST `/api/v1/posts/{id}/view`** (Increment View Count)
Increments the `views_count` for a post/reel. 
- **Authentication**: Optional (supports anonymous guest views to ensure accurate engagement tracking).
- **Response (200 OK)**:
```json
{
  "status": true,
  "message": "View incremented successfully",
  "views_count": 422
}
```

---

## 6. Unique User Identifier (`alsa7a_id`)

For enhanced security and a more professional presentation, real database IDs (`id`) are visually masked across the platform.

### Implementation
A new dynamic accessor has been added to the `User` model, which generates a padded, branded unique identifier on-the-fly. This identifier is automatically appended to all User objects in every API response.

- **Format**: Numeric (e.g., `100150` for User ID 15 via the formula `100000 + (ID * 10)`).
- **Attribute Name**: `alsa7a_id`

### API Impact
All endpoints returning user data (Login, Profile, Feed, Comments, Reels, etc.) will now include the `alsa7a_id` alongside standard user fields:
```json
{
  "id": 15,
  "alsa7a_id": 100150,
  "name": "Ahmed",
  "profile_photo_url": "https://alsaha.tech/storage/..."
}
```

---

## 7. Club Member Management APIs

Three new endpoints have been introduced to allow Club Owners to manage club members (players, coaches, staff) directly through the mobile application.

### Authorization & Safety Guards
- **Strictly Owner-Only**: Only the owner of the club (`club.user_id === auth_user.id`) can perform update or delete actions.
- **Admin Panel Control**: System administrators are barred from executing these actions through these APIs; they must use the Filament admin dashboard.
- **Protected Accounts**: Club owners cannot remove themselves, nor can they delete or modify any other accounts belonging to the "Club" category (`isProtected()`).
- **No Player Access**: Regular players or non-owner members will receive a `403 Forbidden` error if they try to access update/delete endpoints.

---

### **7.1 GET `/api/v1/clubs/{club_id}/members`** (List Members)
Retrieves a paginated list of users belonging to the club.
- **Authentication**: Required (`auth:sanctum`).
- **Query Parameters**:
  - `search` (string, optional): Search by user name, email, or `alsa7a_id` (fully supports reverse-math lookup).
  - `team_id` (string/integer, optional): Filter members by team. Pass `'none'` to fetch members not assigned to any team.
- **Response (200 OK)**:
```json
{
  "status": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 28,
        "alsa7a_id": 100280,
        "name": "Youssef Player",
        "email": "player@alsa7a.com",
        "position": "Forward",
        "number": 10,
        "team_id": 4,
        "team": {
          "id": 4,
          "name": "Under 18 Team"
        }
      }
    ],
    "total": 1
  },
  "message": "Club members retrieved successfully"
}
```

---

### **7.2 POST `/api/v1/clubs/{club_id}/members/{user_id}`** (Update Member/Transfer)
Updates the player's team association, position, or jersey number.
- **Authentication**: Required (`auth:sanctum` - Club Owner only).
- **Body Parameters (form-data/json)**:
  - `team_id` (integer/null, optional): The ID of the team to transfer the user to. Must belong to the same club. Pass `null` to remove from all teams.
  - `position` (string, optional): The player's position (e.g., `"Forward"`, `"Goalkeeper"`).
  - `number` (integer, optional): The player's jersey number.
- **Response (200 OK)**:
```json
{
  "status": true,
  "data": {
    "id": 28,
    "alsa7a_id": 100280,
    "name": "Youssef Player",
    "team_id": 5,
    "position": "Midfielder",
    "number": 8
  },
  "message": "Member updated successfully"
}
```

---

### **7.3 DELETE `/api/v1/clubs/{club_id}/members/{user_id}`** (Remove Member)
Completely removes the member from the club roster, setting their `club_id` and `team_id` to `null`.
- **Authentication**: Required (`auth:sanctum` - Club Owner only).
- **Response (200 OK)**:
```json
{
  "status": true,
  "message": "Member removed from club successfully"
}
```