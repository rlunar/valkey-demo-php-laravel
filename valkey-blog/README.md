# Cache Me, If You Can: Valkey Edition

A modern, responsive blog application built with Laravel 10 and Bootstrap 5. This full-featured blog platform includes post management, categorization, tagging, and a weather widget displaying international city weather data.

## Features

### Core Blog Functionality

- **Post Management**: Full CRUD operations for blog posts with rich content support
- **User Authentication**: Secure login system with Laravel Breeze
- **Categories & Tags**: Organize posts with categories and flexible tagging system
- **SEO-Friendly URLs**: Automatic slug generation for posts and categories
- **Draft System**: Save posts as drafts before publishing
- **Responsive Design**: Mobile-first design using Bootstrap 5

### Weather Widget

- **International Weather**: Displays weather for 100+ cities across 10 countries
- **Real-time Updates**: Auto-refreshing weather data every 5 minutes
- **Interactive Interface**: Manual refresh capability and hover effects
- **Local Time Display**: Shows local time for each displayed city

### Technical Features

- **Laravel 10**: Modern PHP framework with Eloquent ORM
- **Bootstrap 5**: Responsive CSS framework with custom styling
- **Vite**: Fast build tool for asset compilation
- **Laravel Telescope**: Application debugging and monitoring
- **PHPUnit Testing**: Comprehensive test suite

## Project Structure

```
├── app/
│   ├── Http/Controllers/     # Application controllers
│   │   ├── HomeController.php       # Public blog pages
│   │   ├── PostController.php       # Post CRUD operations
│   │   ├── CategoryController.php   # Category management
│   │   ├── TagController.php        # Tag management
│   │   └── WeatherController.php    # Weather API endpoints
│   ├── Models/              # Eloquent models
│   │   ├── Post.php         # Blog post model
│   │   ├── Category.php     # Post categories
│   │   ├── Tag.php          # Post tags
│   │   ├── User.php         # User authentication
│   │   └── WeatherCity.php  # Weather cities data
│   └── View/                # View composers and components
├── database/
│   ├── migrations/          # Database schema migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── views/              # Blade templates
│   │   ├── components/     # Reusable components
│   │   ├── layouts/        # Layout templates
│   │   └── posts/          # Post-related views
│   └── js/                 # Frontend JavaScript
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
└── tests/                  # PHPUnit tests
```

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & npm
- MySQL or SQLite database

### Setup Steps

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd laravel-bootstrap-blog
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install Node.js dependencies**

   ```bash
   npm install
   ```

4. **Environment configuration**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**

   ```bash
   # Configure database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**

   ```bash
   npm run build
   # or for development
   npm run dev
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to view the blog.

## Usage

### Public Features

- **Homepage**: Browse published blog posts with pagination
- **Post Reading**: View individual posts with full content and metadata
- **Categories**: Browse posts by category
- **Tags**: Filter posts by tags
- **Popular Posts**: View most popular content
- **Weather Widget**: Check weather for international cities

### Admin Features (Authentication Required)

- **Post Management**: Create, edit, and delete blog posts
- **Category Management**: Organize posts into categories
- **Tag Management**: Create and manage post tags
- **Draft System**: Save posts as drafts before publishing

### API Endpoints

- `GET /api/weather/random?count=5` - Get weather for multiple random cities
- `GET /api/weather/single` - Get weather for one random city

## Database Schema

### Core Tables

- **users**: User authentication and profiles
- **posts**: Blog posts with content, metadata, and relationships
- **categories**: Post categorization system
- **tags**: Flexible tagging system
- **weather_cities**: International cities for weather widget

### Key Relationships

- Posts belong to Users (author relationship)
- Posts belong to Categories
- Posts have many Tags (many-to-many)
- Weather cities are independent entities

## Testing

Run the comprehensive test suite:

```bash
# Run all tests
php artisan test

# Run specific test files
php artisan test tests/Feature/PostTest.php

# Run with coverage
php artisan test --coverage
```

## Development

### Asset Development

```bash
# Watch for changes during development
npm run dev

# Build for production
npm run build
```

### Database Management

```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

### Debugging

Laravel Telescope is included for application monitoring:

- Visit `/telescope` when running in local environment
- Monitor database queries, requests, and performance

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Security

- CSRF protection on all forms
- XSS prevention in content display
- Authentication middleware on admin routes
- Input validation and sanitization
- Secure password hashing with Laravel's built-in system

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
