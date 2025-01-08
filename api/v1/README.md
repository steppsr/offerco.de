
# Offer Code API

This API provides two endpoints for managing offer codes and associated shortcodes. Both endpoints use POST methods and expect data in form format with an API key for authentication.

## Table of Contents
- [Offer Code API](#offer-code-api)
  - [Table of Contents](#table-of-contents)
  - [Endpoints](#endpoints)
    - [GET Offer](#get-offer)
    - [GET Shortcode](#get-shortcode)
  - [Authentication](#authentication)
  - [Error Handling](#error-handling)
  - [Usage Examples](#usage-examples)
    - [Using `curl`](#using-curl)
    - [Using `Javascript`](#using-javascript)
    - [Response Structure](#response-structure)

## Endpoints

### GET Offer
- **URL**: `/api/v1/getoffer`
- **Method**: `POST`
- **Parameters**:
  - `api_key` (string, required): The API key for authentication.
  - `short_code` (string, required): The short code to look up the offer.

**Description**: This endpoint fetches the offer code, description, and a unique offer ID based on the provided short code from the database.

### GET Shortcode
- **URL**: `/api/v1/getshortcode`
- **Method**: `POST`
- **Parameters**:
  - `api_key` (string, required): The API key for authentication.
  - `offer_code` (string, required): The offer code you want to associate with a new or existing short code.
  - `description` (string, optional): A description for the offer. If provided, it will be stored with the offer code.

**Description**: This endpoint generates or retrieves a short code for a given offer code. If no short code exists for the offer code, a new unique short code is generated, inserted into the database, and returned.

## Authentication
- **API Key**: Each request must include an `api_key` parameter. 
  - API keys are associated with Twitter usernames (used as `user_id` in our system).
  - Keys can be given upon request via DM to @steppsr on X.
- **Security**: API keys should be kept confidential. Do not share them in public repositories or over insecure channels.

## Error Handling
- Both endpoints return a JSON response with a `status` field indicating either "success" or "error".
- When an error occurs, a `message` field in the JSON response provides details on the error.
- Possible errors include:
  - API key not provided
  - Invalid or inactive API key
  - API key has expired
  - Shortcode or offer code not provided

## Usage Examples

### Using `curl`

```bash
# For GET Offer
curl -X POST -d "api_key=YOUR_API_KEY&short_code=9K1xX" "https://offerco.de/api/v1/getoffer"

# For GET Shortcode (standard form method)
curl -X POST -d "api_key=YOUR_API_KEY&offer_code=OFFER-ABC&description=This%20is%20a%20test%20offer" "https://offerco.de/api/v1/getshortcode"

# For GET Shortcode (using form-data to avoid URL encoding)
curl -X POST -F "api_key=YOUR_API_KEY" -F "offer_code=OFFER-ABC" -F "description=This is a test offer" "https://offerco.de/api/v1/getshortcode"
```

### Using `Javascript`

```javascript
// For GET Offer
fetch('https://offerco.de/api/v1/getoffer', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
  body: 'api_key=YOUR_API_KEY&short_code=XXXXX'
})
.then(response => response.json())
.then(data => console.log(data));

// For GET Shortcode (standard form method)
fetch('https://offerco.de/api/v1/getshortcode', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
  body: 'api_key=YOUR_API_KEY&offer_code=OFFER_CODE&description=This%20is%20a%20test%20offer'
})
.then(response => response.json())
.then(data => console.log(data));

// For GET Shortcode (using FormData to avoid manual URL encoding)
const formData = new FormData();
formData.append('api_key', 'YOUR_API_KEY');
formData.append('offer_code', 'OFFER_CODE');
formData.append('description', 'This is a test offer');

fetch('https://offerco.de/api/v1/getshortcode', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### Response Structure
```json
{
  "status": "success",
  "data": {
    "short_code": "string",
    "offer_code": "string",
    "description": "string",
    "offer_id": "string"
  }
}
```

```json
{
  "status": "error",
  "data": {
    "short_code": "string",
    "offer_code": "string",
    "description": "string",
    "offer_id": "string"
  },
  "message": "string"
}
```