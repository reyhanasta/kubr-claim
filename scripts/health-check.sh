#!/bin/bash

#############################################
# KUBR Claim System - Health Check Script
#############################################

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

APP_DIR="/var/www/kubr-claim"
APP_URL="https://your-domain.com"  # Update with your actual domain

echo "================================================"
echo "KUBR Claim System - Health Check"
echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================"
echo ""

# 1. Check Web Server
echo -n "Web Server (HTTP): "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL")
if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}OK${NC} (HTTP $HTTP_CODE)"
else
    echo -e "${RED}FAILED${NC} (HTTP $HTTP_CODE)"
fi

# 2. Check PHP-FPM
echo -n "PHP-FPM Service: "
if systemctl is-active --quiet php8.3-fpm; then
    echo -e "${GREEN}Running${NC}"
else
    echo -e "${RED}Not Running${NC}"
fi

# 3. Check Nginx
echo -n "Nginx Service: "
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}Running${NC}"
else
    echo -e "${RED}Not Running${NC}"
fi

# 4. Check Queue Worker
echo -n "Queue Worker: "
if systemctl is-active --quiet kubr-claim-worker; then
    echo -e "${GREEN}Running${NC}"
else
    echo -e "${YELLOW}Not Running${NC}"
fi

# 5. Check Redis
echo -n "Redis: "
if redis-cli ping > /dev/null 2>&1; then
    echo -e "${GREEN}Connected${NC}"
else
    echo -e "${RED}Disconnected${NC}"
fi

# 6. Check Database
echo -n "Database: "
cd "$APP_DIR"
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" > /dev/null 2>&1; then
    echo -e "${GREEN}Connected${NC}"
else
    echo -e "${RED}Connection Failed${NC}"
fi

# 7. Check Disk Space
echo ""
echo "Disk Space:"
df -h | grep -E '/$|/var/www' | awk '{printf "  %s: %s used (%s available)\n", $6, $5, $4}'

# 8. Check Memory Usage
echo ""
echo "Memory Usage:"
free -h | awk 'NR==2{printf "  Used: %s / %s (%.2f%%)\n", $3, $2, $3*100/$2 }'

# 9. Check Storage Permissions
echo ""
echo -n "Storage Writable: "
if [ -w "$APP_DIR/storage" ]; then
    echo -e "${GREEN}Yes${NC}"
else
    echo -e "${RED}No${NC}"
fi

# 10. Check Recent Errors in Laravel Log
echo ""
echo "Recent Errors (Last 5):"
if [ -f "$APP_DIR/storage/logs/laravel.log" ]; then
    ERROR_COUNT=$(grep -c "ERROR" "$APP_DIR/storage/logs/laravel.log" 2>/dev/null || echo "0")
    echo "  Total errors in log: $ERROR_COUNT"
    
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "  Latest errors:"
        grep "ERROR" "$APP_DIR/storage/logs/laravel.log" | tail -5 | while read line; do
            echo -e "    ${RED}$line${NC}"
        done
    fi
else
    echo "  No log file found"
fi

# 11. Check Queue Jobs
echo ""
echo "Queue Status:"
cd "$APP_DIR"
php artisan queue:monitor 2>/dev/null | head -5

# 12. Check SSL Certificate Expiry
echo ""
echo -n "SSL Certificate: "
if command -v openssl &> /dev/null; then
    CERT_EXPIRY=$(echo | openssl s_client -servername $(echo $APP_URL | sed 's/https:\/\///') -connect $(echo $APP_URL | sed 's/https:\/\///'):443 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
    if [ -n "$CERT_EXPIRY" ]; then
        EXPIRY_EPOCH=$(date -d "$CERT_EXPIRY" +%s)
        NOW_EPOCH=$(date +%s)
        DAYS_LEFT=$(( ($EXPIRY_EPOCH - $NOW_EPOCH) / 86400 ))
        
        if [ $DAYS_LEFT -lt 30 ]; then
            echo -e "${RED}Expires in $DAYS_LEFT days${NC}"
        else
            echo -e "${GREEN}Valid ($DAYS_LEFT days remaining)${NC}"
        fi
    else
        echo -e "${YELLOW}Unable to check${NC}"
    fi
else
    echo -e "${YELLOW}OpenSSL not installed${NC}"
fi

echo ""
echo "================================================"
echo "Health Check Completed"
echo "================================================"
