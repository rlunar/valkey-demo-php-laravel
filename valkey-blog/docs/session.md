# Laravel Session Store: Database to Valkey Migration

## Overview

This guide shows how to migrate from database sessions to Valkey sessions to reduce database pressure. Your application will work exactly the same, but sessions will be stored in Valkey instead of the database.

## Benefits

- **Reduced Database Load**: Eliminates session table writes on every request
- **Better Performance**: In-memory storage is faster than database queries
- **Same Functionality**: All existing session features work identically

## Prerequisites

- Valkey installed (see [caching.md](./caching.md) for installation)
- Laravel application currently using database sessions

---

## Migration Steps

### Step 1: Update Environment Configuration

Change your `.env` file from database to Valkey sessions:

```env
# Change this line
SESSION_DRIVER=redis

# Keep existing Valkey configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Step 2: Test the Migration

1. **Clear existing sessions** (optional, for clean migration):
```bash
php artisan session:table  # if you want to backup current sessions
php artisan migrate:rollback --step=1  # remove sessions table if desired
```

2. **Test your application**:
   - Visit your application URLs
   - Login/logout functionality
   - Any session-dependent features
   - Everything should work exactly the same

### Step 3: Validate with Telescope

Install and configure Laravel Telescope to monitor session operations:

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

**Monitor session activity in Telescope:**
- Visit `/telescope/cache` to see Valkey operations
- Check that session reads/writes are happening in Valkey
- Verify no more database queries to the `sessions` table

### Step 4: Verify the Migration

**Check Valkey contains your sessions:**
```bash
# Connect to Valkey and see session keys
valkey-cli keys "*session*"

# View session data
valkey-cli get "laravel_database_session:YOUR_SESSION_ID"
```

**Confirm database is no longer used:**
- Use Telescope's database tab to verify no session table queries
- Check your database logs - should see no more session-related queries

---

## Validation Checklist

✅ **Application Functions Normally**
- All URLs work as before
- User authentication works
- Flash messages display correctly
- CSRF protection works
- Shopping carts/form data persists

✅ **Telescope Shows Valkey Activity**
- Session reads appear in `/telescope/cache`
- No session queries in `/telescope/queries`
- Valkey operations visible for each request

✅ **Database Pressure Reduced**
- No more `sessions` table queries
- Reduced database connection usage
- Better database performance for actual data

---

## Rollback (if needed)

If you need to rollback to database sessions:

```env
# Change back in .env
SESSION_DRIVER=database
```

```bash
# Recreate sessions table if removed
php artisan session:table
php artisan migrate
```

---

## Monitoring

Use these commands to monitor your Valkey sessions:

```bash
# Count active sessions
valkey-cli dbsize

# Monitor Valkey operations in real-time
valkey-cli monitor

# Check memory usage
valkey-cli info memory
```

**In Telescope:**
- Monitor `/telescope/cache` for session operations
- Check `/telescope/requests` for overall performance improvements
- Verify `/telescope/queries` shows reduced database activity

That's it! Your sessions are now stored in Valkey with zero functional changes to your application.