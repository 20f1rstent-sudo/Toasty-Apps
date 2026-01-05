# ToastyApps Mobile Manager

A WordPress plugin that allows dispensary owners to manage their mobile app content directly from their WordPress admin dashboard.

## Description

ToastyApps Mobile Manager provides a complete backend solution for iOS apps, enabling dispensary owners to:

- Upload and manage hero images for the app home screen
- Create and organize media reels (videos with descriptions)
- Send push notifications to app users via Firebase Cloud Messaging
- Manage multiple store locations with maps and contact info
- Customize app branding (colors, tagline, logo)
- Secure REST API for the iOS app to fetch content

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Firebase project (for push notifications)

## Installation

1. Download the plugin files
2. Upload the `toastyapps-mobile-manager` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **ToastyApps** in the admin menu to configure

### Quick Start

1. Go to **ToastyApps > Settings** to view your API key
2. Configure Firebase Cloud Messaging for push notifications
3. Add your hero image at **ToastyApps > Hero Image**
4. Add your locations at **ToastyApps > Locations**
5. Customize branding at **ToastyApps > Branding**

## Features

### Dashboard
- Quick overview of active devices, media reels, locations
- API usage statistics
- Status checks for configuration
- Quick action buttons

### Hero Image
- Upload high-quality images for app home screen
- Preview how it will appear in the app
- Supports JPG, PNG, WebP formats

### Media Reels
- Upload videos with titles and descriptions
- Custom thumbnails
- Drag-and-drop reordering
- Duration tracking

### Push Notifications
- Send notifications to all registered devices
- Rich notifications with images
- Notification history with success rates
- Character limits for iOS compatibility

### Locations
- Multiple location support
- Address, phone, email, website, hours
- Google Maps embed URL support
- GPS coordinates for native maps
- Mark primary location

### Branding
- App name and tagline
- Custom logo upload
- Color scheme customization:
  - Primary color
  - Secondary color
  - Accent color
  - Text color
  - Background color
- Live preview

### Settings
- API key management
- Firebase Cloud Messaging configuration
- API logging and rate limiting
- File upload size limits

## REST API Endpoints

All endpoints require authentication via API key:

```
Authorization: Bearer YOUR_API_KEY
```

Or:

```
X-API-Key: YOUR_API_KEY
```

### Available Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/toastyapps/v1/config` | GET | Get app configuration (branding, locations, colors) |
| `/wp-json/toastyapps/v1/hero-image` | GET | Get hero image URL and metadata |
| `/wp-json/toastyapps/v1/media-reels` | GET | Get list of all media reels |
| `/wp-json/toastyapps/v1/media-reels/{id}` | GET | Get single media reel by ID |
| `/wp-json/toastyapps/v1/locations` | GET | Get all locations |
| `/wp-json/toastyapps/v1/branding` | GET | Get branding configuration |
| `/wp-json/toastyapps/v1/all-content` | GET | Get all content in one request |
| `/wp-json/toastyapps/v1/register-device` | POST | Register device for push notifications |
| `/wp-json/toastyapps/v1/unregister-device` | POST | Unregister device |
| `/wp-json/toastyapps/v1/heartbeat` | POST | Update device last active timestamp |

### Example: Get All Content

```bash
curl -X GET \
  'https://yoursite.com/wp-json/toastyapps/v1/all-content' \
  -H 'Authorization: Bearer YOUR_API_KEY'
```

Response:

```json
{
  "success": true,
  "data": {
    "config": {
      "app_name": "Your App",
      "tagline": "Your tagline",
      "primary_color": "#1a73e8",
      "secondary_color": "#34a853",
      "accent_color": "#ea4335",
      "text_color": "#202124",
      "background_color": "#ffffff",
      "hero_image_url": "https://yoursite.com/wp-content/uploads/hero.jpg",
      "locations": [...]
    },
    "hero_image": {
      "id": 123,
      "url": "https://yoursite.com/wp-content/uploads/hero.jpg",
      "width": 1200,
      "height": 600
    },
    "media_reels": [
      {
        "id": 1,
        "title": "Welcome Video",
        "description": "Introduction to our dispensary",
        "video_url": "https://yoursite.com/wp-content/uploads/welcome.mp4",
        "thumbnail_url": "https://yoursite.com/wp-content/uploads/thumb.jpg",
        "duration": "0:30"
      }
    ]
  },
  "meta": {
    "version": "1.0.0",
    "generated_at": "2024-01-15T12:00:00+00:00"
  }
}
```

### Example: Register Device

```bash
curl -X POST \
  'https://yoursite.com/wp-json/toastyapps/v1/register-device' \
  -H 'Authorization: Bearer YOUR_API_KEY' \
  -H 'Content-Type: application/json' \
  -d '{
    "device_token": "FCM_DEVICE_TOKEN",
    "device_type": "ios",
    "device_name": "iPhone 15 Pro",
    "app_version": "1.0.0",
    "os_version": "17.0"
  }'
```

## iOS Integration

### Swift Example

```swift
import Foundation

class ToastyAppsAPI {
    private let baseURL: URL
    private let apiKey: String

    init(baseURL: String, apiKey: String) {
        self.baseURL = URL(string: baseURL)!
        self.apiKey = apiKey
    }

    func fetchAllContent() async throws -> AppContent {
        var request = URLRequest(url: baseURL.appendingPathComponent("all-content"))
        request.setValue("Bearer \(apiKey)", forHTTPHeaderField: "Authorization")

        let (data, _) = try await URLSession.shared.data(for: request)
        let response = try JSONDecoder().decode(APIResponse<AppContent>.self, from: data)

        return response.data
    }

    func registerDevice(token: String) async throws {
        var request = URLRequest(url: baseURL.appendingPathComponent("register-device"))
        request.httpMethod = "POST"
        request.setValue("Bearer \(apiKey)", forHTTPHeaderField: "Authorization")
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")

        let body: [String: Any] = [
            "device_token": token,
            "device_type": "ios",
            "device_name": UIDevice.current.name,
            "app_version": Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "",
            "os_version": UIDevice.current.systemVersion
        ]

        request.httpBody = try JSONSerialization.data(withJSONObject: body)

        let (_, _) = try await URLSession.shared.data(for: request)
    }
}
```

## Firebase Cloud Messaging Setup

1. Create a Firebase project at [Firebase Console](https://console.firebase.google.com/)
2. Add your iOS app to the project
3. Download `GoogleService-Info.plist` and add to your Xcode project
4. Go to Project Settings > Cloud Messaging
5. Copy the **Server Key**
6. In WordPress, go to **ToastyApps > Settings > Push Notifications**
7. Paste the Server Key and enable FCM

## Database Tables

The plugin creates these custom tables:

### `{prefix}_toastyapps_devices`
Stores registered devices for push notifications.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| device_token | varchar(500) | FCM device token |
| device_type | varchar(20) | ios/android |
| device_name | varchar(255) | Device name |
| app_version | varchar(20) | App version |
| os_version | varchar(20) | OS version |
| is_active | tinyint | Active status |
| last_active | datetime | Last activity |
| created_at | datetime | Registration date |

### `{prefix}_toastyapps_notifications`
Stores notification history.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| title | varchar(255) | Notification title |
| body | text | Notification body |
| image_url | varchar(500) | Image URL |
| sent_count | int | Total sent |
| success_count | int | Successful deliveries |
| failure_count | int | Failed deliveries |
| status | varchar(20) | pending/sending/sent |
| sent_at | datetime | Send timestamp |

### `{prefix}_toastyapps_api_logs`
Stores API request logs for analytics.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| endpoint | varchar(100) | API endpoint |
| method | varchar(10) | HTTP method |
| device_token | varchar(500) | Device token |
| ip_address | varchar(45) | Client IP |
| user_agent | varchar(500) | User agent |
| response_code | int | HTTP response code |
| request_time | float | Request duration |
| created_at | datetime | Request timestamp |

## Hooks and Filters

### Actions

```php
// After device registration
do_action( 'toastyapps_device_registered', $device_id, $device_data );

// After notification sent
do_action( 'toastyapps_notification_sent', $notification_id, $result );
```

### Filters

```php
// Modify API response
add_filter( 'toastyapps_api_config_response', function( $data ) {
    // Modify $data
    return $data;
});

// Customize allowed file types
add_filter( 'toastyapps_allowed_file_types', function( $types ) {
    $types[] = 'svg';
    return $types;
});
```

## Security

- All API endpoints require authentication
- API keys are generated using WordPress cryptographic functions
- All inputs are sanitized and validated
- Nonce verification for admin forms
- Rate limiting support
- Prepared SQL statements for database queries

## Support

For issues and feature requests, please visit:
https://github.com/toastyapps/mobile-manager

## License

GPL-2.0+

## Changelog

### 1.0.0
- Initial release
- Hero image management
- Media reels with video support
- Push notifications via FCM
- Location management
- Branding customization
- REST API endpoints
- Admin dashboard with statistics
