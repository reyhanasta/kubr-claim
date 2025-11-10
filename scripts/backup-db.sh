#!/bin/bash

#############################################
# KUBR Claim System - Database Backup Script
#############################################

APP_DIR="/var/www/kubr-claim"
BACKUP_DIR="/var/backups/kubr-claim"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
RETENTION_DAYS=14

# Create backup directory if not exists
mkdir -p "$BACKUP_DIR"

# Read database credentials from .env
DB_NAME=$(grep DB_DATABASE "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_USER=$(grep DB_USERNAME "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_PASS=$(grep DB_PASSWORD "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
DB_HOST=$(grep DB_HOST "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")

echo "=== KUBR Claim Database Backup ==="
echo "Date: $(date)"
echo "Database: $DB_NAME"

# Create backup
echo "Creating backup..."
if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db-$DATE.sql.gz"; then
    echo "✓ Backup created successfully: $BACKUP_DIR/db-$DATE.sql.gz"
    
    # Get backup size
    BACKUP_SIZE=$(du -h "$BACKUP_DIR/db-$DATE.sql.gz" | cut -f1)
    echo "  Size: $BACKUP_SIZE"
else
    echo "✗ Backup failed!"
    exit 1
fi

# Remove old backups (keep only last X days)
echo ""
echo "Cleaning old backups (keeping last $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "db-*.sql.gz" -mtime +$RETENTION_DAYS -delete
REMAINING_BACKUPS=$(find "$BACKUP_DIR" -name "db-*.sql.gz" | wc -l)
echo "  Remaining backups: $REMAINING_BACKUPS"

# Calculate total backup size
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
echo "  Total backup size: $TOTAL_SIZE"

echo ""
echo "=== Backup Completed ==="
