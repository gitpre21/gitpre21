<?php
// reset_users.php
include 'db.php';

// Clear existing users (optional)
$conn->query("DELETE FROM users");

// Define default users
$users = [
    [
        'role'     => 'Admin',
        'username' => 'admin',
        'password' => 'admin123', // login password
        'first_name' => 'Super',
        'last_name'  => 'Admin'
    ],
    [
        'role'     => 'Faculty',
        'username' => 'faculty1',
        'password' => 'faculty123',
        'first_name' => 'John',
        'last_name'  => 'Doe'
    ],
    [
        'role'     => 'Student',
        'username' => 'student1',
        'password' => 'student123',
        'first_name' => 'Jane',
        'last_name'  => 'Smith'
    ]
];

// Loop through users and insert into DB
foreach ($users as $user) {

    // Get role_id
    $roleRow = $conn->query("SELECT role_id FROM roles WHERE role_name='{$user['role']}' LIMIT 1")->fetch_assoc();
    $role_id = $roleRow['role_id'];

    // Hash password
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (role_id, username, password, first_name, last_name)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $role_id, $user['username'], $hashed_password, $user['first_name'], $user['last_name']);
    $stmt->execute();

    echo "{$user['role']} user '{$user['username']}' created successfully.<br>";
}

echo "All default users are inserted with hashed passwords!";
?>
