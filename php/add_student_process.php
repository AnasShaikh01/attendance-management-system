<?php
session_start();
require_once 'utils.php';
require_once 'defines.php';

/* ==============================
   SECURITY CHECK
================================= */
if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../add_student.php");
    exit();
}

/* ==============================
   INPUT VALIDATION
================================= */
$name    = trim($_POST['name'] ?? '');
$roll    = strtoupper(trim($_POST['roll_number'] ?? ''));
$class   = trim($_POST['class'] ?? '');
$section = trim($_POST['section'] ?? '');

if ($name === '' || $roll === '') {
    header("Location: ../add_student.php?error=Name and Roll Number are required.");
    exit();
}

if (!verify(NAME, $name)) {
    header("Location: ../add_student.php?error=Invalid name format.");
    exit();
}

if (!verify(ROLL, $roll)) {
    header("Location: ../add_student.php?error=Invalid roll number format.");
    exit();
}

if ($class === '' || $section === '') {
    header("Location: ../add_student.php?error=Class and Section are required.");
    exit();
}

/* ==============================
   DATABASE OPERATIONS
================================= */
$conn = connectTo();

try {

    // Check duplicate
    $check = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
    $check->bind_param("s", $roll);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        $conn->close();
        header("Location: ../add_student.php?error=Student already exists.");
        exit();
    }

    $check->close();

    // Insert
    $stmt = $conn->prepare(
        "INSERT INTO students (name, roll_number, class, section)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssss", $name, $roll, $class, $section);

    if (!$stmt->execute()) {
        throw new Exception("Insert failed");
    }

    $stmt->close();
    $conn->close();

    header("Location: ../add_student.php?success=1");
    exit();

} catch (Exception $e) {

    error_log("Add student error: " . $e->getMessage());

    header("Location: ../add_student.php?error=Server error occurred.");
    exit();
}
?>