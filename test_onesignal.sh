#!/bin/bash

# Set your token here
TOKEN=$1

if [ -z "$TOKEN" ]; then
    echo "Usage: bash test_onesignal.sh [YOUR_TOKEN]"
    echo "Please provide your bearer token."
    exit 1
fi

echo ""
echo "--- Updating OneSignal Subscription ---"
echo "Token: $TOKEN"
echo ""

# Sending a sample subscription object (simulating what OneSignal SDK might return)
curl -X POST \
  https://saha.wasl-x.com/api/v1/users/onesignal-subscription \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "onesignal_subscription": {
        "id": "player-id-uuid-1234",
        "token": "push-token-abc-123",
        "device_type": 1,
        "notification_types": 1
    }
  }' \
  -k

echo ""
echo ""
echo "Check the response 'data' to see the stored subscription object."
