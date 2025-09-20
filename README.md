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
- **Testing** - Comprehensive unit and integration tests
- **Doctrine ORM** - Advanced database operations with inheritance support

## Installation

1. **Clone or navigate to the project directory:**
   ```bash
   cd mypos-symfony-app
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Database setup:**
   The application is configured to use SQLite database. The database file will be created automatically at `var/data_dev.db`.

4. **Run migrations:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

## Running the Application

### Option 1: Using Symfony CLI (Recommended)
```bash
symfony serve
```

### Option 2: Using PHP built-in server
```bash
php -S localhost:8000 -t public/
```

### Option 3: Using Symfony console
```bash
php bin/console server:start
```

The application will be available at `http://localhost:8000`

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
│   ├── Controller/       # Controllers (Auth, Vehicle, Home)
│   ├── Entity/          # Doctrine entities (User, Vehicle inheritance)
│   ├── Repository/      # Doctrine repositories
│   ├── Service/         # Business logic services
│   └── DTO/            # Data Transfer Objects
├── templates/           # Twig templates
│   ├── auth/           # Authentication templates
│   ├── vehicle/        # Vehicle management templates
│   └── base.html.twig  # Base template with navigation
├── tests/              # Test suite
│   ├── Controller/     # Controller tests
│   └── Service/        # Service tests
└── var/                # Variable data
    └── data_dev.db     # SQLite database
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

### Database
- `php bin/console doctrine:database:create` - Create database
- `php bin/console make:migration` - Generate migration
- `php bin/console doctrine:migrations:migrate` - Run migrations

### Development
- `php bin/console make:entity` - Create new entity
- `php bin/console make:controller` - Create new controller
- `php bin/console make:test` - Create new test

### Testing
- `php bin/phpunit` - Run all tests
- `php bin/phpunit tests/Controller/` - Run controller tests
- `php bin/phpunit tests/Service/` - Run service tests

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
- `GET /vehicles` - Vehicle listing with filters
- `GET /vehicle/{id}` - Vehicle details
- `POST /vehicle/{id}/follow` - Follow vehicle (buyers only)
- `POST /vehicle/{id}/unfollow` - Unfollow vehicle (buyers only)

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

The application includes comprehensive tests covering:

### Controller Tests
- Vehicle listing and filtering
- Authentication and authorization
- CRUD operations for merchants
- Follow/unfollow functionality for buyers
- Access control validation

### Service Tests
- Vehicle creation and management
- Filtering and pagination
- Follow/unfollow operations
- Data integrity validation

### Running Tests
```bash
# Run all tests
php bin/phpunit

# Run specific test suites
php bin/phpunit tests/Controller/
php bin/phpunit tests/Service/

# Run with coverage
php bin/phpunit --coverage-html coverage/
```

## Configuration

- **Database**: SQLite (configured in `.env`)
- **Template Engine**: Twig with Bootstrap 5
- **Authentication**: Symfony Security Bundle
- **ORM**: Doctrine with inheritance support
- **Validation**: Symfony Validator
- **Testing**: PHPUnit with Symfony Test Pack

## Development

The application is configured for development with:
- Debug mode enabled
- Detailed error pages
- Hot reloading for templates
- SQLite for easy development setup
- Comprehensive logging

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
