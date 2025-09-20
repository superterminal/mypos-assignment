# MyPOS Symfony Application

A starter Symfony application with Twig templates and Doctrine ORM.

## Features

- **Symfony 7.3** - Latest stable version
- **Twig Templates** - Template engine with Bootstrap 5 styling
- **Doctrine ORM** - Database abstraction layer with SQLite
- **Sample Entity** - Product entity with repository
- **Modern UI** - Bootstrap 5 for responsive design

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
│   ├── packages/          # Package configurations
│   └── routes.yaml        # Routing configuration
├── migrations/            # Database migrations
├── public/               # Web root
│   └── index.php         # Entry point
├── src/                  # Source code
│   ├── Controller/       # Controllers
│   ├── Entity/          # Doctrine entities
│   └── Repository/      # Doctrine repositories
├── templates/           # Twig templates
│   ├── base.html.twig   # Base template
│   └── home/           # Home page templates
└── var/                # Variable data
    └── data_dev.db     # SQLite database
```

## Available Commands

- `php bin/console doctrine:database:create` - Create database
- `php bin/console make:migration` - Generate migration
- `php bin/console doctrine:migrations:migrate` - Run migrations
- `php bin/console make:entity` - Create new entity
- `php bin/console make:controller` - Create new controller

## Sample Entity

The project includes a `Product` entity with the following fields:
- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price (decimal)
- `stock` - Stock quantity
- `createdAt` - Creation timestamp
- `updatedAt` - Last update timestamp

## Next Steps

1. Create additional entities as needed
2. Implement CRUD operations
3. Add authentication and authorization
4. Implement business logic
5. Add API endpoints if needed
6. Add tests

## Configuration

- Database: SQLite (configured in `.env`)
- Template Engine: Twig
- CSS Framework: Bootstrap 5
- ORM: Doctrine

## Development

The application is set up for development with:
- Debug mode enabled
- Detailed error pages
- Hot reloading for templates
- SQLite for easy development setup
