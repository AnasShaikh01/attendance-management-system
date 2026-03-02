<?php
session_start();
header('Content-Type: application/json');

require_once 'node_class.php';

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$class_id   = intval($_POST['class_id'] ?? 0);
$present    = $_POST['present'] ?? [];

if ($class_id <= 0) {
    echo json_encode(["error" => "missing_class"]);
    exit();
}

if (!is_array($present)) {
    $present = [$present];
}

/* 🔥 Convert to lookup map (performance improvement) */
$present = array_flip($present);

$node = Node::retrieveObjecti($class_id, $teacher_id);

if (!$node) {
    echo json_encode(["error" => "class_not_found"]);
    exit();
}

$records   = $node->getRecords();
$timestamp = time();

/* 🔥 Update attendance */
foreach ($records as $roll => $data) {

    $current = $data['present'];

    if (isset($present[$roll])) {
        $node->setPresence($roll, $current + 1, $timestamp);
    } else {
        $node->setPresence($roll, $current, $timestamp);
    }
}

/* 🔥 Proper encapsulated day increment */
$node->incrementDay();

if (!$node->saveNode()) {
    echo json_encode(["error" => "save_failed"]);
    exit();
}

echo json_encode(["success" => true]);
exit();