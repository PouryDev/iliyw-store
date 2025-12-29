#!/bin/bash
# Script to fix 404 issue on production server

echo "=== Fixing 404 Payment Callback Issue ==="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from Laravel root directory."
    exit 1
fi

echo "1. Checking current routes..."
php artisan route:list | grep -i payment

echo ""
echo "2. Clearing all caches..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear

echo ""
echo "3. Re-caching routes..."
php artisan route:cache

echo ""
echo "4. Re-caching config..."
php artisan config:cache

echo ""
echo "5. Verifying routes after cache..."
php artisan route:list | grep -i payment

echo ""
echo "✅ Done! Now test: https://iliywstore.ir/payment/callback/zibal?success=1"
echo ""

