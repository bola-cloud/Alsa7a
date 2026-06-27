<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'abdelrahman.hossaam@gmail.com')->first();
if (!$user) {
    $user = App\Models\User::find(65);
}

if (!$user) {
    echo "Error: User not found!\n";
    exit(1);
}

$token = $user->createToken('prod-test-65')->plainTextToken;
echo "\nToken for user {$user->name} ({$user->email}):\n";
echo "{$token}\n\n";
