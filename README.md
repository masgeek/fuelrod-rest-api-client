# Fuelrod REST API Client

PHP client library for the [Fuelrod](https://www.fuelrod.co.ke) messaging API.

## Requirements

- PHP 8.2 or higher
- ext-curl, ext-json, ext-mbstring

## Installation

```bash
composer require masgeek/fuelrod-rest-api-client
```

## Usage

### Credential-based authentication

```php
use Fuelrod\Fuelrod;

$client = new Fuelrod(
    username: 'your-username',
    password: 'your-password',
    baseUrl:  'https://api.fuelrod.co.ke',
);
```

### API key authentication

Pass an API key as the fourth argument. When an API key is provided the `user`
and `password` fields are omitted from every request payload.

```php
$client = new Fuelrod(
    username: 'your-username',
    password: 'your-password',
    baseUrl:  'https://api.fuelrod.co.ke',
    apiKey:   'your-api-key',
);
```

### Sending a single SMS

```php
$response = $client->singleSms([
    'to'      => '0712345678',   // or E.164: '+254712345678'
    'message' => 'Hello from Fuelrod!',
]);

// $response['status'] === 'success' | 'error'
// $response['data']   === decoded response body
```

### Sending a plain SMS

```php
$response = $client->plainSms([
    'to'      => '+254712345678',
    'message' => 'Hello from Fuelrod!',
]);
```

### Sending a premium SMS

```php
$response = $client->premiumSms([
    'to'      => '+254712345678',
    'message' => 'Hello from Fuelrod!',
]);
```

### Response shape

Every method returns an associative array:

| Key      | Type             | Description                                              |
|----------|------------------|----------------------------------------------------------|
| `status` | `string`         | `"success"` on 2xx, `"error"` on a 4xx client error     |
| `data`   | `object\|string` | Decoded JSON body, or raw string if the body is not JSON |

Server-level errors (5xx, network timeouts) throw `GuzzleHttp\Exception\GuzzleException`.

### Validation errors

`FuelrodException` (code 422) is thrown when:

- `to` is missing, empty, or not a string
- `message` is missing or empty
- The phone number does not match `+?[0-9]{7,15}` (E.164 or local format)

## Phone number format

| Format | Example         |
|--------|-----------------|
| Local  | `0712345678`    |
| E.164  | `+254712345678` |

## Running tests

```bash
composer test
```

## License

MIT
