<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(26);
$user->parent_code = 'PARENT-TEST-123';
$user->save();

$token = $user->createToken('test-local-26')->plainTextToken;
echo "Token: {$token}\n";
