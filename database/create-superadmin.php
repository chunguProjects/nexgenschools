<?php
// Include DB connection
$host = 'localhost';
$db   = 'nextgen';
$user = 'root';
$pass = ''; // or your MySQL root password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Hash password
    $plainPassword = "12345678";
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Insert Super Admin
    $stmt = $pdo->prepare("INSERT INTO users (
        school_id, full_name, country_code, phone_number,
        username, email, password, role_id, password_updated
    ) VALUES (
        NULL, :full_name, :country_code, :phone_number,
        :username, :email, :password, :role_id, 0
    )");

    $stmt->execute([
        'full_name'     => 'System SuperAdmin',
        'country_code'  => '+265',
        'phone_number'  => '0999000000',
        'username'      => 'superadmin',
        'email'         => 'superadmin@example.com',
        'password'      => $hashedPassword,
        'role_id'       => 1
    ]);

    echo "✅ Super Admin created successfully.";
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
