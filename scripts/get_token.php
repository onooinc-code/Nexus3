<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$user = User::first();
if ($user) {
    echo $user->createToken('dev')->plainTextToken;
} else {
    echo 'NO_USER';
}
