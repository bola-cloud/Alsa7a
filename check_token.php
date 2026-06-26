<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tokenString = '554|MsUmY92Xq9zReXd64AW2f4qKefGlQPHdwniwkjbw4886b265';
[$id, $token] = explode('|', $tokenString, 2);

$accessToken = Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);

if (!$accessToken) {
    echo "Token not found in DB\n";
    exit;
}

$user = $accessToken->tokenable;
echo "User ID: {$user->id}\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "parent_code in DB: " . ($user->parent_code === null ? 'IS NULL' : $user->parent_code) . "\n";
