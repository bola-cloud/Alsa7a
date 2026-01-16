#!/bin/bash

# Set your token here
TOKEN=$1

if [ -z "$TOKEN" ]; then
    echo "Usage: bash test_profile_image.sh [YOUR_TOKEN]"
    echo "Please provide your bearer token."
    exit 1
fi

# Create a dummy image if not exists
if [ ! -f dummy.jpg ]; then
    echo "Creating dummy image..."
    # Create a simple valid JPEG header to avoid validation errors if strictly checked
    # Or just use a random file, but sometimes naive random bytes fail strict image validation.
    # For now, we'll try random bytes as 'fsutil' did, but usually 'convert' or fetching an image is safer.
    # Creating a 1KB file with random data:
    dd if=/dev/urandom of=dummy.jpg bs=1024 count=1 >/dev/null 2>&1
fi

echo ""
echo "--- Uploading Profile Image ---"
echo "Token: $TOKEN"
echo ""

curl -X POST \
  https://saha.wasl-x.com/api/v1/users/profile \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "image=@dummy.jpg;type=image/jpeg" \
  -k

echo ""
echo ""
echo "Check the 'data.image' field in the response above. It should be a full URL now."
