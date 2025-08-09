# Package Versions & Dependencies
## Sannu-Sannu SaaS Platform

### Overview

This document specifies the exact versions of all packages and dependencies used in the Sannu-Sannu platform, ensuring consistency across development, staging, and production environments.

---

## Backend Dependencies (PHP/Laravel)

### Core Framework
```json
{
  "php": "^8.4",
  "laravel/framework": "^12.0",
  "laravel/sanctum": "^4.0",
  "laravel/tinker": "^2.9",
  "inertiajs/inertia-laravel": "^2.0"
}
```

### Multi-Tenancy & Database
```json
{
  "stancl/tenancy": "^4.0",
  "doctrine/dbal": "^4.0",
  "laravel/telescope": "^5.0",
  "spatie/laravel-permission": "^6.0",
  "spatie/laravel-query-builder": "^6.0"
}
```

### Payment & External Services
```json
{
  "guzzlehttp/guzzle": "^7.8",
  "laravel/socialite": "^5.12",
  "pusher/pusher-php-server": "^7.2",
  "laravel/horizon": "^5.24"
}
```

### Development & Testing
```json
{
  "phpunit/phpunit": "^11.0",
  "mockery/mockery": "^1.6",
  "nunomaduro/collision": "^8.0",
  "fakerphp/faker": "^1.23",
  "laravel/pint": "^1.13",
  "phpstan/phpstan": "^1.10",
  "larastan/larastan": "^2.8"
}
```

---

## Frontend Dependencies (Node.js/React)

### Core Framework & Build Tools
```json
{
  "react": "^19.0.0",
  "react-dom": "^19.0.0",
  "@types/react": "^19.0.0",
  "@types/react-dom": "^19.0.0",
  "typescript": "^5.4",
  "vite": "^6.0.0",
  "@vitejs/plugin-react": "^4.2"
}
```

### Inertia.js & Routing
```json
{
  "@inertiajs/react": "^2.0.0",
  "@inertiajs/progress": "^0.2.7",
  "ziggy-js": "^2.0"
}
```

### UI Components & Styling
```json
{
  "tailwindcss": "^4.0.0",
  "@tailwindcss/forms": "^0.5.7",
  "@tailwindcss/typography": "^0.5.10",
  "tailwindcss-animate": "^1.0.7",
  "@radix-ui/react-slot": "^1.0.2",
  "@radix-ui/react-dialog": "^1.0.5",
  "@radix-ui/react-dropdown-menu": "^2.0.6",
  "@radix-ui/react-select": "^2.0.0",
  "@radix-ui/react-tabs": "^1.0.4",
  "@radix-ui/react-toast": "^1.1.5",
  "@radix-ui/react-tooltip": "^1.0.7",
  "class-variance-authority": "^0.7.0",
  "clsx": "^2.1.0",
  "tailwind-merge": "^2.2.0"
}
```

### Form Handling & Validation
```json
{
  "react-hook-form": "^7.49",
  "@hookform/resolvers": "^3.3",
  "zod": "^3.22"
}
```

### State Management & Utilities
```json
{
  "zustand": "^5.0.0",
  "immer": "^10.0.3",
  "date-fns": "^3.2.0",
  "lucide-react": "^0.344.0"
}
```

### Development Tools
```json
{
  "@types/node": "^20.11.0",
  "eslint": "^9.0.0",
  "@typescript-eslint/eslint-plugin": "^7.0.0",
  "@typescript-eslint/parser": "^7.0.0",
  "prettier": "^3.2.0",
  "prettier-plugin-tailwindcss": "^0.5.11",
  "vitest": "^1.2.0",
  "@testing-library/react": "^14.2.0",
  "@testing-library/jest-dom": "^6.4.0",
  "@testing-library/user-event": "^14.5.0"
}
```

---

## Infrastructure & DevOps

### Database
```yaml
mysql: "8.4"
redis: "7.2"
```

### Web Server
```yaml
nginx: "1.26"
# OR
apache: "2.4.58"
```

### PHP Configuration
```ini
; php.ini optimizations for Laravel 12
php_version = "8.4"
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.jit = tracing
opcache.jit_buffer_size = 100M
```

### Node.js & Package Manager
```yaml
node: "22.x LTS"
pnpm: "9.x"
```

---

## Development Environment Setup

### Package Installation Commands

#### Backend Setup
```bash
# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Install Laravel-specific packages
composer require laravel/framework:^12.0
composer require laravel/sanctum:^4.0
composer require inertiajs/inertia-laravel:^2.0
composer require stancl/tenancy:^4.0

# Development dependencies
composer require --dev phpunit/phpunit:^11.0
composer require --dev laravel/pint:^1.13
composer require --dev phpstan/phpstan:^1.10
```

#### Frontend Setup
```bash
# Install Node.js dependencies with pnpm
pnpm install

# Install React 19 and TypeScript
pnpm add react@^19.0.0 react-dom@^19.0.0
pnpm add -D @types/react@^19.0.0 @types/react-dom@^19.0.0
pnpm add -D typescript@^5.4

# Install Inertia.js
pnpm add @inertiajs/react@^2.0.0

# Install Tailwind CSS and shadcn/ui
pnpm add -D tailwindcss@^4.0.0
pnpm dlx shadcn@latest init

# Install UI components
pnpm dlx shadcn@latest add button card input dialog progress badge form table toast
```

---

## Version Compatibility Matrix

| Component | Version | Compatible With |
|-----------|---------|-----------------|
| PHP | 8.4+ | Laravel 12.x |
| Laravel | 12.x | PHP 8.4+, MySQL 8.4+ |
| React | 19.x | TypeScript 5.x, Vite 6.x |
| TypeScript | 5.4+ | React 19.x, Node.js 22+ |
| Node.js | 22.x LTS | pnpm 9.x, Vite 6.x |
| MySQL | 8.4+ | Laravel 12.x |
| Redis | 7.2+ | Laravel 12.x |
| Tailwind CSS | 4.x | React 19.x, Vite 6.x |

---

## Performance Optimizations

### PHP Optimizations
```bash
# Enable JIT compilation
echo "opcache.jit=tracing" >> /etc/php/8.4/fpm/conf.d/10-opcache.ini
echo "opcache.jit_buffer_size=100M" >> /etc/php/8.4/fpm/conf.d/10-opcache.ini

# Optimize Composer autoloader
composer dump-autoload --optimize --classmap-authoritative
```

### Node.js Optimizations
```bash
# Use pnpm for faster installs
pnpm config set store-dir ~/.pnpm-store
pnpm config set cache-dir ~/.pnpm-cache

# Enable Vite optimizations
echo "NODE_OPTIONS=--max-old-space-size=4096" >> .env
```

### Database Optimizations
```sql
-- MySQL 8.4 optimizations
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB
SET GLOBAL query_cache_type = ON;
SET GLOBAL query_cache_size = 67108864; -- 64MB
```

---

## Security Updates

### Automated Security Updates
```bash
# Composer security updates
composer audit
composer update --with-dependencies

# npm security updates
pnpm audit
pnpm update

# System security updates (Ubuntu/Debian)
apt update && apt upgrade -y
```

### Security Scanning
```bash
# PHP security scanning
composer require --dev roave/security-advisories:dev-latest

# Node.js security scanning
pnpm dlx audit-ci --config audit-ci.json

# Static analysis
./vendor/bin/phpstan analyse
pnpm run type-check
```

---

## Deployment Checklist

### Pre-deployment
- [ ] All packages updated to specified versions
- [ ] Security audit passed
- [ ] Tests passing (PHPUnit + Vitest)
- [ ] Type checking passed (TypeScript + PHPStan)
- [ ] Code quality checks passed (Pint + ESLint)

### Production Environment
- [ ] PHP 8.4 with JIT enabled
- [ ] MySQL 8.4 with optimized configuration
- [ ] Redis 7.2 for caching and sessions
- [ ] Nginx 1.26 with HTTP/2 and compression
- [ ] SSL/TLS certificates configured
- [ ] Monitoring and logging enabled

This comprehensive package specification ensures the Sannu-Sannu platform uses the latest, most secure, and performant versions of all dependencies.