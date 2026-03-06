# API: Advanced Verification Flow

With the new update, verification requirements are dynamic per user category.

## 1. Fetching Requirements
When a user selects a category (or from the `profile` endpoint), the `category` object now contains:
- `requires_verification`: Boolean
- `verification_requirements_en`: General description (English)
- `verification_requirements_ar`: General description (Arabic)
- `verification_fields`: JSON array of specific requirements.

### Example `verification_fields`:
```json
[
  {
    "id": "id_card",
    "type": "file",
    "label_en": "ID Card Front",
    "label_ar": "وجه البطاقة الشخصية"
  },
  {
    "id": "license_no",
    "type": "text",
    "label_en": "License Number",
    "label_ar": "رقم الترخيص"
  }
]
```

## 2. Submitting Verification
**Endpoint:** `POST /api/v1/verification/upload`
**Auth:** Bearer Token required.

The request must use `multipart/form-data` if files are involved.

### Request Data:
The keys in the POST request must match the `id` defined in the category's `verification_fields`.

**Example Body:**
- `id_card`: [File Binary]
- `license_no`: "ABC-123-XYZ"

### Response:
- **200 OK**: `{ "message": "Verification submitted successfully", "status": "pending" }`
- **422 Unprocessable Entity**: If required fields are missing or file types are invalid.

---

## Technical Note for Developers:
- If `verification_fields` is null/empty, the app should fall back to a generic file upload named `documents[]`.
- Always check `requires_verification` before showing the verification UI.
