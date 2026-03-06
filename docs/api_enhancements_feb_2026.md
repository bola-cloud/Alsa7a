# Alsa7a API Enhancements - February 2026

This document summarizes the technical changes and new API features implemented in February 2026.

## 1. Algorithmic Feed System
A new intelligent feed API that prioritizes relevant content based on user relationships and previous interactions.

### Endpoints
- **GET `/api/v1/feed`**: Retrieves the prioritized feed.
    - **Logic**: 
        1. Posts from followed users (Friends).
        2. Suggested posts from users not followed.
        3. Fallback: Previously seen posts (only if no new content remains).
- **POST `/api/v1/feed/seen`**: Marks posts as "seen" to prevent them from reappearing in the primary feed.
    - **Payload**: `{"post_ids": [1, 2, 3]}`

### Database Changes
- Created `post_views` table to track which posts have been seen by which users.

---

## 2. Service Enhancements: Manual Address
Added support for a manually typed address field for services, independent of coordinates.

### Features
- **Field**: `address` (String, Optional).
- **Associated Models**: `Service`.
- **API Support**: Included in `GET` (index/show), `POST` (store), and `PUT` (update) for Services.

---

## 3. Video Thumbnail Support (Client-Side)
Implemented a lightweight mechanism for associating thumbnails with video posts.

### Features
- **Client-Side Processing**: The mobile app generates and uploads the thumbnail.
- **Fields**: `video_thumbnail` (String) added to `posts` and `community_posts` tables.
- **API Support**:
    - `POST /api/v1/posts` and `POST /api/v1/community/posts` accept a `video_thumbnail` (image file).

---

## 4. Media URL Auto-Resolution
Centralized media URL handling in the model layer to ensure the mobile app always receives full URLs.

### Implemented Accessors
The following models now automatically prepend the storage path and app URL to media fields:
- **`Post`**: `image`, `video_thumbnail`
- **`CommunityPost`**: `image`, `video_thumbnail`

*Example*: If the database stores `posts/image.jpg`, the API will return `https://saha.wasl-x.com/storage/posts/image.jpg`.

---

## 5. Subscription System (Thawani Integration)
A new subscription system allowing users to subscribe to Monthly or Annual plans via Thawani.

### Endpoints
- **GET `/api/v1/subscriptions/plans`**: Returns available plans and prices (public).
- **POST `/api/v1/subscriptions/checkout`**: Starts a Thawani checkout session for a specific plan (`monthly` or `annual`).
- **GET `/api/v1/subscriptions/status`**: Returns the current user's subscription status and expiration date.

---

## 6. Admin Subscription Management
Administrators can now manage subscription pricing directly from the control panel.

### Features
- Customizable **Monthly** and **Annual** prices in Omani Riyals (OMR).
- Located under **Admin Settings > Subscription**.
