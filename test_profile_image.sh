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
    echo "Downloading valid dummy image..."
    # Download a real valid JPEG (small placeholder)
    # Using -L to follow redirects (placehold.co often redirects)
    curl -L "https://placehold.co/200.jpg" -o dummy.jpg
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
