<?php
/**
 * Debug script to check routes on production server
 * Upload this to server root and run: php debug-routes.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ROUTE DEBUG INFO ===\n\n";

// 1. Check if routes/web.php file exists and has callback route
echo "1. Checking routes/web.php file...\n";
$webRoutesPath = __DIR__ . '/routes/web.php';
if (file_exists($webRoutesPath)) {
    $content = file_get_contents($webRoutesPath);
    if (strpos($content, 'payment/callback') !== false) {
        echo "   ✅ payment/callback route found in routes/web.php\n";
        // Show the line
        $lines = explode("\n", $content);
        foreach ($lines as $num => $line) {
            if (strpos($line, 'payment/callback') !== false) {
                echo "   Line " . ($num + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "   ❌ payment/callback route NOT found in routes/web.php\n";
    }
} else {
    echo "   ❌ routes/web.php file not found!\n";
}

echo "\n2. Checking registered routes...\n";
$routes = app('router')->getRoutes();
$callbackFound = false;
foreach ($routes as $route) {
    if (strpos($route->uri(), 'payment/callback') !== false) {
        $callbackFound = true;
        echo "   ✅ Route registered: " . $route->methods()[0] . " " . $route->uri() . "\n";
        echo "      Name: " . $route->getName() . "\n";
        echo "      Action: " . $route->getActionName() . "\n";
    }
}
if (!$callbackFound) {
    echo "   ❌ payment/callback route NOT registered!\n";
}

echo "\n3. Checking route cache...\n";
$routeCachePath = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCachePath)) {
    echo "   ⚠️  Route cache exists: $routeCachePath\n";
    $cacheContent = file_get_contents($routeCachePath);
    if (strpos($cacheContent, 'payment/callback') !== false) {
        echo "   ✅ payment/callback found in cache\n";
    } else {
        echo "   ❌ payment/callback NOT found in cache - NEED TO CLEAR AND RE-CACHE!\n";
    }
} else {
    echo "   ℹ️  No route cache file\n";
}

echo "\n4. Checking config cache...\n";
$configCachePath = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configCachePath)) {
    echo "   ⚠️  Config cache exists\n";
} else {
    echo "   ℹ️  No config cache\n";
}

echo "\n5. Testing route matching...\n";
try {
    $request = Illuminate\Http\Request::create('/payment/callback/zibal?success=1&status=2&trackId=test', 'GET');
    $response = app('router')->dispatch($request);
    echo "   Status Code: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 404) {
        echo "   ❌ Route returns 404 - Route not matched!\n";
    } else {
        echo "   ✅ Route matched successfully!\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SOLUTION ===\n";
echo "Run these commands on the server:\n";
echo "1. php artisan route:clear\n";
echo "2. php artisan route:cache\n";
echo "3. php artisan config:clear\n";
echo "4. php artisan cache:clear\n";
echo "5. php artisan view:clear\n";
echo "\nIf using Docker:\n";
echo "docker-compose exec app php artisan route:clear && docker-compose exec app php artisan route:cache\n";
echo "\n";



