<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Helpers\Str;
use App\Models\Admin;

Env::load(__DIR__ . '/../.env');
Config::load(__DIR__ . '/../config');

$username = env('ADMIN_USERNAME', 'admin');

if (Admin::findByUsername($username)) {
    echo "'{$username}' kullanıcısı zaten mevcut, atlanıyor.\n";
    exit;
}

$password = env('ADMIN_PASSWORD');
$generated = false;

if (!$password) {
    $password = Str::random(12);
    $generated = true;
}

Admin::create([
    'username' => $username,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'name' => env('ADMIN_NAME', 'Yönetici'),
    'is_active' => 1,
]);

echo "Admin oluşturuldu: {$username}\n";

if ($generated) {
    echo "Şifre (bir daha gösterilmeyecek): {$password}\n";
}
