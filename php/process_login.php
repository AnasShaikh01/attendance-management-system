<?php
session_start();
require_once 'defines.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

unset($_SESSION['errors']);
unset($_SESSION['old']);

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['errors'][] = "Please enter email and password.";
    $_SESSION['old'] = $_POST;
    header("Location: ../index.php?tab=login");
    exit();
}

if (!validateEmail($email)) {
    $_SESSION['errors'][] = "Invalid email format.";
    $_SESSION['old'] = $_POST;
    header("Location: ../index.php?tab=login");
    exit();
}

$con = connectTo();

$stmt = $con->prepare(
    "SELECT uid, password FROM teacher WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['errors'][] = "Invalid email or password.";
    $_SESSION['old'] = $_POST;
    $stmt->close();
    $con->close();
    header("Location: ../index.php?tab=login");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    $_SESSION['errors'][] = "Invalid email or password.";
    $_SESSION['old'] = $_POST;
    $stmt->close();
    $con->close();
    header("Location: ../index.php?tab=login");
    exit();
}

session_regenerate_id(true);

/* 🔥 Use centralized session loader */
loadTeacherSession($user['uid']);

$stmt->close();
$con->close();

header("Location: ../teacher.php");
exit();
?>