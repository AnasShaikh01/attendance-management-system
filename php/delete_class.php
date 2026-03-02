<?php
session_start();
require_once 'defines.php';

/* ==============================
   SECURITY CHECK
================================= */
if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../teacher.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$class_id   = intval($_GET['id']);

$con = connectTo();

try {

    // Verify ownership first
    $stmt = $con->prepare(
        "SELECT uid FROM objects 
         WHERE uid = ? AND teacher_uid = ?"
    );

    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $con->close();
        header("Location: ../teacher.php");
        exit();
    }

    $stmt->close();

    // Delete class
    $delete = $con->prepare(
        "DELETE FROM objects 
         WHERE uid = ? AND teacher_uid = ?"
    );

    $delete->bind_param("ii", $class_id, $teacher_id);

    if (!$delete->execute()) {
        throw new Exception("Delete failed");
    }

    $delete->close();
    $con->close();

    // Refresh teacher session
    loadTeacherSession($teacher_id);

    header("Location: ../teacher.php");
    exit();

} catch (Exception $e) {

    error_log("Delete class error: " . $e->getMessage());

    header("Location: ../teacher.php");
    exit();
}
?>