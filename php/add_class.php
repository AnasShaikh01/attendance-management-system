<?php
session_start();
header('Content-Type: application/json');

require_once 'node_class.php';
require_once 'defines.php';

/* ==============================
   SECURITY CHECK
================================= */
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

/* ==============================
   VALIDATE INPUTS
================================= */
$required = ['code','year','semester','section','start','end'];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        echo json_encode(["error" => "missing_$field"]);
        exit();
    }
}

$teacher_id = $_SESSION['teacher_id'];

$code      = strtoupper(trim($_POST['code']));
$year      = intval($_POST['year']);
$semester  = intval($_POST['semester']);
$section   = intval($_POST['section']);
$startRoll = strtoupper(trim($_POST['start']));
$endRoll   = strtoupper(trim($_POST['end']));

/* ==============================
   STRICT VALIDATION
================================= */

if (!preg_match('/^[A-Z]{2,4}-[0-9]{2,3}$/', $code)) {
    echo json_encode(["error" => "invalid_code"]);
    exit();
}

if (!preg_match('/^[0-9]{3}\/[A-Z]{2}\/[0-9]{2}$/', $startRoll)) {
    echo json_encode(["error" => "invalid_start_roll"]);
    exit();
}

if (!preg_match('/^[0-9]{3}\/[A-Z]{2}\/[0-9]{2}$/', $endRoll)) {
    echo json_encode(["error" => "invalid_end_roll"]);
    exit();
}

if ($semester < 1 || $semester > 8) {
    echo json_encode(["error" => "invalid_semester"]);
    exit();
}

if ($section < 1 || $section > 10) {
    echo json_encode(["error" => "invalid_section"]);
    exit();
}

$currentYear = date('Y');
if ($year < 1983 || $year > $currentYear + 1) {
    echo json_encode(["error" => "invalid_year"]);
    exit();
}

/* ==============================
   LOGICAL ROLL CHECK
================================= */
$startNum = intval(substr($startRoll, 0, 3));
$endNum   = intval(substr($endRoll, 0, 3));

if ($startNum > $endNum) {
    echo json_encode(["error" => "invalid_roll_range"]);
    exit();
}

/* ==============================
   CREATE CLASS
================================= */
try {

    $existing = $_SESSION['classes'] ?? [];

    $node = new Node(
        $code,
        $teacher_id,
        $year,
        $semester,
        $section,
        $startRoll,
        $endRoll
    );

    loadTeacherSession($teacher_id);

    $updated = $_SESSION['classes'] ?? [];

    $newClassId = array_values(array_diff($updated, $existing))[0] ?? null;

    if (!$newClassId) {
        echo json_encode(["error" => "class_creation_failed"]);
        exit();
    }

    echo json_encode([
        "success"  => true,
        "class_id" => $newClassId,
        "code"     => $code,
        "year"     => $year,
        "section"  => $section
    ]);

} catch (Exception $e) {

    error_log("Class creation error: " . $e->getMessage());

    echo json_encode([
        "error" => "server_error"
    ]);
}