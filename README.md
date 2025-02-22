# Delivery Management System

A comprehensive delivery management system built with Laravel for managing zones, locations, drivers, payments, and analytics.

## Features

### Admin Panel
- Dashboard with real-time statistics and analytics
- Zone management with location assignments
- Driver management and zone assignments
- Payment tracking and reporting
- Activity and login logs
- System usage reports and analytics
- Application settings and branding customization

### Driver Portal
- Real-time dashboard with delivery statistics
- Zone and location view
- Payment collection tracking
- Activity history
- Online/offline status management

## Requirements

- PHP >= 8.1
- Laravel 10.x
- MySQL 8.0 or higher
- Composer
- Node.js & NPM

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/delivery-management.git
cd delivery-management
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install and compile frontend assets:
```bash
npm install
npm run dev
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delivery_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run database migrations and seeders:
```bash
php artisan migrate --seed
```

## API Documentation & Testing

### Authentication

#### Admin Login
```http
POST /api/auth/admin/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}

Response:
{
    "token": "your_access_token",
    "user": {
        "id": 1,
        "name": "Admin User",
        "role": "admin"
    }
}
```

### Admin API Endpoints

#### Dashboard Analytics
```http
GET /api/admin/dashboard
Authorization: Bearer your_access_token

Response:
{
    "data": {
        "total_zones": 10,
        "active_drivers": 25,
        "total_locations": 150,
        "today_collections": 5000.00,
        "recent_activities": [...],
        "performance_metrics": {...}
    }
}
```

#### Driver Management
```http
# List Drivers
GET /api/admin/drivers
Response: {
    "data": [{
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+233...",
        "status": "active",
        "assigned_zones": [...]
    }]
}

# Create Driver
POST /api/admin/drivers
{
    "name": "New Driver",
    "email": "driver@example.com",
    "phone": "+233...",
    "password": "password",
    "zone_ids": [1, 2]
}

# Update Driver
PUT /api/admin/drivers/{id}
{
    "name": "Updated Name",
    "status": "inactive",
    "zone_ids": [3, 4]
}

# Delete Driver
DELETE /api/admin/drivers/{id}
```

#### Zone Management
```http
# List Zones
GET /api/admin/zones
Response: {
    "data": [{
        "id": 1,
        "name": "Zone A",
        "status": "active",
        "locations_count": 15,
        "drivers_count": 5
    }]
}

# Create Zone
POST /api/admin/zones
{
    "name": "New Zone",
    "description": "Zone description",
    "status": "active"
}

# Update Zone
PUT /api/admin/zones/{id}
{
    "name": "Updated Zone",
    "status": "inactive"
}

# Delete Zone
DELETE /api/admin/zones/{id}
```

#### Location Management
```http
# List Locations
GET /api/admin/locations
Response: {
    "data": [{
        "id": 1,
        "zone_id": 1,
        "shop_name": "Shop Name",
        "address": "Location Address",
        "coordinates": {
            "latitude": 5.6037,
            "longitude": -0.1870
        }
    }]
}

# Create Location
POST /api/admin/locations
{
    "zone_id": 1,
    "shop_name": "New Shop",
    "address": "Shop Address",
    "ghana_post_gps_code": "GA-123-456",
    "latitude": 5.6037,
    "longitude": -0.1870
}

# Update Location
PUT /api/admin/locations/{id}
{
    "shop_name": "Updated Shop",
    "status": "inactive"
}

# Delete Location
DELETE /api/admin/locations/{id}
```

#### System Settings
```http
# Get Settings
GET /api/admin/settings
Response: {
    "data": {
        "app_name": "Delivery Management",
        "theme": {
            "primary_color": "#007bff",
            "secondary_color": "#6c757d"
        },
        "notification_settings": {...}
    }
}

# Update Settings
POST /api/admin/settings
{
    "app_name": "Updated Name",
    "theme": {
        "primary_color": "#0056b3"
    }
}

# Update Branding
POST /api/admin/settings/branding
Content-Type: multipart/form-data
{
    "logo": [file],
    "favicon": [file]
}
```

#### Reports & Analytics
```http
# Activity Report
GET /api/admin/reports/activity
Query: ?start_date=2023-01-01&end_date=2023-01-31
Response: {
    "data": {
        "total_activities": 500,
        "activities_by_type": {...},
        "activities_by_user": [...]
    }
}

# Performance Report
GET /api/admin/reports/performance
Query: ?zone_id=1&period=monthly
Response: {
    "data": {
        "delivery_success_rate": 95,
        "average_delivery_time": "45 minutes",
        "collections_summary": {...}
    }
}

# Export Reports
GET /api/admin/reports/export
Query: ?type=activity&format=csv
Response: [Binary file download]
```

### Driver API Endpoints

#### Zone Access
```http
GET /api/driver/zones
Authorization: Bearer your_access_token

Response:
{
    "data": [{
        "id": 1,
        "name": "Zone A",
        "locations": [...]
    }]
}
```

#### Location Management
```http
GET /api/driver/locations/{zone_id}
Authorization: Bearer your_access_token

Response:
{
    "data": [{
        "id": 1,
        "shop_name": "Shop Name",
        "address": "Address",
        "coordinates": {
            "latitude": 5.6037,
            "longitude": -0.1870
        }
    }]
}
```

### Testing with Postman

1. Import the Postman collection:
```bash
delivery-management/tests/postman/DeliveryManagement.postman_collection.json
```

2. Set up environment variables in Postman:
- `base_url`: Your API base URL (e.g., http://localhost:8000/api)
- `admin_token`: Admin user access token
- `driver_token`: Driver user access token

3. Run the collection:
- The collection includes tests for all API endpoints
- Environment-specific tests are organized in folders
- Pre-request scripts handle authentication automatically

### Running API Tests

```bash
# Run all feature tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/ZoneTest.php

# Run with coverage report
php artisan test --coverage
```

### API Response Codes

- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Server Error

### Rate Limiting

API requests are limited to:
- Authenticated users: 60 requests per minute
- Guest users: 30 requests per minute

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
