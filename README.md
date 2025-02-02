# Event Management System

A robust PHP-based event management system with **OOP and MVC**  that allows organizations to create and manage events while enabling users to discover and register for events.

## Features

### For Users
- Browse and search events
- Register for free and paid events
- View event details and organizer information
- Profile management
- Event registration history
- Event registration deadline count in home page
- Payment for paid events


### For Organizers
- Create and manage events
- Track event registrations
- Manage event capacity
- Upload event images
- View registered users
- Export attendees data

### For Administrators
- User management (Create, Update, Delete)
- Organization management
- Event oversight
- Dashboard
- Profile 

## Technical Stack

- PHP 8.0+
- MySQL 5.7+
- Bootstrap 5
- Vanilla JavaScript
- PDO for database operations

## Directory Structure
event-management/
├── config/           # Configuration files
├── public/          # Public assets and entry point
├── src/             # Application source code
│   ├── Controllers/ # Application controllers
│   ├── Models/      # Database models
│   ├── Services/    # Business logic
│   ├── Views/       # Template files
│   └── Migrations/  # Database migrations
└── vendor/         # Composer dependencies
└── migration.php         # Migration Setup File
## Installation

## 1. Clone the repository:
```bash
git clone git@github.com:ziko1153/event-management.git
cd event-management
```
## 2. Install dependencies:
```bash
composer install
```
## 3. Configure environment:
```bash
cp .env.example .env
```
## 4. Update .env file with your configuration:
```bash
APP_NAME=EventManagement
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
## 5. Create database
```bash 
mysql -u root -p -e "CREATE DATABASE event_management"
```

## 6. Import database schema and seed data
```bash
php migration.php --migrate
```

## 7. SET Permission
```bash 
chmod -R 755 public/img
chmod 644 .env
```

## 8. Start Development Server 
```bash
php -S localhost:8000 -t public
```
## (Optional) For Share Hosting Using this in Public folder, create a file .htaccess
```bash
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Redirect all requests to index.php
RewriteRule ^(.*)$ index.php?/$1 [L,QSA]

```
## (optional)  Rollback all Data 
```bash
php migrate.php --rollback
```

## (optional) Run Specific Table for migration
```bash
#First create a schema table  under src/migration/database
#then just added file into migration.php 
$availableMigrations = [
    new CreateUserTable($connection),
];
#then RUN 
php migrate.php --file=CreateUserTable

```

## Test Credentials
### Admin Access
- Email: tahmidziko@test.com
- Password: 12345678
### Organizer Access
- Email: niharika@test.com
- Password: 12345678
### User Access
- Email: nur@test.com
- Password: 12345678


## Key Features
### Event Management
- Create and manage events with rich details
- Set event capacity and registration deadlines
- Multiple event types (Conference, Workshop, Seminar, etc.)
- Image upload and management
- Event status management (Draft, Published, Cancelled, Completed)


### User System
- Role-based access control (Admin, Organizer, User)
- Profile management with avatar upload
- Password reset functionality


### Event Registration System
- Easy event registration process
- Registration status tracking
- Capacity management
- Registration deadline enforcement
- Payment integration ready

### Search & Discovery
- Advanced event search
- Filter by date, type, and price
- Sort by various parameters
- Featured events showcase
- Type wise events listing

### Analytics & Reporting
- Event registration list
- Export Attendees report 

## Security Features
- Password hashing using bcrypt
- CSRF protection
- Input validation and sanitization
- Role-based access control
- Secure file upload handling
- Session management
- SQL injection prevention

## Future Development Plans
### Phase 1 (Q1 2025)
- Mobile responsive design optimization
- Email notification system implementation
- Payment gateway integration
- QR code for event check-in
- Social media sharing integration
- Category wise Event module

### Phase 2 (Q2 2025)
- Real-time chat between organizers and attendees
- Advanced analytics dashboard
- Multi-language support
- Event rating and review system

### Phase 3 (Q3 2025)
- Virtual event platform integration
- Automated email marketing tools
- API development for third-party integration
- Subscription-based pricing model
- Advanced reporting features

## Contributing
1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

