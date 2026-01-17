#!/bin/bash

# Configuration
# User provided production domain
DOMAIN="https://saha.wasl-x.com" 
BASE_URL="$DOMAIN/api/v1"
TOKEN="59|ZUmSiZ3TOwuR2qbsBRckJna7pqzhI87SpLgpqFcvf9a75369"

echo "Testing Club APIs against: $DOMAIN"
echo "-----------------------------------"

# 1. List Clubs
echo "1. GET /clubs (List all clubs)"
curl -s -X GET "$BASE_URL/clubs" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

echo -e "\n\n-----------------------------------\n"

# 2. Get Club Details (ID 1)
echo "2. GET /clubs/1 (Get details for Club ID 1)"
curl -s -X GET "$BASE_URL/clubs/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

echo -e "\n\n-----------------------------------"
