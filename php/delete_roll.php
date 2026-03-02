<?php
session_start();
require_once 'node_class.php';
require_once 'defines.php';

header('Content-Type: application/json');

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

$teacher_id = $_SESSION['teacher_id'];
$class_id   = intval($_POST['class_id'] ?? 0);
$roll       = strtoupper(trim($_POST['roll'] ?? ''));

if ($class_id <= 0 || $roll === '') {
    echo json_encode(["error" => "invalid_input"]);
    exit();
}

/* ==============================
   VERIFY OWNERSHIP
================================= */
$con = connectTo();

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
    echo json_encode(["error" => "not_found"]);
    exit();
}

$stmt->close();
$con->close();

/* ==============================
   LOAD NODE OBJECT
================================= */
$node = Node::retrieveObjecti($class_id, $teacher_id);

if (!$node) {
    echo json_encode(["error" => "not_found"]);
    exit();
}

/* ==============================
   DELETE ROLL
================================= */
if ($node->deleteRoll($roll)) {

    $node->saveNode();

    echo json_encode([
        "success" => true
    ]);
    exit();
}

echo json_encode([
    "error" => "roll_not_found"
]);
exit();
?>