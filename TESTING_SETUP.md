# Testing Setup Guide

This document outlines the testing tools and commands available in this Laravel project.

## Installed Packages

### 1. Blueprint (Code Generation)
**Package:** `laravel-shift/blueprint`  
**Purpose:** Generate models, migrations, factories, controllers, and tests from YAML definitions.

**Usage:**
```bash
# Create a new draft file
php artisan blueprint:new

# Edit draft.yaml, then build components
php artisan blueprint:build

# Erase generated files
php artisan blueprint:erase
```

**Example `draft.yaml`:**
```yaml
models:
  Post:
    title: string:400
    content: longtext
    published_at: nullable timestamp
    relationships:
      hasMany: Comment

controllers:
  Post:
    index:
      query: all
      render: post.index with:posts
    store:
      validate: title, content
      save: post
      redirect: post.index
```

**Documentation:** [Blueprint Docs](https://blueprint.laravelshift.com/docs)

---

### 2. Laravel Test Assertions
**Package:** `jasonmccreary/laravel-test-assertions`  
**Purpose:** Additional helpful test assertions for Laravel.

**Usage:**
This package automatically extends your test classes with additional assertions. No configuration needed.

**Example:**
```php
// Additional assertions available:
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonValidationErrors(['email']);
```

**Documentation:** [GitHub](https://github.com/jasonmccreary/laravel-test-assertions)

---

### 3. Laravel Debugbar ⭐ Must-Have
**Package:** `barryvdh/laravel-debugbar`  
**Purpose:** Developer toolbar for debugging with comprehensive request/response information.

**Features:**
- ✅ View all database queries with execution time
- ✅ Catch N+1 query problems automatically
- ✅ Monitor request/response cycles
- ✅ Track AJAX requests
- ✅ View route execution times
- ✅ Memory usage monitoring
- ✅ View queries, logs, exceptions, views, and more

**How It Works:**
- Automatically enabled when `APP_DEBUG=true` in your `.env`
- Shows a toolbar at the bottom of your pages
- Click tabs to view detailed information
- Stores data for AJAX requests

**Configuration:**
The configuration file is at `config/debugbar.php`. Key settings:

```php
'enabled' => env('DEBUGBAR_ENABLED'), // null = auto (based on APP_DEBUG)
'hide_empty_tabs' => true,            // Hide tabs until they have content
'except' => [                         // Routes to exclude
    'telescope*',
    'horizon*',
],
'storage' => [
    'enabled' => true,                // Store data for AJAX requests
    'driver' => 'file',                // file, redis, pdo
],
'editor' => 'phpstorm',              // Editor for file links
```

**Usage:**
1. Ensure `APP_DEBUG=true` in your `.env` file
2. Visit any page in your application
3. The debugbar will appear at the bottom
4. Click tabs to explore:
   - **Messages:** Log messages
   - **Time:** Execution time breakdown
   - **Queries:** All database queries with bindings
   - **Models:** Eloquent models loaded
   - **Views:** Rendered Blade templates
   - **Route:** Current route information
   - **Exceptions:** Any exceptions thrown
   - **Request:** Request data
   - **Response:** Response data

**Disable for Specific Routes:**
Add routes to the `except` array in `config/debugbar.php`:

```php
'except' => [
    'api/*',
    'admin/*',
],
```

**Environment Variables:**
```env
# Enable/disable debugbar (null = auto based on APP_DEBUG)
DEBUGBAR_ENABLED=true

# Hide empty tabs
DEBUGBAR_HIDE_EMPTY_TABS=true

# Storage settings
DEBUGBAR_STORAGE_ENABLED=true
DEBUGBAR_STORAGE_DRIVER=file
```

**Documentation:** [GitHub](https://github.com/barryvdh/laravel-debugbar)

---

### 4. Laravel Telescope ⭐ Essential
**Package:** `laravel/telescope`  
**Purpose:** Official debugging & monitoring tool for production-ready applications.

**Features:**
- ✅ Request monitoring with full request/response data
- ✅ Exception tracking with stack traces
- ✅ Database query logs with bindings
- ✅ Job monitoring (queued jobs, failed jobs)
- ✅ Mail preview and tracking
- ✅ Cache operations monitoring
- ✅ Scheduled tasks tracking
- ✅ View rendered templates
- ✅ Event tracking
- ✅ Log aggregation

**Installation:**
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

**Access:**
- **Local environment:** Accessible at `/telescope` (no authentication required)
- **Production:** Only accessible to admin users (configured in `TelescopeServiceProvider`)

**Configuration:**
The configuration file is at `config/telescope.php`. Key settings:

```php
'enabled' => env('TELESCOPE_ENABLED', true),
'path' => env('TELESCOPE_PATH', 'telescope'),
'driver' => env('TELESCOPE_DRIVER', 'database'),
```

**Access Control:**
Access is controlled in `app/Providers/TelescopeServiceProvider.php`:
- **Local environment:** Open access
- **Production:** Only users with `is_admin = true` can access

**What Telescope Tracks:**
- **Requests:** All HTTP requests with headers, body, response
- **Queries:** Database queries with execution time and bindings
- **Models:** Eloquent model operations
- **Jobs:** Queue jobs (pending, processing, failed)
- **Mail:** All sent emails with preview
- **Notifications:** All notifications sent
- **Cache:** Cache hits, misses, writes
- **Scheduled Tasks:** All scheduled command executions
- **Events:** All fired events
- **Logs:** Application logs
- **Dumps:** `dd()` and `dump()` output
- **Views:** Rendered Blade templates

**Usage:**
1. Visit `/telescope` in your browser
2. Browse through different tabs:
   - **Requests:** View all HTTP requests
   - **Commands:** Artisan commands executed
   - **Schedule:** Scheduled tasks
   - **Jobs:** Queue jobs
   - **Batches:** Job batches
   - **Queries:** Database queries
   - **Models:** Eloquent operations
   - **Events:** Fired events
   - **Mail:** Sent emails
   - **Notifications:** Sent notifications
   - **Cache:** Cache operations
   - **Dumps:** Debug dumps
   - **Logs:** Application logs
   - **Exceptions:** Caught exceptions

**Filtering:**
- Use the search bar to filter by tag, request ID, or content
- Click on any entry to see detailed information
- Use tags to mark important requests: `Telescope::tag('important')`

**Production Considerations:**
- Telescope stores data in the database (can grow large)
- Use `php artisan telescope:prune` to clean old entries
- Configure watchers to reduce data collection in production
- Set up scheduled pruning: `php artisan schedule:list`

**Pruning Old Data:**
```bash
# Prune entries older than 24 hours
php artisan telescope:prune --hours=24

# Add to scheduler (app/Console/Kernel.php)
$schedule->command('telescope:prune --hours=48')->daily();
```

**Environment Variables:**
```env
# Enable/disable Telescope
TELESCOPE_ENABLED=true

# Custom path (default: telescope)
TELESCOPE_PATH=telescope

# Storage driver (database, redis)
TELESCOPE_DRIVER=database
```

**Documentation:** [Laravel Telescope Docs](https://laravel.com/docs/12.x/telescope)

---

## Custom Test Generation Command

### `php artisan test:generate`

Automatically generates missing test files for your application.

**Options:**
- `--type=all|models|services|controllers|livewire` - Type of tests to generate (default: `all`)
- `--force` - Overwrite existing test files

**Examples:**
```bash
# Generate all missing tests
php artisan test:generate

# Generate only model tests
php artisan test:generate --type=models

# Generate service tests
php artisan test:generate --type=services

# Generate controller tests
php artisan test:generate --type=controllers

# Generate Livewire component tests
php artisan test:generate --type=livewire

# Force overwrite existing tests
php artisan test:generate --force
```

**What it generates:**
- **Models:** Basic factory and creation tests in `tests/Unit/Models/`
- **Services:** Service instantiation tests in `tests/Unit/Services/`
- **Controllers:** Basic controller tests in `tests/Feature/`
- **Livewire:** Component rendering tests in `tests/Feature/Livewire/`

---

## Current Testing Framework

This project uses **PHPUnit 12** (not Pest) due to compatibility requirements.

**Note:** Pest 4.x is not yet compatible with PHPUnit 12. If you want to use Pest in the future, you'll need to wait for Pest to support PHPUnit 12, or downgrade to PHPUnit 11.

---

## Running Tests

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

---

## Recommended Workflow

### For New Features:
1. Use **Blueprint** to generate models, controllers, and initial tests:
   ```bash
   php artisan blueprint:new
   # Edit draft.yaml
   php artisan blueprint:build
   ```

2. Use **test:generate** to fill in any missing tests:
   ```bash
   php artisan test:generate --type=all
   ```

3. Write custom test logic as needed.

### For Existing Code:
1. Run **test:generate** to identify and create missing tests:
   ```bash
   php artisan test:generate
   ```

2. Review generated tests and add business logic tests.

3. Run tests to ensure everything passes:
   ```bash
   composer test
   ```

---

## Alternative Tools (Not Installed)

### Laravel Shift Tests Generator ($9)
- **Best for:** Complete automation of existing applications
- **Website:** https://laravelshift.com/laravel-test-generator
- **What it does:** Analyzes your codebase and generates comprehensive tests automatically

### AutoAPI Laravel Tester (Free)
- **Best for:** API testing automation
- **Installation:** `composer require --dev autoapi/laravel-tester`
- **Usage:** `php artisan api:test-all`

### PestPHP (Free)
- **Status:** Not compatible with PHPUnit 12 yet
- **Note:** Will be available once Pest supports PHPUnit 12

---

## Best Practices

1. **Run tests frequently:** Use `composer test` before committing
2. **Generate tests early:** Use `test:generate` when creating new components
3. **Use factories:** All models should have factories for easy test data creation
4. **Test business logic:** Don't just test that code runs—test that it works correctly
5. **Keep tests fast:** Use `RefreshDatabase` sparingly, prefer in-memory SQLite for unit tests

---

## Email Preview (Spatie Laravel Mail Preview)

**Package:** `spatie/laravel-mail-preview`  
**Purpose:** Preview emails in the browser instead of sending them during development.

### How It Works

When `APP_DEBUG=true` in your `.env` file, the package automatically:
- Intercepts all outgoing emails
- Stores them as HTML files in `storage/email-previews/`
- Shows a preview link in the response when emails are sent
- Displays a popup with the email preview (configurable timeout)

### Configuration

The configuration file is located at `config/mail-preview.php`:

```php
'enabled' => env('APP_DEBUG', false),  // Only active in debug mode
'storage_path' => storage_path('email-previews'),
'maximum_lifetime_in_seconds' => 60,  // How long preview files are kept
'show_link_to_preview' => true,       // Show link in response
'popup_timeout_in_seconds' => 8,      // Popup display duration
```

### Usage

1. **Ensure debug mode is enabled:**
   ```env
   APP_DEBUG=true
   ```

2. **Send an email as normal:**
   ```php
   use App\Notifications\PostMentioned;
   
   $user->notify(new PostMentioned($post, $mentionedBy));
   ```

3. **View the preview:**
   - A popup will automatically appear showing the email
   - Or click the preview link in the response
   - Or visit the stored HTML file in `storage/email-previews/`

### Benefits

- ✅ No need to configure SMTP for development
- ✅ See exactly how emails look before sending
- ✅ Test email templates without sending real emails
- ✅ Automatically disabled in production (when `APP_DEBUG=false`)

**Documentation:** [Spatie Mail Preview](https://github.com/spatie/laravel-mail-preview)

---

## Resources

- [Laravel Testing Documentation](https://laravel.com/docs/12.x/testing)
- [Blueprint Documentation](https://blueprint.laravelshift.com/docs)
- [Laravel Test Assertions](https://github.com/jasonmccreary/laravel-test-assertions)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Spatie Mail Preview](https://github.com/spatie/laravel-mail-preview)
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Laravel Telescope](https://laravel.com/docs/12.x/telescope)

