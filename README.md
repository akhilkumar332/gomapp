# Delivery Management System

A Laravel-based delivery management system with Firebase authentication integration.

## Features

- Firebase Phone Authentication for Drivers
- Admin Dashboard with Analytics
- Zone-based Delivery Management
- Real-time Location Tracking
- Payment Collection Management
- Activity Logging
- Performance Reports

## API Endpoints

### Authentication

#### Admin Login
```
POST /api/admin/auth/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}
```

#### Driver Phone Verification
```
POST /api/auth/verify-phone
Content-Type: application/json

{
    "firebase_token": "firebase_id_token",
    "device_token": "fcm_device_token"
}
```

### Admin API Endpoints

#### Dashboard Analytics
```
GET /api/admin/dashboard
Authorization: Bearer your_access_token
```

#### Driver Management
```
GET    /api/admin/drivers
POST   /api/admin/drivers
PUT    /api/admin/drivers/{id}
DELETE /api/admin/drivers/{id}
```

#### Zone Management
```
GET    /api/admin/zones
POST   /api/admin/zones
PUT    /api/admin/zones/{id}
DELETE /api/admin/zones/{id}
GET    /api/admin/zones/{zone}/statistics
```

#### Location Management
```
GET    /api/admin/locations
POST   /api/admin/locations
PUT    /api/admin/locations/{id}
DELETE /api/admin/locations/{id}
```

#### Settings
```
GET  /api/admin/settings
POST /api/admin/settings
POST /api/admin/settings/branding
```

#### Reports & Analytics
```
GET /api/admin/reports/activity
GET /api/admin/reports/performance
GET /api/admin/reports/export
```

### Driver API Endpoints

#### Zone Access
```
GET /api/zones
GET /api/zones/{zone}
GET /api/zones/{zone}/locations
```

#### Location Management
```
GET  /api/locations
GET  /api/locations/{location}
POST /api/locations/{location}/status
POST /api/locations/{location}/position
```

## Installation

1. Clone the repository
```bash
git clone <repository-url>
```

2. Install dependencies
```bash
composer install
npm install
```

3. Copy environment file
```bash
cp .env.example .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Configure Firebase
- Add your Firebase service account credentials to `storage/app/firebase/service-account.json`
- Update Firebase configuration in `.env` file

6. Run migrations
```bash
php artisan migrate
```

7. Seed the database
```bash
php artisan db:seed
```

8. Start the development server
```bash
php artisan serve
```

## Environment Variables

```env
# Firebase Configuration
FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
FIREBASE_DATABASE_URL=
FIREBASE_PROJECT_ID=
FIREBASE_STORAGE_BUCKET=
FIREBASE_API_KEY=
FIREBASE_AUTH_DOMAIN=
FIREBASE_MESSAGING_SENDER_ID=
FIREBASE_APP_ID=
FIREBASE_MEASUREMENT_ID=
```

## License

This project is licensed under the MIT License.

## Additional Information

### Composer Dependencies
- Laravel Framework
- Other dependencies as specified in `composer.json`

### Controller Summaries
- **SettingController**: Manages application settings and configurations.
- **ProfileController**: Handles user profile-related actions.
- **AppSettingController**: Manages application settings and their retrieval.
