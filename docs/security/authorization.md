# Authorization

This API generates the tokens for authenticating your API calls. This is the first API you will engage with within the set of APIs available because all the other APIs require authentication information from this API to work.

## Overview

The authorization endpoint is used to obtain an access token that is required for all subsequent API calls to the M-Pesa Daraja API. The token expires after 3600 seconds (1 hour) and must be refreshed when expired.

**Good News**: The Laravel M-Pesa package handles authorization automatically! You don't need to manually manage tokens.

## How Authorization Works in This Package

### Automatic Token Management

The package automatically handles the entire authorization process:

1. **Token Generation**: When you instantiate the `Mpesa` class, it automatically generates an access token
2. **Token Caching**: The token is stored with its expiry time
3. **Token Refresh**: When making API calls, the package checks if the token is expired and automatically refreshes it
4. **Request Authentication**: All API requests are automatically authenticated with the current valid token

### Implementation Details

```php
// The package automatically handles authorization
$mpesa = new Mpesa();

// Behind the scenes, the package:
// 1. Reads your credentials from config
// 2. Generates an access token
// 3. Caches the token with expiry time
// 4. Uses the token for all subsequent requests
```

## Configuration

### Required Configuration

Make sure your `config/mpesa.php` file contains the necessary credentials:

```php
return [
    'consumerKey' => env('MPESA_CONSUMER_KEY'),
    'consumerSecret' => env('MPESA_CONSUMER_SECRET'),
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    // ... other configuration
];
```

### Environment Variables

Add these to your `.env` file:

```env
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
```

## API Endpoint (Internal)

The package uses this endpoint internally:

```
GET /oauth/v1/generate?grant_type=client_credentials
```

## Authentication Process

### 1. Basic Authentication

The package uses Basic Authentication with your Consumer Key as the username and Consumer Secret as the password.

### 2. Token Generation

```php
// This happens automatically in the package
$response = Http::withHeaders([
    'Authorization' => 'Basic ' . base64_encode($consumerKey . ':' . $consumerSecret),
])->get($baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
```

### 3. Token Storage

The package stores the token and its expiry time:

```php
$this->expiresIn = date('Y-m-d H:i:s', (time() + $response->expires_in));
$this->accessToken = $response->access_token;
```

### 4. Automatic Token Refresh

Before each API request, the package checks if the token is expired:

```php
if (isset($this->accessToken) && strtotime($this->expiresIn) > time()) {
    $accessToken = $this->accessToken;
} else {
    $accessToken = $this->getAccessToken(); // Automatically refresh
}
```

## Response Format

### Success Response

**Status Code:** `200 OK`

```json
{
   "access_token": "c9SQxWWhmdVRlyh0zh8gZDTkubVF",
   "expires_in": "3599"
}
```

### Response Parameters

| Name | Description | Type | Sample Values |
|------|-------------|------|---------------|
| `access_token` | Access token to access other APIs | String | `c9SQxWWhmdVRlyh0zh8gZDTkubVF` |
| `expires_in` | Token expiry time in seconds | Integer | `3599` |

## Testing

### Manual Testing

The test is automated so that you can easily generate access tokens via the simulator section of this API. Just select the app you are testing, and the keys will auto-populate and then simulate the request. You will have your token that expires in 3600 seconds.

You can also get the Postman collection then access your Consumer Key and Consumer Secret under the Keys tab from My apps on Daraja, and paste them on username and password respectively.

### Testing with the Package

```php
// Simply instantiate the class - authorization is automatic
$mpesa = new Mpesa();

// Make API calls - tokens are handled automatically
$response = $mpesa->expressPayment(100, '254700000000');
```

## Error Responses

### Invalid Credentials

**Status Code:** `401 Unauthorized`

```json
{
   "error": "invalid_client",
   "error_description": "Invalid client credentials"
}
```

### Missing Grant Type

**Status Code:** `400 Bad Request`

```json
{
   "error": "invalid_request",
   "error_description": "Missing grant_type parameter"
}
```

## Security Best Practices

1. **Never expose credentials**: Keep your Consumer Key and Consumer Secret secure and never commit them to version control.

2. **Use environment variables**: Store sensitive credentials in environment variables as shown above.

3. **Automatic token management**: The package handles token refresh automatically, so you don't need to worry about token expiration.

4. **HTTPS only**: Always use HTTPS in production to secure your API communications.

5. **Regular credential rotation**: Periodically rotate your Consumer Key and Consumer Secret for enhanced security.

6. **Environment separation**: Use different credentials for sandbox and production environments.

## Troubleshooting

### Common Issues

1. **401 Unauthorized**: Check that your Consumer Key and Consumer Secret are correct in your configuration.

2. **Configuration not found**: Ensure your `config/mpesa.php` file exists and contains the required credentials.

3. **Environment variables not loaded**: Make sure your `.env` file contains the M-Pesa credentials.

4. **Network issues**: Ensure your application can reach the M-Pesa API endpoints.

### Debugging

If you need to debug authorization issues, you can check your configuration:

```php
// Check if configuration is loaded correctly
dd(config('mpesa.consumerKey')); // Should not be null
dd(config('mpesa.consumerSecret')); // Should not be null
dd(config('mpesa.baseUrl')); // Should be a valid URL
```

### Testing Environments

For testing purposes, you can use the sandbox environment:

- **Sandbox URL**: `https://sandbox.safaricom.co.ke`
- **Production URL**: `https://api.safaricom.co.ke`

## Package Features

### What the Package Handles Automatically

✅ **Token Generation**: Automatically generates access tokens  
✅ **Token Caching**: Stores tokens with expiry times  
✅ **Token Refresh**: Automatically refreshes expired tokens  
✅ **Request Authentication**: Adds Bearer tokens to all API requests  
✅ **Error Handling**: Handles authorization errors gracefully  
✅ **Configuration Management**: Reads credentials from Laravel config  

### What You Don't Need to Worry About

❌ Manual token generation  
❌ Token expiry checking  
❌ Token refresh logic  
❌ Adding Authorization headers  
❌ Managing token storage  

## Related Documentation

- [Getting Started](../introduction/getting-started.md)
- [Configuration](../introduction/configuration.md)
- [Error Handling](../introduction/error-handling.md)
- [M-Pesa Express](../payments/mpesa-express.md)
- [C2B Payments](../payments/c2b.md)
