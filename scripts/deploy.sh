#!/bin/bash

#############################################
# KUBR Claim System - Production Deployment
# Automated Deployment Script
#############################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/kubr-claim"
BACKUP_DIR="/var/backups/kubr-claim"
PHP_FPM_SERVICE="php8.3-fpm"
NGINX_SERVICE="nginx"
WORKER_SERVICE="kubr-claim-worker"

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then 
        log_error "Please run as root or with sudo"
        exit 1
    fi
}

backup_database() {
    log_info "Creating database backup..."
    mkdir -p "$BACKUP_DIR"
    DATE=$(date +%Y-%m-%d_%H-%M-%S)
    
    # Read database credentials from .env
    DB_NAME=$(grep DB_DATABASE "$APP_DIR/.env" | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME "$APP_DIR/.env" | cut -d '=' -f2)
    DB_PASS=$(grep DB_PASSWORD "$APP_DIR/.env" | cut -d '=' -f2)
    
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db-pre-deploy-$DATE.sql.gz"
    log_info "Database backup created: $BACKUP_DIR/db-pre-deploy-$DATE.sql.gz"
}

enable_maintenance_mode() {
    log_info "Enabling maintenance mode..."
    cd "$APP_DIR"
    php artisan down --render="errors::503" --retry=60
}

disable_maintenance_mode() {
    log_info "Disabling maintenance mode..."
    cd "$APP_DIR"
    php artisan up
}

pull_latest_code() {
    log_info "Pulling latest code from repository..."
    cd "$APP_DIR"
    git pull origin main
}

install_dependencies() {
    log_info "Installing Composer dependencies..."
    cd "$APP_DIR"
    composer install --optimize-autoloader --no-dev
    
    log_info "Installing NPM dependencies..."
    npm ci
    
    log_info "Building assets..."
    npm run build
}

run_migrations() {
    log_info "Running database migrations..."
    cd "$APP_DIR"
    php artisan migrate --force
}

clear_caches() {
    log_info "Clearing caches..."
    cd "$APP_DIR"
    php artisan optimize:clear
}

optimize_app() {
    log_info "Optimizing application..."
    cd "$APP_DIR"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
}

set_permissions() {
    log_info "Setting correct permissions..."
    chown -R www-data:www-data "$APP_DIR"
    find "$APP_DIR" -type d -exec chmod 755 {} \;
    find "$APP_DIR" -type f -exec chmod 644 {} \;
    chmod -R 775 "$APP_DIR/storage"
    chmod -R 775 "$APP_DIR/bootstrap/cache"
    chmod 600 "$APP_DIR/.env"
}

restart_services() {
    log_info "Restarting services..."
    systemctl restart "$PHP_FPM_SERVICE"
    systemctl reload "$NGINX_SERVICE"
    
    if systemctl is-active --quiet "$WORKER_SERVICE"; then
        systemctl restart "$WORKER_SERVICE"
    else
        log_warning "Queue worker service not found or not running"
    fi
}

verify_deployment() {
    log_info "Verifying deployment..."
    
    # Check if services are running
    if systemctl is-active --quiet "$PHP_FPM_SERVICE"; then
        log_info "PHP-FPM: Running"
    else
        log_error "PHP-FPM: Not running!"
        exit 1
    fi
    
    if systemctl is-active --quiet "$NGINX_SERVICE"; then
        log_info "Nginx: Running"
    else
        log_error "Nginx: Not running!"
        exit 1
    fi
    
    # Check database connection
    cd "$APP_DIR"
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" > /dev/null 2>&1; then
        log_info "Database: Connected"
    else
        log_error "Database: Connection failed!"
        exit 1
    fi
    
    log_info "Deployment verification completed successfully!"
}

# Main deployment process
main() {
    log_info "================================"
    log_info "KUBR Claim System - Deployment"
    log_info "================================"
    echo ""
    
    # Check if running as root
    check_root
    
    # Confirm deployment
    read -p "Do you want to proceed with deployment? (yes/no): " CONFIRM
    if [ "$CONFIRM" != "yes" ]; then
        log_warning "Deployment cancelled"
        exit 0
    fi
    
    # Start deployment
    log_info "Starting deployment process..."
    echo ""
    
    # Step 1: Backup database
    backup_database
    
    # Step 2: Enable maintenance mode
    enable_maintenance_mode
    
    # Step 3: Pull latest code
    pull_latest_code
    
    # Step 4: Install dependencies
    install_dependencies
    
    # Step 5: Run migrations
    run_migrations
    
    # Step 6: Clear old caches
    clear_caches
    
    # Step 7: Optimize application
    optimize_app
    
    # Step 8: Set permissions
    set_permissions
    
    # Step 9: Restart services
    restart_services
    
    # Step 10: Disable maintenance mode
    disable_maintenance_mode
    
    # Step 11: Verify deployment
    verify_deployment
    
    echo ""
    log_info "================================"
    log_info "Deployment completed successfully!"
    log_info "================================"
}

# Run main function
main
