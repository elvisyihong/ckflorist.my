<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

[$script, $email, $name] = array_pad($argv, 3, null);
if (!$email || !$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php database/create_admin.php owner@example.com \"Owner Name\"\n");
    exit(1);
}

fwrite(STDOUT, 'Password: ');
$password = trim((string) fgets(STDIN));
if (strlen($password) < 12) {
    fwrite(STDERR, "Password must contain at least 12 characters.\n");
    exit(1);
}

$pdo = Database::connection();
$statement = $pdo->prepare(
    'INSERT INTO admin_users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)'
);
$statement->execute([
    'name' => trim($name),
    'email' => strtolower(trim($email)),
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'role' => 'owner',
]);

fwrite(STDOUT, "Owner account created.\n");

