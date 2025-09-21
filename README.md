# MyPOS Vehicle Marketplace

A comprehensive vehicle marketplace application built with Symfony 7.3, featuring role-based authentication, vehicle management, and advanced filtering capabilities.

## Features

### Authentication & Authorization
- **User Registration & Login** - Secure authentication with Symfony Security
- **Role-based Access Control** - Separate interfaces for Merchants and Buyers
- **Password Reset** - Email-based password recovery system
- **Session Management** - Remember me functionality

### Vehicle Management
- **Multiple Vehicle Types** - Motorcycle, Car, Truck, and Trailer support
- **Inheritance-based Design** - Clean entity structure with type-specific attributes
- **CRUD Operations** - Full create, read, update, delete functionality for merchants
- **Advanced Filtering** - Filter by type, brand, model, colour, and price range
- **Pagination** - Efficient handling of large vehicle listings

### User Features
- **Merchant Dashboard** - Manage vehicle inventory and view follower statistics
- **Buyer Experience** - Browse vehicles, follow/unfollow, and maintain watchlists
- **Responsive Design** - Bootstrap 5 for modern, mobile-friendly interface

### Technical Features
- **Clean Architecture** - Thin controllers, service layer, repository pattern
- **DTOs** - Data Transfer Objects for type-safe data handling
- **Validation** - Both frontend and backend validation with Symfony Validator
- **Comprehensive Testing** - 26 tests covering unit, integration, and application layers
- **Doctrine ORM** - Advanced database operations with inheritance support
- **Email System** - SMTP integration with Gmail support
- **Custom Error Pages** - Branded 404 and error handling
- **Autocomplete Features** - Dynamic brand/model suggestions for vehicle forms
- **CSRF Protection** - Secure form submissions
- **Custom Logging** - Application-level logging system

## Installation

### Option 1: Docker Setup (Recommended)

**Prerequisites**: Docker and Docker Compose installed on your system

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd mypos-symfony-app
   ```

2. **Start the application with Docker:**
   ```bash
   docker-compose up --build -d
   ```

3. **Access the application:**
   - **Application**: http://localhost:8000

**That's it!** The Docker setup will automatically:
- Build the PHP 8.3 + Apache container
- Set up SQLite database
- Run database migrations
- Seed the database with test data
- Start the web server

**Note**: The Docker setup uses `MAILER_DSN="null://null"` (email disabled) for development. To enable email functionality, you can override the environment variable in `docker-compose.yml`.


### Option 2: Manual Setup

1. **Clone or navigate to the project directory:**
   ```bash
   cd mypos-symfony-app
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Environment configuration:**
   Create a `.env.local` file with your email configuration:
   ```bash
   # For Gmail SMTP
   MAILER_DSN="gmail://your-email@gmail.com:your-app-password@smtp.gmail.com:587"
   FROM_EMAIL="your-email@gmail.com"
   
   # For development (email disabled)
   MAILER_DSN="null://null"
   FROM_EMAIL="noreply@mypos-carmarket.com"
   ```

4. **Database setup:**
   The application is configured to use SQLite database. The database file will be created automatically at `var/data/data_dev.db`.

5. **Run migrations:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Seed sample data (optional):**
   ```bash
   php bin/console app:seed-data
   ```

## Running the Application

### Docker (Recommended)
```bash
# Start the application (first time or after changes)
docker-compose up --build -d

# Start the application (subsequent times)
docker-compose up -d

# Stop the application
docker-compose down

# View logs
docker-compose logs -f

# Restart the application
docker-compose restart

# Access container shell
docker-compose exec app bash

# Run Symfony commands
docker-compose exec app php bin/console [command]
```

### Manual Setup
```bash
# Option 1: Using Symfony CLI
symfony serve

# Option 2: Using PHP built-in server
php -S localhost:8000 -t public/

# Option 3: Using Symfony console
php bin/console server:start
```

The application will be available at `http://localhost:8000`

## Quick Start Guide

### For New Users (Docker)
```bash
# 1. Clone the repository
git clone <your-repo-url>
cd mypos-symfony-app

# 2. Start the application
docker-compose up --build -d

# 3. Access the application
open http://localhost:8000

# 4. Login with credentials

### Troubleshooting
```bash
# If the application won't start
docker-compose logs

# If you need to reset the database
docker-compose down
docker-compose up --build -d

# If port 8000 is already in use
# Edit docker-compose.yml and change "8000:80" to "8001:80"
```

## Project Structure

```
mypos-symfony-app/
├── config/                 # Configuration files
│   ├── packages/          # Package configurations (security, doctrine, etc.)
│   └── routes.yaml        # Routing configuration
├── migrations/            # Database migrations
├── public/               # Web root
│   └── index.php         # Entry point
├── src/                  # Source code
│   ├── Controller/       # Controllers (Auth, Vehicle, Home, API, Error)
│   ├── Entity/          # Doctrine entities (User, Vehicle inheritance)
│   ├── Repository/      # Doctrine repositories
│   ├── Service/         # Business logic services
│   ├── DTO/            # Data Transfer Objects
│   └── Command/        # Console commands
├── templates/           # Twig templates
│   ├── auth/           # Authentication templates
│   ├── vehicle/        # Vehicle management templates
│   ├── emails/         # Email templates
│   ├── bundles/        # Custom error pages
│   └── base.html.twig  # Base template with navigation
├── tests/              # Comprehensive test suite
│   ├── Unit/           # Unit tests (isolated business logic)
│   ├── Integration/    # Integration tests (database interactions)
│   └── Application/    # Application tests (full HTTP requests)
├── public/             # Web assets
│   └── car-data.json   # Vehicle brand/model data
└── var/                # Variable data
    ├── data/           # Database directory
    │   ├── data_dev.db # SQLite database
    │   └── data_test.db # Test database
    └── logs/           # Application logs
```

## Vehicle Types & Validation

### Motorcycle
- **Required**: brand, model, engine_capacity, colour, price, quantity
- **Validation**: engine_capacity > 0, price > 0, quantity ≥ 0

### Car
- **Required**: brand, model, engine_capacity, colour, doors, category, price, quantity
- **Validation**: doors (2-5), engine_capacity > 0, price > 0, quantity ≥ 0

### Truck
- **Required**: brand, model, engine_capacity, colour, beds, price, quantity
- **Validation**: beds > 0, engine_capacity > 0, price > 0, quantity ≥ 0

### Trailer
- **Required**: brand, model, load_capacity_kg, axles, price, quantity
- **Validation**: load_capacity_kg > 0, axles > 0, price > 0, quantity ≥ 0

## Available Commands

### Docker Commands
```bash
# Start the application
docker-compose up --build -d

# Stop the application
docker-compose down

# View logs
docker-compose logs -f

# Access container shell
docker-compose exec app bash

# Run Symfony commands in container
docker-compose exec app php bin/console [command]

# Run tests in container
docker-compose exec app php bin/phpunit

# Enable email functionality (edit docker-compose.yml)
# Change MAILER_DSN from "null://null" to your SMTP configuration
```

### Database Commands
```bash
# Local development
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Docker
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console make:migration
docker-compose exec app php bin/console doctrine:migrations:migrate
```

### Data Management Commands
```bash
# Local development
php bin/console app:seed-data
php bin/console app:clear-data
php bin/console app:clear-cache

# Docker
docker-compose exec app php bin/console app:seed-data
docker-compose exec app php bin/console app:clear-data
docker-compose exec app php bin/console app:clear-cache
```

### Development Commands
```bash
# Local development
php bin/console make:entity
php bin/console make:controller
php bin/console make:test

# Docker
docker-compose exec app php bin/console make:entity
docker-compose exec app php bin/console make:controller
docker-compose exec app php bin/console make:test
```

### Testing Commands
```bash
# Local development
php bin/phpunit
php bin/phpunit --testsuite=unit
php bin/phpunit --testsuite=integration
php bin/phpunit --testsuite=application

# Docker
docker-compose exec app php bin/phpunit
docker-compose exec app php bin/phpunit --testsuite=unit
docker-compose exec app php bin/phpunit --testsuite=integration
docker-compose exec app php bin/phpunit --testsuite=application
```

## User Roles & Permissions

### Merchant (ROLE_MERCHANT)
- Create, edit, and delete their own vehicles
- View their vehicle inventory
- See follower statistics for their vehicles
- Access merchant dashboard

### Buyer (ROLE_BUYER)
- Browse all vehicles with filtering
- View vehicle details
- Follow/unfollow vehicles
- Access followed vehicles list

## API Endpoints

### Authentication
- `GET /login` - Login page
- `POST /login` - Process login
- `GET /register` - Registration page
- `POST /register` - Process registration
- `GET /logout` - Logout
- `GET /forgot-password` - Password reset request
- `POST /forgot-password` - Send reset email
- `GET /reset-password/{token}` - Password reset form
- `POST /reset-password/{token}` - Process password reset

### Vehicles
- `GET /vehicles` - Vehicle listing with filters and pagination
- `GET /vehicle/{id}` - Vehicle details
- `POST /vehicle/{id}/follow` - Follow vehicle (buyers only)
- `POST /vehicle/{id}/unfollow` - Unfollow vehicle (buyers only)

### API Endpoints
- `GET /api/car-data` - Get vehicle brand/model data for autocomplete

### Merchant Routes
- `GET /merchant/vehicles` - Merchant's vehicle list
- `GET /merchant/vehicle/new` - Create vehicle form
- `POST /merchant/vehicle/new` - Process vehicle creation
- `GET /merchant/vehicle/{id}/edit` - Edit vehicle form
- `POST /merchant/vehicle/{id}/edit` - Process vehicle update
- `POST /merchant/vehicle/{id}/delete` - Delete vehicle

### Buyer Routes
- `GET /buyer/followed` - Followed vehicles list

## Testing

The application includes a comprehensive test suite with **26 tests** covering all aspects of the application:

### Test Structure
- **Unit Tests (8 tests)**: Test business logic in isolation using mocks
- **Integration Tests (6 tests)**: Test service layer with real database interactions
- **Application Tests (12 tests)**: Test complete HTTP request/response cycle

### Test Coverage

#### Unit Tests (`tests/Unit/Service/VehicleServiceTest.php`)
- Vehicle creation for all types (Car, Motorcycle, Truck, Trailer)
- Follow/unfollow functionality
- Vehicle deletion
- Business logic validation

#### Integration Tests (`tests/Integration/Service/VehicleServiceIntegrationTest.php`)
- Database operations and data persistence
- Vehicle filtering and pagination
- Follow/unfollow workflow with database
- Filter options generation
- Relationship management (User-Vehicle many-to-many)

#### Application Tests (`tests/Application/Controller/VehicleControllerApplicationTest.php`)
- **Permission Tests**: Unauthorized access prevention, role-based access control
- **Data Integrity Tests**: Vehicle creation/update via HTTP forms
- **Listing & Filtering Tests**: Pagination, filtering by type/brand/color/price
- **Follow/Unfollow Tests**: Complete user interaction workflow
- **Complete Workflow Test**: End-to-end user journey

### Running Tests
```bash
# Run all tests (26 tests)
php bin/phpunit

# Run specific test suites
php bin/phpunit --testsuite=unit        # 8 unit tests
php bin/phpunit --testsuite=integration # 6 integration tests
php bin/phpunit --testsuite=application # 12 application tests

# Run with verbose output
php bin/phpunit --verbose

# Run with coverage
php bin/phpunit --coverage-html coverage/
```

### Test Environment
- Separate SQLite test database (`var/data/data_test.db`)
- Isolated test configuration
- Automatic database schema creation/cleanup
- Mock email sending for tests

## Configuration

- **Database**: SQLite (configured in `.env`)
- **Template Engine**: Twig with Bootstrap 5
- **Authentication**: Symfony Security Bundle
- **ORM**: Doctrine with inheritance support
- **Validation**: Symfony Validator
- **Testing**: PHPUnit with Symfony Test Pack
- **Email**: SMTP integration (Gmail) or disabled for development
- **Logging**: Custom LoggerService for application logs
- **Error Handling**: Custom error pages and controllers

## Development

The application is configured for development with:
- Debug mode enabled
- Detailed error pages with custom branding
- Hot reloading for templates
- SQLite for easy development setup
- Comprehensive logging with custom LoggerService
- Email testing with disabled mailer for development
- Autocomplete features for vehicle forms
- CSRF protection on all forms
- Custom error handling with branded 404 pages

## Architecture

### Clean Architecture Principles
- **Controllers**: Thin controllers handling HTTP requests/responses
- **Services**: Business logic encapsulated in service classes
- **Repositories**: Data access layer with custom query methods
- **DTOs**: Type-safe data transfer objects
- **Entities**: Rich domain models with validation

### Design Patterns
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic separation
- **DTO Pattern**: Data transfer optimization
- **Inheritance**: Vehicle type specialization
- **Dependency Injection**: Loose coupling

## Email System

The application includes a comprehensive email system for user communication:

### Features
- **Welcome Emails**: Sent to new users upon registration
- **Password Reset**: Secure token-based password recovery
- **SMTP Integration**: Support for Gmail or disabled for development
- **Template System**: Branded email templates with responsive design

### Configuration
```bash
# Gmail SMTP (Production)
MAILER_DSN="gmail://your-email@gmail.com:your-app-password@smtp.gmail.com:587"
FROM_EMAIL="your-email@gmail.com"

# Development (Email disabled)
MAILER_DSN="null://null"
FROM_EMAIL="noreply@mypos-carmarket.com"
```

### Email Templates
- `templates/emails/welcome.html.twig` - Welcome email for new users
- `templates/emails/password_reset.html.twig` - Password reset instructions

## Error Handling

### Custom Error Pages
- **404 Not Found**: Branded error page for missing routes
- **403 Forbidden**: Access denied page
- **500 Server Error**: Generic error page
- **Custom Error Controller**: Centralized error handling

### Error Page Locations
- `templates/bundles/TwigBundle/Exception/error404.html.twig`
- `templates/bundles/TwigBundle/Exception/error.html.twig`

## Autocomplete Features

### Vehicle Form Enhancement
- **Brand Autocomplete**: Dynamic suggestions from JSON data
- **Model Autocomplete**: Context-aware model suggestions
- **Data Source**: `public/car-data.json` with comprehensive vehicle data
- **API Endpoint**: `/api/car-data` for frontend integration

## Security Features

### CSRF Protection
- All forms include CSRF tokens
- Automatic token validation
- Secure form submissions

### Authentication & Authorization
- Role-based access control (ROLE_MERCHANT, ROLE_BUYER)
- Secure password hashing
- Session management
- Remember me functionality

## Logging System

### Custom LoggerService
- Application-level logging without Monolog dependency
- Multiple log levels (DEBUG, INFO, WARNING, ERROR)
- Automatic log file rotation
- Context-aware logging with exception support

### Log Locations
- `var/logs/application.log` - Main application log
- Automatic directory creation
- Configurable log levels
