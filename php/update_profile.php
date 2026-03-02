<?php
session_start();
require_once 'defines.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$name  = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');

if (!validateName($name)) {
    echo json_encode(["error" => "invalid_name"]);
    exit();
}

if (!validateEmail($email)) {
    echo json_encode(["error" => "invalid_email"]);
    exit();
}

if (!validatePhone($phone)) {
    echo json_encode(["error" => "invalid_phone"]);
    exit();
}

$con = connectTo();

/* 🔥 Check email uniqueness */
$stmt = $con->prepare(
    "SELECT uid FROM teacher WHERE email = ? AND uid != ?"
);

$stmt->bind_param("si", $email, $teacher_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["error" => "email_exists"]);
    $stmt->close();
    $con->close();
    exit();
}

$stmt->close();

/* 🔥 Update profile */
$stmt = $con->prepare(
    "UPDATE teacher SET name = ?, email = ?, phone = ? WHERE uid = ?"
);

$stmt->bind_param("sssi", $name, $email, $phone, $teacher_id);

if (!$stmt->execute()) {
    echo json_encode(["error" => "update_failed"]);
    $stmt->close();
    $con->close();
    exit();
}

/* 🔥 Update session */
$_SESSION['name']  = $name;
$_SESSION['email'] = $email;
$_SESSION['phone'] = $phone;

$stmt->close();
$con->close();

echo json_encode(["success" => true]);
exit();
?>