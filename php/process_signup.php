<?php
session_start();
require_once 'defines.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

unset($_SESSION['errors']);
unset($_SESSION['success']);
unset($_SESSION['old']);

$requiredFields = ['name', 'email', 'phone', 'password', 'password2'];

foreach ($requiredFields as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        $_SESSION['errors'][] = "All fields are required.";
    }
}

$name      = trim($_POST['name'] ?? '');
$email     = strtolower(trim($_POST['email'] ?? ''));
$phone     = trim($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if (!validateEmail($email)) {
    $_SESSION['errors'][] = "Invalid email format.";
}

if (!validatePhone($phone)) {
    $_SESSION['errors'][] = "Phone number must be 10 digits.";
}

if (!validateName($name)) {
    $_SESSION['errors'][] = "Name can only contain letters and spaces.";
}

if ($password !== $password2) {
    $_SESSION['errors'][] = "Passwords do not match.";
}

if (strlen($password) < 6) {
    $_SESSION['errors'][] = "Password must be at least 6 characters.";
}

if (!empty($_SESSION['errors'])) {
    $_SESSION['old'] = $_POST;
    header("Location: ../index.php?tab=signup");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$con = connectTo();

/* 🔥 Rely on DB UNIQUE constraint instead of SELECT check */
$stmt = $con->prepare(
    "INSERT INTO teacher (name, email, phone, password) VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

if (!$stmt->execute()) {

    if ($con->errno === 1062) {
        $_SESSION['errors'][] = "Email already registered.";
    } else {
        $_SESSION['errors'][] = "Something went wrong. Please try again.";
    }

    $_SESSION['old'] = $_POST;
    $stmt->close();
    $con->close();
    header("Location: ../index.php?tab=signup");
    exit();
}

$stmt->close();
$con->close();

unset($_SESSION['old']);

$_SESSION['success'] = "Account created successfully! Please login.";
header("Location: ../index.php?tab=login");
exit();
?>