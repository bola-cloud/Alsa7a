# API & System Enhancements - March 2026

This document outlines the major modifications made to the Alsa7a API in March 2026, focusing on the Subscription System and the standardization of User/Club profile data.

## 1. Subscription System (Thawani Integration)

A comprehensive subscription system has been implemented, allowing users to subscribe to Monthly or Annual plans via the Thawani payment gateway.

### Features
- **Subscription Plans**: Monthly (OMR 5) and Annual (OMR 50) - *configurable via Admin Settings*.
- **Integrated Checkout**: Secure payment flow using Thawani.
- **Auto-Activation**: Subscriptions are automatically activated upon successful payment callback.
- **Model Integration**: The `User` model now has `isSubscribed()` and `subscription()` relationships.

### Endpoints
- `GET /api/v1/subscriptions/plans`: List available pricing plans.
- `POST /api/v1/subscriptions/checkout`: Initiate a subscription payment.
- `GET /api/v1/subscriptions/status`: Check current subscription details.

---

## 2. Standardized Profile Data Structure

To ensure consistency across the entire mobile application, a centralized formatting system has been introduced using the `FormatsProfileData` trait.

### Standardized Keys
Depending on the context, the following keys are added to responses to provide the full profile structure:
- `profile`: General user profile.
- `club_profile`: Detailed club owner profile (when applicable).
- `requester_profile`: Profile of a service or event requester.
- `provider_profile`: Profile of a service provider.

### Full Profile Payload Includes:
- Basic Info: `id`, `name`, `username`, `email`, `phone`, `birth_date`.
- Media: `profile_photo_url`, `cover_photo_url`.
- Professional: `team_id`, `position`, `number`, `nationality`, `stats`.
- Social: `is_following`, `followers_count`, `following_count`.
- **Identity**: `is_club_account`, `role_in_club`.
- **Subscription**: `is_subscribed`, `type`, `end_date`, `status`.
- **Club Details**: Full roster and team information if the account is a Club.

---

## 3. Synchronized API Endpoints

The following "Production-Critical" endpoints have been updated to return the standardized profile data while maintaining **strict backward compatibility**.

### Events & Bookings
1.  `GET /api/v1/events`: Returns `club_profile`.
2.  `GET /api/v1/events/{id}`: Returns `club_profile`.
3.  `POST /api/v1/events/{id}/book`: Returns `event.club_profile` and `user_profile`.
4.  `GET /api/v1/my-bookings`: Returns `event.club_profile` and `user_profile`.

### Service Requests
5.  `POST /api/v1/services/{id}/request`: Returns `provider_profile` and `requester_profile`.
6.  `GET /api/v1/my-requests`: Returns `provider_profile` and `requester_profile`.
7.  `GET /api/v1/provider/requests`: Returns `requester_profile` and `provider_profile`.

### Discovery
8.  `GET /api/v1/search`: Returns full `profile` object for each user result.

---

## 4. Production Safety & Reliability

No existing fields were removed during these enhancements. The following measures ensure production stability:
- **Legacy Field Preservation**: Fields like `image`, `featured_image`, and original `user` objects are kept for older app versions.
- **Feed Pagination Fix**: Resolved an issue where `/api/v1/feed` would return an object instead of an array on page 2.
- **Performance**: All profile data is eager-loaded to prevent N+1 database queries.

---

## 6. Admin Control over Verification

Administrators now have full control over the verification process for each user category through the Admin Panel.

### Features:
- **Toggle Verification**: Enable or disable the requirement for users to upload documents per category.
- **Dynamic Requirements**: Specify the exact text (in English and Arabic) that users will see when asked to upload documents for a specific category.
- **Improved UI**: A new "Requires Verification" checkbox in the Category Edit/Create screens with auto-expanding requirement fields.

### How to use:
1. Navigate to **Categories** in the Admin Sidebar.
2. Edit an existing category (e.g., "Coach").
3. Check the **Requires Verification** box.
4. Enter the required documents in both the **English** and **Arabic** textareas.
5. Click **Update**.

As part of the account approval process, users must upload specific documentation according to their chosen category. The following files are required for verification:

### | Category | Required Documents |
| :--- | :--- |
| **Player / Fan / Parent** | Copy of National ID or Passport. |
| **Coach** | Coaching Licenses/Certificates + Copy of National ID. |
| **Club / Academy** | Commercial Register (CR) or Official Licensing Document. |
| **Photographer / Videographer** | Portfolio/Work Samples + Copy of National ID. |
| **Physiotherapist** | Medical Practice License / Professional Certification + Copy of National ID. |
| **Agent / Scout** | Official Agent License / Sports Federation Accreditation + Copy of National ID. |

> [!NOTE]
> All files must be in **JPG, PNG, or PDF** format with a maximum size of **5MB** each. Verification status can be tracked via the `GET /api/v1/verification/status` endpoint.
