# 🚀 Production Deployment - Quick Start Guide

## ⚡ Quick Deployment Steps

### 1. Pre-Deployment (Local)

```bash
# Run all tests
php artisan test

# Format code
vendor/bin/pint

# Build production assets
npm run build

# Tag release
git tag -a v1.0.0 -m "Production Release"
git push origin v1.0.0
```

### 2. Server Setup (One-time)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install nginx mysql-server redis-server \
    php8.3-fpm php8.3-mysql php8.3-redis \
    poppler-utils git curl unzip

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE kubr_claim_production;
CREATE USER 'kubr_claim_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD';
GRANT ALL ON kubr_claim_production.* TO 'kubr_claim_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Deploy Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/reyhanasta/kubr-claim.git

# Enter directory
cd kubr-claim

# Copy environment file
cp .env.production .env
nano .env  # Edit with your values

# Generate app key
php artisan key:generate

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data /var/www/kubr-claim
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 600 .env

# Create storage link
php artisan storage:link

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Configure Nginx

```bash
# Copy config from documentation
sudo nano /etc/nginx/sites-available/kubr-claim

# Enable site
sudo ln -s /etc/nginx/sites-available/kubr-claim /etc/nginx/sites-enabled/

# Test and reload
sudo nginx -t
sudo systemctl reload nginx
```

### 6. SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d your-domain.com
```

### 7. Setup Queue Worker

```bash
# Copy worker service from documentation
sudo nano /etc/systemd/system/kubr-claim-worker.service

# Enable and start
sudo systemctl daemon-reload
sudo systemctl enable kubr-claim-worker
sudo systemctl start kubr-claim-worker
```

### 8. Setup Cron Jobs

```bash
sudo crontab -e -u www-data

# Add:
* * * * * cd /var/www/kubr-claim && php artisan schedule:run >> /dev/null 2>&1
0 2 * * * /var/www/kubr-claim/scripts/backup-db.sh >> /var/log/kubr-backup.log 2>&1
*/5 * * * * /var/www/kubr-claim/scripts/health-check.sh >> /var/log/kubr-health.log 2>&1
```

---

## ✅ Essential Commands

### Application Management

```bash
# Enable maintenance mode
php artisan down

# Disable maintenance mode
php artisan up

# Clear all caches
php artisan optimize:clear

# Cache for production
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Run migrations
php artisan migrate --force

# Check queue status
php artisan queue:monitor
```

### Service Management

```bash
# Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
sudo systemctl restart kubr-claim-worker

# Check service status
sudo systemctl status php8.3-fpm
sudo systemctl status nginx
sudo systemctl status kubr-claim-worker
```

### Monitoring

```bash
# Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Check queue jobs
php artisan queue:work --once

# Health check
bash scripts/health-check.sh

# Database backup
bash scripts/backup-db.sh
```

---

## 🔧 Troubleshooting

### 500 Error

```bash
# Check permissions
sudo chown -R www-data:www-data /var/www/kubr-claim
sudo chmod -R 775 storage bootstrap/cache

# Check logs
tail -f storage/logs/laravel.log
```

### Queue Not Processing

```bash
# Restart worker
sudo systemctl restart kubr-claim-worker

# Check Redis
redis-cli ping

# Manual queue work
php artisan queue:work --once
```

### Database Connection Failed

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL
sudo systemctl status mysql
mysql -u kubr_claim_user -p
```

---

## 📊 Quick Health Check

```bash
# All-in-one health check
curl -I https://your-domain.com                    # Web server
systemctl is-active php8.3-fpm                     # PHP-FPM
systemctl is-active nginx                          # Nginx
systemctl is-active kubr-claim-worker              # Queue worker
redis-cli ping                                     # Redis
php artisan tinker --execute="DB::connection()->getPdo();"  # Database
df -h | grep -E '/$|/var/www'                      # Disk space
free -h                                            # Memory
```

---

## 🔄 Update Deployment

```bash
# Quick update (use deploy script)
sudo bash /var/www/kubr-claim/scripts/deploy.sh

# Or manual:
cd /var/www/kubr-claim
php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo systemctl restart php8.3-fpm kubr-claim-worker
php artisan up
```

---

## 📞 Emergency Contacts

-   **Developer:** Reyhan Asta
-   **Repository:** https://github.com/reyhanasta/kubr-claim
-   **Full Guide:** See `PRODUCTION_DEPLOYMENT_GUIDE.md`

---

## ⚠️ Critical Notes

1. **Always backup database before deployment**
2. **Test on staging first**
3. **Monitor logs after deployment**
4. **Keep .env file secure (600 permissions)**
5. **Update APP_KEY for production**
6. **Use strong database passwords**
7. **Enable SSL/HTTPS**
8. **Regular security updates**

---

## 🎉 Post-Deployment

-   [ ] Test login functionality
-   [ ] Upload SEP file and verify processing
-   [ ] Submit test claim
-   [ ] Check file storage locations
-   [ ] Verify backup jobs running
-   [ ] Monitor error logs for 24 hours
-   [ ] Load test the application
-   [ ] Train end users

**Good luck! 🚀**
