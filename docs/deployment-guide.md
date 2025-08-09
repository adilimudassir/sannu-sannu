# Deployment Guide
## Sannu-Sannu SaaS Platform

### Deployment Overview

This guide covers the complete deployment process for the Sannu-Sannu platform, from development to production environments. The platform uses Laravel + Inertia.js + React with shadcn/ui components.

---

## Environment Setup

### Development Environment

#### Prerequisites
```bash
# Required software
- PHP 8.4+ (with JIT compilation enabled)
- Node.js 22+ LTS
- MySQL 8.4+
- Redis 7.x
- Composer 2.7+
- pnpm 9.x (recommended over npm/yarn)

# PHP Extensions (required)
- opcache, redis, mysql, gd, zip, curl, mbstring, intl, bcmath

# Optional but recommended
- Docker & Docker Compose v2
- Git 2.40+
- Nginx 1.26+ or Apache 2.4+
```

#### Local Development Setup
```bash
# Clone repository
git clone https://github.com/your-org/sannu-sannu.git
cd sannu-sannu

# Install PHP dependencies (with optimizations)
composer install --optimize-autoloader

# Install Node.js dependencies (using pnpm for speed)
pnpm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database with multi-tenant setup
php artisan migrate --seed
php artisan tenants:migrate

# Install shadcn/ui components
pnpm dlx shadcn@latest init
pnpm dlx shadcn@latest add button card input dialog progress badge form table toast tabs select

# Enable PHP OPcache for development
echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Start development servers with hot reload
php artisan serve &
pnpm run dev
```

#### Docker Development Setup
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.dev
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=local
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis

  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: sannu_sannu
      MYSQL_USER: sannu_user
      MYSQL_PASSWORD: sannu_password
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

  node:
    image: node:18-alpine
    working_dir: /app
    volumes:
      - .:/app
    command: npm run dev
    ports:
      - "5173:5173"

volumes:
  mysql_data:
  redis_data:
```

```dockerfile
# Dockerfile.dev
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
```

---

## Staging Environment

### Server Requirements
```bash
# Minimum server specifications
- CPU: 2 cores
- RAM: 4GB
- Storage: 50GB SSD
- OS: Ubuntu 20.04 LTS or CentOS 8

# Software requirements
- Nginx 1.18+
- PHP 8.1+ with FPM
- MySQL 8.0+
- Redis 6.0+
- Node.js 18+
- SSL Certificate
```

### Server Setup Script
```bash
#!/bin/bash
# staging-setup.sh

# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.1-fpm php8.1-mysql \
    php8.1-redis php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip \
    php8.1-bcmath php8.1-gd unzip curl git

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Configure MySQL
sudo mysql_secure_installation

# Configure Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Configure PHP-FPM
sudo systemctl enable php8.1-fpm
sudo systemctl start php8.1-fpm

# Configure Nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### Nginx Configuration
```nginx
# /etc/nginx/sites-available/sannu-sannu-staging
server {
    listen 80;
    server_name staging.sannu-sannu.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name staging.sannu-sannu.com;
    root /var/www/sannu-sannu/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/staging.sannu-sannu.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/staging.sannu-sannu.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Deployment Script for Staging
```bash
#!/bin/bash
# deploy-staging.sh

set -e

PROJECT_DIR="/var/www/sannu-sannu"
BACKUP_DIR="/var/backups/sannu-sannu"
DATE=$(date +%Y%m%d_%H%M%S)

echo "Starting deployment to staging..."

# Create backup
echo "Creating backup..."
mkdir -p $BACKUP_DIR
mysqldump sannu_sannu > $BACKUP_DIR/db_backup_$DATE.sql
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C $PROJECT_DIR .

# Pull latest code
echo "Pulling latest code..."
cd $PROJECT_DIR
git pull origin staging

# Install/update dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear caches
echo "Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart services
echo "Restarting services..."
sudo systemctl reload nginx
sudo systemctl reload php8.1-fpm

echo "Staging deployment completed successfully!"
```

---

## Production Environment

### Infrastructure Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Load Balancer │    │   Web Servers   │    │   Database      │
│   (Nginx/HAProxy│◄──►│   (2+ instances)│◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   CDN           │    │   Redis Cluster │    │   File Storage  │
│   (CloudFlare)  │    │   (Cache/Queue) │    │   (S3/Local)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Production Server Setup
```bash
#!/bin/bash
# production-setup.sh

# Enhanced security setup
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https

# Install fail2ban
sudo apt install -y fail2ban
sudo systemctl enable fail2ban

# Configure automatic security updates
sudo apt install -y unattended-upgrades
echo 'Unattended-Upgrade::Automatic-Reboot "false";' | sudo tee -a /etc/apt/apt.conf.d/50unattended-upgrades

# Setup log rotation
sudo tee /etc/logrotate.d/sannu-sannu << EOF
/var/www/sannu-sannu/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
EOF
```

### Production Environment Variables
```bash
# .env.production
APP_NAME="Sannu-Sannu"
APP_ENV=production
APP_KEY=base64:your-production-key-here
APP_DEBUG=false
APP_URL=https://sannu-sannu.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sannu_sannu_prod
DB_USERNAME=sannu_prod_user
DB_PASSWORD=your-secure-database-password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@sannu-sannu.com
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_DEFAULT_REGION=us-west-2
AWS_BUCKET=sannu-sannu-storage

PAYSTACK_PUBLIC_KEY=pk_live_your-public-key
PAYSTACK_SECRET_KEY=sk_live_your-secret-key
PAYSTACK_WEBHOOK_SECRET=your-webhook-secret

VITE_APP_NAME="${APP_NAME}"
VITE_PAYSTACK_PUBLIC_KEY="${PAYSTACK_PUBLIC_KEY}"
```

### Zero-Downtime Deployment Script
```bash
#!/bin/bash
# deploy-production.sh

set -e

PROJECT_DIR="/var/www/sannu-sannu"
RELEASES_DIR="$PROJECT_DIR/releases"
SHARED_DIR="$PROJECT_DIR/shared"
CURRENT_LINK="$PROJECT_DIR/current"
RELEASE_DIR="$RELEASES_DIR/$(date +%Y%m%d_%H%M%S)"

echo "Starting zero-downtime deployment..."

# Create directory structure
mkdir -p $RELEASES_DIR $SHARED_DIR/{storage,bootstrap/cache}

# Clone latest code
echo "Cloning latest code..."
git clone --depth 1 --branch main https://github.com/your-org/sannu-sannu.git $RELEASE_DIR

cd $RELEASE_DIR

# Create symlinks to shared directories
echo "Creating symlinks..."
rm -rf storage bootstrap/cache
ln -nfs $SHARED_DIR/storage storage
ln -nfs $SHARED_DIR/bootstrap/cache bootstrap/cache

# Copy environment file
cp $SHARED_DIR/.env .env

# Install dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --production
npm run build

# Run migrations (if needed)
echo "Running migrations..."
php artisan migrate --force

# Optimize Laravel
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update symlink atomically
echo "Switching to new release..."
ln -nfs $RELEASE_DIR $CURRENT_LINK

# Restart services
echo "Restarting services..."
sudo systemctl reload nginx
sudo systemctl reload php8.1-fpm
php artisan queue:restart

# Clean up old releases (keep last 5)
echo "Cleaning up old releases..."
cd $RELEASES_DIR && ls -t | tail -n +6 | xargs rm -rf

echo "Production deployment completed successfully!"
```

---

## Monitoring & Health Checks

### Application Monitoring
```php
// app/Http/Controllers/HealthController.php
class HealthController extends Controller
{
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connection successful'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['status' => 'ok', 'message' => 'Redis connection successful'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Redis connection failed'];
        }
    }
}
```

### Monitoring Script
```bash
#!/bin/bash
# monitor.sh

HEALTH_URL="https://sannu-sannu.com/health"
SLACK_WEBHOOK="your-slack-webhook-url"

# Check application health
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $HEALTH_URL)

if [ $RESPONSE -ne 200 ]; then
    # Send alert to Slack
    curl -X POST -H 'Content-type: application/json' \
        --data '{"text":"🚨 Sannu-Sannu health check failed! Status: '$RESPONSE'"}' \
        $SLACK_WEBHOOK
    
    # Log the incident
    echo "$(date): Health check failed with status $RESPONSE" >> /var/log/sannu-sannu-monitor.log
fi

# Check disk space
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data '{"text":"⚠️ Sannu-Sannu server disk usage is at '$DISK_USAGE'%"}' \
        $SLACK_WEBHOOK
fi
```

### Cron Jobs Setup
```bash
# Add to crontab: crontab -e

# Laravel scheduler
* * * * * cd /var/www/sannu-sannu/current && php artisan schedule:run >> /dev/null 2>&1

# Health monitoring (every 5 minutes)
*/5 * * * * /var/www/sannu-sannu/scripts/monitor.sh

# Database backup (daily at 2 AM)
0 2 * * * /var/www/sannu-sannu/scripts/backup.sh

# Log cleanup (weekly)
0 0 * * 0 find /var/www/sannu-sannu/current/storage/logs -name "*.log" -mtime +30 -delete
```

---

## Backup & Recovery

### Automated Backup Script
```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/var/backups/sannu-sannu"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
echo "Creating database backup..."
mysqldump --single-transaction --routines --triggers \
    sannu_sannu_prod > $BACKUP_DIR/db_backup_$DATE.sql

# Compress database backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# File backup (excluding cache and logs)
echo "Creating file backup..."
tar --exclude='storage/logs/*' --exclude='storage/framework/cache/*' \
    -czf $BACKUP_DIR/files_backup_$DATE.tar.gz \
    -C /var/www/sannu-sannu/current .

# Upload to S3 (optional)
if command -v aws &> /dev/null; then
    echo "Uploading to S3..."
    aws s3 cp $BACKUP_DIR/db_backup_$DATE.sql.gz s3://sannu-sannu-backups/
    aws s3 cp $BACKUP_DIR/files_backup_$DATE.tar.gz s3://sannu-sannu-backups/
fi

# Clean up old backups
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: $DATE"
```

### Recovery Procedures
```bash
#!/bin/bash
# restore.sh

BACKUP_DATE=$1
BACKUP_DIR="/var/backups/sannu-sannu"

if [ -z "$BACKUP_DATE" ]; then
    echo "Usage: $0 <backup_date>"
    echo "Available backups:"
    ls -la $BACKUP_DIR | grep backup
    exit 1
fi

echo "Restoring from backup: $BACKUP_DATE"

# Put application in maintenance mode
cd /var/www/sannu-sannu/current
php artisan down

# Restore database
echo "Restoring database..."
gunzip -c $BACKUP_DIR/db_backup_$BACKUP_DATE.sql.gz | mysql sannu_sannu_prod

# Restore files
echo "Restoring files..."
tar -xzf $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz -C /var/www/sannu-sannu/current

# Set permissions
chown -R www-data:www-data /var/www/sannu-sannu/current/storage
chmod -R 775 /var/www/sannu-sannu/current/storage

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Bring application back online
php artisan up

echo "Recovery completed successfully!"
```

This comprehensive deployment guide ensures reliable, secure, and scalable deployment of the Sannu-Sannu platform across all environments.