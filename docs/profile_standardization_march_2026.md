# API Response Changes Documentation (v1)

This document details the changes made to the user-related objects across 7 endpoints. All changes are **additive**, meaning existing fields were preserved while new standardized profile and club data fields were added.

## 1. Summary of Changes

The following objects have been updated to use the unified `FormatsProfileData` structure:
- **Services**: `provider` (List/Show), `reviews[].user` (Show)
- **Chat**: `other_user`, `user_one`, `user_two` (Conversations List), `sender` (Messages List/Store)
- **Community/Posts**: `user` (Posts List), `comments[].user` (Comments List)

---

## 2. Object Structural Diff

### Previous Structure (Simplified)
Older versions returned a minimal user object:
```json
{
  "id": 1,
  "name": "User Name",
  "profile_photo_path": "path/to/photo.jpg",
  "profile_photo_url": "http://example.com/storage/path/to/photo.jpg"
}
```

### New Standardized Structure
The objects now include the full profile context:
```json
{
  "id": 1,
  "name": "User Name",
  "email": "user@example.com",
  "image": "http://example.com/storage/photo.jpg",
  "profile_photo_url": "http://example.com/storage/photo.jpg",
  "is_club_account": true,
  "club_details": {
    "id": 5,
    "name": "Alsa7a Club",
    "logo": "http://example.com/storage/logo.png",
    "banner": "http://example.com/storage/banner.jpg"
  },
  "professional": {
    "club": { "id": 5, "name": "Alsa7a Club", "logo": "..." },
    "position": "Forward",
    "stats": { ... }
  },
  "category": {
    "id": 1,
    "name": "Player",
    "parent_category": { ... }
  },
  "stats": { "followers": 10, "following": 5, "posts": 20 },
  "subscription": { "is_subscribed": true, "type": "annual" }
}
```

---

## 3. Specific Endpoint Diffs

### A. Services (`/api/v1/services`)
- **Modified Key**: `data.data[].provider`
- **New Fields**: `is_club_account`, `club_details`, `professional`, `subscription`.
- **Purpose**: Allows identifying if the service provider is a club and displaying club logos/details in the services list and details page.

### B. Chat (`/api/v1/chat/conversations`)
- **Modified Keys**: `data[].other_user`, `data[].user_one`, `data[].user_two`
- **New Fields**: Full profile structure as shown above.
- **Purpose**: Standardizes the chat participant list, showing club status and professional roles.

### C. Chat Messages (`/api/v1/chat/conversations/{id}/messages`)
- **Modified Key**: `data.data[].sender`
- **New Fields**: Full profile structure.
- **Purpose**: Displays club/player details directly next to messages.

### D. Community Posts (`/api/v1/community/posts`)
- **Modified Key**: `data.data[].user` (Author)
- **New Fields**: Full profile structure.
- **Purpose**: Enhances the feed by showing if the author is a club and their professional status.

### E. Comments (`/api/v1/posts/{id}/comments` & `/api/v1/community/posts/{id}/comments`)
- **Modified Key**: `data.data[].user` (Commenter)
- **New Fields**: Full profile structure.
- **Purpose**: Standardizes commenter identities across profile posts and community blogs.

---
## 4. Key Fields for Mobile Logic
- `is_club_account`: Use this boolean to toggle between "Player" and "Club" UI variants.
- `club_details`: Use this object when `is_club_account` is true to show club-specific metadata.
- `professional.club`: Provided for legacy compatibility where the club is nested under professional details.
