# 🚀 Production Deployment Guide - KUBR Claim System

## 📋 Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Environment Configuration](#environment-configuration)
3. [Database Setup](#database-setup)
4. [Security Hardening](#security-hardening)
5. [Performance Optimization](#performance-optimization)
6. [Server Requirements](#server-requirements)
7. [Deployment Steps](#deployment-steps)
8. [Post-Deployment Verification](#post-deployment-verification)
9. [Monitoring & Maintenance](#monitoring--maintenance)
10. [Rollback Plan](#rollback-plan)

---

## 1️⃣ Pre-Deployment Checklist

### ✅ Code Quality & Testing

-   [x] All tests passing (31/31 ✅)
-   [x] Code formatted with Pint
-   [x] No syntax errors
-   [x] Validation working correctly
-   [ ] Run full test suite one more time
-   [ ] Test on staging environment

### ✅ Dependencies

```bash
# Verify all dependencies are installed
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### ✅ Git Repository

```bash
# Ensure clean state
git status
git log --oneline -5

# Tag release version
git tag -a v1.0.0 -m "Production Release v1.0.0"
git push origin v1.0.0
```

---

## 2️⃣ Environment Configuration

### 📝 Create Production .env File

**Current Development `.env`:**

```properties
APP_ENV=local
APP_DEBUG=true
APP_URL=http://kubr-claim.test
DB_CONNECTION=sqlite
PDFTOTEXT_PATH=C:\laragon\bin\git\mingw64\bin\pdftotext
FOLDER_BACKUP="D:/Backup Folder Klaim/Folder Klaim Reguler BPJS"
FOLDER_SHARED="D:/mnt/test_shared"
```

**Production `.env.production` (Template):**

```properties
# Application
APP_NAME="KUBR Claim System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Jakarta

# Localization
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

# Security - GENERATE NEW KEY!
APP_KEY=base64:GENERATE_NEW_KEY_HERE

# Database - Use MySQL/PostgreSQL for Production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kubr_claim_production
DB_USERNAME=kubr_claim_user
DB_PASSWORD=SECURE_PASSWORD_HERE

# Session & Cache - Use Redis for Better Performance
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

CACHE_STORE=redis
CACHE_PREFIX=kubr_claim

QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2

# Filesystem - Production Paths
FILESYSTEM_DISK=local

# PDF Processing
PDFTOTEXT_PATH=/usr/bin/pdftotext

# Folder Paths - Production Network Shares
FOLDER_BACKUP="/mnt/backup/klaim-reguler-bpjs"
FOLDER_SHARED="/mnt/shared/klaim-reguler-bpjs"

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=YOUR_MAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Performance
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

# Broadcast
BROADCAST_CONNECTION=log
```

### 🔐 Generate Production APP_KEY

```bash
php artisan key:generate --show
# Copy the output to .env.production
```

---

## 3️⃣ Database Setup

### Option A: MySQL (Recommended for Production)

#### Install MySQL

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install mysql-server

# Start MySQL
sudo systemctl start mysql
sudo systemctl enable mysql
```

#### Create Database & User

```sql
-- Login to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE kubr_claim_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER 'kubr_claim_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON kubr_claim_production.* TO 'kubr_claim_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User = 'kubr_claim_user';

-- Exit
EXIT;
```

#### Run Migrations

```bash
# Backup current SQLite data if needed
php artisan db:backup

# Run migrations on MySQL
php artisan migrate --force

# Seed if necessary (for initial admin user)
php artisan db:seed --class=DatabaseSeeder --force
```

### Option B: PostgreSQL (Alternative)

```bash
# Install PostgreSQL
sudo apt install postgresql postgresql-contrib

# Create database
sudo -u postgres psql
CREATE DATABASE kubr_claim_production;
CREATE USER kubr_claim_user WITH ENCRYPTED PASSWORD 'SECURE_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON DATABASE kubr_claim_production TO kubr_claim_user;
\q
```

Update `.env`:

```properties
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
```

---

## 4️⃣ Security Hardening

### 🔒 File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/kubr-claim

# Set directory permissions
sudo find /var/www/kubr-claim -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/kubr-claim -type f -exec chmod 644 {} \;

# Storage and cache need write access
sudo chmod -R 775 /var/www/kubr-claim/storage
sudo chmod -R 775 /var/www/kubr-claim/bootstrap/cache

# Secure .env file
sudo chmod 600 /var/www/kubr-claim/.env
```

### 🛡️ Security Headers (Nginx)

**Create `/etc/nginx/sites-available/kubr-claim`:**

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/kubr-claim/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logging
    access_log /var/log/nginx/kubr-claim-access.log;
    error_log /var/log/nginx/kubr-claim-error.log;

    # Max upload size
    client_max_body_size 10M;

    # Character set
    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Increase timeouts for PDF processing
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Enable site:**

```bash
sudo ln -s /etc/nginx/sites-available/kubr-claim /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 🔐 Rate Limiting

Add to `app/Http/Kernel.php` or `bootstrap/app.php`:

```php
// For Laravel 12
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi('60,1'); // 60 requests per minute
})
```

---

## 5️⃣ Performance Optimization

### ⚡ Caching

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build frontend assets (production)
npm run build
```

### 🔄 Queue Workers (Redis)

**Install Redis:**

```bash
# Ubuntu/Debian
sudo apt install redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test Redis
redis-cli ping
# Should return: PONG
```

**Setup Queue Worker as Systemd Service:**

Create `/etc/systemd/system/kubr-claim-worker.service`:

```ini
[Unit]
Description=KUBR Claim Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/kubr-claim/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=300

[Install]
WantedBy=multi-user.target
```

**Enable & Start Worker:**

```bash
sudo systemctl daemon-reload
sudo systemctl enable kubr-claim-worker
sudo systemctl start kubr-claim-worker
sudo systemctl status kubr-claim-worker
```

### 📊 Opcache Configuration

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
```

**Restart PHP-FPM:**

```bash
sudo systemctl restart php8.3-fpm
```

---

## 6️⃣ Server Requirements

### Minimum Requirements

-   **OS:** Ubuntu 22.04 LTS or higher
-   **PHP:** 8.3+
-   **Memory:** 4GB RAM minimum, 8GB recommended
-   **Storage:** 50GB SSD minimum
-   **Database:** MySQL 8.0+ or PostgreSQL 14+
-   **Redis:** 6.0+

### Required PHP Extensions

```bash
sudo apt install php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-pgsql php8.3-sqlite3 \
    php8.3-zip php8.3-gd php8.3-mbstring \
    php8.3-curl php8.3-xml php8.3-bcmath \
    php8.3-redis php8.3-intl
```

### Install Poppler Utils (for pdftotext)

```bash
sudo apt install poppler-utils

# Verify installation
which pdftotext
# Should return: /usr/bin/pdftotext
```

### Install Composer

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
composer --version
```

### Install Node.js & NPM

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs
node --version
npm --version
```

---

## 7️⃣ Deployment Steps

### Step 1: Prepare Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install nginx mysql-server redis-server git curl unzip
```

### Step 2: Clone Repository

```bash
# Create directory
sudo mkdir -p /var/www/kubr-claim

# Clone from Git
cd /var/www
sudo git clone https://github.com/reyhanasta/kubr-claim.git kubr-claim

# Or upload via SCP/SFTP
# scp -r /local/path user@server:/var/www/kubr-claim
```

### Step 3: Install Dependencies

```bash
cd /var/www/kubr-claim

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci

# Build assets
npm run build
```

### Step 4: Configure Environment

```bash
# Copy production env
cp .env.production .env

# Edit with your values
nano .env

# Generate app key
php artisan key:generate

# Create storage link
php artisan storage:link
```

### Step 5: Setup Database

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force
```

### Step 6: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/kubr-claim
sudo find /var/www/kubr-claim -type d -exec chmod 755 {} \;
sudo find /var/www/kubr-claim -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/kubr-claim/storage
sudo chmod -R 775 /var/www/kubr-claim/bootstrap/cache
sudo chmod 600 /var/www/kubr-claim/.env
```

### Step 7: Optimize Application

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear any old cache
php artisan optimize:clear
php artisan optimize
```

### Step 8: Configure Web Server

```bash
# Copy Nginx config (see Section 4)
sudo nano /etc/nginx/sites-available/kubr-claim

# Enable site
sudo ln -s /etc/nginx/sites-available/kubr-claim /etc/nginx/sites-enabled/

# Test config
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### Step 9: Setup SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

### Step 10: Start Queue Worker

```bash
# Copy worker service (see Section 5)
sudo nano /etc/systemd/system/kubr-claim-worker.service

# Enable and start
sudo systemctl daemon-reload
sudo systemctl enable kubr-claim-worker
sudo systemctl start kubr-claim-worker
```

---

## 8️⃣ Post-Deployment Verification

### ✅ Health Checks

```bash
# 1. Check web server
curl -I https://your-domain.com
# Should return: HTTP/2 200

# 2. Check PHP-FPM
sudo systemctl status php8.3-fpm

# 3. Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# 4. Check Redis
redis-cli ping
# Should return: PONG

# 5. Check queue worker
sudo systemctl status kubr-claim-worker

# 6. Check storage permissions
ls -la storage/

# 7. Check logs
tail -f storage/logs/laravel.log
```

### 🧪 Functional Testing

1. **Visit homepage**: `https://your-domain.com`
2. **Login**: Test authentication
3. **Upload SEP file**: Test file processing
4. **Submit claim**: Test full workflow
5. **Check shared folders**: Verify files are saved correctly
6. **Check backup folder**: Verify backup job works
7. **Test validation**: Try invalid inputs
8. **Check error pages**: Test 404, 500 pages

### 📊 Performance Testing

```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 https://your-domain.com/

# Monitor resources
htop

# Check queue processing
php artisan queue:monitor

# Check logs
tail -f /var/log/nginx/kubr-claim-access.log
tail -f storage/logs/laravel.log
```

---

## 9️⃣ Monitoring & Maintenance

### 📈 Setup Monitoring

**Laravel Telescope (Development Only):**

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Production Monitoring with Laravel Pulse:**

```bash
composer require laravel/pulse
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate
```

### 🔄 Scheduled Tasks

Add to crontab:

```bash
sudo crontab -e -u www-data

# Add this line:
* * * * * cd /var/www/kubr-claim && php artisan schedule:run >> /dev/null 2>&1
```

### 📝 Log Rotation

Create `/etc/logrotate.d/kubr-claim`:

```
/var/www/kubr-claim/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 🔍 Monitoring Scripts

Create `/var/www/kubr-claim/scripts/health-check.sh`:

```bash
#!/bin/bash

# Health check script
echo "=== KUBR Claim Health Check ==="
echo "Date: $(date)"

# Check web server
curl -s -o /dev/null -w "Web Server: %{http_code}\n" https://your-domain.com

# Check queue worker
systemctl is-active --quiet kubr-claim-worker && echo "Queue Worker: Active" || echo "Queue Worker: INACTIVE!"

# Check disk space
df -h | grep -E '/$|/var/www'

# Check Redis
redis-cli ping > /dev/null && echo "Redis: Connected" || echo "Redis: DISCONNECTED!"

# Check database
php /var/www/kubr-claim/artisan tinker --execute="DB::connection()->getPdo(); echo 'Database: Connected';"

echo "=== End Health Check ==="
```

**Run via cron (every 5 minutes):**

```bash
*/5 * * * * /var/www/kubr-claim/scripts/health-check.sh >> /var/log/kubr-claim-health.log 2>&1
```

---

## 🔄 Rollback Plan

### Quick Rollback Steps

```bash
# 1. Stop queue worker
sudo systemctl stop kubr-claim-worker

# 2. Restore previous version
cd /var/www
sudo mv kubr-claim kubr-claim-broken
sudo git clone https://github.com/reyhanasta/kubr-claim.git kubr-claim
cd kubr-claim
sudo git checkout v1.0.0  # previous stable version

# 3. Restore database backup
mysql -u kubr_claim_user -p kubr_claim_production < backup-2025-11-06.sql

# 4. Restore .env
sudo cp /var/www/kubr-claim-broken/.env /var/www/kubr-claim/.env

# 5. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# 6. Clear cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
sudo systemctl start kubr-claim-worker

# 8. Verify
curl -I https://your-domain.com
```

### Database Backup Strategy

**Automated Daily Backups:**

```bash
# Create backup script
sudo nano /var/www/kubr-claim/scripts/backup-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/kubr-claim"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u kubr_claim_user -p'PASSWORD' kubr_claim_production | gzip > $BACKUP_DIR/db-$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db-*.sql.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

**Add to crontab (daily at 2 AM):**

```bash
0 2 * * * /var/www/kubr-claim/scripts/backup-db.sh >> /var/log/kubr-claim-backup.log 2>&1
```

---

## 📋 Production Checklist

### Before Go-Live

-   [ ] All tests passing (31/31)
-   [ ] Production .env configured
-   [ ] Database migrated
-   [ ] SSL certificate installed
-   [ ] File permissions set correctly
-   [ ] Queue worker running
-   [ ] Cron jobs configured
-   [ ] Backup strategy implemented
-   [ ] Monitoring tools installed
-   [ ] Error pages customized
-   [ ] Performance tested
-   [ ] Security headers configured
-   [ ] Rate limiting enabled
-   [ ] Log rotation configured

### After Go-Live

-   [ ] Monitor error logs (first 24 hours)
-   [ ] Test all critical features
-   [ ] Verify backup jobs running
-   [ ] Check queue processing
-   [ ] Monitor server resources
-   [ ] Test rollback procedure
-   [ ] Update documentation
-   [ ] Train users
-   [ ] Create admin accounts
-   [ ] Set up alerts

---

## 🆘 Troubleshooting

### Common Issues

**1. 500 Internal Server Error**

```bash
# Check PHP-FPM logs
sudo tail -f /var/log/php8.3-fpm.log

# Check Laravel logs
tail -f /var/www/kubr-claim/storage/logs/laravel.log

# Check permissions
ls -la /var/www/kubr-claim/storage
```

**2. Queue Jobs Not Processing**

```bash
# Check worker status
sudo systemctl status kubr-claim-worker

# Check Redis
redis-cli ping

# Restart worker
sudo systemctl restart kubr-claim-worker

# Monitor queue
php artisan queue:monitor
```

**3. PDF Processing Fails**

```bash
# Check pdftotext
which pdftotext
pdftotext -v

# Test manually
pdftotext test.pdf test.txt

# Check permissions on temp folder
ls -la storage/app/temp/
```

**4. Database Connection Issues**

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL
sudo systemctl status mysql
mysql -u kubr_claim_user -p

# Check .env database credentials
cat .env | grep DB_
```

---

## 📞 Support & Contacts

-   **Developer:** Reyhan Asta
-   **Repository:** https://github.com/reyhanasta/kubr-claim
-   **Documentation:** See project README.md

---

## 🎉 Congratulations!

Your KUBR Claim System is now production-ready! 🚀

**Next Steps:**

1. Monitor the system for the first week
2. Collect user feedback
3. Plan iterative improvements
4. Keep dependencies updated
5. Regular security audits

**Good luck with your deployment!** 🌟
