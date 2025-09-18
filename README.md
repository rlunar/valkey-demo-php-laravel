# Valkey Demo - PHP Laravel Application

A modern Laravel application demonstrating high-performance caching, session management, leaderboard systems, and queue processing using **Valkey** (Redis-compatible) as the backend store.

## 🚀 Features

This demo showcases four key Valkey use cases:

### 🗄️ **Cache Management**
- Database query result caching
- Application-level caching with TTL
- Cache invalidation strategies
- Multiple cache stores (database, Redis/Valkey)

### 🔐 **Session Management** 
- User session storage in Valkey
- Secure session handling
- Session persistence across requests
- Configurable session lifetime

### 🏆 **Leaderboard System**
- Real-time scoring and rankings
- Sorted sets for efficient leaderboards
- User score tracking and updates
- Top performers display

### ⚡ **Queue Processing**
- Background job processing
- Asynchronous task execution
- Job batching and retry mechanisms
- Failed job handling

## 🛠️ Tech Stack

- **Backend**: PHP 8.2+ with Laravel 12
- **Frontend**: React 19 with TypeScript and Inertia.js
- **Styling**: Tailwind CSS 4.0
- **Cache/Session/Queue Store**: Valkey (Redis-compatible)
- **Database**: SQLite (configurable)
- **Build Tools**: Vite, Laravel Mix

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- Valkey server (or Redis)

## 🚀 Quick Start

### 1. Clone and Install Dependencies

```bash
git clone <repository-url>
cd valkey-demo-php-laravel
composer install
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Valkey Connection

Update your `.env` file with Valkey settings:

```env
# Cache Configuration
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null

# Session Configuration  
SESSION_DRIVER=redis
SESSION_STORE=redis

# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_QUEUE_CONNECTION=default
```

### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets

```bash
npm run build
# or for development
npm run dev
```

### 6. Start the Application

```bash
# Development mode with all services
composer run dev

# Or start services individually:
php artisan serve              # Web server
php artisan queue:work         # Queue worker  
php artisan pail              # Log monitoring
npm run dev                   # Asset compilation
```

Visit `http://localhost:8000` to see the application.

## 🔧 Configuration

### Cache Stores

The application supports multiple cache backends configured in `config/cache.php`:

- **Database**: Uses `cache` table for persistence
- **Redis/Valkey**: High-performance in-memory caching
- **File**: Filesystem-based caching
- **Array**: In-memory caching for testing

### Session Management

Session configuration in `config/session.php`:

- **Driver**: Database, Redis/Valkey, or file-based
- **Lifetime**: Configurable session timeout
- **Security**: HTTPS-only, HTTP-only, and SameSite options

### Queue System

Queue configuration in `config/queue.php`:

- **Database**: Persistent job storage
- **Redis/Valkey**: High-performance job processing
- **Sync**: Immediate job execution for development
- **SQS**: AWS Simple Queue Service integration

## 📊 Demo Features

### Cache Examples
- **Page Caching**: Frequently accessed pages cached for faster loading
- **Query Caching**: Database query results cached with automatic invalidation
- **API Response Caching**: External API calls cached to reduce latency

### Session Examples
- **User Authentication**: Secure login/logout with session persistence
- **Shopping Cart**: Session-based cart management
- **User Preferences**: Personalized settings stored in sessions

### Leaderboard Examples
- **Gaming Scores**: Real-time player rankings
- **User Activity**: Most active users leaderboard
- **Performance Metrics**: Top performers by various criteria

### Queue Examples
- **Email Processing**: Asynchronous email sending
- **Image Processing**: Background image optimization
- **Data Import**: Large dataset processing in background
- **Report Generation**: Heavy computation tasks

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## 📈 Performance Monitoring

The application includes built-in monitoring:

- **Laravel Pail**: Real-time log monitoring
- **Queue Monitoring**: Job processing statistics
- **Cache Hit Rates**: Cache performance metrics
- **Session Analytics**: User session insights

## 🔍 Development Tools

### Available Commands

```bash
# Development server with all services
composer run dev

# Development with SSR support
composer run dev:ssr

# Code formatting
npm run format
composer run format

# Linting
npm run lint
php artisan pint

# Type checking
npm run types
```

### Debugging

- **Laravel Telescope**: Application debugging (install separately)
- **Laravel Debugbar**: Request/response debugging
- **Pail Logs**: Real-time application logs
- **Queue Dashboard**: Job monitoring interface

## 🚀 Deployment

### Production Setup

1. **Environment Configuration**:
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   ```

2. **Optimize Application**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

3. **Queue Workers**:
   ```bash
   php artisan queue:work --daemon
   ```

### Docker Deployment

```dockerfile
# Example Dockerfile structure
FROM php:8.2-fpm
# Install dependencies, copy files, configure services
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [Laravel Docs](https://laravel.com/docs)
- **Valkey**: [Valkey Documentation](https://valkey.io/docs/)
- **Issues**: [GitHub Issues](../../issues)
- **Discussions**: [GitHub Discussions](../../discussions)

## 🙏 Acknowledgments

- Laravel Framework team
- Valkey project contributors
- React and Inertia.js communities
- Tailwind CSS team

---

**Built with ❤️ using Laravel, React, and Valkey**